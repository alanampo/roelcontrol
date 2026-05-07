// Toastr config
toastr.options = {
  closeButton: true,
  progressBar: true,
  positionClass: "toast-top-right",
  timeOut: 3000
};

// Global state
let imagenesSeleccionadas = [];
let registroActual = null;
let mesActual = new Date().getMonth() + 1;
let anioActual = new Date().getFullYear();
let pedidoSeleccionado = null;
let pedidosDisponibles = [];

$(document).ready(function () {
  inicializarSelectorMes();
  cargarPedidosDisponibles();
  cargarEstadisticas(mesActual, anioActual);
  cargarHistorial(mesActual, anioActual);

  const hoy = new Date().toISOString().split('T')[0];
  $("#input-fecha").val(hoy);

  $("#btn-registrar").on("click", guardarRegistro);
  $("#input-imagenes").on("change", manejarSeleccionImagenes);
  $("#btn-seleccionar-imagenes").on("click", function () {
    $("#input-imagenes").click();
  });

  $("#btn-mes-anterior").on("click", irMesAnterior);
  $("#btn-mes-siguiente").on("click", irMesSiguiente);
  $("#btn-mes-hoy").on("click", irMesActual);
  $("#input-mes-actual").on("change", cambioMesManual);
});

// ==================== NAVEGACIÓN DE MESES ====================

function inicializarSelectorMes() {
  const mesStr = String(mesActual).padStart(2, '0');
  $("#input-mes-actual").val(`${anioActual}-${mesStr}`);
}

function irMesAnterior() {
  let mes = mesActual - 1, anio = anioActual;
  if (mes < 1) { mes = 12; anio--; }
  cambiarMes(mes, anio);
}

function irMesSiguiente() {
  let mes = mesActual + 1, anio = anioActual;
  if (mes > 12) { mes = 1; anio++; }
  cambiarMes(mes, anio);
}

function irMesActual() {
  const hoy = new Date();
  cambiarMes(hoy.getMonth() + 1, hoy.getFullYear());
}

function cambioMesManual() {
  const valor = $("#input-mes-actual").val();
  if (!valor) return;
  const [anio, mes] = valor.split('-');
  cambiarMes(parseInt(mes), parseInt(anio));
}

function cambiarMes(mes, anio) {
  mesActual = mes;
  anioActual = anio;
  const mesStr = String(mes).padStart(2, '0');
  $("#input-mes-actual").val(`${anio}-${mesStr}`);
  cargarEstadisticas(mes, anio);
  cargarHistorial(mes, anio);
}

// ==================== PEDIDOS DISPONIBLES ====================

function cargarPedidosDisponibles() {
  $("#tabla-pedidos-disponibles").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Cargando pedidos...</p></div>');

  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_pedidos_disponibles" },
    success: function (x) {
      try {
        pedidosDisponibles = JSON.parse(x);
        renderizarPedidosDisponibles(pedidosDisponibles);
      } catch (e) {
        console.error("Error al cargar pedidos:", e);
        $("#tabla-pedidos-disponibles").html('<div class="callout callout-danger"><p>Error al cargar pedidos</p></div>');
      }
    },
    error: function () {
      $("#tabla-pedidos-disponibles").html('<div class="callout callout-danger"><p>Error de conexión</p></div>');
    }
  });
}

