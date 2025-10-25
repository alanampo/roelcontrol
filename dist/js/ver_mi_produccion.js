// Configuración de Toastr
toastr.options = {
  closeButton: true,
  progressBar: true,
  positionClass: "toast-top-right",
  timeOut: 3000
};

// Variables globales
let variedades = [];
let descripcionesManuales = [];
let imagenesSeleccionadas = [];
let registroActual = null;

$(document).ready(function() {
  cargarVariedades();
  cargarDescripcionesManuales();
  cargarEstadisticas();
  cargarHistorial();

  // Establecer fecha actual por defecto
  const hoy = new Date().toISOString().split('T')[0];
  $("#input-fecha").val(hoy);

  // Event listeners
  $("#btn-registrar").on("click", guardarRegistro);
  $("#btn-cancelar").on("click", limpiarFormulario);
  $("#input-imagenes").on("change", manejarSeleccionImagenes);
  $("#btn-seleccionar-imagenes").on("click", function() {
    $("#input-imagenes").click();
  });

  // Cambio de tipo de item
  $("#select-tipo-item").on("change", cambioTipoItem);

  // Cambio en select de descripción manual
  $("#select-descripcion-manual").on("changed.bs.select", function() {
    const valor = $(this).val();
    if (valor === "__NUEVO__") {
      $("#col-descripcion-texto").show();
      $("#input-descripcion-texto").focus();
    } else {
      $("#col-descripcion-texto").hide();
      $("#input-descripcion-texto").val("");
    }
  });
});

// ==================== CARGA DE DATOS ====================

function cargarVariedades() {
  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_variedades" },
    success: function(x) {
      try {
        variedades = JSON.parse(x);
        renderizarSelectVariedades();
      } catch (error) {
        console.error("Error al cargar variedades:", error);
        toastr.error("Error al cargar las variedades");
      }
    },
    error: function() {
      toastr.error("Error de conexión al cargar variedades");
    }
  });
}

function renderizarSelectVariedades() {
  const select = $("#select-variedad");
  select.empty();
  select.append('<option value="">Seleccionar Variedad</option>');

  variedades.forEach(function(variedad) {
    select.append(`<option value="${variedad.id}">${variedad.nombre}</option>`);
  });

  select.selectpicker('refresh');
}

function cargarDescripcionesManuales() {
  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_descripciones_manuales" },
    success: function(x) {
      try {
        descripcionesManuales = JSON.parse(x);
        renderizarSelectDescripciones();
      } catch (error) {
        console.error("Error al cargar descripciones:", error);
      }
    },
    error: function() {
      console.error("Error de conexión al cargar descripciones");
    }
  });
}

function renderizarSelectDescripciones() {
  const select = $("#select-descripcion-manual");
  select.empty();
  select.append('<option value="__NUEVO__">+ Nueva descripción...</option>');

  descripcionesManuales.forEach(function(desc) {
    select.append(`<option value="${desc}">${desc}</option>`);
  });

  select.selectpicker('refresh');
}

function cambioTipoItem() {
  const tipo = $("#select-tipo-item").val();

  if (tipo === "variedad") {
    $("#col-variedad").show();
    $("#col-descripcion-manual").hide();
    $("#col-descripcion-texto").hide();
    $("#select-variedad").selectpicker('refresh');
  } else {
    $("#col-variedad").hide();
    $("#col-descripcion-manual").show();
    $("#select-descripcion-manual").selectpicker('refresh');
  }
}

function cargarEstadisticas() {
  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_estadisticas" },
    success: function(x) {
      try {
        const stats = JSON.parse(x);
        renderizarEstadisticas(stats);
      } catch (error) {
        console.error("Error al cargar estadísticas:", error);
      }
    },
    error: function() {
      console.error("Error al cargar estadísticas");
    }
  });
}

