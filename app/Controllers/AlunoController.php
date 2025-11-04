<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AlunoModel;
use App\Models\AlunoEmailModel;
use App\Models\AlunoTelefoneModel;
use App\Models\TurmaModel;
use App\Models\EnviarMensagensModel;
use App\Libraries\EvolutionAPI;
use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;


class AlunoController extends BaseController
{
    protected $baseRoute = '/sys/alunos';

    public function index()
    {
        $alunoModel = new AlunoModel();
        $alunoEmailModel = new AlunoEmailModel();
        $alunoTelefoneModel = new AlunoTelefoneModel();
        $turmaModel = new TurmaModel();
        
        $alunos = $alunoModel
            ->select('alunos.*, t.nome as turma_nome, c.nome as curso_nome')
            ->join('turmas as t', 't.id = alunos.turma_id', 'left')
            ->join('cursos as c', 'c.id = t.curso_id', 'left')
            ->orderBy('alunos.nome')
            ->findAll();

        foreach ($alunos as &$aluno) {
            $emails = $alunoEmailModel->where('aluno_id', $aluno['matricula'])->findAll();
            $aluno['emails'] = array_column($emails, 'email');
            
            $telefones = $alunoTelefoneModel->where('aluno_id', $aluno['matricula'])->findAll();
            $aluno['telefones'] = array_column($telefones, 'telefone');
        }

        $data['alunos'] = $alunos;
        $data['turmas'] = $turmaModel
            ->select('turmas.*, c.nome as curso_nome')
            ->join('cursos as c', 'c.id = turmas.curso_id', 'left')
            ->orderBy('turmas.nome')
            ->findAll();

        $data['content'] = view('sys/aluno', $data);
        return view('dashboard', $data);
    }
    
    public function create()
    {
        $alunoModel = new AlunoModel();
        $alunoEmailModel = new AlunoEmailModel();
        $alunoTelefoneModel = new AlunoTelefoneModel();
        $post = $this->request->getPost();
        
        $alunoModel->db->transBegin();
        
        try {
            $alunoData = [
                'matricula' => strip_tags($post['matricula']),
                'nome'      => strip_tags($post['nome']),
                'turma_id'  => (int)strip_tags($post['turma_id']),
                'status'    => strip_tags($post['status']),
            ];

            if (!$alunoModel->insert($alunoData)) {
                $alunoModel->db->transRollback();
                return $this->redirectToBaseRoute($alunoModel->errors());
            }

            // Coleta e insere os e-mails
            $emails = $post['email'] ?? [];
            foreach ($emails as $email) {
                $emailData = ['aluno_id' => $alunoData['matricula'], 'email' => trim($email), 'status' => 'ativo'];
                if (!$alunoEmailModel->insert($emailData)) {
                    $alunoModel->db->transRollback();
                    return $this->redirectToBaseRoute($alunoEmailModel->errors() ?? ['Erro ao inserir e-mail.']);
                }
            }

            // Coleta e insere os telefones
            $telefones = $post['telefone'] ?? [];
            $primeiroTelefone = null;

            foreach ($telefones as $telefone) {
                $telefoneData = ['aluno_id' => $alunoData['matricula'], 'telefone' => trim(str_replace(['(', ')', ' ', '-', '+'], '', $telefone)), 'status' => 'inativo'];

                if ($primeiroTelefone === null && !empty($telefoneData['telefone'])) {
                    $primeiroTelefone = $telefoneData['telefone'];
                }

                if (!$alunoTelefoneModel->insert($telefoneData)) {
                    $alunoModel->db->transRollback();
                    return $this->redirectToBaseRoute($alunoTelefoneModel->errors() ?? ['Erro ao inserir telefone.']);
                }
            }

            if ($primeiroTelefone !== null) {
                $this->criaMensagemValidacao($alunoData, $primeiroTelefone);
            }

            $alunoModel->db->transCommit();
            session()->setFlashdata('sucesso', 'Aluno, e-mails e telefones cadastrados com sucesso!');
            return $this->redirectToBaseRoute();
        } catch (Exception $e) {
            $alunoModel->db->transRollback();
            return $this->redirectToBaseRoute(['Ocorreu um erro inesperado. Tente novamente.']);
        }
    }