function renderizarPedidosDisponibles(pedidos) {
  if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tabla-pedidos-dt')) {
    $('#tabla-pedidos-dt').DataTable().destroy();
  }

  if (pedidos.length === 0) {
    $("#tabla-pedidos-disponibles").html(
      '<div class="callout callout-info"><p>No hay pedidos de Esquejes disponibles en Etapa 0 o 1 en este momento.</p></div>'
    );
    return;
  }

  const etapaLabels = { '0': 'Etapa 0 - INICIO', '1': 'Etapa 1 -  10%' };
  const etapaClasses = { '0': 'bg-red', '1': 'bg-yellow' };

  let html = '<div class="table-responsive">';
  html += '<table id="tabla-pedidos-dt" class="table table-bordered table-hover" style="margin-bottom:0;">';
  html += '<thead class="bg-light"><tr>';
  html += '<th>Código</th><th>Variedad</th><th>Cliente</th><th class="text-center">Etapa</th>';
  html += '<th class="text-center">Total Plantas</th><th class="text-center">Progreso</th>';
  html += '<th class="text-center">Restante</th><th class="text-center" style="width:120px;">Acción</th>';
  html += '</tr></thead><tbody>';

  pedidos.forEach(function (p) {
    const totalTrabajado = parseInt(p.total_trabajado);
    const cantTotal = parseInt(p.cant_plantas);
    const restante = cantTotal - totalTrabajado;
    const porcentaje = cantTotal > 0 ? Math.min(100, Math.round((totalTrabajado / cantTotal) * 100)) : 0;
    const etapaKey = String(p.estado);
    const etapaLabel = etapaLabels[etapaKey] || `Etapa ${p.estado}`;
    const etapaClass = etapaClasses[etapaKey] || 'bg-gray';
    const bandejas = p.cant_bandejas ? `(${p.cant_bandejas} band. de ${p.tipo_bandeja || '-'})` : '';
    const completado = restante <= 0;
    const esSeleccionado = pedidoSeleccionado && pedidoSeleccionado.id_artpedido == p.id_artpedido;

    const idEspecie = p.id_especie ? '-' + String(p.id_especie).padStart(2, '0') : '';
    const idInterno = String(p.id_variedad_interno).padStart(2, '0');
    const idCliente = String(p.id_cliente).padStart(2, '0');
    const codigoCompleto = `${p.iniciales || ''}${p.id_pedido_interno}/M${p.mes_dia}/${p.tipo_codigo}${idInterno}${idEspecie}/${p.cant_plantas}/${idCliente}`;

    html += `<tr ${esSeleccionado ? 'class="active"' : ''}>
      <td><small class="text-monospace">${codigoCompleto}</small></td>
      <td>
        <strong>${p.nombre_variedad}</strong>
        ${bandejas ? `<br><small class="text-muted">${bandejas}</small>` : ''}
      </td>
      <td>${p.nombre_cliente}</td>
      <td class="text-center"><span class="badge ${etapaClass}">${etapaLabel}</span></td>
      <td class="text-center">${formatNumber(cantTotal)}</td>
      <td class="text-center" style="min-width:120px;">
        <div class="progress" style="margin-bottom:4px;height:14px;">
          <div class="progress-bar progress-bar-${completado ? 'success' : (porcentaje >= 75 ? 'warning' : 'danger')}" role="progressbar" style="width:${porcentaje}%;line-height:14px;font-size:11px;">${porcentaje > 15 ? porcentaje + '%' : ''}</div>
        </div>
        <small class="text-muted">${formatNumber(totalTrabajado)} de ${formatNumber(cantTotal)}</small>
      </td>
      <td class="text-center">
        ${completado
          ? '<span class="label label-success"><i class="fa fa-check"></i> Completo</span>'
          : `<strong class="text-success">${formatNumber(restante)}</strong>`}
      </td>
      <td class="text-center">
        ${completado
          ? '<span class="text-muted"><i class="fa fa-lock"></i></span>'
          : `<button class="btn btn-success btn-sm" onclick="seleccionarPedido(${p.id_artpedido})"><i class="fa fa-play"></i> Trabajar</button>`}
      </td>
    </tr>`;
  });

  html += '</tbody></table></div>';
  $("#tabla-pedidos-disponibles").html(html);

  $('#tabla-pedidos-dt').DataTable({
    pageLength: 15,
    order: [[3, 'asc'], [0, 'asc']],
    columnDefs: [
      { orderable: false, targets: [5, 7] }
    ],
    language: {
      search: "Buscar:",
      lengthMenu: "Mostrar _MENU_ pedidos",
      info: "Mostrando _START_ a _END_ de _TOTAL_ pedidos",
      infoEmpty: "Sin pedidos",
      infoFiltered: "(filtrado de _MAX_ total)",
      loadingRecords: "Cargando...",
      processing: "Procesando...",
      zeroRecords: "No se encontraron pedidos",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior"
      }
    }
  });
}