function renderizarEstadisticas(stats) {
  // Actualizar números
  $("#stat-diaria").text(formatNumber(stats.produccion_diaria));
  $("#stat-semanal").text(formatNumber(stats.produccion_semanal));
  $("#stat-mensual").text(formatNumber(stats.produccion_mensual));
  $("#stat-meta").text(formatNumber(stats.meta_semanal));
  $("#stat-progreso").text(stats.progreso_semanal + "%");
  $("#stat-bono").text("$" + formatNumber(stats.bono_estimado));

  // Actualizar barra de progreso
  const progressBar = $("#progress-bar-semanal");
  progressBar.css("width", stats.progreso_semanal + "%");
  progressBar.attr("aria-valuenow", stats.progreso_semanal);

  // Actualizar clase de color según progreso
  progressBar.removeClass("bg-success bg-warning bg-danger");
  if (stats.progreso_semanal >= 100) {
    progressBar.addClass("bg-success");
  } else if (stats.progreso_semanal >= 75) {
    progressBar.addClass("bg-warning");
  } else {
    progressBar.addClass("bg-danger");
  }

  // Actualizar indicador
  const indicador = $("#indicador-cumplimiento");
  indicador.removeClass("bg-green bg-yellow bg-red");

  if (stats.indicador === "green") {
    indicador.addClass("bg-green");
    indicador.html('<i class="fa fa-check-circle"></i> Cumpliendo');
  } else if (stats.indicador === "yellow") {
    indicador.addClass("bg-yellow");
    indicador.html('<i class="fa fa-exclamation-triangle"></i> En Progreso');
  } else {
    indicador.addClass("bg-red");
    indicador.html('<i class="fa fa-times-circle"></i> Bajo Meta');
  }
}

function cargarHistorial() {
  const mesActual = new Date();
  const primerDia = new Date(mesActual.getFullYear(), mesActual.getMonth(), 1).toISOString().split('T')[0];
  const ultimoDia = new Date(mesActual.getFullYear(), mesActual.getMonth() + 1, 0).toISOString().split('T')[0];

  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_mi_produccion",
      fecha_desde: primerDia,
      fecha_hasta: ultimoDia
    },
    success: function(x) {
      try {
        const registros = JSON.parse(x);
        renderizarHistorial(registros);
      } catch (error) {
        console.error("Error al cargar historial:", error);
        renderizarHistorial([]);
      }
    },
    error: function() {
      console.error("Error al cargar historial");
      renderizarHistorial([]);
    }
  });
}

function renderizarHistorial(registros) {
  let html = "";

  if (registros.length === 0) {
    html = `<div class="callout callout-info">
              <p>No hay registros de producción para este mes.</p>
            </div>`;
    $("#tabla-historial").html(html);
    return;
  }

  html = `<div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="bg-light">
                <tr>
                  <th>Fecha</th>
                  <th>Turno</th>
                  <th>Variedad</th>
                  <th>Cantidad</th>
                  <th>Ubicación</th>
                  <th>Evidencias</th>
                  <th>Estado</th>
                  <th style="width:80px;">Acciones</th>
                </tr>
              </thead>
              <tbody>`;

  registros.forEach(function(reg) {
    const fechaFormateada = moment(reg.fecha).format('DD/MM/YYYY');
    const turnoIcon = reg.turno === 'mañana' ? 'fa-sun-o' : 'fa-moon-o';
    const turnoColor = reg.turno === 'mañana' ? 'text-warning' : 'text-info';
    const validadoIcon = reg.validado == 1 ? '<i class="fa fa-check-circle text-success"></i> Validado' : '<i class="fa fa-clock-o text-muted"></i> Pendiente';
    const ubicacion = reg.ubicacion_lote || '-';

    // Mostrar variedad o descripción manual
    const descripcionItem = reg.item_tipo === 'variedad' ? reg.variedad_nombre : reg.descripcion_manual;

    html += `<tr>
              <td>${fechaFormateada}</td>
              <td><i class="fa ${turnoIcon} ${turnoColor}"></i> ${reg.turno}</td>
              <td>${descripcionItem}</td>
              <td><strong>${formatNumber(reg.cantidad_plantines)}</strong></td>
              <td>${ubicacion}</td>
              <td class="text-center">`;

    if (reg.num_evidencias > 0) {
      html += `<button class="btn btn-xs btn-primary" onclick="verEvidencias(${reg.id})" title="Ver ${reg.num_evidencias} foto(s)">
                 <i class="fa fa-camera"></i> ${reg.num_evidencias}
               </button>`;
    } else {
      html += '<span class="text-muted">Sin fotos</span>';
    }

    html += `</td>
              <td>${validadoIcon}</td>
              <td class="text-center">`;

    if (reg.validado == 0) {
      html += `<button class="btn btn-xs btn-danger" onclick="eliminarRegistro(${reg.id})" title="Eliminar">
                 <i class="fa fa-trash"></i>
               </button>`;
    }

    html += `</td>
            </tr>`;
  });

  html += `</tbody></table></div>`;

  $("#tabla-historial").html(html);
}

