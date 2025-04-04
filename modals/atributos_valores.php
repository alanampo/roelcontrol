<div class="modal" id="modal-editar-valores" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Valores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="control-label">Nombre/Valor:</label>
                        <input type="search" autocomplete="off" placeholder="Nombre" class="form-control"
                            id="input-nombre-valor" style="text-transform: uppercase" maxlength="30" />
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="control-label">Precio Extra:</label>
                        <input type="search" autocomplete="off" placeholder="Precio Extra" class="form-control"
                            id="input-precio-valor" maxlength="9" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button onclick="editarValor()" type="button" class="btn btn-primary">GUARDAR</button>
            </div>
        </div>
    </div>
</div>