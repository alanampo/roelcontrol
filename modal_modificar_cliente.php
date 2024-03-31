<div id="modal-modificar-cliente" class="modal">
    <div class="modalpago-content">
        <div class='box box-primary'>
            <div class='box-header with-border'>
               <h3 class='box-title'>Asignar Pedido a Otro Cliente</h3>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div class='row'>
                        <div class='col'>
                            <label for="select-nuevo-cliente" class="control-label">Nuevo Cliente:</label>
                            <select id="select-nuevo-cliente" title="Selecciona Cliente" class="selectpicker" data-style="btn-info"
                    data-live-search="true" data-width="100%"></select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="d-flex flex-row justify-content-end">
                            <button type="button" class="btn btn-modal-bottom fa fa-close"
                                onClick="$('#modal-modificar-cliente').modal('hide')"></button>
                            <button type="button" class="btn btn-modal-bottom ml-2 fa fa-save"
                                onClick="guardarCambioCliente();"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- MODAL MODIFICAR CLIENTE FIN -->