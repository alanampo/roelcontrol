function exportar(tipo){
    $.ajax({
      url: "data_informes.php",
      type: "POST",
      data: {
        consulta: "exportar_"+tipo,
        tipo: tipo
      },
      success: function (x) {
        console.log(x)
        if (x.includes("success")){
          window
                .open(
                  `${tipo}.csv?date={${Date.now()}}`,
                  "_blank"
                )
          swal("Generaste el CSV correctamente!", "", "success")
        }
      },
    });
  }
  