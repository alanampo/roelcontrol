$(document).ready(()=>{
    busca_entradas();
});

function busca_entradas() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html(
        "<h4 class='ml-1'>Buscando, espera...</h4>"
      );
    },
    url: "data_ver_clicks.php",
    type: "POST",
    data: {
      consulta: "busca_clicks",
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [[2, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ registros por página",
          zeroRecords: "No hay registros",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay registros",
          infoFiltered: "(filtrado de _MAX_ registros en total)",
          lengthMenu: "Mostrar _MENU_ registros",
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
        /*"columnDefs": [
                  { "width": "30%", "targets": [2] }
                  ]*/
      });


    },
    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}
