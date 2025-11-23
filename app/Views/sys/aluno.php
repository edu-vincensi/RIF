<?= $this->include('components/alunos/modal_cad_aluno', ['turmas' => $turmas]) ?>
<?= $this->include('components/alunos/modal_del_aluno') ?>
<?= $this->include('components/alunos/modal_edit_aluno', ['turmas' => $turmas]) ?>
<?= $this->include('components/alunos/modal_importar_aluno', ['turmas' => $turmas]) ?>



<div class="mb-3">
    <h2 class="card-title mb-0">Alunos</h2>
</div>
<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card ">
            <div class="card-body">
                <div class="my-1">
                    <div class="mb-3">
                        <h5 class="card-title mb-0">Ações</h5>
                    </div>
                    <button type="button" class="btn btn-primary btn-fw" data-bs-toggle="modal" data-bs-target="#modal-cadastrar-aluno">
                        <i class="mdi mdi-plus-circle btn-icon-prepend"></i>
                        Novo Aluno
                    </button>
                    <button type="button" class="btn btn-info btn-fw" data-bs-toggle="modal" data-bs-target="#modal-importar-aluno">
                        <i class="fa fa-upload btn-icon-prepend"></i> 
                        Importar Alunos do SUAP
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 grid-margin stretch-card">
        <div class="card ">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="card-title">Filtros</h5>
                    <div class="form-group row align-items-end">
                        <div class="col-md-3">
                            <label for="filtro-curso">Curso</label>
                            <select id="filtro-curso" class="js-example-basic-single" style="width:100%">
                                <option value="">--</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="filtro-turma">Turma</label>
                            <select id="filtro-turma" class="js-example-basic-single" style="width:100%">
                                <option value="">--</option>
                                <?php foreach ($turmas as $turma): ?>
                                    <option value="<?= esc($turma['nome']) ?>"><?= esc($turma['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="filtro-status">Status</label>
                            <select id="filtro-status" class="js-example-basic-single" style="width:100%">
                                <option value="">--</option>
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                            </select>
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
                <?php if (!empty($alunos)): ?>
                    <table class="table" id="tabela-alunos" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Matrícula</th>
                                <th>Nome</th>
                                <th>Turma</th>
                                <th>Curso</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th style="text-align: center; width: 10%; min-width: 100px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                <?php else: ?>
                    <p>Nenhum aluno encontrado no banco de dados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-dialog {
        margin-top: 10vh;
    }
    .form-control[disabled], .form-control[readonly] {
        background-color: #2a3038;
        color: #fff;
        opacity: 1;
    }
    .form-control {
        color: #fff !important;
    }
    select.form-control {
        cursor: pointer;
    }
    input#curso.form-control[disabled] {
        cursor: not-allowed;
    }
    .tooltip-on-top {
        z-index: 1060 !important;
    }
</style>

<script>
    const dataTableLangUrl = "<?php echo base_url('assets/js/traducao-dataTable/pt_br.json'); ?>";
    const alunosData = <?= json_encode($alunos) ?>;

    $(document).ready(function() {

        //ESSA PARTE APENAS RENDERIZA O MODELO DO CORONA
        $('.js-example-basic-single').select2();

        
        const cursosSet = new Set();
        alunosData.forEach(aluno => {
            if (aluno.curso_nome) cursosSet.add(aluno.curso_nome);
        });

        const $cursoSelect = $('#filtro-curso');
        cursosSet.forEach(curso => {
            $cursoSelect.append(`<option value="${curso}">${curso}</option>`); // Adiciona cada curso ao select
        });

        // Objeto que contém os templates e a lógica para cada tipo de repetidor
        const repeaters = {
            email: {
                template: (value = '') => `
                    <div class="email-repeater-item d-flex align-items-center mb-2">
                        <div class="input-group me-2">
                            <input type="email" class="form-control form-control-sm" name="email[]" placeholder="aluno@gmail.com" value="${value}" required>
                        </div>
                        <button type="button" class="btn btn-inverse-danger btn-sm icon-btn remove-email me-2" data-bs-toggle="tooltip" title="Remover Email">
                            <i class="mdi mdi-delete"></i>
                        </button>
                        <button type="button" class="btn btn-inverse-info btn-sm icon-btn add-email" data-bs-toggle="tooltip" title="Adicionar Email">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>`,
                placeholder: 'É necessário ter pelo menos um e-mail.'
            },
            telefone: {
                template: (value = '') => `
                    <div class="telefone-repeater-item d-flex align-items-center mb-2">
                        <div class="input-group me-2">
                            <input type="text" class="form-control form-control-sm telefone-input" name="telefone[]" placeholder="Ex: (99) 99999-9090" value="${value}" required>
                        </div>
                        <button type="button" class="btn btn-inverse-danger btn-sm icon-btn remove-telefone me-2" data-bs-toggle="tooltip" title="Remover Telefone">
                            <i class="mdi mdi-delete"></i>
                        </button>
                        <button type="button" class="btn btn-inverse-info btn-sm icon-btn add-telefone" data-bs-toggle="tooltip" title="Adicionar Telefone">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>`,
                placeholder: 'É necessário ter pelo menos um telefone.'
            }
        };

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

        const setupRepeater = (containerId, type, values = []) => {
            const container = $(containerId);
            container.empty();
            if (values.length > 0) {
                values.forEach(value => container.append(repeaters[type].template(value)));
            } else {
                container.append(repeaters[type].template());
            }
            updateRepeaterButtons(container);
        };

        const updateRepeaterButtons = container => {
            const isSingleItem = container.find('.email-repeater-item, .telefone-repeater-item').length <= 1;
            container.find('.remove-email, .remove-telefone').toggle(!isSingleItem);
        };

        const handleAddButtonClick = function() {
            const type = $(this).hasClass('add-email') ? 'email' : 'telefone';
            const container = $(this).closest('.card-body').find(`[id$="-repeater-container"]`);
            container.append(repeaters[type].template());
            updateRepeaterButtons(container);
            initTooltips();
        };

        const handleRemoveButtonClick = function() {
            const type = $(this).hasClass('remove-email') ? 'email' : 'telefone';
            const container = $(this).closest('.card-body').find(`[id$="-repeater-container"]`);
            
            if (container.find(`.${type}-repeater-item`).length > 1) {
                const tooltipInstance = bootstrap.Tooltip.getInstance(this);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                }
                $(this).closest(`.${type}-repeater-item`).remove();
                updateRepeaterButtons(container);
                initTooltips();
            } else {
                alert(repeaters[type].placeholder);
            }
        };

        const handleTurmaChange = function() {
            const cursoNome = $(this).find('option:selected').data('curso-nome');
            $(this).closest('.modal-body').find('input[name="curso"]').val(cursoNome || 'Selecione uma turma');
        };

        const handleDeletarModalShow = function(event) {
            const button = $(event.relatedTarget);
            const matricula = button.data('matricula');
            const nome = button.data('nome');
            const modal = $(this);
            modal.find('.modal-body p').html(`Tem certeza de que deseja deletar o aluno <strong>${nome}</strong> (Matrícula: ${matricula})?`);
            modal.find('#delete-matricula').val(matricula);
        };

        $('#modal-cadastrar-aluno').on('show.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#curso').val('Selecione uma turma');
            setupRepeater('#email-repeater-container', 'email');
            setupRepeater('#telefone-repeater-container', 'telefone');
            initTooltips();
        });

        $('#modal-editar-aluno').on('show.bs.modal', function(event) {
            const matricula = $(event.relatedTarget).data('matricula');
            const modal = $(this);
            const url = `<?= base_url('sys/alunos/edit') ?>/${matricula}`;

            fetch(url)
                .then(response => response.json())
                .then(aluno => {
                    modal.find('#edit_matricula').val(aluno.matricula);
                    modal.find('#edit_nome').val(aluno.nome);
                    modal.find('#edit_turma_id').val(aluno.turma_id);
                    modal.find('#edit_status').val(aluno.status);
                    
                    const cursoNome = modal.find(`#edit_turma_id option[value='${aluno.turma_id}']`).data('curso-nome');
                    modal.find('#edit_curso').val(cursoNome || 'Selecione uma turma');
                    
                    setupRepeater('#edit-email-repeater-container', 'email', aluno.emails);
                    setupRepeater('#edit-telefone-repeater-container', 'telefone', aluno.telefones);
                    initTooltips();
                })
                .catch(error => console.error('Erro ao buscar dados do aluno:', error));
        });

        $(document).on('click', '.add-email, .add-telefone', handleAddButtonClick);
        $(document).on('click', '.remove-email, .remove-telefone', handleRemoveButtonClick);
        $(document).on('change', '#turma_id, #edit_turma_id', handleTurmaChange);
        $('#deletarModal').on('show.bs.modal', handleDeletarModalShow);
        
        // Inicialização do DataTables
        const table = $('#tabela-alunos').DataTable({  //OBS: MUDEI A VÁRIAVEL PARA CONST
            data: alunosData,
            columns: [
                { data: 'matricula' },
                { data: 'nome' },
                { data: 'turma_nome' },
                { data: 'curso_nome' },
                { 
                    data: 'emails',
                    render: function(data, type, row) {
                        let html = '';
                        if (Array.isArray(data) && data.length > 0) {
                            html = data.map(email => `<small class="font-weight-bold">${email}</small>`).join('');
                        } else {
                            html = '<small class="font-weight-bold">Nenhum e-mail</small>';
                        }
                        return `<div class="d-flex flex-column">${html}</div>`;
                    }
                },
                { 
                    data: 'telefones',
                    render: function(data, type, row) {
                        let html = '';
                        if (Array.isArray(data) && data.length > 0) {
                            html = data.map(telefone => `<small class="font-weight-bold">${telefone}</small>`).join('');
                        } else {
                            html = '<small class="font-weight-bold">Nenhum telefone</small>';
                        }
                        return `<div class="d-flex flex-column">${html}</div>`;
                    }
                },
                {
                    data: 'status',
                    render: function(data, type, row) {
                        return data == 1 ? 'Ativo' : 'Inativo';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex align-center justify-content-center gap-2">
                                <span data-bs-toggle="tooltip" data-placement="top" title="Editar">
                                    <button
                                        type="button"
                                        class="justify-content-center align-items-center d-flex btn btn-inverse-success btn-icon me-1 edit-aluno-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modal-editar-aluno"
                                        data-matricula="${data.matricula}">
                                        <i class="mdi mdi-pencil"></i>
                                    </button>
                                </span>
                                <span data-bs-toggle="tooltip" data-placement="top" title="Excluir">
                                    <button
                                        type="button"
                                        class="justify-content-center align-items-center d-flex btn btn-inverse-danger btn-icon"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletarModal"
                                        data-matricula="${data.matricula}"
                                        data-nome="${data.nome}">
                                        <i class="mdi mdi-delete"></i>
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

        // Filtro de DataTable
        $.fn.dataTable.ext.search.push(function(settings, data) {
            const curso = $('#filtro-curso').val();
            const turma = $('#filtro-turma').val();
            const status = $('#filtro-status').val();

            const rowCurso = data[3]; // coluna Curso
            const rowTurma = data[2]; // coluna Turma
            const rowStatus = data[6]; // coluna Status

            return (!curso || rowCurso === curso) &&
                (!turma || rowTurma === turma) &&
                (!status || rowStatus === status);
        });

        $('#filtro-curso, #filtro-turma, #filtro-status').on('change', function() {
            table.draw();
        });

        $('#filtro-curso, #filtro-turma, #filtro-status').on('change', function() {
            localStorage.setItem('filtroCurso', $('#filtro-curso').val());
            localStorage.setItem('filtroTurma', $('#filtro-turma').val());
            localStorage.setItem('filtroStatus', $('#filtro-status').val());
            table.draw();
        });

        const curso = localStorage.getItem('filtroCurso');
        const turma = localStorage.getItem('filtroTurma');
        const status = localStorage.getItem('filtroStatus');

        if (curso) $('#filtro-curso').val(curso).trigger('change');
        if (turma) $('#filtro-turma').val(turma).trigger('change');
        if (status) $('#filtro-status').val(status).trigger('change');

        // Redesenha a tabela já com os filtros aplicados
        table.draw();

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

        $(document).on('input', '.telefone-input', function () {
            let telefone = $(this).val().replace(/\D/g, '').slice(0, 11);

            if (telefone.length > 2)
                telefone = '(' + telefone.slice(0, 2) + ') ' + telefone.slice(2);

            if (telefone.length > 10)
                telefone = telefone.slice(0, 10) + '-' + telefone.slice(10);

            $(this).val(telefone);
        });    

    });
</script>