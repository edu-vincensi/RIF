<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\EnviarMensagensModel;
use App\Models\ControleRefeicoesModel;
use App\Models\AlunoTelefoneModel;

use App\Libraries\EvolutionAPI;


class WebhookController extends BaseController
{
    public function index()
    {
        //
    }

    public function response()
    {
        $mensagemModel = new EnviarMensagensModel();
        $refeicaoModel = new ControleRefeicoesModel();
        $alunoTelefoneModel = new AlunoTelefoneModel();
        $evolutionAPI = new EvolutionAPI();

        //resposta do webhook
        $dados = $this->request->getJSON(true); 

        $evento = $dados['event'];
        $data = $dados['data'];

        if ($evento === 'messages.upsert') {

            //ignorar as mensagens do telefone de origem
            // if ($data['key']['fromMe'] === true) {
            //     return;
            // }

            $destinatarioSujo = $data['key']['remoteJid'];
            $destinatarioCompleto = str_replace('@s.whatsapp.net', '', $destinatarioSujo); //destinatario completo = com dd de país
            $ddd_e_numero  =  substr($destinatarioCompleto, 2); // dd da cidade e o telefone

            $ddd = substr($ddd_e_numero, 0, 2); 
            $numero = substr($ddd_e_numero, 2);

            // acrescenta um 9 se tiver apenas 8 números
            if (strlen($numero) === 8) {
                $destinatario = $ddd . '9' . $numero;
            } else {
                $destinatario = $ddd_e_numero;
            }

            $resposta = trim($data['message']['conversation']);

            $alunoTelefone = $alunoTelefoneModel
                ->where('telefone', $destinatario)
                ->first();

            $alunoMatricula = $alunoTelefone['aluno_id'];

            $mensagemRetorno = '';

            $mensagem = $mensagemModel
                ->where('destinatario', $destinatario)
                ->where('status', 1)
                ->orderBy('data_envio', 'DESC')
                ->first();
            
            if (!$mensagem) {
                $mensagemRetorno = 'Mensagem não encontrada';
                //return;
            }

            $categoria = $mensagem['categoria'];

            $mensagemModel->update($mensagem['id'], ['status' => 2]); //Recebida

            //Quando a resposta é sobre solicitação
            if ($categoria == 0) {

                $refeicao = $refeicaoModel
                    ->where('aluno_id', $alunoMatricula)
                    ->orderBy('id', 'DESC')
                    ->first();

                $mensagemRetorno = '';
                $dadosAtualizacao = [
                    'data_confirmacao' => date('Y-m-d H:i:s') 
                ];

                if ($resposta === '1') {

                    $refeicaoModel->update($refeicao['id'], ['status' => 1]);

                    $dataRefeicao = $refeicao['data_refeicao'];

                    $this->criarMensagemQrCode($alunoMatricula, $destinatario, $dataRefeicao); 
                    $mensagemRetorno = 'Refeição confirmada. Você receberá o QR Code em breve!';

                } else if ($resposta === '2') {
                    $refeicaoModel->update($refeicao['id'], ['status' => 3]);
                    $mensagemRetorno = 'Refeição recusada.';

                } else {
                    $mensagemRetorno = 'Resposta recebida, mas não reconhecida.';
                }

                $evolutionAPI->sendMessage($destinatario, $mensagemRetorno);
                return;

            } 
            //Quando a mensagem é pra validar o telefone
            elseif ($categoria == 2) {

                $respostaLimpa = trim(str_replace(['(', ')', ' ', '-', '+'], '', $resposta));

                if ($resposta === '1') {
                    $alunoTelefoneModel
                        ->where('telefone', $destinatario)
                        ->where('aluno_id', $alunoMatricula)
                        ->set(['status' => 'ativo'])
                        ->update();

                    $mensagemRetorno = 'Telefone validado e ativado com sucesso!';

                } elseif ($resposta === '2') {

                    $mensagemModel->insert([
                        'destinatario' => $destinatario,
                        'mensagem'     => "Por favor, informe o número de telefone que deseja cadastrar no seguinte formato (DD) 99999-9999.",
                        'status'       => 0,
                        'categoria'    => 2
                    ]);

                } elseif (preg_match('/^\d+$/', $respostaLimpa)) {
                    $telefoneExistente = $alunoTelefoneModel
                        ->where('telefone', $respostaLimpa)
                        ->where('aluno_id', $alunoMatricula)
                        ->first();

                    if ($telefoneExistente) {
                        $alunoTelefoneModel->update($telefoneExistente['id'], ['status' => 'ativo']);
                        $mensagemRetorno = 'Telefone informado foi validado e ativado com sucesso!';
                    } else {
                        $mensagemRetorno = "O número informado não foi encontrado no seu cadastro do SUAP. Entre em contato com o DEPAE para mais informações.";
                    }
                } else {
                    $mensagemRetorno = "Resposta recebida, mas não reconhecida.";
                }
                
            }

            $evolutionAPI->sendMessage($destinatario, $mensagemRetorno);
            
        }
        else{
            return;
        }

        
    }


    public function criarMensagemQrCode(int $alunoId, string $destinatario, string $dataRefeicao)
    {
        $enviaModel = new EnviarMensagensModel();
        $dataFormatada = (new \DateTime($dataRefeicao))->format('d/m/Y');

        $mensagem = "Esse é o qrCode a ser utilizado no dia {$dataFormatada}, lembre-se esse qrCode é uso único e exclusivo na data respectiva e do titular.";

        $dadosMensagem = [
            'destinatario'  => $destinatario,
            'mensagem'      => $mensagem, 
            'status'        => 0, 
            'categoria'     => 1, 
        ];

        if ($enviaModel->insert($dadosMensagem)) {
            return true;
        }

        return false;
    
    }
}