function seleccionarPedido(idArtpedido) {
  pedidoSeleccionado = pedidosDisponibles.find(function (p) { return p.id_artpedido == idArtpedido; });
  if (!pedidoSeleccionado) return;

  const totalTrabajado = parseInt(pedidoSeleccionado.total_trabajado);
  const restante = parseInt(pedidoSeleccionado.cant_plantas) - totalTrabajado;
  const etapaLabels = { '0': 'Etapa 0 - INICIO', '1': 'Etapa 1 - 10%' };
  const etapaLabel = etapaLabels[String(pedidoSeleccionado.estado)] || `Etapa ${pedidoSeleccionado.estado}`;
  const p = pedidoSeleccionado;
  const idEspecieSel = p.id_especie ? '-' + String(p.id_especie).padStart(2, '0') : '';
  const codigoSel = `${p.iniciales || ''}${p.id_pedido_interno}/M${p.mes_dia}/${p.tipo_codigo}${String(p.id_variedad_interno).padStart(2, '0')}${idEspecieSel}/${p.cant_plantas}/${String(p.id_cliente).padStart(2, '0')}`;

  $("#info-pedido-seleccionado").html(
    `<small class="text-monospace pull-right text-muted">${codigoSel}</small>
     <i class="fa fa-cube"></i> <strong>${pedidoSeleccionado.nombre_variedad}</strong>
     &nbsp;&middot;&nbsp; <i class="fa fa-user"></i> ${pedidoSeleccionado.nombre_cliente}
     &nbsp;&middot;&nbsp; <span class="badge bg-yellow">${etapaLabel}</span>
     &nbsp;&middot;&nbsp; Total: <strong>${formatNumber(pedidoSeleccionado.cant_plantas)}</strong> plantas
     &nbsp;&middot;&nbsp; Trabajado: <strong>${formatNumber(totalTrabajado)}</strong>
     &nbsp;&middot;&nbsp; <span class="text-success">Restante: <strong>${formatNumber(restante)}</strong></span>`
  );

  $("#input-cantidad").attr("max", restante);
  $("#label-restante").text("Máximo: " + formatNumber(restante) + " plantines");
  limpiarFormulario();

  const hoy = new Date().toISOString().split('T')[0];
  $("#input-fecha").val(hoy);

  $("#panel-registro").show();
  $("html, body").animate({ scrollTop: $("#panel-registro").offset().top - 100 }, 400);
  renderizarPedidosDisponibles(pedidosDisponibles);
}

function cancelarSeleccionPedido() {
  pedidoSeleccionado = null;
  $("#panel-registro").hide();
  limpiarFormulario();
  renderizarPedidosDisponibles(pedidosDisponibles);
}

// ==================== CARGA DE ESTADÍSTICAS E HISTORIAL ====================

function cargarEstadisticas(mes, anio) {
  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_estadisticas", mes: mes, anio: anio },
    success: function (x) {
      try { renderizarEstadisticas(JSON.parse(x)); }
      catch (e) { console.error("Error estadísticas:", e); }
    }
  });
}

function renderizarEstadisticas(stats) {
  if (stats.es_mes_actual) {
    $("#stats-mes-actual").show();
    $("#stats-mes-historico").hide();
    $("#progreso-semanal-container").show();
    $("#stat-diaria").text(formatNumber(stats.produccion_diaria));
    $("#stat-semanal").text(formatNumber(stats.produccion_semanal));
    $("#stat-mensual").text(formatNumber(stats.produccion_mensual));
    $("#stat-meta").text(formatNumber(stats.meta_semanal));
    $("#stat-progreso").text(stats.progreso_semanal + "%");
    $("#stat-bono").text("$" + formatNumber(stats.bono_estimado));

    const pb = $("#progress-bar-semanal");
    pb.css("width", stats.progreso_semanal + "%").attr("aria-valuenow", stats.progreso_semanal);
    pb.removeClass("bg-success bg-warning bg-danger");
    if (stats.progreso_semanal >= 100) pb.addClass("bg-success");
    else if (stats.progreso_semanal >= 75) pb.addClass("bg-warning");
    else pb.addClass("bg-danger");

    const ind = $("#indicador-cumplimiento");
    ind.removeClass("bg-green bg-yellow bg-red");
    if (stats.indicador === "green") { ind.addClass("bg-green"); ind.html('<i class="fa fa-check-circle"></i> Cumpliendo'); }
    else if (stats.indicador === "yellow") { ind.addClass("bg-yellow"); ind.html('<i class="fa fa-exclamation-triangle"></i> En Progreso'); }
    else { ind.addClass("bg-red"); ind.html('<i class="fa fa-times-circle"></i> Bajo Meta'); }
  } else {
    $("#stats-mes-actual").hide();
    $("#stats-mes-historico").show();
    $("#progreso-semanal-container").hide();
    $("#label-mes-historico").text(stats.nombre_mes + " " + stats.anio);
    $("#stat-mes-historico").text(formatNumber(stats.produccion_mensual));
    $("#stat-bono-historico").text("$" + formatNumber(stats.bono_estimado));
  }
}