    public function update()
    {
        $alunoModel = new AlunoModel();
        $alunoEmailModel = new AlunoEmailModel();
        $alunoTelefoneModel = new AlunoTelefoneModel();
        $post = $this->request->getPost();
        
        $matricula = strip_tags($post['matricula']);

        $alunoModel->db->transBegin();
        
        try {
            $alunoOriginal = $alunoModel->find($matricula);
            $alunoData = [
                'nome'      => strip_tags($post['nome']),
                'turma_id'  => (int)strip_tags($post['turma_id']),
                'status'    => strip_tags($post['status']),
            ];

            if ($alunoOriginal && $alunoOriginal['nome'] === $alunoData['nome']) {
                unset($alunoData['nome']);
            }   

            if (!$alunoModel->update($matricula, $alunoData)) {
                $alunoModel->db->transRollback();
                return $this->redirectToBaseRoute($alunoModel->errors());
            }
            

            if (!empty($post['email'])) {
                $emailsExistentes = $alunoEmailModel->where('aluno_id', $matricula)->findAll();
                $idsDeEmailsEnviados = $post['email_id'] ?? [];
                $idsDeEmailsEnviados = array_filter($idsDeEmailsEnviados);

                foreach ($emailsExistentes as $emailExistente) {
                    if (!in_array($emailExistente['id'], $idsDeEmailsEnviados)) {
                        $alunoEmailModel->delete($emailExistente['id']);
                    }
                }
                
                foreach ($post['email'] as $i => $email) {
                    $emailId = $post['email_id'][$i] ?? null;

                    $emailData = [
                        'aluno_id' => $matricula,
                        'email'    => trim($email),
                        'status'   => 'ativo',
                    ];

                    if ($emailId) {
                        $emailData['id'] = $emailId;
                    }

                    $alunoEmailModel->save($emailData); 
                }
            }

            if (!empty($post['telefone'])) {
                $telefonesExistentes = $alunoTelefoneModel->where('aluno_id', $matricula)->findAll();
                $idsDeTelefonesEnviados = $post['telefone_id'] ?? [];
                $idsDeTelefonesEnviados = array_filter($idsDeTelefonesEnviados); 

                foreach ($telefonesExistentes as $telefoneExistente) {
                    if (!in_array($telefoneExistente['id'], $idsDeTelefonesEnviados)) {
                        $alunoTelefoneModel->delete($telefoneExistente['id']);
                    }
                }
                
                foreach ($post['telefone'] as $i => $telefone) {
                    $telefoneId = $post['telefone_id'][$i] ?? null;

                    $telefoneData = [
                        'aluno_id' => $matricula,
                        'telefone' => trim(str_replace(['(', ')', '-', ' '], '', $telefone)), //deixa limpo
                        'status'   => 'ativo',
                    ];

                    if ($telefoneId) {
                        $telefoneData['id'] = $telefoneId;
                    }

                    $alunoTelefoneModel->save($telefoneData);
                }
            }
            //$this->updateAssociatedData($alunoEmailModel, $post['email'] ?? [], $matricula, 'email', $alunoData['status']);
            //$this->updateAssociatedData($alunoTelefoneModel, $post['telefone'] ?? [], $matricula, 'telefone', $alunoData['status']);

            $alunoModel->db->transCommit();
            session()->setFlashdata('sucesso', 'Aluno, e-mails e telefones atualizados com sucesso!');
            return $this->redirectToBaseRoute();
        } catch (Exception $e) {
            $alunoModel->db->transRollback();
            return $this->redirectToBaseRoute(['Ocorreu um erro ao editar o aluno, e-mails e/ou telefones!']);
        }
    }
    
