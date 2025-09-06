let miTab;
function openTab(evt, tabName) {
  let i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  miTab = tabName;
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  $("#input-search").val("");
  if (miTab == "tab-esquejes") {
    loadEsquejes();
  } else if (miTab == "tab-semillas") {
    loadSemillas();
  }
  else if (miTab == "tab-interior") {
    loadPedidos("interior");
  }
  else if (miTab == "tab-exterior") {
    loadPedidos("exterior");
  }
  else if (miTab == "tab-vivero") {
    loadPedidos("vivero");
  }
  else if (miTab == "tab-packs") {
    
    loadPedidos("packs");
  }
  else if (miTab == "tab-invitro") {
    loadPedidos("invitro");
  }
}

$(document).ready(function () {
  if (!document.location.href.includes("ver_seguimiento")) return;

  document.getElementById("defaultOpen").click();
  let html2 = "";
  for (let i = 0; i < 300; i++) {
    html2 += `<tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  </tr>`;
  }
  $("#tabla-semillas,#tabla-esquejes,#tabla-interior,#tabla-exterior,#tabla-vivero,#tabla-packs,#tabla-invitro").find("tbody").html(html2);

  oncontextmenu = (e) => {
    if ($("#ModalVerEstado").css("display") == "block") return;
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
    menu.style = `top:${e.clientY}px;left:${e.pageX - 40}px`;
    menu.onmouseleave = () => (ctxmenu.outerHTML = "");
    menu.innerHTML = `<p onclick='DeseleccionarTodo()'>Deseleccionar Todo</p>
                        <p onclick='cambiarEtapa(0)'>Pasar a Etapa 0</p>
                        <p onclick='cambiarEtapa(1)'>Pasar a Etapa 1</p>
                        <p onclick='cambiarEtapa(2)'>Pasar a Etapa 2</p>
                        <p onclick='cambiarEtapa(3)'>Pasar a Etapa 3</p>
                        <p onclick='cambiarEtapa(4)'>Pasar a Etapa 4</p>
                        <p onclick='cambiarEtapa(5)'>Pasar a Etapa 5</p>
                        <p onclick='cambiarEtapa(-10)'>DEVOLVER A PENDIENTES</p>
                        `;
    document.body.appendChild(menu);
  };
});

