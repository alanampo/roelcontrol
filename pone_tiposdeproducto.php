<?php

include "./class_lib/sesionSecurity.php";
error_reporting(0);
require('class_lib/class_conecta_mysql.php');

$tipo = $_POST['tipo'];
$id_tipo = $_POST['id_tipo'];
$id_subtipo = $_POST['id_subtipo'];

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}


mysqli_query($con,"SET NAMES 'utf8'");

if ($tipo == "pone_tiposdeproducto"){

	$cadena = "SELECT id_articulo, nombre FROM tipos_producto ORDER BY nombre ASC";
	$val = mysqli_query($con, $cadena);
	 if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<option value=$re[id_articulo]>$re[nombre]</option>";
	    }
	}
}
else if ($tipo == "carga_subtipos")
{

	$cadena = "SELECT id_articulo, nombre FROM subtipos_producto WHERE id_tipo = '$id_tipo' ORDER BY nombre ASC";
	$val = mysqli_query($con, $cadena);
	 if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<option value=$re[id_articulo]>$re[nombre]</option>";
	    }
	}
}

else if ($tipo == "carga_variedades") {
	$cadena = "SELECT id_articulo, nombre FROM variedades_producto WHERE id_subtipo = $id_subtipo ORDER BY nombre ASC;";
	$val = mysqli_query($con, $cadena);
	if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<option value=$re[id_articulo]>$re[nombre]</option>";
	    }
	}
}


else if ($tipo == "carga_stock"){
	$id_tipo = $_POST["id_tipo"];
	$cadena = "(SELECT t.nombre as nombre_tipo, s.nombre as nombre_subtipo, v.nombre as nombre_variedad, SUM(om.cantidad) as cantidad, p.bandeja, v.id_articulo AS id_variedad 
		FROM variedades_producto v 
		INNER JOIN subtipos_producto s ON v.id_subtipo = s.id_articulo 
		INNER JOIN tipos_producto t ON t.id_articulo = s.id_tipo
		INNER JOIN articulospedidos p ON p.id_articulo = v.id_articulo 
		INNER JOIN ordenes_siembra o ON o.id_artpedido = p.id_artpedido
		INNER JOIN ordenes_mesadas om ON om.id_orden = o.id_orden
		WHERE t.id_articulo = $id_tipo AND om.cantidad > 0 AND om.tipo = 1 
		GROUP BY v.id_articulo, p.bandeja
		ORDER BY nombre_subtipo ASC, nombre_variedad ASC)
UNION ALL
		(SELECT t.nombre as nombre_tipo, s.nombre as nombre_subtipo, v.nombre as nombre_variedad, SUM(om.cantidad) as cantidad, om.bandeja, om.id_variedad
		FROM variedades_producto v 
		INNER JOIN subtipos_producto s ON v.id_subtipo = s.id_articulo 
		INNER JOIN tipos_producto t ON t.id_articulo = s.id_tipo
		INNER JOIN ordenes_mesadas om ON om.id_variedad = v.id_articulo
		WHERE om.id_orden IS NULL AND t.id_articulo = $id_tipo AND om.cantidad > 0 AND om.tipo = 1 
		GROUP BY v.id_articulo, om.bandeja
		ORDER BY nombre_subtipo ASC, nombre_variedad ASC);";


	$val = mysqli_query($con, $cadena);
	if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<tr onClick='toggleSelection(this)' style='cursor:pointer;text-align:center;'>";
    		echo "<td>$re[nombre_subtipo]</td>";
    		echo "<td id='variedad_$re[id_variedad]'>$re[nombre_variedad]</td>";
   			echo "<td>$re[bandeja]</td>";
   			echo "<td>$re[cantidad]</td>";
   			echo "</tr>";
	    }
	}



}

?>
