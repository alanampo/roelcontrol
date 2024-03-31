<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Reservas y Stock</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_reservas.js?v=<?php echo $version ?>"></script>
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
            Reservas y Stock
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Reservas y Stock</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->

          <div class="row">
            <div class="col">
              <div class="tab">
              <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'reservas');">RESERVAS</button>
                <button  class="tablinks" onclick="abrirTab(event, 'actual');">PRODUCTOS EN
                  STOCK</button>
                
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

    <div id="modal-entregar-reserva" class="modal">
      <div class="modal-entregar-reserva">
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h3 class='box-title'></h3>
            <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class='box-body'>

          <div class="row">
            <div class="col-md-4 form-group">
              <label class="col-form-label" for="input-cantidad">Cantidad a Entregar:</label>
              <input type="search" autocomplete="off" class="form-control" name="input-cantidad" id="input-cantidad"
                maxlength="20" />
            </div>
            <div class="col-md-4">

            </div>
            <div class="col-md-4 form-group">
              <label class="col-form-label text-primary" for="input-cantidad-disponible">Cantidad Disponible:</label>
              <input type="search" autocomplete="off" class="form-control font-weight-bold" name="input-cantidad-disponible" id="input-cantidad-disponible"
                maxlength="20" readonly />
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="col-form-label" for="input-comentario">Comentario:</label>
              <input type="search" autocomplete="off" class="form-control" name="input-comentario"
                id="input-comentario" maxlength="100" />
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <button onclick="guardarEntrega()" class="btn btn-success pull-right"><i class="fa fa-save"></i> CONFIRMAR ENTREGA</button>
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


</body>

</html>