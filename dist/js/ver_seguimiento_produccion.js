let mesActual = new Date().getMonth() + 1; // 1-12
let anioActual = new Date().getFullYear();
let usuarioSeleccionado = null;
let totalMensual = 0;

const meses = [
  "ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO",
  "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"
];

// Configuración de toastr para este módulo
toastr.options = {
  closeButton: true,
  debug: false,
  newestOnTop: true,
  progressBar: true,
  positionClass: "toast-top-right",
  preventDuplicates: false,
  onclick: null,
  showDuration: "300",
  hideDuration: "1000",
  timeOut: "3000",
  extendedTimeOut: "1000",
  showEasing: "swing",
  hideEasing: "linear",
  showMethod: "fadeIn",
  hideMethod: "fadeOut"
};

$(document).ready(function () {
  cargarUsuarios();
  actualizarLabelMes();

  $("#btn-mes-anterior").on("click", function () {
    mesActual--;
    if (mesActual < 1) {
      mesActual = 12;
      anioActual--;
    }
    actualizarLabelMes();
    if (usuarioSeleccionado) {
      cargarDatosProduccion();
      cargarComentarios();
    }
  });

  $("#btn-mes-siguiente").on("click", function () {
    mesActual++;
    if (mesActual > 12) {
      mesActual = 1;
      anioActual++;
    }
    actualizarLabelMes();
    if (usuarioSeleccionado) {
      cargarDatosProduccion();
      cargarComentarios();
    }
  });

  $("#select-usuario").on("changed.bs.select", function (e, clickedIndex, newValue, oldValue) {
    usuarioSeleccionado = $(this).val();
    if (usuarioSeleccionado) {
      cargarDatosProduccion();
      cargarMetaUsuario();
      cargarComentarios();
      $("#seccion-comentarios").show();
    } else {
      $("#seccion-comentarios").hide();
    }
  });
});

function actualizarLabelMes() {
  $("#label-mes-anio").text(meses[mesActual - 1] + " " + anioActual);
}

function cargarUsuarios() {
  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: { consulta: "obtener_usuarios" },
    success: function (x) {
      if (x.length) {
        try {
          const usuarios = JSON.parse(x);
          $("#select-usuario").html("");
          usuarios.forEach(function (usuario) {
            $("#select-usuario").append(
              `<option value="${usuario.id_usuario}">${usuario.nombre_completo}</option>`
            );
          });
          $(".selectpicker").selectpicker("refresh");
        } catch (error) {
          console.error("Error al cargar usuarios:", error);
          toastr.error("Error al cargar usuarios");
        }
      }
    },
    error: function (jqXHR, estado, error) {
      console.error("Error al cargar usuarios:", error);
      toastr.error("Error al cargar usuarios");
    },
  });
}

function getDiasDelMes(mes, anio) {
  return new Date(anio, mes, 0).getDate();
}

function cargarDatosProduccion() {
  const diasDelMes = getDiasDelMes(mesActual, anioActual);

  $.ajax({
    beforeSend: function () {
      $("#tabla_produccion").html("<h4>Cargando datos...</h4>");
    },
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_produccion",
      mes: mesActual,
      anio: anioActual,
      id_usuario: usuarioSeleccionado,
    },
    success: function (x) {
      if (x.length) {
        try {
          const data = JSON.parse(x);
          renderizarTabla(data, diasDelMes);
        } catch (error) {
          console.error("Error al parsear datos:", error);
          $("#tabla_produccion").html(
            "<div class='callout callout-danger'><b>Error al cargar los datos</b></div>"
          );
        }
      } else {
        renderizarTabla([], diasDelMes);
      }
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_produccion").html(
        "<div class='callout callout-danger'><b>Error: " + error + "</b></div>"
      );
    },
  });
}

