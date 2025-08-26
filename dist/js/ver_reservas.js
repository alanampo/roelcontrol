let currentTab;
let max = null;
let currentReserva = null;

let phpFile = "data_ver_reservas.php"
$(document).ready(function () {

  $("#input-cantidad").on(
    "propertychange input",
    function (e) {
      this.value = this.value.replace(/\D/g, "");
    }
  );

  document.getElementById("defaultOpen").click();
});

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


function busca_entradas(tabName) {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Buscando, espere...");
    },
    url: phpFile,
    type: "POST",
    data: {
      consulta: tabName == "reservas" ? "busca_reservas" : "busca_stock_actual"
    },
    success: function (x) {
      let tipo = (tabName == "reservas" ? "reservas" : "productos")
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [tabName == "reservas" ? [0, "desc"] : [0, "asc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ " + tipo + " por página",
          zeroRecords: "No hay " + tipo + "",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay " + tipo + "",
          infoFiltered: "(filtrado de _MAX_ " + tipo + " en total)",
          lengthMenu: "Mostrar _MENU_ " + tipo + "",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron " + tipo + "",
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
      });
    },
    error: function (jqXHR, estado, error) {
      url: "data_ver_reservas.php"
      url: "data_ver_reservas.php"
      url: "data_ver_reservas.php"
      url: "data_ver_reservas.php"
      url: "data_ver_reservas.php"
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}


function cancelarReserva(id_reserva) {
  swal(
    "Estás seguro/a de CANCELAR la Reserva?",
    "",
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
          url: phpFile,
          data: { consulta: "cancelar_reserva", id_reserva: id_reserva },
          success: function (data) {
            console.log(data)
            if (data.trim() == "success") {
              swal("Cancelaste la Reserva correctamente!", "", "success");
              busca_entradas(currentTab);
            } else {
              swal(
                "Ocurrió un error al cancelar la Reserva",
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

function entregar(id_reserva, nombre_producto, cantidad, cantidad_disponible) {
  max = cantidad_disponible;
  currentReserva = id_reserva;
  $("#modal-entregar-reserva input").val("")
  $("#modal-entregar-reserva .box-title").html(`Entregar Reserva ${id_reserva} (${nombre_producto})`)
  $("#input-cantidad").val(cantidad)
  $("#input-cantidad-disponible").val(cantidad_disponible)
  $("#modal-entregar-reserva").modal("show")
  $("#input-cantidad").focus();
}

function guardarEntrega() {
  const cantidad = $("#input-cantidad").val().trim();
  const comentario = $("#input-comentario").val().trim();

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
      id_reserva: currentReserva
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

function marcarVisto(id_reserva) {
  $.ajax({
    type: "POST",
    url: phpFile,
    data: { consulta: "marcar_visto", id_reserva: id_reserva },
    success: function (data) {
      if (data.trim() == "success") {
        busca_entradas(currentTab);
      }
    },
  });
}

function marcarEnProceso(id_reserva) {
  swal(
    "Cambiar el Estado a EN PROCESO?",
    "",
    {
      icon: "info",
      buttons: {
        cancel: "NO",
        catch: {
          text: "SI, CAMBIAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: phpFile,
          data: { consulta: "marcar_en_proceso", id_reserva: id_reserva },
          success: function (data) {
            if (data.trim() == "success") {
              busca_entradas(currentTab);
            } else {
              swal(
                "Ocurrió un error al cambiar el estado de la Reserva",
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


// Función opcional para guardar con Enter
$(document).on('keypress', '[id^="stock-input-"]', function (e) {
  if (e.which === 13) { // Enter key
    const inputId = this.id;
    const id_artpedido = inputId.replace('stock-input-', '');
    // Necesitarías pasar también el id_variedad, podrías agregarlo como data attribute
    const id_variedad = $(this).closest('tr').data('id-variedad');

    if (id_variedad) {
      guardarStockArticulo(id_artpedido, id_variedad);
    }
  }
});

function modalReservar(id_variedad, nombre_producto, cantidad) {
  pone_clientes()
  max = cantidad;
  $("#modal-reservar").attr("x-id-variedad", id_variedad);
  $("#modal-reservar input").val("")
  $("#modal-reservar .box-title").html(`Reservar Producto (${nombre_producto})`)
  $("#input-cantidad-disponible2").val(cantidad)
  $("#modal-reservar").modal("show")
  $("#input-cantidad-reserva").focus();
}

function guardarReserva() {
  const cantidad = $("#input-cantidad-reserva").val().trim();
  const comentario = $("#input-comentario").val().trim();
  const id_cliente = $("#select-cliente").val();
  if (!id_cliente) {
    swal("Selecciona un Cliente", "", "error")
    return;
  }

  if (!cantidad || !cantidad.length || isNaN(cantidad) || parseInt(cantidad) <= 0) {
    swal("Ingresa la cantidad que quieres Reservar", "", "error")
    return;
  }

  if (parseInt(cantidad) > max) {
    swal("Ingresaste una cantidad superior a la disponible!", "", "error");
    return;
  }

  $("#modal-reservar").modal("hide");

  $.ajax({
    type: "POST",
    url: "data_ver_reservas.php",
    data: {
      consulta: "guardar_reserva",
      comentario: comentario,
      cantidad: parseInt(cantidad),
      id_variedad: $("#modal-reservar").attr("x-id-variedad"),
      id_cliente: id_cliente
    },
    success: function (x) {
      console.log(x)
      if (x.trim() == "success") {
        swal("Realizaste la Reserva correctamente!", "Te contactaremos para acordar los detalles de la Entrega.", "success");
        busca_entradas(currentTab);
      }
      else if (x.trim().includes("yaexiste")) {
        swal("No puedes reservar otra vez el mismo producto!", "Debes cancelar la reserva anterior.", "error")
      }
      else if (x.trim().includes("max:")) {
        swal("La cantidad ingresada ya no está disponible", "", "error")
        $("#input-cantidad-disponible2").val(x.trim().replace("max:", ""));
        $("#modal-reservar").modal("show");
      }
      else {
        swal(
          "Ocurrió un error al enviar la Reserva",
          "",
          "error"
        );
        $("#modal-reservar").modal("show");
      }
    },
  });
}

function pone_clientes() {
  $.ajax({
    beforeSend: function () {
      $("#select-cliente").html("Cargando lista de clientes...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "pone_clientes"
    },
    success: function (x) {
      $(".selectpicker").selectpicker({});

      $("#select-cliente").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}