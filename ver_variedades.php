<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Variedades</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <link rel="stylesheet" href="plugins/select2/select2.min.css" />
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_variedades.js?v=<?php echo $version ?>"></script>
  </head>

  <body
    onload="chequear_permisos();busca_productos(null);pone_tipos();"
  >
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
              Variedades
            </h1>
            <ol class="breadcrumb">
              <li><a href="inicio.php"> Inicio</a></li>
              <li class="active">Variedades</li>
            </ol>
          </section>
          <!-- Main content -->
          <section class="content">
            <div class="row">
              <div class="col-6">
                <div class="d-flex flex-row">
                  <label for="select_tipo" class="control-label"
                    >Filtrar por:</label
                  >
                  <select
                    id="select_tipo"
                    class="selectpicker mobile-device ml-3 w-75"
                    title="Selecciona Tipo de Producto"
                    data-style="btn-info"
                    data-live-search="true"
                    onChange="busca_productos(this.value);"
                  ></select>
                </div>
              </div>
              <div class="col text-right">
                <button
                  class="btn btn-success btn-round fa fa-plus-square"
                  style="font-size: 1.6em"
                  onclick="pone_tipos();MostrarModalAgregarProducto(null);"
                ></button>
              </div>
            </div>

            <!-- Your Page Content Here -->

            <div class="row mt-2">
              <div class="col">
              <button
                  class="btn btn-sm btn-primary"
                  onclick="exportarVariedades()"
                ><i class="fa fa-save"></i> EXPORTAR A CSV</button>
              </div>
            </div>
            <div class="row mt-2 mb-5">
              <div class="col">
                <div id="tabla_entradas"></div>
              </div>
            </div>
          </section>
          <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <!-- Main Footer -->
        <?php
      include('class_lib/main_footer.php');
      ?>

        <!-- Add the sidebar's background. This div must be placed

           immediately after the control sidebar -->

        <div class="control-sidebar-bg"></div>
      </div>
      <!-- ./wrapper -->
    </div>

    <div id="ModalAgregarProducto" class="modal">
      <div class="modal-tipo">  
      <div class="box box-primary">
          <div class="box-header with-border">
            <h3 id="titulo" class="box-title">Agregar Variedad</h3>
          </div>
          <div class="box-body">
            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label">Tipo de Producto:</label>
                  <select
                    id="select_tipo2"
                    class="selectpicker mobile-device w-100"
                    title="Tipo de Producto"
                    data-style="btn-info"
                    data-live-search="true"
                    data-width="100%"
                  ></select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label"
                    >Nombre de la Variedad:</label
                  >
                  <input
                    type="search"
                    autocomplete="off"
                    id="input-nombre"
                    maxLength="50"
                    style="text-transform: uppercase"
                    class="form-control"
                    placeholder="Ingresa el Nombre"
                  />
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label"
                    >Código/ID: <span class='label-codigo text-primary font-weight-bold'></span></label
                  >
                  <input
                    type="search"
                    autocomplete="off"
                    id="input-codigo"
                    maxLength="6"
                    style="text-transform: uppercase"
                    class="form-control"
                    placeholder="SOLO NÚMEROS"
                  />
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label">Precio:</label>
                  <input
                    type="search"
                    autocomplete="off"
                    id="input-precio"
                    maxLength="9"
                    style="font-weight: bold;"
                    class="form-control"
                    placeholder="0.00"
                  />
                </div>
              </div>
            </div>

            <div class="form-group form-dias-produccion-variedad d-none">
              <div class="row">
                <div class="col">
                  <label class="control-label" for="dias-produccion-variedad">Días de Producción</label>
                  <div class="select-editable">
                    <select class="form-control" id="select-dias-produccion" onchange="this.nextElementSibling.value=this.value;">
                      <option class="option" value="0">PERSONALIZADO</option>
                      <option class="option" value="30">30 (1 mes)</option>
                      <option class="option" value="60">60 (2 meses)</option>
                      <option class="option" value="90">90 (3 meses)</option>
                      <option class="option" value="120">120 (4 meses)</option>
                      <option class="option" value="150">150 (5 meses)</option>
                      <option class="option" value="180">180 (6 meses)</option>
                    </select>
                    <input type="search" autocomplete="off" maxlength="3" class="form-control" id="dias-produccion-variedad" type="search" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div align="right">
              <button
                type="button"
                class="btn fa fa-close btn-modal-bottom"
                onClick="CerrarModalProducto();"
              ></button>
              <button
                type="button"
                class="btn fa fa-save btn-modal-bottom ml-2"
                onClick="GuardarProducto();"
              ></button>
            </div>
          </div>
        </div>
        </div>
     </div>
    <!-- MODAL FIN -->

    <!-- REQUIRED JS SCRIPTS -->

    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <script type="text/javascript">
      $(document).ready(function () {
        if (
          /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)
        ) {
          $(".selectpicker").selectpicker("mobile");
        } else {
          var elements = document.querySelectorAll(".mobile-device");
          for (var i = 0; i < elements.length; i++) {
            elements[i].classList.remove("mobile-device");
          }
          $(".selectpicker").selectpicker({});
        }
      });

      function chequear_permisos() {
        var permisos = "<?php echo $_SESSION['permisos'] ?>";
      }

      var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
      var permisos = "<?php echo $_SESSION['permisos'] ?>";
      func_check(id_usuario, permisos.split(","));
    </script>
  </body>
</html>