function renderizarTabla(datos, diasDelMes) {
  let html = "<div class='box box-primary'>";
  html += "<div class='box-header with-border'>";
  html += `<h3 class='box-title'>${meses[mesActual - 1]} ${anioActual}</h3>`;
  html += "<button class='btn btn-success btn-sm pull-right' onclick='agregarFila()'><i class='fa fa-plus'></i> Agregar Fila</button>";
  html += "</div>";
  html += "<div class='box-body' style='overflow-x:auto;'>";
  html += "<table class='table table-bordered table-condensed' id='tabla-datos'>";
  html += "<thead>";
  html += "<tr>";
  html += "<th style='min-width:150px;'>Descripción</th>";
  html += "<th style='min-width:100px;'>Precio</th>";

  // Columnas de días
  const hoy = new Date();
  const diaActual = hoy.getDate();
  const mesHoy = hoy.getMonth() + 1;
  const anioHoy = hoy.getFullYear();

  for (let dia = 1; dia <= diasDelMes; dia++) {
    const esDiaHoy = (dia === diaActual && mesActual === mesHoy && anioActual === anioHoy);
    const claseHoy = esDiaHoy ? 'dia-actual' : '';
    html += `<th class='text-center ${claseHoy}' style='min-width:120px;' data-dia='${dia}'>${dia}</th>`;
  }

  html += "<th class='text-center' style='min-width:100px;'>Total 1ª Q.</th>";
  html += "<th class='text-center' style='min-width:100px;'>Total 2ª Q.</th>";
  html += "<th class='text-center' style='min-width:100px;'>$ 1ª Q.</th>";
  html += "<th class='text-center' style='min-width:100px;'>$ 2ª Q.</th>";
  html += "<th style='min-width:80px;'>Acciones</th>";
  html += "</tr>";
  html += "</thead>";
  html += "<tbody>";

  let totalSueldoLiquido = 0;
  let totalPagar1Q = 0;
  let totalPagar2Q = 0;

  datos.forEach(function (item) {
    html += renderizarFila(item, diasDelMes);

    // Calcular totales
    let cantidad1Q = 0;
    let cantidad2Q = 0;
    for (let dia = 1; dia <= 15 && dia <= diasDelMes; dia++) {
      cantidad1Q += parseInt(item[`dia_${String(dia).padStart(2, '0')}`] || 0);
    }
    for (let dia = 16; dia <= diasDelMes; dia++) {
      cantidad2Q += parseInt(item[`dia_${String(dia).padStart(2, '0')}`] || 0);
    }

    const pagar1Q = cantidad1Q * parseFloat(item.precio || 0);
    const pagar2Q = cantidad2Q * parseFloat(item.precio || 0);

    totalPagar1Q += pagar1Q;
    totalPagar2Q += pagar2Q;
  });

  html += "</tbody>";
  html += "</table>";
  html += "</div>";

  // Footer con totales
  totalSueldoLiquido = totalPagar1Q + totalPagar2Q;
  totalMensual = totalSueldoLiquido; // Guardar para la sección de pagos

  html += "<div class='box-footer'>";
  html += "<div class='row'>";
  html += "<div class='col-md-6'>";
  html += `<p style='font-size: 15px; margin-bottom: 8px;'>Total a Pagar 1ª Quincena: <strong>$${formatNumber(totalPagar1Q)}</strong></p>`;
  html += `<p style='font-size: 15px; margin-bottom: 8px;'>Total a Pagar 2ª Quincena: <strong>$${formatNumber(totalPagar2Q)}</strong></p>`;
  html += `<p style='font-size: 16px; margin-bottom: 0; margin-top: 10px; border-top: 2px solid #ddd; padding-top: 10px;'>Total Mes: <strong class='text-primary'>$${formatNumber(totalSueldoLiquido)}</strong></p>`;
  html += "</div>";
  html += "<div class='col-md-6 text-right'>";
  //html += `<p style='font-size: 18px; margin-bottom: 0;'>Sueldo Líquido: <strong class='text-success'>$${formatNumber(totalSueldoLiquido)}</strong></p>`;
  html += "</div>";
  html += "</div>";
  html += "</div>";

  html += "</div>";

  $("#tabla_produccion").html(html);

  // Auto-scroll al día actual
  scrollToDiaActual();

  // Cargar pagos del mes
  if (usuarioSeleccionado) {
    cargarPagos();
  }
}

