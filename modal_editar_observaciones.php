<div id="modal-editar-observacion" class="modal">
    <div class="modal-content">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <h4 class="box-title">Editar Observación: <span id="modal-obs-title"></span></h4>
                <button style="float:right;font-size: 1.6em" class="btn fa fa-close"
                        onClick="$('#modal-editar-observacion').modal('hide')"></button>
            </div>
            <div class='box-body'>
                <input type="hidden" id="obs-id-artpedido">
                <input type="hidden" id="obs-id-pedido">
                <input type="hidden" id="obs-type">
                <div class="form-group">
                    <textarea id="obs-text" class="form-control" rows="4" style="resize:none;text-transform:uppercase;"></textarea>
                </div>
            </div>
            <div class="box-footer">
                <button type="button" class="btn btn-danger" onClick="$('#modal-editar-observacion').modal('hide')">Cancelar</button>
                <button type="button" class="btn btn-success pull-right" onClick="guardarObservacion()">Guardar</button>
            </div>
        </div>
    </div>
</div>