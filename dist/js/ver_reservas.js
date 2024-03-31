let currentTab;
let max = null;
let currentReserva = null;
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
    url: "data_ver_reservas.php",
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
          lengthMenu: "Mostrando _MENU_ "+tipo+" por página",
          zeroRecords: "No hay "+tipo+"",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay "+tipo+"",
          infoFiltered: "(filtrado de _MAX_ "+tipo+" en total)",
          lengthMenu: "Mostrar _MENU_ "+tipo+"",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron "+tipo+"",
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
          url: "data_ver_reservas.php",
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

function entregar(id_reserva, nombre_producto, cantidad, cantidad_disponible){
  max = cantidad_disponible;
  currentReserva = id_reserva;
  $("#modal-entregar-reserva input").val("")
  $("#modal-entregar-reserva .box-title").html(`Entregar Reserva ${id_reserva} (${nombre_producto})`)
  $("#input-cantidad").val(cantidad)
  $("#input-cantidad-disponible").val(cantidad_disponible)
  $("#modal-entregar-reserva").modal("show")
  $("#input-cantidad").focus();
}

function guardarEntrega(){
  const cantidad = $("#input-cantidad").val().trim();
  const comentario = $("#input-comentario").val().trim();

  if (!cantidad || !cantidad.length || isNaN(cantidad) || parseInt(cantidad) <= 0){
    swal("Ingresa la cantidad que quieres Entregar", "", "error")
    return;
  }

  if (parseInt(cantidad) > max){
    swal("Ingresaste una cantidad superior a la disponible!", "", "error");
    return;
  }

  $("#modal-entregar-reserva").modal("hide");

  $.ajax({
    type: "POST",
    url: "data_ver_reservas.php",
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
      else if (x.trim().includes("cancelada:")){
        swal("ERROR! El cliente u otro usuario CANCELARON la reserva", "", "error");
      }
      else if (x.trim().includes("max:")){
        swal("La cantidad ingresada ya no está disponible", "", "error")
        $("#input-cantidad-disponible").val(x.trim().replace("max:",""));
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

function marcarVisto(id_reserva){
  $.ajax({
    type: "POST",
    url: "data_ver_reservas.php",
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
          url: "data_ver_reservas.php",
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