function renderizarFila(item, diasDelMes) {
  const itemId = item.id || 'new';
  const descripcion = item.item_tipo === 'variedad'
    ? item.descripcion_variedad
    : item.descripcion_manual || '';

  let html = `<tr data-item-id="${itemId}">`;

  // Descripción
  html += `<td>`;
  if (itemId === 'new') {
    html += `<select class="form-control input-descripcion" onchange="cambioTipoItem(this)">
      <option value="">Seleccionar...</option>
      <option value="manual">--- Descripción Manual ---</option>
    </select>`;
  } else {
    html += descripcion;
  }
  html += `</td>`;

  // Precio
  html += `<td><input type="number" class="form-control text-center input-precio" value="${item.precio || ''}" step="0.01" onchange="guardarCambio(this, ${itemId}, 'precio')" /></td>`;

  // Días del mes
  let cantidad1Q = 0;
  let cantidad2Q = 0;

  const hoy = new Date();
  const diaActual = hoy.getDate();
  const mesHoy = hoy.getMonth() + 1;
  const anioHoy = hoy.getFullYear();

  for (let dia = 1; dia <= diasDelMes; dia++) {
    const diaStr = String(dia).padStart(2, '0');
    const valor = item[`dia_${diaStr}`] || 0;

    // Construir fecha completa para este día
    const fecha = `${anioActual}-${String(mesActual).padStart(2, '0')}-${diaStr}`;

    // Determinar si es el día actual
    const esDiaHoy = (dia === diaActual && mesActual === mesHoy && anioActual === anioHoy);
    const claseHoy = esDiaHoy ? 'dia-actual' : '';

    html += `<td class="${claseHoy}" style="position:relative;">
              <input type="number" class="form-control text-center input-dia" style="min-width:120px;" value="${valor}" min="0" disabled data-id="${itemId}" data-campo="dia_${diaStr}" />
              <button type="button" class="btn btn-xs btn-warning" style="position:absolute;top:2px;left:2px;padding:1px 4px;font-size:10px;"
                      onclick="habilitarEdicion(this)" title="Editar cantidad">
                <i class="fa fa-pencil"></i>
              </button>`;

    // Agregar botón de evidencias si hay cantidad
    if (parseInt(valor) > 0) {
      html += `<button type="button" class="btn btn-xs btn-info" style="position:absolute;top:2px;right:2px;padding:1px 4px;font-size:10px;"
                      onclick="verEvidenciasDia('${fecha}')" title="Ver evidencias">
                 <i class="fa fa-camera"></i>
               </button>`;
    }

    html += `</td>`;

    if (dia <= 15) {
      cantidad1Q += parseInt(valor);
    } else {
      cantidad2Q += parseInt(valor);
    }
  }

  // Totales
  const precio = parseFloat(item.precio || 0);
  const pagar1Q = cantidad1Q * precio;
  const pagar2Q = cantidad2Q * precio;

  html += `<td class='text-center'><strong>${cantidad1Q}</strong></td>`;
  html += `<td class='text-center'><strong>${cantidad2Q}</strong></td>`;
  html += `<td class='text-center'><strong>$${formatNumber(pagar1Q)}</strong></td>`;
  html += `<td class='text-center'><strong>$${formatNumber(pagar2Q)}</strong></td>`;

  // Acciones
  html += `<td class='text-center'><button class='btn btn-danger btn-xs' onclick='eliminarFila(${itemId})'><i class='fa fa-trash'></i></button></td>`;

  html += "</tr>";
  return html;
}

function agregarFila() {
  // Cargar variedades primero
  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: { consulta: "obtener_variedades" },
    success: function (x) {
      if (x.length) {
        try {
          const variedades = JSON.parse(x);
          agregarFilaConVariedades(variedades);
        } catch (error) {
          console.error("Error al cargar variedades:", error);
          toastr.error("No se pudieron cargar las variedades");
        }
      }
    },
    error: function () {
      toastr.error("Ocurrió un error al cargar las variedades");
    }
  });
}

function agregarFilaConVariedades(variedades) {
  const diasDelMes = getDiasDelMes(mesActual, anioActual);
  const rowId = 'new_' + Date.now();

  let html = `<tr id="${rowId}" class="fila-nueva">`;

  // Columna Descripción con selectpicker
  html += `<td style="min-width:200px;">`;
  html += `<select class="form-control selectpicker-variedad" data-live-search="true" onchange="cambioVariedad(this, '${rowId}')">`;
  html += `<option value="">Seleccionar variedad...</option>`;
  html += `<option value="manual">--- Descripción Manual ---</option>`;
  variedades.forEach(function (v) {
    html += `<option value="${v.id_variedad}" data-precio="${v.precio_produccion || 0}">${v.nombre_variedad}</option>`;
  });
  html += `</select>`;
  html += `<input type="text" class="form-control input-descripcion-manual d-none" placeholder="Escribir descripción..." />`;
  html += `</td>`;

  // Columna Precio
  html += `<td><input type="number" class="form-control text-center input-precio" value="" step="0.01" disabled /></td>`;

  // Columnas de días (vacías inicialmente)
  const hoy = new Date();
  const diaActual = hoy.getDate();
  const mesHoy = hoy.getMonth() + 1;
  const anioHoy = hoy.getFullYear();

  for (let dia = 1; dia <= diasDelMes; dia++) {
    const esDiaHoy = (dia === diaActual && mesActual === mesHoy && anioActual === anioHoy);
    const claseHoy = esDiaHoy ? 'dia-actual' : '';
    html += `<td class="${claseHoy}"><input type="number" class="form-control text-center input-dia" style="min-width:120px;" value="0" min="0" disabled /></td>`;
  }

  // Totales (vacíos)
  html += `<td class='text-center'><strong>0</strong></td>`;
  html += `<td class='text-center'><strong>0</strong></td>`;
  html += `<td class='text-center'><strong>$0</strong></td>`;
  html += `<td class='text-center'><strong>$0</strong></td>`;

  // Botones de acción
  html += `<td class='text-center'>`;
  html += `<button class='btn btn-success btn-xs mr-1' onclick='guardarFilaNueva("${rowId}")' title="Guardar"><i class='fa fa-save'></i></button>`;
  html += `<button class='btn btn-danger btn-xs' onclick='cancelarFilaNueva("${rowId}")' title="Cancelar"><i class='fa fa-times'></i></button>`;
  html += `</td>`;

  html += "</tr>";

  $("#tabla-datos tbody").append(html);

  // Inicializar el selectpicker de la fila nueva
  $(`#${rowId} .selectpicker-variedad`).selectpicker({
    liveSearch: true,
    size: 10
  });
}

