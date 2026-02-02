let currentTab;
let max = null;
let currentReserva = null;
let productosReserva = [];
let productosParaReserva = [];

let phpFile = "data_ver_ventas.php";

$(document).ready(function () {
    $("#input-cantidad-reserva").on("propertychange input", function (e) {
        this.value = this.value.replace(/\D/g, "");
    });
    $("#input-cantidad").on("propertychange input", function (e) {
        this.value = this.value.replace(/\D/g, "");
    });

    // Initialize selectpicker for states
    $('#select-estado-reserva').selectpicker();

    // Load saved states from localStorage or set defaults
    let savedStates = localStorage.getItem('reservas_filter_estados');
    if (savedStates) {
        savedStates = JSON.parse(savedStates);
        $('#select-estado-reserva').selectpicker('val', savedStates).selectpicker("refresh");
    } else {
        // Default selected states: PAGO ACEPTADO (0), EN PROCESO (1), EN REVISIÓN (3)
        $('#select-estado-reserva').selectpicker('val', ['0', '1', '3']).selectpicker("refresh");
    }

    // Event listener for state filter changes
    $('#select-estado-reserva').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        let selected = $(this).val();
        localStorage.setItem('reservas_filter_estados', JSON.stringify(selected));
        if (currentTab === 'reservas') { // Only re-search if 'VENTAS' tab is active
            busca_entradas('reservas', selected);
        }
    });

    document.getElementById("defaultOpen").click();

    $('#tabla_entradas').on('change', '.venta-checkbox', function() {
        $(this).closest('tr').toggleClass('selected-row', $(this).is(':checked'));
        checkSelectedVentas();
    });

    $('#btn-cambiar-estado-masa').on('click', function() {
        $('#modal-cambiar-estado-masa').css({display:'block'});
    });

    // Event listener para cambio de tipo de envío en modal orden envío
    $(document).on('changed.bs.select', '#select-tipo-envio', function (e, clickedIndex, newValue, oldValue) {
        $("#select-sucursal").html("").selectpicker();
        $(".col-direccion-envio-2").addClass("d-none");

        // Asegurar que cliente existe
        const cliente = (currentReservaOrden && currentReservaOrden.cliente) || {};
        console.log("Cliente en tipo envío:", cliente);

        if (this.value == 0) {
            getTransportistasSelect();
            $(".col-select-transp,.col-select-sucursal").removeClass("d-none");
            $(".col-direccion-envio").addClass("d-none");
            $("#input-direccion-entrega").val("");
            $("#input-direccion-entrega2").val("");
        } else if (this.value == 1) {
            // Autocompletar con domicilio del cliente
            const direccionCliente = cliente.domicilio || "";
            console.log("Asignando domicilio:", direccionCliente);
            $("#input-direccion-entrega").val(direccionCliente);
            $(".col-select-transp,.col-select-sucursal").addClass("d-none");
            $(".col-direccion-envio").removeClass("d-none");
            $("#input-direccion-entrega2").val("");
        }
        else if (this.value == 2) {
            // Autocompletar con domicilio de envío del cliente
            const direccionEnvio = cliente.domicilio2 || "";
            console.log("Asignando domicilio2:", direccionEnvio);
            $("#input-direccion-entrega2").val(direccionEnvio);
            $(".col-select-transp,.col-select-sucursal,.col-direccion-envio").addClass("d-none");
            $(".col-direccion-envio-2").removeClass("d-none");
            $("#input-direccion-entrega").val("");
        }
        else {
            $(".col-select-transp,.col-select-sucursal").addClass("d-none");
            $(".col-direccion-envio").addClass("d-none");
        }
        $("#select-transportista").val("default").selectpicker("refresh");
    });

    $('#select-producto-reserva').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        let id_variedad = $(this).val();
        let producto = productosParaReserva.find(p => p.id_variedad == id_variedad);
        if(producto){
            $("#input-cantidad-disponible2").val(producto.disponible);
            $("#input-cantidad-reserva").val(1);
        }
    });
});

function abrirTab(evt, tabName) {
    let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    currentTab = tabName;
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    evt.currentTarget.className += " active";
    
    // Show/hide filter for 'RESERVAS' tab
    if (tabName === 'reservas') {
        $('#filtro-estado-reservas').show();
        // Pass selected states to busca_entradas when opening the reservas tab
        let selectedStates = $('#select-estado-reserva').val();
        busca_entradas(tabName, selectedStates);
    } else {
        $('#filtro-estado-reservas').hide();
        busca_entradas(tabName);
    }
}

// Modify busca_entradas to accept selectedStates
function busca_entradas(tabName, selectedStates = []) {
    let consulta = "";
    let postData = {
        consulta: consulta,
    };

    if (tabName == "reservas") {
        consulta = "busca_ventas";
        if (selectedStates.length > 0) {
            postData.estados = JSON.stringify(selectedStates);
        }
    } else if (tabName == "actual") {
        consulta = "busca_stock_actual";
    } else if (tabName == "picking") {
        consulta = "busca_picking";
    } else if (tabName == "packing") {
        consulta = "busca_packing";
    } else if (tabName == "en_transporte") {
        consulta = "busca_en_transporte";
    } else if (tabName == "entregadas") {
        consulta = "busca_entregadas";
    }
    postData.consulta = consulta;

    $.ajax({
        beforeSend: function () {
            $("#tabla_entradas").html("Buscando, espere...");
        },
        url: phpFile,
        type: "POST",
        data: postData,
        success: function (x) {
            let tipo = tabName;
            $("#tabla_entradas").html(x);

            $("#tabla-reservas, #tabla, #tabla-picking, #tabla-packing, #tabla-en-transporte, #tabla-entregadas").DataTable({ // UPDATED SELECTOR
                pageLength: 50,
                order: [
                    tabName == "reservas" ||
                    tabName == "picking" ||
                    tabName == "packing" ||
                    tabName == "en_transporte" ||
                    tabName == "entregadas"
                        ? [1, "desc"]
                        : [0, "asc"]
                ],
                language: {
                    lengthMenu: `Mostrando _MENU_ ${tipo} por página`,
                    zeroRecords: `No hay ${tipo}`,
                    info: "Página _PAGE_ de _PAGES_",
                    infoEmpty: `No hay ${tipo}`,
                    infoFiltered: `(filtrado de _MAX_ ${tipo} en total)`,
                    search: "Buscar:",
                    paginate: {
                        first: "Primera",
                        last: "Última",
                        next: "Siguiente",
                        previous: "Anterior",
                    },
                },
            });
        },
        error: function (jqXHR, estado, error) {
            $("#tabla_entradas").html(
                "Ocurrió un error al cargar los datos: " + estado + " " + error
            );
        },
    });
}


