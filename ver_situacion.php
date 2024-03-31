<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Situación Clientes</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <link rel="stylesheet" href="plugins/select2/select2.min.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
    <script src="dist/js/ver_situacion.js"></script>
    <script src="dist/js/check_permisos.js"></script>
  </head>

  <body onload="pone_clientes();pone_tipos();$('#select_tipofecha').selectpicker('val', [0]); ">

    <div id="miVentana">

    </div>

    

    <div id="ocultar">

    <div class="wrapper">



      <!-- Main Header -->

      <header class="main-header">



        <!-- Logo -->

        <?php

        include('class_lib/nav_header.php');

        ?>



      </header>

      <!-- Left side column. contains the logo and sidebar -->

      <aside class="main-sidebar">



        <!-- sidebar: style can be found in sidebar.less -->

        <?php

        include('class_lib/sidebar.php');





        ?>

        <!-- /.sidebar -->

      </aside>



      <!-- Content Wrapper. Contains page content -->

      <div class="content-wrapper">

        <!-- Content Header (Page header) -->

        <section class="content-header">

          <h1>

            Situación Clientes

          </h1>

          <ol class="breadcrumb">

            <li><a href="inicio.php"> Inicio</a></li>

            <li class="active">Situación Clientes</li>

          </ol>

        </section>



        <!-- Main content -->

        <section class="content">

          <div class='row mt-2'>
            <div class='col-md-5'>
            <div class='box box-primary'>
            <div class='box-header with-border'>
            <h3 class='box-title'>Buscar</h3>
            </div>
            <div class='box-body'>
                <div class="form-group row">
                  <label for="select_cliente" class="col-md-2 col-form-label">Cliente:</label>

                  <div class="col-md-7">
                    <select id="select_cliente" class="selectpicker mobile-device" title="Selecciona un Cliente" data-style="btn-info" data-live-search="true" data-width="100%" data-dropup-auto="false" data-size="10"></select>
                  </div>
                </div>


                <div class="form-group row">
                  <label class="col-md-2 col-form-label">Fechas:</label>

                  <div class="col-md-6">
                    <div class="input-group">
                            <button class="btn btn-default pull-left" id="daterange-btn">
                              <i class="fa fa-calendar"></i> Seleccionar...
                              <i class="fa fa-caret-down"></i>
                            </button>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <button class='btn btn-primary pull-right' onclick='busca_entradas($("#select_cliente").find("option:selected").val());' id='btn-busca'><i class='fa fa-search'></i> Buscar...</button>
                  </div>
                  <span class='fe'></span>
                      <input type='hidden'  class='form-control' id='fi' value=''>
                      <input type="hidden"  class='form-control' id='ff' value=''>
                </div>
              
              </div>
            </div>
          </div> <!--FIN COL-->

          <div class='col-md-7 col-balance'>
          
          </div> <!--FIN COL-->

</div>

<div class="row mb-5">
  <div class="col">
    <div id='tabla_entradas'></div>
  </div>

</div>
      
      </div>
    