function cambioVariedad(select, rowId) {
  const valor = $(select).val();
  const $row = $(`#${rowId}`);

  if (valor === "manual") {
    // Mostrar campo de texto manual y ocultar select
    $(select).addClass("d-none");
    $row.find(".input-descripcion-manual").removeClass("d-none").focus();
    $row.find(".input-precio").prop("disabled", false);
    // Habilitar los inputs de días
    $row.find(".input-dia").prop("disabled", false);
  } else if (valor !== "") {
    // Es una variedad de producto
    const precio = $(select).find("option:selected").data("precio");
    $row.find(".input-precio").val(precio).prop("disabled", false);
    // Habilitar los inputs de días
    $row.find(".input-dia").prop("disabled", false);
  } else {
    // No hay selección
    $row.find(".input-precio").val("").prop("disabled", true);
    $row.find(".input-dia").prop("disabled", true);
  }
}

function guardarFilaNueva(rowId) {
  const $row = $(`#${rowId}`);
  const $select = $row.find(".selectpicker-variedad");
  const valorSelect = $select.val();

  let tipo, idVariedad, descripcionManual, precio;

  if (!valorSelect || valorSelect === "") {
    toastr.error("Debes seleccionar una variedad o ingresar descripción manual");
    return;
  }

  precio = $row.find(".input-precio").val();
  if (!precio || precio === "" || isNaN(precio)) {
    toastr.error("Debes ingresar un precio válido");
    return;
  }

  if (valorSelect === "manual") {
    // Es descripción manual
    tipo = "manual";
    idVariedad = null;
    descripcionManual = $row.find(".input-descripcion-manual").val();
    if (!descripcionManual || descripcionManual.trim() === "") {
      toastr.error("Debes ingresar una descripción");
      return;
    }
  } else {
    // Es variedad de producto
    tipo = "variedad";
    idVariedad = valorSelect;
    descripcionManual = null;
  }

  // Guardar en base de datos
  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "agregar_fila",
      mes: mesActual,
      anio: anioActual,
      id_usuario: usuarioSeleccionado,
      item_tipo: tipo,
      id_variedad: idVariedad,
      descripcion_manual: descripcionManual,
      precio: precio
    },
    success: function (x) {
      if (x.includes("success")) {
        toastr.success("La fila se guardó correctamente");
        cargarDatosProduccion();
      } else {
        toastr.error("No se pudo guardar la fila: " + x);
      }
    },
    error: function () {
      toastr.error("Ocurrió un error al guardar");
    }
  });
}

function cancelarFilaNueva(rowId) {
  $(`#${rowId}`).remove();
}

function guardarCambio(input, itemId, campo) {
  const valor = $(input).val();

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "guardar_cambio",
      id: itemId,
      campo: campo,
      valor: valor
    },
    success: function (x) {
      if (x.includes("success")) {
        // Recargar para actualizar totales
        cargarDatosProduccion();
        toastr.success("Cambio guardado");
      } else {
        toastr.error("No se pudo guardar: " + x);
      }
    },
    error: function () {
      toastr.error("Ocurrió un error al guardar");
    }
  });
}

function eliminarFila(itemId) {
  swal(
    "¿Estás seguro de eliminar esta fila?",
    "Esta acción no se puede deshacer",
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
          url: "data_ver_seguimiento_produccion.php",
          type: "POST",
          data: {
            consulta: "eliminar_fila",
            id: itemId
          },
          success: function (x) {
            if (x.includes("success")) {
              swal("Eliminado", "La fila se eliminó correctamente", "success");
              cargarDatosProduccion();
            } else {
              swal("Error", "No se pudo eliminar: " + x, "error");
            }
          },
          error: function () {
            swal("Error", "Ocurrió un error al eliminar", "error");
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

// ==================== FUNCIONES DE META SEMANAL ====================

function cargarMetaUsuario() {
  if (!usuarioSeleccionado) return;

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_meta_usuario",
      id_usuario: usuarioSeleccionado
    },
    success: function(x) {
      try {
        const meta = JSON.parse(x);
        if (meta.meta_semanal) {
          $("#meta-actual").html(`<span class="label label-info">Meta actual: ${formatNumber(meta.meta_semanal)} plantines/semana</span>`).show();
        } else {
          $("#meta-actual").html(`<span class="label label-default">Sin meta establecida</span>`).show();
        }
        $("#btn-establecer-meta").show();
      } catch (error) {
        console.error("Error al cargar meta:", error);
      }
    }
  });
}