function cancelarReserva(id_reserva) {
    swal("Estás seguro/a de CANCELAR la Venta?", "", {
        icon: "warning",
        buttons: {
            cancel: "NO",
            catch: {
                text: "SI, CANCELAR",
                value: "catch",
            },
        },
    }).then((value) => {
        if (value === "catch") {
            $.ajax({
                type: "POST",
                url: phpFile,
                data: { consulta: "cancelar_reserva", id_reserva: id_reserva },
                success: function (data) {
                    if (data.trim() == "success") {
                        swal("Cancelaste la Venta correctamente!", "", "success");
                        // After action, re-fetch based on current tab and filters
                        if (currentTab === 'reservas') {
                            let selectedStates = $('#select-estado-reserva').val();
                            busca_entradas('reservas', selectedStates);
                        } else {
                            busca_entradas(currentTab);
                        }
                    } else {
                        swal("Ocurrió un error al cancelar la Venta", data, "error");
                    }
                },
            });
        }
    });
}

function cambiarEstadoProducto(id_reserva_producto, estado) {
    $.ajax({
        type: "POST",
        url: phpFile,
        data: { consulta: "cambiar_estado_producto", id_reserva_producto: id_reserva_producto, estado: estado },
        success: function (data) {
            if (data.trim() == "success") {
                swal("El estado del producto ha sido actualizado.", "", "success");
                // After action, re-fetch based on current tab and filters
                if (currentTab === 'reservas') {
                    let selectedStates = $('#select-estado-reserva').val();
                    busca_entradas('reservas', selectedStates);
                } else {
                    busca_entradas(currentTab);
                }
            } else {
                swal("Ocurrió un error al cambiar el estado del producto", data, "error");
            }
        },
    });
}

function enviarAPickingReserva(id_reserva) {
    swal("Estás seguro/a de ENVIAR A PICKING todos los productos de esta reserva?", "", {
        icon: "warning",
        buttons: {
            cancel: "NO",
            catch: {
                text: "SI, ENVIAR",
                value: "catch",
            },
        },
    }).then((value) => {
        if (value === "catch") {
            $.ajax({
                type: "POST",
                url: phpFile,
                data: { consulta: "enviar_a_picking_reserva", id_reserva: id_reserva },
                success: function (data) {
                    if (data.trim() == "success") {
                        swal("La venta ha sido enviada a picking.", "", "success");
                        // After action, re-fetch based on current tab and filters
                        if (currentTab === 'reservas') {
                            let selectedStates = $('#select-estado-reserva').val();
                            busca_entradas('reservas', selectedStates);
                        } else {
                            busca_entradas(currentTab);
                        }
                    } else {
                        swal("Ocurrió un error al enviar La venta a picking", data, "error");
                    }
                },
            });
        }
    });
}

function enviarAPackingReserva(id_reserva) {
    swal("Estás seguro/a de ENVIAR A PACKING todos los productos de esta reserva?", "", {
        icon: "warning",
        buttons: {
            cancel: "NO",
            catch: {
                text: "SI, ENVIAR",
                value: "catch",
            },
        },
    }).then((value) => {
        if (value === "catch") {
            $.ajax({
                type: "POST",
                url: phpFile,
                data: { consulta: "enviar_a_packing_reserva", id_reserva: id_reserva },
                success: function (data) {
                    if (data.trim() == "success") {
                        swal("La venta ha sido enviada a packing.", "", "success");
                        // After action, re-fetch based on current tab and filters
                        if (currentTab === 'reservas') {
                            let selectedStates = $('#select-estado-reserva').val();
                            busca_entradas('reservas', selectedStates);
                        } else {
                            busca_entradas(currentTab);
                        }
                    } else {
                        swal("Ocurrió un error al enviar La venta a packing", data, "error");
                    }
                },
            });
        }
    });
}

// NEW FUNCTION FOR ENVIAR A TRANSPORTE
function enviarATransporteReserva(id_reserva) {
    swal("Estás seguro/a de ENVIAR A TRANSPORTE todos los productos de esta reserva?", "", {
        icon: "warning",
        buttons: {
            cancel: "NO",
            catch: {
                text: "SI, ENVIAR",
                value: "catch",
            },
        },
    }).then((value) => {
        if (value === "catch") {
            $.ajax({
                type: "POST",
                url: phpFile,
                data: { consulta: "enviar_a_transporte_reserva", id_reserva: id_reserva },
                success: function (data) {
                    if (data.trim() == "success") {
                        swal("La venta ha sido enviada a Transporte.", "", "success");
                        // After action, re-fetch based on current tab and filters
                        if (currentTab === 'packing') { // Refresh packing tab after sending to transporte
                            busca_entradas('packing');
                        } else {
                            // If not on packing tab, refresh current tab
                            let selectedStates = $('#select-estado-reserva').val();
                            busca_entradas(currentTab, selectedStates);
                        }
                    } else {
                        swal("Ocurrió un error al enviar La venta a Transporte", data, "error");
                    }
                },
            });
        }
    });
}
// END NEW FUNCTION

function entregaRapida(id_reserva) {
    swal("Estás seguro/a de realizar la entrega de toda la Venta?", "Se entregarán todos los productos pendientes de la misma.", {
        icon: "warning",
        buttons: {
            cancel: "NO",
            catch: {
                text: "SI, ENTREGAR",
                value: "catch",
            },
        },
    }).then((value) => {
        if (value === "catch") {
            $.ajax({
                type: "POST",
                url: phpFile,
                data: { consulta: "entrega_rapida", id_reserva: id_reserva },
                success: function (data) {
                    if (data.trim() == "success") {
                        swal("La entrega rápida se ha realizado correctamente!", "", "success");
                        // After action, re-fetch based on current tab and filters
                        if (currentTab === 'reservas') {
                            let selectedStates = $('#select-estado-reserva').val();
                            busca_entradas('reservas', selectedStates);
                        } else {
                            busca_entradas(currentTab);
                        }
                    } else {
                        swal("Ocurrió un error al realizar la entrega rápida", data, "error");
                    }
                },
            });
        }
    });
}

function entregarProducto(id_reserva_producto, nombre_producto, cantidad_pendiente, stock_disponible) {
  max = stock_disponible;
  currentReserva = id_reserva_producto;
  $("#modal-entregar-reserva input").val("")
  $("#modal-entregar-reserva .box-title").html(`Entregar Producto de Venta (${nombre_producto})`)
  $("#input-cantidad").val(cantidad_pendiente)
  $("#input-cantidad-disponible").val(stock_disponible)
  $("#modal-entregar-reserva").modal("show")
  $("#input-cantidad").focus();
}

