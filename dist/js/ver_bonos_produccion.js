let currentTab = null;
const meses = [
  "ENERO",
  "FEBRERO",
  "MARZO",
  "ABRIL",
  "MAYO",
  "JUNIO",
  "JULIO",
  "AGOSTO",
  "SEPTIEMBRE",
  "OCTUBRE",
  "NOVIEMBRE",
  "DICIEMBRE",
];


$(document).ready(function () {
  $("#select-anio").html("");
  const anio = new Date().getFullYear();
  for (let i = 2022; i <= anio; i++) {
    $("#select-anio").append(`<option value="${i}">${i}</option>`);
  }
  $("#select-anio").val(anio);
  $(".selectpicker").selectpicker("refresh");

  
  $("#select-anio").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      if (currentTab == "tabla"){
        loadBonos()
        $(".tab-tabla").removeClass("d-none")
      }
      else if (currentTab == "grafico"){
        loadGrafico()
        $(".tab-grafico").removeClass("d-none")
      }
      else if (currentTab == "graficobonos"){
        loadGraficoBonos()
        $(".tab-grafico-bonos").removeClass("d-none")
      }
    }
  );

  document.getElementById("defaultOpen").click();
});

function abrirTab(evt, tabName) {
  let i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  currentTab = tabName;
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  $(".tabco").addClass("d-none");

  //document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  currentTab = tabName;

  if (tabName == "tabla"){
    loadBonos()
    $(".tab-tabla").removeClass("d-none")
  }
  else if (tabName == "grafico"){
    loadGrafico()
    $(".tab-grafico").removeClass("d-none")
  }
  else if (tabName == "graficobonos"){
    loadGraficoBonos()
    $(".tab-grafico-bonos").removeClass("d-none")
  }
}

function loadBonos() {
  const anio = $("#select-anio option:selected").val();
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Buscando, espere...");
    },
    url: "data_ver_bonos_produccion.php",
    type: "POST",
    data: {
      consulta: "busca_bonos",
      anio: anio
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [[0, "asc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ resultados por página",
          zeroRecords: "No hay resultados",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay resultados",
          infoFiltered: "(filtrado de _MAX_ resultados en total)",
          lengthMenu: "Mostrar _MENU_ resultados",
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
      setTimeout(()=>{
        $(".input-number").on(
          "propertychange input",
          function (e) {
            this.value = this.value.replace(/\D/g, "");
          }
        );
      }, 500)
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function guardarCambios(obj, anio, mes){
  const cantEmpleados = $(obj).closest("tr").find(".input-cant-empl").val().trim();
  const montoEmpleados = $(obj).closest("tr").find(".input-monto-empl").val().trim();

  const cantSupervisores = $(obj).closest("tr").find(".input-cant-super").val().trim();
  const montoSupervisores = $(obj).closest("tr").find(".input-monto-super").val().trim();

  if (!cantEmpleados || !cantEmpleados.length || isNaN(cantEmpleados)){
    swal("Ingresa la Cantidad de Empleados!", "", "error")
  }
  else if (!montoEmpleados || !montoEmpleados.length || isNaN(montoEmpleados)){
    swal("Ingresa el Bonus por Planta para los Empleados!", "", "error")
  }
  else if (!cantSupervisores || !cantSupervisores.length || isNaN(cantSupervisores)){
    swal("Ingresa la Cantidad de Supervisores!", "", "error")
  }
  else if (!montoSupervisores || !montoSupervisores.length || isNaN(montoSupervisores)){
    swal("Ingresa el Bonus por Planta para los Supervisores!", "", "error")
  }
  else{
    $.ajax({
      type: "POST",
      url: "data_ver_bonos_produccion.php",
      data: { 
        consulta: "guardar_cambios", 
        cantEmpleados: cantEmpleados,
        montoEmpleados: montoEmpleados,
        cantSupervisores: cantSupervisores,
        montoSupervisores: montoSupervisores,
        anio: anio,
        mes: mes
      },
      success: function (x) {
        if (x.includes("success")){
          swal("Guardaste los cambios correctamente!", "", "success")
          loadBonos();
        }
        else{
          swal("Ocurrió un error al guardar los cambios", x, "error")
        }        
      },
    });
  }
}


function getLastData(obj){
  $(obj).prop("disabled", true);
  $.ajax({
    type: "POST",
    url: "data_ver_bonos_produccion.php",
    data: { 
      consulta: "get_last_data", 
    },
    success: function (x) {
      if (x.length){
        try {
          const data = JSON.parse(x);
          const { cantidadEmpleados, montoEmpleados, cantidadSupervisores, montoSupervisores } = data;
          $(obj).closest("tr").find(".input-cant-empl").val(cantidadEmpleados)
          $(obj).closest("tr").find(".input-monto-empl").val(montoEmpleados)
          $(obj).closest("tr").find(".input-cant-super").val(cantidadSupervisores)
          $(obj).closest("tr").find(".input-monto-super").val(montoSupervisores)
          $(obj).css({visibility:"hidden"})
        } catch (error) {
          
        }
      }    
      $(obj).prop("disabled", false);   
    },
    error: function(e){
      $(obj).prop("disabled", false);   
    }
  });
}


function loadGrafico() {
  const anio = $("#select-anio option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: "data_ver_bonos_produccion.php",
    type: "POST",
    data: {
      consulta: "grafico_bonos",
      anio: anio,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          chartBonos(data);
        } catch (error) {
          console.log(error);
          $(".chart-container").html(
            `<div class='callout callout-danger'><b>No se encontraron datos...</b></div>`
          );
        }
      } else {
        $(".chart-container").html(
          `<div class='callout callout-danger'><b>No se encontraron datos...</b></div>`
        );
        $(".label-estadisticas").html("");
      }
    },
    error: function (jqXHR, estado, error) {
      $(".chart-container").html(
        `<div class='callout callout-danger'><b>Ocurrió un error... ${error}</b></div>`
      );
    },
  });
}