function abrirModalMeta() {
  if (!usuarioSeleccionado) {
    toastr.warning("Selecciona un usuario primero");
    return;
  }

  // Cargar meta actual si existe
  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_meta_usuario",
      id_usuario: usuarioSeleccionado
    },
    success: function(x) {
      try {
        const meta = JSON.parse(x);
        if (meta.meta_semanal) {
          $("#input-meta-semanal").val(meta.meta_semanal);
        } else {
          $("#input-meta-semanal").val(1000); // Default
        }
      } catch (error) {
        $("#input-meta-semanal").val(1000);
      }
    }
  });

  // Establecer fecha actual por defecto
  const hoy = new Date().toISOString().split('T')[0];
  $("#input-fecha-desde-meta").val(hoy);

  // Mostrar modal
  $("#modalEstablecerMeta").show();
}

function cerrarModalMeta() {
  $("#modalEstablecerMeta").hide();
}

function guardarMeta() {
  const metaSemanal = $("#input-meta-semanal").val();
  const fechaDesde = $("#input-fecha-desde-meta").val();

  if (!metaSemanal || metaSemanal <= 0) {
    toastr.error("Debes ingresar una meta válida");
    return;
  }

  if (!fechaDesde) {
    toastr.error("Debes seleccionar una fecha");
    return;
  }

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "establecer_meta",
      id_usuario: usuarioSeleccionado,
      meta_semanal: metaSemanal,
      fecha_desde: fechaDesde
    },
    success: function(x) {
      if (x.includes("success")) {
        toastr.success("Meta establecida correctamente");
        $("#modalEstablecerMeta").hide();
        cargarMetaUsuario();
      } else {
        toastr.error("No se pudo establecer la meta: " + x);
      }
    },
    error: function() {
      toastr.error("Error de conexión");
    }
  });
}

// ==================== FUNCIONES DE EVIDENCIAS ====================

function verEvidenciasDia(fecha) {
  if (!usuarioSeleccionado) {
    toastr.warning("Selecciona un usuario primero");
    return;
  }

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_evidencias_dia",
      id_usuario: usuarioSeleccionado,
      fecha: fecha
    },
    success: function(x) {
      try {
        const registros = JSON.parse(x);
        mostrarModalEvidencias(fecha, registros);
      } catch (error) {
        console.error("Error al cargar evidencias:", error);
        toastr.error("Error al cargar evidencias");
      }
    },
    error: function() {
      toastr.error("Error de conexión");
    }
  });
}

function mostrarModalEvidencias(fecha, registros) {
  const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
  $("#label-fecha-evidencias").text(fechaFormateada).attr('data-fecha', fecha);

  let html = "";

  if (registros.length === 0) {
    html = '<div class="callout callout-info"><p>No hay evidencias registradas para este día.</p></div>';
  } else {
    registros.forEach(function(reg) {
      const turnoIcon = reg.turno === 'mañana' ? 'fa-sun-o text-warning' : 'fa-moon-o text-info';

      // Determinar estilo según estado
      let boxClass = 'box-default';
      let estadoBadge = '';
      if (reg.estado === 'aprobado') {
        boxClass = 'box-success';
        estadoBadge = '<span class="badge bg-green"><i class="fa fa-check"></i> Aprobado</span>';
      } else if (reg.estado === 'rechazado') {
        boxClass = 'box-danger';
        estadoBadge = '<span class="badge bg-red"><i class="fa fa-times"></i> Rechazado</span>';
      } else {
        estadoBadge = '<span class="badge bg-yellow"><i class="fa fa-clock-o"></i> Pendiente</span>';
      }

      html += `<div class="box ${boxClass} mb-3">
                <div class="box-header">
                  <h4 class="box-title">
                    <i class="fa ${turnoIcon}"></i>
                    ${reg.turno} - ${reg.descripcion}
                    <span class="badge bg-blue">${reg.cantidad} plantines</span>
                    ${estadoBadge}
                  </h4>
                </div>
                <div class="box-body">`;

      // Mostrar motivo de rechazo si existe
      if (reg.estado === 'rechazado' && reg.motivo_rechazo) {
        html += `<div class="alert alert-danger">
                   <strong>Motivo del rechazo:</strong> ${reg.motivo_rechazo}
                 </div>`;
      }

      if (reg.evidencias.length > 0) {
        html += '<div class="row">';
        reg.evidencias.forEach(function(ev) {
          html += `<div class="col-md-4 mb-3">
                     <a href="uploads/evidencias/${ev.ruta_imagen}" target="_blank">
                       <img src="uploads/evidencias/${ev.ruta_imagen}" class="img-thumbnail" style="width:100%;height:150px;object-fit:cover;">
                     </a>
                     <div class="text-center mt-1">
                       <small class="text-muted">${ev.tamano_kb} KB</small>
                     </div>
                   </div>`;
        });
        html += '</div>';
      } else {
        html += '<p class="text-muted">Sin evidencias fotográficas</p>';
      }

      // Botones de acción
      html += `<div class="mt-3">`;
      if (reg.estado === 'pendiente' || reg.estado === 'rechazado') {
        html += `<button class="btn btn-success btn-sm" onclick="aprobarRegistro(${reg.id})">
                   <i class="fa fa-check"></i> Aprobar
                 </button> `;
      }
      if (reg.estado === 'pendiente' || reg.estado === 'aprobado') {
        html += `<button class="btn btn-danger btn-sm" onclick="rechazarRegistro(${reg.id})">
                   <i class="fa fa-times"></i> Rechazar
                 </button>`;
      }
      html += `</div>`;

      html += `</div></div>`;
    });
  }

  $("#contenedor-evidencias-dia").html(html);
  $("#modalVerEvidencias").show();
}

