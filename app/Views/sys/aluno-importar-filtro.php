<div class="mb-3">
    <h2 class="card-title mb-0">Filtrar Alunos por Cursos</h2>
</div>

 <div class="card mt-4">
        <div class="card-body">
            <h4>Listando registros encontrados no arquivo</h4>
            <p>Selecione de quais cursos deseja importar os alunos</p>
        </div>

        <div class="card-body">
        <form id="form-filtro-cursos" method="post" action="<?php echo base_url('sys/alunos/filter'); ?>">
            <?= csrf_field() ?>

            <div class="mb">

                <label for="filtro-cursos" class="form-label">Cursos Encontrados</label>
            
                <select id="filtro-cursos" name="filtro[]" multiple class="js-example-basic-multiple" style="width:100%">
                    <option value="select_all">-- Selecionar Todos --</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= esc($curso) ?>"><?= esc($curso) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <br>

            <button type="submit" class="btn btn-primary">Avançar</button>
            
        </form>
        </div>
</div>

<script>
$(document).ready(function() {
    const $filtroCursos = $('#filtro-cursos');

    $filtroCursos.select2({
        templateSelection: function(selection) {
            if (selection.id === 'select_all') return null;
            return selection.text;
        }
    });

    $filtroCursos.on('change', function() {
        let valoresSelecionados = $(this).val() || [];

        if (valoresSelecionados.includes('select_all')) {

            const todosCursos = $filtroCursos.find('option')
                                .map(function() { return this.value; })
                                .get()
                                .filter(v => v !== 'select_all');

            $filtroCursos.val(todosCursos).trigger('change');
        }
    });

    // licar no x limpa tudo
    $filtroCursos.on('select2:unselect', function(e) {
        $filtroCursos.val(null).trigger('change');
    });
});
</script>






