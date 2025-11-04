<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter para verificar se o usuário pertence a um dos grupos necessários.
 */
class GroupFilter implements FilterInterface
{
    /**
     * Verifica a permissão do usuário antes da execução do Controller.
     *
     * @param array|null $arguments A lista de grupos permitidos.
     *
     * @return ResponseInterface|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Se não estiver logado, redireciona para a tela de login.
        if (! auth()->loggedIn()) {
             return redirect()->to(config('Auth')->loginRoute);
        }

        $user = auth()->user();

        // Garante que o usuário logado foi carregado corretamente.
        if ($user === null) {
            return redirect()->to(config('Auth')->loginRoute)->with('error', 'Sessão inválida. Faça login novamente.');
        }

        // Obtém os grupos permitidos da rota.
        $allowedGroups = is_array($arguments) ? $arguments : [$arguments];
        
        // Verifica se o usuário pertence a algum dos grupos permitidos.
        foreach ($allowedGroups as $group) {
            if ($user->inGroup($group)) {
                return; // Acesso permitido
            }
        }

        // Redireciona se o usuário não tiver permissão.
        return redirect()->to('/sys')->with('erro', 'Acesso negado. Você não tem permissão para acessar esta área.');
    }

    /**
     * Permite inspecionar ou modificar o objeto de resposta após a execução do Controller.
     *
     * @param array|null $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}