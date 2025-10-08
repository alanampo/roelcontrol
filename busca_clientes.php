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

// Filtro de clientes sin vendedor
$filtro_sin_vendedor = isset($_POST['sin_vendedor']) && $_POST['sin_vendedor'] == 'true';

$where_clause = "";
if ($filtro_sin_vendedor) {
    $where_clause = "WHERE c.id_vendedor IS NULL";
}

$cadena="SELECT c.id_cliente as id_cliente, UPPER(c.razon_social) as razon_social, c.nombre as nombre, c.domicilio as domicilio, c.domicilio2, c.telefono, c.mail as mail, c.provincia, c.region, c.rut as rut, c.id_vendedor, c.fecha_ultimo_contacto, com.ciudad as ciudad, com.nombre as comuna, com.id as id_comuna, u.nombre_real as vendedor_nombre,
DATE_FORMAT(c.fecha_ultimo_contacto, '%d/%m/%Y') as fecha_ultimo_contacto_format
FROM clientes c
LEFT JOIN comunas com ON c.comuna = com.id
LEFT JOIN usuarios u ON c.id_vendedor = u.id
$where_clause
ORDER BY nombre ASC;";

$val = mysqli_query($con, $cadena);

if (mysqli_num_rows($val)>0){
 echo "<div class='box box-primary'>";
 echo "<div class='box-header with-border'>";
 echo "</div>";
 echo "<div class='box-body'>";
 echo "<table id='tabla' class='table table-bordered table-striped'>";
 echo "<thead>";
 echo "<tr>";
 $th_eliminar = ($_SESSION["id_usuario"] == 1 ? "<th></th>" :"");

 echo "<th>ID</th><th>Nombre</th><th>Razon Social</th><th>Domicilio</th><th>Domicilio Envío</th><th>Teléfono</th><th>E-Mail</th><th>R.U.T</th><th>Ciudad</th><th>Comuna</th><th>Provincia</th><th>Región</th><th>Vendedor</th><th>Últ. Contacto</th>$th_eliminar";
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
     $id_vendedor = $ww['id_vendedor'] ? $ww['id_vendedor'] : '';
     $vendedor_nombre = $ww['vendedor_nombre'] ? $ww['vendedor_nombre'] : '-';
     $fecha_ultimo_contacto = $ww['fecha_ultimo_contacto_format'] ? $ww['fecha_ultimo_contacto_format'] : '-';

     // Verificar si han pasado más de 6 meses sin contacto
     $alerta_inactividad = '';
     if ($ww['fecha_ultimo_contacto'] && $ww['id_vendedor']) {
         $fecha_limite = date('Y-m-d', strtotime('-6 months'));
         if ($ww['fecha_ultimo_contacto'] < $fecha_limite) {
             $alerta_inactividad = " style='background-color: #ffcccc;'";
         }
     }

   echo "<tr x-id-comuna='$ww[id_comuna]' x-id-vendedor='$id_vendedor' id='cliente_$id_cliente' style='cursor:pointer;'$alerta_inactividad>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center; color:#1F618D; font-weight:bold; font-size:16px;'>$id_cliente</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$nombre</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$ww[razon_social]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$domicilio</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$domicilio2</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$telefono</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$mail</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$ww[rut]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$ww[ciudad]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$ww[comuna]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-provincia' style='text-align: center;'>$ww[provincia]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-region' style='text-align: center;'>$ww[region]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$vendedor_nombre</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='text-align: center;'>$fecha_ultimo_contacto</td>";
   
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