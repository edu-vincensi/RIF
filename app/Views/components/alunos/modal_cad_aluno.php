<div class="modal fade" id="modal-cadastrar-aluno" tabindex="-1" aria-labelledby="modal-cadastrar-aluno-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-cadastrar-aluno-label">Cadastrar Novo Aluno</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="alunoForm" action="<?= site_url('sys/alunos/create') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Aluno</label>
                        <small class="text-danger"> *</small>
                        <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: João da Silva" required>
                    </div>

                    <div class="mb-3">
                        <label for="matricula" class="form-label">Matrícula</label>
                        <small class="text-danger"> *</small>
                        <input type="text" class="form-control" id="matricula" name="matricula" placeholder="Ex: 20230001" required>
                    </div>

                    <div class="mb-3">
                        <label for="curso" class="form-label">Curso</label>
                        <input type="text" class="form-control" id="curso" name="curso" disabled placeholder="Selecione uma turma antes">
                    </div>

                    <div class="mb-3">
                        <label for="turma_id" class="form-label">Turma</label>
                        <select class="form-control" id="turma_id" name="turma_id">
                            <option value="">Selecione uma turma</option>
                            <?php if (!empty($turmas)): ?>
                                <?php foreach ($turmas as $turma): ?>
                                    <option value="<?= $turma['id'] ?>" 
                                            data-curso-id="<?= $turma['curso_id'] ?>" 
                                            data-curso-nome="<?= $turma['curso_nome'] ?>">
                                        <?= esc($turma['nome']) ?> - <?= esc($turma['curso_nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <small class="text-danger"> *</small>
                        <select class="form-control" id="status" name="status" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                    <!-- REMOVIDO: bloco de EMAIL -->

                    <div class="card-body">
                        <label for="telefones" class="form-label">Telefone (WhatsApp)</label>
                        <small class="text-danger"> *</small>
                        <div id="telefone-repeater-container"></div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="alunoForm">Salvar</button>
            </div>
        </div>
    </div>
</div>
