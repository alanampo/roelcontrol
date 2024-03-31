let edit_mode = false;
let global_id_usuario = "";

$(document).ready(function () {
  $("#select_tipo").val(["0"]).selectpicker("refresh");
  $("#select_tipousuario").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      const tipo_usuario = this.value;
      if (tipo_usuario == "cliente") {
        pone_clientes();
        toggleMode(0);
      } else if (tipo_usuario == "trabajador") {
        toggleMode(1);
      }
    }
  );

  $("#username_txt,#input-email,#password_txt,#password2_txt").on('keydown', function(e) {
    if (e.keyCode == 32) return false;
  });

  busca_usuarios(0);
});

function toggleMode(tipo_usuario) {
  if (tipo_usuario == 0) {
    $(".form-email").removeClass("d-none");
    $(".form-usuario").addClass("d-none");
    $(".form-cliente").removeClass("d-none");
    $(".form-permisos").addClass("d-none");
  } else if (tipo_usuario == 1) {
    $(".form-email").addClass("d-none");
    $(".form-usuario").removeClass("d-none");
    $(".form-permisos").removeClass("d-none");
    $(".form-cliente").addClass("d-none");
  } else if (tipo_usuario == -1) {
    $(".form-email").addClass("d-none");
    $(".form-usuario").addClass("d-none");
    $(".form-permisos").addClass("d-none");
    $(".form-cliente").addClass("d-none");
  }
}

function pone_clientes() {
  
    $.ajax({
      beforeSend: function () {
        $("#select_cliente").html("Cargando lista de clientes...");
      },
      url: "data_ver_clientes.php",
      type: "POST",
      data: {
        consulta: "pone_clientes"
      },
      success: function (x) {
        if (
          /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)
        ) {
          $(".selectpicker").selectpicker("mobile");
        } else {
          let elements = document.querySelectorAll(".mobile-device");
          for (let i = 0; i < elements.length; i++) {
            elements[i].classList.remove("mobile-device");
          }
          $(".selectpicker").selectpicker({});
        }

        $("#select_cliente").html(x).selectpicker("refresh");

        $("#select_cliente").on(
          "changed.bs.select",
          function (e, clickedIndex, newValue, oldValue) {
            const email = $("#select_cliente")
              .find("option:selected")
              .attr("x-email")
              .trim();
            if (email && email.length && email.includes("@")) {
              $("#input-email").val(email).focus();
            }
          }
        );
      },
      error: function (jqXHR, estado, error) {},
    });
 
}