// ==================== MANEJO DE IMÁGENES ====================

function manejarSeleccionImagenes(event) {
  const files = event.target.files;

  if (files.length === 0) return;

  // Validar cantidad máxima
  if (files.length > 5) {
    toastr.warning("Máximo 5 imágenes por registro");
    return;
  }

  // Mostrar loading
  $("#preview-imagenes").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Comprimiendo imágenes...</p></div>');
  $("#preview-imagenes").show();

  // Comprimir imágenes
  ImageCompressor.compressMultiple(files, function(current, total, result) {
    console.log(`Comprimiendo ${current}/${total}:`, result);
  }).then(function(results) {
    imagenesSeleccionadas = results;
    mostrarPreviewImagenes();
    // toastr.success(`${results.length} imagen(es) optimizada(s)`);
  }).catch(function(error) {
    console.error("Error al comprimir:", error);
    toastr.error("Error al procesar las imágenes");
    $("#preview-imagenes").hide();
  });
}

function mostrarPreviewImagenes() {
  let html = '<div class="row">';

  imagenesSeleccionadas.forEach(function(img, index) {
    html += `<div class="col-md-3 mb-3">
               <div class="imagen-preview-container">
                 <img src="${URL.createObjectURL(img.file)}" class="imagen-preview img-thumbnail" alt="Preview">
                 <button type="button" class="btn btn-danger btn-xs btn-eliminar-preview" onclick="eliminarImagenPreview(${index})">
                   <i class="fa fa-times"></i>
                 </button>
                 <div class="text-center mt-1">
                   <small class="text-muted">${img.compressed.sizeKB} KB</small>
                 </div>
               </div>
             </div>`;
  });

  html += '</div>';
  $("#preview-imagenes").html(html).show();
}

function eliminarImagenPreview(index) {
  imagenesSeleccionadas.splice(index, 1);

  if (imagenesSeleccionadas.length === 0) {
    $("#preview-imagenes").hide();
    $("#input-imagenes").val('');
  } else {
    mostrarPreviewImagenes();
  }
}

// ==================== GUARDAR REGISTRO ====================

function guardarRegistro() {
  // Validaciones
  const fecha = $("#input-fecha").val();
  const turno = $("#select-turno").val();
  const tipoItem = $("#select-tipo-item").val();
  const cantidad = $("#input-cantidad").val();

  if (!fecha) {
    toastr.error("Debes seleccionar una fecha");
    return;
  }

  if (!turno) {
    toastr.error("Debes seleccionar un turno");
    return;
  }

  // Validar según tipo de item
  let idVariedad = null;
  let descripcionManual = null;

  if (tipoItem === "variedad") {
    idVariedad = $("#select-variedad").val();
    if (!idVariedad) {
      toastr.error("Debes seleccionar una variedad");
      return;
    }
  } else {
    const selectDesc = $("#select-descripcion-manual").val();
    if (selectDesc === "__NUEVO__") {
      descripcionManual = $("#input-descripcion-texto").val().trim();
      if (!descripcionManual) {
        toastr.error("Debes ingresar una descripción");
        return;
      }
    } else {
      descripcionManual = selectDesc;
    }

    if (!descripcionManual) {
      toastr.error("Debes seleccionar o ingresar una descripción");
      return;
    }
  }

  if (!cantidad || cantidad <= 0) {
    toastr.error("Debes ingresar una cantidad válida");
    return;
  }

  // Deshabilitar botón
  $("#btn-registrar").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

  const ubicacion = $("#input-ubicacion").val();
  const observaciones = $("#input-observaciones").val();

  const datos = {
    consulta: "guardar_registro",
    fecha: fecha,
    turno: turno,
    item_tipo: tipoItem,
    cantidad_plantines: cantidad,
    ubicacion_lote: ubicacion,
    observaciones: observaciones
  };

  if (tipoItem === "variedad") {
    datos.id_variedad = idVariedad;
  } else {
    datos.descripcion_manual = descripcionManual;
  }

  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: datos,
    success: function(x) {
      try {
        const response = JSON.parse(x);

        if (response.success) {
          toastr.success("Registro guardado correctamente");
          registroActual = response.id_registro;

          // Si hay imágenes, subirlas
          if (imagenesSeleccionadas.length > 0) {
            subirEvidencias(response.id_registro);
          } else {
            finalizarGuardado();
          }
        } else {
          toastr.error(response.error || "Error al guardar el registro");
          $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar');
        }
      } catch (error) {
        console.error("Error:", error);
        toastr.error("Error al procesar la respuesta");
        $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar');
      }
    },
    error: function() {
      toastr.error("Error de conexión");
      $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar');
    }
  });
}