    public function delete()
    {
        $alunoModel = new AlunoModel();
        $alunoEmailModel = new AlunoEmailModel();
        $alunoTelefoneModel = new AlunoTelefoneModel();
        $post = $this->request->getPost();
        
        $matricula = strip_tags($post['matricula']);

        $alunoModel->db->transBegin();
        
        try {
            $alunoTelefoneModel->where('aluno_id', $matricula)->delete();
            $alunoEmailModel->where('aluno_id', $matricula)->delete();
            $alunoModel->where('matricula', $matricula)->delete();
            
            if ($alunoModel->db->transStatus() === false) {
                $alunoModel->db->transRollback();
                return $this->redirectToBaseRoute(['Erro ao deletar o aluno.']);
            }

            $alunoModel->db->transCommit();
            session()->setFlashdata('sucesso', 'Aluno e dados relacionados deletados com sucesso!');
            return $this->redirectToBaseRoute();
        } catch (Exception $e) {
            $alunoModel->db->transRollback();
            return $this->redirectToBaseRoute(['Ocorreu um erro ao deletar o aluno!']);
        }
    }
    
    public function edit($matricula)
    {
        $alunoModel = new AlunoModel();
        $alunoEmailModel = new AlunoEmailModel();
        $alunoTelefoneModel = new AlunoTelefoneModel();
        
        $aluno = $alunoModel->find($matricula);

        if ($aluno === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Aluno não encontrado.']);
        }

        $aluno['status'] = ($aluno['status'] == 1) ? 'ativo' : 'inativo';
        
        $emails = $alunoEmailModel->where('aluno_id', $matricula)->findAll();
        $aluno['emails'] = array_column($emails, 'email');

        $telefones = $alunoTelefoneModel->where('aluno_id', $matricula)->findAll();
        $aluno['telefones'] = array_column($telefones, 'telefone');

        return $this->response->setJSON($aluno);
    }

    /**
     * @route POST /alunos/import
     */
    public function import()
    {
        $post = $this->request->getFiles();
        $planilha = $post['planilha-alunos']; 

        //Tratamento
        if (!$planilha->isValid()){
            session()->setFlashdata('erros', ['O arquivo é inválido. Por favor, tente novamente.']);
            return redirect()->to(base_url('sys/alunos'));
        } 
        else if(!in_array($planilha->getClientExtension(), ['xls', 'xlsx'])){
            session()->setFlashdata('erros', ['Formato de arquivo não suportado. Use .XLS ou .XLSX.']);
            return redirect()->to(base_url('sys/alunos'));
        }

        $reader = $planilha->getClientExtension() === 'xlsx' ? new Xlsx() : new Xls();
        
        try 
        {
            $spreadsheet = $reader->load($planilha->getRealPath());
        } 
        catch (\Exception $e) 
        {
            session()->setFlashdata('erros', ['Ocorreu um erro ao carregar a planilha.']);
            return redirect()->to(base_url('sys/alunos'));
        }

        $sheet = $spreadsheet->getActiveSheet(); //Pega a 1° aba
        $dataRows = [];
        $primeiraLinha = true;
        $cabecalho = $sheet->toArray(null, false, true, false)[0];

        $mapeiaCabecalho = [
            'matricula'      => array_search('Matrícula', $cabecalho ),
            'nome'           => array_search('Nome', $cabecalho ),
            'descricao_curso'=> array_search('Descrição do Curso', $cabecalho),
            'status'         => array_search('Situação no Curso', $cabecalho ),
            'telefone'       => array_search('Telefone', $cabecalho ),
            'cod_turma'      => array_search('Turma', $cabecalho ),
        ];

        if (in_array(false, $mapeiaCabecalho, true)) {
            session()->setFlashdata('erros', ['A planilha não contém todas as colunas necessárias (Matrícula, Nome, Descrição do Curso, Situação no Curso, Telefone, Turma).']);
            return redirect()->to(base_url('sys/alunos'));
        }

        foreach ($sheet->getRowIterator() as $row) 
        {
            //Pra não contar com a primeira linha que é cabeçalho
            if ($primeiraLinha) 
            {
                $primeiraLinha = false;
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];

            foreach ($cellIterator as $cell) 
            {
                $rowData[] = $cell->getValue(); //pega os dados da linha
            }

            //Só vai salvar os alunos matriculados
            $status = $rowData[$mapeiaCabecalho['status']];
            if ($status !== 'Matriculado') {
                continue; 
            }

            //Só vai pegar o ensino médio 
            $descricao_curso = $rowData[$mapeiaCabecalho['descricao_curso']];
            if (stripos($descricao_curso, 'Integrado ao Ensino Médio') === false) {
                continue; 
            }

            $matricula = $rowData[$mapeiaCabecalho['matricula']];
            $matricula_limpa = preg_replace('/[^0-9]/', '', $matricula);

            $nome = $rowData[$mapeiaCabecalho['nome']];

            $status_padrao = ($status === 'Matriculado')  ? 'ativo' : 'inativo';

            $telefone =[];

            if (isset($rowData[$mapeiaCabecalho['telefone']])) {
                $telefones = explode(', ', $rowData[$mapeiaCabecalho['telefone']]); //separa eles
                foreach ($telefones as $tel) {
                    $tel = trim($tel);
                    if (!empty($tel)) {
                        $telefone[] = $tel; 
                    }
                }

                $telefone = array_unique($telefone);
            }

            $cod_turma_completo = $rowData[$mapeiaCabecalho['cod_turma']] ?? '';
            $cod_turma= substr($cod_turma_completo, 0, 15);

            $dataRows[] = [
                'matricula' => $matricula_limpa,
                'nome'      => $nome,
                'status'    => $status_padrao,
                'telefone' => $telefone,
                'cod_turma'    => $cod_turma
            ];

        }

        $data['alunos'] = $dataRows;
        $data['import_completo'] = true;
        $mainContent = view('sys/aluno-importar-form', $data);
        return view('dashboard', ['content' => $mainContent]);
        
    }

