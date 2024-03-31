<?php include "./class_lib/sesionSecurity.php";
    if(!isset($_SESSION["id_usuario"]) || (int)$_SESSION["id_usuario"] != 1){
        header("Location: index.php");
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Clicks Control Vivero</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    
    <script src="dist/js/ver_clicks.js?v=<?php echo $version ?>"></script>
    
    
  </head>
  <body>
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
            Clicks Control Vivero
            
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Clicks Control Vivero</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->
          <div class='row mt-2 mb-5'>
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

      
      
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
  </div> <!-- MODAL FIN -->
    
    
    
  </body>
</html>