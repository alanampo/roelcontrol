<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Historial de Entregas</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <link rel="stylesheet" href="plugins/select2/select2.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
  <style>
    .table-historial tr.selected td {
      background-color: #333;
      color: #fff;    
    }
  </style>

  <script src="dist/js/ver_seguimiento.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_historial.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
</head>

<body onload="busca_entradas();pone_tipos();">
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
            Historial de Entregas
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Historial de Entregas</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->
          <div class='row'>
            <div class='col-md-5'>
              <div class='box box-primary'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Buscar</h3>
                  <button class='btn btn-primary pull-right' onclick='expande_busqueda()' id='btn-busca'><i
                      class='fa fa-caret-down'></i> Busqueda Avanzada</button>
                </div>
                <div class='box-body'>
                  <div class="form-group">
                    <div class='row'>
                      <div class='col-md-2'>
                        <label>Fechas:</label>
                      </div>
                      <div class='col-md-8'>
                        <div class="input-group">
                          <button class="btn btn-default pull-left" id="daterange-btn">
                            <i class="fa fa-calendar"></i> Seleccionar...
                            <i class="fa fa-caret-down"></i>
                          </button>
                        </div>
                      </div>
                      <div class='col-md-2'>
                        <button class='btn btn-primary pull-right' onclick='busca_entradas();' id='btn-busca'><i
                            class='fa fa-search'></i> Buscar...</button>
                      </div>
                    </div>

                    <span class='fe'></span>
                    <input type='hidden' class='form-control' id='fi' value=''>
                    <input type="hidden" class='form-control' id='ff' value=''>
                  </div><!-- /.form group -->

                  <div id="contenedor_busqueda" style="display:none">

                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-2'>
                          <label>Producto:</label>
                        </div>
                        <div class='col-md-5'>
                          <select id="select_tipo" class="selectpicker mobile-device" title="Tipo" data-style="btn-info"
                            data-dropup-auto="false" data-size="5" data-width="100%" multiple></select>
                        </div>
                      </div>
                    </div>


                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-2'>
                          <label>Variedad:</label>
                        </div>
                        <div class='col-md-5'>
                          <div class="btn-group" style="width:100%">
                            <input id="busca_variedad" style="text-transform:uppercase" type="search"
                              class="form-control">
                            <span id="searchclear" onClick="$('#busca_variedad').val('');"
                              class="glyphicon glyphicon-remove-circle"></span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-2'>
                          <label>Cliente:</label>
                        </div>
                        <div class='col-md-5'>
                          <div class="btn-group" style="width:100%">
                            <input id="busca_cliente" style="text-transform:uppercase;" type="search"
                              class="form-control">
                            <span id="searchclear" onClick="$('#busca_cliente').val('');"
                              class="glyphicon glyphicon-remove-circle"></span>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div> <!-- CONTENEDOR BUSQUEDA -->






                </div>
              </div>
            </div>


            <div class='col text-right'>
              <button type="button" class="btn btn-primary btn-round fa fa-print" id="btn_printcliente"
                onClick="print_Busqueda(1);"> IMPRIMIR</button>
            </div>


          </div> <!-- FIN ROW -->


          <div class="row mb-5">
            <div class='col'>
              <div id='tabla_entradas'></div>
            </div>
          </div>


        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->


      <!-- Main Footer -->

      <?php
      include('class_lib/main_footer.php');
      ?>



      <?php include("./modal_ver_estado.php"); ?>
      

      <div id="ModalRegistroEntregas" class="modal">
        <div class="modal-content-verpedido">
          <div class='box box-primary'>
            <div class='box-header with-border'>
              <h4 class="box-title">Entregas del Producto: <span class="title-pedido"></span>
              </h4>
              <button style="float:right;font-size: 1.6em" class="btn fa fa-close"
                onClick="$('#ModalRegistroEntregas').modal('hide')"></button>
            </div>
            <div id="tablita">
              <div class='box-body'>
                <div class="row mt-2 mb-2">
                  <div class="col text-center">
                    <h5 class="text-primary font-weight-bold">Registro de Entregas:</h5>
                  </div>
                </div>
                <div class="row">
                  <div class="col">
                    <table class="table tabla-entregas table-responsive w-100 d-block d-md-table">
                      <thead class="thead-dark">
                        <tr class="text-center">
                          <th scope="col">Fecha</th>
                          <th scope="col">Cantidad</th>
                          <th scope="col">Tipo Entrega</th>
                        </tr>
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
      </div> <!-- MODAL FIN -->




      <div class="control-sidebar-bg"></div>


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
            for (var i = 0; i < elements.length; i++) {
              elements[i].classList.remove('mobile-device');
            }
            $('.selectpicker').selectpicker({});
          }
        });

      </script>
</body>

</html>