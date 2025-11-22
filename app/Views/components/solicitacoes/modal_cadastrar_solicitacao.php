<div class="modal fade" id="modal-cadastrar-solicitacao" tabindex="-1" role="dialog" aria-labelledby="modal-cadastrar-solicitacao-label" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width: 700px;">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modal-cadastrar-solicitacao-label">Cadastrar Nova Solicitação</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <form id="form-cadastrar-solicitacao" class="forms-sample" method="post" action="<?php echo base_url('sys/solicitacoes/admin/create'); ?>">
                <?php echo csrf_field() ?>

                <div class="modal-body">

                    <div class="mb-3">
                        <label for="turma_id" class="form-label">Selecione a Turma(s)</label>
                        <select id="turma_id" name="turma_id[]" class="js-example-basic-multiple" multiple="multiple" style="width: 100%;">
                            <?php foreach ($turmas as $turma): ?>
                                <option value="<?= $turma['id'] ?>">
                                    <?= esc($turma['nome_turma'] . ' - ' . esc($turma['nome_curso'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="alunos_id" class="form-label">Selecione os Alunos</label>
                        <select id="alunos_id" name="matriculas[]" class="js-example-basic-multiple" multiple="multiple" style="width:100%">
                            <!-- Opções serão carregadas dinamicamente via JS -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data(s) da Solicitação</label>
                        <div id="inline-datepicker" class="datepicker"></div> 
                        <input type="hidden" name="datas" id="datas-hidden">
                    </div>

                    <div class="mb-3 d-flex gap-3">
                        <div class="flex-fill">
                            <label for="crc" class="form-label">Código CRC</label>
                            <input type="text" class="form-control" id="crc" name="crc" placeholder="Digite o código CRC" required>
                        </div>
                        <div class="flex-fill">
                            <label for="codigo" class="form-label">Código Verificador</label>
                            <input type="number" class="form-control" id="codigo" name="codigo" placeholder="Digite o código verificador" required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="motivo" class="form-label">Motivo</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="" disabled>Selecione o motivo</option>
                            <option value="0" selected>Contraturno</option>
                            <option value="1">Estágio</option>
                            <option value="2">Treino</option>
                            <option value="3">Projeto</option>
                            <option value="4">Visita Técnica</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary me-2">Salvar</button>
                </div>
            </form>

        </div>
    </div>
</div>