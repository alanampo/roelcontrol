<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Seguimiento de Pedidos</title>
  <script src="plugins/moment/moment.min.js"></script>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_seguimiento.js?v=<?php echo uniqid() ?>"></script>
</head>

<body>
  <div id="miVentana">
  </div>
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
        $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha=$dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
        ?>
        <!-- /.sidebar -->
      </aside>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Seguimiento de Pedidos
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Seguimiento</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-md-9">
              <div class="tab">
                <button class="tablinks" onclick="openTab(event, 'tab-esquejes');"
                  id="defaultOpen">Esquejes</button>
                <button class="tablinks" onclick="openTab(event, 'tab-semillas');">Semillas</button>
                <button class="tablinks" onclick="openTab(event, 'tab-interior');">Interior</button>
                <button class="tablinks" onclick="openTab(event, 'tab-exterior');">Exterior</button>
                <button class="tablinks" onclick="openTab(event, 'tab-vivero');">Vivero</button>
                <button class="tablinks" onclick="openTab(event, 'tab-packs');">Packs</button>
                <button class="tablinks" onclick="openTab(event, 'tab-invitro');">Invitro</button>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex flex-row align-items-center pt-2">
                <label for="input-search">Buscar:</label>
                <input id="input-search" oninput='buscar(this.value);' class="form-control w-75 ml-2" type="search" autocomplete="off"></input>
              </div>
            </div>
          </div>

          <div id="tab-esquejes" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-esquejes">
              <thead class="thead-dark">
                <tr class="text-center">
                  <th>ETAPA 0<br><span class="header-subtitle">INICIO</span></th>
                  <th>ETAPA 1<br><span class="header-subtitle">10%</span></th>
                  <th>ETAPA 2<br><span class="header-subtitle">50%</span></th>
                  <th>ETAPA 3<br><span class="header-subtitle">100%</span></th>
                  <th>ETAPA 4<br><span class="header-subtitle">REPIQUE</span></th>
                  <th>ETAPA 5<br><span class="header-subtitle">ENTREGA</span></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div id="tab-semillas" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-semillas">
              <thead style="background-color: rgb(1, 84, 139);color: white;">
                <tr class="text-center">
                  <th>ETAPA 0<br><span class="header-subtitle">SEMBRADO</span></th>
                  <th>ETAPA 1<br><span class="header-subtitle">GERMINADO</span></th>
                  <th>ETAPA 2<br><span class="header-subtitle">2 COTILEDONES</span></th>
                  <th>ETAPA 3<br><span class="header-subtitle">HOJAS VERDADERAS</span></th>
                  <th>ETAPA 4<br><span class="header-subtitle">REPIQUE</span></th>
                  <th>ETAPA 5<br><span class="header-subtitle">ENTREGA</span></th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div id="tab-interior" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-interior">
              <thead style="background-color: rgb(200, 84, 139);color: white;">
                <tr class="text-center">
                  <th>ETAPA 0</th>
                  <th>ETAPA 1</th>
                  <th>ETAPA 2</th>
                  <th>ETAPA 3</th>
                  <th>ETAPA 4</th>
                  <th>ETAPA 5</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div id="tab-exterior" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-exterior">
              <thead style="background-color: rgb(200, 84, 139);color: white;">
                <tr class="text-center">
                  <th>ETAPA 0</th>
                  <th>ETAPA 1</th>
                  <th>ETAPA 2</th>
                  <th>ETAPA 3</th>
                  <th>ETAPA 4</th>
                  <th>ETAPA 5</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div id="tab-vivero" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-vivero">
              <thead style="background-color: rgb(200, 84, 139);color: white;">
                <tr class="text-center">
                  <th>ETAPA 0</th>
                  <th>ETAPA 1</th>
                  <th>ETAPA 2</th>
                  <th>ETAPA 3</th>
                  <th>ETAPA 4</th>
                  <th>ETAPA 5</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div id="tab-packs" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-packs">
              <thead style="background-color: rgb(200, 84, 139);color: white;">
                <tr class="text-center">
                  <th>ETAPA 0</th>
                  <th>ETAPA 1</th>
                  <th>ETAPA 2</th>
                  <th>ETAPA 3</th>
                  <th>ETAPA 4</th>
                  <th>ETAPA 5</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div id="tab-invitro" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-invitro">
              <thead style="background-color: rgb(200, 84, 139);color: white;">
                <tr class="text-center">
                  <th>ETAPA 0</th>
                  <th>ETAPA 1</th>
                  <th>ETAPA 2</th>
                  <th>ETAPA 3</th>
                  <th>ETAPA 4</th>
                  <th>ETAPA 5</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <style>
            table,
            thead,
            tr,
            tbody,
            th,
            td {
              text-align: center;
              table-layout: fixed;
            }

            .table td {
              text-align: center;
              height: 70px;
              background-color: #e2f8ffc2;
              border: 1px solid rgba(87, 87, 87, 0.466);
            }
          </style>

      </div>
      </section><!-- /.content -->

      <?php include("./modal_ver_estado.php"); ?>
      <?php include("./modal_modificar_cliente.php"); ?>
      
    </div><!-- /.content-wrapper -->
    <!-- Main Footer -->
    <?php
      include('./class_lib/main_footer.php');
      ?>
  </div>
  </div>
  <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
  </div><!-- ./wrapper -->
  </div>

   <script type="text/javascript">
    

   
    var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
    var permisos = "<?php echo $_SESSION['permisos'] ?>";
    func_check(id_usuario, permisos.split(","));
  </script>
</body>

</html>