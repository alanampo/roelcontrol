<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Bandejas en Stock</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <link rel="stylesheet" href="plugins/select2/select2.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
  <script src="dist/js/ver_stock_bandejas.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/check_permisos.js"></script>
</head>

<body>
  <div id="miVentana"></div>

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
        <?php include('class_lib/sidebar.php');?>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Bandejas en Stock <button style="display:inline-block" class="btn btn-success fa fa-plus-square ml-3" onclick="modalAgregarBandejas();"></button>
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Bandejas en Stock</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->
          
          <div class="row">
                <div class="col">
                  <div class="tab">
                  <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'actual');">STOCK ACTUAL</button>
                    <button class="tablinks" onclick="abrirTab(event, 'historial');"
                      >HISTORIAL</button>
                    
                    
                  </div>
                </div>
              </div>

          <div class="row mt-3">
            <div class="col">
              <div id='tabla_entradas'></div>
            </div>
          </div>
      </div>

      </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <div id="modal-agregar-bandejas" class="modal">
      <div class="modal-content" style="height: 98%">
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h3 class='box-title'>Agregar Bandejas a Stock</h3>
          </div>
        </div>
        <div class='box-body'>
          <div class='form-group'>
            <div class="row">
              <div class="col-md-6">
                <label for="select-bandeja" class="control-label">Tipo de Bandeja:</label>
                <select id="select-bandeja" title="Bandeja" class="selectpicker" data-style="btn-info"
                  data-width="100%">
                  <option value="288">288</option>
                  <option value="200">200</option>
                  <option value="162">162</option>
                  <option value="128">128</option>
                  <option value="105">105</option>
                  <option value="72">72</option>
                  <option value="50">50</option>
                  <option value="25">25</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="select-condicion" class="control-label">Condición:</label>
                <select id="select-condicion" title="Condición" class="selectpicker" data-style="btn-info"
                  data-width="100%">
                  <option value="nuevas">NUEVAS</option>
                  <option value="usadas">USADAS</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-md-6">
                <label class="control-label">Cantidad Bandejas:</label>
                <input maxLength="5" max="99999" style="font-size: 1.2em" type="number" min="0" step="1" id="cantidad-bandejas"
                  class="form-control text-right">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col">
              <div class="d-flex flex-row justify-content-end">
                <button type="button" class="btn btn-modal-bottom fa fa-close" id="btn_cancel"
                  onClick="$('#modal-agregar-bandejas').modal('hide')"></button>
                <button type="button" class="btn btn-modal-bottom ml-2 fa fa-save" id="btn_guardarcliente"
                  onClick="guardarBandejas();"></button>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- MODAL FIN -->
    </div>

    <!-- Main Footer -->
    <?php
      include('class_lib/main_footer.php');
      ?>


    <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
  </div><!-- ./wrapper -->

  </div> <!-- ID OCULTAR-->

  <!-- REQUIRED JS SCRIPTS -->
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/daterangepicker/daterangepicker.js"></script>

  <script type="text/javascript">
    $(document).ready(function () {
      const id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
      const permisos = "<?php echo $_SESSION['permisos'] ?>";
      func_check(id_usuario, permisos.split(","));

      $('.selectpicker').selectpicker();
    });
  </script>
</body>
</html>