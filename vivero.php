<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Seguimiento de Vivero</title>
  <script src="plugins/moment/moment.min.js"></script>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/vivero.js?v=<?php echo $version ?>"></script>
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
            Seguimiento de Vivero
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
                <button class="tablinks" onclick="openTab(event, 'tab-planta-terminada');"
                  id="defaultOpen">Planta Terminada (PT)</button>
                <button class="tablinks" onclick="openTab(event, 'tab-bolsa');">Bolsa (BOL)</button>
                <button class="tablinks" onclick="openTab(event, 'tab-maceta');">Maceta (MAC)</button>
                <button class="tablinks" onclick="openTab(event, 'tab-arboles');">ARBOLES (ARB)</button>
                <button class="tablinks" onclick="openTab(event, 'tab-planta');">PLANTA (PLA)</button>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex flex-row align-items-center pt-2">
                <label for="input-search">Buscar:</label>
                <input id="input-search" oninput='buscar(this.value);' class="form-control w-75 ml-2" type="search" autocomplete="off"></input>
              </div>
            </div>
          </div>

          <div id="tab-pedidos" class="tabcontent">
            <table class="table table-responsive mt-3 w-100 d-block d-md-table" id="tabla-pedidos">
              <thead class="thead-dark">
                <tr class="text-center">
                  <th>ETAPA 0<br><span class="header-subtitle">PREPARACIÓN DE MACETAS</span></th>
                  <th>ETAPA 1<br><span class="header-subtitle">RECEPCIÓN DEL PLANTÍN</span></th>
                  <th>ETAPA 2<br><span class="header-subtitle">TRASPLANTE A MACETAS</span></th>
                  <th>ETAPA 3<br><span class="header-subtitle">DESARROLLO VEGETATIVO</span></th>
                  <th>ETAPA 4<br><span class="header-subtitle">CUIDADO DE BOTONES FLORALES</span></th>
                  <th>ETAPA 5<br><span class="header-subtitle">FLORACIÓN Y MADURACIÓN</span></th>
                  <th>ETAPA 6<br><span class="header-subtitle">ENTREGA</span></th>
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