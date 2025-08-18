let edit_mode = false;
// Variables globales para manejo de imágenes
let imagenesSeleccionadas = [];
let imagenesAEliminar = [];
let maxImagenes = 3;
$(document).ready(() => {
  $("#input-precio,#input-precio-detalle")
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

  $("#btn-seleccionar-imagenes").click(function () {
    $("#imagenes-variedad").click();
  });

  // Evento cuando se seleccionan archivos
  $("#imagenes-variedad").change(function () {
    manejarSeleccionImagenes(this.files);
  });
});

function handleKeyDown(event, obj, id) {
  if (event.key === 'Enter') {
    $(obj).parent().find("button").click()
    setTimeout(() => {
      if (id) {
        $(".input-value-" + id).focus()
      }
      else {
        $("#input-nombre-atributo").focus()
      }

    }, 500)
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
        columnDefs: [
          {
            targets: -2, // anteúltima columna
            width: "200px", // ancho fijo
            render: function (data, type, row) {
              return `<div style="max-width:200px; text-align:left;">${data}</div>`;
            }
          }
        ],
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
  let descripcion = $(row).attr("x-descripcion");
  let precio = $(row).attr("x-precio");
  let precio_detalle = $(row).attr("x-precio-detalle");
  let precio_detalle_iva = $(row).attr("x-precio-detalle-iva");
  let precio_iva = $(row).attr("x-precio-iva");
  let id_interno = $(row).attr("x-id-interno");
  let codigo_tipo = $(row).attr("x-codigo-tipo");
  let dias_produccion = $(row).attr("x-dias-produccion");
  MostrarModalAgregarProducto({
    id: id,
    nombre: nombre,
    precio: precio,
    precio_iva: precio_iva,
    precio_detalle: precio_detalle,
    precio_detalle_iva: precio_detalle_iva,
    id_interno: id_interno,
    codigo_tipo: codigo_tipo,
    dias_produccion: dias_produccion,
    descripcion: descripcion ?? null
  });
}



function CerrarModalProducto() {
  $("#ModalAgregarProducto").modal("hide");
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
//////IMAGENES

// Función para manejar la selección de imágenes
function manejarSeleccionImagenes(files) {
  const imagenesActuales = imagenesSeleccionadas.length;
  const imagenesExistentes = $("#contenedor-imagenes-existentes .imagen-existente").not(".imagen-marcada-eliminar").length;
  const totalActuales = imagenesActuales + imagenesExistentes;

  if (files.length + totalActuales > maxImagenes) {
    swal(`Solo puedes subir máximo ${maxImagenes} imágenes`, "", "error");
    return;
  }

  for (let i = 0; i < files.length; i++) {
    const file = files[i];

    // Validar tipo de archivo
    if (!file.type.startsWith('image/')) {
      swal(`El archivo ${file.name} no es una imagen válida`, "", "error");
      continue;
    }

    // Validar tamaño (5MB máximo)
    if (file.size > 5 * 1024 * 1024) {
      swal(`La imagen ${file.name} es muy grande. Máximo 5MB`, "", "error");
      continue;
    }

    imagenesSeleccionadas.push(file);
    mostrarPreviewImagen(file, imagenesSeleccionadas.length - 1);
  }

  // Limpiar input
  $("#imagenes-variedad").val('');

  // Mostrar/ocultar contenedor
  if (imagenesSeleccionadas.length > 0) {
    $("#contenedor-imagenes-preview").show();
  }
}

// Función para mostrar preview de imagen nueva
function mostrarPreviewImagen(file, index) {
  const reader = new FileReader();
  reader.onload = function (e) {
    const html = `
      <div class="col-md-4 imagen-preview-container" data-index="${index}">
        <img src="${e.target.result}" class="imagen-preview" alt="Preview">
        <button type="button" class="btn-eliminar-imagen" onclick="eliminarImagenNueva(${index})">
          <i class="fa fa-times"></i>
        </button>
        <div class="text-center mt-1">
          <small class="text-success"><strong>NUEVA</strong></small>
        </div>
      </div>
    `;
    $("#contenedor-imagenes-preview").append(html);
  };
  reader.readAsDataURL(file);
}

// Función para eliminar imagen nueva (antes de guardar)
function eliminarImagenNueva(index) {
  imagenesSeleccionadas.splice(index, 1);
  $(`[data-index="${index}"]`).remove();

  // Reindexar elementos restantes
  $("#contenedor-imagenes-preview .imagen-preview-container").each(function (newIndex) {
    $(this).attr('data-index', newIndex);
    $(this).find('.btn-eliminar-imagen').attr('onclick', `eliminarImagenNueva(${newIndex})`);
  });

  if (imagenesSeleccionadas.length === 0) {
    $("#contenedor-imagenes-preview").hide();
  }
}

// Función para marcar/desmarcar imagen existente para eliminar
function toggleEliminarImagenExistente(idImagen, elemento) {
  const container = $(elemento).closest('.imagen-existente');

  if (container.hasClass('imagen-marcada-eliminar')) {
    // Desmarcar para eliminar
    container.removeClass('imagen-marcada-eliminar');
    const index = imagenesAEliminar.indexOf(idImagen);
    if (index > -1) {
      imagenesAEliminar.splice(index, 1);
    }
  } else {
    // Marcar para eliminar
    container.addClass('imagen-marcada-eliminar');
    if (!imagenesAEliminar.includes(idImagen)) {
      imagenesAEliminar.push(idImagen);
    }
  }
}

// Función para cargar imágenes existentes (modo edición)
function cargarImagenesExistentes(idVariedad) {
  $.ajax({
    url: "data_ver_variedades.php",
    type: "POST",
    data: {
      consulta: "obtener_imagenes_variedad",
      id_variedad: idVariedad
    },
    success: function (response) {
      try {
        const imagenes = JSON.parse(response);
        mostrarImagenesExistentes(imagenes);
      } catch (e) {
        console.log("No hay imágenes existentes o error en respuesta:", response);
      }
    },
    error: function () {
      console.log("Error al cargar imágenes existentes");
    }
  });
}

// Función para mostrar imágenes existentes
function mostrarImagenesExistentes(imagenes) {
  $("#contenedor-imagenes-existentes").empty();

  if (imagenes && imagenes.length > 0) {
    let html = `
      <div class="col-12 mb-2">
        <h6 class="text-primary">Imágenes Actuales:</h6>
      </div>
    `;

    imagenes.forEach(function (imagen) {
      html += `
        <div class="col-md-4 imagen-existente mb-3" data-id="${imagen.id}">
          <div class="imagen-preview-container">
            <img src="uploads/variedades/${imagen.nombre_archivo}" 
                 class="imagen-preview" 
                 alt="Imagen de variedad">
            <button type="button" 
                    class="btn-eliminar-imagen" 
                    onclick="toggleEliminarImagenExistente(${imagen.id}, this)"
                    title="Marcar para eliminar">
              <i class="fa fa-times"></i>
            </button>
          </div>
        </div>
      `;
    });

    $("#contenedor-imagenes-existentes").html(html);
    $("#contenedor-imagenes-existentes").show();
  } else {
    $("#contenedor-imagenes-existentes").hide();
  }
}

// Función modificada para guardar variedad (incluye imágenes)
function GuardarProducto() {
  const id_tipo = $("#select_tipo2 option:selected").val();
  const nombre = $("#input-nombre").val().trim().replace(/\s+/g, " ");
  const descripcion = $("#input-descripcion").val().trim();
  const precio = $("#input-precio").val().trim();
  const precio_detalle = $("#input-precio-detalle").val().trim();
  const codigo = $("#input-codigo").val().trim().replace(/\s+/g, "");
  const dias_produccion = $("#dias-produccion-variedad").val().trim().replace(/\s+/g, "");

  let codigo_tipo = $("#select_tipo2").find("option:selected").attr("x-codigo");

  // Validaciones existentes...
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
    swal("Ingresa la cantidad de días que permanecerá la Variedad en Producción", "", "error");
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

  // Crear FormData para incluir archivos
  const formData = new FormData();

  if (!edit_mode) {
    formData.append('consulta', 'agregar_variedad');
    formData.append('id_tipo', id_tipo);
    formData.append('codigo', codigo);
  } else {
    formData.append('consulta', 'editar_variedad');
    formData.append('id_variedad', $("#ModalAgregarProducto").attr("x-id-variedad"));
    codigo_tipo = $("#ModalAgregarProducto").attr("x-codigo-tipo");
  }

  // Datos comunes
  formData.append('nombre', nombre);
  formData.append('descripcion', descripcion);
  formData.append('precio', precio);
  formData.append('precio_detalle', precio_detalle && precio_detalle.length ? precio_detalle : '');
  formData.append('atributos', atributos && atributos.length ? JSON.stringify(atributos) : '');
  formData.append('dias_produccion', (codigo_tipo == "E" || codigo_tipo == "S") ? dias_produccion : '');

  // Agregar imágenes nuevas
  imagenesSeleccionadas.forEach(function (file, index) {
    formData.append('imagenes[]', file);
  });

  // Agregar IDs de imágenes a eliminar
  if (imagenesAEliminar.length > 0) {
    formData.append('imagenes_eliminar', JSON.stringify(imagenesAEliminar));
  }

  $.ajax({
    url: "data_ver_variedades.php",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (x) {
      if (x.trim() == "success") {
        busca_productos(null);
        limpiarFormularioImagenes();
        if (!edit_mode) {
          $("#input-nombre,#input-precio,#input-precio-detalle,#input-codigo").val("");
          $("#select_tipo2").val("default").selectpicker("refresh");
          $("#input-nombre").focus();
          swal("La Variedad se agregó correctamente!", "", "success");
        } else {
          swal("El producto fue modificado correctamente!", "", "success");
        }
      } else {
        swal(x.replace("error: ", ""), "", "error");
        $("#ModalAgregarProducto").modal("show");
      }
    },
    error: function (jqXHR, estado, error) {
      swal("Ocurrió un error", error.toString(), "error");
      $("#ModalAgregarProducto").modal("show");
    },
  });
}

// Función para limpiar el formulario de imágenes
function limpiarFormularioImagenes() {
  imagenesSeleccionadas = [];
  imagenesAEliminar = [];
  $("#contenedor-imagenes-preview").hide().empty();
  $("#contenedor-imagenes-existentes").hide().empty();
  $("#imagenes-variedad").val('');
}

// Función modificada para mostrar modal
function MostrarModalAgregarProducto(producto) {
  limpiarFormularioImagenes(); // Limpiar imágenes al abrir modal

  if (producto) {
    // EDITANDO
    $("#ModalAgregarProducto").find("#titulo").html("Modificar Variedad");
    $("#select_tipo2").attr("disabled", "disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#input-nombre").val(producto.nombre);
    $("#input-descripcion").val(producto.descripcion ?? '');
    $("#input-precio").val(producto.precio);
    $("#input-precio-detalle").val(producto.precio_detalle);
    $("#input-codigo").val(producto.id_interno).attr("disabled", true);
    $("#dias-produccion-variedad").val(producto.dias_produccion ? producto.dias_produccion : "");
    $("#select-dias-produccion").addClass("d-none");
    $("#ModalAgregarProducto").attr("x-id-variedad", producto.id);
    $("#ModalAgregarProducto").attr("x-codigo-tipo", producto.codigo_tipo);

    if (producto.codigo_tipo == "E" || producto.codigo_tipo == "S") {
      $(".form-dias-produccion-variedad").removeClass("d-none");
    } else {
      $(".form-dias-produccion-variedad").addClass("d-none");
    }

    edit_mode = true;
    getAtributosVariedad(producto.id);

    // Cargar imágenes existentes
    cargarImagenesExistentes(producto.id);

  } else {
    //AGREGANDO
    $("#input-nombre, #input-precio, #input-precio-detalle, #input-codigo, #dias-produccion-variedad").val("");
    $("#select-dias-produccion").val("0");
    $("#input-codigo").removeAttr("disabled");
    $("#select_tipo2").removeAttr("disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#ModalAgregarProducto").find("#titulo").html("Agregar Variedad");
    $("#select-dias-produccion").removeClass("d-none");
    $("#ModalAgregarProducto").removeAttr("x-codigo-tipo");
    edit_mode = false;
    $(".form-dias-produccion-variedad").addClass("d-none");
    getAtributosVariedad();
  }

  $("#ModalAgregarProducto").modal("show");
  $("#input-nombre").focus();
}