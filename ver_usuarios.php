<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>

<html>

<head>
  <title>Usuarios</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <link rel="stylesheet" href="plugins/select2/select2.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_usuarios.js?v=<?php echo $version ?>"></script>

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
            Usuarios

          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Usuarios</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-6">
              <div class="d-flex flex-row">
                <label for="select_tipo" class="control-label">Tipo de Usuario:</label>
                <select id="select_tipo" class="selectpicker mobile-device ml-3 w-75" title="Selecciona Tipo de Usuario"
                  data-style="btn-info" onChange="busca_usuarios(this.value);">
                  <option value="0">Clientes</option>
                  <option value="1">Trabajadores de Roelplant</option>
                </select>
              </div>
            </div>
            <div class="col text-lg-right text-center">
              <button class="btn btn-success btn-round fa fa-plus-square" style="font-size: 1.3em"
                onclick="MostrarModalAgregarUsuario();"></button>
            </div>
          </div>



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

      <div id="ModalAgregarUsuario" class="modal">
        <div class="modal-usuarios">
          <div class='box box-primary'>
            <div class='box-header with-border'>
              <h3 id='titulo' class='box-title'>Agregar Usuario</h3>
            </div>
            <div class='box-body'>
              <div class='form-group form-tipousuario'>

                <label for="select_tipousuario" class="control-label">Tipo Usuario:</label>

                <select id="select_tipousuario" class="selectpicker mobile-device" title="Selecciona Tipo de Usuario"
                  data-style="btn-info" data-dropup-auto="false" data-size="6" data-width="100%">
                  <option value="cliente">Cliente</option>
                  <option value="trabajador">Trabajador/a de Roelplant</option>
                </select>
              </div>
              <div class='form-group form-cliente d-none'>

                <label for="select_cliente" class="control-label">Cliente:</label>

                <select id="select_cliente" class="selectpicker mobile-device" title="Selecciona Cliente"
                  data-style="btn-info" data-live-search="true" data-dropup-auto="false" data-size="6"
                  data-width="100%">

                </select>
              </div>
              <div class="form-usuario d-none">
                <div class='form-group'>
                  <div>
                    <label for="username_txt" class="control-label">Nombre de Usuario:</label>
                  </div>
                  <div>
                    <form autocomplete="off" method="post" action="">
                      <input autocomplete="false" maxlength="20" type="search" id="username_txt"
                        style="text-transform:lowercase !important;" class="form-control">
                    </form>
                  </div>
                </div>

                <div class='form-group'>
                  <label for="nombre_txt" class="control-label">Nombre Real:</label>

                  <input autocomplete="false" maxlength="30" type="search" id="nombre_txt"
                    style="text-transform:capitalize !important;" class="form-control">


                </div>
              </div>
              <div class='form-group form-email d-none'>
                <div>
                  <label for="input-email" class="control-label">E-Mail:</label>
                </div>
                <div>
                  <form autocomplete="off" method="post" action="">
                    <input autocomplete="false" maxlength="50" type="search" autocomplete="off" id="input-email"
                      style="text-transform:lowercase !important;" class="form-control">
                  </form>
                </div>
              </div>

              <div class='form-group'>
                <div>
                  <label for="password_txt" class="control-label">Contraseña:</label>
                </div>
                <div>
                  <form autocomplete="new-password" method="post" action="">
                    <input autocomplete="new-password" maxlength="20" type="password" id="password_txt"
                      class="form-control">
                  </form>
                </div>
              </div>

              <div class='form-group'>
                <div>
                  <label for="password2_txt" class="control-label">Repita Contraseña:</label>
                </div>
                <div>
                  <form autocomplete="new-password" method="post" action="">
                    <input autocomplete="new-password" maxlength="20" type="password" id="password2_txt"
                      class="form-control">
                  </form>
                </div>
              </div>

              <div class='form-group form-permisos d-none'>

                <label for="select_permisos" class="control-label">Permisos:</label>

                <select id="select_permisos" class="selectpicker mobile-device" title="Selecciona los Permisos"
                  data-style="btn-info" data-dropup-auto="false" data-size="6" data-width="100%" multiple>
                  <option value="pedidos">Cargar/Ver Pedidos</option>
                  <option value="planificacionpedidos">Planificación de Pedidos</option>
                  <option value="historialentrega">Historial de Entregas</option>
                  <option value="mesadas">Mesadas</option>
                  <option value="estadisticas">Estadísticas</option>
                  <option value="stock">Stock Bandejas</option>
                  <option value="semillas">Semillas</option>
                  <option value="tendencias">Tendencias</option>
                  <option value="productos">Productos</option>
                  <option value="panel">Panel de Control</option>
                  <option value="cotizaciones">Cotizaciones</option>
                  <option value="facturacion">Facturación</option>
                  <option value="integracion">Integración</option>
                  <option value="informes">Informes</option>
                  <option value="inventario">Inventario</option>
                  <option value="viveros">Viveros</option>
                  <option value="reservas">Reservas</option>
                  <option value="vivero">Seguimiento Vivero</option>
                  <option value="laboratorio">Laboratorio</option>
                  <option value="seguimiento_produccion">Mi Producción (Trabajadores)</option>
                </select>

              </div>

              <div style="margin-top: 100px" align="right">
                <button type="button" class="btn btn-modal-bottom fa fa-close" id="btn_cancel"
                  onClick="CerrarModal();"></button>
                <button type="button" class="btn btn-modal-bottom ml-3 fa fa-save" id="btn-guardar-usuario"></button>
              </div>

            </div>

          </div>

        </div> <!-- MODAL FIN -->
        <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
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