$(document).ready(function () {
  loadMesadas();
});

let id_mesa_global = null;
function loadMesadas() {
  let tipo_consulta = "cargar_mesadas";
  $(".tabla-mesadas > tbody").html("");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_mesadas.php",
    type: "POST",
    data: { consulta: tipo_consulta },
    success: function (x) {
      if (x.trim().length > 0) {
        let obj = JSON.parse(x);

        if (obj.length > 0) {
          for (let i = 0; i < obj[0].maximo; i++) {
            $(".tabla-mesadas > tbody").append(`
            <tr>  
              <td></td>
              <td></td>
            </tr>
              `);
          }
        }

        let lastE;
        let lastS;
        for (let i = 0; i < obj.length; i++) {
          const { id_mesada, id_interno, id_tipo } = obj[i];
          const color = id_tipo == "E" ? "#B6D7A8" : "#FFE59A";

          const codigomesada = `
                    <div class='mesabox' onClick='click_mesada(${id_mesada})' style='width:14em;background-color:${color};'>
                          <div class="row">
                            <div class="col text-center">
                              <div class='id_tipo p-3' style='font-size:1.2em;font-weight:bold;'>${id_tipo}${id_interno}
                              </div>
                            </div>
                          </div>

                        </div>
                  `;

          if (id_tipo == "S") {
            lastS = id_mesada;
            $(".tabla-mesadas > tbody")
              .find("tr")
              .eq(id_interno - 1)
              .find("td:first").append(`
                        <div class='d-flex' style='justify-content: center;align-items:center'>
                          ${codigomesada}
                          <div class='mesa-${id_mesada}' style="width:80px"></div>
                        </div>
                      
                    `);
          } else if (id_tipo == "E") {
            lastE = id_mesada;
            $(".tabla-mesadas > tbody")
              .find("tr")
              .eq(id_interno - 1)
              .find("td:eq(1)").append(`
            <div class='d-flex' style='justify-content: center;align-items:center'>
              ${codigomesada}
              <div class='mesa-${id_mesada}' style="width:80px"></div>
            </div>
          
          `);
          }
        }

        if (lastS) {
          $(`.mesa-${lastS}`).html(
            `<button class='btn btn-danger fa fa-trash btn-sm ml-3' onclick='eliminarMesada(${lastS})'></button>`
          );
        }
        if (lastE) {
          $(`.mesa-${lastE}`).html(
            `<button class='btn btn-danger fa fa-trash btn-sm ml-3' onclick='eliminarMesada(${lastE})'></button>`
          );
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function CrearMesada() {
  $("#select_tipo").val("default").selectpicker("refresh");
  id_mesa_global = null;
  $("#titulo_modal").html("Agregar Mesada");
  $("#ModalAgregarMesada").modal("show");
  $("#capacidad_txt").focus();
}

function GuardarMesada() {
  const id_tipo = $("#select_tipo").find("option:selected").val();
  if (id_tipo) $("#ModalAgregarMesada").modal("hide");
  let tipo_operacion = $("#titulo_modal").text();
  let tipo_consulta = "";
  if (tipo_operacion.includes("Agregar")) {
    tipo_consulta = "crear_mesada";
  } else {
    tipo_consulta = "editar_mesada";
  }

  $.ajax({
    beforeSend: function () {},
    url: "data_ver_mesadas.php",
    type: "POST",
    data: {
      consulta: tipo_consulta,
      id_tipo: id_tipo,
      id_mesa_global: id_mesa_global,
    },
    success: function (x) {
      if (x.trim() != "success") {
        swal("Ocurrió un error al guardar el mesón", x, "error");
      } else {
        swal("El mesón fue creado correctamente!", "", "success");
        loadMesadas();
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function pone_tiposdeproducto(id) {
  $.ajax({
    beforeSend: function () {
      $("#select_tipo").html("Cargando productos...");
    },
    url: "pone_tiposdeproducto.php",
    type: "POST",
    data: { tipo: "pone_tiposdeproducto" },
    success: function (x) {
      $("#select_tipo").selectpicker();
      $("#select_tipo")
        .html(x)
        .append('<option value="0">NINGUNO</option>')
        .selectpicker("refresh");
      if (id != undefined) {
        $("#select_tipo").selectpicker("val", [id.replace("tipo_", "")]);
      }
    },

    error: function (jqXHR, estado, error) {},
  });
}

function CerrarModalVerMesada() {
  let modal = document.getElementById("ModalVerMesada");
  modal.style.display = "none";
}

function CerrarModalEnviarMesadas() {
  let modal = document.getElementById("ModalEnviaraMesadas");
  modal.style.display = "none";
}

function editMesada(id) {
  let num_mesada = id.replace("edit_", "");
  id_mesa_global = num_mesada;
  $("#capacidad_txt").val($("#capacidad_" + num_mesada).text());
  $("#titulo_modal").html("Editar Mesada");
  let id_tipo = $("#mesada_" + num_mesada)
    .find(".id_tipo")
    .attr("id");
  pone_tiposdeproducto(id_tipo);
  $("#ModalAgregarMesada").modal("show");
  $("#capacidad_txt").focus();
}

function click_mesada(mesada) {
  $.ajax({
    beforeSend: function () {
      $("#tabla_contenidomesada tbody").html("Cargando productos...");
    },
    url: "data_ver_mesadas.php",
    type: "POST",
    data: { consulta: "cargar_infomesada", id_mesada: mesada },
    success: function (x) {
      $("#tabla_contenidomesada tbody").html(x);
      $("#num_mesadaview").html(mesada);
      document.getElementById("ModalVerMesada").style.display = "block";
      $("html").css({ "overflow-y": "hidden" });
    },
    error: function (jqXHR, estado, error) {},
  });
}

function MostrarModalEntregar(objeto) {
  swal(
    'Las entregas deben realizarse en la sección "Planificación de Entregas"',
    "",
    "info"
  );
}

function VerEstadoOrden(id) {
  $.ajax({
    beforeSend: function () {},
    url: "cargar_detalleestadopedido.php",
    type: "POST",
    data: { id: id, consulta: "cliente" },
    success: function (x) {
      $("#nombre_cliente3").html(x);
    },
    error: function (jqXHR, estado, error) {
      $("#nombre_cliente3").html(
        "Hubo un error al cargar la información del pedido"
      );
    },
  });
  $.ajax({
    beforeSend: function () {},
    url: "cargar_detalleestadopedido.php",
    type: "POST",
    data: { id: id, consulta: "pedido" },
    success: function (x) {
      $("#box_info").html(x);
    },
    error: function (jqXHR, estado, error) {
      $("#box_info").html("Hubo un error al cargar la información del pedido");
    },
  });
  let modal = document.getElementById("ModalVerEstado");
  modal.style.display = "block";
}

function CerrarModalEstado() {
  let modal = document.getElementById("ModalVerEstado");
  modal.style.display = "none";
}

//***************************************************ASIGNAR MESADAS*******************************

function ModificarMesadas(id) {
  loadMesadasAsignacion();
  $(".cantidadbox").remove();
  $("#bandejas_pendientes").html(
    "Bandejas a Organizar: " + $("#cantidad_bandejas").text()
  );
  $("#quedan_bandejas").html($("#cantidad_bandejas").text());
  let modal = document.getElementById("ModalEnviaraMesadas");
  modal.style.display = "block";
  indicescroll = 0;
}

function loadMesadasAsignacion() {
  $(".row-reasignar").html("");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_mesadas.php",
    type: "POST",
    data: { consulta: "cargar_mesadas" },
    success: function (x) {
      if (x.trim().length > 0) {
        let obj = JSON.parse(x);
        let html2 = "";

        for (let i = 0; i < obj.length; i++) {
          let color = "white";
          let num_mesada = parseInt(obj[i][0]);
          let capacidad = obj[i][1];
          let cantidad = obj[i][2];
          let libres = (parseInt(capacidad) - parseInt(cantidad)).toString();
          let tipo_producto = obj[i][3] ?? "-";
          if (tipo_producto != null) {
            if (tipo_producto.includes("TOMATE")) {
              color = "#FFACAC";
            } else if (tipo_producto.includes("PIMIENTO")) {
              color = "#BAE1A2";
            } else if (tipo_producto.includes("BERENJENA")) {
              color = "#D5B4FF";
            } else if (tipo_producto.includes("LECHUGA")) {
              color = "#D7FFBC";
            } else if (tipo_producto.includes("ACELGA")) {
              color = "#BFDCBC";
            } else if (tipo_producto.includes("REMOLACHA")) {
              color = "#eba5b5";
            } else if (
              tipo_producto.includes("COLES") ||
              tipo_producto.includes("HINOJO") ||
              tipo_producto.includes("APIO")
            ) {
              color = "#58ACFA";
            } else if (
              tipo_producto.includes("VERDEO") ||
              tipo_producto.includes("PUERRO")
            ) {
              color = "#F7BE81";
            } else {
              color = "#A9F5F2";
            }
            if (libres == 0) {
              color = "#A4A4A4";
            }
          } else {
            if (libres == 0) {
              color = "#A4A4A4";
            } else {
              color = "#A9F5F2";
            }
          }
          const codigomesada = `
                    <div id='mesada_${num_mesada}' class='mesabox' onClick='click_mesada2(this)' style='width:14em;background-color:${color};'>
                          <div class="row">
                            <div class="col text-center">
                              <div class='id_tipo'>${tipo_producto}
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col text-center">
                              Capacidad:
                              <span id='capacidad_${num_mesada}'>${capacidad}</span> - Libres: 
                              <span id='libres_${num_mesada}'>${libres}</span>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col text-right">
                              <div class='pr-2 pb-2'><b>${num_mesada.toString()}</b>
                              </div>
                            </div>
                          </div>
                        </div>
                  `;

          $(".row-reasignar").append(`
                      <div class="col-md-6 mb-3">
                        <div class='d-flex' style='justify-content: space-between'>
                          ${codigomesada}
                        </div>
                      </div>
                    `);
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function click_mesada2(id) {
  $(id).toggleClass("active2");
  let id_mesada = $(id).attr("id").replace("mesada_", "");
  if ($(id).hasClass("active2")) {
    codigo =
      "<div class='row cantidadbox' id='cantidadtxt_" +
      id_mesada +
      "'>" +
      "<div class='col-md-8'>" +
      "<label class='control-label'>Cantidad Mesada " +
      id.id.replace("mesada_", "") +
      ":</label>" +
      "<input type='number' id='input_" +
      id.id.replace("mesada_", "") +
      "' min='0' step='1' class='form-control cantidadmesada' value='0' onchange='setFaltante()' onkeyup='this.onchange();' onpaste='this.onchange();' oninput='this.onchange();'> " +
      "</div></div>";

    $("#contenedor_cantidades").append(codigo);
    let x = document.getElementsByClassName("cantidadbox");
    if (x.length == 1) {
      $("#input_" + id.id.replace("mesada_", "")).val(
        $("#cantidad_bandejas").text()
      );
    } else if (x.length >= 1) {
      $(x[0]).find("input").val("0");
    }
  } else {
    $("#cantidadtxt_" + id.id.replace("mesada_", "")).remove();
  }
  setFaltante();
}

function setFaltante() {
  let x = document.getElementsByClassName("cantidadmesada");
  let cant_original = parseInt($("#cantidad_bandejas").text());
  for (let i = 0; i < x.length; i++) {
    let valor = parseInt(x[i].value);
    if (isNaN(valor)) {
      valor = 0;
    }
    cant_original -= valor;
  }
  $("#quedan_bandejas").html(cant_original.toString());
}


function eliminarMesada(id_mesada) {
  swal("Estás seguro/a de eliminar este Mesón?", "", {
    icon: "warning",
    buttons: {
      cancel: "Cancelar",
      catch: {
        text: "ELIMINAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_mesadas.php",
          data: { consulta: "eliminar_mesada", id_mesada: id_mesada },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste el Mesón correctamente!", "", "success");
              loadMesadas();
            } else {
              swal(
                "Ocurrió un error al eliminar el Mesón",
                "Asegúrate de no haber asignado ningún producto a este mesón",
                "error"
              );
            }
          },
        });

        break;

      default:
        break;
    }
  });
}
