$(function () {
  $("#daterange-btn").daterangepicker(
    {
      ranges: {
        Hoy: [moment(), moment()],
        Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "SEMANA PASADA": [
          moment().startOf("isoWeek").subtract(7, "days"),
          moment().startOf("isoWeek").subtract(1, "days"),
        ],
        "Los ultimos 7 dias": [moment().subtract(6, "days"), moment()],
        "Los ultimos 30 dias": [moment().subtract(29, "days"), moment()],
        "Los ultimos 3 meses": [moment().subtract(90, "days"), moment()],
        "Este mes": [moment().startOf("month"), moment().endOf("month")],
        //'El mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        "Todo el año": [moment().startOf("year"), moment()],
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

function busca_entradas() {
  let fecha = $("#fi").val();
  let fechaf = $("#ff").val();
  let tipos = $("#select_tipo").val();
  if (tipos.length == 0) tipos = null;
  else {
    tipos = JSON.stringify(tipos).replace("[", "(").replace("]", ")");
  }

  let variedad = $("#busca_variedad").val().trim().toUpperCase();
  if (variedad.length == 0) variedad = null;
  else if (variedad.includes(",")) {
    variedad = variedad.replace(",", "|");
  }

  let cliente = $("#busca_cliente").val().trim().toUpperCase();
  if (cliente.length == 0) cliente = null;
  else if (cliente.includes(",")) {
    cliente = cliente.replace(",", "|");
  }

  let filtros = {
    tipo: tipos,
    variedad: variedad ? variedad.toUpperCase() : null,
    cliente: cliente ? cliente.toUpperCase() : null,
  };
  filtros = JSON.stringify(filtros);

  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html(
        "<h4 class='ml-1'>Buscando entregas, espera...</h4>"
      );
      oncontextmenu = null;
    },
    url: "data_ver_historial.php",
    type: "POST",
    data: {
      consulta: "busca_pedidos",
      fechai: fecha,
      fechaf: fechaf,
      filtros: filtros,
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [[1, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ entregas por página",
          zeroRecords: "No hay entregas",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay entregas",
          infoFiltered: "(filtrado de _MAX_ entregas en total)",
          lengthMenu: "Mostrar _MENU_ entregas",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron resultados",
          paginate: {
            first: "Primera",
            last: "Última",
            next: "Siguiente",
            previous: "Anterior",
          },
          aria: {
            sortAscending: ": tocá para ordenar en modo ascendente",
            sortDescending: ": tocá para ordenar en modo descendente",
          },
        },
        /*"columnDefs": [
                  { "width": "30%", "targets": [2] }
                  ]*/
      });
      oncontextmenu = (e) => {
        if (
          $("#ModalRegistroEntregas").css("display") == "block" ||
          $("#miVentana").css("display") == "block"
        )
          return;
        if (!$(".selected").length) return;
        e.preventDefault();
        if ($("#ctxmenu").length) {
          $("#ctxmenu").remove();
          return;
        }
        let menu = document.createElement("div");
        menu.id = "ctxmenu";
        menu.onclick = () => {
          ctxmenu.outerHTML = "";
        };
        menu.style = `top:${e.clientY}px;left:${e.pageX - 200}px`;
        menu.onmouseleave = () => (ctxmenu.outerHTML = "");
        menu.innerHTML = `<p onclick='quitarSeleccion()'>Deseleccionar Todo</p>
                            <p onclick='imprimirEntregas()'>IMPRIMIR ENTREGAS</p>
                          `;
        document.body.appendChild(menu);
      };
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function CerrarModalPedido() {
  let modal = document.getElementById("ModalVerPedido");
  modal.style.display = "none";
}

function quitarSeleccion() {
  $(".selected").removeClass("selected");
}

function print_Busqueda(tipo) {
  if (tipo == 1) {
    func_printBusqueda();
    document.getElementById("ocultar").style.display = "none";
    document.getElementById("miVentana").style.display = "block";
  } else {
    document.getElementById("ocultar").style.display = "block";
    document.getElementById("miVentana").style.display = "none";
    $("#miVentana").html("");
  }
}

function func_printBusqueda() {
  let direccion = `<div align='center'><img src='${globals.logoPrintImg}' class="logo-print"></img>`;
  $("#miVentana").html(direccion);
  $("#miVentana").append(document.getElementById("tabla").outerHTML);
  $("#miVentana")
    .find("tr,td,th")
    .css({ "font-size": "9px", "word-wrap": "break-word" });

  let haymesada = false;
  $("#miVentana")
    .find("tr")
    .each(function () {
      $(this).find("td:eq(7)").css({ "font-size": "7px" });
      if ($(this).find("td:eq(9)").text().trim().length > 0) {
        haymesada = true;
      }
    });

  if (!haymesada) {
    $("#miVentana").find("th:eq(9)").remove();
    $("#miVentana")
      .find("tr")
      .each(function () {
        $(this).find("td:eq(9)").remove();
      });
  }

  setTimeout("window.print();print_Busqueda(2)", 500);
}

function expande_busqueda() {
  let contenedor = $("#contenedor_busqueda");
  if ($(contenedor).css("display") == "none")
    $(contenedor).css({ display: "block" });
  else {
    $(contenedor).css({ display: "none" });
    $("#select_tipo,#select_estado,#busca_tiporevision,#busca_tiposolucion")
      .val("default")
      .selectpicker("refresh");
    $("#busca_subtipo,#busca_variedad,#busca_cliente").val("");
  }
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

async function registroEntregas(
  id_artpedido,
  codigo_producto,
  nombre_cliente,
  id_entrega,
  nombre_variedad
) {
  $(".title-pedido").html(
    `${codigo_producto} ${nombre_variedad} (${nombre_cliente})`
  );
  $("#ModalRegistroEntregas").attr("x-id-artpedido", id_artpedido);
  $("#ModalRegistroEntregas").attr("x-codigo", codigo_producto);
  $("#ModalRegistroEntregas").attr("x-nombre-cliente", nombre_cliente);
  $(".tabla-entregas > tbody").html("");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_historial.php",
    type: "POST",
    data: { id_artpedido: id_artpedido, consulta: "cargar_registro_entregas" },
    success: function (x) {
      if (x.trim().length) {
        const data = JSON.parse(x);
        if (!data || data.length < 1) return;
        data.forEach((e, i) => {
          const { fecha_ingreso, id_entrega, cantidad, tipo_entrega } = e;
          $(".tabla-entregas > tbody").append(`
            <tr class='text-center'>  
              <td>${fecha_ingreso}</td>
              <td style='font-weight:bold;font-size:1.2em'>${cantidad}</td>
              <td>${generaBoxTipoEntrega(tipo_entrega)}</td>
            </tr>
          `);
        });
      }
    },
    error: function (jqXHR, estado, error) {},
  });

  $("#ModalRegistroEntregas").modal("show");
}

function generaBoxTipoEntrega(tipo_entrega, fullWidth) {
  let w100 = "";
  if (fullWidth == true) {
    w100 = "w-100";
  }

  if (tipo_entrega == 1) {
    return `<div class='d-inline-block cajita ${w100}' style='text-align:center;background-color:#FFFF00; padding:3px; cursor:pointer;'><div>ENTREGA PARCIAL</div></div>`;
  } else if (tipo_entrega == 0) {
    return `<div class='d-inline-block cajita ${w100}' style='text-align:center;background-color:#A9F5BC; padding:3px; cursor:pointer;'><div>ENTREGA COMPLETA</div></div>`;
  }
}

function setSelected(objeto) {
  let tr = $(objeto).parent();

  if (tr.hasClass("selected")) {
    tr.removeClass("selected");
  } else {
    tr.addClass("selected");
  }
}

function imprimirEntregas() {
  let firstID = null;
  let puede = true;
  $(".selected").each(function (i, e) {
    const id_cliente = $(e).attr("x-id-cliente");
    if (i == 0) {
      firstID = id_cliente;
    }
    if (id_cliente != firstID) {
      puede = false;
    }
  });

  if (!puede) {
    swal(
      "Las entregas seleccionadas deben pertenecer a un único cliente!",
      "",
      "error"
    );
    return;
  }
  const fecha = moment().format("DD/MM/YYYY HH:mm");

  const nombre_cliente = $(".selected").first().attr("x-cliente");
  $("#miVentana").html(globals.printHeader);
  $("#miVentana").append(`
  <div class="row">
    <div class="col">
      <h5>Fecha: ${fecha}</h5>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <h5>Cliente: ${nombre_cliente}</h5>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col">
      <h5>COMPROBANTE DE ENTREGA DE PEDIDOS: </h5>
    </div>
  </div>
  <div class="row mt-3">
  <div class="col">
    <table style="font-size:18px !important;" class="table tabla-entregas-print table-responsive w-100 d-block d-md-table">
      <thead class="thead-dark">
        <tr class="text-center">
          <th scope="col">Pedido</th>
          <th scope="col">Producto</th>
          <th scope="col">Bandejas/Plantas</th>
          <th scope="col">Fecha Entrega</th>
          <th scope="col">Tipo Entrega</th>
          <th scope="col">ID Producto</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
    </table>
  </div>
  </div>
  `);

  $(".selected").each(function (i, e) {
    const id_cliente = $(e).attr("x-id-cliente");
    const producto = $(e).attr("x-producto");
    const cant_plantas = $(e).attr("x-cant-plantas");
    const productoID = $(e).attr("x-codigo");
    const pedidoID = $(e).attr("x-pedido");
    const fecha_entrega = $(e).attr("x-fecha-entrega");

    const etapa = $(e).find(".td-estado").html();
    $(".tabla-entregas-print > tbody").append(`
      <tr class='text-center'>
        <td>${pedidoID}</td>
        <td>${producto}</td>
        <td>${cant_plantas}</td>
        <td>${fecha_entrega}</td>
        <td>${etapa}</td>
        <td>${productoID}</td>
      </tr>
    `);
  });
  document.getElementById("ocultar").style.display = "none";
  document.getElementById("miVentana").style.display = "block";

  setTimeout(() => {
    try {
      document.title = nombre_cliente;
    } catch (error) {
      document.title = "Comprobante de Entregas";
    }
    window.print();
    document.getElementById("ocultar").style.display = "block";
    document.getElementById("miVentana").style.display = "none";
    $("#miVentana").html("");
    document.title = "Historial de Entregas";
  }, 500);
}