    /**
    * @route POST /alunos/importProcess
    */
    public function importProcess()
    {
        $selecionados = $this->request->getPost('selecionados');

        if (empty($selecionados)) {
            session()->setFlashdata('erros', ['Nenhum aluno foi selecionado para importação.']);
            return redirect()->to('sys/alunos');
        }

        $aluno = new AlunoModel();
        $telefoneModel = new AlunoTelefoneModel();
        $turmaModel = new TurmaModel();
        
        $testar_enviar_telefone = 0; // pra testar sem lotar de mensagem por enquanto

        $insertedCount = 0;
        $errors = [];

        foreach ($selecionados as $alunoJson) {
            $alunoData = json_decode($alunoJson, true, 512, JSON_BIGINT_AS_STRING);

            $codTurma = $alunoData['cod_turma']; 
            $turma = $turmaModel->where('codTurma', $codTurma)->first();

            if ($turma) {
                $turmaID = $turma['id'];
                $data = [
                    'matricula' => $alunoData['matricula'],
                    'nome'      => $alunoData['nome'],
                    'status'    => $alunoData['status'],
                    'turma_id'  => $turmaID,
                ];

                $sucesso = $aluno->insert($data);
                
                if (!$sucesso) {
                    $errosDoModelo = $aluno->errors();
                    $errors[] = "Ocorreu um erro ao importar o aluno de matrícula {$alunoData['matricula']}: " . implode(', ', $errosDoModelo);
                } else {
                    $insertedCount++;
                    $alunoId = $aluno->getInsertID();

                    $destino = null; // destino vai ser o primeiro telefone a tentar ser validado

                    // Faz a inserção dos telefones
                    if (!empty($alunoData['telefone'])) {
                        foreach ($alunoData['telefone'] as $telefone) {
                            if (!empty(trim($telefone)) && trim($telefone) !== '-') {

                                $telefonePadrao = str_replace(['(', ')', ' ', '-'], '', $telefone);
                                
                                // pega o telefone a ser validado
                                if ($destino === null) {
                                    $destino = $telefonePadrao;
                                }

                                $telefoneModel->insert([
                                    'aluno_id' => $alunoId,
                                    'telefone' => $telefonePadrao,
                                    'status' => 'inativo' // todos por padrão vão começar inativos
                                ]);
                            }
                        }
                    }

                    /**
                    * Carrega os parâmetros pra função (Envio de Whatsapp)
                    */
                    if ($destino !== null) { 
                        if ($testar_enviar_telefone < 5) { 
                            $this->criaMensagemValidacao($alunoData, $destino);
                            $testar_enviar_telefone++;
                        }
                    }
                    
                }

            } else {
                $errors[] = "A turma com código '{$codTurma}' do aluno {$alunoData['nome']} ({$alunoData['matricula']}) não está cadastrada.";
            }
        } // Fim do foreach

        $redirect = redirect()->to('sys/alunos');

        if ($insertedCount > 0) {
            $redirect->with('sucesso', "{$insertedCount} aluno(s) importado(s) com sucesso!");
        }

        if (!empty($errors)) {
            $redirect->with('erros', $errors);
        }
        
        return $redirect;
    }

