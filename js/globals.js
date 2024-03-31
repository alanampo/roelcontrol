$(document).ready(function () {
  //localStorage.removeItem("lastReserva")
  checkReservas();

});

const logoPrintImg = "dist/img/roel.jpg";

toastr.options = {
  closeButton: true,
  debug: false,
  newestOnTop: false,
  progressBar: false,
  positionClass: "toast-bottom-right",
  preventDuplicates: false,
  onclick: () => {
    location.href = "ver_reservas.php";
  },
  showDuration: "300",
  hideDuration: "1000",
  timeOut: "600000",
  extendedTimeOut: "600000",
  showEasing: "swing",
  hideEasing: "linear",
  showMethod: "fadeIn",
  hideMethod: "fadeOut",
};

var globals = {
  logoPrintImg: logoPrintImg,
  printHeader: `
    <div class="row">
        <div class="col text-center">
            <img src='${logoPrintImg}' class="logo-print"></img>
            <address style='font-size:12px !important;padding-top:3px;padding-bottom:10px;'>
            Paradero 7 de San Pedro<br> 
            Quillota, Valparaíso<br>
            Tel.: +56 944 988 254<br>
            <p>E-mail: ventas@roelplant.cl</p>
            </address>
        </div>
    </div>
    `,
  printHeaderSimple: `
    <div align='center'>
        <img src='${logoPrintImg}' class="logo-print"></img>
        <address style='font-size:10px;'>
            <p>ventas@roelplant.cl</p>
        </address>
    </div><br><br>`,
};

function checkReservas() {
  const funcion = () => {
    $.ajax({
      type: "POST",
      url: "data_ver_reservas.php",
      data: { consulta: "check_reservas_nuevas" },
      success: function (x) {
        if (x.trim().length) {
          //console.log(x);
          try {
            const data = JSON.parse(x);
            const stored = localStorage.getItem("lastReserva");

            if (!stored) {
              // EL USUARIO NO RECIBIO NINGUNA NOTIFICACION
              toastr.info("Nueva Reserva de " + data.nombre_cliente);
              playSound();

              localStorage.setItem("lastReserva", JSON.stringify({
                id_reserva: data.id_reserva,
                nombre_cliente: data.nombre_cliente,
              }));
            } else {
              const storedData = JSON.parse(stored);
              if (
                storedData.id_reserva &&
                parseInt(storedData.id_reserva) < parseInt(data.id_reserva)
              ) {
                toastr.info("Nueva Reserva de " + data.nombre_cliente);
                playSound();
  
                localStorage.setItem("lastReserva", JSON.stringify({
                  id_reserva: data.id_reserva,
                  nombre_cliente: data.nombre_cliente,
                }));
              }
            } 

            
          } catch (error) {
            console.error(error);
          }
        }
        setTimeout(funcion, 5000);
      },
    });
  };
  funcion();
}

function playSound() {
  document.getElementById("alarm").play();
  document.getElementById("alarm").muted = false;
}