</section><!-- /.content -->
</div><!-- /.content-wrapper -->
      <!-- Main Footer -->
      <?php
      include('class_lib/main_footer.php');
      ?>

      <div id="ModalVerPagos" class="modal">
          <!-- Modal content -->
          <div class="modal-verpagos" style="overflow-y: auto;">
           <div class='box box-primary'>
           <div class='box-header with-border'>
           <div class="col-md-10">
           <h3 class='box-title'>Historial de Pagos</h3>
           </div>
           <div class="col text-right">
            <button type="button" class="btn fa fa-close btn-modal-top" id="btn_cancel" onClick="cerrarModalVerPagos();"></button>
          </div>
           </div>
          <div class='box-body body-pagos'>
          
          </div>
        </div>
        </div>
      </div>


      <div id="ModalPagos" class="modal">
          <!-- Modal content -->
          <div class="modalpago-content">
           <div class='box box-primary'>
           <div class='box-header with-border'>
           <div class="col-md-10">
           <h3 class='box-title'>Agregar Pago</h3>
           </div>
           <div class="col text-right">
            <button type="button" class="btn fa fa-close btn-modal-top" id="btn_cancel" onClick="cerrarModalPagos();"></button>
          </div>
           </div>
          <div class='box-body'>
                <div class='form-group'>
                  <div class='row'>
                    <div class='col-md-12'>
                    <label for="input_pago" class="control-label">Monto ($):</label>
                    <input type="text" style="font-size: 1.5em" class="form-control numeric-only" id="input_pago" value="" maxlength="10"> 
                   </div>
                  </div>
                </div>
                <div class='form-group'>
                  <div class='row'>
                    <div class='col-md-12'>
                    <label for="input_concepto" class="control-label">Concepto:</label>
                    <input class="form-control" type="text" id="input_concepto" value="" maxlength="50" style="text-transform: uppercase;"> 
                    </div>
                  </div>
                </div>
                <div align="right">
                  <button class="btn btn-success mt-3 font-weight-bold" onClick="agregarPago()">AGREGAR</button>
                </div>

                
        </div>
        </div>
        </div>
      </div> <!-- MODAL PAGOS FIN -->


      <div id="ModalDeudas" class="modal">
          <!-- Modal content -->
          <div class="modalpago-content">
           <div class='box box-primary'>
           <div class='box-header with-border'>
           <div class="col-md-10">
           <h3 class='box-title'>Agregar Deuda</span></h3>
           </div>
           <div class="col text-right">
            <button type="button" class="btn fa fa-close btn-modal-top" id="btn_cancel" onClick="cerrarModalDeudas();"></button>
          </div>
           </div>
          <div class='box-body'>
                <div class='form-group'>
                  <div class='row'>
                    <div class='col-md-12'>
                    <label for="input-monto-deuda" class="control-label">Monto ($):</label>
                    <input type="text" style="font-size: 1.5em" class="form-control numeric-only" id="input-monto-deuda" value="" maxlength="10"> 
                   </div>
                  </div>
                </div>
                <div class='form-group'>
                  <div class='row'>
                    <div class='col-md-12'>
                    <label for="input-obs-deuda" class="control-label">Observaciones:</label>
                    <input class="form-control" type="text" id="input-obs-deuda" value="" maxlength="50" style="text-transform: uppercase;"> 
                    </div>
                  </div>
                </div>
                <div align="right">
                  <button class="btn btn-danger mt-3 font-weight-bold" onClick="agregarDeuda()">AGREGAR</button>
                </div>
        </div>
        </div>
        </div>
      </div> <!-- MODAL PAGOS FIN -->
      
      <!-- Add the sidebar's background. This div must be placed

           immediately after the control sidebar -->

      <div class="control-sidebar-bg"></div>

    </div><!-- ./wrapper -->

  

  </div> <!-- ID OCULTAR-->

  



    <style>

              .table2 tr.selected td {

                background-color: #333;

                color: #fff;    

              }

              .table2 tr.selected2 td {

                background-color: #333;

                color: #fff;    

              }

   



                            

            </style> 

    
    <script src="plugins/moment/moment.min.js"></script>

    <script src="plugins/daterangepicker/daterangepicker.js"></script>

    <script type="text/javascript">

      var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>"; 

       var permisos = "<?php echo $_SESSION['permisos'] ?>"; 

       func_check(id_usuario, permisos.split(","));



       $(document).ready(function () {

        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
          $('.selectpicker').selectpicker('mobile');
        }
        else {
          var elements = document.querySelectorAll('.mobile-device'); 
          for(var i = 0; i < elements.length; i++)
          {
              elements[i].classList.remove('mobile-device');
          }
          $('.selectpicker').selectpicker({});
        }

        $('#busca_tiposolucion').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
        $("#busca_tiporevision").val('default').selectpicker("refresh");
        });
        $('#busca_tiporevision').on('changed.bs.select', function (e, clickedIndex, newValue, oldValue) {
        $("#busca_tiposolucion").val('default').selectpicker("refresh");
        });

        $.datepicker.setDefaults($.datepicker.regional["es"]);

        

      });

        

      

    </script>

  </body>

</html>