function subirEvidencias(idRegistro) {
  let subidas = 0;
  const total = imagenesSeleccionadas.length;

  toastr.info(`Subiendo ${total} imagen(es)...`, '', { timeOut: 0 });

  imagenesSeleccionadas.forEach(function(img, index) {
    const formData = new FormData();
    formData.append('consulta', 'subir_evidencia');
    formData.append('id_registro', idRegistro);
    formData.append('imagen', img.file);

    $.ajax({
      url: "data_mi_produccion.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function(x) {
        try {
          const response = JSON.parse(x);
          if (response.success) {
            subidas++;
            if (subidas === total) {
              toastr.clear();
              toastr.success(`${total} evidencia(s) subida(s)`);
              finalizarGuardado();
            }
          } else {
            console.error("Error al subir imagen:", response.error);
          }
        } catch (error) {
          console.error("Error al procesar respuesta:", error);
        }
      },
      error: function() {
        console.error("Error al subir imagen");
      }
    });
  });
}

function finalizarGuardado() {
  limpiarFormulario();
  cargarDescripcionesManuales(); // Recargar por si agregó una nueva
  cargarEstadisticas();
  cargarHistorial();
  $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar');
}

function limpiarFormulario() {
  $("#input-cantidad").val('');
  $("#input-ubicacion").val('');
  $("#input-observaciones").val('');
  $("#select-tipo-item").val('variedad');
  $("#select-variedad").val('').selectpicker('refresh');
  $("#select-descripcion-manual").val('__NUEVO__').selectpicker('refresh');
  $("#input-descripcion-texto").val('');
  $("#col-variedad").show();
  $("#col-descripcion-manual").hide();
  $("#col-descripcion-texto").hide();
  $("#input-imagenes").val('');
  imagenesSeleccionadas = [];
  $("#preview-imagenes").hide();
  registroActual = null;
}

// ==================== OTRAS FUNCIONES ====================

function verEvidencias(idRegistro) {
  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_mi_produccion"
    },
    success: function(x) {
      try {
        const registros = JSON.parse(x);
        const registro = registros.find(r => r.id == idRegistro);

        if (registro && registro.evidencias.length > 0) {
          let html = '<div class="row">';
          registro.evidencias.forEach(function(ev) {
            html += `<div class="col-md-4 mb-3">
                       <a href="uploads/evidencias/${ev.ruta_imagen}" target="_blank">
                         <img src="uploads/evidencias/${ev.ruta_imagen}" class="img-thumbnail" style="width:100%;">
                       </a>
                       <div class="text-center mt-1">
                         <small class="text-muted">${ev.tamano_kb} KB</small>
                       </div>
                     </div>`;
          });
          html += '</div>';

          swal({
            title: "Evidencias Fotográficas",
            content: {
              element: "div",
              attributes: {
                innerHTML: html
              }
            },
            button: "Cerrar"
          });
        }
      } catch (error) {
        console.error("Error:", error);
      }
    }
  });
}

function eliminarRegistro(idRegistro) {
  swal(
    "¿Estás seguro de eliminar este registro?",
    "Esta acción no se puede deshacer y se eliminarán todas las evidencias",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "SÍ, ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          url: "data_mi_produccion.php",
          type: "POST",
          data: {
            consulta: "eliminar_registro",
            id_registro: idRegistro
          },
          success: function(x) {
            if (x.includes("success")) {
              toastr.success("Registro eliminado");
              cargarEstadisticas();
              cargarHistorial();
            } else {
              toastr.error("No se pudo eliminar: " + x);
            }
          },
          error: function() {
            toastr.error("Error de conexión");
          }
        });
        break;

      default:
        break;
    }
  });
}

function formatNumber(num) {
  return parseFloat(num).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
