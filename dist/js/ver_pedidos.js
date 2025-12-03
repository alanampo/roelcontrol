let globalProductos = null;
$(document).ready(function () {
  document.getElementById("defaultOpen").click();
});
$(function () {
  $("#daterange-btn").daterangepicker(
    {
      ranges: {
        Hoy: [moment(), moment()],
        Ayer: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        // "SEMANA PASADA": [
        //   moment().startOf("isoWeek").subtract(7, "days"),
        //   moment().startOf("isoWeek").subtract(1, "days"),
        // ],
        "Los ultimos 7 dias": [moment().subtract(6, "days"), moment()],
        "Los ultimos 30 dias": [moment().subtract(29, "days"), moment()],
        "Los ultimos 3 meses": [moment().subtract(90, "days"), moment()],
        "Los ultimos 6 meses": [moment().subtract(180, "days"), moment()],
        "Los ultimos 12 meses": [moment().subtract(365, "days"), moment()],
        "Este mes": [moment().startOf("month"), moment().endOf("month")],
        "Todo el año": [moment().startOf("year"), moment()],
      },
      startDate: moment().subtract(365, "days"),
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

function busca_entradas(tipo_busqueda) {
  let fecha = $("#fi").val();
  let fechaf = $("#ff").val();
  let tipos = $("#select_tipo1").val();
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

  // let estados = $("#select_estado").val();
  // if (estados.length == 0) estados = null;
  // else {
  //   estados = JSON.stringify(estados).replace("[", "(").replace("]", ")");
  // }
  if (getQueryVariable("tipo_pedido")){
    $(".row-busqueda").addClass("d-none")
  }
  else{
    $(".row-busqueda").removeClass("d-none")
  }
  let filtros = {
    tipo: tipos,
    variedad: variedad ? variedad.toUpperCase() : null,
    cliente: cliente ? cliente.toUpperCase() : null,
    tipo_busqueda: tipo_busqueda,
    
  };
  filtros = JSON.stringify(filtros);

  loadCantidadPedidos();
  $.ajax({
    beforeSend: function () {
      oncontextmenu = null;
      $("#tabla_entradas").html(
        "<h4 class='ml-1'>Buscando pedidos, espera...</h4>"
      );
    },
    url: "data_ver_pedidos.php",
    type: "POST",
    data: {
      consulta: "busca_pedidos",
      fechai: fecha,
      fechaf: fechaf,
      filtros: filtros,
      tipo_pedido: getQueryVariable("tipo_pedido")
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [[0, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ pedidos por página",
          zeroRecords: "No hay pedidos",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay pedidos",
          infoFiltered: "(filtrado de _MAX_ pedidos en total)",
          lengthMenu: "Mostrar _MENU_ pedidos",
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
          $("#ModalVerEstado").css("display") == "block" ||
          $("#modal-modificar-pedido").css("display") == "block" ||
          $("#modal-produccion").css("display") == "block" ||
          $("#modal-etiquetas").css("display") == "block"
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
                            <p onclick='cambiarEtapaVP(0)'>ENVIAR A PRODUCCIÓN</p>
                            <p onclick='cambiarEtapaVP(-10)'>DEVOLVER A PENDIENTES</p>
                            <p onclick='modalEtiquetas()'>GENERAR ETIQUETAS</p>
                            
                            
                            `;////////
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

function cambiarEtapaVP(etapa) {
  if (!$(".selected").length) return;

  let puede = true;

  if (etapa == 0) {
    // ENVIANDO DE PENDIENTES A LA ETAPA 0
    $(".selected").each(function (i, e) {
      if ($(e).attr("x-etapa") != "-10") {
        puede = false;
      }
    });
    if (!puede) {
      swal(
        "Verifica que los pedidos seleccionados estén en estado PENDIENTE!",
        "",
        "error"
      );
      return;
    } else {
      globalProductos = null;
      modalProduccion();
      return;
    }
  } else if (etapa == -10) {
    // DEVOLVIENDO A PENDIENTES
    $(".selected").each(function (i, e) {
      if ($(e).attr("x-etapa") != "0") {
        puede = false;
      }
    });
    if (!puede) {
      swal(
        "Verifica que los pedidos seleccionados estén en ETAPA 0!",
        "",
        "error"
      );
      return;
    }
  }

  swal(
    etapa != -10
      ? `Cambiar a Etapa ${etapa}?`
      : "Devolver pedidos a PENDIENTES?",
    "",
    {
      icon: "info",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "ACEPTAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        let productos = [];
        $(".selected").each(function (i, e) {
          const id_artpedido = $(e).attr("x-id-artpedido");
          productos.push(id_artpedido);
        });

        if (!productos.length) return;

        $.ajax({
          type: "POST",
          url: "data_ver_seguimiento.php",
          data: {
            consulta: "cambiar_etapa",
            productos: JSON.stringify(productos),
            etapa: etapa,
          },
          success: function (data) {
            if (data == "success") {
              swal(
                "Cambiaste de Etapa a los productos seleccionados!",
                "",
                "success"
              );
              busca_entradas(currentTab);
            } else {
              swal(
                "Ocurrió un error cambiar los productos de Etapa",
                "",
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

function quitarSeleccion() {
  $(".selected").removeClass("selected");
}

function print_Busqueda(tipo) {
  if (tipo == 1) {
    func_printBusqueda();

    document.getElementById("ocultar").style.display = "none";

    document.getElementById("print-wrapper").style.display = "block";
  } else {
    document.getElementById("ocultar").style.display = "block";

    document.getElementById("print-wrapper").style.display = "none";

    $("#print-wrapper").html("");
  }
}

function func_printBusqueda() {
  let direccion = `<div align='center'><img src='${globals.logoPrintImg}' class="logo-print"></img>`;

  $("#print-wrapper").html(direccion);

  $("#print-wrapper").append(document.getElementById("tabla").outerHTML);

  $("#print-wrapper")
    .find("tr,td,th")
    .css({ "font-size": "9px", "word-wrap": "break-word" });

  let haymesada = false;
  $("#print-wrapper")
    .find("tr")
    .each(function () {
      $(this).find("td:eq(7)").css({ "font-size": "7px" });
      if ($(this).find("td:eq(9)").text().trim().length > 0) {
        haymesada = true;
      }
    });

  if (!haymesada) {
    $("#print-wrapper").find("th:eq(9)").remove();
    $("#print-wrapper")
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
    $("#select_tipo1,#select_estado").val("default").selectpicker("refresh");
    $("#busca_subtipo,#busca_variedad,#busca_cliente").val("");
  }
  $(".box-body-buscar").toggleClass("p-0");
}

function setSelected(objeto) {
  let tr = $(objeto).parent();

  if (tr.hasClass("selected")) {
    tr.removeClass("selected");
  } else {
    tr.addClass("selected");
  }
}

function pone_tipos1() {
  $.ajax({
    beforeSend: function () {
      $("#select_tipo1").html("Cargando productos...");
    },
    url: "data_ver_tipos.php",
    type: "POST",
    data: { consulta: "busca_tipos_select" },
    success: function (x) {
      $(".selectpicker").selectpicker();
      $("#select_tipo1").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}

let currentTab;
function abrirTab(evt, tabName) {
  let i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  currentTab = tabName;
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  //document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  busca_entradas(tabName);
}

function loadCantidadPedidos() {
  $.ajax({
    url: "data_ver_pedidos.php",
    type: "POST",
    data: { consulta: "carga_cantidad_pedidos" },
    success: function (x) {
      if (x.length) {
        const data = JSON.parse(x);
        if (data && data.todos) {
          $(".label-todos").html(`(${data.todos})`);
          $(".label-entregados").html(`(${data.entregados})`);
          $(".label-produccion").html(`(${data.produccion})`);
          $(".label-cancelados").html(`(${data.cancelados})`);
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

function modalModificarPedido(id_pedido, nombre_cliente) {
  $(".title-modificar-pedido").html(`${id_pedido} (${nombre_cliente})`);
  $("#modal-modificar-pedido").attr("x-id-pedido", id_pedido);
  $("#modal-modificar-pedido").attr("x-nombre-cliente", nombre_cliente);
  $("#modal-modificar-pedido").modal("show");
  $(".tabla-modificar-pedido > tbody").html("");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_pedidos.php",
    type: "POST",
    data: {
      consulta: "get_pedido_especifico",
      id_pedido: id_pedido,
    },
    success: function (x) {
      if (x.length) {
        const data = JSON.parse(x);
        if (!data || !data.length) return;
        data.forEach((e) => {
          const {
            producto,
            id_artpedido,
            cant_plantas,
            estado,
            cant_bandejas,
            fecha_ingreso,
            fecha_entrega,
            etapa,
            id_producto,
          } = e;
          $(".tabla-modificar-pedido > tbody").append(
            `
            <tr class='text-center'>
              <td>${producto}</td>
              <td>
                <input type="search" autocomplete="off" maxlength="9" class="form-control text-center input-plantas" readonly value="${cant_plantas}">
                <input type="search" autocomplete="off" maxlength="3" class="form-control text-center mt-2 input-bandejas" readonly value="${cant_bandejas}">
                
              </td>
              <td>${fecha_ingreso}</td>
              <td>${fecha_entrega}</td>
              <td>${etapa}</td>
              <td>${id_producto}</td>
              <td>
                <div class="d-flex flex-row justify-content-center">
                  ${
                    estado == -10 || estado == 0
                      ? `
                    <button class="btn btn-danger btn-sm fa fa-trash" onclick="eliminarProducto(${id_artpedido}, ${id_pedido}, '${nombre_cliente}')"></button>
                    <button class="btn btn-primary btn-sm fa fa-edit ml-2 btn-edit" onclick="enableEdit(this)"></button>
                    <div class="btn-save d-none">
                      <button class="btn btn-secondary btn-sm fa fa-close ml-4" onclick="cancelEdit(this)"></button>
                      <button class="btn btn-success btn-sm fa fa-save ml-1" onclick="editarProducto(this, ${id_artpedido})"></button>
                    </div>
                    `
                      : ""
                  }
      
                </div>
              </td>
            </tr>
            `
          );
        });
        $(".tabla-modificar-pedido")
          .find("input")
          .on("propertychange input", function (e) {
            this.value = this.value.replace(/\D/g, "");
          });
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function enableEdit(obj) {
  const tr = $(obj).parent().parent().parent();

  $(tr).find("input").removeAttr("readonly");
  $(tr).find("input").first().focus();
  $(tr).find(".btn-save").removeClass("d-none");
  $(tr).find(".btn-edit").addClass("d-none");
}

function cancelEdit(obj) {
  const tr = $(obj).parent().parent().parent().parent();
  $(tr).find("input").attr("readonly", true);

  $(tr).find(".btn-save").addClass("d-none");
  $(tr).find(".btn-edit").removeClass("d-none");
}

function editarProducto(obj, id_artpedido) {
  const tr = $(obj).parent().parent().parent().parent();
  const cantidad_bandejas = $(tr).find(".input-bandejas").val().trim();
  const cantidad_plantas = $(tr).find(".input-plantas").val().trim();
  const id_pedido = $("#modal-modificar-pedido").attr("x-id-pedido");
  const nombre_cliente = $("#modal-modificar-pedido").attr("x-nombre-cliente");
  if (!cantidad_bandejas.length || parseInt(cantidad_bandejas) < 1) {
    swal(
      "Ingresa la cantidad de Bandejas!",
      "Debe ser de 1 como mínimo.",
      "error"
    );
  } else if (!cantidad_plantas.length || parseInt(cantidad_plantas) < 1) {
    swal("Ingresa la cantidad de Plantas!", "", "error");
  } else {
    cancelEdit(obj);

    $.ajax({
      type: "POST",
      url: "data_ver_pedidos.php",
      data: {
        consulta: "modificar_pedido",
        id_artpedido: id_artpedido,
        cant_bandejas: cantidad_bandejas,
        cant_plantas: cantidad_plantas,
      },
      success: function (data) {
        if (data.trim() == "success") {
          swal("Modificaste el Pedido correctamente!", "", "success");
          modalModificarPedido(id_pedido, nombre_cliente);
          busca_entradas(currentTab);
        } else {
          swal(
            "Ocurrió un error al modificar el Producto del Pedido",
            data,
            "error"
          );
        }
      },
    });
  }
}

function modalProduccion(id, clear) {
  let productos = [];
  if (!clear && globalProductos && globalProductos.length) {
    productos = globalProductos.slice();
  } else {
    globalProductos = [];
    if (id) {
      productos.push(id);
      globalProductos.push(id);
    } else {
      $(".selected").each(function (i, e) {
        const id_artpedido = $(e).attr("x-id-artpedido");
        productos.push(id_artpedido);
        globalProductos.push(id_artpedido);
      });
    }
  }

  if (!productos.length) return;

  $(".tabla-produccion > tbody").html("");
  $("#modal-produccion").modal("show");
  $.ajax({
    url: "data_ver_pedidos.php",
    type: "POST",
    data: {
      consulta: "get_pedidos_para_produccion",
      productos: JSON.stringify(productos),
    },
    success: function (x) {
      if (x.length) {
        $("#modal-produccion").on("hidden.bs.modal", function () {
          busca_entradas(currentTab);
        });
        const data = JSON.parse(x);
        if (!data || !data.length) return;
        data.forEach((e) => {
          const {
            id_cliente,
            producto,
            id_artpedido,
            cant_plantas,
            cant_bandejas,
            cant_bandejas_nuevas,
            cant_bandejas_usadas,
            cant_semillas,
            fecha_ingreso,
            tipo_bandeja,
            id_producto,
            nombre_cliente,
            codigo,
            id_especie,
            id_variedad,
            semillas,
          } = e;
          const stock_nuevas = e.stock_nuevas < 0 ? 0 : e.stock_nuevas;
          const stock_usadas = e.stock_usadas < 0 ? 0 : e.stock_usadas;
          let strsemillas = "";
          if (semillas && semillas.length) {
            semillas.forEach(function (e, i) {
              const nombre_semillas = `${
                e.codigo ? e.codigo.toUpperCase() : ""
              } [${e.marca} - ${e.proveedor} - ${e.porcentaje}%]`;
              strsemillas += `
              <div class="row mt-2 mb-2">
                <div class="col">
                  <input type="search" readonly autocomplete="off" style='background-color: #F2F2F2 !important' class="form-control" value="${
                    e.id_cliente == "1"
                      ? `ROEL-${nombre_semillas}`
                      : nombre_semillas
                  }">
                </div>
              </div>
              <div class="w-100 d-flex flex-row">
                <input type="search" autocomplete="off" maxlength="12" class="form-control text-center input-semillas w-50" x-max="${
                  e.cantidad_stock
                }" x-id-stock-semillas="${
                e.id_stock
              }" placeholder="Cantidad" value="${e.cantidad}">
                <button class='btn btn-danger btn-sm fa fa-trash ml-2' onclick='eliminarSemillasPedido(${id_artpedido}, ${
                e.id_semillapedida
              })'></button>
              </div>
              <div class='w-100 d-flex justify-content-end'>  
                <span style='font-size:10px'>EN STOCK: ${
                  e.cantidad_stock
                }</span>
              </div>
              `;
            });
          }

          $(".tabla-produccion > tbody").append(
            `
            <tr class='text-center' x-id='${id_artpedido}' x-tipo-bandeja='${tipo_bandeja}' x-stock-nuevas='${stock_nuevas}' x-stock-usadas='${stock_usadas}' x-cant-original="${cant_bandejas}" x-codigo="${codigo}">
              <td>${producto}</td>
              <td>${nombre_cliente}</td>
              <td>
                <p>P: <span class="font-weight-bold">${cant_plantas}</span> | B: <span class='font-weight-bold'>${cant_bandejas}</span> de ${tipo_bandeja}</p>
                <div class="row">
                  <div class="col-6 text-left">
                    <span>NUEVAS:</span>
                  </div>
                  <div class="col-6 text-right">
                    <span class="${
                      stock_nuevas > 0 ? "text-success" : "text-danger"
                    }">STOCK: <span class="lbl-cant-nuevas">${stock_nuevas}</span></span>
                  </div>
                </div>
                <input type="search" autocomplete="off" maxlength="9" class="form-control text-center input-bandejas-nuevas mb-2" value="${cant_bandejas_nuevas}">
                <div class="row">
                  <div class="col-6 text-left">
                    <span>USADAS:</span>
                  </div>
                  <div class="col-6 text-right">
                    <span class="${
                      stock_usadas > 0 ? "text-success" : "text-danger"
                    }">STOCK: <span class="lbl-cant-usadas">${stock_usadas}</span></span>
                  </div>
                </div>
                <input type="search" autocomplete="off" maxlength="3" class="form-control text-center input-bandejas-usadas" value="${cant_bandejas_usadas}">
                
                ${
                  codigo == "HS" || codigo == "S"
                    ? `<div class="row mt-2">
                    <div class="col text-center">
                      <span>SEMILLAS: <b>${
                        cant_semillas && parseInt(cant_semillas) < 0
                          ? 0
                          : cant_semillas && parseInt(cant_semillas) >= 0
                          ? cant_semillas
                          : 0
                      }</b> <button class='btn btn-success btn-sm fa fa-plus-square ml-2' onclick='modalAgregarSemillas(${id_artpedido}, ${id_cliente}, "${codigo}", ${
                        id_especie ? id_especie : id_variedad
                      })'></button></span>
                    </div>
                </div>
                ${strsemillas}
                
                `
                    : ""
                }
                
              </td>
              <td>${fecha_ingreso}</td>
              <td>${id_producto}</td>
              <td>
                <button class="btn btn-success fa fa-save" onclick="enviarProduccion(${id_artpedido}, this)"></button>
              </td>
            </tr>
            `
          );
        });
        $(".tabla-produccion")
          .find("input")
          .on("propertychange input", function (e) {
            this.value = this.value.replace(/\D/g, "");
          });

        $(".select-semillas").selectpicker();
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminarProducto(id_artpedido, id_pedido, nombre_cliente) {
  swal(
    "Estás seguro/a de ELIMINAR el Producto del Pedido?",
    "ATENCIÓN: NO SE ELIMINA EL PEDIDO COMPLETO, SINO ESTE PRODUCTO EN PARTICULAR. VERIFICA SI EL PEDIDO TIENE OTROS PRODUCTOS.",
    {
      icon: "warning",
      buttons: {
        cancel: "NO",
        catch: {
          text: "SI, ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_seguimiento.php",
          data: { consulta: "eliminar_pedido", id_artpedido: id_artpedido },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste el Producto correctamente!", "", "success");
              modalModificarPedido(id_pedido, nombre_cliente);
              busca_entradas(currentTab);
            } else {
              swal(
                "Ocurrió un error al eliminar el Producto del Pedido",
                data,
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

function enviarProduccion(id_artpedido, obj) {
  const tr = $(obj).parent().parent();
  let cantidad_bandejas_nuevas = $(tr)
    .find(".input-bandejas-nuevas")
    .val()
    .trim();
  let cantidad_bandejas_usadas = $(tr)
    .find(".input-bandejas-usadas")
    .val()
    .trim();
  const stock_nuevas = parseInt($(tr).attr("x-stock-nuevas"));
  const stock_usadas = parseInt($(tr).attr("x-stock-usadas"));
  const tipo_bandeja = $(tr).attr("x-tipo-bandeja");
  const cantidad_bandejas_original = parseInt($(tr).attr("x-cant-original"));

  const codigo = $(tr).attr("x-codigo");
  cantidad_bandejas_nuevas = cantidad_bandejas_nuevas.length
    ? parseInt(cantidad_bandejas_nuevas)
    : 0;
  cantidad_bandejas_usadas = cantidad_bandejas_usadas.length
    ? parseInt(cantidad_bandejas_usadas)
    : 0;

  if ((codigo == "S" || codigo == "HS") && !$(".input-semillas").length) {
    swal(
      "El pedido no tiene Semillas asignadas, toca en el boton Agregar o Carga Semillas en Stock",
      "",
      "error"
    );
    return;
  }
  let semillas = null;
  if (codigo == "S" || codigo == "HS"){
    semillas = [];
    $(tr).find(".input-semillas").each(function(i,e){
      if (!$(e).val().trim().length){
        swal("Ingresa la cantidad de semillas", "", "error");
        return;
      }

      if (parseInt($(e).val().trim()) < 0){
        swal("Las cantidades de semillas deben ser mayores a cero", "", "error");
        return;
      }

      const max = $(e).attr("max");
      if (parseInt($(e).val().trim()) > parseInt(max)){
        swal("La cantidad de semillas es superior a la disponible", "", "error");
        return;
      }

      semillas.push({
        cantidad: $(e).val().trim(),
        id_stock_semillas: $(e).attr("x-id-stock-semillas")
      })
    })
  }

  if (stock_nuevas < 0) stock_nuevas = 0;
  if (stock_usadas < 0) stock_usadas = 0;

  if (
    cantidad_bandejas_nuevas + cantidad_bandejas_usadas >
    cantidad_bandejas_original
  ) {
    swal(
      "La cantidad de Bandejas ingresada es mayor a la que solicitó el cliente! ¿Continuar?",
      `PEDIDAS: ${cantidad_bandejas_original} | VAS A SEMBRAR: ${
        cantidad_bandejas_nuevas + cantidad_bandejas_usadas
      }`,
      {
        icon: "warning",
        buttons: {
          cancel: "NO",
          catch: {
            text: "SI, CONTINUAR",
            value: "catch",
          },
        },
      }
    ).then((value) => {
      switch (value) {
        case "catch":
          funcEnviarProduccion(
            id_artpedido,
            cantidad_bandejas_nuevas,
            cantidad_bandejas_usadas,
            semillas,
            tipo_bandeja,
            tr
          );

          break;

        default:
          break;
      }
    });
    return;
  } else if (
    cantidad_bandejas_nuevas + cantidad_bandejas_usadas <
    cantidad_bandejas_original
  ) {
    swal(
      "La cantidad de Bandejas ingresada es menor a la que solicitó el cliente!",
      "",
      "error"
    );
    return;
  } else if (cantidad_bandejas_nuevas > stock_nuevas) {
    swal("No hay suficientes bandejas NUEVAS en Stock", "", "error");
    return;
  } else if (cantidad_bandejas_usadas > stock_usadas) {
    swal("No hay suficientes bandejas USADAS en Stock", "", "error");
    return;
  }

  funcEnviarProduccion(
    id_artpedido,
    cantidad_bandejas_nuevas,
    cantidad_bandejas_usadas,
    semillas,
    tipo_bandeja,
    tr
  );
}
function funcEnviarProduccion(
  id_artpedido,
  cantidad_bandejas_nuevas,
  cantidad_bandejas_usadas,
  semillas,
  tipo_bandeja,
  tr
) {
  $.ajax({
    type: "POST",
    url: "data_ver_pedidos.php",
    data: {
      consulta: "enviar_produccion",
      id_artpedido: id_artpedido,
      cantidad_bandejas_nuevas: cantidad_bandejas_nuevas,
      cantidad_bandejas_usadas: cantidad_bandejas_usadas,
      tipo_bandeja: tipo_bandeja,
      semillas: semillas ? JSON.stringify(semillas) : null,
    },
    success: function (data) {
      if (data.trim() == "success") {
        swal("Enviaste el Pedido a Producción!", "", "success");
        $(tr).remove();
        if ($(".tabla-produccion > tbody > tr").length == 0) {
          $("#modal-produccion").modal("hide");
        }

        if ($("#ModalVerEstado").css("display") == "block")
          MostrarModalEstado(id_artpedido, "", "");
      } else {
        swal("Ocurrió un error al enviar a Producción", data, "error");
      }
    },
  });
}

function modalAgregarSemillas(id_artpedido, id_cliente, tipo, id_producto) {
  loadSemillasSelectModificar(id_cliente, id_producto, tipo, id_artpedido);

  $("#input_cantidad_modificar").on("propertychange input", function () {
    this.value = this.value.replace(/\D/g, "");
  });
  $("#input_cantidad_modificar").val("");

  $("#modal-modificar-semillas").attr("x-id-artpedido", id_artpedido);

  $("#modal-modificar-semillas").modal("show");
}

function loadSemillasSelectModificar(id_cliente, id, tipo, id_artpedido) {
  $.ajax({
    beforeSend: function () {
      $("#select_semillas_modificar").html("Cargando semillas...");
    },
    type: "POST",
    url: "data_ver_semillas.php",
    data: {
      consulta: "cargar_semillas_select_modificar",
      id: id,
      tipo: tipo,
      id_cliente: id_cliente,
      plantinera: "1",
      id_artpedido: id_artpedido,
    },
    success: function (data) {
      $("#select_semillas_modificar")
        .html(data)
        .val("default")
        .selectpicker("refresh");
    },
  });
}

function asignarSemillas() {
  const id_stock_semillas = $("#select_semillas_modificar")
    .find("option:selected")
    .val();
  const cantidad = $("#input_cantidad_modificar").val().trim();

  const id_artpedido = $("#modal-modificar-semillas").attr("x-id-artpedido");

  if (
    !id_stock_semillas ||
    !id_stock_semillas.length ||
    id_stock_semillas == "0" ||
    id_stock_semillas == 0
  ) {
    swal(
      "Selecciona una Semilla del Stock",
      "Si no aparece en el menú, deberás cargar Semillas al Stock",
      "error"
    );
    return;
  }

  if (!cantidad || !cantidad.length || parseInt(cantidad) < 1) {
    swal("Ingresa una cantidad mayor a cero", "", "error");
    return;
  }

  $.ajax({
    beforeSend: function () {
      $("#modal-modificar-semillas").modal("hide");
    },
    type: "POST",
    url: "data_ver_semillas.php",
    data: {
      consulta: "modificar_semillas_pedido",
      id_stock_semillas: id_stock_semillas,
      cantidad: cantidad,
      id_artpedido: id_artpedido,
    },
    success: function (data) {
      if (data.trim() == "success") {
        swal("Asignaste las semillas correctamente!", "", "success");
        modalProduccion();
      } else {
        swal("Ocurrió un error", data, "error");
      }
    },
  });
}

function eliminarSemillasPedido(id_artpedido, id_semillapedida) {
  swal("Estás seguro/a de QUITAR este Sobre de Semillas del Pedido?", "", {
    icon: "warning",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, QUITAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_pedidos.php",
          data: {
            consulta: "quitar_semillas",
            id_artpedido: id_artpedido,
            id_semillapedida: id_semillapedida,
          },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Quitaste las Semillas correctamente!", "", "success");
              modalProduccion(id_artpedido);
            } else {
              swal("Ocurrió un error al quitar las Semillas", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}


function migrarSemillas(){
  $.ajax({
    type: "POST",
    url: "data_ver_pedidos.php",
    data: {
      consulta: "migrar_semillas",
    },
    success: function (data) {
      if (data.trim() == "success") {
        swal("Quitaste las Semillas correctamente!", "", "success");
      } else {
        swal("Ocurrió un error al quitar las Semillas", data, "error");
      }
    },
  });
}

function getQueryVariable(variable) {
  const query = window.location.href.substring(window.location.href.indexOf(".php") + 5).replace("#", "&");
  if (query && query.trim().length && query.includes("=")) {
    let vars = query.split("&");
    if (vars.length > 0 && vars[0] != "") {
      for (let i = 0; i < vars.length; i++) {
        let pair = vars[i].split("=");
        if (pair[0] == variable) {
          return pair[1];
        }
      }
    }
  }
  return null;
}

function abrirModalEditarObservacion(idArticuloPedido, idPedido, tipo, texto) {
    $('#obs-id-artpedido').val(idArticuloPedido);
    $('#obs-id-pedido').val(idPedido);
    $('#obs-type').val(tipo);
    $('#obs-text').val(texto);

    let titulo = '';
    switch (tipo) {
        case 'producto':
            titulo = 'Observación del Producto';
            break;
        case 'problema':
            titulo = 'Problema del Producto';
            break;
        case 'pedido':
            titulo = 'Observación del Pedido';
            break;
    }
    $('#modal-obs-title').text(titulo);
    $('#modal-editar-observacion').modal('show');
}

function guardarObservacion() {
    const id_artpedido = $('#obs-id-artpedido').val();
    const id_pedido = $('#obs-id-pedido').val();
    const type = $('#obs-type').val();
    const text = $('#obs-text').val();

    $.ajax({
        url: 'data_ver_pedidos.php',
        type: 'POST',
        dataType: 'html',
        data: {
            consulta: 'guardar_observacion',
            id_artpedido: id_artpedido,
            id_pedido: id_pedido,
            type: type,
            text: text
        },
        success: function (response) {
            if (response.trim() === 'success') {
                Swal.fire('Guardado!', 'La observación ha sido guardada.', 'success');
                $('#modal-editar-observacion').modal('hide');
                busca_entradas(currentTab); // Refresh table
            } else {
                Swal.fire('Error', 'Hubo un error al guardar la observación: ' + response, 'error');
            }
        },
        error: function (xhr, status, error) {
            Swal.fire('Error', 'Hubo un error de conexión: ' + error, 'error');
        }
    });
}
