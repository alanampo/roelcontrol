$(document).ready(function(){
  pone_alertas();
});

function pone_pedidos() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "pedidos"},
    success: function (x) {
      $(".col-pedidos").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="cargar_pedido.php"><i class="fa fa-arrow-circle-right"></i> Agregar Pedido</a></li><li><a href="ver_pedidos.php"><i class="fa fa-arrow-circle-right"></i> Ver Pedidos</a></li>'
      );
    },
  });
}

function pone_agregar_pedidos() {
  $(".col-agregar-pedidos")
    .html(
      `
    <a href='cargar_pedido.php' style="text-decoration:none;">
                    <div class="small-box bg-red">
                      <div class="inner">
                        <img src='dist/img/addpedido.png' width='38px' height='38px'>
                          <p style="margin-top:18px">Agregar Pedido</p>
                        </div>
                        <div class="icon">
                          <i class="fa fa-add"></i>
                        </div>
                        <span class="small-box-footer"><i class="fa fa-arrow-circle-right"></i></span>
                      </div>
  </a>
    `
    )
    .removeClass("d-none");
}

function pone_reservas() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "reservas"},
    success: function (x) {
      $(".col-reservas").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="ver_reservas.php"><i class="fa fa-arrow-circle-right"></i> Reservas</a></li>'
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}

function pone_historialentregas() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "historial"},
    success: function (x) {
      $(".col-historial-entregas").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="ver_historial.php"><i class="fa fa-arrow-circle-right"></i> Historial de Entregas</a></li>'
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}

function pone_planificacionpedidos() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "seguimiento"},
    success: function (x) {
      $(".col-planificacion").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="ver_seguimiento.php"><i class="fa fa-arrow-circle-right"></i> Seguimiento de Pedidos</a></li>'
      );
    },
  });
}

function pone_vivero() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "vivero"},
    success: function (x) {
      $(".col-vivero").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="vivero.php"><i class="fa fa-arrow-circle-right"></i> Seguimiento Vivero</a></li>'
      );
    },
  });
}

function pone_laboratorio() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "laboratorio"},
    success: function (x) {
      $(".col-laboratorio").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="laboratorio.php"><i class="fa fa-arrow-circle-right"></i> Laboratorio</a></li>'
      );
    },
  });
}

function pone_mesadas() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "mesadas"},
    success: function (x) {
      $(".col-mesadas").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="ver_mesadas.php"><i class="fa fa-arrow-circle-right"></i> Mesones</a></li>'
      );
    },
  });
}

function pone_stock() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "stock"},
    success: function (x) {
      $(".col-stock").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="ver_stock_bandejas.php"><i class="fa fa-arrow-circle-right"></i> Stock Bandejas</a></li>'
      );
    },
  });
}

function pone_semillas() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "semillas"},
    success: function (x) {
      $(".col-semillas").html(x).removeClass("d-none");
      $("#contenedor_modulos").append(
        '<li><a href="ver_semillas.php"><i class="fa fa-arrow-circle-right"></i> Semillas</a></li>'
      );
    },
  });
}

function pone_estadisticas() {
  $(".col-estadisticas")
    .html(
      `
        <a href="ver_estadisticas.php">
          <div class="small-box" style="background-color:#ffd700"> 
            <div class="inner"  style="height:7.1em;">    
              <p style='color:black'>Estadísticas</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-line-chart"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Estadísticas <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="ver_estadisticas.php"><i class="fa fa-arrow-circle-right"></i> Estadísticas</a></li>'
  );
}

function pone_tendencias() {
  $(".col-tendencias")
    .html(
      `
        <a href="ver_tendencias.php">
          <div class="small-box" style="background-color:#F781D8"> 
            <div class="inner"  style="height:7.1em;">    
              <p style='color:black'>Tendencias</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-area-chart"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Tendencias <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="ver_tendencias.php"><i class="fa fa-arrow-circle-right"></i> Tendencias</a></li>'
  );
}

function pone_cotizaciones() {
  $(".col-cotizaciones")
    .html(
      `
        <a href="ver_cotizaciones.php">
          <div class="small-box" style="background-color:#0080CD"> 
            <div class="inner"  style="height:7.1em;">    
              <p style='color:white'>Cotizaciones</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-usd"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Cotizaciones <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="ver_estadisticas.php"><i class="fa fa-arrow-circle-right"></i> Estadísticas</a></li>'
  );
}

function pone_alertas() {
  $.ajax({
    url: "pone_boxes.php",
    type: "POST",
    data: {tipo: "alertas"},
    success: function (x) {
      if (x.length){
        try {
          const data = JSON.parse(x);
          if (data.atrasados){
            const { atrasados, paraentregar, problemas} = data;
            $(".alert-wrapper").html(`
              <div class="row mt-3 pl-2 pr-2">
                <div class="col-md-4" onClick="clickAlerta('atrasados')">
                  <div class="alert alert-warning" role="alert">
                    Pedidos Atrasados <span class="alert-link">(${atrasados})</span>
                  </div>
                </div>
                <div class="col-md-4" onClick="clickAlerta('paraentregar')">
                  <div class="alert alert-primary" role="alert">
                    Pedidos para Entregar <span class="alert-link">(${paraentregar})</span>
                  </div>
                </div>
                <div class="col-md-4" onClick="clickAlerta('problemas')">
                  <div class="alert alert-danger" role="alert">
                    Pedidos con Problemas <span class="alert-link">(${problemas})</span>
                  </div>
                </div>
              </div>
            `)
          }
          else{
            console.error("SIN DATA")
          }
        } catch (error) {
          console.error(error)
        }
      }
    },
  });
}

function clickAlerta(tipo){
  location.href = "ver_pedidos.php?tipo_pedido="+tipo;
}

function pone_bonos() {
  $(".col-bonos")
    .html(
      `
        <a href="ver_bonos_produccion.php">
          <div class="small-box" style="background-color:#04B486"> 
            <div class="inner"  style="height:7.1em;">    
              <p style='color:white'>Bonos de Producción</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-product-hunt"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Módulo <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="ver_bonos_produccion.php"><i class="fa fa-arrow-circle-right"></i> Bonos de Producción</a></li>'
  );
}

function pone_informes() {
  $(".col-informes")
    .html(
      `
        <a href="informes.php">
          <div class="small-box" style="background-color:#585858;">
            <div class="inner"  style="height:7.1em;">
              <p style='color:white'>Informes</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-file-excel-o"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Módulo <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="informes.php"><i class="fa fa-arrow-circle-right"></i> Informes</a></li>'
  );
}

function pone_seguimientoproduccion() {
  $(".col-seguimiento-produccion")
    .html(
      `
        <a href="ver_seguimiento_produccion.php">
          <div class="small-box" style="background-color:#17a2b8;">
            <div class="inner"  style="height:7.1em;">
              <p style='color:white'>Seguimiento de Producción</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-calendar-check-o"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Módulo <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="ver_seguimiento_produccion.php"><i class="fa fa-arrow-circle-right"></i> Seguimiento de Producción</a></li>'
  );
}

function pone_miproduccion() {
  $(".col-mi-produccion")
    .html(
      `
        <a href="ver_mi_produccion.php">
          <div class="small-box" style="background-color:#28a745;">
            <div class="inner"  style="height:7.1em;">
              <p style='color:white'>Mi Producción</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-user-circle"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Registrar Avance <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
    )
    .removeClass("d-none");
  $("#contenedor_modulos").append(
    '<li><a href="ver_mi_produccion.php"><i class="fa fa-arrow-circle-right"></i> Mi Producción</a></li>'
  );
}