function guardarEntrega() {
  const cantidad = $("#input-cantidad").val().trim();
  const comentario = $("#input-comentario-entrega").val().trim();

  if (!cantidad || !cantidad.length || isNaN(cantidad) || parseInt(cantidad) <= 0) {
    swal("Ingresa la cantidad que quieres Entregar", "", "error")
    return;
  }

  if (parseInt(cantidad) > max) {
    swal("Ingresaste una cantidad superior a la disponible!", "", "error");
    return;
  }

  $("#modal-entregar-reserva").modal("hide");

  $.ajax({
    type: "POST",
    url: phpFile,
    data: {
      consulta: "guardar_entrega",
      comentario: comentario,
      cantidad: parseInt(cantidad),
      id_reserva_producto: currentReserva
    },
    success: function (x) {
      console.log(x)
      if (x.trim() == "success") {
        swal("Realizaste la Entrega correctamente!", "", "success");
        // After action, re-fetch based on current tab and filters
        if (currentTab === 'reservas') {
            let selectedStates = $('#select-estado-reserva').val();
            busca_entradas('reservas', selectedStates);
        } else {
            busca_entradas(currentTab);
        }
      }
      else if (x.trim().includes("cancelada:")) {
        swal("ERROR! El cliente u otro usuario CANCELARON La venta", "", "error");
      }
      else if (x.trim().includes("max:")) {
        swal("La cantidad ingresada ya no está disponible", "", "error")
        $("#input-cantidad-disponible").val(x.trim().replace("max:", ""));
        $("#modal-entregar-reserva").modal("show");
      }
      else {
        swal(
          "Ocurrió un error al guardar la Entrega",
          "",
          "error"
        );
        console.log(x)
        $("#modal-entregar-reserva").modal("show");
      }
    },
  });
}


function modalReservar() {
    productosReserva = [];
    refrescarTablaProductosReserva();
    pone_clientes();
    pone_productos_reserva();
    $("#modal-reservar .box-title").html(`Generar Venta`);
    $("#modal-reservar").modal("show");
    $("#input-cantidad-reserva").focus();
}

function pone_productos_reserva() {
    $.ajax({
        url: phpFile,
        type: "POST",
        data: { consulta: "get_productos_para_reserva" },
        dataType: 'json',
        success: function (data) {
            productosParaReserva = data;
            let options = '<option value="">Seleccione un producto...</option>';
            data.forEach(p => {
                options += `<option value="${p.id_variedad}" data-disponible="${p.disponible}">${p.nombre_variedad} (${p.codigo}${p.id_interno}) - Disp: ${p.disponible}</option>`;
            });
            $("#select-producto-reserva").html(options).selectpicker("refresh");
        },
    });
}

function pone_clientes() {
  $.ajax({
    url: phpFile,
    type: "POST",
    data: { consulta: "get_clientes" },
    dataType: 'json',
    success: function (data) {
      let options = '<option value="">Seleccione un cliente...</option>';
      data.forEach(c => {
          options += `<option value="${c.ID_CLIENTE}">${c.nombre}</option>`;
      });
      $("#select-cliente").html(options).selectpicker("refresh");
    },
  });
}

function agregarProductoReserva() {
    let id_variedad = $("#select-producto-reserva").val();
    let cantidad = parseInt($("#input-cantidad-reserva").val());
    let disponible = parseInt($("#input-cantidad-disponible2").val());

    if (!id_variedad) {
        swal("Error", "Debes seleccionar un producto.", "error");
        return;
    }
    if (isNaN(cantidad) || cantidad <= 0) {
        swal("Error", "La cantidad debe ser mayor a cero.", "error");
        return;
    }
    if (cantidad > disponible) {
        swal("Error", "La cantidad a reservar no puede ser mayor al stock disponible.", "error");
        return;
    }

    let producto_existente = productosReserva.find(p => p.id_variedad == id_variedad);
    if (producto_existente) {
        producto_existente.cantidad += cantidad;
    } else {
        let nombre_producto = $("#select-producto-reserva option:selected").text();
        productosReserva.push({
            id_variedad: id_variedad,
            nombre: nombre_producto.split('-')[0].trim(),
            cantidad: cantidad,
            disponible: disponible
        });
    }
    
    // Actualizar disponible en el selector
    let producto_maestro = productosParaReserva.find(p => p.id_variedad == id_variedad);
    producto_maestro.disponible -= cantidad;
    $('#select-producto-reserva option[value="' + id_variedad + '"]').data('disponible', producto_maestro.disponible);
    $('#select-producto-reserva option[value="' + id_variedad + '"]').text(`${producto_maestro.nombre_variedad} (${producto_maestro.codigo}${producto_maestro.id_interno}) - Disp: ${producto_maestro.disponible}`);
    $("#select-producto-reserva").selectpicker("refresh");
    refrescarTablaProductosReserva();
    
    // Resetear controles
    $("#select-producto-reserva").val('').selectpicker('refresh');
    $("#input-cantidad-reserva").val('');
    $("#input-cantidad-disponible2").val('');
}

function refrescarTablaProductosReserva() {
    let tablaBody = $("#tabla-productos-reserva tbody");
    tablaBody.empty();
    productosReserva.forEach((p, index) => {
        let row = `<tr>
            <td>${p.nombre}</td>
            <td>${p.cantidad}</td>
            <td><button class="btn btn-danger btn-sm" onclick="eliminarProductoReserva(${index})"><i class="fa fa-trash"></i></button></td>
        </tr>`;
        tablaBody.append(row);
    });
}

function eliminarProductoReserva(index) {
    let producto_eliminado = productosReserva.splice(index, 1)[0];
    
    // Devolver stock al selector
    let producto_maestro = productosParaReserva.find(p => p.id_variedad == producto_eliminado.id_variedad);
    producto_maestro.disponible = parseInt(producto_maestro.disponible) + parseInt(producto_eliminado.cantidad);
    let nombre_maestro = producto_maestro.nombre_variedad + " (" + producto_maestro.codigo + producto_maestro.id_interno + ")";
    $('#select-producto-reserva option[value="' + producto_eliminado.id_variedad + '"]').data('disponible', producto_maestro.disponible);
     $('#select-producto-reserva option[value="' + producto_eliminado.id_variedad + '"]').text(`${nombre_maestro} - Disp: ${producto_maestro.disponible}`);
    $("#select-producto-reserva").selectpicker("refresh");
    
    if($("#select-producto-reserva").val() == producto_eliminado.id_variedad){
        $("#input-cantidad-disponible2").val(producto_maestro.disponible);
    }
    
    refrescarTablaProductosReserva();
}

