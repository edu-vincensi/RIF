<?= $this->include('components/turmas/modal_cadastrar_turma', ['cursos' => $cursos]) ?>
<?= $this->include('components/turmas/modal_editar_turma', ['cursos' => $cursos]) ?>
<?= $this->include('components/turmas/modal_deletar_turma', ['cursos' => $cursos]) ?>
<?= $this->include('components/turmas/modal_importar_alunos_turma', ['cursos' => $cursos]) ?>
<?= $this->include('components/turmas/modal_confirmar_senha') ?>


<div class="mb-3">
    <h2 class="card-title mb-0">Turmas</h2>
</div>
<div class="row">
    <div class="col-md-2 grid-margin stretch-card">
        <div class="card ">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-fw" data-bs-toggle="modal" data-bs-target="#modal-cadastrar-turma">
                        <i class="fa fa-plus-circle btn-icon-prepend"></i>
                        Nova Turma
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-10 grid-margin stretch-card">
        <div class="card ">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="card-title">Filtros</h5>
                    <div class="form-group row align-items-end">
                        <div class="col-md-3">
                            <label for="filtro-curso">Curso</label>
                            <select id="filtro-curso" class="js-example-basic-single" style="width:100%">
                                <option value="">--</option>
                                <?php foreach($cursos as $curso): ?>
                                    <option value="<?= esc($curso['nome']) ?>"><?= esc($curso['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-1 d-grid">
                            <button type="button" id="btn-filtrar-curso" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($turmas)): ?>
                    <table class="table mb-4" id="tabela-turmas">
                        <thead>
                            <tr>
                                <th style="width: 4%; min-width: 45px;"><strong>Id</strong></th>
                                <th><strong>Código da Turma</strong></th>
                                <th><strong>Nome</strong></th>
                                <th><strong>Curso</strong></th>
                                <th class="text-nowrap" style="text-align: center; width: 12%; min-width: 100px;"><strong>Ações</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhuma turma encontrada no banco de dados.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
    const dataTableLangUrl = "<?php echo base_url('assets/js/traducao-dataTable/pt_br.json'); ?>";
    const turmasData = <?= json_encode($turmas ?? []) ?>;

    $(document).ready(function() {

        //ESSA PARTE APENAS RENDERIZA O MODELO DO CORONA
        $('.js-example-basic-single').select2();

        const initTooltips = () => {
            $('[data-bs-toggle="tooltip"]').each(function() {
                const tooltipInstance = bootstrap.Tooltip.getInstance(this);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                }
                new bootstrap.Tooltip(this, {
                    container: 'body',
                    customClass: 'tooltip-on-top',
                    offset: [0, 10]
                });
            });
        };

        if (turmasData.length > 0) {
            $('#tabela-turmas').DataTable({
                data: turmasData,
                columns: [
                    { data: 'id' },
                    { data: 'codTurma' },
                    { data: 'nome' },
                    { data: 'curso_nome' },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <div class="d-flex d-flex align-center justify-content-center gap-2">
                                    <span data-bs-toggle="tooltip" data-placement="top" title="Editar">
                                        <button
                                            type="button"
                                            class="justify-content-center align-items-center d-flex btn btn-inverse-success button-trans-success btn-icon me-1 edit-turma-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-editar-turma"
                                            data-id="${data.id}"
                                            data-cod="${data.codTurma}"
                                            data-nome="${data.nome}"
                                            data-curso_id="${data.curso_id}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </span>
                                    <span data-bs-toggle="tooltip" data-placement="top" title="Excluir">
                                        <button
                                            type="button"
                                            class="justify-content-center align-items-center d-flex btn btn-inverse-danger button-trans-danger btn-icon me-1 delete-turma-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-deletar-turma"
                                            data-id="${data.id}"
                                            data-nome="${data.nome}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </span>
                                    <span data-bs-toggle="tooltip" data-placement="top" title="Importar Lista de alunos">
                                        <button
                                            type="button"
                                            class="justify-content-center align-items-center d-flex btn btn-inverse-info button-trans-info btn-icon me-1 import-alunos-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-importar-alunos-turma"
                                            data-id="${data.id}"
                                            data-nome="${data.nome}"
                                            data-curso_nome="${data.curso_nome}"
                                            data-curso_id="${data.curso_id}">
                                            <i class="fa fa-upload"></i>
                                        </button>
                                    </span>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    search: "Pesquisar:",
                    url: dataTableLangUrl
                },
                ordering: true,
                aLengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Todos"],
                ],
                initComplete: function(settings, json) {
                    initTooltips();
                },
                drawCallback: function() {
                    initTooltips();
                }
            });

            $('#btn-filtrar-curso').on('click', function() {
                const valorCurso = $('#filtro-curso').val();
                const table = $('#tabela-turmas').DataTable();

                if (valorCurso) {
                    table.column(3).search('^' + valorCurso + '$', true, false).draw();
                } else {
                    table.column(3).search('').draw();
                }
            });
        }
    
        // Lógica de notificação
        <?php if (session()->has('erros')): ?>
            <?php foreach (session('erros') as $erro): ?>
                $.toast({
                    heading: 'Erro',
                    text: '<?= esc($erro); ?>',
                    showHideTransition: 'fade',
                    icon: 'error',
                    loaderBg: '#dc3545',
                    position: 'top-center'
                });
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!session()->has('erros') && session()->has('sucesso')): ?>
            $.toast({
                heading: 'Sucesso',
                text: '<?= session('sucesso') ?>',
                showHideTransition: 'fade',
                icon: 'success',
                loaderBg: '#35dc5fff',
                position: 'top-center'
            });
        <?php endif; ?>
        
        // Lógica para preencher o modal de edição
        $('#modal-editar-turma').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nome = button.data('nome');
            var curso_id = button.data('curso_id');
            var modal = $(this);
            modal.find('#edit-turma-id').val(id);
            modal.find('#edit-turma-nome').val(nome);
            modal.find('#edit-curso-id').val(curso_id);
        });

        // Lógica para preencher o modal de exclusão
        $('#modal-deletar-turma').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nome = button.data('nome');
            var modal = $(this);
            modal.find('#deletar-id').val(id);
            modal.find('#deletar-nome').html('<b>' + nome + '</b>');
        });

        // Lógica para preencher o modal de importar alunos
        $('#modal-importar-alunos-turma').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var nome = button.data('nome');
            var cursoNome = button.data('curso_nome');
            var cursoId = button.data('curso_id');
            var modal = $(this);
            modal.find('#importar-turma-id').val(id);
            modal.find('#importar-turma-nome').text(nome);
            modal.find('#importar-curso-id').val(cursoId);
            modal.find('#importar-curso-nome').text(cursoNome);
        });
    });

    function abrirModalDeletarTurma(id, nome) {
        // Verifica via AJAX se há alunos vinculados à turma
        $.get("<?= base_url('sys/turmas/verificarAlunos') ?>/" + id, function (resposta) {
            const temAlunos = resposta.temAlunos;

            if (temAlunos) {
                // Mensagem de alerta se houver alunos
                $('#deleteModalBody').html(`
                    <p class="text-break">
                        <strong>ATENÇÃO!</strong>
                        A turma <strong>${nome}</strong> possui alunos cadastrados.
                        Deseja excluir mesmo assim?
                    </p>
                `);

                $('#deleteModalFooter').html(`
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Sim</button>
                `);

                $('#deleteTurmaId').val(id); // Atualiza o input hidden
                $('#modal-deletar-turma').modal('show');

                // Clique em "Sim" → abre modal de senha
                $(document).off('click', '#btnConfirmarExclusao').on('click', '#btnConfirmarExclusao', function() {
                    $('#senhaTurmaId').val(id);
                    $('#modal-deletar-turma').modal('hide');
                    $('#modal-confirmar-senha').modal('show');
                });

            } else {
                // Mensagem normal
                $('#deleteModalBody').html(`
                    <p class="text-break">
                        Confirma a exclusão da turma <strong>${nome}</strong>?
                    </p>
                `);

                $('#deleteModalFooter').html(`
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnExcluirTurma">Excluir Turma</button>
                `);

                $('#deleteTurmaId').val(id);
                $('#modal-deletar-turma').modal('show');
            }
        });
    }

    // Botão de deletar
    $(document).on('click', '.delete-turma-btn', function () {
        const id = $(this).data('id');
        const nome = $(this).data('nome');
        abrirModalDeletarTurma(id, nome);
    });
</script>
