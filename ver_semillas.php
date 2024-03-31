<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Semillas</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <link rel="stylesheet" href="plugins/select2/select2.min.css">
  <script src="dist/js/ver_semillas.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
</head>

<body>
  <div id="miVentana"></div>

  <div id="ocultar">
    <div class="wrapper">
      <header class="main-header">
        <?php include('class_lib/nav_header.php');?>
      </header>
      <aside class="main-sidebar">
        <?php include('class_lib/sidebar.php');?>
      </aside>

      <div class="content-wrapper">
        <section class="content-header">
          <h1>Semillas <button style="display:inline-block" class="btn btn-success fa fa-plus-square ml-3"
              onclick="modalAgregarSemillas();"></button></h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Semillas</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col">
              <div class="tab">
                <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'stock');">STOCK ACTUAL</button>  
                <button class="tablinks" onclick="abrirTab(event, 'ingresos');">INGRESOS</button>
                <button class="tablinks" onclick="abrirTab(event, 'egresos');">EGRESOS</button>
                
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

    <?php include("modal_semillas.php");?>

    <!-- Main Footer -->
    <?php include('class_lib/main_footer.php');?>

    <div class="control-sidebar-bg"></div>
  </div>

  </div>

  
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