function guardarReserva() {
    const id_cliente = $("#select-cliente").val();
    const observaciones = $("#input-comentario-reserva").val().trim();

    if (!id_cliente) {
        swal("Error", "Debes seleccionar un Cliente.", "error");
        return;
    }
    if (productosReserva.length === 0) {
        swal("Error", "Debes agregar al menos un producto a La venta.", "error");
        return;
    }

    $("#modal-reservar").modal("hide");

    $.ajax({
        type: "POST",
        url: phpFile,
        data: {
            consulta: "guardar_reserva",
            id_cliente: id_cliente,
            observaciones: observaciones,
            productos: JSON.stringify(productosReserva)
        },
        success: function (x) {
            if (x.trim() == "success") {
                swal("Éxito", "La venta se ha guardado correctamente.", "success");
                // After action, re-fetch based on current tab and filters
                if (currentTab === 'reservas') {
                    let selectedStates = $('#select-estado-reserva').val();
                    busca_entradas('reservas', selectedStates);
                } else {
                    busca_entradas(currentTab);
                }
            } else {
                swal("Ocurrió un error al guardar La venta", x, "error");
                $("#modal-reservar").modal("show");
            }
        },
        error: function(){
            swal("Error de conexión", "No se pudo conectar con el servidor", "error");
            $("#modal-reservar").modal("show");
        }
    });
}
function modalEditStock(id_variedad) {
  $.ajax({
    beforeSend: function () {
      $("#tabla-editar-stock tbody").html("Cargando productos...");
    },
    url: phpFile,
    type: "POST",
    data: { consulta: "get_stock_variedad", id_variedad },
    success: function (x) {
      $("#tabla-editar-stock tbody").html(x);
      $("#modal-edit-stock").css({ display: "block" });
    },
    error: function (jqXHR, estado, error) { },
  });
}

// Función para guardar el stock de un artículo específico
function guardarStockArticulo(id_artpedido, id_variedad) {
  const accion = document.querySelector(`input[name="accion-${id_artpedido}"]:checked`).value;
  const inputElement = document.getElementById(`cantidad-ajuste-${id_artpedido}`);
  const cantidad = inputElement.value.trim();

  // Validaciones
  if (!cantidad || cantidad.length === 0 || isNaN(cantidad)) {
    swal("Ingresa una cantidad válida", "", "error");
    return;
  }

  if (parseInt(cantidad) < 0) {
    swal("La cantidad no puede ser negativa", "", "error");
    return;
  }

  // Deshabilitar el botón mientras se procesa
  const botonGuardar = inputElement.nextElementSibling;
  const textoOriginal = botonGuardar.innerHTML;
  botonGuardar.disabled = true;
  botonGuardar.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

  $.ajax({
    type: "POST",
    url: phpFile, // Asegúrate de que phpFile esté definido
    data: {
      consulta: "actualizar_stock_articulo",
      id_artpedido: id_artpedido,
      id_variedad: id_variedad,
      accion: accion,        // nuevo campo
      cantidad: parseInt(cantidad) // nuevo campo
    },
    success: function (response) {
      console.log(response);

      // Rehabilitar el botón
      botonGuardar.disabled = false;
      botonGuardar.innerHTML = textoOriginal;

      if (response.trim() === "success") {
        swal("Stock actualizado correctamente!", "", "success");

        // Refrescar tablas/modales si hace falta
        // After action, re-fetch based on current tab and filters
        if (currentTab === 'reservas') {
            let selectedStates = $('#select-estado-reserva').val();
            busca_entradas('reservas', selectedStates);
        } else {
            busca_entradas(currentTab);
        }
        modalEditStock(id_variedad);
      } else if (response.trim().includes("error:")) {
        const errorMsg = response.trim().replace("error:", "");
        swal("Error al actualizar el stock", errorMsg, "error");
      } else {
        swal("Ocurrió un error al actualizar el stock", "", "error");
        console.log("Respuesta del servidor:", response);
      }
    },
    error: function () {
      // Rehabilitar el botón en caso de error
      botonGuardar.disabled = false;
      botonGuardar.innerHTML = textoOriginal;

      swal("Error de conexión", "No se pudo conectar con el servidor", "error");
    }
  });
}

function printTable(tableId) {
    const table = $(`#${tableId}`).DataTable();
    const tableTitle = $(`#${tableId}`).closest('.box-body').prev('.box-header').find('.box-title').text();
    const allData = table.rows({ search: 'applied' }).data().toArray();
    const headers = table.columns().header().toArray().map(th => th.innerHTML);

    const productosColIndex = headers.findIndex(header => header.toLowerCase() === 'productos');

    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const timestamp = `${year}${month}${day}${hours}${minutes}${seconds}`;

    let printContents = `
        <html>
        <head>
            <title>${tableTitle} - ${timestamp}</title>
            <style>
                body { font-family: sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; vertical-align: top; }
                th { background-color: #f2f2f2; }
                h1 {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <h1>${tableTitle}</h1>
            <table>
                <thead>
                    <tr>`;
    
    headers.forEach((header, index) => {
        if (index < headers.length - 1 && header.trim() !== '') { // Exclude checkbox and actions columns
            if (index === productosColIndex) {
                printContents += `<th>Producto</th><th>Cantidad</th><th>Estado</th>`;
            } else {
                printContents += `<th>${header}</th>`;
            }
        }
    });

    printContents += `
                    </tr>
                </thead>
                <tbody>`;

    allData.forEach(rowData => {
        printContents += `<tr>`;
        
        rowData.forEach((cellData, index) => {
            if (index >= rowData.length - 1 || index === 0) return; // Exclude checkbox and actions columns

            if (index === productosColIndex) {
                let productosContent = [];
                let cantidadesContent = [];
                let estadosContent = [];

                const $ul = $('<div>').html(cellData).find('ul.list-group');
                $ul.find('li').each(function() {
                    const $li = $(this);
                    const $mainDiv = $li.children('div').first();
                    const $statusSpan = $mainDiv.find('span.badge');

                    let productAndQuantityText = $mainDiv.clone().children().remove().end().text();
                    let statusText = $statusSpan.text().trim();

                    // Normalize whitespace before regex
                    productAndQuantityText = productAndQuantityText.replace(/\s+/g, ' ').trim();

                    const regex = /^(.*) - Cant: (\d+)/;
                    const match = productAndQuantityText.match(regex);

                    if (match) {
                        productosContent.push(match[1].trim());
                        cantidadesContent.push(match[2].trim());
                        estadosContent.push(statusText);
                    } else {
                        productosContent.push(productAndQuantityText);
                        cantidadesContent.push('');
                        estadosContent.push(statusText);
                    }
                });

                printContents += `<td>${productosContent.join('<br>')}</td>`;
                printContents += `<td>${cantidadesContent.join('<br>')}</td>`;
                printContents += `<td>${estadosContent.join('<br>')}</td>`;

            } else {
                const cleanedCellData = $('<div>').html(cellData).text();
                printContents += `<td>${cleanedCellData}</td>`;
            }
        });
        
        printContents += `</tr>`;
    });

    printContents += `
                </tbody>
            </table>
        </body>
        </html>`;

    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(printContents);
    printWindow.document.close();
    printWindow.print();
}
// Add these functions at the end of dist/js/ver_ventas.js
function modalEditarObservacionGeneral(id_reserva, observacion_text) {
    $("#hidden-id-reserva-observacion").val(id_reserva);
    $("#textarea-observacion-general").val(observacion_text);
    $("#modal-editar-observacion").css({display:'block'});
    $("#textarea-observacion-general").focus();
}