function cerrarModalEvidencias() {
  $("#modalVerEvidencias").hide();
}

// ==================== FUNCIONES DE VALIDACIÓN ====================

function aprobarRegistro(id_registro) {
  swal({
    title: "¿Aprobar registro?",
    text: "Esta cantidad será contabilizada en los totales y pagos",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#28a745",
    confirmButtonText: "Sí, aprobar",
    cancelButtonText: "Cancelar",
    closeOnConfirm: false
  }).then(function(isConfirm) {
    switch (isConfirm) {
      case true:
        $.ajax({
          url: "data_ver_seguimiento_produccion.php",
          type: "POST",
          data: {
            consulta: "aprobar_registro",
            id_registro: id_registro
          },
          success: function(x) {
            try {
              const response = JSON.parse(x);
              if (response.success) {
                swal({
                  title: "¡Aprobado!",
                  text: "El registro ha sido aprobado correctamente",
                  icon: "success",
                  timer: 2000,
                  showConfirmButton: false
                });
                // Recargar evidencias y tabla
                const fecha = $("#label-fecha-evidencias").attr('data-fecha');
                verEvidenciasDia(fecha);
                cargarDatosProduccion();
              } else {
                swal("Error", response.error || "No se pudo aprobar el registro", "error");
              }
            } catch (error) {
              swal("Error", "Error al procesar la respuesta", "error");
              console.error(error)
            }
          },
          error: function() {
            swal("Error", "Error en la conexión", "error");
          }
        });
        break;
      case false:
        break;
    }
  });
}

function rechazarRegistro(id_registro) {
  swal({
    title: "¿Rechazar registro?",
    text: "Esta cantidad NO será contabilizada en los totales ni pagos",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    confirmButtonText: "Sí, rechazar",
    cancelButtonText: "Cancelar",
    closeOnConfirm: false
  }).then(function(isConfirm) {
    switch (isConfirm) {
      case true:
        // Pedir motivo del rechazo (opcional)
        const motivo = prompt("Motivo del rechazo (opcional):\nEj: Cantidad exagerada, sin evidencias suficientes...");

        // Si presiona cancelar en el prompt, cancelamos todo
        if (motivo === null) {
          swal.close();
          return;
        }

        $.ajax({
          url: "data_ver_seguimiento_produccion.php",
          type: "POST",
          data: {
            consulta: "rechazar_registro",
            id_registro: id_registro,
            motivo: motivo || ""
          },
          success: function(x) {
            try {
              const response = JSON.parse(x);
              if (response.success) {
                swal({
                  title: "¡Rechazado!",
                  text: "El registro ha sido rechazado correctamente",
                  icon: "success",
                  timer: 2000,
                  showConfirmButton: false
                });
                // Recargar evidencias y tabla
                const fecha = $("#label-fecha-evidencias").attr('data-fecha');
                verEvidenciasDia(fecha);
                cargarDatosProduccion();
              } else {
                swal("Error", response.error || "No se pudo rechazar el registro", "error");
              }
            } catch (error) {
              swal("Error", "Error al procesar la respuesta", "error");
              console.error(error)
            }
          },
          error: function() {
            swal("Error", "Error en la conexión", "error");
          }
        });
        break;
      case false:
        break;
    }
  });
}

// ==================== FUNCIONES DE EDICIÓN ====================

function habilitarEdicion(btn) {
  const $btn = $(btn);
  const $td = $btn.parent();
  const $input = $td.find('input.input-dia');

  // Habilitar input
  $input.prop('disabled', false);
  $input.focus();
  $input.select();

  // Cambiar botón a guardar
  $btn.removeClass('btn-warning').addClass('btn-success');
  $btn.html('<i class="fa fa-save"></i>');
  $btn.attr('title', 'Guardar cambio');
  $btn.attr('onclick', 'guardarEdicion(this)');
}

