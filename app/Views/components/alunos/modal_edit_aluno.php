<div class="modal fade" id="modal-editar-aluno" tabindex="-1" aria-labelledby="modal-editar-aluno-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-editar-aluno-label">Editar Aluno</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editarAlunoForm" action="<?= site_url('sys/alunos/update') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="PUT"> 
                    <input type="hidden" name="matricula" id="edit_matricula">

                    <div class="mb-3">
                        <label for="edit_nome" class="form-label">Nome do Aluno</label>
                        <input type="text" class="form-control" id="edit_nome" name="nome" placeholder="Ex: João da Silva" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_turma_id" class="form-label">Turma</label>
                        <select class="form-control" id="edit_turma_id" name="turma_id">
                            <option value="">Selecione uma turma</option>
                            <?php if (!empty($turmas)): ?>
                                <?php foreach ($turmas as $turma): ?>
                                    <option value="<?= $turma['id'] ?>" data-curso-id="<?= $turma['curso_id'] ?>" data-curso-nome="<?= $turma['curso_nome'] ?>">
                                        <?= esc($turma['nome']) ?> - <?= esc($turma['curso_nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_curso" class="form-label">Curso</label>
                        <input type="text" class="form-control" id="edit_curso" name="curso" disabled placeholder="Selecione uma turma">
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                    <!-- REMOVIDO: bloco de EMAIL -->

                    <div class="card-body">
                        <label for="edit_telefones" class="form-label">Telefone</label>
                        <div id="edit-telefone-repeater-container"></div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="editarAlunoForm">Salvar</button>
            </div>
        </div>
    </div>
</div>
