<?php echo view('components/solicitacoes/modal_cadastrar_solicitacao', ["turmas" => $turmas]) ?>
<?php echo view('components/solicitacoes/modal_editar_solicitacao', ["turmas" => $turmas]) ?>
<?php echo view('components/solicitacoes/modal_deletar_solicitacao') ?>


<div class="mb-3">
    <h2 class="card-title mb-0">Solicitações de Refeições</h2>
</div>
<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card ">
            <div class="card-body">
                <div class="my-1">
                    <div class="mb-3">
                        <h5 class="card-title mb-0">Ações</h5>
                    </div>
                    <button type="button" class="btn btn-primary btn-fw" data-bs-toggle="modal" data-bs-target="#modal-cadastrar-solicitacao">
                        <i class="fa fa-plus-circle btn-icon-prepend"></i>
                        Nova Solicitação
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
                        <label>Motivo</label>
                        <select id="filtro-motivo" class="js-example-basic-single" style="width:100%">
                            <option value="">--</option>
                            <option value="0">Contraturno</option>
                            <option value="1">Estágio</option>
                            <option value="2">Treino</option>
                            <option value="3">Projeto</option>
                            <option value="4">Visita Técnica</option>
                        </select>
                      </div>
                      <div class="col-md-5">
                        <label for="">Período:</label>
                        <div id="datepicker-popup" class="input-group input-daterange d-flex align-items-center">
                            <input type="text" class="form-control" style="background-color: black;"> 
                            <div class="input-group-addon mx-4"> até </div>
                            <input type="text" class="form-control" style="background-color: black;">
                        </div>
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
                <?php if (!empty($solicitacoes)): ?>
                    <table class="table mb-4" id="listagem-solicitacoes" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Turma</th>
                                <th>Data</th>
                                <th>CRC</th>
                                <th>Código</th>
                                <th>Motivo</th>
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
    .tooltip-on-top {
        z-index: 9999 !important;
    }
    
    .tooltip-on-top .tooltip-inner {
        background-color: #333;
        color: #fff;
        font-size: 13px;
        padding: 8px 10px;
        border-radius: 6px;
        text-align: center;
        max-width: 220px;
    }

    .tooltip-on-top .tooltip-arrow::before {
        border-top-color: #333 !important;
    }

    .datepicker table tr td.today.active::before {
        background-color: #28a745 !important;
        color: #fff !important;
    }
</style>


