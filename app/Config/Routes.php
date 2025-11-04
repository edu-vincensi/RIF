<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->get('/', 'Home::index'); 

$routes->get('/teste', 'Home::teste');

service('auth')->routes($routes);


// =================================================================================
// GRUPO PRINCIPAL: Rotas Protegidas do Sistema (/sys)
// =================================================================================
$routes->group('sys', [], static function ($routes) {

    //==============================================================
    // Rotas de Cursos - Acesso para 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('cursos', ['filter' => 'app_group:admin,developer'], static function ($routes) {
        $routes->get('', 'CursoController::index');
        $routes->post('create', 'CursoController::create');
        $routes->post('update', 'CursoController::update');
        $routes->post('delete', 'CursoController::delete');
        $routes->get('verificarTurmas/(:num)', 'CursoController::verificarTurmas/$1');
    });

    //==============================================================
    // Rotas de Turmas - Acesso para 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('turmas', ['filter' => 'app_group:admin,developer'], static function ($routes) {
        $routes->get('', 'TurmaController::index');
        $routes->post('create', 'TurmaController::create');
        $routes->post('update', 'TurmaController::update');
        $routes->post('delete', 'TurmaController::delete');
        $routes->post('import', 'TurmaController::import');
        $routes->post('importProcess', 'TurmaController::importProcess'); 
        $routes->get('verificarAlunos/(:num)', 'TurmaController::verificarAlunos/$1');
    });

    //==============================================================
    // Rotas de Controle de Refeições - Acesso para 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('controle-refeicoes', ['filter' => 'app_group:admin,developer'], static function ($routes) {
        $routes->get('', 'ControleRefeicoesController::index');
        $routes->post('salvar', 'ControleRefeicoesController::salvar');
        $routes->post('atualizar', 'ControleRefeicoesController::atualizar');
        $routes->post('deletar', 'ControleRefeicoesController::deletar');
        $routes->get('confirmacao', 'ControleRefeicoesController::tela_confirmacao');
        $routes->post('validar', 'ControleRefeicoesController::validar');
        $routes->post('confirmar', 'ControleRefeicoesController::confirmar');
    });

    //==============================================================
    // Rotas de Alunos - Acesso para 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('alunos', ['filter' => 'app_group:admin,developer'], static function ($routes) {
        $routes->get('', 'AlunoController::index');
        $routes->post('create', 'AlunoController::create');
        $routes->get('edit/(:any)', 'AlunoController::edit/$1'); 
        $routes->put('update', 'AlunoController::update');
        $routes->delete('delete', 'AlunoController::delete');
        $routes->post('import', 'AlunoController::import');
        $routes->post('importProcess', 'AlunoController::importProcess');
        
        //provisorio
        $routes->get('sendEmail', 'AlunoController::enviarEmail'); 
    });

    //==============================================================
    // Rotas de Usuários - Acesso para 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('usuarios', ['filter' => 'app_group:admin,developer'], static function ($routes) {
        $routes->get('', 'UsuarioController::index');
        $routes->post('criar', 'UsuarioController::store');
        $routes->put('atualizar/(:num)', 'UsuarioController::update/$1');
        $routes->delete('deletar/(:num)', 'UsuarioController::delete/$1');
    });

    //==============================================================
    // Rotas de Agendamento - Acesso para 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('agendamento', ['filter' => 'app_group:admin,developer'], static function ($routes) {
        $routes->get('', 'AgendamentoController::index');
        $routes->post('admin/create', 'AgendamentoController::create');
        $routes->get('admin/getAlunosByTurma', 'AgendamentoController::getAlunosByTurma');
        $routes->post('admin/update', 'AgendamentoController::update');
        $routes->post('admin/delete', 'AgendamentoController::delete');
    });

    //==============================================================
    // Rotas de Solicitação de Refeições - Acesso para 'aluno', 'solicitante', 'admin' E/OU 'developer'
    //==============================================================
    $routes->group('solicitacoes', ['filter' => 'app_group:aluno,solicitante,admin,developer'], static function ($routes) {
        $routes->get('', 'SolicitacaoRefeicoesController::index');
        $routes->post('create', 'SolicitacaoRefeicoesController::create');
        $routes->post('update', 'SolicitacaoRefeicoesController::update');
        $routes->post('delete', 'SolicitacaoRefeicoesController::delete');
    });

    //==============================================================
    // Rotas de Análise de solicitação - Acesso Restrito
    //==============================================================
    $routes->get('analise', 'AnaliseSolicitacaoController::index', ['filter' => 'app_group:admin,developer']);

    //==============================================================
    // Rotas de Restaurante - Acesso para 'restaurante'
    //==============================================================
    $routes->group('restaurante', ['filter' => 'app_group:restaurante'], static function ($routes) {
        $routes->post('registrar-servida', 'RefeicaoController::registrarServida');
    });

    //==============================================================
    // Rotas de Relatórios - Acesso para 'admin' E/OU 'developer' E/OU 'restaurante'
    //==============================================================
    $routes->group('relatorios', ['filter' => 'app_group:admin,developer,restaurante'], static function ($routes) {
        $routes->get('', 'RelatorioController::index');
        $routes->get('previstos', 'RelatorioController::refeicoesPrevistas');
        $routes->get('servidos', 'RelatorioController::refeicoesServidas');
        $routes->get('nao-servidos', 'RelatorioController::refeicoesNaoServidas');
        $routes->get('confirmados', 'RelatorioController::confirmados');
    });

    //==============================================================
    // Rotas do Admin para o gerenciamento de usuários - Acesso para 'admin'
    //==============================================================
    $routes->group('admin', ['filter' => 'app_group:admin'], static function ($routes) {
        $routes->get('/', 'AdminController::index'); 
        $routes->post('alterar-grupo', 'AdminController::alterarGrupoUsuario'); 
        $routes->post('atualizar-usuario', 'AdminController::atualizarUsuario'); 
        $routes->post('resetar-senha', 'AdminController::resetarSenha'); 
        $routes->post('desativar-usuario', 'AdminController::desativarUsuario');
        $routes->post('registrar-usuario', 'AdminController::registrarUsuario');
        $routes->get('usuarios-inativos', 'AdminController::usuariosInativos');
        $routes->post('reativar-usuario', 'AdminController::reativarUsuario');
        $routes->post('excluir-permanentemente', 'AdminController::excluirPermanentemente');
    });
});

// Rota do Webhook (Fora do grupo protegido)
$routes->post('webhook/response', 'WebhookController::response');