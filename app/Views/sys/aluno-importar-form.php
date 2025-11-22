<div class="mb-3">
    <h2 class="card-title mb-0">Alunos Importados</h2>
</div>

<div class="card">
    <div class="card-body">
        <h4>Listando registros encontrados no arquivo</h4>
        <p>Selecione quais deseja importar, e confirme no botão ao final da listagem</p>
    
        <table class="table mb-4" id="listagem-alunos-importados">
            <?php if (isset($import_completo) && $import_completo): ?>
                <form id="form-importar-alunos" class="forms-sample" method="post" action="<?php echo base_url('sys/alunos/importProcess'); ?>">
            <?php else: ?>
                <form id="form-importar-alunos" class="forms-sample" method="post" action="<?php echo base_url('sys/turmas/importProcess'); ?>">
                    <input type="hidden" name="turma_id" value="<?= esc($turma_id ?? '') ?>">
            <?php endif; ?>
                <?php echo csrf_field() ?>

                <thead>
                    <tr>
                        <th>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" id="select-all" class="form-check-input">
                                </label>
                            </div>
                        </th>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <?php if (isset($import_completo) && $import_completo): ?>
                            <th>Telefone</th>
                            <th>Status</th>
                        <?php endif; ?>                    
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($alunos) && !empty($alunos)): ?>
                        <?php foreach ($alunos as $index => $aluno): ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox"
                                                checked
                                                name="selecionados[]" 
                                                class="form-check-input" 
                                                value="<?= htmlspecialchars(json_encode($aluno), ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                        </label>
                                    </div>
                                </td>
                                <td><?= esc($aluno['matricula']) ?></td>
                                <td><?= esc($aluno['nome']) ?></td>
                                <?php if (isset($import_completo) && $import_completo): ?>
                                    <td><?= esc(implode(', ', $aluno['telefone'])) ?></td>
                                    <td><?= esc($aluno['status']) ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <button type="submit" class="btn btn-success mt-3">Importar Selecionadas</button>
            </form>
        </table>
    </div>

<div>

<script>
    $(document).ready(function()
    {
        document.getElementById('select-all').addEventListener('change', function() 
        {
            const checkboxes = document.querySelectorAll('input[name="selecionados[]"]:not([disabled])');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        //Ativa os tooltips dos botões
        $('[data-bs-toggle="tooltip"]').tooltip();
    });    
</script>