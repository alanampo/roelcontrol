<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Bonos de Producción</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="js/charts.min.js"></script>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_bonos_produccion.js?v=<?php echo $version ?>"></script>
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
            Bonos de Producción</button>
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Bonos de Producción</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->

          <div class="row">
            <div class="col">
              <div class="tab">
              <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'tabla');">TABLA</button>
                <button class="tablinks" onclick="abrirTab(event, 'grafico');"
                  >GRÁFICO PRODUCCIÓN</button>
                <button class="tablinks" onclick="abrirTab(event, 'graficobonos');"
                  >GRÁFICO BONOS</button>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-2">
              <select id="select-anio" class="selectpicker" title="Año" data-style="btn-info"
                  data-dropup-auto="false" data-width="100%">
              </select>
            </div>
          </div>


          <div class="row mt-3 tab-tabla d-none tabco">
            <div class="col">
              <div id='tabla_entradas'></div>
            </div>
          </div>

          <div class="row mt-3 tab-grafico d-none tabco">
            <div class="col">
              <div class='box box-primary'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Gráfico Producción</h3>
                </div>
                <div class='box-body chart-container mb-5' style="min-height:75vh;"></div>
              </div>
              
            </div>
          </div>

          <div class="row mt-3 tab-grafico-bonos d-none tabco">
            <div class="col">
              <div class='box box-primary'>
                <div class='box-header with-border'>
                  <h3 class='box-title'>Gráfico Bonos</h3>
                </div>
                <div class='box-body chart-container2 mb-5' style="min-height:75vh;"></div>
              </div>
              
            </div>
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