function cargarHistorial(mes, anio) {
  const primerDia = new Date(anio, mes - 1, 1).toISOString().split('T')[0];
  const ultimoDia = new Date(anio, mes, 0).toISOString().split('T')[0];

  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_mi_produccion", fecha_desde: primerDia, fecha_hasta: ultimoDia },
    success: function (x) {
      try { renderizarHistorial(JSON.parse(x)); }
      catch (e) { console.error("Error historial:", e); renderizarHistorial([]); }
    },
    error: function () { renderizarHistorial([]); }
  });
}

function renderizarHistorial(registros) {
  let html = "";

  if (registros.length === 0) {
    html = '<div class="callout callout-info"><p>No hay registros de producción para este mes.</p></div>';
    $("#tabla-historial").html(html);
    return;
  }

  html = `<div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="bg-light">
        <tr>
          <th>Fecha</th>
          <th>Turno</th>
          <th>Variedad / Descripción</th>
          <th>Pedido / Cliente</th>
          <th class="text-center">Cantidad</th>
          <th>Ubicación</th>
          <th class="text-center">Evidencias</th>
          <th>Estado</th>
          <th style="width:80px;">Acciones</th>
        </tr>
      </thead>
      <tbody>`;

  registros.forEach(function (reg) {
    const fechaFormateada = moment(reg.fecha).format('DD/MM/YYYY');
    const turnoIcon = reg.turno === 'mañana' ? 'fa-sun-o' : 'fa-moon-o';
    const turnoColor = reg.turno === 'mañana' ? 'text-warning' : 'text-info';
    const ubicacion = reg.ubicacion_lote || '-';
    const descripcionItem = reg.item_tipo === 'variedad' ? reg.variedad_nombre : reg.descripcion_manual;

    let estadoIcon = '', rowClass = '';
    if (reg.estado === 'aprobado') {
      estadoIcon = '<i class="fa fa-check-circle text-success"></i> Aprobado';
    } else if (reg.estado === 'rechazado') {
      estadoIcon = '<i class="fa fa-times-circle text-danger"></i> Rechazado';
      rowClass = 'danger';
    } else {
      estadoIcon = '<i class="fa fa-clock-o text-warning"></i> Pendiente';
    }

    // Pedido info column
    let pedidoInfo = '<span class="text-muted">-</span>';
    if (reg.id_artpedido) {
      const etapaLabels = { '0': 'ETAPA 0', '1': 'ETAPA 1' };
      const etapaLabel = etapaLabels[String(reg.etapa_pedido)] || `E${reg.etapa_pedido}`;
      
      let codigoPedido = '';
      if (reg.id_pedido_interno) {
        const idEspecie = reg.id_especie ? '-' + String(reg.id_especie).padStart(2, '0') : '';
        const idInterno = String(reg.id_variedad_interno).padStart(2, '0');
        const idCliente = String(reg.id_cliente).padStart(2, '0');
        codigoPedido = `${reg.iniciales || ''}${reg.id_pedido_interno}/M${reg.mes_dia}/${reg.tipo_codigo}${idInterno}${idEspecie}/${reg.cantidad_plantines}/${idCliente}`;
        pedidoInfo = `<small class="text-monospace text-primary">${codigoPedido}</small><br><span class="label label-default">${etapaLabel}</span> ${reg.pedido_cliente || ''}`;
      } else {
        pedidoInfo = `<span class="label label-default">${etapaLabel}</span> ${reg.pedido_cliente || ''}`;
      }
    }

    html += `<tr class="${rowClass}">
      <td>${fechaFormateada}</td>
      <td><i class="fa ${turnoIcon} ${turnoColor}"></i> ${reg.turno}</td>
      <td>${descripcionItem}</td>
      <td>${pedidoInfo}</td>
      <td class="text-center"><strong>${formatNumber(reg.cantidad_plantines)}</strong></td>
      <td>${ubicacion}</td>
      <td class="text-center">`;

    if (reg.num_evidencias > 0) {
      html += `<button class="btn btn-xs btn-primary" onclick="verEvidencias(${reg.id})" title="Ver ${reg.num_evidencias} foto(s)">
                 <i class="fa fa-camera"></i> ${reg.num_evidencias}
               </button>`;
    } else {
      html += '<span class="text-muted">-</span>';
    }

    html += `</td><td>${estadoIcon}`;

    if (reg.estado === 'rechazado' && reg.motivo_rechazo) {
      html += `<br><small class="text-danger"><strong>Motivo:</strong> ${reg.motivo_rechazo}</small>`;
    }

    html += `</td><td class="text-center">`;

    if (reg.estado === 'pendiente' || reg.estado === 'rechazado') {
      html += `<button class="btn btn-xs btn-danger" onclick="eliminarRegistro(${reg.id})" title="Eliminar">
                 <i class="fa fa-trash"></i>
               </button>`;
    } else if (reg.estado === 'aprobado') {
      html += '<span class="text-muted"><i class="fa fa-lock"></i></span>';
    }

    html += `</td></tr>`;
  });

  html += `</tbody></table></div>`;
  $("#tabla-historial").html(html);
}

