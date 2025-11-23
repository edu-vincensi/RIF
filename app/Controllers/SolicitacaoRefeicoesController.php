<?php

namespace App\Controllers;

use App\Models\SolicitacaoRefeicoesModel;
use App\Models\TurmaModel;
use App\Models\AlunoModel;
use Exception;

class SolicitacaoRefeicoesController extends BaseController
{
    protected $baseRoute = 'sys/solicitacoes';

    public function index()
    {
        $solicitacoes = new SolicitacaoRefeicoesModel();
        $turmasModel  = new TurmaModel();

        $data['solicitacoes'] = $solicitacoes
            ->select("
                solicitacao_refeicoes.*, 
                alunos.nome AS aluno_nome,
                turmas.nome AS turma_nome
            ")
            ->join('alunos', 'alunos.matricula = solicitacao_refeicoes.aluno_id', 'left')
            ->join('turmas', 'turmas.id = alunos.turma_id', 'left')
            ->orderBy('solicitacao_refeicoes.id', 'ASC')
            ->findAll();

        // Buscar turmas com o nome do curso (mesma estrutura usada no agendamento)
        $data['turmas'] = $turmasModel
            ->select('turmas.id, turmas.nome as nome_turma, cursos.nome as nome_curso')
            ->join('cursos', 'cursos.id = turmas.curso_id', 'left')
            ->orderBy('turmas.nome')
            ->findAll();

        $data['content'] = view('sys/solicitacoes', $data);
        return view('dashboard', $data);
    }

    public function getAlunosByTurma()
    {
        $alunoModel = new AlunoModel();

        $turmaId = $this->request->getGet('turma_id');

        if (!$turmaId) {
            return $this->response->setJSON([]);
        }

        $alunos = $alunoModel
            ->where('turma_id', $turmaId)
            ->orderBy('nome')
            ->findAll();

        return $this->response->setJSON($alunos);
    }

    /**
     * @route POST sys/solicitacoes/create
     */
    public function create()
    {
        $this->response->setContentType('application/json');

        try {
            $alunos = $this->request->getPost('matriculas') ?? [];

            $datasString = $this->request->getPost('datas') ?? '';
            $datas = $datasString ? explode(',', $datasString) : [];

            if (empty($alunos) || empty($datas)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Selecione pelo menos um aluno e uma data.'
                ]);
            }

            $crc           = strip_tags($this->request->getPost('crc'));
            $codigo        = (int) strip_tags($this->request->getPost('codigo'));
            $motivo        = (int) $this->request->getPost('motivo'); 
            $idCreate      = auth()->id();
            $agora         = date('Y-m-d H:i:s');

            $solicitacaoModel = new SolicitacaoRefeicoesModel();

            $insercoes = [];

            foreach ($alunos as $alunoId) {

                $alunoId = (int) $alunoId;

                foreach ($datas as $data) {

                    $data = strip_tags($data);

                    // Verificar duplicidade
                    $jaExiste = $solicitacaoModel
                        ->where('aluno_id', $alunoId)
                        ->where('data_refeicao', $data)
                        ->first();

                    if ($jaExiste) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => "O aluno $alunoId já possui solicitação em $data."
                        ]);
                    }

                    $insercoes[] = [
                        'aluno_id'     => $alunoId,
                        'data_refeicao'=> $data,
                        'crc'          => $crc,
                        'status'       => 0,
                        'codigo'       => $codigo,
                        'motivo'        => $motivo,
                        'id_creat'     => $idCreate,
                        'data_solicitada' => $agora,
                    ];
                }
            }

            $solicitacaoModel->insertBatch($insercoes);

            session()->setFlashdata('sucesso', 'Solicitação(s) cadastrada(s) com sucesso!');
            return $this->response->setJSON(['success' => true]);
            

        } catch (\Exception $e) {

            log_message('error', '[SolicitacaoController] Erro em create: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro inesperado no servidor. Verifique os logs.'
            ]);
        }
    }

    /**
     * @route POST sys/solicitacoes/update
     */
    public function update()
    {
        $post = $this->request->getPost();

        // Valores originais vindos do modal (hidden inputs)
        $originalAlunoId     = (int) strip_tags($post['original_aluno_id']);
        $originalData        = strip_tags($post['original_data_refeicao']);
        $originalMotivo      = strip_tags($post['original_motivo']);

        // Novos valores vindos do formulário
        $newAlunoId          = (int) strip_tags($post['aluno_id']);
        $newData             = strip_tags($post['data_refeicao']);
        $newMotivo           = strip_tags($post['motivo']);
        $newCRC              = strip_tags($post['crc']);
        $newCodigo           = (int) strip_tags($post['codigo']);

        $idSolicitacao       = (int) strip_tags($post['id']);

        // ---------------------------------------------------------
        // 1. VERIFICAR SE NOVO VALOR JÁ EXISTE EM OUTRA SOLICITAÇÃO
        // ---------------------------------------------------------

        $solicitacaoModel = new SolicitacaoRefeicoesModel();

        $existe = $solicitacaoModel
            ->where('aluno_id', $newAlunoId)
            ->where('data_refeicao', $newData)
            ->where('id !=', $idSolicitacao)
            ->first();

        if ($existe) {
            session()->setFlashdata('erros', [
                "Este aluno já possui uma solicitação cadastrada para esta data."
            ]);
            return redirect()->back();
        }

        // ---------------------------------------------------------
        // 2. MONTAR ARRAY PARA ATUALIZAÇÃO
        // ---------------------------------------------------------

        $dadosUpdate = [
            'id'             => $idSolicitacao,
            'aluno_id'       => $newAlunoId,
            'data_refeicao'  => $newData,
            'motivo'         => $newMotivo,
            'crc'            => $newCRC,
            'codigo'         => $newCodigo,
            'id_creat'       => auth()->id(),
            'solicitada'     => date('Y-m-d H:i:s'),
        ];

        // ---------------------------------------------------------
        // 3. SALVAR ALTERAÇÕES
        // ---------------------------------------------------------

        try {
            $sucesso = $solicitacaoModel->save($dadosUpdate);

            if (!$sucesso) {
                return redirect()->back()
                    ->with('erros', $solicitacaoModel->errors());
            }

            session()->setFlashdata('sucesso', 'Solicitação atualizada com sucesso!');
            return redirect()->to(site_url('sys/solicitacoes/'));

        } catch (\Exception $e) {
            session()->setFlashdata('erros', [
                'Erro ao atualizar a solicitação: ' . $e->getMessage()
            ]);
            return redirect()->back();
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