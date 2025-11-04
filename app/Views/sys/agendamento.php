<?php echo view('components/agendamentos/modal_cadastrar_agendamento', ['turmas' => $turmas, 'alunos' => $alunos]) ?>
<?php echo view('components/agendamentos/modal_editar_agendamento', ["turmas" => $turmas]); ?>
<?php echo view('components/agendamentos/modal_deletar_agendamento');?>


<div class="mb-3">
    <h2 class="card-title mb-0">Agendamento de Refeição</h2>
</div>
<div class="row">
    <div class="col-12 col-xl-3 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div class="my-4">
                    <span data-bs-toggle="tooltip" title="Cadastrar Agendamento">
                        <button type="button" class="btn btn-primary btn-fw" data-bs-toggle="modal" data-bs-target="#modal-cadastrar-agendamento">
                            <i class="fa fa-plus-circle btn-icon-prepend"></i>
                            <span class="d-none d-md-inline ms-1">Novo Agendamento</span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-9 grid-margin stretch-card">
        <div class="card ">
            <div class="card-body">
                <div class="mb-3">
                    <h5 class="card-title">Filtros</h5>
                    <div class="form-group row align-items-end">
                      <div class="col-md-2">
                        <label>Turma</label>
                        <select id="filtro-turma" class="js-example-basic-single" style="width:100%">
                            <option value="">--</option>
                            <?php foreach ($turmas as $turma): ?>
                                <option value="<?= esc($turma['id']) ?>"><?= esc($turma['nome_turma']) ?></option>
                            <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label>Status</label>
                        <select id="filtro-status" class="js-example-basic-single" style="width:100%">
                            <option value="">--</option>
                            <option value="Disponível">Disponível</option>
                            <option value="Confirmada">Confirmada</option>
                            <option value="Retirada">Retirada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label>Motivo</label>
                        <select id="filtro-motivo" class="js-example-basic-single" style="width:100%">
                            <option value="">--</option>
                            <option value="Contraturno">Contraturno</option>
                            <option value="Estágio">Estágio</option>
                            <option value="Treino">Treino</option>
                            <option value="Projeto">Projeto</option>
                            <option value="Visita Técnica">Visita Técnica</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label for="">Período:</label>
                        <div id="datepicker-popup" class="input-group input-daterange d-flex align-items-center">
                            <input type="text" class="form-control" style="background-color: black;"> 
                            <div class="input-group-addon mx-4"> até </div>
                            <input type="text" class="form-control" style="background-color: black;">
                        </div>
                      </div>
                      <div class="col-md-2 text-start">
                        <button id="btn-filtrar" class="btn btn-primary">Filtrar</button>
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
                <div class="table-responsive">
                    <?php if (isset($agendamentos) && !empty($agendamentos)): ?>
                        <table class="table mb-4" id="listagem-agendamentos">
                            <thead>
                                <tr>
                                    <th><strong>Aluno(a)<i class="mdi mdi-chevron-down"></i></strong></th>
                                    <th><strong>Turma<i class="mdi mdi-chevron-down"></i></strong></th>
                                    <th><strong>Data<i class="mdi mdi-chevron-down"></i></strong></th>
                                    <th><strong>Status<i class="mdi mdi-chevron-down"></i></strong></th>
                                    <th><strong>Motivo<i class="mdi mdi-chevron-down"></i></strong></th>
                                    <th style="text-align: center; width: 10%; min-width: 100px;"><strong>Ações</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nenhum agendamento encontrado no banco de dados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-ver-alunos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alunos do Agendamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <ul id="lista-alunos-modal" class="list-group"></ul>
            </div>
        </div>
    </div>
</div>

<style>
    .flatpickr-calendar {
        background-color: #2a3038 !important;
        color: #fff !important;
        border: 1px solid #444 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4) !important;
    }

    .flatpickr-weekdaycontainer {
        display: grid !important;
        grid-template-columns: repeat(7, 1fr) !important;
        text-align: center !important;
    }

    .flatpickr-weekday {
        color: #fff !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        font-weight: 600 !important;
    }

    .flatpickr-days .dayContainer {
        display: grid !important;
        grid-template-columns: repeat(7, 1fr) !important;
        grid-auto-rows: 38px !important; 
        justify-items: center !important;
        align-items: center !important;
    }

    .flatpickr-day {
        color: #fff !important;
        width: 32px !important;
        height: 32px !important;
        font-size: 0.85rem !important;
        line-height: 32px !important; 
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        border-radius: 50% !important;
        transition: background 0.2s ease !important;
    }

    .flatpickr-day:hover {
        background: #3a4048 !important;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #007bff !important;
        color: #fff !important;
    }

    .flatpickr-day.today {
        border: 1px solid #007bff !important;
    }

    .flatpickr-current-month {
        color: #fff !important;
    }

    .ver-alunos-link {
        color: #ffffffff;
        text-decoration: none;
        cursor: pointer;
        font-weight: 500;
    }

    .ver-alunos-link:hover {
        color: #0056b3;
    }
    .tooltip-on-top {
        z-index: 9999 !important;
    }
