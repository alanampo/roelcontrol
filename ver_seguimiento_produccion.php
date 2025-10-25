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

    /* Estilos para info-boxes de pagos */
    #seccion-pagos .info-box-text {
      font-size: 13px !important;
      font-weight: 500 !important;
    }
    #seccion-pagos .info-box-number {
      font-size: 20px !important;
      font-weight: 600 !important;
    }
    #seccion-pagos .info-box-icon {
      font-size: 50px !important;
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
            <div class="col-md-4">
              <button class="btn btn-warning" id="btn-establecer-meta" onclick="abrirModalMeta()" style="display:none;">
                <i class="fa fa-target"></i> Establecer Meta Semanal
              </button>
              <span id="meta-actual" style="display:none; margin-left: 10px;"></span>
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

          <div class="row mt-4" id="seccion-pagos" style="display:none;">
            <div class="col-md-12">
              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title">Pagos del Mes</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-success btn-sm" onclick="abrirModalPago()">
                      <i class="fa fa-plus"></i> Registrar Pago
                    </button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Total a Pagar</span>
                          <span class="info-box-number" id="label-total-pagar">$0</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Total Pagado</span>
                          <span class="info-box-number" id="label-total-pagado">$0</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="info-box" id="info-estado">
                        <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Estado</span>
                          <span class="info-box-number" id="label-estado">PENDIENTE</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div id="tabla-pagos"></div>
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

  <!-- Modal Establecer Meta -->
  <div id="modalEstablecerMeta" class="modal">
    <div class="modal-content3">
      <div class="box box-warning">
        <div class="box-header with-border">
          <h3 class="box-title">Establecer Meta Semanal</h3>
        </div>
        <div class="box-body">
          <div class="form-group">
            <label>Meta Semanal (plantines)</label>
            <input type="number" class="form-control" id="input-meta-semanal" placeholder="1000" min="1" step="100">
            <p class="help-block">Cantidad de plantines que debe producir por semana</p>
          </div>
          <div class="form-group">
            <label>Vigente desde</label>
            <input type="date" class="form-control" id="input-fecha-desde-meta">
          </div>
          <div align="right">
            <button type="button" class="btn fa fa-close btn-modal-bottom" onclick="cerrarModalMeta()"></button>
            <button type="button" class="btn fa fa-save btn-modal-bottom ml-2" onclick="guardarMeta()"></button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Registrar Pago -->
  <div id="modalRegistrarPago" class="modal">
    <div class="modalpago-content">
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title">Registrar Pago</h3>
        </div>
        <div class="box-body">
          <div class="form-group">
            <label>Monto del Pago</label>
            <input type="number" class="form-control" id="input-monto-pago" placeholder="0.00" step="0.01" min="0">
          </div>
          <div class="form-group">
            <label>Fecha del Pago</label>
            <input type="date" class="form-control" id="input-fecha-pago">
          </div>
          <div class="form-group">
            <label>Observaciones (opcional)</label>
            <textarea class="form-control" id="input-observaciones-pago" rows="3" placeholder="Ej: Pago primera semana..."></textarea>
          </div>
          <div align="right">
            <button type="button" class="btn fa fa-close btn-modal-bottom" onclick="cerrarModalPago()"></button>
            <button type="button" class="btn fa fa-save btn-modal-bottom ml-2" onclick="guardarPago()"></button>
          </div>
        </div>
      </div>
    </div>
  </div>

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