// ==================== IMÁGENES ====================

function manejarSeleccionImagenes(event) {
  const files = event.target.files;
  if (files.length === 0) return;
  if (files.length > 5) { toastr.warning("Máximo 5 imágenes por registro"); return; }

  $("#preview-imagenes").html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Comprimiendo imágenes...</p></div>').show();

  ImageCompressor.compressMultiple(files, function () {}).then(function (results) {
    imagenesSeleccionadas = results;
    mostrarPreviewImagenes();
  }).catch(function (e) {
    console.error(e);
    toastr.error("Error al procesar las imágenes");
    $("#preview-imagenes").hide();
  });
}

function mostrarPreviewImagenes() {
  let html = '<div class="row">';
  imagenesSeleccionadas.forEach(function (img, index) {
    html += `<div class="col-md-3 mb-3">
               <div class="imagen-preview-container">
                 <img src="${URL.createObjectURL(img.file)}" class="imagen-preview img-thumbnail" alt="Preview">
                 <button type="button" class="btn btn-danger btn-xs btn-eliminar-preview" onclick="eliminarImagenPreview(${index})">
                   <i class="fa fa-times"></i>
                 </button>
                 <div class="text-center mt-1"><small class="text-muted">${img.compressed.sizeKB} KB</small></div>
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
  if (!pedidoSeleccionado) {
    toastr.error("Debes seleccionar un pedido primero");
    return;
  }

  const fecha = $("#input-fecha").val();
  const turno = $("#select-turno").val();
  const cantidad = parseInt($("#input-cantidad").val());

  if (!fecha) { toastr.error("Debes seleccionar una fecha"); return; }
  if (!turno) { toastr.error("Debes seleccionar un turno"); return; }
  if (!cantidad || cantidad <= 0) { toastr.error("Debes ingresar una cantidad válida"); return; }

  const totalTrabajado = parseInt(pedidoSeleccionado.total_trabajado);
  const restante = parseInt(pedidoSeleccionado.cant_plantas) - totalTrabajado;
  if (cantidad > restante) {
    toastr.error(`La cantidad no puede superar el restante (${formatNumber(restante)} plantines)`);
    return;
  }

  $("#btn-registrar").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: {
      consulta: "guardar_registro",
      fecha: fecha,
      turno: turno,
      id_artpedido: pedidoSeleccionado.id_artpedido,
      cantidad_plantines: cantidad,
      ubicacion_lote: $("#input-ubicacion").val(),
      observaciones: $("#input-observaciones").val()
    },
    success: function (x) {
      try {
        const response = JSON.parse(x);
        if (response.success) {
          if (response.pedido_completado) {
            toastr.success("¡Pedido completado! Avanzó a la siguiente etapa automáticamente.", "Producción Completa", { timeOut: 5000 });
          } else {
            toastr.success("Registro guardado correctamente");
          }
          registroActual = response.id_registro;
          if (imagenesSeleccionadas.length > 0) {
            subirEvidencias(response.id_registro);
          } else {
            finalizarGuardado();
          }
        } else {
          toastr.error(response.error || "Error al guardar el registro");
          $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar Avance');
        }
      } catch (e) {
        console.error(e);
        toastr.error("Error al procesar la respuesta");
        $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar Avance');
      }
    },
    error: function () {
      toastr.error("Error de conexión");
      $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar Avance');
    }
  });
}

function subirEvidencias(idRegistro) {
  const total = imagenesSeleccionadas.length;
  let subidas = 0;
  toastr.info(`Subiendo ${total} imagen(es)...`, '', { timeOut: 0 });

  imagenesSeleccionadas.forEach(function (img) {
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
      success: function (x) {
        try {
          if (JSON.parse(x).success) {
            subidas++;
            if (subidas === total) {
              toastr.clear();
              toastr.success(`${total} evidencia(s) subida(s)`);
              finalizarGuardado();
            }
          }
        } catch (e) { console.error(e); }
      },
      error: function () { console.error("Error al subir imagen"); }
    });
  });
}

function finalizarGuardado() {
  cancelarSeleccionPedido();
  cargarPedidosDisponibles();
  cargarEstadisticas(mesActual, anioActual);
  cargarHistorial(mesActual, anioActual);
  $("#btn-registrar").prop("disabled", false).html('<i class="fa fa-save"></i> Registrar Avance');
}

function limpiarFormulario() {
  $("#input-cantidad").val('');
  $("#input-ubicacion").val('');
  $("#input-observaciones").val('');
  $("#select-turno").val('');
  $("#input-imagenes").val('');
  imagenesSeleccionadas = [];
  $("#preview-imagenes").hide();
  registroActual = null;
}

// ==================== VER EVIDENCIAS ====================

function verEvidencias(idRegistro) {
  $.ajax({
    url: "data_mi_produccion.php",
    type: "POST",
    data: { consulta: "obtener_mi_produccion" },
    success: function (x) {
      try {
        const registros = JSON.parse(x);
        const registro = registros.find(function (r) { return r.id == idRegistro; });
        if (registro && registro.evidencias.length > 0) {
          let html = '<div class="row">';
          registro.evidencias.forEach(function (ev) {
            html += `<div class="col-md-4 mb-3">
                       <a href="uploads/evidencias/${ev.ruta_imagen}" target="_blank">
                         <img src="uploads/evidencias/${ev.ruta_imagen}" class="img-thumbnail" style="width:100%;">
                       </a>
                       <div class="text-center mt-1"><small class="text-muted">${ev.tamano_kb} KB</small></div>
                     </div>`;
          });
          html += '</div>';
          swal({ title: "Evidencias Fotográficas", content: { element: "div", attributes: { innerHTML: html } }, button: "Cerrar" });
        }
      } catch (e) { console.error(e); }
    }
  });
}

// ==================== ELIMINAR REGISTRO ====================

function eliminarRegistro(idRegistro) {
  swal("¿Estás seguro de eliminar este registro?", "Esta acción no se puede deshacer", {
    icon: "warning",
    buttons: { cancel: "Cancelar", catch: { text: "SÍ, ELIMINAR", value: "catch" } }
  }).then(function (value) {
    if (value !== "catch") return;
    $.ajax({
      url: "data_mi_produccion.php",
      type: "POST",
      data: { consulta: "eliminar_registro", id_registro: idRegistro },
      success: function (x) {
        if (x.includes("success")) {
          toastr.success("Registro eliminado");
          cargarPedidosDisponibles();
          cargarEstadisticas(mesActual, anioActual);
          cargarHistorial(mesActual, anioActual);
        } else {
          toastr.error("No se pudo eliminar: " + x);
        }
      },
      error: function () { toastr.error("Error de conexión"); }
    });
  });
}

// ==================== UTILIDADES ====================

function formatNumber(num) {
  return parseFloat(num).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