<script>
    const dataTableLangUrl = "<?= base_url('assets/js/traducao-dataTable/pt_br.json'); ?>";
    const solicitacoesData = <?= json_encode($solicitacoes ?? []) ?>;
    const getAlunosByTurmaUrl = '<?= base_url('sys/solicitacoes/admin/getAlunosByTurma') ?>';

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

    function restaurarFiltros() {
        const salvo = localStorage.getItem('filtrosSolicitacoes');
        if (!salvo) return;

        const filtros = JSON.parse(salvo);

        $('#filtro-turma').val(filtros.turma);
        $('#filtro-motivo').val(filtros.motivo);
        $('#datepicker-popup input:first').val(filtros.dataInicio);
        $('#datepicker-popup input:last').val(filtros.dataFim);
    }

    $(document).ready(function() {
        restaurarFiltros();

        $('.js-example-basic-single').select2();
        $('.js-example-basic-multiple').select2();

        
        $('#modal-cadastrar-solicitacao').on('shown.bs.modal', function () {
            $(this).find('.js-example-basic-single').select2({
                dropdownParent: $('#modal-cadastrar-solicitacao')
            });
            $(this).find('.js-example-basic-multiple').select2({
                dropdownParent: $('#modal-cadastrar-solicitacao')
            });

            const $cal = $('#inline-datepicker');
            $cal.datepicker('destroy'); // garante uma instância limpa
            $cal.datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                multidate: true,
                language: 'pt-BR'
            }).on('changeDate', function(e) {
                const datas = e.dates.map(date => {
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                });
                $('#datas-hidden').val(datas.join(','));
            });
        });

        if (solicitacoesData && solicitacoesData.length > 0) {

            const motivos = {
                0: 'Contraturno',
                1: 'Estágio',
                2: 'Treino',
                3: 'Projeto',
                4: 'Visita Técnica'
            };

            const tabela = $('#listagem-solicitacoes').DataTable({
                data: solicitacoesData,
                columns: [
                    {
                        data: 'aluno_nome',
                        render: function(data) {
                            return `<span>${data}</span>`;
                        }
                    },
                    {
                        data: 'turma_nome',
                        render: function(data) {
                            return `<span>${data}</span>`;
                        }
                    },
                    {
                        data: 'data_refeicao',
                        render: function(data) {
                            if (!data) return '';
                            const partes = data.split('-'); 
                            return `${partes[2]}/${partes[1]}/${partes[0]}`;
                        }
                    },
                    {
                        data: 'crc',
                        render: function(data) {
                            return `<span>${data}</span>`;
                        }
                    },
                    {
                        data: 'codigo',
                        render: function(data) {
                            return `<span>${data}</span>`;
                        }
                    },
                    {
                        data: 'motivo',
                        render: function(data) {
                            return `<span>${motivos[data] ?? '—'}</span>`;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            const editInfoAttr = JSON.stringify(row);

                            return `
                                <div class="d-flex align-center justify-content-center gap-2">

                                    <span data-bs-toggle="tooltip" title="Editar solicitação">
                                        <button type="button" 
                                            class="btn btn-inverse-success btn-icon me-1 btn-editar-solicitacao d-flex align-items-center justify-content-center"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modal-editar-solicitacao"
                                            data-edit-info='${editInfoAttr}'>
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </span>

                                    <span data-bs-toggle="tooltip" title="Excluir solicitação">
                                        <button type="button" 
                                            class="btn btn-inverse-danger btn-icon me-1 btn-excluir-solicitacao d-flex align-items-center justify-content-center"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modal-deletar-solicitacao"
                                            data-id="${row.id}" 
                                            data-nome="Solicitação #${row.id}">
                                            <i class="fa fa-trash"></i>
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

                initComplete: function() {
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

            function filtrarTabela() {
                const turmaSelecionada = $('#filtro-turma').val()?.trim();
                const motivoSelecionado = $('#filtro-motivo').val()?.toLowerCase().trim();

                const dataInicioStr = $('#datepicker-popup input:first').val()?.trim(); 
                const dataFimStr = $('#datepicker-popup input:last').val()?.trim();

                const dataInicio = parseDateDMY(dataInicioStr);
                const dataFim = parseDateDMY(dataFimStr);

                const filtrados = solicitacoesData.filter(item => {

                    const matchTurma = !turmaSelecionada || item.turma_nome?.includes($('#filtro-turma option:selected').text().trim());
                    const matchMotivo = !motivoSelecionado || item.motivo?.toLowerCase().trim() === motivoSelecionado;

                    let matchData = true;
                    if (dataInicio || dataFim) {
                        const itemData = parseDateDMY(item.data_refeicao);
                        if (!itemData) return false;
                        if (dataInicio && itemData < dataInicio) matchData = false;
                        if (dataFim && itemData > dataFim) matchData = false;
                    }

                    return matchTurma && matchMotivo && matchData;
                });

                tabela.clear().rows.add(filtrados).draw();
            }

            function salvarFiltros() {
                const filtros = {
                    turma: $('#filtro-turma').val(),
                    motivo: $('#filtro-motivo').val(),
                    dataInicio: $('#datepicker-popup input:first').val(),
                    dataFim: $('#datepicker-popup input:last').val()
                };

                localStorage.setItem('filtrosSolicitacoes', JSON.stringify(filtros));
            }


            $('#filtro-turma').on('change', function() {
                salvarFiltros();
                filtrarTabela();
            });

            $('#filtro-motivo').on('change', function() {
                salvarFiltros();
                filtrarTabela();
            });

            $('#datepicker-popup').on('changeDate', function() {
                salvarFiltros();
                filtrarTabela();
            });

            $('#datepicker-popup input').on('keyup change', function() {
                salvarFiltros();
                filtrarTabela();
            });
        }

        filtrarTabela();

        $('#listagem-solicitacoes').on('click', '.btn-editar-solicitacao', function () {
            const data = $(this).data('edit-info');

            // Preenche hidden originais
            $("#original_aluno_id").val(data.aluno_id);
            $("#original_data_refeicao").val(data.data_refeicao);
            $("#original_motivo").val(data.motivo);

            // Preenche campos editáveis
            $("#edit-id").val(data.id);
            $("#edit-status").val(data.status);
            $("#edit-crc").val(data.crc);
            $("#edit-codigo").val(data.codigo);

            // Seleciona motivo automaticamente
            $("#edit_motivo").val(data.motivo).change();

            // Preencher turma
            if (data.turma_id) {
                $("#edit_turma_id").val([data.turma_id]).trigger("change");
            }

            // Carregar alunos da turma
            fetch(`${getAlunosByTurmaUrl}?turmas=${data.turma_id}`)
                .then(res => res.json())
                .then(alunos => {
                    const alunosSelect = $("#edit_alunos_id");
                    alunosSelect.empty();

                    alunos.forEach(aluno => {
                        const option = new Option(aluno.nome, aluno.matricula, aluno.matricula == data.aluno_id, aluno.matricula == data.aluno_id);
                        alunosSelect.append(option);
                    });

                    alunosSelect.trigger("change");
                });

            // Preencher a data no datepicker
            $("#edit_datas-hidden").val(data.data_refeicao);

            if (typeof setDatepickerDate !== "undefined") {
                setDatepickerDate("#edit-inline-datepicker", data.data_refeicao);
            }
        });

        $('#modal-editar-solicitacao').on('shown.bs.modal', function () {
            const datasParaSelecionar = $(this).data('datas-para-selecionar');
            const minDateParaEditar = $(this).data('min-date-para-editar');

            $('#edit_turma_id').select2({
                dropdownParent: $('#modal-editar-solicitacao'),
                width: '100%'
            });
            $('#edit_alunos_id').select2({
                dropdownParent: $('#modal-editar-solicitacao'),
                width: '100%'
            });
            
            // inicializa (ou reinicializa) o calendário
            const $editCal = $('#edit-inline-datepicker');
            $editCal.datepicker('destroy'); // limpa qualquer instância anterior

            $editCal.datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                multidate: true,
                language: 'pt-BR',
                startDate: minDateParaEditar // impede selecionar datas anteriores
            }).on('changeDate', function(e) {
                const datas = e.dates.map(date => {
                    const y = date.getFullYear();
                    const m = String(date.getMonth() + 1).padStart(2, '0');
                    const d = String(date.getDate()).padStart(2, '0');
                    return `${y}-${m}-${d}`;
                });
                $('#edit_datas-hidden').val(datas.join(','));
            });

            // pré-seleciona as datas existentes
            if (datasParaSelecionar.length > 0) {
                const parsedDates = datasParaSelecionar.map(str => new Date(str));
                $editCal.datepicker('setDates', parsedDates);
                $('#edit_datas-hidden').val(datasParaSelecionar.join(','));
            }
        });

        if (document.getElementById('form-cadastrar-solicitacao')) {

            const getAlunosByTurmaUrl = "<?= base_url('sys/solicitacoes/admin/getAlunosByTurma') ?>";
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
            
            $('#form-cadastrar-solicitacao').on('submit', function(e) {
                e.preventDefault(); // Impede o recarregamento da página
                const form = this;
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Resposta JSON:', data);
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

        $(document).on('mouseenter', '.datepicker-days td.day.disabled', function() {
            const el = this;
            let tooltipTitle = '';
            if ($(el).closest('#modal-editar-solicitacao').length) {
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

            // Ajuste automático de posição caso o calendário esteja no topo da tela
            const tip = $(tooltip.tip);
            const offset = $(el).offset();
            const tipHeight = tip.outerHeight();
            const scrollTop = $(window).scrollTop();

            // Se o tooltip estiver saindo da tela, move para baixo
            if (offset.top - tipHeight < scrollTop) {
                tooltip.dispose();
                const tooltipBottom = new bootstrap.Tooltip(el, {
                    html: true,
                    title: tooltipTitle,
                    trigger: 'manual',
                    container: 'body',
                    placement: 'bottom',
                    customClass: 'tooltip-on-top'
                });
                tooltipBottom.show();
            }
        });
        
        $(document).on('mouseleave', '.datepicker-days td.day.disabled', function() {
            const el = this;
            const tooltip = bootstrap.Tooltip.getInstance(el);
            if (tooltip) {
                tooltip.dispose();
            }
        });

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
    });
</script>