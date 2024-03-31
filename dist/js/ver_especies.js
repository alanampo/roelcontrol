let edit_mode = false;

$(document).ready(() => {
  $("#dias-produccion-especie").on("propertychange input", function (e) {
    this.value = this.value.replace(/\D/g, "");
  });
});

function busca_productos(filtro) {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando productos, espere...");
    },
    url: "data_ver_especies.php",
    type: "POST",
    data: { filtro: filtro, consulta: "busca_especies" },
    success: function (x) {
      $("#tabla_entradas").html(x);
      let table = $("#tabla").DataTable({
        pageLength: 100,
        language: {
          lengthMenu: "Mostrando _MENU_ especies por página",
          zeroRecords: "No hay especies",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay especies",
          infoFiltered: "(filtrado de _MAX_ especies en total)",
          lengthMenu: "Mostrar _MENU_ especies",
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
      swal("Ocurrió un error al cargar los datos", error.toString(), "error");
    },
  });
}

function setClickevent() {
  $(".clickable").on("click", function (e) {
    const row = $(this).parent();
    let id = $(row).attr("x-id");
    let nombre = $(row).attr("x-nombre");
    let codigo_tipo = $(row).attr("x-codigo-tipo");
    let dias_produccion = $(row).attr("x-dias-produccion");
    MostrarModalAgregarProducto({
      id: id,
      nombre: nombre,
      codigo_tipo: codigo_tipo,
      dias_produccion: dias_produccion,
    });
  });
}

function MostrarModalAgregarProducto(producto) {
  if (producto) {
    // EDITANDO
    $("#ModalAgregarProducto").find("#titulo").html("Modificar Especie");
    $("#select_tipo2").attr("disabled", "disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#input-nombre").val(producto.nombre);
    $("#dias-produccion-especie").val(
      producto.dias_produccion ? producto.dias_produccion : ""
    );
    $("#select-dias-produccion").addClass("d-none");
    $("#ModalAgregarProducto").attr("x-id-especie", producto.id);
    $("#ModalAgregarProducto").attr("x-codigo-tipo", producto.codigo_tipo);

    edit_mode = true;
  } else {
    //AGREGANDO
    $("#input-nombre, #dias-produccion-especie").val("");
    $("#select-dias-produccion").val("0");
    $("#select_tipo2").removeAttr("disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#ModalAgregarProducto").find("#titulo").html("Agregar Especie");
    $("#select-dias-produccion").removeClass("d-none");
    $("#ModalAgregarProducto").removeAttr("x-codigo-tipo");
    edit_mode = false;
  }

  $("#ModalAgregarProducto").modal("show");
  $("#input-nombre").focus();
}

function CerrarModalProducto() {
  $("#ModalAgregarProducto").modal("hide");
}

function GuardarProducto() {
  const id_tipo = $("#select_tipo2 option:selected").val();
  const nombre = $("#input-nombre").val().trim().replace(/\s+/g, " ");
  const dias_produccion = $("#dias-produccion-especie")
    .val()
    .trim()
    .replace(/\s+/g, "");

  let codigo_tipo = $("#select_tipo2").find("option:selected").attr("x-codigo");

  if (!edit_mode && !id_tipo.length) {
    if (edit_mode == false) {
      swal("Debes elegir un tipo de producto", "", "error");
      return;
    }
  } else if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
    return;
  } else if (
    !dias_produccion.length ||
    isNaN(dias_produccion) ||
    parseInt(dias_produccion) < 0 ||
    parseInt(dias_produccion) > 365
  ) {
    swal(
      "Ingresa la cantidad de días que permanecerá la Especie en Producción",
      "",
      "error"
    );
    return;
  }

  CerrarModalProducto();
  if (!edit_mode) {
    $.ajax({
      url: "data_ver_especies.php",
      type: "POST",
      data: {
        consulta: "agregar_especie",
        nombre: nombre,
        id_tipo: id_tipo,
        dias_produccion: dias_produccion,
      },
      success: function (x) {
        if (!x.includes("success")) {
          swal("Ocurrió un error", x, "error");
          $("#ModalAgregarProducto").modal("show");
        } else {
          busca_productos(null);
          $("#input-nombre").val("");
          $("#select_tipo2").val("default").selectpicker("refresh");
          swal("La Especie se agregó correctamente!", "", "success");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
      },
    });
  } else {
    codigo_tipo = $("#ModalAgregarProducto").attr("x-codigo-tipo");

    $.ajax({
      url: "data_ver_especies.php",
      type: "POST",
      data: {
        consulta: "editar_especie",
        id_especie: $("#ModalAgregarProducto").attr("x-id-especie"),
        nombre: nombre,
        dias_produccion: dias_produccion,
      },
      success: function (x) {
        if (x.trim() == "success") {
          swal("El producto fue modificado correctamente!", "", "success");
          busca_productos(null);
        } else {
          swal("Ocurrió un error al modificar el producto", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {
        swal(
          "Ocurrió un error al modificar el producto",
          error.toString(),
          "error"
        );
        $("#ModalAgregarProducto").modal("show");
      },
    });
  }
}

function pone_tipos() {
  $.ajax({
    beforeSend: function () {
      $("#select_tipo").html("Cargando tipos...");
    },
    url: "data_ver_tipos.php",
    type: "POST",
    data: { consulta: "busca_tipos_select", tipo: "HS/HE" },
    success: function (x) {
      $(".selectpicker").selectpicker();
      $("#select_tipo").html(x).selectpicker("refresh");
      $("#select_tipo2").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminar(id) {
  swal(
    "Estás seguro/a de eliminar la Especie seleccionada?",
    "Se eliminará para futuros pedidos.",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          beforeSend: function () {},
          url: "data_ver_especies.php",
          type: "POST",
          data: { consulta: "eliminar_especie", id_especie: id },
          success: function (x) {
            if (!x.includes("success")) {
              swal("Ocurrió un error!", x, "error");
            } else {
              swal("Eliminaste la Especie correctamente!", "", "success");
              busca_productos(null);
            }
          },
          error: function (jqXHR, estado, error) {},
        });

        break;

      default:
        break;
    }
  });
}
