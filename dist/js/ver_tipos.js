let edit_mode = false;
let global_id_tipo = null;

function busca_productos() {
  global_id_tipo = null;
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando tipos de Producto, esperá...");
    },
    url: "data_ver_tipos.php",
    type: "POST",
    data: { consulta: "busca_tipos" },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        language: {
          lengthMenu: "Mostrando _MENU_ productos por página",
          zeroRecords: "No hay productos",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay productos",
          infoFiltered: "(filtrado de _MAX_ productos en total)",
          lengthMenu: "Mostrar _MENU_ productos",
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

      setClickevent();

      $("#tabla").on("draw.dt", function () {
        setClickevent();
      });
    },

    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function setClickevent() {
  let tabli = document.getElementById("tabla");
  let rows = tabli.getElementsByTagName("tr");
  for (i = 1; i < rows.length; i++) {
    let currentRow = tabli.rows[i];
    let createClickHandler = function (row) {
      return function () {
        let id = row.getElementsByTagName("td")[0].getAttribute("x-id");
        let codigo = row.getElementsByTagName("td")[0].getAttribute("x-codigo");
        let nombre = row.getElementsByTagName("td")[1].innerHTML;
        MostrarModalAgregarProducto({
          id: id,
          codigo: codigo,
          nombre: nombre,
        });
      };
    };
    currentRow.onclick = createClickHandler(currentRow);
  }
}

function MostrarModalAgregarProducto(producto) {
  $("#select_tipobandeja").val("default").selectpicker("refresh");
  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
    $(".selectpicker").selectpicker("mobile");
  } else {
    let elements = document.querySelectorAll(".mobile-device");
    for (let i = 0; i < elements.length; i++) {
      elements[i].classList.remove("mobile-device");
    }
    $(".selectpicker").selectpicker({});
  }

  if (producto) {
    $("#ModalAgregarProducto").find("#titulo").html("Modificar Tipo");
    $("#input-nombre").val(producto.nombre);
    $("#input-siglas").val(producto.codigo);

    document.getElementById("input-nombre").focus();
    global_id_tipo = producto.id;
    edit_mode = true;
  } else {
    document.getElementById("input-nombre").focus();
    $("#input-nombre,#input-siglas").val("");
    $("#ModalAgregarProducto").find("#titulo").html("Agregar Tipo");
    edit_mode = false;
    global_id_tipo = null;
  }

  let modal = document.getElementById("ModalAgregarProducto");
  modal.style.display = "block";
}

function CerrarModalProducto() {
  let modal = document.getElementById("ModalAgregarProducto");
  modal.style.display = "none";
}

function GuardarTipo() {
  let nombre = $("#input-nombre").val().trim();
  let codigo = $("#input-siglas").val().trim();
  let puede = true;
  if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
    puede = false;
  } else if (!codigo.length) {
    swal("Debes un código en letras", "Ejemplo: HS, E, HE", "error");
    puede = false;
  }

  if (puede == true) {
    if (!edit_mode) {
      $.ajax({
        url: "data_ver_tipos.php",
        type: "POST",
        data: {
          consulta: "agregar_tipo",
          nombre: nombre,
          codigo: codigo,
        },
        success: function (x) {
          if (x.includes("existe")) {
            swal(
              "Ya existe un tipo de producto con ese nombre",
              "",
              "error"
            );
          } else if (x.trim() == "success"){
            busca_productos(null);
            $("#input-nombre,#input-siglas").val("");
            $("#input-nombre").focus();
            document.getElementById("ModalAgregarProducto").style.display =
              "none";
            swal("El tipo de producto se agregó correctamente!", "", "success");
          }
          else{
            swal("Ocurrió un error", x, "error")
          }
        },
        error: function (jqXHR, estado, error) {
          alert("Hubo un error al agregar el producto " + error);
        },
      });
    } else {
      $.ajax({
        url: "data_ver_tipos.php",
        type: "POST",
        data: {
          consulta: "editar_tipo",
          id_tipo: global_id_tipo,
          nombre: nombre,
          codigo: codigo,
        },
        success: function (x) {
          if (x.trim() == "success")  {
            document.getElementById("ModalAgregarProducto").style.display =
              "none";
            swal(
              "El tipo de producto fue modificado correctamente!",
              "",
              "success"
            );
            busca_productos(null);
          }
          else{
            swal("Ocurrió un error al modificar el producto", x, "error")
          }
        },
        error: function (jqXHR, estado, error) {
          alert("Hubo un error al modificar el producto " + error);
        },
      });
    }
  }
}