</style>

<script>
    const dataTableLangUrl = "<?= base_url('assets/js/traducao-dataTable/pt_br.json'); ?>";
    const agendamentosData = <?= json_encode($agendamentos ?? []) ?>;
    const getAlunosByTurmaUrl = '<?= base_url('sys/agendamento/admin/getAlunosByTurma') ?>';

    let flatpickrEditInstance = null;
    const alunosSelecionadosEdit = new Map();
    
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            // Limpa instâncias antigas para evitar bugs
            const oldTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (oldTooltip) {
                oldTooltip.dispose();
            }
            // Cria a nova instância
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                container: 'body' // Garante que o tooltip apareça sobre outros elementos
            });
        });
    }

    function atualizarListaAlunosEdit() {
        const listaAlunosUl = $('#edit_lista-alunos');
        listaAlunosUl.empty();

        if (alunosSelecionadosEdit.size === 0) {
            listaAlunosUl.append('<li class="text-muted small p-2">Nenhum aluno selecionado.</li>');
        } else {
            alunosSelecionadosEdit.forEach((nome, matricula) => {
                const li = `
                    <li class="d-flex justify-content-between align-items-center p-1" data-matricula="${matricula}">
                        <span>${nome}</span>
                        <button type="button" class="btn-close btn-close-white btn-sm remove-aluno-edit"></button>
                    </li>`;
                listaAlunosUl.append(li);
            });
        }
        $('#edit_matriculas-hidden').val(Array.from(alunosSelecionadosEdit.keys()).join(','));
    }

    $(document).ready(function() {

        //ESSA PARTE APENAS RENDERIZA O MODELO DO CORONA
        $('.js-example-basic-single').select2();
        $('.js-example-basic-multiple').select2();

        
        $('#modal-cadastrar-agendamento').on('shown.bs.modal', function () {
            $(this).find('.js-example-basic-single').select2({
                dropdownParent: $('#modal-cadastrar-agendamento')
            });
            $(this).find('.js-example-basic-multiple').select2({
                dropdownParent: $('#modal-cadastrar-agendamento')
            });
        });
        //FIM DA PARTE DO CORONA

        if (agendamentosData && agendamentosData.length > 0) {
            const tabela = $('#listagem-agendamentos').DataTable({
                data: agendamentosData,
                columns: [{
                    data: 'turma_aluno',
                    render: function(data, type, row) {
                        const alunosJson = JSON.stringify(row.alunos).replace(/'/g, "&apos;");
                        const turmasAlunosJson = JSON.stringify(row.alunos_por_turma).replace(/'/g, "&apos;");
                        
                        if (row.tipo === 'turma') {
                            return `<a href="#" 
                                    class="ver-alunos-link" 
                                    data-bs-toggle="tooltip" 
                                    title="Ver Alunos" 
                                    data-alunos='${alunosJson}'><u>${data}</u></a>`;
                        
                        } else if (row.tipo === 'multi_turma') {
                            return `<a href="#" 
                                    class="ver-alunos-link" 
                                    data-bs-toggle="tooltip" 
                                    title="Ver Turmas e Alunos" 
                                    data-turmas-alunos='${turmasAlunosJson}'><u>${data}</u></a>`;
                        }
                        return data;
                    }
                }, {
                    data: 'turma'
                },{
                    data: 'data'
                }, {
                    data: 'status'
                }, {
                    data: 'motivo'
                }, {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const deleteInfoAttr = JSON.stringify(row.delete_info);
                        const editInfoAttr = JSON.stringify(row);

                        return `
                            <div class="d-flex align-center justify-content-center gap-2">
                                <span data-bs-toggle="tooltip" title="Editar agendamento">
                                    <button type="button" class="btn btn-inverse-success btn-icon me-1 btn-editar-agendamento d-flex align-items-center justify-content-center"
                                        data-bs-toggle="modal" data-bs-target="#modal-editar-agendamento"
                                        data-edit-info='${editInfoAttr}'>
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </span>
                                <span data-bs-toggle="tooltip" title="Excluir agendamento">
                                    <button type="button" class="btn btn-inverse-danger btn-icon me-1 btn-excluir-agendamento d-flex align-items-center justify-content-center"
                                        data-bs-toggle="modal" data-bs-target="#modal-deletar-agendamento"
                                        data-nome="${row.turma_aluno}" data-delete-info='${deleteInfoAttr}'>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </span>
                            </div>
                        `;
                    }
                }],
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

            // Função para converter data no formato DMY para objeto Date
            function parseDateDMY(str) {
                if (typeof str !== 'string' || !str.trim()) return null;

                const s = str.trim();

                // Padrões de formato possíveis
                const isoPattern = /^(\d{4})-(\d{2})-(\d{2})$/;  // YYYY-MM-DD
                const brPattern  = /^(\d{2})\/(\d{2})\/(\d{4})$/; // DD/MM/YYYY

                let dia, mes, ano;

                if (isoPattern.test(s)) {
                    [, ano, mes, dia] = s.match(isoPattern).map(Number);
                } else if (brPattern.test(s)) {
                    [, dia, mes, ano] = s.match(brPattern).map(Number);
                } else {
                    return null; // formato não reconhecido
                }

                const data = new Date(ano, mes - 1, dia);

                // Garante que a data é válida (ex: 31/02 -> inválida)
                return isNaN(data.getTime()) ? null : data;
            }

            if ($('#datepicker-popup').length) {
                $('#datepicker-popup').datepicker('destroy'); // remove a configuração antiga
                $('#datepicker-popup').datepicker({
                    format: 'dd/mm/yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    language: 'pt-BR'
                });
            }

            $('#btn-filtrar').on('click', function () {
                const turmaSelecionada = $('#filtro-turma').val()?.trim(); // pega o value
                const statusSelecionado = $('#filtro-status').val()?.toLowerCase().trim();
                const motivoSelecionado = $('#filtro-motivo').val()?.toLowerCase().trim();

                const dataInicioStr = $('#datepicker-popup input:first').val()?.trim(); 
                const dataFimStr = $('#datepicker-popup input:last').val()?.trim();

                // Converte para objetos Date (se existirem)
                const dataInicio = parseDateDMY(dataInicioStr);
                const dataFim = parseDateDMY(dataFimStr);

                const filtrados = agendamentosData.filter(item => {
                    // Se turmaSelecionada estiver vazia (""), retorna todas
                    const matchTurma = !turmaSelecionada || item.turma?.includes($('#filtro-turma option:selected').text().trim());

                    const matchStatus = !statusSelecionado || item.status?.toLowerCase().trim() === statusSelecionado;
                    const matchMotivo = !motivoSelecionado || item.motivo?.toLowerCase().trim() === motivoSelecionado;

                    // Filtro de datas
                    let matchData = true;
                    if (dataInicio || dataFim) {
                        const itemData = parseDateDMY(item.data); // item.data deve estar no formato YYYY-MM-DD
                        if (!itemData) return false;
                        if (dataInicio && itemData < dataInicio) matchData = false;
                        if (dataFim && itemData > dataFim) matchData = false;
                    }

                    return matchTurma && matchStatus && matchMotivo && matchData;
                });

                tabela.clear().rows.add(filtrados).draw();
            });

        }        
        // Modal de VISUALIZAÇÃO de Alunos
        $('#listagem-agendamentos tbody').on('click', '.ver-alunos-link', function(e) {
            e.preventDefault();
            const ul = $("#lista-alunos-modal");
            ul.empty();
            const alunos = $(this).data('alunos');
            const turmasAlunos = $(this).data('turmas-alunos');

            if (turmasAlunos) {
                for (const nomeTurma in turmasAlunos) {
                    ul.append(`<li class="list-group-item turma-header">${nomeTurma}</li>`);
                    turmasAlunos[nomeTurma].forEach(nomeAluno => ul.append(`<li class="list-group-item aluno-item">${nomeAluno}</li>`));
                }
            } else if (alunos) {
                alunos.forEach(nome => ul.append(`<li class="list-group-item">${nome}</li>`));
            }
            new bootstrap.Modal(document.getElementById("modal-ver-alunos")).show();
        });

        // Modal de EXCLUSÃO de Agendamento
        $('#listagem-agendamentos').on('click', '.btn-excluir-agendamento', function() {
            const button = $(this);
            const nome = button.data('nome');
            const deleteInfo = button.data('delete-info');
            const modal = $('#modal-deletar-agendamento');
            modal.find('#deleteAgendamentoNome').text(nome);
            modal.find('#deleteAgendamentoInfo').val(JSON.stringify(deleteInfo));
        });

        // Modal de EDIÇÃO de Agendamento
        $('#listagem-agendamentos').on('click', '.btn-editar-agendamento', function() {
            const data = $(this).data('edit-info');
            const deleteInfo = data.delete_info;
            const statusMap = { 'Disponível': '0', 'Confirmada': '1', 'Retirada': '2', 'Cancelada': '3' };
            const motivoMap = { 'Contraturno': '0', 'Estágio': '1', 'Treino': '2', 'Projeto': '3', 'Visita Técnica': '4' };

            $('#edit_original_aluno_ids').val(deleteInfo.aluno_ids.join(','));
            $('#edit_original_datas').val(deleteInfo.datas.join(','));
            $('#edit_original_motivo').val(deleteInfo.motivo);
            $('#edit_turma_id').val(data.turmas || []).trigger('change');

            // Aguarda o carregamento dos alunos
            setTimeout(() => {
                $('#edit_alunos_id').val(deleteInfo.aluno_ids || []).trigger('change');
            }, 500);

            $('#edit_motivo').val(motivoMap[data.motivo] || deleteInfo.motivo);
            $('#edit_status').val(statusMap[data.status]);
            
            alunosSelecionadosEdit.clear();
            data.alunos.forEach((nome, index) => {
                alunosSelecionadosEdit.set(String(deleteInfo.aluno_ids[index]), nome);
            });
            atualizarListaAlunosEdit();
            const dataMaisAntiga = deleteInfo.datas[0]; 

            $('#modal-editar-agendamento').data('datas-para-selecionar', deleteInfo.datas);
            $('#modal-editar-agendamento').data('min-date-para-editar', dataMaisAntiga);
            $('#edit_datas-hidden').val(deleteInfo.datas.join(','));    
        });

        // Função pra puxar as datas selecionadas
        $('#modal-editar-agendamento').on('shown.bs.modal', function () {
            const datasParaSelecionar = $(this).data('datas-para-selecionar');
            const minDateParaEditar = $(this).data('min-date-para-editar');

            $('#edit_turma_id').select2({
                dropdownParent: $('#modal-editar-agendamento'),
                width: '100%'
            });
            $('#edit_alunos_id').select2({
                dropdownParent: $('#modal-editar-agendamento'),
                width: '100%'
            });
            
            if (flatpickrEditInstance) {
                flatpickrEditInstance.destroy();
            }
            flatpickrEditInstance = flatpickr("#edit-datepicker", {
                inline: true,
                mode: "multiple",
                dateFormat: "Y-m-d",
                locale: "pt",
                // minDate: "today",
                minDate: minDateParaEditar,
                defaultDate: datasParaSelecionar,
                onChange: function(selectedDates, dateStr, instance) {
                    const datas = selectedDates.map(d => instance.formatDate(d, "Y-m-d"));
                    $('#edit_datas-hidden').val(datas.join(','));
                }
            });
        });

        if (document.getElementById('form-cadastrar-agendamento')) {

            const getAlunosByTurmaUrl = "<?= base_url('sys/agendamento/admin/getAlunosByTurma') ?>";
            // Inicializa Select2 nos selects já existentes
            $('#turma_id').on('change', function() {
                const turmasSelecionadas = $(this).val(); // array de IDs
                const alunosSelect = $('#alunos_id');

                alunosSelect.prop('disabled', true).empty();

                if (turmasSelecionadas && turmasSelecionadas.length > 0) {
                    fetch(`${getAlunosByTurmaUrl}?turmas=${turmasSelecionadas.join(',')}`)
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                            return res.json();
                        })
                        .then(alunos => {
                            alunosSelect.empty();
                            // Adiciona a opção "Selecionar Todos"
                            alunosSelect.append('<option value="select_all">-- Selecionar Todos --</option>');

                            // Adiciona os alunos retornados
                            alunos.forEach(aluno => {
                                // Cria opção apenas se ainda não existir
                                if ($('#alunos_id option[value="' + aluno.matricula + '"]').length === 0) {
                                    const option = new Option(aluno.nome, aluno.matricula, false, false); // selecionado
                                    $('#alunos_id').append(option).trigger('change');
                                }
                            });
                            alunosSelect.prop('disabled', false).trigger('change');
                        })
                        .catch(err => {
                            alunosSelect.prop('disabled', false);
                            console.error('Erro no fetch:', err);
                            alert('Erro ao carregar alunos. Veja o console para detalhes.');
                        });
                } else {
                    alunosSelect.prop('disabled', false);
                }
            });

            // Evento: Selecionar Todos os alunos
            $('#alunos_id').on('change', function() {
                const valoresSelecionados = $(this).val() || [];
                if (valoresSelecionados.includes('select_all')) {
                    // Marca todos os alunos (exceto o "select_all")
                    const todosAlunos = $('#alunos_id option')
                        .map(function() { return this.value; })
                        .get()
                        .filter(v => v !== 'select_all');

                    // Atualiza o Select2
                    $('#alunos_id').val(todosAlunos).trigger('change');
                }
            });

            // Inicialização de Plugins (Flatpickr)
            flatpickr("#datepicker-container-cadastro", {
                inline: true,
                mode: "multiple",
                dateFormat: "Y-m-d",
                minDate: "today",
                locale: "pt",
                onChange: function(selectedDates, dateStr, instance) {
                    const datas = selectedDates.map(d => instance.formatDate(d, "Y-m-d"));
                    $('#datas-hidden').val(datas.join(','));
                }
            });
            
            $('#form-cadastrar-agendamento').on('submit', function(e) {
                e.preventDefault(); // Impede o recarregamento da página
                const form = this;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload(); 
                    } else {
                        $.toast({
                            heading: 'Erro ao Salvar',
                            text: data.message || 'Verifique os dados e tente novamente.',
                            showHideTransition: 'fade',
                            icon: 'error',
                            loaderBg: '#dc3545',
                            position: 'top-center'
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    $.toast({
                        heading: 'Erro de Conexão',
                        text: 'Não foi possível se conectar ao servidor.',
                        showHideTransition: 'fade',
                        icon: 'error',
                        loaderBg: '#dc3545',
                        position: 'top-center'
                    });
                });
            });
        }

        const getAlunosByTurmaUrl = "<?= base_url('sys/agendamento/admin/getAlunosByTurma') ?>";

        // --- EDIÇÃO ---
        $('#edit_turma_id').on('change', function() {
            const turmasSelecionadas = $(this).val(); // array de IDs
            const alunosSelect = $('#edit_alunos_id');

            alunosSelect.prop('disabled', true).empty();

            if (turmasSelecionadas && turmasSelecionadas.length > 0) {
                fetch(`${getAlunosByTurmaUrl}?turmas=${turmasSelecionadas.join(',')}`)
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(alunos => {
                        alunos.forEach(aluno => {
                            // Evita duplicatas
                            if ($('#edit_alunos_id option[value="' + aluno.matricula + '"]').length === 0) {
                                const option = new Option(aluno.nome, aluno.matricula, false, false);
                                alunosSelect.append(option);
                            }
                        });
                        alunosSelect.prop('disabled', false).trigger('change');
                    })
                    .catch(err => {
                        alunosSelect.prop('disabled', false);
                        console.error('Erro no fetch:', err);
                        alert('Erro ao carregar alunos. Veja o console para detalhes.');
                    });
            } else {
                alunosSelect.prop('disabled', false);
            }
        });
        
        $('#edit_lista-alunos').on('click', '.remove-aluno-edit', function() {
            const matricula = $(this).closest('li').data('matricula');
            alunosSelecionadosEdit.delete(String(matricula));
            atualizarListaAlunosEdit();
        });

        $(document).on('mouseover', '.flatpickr-day.flatpickr-disabled', function() {
            const el = this;
            let tooltipTitle = '';
            if ($(el).closest('#modal-editar-agendamento').length) {
                tooltipTitle = `<i class="fa fa-exclamation-triangle text-warning" style="margin-right: 6px;"></i> A data não pode ser inferior à data já cadastrada`;
            } else {
                tooltipTitle = `<i class="fa fa-exclamation-triangle text-warning" style="margin-right: 6px;"></i> A data não pode ser anterior à de hoje`;
            }
            const tooltip = new bootstrap.Tooltip(el, {
                html: true,
                title: tooltipTitle,
                trigger: 'manual',
                container: 'body',
                customClass: 'tooltip-on-top'
            });
            tooltip.show();
        });
        
        $(document).on('mouseout', '.flatpickr-day.flatpickr-disabled', function() {
            const el = this;
            const tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) {
                tooltip.dispose();
            }
        });
         $(document).ready(function() {
            <?php if (session()->getFlashdata('sucesso')): ?>
                $.toast({
                    heading: 'Sucesso!',
                    text: '<?= session()->getFlashdata('sucesso') ?>',
                    showHideTransition: 'fade',
                    icon: 'success',
                    loaderBg: '#28a745',
                    position: 'top-center'
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('erros')): ?>
                $.toast({
                    heading: 'Erro',
                    text: '<?= session()->getFlashdata('erros')[0]?>',
                    showHideTransition: 'fade',
                    icon: 'error',
                    loaderBg: '#dc3545',
                    position: 'top-center'
                });
            <?php endif; ?>
        });

    });
</script>