<?php

include "./class_lib/sesionSecurity.php";
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
require('class_lib/class_conecta_mysql.php');
require('class_lib/funciones.php');

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con,"SET NAMES 'utf8'");

$cadena="SELECT c.id_cliente as id_cliente, UPPER(c.razon_social) as razon_social, c.nombre as nombre, c.domicilio as domicilio, c.domicilio2, c.telefono, c.mail as mail, c.provincia, c.region, c.rut as rut, com.ciudad as ciudad, com.nombre as comuna, com.id as id_comuna  
FROM clientes c 
LEFT JOIN comunas com ON c.comuna = com.id
ORDER BY nombre ASC;";

$val = mysqli_query($con, $cadena);

if (mysqli_num_rows($val)>0){
 echo "<div class='box box-primary'>";
 echo "<div class='box-header with-border'>";
 echo "</div>";
 echo "<div class='box-body'>";
 echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
 echo "<thead>";
 echo "<tr>";
 $th_eliminar = ($_SESSION["id_usuario"] == 1 ? "<th></th>" :"");

 echo "<th>ID</th><th>Nombre</th><th>Razon Social</th><th>Domicilio</th><th>Domicilio Envío</th><th>Teléfono</th><th>E-Mail</th><th>R.U.T</th><th>Ciudad</th><th>Comuna</th><th>Provincia</th><th>Región</th>$th_eliminar";
 echo "</tr>";
 echo "</thead>";
 echo "<tbody>";
  
 while($ww=mysqli_fetch_array($val)){
     $id_cliente=$ww['id_cliente'];
     $nombre=$ww['nombre'];
     $domicilio=$ww['domicilio'];
     $domicilio2=$ww['domicilio2'];

     $telefono = $ww['telefono'];
     $mail = $ww['mail'];
    
   echo "<tr x-id-comuna='$ww[id_comuna]' id='cliente_$id_cliente' style='cursor:pointer;'>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center; color:#1F618D; font-weight:bold; font-size:16px;'>$id_cliente</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$nombre</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$ww[razon_social]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$domicilio</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$domicilio2</td>";
   echo "<td style='text-align: center;'>$telefono</td>";
   echo "<td style='text-align: center;'>$mail</td>";
   echo "<td style='text-align: center;'>$ww[rut]</td>";
   echo "<td style='text-align: center;'>$ww[ciudad]</td>";
   echo "<td style='text-align: center;'>$ww[comuna]</td>";
   echo "<td class='td-provincia' style='text-align: center;'>$ww[provincia]</td>";
   echo "<td class='td-region' style='text-align: center;'>$ww[region]</td>";
   
   if ($_SESSION["id_usuario"] == 1){
    echo "<td style='text-align: center;'>
    <button class='btn btn-sm btn-danger fa fa-trash' onclick='eliminarCliente($id_cliente, \"$nombre\")'></button>
    </td>";
   }
   echo "</tr>";
   
 }
 echo "</tbody>";
 echo "</table>";
 echo "</div>";
 echo "</div>";


}else{
  echo "<div class='callout callout-danger'><b>No se encontraron clientes en la base de datos...</b></div>";
}
?>