<?php

include "./class_lib/sesionSecurity.php";

error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$objetivo = 60000;
$listameses = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];

if ($consulta == "busca_bonos") {
  $anio = $_POST["anio"];

  $querymes = "";
  for ($i = 1;$i<=12;$i++){
    $mes = str_pad($i, 2, '0', STR_PAD_LEFT);
    $dt = strtotime("$anio-$mes-01");
    $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

    $querymes.="(SELECT IFNULL(SUM(cant_plantas),0) as mes$i FROM articulospedidos WHERE fecha_stock IS NULL AND fecha_etapa5 >= '$anio-$mes-01 00:00:00' AND fecha_etapa5 < '$fechafin 00:00:00') q$i,";
  }

  for ($i = 1;$i<=12;$i++){
    $mes = str_pad($i, 2, '0', STR_PAD_LEFT);
    $dt = strtotime("$anio-$mes-01");
    $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

    $querymes.="(SELECT IFNULL(SUM(cant_plantas),0) as mes_stock$i FROM articulospedidos WHERE fecha_stock IS NOT NULL AND fecha_stock >= '$anio-$mes-01 00:00:00' AND fecha_stock < '$fechafin 00:00:00') q_2_$i,";
  }

  $querymes = rtrim($querymes, ",");  

  $query = "SELECT * FROM (
    $querymes

  )";
  
  $val = mysqli_query($con, $query);
  if (mysqli_num_rows($val) > 0) {
      $ww = mysqli_fetch_assoc($val);
      echo "<div class='box box-primary'>";
      echo "<div class='box-header with-border'>";
      echo "<h3 class='box-title'>Bonos de Producción</h3>";
      echo "</div>";
      echo "<div class='box-body'>";
      echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Mes</th><th>Plantas Etapa 5</th><th>Plantas Stock</th><th>Cantidad Total</th><th style='width:100px'>Empleados</th><th style='width:100px'>Supervisores</th><th>C/ Empleado Recibe</th><th>C/ Supervisor Recibe</th>";
      echo "</tr>";
      echo "</thead>";
      echo "<tbody>";
      
      for ($i = 1;$i<=12;$i++){
          $val2 = mysqli_query($con, "SELECT empleados_cantidad, empleados_monto, supervisores_cantidad, supervisores_monto FROM data_bonos_produccion WHERE mes = $i AND anio = $anio;");
          if ($val2){
            $cantEmpleados = NULL;
            $montoEmpleados = NULL;
            $cantSupervisores = NULL;
            $montoSupervisores = NULL;
            $bonoEmpleados = 0;
            $bonoSupervisores = 0;
            $total = (int)$ww["mes$i"] + (int)$ww["mes_stock$i"];
            $btn_get_last = "<span></span>";
            if (mysqli_num_rows($val2)){
              $v = mysqli_fetch_assoc($val2);
              $cantEmpleados = $v["empleados_cantidad"];
              $montoEmpleados = $v["empleados_monto"];
              $cantSupervisores = $v["supervisores_cantidad"];
              $montoSupervisores = $v["supervisores_monto"];

              $bonoEmpleados = ($montoEmpleados * $total) / $cantEmpleados;
              $bonoSupervisores = (($montoSupervisores * $total) / $cantEmpleados) / $cantSupervisores;
            }
            else{
              $btn_get_last = "<button onclick='getLastData(this)' class='btn mt-2 btn-secondary btn-sm'>=</button>";
            }




            
            $clase = $total < $objetivo ? "danger" : "success";
            echo "<tr class='text-center' style='cursor:pointer;'>";
            echo "<td><span class='d-none'>".str_pad($i, 2, '0', STR_PAD_LEFT)."</span>".$listameses[$i-1]."</td>";
            echo "<td>".$ww["mes$i"]."</td>";
            echo "<td>".$ww["mes_stock$i"]."</td>";
            echo "<td class='text-$clase'>".(number_format($total, 0, ',', '.'))."</td>";
            echo "<td>
            <div class='d-flex flex-row'>
              <small>CANT. EMPL.</small>
              <input type='text' value='$cantEmpleados' class='form-control text-center input-number input-cant-empl' maxlength='4'/>
            </div>
  
            <div class='d-flex flex-row mt-2'>
              <small>$ POR PLANTA</small>
              <input type='text' value='$montoEmpleados' class='form-control text-center input-number input-monto-empl' maxlength='4'/>
            </div>
            
            </td>";
            
            echo "<td>
            <div class='d-flex flex-row'>
              <small>CANT. SUPE.</small>
              <input type='text' value='$cantSupervisores' class='form-control text-center input-number input-cant-super' maxlength='4'/>
            </div>
  
            <div class='d-flex flex-row mt-2'>
              <small>$ POR PLANTA</small>
              <input type='text' value='$montoSupervisores' class='form-control text-center input-number input-monto-super' maxlength='4'/>
            </div>
            
            <div class='d-flex flex-row mt-2 justify-content-between'>
              $btn_get_last
              <button onclick='guardarCambios(this, $anio, $i)' class='btn mt-2 btn-primary btn-sm'><i class='fa fa-save'></i></button>
            </div>
            </td>";
            echo "<td class='text-$clase'>$".(number_format($bonoEmpleados, 0, ',', '.'))."</td>";
            echo "<td class='text-$clase'>$".(number_format($bonoSupervisores, 0, ',', '.'))."</td>";
            echo "</tr>";
          }


         
          //".(strlen($comision) > 0 ? "$".number_format($comision, 0, ',', '.') : "")."
      }
      echo "</tbody>";
      echo "</table>";
      echo "</div>";
      echo "</div>";

  } else {
      echo "<div class='callout callout-danger'><b>No se encontraron datos...</b></div>";
  }
}
else if ($consulta == "guardar_cambios"){
  $cantEmpleados = $_POST["cantEmpleados"];
  $montoEmpleados = $_POST["montoEmpleados"];
  $cantSupervisores = $_POST["cantSupervisores"];
  $montoSupervisores = $_POST["montoSupervisores"];
  $mes = $_POST["mes"];
  $anio = $_POST["anio"];

  $query = "SELECT * FROM data_bonos_produccion WHERE mes = $mes AND anio = $anio;";

  $val = mysqli_query($con, $query);

  if ($val && mysqli_num_rows($val) > 0){
    $query = "UPDATE data_bonos_produccion SET empleados_cantidad = $cantEmpleados, empleados_monto = $montoEmpleados, supervisores_cantidad = $cantSupervisores, supervisores_monto = $montoSupervisores WHERE mes = $mes AND anio = $anio;";
    if (mysqli_query($con, $query)){
      echo "success";
    }
    else{
      echo mysqli_error($con);
    }
  }
  else if ($val && mysqli_num_rows($val) === 0){
    $query = "INSERT INTO data_bonos_produccion (
      empleados_cantidad, 
      empleados_monto, 
      supervisores_cantidad, 
      supervisores_monto, 
      mes, 
      anio)
    VALUES (
      $cantEmpleados,
      $montoEmpleados,
      $cantSupervisores,
      $montoSupervisores,
      $mes, 
      $anio
    )";

    if (mysqli_query($con, $query)){
      echo "success";
    }
    else{
      echo mysqli_error($con);
    }
  }
}
else if ($consulta == "get_last_data"){
  $val = mysqli_query($con, "SELECT * FROM data_bonos_produccion ORDER BY rowid DESC LIMIT 1");
  if ($val && mysqli_num_rows($val) > 0){
    $v = mysqli_fetch_assoc($val);
    echo json_encode(array(
      "cantidadEmpleados" => $v["empleados_cantidad"],
      "montoEmpleados" => $v["empleados_monto"],
      "cantidadSupervisores" => $v["supervisores_cantidad"],
      "montoSupervisores" => $v["supervisores_monto"],
    ));
  }
}
else if ($consulta == "grafico_bonos") {
  $anio = $_POST["anio"];
  $array = array();
  $querymes = "";
  for ($i = 1;$i<=12;$i++){
    $mes = str_pad($i, 2, '0', STR_PAD_LEFT);
    $dt = strtotime("$anio-$mes-01");
    $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

    $querymes.="(SELECT IFNULL(SUM(cant_plantas),0) as mes$i FROM articulospedidos WHERE fecha_stock IS NULL AND fecha_etapa5 >= '$anio-$mes-01 00:00:00' AND fecha_etapa5 < '$fechafin 00:00:00') q$i,";
  }

  for ($i = 1;$i<=12;$i++){
    $mes = str_pad($i, 2, '0', STR_PAD_LEFT);
    $dt = strtotime("$anio-$mes-01");
    $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

    $querymes.="(SELECT IFNULL(SUM(cant_plantas),0) as mes_stock$i FROM articulospedidos WHERE fecha_stock IS NOT NULL AND fecha_stock >= '$anio-$mes-01 00:00:00' AND fecha_stock < '$fechafin 00:00:00') q_2_$i,";
  }

  $querymes = rtrim($querymes, ",");  

  $query = "SELECT * FROM (
    $querymes

  )";
  
  $val = mysqli_query($con, $query);
  if (mysqli_num_rows($val) > 0) {
      $ww = mysqli_fetch_assoc($val);
      
      
      for ($i = 1;$i<=12;$i++){
          $val2 = mysqli_query($con, "SELECT empleados_cantidad, empleados_monto, supervisores_cantidad, supervisores_monto FROM data_bonos_produccion WHERE mes = $i AND anio = $anio;");
          if ($val2){
            $cantEmpleados = NULL;
            $montoEmpleados = NULL;
            $cantSupervisores = NULL;
            $montoSupervisores = NULL;
            $bonoEmpleados = 0;
            $bonoSupervisores = 0;
            $total = (int)$ww["mes$i"] + (int)$ww["mes_stock$i"];
            if (mysqli_num_rows($val2)){
              $v = mysqli_fetch_assoc($val2);
              $cantEmpleados = $v["empleados_cantidad"];
              $montoEmpleados = $v["empleados_monto"];
              $cantSupervisores = $v["supervisores_cantidad"];
              $montoSupervisores = $v["supervisores_monto"];

              $bonoEmpleados = ($montoEmpleados * $total) / $cantEmpleados;
              $bonoSupervisores = (($montoSupervisores * $total) / $cantEmpleados) / $cantSupervisores;
            }

            array_push($array, array(
              "cantidad_plantas" => $total,
              "bono_empleados" => $bonoEmpleados,
              "bono_supervisores" => $bonoSupervisores
            ));
          }
      }
      echo json_encode($array);
  }
}
