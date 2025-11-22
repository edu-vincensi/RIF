<div class="modal fade" id="modal-importar-aluno" tabindex="-1" aria-labelledby="modal-importar-aluno-label" style="display: none;" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modal-importar-aluno-label">Importar Alunos<h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="alert alert-primary text-dark" role="alert">
                        <i class="fa fa-info-circle"></i><strong>Caminho para exportação destes dados no SUAP:</strong><br>
                        //<br>
                        //<br>
                        Clicar no botão [Exportar para XLS], no canto superior direito.<br>
                        Salvar o arquivo e então enviar através do campo abaixo.
                    </div>
                </div>
            </div>

            <form id="form-importar-aluno" class="forms-sample" method="post" action="<?php echo base_url('sys/alunos/import'); ?>" enctype="multipart/form-data">
	            <?php echo csrf_field() ?>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label>Selecionar planilha</label>
                        <input type="file" id="import-aluno" name="planilha-alunos" class="file-upload-default" required>
                        <div class="input-group col-xs-12 d-flex align-items-center">
                            <input type="text" class="form-control file-upload-info" disabled placeholder="Selecione a planilha clicando ao lado ->">
                            <span class="input-group-append ms-2">
                                <button class="file-upload-browse btn btn-primary" type="button">Buscar planilha</button>
                            </span>
                        </div>
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
