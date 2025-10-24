<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Seguimiento de Producción</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_seguimiento_produccion.js?v=<?php echo $version ?>"></script>
  <style>
    /* Estilos específicos para toastr en este módulo */
    #toast-container > .toast-success {
      background-color: #51A351 !important;
      color: #FFFFFF !important;
    }
    #toast-container > .toast-error {
      background-color: #BD362F !important;
      color: #FFFFFF !important;
    }
    #toast-container > .toast-info {
      background-color: #2F96B4 !important;
      color: #FFFFFF !important;
    }
    #toast-container > .toast-warning {
      background-color: #F89406 !important;
      color: #FFFFFF !important;
    }
  </style>
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
            Seguimiento de Producción
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Seguimiento de Producción</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->

          <div class="row">
            <div class="col-md-4">
              <select id="select-usuario" class="selectpicker" title="Seleccionar Usuario" data-style="btn-info"
                  data-dropup-auto="false" data-width="100%" data-live-search="true">
              </select>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12 text-center">
              <button id="btn-mes-anterior" class="btn btn-default">
                <i class="fa fa-chevron-left"></i>
              </button>
              <h3 class="d-inline-block mx-3" id="label-mes-anio"></h3>
              <button id="btn-mes-siguiente" class="btn btn-default">
                <i class="fa fa-chevron-right"></i>
              </button>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-12">
              <div id='tabla_produccion'></div>
            </div>
          </div>

      </section><!-- /.content -->
    </div><!-- /.content-wrapper -->


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
