<?php

namespace App\Controllers;

use App\Models\SolicitacaoRefeicoesModel;
use App\Models\TurmaModel;
use Exception;

class SolicitacaoRefeicoesController extends BaseController
{
    protected $baseRoute = 'sys/solicitacoes';

    public function index()
    {
        $solicitacoes = new SolicitacaoRefeicoesModel();
        $turmas = new TurmaModel();

        $data['solicitacoes'] = $solicitacoes->orderBy('id')->findAll();
        $data['turmas'] = $turmas->orderBy('nome')->findAll();

        $data['content'] = view('sys/solicitacoes', $data);
        return view('dashboard', $data);
    }

    /**
     * @route POST sys/solicitacoes/create
     */
    public function create()
    {
        $post = $this->request->getPost();

        $input['turma_id'] = (int) strip_tags($post['turma_id']);
        $input['data_refeicao'] = strip_tags($post['data_refeicao']);
        $input['crc'] = strip_tags($post['crc']);
        $input['status'] = 0; //por padrão, significa "pendente"
        $input['codigo'] = (int) strip_tags($post['codigo']);
        $input['justificativa'] = strip_tags($post['justificativa']);

        try {
            $solicitacao = new SolicitacaoRefeicoesModel();
            $sucesso = $solicitacao->insert($input);

            if (!$sucesso) {
                return $this->redirectToBaseRoute($solicitacao->errors());
            }

            session()->setFlashdata('sucesso', 'Solicitação cadastrada com sucesso!');
            return $this->redirectToBaseRoute();
        } catch (Exception $e) {
            return $this->redirectToBaseRoute(['Ocorreu um erro ao cadastrar a solicitação!']);
        }
    }

    /**
     * @route POST sys/solicitacoes/update
     */
    public function update()
    {
        $post = $this->request->getPost();

        $input['id'] = (int) strip_tags($post['id']);
        $input['turma_id'] = (int) strip_tags($post['turma_id']);
        $input['data_refeicao'] = strip_tags($post['data_refeicao']);
        $input['crc'] = strip_tags($post['crc']);
        $input['status'] = (int) strip_tags($post['status']);
        $input['codigo'] = (int) strip_tags($post['codigo']);
        $input['justificativa'] = strip_tags($post['justificativa']);

        try {
            $solicitacao = new SolicitacaoRefeicoesModel();
            $sucesso = $solicitacao->save($input);

            if (!$sucesso) {
                return $this->redirectToBaseRoute($solicitacao->errors());
            }

            session()->setFlashdata('sucesso', 'Solicitação atualizada com sucesso!');
            return $this->redirectToBaseRoute();
        } catch (\Exception $e) {
            return $this->redirectToBaseRoute(['Ocorreu um erro ao editar a solicitação!']);
        }
    }

    /**
     * @route POST sys/solicitacoes/delete
     */
    public function delete()
    {
        $post = $this->request->getPost();

        $id = (int) strip_tags($post['id']);

        try {
            $solicitacao = new SolicitacaoRefeicoesModel();
            $sucesso = $solicitacao->delete($id);

            if (!$sucesso) {
                return $this->redirectToBaseRoute($solicitacao->errors());
            }

            session()->setFlashdata('sucesso', 'Solicitação deletada com sucesso!');
            return $this->redirectToBaseRoute();
        } catch (Exception $e) {
            return $this->redirectToBaseRoute(['Ocorreu um erro ao deletar a solicitação!']);
        }
    }
}