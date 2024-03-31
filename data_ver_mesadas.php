<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';
$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST['consulta'];

if ($consulta == "cargar_mesadas") {
    $arraymesadas = array();
    $queryselect = "SELECT id_mesada, id_tipo, id_interno, (select * from (SELECT IFNULL(MAX(id_interno), 1) FROM mesadas) t) as maximo FROM mesadas ORDER BY id_tipo, id_interno;";

	$val = mysqli_query($con, $queryselect);

    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            array_push($arraymesadas,
				array(
					"id_mesada" => $re["id_mesada"],
					"id_tipo" => $re["id_tipo"],
					"id_interno" => $re["id_interno"],
                    "maximo" => $re["maximo"]
				)
			);
        }
        echo json_encode($arraymesadas);
    } 
}
else if ($consulta == "crear_mesada"){
	$id_tipo = $_POST['id_tipo'];
	$query = "INSERT INTO mesadas (id_tipo, id_interno) VALUES ('$id_tipo', 
	(select * from (SELECT IFNULL(MAX(id_interno)+1, 1) FROM mesadas WHERE id_tipo = '$id_tipo') t)
	);";
	try {
		if (mysqli_query($con, $query)){
			echo "success";
		}
		else{
			print_r(mysqli_error($con));
		}
		
	} catch (\Throwable $th) {
		throw $th;
	}
	
}
else if ($consulta == "eliminar_mesada"){
	$id_mesada = $_POST["id_mesada"];
	try {
		if (mysqli_query($con, "DELETE FROM mesadas WHERE id_mesada = $id_mesada;")){
			echo "success";
		}
		else{
			print_r(mysqli_error($con));
		}
	} catch (\Throwable $th) {
		throw $th;
	}
}
else if ($consulta == "asignar_mesada"){
	$id_mesada = $_POST["id_mesada"];
    $id_artpedido = $_POST["id_artpedido"];
	try {
		if (mysqli_query($con, "INSERT INTO mesadas_productos (id_mesada, id_artpedido) VALUES ($id_mesada, $id_artpedido)")){
			echo "success";
		}
		else{
			print_r(mysqli_error($con));
		}
	} catch (\Throwable $th) {
		throw $th;
	}
}
