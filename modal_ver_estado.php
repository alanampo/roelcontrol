<div id="ModalVerEstado" class="modal">
        <div class="modal-content-verpedido">
          <div class='box box-primary'>
            <div class='box-header with-border'>
              <h4 class="box-title"><span class="title-pedido"></span>
              <button class='btn btn-secondary btn-sm d-none' style='font-size:12px' id='btn-modificar-cliente'><i class='fa fa-edit'></i>  Modificar Cliente</button>
                  
            </h4>
              <button style="float:right;font-size: 1.6em" class="btn fa fa-close"
                onClick="CerrarModalEstado()"></button>
            </div>
            <div id="tablita">
              <div class='box-body'>
              <?php include('loader.php'); ?>
                <div class="row">
                  <div class="col-md-6">
                    <h5><span class="label-etapa"></span>
                      <button id="btn-entregar" class="btn btn-success ml-3 d-none" style="font-size:14px;margin-top:-4px;"><i class="fa fa-truck"></i> ENTREGAR</button>
                      <button id="btn-enviar-stock" class="btn btn-primary ml-3 d-none" style="font-size:14px;margin-top:-4px;"><i class="fa fa-shopping-cart"></i> STOCK</button>
                      <button id="btn-produccion" class="btn btn-info ml-3 d-none" style="font-size:14px;margin-top:-4px;"><i class="fa fa-step-forward"></i> ENVIAR A PRODUCCIÓN</button>
                    </h5>
                    <h5>Nombre: <span class="label-producto"></span></h5>
                    <h5>Plantas Pedidas: <span class="label-cantidad"></span> <button class='btn btn-secondary btn-sm ml-1 d-none' style='font-size:12px' id='btn-modificar-cantidad'><i class='fa fa-edit'></i></button> <span class="label-band-pedidas"></span>
                    </h5>
                    <h5>Bandejas Sembradas: <span class="label-band-sembradas"></span> 
                    </h5>
                    <div class="label-semillas"></div>
                    <h5>Ingresó el Día: <span class="label-fecha-ingreso"></span></h5>
                    <h5>Pasó a ETAPA 1: <span class="label-etapa1"></span></h5>
                    <h5>Pasó a ETAPA 2: <span class="label-etapa2"></span></h5>
                    <h5>Pasó a ETAPA 3: <span class="label-etapa3"></span></h5>
                    <h5>Pasó a ETAPA 4: <span class="label-etapa4"></span></h5>
                    <h5>Pasó a ETAPA 5: <span class="label-etapa5"></span></h5>
                    <h5>Pasó a ETAPA 6: <span class="label-etapa6"></span></h5>
                    <h5>Se entregó el Día: <span class="label-fecha-entrega"></span></h5>
                    <h5 class="text-success font-weight-bold">MESÓN: <span class="label-mesada text-primary"></span>
                      <button class='btn btn-success btn-sm d-none ml-2' id='btn-asignar-mesada'><i class='fa fa-table'></i>  Asignar Mesón</button>
                    </h5>
                    <br>
                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 0: </h5>
                      <button id="btn-control1" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto1')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(1)' capture='camera'
                        id='input-foto1' style='display:none' />
                      <button id="btn-verfoto1" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto1"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 1: </h5>
                      <button id="btn-control2" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto2')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(2)' capture='camera'
                        id='input-foto2' style='display:none' />
                      <button id="btn-verfoto2" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto2"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 2: </h5>
                      <button id="btn-control3" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto3')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(3)' capture='camera'
                        id='input-foto3' style='display:none' />
                      <button id="btn-verfoto3" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto3"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 3: </h5>
                      <button id="btn-control4" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto4')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(4)' capture='camera'
                        id='input-foto4' style='display:none' />
                      <button id="btn-verfoto4" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto4"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 4: </h5>
                      <button id="btn-control5" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto5')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(5)' capture='camera'
                        id='input-foto5' style='display:none' />
                      <button id="btn-verfoto5" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto5"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 5: </h5>
                      <button id="btn-control6" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto6')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(6)' capture='camera'
                        id='input-foto6' style='display:none' />
                      <button id="btn-verfoto6" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto6"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                    <div class="d-flex flex-row align-items-center">
                      <h5 class="text-primary">ETAPA 6: </h5>
                      <button id="btn-control6" class="btn btn-sm btn-success ml-2 mb-1 btn-control"><i
                          class="fa fa-search"></i> CONTROL</button>
                      <button onClick="abrir('input-foto7')" class="btn btn-sm btn-primary ml-2 mb-1"><i
                          class="fa fa-camera"></i> Cargar Foto</button>
                      <input type='file' accept='.jpg, .jpeg' onchange='cambiofoto(7)' capture='camera'
                        id='input-foto7' style='display:none' />
                      <button id="btn-verfoto6" class="btn btn-sm btn-info ml-2 mb-1 d-none btn-verfoto"><i
                          class="fa fa-picture-o"></i> Ver Foto</button>
                      <button id="btn-eliminarfoto7"
                        class="btn btn-sm btn-danger ml-2 mb-1 btn-verfoto fa fa-trash"></button>
                    </div>

                  </div>
                  <div class="col-md-6">
                    <div class="row">
                      <div class="col">
                        <div style='background-color:#e6e6e6;padding:5px'>
                          <span style='color:#74DF00;font-weight:bold;font-size:1.5em'>Observaciones del
                            PRODUCTO:</span><br>
                          <textarea name='textarea' maxlength="100" class='form-control' disabled='true'
                            id='input-observaciones' type='text' rows="4"
                            style='width:100%;text-transform:uppercase;resize:none'>
                          </textarea>
                          <br>
                          <button class='btn btn-primary btn-sm' onclick='activarInputObs();'><i class='fa fa-edit'></i>
                            Modificar</button>
                          <button class='btn btn-success btn-sm' id='btn-guardar-obs' disabled='true'
                            onclick='guardarObs();'><i class='fa fa-save'></i> Guardar</button>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col">
                        <div style='background-color:#e6e6e6;padding:5px'>
                          <span class="text-danger" style='font-weight:bold;font-size:1.5em'>Marcar PROBLEMA:</span><br>
                          <textarea name='textarea' maxlength="50" class='form-control' disabled='true'
                            id='input-problema' type='text' rows="4"
                            style='width:100%;text-transform:uppercase;resize:none'>
                          </textarea>
                          <br>
                          <button class='btn btn-primary btn-sm' onclick='activarInputProblema();'><i
                              class='fa fa-edit'></i> Modificar</button>
                          <button class='btn btn-danger btn-sm' id='btn-guardar-obs-problema' disabled='true'
                            onclick='guardarProblema();'><i class='fa fa-save'></i> Guardar</button>
                          <button class='btn btn-success btn-sm d-none pull-right' id='btn-solucionado' disabled='true'
                            onclick='solucionarProblema();'><i class='fa fa-check'></i> Solucionado</button>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col">
                        <div style='background-color:#e6e6e6;padding:5px'>
                          <span class='text-primary' style='font-weight:bold;font-size:1.5em'>Observaciones del
                            PEDIDO:</span><br>
                          <textarea name='textarea' maxlength="120" class='form-control' disabled='true'
                            id='input-observaciones-pedido' type='text' rows="4"
                            style='width:100%;text-transform:uppercase;resize:none'>
                          </textarea>
                          
                        </div>
                      </div>
                    </div>

                    <div class="row mt-5">
                         <div class="col">
                            <button id="btn-eliminar-pedido" class="btn btn-danger btn-sm text-center d-none"><i class="fa fa-trash"></i> ELIMINAR PEDIDO</button>
                        </div>
                        <div class="col">
                            <button id="btn-cancelar-pedido" class="btn btn-danger btn-sm text-center d-none"><i class="fa fa-close"></i> CANCELAR PEDIDO</button>
                        </div>
                        
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- MODAL FIN -->




    <div id="ModalCambioCantidad" class="modal">
    <div class="modal-content3">
      <div class='box box-primary'>
        <div class='box-header with-border'>
          <h3 class='box-title'>Modificar Cantidad</h3>
        </div>
        <div class='box-body'>
          <div class='form-group'>
            <div class="row">
              <div class="col-md-4">
                <label for="input-cantidad" class="control-label">Nueva Cantidad:</label>
                <input maxlength="9" type="number" min="0" step="1" id="input-cantidad" class="form-control text-right" > 
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
              <div class="d-flex flex-row justify-content-end">
                <button type="button" class="btn fa fa-close btn-modal-bottom" onClick="CerrarModalCantidad();"></button>
                <button type="button" class="btn fa fa-save btn-modal-bottom" onClick="GuardarCambioCantidad();"></button>
              </div>
           
          </div>
        </div>
      </div>
    </div>
  </div>


  <div id="ModalEntrega" class="modal">
    <div class="modal-content3">
      <div class='box box-primary'>
        <div class='box-header with-border'>
          <h3 class='box-title'>Entregar Producto</span></h3>
        </div>
        <div class='box-body'>
          <div class="row">
            <div class="col text-center">
              <h6 class="text-success">Ya entregado: <span class="label-entregado"></span></h6>
              <h5 class="text-danger font-weight-bold">Faltan entregar: <span class="label-falta-entregar"></span></h5>
            </div>
          </div>
          
          
          <div class='form-group'>
            <div class="row">
              <div class="col-md-4">
                <label for="input-cantidad" class="control-label">Cantidad:</label>
                <input maxlength="9" type="number" min="0" step="1" id="input-cantidad-entrega" class="form-control text-right font-weight-bold" style="font-size: 1.3em" > 
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
              <div class="d-flex flex-row justify-content-end">
                <button type="button" class="btn fa fa-close btn-modal-bottom" onClick="$('#ModalEntrega').modal('hide')"></button>
                <button type="button" class="btn fa fa-save btn-modal-bottom" onClick="guardarEntrega();"></button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>







  <div id="ModalAsignarMesada" class="modal">
    <div class="modal-asignar-mesadas">
      <div class='box box-primary'>
        <div class='box-header with-border'>
          <h3 class='box-title'>Asignar Mesada: <span class="title-pedido"></span></h3>
        </div>
        <div class='box-body'>
          <div class='form-group'>
            <div class="row">
              <div class="col">
                <table class="table table-responsive w-100 d-block d-md-table tabla-mesadas">
                  <thead class="thead-dark text-center">
                    <tr>
                      <th scope="col">Semillas</th>
                      <th scope="col">Esquejes</th>
                    </tr>
                  </thead>
                  <tbody>
                    
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col">
              <div class="d-flex flex-row justify-content-end">
                <button type="button" class="btn fa fa-close btn-modal-bottom" onClick="$('#ModalAsignarMesada').modal('hide')"></button>
                <button type="button" class="btn fa fa-save btn-modal-bottom" onClick="guardarEnMesada();"></button>
              </div>
           
          </div>
        </div>
      </div>
    </div>
  </div>




  <div id="ModalControl" class="modal">
    <div class="modal-control">
      <div class='box box-primary'>
        <div class='box-header with-border'>
          <h3 class='box-title'>Control Etapa <span class="title-etapa"></span></h3>
          <button style="float:right;font-size: 1.6em" class="btn fa fa-close"
                onClick="$('#ModalControl').modal('hide')"></button>
        </div>
        <div class='box-body'>
          <?php include('loader.php'); ?>
          <div class='form-group'>
            <div class="row">
              <div class="col">
                <table class="table table-responsive w-100 d-block d-md-table tabla-control">
                  <thead class="thead-dark text-center">
                    
                  </thead>
                  <tbody>
                    
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

       
      </div>
    </div>
  </div>