    protected function redirectToBaseRoute($errors = null)
    {
        if ($errors) {
            session()->setFlashdata('erros', $errors);
            return redirect()->to($this->baseRoute)->withInput();
        }
        
        return redirect()->to($this->baseRoute);
    }

    protected function criaMensagemValidacao(array $alunoData, string $destino)
    {
        $destinoReal = $destino;
        $destino = "69992809488"; //Próvisório 

        $nome = $alunoData['nome'];

        $mensagem = "Prezado(a) {$nome},\n\n";
        $mensagem .= "Você foi cadastrado no Sistema de Refeições do IFRO e precisamos validar seu telefone para que possa receber os avisos.\nDigite uma das opções abaixo.\n\n";
        $mensagem .= "*1* - Quero receber os avisos por esse telefone.\n";
        $mensagem .= "*2* - Não quero receber os avisos por este telefone e sim informar outro.\n";

        $enviaMensagensModel = new EnviarMensagensModel();

        $dataMensagem = [
            'destinatario' => $destino,
            'mensagem'     => $mensagem,
            'status'       => 0, 
            'categoria'    => 2, 
        ];

        if ($enviaMensagensModel->insert($dataMensagem)) {
            return true;
        } else {
            log_message('error', 'Erro ao inserir mensagem de validação para aluno ID ' . $alunoData['matricula'] . ': ' . implode(', ', $enviaMensagensModel->errors()));
            return false;
        }

    }

    // provisoriamente aqui 
    // public function enviarEmail(){
    //     $email = \Config\Services::email();

    //     $destino = 'isatereza.07@gmail.com';
    //     $nome = 'Isabella';

    //     $data = date('d/m/Y', strtotime('+2 days')); //se for 48h de antecedencia

    //     $link = 'link.provisorio.com.br';

    //     $mensagem = "Prezado(a) {$nome},<br><br>";
    //     $mensagem .= "Clique no link abaixo para confirmar o almoço do dia **{$data}**, caso não irá utilizar o benefício no mesmo link marque a opção que não irá fazer uso nesse dia.<br><br>";
    //     $mensagem .= "<a href='{$link}'>Clique aqui</a><br><br>";
    //     $mensagem .= "Sua resposta é de extrema importância para o planejamento das refeições do campus, ao não responder o link, você estará sujeito a perder o benefício. Qualquer problema entrar em contato com o DEPAE.<br><br>";
    //     $mensagem .= "Atenciosamente, DEPAE. <br>";


    //     $email->setTo($destino);
    //     $email->setSubject("Confirme a refeição do dia {$data}");
    //     $email->setMessage($mensagem);

    //     if ($email->send()) {
    //         echo "Operação concluída e e-mail de confirmação enviado.";
    //     } else{
    //         echo "Operação concluída, mas falha ao enviar o e-mail.";
    //         echo $email->printDebugger();
    //     }
    // }

}