function loadEsquejes() {
  const busqueda = $("#input-search").val().trim();
  $.ajax({
    beforeSend: function () {
      $("#tabla-esquejes td").html("");
    },
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: { consulta: "cargar_esquejes", busqueda: busqueda },
    success: function (x) {
      if (x.trim().length) {
        const pedidos = JSON.parse(x);
        if (pedidos.length) {
          for (let j = 0; j < 6; j++) {
            let index = 0;
            pedidos.forEach(function (e, i) {
              if (e.estado == 6) {
                e.estado = 5;
                e.es_entrega_parcial = true;
              }
              if (e.estado == j) {
                $("#tabla-esquejes > tbody")
                  .find("tr")
                  .eq(index)
                  .find(`td:eq(${j})`)
                  .html(MakeBox(e, e.es_entrega_parcial ? 6 : j, "esqueje"));
                index++;
              }
            });
          }
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });

  DeseleccionarTodo();
}

function loadPedidos(tipo) {
  const busqueda = $("#input-search").val().trim();
  $.ajax({
    beforeSend: function () {
      $(`#tabla-${tipo} td`).html("");
    },
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: { consulta: `cargar_pedidos`, tipo, busqueda: busqueda },
    success: function (x) {
      if (x.trim().length) {
        const pedidos = JSON.parse(x);
        if (pedidos.length) {
          for (let j = 0; j < 6; j++) {
            let index = 0;
            pedidos.forEach(function (e, i) {
              if (e.estado == 6) {
                e.estado = 5;
                e.es_entrega_parcial = true;
              }
              if (e.estado == j) {
                $(`#tabla-${tipo} > tbody`)
                  .find("tr")
                  .eq(index)
                  .find(`td:eq(${j})`)
                  .html(MakeBox(e, e.es_entrega_parcial ? 6 : j, tipo));
                index++;
              }
            });
          }
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });

  DeseleccionarTodo();
}

function loadSemillas() {
  const busqueda = $("#input-search").val().trim();
  $.ajax({
    beforeSend: function () {
      $("#tabla-semillas td").html("");
    },
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: { consulta: "cargar_semillas", busqueda: busqueda },
    success: function (x) {
      console.log(x)
      if (x.trim().length) {
        const pedidos = JSON.parse(x);
        if (pedidos.length) {
          for (let j = 0; j < 6; j++) {
            let index = 0;
            pedidos.forEach(function (e, i) {
              if (e.estado == 6) {
                e.estado = 5;
                e.es_entrega_parcial = true;
              }
              if (e.estado == j) {
                $("#tabla-semillas > tbody")
                  .find("tr")
                  .eq(index)
                  .find(`td:eq(${j})`)
                  .html(MakeBox(e, e.es_entrega_parcial ? 6 : j, "semilla"));
                index++;
              }
            });
          }
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });

  DeseleccionarTodo();
}

function cambiarEtapa(etapa) {
  if (!$(".selected").length) return;

  let es_entrega_parcial = false;
  $(".selected").each(function (i, e) {
    if ($(e).attr("x-parcial") == "1" || $(e).attr("x-parcial") == 1) {
      es_entrega_parcial = true;
    }
  });

  if (es_entrega_parcial) {
    swal("No se puede mover un producto que ya fue entregado!", "", "error");
    return;
  }

  let puede = true;
  if (etapa == -10) {
    $(".selected").each(function (i, e) {
      if ($(e).attr("x-estado") != "0") {
        puede = false;
      }
    });
  }

  if (!puede) {
    swal("Los pedidos deben estar en la ETAPA 0!", "", "error");
    return;
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
        $(".loading-wrapper").show();
        $(".selected").each(function (i, e) {
          const id_artpedido = $(e).attr("x-id-real");
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
            if (data.trim() == "success") {
              swal(
                "Cambiaste de Etapa a los productos seleccionados!",
                "",
                "success"
              );
              if (!miTab || miTab == "tab-esquejes") loadEsquejes();
              else if (miTab == "tab-semillas") loadSemillas();
              else loadPedidos(miTab.replace("tab-",""))
            } else {
              swal(
                "Ocurrió un error cambiar los productos de Etapa",
                x,
                "error"
              );
            }
            $(".selected").removeClass("selected");
            $("td").css({ "background-color": "" });
          },
        });

        break;

      default:
        break;
    }
  });
}

function MakeBox(producto, index, tipo_producto) {
  let colores = [
    "#ffffff",
    "#ffffff",
    "#ffffff",
    "#ffffff",
    "#ffffff",
    "#ffffff",
    "#ffffff",
  ];
  if (tipo_producto == "esqueje") {
    colores = [
      "#D8EAD2",
      "#B6D7A8",
      "#A9D994",
      "#A2D98A",
      "#99D87D",
      "#8AD868",
      "#FBF07D",
    ];
  } else if (tipo_producto == "semilla") {
    colores = [
      "#FFF2CD",
      "#FFE59A",
      "#FED966",
      "#F2C234",
      "#E0B42F",
      "#CEA62E",
      "#FBF07D",
    ];
  }
  else {
    colores = [
      "#D8EAD2",
      "#B6D7A8",
      "#A9D994",
      "#A2D98A",
      "#99D87D",
      "#8AD868",
      "#FBF07D",
    ];
  }
  const date = moment(producto.fecha);
  const fecha = date.format("DD/MM/YY HH:mm");

  let codigo =
    producto.iniciales +
    producto.id_pedido_interno +
    "/M" +
    date.format("MM") +
    "/" +
    date.format("DD") +
    "/" +
    producto.codigo +
    producto.id_interno.padStart(2, "0") +
    (producto.id_especie ? "-" + producto.id_especie.padStart(2, "0") : "") +
    "/" +
    producto.cant_plantas +
    "/" +
    producto.id_cliente.padStart(2, "0"); //"T1/M3/07/S130/1000";

  let observacionproblema = "";
  let observacion = "";
  if (producto.observacionproblema && producto.problema) {
    observacionproblema = `<div  style="font-size: 0.8em; word-wrap: break-all;"  class='bg-light text-danger ml-1 mr-1 mb-1'>${
      producto.observacionproblema.length > 20
        ? producto.observacionproblema.substring(0, 17) + "..."
        : producto.observacionproblema
    }</div>`;
  }

  if (producto.observacion) {
    observacion = `<div  style="font-size: 0.8em; word-wrap: break-all;"  class='bg-light text-primary ml-1 mr-1 mb-1'>${
      producto.observacion.length > 20
        ? producto.observacion.substring(0, 17) + "..."
        : producto.observacion
    }</div>`;
  }
  let especie = "";
  if (producto.id_especie) {
    especie = `<span class='${
      producto.problema ? "text-light" : "text-primary"
    }'>${producto.nombre_especie}</span><br>`;
  }

  let html = `<div x-id-real="${
    producto.id_artpedido
  }" x-id="${codigo}" x-codigo="${codigo}" x-estado='${producto.estado}' x-parcial='${
    producto.es_entrega_parcial ? 1 : 0
  }' class='cajita' onClick='toggleSelection(this)' style='word-wrap: break-word;touch-action: none;cursor:pointer;background-color:${
    producto.problema ? "#DA6E6B" : colores[index]
  };font-size:1.0em;'
      ondblclick='MostrarModalEstado(${producto.id_artpedido}, "${codigo}", "${
    producto.nombre_cliente
  }", ${producto.id_cliente})'>
      <span>${codigo}<br></span>
      <span style='font-weight:bold;'>${producto.nombre_variedad}<br>
      ${especie}
      ${producto.nombre_cliente}<br>Cant. Plantas: ${producto.cant_plantas}
        ${
          producto.cant_bandejas
            ? `<br>
        <small>(${producto.cant_bandejas} band. de ${producto.tipo_bandeja})</small>`
            : ""
        }
        <br>
        <span style="font-size: 0.7em">
        ${fecha}</span>
        ${observacionproblema}
        ${observacion}
        </span></div>`;

  return html;
}

function addToLista(objeto) {
  let id = $(objeto).attr("x-id-real");
  if ($(objeto).hasClass("selected")) {
    listaseleccionados.push(id);
    listacolumnas.push($(objeto).closest("td").index());
  } else {
    let index = listaseleccionados.indexOf(id);
    if (index > -1) {
      listacolumnas.splice(index, 1);
    }
    listaseleccionados = listaseleccionados.filter((e) => e !== id);
  }
}

async function MostrarModalEstado(
  id_artpedido,
  codigo_producto,
  nombre_cliente,
  id_cliente
) {
  $("#ModalVerEstado .loading-wrapper").removeClass("d-none").show();

  $(".title-pedido").html(`${codigo_producto} (${nombre_cliente})`);
  $("#ModalVerEstado").attr("x-id-artpedido", id_artpedido);
  $("#ModalVerEstado").attr("x-codigo", codigo_producto);
  $("#ModalVerEstado").attr("x-nombre-cliente", nombre_cliente);
  $("#ModalVerEstado").attr("x-id-cliente", id_cliente);
  $("#btn-guardar-obs,#btn-guardar-obs-problema").attr("disabled", true);
  $(".btn-verfoto").addClass("d-none");

  $.ajax({
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: { id_artpedido: id_artpedido, consulta: "cargar_detalle_pedido" },
    success: function (x) {
      if (x.trim().length && x != "nodata") {
        try {
          const data = JSON.parse(x);
          if (!data) return;
          const {
            estado,
            cant_plantas,
            cant_bandejas,
            cant_bandejas_usadas,
            cant_bandejas_nuevas,
            nombre_variedad,
            nombre_especie,
            codigo,
            id_interno,
            observacion,
            observacionproblema,
            observacion_pedido,
            problema,
            fecha_entrega_real,
            fecha_etapa1,
            fecha_etapa2,
            fecha_etapa3,
            fecha_etapa4,
            fecha_etapa5,
            fecha_ingreso,
            mesada,
            cant_semillas,
            cantidad_entregada,
            semillas,
          } = data;
          const date = moment(fecha_ingreso).format("DD/MM/YY");

          $(".label-semillas").html("");

          let strsemillas = "";
          if (semillas && semillas.length) {
            semillas.forEach(function (e, i) {
              strsemillas += `
                <span style="font-size:12px" class="text-primary">${
                  e.cantidad
                } de ${
                e.id_cliente == 1 || e.id_cliente == "1" ? "ROEL-" : ""
              }${e.codigo ? e.codigo.toUpperCase() : ""} [${e.marca} - ${
                e.proveedor
              } - ${e.porcentaje}%]</span><br>
              `;
            });
          }

          if (cant_semillas) {
            $(".label-semillas").html(
              `<h5>Semillas: ${cant_semillas}</h5> ${strsemillas}`
            );
          }

          $(".label-etapa").html(generaBoxEstado(estado, false, codigo));

          $("#btn-produccion").unbind("click");
          $("#btn-produccion").addClass("d-none");
          if (estado == -10) {
            // ESTA EN PENDIENTES
            $("#btn-produccion").removeClass("d-none");
            $("#btn-produccion").attr(
              "onClick",
              `modalProduccion(${id_artpedido}, true)`
            );
          } else {
            $("#btn-produccion").removeAttr("onClick");
          }

          $("#btn-modificar-cliente").unbind("click");
          $("#btn-modificar-cliente").addClass("d-none");
          if (estado == -10 || estado == 0 || estado == 1 || estado == 2 || estado == 3 || estado == 4 || estado == 5) {
            // ESTA EN PENDIENTES
            $("#btn-modificar-cliente").removeClass("d-none");
            $("#btn-modificar-cliente").attr(
              "onClick",
              `modalModificarCliente(${id_artpedido})`
            );
          } else {
            $("#btn-modificar-cliente").removeAttr("onClick");
          }

          $("#btn-entregar,#btn-enviar-stock").addClass("d-none");
          $("#btn-entregar").unbind("click");
          if ((estado == 5 || estado == 6)) {
            // ETAPA 5 O ENTREGA PARCIAL
            $("#btn-entregar").removeClass("d-none");
            $("#btn-entregar").on("click", function (e) {
              modalEntrega(id_artpedido, cant_plantas);
            });
          }

          if ((estado == 5 || estado == 6)) {
            // ETAPA 5 O ENTREGA PARCIAL
            $("#btn-enviar-stock").removeClass("d-none");
            $("#btn-enviar-stock").on("click", function (e) {
              enviarStock(id_artpedido, codigo, nombre_cliente, cantidad_entregada, cant_plantas);
            });
          }

          $(".label-producto").html(
            `${nombre_variedad} ${
              nombre_especie ? nombre_especie : ""
            } (${codigo}${id_interno.padStart(2, "0")})`
          );
          const cant_bandejas_reales =
            parseInt(cant_bandejas_usadas) + parseInt(cant_bandejas_nuevas);
          $(".label-cantidad").html(cant_plantas);
          $(".label-band-pedidas").html(`(${cant_bandejas} Band.)`);
          $(".label-band-sembradas").html(
            `${
              estado >= 0
                ? cant_bandejas_reales > 0
                  ? cant_bandejas_reales
                  : cant_bandejas
                : 0
            }`
          );

          $("#btn-modificar-cantidad").addClass("d-none");
          $("#btn-modificar-cantidad").unbind("click");
          if (estado >= 0 && estado < 6) {
            $("#btn-modificar-cantidad").removeClass("d-none");
            $("#btn-modificar-cantidad").on("click", function () {
              ModificarCantidadPedida(id_artpedido, cant_plantas, id_cliente);
            });
          }

          $(".label-fecha-ingreso").html(date);
          $(".label-etapa1").html(
            fecha_etapa1 ? moment(fecha_etapa1).format("DD/MM/YY HH:mm") : "-"
          );
          $(".label-etapa2").html(
            fecha_etapa2 ? moment(fecha_etapa2).format("DD/MM/YY HH:mm") : "-"
          );
          $(".label-etapa3").html(
            fecha_etapa3 ? moment(fecha_etapa3).format("DD/MM/YY HH:mm") : "-"
          );
          $(".label-etapa4").html(
            fecha_etapa4 ? moment(fecha_etapa4).format("DD/MM/YY HH:mm") : "-"
          );
          $(".label-etapa5").html(
            fecha_etapa5 ? moment(fecha_etapa5).format("DD/MM/YY HH:mm") : "-"
          );

          $(".label-fecha-entrega").html(
            fecha_entrega_real
              ? moment(fecha_entrega_real).format("DD/MM/YY HH:mm")
              : "-"
          );

          $("#btn-cancelar-pedido").addClass("d-none");
          $("#btn-cancelar-pedido").unbind("click");
          if (estado < 6 && estado >= 0) {
            //BTN CANCELAR PEDIDO
            $("#btn-cancelar-pedido").removeClass("d-none");
            $("#btn-cancelar-pedido").on("click", function (e) {
              cancelarPedido(id_artpedido, codigo_producto, nombre_cliente, id_cliente);
            });
          }
          let id_usuario;
          async () => {
            await $.get(
              "get_session_variable.php",
              { requested: "id_usuario" },
              function (data) {
                if (data.trim().length) {
                  id_usuario = data.trim();
                }
              }
            );
          };

          if (id_usuario == "1") {
            $("#btn-eliminar-pedido").removeClass("d-none");
            $("#btn-eliminar-pedido").on("click", function (e) {
              eliminarPedido(id_artpedido, codigo_producto, nombre_cliente, id_cliente);
            });
          } else {
            $("#btn-eliminar-pedido").addClass("d-none");
            $("#btn-eliminar-pedido").unbind("click");
          }

          $(".label-mesada").html(mesada ? mesada : "-");
          if (estado < 6 && estado >= 1) {
            //MESADA
            $("#btn-asignar-mesada").removeClass("d-none");
            $("#btn-asignar-mesada").on("click", function (e) {
              asignarMesada(id_artpedido, codigo_producto, nombre_cliente);
            });
          } else {
            $("#btn-asignar-mesada").addClass("d-none");
            $("#btn-asignar-mesada").unbind("click");
          }

          $("#input-observaciones").val(observacion ? observacion : "");
          $("#input-observaciones-pedido").val(observacion_pedido ? observacion_pedido : "");
          
          $("#input-problema").val(
            observacionproblema ? observacionproblema : ""
          );

          if (problema) {
            $("#btn-solucionado").removeClass("d-none");
            $("#btn-solucionado").attr("disabled", false);
          } else {
            $("#btn-solucionado").addClass("d-none");
            $("#btn-solucionado").attr("disabled", true);
          }

          (async () => {
            // true
            let existe1 = await imageExists(`imagenes/${id_artpedido}.jpg`);
            if (existe1) {
              $("#btn-verfoto1,#btn-eliminarfoto1").removeClass("d-none");
              $("#btn-verfoto1").on("click", function () {
                verFoto(id_artpedido, 1);
              });
              $("#btn-eliminarfoto1").on("click", function () {
                eliminarFoto(
                  `imagenes/${id_artpedido}.jpg`,
                  id_artpedido,
                  codigo_producto,
                  nombre_cliente
                );
              });
            } else {
              $("#btn-verfoto1,#btn-eliminarfoto1").addClass("d-none");
            }

            let existe2 = await imageExists(`imagenes/${id_artpedido}_2.jpg`);
            if (existe2) {
              $("#btn-verfoto2,#btn-eliminarfoto2").removeClass("d-none");
              $("#btn-verfoto2").on("click", function () {
                verFoto(id_artpedido, 2);
              });
              $("#btn-eliminarfoto2").on("click", function () {
                eliminarFoto(
                  `imagenes/${id_artpedido}_2.jpg`,
                  id_artpedido,
                  codigo_producto,
                  nombre_cliente
                );
              });
            } else {
              $("#btn-verfoto2,#btn-eliminarfoto2").addClass("d-none");
            }

            let existe3 = await imageExists(`imagenes/${id_artpedido}_3.jpg`);
            if (existe3) {
              $("#btn-verfoto3,#btn-eliminarfoto3").removeClass("d-none");
              $("#btn-verfoto3").on("click", function () {
                verFoto(id_artpedido, 3);
              });
              $("#btn-eliminarfoto3").on("click", function () {
                eliminarFoto(
                  `imagenes/${id_artpedido}_3.jpg`,
                  id_artpedido,
                  codigo_producto,
                  nombre_cliente
                );
              });
            } else {
              $("#btn-verfoto3,#btn-eliminarfoto3").addClass("d-none");
            }

            let existe4 = await imageExists(`imagenes/${id_artpedido}_4.jpg`);
            if (existe4) {
              $("#btn-verfoto4,#btn-eliminarfoto4").removeClass("d-none");
              $("#btn-verfoto4").on("click", function () {
                verFoto(id_artpedido, 4);
              });
              $("#btn-eliminarfoto4").on("click", function () {
                eliminarFoto(
                  `imagenes/${id_artpedido}_4.jpg`,
                  id_artpedido,
                  codigo_producto,
                  nombre_cliente
                );
              });
            } else {
              $("#btn-verfoto4,#btn-eliminarfoto4").addClass("d-none");
            }

            let existe5 = await imageExists(`imagenes/${id_artpedido}_5.jpg`);
            if (existe5) {
              $("#btn-verfoto5,#btn-eliminarfoto5").removeClass("d-none");
              $("#btn-verfoto5").on("click", function () {
                verFoto(id_artpedido, 5);
              });
              $("#btn-eliminarfoto5").on("click", function () {
                eliminarFoto(
                  `imagenes/${id_artpedido}_5.jpg`,
                  id_artpedido,
                  codigo_producto,
                  nombre_cliente
                );
              });
            } else {
              $("#btn-verfoto5,#btn-eliminarfoto5").addClass("d-none");
            }

            let existe6 = await imageExists(`imagenes/${id_artpedido}_6.jpg`);
            if (existe6) {
              $("#btn-verfoto6,#btn-eliminarfoto6").removeClass("d-none");
              $("#btn-verfoto6").on("click", function () {
                verFoto(id_artpedido, 6);
              });
              $("#btn-eliminarfoto6").on("click", function () {
                eliminarFoto(
                  `imagenes/${id_artpedido}_6.jpg`,
                  id_artpedido,
                  codigo_producto,
                  nombre_cliente
                );
              });
            } else {
              $("#btn-verfoto6,#btn-eliminarfoto6").addClass("d-none");
            }
          })();

          //
          $(`#btn-control1,#btn-control2,#btn-control3,#btn-control4,#btn-control5,#btn-control6,
        #btn-verfoto1,#btn-eliminarfoto1,#btn-verfoto2,#btn-eliminarfoto2,#btn-verfoto3,#btn-eliminarfoto3,#btn-verfoto4,#btn-eliminarfoto4,#btn-verfoto5,#btn-eliminarfoto5,#btn-verfoto6,#btn-eliminarfoto6
        `).off("click");
          $("#btn-control1").on("click", function () {
            modalControl(0, id_artpedido);
          });
          $("#btn-control2").on("click", function () {
            modalControl(1, id_artpedido);
          });
          $("#btn-control3").on("click", function () {
            modalControl(2, id_artpedido);
          });
          $("#btn-control4").on("click", function () {
            modalControl(3, id_artpedido);
          });
          $("#btn-control5").on("click", function () {
            modalControl(4, id_artpedido);
          });
          $("#btn-control6").on("click", function () {
            modalControl(5, id_artpedido);
          });

          $("#ModalVerEstado .loading-wrapper").addClass("d-none").hide();
        } catch (error) {
          swal("Error de conexión. Intenta nuevamente.", x, "error");
          console.log(x);
          console.log(error);
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });

  let modal = document.getElementById("ModalVerEstado");
  modal.style.display = "block";
}

function modalControl(etapa, id_artpedido) {
  $(".title-etapa").html(etapa);
  generarTabla(etapa, id_artpedido);
  $("#ModalControl").modal("show");
}

function generarTabla(etapa, id_artpedido) {
  if (etapa == 0) {
    $(".tabla-control > thead").html(`
      <tr>
        <th scope="col">Fecha Siembra</th>
        <th scope="col">Bandejas Sembradas</th>
        <th scope="col">T/C° (S) AM</th>
        <th scope="col">T/C° (S) PM</th>
        <th scope="col">T/C° (A) AM</th>
        <th scope="col">T/C° (A) PM</th>
        <th scope="col" style="width:25%">Observación</th>
        <th></th>
      </tr>
    `);
    $(".tabla-control > tbody").html("");
    for (let i = 1; i < 5; i++) {
      $(".tabla-control > tbody").append(`
      <tr>
        <td scope="row">
          <input type='text' data-date-format='dd/mm/yy' value="DD/MM/YYYY" class="datepicker form-control fecha-siembra-picker datepicker-control text-center"
           disabled="true"/>
        </td>
        <td>
          <input style="font-weight: bold;" type="search" min="0" maxlength="4" step="1"
          class="form-control text-center input-control input-cantidad-bandejas only-digits" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-s-am" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-s-pm" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-a-am" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-a-pm" readonly="readonly">
        </td>
        <td>
          <input type="search" autocomplete="off" maxlength="150"
          class="form-control input-control input-observacion" readonly="readonly">
        </td>
        <td>
          <button class='btn btn-primary btn-sm fa fa-edit btn-edit' onclick="editarControl(0, $(this).parent().parent())"></button>
          <div class='btn-container d-none'>
            <button class='btn btn-danger btn-sm fa fa-close' onclick="cancelEditing($(this).parent().parent().parent())"></button>
            <button class='btn btn-success btn-sm fa fa-save' onclick="guardarControl(0, ${id_artpedido}, $(this).parent().parent().parent(), ${i})"></button>
          </div>
        </td>
      </tr>
      `);
    }
  } else if (etapa >= 1 && etapa <= 3) {
    $(".tabla-control > thead").html(`
      <tr>
        <th scope="col">Mesón</th>
        <th scope="col">Cantidad Bandejas</th>
        <th scope="col" style="width: 140px">Fecha Control</th>
        <th scope="col">%</th>
        <th scope="col">T/C° (S) AM</th>
        <th scope="col">T/C° (S) PM</th>
        <th scope="col">T/C° (A) AM</th>
        <th scope="col">T/C° (A) PM</th>
        <th scope="col" style="width:260px">Observación</th>
        <th></th>
      </tr>
    `);
    $(".tabla-control > tbody").html("");
    for (let i = 1; i < 5; i++) {
      $(".tabla-control > tbody").append(`
      <tr>
        <td scope="row">
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-meson" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-cantidad-bandejas only-digits" readonly="readonly">
        </td>
        <td>
          <input type='text' data-date-format='dd/mm/yy' value="DD/MM/YYYY" class="datepicker form-control fecha-control-picker datepicker-control text-center"
          disabled="true"/>
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-porcentaje1" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-s-am" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-s-pm" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-a-am" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-a-pm" readonly="readonly">
        </td>
        <td>
          <input type="search" autocomplete="off" maxlength="150"
          class="form-control input-control input-observacion" readonly="readonly">
        </td>
        <td>
          <button class='btn btn-primary btn-sm fa fa-edit btn-edit' onclick="editarControl(1, $(this).parent().parent())"></button>
          <div class='btn-container d-none'>
            <button class='btn btn-danger btn-sm fa fa-close' onclick="cancelEditing($(this).parent().parent().parent())"></button>
            <button class='btn btn-success btn-sm fa fa-save' onclick="guardarControl(${etapa}, ${id_artpedido}, $(this).parent().parent().parent(), ${i})"></button>
          </div>
        </td>
      </tr>
      `);
    }
  } else if (etapa == 4) {
    $(".tabla-control > thead").html(`
      <tr>
        <th scope="col">Mesón</th>
        <th scope="col">Bandejas Repicadas</th>
        <th scope="col">Bandejas Perdidas</th>
        <th scope="col" style="width:140px">Fecha Repique</th>
        <th scope="col">T/C° (S) AM</th>
        <th scope="col">T/C° (S) PM</th>
        <th scope="col">T/C° (A) AM</th>
        <th scope="col">T/C° (A) PM</th>
        <th scope="col" style="width:25%">Observación</th>
        <th></th>
      </tr>
    `);
    $(".tabla-control > tbody").html("");
    for (let i = 1; i < 5; i++) {
      $(".tabla-control > tbody").append(`
      <tr>
        <td scope="row">
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-meson" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-cantidad-bandejas-repicadas only-digits" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-cantidad-bandejas-perdidas only-digits" readonly="readonly">
        </td>
        
        <td>
          <input type='text' disabled="true" data-date-format='dd/mm/yy' value="DD/MM/YYYY" class="datepicker form-control fecha-repique-picker datepicker-control text-center"/>
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-s-am" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-s-pm" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-a-am" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-t-a-pm" readonly="readonly">
        </td>
        <td>
          <input type="search" autocomplete="off" maxlength="150"
          class="form-control input-observacion" readonly="readonly">
        </td>
        <td>
          <button class='btn btn-primary btn-sm fa fa-edit btn-edit' onclick="editarControl(4, $(this).parent().parent())"></button>
          <div class='btn-container d-none'>
            <button class='btn btn-danger btn-sm fa fa-close' onclick="cancelEditing($(this).parent().parent().parent())"></button>
            <button class='btn btn-success btn-sm fa fa-save' onclick="guardarControl(4, ${id_artpedido}, $(this).parent().parent().parent(), ${i})"></button>
          </div>
        </td>
      </tr>
      `);
    }
  } else if (etapa == 5) {
    $(".tabla-control > thead").html(`
      <tr>
        <th scope="col">Mesón</th>
        <th scope="col">Bandejas Finales</th>
        <th scope="col">Estado</th>
        <th scope="col">Fecha Disponibilidad</th>
        <th scope="col">Fecha Entrega</th>
        <th scope="col" style="width:25%">Observación</th>
        <th style="width:60px"></th>
      </tr>
    `);
    $(".tabla-control > tbody").html("");
    for (let i = 1; i < 5; i++) {
      $(".tabla-control > tbody").append(`
      <tr>
        <td scope="row">
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-meson" readonly="readonly">
        </td>
        <td>
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-bandejas-finales only-digits" readonly="readonly">
        </td>
        <td scope="row">
          <input style="font-weight: bold;" type="search" autocomplete="off" maxlength="4"
          class="form-control text-center input-control input-estado" readonly="readonly">
        </td>
        <td>
          <input type='text' disabled="true" data-date-format='dd/mm/yy' value="DD/MM/YYYY" class="datepicker form-control fecha-disponibilidad-picker datepicker-control text-center"/>
        </td>
        <td>
          <input type='text' disabled="true" data-date-format='dd/mm/yy' value="DD/MM/YYYY" class="datepicker form-control fecha-entrega-picker datepicker-control text-center"/>
        </td>
        <td>
          <input type="search" autocomplete="off" maxlength="150"
          class="form-control input-control input-observacion" readonly="readonly">
        </td>
        <td>
          <button class='btn btn-primary btn-sm fa fa-edit btn-edit' onclick="editarControl(5, $(this).parent().parent())"></button>
          <div class='btn-container d-none'>
            <button class='btn btn-danger btn-sm fa fa-close' onclick="cancelEditing($(this).parent().parent().parent())"></button>
            <button class='btn btn-success btn-sm fa fa-save' onclick="guardarControl(5, ${id_artpedido}, $(this).parent().parent().parent(), ${i})"></button>
          </div>
        </td>
      </tr>
      `);
    }
  }

  $.datepicker.setDefaults($.datepicker.regional["es"]);
  $(".datepicker-control")
    .datepicker({
      format: "dd-M-yyyy",
      autoclose: true,
      disableTouchKeyboard: true,
      Readonly: true,
      minDate: -90,
      dateFormat: "dd/mm/yy",
      onSelect: function (dateText, inst) {},
    })
    .attr("readonly", "readonly");

  $(
    ".input-t-s-am,.input-t-s-pm,.input-t-a-am,.input-t-a-pm,.input-porcentaje1"
  )
    .on("keypress", function (evt) {
      let $txtBox = $(this);
      let charCode = evt.which ? evt.which : evt.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
        return false;
      else {
        let len = $txtBox.val().length;
        let index = $txtBox.val().indexOf(".");
        if (index > 0 && charCode == 46) {
          return false;
        }
        if (index > 0) {
          let charAfterdot = len + 1 - index;
          if (charAfterdot > 3) {
            return false;
          }
        }
      }
      return $txtBox; //for chaining
    })
    .on("paste", function (e) {
      return false;
    });

  $(".only-digits").on("propertychange input", function (e) {
    this.value = this.value.replace(/\D/g, "");
  });

  loadControl(id_artpedido, etapa);
}

function editarControl(etapa, tr) {
  $(tr).find("input").removeAttr("disabled").removeAttr("readonly");
  //$(tr).find("input").first().focus();
  $(tr).css({ "background-color": "#848484" });
  $(tr).find(".btn-container").removeClass("d-none").addClass("d-inline-block");
  $(tr).find(".btn-edit").addClass("d-none");
}

function cancelEditing(tr) {
  $(tr).find("input").attr("readonly", "readonly");
  $(tr).find(".datepicker").attr("disabled", true);
  $(tr).find(".btn-edit").removeClass("d-none");
  $(tr).css({ "background-color": "" });
  $(tr).find(".btn-container").addClass("d-none").removeClass("d-inline-block");
}

function guardarControl(etapa, id_artpedido, tr, id_interno) {
  if (etapa == 0) {
    const fecha_siembra = $(tr).find(".datepicker").first().val();
    const cantidad_bandejas = $(tr)
      .find(".input-cantidad-bandejas")
      .first()
      .val()
      .trim();
    const t_s_am = $(tr).find(".input-t-s-am").first().val().trim();
    const t_s_pm = $(tr).find(".input-t-s-pm").first().val().trim();
    const t_a_am = $(tr).find(".input-t-a-am").first().val().trim();
    const t_a_pm = $(tr).find(".input-t-a-pm").first().val().trim();
    const observacion = $(tr).find(".input-observacion").first().val().trim();
    $.ajax({
      type: "POST",
      url: "data_ver_seguimiento.php",
      data: {
        consulta: "guardar_control_0",
        fecha_siembra: fecha_siembra,
        bandejas_sembradas: cantidad_bandejas,
        t_s_am: t_s_am,
        t_s_pm: t_s_pm,
        t_a_am: t_a_am,
        t_a_pm: t_a_pm,
        observacion: observacion.length ? observacion : "",
        id_artpedido: id_artpedido,
        id_interno: id_interno,
      },
      success: function (data) {
        if (data.trim() == "success") {
          swal("Guardaste los datos correctamente!", "", "success");
          cancelEditing(tr);
        } else {
          swal("Ocurrió un error al guardar los datos", data, "error");
          console.log(data);
        }
      },
    });
  } else if (etapa == 1 || etapa == 2 || etapa == 3) {
    const meson = $(tr).find(".input-meson").first().val().trim();
    const cantidad_bandejas = $(tr)
      .find(".input-cantidad-bandejas")
      .first()
      .val()
      .trim();
    const fecha_control = $(tr).find(".datepicker").first().val();

    const porcentaje1 = $(tr).find(".input-porcentaje1").first().val().trim();

    const t_s_am = $(tr).find(".input-t-s-am").first().val().trim();
    const t_s_pm = $(tr).find(".input-t-s-pm").first().val().trim();
    const t_a_am = $(tr).find(".input-t-a-am").first().val().trim();
    const t_a_pm = $(tr).find(".input-t-a-pm").first().val().trim();
    const observacion = $(tr).find(".input-observacion").first().val().trim();
    $.ajax({
      type: "POST",
      url: "data_ver_seguimiento.php",
      data: {
        consulta: `guardar_control_1_a_3`,
        etapa: etapa,
        cantidad_bandejas: cantidad_bandejas,
        porcentaje1: porcentaje1,
        t_s_am: t_s_am,
        t_s_pm: t_s_pm,
        t_a_am: t_a_am,
        t_a_pm: t_a_pm,
        meson: meson,
        fecha_control: fecha_control,
        observacion: observacion.length ? observacion : "",
        id_artpedido: id_artpedido,
        id_interno: id_interno,
      },
      success: function (data) {
        if (data.trim() == "success") {
          swal("Guardaste los datos correctamente!", "", "success");
          cancelEditing(tr);
        } else {
          swal("Ocurrió un error al guardar los datos", data, "error");
        }
      },
    });
  } else if (etapa == 4) {
    const fecha_repique = $(tr).find(".datepicker").first().val();
    const cantidad_bandejas_repicadas = $(tr)
      .find(".input-cantidad-bandejas-repicadas")
      .first()
      .val()
      .trim();
    const cantidad_bandejas_perdidas = $(tr)
      .find(".input-cantidad-bandejas-perdidas")
      .first()
      .val()
      .trim();
    const meson = $(tr).find(".input-meson").first().val().trim();

    const t_s_am = $(tr).find(".input-t-s-am").first().val().trim();
    const t_s_pm = $(tr).find(".input-t-s-pm").first().val().trim();
    const t_a_am = $(tr).find(".input-t-a-am").first().val().trim();
    const t_a_pm = $(tr).find(".input-t-a-pm").first().val().trim();
    const observacion = $(tr).find(".input-observacion").first().val().trim();
    $.ajax({
      type: "POST",
      url: "data_ver_seguimiento.php",
      data: {
        consulta: "guardar_control_4",
        fecha_repique: fecha_repique,
        bandejas_repicadas: cantidad_bandejas_repicadas,
        bandejas_perdidas: cantidad_bandejas_perdidas,
        meson: meson,
        t_s_am: t_s_am,
        t_s_pm: t_s_pm,
        t_a_am: t_a_am,
        t_a_pm: t_a_pm,
        observacion: observacion.length ? observacion : "",
        id_artpedido: id_artpedido,
        id_interno: id_interno,
      },
      success: function (data) {
        if (data.trim() == "success") {
          swal("Guardaste los datos correctamente!", "", "success");
          cancelEditing(tr);
        } else {
          swal("Ocurrió un error al guardar los datos", data, "error");
        }
      },
    });
  } else if (etapa == 5) {
    const fecha_disponibilidad = $(tr)
      .find(".fecha-disponibilidad-picker")
      .first()
      .val();
    const fecha_entrega = $(tr).find(".fecha-entrega-picker").first().val();

    const bandejas_finales = $(tr)
      .find(".input-bandejas-finales")
      .first()
      .val()
      .trim();

    const meson = $(tr).find(".input-meson").first().val().trim();
    const estado = $(tr).find(".input-estado").first().val().trim();
    const observacion = $(tr).find(".input-observacion").first().val().trim();
    $.ajax({
      type: "POST",
      url: "data_ver_seguimiento.php",
      data: {
        consulta: "guardar_control_5",
        fecha_disponibilidad: fecha_disponibilidad,
        fecha_entrega: fecha_entrega,
        meson: meson,
        estado: estado,
        bandejas_finales: bandejas_finales,
        observacion: observacion.length ? observacion : "",
        id_artpedido: id_artpedido,
        id_interno: id_interno,
      },
      success: function (data) {
        if (data.trim() == "success") {
          swal("Guardaste los datos correctamente!", "", "success");
          cancelEditing(tr);
        } else {
          swal("Ocurrió un error al guardar los datos", data, "error");
        }
      },
    });
  }
}

function loadControl(id_artpedido, etapa) {
  $("#ModalControl .loading-wrapper").removeClass("d-none").show();
  $.ajax({
    type: "POST",
    url: "data_ver_seguimiento.php",
    data: {
      consulta: `cargar_control_${etapa}`,
      etapa: etapa,
      id_artpedido: id_artpedido,
    },
    success: function (x) {
      if (x.length) {
        try {
          const data = JSON.parse(x);
          data.forEach(function (e, i) {
            if (etapa == 0) {
              const {
                id_interno,
                bandejas_sembradas,
                fecha_siembra,
                t_s_am,
                t_s_pm,
                t_a_am,
                t_a_pm,
                observacion,
              } = e;
              const tr = $(".tabla-control > tbody")
                .find("tr")
                .eq(id_interno - 1);
              $(tr)
                .find(".datepicker")
                .first()
                .val(fecha_siembra ? fecha_siembra : "DD/MM/YYYY");
              $(tr)
                .find(".input-cantidad-bandejas")
                .first()
                .val(bandejas_sembradas ? bandejas_sembradas : "");
              $(tr)
                .find(".input-t-s-am")
                .first()
                .val(t_s_am ? t_s_am.replace(",", ".") : "");
              $(tr)
                .find(".input-t-s-pm")
                .first()
                .val(t_s_pm ? t_s_pm.replace(",", ".") : "");
              $(tr)
                .find(".input-t-a-am")
                .first()
                .val(t_a_am ? t_a_am.replace(",", ".") : "");
              $(tr)
                .find(".input-t-a-pm")
                .first()
                .val(t_a_pm ? t_a_pm.replace(",", ".") : "");
              $(tr)
                .find(".input-observacion")
                .first()
                .val(observacion ? observacion : "");
            } else if (etapa >= 1 && etapa <= 3) {
              const {
                id_interno,
                cantidad_bandejas,
                fecha_control,
                porcentaje_1,
                meson,
                t_s_am,
                t_s_pm,
                t_a_am,
                t_a_pm,
                observacion,
              } = e;
              const tr = $(".tabla-control > tbody")
                .find("tr")
                .eq(id_interno - 1);
              $(tr)
                .find(".datepicker")
                .first()
                .val(fecha_control ? fecha_control : "DD/MM/YYYY");
              $(tr)
                .find(".input-cantidad-bandejas")
                .first()
                .val(cantidad_bandejas ? cantidad_bandejas : "");

              $(tr)
                .find(".input-porcentaje1")
                .first()
                .val(
                  porcentaje_1
                    ? porcentaje_1.replace(",", ".").replace(".00", "")
                    : ""
                );

              $(tr)
                .find(".input-meson")
                .first()
                .val(meson ? meson : "");

              $(tr)
                .find(".input-t-s-am")
                .first()
                .val(t_s_am ? t_s_am.replace(",", ".") : "");
              $(tr)
                .find(".input-t-s-pm")
                .first()
                .val(t_s_pm ? t_s_pm.replace(",", ".") : "");
              $(tr)
                .find(".input-t-a-am")
                .first()
                .val(t_a_am ? t_a_am.replace(",", ".") : "");
              $(tr)
                .find(".input-t-a-pm")
                .first()
                .val(t_a_pm ? t_a_pm.replace(",", ".") : "");
              $(tr)
                .find(".input-observacion")
                .first()
                .val(observacion ? observacion : "");
            } else if (etapa == 4) {
              const {
                id_interno,
                bandejas_repicadas,
                bandejas_perdidas,
                fecha_repique,
                t_s_am,
                t_s_pm,
                t_a_am,
                t_a_pm,
                meson,
                observacion,
              } = e;
              const tr = $(".tabla-control > tbody")
                .find("tr")
                .eq(id_interno - 1);
              $(tr)
                .find(".datepicker")
                .first()
                .val(fecha_repique ? fecha_repique : "DD/MM/YYYY");
              $(tr)
                .find(".input-cantidad-bandejas-repicadas")
                .first()
                .val(bandejas_repicadas ? bandejas_repicadas : "");
              $(tr)
                .find(".input-cantidad-bandejas-perdidas")
                .first()
                .val(bandejas_perdidas ? bandejas_perdidas : "");
              $(tr)
                .find(".input-t-s-am")
                .first()
                .val(t_s_am ? t_s_am.replace(",", ".") : "");
              $(tr)
                .find(".input-t-s-pm")
                .first()
                .val(t_s_pm ? t_s_pm.replace(",", ".") : "");
              $(tr)
                .find(".input-t-a-am")
                .first()
                .val(t_a_am ? t_a_am.replace(",", ".") : "");
              $(tr)
                .find(".input-t-a-pm")
                .first()
                .val(t_a_pm ? t_a_pm.replace(",", ".") : "");
              $(tr)
                .find(".input-meson")
                .first()
                .val(meson ? meson : "");
              $(tr)
                .find(".input-observacion")
                .first()
                .val(observacion ? observacion : "");
            } else if (etapa == 5) {
              const {
                id_interno,
                bandejas_finales,
                fecha_disponibilidad,
                fecha_entrega,
                estado,
                meson,
                observacion,
              } = e;
              const tr = $(".tabla-control > tbody")
                .find("tr")
                .eq(id_interno - 1);
              $(tr)
                .find(".fecha-disponibilidad-picker")
                .first()
                .val(
                  fecha_disponibilidad ? fecha_disponibilidad : "DD/MM/YYYY"
                );
              $(tr)
                .find(".fecha-entrega-picker")
                .first()
                .val(fecha_entrega ? fecha_entrega : "DD/MM/YYYY");
              $(tr)
                .find(".input-bandejas-finales")
                .first()
                .val(bandejas_finales ? bandejas_finales : "");
              $(tr)
                .find(".input-estado")
                .first()
                .val(estado ? estado : "");

              $(tr)
                .find(".input-meson")
                .first()
                .val(meson ? meson : "");
              $(tr)
                .find(".input-observacion")
                .first()
                .val(observacion ? observacion : "");
            }
          });

          $("#ModalControl .loading-wrapper").addClass("d-none").hide();
        } catch (error) {
          swal("Error de Conexión. Intenta nuevamente.", error, "error");
        }
      } else {
        $("#ModalControl .loading-wrapper").addClass("d-none").hide();
      }
    },
  });
}

function eliminarFoto(path, id_art, codigo, nombre_cliente) {
  swal("Estás seguro/a de eliminar la foto?", "", {
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
          url: "delfoto.php",
          data: { filename: path },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste la foto correctamente!", "", "success");
              const id_cliente = $("#ModalVerEstado").attr("x-id-cliente");
              MostrarModalEstado(id_art, codigo, nombre_cliente, id_cliente);
            } else {
              swal("Ocurrió un error al eliminar la foto", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function modalEntrega(id_art, cantidad_original) {
  $("#btn-entregar").prop("disabled", true);
  $("#input-cantidad-entrega").val("").focus();
  $("#ModalEntrega").attr("x-id-artpedido", id_art);
  $("#ModalEntrega").attr("x-falta-entregar", null);
  $.ajax({
    type: "POST",
    url: "data_ver_seguimiento.php",
    data: { consulta: "cargar_plantas_entregadas", id_artpedido: id_art },
    success: function (data) {
      if (data.includes("entregado:")) {
        const entregado = data.replace("entregado:", "");

        $(".label-entregado").html(`${entregado} Plantas`);
        $(".label-falta-entregar").html(
          `${cantidad_original - entregado} Plantas`
        );
        $("#ModalEntrega").attr(
          "x-falta-entregar",
          cantidad_original - entregado
        );

        if ((cantidad_original - entregado) <= 0){
          marcarComoEntregado(id_art)
        }
        else{
          $("#input-cantidad-entrega").attr("max", cantidad_original - entregado);
          document.getElementById("input-cantidad-entrega").oninput =
          function () {
            let max = parseInt(this.max);

            if (parseInt(this.value) > max) {
              this.value = max;
            }
            if (/^\s+$/.test(this.value)) {
              this.value = this.value.replace(/\s/g, "");
            }
          };
          $("#ModalEntrega").modal("show");
        }

        $("#btn-entregar").prop("disabled", false);
        
      }
    },
  });
}

function marcarComoEntregado(id_art){
  const codigo = $("#ModalVerEstado").attr("x-codigo");
  const nombre_cliente = $("#ModalVerEstado").attr("x-nombre-cliente");
  const id_cliente = $("#ModalVerEstado").attr("x-id-cliente");

  $.ajax({
    type: "POST",
    url: "data_ver_seguimiento.php",
    data: {
      consulta: "marcar_entregado",
      id_artpedido: id_art,      
    },
    success: function (data) {
      if (data.trim() == "success") {
        swal("El pedido ya había sido entregado!", "Se actualizó el estado a ENTREGADO COMPLETAMENTE", "success");
        MostrarModalEstado(id_art, codigo, nombre_cliente, id_cliente);
        if (!miTab || miTab == "tab-esquejes") loadEsquejes();
        else if (miTab == "tab-semillas") loadSemillas();
        else loadPedidos(miTab.replace("tab-",""))
      } else {
        swal("Ocurrió un error al marcar el Pedido como Entregado", data, "error");
      }
    },
  });
}

function guardarEntrega() {
  const id_artpedido = $("#ModalEntrega").attr("x-id-artpedido");
  const cantidad_entrega = $("#input-cantidad-entrega").val().trim();
  const codigo = $("#ModalVerEstado").attr("x-codigo");
  const nombre_cliente = $("#ModalVerEstado").attr("x-nombre-cliente");
  const id_cliente = $("#ModalVerEstado").attr("x-id-cliente");
  const falta_entregar = $("#ModalEntrega").attr("x-falta-entregar");

  if (
    !cantidad_entrega.length ||
    isNaN(cantidad_entrega) ||
    parseInt(cantidad_entrega) < 1
  ) {
    swal("Ingresa una cantidad superior a cero!", "", "error");
  } else {
    $("#ModalEntrega").modal("hide");
    $.ajax({
      type: "POST",
      url: "data_ver_seguimiento.php",
      data: {
        consulta: "guardar_entrega",
        id_artpedido: id_artpedido,
        cantidad_entrega: cantidad_entrega,
        falta_entregar: falta_entregar,
      },
      success: function (data) {
        if (data.trim() == "success") {
          swal("Guardaste la Entrega correctamente!", "", "success");
          MostrarModalEstado(id_artpedido, codigo, nombre_cliente, id_cliente);
          if (!miTab || miTab == "tab-esquejes") loadEsquejes();
          else if (miTab == "tab-semillas") loadSemillas();
          else loadPedidos(miTab.replace("tab-",""))
        } else {
          swal("Ocurrió un error al entregar el Producto", data, "error");
        }
      },
    });
  }
}

function cancelarPedido(id_art, codigo, nombre_cliente, id_cliente) {
  swal(
    "Estás seguro/a de cancelar el Pedido?",
    "ATENCIÓN: NO SE CANCELA EL PEDIDO COMPLETO, SINO ESTE PRODUCTO EN PARTICULAR. VERIFICA SI EL PEDIDO TIENE OTROS PRODUCTOS.",
    {
      icon: "warning",
      buttons: {
        cancel: "NO",
        catch: {
          text: "SI, CANCELAR",
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
          data: { consulta: "cancelar_pedido", id_artpedido: id_art },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Cancelaste el Pedido correctamente!", "", "success");
              MostrarModalEstado(id_art, codigo, nombre_cliente, id_cliente);
            } else {
              swal("Ocurrió un error al cancelar el Pedido", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function eliminarPedido(id_art, codigo, nombre_cliente, id_cliente) {
  swal(
    "Estás seguro/a de ELIMINAR el Pedido?",
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
          data: { consulta: "eliminar_pedido", id_artpedido: id_art },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste el Pedido correctamente!", "", "success");
              MostrarModalEstado(id_art, codigo, nombre_cliente, id_cliente);
            } else {
              swal("Ocurrió un error al eliminar el Pedido", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

async function imageExists(imgUrl) {
  if (!imgUrl) {
    return false;
  }
  return new Promise((res) => {
    const image = new Image();
    image.onload = () => res(true);
    image.onerror = () => res(false);
    image.src = imgUrl + `?t=${new Date().getTime()}`;
  });
}

function guardarObs() {
  const id_artpedido = $("#ModalVerEstado").attr("x-id-artpedido");
  const observaciones = $("#input-observaciones")
    .val()
    .trim()
    .replace(/\s+/g, " ");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: {
      id_artpedido: id_artpedido,
      consulta: "guardar_observaciones",
      observaciones: observaciones,
    },
    success: function (x) {
      if (x.trim() != "success") {
        swal("Ocurrió un error al guardar la Observación", x, "error");
      } else {
        swal("Guardaste la Observación correctamente!", "", "success");
        $("#input-observaciones").attr("disabled", true);
        $("#btn-guardar-obs").attr("disabled", true);
        if (!miTab || miTab == "tab-esquejes") loadEsquejes();
        else if (miTab == "tab-semillas") loadSemillas();
        else loadPedidos(miTab.replace("tab-",""));
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function guardarProblema() {
  const id_artpedido = $("#ModalVerEstado").attr("x-id-artpedido");
  const problema = $("#input-problema").val().trim().replace(/\s+/g, " ");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: {
      id_artpedido: id_artpedido,
      consulta: "guardar_problema",
      problema: problema,
    },
    success: function (x) {
      if (x.trim() != "success") {
        swal("Ocurrió un error al guardar el Problema", x, "error");
      } else {
        swal("Guardaste el Problema correctamente!", "", "success");
        $("#input-problema").attr("disabled", true);
        $("#btn-guardar-obs-problema").attr("disabled", true);

        if (problema.length > 1) {
          $("#btn-solucionado").removeClass("d-none");
          $("#btn-solucionado").attr("disabled", false);
        }

        if (!miTab || miTab == "tab-esquejes") loadEsquejes();
        else if (miTab == "tab-semillas") loadSemillas();
        else loadPedidos(miTab.replace("tab-",""));
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function solucionarProblema() {
  const id_artpedido = $("#ModalVerEstado").attr("x-id-artpedido");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: { id_artpedido: id_artpedido, consulta: "solucionar_problema" },
    success: function (x) {
      if (x.trim() != "success") {
        swal("Ocurrió un error al marcar la Solución", x, "error");
      } else {
        swal("Marcaste el Problema como Solucionado!", "", "success");
        $("#btn-solucionado").addClass("d-none");
        $("#btn-solucionado").attr("disabled", true);
        if (!miTab || miTab == "tab-esquejes") loadEsquejes();
        else if (miTab == "tab-semillas") loadSemillas();
        else loadPedidos(miTab.replace("tab-",""))
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminar_art(btn) {
  let row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
}

function CerrarModalEstado() {
  $("#btn-modificar-cantidad").addClass("d-none");
  let modal = document.getElementById("ModalVerEstado");
  modal.style.display = "none";
}

function CerrarModalOrden() {
  let modal = document.getElementById("ModalVerOrden");
  modal.style.display = "none";
}

function DeseleccionarTodo() {
  $(".cajita").removeClass("selected");
  $(".cajita").css({ border: "1px solid #00000033" });
  $(".cajita").parent().css({ "background-color": "" });
  listaseleccionados = [];
  listacolumnas = [];
}

function toggleSelection(objeto) {
  let tr = $(objeto);
  if (tr.hasClass("selected")) {
    tr.removeClass("selected");
    tr.css({ border: "1px solid #00000033" });
    tr.parent().css({ "background-color": "" });
    addToLista(objeto);
  } else {
    tr.addClass("selected");
    tr.css({ border: "3px solid #848484" });
    tr.parent().css({ "background-color": "#424242" });
    addToLista(objeto);
  }
}

function activarInputObs() {
  $("#input-observaciones").prop("disabled", false).focus();
  $("#btn-guardar-obs").prop("disabled", false);
}

function activarInputProblema() {
  $("#input-problema").prop("disabled", false).focus();
  $("#btn-guardar-obs-problema").prop("disabled", false);
}

function abrir(element) {
  let file = document.getElementById(element);
  file.dispatchEvent(
    new MouseEvent("click", {
      view: window,
      bubbles: true,
      cancelable: true,
    })
  );
}

function cambiofoto(tipo) {
  let formData = new FormData();
  const id_art = $("#ModalVerEstado").attr("x-id-artpedido");
  const codigo = $("#ModalVerEstado").attr("x-codigo");
  const nombre_cliente = $("#ModalVerEstado").attr("x-nombre-cliente");
  const id_cliente = $("#ModalVerEstado").attr("x-id-cliente");

  let idobj = `#input-foto${tipo}`;

  formData.append("file", $(idobj).prop("files")[0]);
  console.log($(idobj).prop("files")[0]);

  if (tipo == 1) formData.append("id_artpedido", id_art);
  else {
    formData.append("id_artpedido", id_art + `_${tipo}`);
  }
  if ($(idobj).prop("files")[0]) {
    $.ajax({
      url: "subirfoto.php",
      type: "POST",
      data: formData,
      dataType: "script",
      contentType: false,
      processData: false,
      beforeSend: function () {},
      success: function (x) {
        if (x.includes("error")) {
          swal("Ocurrió un error al subir la imagen", x.toString(), "error");
        } else {
          swal("Listo!", "Subiste la foto correctamente", "success");
          MostrarModalEstado(id_art, codigo, nombre_cliente, id_cliente);
        }
      },
      error: function (jqXHR, estado, error) {
        swal("ERROR!", error, "error");
      },
    });
  }
}

function verFoto(id, index) {
  let nombrefoto;
  if (index == 1) nombrefoto = id.toString();
  else {
    nombrefoto = id.toString() + "_" + index.toString();
  }
  $("#ocultar").css({ display: "none" });
  $("#miVentana").html(
    `
      <div class='row'>
        <div class='col-md-6'>
          <button id="back-btn" style="display:block;font-size:4em;border-radius:100%;left: 10px;top:10px;" class="btn btn-primary btn-round fa fa-arrow-left" onclick="cerrarFoto()"></button>        
        </div>
        <div class='col-md-6'>
          <button id="rotate-btn" style="display:block;font-size:3em;border-radius:100%;left: 100px;top:10px;" class="pull-right btn btn-primary btn-round" onclick="rotarFoto()"><span style="color:white"><b>GIRAR</b></span></img></button>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div align="center">
            <img id='current-foto' style="max-height: 80vh; width: auto;" src="imagenes/` +
      nombrefoto +
      `.jpg?t=` +
      new Date().getTime() +
      `"></img>
          </div>
        </div>
      </div>
        `
  );

  $("#miVentana").css({ display: "block" });
}

function cerrarFoto() {
  $("#miVentana").css({ display: "none" });
  $("#ocultar").css({ display: "block" });
}

let rotate_angle = 0;

function rotarFoto() {
  rotate_angle = (rotate_angle + 90) % 360;
  $("#current-foto").css({
    transform: "rotate(" + rotate_angle.toString() + "deg)",
  });
}

function generaBoxEstado(estado, fullWidth, codigo) {
  let w100 = "";
  if (fullWidth == true) {
    w100 = "w-100";
  }
  let colores;
  if (codigo == "E" || codigo == "HE") {
    colores = [
      "#D8EAD2",
      "#B6D7A8",
      "#A9D994",
      "#A2D98A",
      "#99D87D",
      "#8AD868",
    ];
  } else if (codigo == "S" || codigo == "HS") {
    colores = [
      "#FFF2CD",
      "#FFE59A",
      "#FED966",
      "#F2C234",
      "#E0B42F",
      "#CEA62E",
    ];
  } else {
    colores = [
      "#ffffff",
      "#ffffff",
      "#ffffff",
      "#ffffff",
      "#ffffff",
      "#ffffff",
    ];
  }
  if (estado == 0) {
    return `<div class='d-inline-block cajita ${w100}' style='background-color:${colores[estado]}; padding:5px;'>ETAPA 0</div>`;
  } else if (estado == 1) {
    return `<div class='d-inline-block cajita ${w100}' style='background-color:${colores[estado]}; padding:5px;'><span>ETAPA 1</span></div>`;
  } else if (estado == 2) {
    return `<div class='d-inline-block cajita ${w100}' style='background-color:${colores[estado]}; padding:5px;'>ETAPA 2</div>`;
  } else if (estado == 3) {
    return `<div class='d-inline-block cajita ${w100}' style='background-color:${colores[estado]}; padding:5px;'>ETAPA 3</div>`;
  } else if (estado == 4) {
    return `<div class='d-inline-block cajita ${w100}' style='background-color:${colores[estado]}; padding:5px;'>ETAPA 4</div>`;
  } else if (estado == 5) {
    return `<div class='d-inline-block cajita ${w100}' style='text-align:center;background-color:${colores[estado]}; padding:3px;'><div>ETAPA 5</div></div>`;
  } else if (estado == 6) {
    return `<div class='d-inline-block cajita ${w100}' style='text-align:center;background-color:#FFFF00; padding:3px; cursor:pointer;'><div>ENTREGA PARCIAL</div></div>`;
  } else if (estado == 7) {
    return `<div class='d-inline-block cajita ${w100}' style='text-align:center;background-color:#A9F5BC; padding:3px; cursor:pointer;'><div>ENTREGA COMPLETA</div></div>`;
  } else if (estado == 8) {
    //STOCK
    return `<div class='d-inline-block cajita ${w100}' style='text-align:center;background-color:#58D3F7; padding:3px; cursor:pointer;'><div>EN STOCK</div></div>`;
  } else if (estado == -1) {
    return `<div class='d-inline-block cajita ${w100}' style='word-wrap:break-word;text-align:center;background-color:#FA5858; padding:3px; cursor:pointer;'>CANCELADO</div>`;
  } else if (estado == -10) {
    return `<div class='d-inline-block cajita ${w100}' style='word-wrap:break-word;text-align:center;background-color:#D8d8d8; padding:3px; cursor:pointer;'>PENDIENTE</div>`;
  } else {
    return `<div class='d-inline-block cajita ${w100}' style='background-color:#A4A4A4; padding:5px;'>NO DEFINIDO</div>`;
  }
}

function CerrarModalCantidad() {
  document.getElementById("ModalCambioCantidad").style.display = "none";
}

function ModificarCantidadPedida(id_artpedido, cantidad, id_cliente) {
  $("#ModalCambioCantidad").attr("x-id-artpedido", id_artpedido);
  $("#ModalCambioCantidad").attr("x-id-cliente", id_cliente);

  $("#input-cantidad").val(cantidad);

  document.getElementById("ModalCambioCantidad").style.display = "block";
  $("#input-cantidad").focus().select();
}
function GuardarCambioCantidad() {
  const cantidad = $("#input-cantidad").val().trim();
  const id_artpedido = $("#ModalCambioCantidad").attr("x-id-artpedido");
  const codigo = $("#ModalVerEstado").attr("x-codigo");
  const nombre_cliente = $("#ModalVerEstado").attr("x-nombre-cliente");
  const id_cliente = $("#ModalCambioCantidad").attr("x-id-cliente");
  if (cantidad.length && !isNaN(cantidad) && parseInt(cantidad) > 0) {
    CerrarModalCantidad();
    $.ajax({
      beforeSend: function () {},
      url: "data_ver_seguimiento.php",
      type: "POST",
      data: {
        consulta: "modificar_cantidad",
        id_artpedido: id_artpedido,
        cantidad: cantidad,
      },
      success: function (x) {
        if (x.trim() == "success") {
          swal("La cantidad fue modificada correctamente!", "", "success");
          MostrarModalEstado(id_artpedido, codigo, nombre_cliente, id_cliente);
          if (!miTab || miTab == "tab-esquejes") loadEsquejes();
          else if (miTab == "tab-semillas") loadSemillas();
          else loadPedidos(miTab.replace("tab-",""))
        } else {
          swal("Ocurrió un error al modificar la cantidad", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  } else {
    swal("ERROR", "La cantidad debe ser mayor a cero", "error");
  }
}

function asignarMesada(id_artpedido, codigo_producto, nombre_cliente) {
  $("#ModalAsignarMesada").attr("x-id-artpedido", id_artpedido);
  $("#ModalAsignarMesada").attr("x-codigo", codigo_producto);
  $("#ModalAsignarMesada").attr("x-nombre-cliente", nombre_cliente);

  $("#ModalAsignarMesada").modal("show");

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
                    <div class='mesabox mesada-${id_mesada}' x-id-mesada=${id_mesada}  onClick='click_mesada(${id_mesada})' style='width:14em;background-color:${color};'>
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

            </div>
          
          `);
          }
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

if (! location.href.includes("ver_mesadas")){
  function click_mesada(id_mesada) {
    $(".active2").removeClass("active2");
    $(`.mesada-${id_mesada}`).addClass("active2");
  }
}


function guardarEnMesada() {
  if (!$(".active2").length) {
    swal("Selecciona un Mesón!", "", "error");
    return;
  }

  const id_artpedido = $("#ModalAsignarMesada").attr("x-id-artpedido");
  const codigo_producto = $("#ModalAsignarMesada").attr("x-codigo");
  const nombre_cliente = $("#ModalAsignarMesada").attr("x-nombre-cliente");
  const id_mesada = $(".active2").first().attr("x-id-mesada");

  $("#ModalAsignarMesada").modal("hide");

  $.ajax({
    beforeSend: function () {},
    url: "data_ver_mesadas.php",
    type: "POST",
    data: {
      consulta: "asignar_mesada",
      id_artpedido: id_artpedido,
      id_mesada: id_mesada,
    },
    success: function (x) {
      if (x.includes("success")) {
        swal("Asignaste el Mesón correctamente!", "", "success");
        MostrarModalEstado(id_artpedido, codigo_producto, nombre_cliente);
      } else {
        swal("Ocurrió un error al asignar el Mesón", x, "error");
      }
    },
  });
}

function buscar() {
  const busqueda = $("#input-search").val().trim();
  if (!busqueda.length) {
    // Mostrar todos los elementos
    $(".cajita").show();
    if (miTab == "tab-esquejes") {
      loadEsquejes();
    } else if (miTab == "tab-semillas") {
      loadSemillas();
    }
    else if (miTab == "tab-interior" || miTab == "tab-exterior" || miTab == "tab-vivero" || miTab == "tab-packs" || miTab == "tab-invitro"){
      loadPedidos(miTab.replace("tab-", ""))
    }
    return;
  }
  
  // Primero intentar filtrado local por código (sin restricción de longitud para códigos)
  let encontrados = 0;
  $(".cajita").each(function() {
    const codigo = $(this).attr("x-id") || $(this).attr("x-codigo");
    console.log("Código encontrado:", codigo, "Búsqueda:", busqueda);
    if (codigo && codigo.toUpperCase().includes(busqueda.toUpperCase())) {
      $(this).show();
      encontrados++;
    } else {
      $(this).hide();
    }
  });
  
  // Si no se encontró nada con el filtrado local y la búsqueda tiene 3+ caracteres, buscar en servidor
  if (encontrados === 0 && busqueda.length >= 3) {
    if (miTab == "tab-esquejes") {
      loadEsquejes();
    } else if (miTab == "tab-semillas") {
      loadSemillas();
    }
    else if (miTab == "tab-interior" || miTab == "tab-exterior" || miTab == "tab-vivero" || miTab == "tab-packs" || miTab == "tab-invitro"){
      loadPedidos(miTab.replace("tab-", ""))
    }
  }
}


function enviarStock(id_artpedido, codigo, nombre_cliente, cantidad_entregada, cant_plantas) {
  const cantidad_disponible = cant_plantas - cantidad_entregada;
  
  swal({
    title: "Enviar el Pedido a Stock",
    text: "Estará disponible para que los clientes puedan reservarlo.",
    content: {
      element: "div",
      attributes: {
        innerHTML: `
          <div class="form-group" style="margin-top: 15px;">
            <label for="cantidad-stock-input" style="display: block; margin-bottom: 8px; font-weight: bold;">
              Cantidad a enviar a stock:
            </label>
            <input 
              id="cantidad-stock-input" 
              type="number" 
              class="form-control" 
              value="${cantidad_disponible}" 
              min="0" 
              max="${cantidad_disponible}"
              style="text-align: center; font-size: 16px; font-weight: bold;"
            >
            <small class="text-muted" style="display: block; margin-top: 5px;">
              Disponible: ${cantidad_disponible} (${cant_plantas} plantas - ${cantidad_entregada} entregadas)
            </small>
          </div>
        `
      }
    },
    icon: "info",
    buttons: {
      cancel: "Cancelar",
      catch: {
        text: "SI, ENVIAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        const cantidadInput = document.getElementById("cantidad-stock-input");
        const cantidadAEnviar = parseInt(cantidadInput.value);
        
        // Validaciones
        if (!cantidadAEnviar || isNaN(cantidadAEnviar) || cantidadAEnviar <= 0) {
          swal("Error", "Ingresa una cantidad válida mayor a 0", "error");
          return;
        }
        
        if (cantidadAEnviar > cantidad_disponible) {
          swal("Error", `La cantidad no puede ser mayor a ${cantidad_disponible}`, "error");
          return;
        }
        
        $.ajax({
          type: "POST",
          url: "data_ver_seguimiento.php",
          data: { 
            consulta: "enviar_stock", 
            id_artpedido: id_artpedido,
            cantidad_enviar: cantidadAEnviar
          },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Enviaste el Pedido a Stock!", `Cantidad enviada: ${cantidadAEnviar}`, "success");
              MostrarModalEstado(id_artpedido, codigo, nombre_cliente, 1);
              if (location.href.includes("ver_seguimiento")){
                if (miTab == "tab-esquejes") {
                  loadEsquejes();
                } else if (miTab == "tab-semillas") {
                  loadSemillas();
                }
                else{
                 loadPedidos(miTab.replace("tab-",""))
                }
              }
              else{
                busca_entradas(currentTab)
              }
              
            } else {
              swal(
                "Ocurrió un error al Enviar el Pedido a Stock",
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


function modalModificarCliente(id_artpedido){
  $("#modal-modificar-cliente").attr("x-id-artpedido", id_artpedido)
  pone_clientes_modificar();
  $("#modal-modificar-cliente").modal("show");
}

function pone_clientes_modificar() {
  $.ajax({
    beforeSend: function () {
      $("#select-nuevo-cliente").html("Cargando lista de clientes...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "pone_clientes"
    },
    success: function (x) {
      $(".selectpicker").selectpicker({});
      $("#select-nuevo-cliente").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}

function guardarCambioCliente(){
  const id_cliente = $("#select-nuevo-cliente option:selected").val();
  const nombre_cliente = $("#select-nuevo-cliente option:selected").attr("x-nombre");
  const id_artpedido = $("#modal-modificar-cliente").attr("x-id-artpedido");
  if (!id_cliente || !id_cliente.length || !id_artpedido){
    swal("Selecciona un Cliente!", "", "error")
    return;
  }

  $("#modal-modificar-cliente").modal("hide");

  $.ajax({
    url: "data_ver_seguimiento.php",
    type: "POST",
    data: {
      consulta: "modificar_cliente",
      id_artpedido: id_artpedido,
      id_nuevo_cliente: id_cliente
    },
    success: function (x) {
      if (x.includes("success")){
        swal("Modificaste el Cliente correctamente!", "", "success")
        MostrarModalEstado(id_artpedido, "", nombre_cliente, id_cliente);
        if (document.location.href.includes("ver_pedidos")){
          busca_entradas(currentTab)
        }
      }
      else{
        swal("Ocurrió un error al Modificar el Cliente", x, "error")
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}