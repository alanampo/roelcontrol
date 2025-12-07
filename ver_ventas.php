<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Ventas y Stock</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_ventas.js?v=<?php echo $version ?>"></script>
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
        <?php include('class_lib/sidebar.php'); ?>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Ventas y Stock
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Ventas y Stock</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->

          <div class="row">
            <div class="col">
              <div class="tab">
                <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'reservas');">VENTAS</button>
                <button class="tablinks" onclick="abrirTab(event, 'picking');">PICKING</button>
                <button class="tablinks" onclick="abrirTab(event, 'packing');">PACKING</button>
                <button class="tablinks" onclick="abrirTab(event, 'en_transporte');">EN TRANSPORTE</button> <!-- NEW -->
                <button class="tablinks" onclick="abrirTab(event, 'entregadas');">ENTREGADAS</button>
                <button class="tablinks" onclick="abrirTab(event, 'actual');">PRODUCTOS EN
                  STOCK</button>

              </div>
            </div>
          </div>

          <div class="row mt-3" id="filtro-estado-reservas">
            <div class="col-md-4">
              <label for="select-estado-reserva" class="control-label">Filtrar por Estado:</label>
              <select id="select-estado-reserva" class="selectpicker" multiple data-actions-box="true" data-live-search="true" title="Seleccione estados..." data-width="100%">
                <option value="-1">CANCELADA</option>
                <option value="0">PAGO ACEPTADO</option>
                <option value="1">EN PROCESO</option>
                <option value="2">ENTREGADA</option>
                <option value="3">EN REVISIÓN</option>
              </select>
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
              <input type="search" autocomplete="off" class="form-control font-weight-bold"
                name="input-cantidad-disponible" id="input-cantidad-disponible" maxlength="20" readonly />
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="col-form-label" for="input-comentario-entrega">Comentario:</label>
              <input type="search" autocomplete="off" class="form-control" name="input-comentario-entrega" id="input-comentario-entrega"
                maxlength="100" />
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <button onclick="guardarEntrega()" class="btn btn-success pull-right"><i class="fa fa-save"></i> CONFIRMAR
                ENTREGA</button>
            </div>
          </div>


        </div>
      </div> <!-- MODAL FIN -->



    </div>
    
    <div id="modal-reservar" class="modal">
      <div class="modal-reservar">
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h3 class='box-title'>Generar Venta</h3>
            <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class='box-body'>
          <div class="row">
            <div class="col-md-6">
              <label for="select-cliente" class="control-label">Cliente:</label>
              <select id="select-cliente" data-size="10" data-live-search="true" title="Cliente" class="selectpicker"
                data-style="btn-info" data-width="100%">
              </select>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-5 form-group">
                <label class="col-form-label" for="select-producto-reserva">Producto:</label>
                <select id="select-producto-reserva" data-size="10" data-live-search="true" title="Producto" class="selectpicker" data-style="btn-primary" data-width="100%">
                </select>
            </div>
            <div class="col-md-3 form-group">
              <label class="col-form-label" for="input-cantidad-reserva">Cantidad a Reservar:</label>
              <input type="number" placeholder="Plantas" autocomplete="off" class="form-control" name="input-cantidad"
                id="input-cantidad-reserva" maxlength="20" />
            </div>
            <div class="col-md-2 form-group">
                <label class="col-form-label text-primary" for="input-cantidad-disponible2">Disponible:</label>
                <input type="text" autocomplete="off" class="form-control font-weight-bold"
                           name="input-cantidad-disponible2" id="input-cantidad-disponible2" maxlength="20" readonly />
            </div>
            <div class="col-md-2">
                <label class="col-form-label">&nbsp;</label>
                <button onclick="agregarProductoReserva()" class="btn btn-primary btn-block"><i class="fa fa-plus"></i> AÑADIR</button>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-12">
                <table id="tabla-productos-reserva" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="col-form-label" for="input-comentario-reserva">Observaciones:</label>
              <textarea class="form-control" name="input-comentario-reserva" id="input-comentario-reserva" rows="3"></textarea>
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <button onclick="guardarReserva()" class="btn btn-success pull-right"><i class="fa fa-save"></i> CONFIRMAR
                VENTA</button>
            </div>
          </div>


        </div>
      </div> <!-- MODAL FIN -->
    </div>


    <div id="modal-edit-stock" class="modal">
      <div id="modal-edit-stockContent" class="modal-content-verpedido">
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h4 class='box-title'>Editar Stock</h4>
            <button class="btn fa fa-close pull-right btn-modal-top"
              onClick="$('#modal-edit-stock').css({display:'none'});"></button>
          </div>
          <div id="tablita">
            <div class='box-body'>
              <table id="tabla-editar-stock" class="table table-bordered table-responsive w-100 d-block d-md-table"
                role="grid">
                <thead>
                  <tr role="row">
                    <th class="text-center">Orden</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Cantidad<br>Plantas</th>
                    <th class="text-center">Cliente</th>
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- FIN MODAL VER -->

    <div id="modal-editar-observacion" class="modal">
      <div class="modal-entregar-reserva"> <!-- Reusing modal-entregar-reserva styling for simplicity -->
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h3 class='box-title'>Editar Observación General</h3>
            <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal" aria-label="Close" onclick="$('#modal-editar-observacion').css({display:'none'});">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class='box-body'>
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="col-form-label" for="textarea-observacion-general">Observación:</label>
              <textarea class="form-control" name="textarea-observacion-general" id="textarea-observacion-general" rows="5"></textarea>
              <input type="hidden" id="hidden-id-reserva-observacion">
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <button onclick="guardarObservacionGeneral()" class="btn btn-success pull-right"><i class="fa fa-save"></i> GUARDAR</button>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- FIN MODAL EDITAR OBSERVACION -->

    <div id="modal-editar-observacion-picking" class="modal">
      <div class="modal-entregar-reserva"> <!-- Reusing modal-entregar-reserva styling for simplicity -->
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h3 class='box-title'>Editar Observación de Picking</h3>
            <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal" aria-label="Close" onclick="$('#modal-editar-observacion-picking').css({display:'none'});">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class='box-body'>
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="col-form-label" for="textarea-observacion-picking">Observación Picking:</label>
              <textarea class="form-control" name="textarea-observacion-picking" id="textarea-observacion-picking" rows="5"></textarea>
              <input type="hidden" id="hidden-id-reserva-observacion-picking">
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <button onclick="guardarObservacionPicking()" class="btn btn-success pull-right"><i class="fa fa-save"></i> GUARDAR</button>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- FIN MODAL EDITAR OBSERVACION PICKING -->

    <div id="modal-editar-observacion-packing" class="modal">
      <div class="modal-entregar-reserva"> <!-- Reusing modal-entregar-reserva styling for simplicity -->
        <div class='box box-primary'>
          <div class='box-header with-border'>
            <h3 class='box-title'>Editar Observación de Packing</h3>
            <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal" aria-label="Close" onclick="$('#modal-editar-observacion-packing').css({display:'none'});">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class='box-body'>
          <div class="row">
            <div class="col-md-12 form-group">
              <label class="col-form-label" for="textarea-observacion-packing">Observación Packing:</label>
              <textarea class="form-control" name="textarea-observacion-packing" id="textarea-observacion-packing" rows="5"></textarea>
              <input type="hidden" id="hidden-id-reserva-observacion-packing">
            </div>
          </div>
          <div class="row mt-2">
            <div class="col">
              <button onclick="guardarObservacionPacking()" class="btn btn-success pull-right"><i class="fa fa-save"></i> GUARDAR</button>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- FIN MODAL EDITAR OBSERVACION PACKING -->


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