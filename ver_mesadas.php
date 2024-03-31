<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Mesones</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_mesadas.js?v=<?php echo $version ?>"></script>
  </head>
  <body>
    <div id="miVentana">
    </div>
  <div id="ocultar">
    <div class="wrapper">
      <header class="main-header">
        <?php
        include('class_lib/nav_header.php');
        ?>
      </header>
      <aside class="main-sidebar">
        <?php
        include('class_lib/sidebar.php');
        ?>
      </aside>
      <div class="content-wrapper">
        <section class="content-header">
          <h1>
           Mesones
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Mesones</li>
          </ol>
        </section>
        <section class="content">
          <div class='box-body'>
            <div align='right'><button style="font-size: 1.5em" class="btn btn-success btn-round fa fa-plus-square" onclick="CrearMesada();"></button></div>
            
            <div class="row mt-2">
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
        </section><!-- /.content -->
        <div id="ModalAgregarMesada" class="modal">
          <div class="modal-content3">
            <div class='box box-primary'>
           <div class='box-header with-border'>
           <h3 class='box-title'><span id="titulo_modal">Agregar Mesada</span></h3>
           </div>
          <div class='box-body'>
              <div class='form-group'>
            <div class="row">
              <div class="col-md-6">
                <label for="select_tipo" class="control-label">Tipo de Mesada:</label>
                <select id="select_tipo" title="Selecciona Tipo" class="selectpicker" data-style="btn-info" data-width="100%">
                  
                  <option value="S">Semillas</option>
                  <option value="E">Esquejes</option>
                </select>
              </div>
            </div>
          </div>
              <div class="row">
              <div class="col">
                <div class="d-flex flex-row justify-content-end">
                  <button type="button" class="btn fa fa-close btn-modal-bottom" id="btn_cancel" onClick="$('#ModalAgregarMesada').modal('hide')"></button>
                  <button type="button" class="btn fa fa-save btn-modal-bottom" id="btn_guardarcliente" onClick="GuardarMesada();"></button>
                </div>
                
                </div>
              </div>
        </div>
        </div>
      </div>
      </div> <!-- MODAL POPUP FIN -->

      <div id="ModalVerMesada" class="modal">
        <div id="ModalVerMesadaContent" class="modal-content-verpedido">
            <div class='box box-primary'>
           <div class='box-header with-border'>
            <h4 class='box-title'>Mesada Nº <b id='num_mesadaview'></b></h4>
            <button class="btn fa fa-close pull-right btn-modal-top" onClick="CerrarModalVerMesada()"></button>
           </div>
           <div id="tablita">
           <div class='box-body'>
            <table id="tabla_contenidomesada" class="table table-bordered table-responsive w-100 d-block d-md-table" role="grid">
              <thead>
              <tr role="row">
                <th class="text-center">Orden</th>
                <th class="text-center">Producto</th>
                <th class="text-center">Cantidad<br>Bandejas</th>
                <th class="text-center">Faltan<br>Entregar</th>
                <th class="text-center">Cliente</th>
                <th class="text-center">Fecha Siembra</th>
                <th class="text-center">Fecha Ingreso Mesada</th>
                <th class="text-center">Entrega Solicitada</th>
                <th class="text-center">Estado</th>
              </tr>
              </thead>
              <tbody>
              </tbody>
           </table>
          </div>
        </div>
          </div>
        </div>
      </div> <!-- FIN MODAL VER -->
            <div id="ModalVerEstado" class="modal">
          <div class="modal-content-verpedido">
              <div class='box box-primary'>
             <div class='box-header with-border'>
              <h4 class="box-title" id="nombre_cliente3">Cliente:</h4>
              <button style="float:right;" class="btn fa fa-close btn-modal-top" onClick="CerrarModalEstado()"></button>
             </div>
             <div id="tablita">
                <div class='box-body'>
                  <div id='box_info'>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="ModalEnviaraMesadas" class="modal">
          <div id="ModalEnviaraMesadasContent" class="modal-content-verpedido">
              <div class='box box-primary'>
             <div class='box-header with-border'>
              <h4 class="box-title" id="nombre_cliente">Reasignar Mesadas</h4>
              <button class="btn fa fa-close btn-modal-top pull-right ml-5" style="font-size: 2em" onClick="CerrarModalEnviarMesadas()"></button>
              <button type="button" class="btn fa fa-save btn-modal-top pull-right" id="btn_guardarcliente" onClick="GuardarReasignacionMesadas();"> GUARDAR</button>
              
             </div>
              <div class='row'>
              <div class='col-md-7'>
                <div class='box-body'>
                  <div id='box_info2'>
                    <h4 id='bandejas_pendientes'></h4>
                    <div class="contenedor">
                        <div class="row">
                          <div class="col text-center">
                            <div class="row row-reasignar">
                            </div>
                          </div>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            
            <div class='col-md-4 order-first order-md-last'>
             <div id="contenedor_cantidades">
                <h4>Faltan asignar: <span id='quedan_bandejas'></span></h4>
             </div>
           </div>
           <button id="scroll_up" style="font-size:40px;border-radius:100%;position: fixed;bottom: 0px;right: 0px;" class="btn btn-primary btn-round fa fa-arrow-up" onclick="scrollear(2);"><span style="font-size:16px;font-family: Calibri"></span></button>
            
        </div>
            </div>
          </div>
        </div> <!-- FIN MODAL MESADAS -->


        <div id="ModalModificarCantidad" class="modal">
          <!-- Modal content -->
          <div class="modal-content3">
            <div class='box box-primary'>
           <div class='box-header with-border'>
           <h3 class='box-title'>Modificar Cantidad en Mesada <span id="id_orden_mesada" style="display:none"></span></h3>
           </div>
          <div id="bodymodal" class='box-body'>
                <div class='form-group'>
                  <div class="row">
                  <div class="col-md-6">
                    <label for="cantidad_bandejas_nueva" class="control-label">Cantidad Bandejas:</label>
                    <input style="font-size: 2em;font-weight: bold;" type="number" min="0" step="1" id="cantidad_bandejas_nueva" class="form-control" value="0"> 
                    </div>
                  </div>
                </div> 
                
              <div class="row" style="margin-top: 80px;">
               </div>
          </div>               

              <div class="row">
              <div class="col-md-12">
              <div align="right">
                <button type="button" style="font-size: 2.3em" class="btn fa fa-close btn-modal-bottom" id="btn_cancel" onClick="$('#ModalModificarCantidad').css('display','none');"></button>
                <button type="button" style="font-size: 2.3em" class="btn fa fa-save btn-modal-bottom" id="btn_guardarentrega" onClick="GuardarNuevaCantidad();"></button>
                </div>
              </div>
              </div>
        <div style="display:none" id="id_artpedido1">
        </div>
        </div>
      </div> 
      </div><!-- MODAL MODIFICAR CANTIDAD FIN -->  


      </div><!-- /.content-wrapper -->
      <!-- Main Footer -->
      <?php
      include('./class_lib/main_footer.php');
      ?>
      </div>
</div>
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
    </div>
    

    <script>
      var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>"; 
    var permisos = "<?php echo $_SESSION['permisos'] ?>"; 
    func_check(id_usuario, permisos.split(","));
    </script>
   
  </body>
</html>