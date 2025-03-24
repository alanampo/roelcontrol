let edit_mode = false;

$(document).ready(() => {
  $("#input-precio")
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

  $("#dias-produccion-variedad").on("propertychange input", function (e) {
    this.value = this.value.replace(/\D/g, "");
  });
});

function handleKeyDown(event, obj,id) {
  if (event.key === 'Enter') {
    $(obj).parent().find("button").click()
    setTimeout(()=>{
      if (id){
        $(".input-value-"+id).focus()
      }
      else{
        $("#input-nombre-atributo").focus()
      }
      
    },500)
  }
}

function busca_productos(filtro) {
  const filtroAtributos = $("#select_filtro_atributos").val();
 
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando productos, espere...");
    },
    url: "data_ver_variedades.php",
    type: "POST",
    data: { filtro: filtro, consulta: "busca_variedades", filtroAtributos },
    success: function (x) {
      $("#tabla_entradas").html(x);
      let table = $("#tabla").DataTable({
        pageLength: 100,
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

      $("#tabla").on("draw.dt", function () {

      });
    },
    error: function (jqXHR, estado, error) {
      swal("Ocurrió un error al cargar los datos", error.toString(), "error");
    },
  });
}

function editarVariedad(event, obj) {
  event.preventDefault()
    const row = $(obj).closest("tr");
    let id = $(row).attr("x-id");
    let nombre = $(row).attr("x-nombre");
    let precio = $(row).attr("x-precio");
    let precio_iva = $(row).attr("x-precio-iva");
    let id_interno = $(row).attr("x-id-interno");
    let codigo_tipo = $(row).attr("x-codigo-tipo");
    let dias_produccion = $(row).attr("x-dias-produccion");
    MostrarModalAgregarProducto({
      id: id,
      nombre: nombre,
      precio: precio,
      precio_iva: precio_iva,
      id_interno: id_interno,
      codigo_tipo: codigo_tipo,
      dias_produccion: dias_produccion,
    });
}