function guardarEdicion(btn) {
  const $btn = $(btn);
  const $td = $btn.parent();
  const $input = $td.find('input.input-dia');

  const id = $input.data('id');
  const campo = $input.data('campo');

  // Llamar a guardarCambio
  guardarCambio($input[0], id, campo);

  // Deshabilitar input
  $input.prop('disabled', true);

  // Cambiar botón de vuelta a editar
  $btn.removeClass('btn-success').addClass('btn-warning');
  $btn.html('<i class="fa fa-pencil"></i>');
  $btn.attr('title', 'Editar cantidad');
  $btn.attr('onclick', 'habilitarEdicion(this)');
}

// ==================== FUNCIONES DE COMENTARIOS ====================

function cargarComentarios() {
  if (!usuarioSeleccionado) return;

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_comentarios",
      mes: mesActual,
      anio: anioActual,
      id_usuario: usuarioSeleccionado
    },
    success: function(x) {
      try {
        const response = JSON.parse(x);
        $("#textarea-comentarios").val(response.comentarios || "");
      } catch (error) {
        console.error("Error al cargar comentarios:", error);
      }
    },
    error: function() {
      console.error("Error al cargar comentarios");
    }
  });
}

function guardarComentarios() {
  if (!usuarioSeleccionado) {
    toastr.warning("Selecciona un usuario primero");
    return;
  }

  const comentarios = $("#textarea-comentarios").val();

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "guardar_comentarios",
      mes: mesActual,
      anio: anioActual,
      id_usuario: usuarioSeleccionado,
      comentarios: comentarios
    },
    success: function(x) {
      if (x.includes("success")) {
        toastr.success("Comentarios guardados");
      } else {
        toastr.error("Error al guardar: " + x);
      }
    },
    error: function() {
      toastr.error("Error de conexión");
    }
  });
}

// ==================== FUNCIONES DE PAGOS ====================

function cargarPagos() {
  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "obtener_pagos",
      mes: mesActual,
      anio: anioActual,
      id_usuario: usuarioSeleccionado
    },
    success: function (x) {
      if (x.length) {
        try {
          const pagos = JSON.parse(x);
          renderizarPagos(pagos);
        } catch (error) {
          console.error("Error al cargar pagos:", error);
          renderizarPagos([]);
        }
      } else {
        renderizarPagos([]);
      }
    },
    error: function () {
      console.error("Error al cargar pagos");
      renderizarPagos([]);
    }
  });
}

function renderizarPagos(pagos) {
  // Mostrar la sección de pagos
  $("#seccion-pagos").show();

  // Calcular total pagado
  let totalPagado = 0;
  pagos.forEach(function(pago) {
    totalPagado += parseFloat(pago.monto);
  });

  // Actualizar labels
  $("#label-total-pagar").text("$" + formatNumber(totalMensual));
  $("#label-total-pagado").text("$" + formatNumber(totalPagado));

  // Determinar estado
  const ultimoDiaMes = new Date(anioActual, mesActual, 0).getDate();
  const ultimaFechaMes = new Date(anioActual, mesActual - 1, ultimoDiaMes);
  const fechaHoy = new Date();
  fechaHoy.setHours(0, 0, 0, 0);

  let estado = "PENDIENTE";
  let colorEstado = "bg-yellow";
  let iconoEstado = "fa-clock-o";

  if (totalPagado >= totalMensual && fechaHoy >= ultimaFechaMes) {
    estado = "PAGADO";
    colorEstado = "bg-green";
    iconoEstado = "fa-check-circle";
  } else if (totalPagado > 0) {
    estado = "PARCIAL";
    colorEstado = "bg-blue";
    iconoEstado = "fa-pie-chart";
  }

  $("#label-estado").text(estado);
  $("#info-estado").removeClass("bg-yellow bg-green bg-blue").addClass(colorEstado);
  $("#info-estado .fa").removeClass("fa-clock-o fa-check-circle fa-pie-chart").addClass(iconoEstado);

  // Renderizar tabla de pagos
  let html = "";
  if (pagos.length > 0) {
    html = "<table class='table table-bordered'>";
    html += "<thead>";
    html += "<tr>";
    html += "<th>Fecha</th>";
    html += "<th>Monto</th>";
    html += "<th>Observaciones</th>";
    html += "<th style='width:80px;'>Acciones</th>";
    html += "</tr>";
    html += "</thead>";
    html += "<tbody>";

    pagos.forEach(function(pago) {
      const fechaFormateada = moment(pago.fecha_pago).format('DD/MM/YYYY');
      html += "<tr>";
      html += `<td>${fechaFormateada}</td>`;
      html += `<td><strong>$${formatNumber(pago.monto)}</strong></td>`;
      html += `<td>${pago.observaciones || '-'}</td>`;
      html += `<td class='text-center'>`;
      html += `<button class='btn btn-danger btn-xs' onclick='eliminarPago(${pago.id})' title='Eliminar'><i class='fa fa-trash'></i></button>`;
      html += `</td>`;
      html += "</tr>";
    });

    html += "</tbody>";
    html += "</table>";
  } else {
    html = "<div class='callout callout-info'><p>No hay pagos registrados para este mes.</p></div>";
  }

  $("#tabla-pagos").html(html);
}