function chartBonos(json) {
  $(".chart-container").css({ height: `` });
  $(".chart-container").html(`<canvas id="myChart"></canvas>`);
  var ctx = document.getElementById("myChart").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 2; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const color = line === 0 ? "0, 50, 255, 1" : "255, 0, 0, 0.5";
    dataset.backgroundColor = `rgba(${color})`;
    dataset.borderColor = `rgba(${color})`;
    dataset.strokeColor = `rgba(${color})`;
    dataset.data = []; //contains the 'Y; axis data
    
    if (line === 1){
      dataset.borderDash = [3,3]
      dataset.pointRadius = 0;
    }
      
    
    for (x = 0; x < 12; x++) {
      if (line === 0){
        y.push(Math.round(json[x].cantidad_plantas)); //push some data aka generate 4 distinct separate lines
      }
      else if (line === 1){
        y.push(60000); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(meses[x]); //adds x axis labels
    } //for x
    
    lineChartData.datasets[line].label = line === 0 ? "Plantas Producidas" : "Objetivo";
    lineChartData.datasets[line].data = y; //send new line data to dataset
    
    
  } //for line

  var myChart = new Chart(ctx, {
    type: "line",
    data: lineChartData,
    options: {
      indexAxis: "x",
      responsive: true,
      maintainAspectRatio: false,
      elements: {
        line: {
            tension : 0.3  // smooth lines
        },
    },
      scales: {
        y: {
          beginAtZero: true,
        },
        xAxes: [
          {
            maxBarThickness: 10,
          },
        ],
      },
    },
  });

  $(".chart-container").removeClass("d-none");
}

function loadGraficoBonos() {
  const anio = $("#select-anio option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container2").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: "data_ver_bonos_produccion.php",
    type: "POST",
    data: {
      consulta: "grafico_bonos",
      anio: anio,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          chartBonos2(data);
        } catch (error) {
          console.log(error);
          $(".chart-container2").html(
            `<div class='callout callout-danger'><b>No se encontraron datos...</b></div>`
          );
        }
      } else {
        $(".chart-container2").html(
          `<div class='callout callout-danger'><b>No se encontraron datos...</b></div>`
        );
        
      }
    },
    error: function (jqXHR, estado, error) {
      $(".chart-container2").html(
        `<div class='callout callout-danger'><b>Ocurrió un error... ${error}</b></div>`
      );
    },
  });
}

function chartBonos2(json) {
  $(".chart-container2").css({ height: `` });
  $(".chart-container2").html(`<canvas id="myChart2"></canvas>`);
  var ctx = document.getElementById("myChart2").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 2; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const color = line === 0 ? "0, 50, 255, 1" : "100, 200, 0, 1";
    dataset.backgroundColor = `rgba(${color})`;
    dataset.borderColor = `rgba(${color})`;
    dataset.strokeColor = `rgba(${color})`;
    dataset.data = []; //contains the 'Y; axis data
      
    for (x = 0; x < 12; x++) {
      if (line === 0){
        y.push(Math.round(json[x].bono_empleados)); //push some data aka generate 4 distinct separate lines
      }
      else if (line === 1){
        y.push(Math.round(json[x].bono_supervisores)); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(meses[x]); //adds x axis labels
    } //for x
    
    lineChartData.datasets[line].label = line === 0 ? "Bono Empleados ($)" : "Bono Supervisores ($)";
    lineChartData.datasets[line].data = y; //send new line data to dataset
    
    
  } //for line

  var myChart = new Chart(ctx, {
    type: "line",
    data: lineChartData,
    options: {
      indexAxis: "x",
      responsive: true,
      maintainAspectRatio: false,
      elements: {
        line: {
            tension : 0.3  // smooth lines
        },
    },
      scales: {
        y: {
          beginAtZero: true,
        },
        xAxes: [
          {
            maxBarThickness: 10,
          },
        ],
      },
    },
  });

  $(".chart-container2").removeClass("d-none");
}