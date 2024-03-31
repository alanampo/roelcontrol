<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Roelplant - Administración</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/init.js?v=<?php echo $version ?>"></script>
    <script>
      $(document).ready(function(){
        const id_usuario = "<?php echo $_SESSION['id_usuario'] ?>"; 
        const permisos = "<?php echo $_SESSION['permisos'] ?>"; 
        check_permisos(id_usuario, permisos.split(","));
      });

      function check_permisos(id_usuario, permisos){
        
        $("#contenedor_modulos").html("");

       if (id_usuario == "1"){
          pone_agregar_pedidos();
          pone_pedidos();
          pone_planificacionpedidos();
          pone_mesadas();
          pone_reservas();
          pone_historialentregas();
          pone_estadisticas();
          pone_stock();
          pone_semillas();
          pone_tendencias();
          pone_bonos();
          pone_informes();
          $("#contenedor_panel").html(`<a href="#"><i class="fa fa-bars"></i> <span>Panel de Control</span> <i class="fa fa-angle-left pull-right"></i></a>
                          <ul class="treeview-menu menu-open" style="display:block;"> 
                            <li><a href="ver_variedades.php"><i class="fa fa-arrow-circle-right"></i> Variedades</a></li> 
                            <li><a href="ver_especies.php"><i class="fa fa-arrow-circle-right"></i> Especies</a></li> 
                            <li><a href="ver_tipos.php"><i class="fa fa-arrow-circle-right"></i> Tipos de Producto</a></li> 
                            <li><a href="ver_clientes.php"><i class="fa fa-arrow-circle-right"></i> Clientes</a></li> 
                            <li><a href="ver_usuarios.php"><i class="fa fa-arrow-circle-right"></i> Usuarios</a></li> 
                            <li><a href='ver_clicks.php'><i class='fa fa-arrow-circle-right'></i> (ADM) Clicks Vivero</a></li> \
                          </ul>`);

      }else{
                  if (permisos.length > 0){
                    var array = permisos;
                    for (var i=0;i<array.length;i++){
                      if (array[i] == "pedidos"){
                        pone_agregar_pedidos();
                        pone_pedidos();
                      }
                      else if (array[i] == "planificacionpedidos"){
                        pone_planificacionpedidos();
                      }
                      else if (array[i] == "mesadas"){
                        pone_mesadas();
                      }
                      else if (array[i] == "historialentregas"){
                        pone_historialentregas();
                      }
                      else if (array[i] == "estadisticas"){
                        pone_estadisticas();
                      }
                      else if (array[i] == "stock"){
                        pone_stock();
                      }
                      else if (array[i] == "semillas"){
                        pone_semillas();
                      }
                      else if (array[i] == "tendencias"){
                        pone_tendencias();
                      }
                      else if (array[i] == "informes"){
                        pone_informes();
                      }
                      else if (array[i] == "cotizaciones"){
                        //pone_cotizaciones();
                      }
                      else if (array[i] == "productos"){
                        $("#contenedor_panel").html(
                          '<a href="#"><i class="fa fa-bars"></i> <span>Productos</span> <i class="fa fa-angle-left pull-right"></i></a> \
                                    <ul class="treeview-menu"> \
                                      <li><a href="ver_variedades.php"><i class="fa fa-arrow-circle-right"></i> Variedades</a></li> \
                                      <li><a href="ver_especies.php"><i class="fa fa-arrow-circle-right"></i> Especies</a></li> \
                                      <li><a href="ver_tipos.php"><i class="fa fa-arrow-circle-right"></i> Tipos de Producto</a></li> \
                                    </ul>'
                        );
                      }
                      else if (array[i] == "panel"){

                        $("#contenedor_panel").html(
                '<a href="#"><i class="fa fa-bars"></i> <span>Panel de Control</span> <i class="fa fa-angle-left pull-right"></i></a> \
                          <ul class="treeview-menu"> \
                            <li><a href="ver_clientes.php"><i class="fa fa-arrow-circle-right"></i> Clientes</a></li> \
                          </ul>'
              );
                      } 
                    }
                  } 
 }    
 
}
    </script>
  </head>
  <body>

    <div class="wrapper">
      <header class="main-header">
        <?php
        include('class_lib/nav_header.php');
        ?>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <?php
        include('class_lib/sidebar.php');
        include('class_lib/class_conecta_mysql.php');
        $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha=$dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
        ?>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
          <div class="bg-light alert-wrapper">
            
          </div>
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            <small><?php echo $fecha; ?></small>
          </h1>
          
        </section>
        <!-- Main content -->
        <section class="content">
          <div class='row row-modulos'>
            
            <div class="col-6 col-md-3 col-agregar-pedidos d-none">
            </div>

            <div class="col-6 col-md-3 col-pedidos d-none">
            </div>

            <div class="col-6 col-md-3 col-planificacion d-none">
            </div>

            <div class="col-6 col-md-3 col-reservas d-none">
            </div>

            <div class="col-6 col-md-3 col-historial-entregas d-none">
            </div>

            <div class="col-6 col-md-3 col-estadisticas d-none">
            </div>

            <div class="col-6 col-md-3 col-mesadas d-none">
            </div>

            <div class="col-6 col-md-3 col-stock d-none">
            </div>

            <div class="col-6 col-md-3 col-semillas d-none">
            </div>

            <div class="col-6 col-md-3 col-tendencias d-none">
            </div>

            <div class="col-6 col-md-3 col-cotizaciones d-none">
            </div>

            <div class="col-6 col-md-3 col-situacion d-none">
            </div>

            <div class="col-6 col-md-3 col-bonos d-none">
            </div>

            <div class="col-6 col-md-3 col-informes d-none">
            </div>
          </div>
        </section>
      </div><!-- /.content-wrapper -->
      <!-- Main Footer -->
      <?php include('./class_lib/main_footer.php'); ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
    
  </body>
</html>

