<?php

namespace App\Controllers;

use App\Models\TurmaModel;
use App\Models\AlunoModel;
use App\Models\AlunoTelefoneModel;
use App\Models\EnviarMensagensModel;
use App\Models\ControleRefeicoesModel;

class AgendamentoController extends BaseController
{
    public function index()
    {
        $controleModel = new ControleRefeicoesModel();
        $data = $controleModel->getViewData(new AlunoModel(), new TurmaModel());
        
        $data['content'] = view('sys/agendamento', $data);
        return view('dashboard', $data);
    }

    
    public function create()
    {
        $this->response->setContentType('application/json');

        try {
            //$post = $this->request->getPost(); --> REMOVI ESTE TRECHO POIS NÃO ESTAVA SENDO USADO

            // $matriculasString = is_array($post['matriculas']) && isset($post['matriculas'][0]) ? $post['matriculas'][0] : '';
            // $datasString      = is_array($post['datas']) && isset($post['datas'][0]) ? $post['datas'][0] : '';
            // $status           = strip_tags($post['status']);
            // $motivo           = strip_tags($post['motivo']);
            $matriculas = $this->request->getPost('matriculas');
            $datasString      = $this->request->getPost('datas') ?? '';
            $status           = strip_tags($this->request->getPost('status'));
            $motivo           = strip_tags($this->request->getPost('motivo'));

            if (empty($matriculas) || empty($datasString)) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Selecione pelo menos um aluno e uma data.'
                ]);
            }

            //$matriculas = explode(',', $matriculasString); --> Antes, armazenava como array de strings separadas por vírgula, agora, já está vindo como array do formulário
            $datas      = explode(',', $datasString);

            $controleModel = new ControleRefeicoesModel();

        foreach ($matriculas as $matricula) {
            foreach ($datas as $data) {
                $existe = $controleModel
                    ->where('aluno_id', $matricula)
                    ->where('data_refeicao', $data)
                    ->first();

                if ($existe) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "O aluno já possui agendamento no dia {$data}."
                    ]);
                }
            }
        }

            $inserido = $controleModel->createAgendamentos($matriculas, $datas, $status, $motivo);

            if ($inserido) {
                $this->createSendMessages($matriculas, $datas);
                session()->setFlashdata('sucesso', 'Agendamento(s) criado(s) com sucesso!');
            }

            return $this->response->setJSON(['success' => (bool) $inserido]);

        } catch (\Exception $e) {
            log_message('error', '[AgendamentoController] Erro em create: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Ocorreu um erro inesperado no servidor. Verifique os logs para mais detalhes.'
            ]);
        }
    }


    public function update()
    {
        $post = $this->request->getPost();

        $originalAlunoIds = explode(',', $post['original_aluno_ids']);
        $originalDatas    = explode(',', $post['original_datas']);
        $originalMotivo   = strip_tags($post['original_motivo']);

        $newMatriculasString = is_array($post['matriculas']) ? $post['matriculas'][0] : '';
        $newDatasString      = is_array($post['datas']) ? $post['datas'][0] : '';
        $newMatriculas       = !empty($newMatriculasString) ? explode(',', $newMatriculasString) : [];
        $newDatas            = !empty($newDatasString) ? explode(',', $newDatasString) : [];
        $newStatus           = strip_tags($post['status']);
        $newMotivo           = strip_tags($post['motivo']);

        if (empty($newMatriculas) || empty($newDatas)) {
            session()->setFlashdata('erros', ['Para editar, é preciso selecionar pelo menos um aluno e uma data.']);
            return redirect()->back();
        }

        $controleModel = new ControleRefeicoesModel();

        foreach ($newMatriculas as $matricula) {
             foreach ($newDatas as $data) {
                 $existe = $controleModel
                ->where('aluno_id', $matricula)
                ->where('data_refeicao', $data)
                ->where('motivo !=', $originalMotivo)
                ->first();

        if ($existe) {
            session()->setFlashdata('erros', ["O aluno já possui agendamento no dia."]);
            return redirect()->back();
        }
    }
}

        $sucesso = $controleModel->updateAgendamentos(
            $originalAlunoIds,
            $originalDatas,
            $originalMotivo,
            $newMatriculas,
            $newDatas,
            $newStatus,
            $newMotivo
        );

        if ($sucesso) {
            $alunosAdicionados = array_diff($newMatriculas, $originalAlunoIds);
            $alunosRemovidos   = array_diff($originalAlunoIds, $newMatriculas);

            if (!empty($alunosAdicionados)) {
                $this->createSendMessages($alunosAdicionados, $newDatas);
            }

            $enviarMensagensModel = new EnviarMensagensModel();
            $enviarMensagensModel->deleteByMatriculaDatas($alunosRemovidos, $originalDatas);

            session()->setFlashdata('sucesso', 'Agendamento atualizado com sucesso!');
        } else {
            session()->setFlashdata('erros', ['Ocorreu um erro ao salvar as alterações.']);
        }

        return redirect()->to(site_url('sys/agendamento/'));
    }


    public function delete()
    {
        $post = $this->request->getPost();
        $deleteInfo = json_decode($post['delete_info'], true);

        if (empty($deleteInfo)) {
            return redirect()->back()->with('erros', ['Dados para exclusão não fornecidos.']);
        }

        $alunoIds = $deleteInfo['aluno_ids'] ?? [];
        $datas    = $deleteInfo['datas'] ?? [];
        $motivo   = $deleteInfo['motivo'] ?? '';

        $controleModel = new ControleRefeicoesModel();
        $sucesso = $controleModel->deleteAgendamentos($alunoIds, $datas, $motivo);

        if ($sucesso) {
            $enviarMensagensModel = new EnviarMensagensModel();
            $enviarMensagensModel->deleteByMatriculaDatas($alunoIds, $datas);

            session()->setFlashdata('sucesso', 'Agendamento deletado com sucesso!');
        } else {
            session()->setFlashdata('erros', ['Ocorreu um erro interno ao deletar o agendamento.']);
        }

        return redirect()->back();
    }

    
    public function getAlunosByTurma()
    {
        $alunoModel = new AlunoModel();
        // Pega o parâmetro 'turmas' do GET (ex: "1,2,5")
        $turmas = $this->request->getGet('turmas');

        if (!$turmas) {
            return $this->response->setJSON([]); // nenhuma turma selecionada
        }

        $turmasArray = array_filter(array_map('intval', explode(',', $turmas)));
        if (empty($turmasArray)) return $this->response->setJSON([]); // transforma em array de inteiros
        $alunos = $alunoModel->getAtivosByTurmas($turmasArray); // novo método no Model

        return $this->response->setJSON($alunos);
    }


    public function createSendMessages(array $matriculas, array $datasSelecionadas)
    {
        $alunoModel = new AlunoModel(); 
        $alunoTelefoneModel = new AlunoTelefoneModel();
        $enviarMensagensModel = new EnviarMensagensModel();
        
        foreach ($matriculas as $matricula) {
            try {
                $aluno = $alunoModel->find($matricula);
                $telefoneAluno = $alunoTelefoneModel->getTelefoneAtivoByAlunoId($matricula);
            
                if ($aluno && $telefoneAluno) {
                    foreach ($datasSelecionadas as $dataRefeicao) {
                        
                        $nomeAluno = $aluno['nome'];
                        //$destinatario = $telefoneAluno['telefone'];
                        $destinatario = '69992809488'; 
                        $data = (new \DateTime($dataRefeicao))->format('d/m/Y');

                        $mensagem = "Prezado(a) {$nomeAluno}\n";
                        $mensagem .= "Confirme sua refeição para o dia {$data}\n";
                        $mensagem .= "*Digite 1* para sim, irei utilizar o beneficio no dia informado\n";
                        $mensagem .= "*Digite 2* para não, não irei utilizar o beneficio no dia informado";

                        $enviarMensagensModel->insert([
                            'destinatario' => $destinatario,
                            'mensagem'     => $mensagem,
                            'status'       => 0, // Pendente
                            'categoria'    => 0,
                        ]);
                    }
                }

            } catch (\Exception $e) {
                log_message('error', "[AgendamentoController] Falha ao criar mensagem para matrícula {$matricula}: " . $e->getMessage());
            }
        }
    }
}