function guardarObservacionGeneral() {
    const id_reserva = $("#hidden-id-reserva-observacion").val();
    const observaciones = $("#textarea-observacion-general").val().trim();

    if (!id_reserva) {
        swal("Error", "No se pudo obtener el ID de La venta.", "error");
        return;
    }

    $("#modal-editar-observacion").css({display:'none'}); // Hide modal while processing

    $.ajax({
        type: "POST",
        url: phpFile,
        data: {
            consulta: "update_general_observacion", // New consulta type
            id_reserva: id_reserva,
            observaciones: observaciones
        },
        success: function (x) {
            if (x.trim() === "success") {
                swal("Éxito", "Observación actualizada correctamente.", "success");

                if (currentTab === 'reservas') {
                    let selectedStates = $('#select-estado-reserva').val();
                    busca_entradas('reservas', selectedStates);
                } else {
                    busca_entradas(currentTab);
                }
            } else {
                swal("Error", "Ocurrió un error al guardar la observación: " + x, "error");
                $("#modal-editar-observacion").css({display:'block'});
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            swal("Error de conexión", "No se pudo conectar con el servidor: " + textStatus, "error");
            $("#modal-editar-observacion").css({display:'block'});
        }
    });
}

// Add these functions at the end of dist/js/ver_ventas.js
function modalEditarObservacionPicking(id_reserva, observacion_picking_text) {
    $("#hidden-id-reserva-observacion-picking").val(id_reserva);
    $("#textarea-observacion-picking").val(observacion_picking_text);
    $("#modal-editar-observacion-picking").css({display:'block'});
    $("#textarea-observacion-picking").focus();
}

function guardarObservacionPicking() {
    const id_reserva = $("#hidden-id-reserva-observacion-picking").val();
    const observaciones_picking = $("#textarea-observacion-picking").val().trim();

    if (!id_reserva) {
        swal("Error", "No se pudo obtener el ID de La venta.", "error");
        return;
    }

    $("#modal-editar-observacion-picking").css({display:'none'}); // Hide modal while processing

    $.ajax({
        type: "POST",
        url: phpFile,
        data: {
            consulta: "update_picking_observacion", // New consulta type
            id_reserva: id_reserva,
            observaciones_picking: observaciones_picking
        },
        success: function (x) {
            if (x.trim() === "success") {
                swal("Éxito", "Observación de Picking actualizada correctamente.", "success");
                // Refresh the PICKING tab to show updated observations
                if (currentTab === 'picking') {
                    busca_entradas('picking'); // Refresh the picking tab
                } else {
                    // If not on picking tab, refresh current tab
                    let selectedStates = $('#select-estado-reserva').val(); // Get current filter states if any
                    busca_entradas(currentTab, selectedStates);
                }
            } else {
                swal("Error", "Ocurrió un error al guardar la observación de Picking: " + x, "error");
                $("#modal-editar-observacion-picking").css({display:'block'}); // Show modal again on error
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            swal("Error de conexión", "No se pudo conectar con el servidor: " + textStatus, "error");
            $("#modal-editar-observacion-picking").css({display:'block'}); // Show modal again on error
        }
    });
}

function modalEditarObservacionPacking(id_reserva, observacion_packing_text) {
    $("#hidden-id-reserva-observacion-packing").val(id_reserva);
    $("#textarea-observacion-packing").val(observacion_packing_text);
    $("#modal-editar-observacion-packing").css({display:'block'});
    $("#textarea-observacion-packing").focus();
}

function guardarObservacionPacking() {
    const id_reserva = $("#hidden-id-reserva-observacion-packing").val();
    const observaciones_packing = $("#textarea-observacion-packing").val().trim();

    if (!id_reserva) {
        swal("Error", "No se pudo obtener el ID de La venta.", "error");
        return;
    }

    $("#modal-editar-observacion-packing").css({display:'none'});

    $.ajax({
        type: "POST",
        url: phpFile,
        data: {
            consulta: "update_packing_observacion",
            id_reserva: id_reserva,
            observaciones_packing: observaciones_packing
        },
        success: function (x) {
            if (x.trim() === "success") {
                swal("Éxito", "Observación de Packing actualizada correctamente.", "success");

                if (currentTab === 'packing') {
                    busca_entradas('packing');
                } else {
                    let selectedStates = [];
                    if (currentTab === 'reservas') {
                        selectedStates = $('#select-estado-reserva').val();
                    }
                    busca_entradas(currentTab, selectedStates);
                }
            } else {
                swal("Error", "Ocurrió un error al guardar la observación de Packing: " + x, "error");
                $("#modal-editar-observacion-packing").css({display:'block'});
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            swal("Error de conexión", "No se pudo conectar con el servidor: " + textStatus, "error");
            $("#modal-editar-observacion-packing").css({display:'block'});
        }
    });
}

function checkSelectedVentas() {
    const count = $('.venta-checkbox:checked').length;
    if (count > 0) {
        $('#btn-cambiar-estado-masa').show();
    } else {
        $('#btn-cambiar-estado-masa').hide();
    }
}

function guardarCambioEstadoMasa() {
    const selectedIds = $('.venta-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    const nuevoEstado = $('#select-nuevo-estado').val();

    if (selectedIds.length === 0) {
        swal("Error", "No has seleccionado ninguna venta.", "error");
        return;
    }

    // Mensaje diferente según el estado
    let titulo = "¿Estás seguro?";
    let mensaje = `Se cambiará el estado de ${selectedIds.length} venta(s).`;
    let textoBoton = "Sí, cambiar estado";

    if (nuevoEstado === '2') {
        titulo = "¿Confirmar Entrega Masiva?";
        mensaje = `Se entregarán ${selectedIds.length} venta(s) automáticamente. Los productos pendientes se marcarán como entregados.`;
        textoBoton = "Sí, entregar";
    } else if (nuevoEstado === '-1') {
        titulo = "¿Confirmar Cancelación Masiva?";
        mensaje = `Se cancelarán ${selectedIds.length} venta(s). Solo se puede cancelar si ningún producto ha sido entregado.`;
        textoBoton = "Sí, cancelar";
    }

    swal({
        title: titulo,
        text: mensaje,
        icon: "warning",
        buttons: ["Cancelar", textoBoton],
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: 'data_ver_ventas.php',
                type: 'POST',
                data: {
                    consulta: 'cambiar_estado_ventas_masa',
                    ids: JSON.stringify(selectedIds),
                    estado: nuevoEstado
                },
                success: function(response) {
                    if (response.trim() === 'success') {
                        let mensajeExito = "El estado de las ventas ha sido actualizado.";
                        if (nuevoEstado === '2') {
                            mensajeExito = "Las entregas se han registrado correctamente.";
                            // Para entregas masivas, refrescar tanto VENTAS como ENTREGADAS
                            if (currentTab === 'reservas') {
                                let selectedStates = $('#select-estado-reserva').val();
                                busca_entradas('reservas', selectedStates);
                            } else {
                                busca_entradas(currentTab);
                            }
                            // También refrescar la pestaña ENTREGADAS para mostrar nuevos productos
                            setTimeout(() => {
                                busca_entradas('entregadas');
                            }, 500);
                        } else if (nuevoEstado === '-1') {
                            mensajeExito = "Las ventas han sido canceladas correctamente.";
                            if (currentTab === 'reservas') {
                                let selectedStates = $('#select-estado-reserva').val();
                                busca_entradas('reservas', selectedStates);
                            } else {
                                busca_entradas(currentTab);
                            }
                        } else {
                            if (currentTab === 'reservas') {
                                let selectedStates = $('#select-estado-reserva').val();
                                busca_entradas('reservas', selectedStates);
                            } else {
                                busca_entradas(currentTab);
                            }
                        }
                        swal("¡Hecho!", mensajeExito, "success");
                        $('#modal-cambiar-estado-masa').css({display:'none'});
                        $('#btn-cambiar-estado-masa').hide();
                    } else {
                        swal("Error", "Ocurrió un error al procesar la acción: " + response, "error");
                    }
                },
                error: function() {
                    swal("Error", "No se pudo conectar con el servidor.", "error");
                }
            });
        }
    });
}

// ORDENES ENVIO
let currentReservaOrden = null;

function modalOrdenEnvio(id_reserva) {
    currentReservaOrden = { id_reserva: id_reserva, cliente: {} };

    // Obtener datos del cliente de forma sincrónica
    $.ajax({
        url: phpFile,
        type: "POST",
        async: false,  // Sincrónico - espera a que se complete
        dataType: "json",  // Especificar que es JSON
        data: {
            consulta: "get_datos_cliente_para_orden_envio",
            id_reserva: id_reserva,
        },
        success: function (response) {
            try {
                // Ya es JSON decodificado por dataType: "json"
                currentReservaOrden.cliente = response;
                console.log("Cliente cargado:", currentReservaOrden.cliente);
            } catch(e) {
                console.error("Error al procesar cliente:", e);
                currentReservaOrden.cliente = {};
            }
        },
        error: function (error) {
            console.error("Error al obtener datos del cliente:", error);
            currentReservaOrden.cliente = {};
        }
    });

    $("#input-direccion-entrega").val("");
    $("#input-direccion-entrega2").val("");
    $("#select-tipo-envio").val("0").selectpicker("refresh");
    getTransportistasSelect();

    $("#select-transportista").val("default").selectpicker("refresh");
    $("#select-sucursal").html("").selectpicker("refresh");
    $(".col-select-transp,.col-select-sucursal").removeClass("d-none");
    $(".col-direccion-envio,.col-direccion-envio-2").addClass("d-none");

    $("#modal-orden-envio").modal("show");

    $("#table-bultos > tbody").html(`
        <tr scope="row" class="tr-add-row">
            <td colspan="6">
                <button onclick="addBulto()" class="btn btn-success btn-sm"><i class="fa fa-plus-square"></i></button>
            </td>
        </tr>
    `);

    addBulto();
}

function addBulto() {
    const index = $("#table-bultos > tbody > tr").length;

    if (index >= 25) return;

    let peso = "",
        alto = "",
        ancho = "",
        largo = "";
    if ($(".tr-bulto").last().length > 0) {
        const obj = $(".tr-bulto").last();
        peso = $(obj).find(".i-peso").val().trim();
        alto = $(obj).find(".i-alto").val().trim();
        ancho = $(obj).find(".i-ancho").val().trim();
        largo = $(obj).find(".i-largo").val().trim();
    }

    $("#table-bultos > tbody .tr-add-row").first().before(`
        <tr class='tr-bulto'>
            <td class='td-index'>
                ${index}
            </td>
            <td>
                <input value='${peso}' type='search' autocomplete="off" class="form-control input-decimal i-peso text-center" maxlength="6"/>
            </td>
            <td>
                <input value='${alto}' type='search' autocomplete="off" class="form-control input-decimal i-alto text-center" maxlength="6"/>
            </td>
            <td>
                <input value='${ancho}' type='search' autocomplete="off" class="form-control input-decimal i-ancho text-center" maxlength="6"/>
            </td>
            <td>
                <input value='${largo}' type='search' autocomplete="off" class="form-control input-decimal i-largo text-center" maxlength="6"/>
            </td>
            <td class="text-center">
                <button onclick="$(this).parent().parent().remove();updateIndexBulto()" class="btn btn-secondary fa fa-trash btn-sm"></button>
            </td>
        </tr>
    `);

    setInputDecimal($(".input-decimal"));
}

function updateIndexBulto() {
    $("#table-bultos > tbody > tr").each(function (i) {
        if (!$(this).hasClass("tr-bulto")) return;

        $(this).find(".td-index").html(i + 1);
    });
}

function setInputDecimal(elements) {
    elements.on("keypress", function (e) {
        var charCode = e.which ? e.which : e.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) return false;
        else {
            var len = $(this).val().length;
            var index = $(this).val().indexOf(".");
            if (index > 0 && charCode == 46) return false;
            if (index > 0) {
                var charAfterdot = len + 1 - index;
                if (charAfterdot > 3) return false;
            }
        }
        return true;
    });
}

