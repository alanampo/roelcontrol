<!-- MODAL AGREGAR PRODUCTO -->
<div id="modal-agregar-producto" class="modal">
    <div class="modal-add-cotizacion">
        <div class='box box-primary mb-0'>
            <div class='box-header with-border'>
                <h3 class='box-title'>Agregar Producto a la Cotización</h3>
            </div>
        </div>
        <div id="modalAgregarProducto" class='box-body'>
            <div class='form-group'>
                <div class="row">
                    <div class="col-md-12">
                        <label for="select_tipo" class="control-label">Tipo de Producto:</label>
                        <select id="select_tipo" title="Selecciona Tipo" class="selectpicker" data-style="btn-info"
                            data-live-search="true" data-width="100%"></select>
                    </div>

                </div>
            </div>
            <div class='form-group'>
                <div class="row">
                    <div class="col-md-12">
                        <label for="select_variedad" class="control-label">Variedad:</label>
                        <div>
                            <div class="row">
                                <div class="col-md-12">
                                    <select id="select_variedad" title="Selecciona Variedad" class="selectpicker"
                                        data-style="btn-info" data-live-search="true" data-width="100%"></select>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

            <div class='form-group form-especie d-none'>
                <div class="row">
                    <div class="col-md-12">
                        <label for="select_especie" class="control-label">Especie provista por el Cliente:</label>
                        <div class="">
                            <div class="row">
                                <div class="col-md-12">
                                    <select id="select_especie" title="Selecciona Especie" class="selectpicker"
                                        data-style="btn-info" data-live-search="true" data-width="100%"></select>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='form-group'>
                <div class="row">
                    <div class="col-md-6">
                        <label for="input-cantidad" class="control-label">Cantidad:</label>
                        <input style="font-weight: bold; font-size: 1.2em; color: black !important;" type="search" autocomplete="off"
                            maxlength="9" id="input-cantidad" placeholder="Cantidad"
                            class="form-control text-right" onkeyup='calcularSubtotal()' onpaste='calcularSubtotal()'>
                    </div>

                    <div class="col-md-6">
                        <label for="input-total" class="control-label">Monto Total (CLP):</label>
                        <input style="font-weight: bold; font-size: 1.2em; color: black !important;" type="search" autocomplete="off"
                            maxlength="16" id="input-total" placeholder="Monto"
                            class="form-control text-right" disabled>
                    </div>
                    
                </div>
            </div>

            <div class="row">
                <div class="col text-right">
                    <button type="button" class="btn fa fa-close" style="font-size: 2em" id="btn_cancel"
                        onClick="cerrarModal('modal-agregar-producto');"></button>
                    <button type="button" class="btn fa fa-save" style="font-size: 2em;margin-left: 0.5em"
                        id="btn_guardarcliente" onClick="addToPedido();"></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL FIN -->