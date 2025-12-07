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

    let printContents = `
        <html>
        <head>
            <title>${tableTitle} - Impresión</title>
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
        if (index < headers.length - 1 && index !== 0) { // Exclude checkbox and actions columns
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

    swal({
        title: "¿Estás seguro?",
        text: `Se cambiará el estado de ${selectedIds.length} venta(s).`,
        icon: "warning",
        buttons: ["Cancelar", "Sí, cambiar estado"],
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
                        swal("¡Hecho!", "El estado de las ventas ha sido actualizado.", "success");
                        $('#modal-cambiar-estado-masa').css({display:'none'});
                        $('#btn-cambiar-estado-masa').hide();
                        if (currentTab === 'reservas') {
                            let selectedStates = $('#select-estado-reserva').val();
                            busca_entradas('reservas', selectedStates);
                        } else {
                            busca_entradas(currentTab);
                        }
                    } else {
                        swal("Error", "Ocurrió un error al actualizar el estado: " + response, "error");
                    }
                },
                error: function() {
                    swal("Error", "No se pudo conectar con el servidor.", "error");
                }
            });
        }
    });
}