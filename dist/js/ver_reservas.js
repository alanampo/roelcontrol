let currentTab;
let max = null;
let currentReserva = null;
let productosReserva = [];
let productosParaReserva = [];

let phpFile = "data_ver_reservas.php";

$(document).ready(function () {
    $("#input-cantidad-reserva").on("propertychange input", function (e) {
        this.value = this.value.replace(/\D/g, "");
    });
    $("#input-cantidad").on("propertychange input", function (e) {
        this.value = this.value.replace(/\D/g, "");
    });

    document.getElementById("defaultOpen").click();
    
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
    busca_entradas(tabName);
}

function busca_entradas(tabName) {
    let consulta = "";
    if(tabName == "reservas"){
        consulta = "busca_reservas";
    } else if (tabName == "actual"){
        consulta = "busca_stock_actual";
    } else if (tabName == "picking"){
        consulta = "busca_picking";
    } else if (tabName == "packing"){
        consulta = "busca_packing";
    }

    $.ajax({
        beforeSend: function () {
            $("#tabla_entradas").html("Buscando, espere...");
        },
        url: phpFile,
        type: "POST",
        data: {
            consulta: consulta,
        },
        success: function (x) {
            let tipo = tabName;
            $("#tabla_entradas").html(x);
            $("#tabla-reservas, #tabla, #tabla-picking, #tabla-packing").DataTable({
                pageLength: 50,
                order: [tabName == "reservas" || tabName == "picking" || tabName == "packing" ? [1, "desc"] : [0, "asc"]],
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
    swal("Estás seguro/a de CANCELAR la Reserva?", "", {
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
                        swal("Cancelaste la Reserva correctamente!", "", "success");
                        busca_entradas(currentTab);
                    } else {
                        swal("Ocurrió un error al cancelar la Reserva", data, "error");
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
                busca_entradas(currentTab);
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
                        swal("La reserva ha sido enviada a picking.", "", "success");
                        busca_entradas(currentTab);
                    } else {
                        swal("Ocurrió un error al enviar la reserva a picking", data, "error");
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
                        swal("La reserva ha sido enviada a packing.", "", "success");
                        busca_entradas(currentTab);
                    } else {
                        swal("Ocurrió un error al enviar la reserva a packing", data, "error");
                    }
                },
            });
        }
    });
}

function entregaRapida(id_reserva) {
    swal("Estás seguro/a de realizar la de toda la Reserva?", "Se entregarán todos los productos pendientes de la misma.", {
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
                        busca_entradas(currentTab);
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
  $("#modal-entregar-reserva .box-title").html(`Entregar Producto de Reserva (${nombre_producto})`)
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
        busca_entradas(currentTab);
      }
      else if (x.trim().includes("cancelada:")) {
        swal("ERROR! El cliente u otro usuario CANCELARON la reserva", "", "error");
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
    $("#modal-reservar .box-title").html(`Crear Reserva`);
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
        swal("Error", "Debes agregar al menos un producto a la reserva.", "error");
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
                swal("Éxito", "La reserva se ha guardado correctamente.", "success");
                busca_entradas(currentTab);
            } else {
                swal("Ocurrió un error al guardar la Reserva", x, "error");
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
        busca_entradas("actual");
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

    // Get all data from the DataTable, including hidden rows and columns (if any)
    const allData = table.rows({ search: 'applied' }).data().toArray(); // 'search: applied' ensures filtered data if search is active
    const headers = table.columns().header().toArray().map(th => th.innerHTML);

    let printContents = `
        <html>
        <head>
            <title>${tableTitle} - Impresión</title>
            <style>
                body { font-family: sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                h1 { text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <h1>${tableTitle}</h1>
            <table>
                <thead>
                    <tr>`;
    // Add headers to print content
    headers.forEach(header => {
        // Exclude the last header (actions column)
        if (headers.indexOf(header) < headers.length - 1) {
            printContents += `<th>${header}</th>`;
        }
    });
    printContents += `
                    </tr>
                </thead>
                <tbody>`;

    // Add all rows to print content
    allData.forEach(rowData => {
        printContents += `<tr>`;
        // Iterate over cells, excluding the last one (actions column)
        rowData.forEach((cellData, index) => {
            if (index < rowData.length - 1) { // Exclude the last column
                // Clean up HTML from cell data (e.g., remove buttons, spans for styling)
                const cleanedCellData = $('<div>').html(cellData).text(); // Use jQuery to parse HTML and get text content
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
