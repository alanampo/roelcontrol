function func_check(id_usuario, permisos) {
  $(document).ready(function () {
    $("#contenedor_modulos").html("");
    if (id_usuario == "1") {
      $("#contenedor_modulos").append(
        "<li><a href='cargar_pedido.php'><i class='fa fa-arrow-circle-right'></i> Agregar Pedido</a></li> \
                        <li><a href='ver_pedidos.php'><i class='fa fa-arrow-circle-right'></i> Ver Pedidos</a></li>"
      );
      $("#contenedor_modulos").append(
        "<li><a href='ver_seguimiento.php'><i class='fa fa-arrow-circle-right'></i> Planificación de Pedidos</a></li>"
      );
      $("#contenedor_modulos").append(
        "<li><a href='ver_mesadas.php'><i class='fa fa-arrow-circle-right'></i> Mesones</a></li>"
      );
      $("#contenedor_modulos").append(
        "<li><a href='ver_historial.php'><i class='fa fa-arrow-circle-right'></i> Historial de Entregas</a></li>"
      );
      $("#contenedor_modulos").append(
        "<li><a href='ver_estadisticas.php'><i class='fa fa-arrow-circle-right'></i> Estadísticas</a></li>"
      );
      $("#contenedor_modulos").append(
        '<li><a href="ver_stock_bandejas.php"><i class="fa fa-arrow-circle-right"></i> Stock Bandejas</a></li>'
      );
      $("#contenedor_modulos").append(
        '<li><a href="ver_semillas.php"><i class="fa fa-arrow-circle-right"></i> Semillas</a></li>'
      );
      $("#contenedor_modulos").append(
        '<li><a href="ver_tendencias.php"><i class="fa fa-arrow-circle-right"></i> Tendencias</a></li>'
      );
      $("#contenedor_panel").html(
        "<a href='#'><i class='fa fa-bars'></i> <span>Panel de Control</span> <i class='fa fa-angle-left pull-right'></i></a> \
                          <ul class='treeview-menu'> \
                            <li><a href='ver_clientes.php'><i class='fa fa-arrow-circle-right'></i> Clientes</a></li> \
                            <li><a href='ver_variedades.php'><i class='fa fa-arrow-circle-right'></i> Variedades</a></li> \
                            <li><a href='ver_especies.php'><i class='fa fa-arrow-circle-right'></i> Especies</a></li> \
                            <li><a href='ver_tipos.php'><i class='fa fa-arrow-circle-right'></i> Tipos de Producto</a></li> \
                            <li><a href='ver_usuarios.php'><i class='fa fa-arrow-circle-right'></i> Usuarios</a></li> \
                            <li><a href='ver_clicks.php'><i class='fa fa-arrow-circle-right'></i> (ADM) Clicks Vivero</a></li> \
                          </ul>"
      );
    } else {
      if (permisos.length > 0) {
        let array = permisos;

        let path = window.location.pathname;

        let page = path.split("/").pop().replace(".php", "");

        if (page == "cargar_pedido") {
          page = "ver_pedidos";
        }

        let permisos1 = [
          "pedidos",
          "planificacionpedidos",
          "mesadas",
          "historialentregas",
          "estadisticas",
          "stock_bandejas",
          "semillas",
          "tendencias",
          "panel",
          "productos",
          "productos",
          "productos",
          "informes"
        ];

        let permisos2 = [
          "ver_pedidos",
          "ver_seguimiento",
          "ver_mesadas",
          "ver_historial",
          "ver_estadisticas",
          "ver_stock_bandejas",
          "ver_semillas",
          "ver_tendencias",
          "ver_clientes",
          "ver_variedades",
          "ver_especies",
          "ver_tipos",
          "informes"
        ];

        if (permisos.includes(permisos1[permisos2.indexOf(page)]) == false) {
          window.location.href = "inicio.php";
        } else {
          console.log(array);
          for (let i = 0; i < array.length; i++) {
            if (array[i] == "pedidos") {
              $("#contenedor_modulos").append(
                '<li><a href="cargar_pedido.php"><i class="fa fa-arrow-circle-right"></i> Agregar Pedido</a></li> \
                          <li><a href="ver_pedidos.php"><i class="fa fa-arrow-circle-right"></i> Ver Pedidos</a></li>'
              );
            } else if (array[i] == "planificacionpedidos") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_seguimiento.php"><i class="fa fa-arrow-circle-right"></i> Seguimiento de Pedidos</a></li>'
              );
            } else if (array[i] == "mesadas") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_mesadas.php"><i class="fa fa-arrow-circle-right"></i> Mesadas</a></li>'
              );
            } else if (array[i] == "historialentregas") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_historial.php"><i class="fa fa-arrow-circle-right"></i> Historial de Entregas</a></li>'
              );
            } else if (array[i] == "estadisticas") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_estadisticas.php"><i class="fa fa-arrow-circle-right"></i> Estadísticas</a></li>'
              );
            } else if (array[i] == "stock") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_stock_bandejas.php"><i class="fa fa-arrow-circle-right"></i> Stock Bandejas</a></li>'
              );
            } else if (array[i] == "semillas") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_semillas.php"><i class="fa fa-arrow-circle-right"></i> Semillas</a></li>'
              );
            } else if (array[i] == "tendencias") {
              $("#contenedor_modulos").append(
                '<li><a href="ver_tendencias.php"><i class="fa fa-arrow-circle-right"></i> Tendencias</a></li>'
              );
            }
            else if (array[i] == "informes") {
              $("#contenedor_modulos").append(
                '<li><a href="informes.php"><i class="fa fa-arrow-circle-right"></i> Informes</a></li>'
              );
            }
            else if (array[i] == "productos") {
              $("#contenedor_panel").html(
                '<a href="#"><i class="fa fa-bars"></i> <span>Productos</span> <i class="fa fa-angle-left pull-right"></i></a> \
                          <ul class="treeview-menu"> \
                            <li><a href="ver_variedades.php"><i class="fa fa-arrow-circle-right"></i> Variedades</a></li> \
                            <li><a href="ver_especies.php"><i class="fa fa-arrow-circle-right"></i> Especies</a></li> \
                            <li><a href="ver_tipos.php"><i class="fa fa-arrow-circle-right"></i> Tipos de Producto</a></li> \
                          </ul>'
              );
            } else if (array[i] == "panel") {
              $("#contenedor_panel").html(
                '<a href="#"><i class="fa fa-bars"></i> <span>Panel de Control</span> <i class="fa fa-angle-left pull-right"></i></a> \
                          <ul class="treeview-menu"> \
                            <li><a href="ver_clientes.php"><i class="fa fa-arrow-circle-right"></i> Clientes</a></li> \
                          </ul>'
              );
            }
          }
        }
      } else {
        window.location.href = "inicio.php";
      }
    }
  });
}

