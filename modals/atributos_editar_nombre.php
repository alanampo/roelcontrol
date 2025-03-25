<div class="modal" id="modal-editar-nombre" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Nombre</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col form-group">
                        <label class="control-label">Nombre:</label>
                        <input type="search" autocomplete="off" placeholder="Nombre" class="form-control"
                            id="input-editar-nombre-atributo" style="text-transform: uppercase" maxlength="40" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button onclick="editarNombreAtributo()" type="button" class="btn btn-primary">GUARDAR</button>
            </div>
        </div>
    </div>
</div>