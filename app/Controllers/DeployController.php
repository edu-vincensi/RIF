<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class DeployController extends BaseController
{
    public function index()
    {
        // Permite apenas POST
        if (strtolower($this->request->getMethod()) !== 'post')
        {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)
                ->setBody('Method Not Allowed');
        }

        //Confere a senha secreta
        $secret = getenv('GITHUB_WEBHOOK_SECRET');
        if (!$secret)
        {
            log_message('error', 'Deploy: GITHUB_WEBHOOK_SECRET não configurado no .env');
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setBody('Config error');
        }

        // Cabeçalhos do GitHub
        $signatureHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        $event           = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'unknown';
        $deliveryId      = $_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? '';

        $rawPayload = file_get_contents('php://input');

        // Valida assinatura
        if (!$this->isValidSignature($rawPayload, $secret, $signatureHeader))
        {
            log_message('error', "Deploy: assinatura inválida para delivery {$deliveryId}");
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setBody('Invalid signature');
        }

        $payload = json_decode($rawPayload, true);
        if (!is_array($payload))
        {
            log_message('error', 'Deploy: payload JSON inválido');
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setBody('Invalid payload');
        }

        // Decide se deve ou não fazer deploy com base no evento e branch
        if (!$this->shouldDeploy($event, $payload))
        {
            // Não é erro — apenas um evento que você optou por ignorar
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setBody('No deploy for this event');
        }

        // Configurações de deploy
        $projectRoot   = getenv('DEPLOY_PROJECT_ROOT') ?: ROOTPATH; // ROOTPATH = raiz do projeto CI4
        $branch        = getenv('DEPLOY_BRANCH') ?: 'main';
        $phpBin        = getenv('DEPLOY_PHP_BIN') ?: 'php';
        $composerBin   = getenv('DEPLOY_COMPOSER_BIN') ?: 'composer';

        // Comandos na ordem desejada
        $commands = [
            "git fetch origin {$branch}",
            "git reset --hard origin/{$branch}",
            "{$composerBin} install --no-dev --no-interaction --prefer-dist --optimize-autoloader",
            "{$phpBin} spark migrate --all --no-interaction",
        ];

        $results = [];
        $allOk   = true;

        foreach ($commands as $cmd)
        {
            $result   = $this->runCommand($cmd, $projectRoot);
            $results[] = $result;

            if ($result['exitCode'] !== 0)
            {
                $allOk = false;
            }
        }

        if (!$allOk)
        {
            log_message('error', 'Deploy: falha em um ou mais comandos: ' . json_encode($results));

            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Deploy failed',
                    'results' => $results,
                ]);
        }

        log_message('info', 'Deploy: sucesso. Resultado: ' . json_encode($results));

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'ok',
                'message' => 'Deploy completed',
                'results' => $results,
            ]);
    }

    private function isValidSignature(string $payload, string $secret, string $signatureHeader): bool
    {
        if (strpos($signatureHeader, 'sha256=') !== 0)
        {
            return false;
        }

        $signature = substr($signatureHeader, 7);

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    private function shouldDeploy(string $event, array $payload): bool
    {
        $branch = getenv('DEPLOY_BRANCH') ?: 'main';

        switch ($event)
        {
            case 'push':
                $ref = $payload['ref'] ?? '';
                return $ref === 'refs/heads/' . $branch;

            case 'pull_request':
                $action    = $payload['action'] ?? '';
                $merged    = $payload['pull_request']['merged'] ?? false;
                $baseRef   = $payload['pull_request']['base']['ref'] ?? '';

                return $action === 'closed'
                    && $merged === true
                    && $baseRef === $branch;

            // Ignora outros eventos
            default:
                return false;
        }
    }

    private function runCommand(string $command, string $cwd): array
    {
        $descriptorSpec = [
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w'], // STDERR
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $cwd);

        if (!is_resource($process))
        {
            return [
                'command'  => $command,
                'output'   => '',
                'error'    => 'Não foi possível iniciar o processo',
                'exitCode' => 1,
            ];
        }

        $output = stream_get_contents($pipes[1]);
        $error  = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return [
            'command'  => $command,
            'output'   => trim($output),
            'error'    => trim($error),
            'exitCode' => $exitCode,
        ];
    }
}