function getSucursalesSelect(id) {
    $.ajax({
        beforeSend: function () {
            $("#select-sucursal").selectpicker({ title: "Cargando..." });
            $("#select-sucursal").html("").selectpicker("refresh");
        },
        url: phpFile,
        type: "POST",
        data: {
            id_transportista: id,
            consulta: "get_sucursales_select",
        },
        success: function (x) {
            $("#select-sucursal").selectpicker({ title: "Selecciona" });
            $("#select-sucursal").html(x).selectpicker("refresh");
        },
        error: function (jqXHR, estado, error) { },
    });
}

function getTransportistasSelect() {
    $.ajax({
        beforeSend: function () {
            $("#select-transportista").html("").selectpicker("refresh");
        },
        url: phpFile,
        type: "POST",
        data: {
            consulta: "get_transportistas_select",
        },
        success: function (x) {
            $("#select-transportista").html(x).selectpicker("refresh");
            $("#select-transportista").on(
                "changed.bs.select",
                function (e, clickedIndex, newValue, oldValue) {
                    getSucursalesSelect(this.value)
                }
            );
        },
        error: function (jqXHR, estado, error) { },
    });
}

function guardarOrdenEnvio() {
    if (!$(".tr-bulto").length) {
        swal("Debes agregar un bulto", "", "error");
        return;
    }

    const tipo = $("#select-tipo-envio option:selected").val();
    const id_transportista = $("#select-transportista option:selected").val();
    const selectedSucursalId = $("#select-sucursal").val();

    // Intentar obtener atributos del elemento original, no del selectpicker wrapper
    let nombre_sucursal = undefined;
    let nombre_transp = undefined;
    let direccion_sucursal = undefined;

    // Obtener atributos del select original
    if (selectedSucursalId) {
        const $selectedOption = $(`#select-sucursal option[value="${selectedSucursalId}"]`);
        nombre_sucursal = $selectedOption.attr("x-nombre");
        direccion_sucursal = $selectedOption.attr("x-direccion");
    }

    // Obtener transportista
    const selectedTranspId = $("#select-transportista").val();
    if (selectedTranspId) {
        const $selectedTranspOption = $(`#select-transportista option[value="${selectedTranspId}"]`);
        nombre_transp = $selectedTranspOption.attr("x-nombre");
    }

    // Si los atributos no están disponibles, intentar extraer del texto visible
    if (!nombre_sucursal || !direccion_sucursal) {
        const textVisible = $("#select-sucursal option:selected").text();
        // Extraer nombre y dirección del formato: "nombre [dirección] (id)"
        const match = textVisible.match(/^(.+?)\s*\[(.+?)\]\s*\(/);
        if (match) {
            nombre_sucursal = match[1].trim();
            direccion_sucursal = match[2].trim();
        }
    }

    if (!tipo || !tipo.length) {
        swal("Selecciona un Tipo de Entrega", "", "error");
        return;
    }

    if (tipo == 0 && (!id_transportista || !id_transportista.length)) {
        swal("Selecciona una Sucursal", "", "error");
        return;
    }

    const direccion = $("#input-direccion-entrega").val().trim().replace(/[\s|.'"']/g, " ");
    if (tipo == 1 && (!direccion || !direccion.length)) {
        swal("Ingresa la Dirección de Entrega", "", "error");
        return;
    }

    const direccion2 = $("#input-direccion-entrega2").val().trim().replace(/[\s|.'"']/g, " ");
    if (tipo == 2 && (!direccion2 || !direccion2.length)) {
        swal("Ingresa la Dirección de Entrega", "", "error");
        return;
    }

    const notas = $("#input-notas-entrega").val().trim().replace(/[\s|.'"']/g, " ");

    let bultos = [];
    $("#table-bultos > tbody > tr").each(function (i) {
        if ($(this).hasClass("tr-add-row") || $(this).hasClass("tr-ignore")) return;

        const peso = $(this).find(".i-peso").val().trim();
        const alto = $(this).find(".i-alto").val().trim();
        const ancho = $(this).find(".i-ancho").val().trim();
        const largo = $(this).find(".i-largo").val().trim();

        bultos.push({
            index: i + 1,
            peso: peso && peso.length ? parseFloat(peso) : null,
            alto: alto && alto.length ? parseFloat(alto) : null,
            ancho: ancho && ancho.length ? parseFloat(ancho) : null,
            largo: largo && largo.length ? parseFloat(largo) : null,
        });
    });

    if (!bultos.length) {
        swal("Debes agregar un bulto", "", "error");
        return;
    }

    $("#modal-orden-envio").modal("hide");

    const dataOrden = {
        tipo,
        id_transportista,
        direccion,
        direccion2,
        notas,
        bultos,
        nombre_sucursal,
        nombre_transp,
        direccion_sucursal,
    };

    printOrdenEnvio(dataOrden);
}

function printOrdenEnvio(dataOrden) {
    const now = new Date();
    const datetime =
        (now.getDate() < 10 ? "0" + now.getDate() : now.getDate()) +
        "/" +
        (now.getMonth() + 1 < 10
            ? "0" + (now.getMonth() + 1)
            : now.getMonth() + 1) +
        "/" +
        now.getFullYear();

    const {
        tipo,
        nombre_sucursal,
        nombre_transp,
        direccion_sucursal,
        notas,
        bultos,
    } = dataOrden;

    let direccionEntrega = "";
    let titulo = "ORDEN ENVÍO";

    if (tipo == 0) {
        // Para tipo SUCURSAL
        const displayNombre = nombre_sucursal || "SUCURSAL";
        const displayTransp = nombre_transp || "TRANSPORTISTA";
        const displayDireccion = direccion_sucursal || "";

        titulo = `${displayNombre} - ${displayTransp}`;
        direccionEntrega = `Suc. ${displayTransp} ${displayNombre}${displayDireccion ? ` - ${displayDireccion}` : ""}`;
    } else if (tipo == 1) {
        direccionEntrega = dataOrden.direccion;
    } else if (tipo == 2) {
        direccionEntrega = dataOrden.direccion2;
    }

    $("#print-orden-envio").html("");

    // Datos fijos de la empresa
    const razonEmpresa = "PLANTINERA V.V.";
    const rutEmpresa = "77436423-4";
    const direccionEmpresa = "EL CARMEN PC 7 LOTE 2 EX FUNDO<br>LA GLORIETA LOBOS 5";
    const telefonoEmpresa = "+56 972 912 979";
    const emailEmpresa = "plantinera@roelplant.cl";

    // Datos del cliente
    const cliente = currentReservaOrden.cliente || {};
    console.log("Cliente completo en print:", cliente);
    console.log("dataOrden completo:", dataOrden);

    const destinatario = cliente.nombre || "-";

    // Lógica de dirección: usar la que el usuario escribió, si no existe usar del cliente
    let direccionFinal = "-";
    if (tipo == 0) {
        // SUCURSAL: usar la dirección de la sucursal
        direccionFinal = direccionEntrega;
    } else if (tipo == 1) {
        // DOMICILIO CLIENTE: preferir lo que escribió el usuario
        direccionFinal = (dataOrden.direccion && dataOrden.direccion.trim()) || cliente.domicilio || "-";
    } else if (tipo == 2) {
        // DOMICILIO ENVÍO: preferir lo que escribió el usuario
        direccionFinal = (dataOrden.direccion2 && dataOrden.direccion2.trim()) || cliente.domicilio2 || "-";
    }

    const comunaCliente = cliente.comuna || "-";
    const regionCliente = cliente.region || "-";
    const rutCliente = cliente.rut || "-";
    const telefonoCliente = cliente.telefono || "-";
    const emailCliente = cliente.mail || "-";

    console.log("Dirección Final:", direccionFinal);
    console.log("Destinatario:", destinatario);

    bultos.forEach(function (b, i) {
        const { peso, alto, ancho, largo } = b;

        const tablarte = `
            <table class='table table-bordered tablin tabla-bulto' style='width: 100%;' role='grid'>
                <tbody>
                    <tr>
                        <td colspan="2">
                            <div class="d-flex flex-row align-items-center">
                                <div style="width: 160px; height: 100px;">
                                    <img style="width: 160px; height: 100px" src="dist/img/roelprint.png"></img>
                                </div>
                                <div class="ml-4">
                                    <h4 style="font-weight:bold;">${titulo}</h4>
                                    ${direccion_sucursal ? `<span>${direccion_sucursal}</span>` : ""}
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Fecha emisión: ${datetime}
                        </td>
                        <td class="text-center">
                            <h5 class="font-weight-bold">${currentReservaOrden.id_reserva
                                .toString()
                                .padStart(6, "0")}</h5>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div><strong>Remitente:</strong> ${razonEmpresa}</div>
                            <div><strong>Dirección:</strong> ${direccionEmpresa}</div>
                            <div><strong>R.U.T:</strong> ${rutEmpresa}</div>
                            <div><strong>Teléfono:</strong> ${telefonoEmpresa}</div>
                            <div><strong>Email:</strong> ${emailEmpresa}</div>
                        </td>
                        <td class="text-center">
                            <div class="p-2 qr-code-cotizacion" id="qr-code-${i}"></div>
                        </td>
                    </tr>
                </tbody>
            </table>`;

        const tabladest = `
            <table class='table table-bordered w-100' role='grid'>
                <tbody>
                    <tr>
                        <td>
                            <div><strong>Destinatario:</strong> ${destinatario}</div>
                            <div><strong>Dirección:</strong> ${direccionFinal}</div>
                            <div><strong>Comuna:</strong> ${comunaCliente}</div>
                            <div><strong>Región:</strong> ${regionCliente}</div>
                            <div><strong>R.U.T:</strong> ${rutCliente}</div>
                            <div><strong>Teléfono:</strong> ${telefonoCliente}</div>
                            <div><strong>Email:</strong> ${emailCliente}</div>
                            <div><strong>Venta Nº:</strong> ${currentReservaOrden.id_reserva}</div>
                        </td>
                        <td class="text-center">
                            <h6>BULTO N°</h6>
                            <h4 class="font-weight-bold">${(i + 1)
                                .toString()
                                .padStart(3, "0")}/${bultos.length.toString().padStart(3, "0")}</h4>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div><strong>Notas:</strong> ${notas ? notas.toUpperCase() : ""}</div>
                        </td>
                        <td class="text-center">
                            <span>Peso: ${peso ? `${peso} kg` : ""}</span><br>
                            <span>Alto: ${alto ? `${alto} cm` : ""}</span><br>
                            <span>Ancho: ${ancho ? `${ancho} cm` : ""}</span><br>
                            <span>Largo: ${largo ? `${largo} cm` : ""}</span><br>
                        </td>
                    </tr>
                </tbody>
            </table>`;

        const contenidoBulto = `
            <div class="bulto-print">
                ${tablarte}
                ${tabladest}
            </div>
        `;

        $("#print-orden-envio").append(contenidoBulto);

        // Generar QR
        setTimeout(() => {
            var qrcode = new QRCode(document.getElementById("qr-code-" + i), {
                text: currentReservaOrden.id_reserva.toString(),
                width: 150,
                height: 150,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H,
            });
        }, 100);
    });

    const css = `
        @media print {
            @page {
                size: 106mm 164mm;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            #print-orden-envio {
                width: 106mm;
                height: 164mm;
            }
            #print-orden-envio .tablin {
                width: 100vw !important;
                page-break-inside: avoid;
            }
            .bulto-print {
                width: 100vw;
                height: 164mm;
                page-break-after: always;
                box-sizing: border-box;
                padding: 5mm;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .bulto-print:last-child {
                page-break-after: auto;
            }
            .bulto-print table {
                width: 100% !important;
                table-layout: fixed;
            }
            .bulto-print td {
                vertical-align: top;
                font-size: 12px;
            }
            .qr-code-cotizacion {
                text-align: center;
                width: 150px !important;
                height: 150px !important;
                padding-bottom: 10px !important;
                margin-bottom: 10px !important;
            }
            .qr-code-cotizacion img {
                width: 150px !important;
                height: 150px !important;
            }
        }`;

    const oldStyleTag = document.getElementById('orden-envio-print-styles');
    if (oldStyleTag) {
        document.head.removeChild(oldStyleTag);
    }

    let styleTag = document.createElement("style");
    styleTag.id = 'orden-envio-print-styles';
    styleTag.innerHTML = css;
    document.head.appendChild(styleTag);

    $("#ocultar").css({ display: "none" });
    $("#print-orden-envio").css({ display: "block" });

    setTimeout(() => {
        storeOrdenEnvio($("#print-orden-envio").html());
        window.print();

        const printStyleTag = document.getElementById('orden-envio-print-styles');
        if (printStyleTag) {
            document.head.removeChild(printStyleTag);
        }
        document.getElementById("ocultar").style.display = "block";
        $("#print-orden-envio").css({ display: "none" });
    }, 500);
}

function storeOrdenEnvio(html) {
    $.ajax({
        beforeSend: function () { },
        url: phpFile,
        type: "POST",
        data: {
            consulta: "guardar_orden_envio",
            data: html,
            id_reserva: currentReservaOrden.id_reserva,
        },
        success: function (x) {
            if (x.includes("success")) {
                // No mostrar swal aquí, ya se está imprimiendo
            }
        },
        error: function (jqXHR, estado, error) {
            // Error silencioso
        },
    });
}
