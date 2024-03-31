let fechaInicio = null;
let fechaFinal = null;
let filtros = null;

let currentTabEst;

$(document).ready(function () {
  $("#select-tipo-pedido").val(["esquejes"]);
  $("#select-filtro").val(["bandejas"]);
  $(".selectpicker").selectpicker("refresh");

  $("#select-filtro,#select-tipo-pedido").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      busca_entradas(currentTabEst ? currentTabEst : "pendientes");
    }
  );

  document.getElementById("defaultOpen").click();

  //swal("Función aún no disponible","","warning")
});

$(function () {
  $("#daterange-btn").daterangepicker(
    {
      ranges: {
        "Semana pasada": [
          moment().startOf("isoWeek").subtract(7, "days"),
          moment().startOf("isoWeek").subtract(1, "days"),
        ],
        "Los ultimos 7 dias": [moment().subtract(6, "days"), moment()],
        "Los ultimos 30 dias": [moment().subtract(29, "days"), moment()],
        "Los ultimos 3 meses": [moment().subtract(90, "days"), moment()],
        "Este mes": [moment().startOf("month"), moment().endOf("month")],
        "El mes pasado": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
        "Todo el año": [moment().startOf("year"), moment()],
        "El año pasado": [
          moment().subtract(1, "year").startOf("year"),
          moment().subtract(1, "year").endOf("year"),
        ],
      },
      startDate: moment().subtract(90, "days"),
      endDate: moment(),
    },
    function (start, end) {
      $(".fe").html(
        start.format("DD/MM/YYYY") + " - " + end.format("DD/MM/YYYY")
      );
      let xstart = start.format("YYYY-MM-DD");
      let xend = end.format("YYYY-MM-DD");
      $("#fi").val(xstart);
      $("#ff").val(xend);
    }
  );
});

function busca_entradas(tipoBusqueda) {
  let tipoFiltro = $("#select-filtro option:selected").val();
  let tipoPedido = $("#select-tipo-pedido option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: "data_ver_estadisticas.php",
    type: "POST",
    data: {
      consulta: "busca_estadisticas",
      tipo_busqueda: tipoBusqueda,
      tipo_pedido: tipoPedido,
    },
    success: function (datos) {
      if (datos.length) {
        $(".label-estadisticas").html(
          tipoPedido == "esquejes"
            ? "Esquejes y Semillas"
            : "Servicios de Hechura"
        );
        try {
          const data = JSON.parse(datos);
          generarChart(tipoBusqueda, tipoFiltro, tipoPedido, data);
        } catch (error) {
          swal("Ocurrió un error al generar el Gráfico", "", "error");
          console.log(error);
        }
      } else {
        $(".chart-container").html(
          `<div class='callout callout-danger'><b>No se encontraron estadísticas...</b></div>`
        );
        $(".label-estadisticas").html("");
      }
    },
    error: function (jqXHR, estado, error) {
      $(".chart-container").html(
        "Ocurrió un error: contactá al desarrollador" +
          "     " +
          estado +
          " " +
          error
      );
    },
  });
}

function monthDiff(d1, d2) {
  let months;
  months = (d2.getFullYear() - d1.getFullYear()) * 12;
  months -= d1.getMonth();
  months += d2.getMonth();
  return months <= 0 ? 0 : months;
}

function generarChart(tipoBusqueda, tipoFiltro, tipoPedido, json) {
  let variedades = [];
  let cantidades = [];
  json.forEach((e, i) => {
    variedades.push(`${e.nombre_variedad} (${e.tipo})`);
    cantidades.push(
      tipoFiltro == "plantas"
        ? e.cant_plantas
        : tipoFiltro == "bandejas"
        ? e.cant_bandejas
        : tipoFiltro == "pedidos"
        ? e.cant_pedidos
        : ""
    );
  });

  $(".chart-container").html(`<canvas id="myChart"></canvas>`);

  let ctx = document.getElementById("myChart").getContext("2d");
  let myChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: variedades,
      datasets: [
        {
          label:
            tipoBusqueda == "pendientes"
              ? "Pendientes"
              : tipoBusqueda == "produccion"
              ? "En Producción"
              : tipoBusqueda == "parcial"
              ? "Entregados Parcialmente"
              : "Entregados",
          data: cantidades,
          backgroundColor: [
            tipoBusqueda == "pendientes"
              ? "rgba(144, 144, 144, 0.5)"
              : tipoBusqueda == "entregados"
              ? "rgba(0, 204, 0, 0.5)" // VERDE
              : tipoBusqueda == "produccion"
              ? "rgba(54, 162, 235, 0.5)"
              : "rgba(255, 195, 0, 0.5)",
          ],
          borderColor: [
            tipoBusqueda == "pendientes"
              ? "rgba(144, 144, 144, 1)"
              : tipoBusqueda == "entregados"
              ? "rgba(0, 204, 0, 1)" // VERDE
              : tipoBusqueda == "produccion"
              ? "rgba(54, 162, 235, 1)"
              : "rgba(255, 195, 0, 1)",
          ],
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      // Elements options apply to all of the options unless overridden in a dataset
      // In this case, we are setting the border of each horizontal bar to be 2px wide
      elements: {
        bar: {
          borderWidth: 2,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
        xAxes: [
          {
            barThickness: 10,
          },
        ],
      },
    },
  });
}

function quitar_filtros() {
  location.reload();
}

function pone_tipos() {
  $.ajax({
    beforeSend: function () {
      $("#select_tipo").html("Cargando productos...");
    },
    url: "data_ver_tipos.php",
    type: "POST",
    data: { consulta: "busca_tipos_select" },
    success: function (x) {
      $(".selectpicker").selectpicker();
      $("#select_tipo").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}

function abrirTabEstadisticas(evt, tabName) {
  let i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  currentTabEst = tabName;
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  evt.currentTarget.className += " active";
  busca_entradas(tabName);
}

function loadCantidadPedidos() {
  let tipoFiltro = $("#select-filtro option:selected").val();
  let tipoPedido = $("#select-tipo-pedido option:selected").val();

  $.ajax({
    beforeSend: function () {
      //$(".label-cant").html("")
    },
    url: "data_ver_estadisticas.php",
    type: "POST",
    data: {
      consulta: "carga_cantidad_pedidos",
      tipo_filtro: tipoFiltro,
      tipo_pedido: tipoPedido,
    },
    success: function (x) {
      if (x.length) {
        const data = JSON.parse(x);
        if (data && data.entregados) {
          $(".label-entregados").html(`(${data.entregados})`);
          $(".label-produccion").html(`(${data.produccion})`);
          $(".label-parcial").html(`(${data.parcial})`);
          $(".label-pendientes").html(`(${data.pendientes})`);

          if (data.pendientes > 0) {
            $(".label-pend").addClass("text-danger");
          } else {
            $(".label-pend").removeClass("text-danger");
          }
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}
function pone_clientes() {
  $.ajax({
    beforeSend: function () {
      $("#select_cliente").html("Cargando lista de clientes...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "pone_clientes"
    },
    success: function (x) {
      if (
        /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)
      ) {
        $(".selectpicker").selectpicker("mobile");
      } else {
        let elements = document.querySelectorAll(".mobile-device");
        for (let i = 0; i < elements.length; i++) {
          elements[i].classList.remove("mobile-device");
        }
        $(".selectpicker").selectpicker({});
      }

      $("#select_cliente").html(x).selectpicker("refresh");
      $("#select_cliente").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          let id_cliente = $("#select_cliente").find("option:selected").val();
        }
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}