function MostrarModalAgregarProducto(producto) {
  if (producto) {
    // EDITANDO
    $("#ModalAgregarProducto").find("#titulo").html("Modificar Variedad");
    $("#select_tipo2").attr("disabled", "disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#input-nombre").val(producto.nombre);
    $("#input-precio").val(producto.precio);
    $("#input-codigo").val(producto.id_interno).attr("disabled", true);
    $("#dias-produccion-variedad").val(
      producto.dias_produccion ? producto.dias_produccion : ""
    );
    $("#select-dias-produccion").addClass("d-none");
    $("#ModalAgregarProducto").attr("x-id-variedad", producto.id);
    $("#ModalAgregarProducto").attr("x-codigo-tipo", producto.codigo_tipo);
    if (producto.codigo_tipo == "E" || producto.codigo_tipo == "S") {
      $(".form-dias-produccion-variedad").removeClass("d-none");
    } else {
      $(".form-dias-produccion-variedad").addClass("d-none");
    }

    edit_mode = true;
    getAtributosVariedad(producto.id)
  } else {
    //AGREGANDO
    $(
      "#input-nombre, #input-precio, #input-codigo, #dias-produccion-variedad"
    ).val("");
    $("#select-dias-produccion").val("0");
    $("#input-codigo").removeAttr("disabled");
    $("#select_tipo2").removeAttr("disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#ModalAgregarProducto").find("#titulo").html("Agregar Variedad");
    $("#select-dias-produccion").removeClass("d-none");
    $("#ModalAgregarProducto").removeAttr("x-codigo-tipo");
    edit_mode = false;
    $(".form-dias-produccion-variedad").addClass("d-none");
    getAtributosVariedad()
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
  const precio = $("#input-precio").val().trim();
  const codigo = $("#input-codigo").val().trim().replace(/\s+/g, "");
  const dias_produccion = $("#dias-produccion-variedad")
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
  } else if (!codigo.length) {
    swal("Ingresá un código para la Variedad", "", "error");
    return;
  } else if (!precio.length || isNaN(precio)) {
    swal("Debes ingresar el Precio!", "", "error");
    return;
  } else if (
    (codigo_tipo == "E" || codigo_tipo == "S") &&
    (!dias_produccion.length ||
      isNaN(dias_produccion) ||
      parseInt(dias_produccion) < 0 ||
      parseInt(dias_produccion) > 365)
  ) {
    swal(
      "Ingresa la cantidad de días que permanecerá la Variedad en Producción",
      "",
      "error"
    );
    return;
  }

  let atributos = null;
  if ($("#table-atributos > tbody > tr").length) {
    atributos = [];
    $("#table-atributos > tbody > tr").each(function (e) {
      const id = $(this).attr("x-id");

      const valorSelect = $(this).find(".selectpicker").length
        ? $(this).find(".selectpicker").first().val()
        : null;
      atributos.push({
        id: id,
        valorSelect: valorSelect && valorSelect.length ? valorSelect : null,
      });
    });
  }

  CerrarModalProducto();
  if (!edit_mode) {
    $.ajax({
      url: "data_ver_variedades.php",
      type: "POST",
      data: {
        consulta: "agregar_variedad",
        nombre: nombre,
        precio: precio,
        id_tipo: id_tipo,
        codigo: codigo,
        atributos: atributos && atributos.length ? JSON.stringify(atributos) : null,
        dias_produccion:
          codigo_tipo == "E" || codigo_tipo == "S" ? dias_produccion : null,
      },
      success: function (x) {
        if (x.trim() == "success") {
          busca_productos(null);
          $("#input-nombre,#input-precio,#input-codigo").val("");
          $("#select_tipo2").val("default").selectpicker("refresh");
          $("#input-nombre").focus();
          swal("La Variedad se agregó correctamente!", "", "success");
        } else {
          swal(x.replace("error: ", ""), "", "error");
          $("#ModalAgregarProducto").modal("show");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
      },
    });
  } else {
    codigo_tipo = $("#ModalAgregarProducto").attr("x-codigo-tipo");

    $.ajax({
      url: "data_ver_variedades.php",
      type: "POST",
      data: {
        consulta: "editar_variedad",
        id_variedad: $("#ModalAgregarProducto").attr("x-id-variedad"),
        nombre: nombre,
        precio: precio,
        atributos: atributos && atributos.length ? JSON.stringify(atributos) : null,
        dias_produccion:
          codigo_tipo == "E" || codigo_tipo == "S" ? dias_produccion : null,
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
    data: { consulta: "busca_tipos_select" },
    success: function (x) {
      $(".selectpicker").selectpicker();
      $("#select_tipo").html(x).selectpicker("refresh");
      $("#select_tipo2").html(x).selectpicker("refresh");

      $("#select_tipo2").on(
        "change",
        function (e, clickedIndex, newValue, oldValue) {
          const codigo = $("#select_tipo2")
            .find("option:selected")
            .attr("x-codigo")
            .trim();
          if (codigo == "E" || codigo == "S") {
            $(".form-dias-produccion-variedad").removeClass("d-none");
          } else if (codigo == "HE" || codigo == "HS") {
            $(".form-dias-produccion-variedad").addClass("d-none");
          }
          cargarUltimaVariedad2(this.value);
        }
      );
    },
    error: function (jqXHR, estado, error) { },
  });
}

function eliminar(id) {
  swal(
    "Estás seguro/a de eliminar la Variedad seleccionada?",
    "Sólo se podrá eliminar si no está contenido en ningún pedido anterior.",
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
          beforeSend: function () { },
          url: "data_ver_variedades.php",
          type: "POST",
          data: { consulta: "eliminar_variedad", id_variedad: id },
          success: function (x) {
            if (x.trim() == "success") {
              swal("Eliminaste el pago correctamente!", "", "success");
              busca_productos(null);
            } else {
              swal(
                "Ocurrió un error!",
                "Recuerda que sólo se pueden eliminar variedades que no estén involucradas en algún pedido",
                "error"
              );
            }
          },
          error: function (jqXHR, estado, error) { },
        });

        break;

      default:
        break;
    }
  });
}

function cargarUltimaVariedad2(id_tipo) {
  $.ajax({
    url: "data_cargar_pedido.php",
    type: "POST",
    data: {
      consulta: "cargar_ultima_variedad",
      id_tipo: id_tipo,
    },
    success: function (x) {
      if (x.includes("success")) {
        $("#input-codigo").val(x.replace("success:", ""));
      }
    },
  });
}

function exportarVariedades() {
  $.ajax({
    url: "data_ver_variedades.php",
    type: "POST",
    data: {
      consulta: "exportar_variedades",

    },
    success: function (x) {
      console.log(x)
      if (x.includes("success")) {
        swal("Generaste el CSV correctamente!", "", "success")
      }
    },
  });
}