function abrirModalPago() {
  // Limpiar formulario
  $("#input-monto-pago").val("");
  $("#input-observaciones-pago").val("");

  // Establecer fecha actual por defecto
  const hoy = new Date();
  const fechaStr = hoy.toISOString().split('T')[0];
  $("#input-fecha-pago").val(fechaStr);

  // Abrir modal
  $("#modalRegistrarPago").show();
}

function cerrarModalPago() {
  $("#modalRegistrarPago").hide();
}

function guardarPago() {
  const monto = $("#input-monto-pago").val();
  const fecha = $("#input-fecha-pago").val();
  const observaciones = $("#input-observaciones-pago").val();

  // Validaciones
  if (!monto || monto === "" || isNaN(monto) || parseFloat(monto) <= 0) {
    toastr.error("Debes ingresar un monto válido");
    return;
  }

  if (!fecha || fecha === "") {
    toastr.error("Debes seleccionar una fecha");
    return;
  }

  $.ajax({
    url: "data_ver_seguimiento_produccion.php",
    type: "POST",
    data: {
      consulta: "registrar_pago",
      mes: mesActual,
      anio: anioActual,
      id_usuario: usuarioSeleccionado,
      monto: monto,
      fecha_pago: fecha,
      observaciones: observaciones
    },
    success: function (x) {
      if (x.includes("success")) {
        toastr.success("Pago registrado correctamente");
        $("#modalRegistrarPago").hide();
        cargarPagos();
      } else {
        toastr.error("No se pudo registrar el pago: " + x);
      }
    },
    error: function () {
      toastr.error("Ocurrió un error al registrar el pago");
    }
  });
}

function eliminarPago(idPago) {
  swal(
    "¿Estás seguro de eliminar este pago?",
    "Esta acción no se puede deshacer",
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
          url: "data_ver_seguimiento_produccion.php",
          type: "POST",
          data: {
            consulta: "eliminar_pago",
            id: idPago
          },
          success: function (x) {
            if (x.includes("success")) {
              swal("Eliminado", "El pago se eliminó correctamente", "success");
              cargarPagos();
            } else {
              swal("Error", "No se pudo eliminar el pago: " + x, "error");
            }
          },
          error: function () {
            swal("Error", "Ocurrió un error al eliminar", "error");
          }
        });
        break;

      default:
        break;
    }
  });
}

// ==================== FUNCIÓN REFRESCAR ====================

function refrescarDatos() {
  if (!usuarioSeleccionado) {
    toastr.warning("Selecciona un usuario primero");
    return;
  }

  // Mostrar animación en el botón
  const $btnRefrescar = $("#btn-refrescar");
  const textoOriginal = $btnRefrescar.html();
  $btnRefrescar.prop('disabled', true);
  $btnRefrescar.html('<i class="fa fa-refresh fa-spin"></i> Actualizando...');

  // Recargar todos los datos
  cargarDatosProduccion();
  cargarMetaUsuario();
  cargarComentarios();
  cargarPagos();

  // Restaurar botón después de 1 segundo
  setTimeout(function() {
    $btnRefrescar.prop('disabled', false);
    $btnRefrescar.html(textoOriginal);
    toastr.success("Datos actualizados correctamente");
  }, 1000);
}

// ==================== FUNCIÓN AUTO-SCROLL ====================

function scrollToDiaActual() {
  // Buscar la columna del día actual
  const $diaActual = $('th.dia-actual').first();

  if ($diaActual.length === 0) {
    return; // No hay día actual en este mes
  }

  // Obtener el contenedor con scroll
  const $contenedor = $('.box-body').first();

  if ($contenedor.length === 0) {
    return;
  }

  // Calcular la posición de scroll para centrar el día actual
  const offsetColumna = $diaActual.position().left;
  const anchoColumna = $diaActual.outerWidth();
  const anchoContenedor = $contenedor.width();
  const scrollActual = $contenedor.scrollLeft();

  // Calcular scroll para centrar la columna (o mostrarla claramente)
  const scrollTarget = scrollActual + offsetColumna - (anchoContenedor / 2) + (anchoColumna / 2);

  // Hacer scroll suave
  $contenedor.animate({
    scrollLeft: scrollTarget
  }, 800, 'swing');
}
