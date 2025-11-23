<?php 
namespace App\Controllers;

use App\Models\AnaliseDeSolicitacaoModel;
use App\Models\ControleRefeicoesModel; // para inserir refeições aprovadas

class AnaliseSolicitacaoController extends BaseController
{
    protected $solicitacaoModel;
    protected $controleModel;

    public function __construct()
    {
        $this->solicitacaoModel = new AnaliseDeSolicitacaoModel();
        $this->controleModel = new ControleRefeicoesModel();
    }

    public function index()
    {
        $data['solicitacoes'] = $this->solicitacaoModel->getSolicitacoesPendentes(); //solicitações pendentes
        $data['content'] = view('sys/analise-solicitacao', $data);
        return view('dashboard', $data); 
    }

    public function atualizar()
    {
        $id_creat = $this-> request->getPost ('id_creat') ; // id do criador da solicitação
        $id = $this->request->getPost('id'); // id da solicitação
        $status = $this->request->getPost('status');

        if (empty($id) || !in_array($status, ['0', '1', '2'], true)) {
            session()->setFlashdata('erro', 'Dados inválidos!');
            return redirect()->back();
        } //se o id está vazio = erro

        $this->solicitacaoModel->update($id, ['status' => $status]);// atualizar banco de dados

        if ((int)$status === 1) {
            $solicitacao = $this->solicitacaoModel->find($id);

            if ($solicitacao) { 
                // inserir no controle de refeições
                $dadosControle = [
                 'data_refeicao' => $solicitacao['data_refeicao'] ?? date('Y-m-d'),                
                 'status'=> 0,
                 'turma_id' => $solicitacao['turma_id'] ?? null,
                ];

                $this->controleModel->insert($dadosControle);
            }
        }

        session()->setFlashdata('sucesso', 'Solicitação atualizada com sucesso!');
        return redirect()->to(site_url('sys/analise'));

    }
}