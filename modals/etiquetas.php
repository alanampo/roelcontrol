<div id="modal-etiquetas" class="modal" 
 data-keyboard="false" 
 data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar Etiquetas</h5>
                
                <button type="button" class="close" data-dismiss="modal" aria-label="control-sidebar-dark">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row row-settings d-none">
                    <div class="form-group col-md-4">
                        <label class="control-label">Tamaño Etiqueta:</label>
                        <div class="d-flex flex-row align-items-center">
                            <input id="input-ancho" onchange="setEtiquetaSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="9999"  placeholder="Ancho" />
                            <span class="mr-2 ml-2">x</span>
                            <input id="input-alto" onchange="setEtiquetaSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="9999"  placeholder="Alto" />
                            <select onchange="setEtiquetaSize()" class="form-control ml-2" id="select-unidad-etiquetas"
                                title="Unidad">
                                <option value="px" selected="selected">px</option>
                                <option value="mm">mm</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="control-label">Tamaño QR:</label>
                        <div class="d-flex flex-row align-items-center">
                            <input id="input-ancho-qr" onchange="setQRSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="9999" placeholder="Ancho" />
                            <span class="mr-2 ml-2">x</span>
                            <input id="input-alto-qr" onchange="setQRSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="9999" placeholder="Alto" />
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="control-label">Tamaño Logo:</label>
                        <div class="d-flex flex-row align-items-center">
                            <input id="input-ancho-logo" onchange="setLogoSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="9999" placeholder="Ancho" />
                            <span class="mr-2 ml-2">x</span>
                            <input id="input-alto-logo" onchange="setLogoSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="9999" placeholder="Alto" />
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="control-label">T. Letra (px):</label>
                        
                            <input id="input-font-size" onchange="setFontSize()" class="form-control text-center"
                                type="number" step="1" min="1" max="100" placeholder="Fuente" />
                        
                    </div>
                    
                </div>
                <div class="row row-settings d-none">
                    <div class="col-md-4">
                        <button id="btn-guardar-size" onclick="guardarSizeEtiquetas()" class="btn btn-primary btn-sm mb-3"><i class="fas fa-save"></i> GUARDAR CAMBIOS</button>
                    </div>
                </div>
                <div class="row row-etiquetas">

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <button onclick="imprimirEtiquetas()" type="button" class="btn btn-primary">
                    IMPRIMIR
                </button>
            </div>
        </div>
    </div>
</div>
<!--modal-etiquetas-->