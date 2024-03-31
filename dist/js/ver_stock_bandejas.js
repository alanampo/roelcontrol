let currentTab;
$(document).ready(function () {
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
    url: "data_ver_stock_bandejas.php",
    type: "POST",
    data: {
      consulta: tabName == "historial" ? "busca_historial" : "busca_stock_actual"
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [tabName == "historial" ? [3, "desc"] : [0, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ resultados por página",
          zeroRecords: "No hay resultados",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay resultados",
          infoFiltered: "(filtrado de _MAX_ resultados en total)",
          lengthMenu: "Mostrar _MENU_ resultados",
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
      });
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function modalAgregarBandejas() {
  $(
    "#select-bandeja,#select-condicion"
  )
    .val("default")
    .selectpicker("refresh");
  $("#cantidad-bandejas").val("");
  $("#modal-agregar-bandejas").modal("show");
}

function guardarBandejas() {
  const bandeja = $("#select-bandeja").find("option:selected").val();
  const condicion = $("#select-condicion").find("option:selected").val();
  const cantidad = $("#cantidad-bandejas").val().trim();
  if (
    !bandeja || !bandeja.length
  ) {
    swal(
      "Selecciona un Tipo de Bandeja!",
      "",
      "error"
    );
  }
  else if (
    !condicion || !condicion.length
  ) {
    swal(
      "Selecciona una Condición! (Nuevas/Usadas)",
      "",
      "error"
    );
  }
  else if (isNaN(cantidad)) {
    swal("Debes ingresar una cantidad numérica!", "", "error");
  } else if (parseInt(cantidad) <= 0) {
    swal("La cantidad debe ser superior a cero!", "", "error");
  } 
  else {
    $.ajax({
      beforeSend: function () {
        $("#modal-agregar-bandejas").modal("hide");
      },
      url: "data_ver_stock_bandejas.php",
      type: "POST",
      data: {
        consulta: "guardar_bandejas",
        bandeja: bandeja,
        condicion: condicion == "nuevas" ? 1 : 0,
        cantidad: cantidad,
      },
      success: function (x) {
        if (x.trim() == "success"){
          swal("Agregaste las bandejas correctamente!", "", "success");
          busca_entradas();
        }
        else{
          swal("Ocurrió un error al guardar las bandejas", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
}

function eliminar(rowid) {
  swal(
    "Estás seguro/a de ELIMINAR el ingreso seleccionado?",
    "",
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
          url: "data_ver_stock_bandejas.php",
          data: { consulta: "eliminar_bandejas", rowid: rowid },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste las Bandejas correctamente!", "", "success");
              busca_entradas(currentTab);
            } else {
              swal(
                "Ocurrió un error al eliminar las Bandejas",
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