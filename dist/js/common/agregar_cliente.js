let edit_mode = false;
let global_id_cliente = null;
$(document).ready(function () {
  pone_comunas();
  pone_usuarios();
  global_id_cliente = null;
  edit_mode = false;

  $("#rutcliente_txt")
    .keypress(function (e) {
      var allowedChars = new RegExp("^[0-9-kK]+$");
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (allowedChars.test(str)) {
        return true;
      }
      e.preventDefault();
      return false;
    })
    .keyup(function () {
      // the addition, which whill check the value after a keyup (triggered by Ctrl+V)
      // We take the same regex as for allowedChars, but we add ^ after the first bracket : it means "all character BUT these"
      var forbiddenChars = new RegExp("[^0-9-kK]", "g");
      if (forbiddenChars.test($(this).val())) {
        $(this).val($(this).val().replace(forbiddenChars, ""));
      }
    });
});

function MostrarModalAgregarCliente() {
  edit_mode = false;
  $("#ModalAgregarCliente").find("input").val("");
  $("#ModalAgregarCliente").find("#titulo").html("Agregar Cliente");
  $("#select-comuna2").val("default").selectpicker("refresh");
  $("#select-vendedor").val("default").selectpicker("refresh");
  $("#grupo-vendedor-agregar").show();
  $("#ModalAgregarCliente").modal("show");
  document.getElementById("nombrecliente_txt").focus();
}

function GuardarCliente() {
  const nombre = $("#nombrecliente_txt").val().trim();
  const domicilio = $("#domiciliocliente_txt").val().trim();
  const domicilio2 = $("#domiciliocliente2_txt").val().trim();
  const telefono = $("#telcliente_txt").val().trim();
  const rut = $("#rutcliente_txt").val().trim();
  const razonSocial = $("#razonsocial_txt").val().trim();
  const mail = $("#mailcliente_txt").val().trim();
  const comuna = $("#select-comuna2 option:selected").val();
  const provincia = $("#provinciacliente_txt").val().trim();
  const region = $("#regioncliente_txt").val().trim();
  const id_vendedor = !edit_mode ? $("#select-vendedor option:selected").val() : null;
  if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
  } else if (domicilio.length < 3) {
    swal("Debes ingresar un Domicilio!", "", "error");
  } else if (!comuna || !comuna.length) {
    swal("Selecciona la Comuna!", "", "error");
  } else if (telefono.length == 0 && whatsapp.length == 0) {
    swal("Debes ingresar un teléfono o whatsapp!", "", "error");
  } else if (mail.includes(" ") == true) {
    swal("El E-Mail no puede contener espacios", "", "error");
  } else {
    $("#ModalAgregarCliente").modal("hide");
    $.ajax({
      url: "guarda_cliente.php",
      type: "POST",
      data: {
        tipo: !edit_mode ? "agregar" : "editar",
        nombre: nombre,
        domicilio: domicilio,
        domicilio2,
        telefono: telefono,
        rut: rut,
        razonSocial: razonSocial,
        mail: mail,
        comuna: comuna,
        provincia: provincia,
        region: region,
        id_vendedor: id_vendedor,
        id_cliente: edit_mode ? global_id_cliente : null,
      },
      success: function (x) {
        if (x.trim() == "success") {
          busca_clientes();
          swal("El cliente fue guardado correctamente!", "", "success");
        } else {
          swal("Ocurrió un error al guardar el cliente", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
        $("#ModalAgregarCliente").modal("show");
      },
    });
  }
}

function pone_comunas() {
  $("#select_cliente").prop("disabled", true);
  $.ajax({
    beforeSend: function () {
      $("#select-comuna,#select-comuna2").html("Cargando lista de comunas...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: { consulta: "pone_comunas" },
    success: function (x) {
      $("#select-comuna,#select-comuna2").html(x).selectpicker("refresh");
      $("#select-comuna").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          const id_cliente = $("#select_cliente option:selected").val();
          if (id_cliente && id_cliente.length) setChanged(true);
        }
      );

      $("#select_cliente").prop("disabled", false).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {
      $("#select_cliente").prop("disabled", false).selectpicker("refresh");
    },
  });
}

function pone_usuarios() {
  $.ajax({
    beforeSend: function () {
      $("#select-vendedor").html("Cargando lista de usuarios...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: { consulta: "pone_usuarios" },
    success: function (x) {
      $("#select-vendedor").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {
      $("#select-vendedor").html("Error al cargar usuarios").selectpicker("refresh");
    },
  });
}

function MostrarModalModificarCliente(id_cliente) {
  let indice = $("#" + id_cliente)
    .closest("tr")
    .index();
  let id = id_cliente.replace("cliente_", "");
  let nombre = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(1)")
    .text();
  let razon = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(2)")
    .text();
  let domicilio = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(3)")
    .text();
    let domicilio2 = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(4)")
    .text();
  let telefono = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(5)")
    .text();
  let mail = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(6)")
    .text();
  let rut = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(7)")
    .text();
  let comuna = $("#" + id_cliente)
    .closest("tr").attr("x-id-comuna")

  const tr = $("#tabla")
  .find("tr:eq(" + (parseInt(indice) + 1).toString() + ")");
  const provincia = $(tr).find(".td-provincia").text();
  const region = $(tr).find(".td-region").text();
  $("#select-comuna2").val("default").selectpicker("refresh");
  $("#ModalAgregarCliente").find("#titulo").html("Modificar Cliente");
  $("#nombrecliente_txt").val(nombre);
  $("#razonsocial_txt").val(razon);
  $("#domiciliocliente_txt").val(domicilio);
  $("#domiciliocliente2_txt").val(domicilio2);
  $("#telcliente_txt").val(telefono);
  $("#mailcliente_txt").val(mail);
  $("#rutcliente_txt").val(rut);
  $("#select-comuna2").val(comuna).selectpicker("refresh")
  $("#provinciacliente_txt").val(provincia);
  $("#regioncliente_txt").val(region);
  $("#grupo-vendedor-agregar").hide();
  global_id_cliente = id;
  edit_mode = true;
  $("#ModalAgregarCliente").modal("show");
  document.getElementById("nombrecliente_txt").focus();
}

function setRazonSocial() {
  const nombre = $("#nombrecliente_txt").val().trim();

  if (nombre && nombre.length) {
    $("#razonsocial_txt").val(nombre);
  }
}
