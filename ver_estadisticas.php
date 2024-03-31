<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Estadísticas</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <link rel="stylesheet" href="plugins/select2/select2.min.css" />
    <link
      rel="stylesheet"
      href="plugins/daterangepicker/daterangepicker-bs3.css"
    />
    <script src="plugins/moment/moment.min.js"></script>
    <script src="dist/js/ver_estadisticas.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>

    <script src="js/charts.min.js"></script>
  </head>

  <body
    onload="pone_tipos();pone_clientes();$('#select_tipofecha').selectpicker('val', [0]); "
  >
    <div id="miVentana"></div>

    <div id="ocultar">
      <div class="wrapper">
        <header class="main-header">
          <?php
          include('class_lib/nav_header.php');
        ?>
        </header>

        <!-- Left side column. contains the logo and sidebar -->

        <aside class="main-sidebar">
          <?php
            include('class_lib/sidebar.php');
          ?>
        </aside>
        <div class="content-wrapper">
          <section class="content-header">
            <h1>Estadísticas</h1>

            <ol class="breadcrumb">
              <li><a href="inicio.php"> Inicio</a></li>
              <li class="active">Estadísticas</li>
            </ol>
          </section>

          <!-- Main content -->
          <section class="content">
            <div class="row">
              <div class="col">
                <div class="d-flex align-items-end h-100 pb-2">
                  <div class="row">
                    <div class="col">
                      <div class="tab">
                        <button
                          class="tablinks"
                          onclick="abrirTabEstadisticas(event, 'pendientes');"
                          id="defaultOpen"
                        >
                          <span class="label-pend"
                            >PENDIENTES
                            <span class="label-cant label-pendientes"></span
                          ></span>
                        </button>
                        <button
                          class="tablinks"
                          onclick="abrirTabEstadisticas(event, 'produccion');"
                        >
                          EN PRODUCCIÓN
                          <span class="label-cant label-produccion"></span>
                        </button>
                        <button
                          class="tablinks"
                          onclick="abrirTabEstadisticas(event, 'parcial');"
                        >
                          ENTREGA PARCIAL
                          <span class="label-cant label-parcial"></span>
                        </button>
                        <button
                          class="tablinks"
                          onclick="abrirTabEstadisticas(event, 'entregados');"
                        >
                          ENTREGADOS
                          <span class="label-cant label-entregados"></span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-2 pt-2">
                <select
                  id="select-filtro"
                  class="selectpicker mobile-device"
                  title="Filtrar por"
                  data-style="btn-info"
                  data-width="100%"
                  data-dropup-auto="false"
                  data-size="5"
                >
                  <option value="bandejas">Filtrar por Bandejas</option>
                  <option value="plantas">Filtrar por Plantas</option>
                  <option value="pedidos">Filtrar por Pedidos</option>
                </select>
              </div>
              <div class="col-md-2 pt-2">
                <select
                  id="select-tipo-pedido"
                  class="selectpicker mobile-device"
                  title="Tipos de Pedido"
                  data-style="btn-info"
                  data-width="100%"
                  data-dropup-auto="false"
                  data-size="5"
                >
                  <option value="esquejes">Esquejes y Semillas</option>
                  <option value="hechura">Servicios de Hechura</option>
                  
                </select>
              </div>
            </div>
            <div class="row d-none">
              <div class="col-md-6">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Buscar</h3>
                  </div>
                  <div class="box-body">
                    <div class="form-group">
                      <div class="row">
                        <div class="col-md-2">
                          <label>Fechas:</label>
                        </div>
                        <div class="col-md-7">
                          <div class="d-flex flex-row">
                            <button
                              class="btn btn-default pull-left"
                              id="daterange-btn"
                            >
                              <i class="fa fa-calendar"></i> Seleccionar...
                              <i class="fa fa-caret-down"></i>
                            </button>

                            <button
                              type="button"
                              class="btn btn-secondary ml-2"
                              onclick='$("#ff,#fi").val("");$(".fe").html("");'
                              id="btn-clearfecha"
                            >
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                        </div>

                        <div class="col-md-3">
                          <button
                            class="btn btn-primary btn-block"
                            onclick="busca_entradas();"
                            id="btn-busca"
                          >
                            <i class="fa fa-search"></i> Buscar...
                          </button>
                        </div>
                      </div>

                      <span class="fe"></span>
                      <input
                        type="hidden"
                        class="form-control"
                        id="fi"
                        value=""
                      />
                      <input
                        type="hidden"
                        class="form-control"
                        id="ff"
                        value=""
                      />
                    </div>
                    <!-- /.form group -->

                    <div id="contenedor_busqueda">
                      <div class="form-group">
                        <div class="row">
                          <div class="col-md-2">
                            <label>Tipo:</label>
                          </div>
                          <div class="col-md-7">
                            <select
                              id="select_tipo"
                              class="selectpicker mobile-device"
                              title="Tipo de Producto"
                              data-style="btn-info"
                              data-dropup-auto="false"
                              data-size="5"
                              data-width="100%"
                            ></select>
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="row">
                          <div class="col-md-2">
                            <label>Subtipo:</label>
                          </div>
                          <div class="col-md-7">
                            <div class="btn-group" style="width: 100%">
                              <input
                                id="busca_subtipo"
                                style="text-transform: uppercase; width: 100%"
                                type="search"
                                class="form-control"
                              />
                              <span
                                id="searchclear"
                                onClick="$('#busca_subtipo').val('');"
                                class="glyphicon glyphicon-remove-circle"
                              ></span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="form-group">
                        <div class="row">
                          <div class="col-md-2">
                            <label>Variedad:</label>
                          </div>
                          <div class="col-md-7">
                            <div class="btn-group" style="width: 100%">
                              <input
                                id="busca_variedad"
                                style="text-transform: uppercase"
                                type="search"
                                class="form-control"
                              />
                              <span
                                id="searchclear"
                                onClick="$('#busca_variedad').val('');"
                                class="glyphicon glyphicon-remove-circle"
                              ></span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-2">
                          <label>Cliente:</label>
                        </div>
                        <div class="col-md-7">
                          <select
                            id="select_cliente"
                            class="selectpicker mobile-device"
                            title="Selecciona un Cliente"
                            data-style="btn-info"
                            data-live-search="true"
                            data-width="100%"
                            data-dropup-auto="false"
                            data-size="5"
                          ></select>
                        </div>
                        <div class="col-md-3">
                          <button
                            class="btn btn-danger btn-block"
                            onclick="quitar_filtros();"
                            id="btn-busca"
                          >
                            <i class="fa fa-times"></i> Quitar Filtros
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- FIN ROW-->

            <div class="row">
              <div class="col bg-light">
                <h5 class="mt-3 ml-5 mb-2 label-estadisticas"></h5>
                <div
                  class="chart-container"
                  style="height: 400px"
                ></div>
              </div>
            </div>

            <div class="row">
              <div class="col">
                <div
                  class="chart-container-servicios bg-light"
                  style="height: 400px"
                ></div>
              </div>
            </div>

            <div class="row">
              <div class="col">
                <div id="tabla_entradas"></div>
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div style="height: 300px; width: 100%"></div>
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
    <!-- ID OCULTAR-->

    <style>
      .table2 tr.selected td {
        background-color: #333;

        color: #fff;
      }

      .table2 tr.selected2 td {
        background-color: #333;

        color: #fff;
      }
    </style>

    <script src="plugins/daterangepicker/daterangepicker.js"></script>

    <script type="text/javascript">
      var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";

      var permisos = "<?php echo $_SESSION['permisos'] ?>";

      func_check(id_usuario, permisos.split(","));

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

        $("#busca_tiposolucion").on(
          "changed.bs.select",
          function (e, clickedIndex, newValue, oldValue) {
            $("#busca_tiporevision").val("default").selectpicker("refresh");
          }
        );
        $("#busca_tiporevision").on(
          "changed.bs.select",
          function (e, clickedIndex, newValue, oldValue) {
            $("#busca_tiposolucion").val("default").selectpicker("refresh");
          }
        );

        $.datepicker.setDefaults($.datepicker.regional["es"]);
      });
    </script>
  </body>
</html>