function busca_usuarios(tipo) {
  console.log(tipo);
  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
    $(".selectpicker").selectpicker("mobile");
  } else {
    let elements = document.querySelectorAll(".mobile-device");
    for (let i = 0; i < elements.length; i++) {
      elements[i].classList.remove("mobile-device");
    }
    $(".selectpicker").selectpicker({});
  }

  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando usuarios, espere...");
    },
    url: "data_ver_usuarios.php",
    type: "POST",
    data: { tipo: tipo, consulta: "busca_usuarios" },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[1, "asc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ usuarios por página",
          zeroRecords: "No hay usuarios",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay usuarios",
          infoFiltered: "(filtrado de _MAX_ usuarios en total)",
          lengthMenu: "Mostrar _MENU_ usuarios",
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

function ModificarUsuario(
  id_usuario,
  password,
  nombre,
  nombre_real,
  permisos,
  tipo_usuario
) {
  $("#ModalAgregarUsuario").attr("x-tipousuario", tipo_usuario);
  $("#ModalAgregarUsuario").attr("x-id-usuario", id_usuario);

  toggleMode(-1);
  if (tipo_usuario == 1) {
    let arraypermisos = null;
    if (permisos.length > 0) {
      if (permisos.includes(",")) {
        arraypermisos = permisos.split(", ");
      } else {
        arraypermisos = [permisos];
      }
    }
    $("#select_permisos").val(arraypermisos).selectpicker("refresh");
    $("#username_txt").val(nombre);
    $("#nombre_txt").val(nombre_real);

    $(".form-usuario").removeClass("d-none");
    $(".form-email").addClass("d-none");
    $(".form-permisos").removeClass("d-none")
  } else if (tipo_usuario == 0) {
    $("#input-email").val(nombre);
    $(".form-email").removeClass("d-none");
    $(".form-usuario").addClass("d-none");
    $(".form-permisos").addClass("d-none")
  }
  $(".form-tipousuario").addClass("d-none");

  $("#password_txt").val(password);
  $("#password2_txt").val(password);

  $("#ModalAgregarUsuario").find("#titulo").html("Modificar Usuario");

  $("#btn-guardar-usuario").on("click", function(){
    modificarUsuario();
  })

  edit_mode = true;
  let modal = document.getElementById("ModalAgregarUsuario");
  modal.style.display = "block";
  document.getElementById("username_txt").focus();
}

function MostrarModalAgregarUsuario() {
  $("#ModalAgregarUsuario")
    .find("#username_txt,#nombre_txt,#password_txt,#password2_txt,#input-email")
    .val("");
  $("#select_permisos").val("default").selectpicker("refresh");
  $("#select_tipousuario").val("default").selectpicker("refresh");

  $("#select_cliente").val("default").selectpicker("refresh");

  $("#ModalAgregarUsuario").find("#titulo").html("Agregar Usuario");
  $(".form-tipousuario").removeClass("d-none");
  toggleMode(-1);
  edit_mode = false;
  global_id_usuario = "";
  let modal = document.getElementById("ModalAgregarUsuario");
  modal.style.display = "block";
  $("#btn-guardar-usuario").on("click", function(){
    guardarNuevoUsuario();
  })
  document.getElementById("username_txt").focus();
}

function CerrarModal() {
  let modal = document.getElementById("ModalAgregarUsuario");
  modal.style.display = "none";
}

function guardarNuevoUsuario() {
  const nombre = $("#username_txt").val().trim();
  const nombre_real = $("#nombre_txt").val().trim();
  const password1 = $("#password_txt").val().trim();
  const password2 = $("#password2_txt").val().trim();
  const permisos = $("#select_permisos").val();
  const tipo_usuario = $("#select_tipousuario").find("option:selected").val();
  const id_cliente = $("#select_cliente").find("option:selected").val();
  const email = $("#input-email").val().trim();

  if (!tipo_usuario) {
    swal("Debes elegir un Tipo de Usuario", "", "error");
  } else if (tipo_usuario == "trabajador" && nombre.length < 3) {
    swal("ERROR", "Debes ingresar un nombre de al menos 3 letras", "error");
  } else if (tipo_usuario == "trabajador" && !nombre.charAt(0).match(/[a-zA-Z]/) ) {
    swal("ERROR", "El nombre no puede comenzar con números", "error");
  }
  else if (tipo_usuario == "trabajador" && /\s/.test(nombre)){
    swal("El nombre no puede contener espacios", "", "error");
  }
   else if (password1.length < 1) {
    swal("ERROR", "Debes ingresar una contraseña!", "error");
  } else if (password1 != password2) {
    swal("ERROR", "Las contraseñas ingresadas no coinciden", "error");
  } else if (password1.length > 20) {
    swal("La contraseña es demasiado larga!", "", "error");
  }
  else if (tipo_usuario == "trabajador" && nombre_real.length < 4) {
    swal("Ingresa el nombre completo del Trabajador", "", "error")
  }
  else if (tipo_usuario == "trabajador" && permisos.length == 0) {
    swal("ERROR", "Debes seleccionar al menos un permiso", "error");
  } else if (tipo_usuario == "cliente" && !validateEmail(email)) {
    swal("Debes ingresar un E-Mail válido para crear el usuario", "", "error");
  } else if (/^[A-Za-z0-9]+$/.test(password1) == false) {
    swal("ERROR", "La contraseña solo puede tener letras y/o números", "error");
  } else {
    document.getElementById("ModalAgregarUsuario").style.display = "none";
    $.ajax({
      url: "guarda_usuario.php",
      type: "POST",
      data: {
        tipo: "agregar",
        tipo_usuario: tipo_usuario,
        id_cliente: tipo_usuario == "cliente" ? id_cliente : null,
        nombre: tipo_usuario == "trabajador" ? nombre : null,
        nombre_real: tipo_usuario == "trabajador" ? nombre_real : null,
        email: tipo_usuario == "cliente" ? email : null,
        password: password1,
        permisos:
          tipo_usuario == "trabajador" ? JSON.stringify(permisos) : null,
      },
      success: function (x) {
        if (x.includes("yaexiste")) {
          swal("Ya existe un usuario con ese nombre", "", "error");
          document.getElementById("ModalAgregarUsuario").style.display =
            "block";
        } else if (x.trim() == "success"){
          swal("El usuario fue agregado correctamente!", "", "success");
          CerrarModal();
          busca_usuarios(tipo_usuario == "trabajador" ? 1 : 0);
        }
        else{
          swal("Ocurrió un error", x, "error")
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
}

function modificarUsuario() {
  const nombre = $("#username_txt").val().trim();
  const nombre_real = $("#nombre_txt").val().trim();
  const password1 = $("#password_txt").val().trim();
  const password2 = $("#password2_txt").val().trim();
  const permisos = $("#select_permisos").val();
  const tipo_usuario = $("#ModalAgregarUsuario").attr("x-tipousuario");
  const id_usuario = $("#ModalAgregarUsuario").attr("x-id-usuario");
  const email = $("#input-email").val().trim();

  if (tipo_usuario == "1" && nombre.length < 3) { //TRABAJADOR
    swal("ERROR", "Debes ingresar un nombre de usuario de al menos 3 letras", "error");
  }
  else if (tipo_usuario == "1" && nombre_real.length < 4) { //TRABAJADOR
    swal("Debes ingresar un nombre de al menos 4 letras", "error");
  }
  else if (password1.length < 1) {
    swal("ERROR", "Debes ingresar una contraseña!", "error");
  } else if (password1 != password2) {
    swal("ERROR", "Las contraseñas ingresadas no coinciden", "error");
  } else if (password1.length > 20) {
    swal("La contraseña es demasiado larga!", "", "error");
  } else if (tipo_usuario == "1" && permisos.length == 0) {
    swal("ERROR", "Debes seleccionar al menos un permiso", "error");
  } else if (tipo_usuario == "cliente" && !validateEmail(email)) {
    swal("Debes ingresar un E-Mail válido para el usuario", "", "error");
  } else if (/^[A-Za-z0-9]+$/.test(password1) == false) {
    swal("ERROR", "La contraseña solo puede tener letras y/o números", "error");
  } else {
    document.getElementById("ModalAgregarUsuario").style.display = "none";
    $.ajax({
      url: "guarda_usuario.php",
      type: "POST",
      data: {
        id_usuario: id_usuario,
        tipo: "editar",
        tipo_usuario: tipo_usuario == "1" ? "trabajador" : "cliente",
        nombre: tipo_usuario == "1" ? nombre : null,
        nombre_real: tipo_usuario == "1" ? nombre_real : null,
        email: tipo_usuario == "0" ? email : null,
        password: password1,
        permisos:
          tipo_usuario == "1" ? JSON.stringify(permisos) : null,
      },
      success: function (x) {
        if (x.includes("yaexiste")) {
          swal("Ya existe un usuario con ese nombre", "", "error");
          document.getElementById("ModalAgregarUsuario").style.display =
            "block";
        } else if (x.trim() == "success") {
          swal("El usuario fue modificado correctamente!", "", "success");
          CerrarModal();
          busca_usuarios(tipo_usuario == "1" ? 1 : 0);
        }
        else{
          swal("Ocurrió un error", x, "error")
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
}

function validateEmail(email) {
  let re = /\S+@\S+\.\S+/;
  return re.test(email);
}

function toggleUsuario(id_usuario, inhabilitado) {
  swal(
    `Estás seguro/a de ${inhabilitado ? "Inhabilitar a":"Activar"} este usuario?`,
    "",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "ACEPTAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          beforeSend: function () {},
          url: "data_ver_usuarios.php",
          type: "POST",
          data: { consulta: "toggle_usuario", id_usuario: id_usuario, inhabilitado:inhabilitado },
          success: function (x) {
            if (x != "success") {
              swal(
                "Ocurrió un error!",
                x,
                "error"
              );
            } else {
              swal("Modificaste el Usuario correctamente!", "", "success");
              const tipo_usuario = $("#select_tipo").find("option:selected").val();
              busca_usuarios(parseInt(tipo_usuario));
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