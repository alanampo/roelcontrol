<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Mi Producción</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/image_compressor.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_mi_produccion.js?v=<?php echo $version ?>"></script>
  <style>
    /* Estilos específicos para toastr */
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

    /* Estilos para preview de imágenes */
    .imagen-preview-container {
      position: relative;
      margin-bottom: 15px;
    }

    .imagen-preview {
      width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #dee2e6;
    }

    .btn-eliminar-preview {
      position: absolute;
      top: 5px;
      right: 5px;
      width: 25px;
      height: 25px;
      padding: 0;
      border-radius: 50%;
      background-color: #dc3545;
      border: none;
      color: white;
      font-size: 12px;
      cursor: pointer;
      z-index: 10;
    }

    .btn-eliminar-preview:hover {
      background-color: #c82333;
    }

    /* Estadísticas */
    .stats-box {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 10px;
      padding: 20px;
      color: white;
      margin-bottom: 20px;
    }

    .stats-box h3 {
      margin: 0;
      font-size: 36px;
      font-weight: bold;
    }

    .stats-box p {
      margin: 5px 0 0;
      font-size: 14px;
      opacity: 0.9;
    }

    .progress-lg {
      height: 30px;
      font-size: 16px;
      line-height: 30px;
    }
  </style>
</head>

<body>
  <div id="miVentana"></div>

  <div id="ocultar">
    <div class="wrapper">
      <!-- Main Header -->
      <header class="main-header">
        <?php include('class_lib/nav_header.php'); ?>
      </header>

      <!-- Left side column -->
      <aside class="main-sidebar">
        <?php include('class_lib/sidebar.php'); ?>
      </aside>

      <!-- Content Wrapper -->
      <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
          <h1>
            Mi Producción
            <small>Registra tu avance diario</small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php">Inicio</a></li>
            <li class="active">Mi Producción</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">

          <!-- Selector de Mes -->
          <div class="row">
            <div class="col-md-12">
              <div class="box box-solid">
                <div class="box-body text-center" style="padding: 15px;">
                  <div class="btn-group" role="group" style="display: inline-flex; align-items: center; gap: 15px;">
                    <button type="button" class="btn btn-default" id="btn-mes-anterior" title="Mes Anterior">
                      <i class="fa fa-chevron-left"></i>
                    </button>
                    <div style="min-width: 200px;">
                      <input type="month" class="form-control text-center" id="input-mes-actual" style="font-size: 16px; font-weight: bold;">
                    </div>
                    <button type="button" class="btn btn-default" id="btn-mes-siguiente" title="Mes Siguiente">
                      <i class="fa fa-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-mes-hoy" title="Ir al mes actual">
                      <i class="fa fa-calendar"></i> Mes Actual
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Estadísticas Rápidas -->
          <div class="row" id="stats-mes-actual">
            <div class="col-md-3 col-sm-6">
              <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-calendar-o"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Hoy</span>
                  <span class="info-box-number" id="stat-diaria">0</span>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="info-box bg-blue">
                <span class="info-box-icon"><i class="fa fa-calendar-check-o"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Esta Semana</span>
                  <span class="info-box-number" id="stat-semanal">0</span>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Este Mes</span>
                  <span class="info-box-number" id="stat-mensual">0</span>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Bono Estimado</span>
                  <span class="info-box-number" id="stat-bono">$0</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Estadísticas para Meses Anteriores -->
          <div class="row" id="stats-mes-historico" style="display: none;">
            <div class="col-md-6 col-sm-6">
              <div class="info-box bg-purple">
                <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text" id="label-mes-historico">Mes</span>
                  <span class="info-box-number" id="stat-mes-historico">0</span>
                </div>
              </div>
            </div>

            <div class="col-md-6 col-sm-6">
              <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Bono Estimado</span>
                  <span class="info-box-number" id="stat-bono-historico">$0</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Progreso Semanal -->
          <div class="row" id="progreso-semanal-container">
            <div class="col-md-12">
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Progreso Semanal</h3>
                  <div class="box-tools pull-right">
                    <span class="badge" id="indicador-cumplimiento">En Progreso</span>
                  </div>
                </div>
                <div class="box-body">
                  <div class="row">
                    <div class="col-md-6">
                      <p>Meta: <strong><span id="stat-meta">0</span> plantines</strong></p>
                    </div>
                    <div class="col-md-6 text-right">
                      <p>Progreso: <strong><span id="stat-progreso">0%</span></strong></p>
                    </div>
                  </div>
                  <div class="progress progress-lg">
                    <div id="progress-bar-semanal" class="progress-bar progress-bar-striped active"
                         role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                      <span class="sr-only">0% Completo</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Formulario de Registro -->
          <div class="row">
            <div class="col-md-12">
              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title">
                    <i class="fa fa-plus-circle"></i> Registrar Avance Diario
                  </h3>
                </div>
                <div class="box-body">
                  <form id="form-registro">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Fecha <span class="text-danger">*</span></label>
                          <input type="date" class="form-control" id="input-fecha" required>
                        </div>
                      </div>

                      <div class="col-md-3">
                        <div class="form-group">
                          <label>Turno <span class="text-danger">*</span></label>
                          <select class="form-control" id="select-turno" required>
                            <option value="">Seleccionar</option>
                            <option value="mañana">Mañana</option>
                            <option value="tarde">Tarde</option>
                          </select>
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="form-group">
                          <label>Tipo de Item <span class="text-danger">*</span></label>
                          <select id="select-tipo-item" class="form-control" required>
                            <option value="variedad">Variedad de Producto</option>
                            <option value="manual">Descripción Manual</option>
                          </select>
                        </div>
                      </div>

                      <div class="col-md-4" id="col-variedad">
                        <div class="form-group">
                          <label>Variedad <span class="text-danger">*</span></label>
                          <select id="select-variedad" class="selectpicker" title="Seleccionar Variedad"
                                  data-style="btn-default" data-width="100%" data-live-search="true">
                          </select>
                        </div>
                      </div>

                      <div class="col-md-4" id="col-descripcion-manual" style="display:none;">
                        <div class="form-group">
                          <label>Descripción Manual <span class="text-danger">*</span></label>
                          <select id="select-descripcion-manual" class="selectpicker" title="Seleccionar o escribir..."
                                  data-style="btn-default" data-width="100%" data-live-search="true">
                            <option value="__NUEVO__">+ Nueva descripción...</option>
                          </select>
                        </div>
                      </div>

                      <div class="col-md-4" id="col-descripcion-texto" style="display:none;">
                        <div class="form-group">
                          <label>Nueva Descripción</label>
                          <input type="text" class="form-control" id="input-descripcion-texto" maxlength="255"
                                 placeholder="Ej: Trasplante lechuga">
                        </div>
                      </div>

                      <div class="col-md-4">
                        <div class="form-group">
                          <label>Cantidad de Plantines <span class="text-danger">*</span></label>
                          <input type="number" class="form-control" id="input-cantidad" placeholder="0" min="1" required>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Ubicación / Lote (opcional)</label>
                          <input type="text" class="form-control" id="input-ubicacion" maxlength="100"
                                 placeholder="Ej: Invernadero 2, Mesa 3">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Observaciones (opcional)</label>
                          <textarea class="form-control" id="input-observaciones" rows="2"
                                    placeholder="Notas adicionales..." maxlength="500"></textarea>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label>
                            <i class="fa fa-camera"></i> Evidencias Fotográficas (opcional, máximo 5)
                          </label>
                          <br>
                          <input type="file" id="input-imagenes" name="imagenes[]" multiple accept="image/*" style="display: none;">
                          <button type="button" id="btn-seleccionar-imagenes" class="btn btn-info btn-sm">
                            <i class="fa fa-camera"></i> Seleccionar Fotos
                          </button>
                          <p class="help-block">Las imágenes se optimizarán automáticamente antes de subirlas</p>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <div id="preview-imagenes" style="display: none;"></div>
                      </div>
                    </div>

                    <div class="row mt-3">
                      <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-default" id="btn-cancelar">
                          <i class="fa fa-times"></i> Limpiar
                        </button>
                        <button type="button" class="btn btn-success" id="btn-registrar">
                          <i class="fa fa-save"></i> Registrar
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <!-- Historial -->
          <div class="row">
            <div class="col-md-12">
              <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">
                    <i class="fa fa-history"></i> Historial de Producción
                  </h3>
                </div>
                <div class="box-body">
                  <div id="tabla-historial"></div>
                </div>
              </div>
            </div>
          </div>

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <!-- Main Footer -->
      <?php include('class_lib/main_footer.php'); ?>

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
