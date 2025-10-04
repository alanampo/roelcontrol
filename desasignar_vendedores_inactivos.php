<?php
// Script para desasignar automáticamente vendedores que no han tenido contacto
// con el cliente en más de 6 meses

include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");

// Desasignar vendedores que llevan más de 6 meses sin contacto
$query = "UPDATE clientes
SET
    vendedor_anterior = id_vendedor,
    id_vendedor = NULL,
    fecha_cambio_vendedor = NOW()
WHERE
    id_vendedor IS NOT NULL
    AND fecha_ultimo_contacto IS NOT NULL
    AND fecha_ultimo_contacto < DATE_SUB(NOW(), INTERVAL 6 MONTH)";

if (mysqli_query($con, $query)) {
    $affected = mysqli_affected_rows($con);
    if ($affected > 0) {
        echo "success|$affected vendedores desasignados por inactividad.";
    } else {
        echo "success|No hay vendedores para desasignar.";
    }
} else {
    echo "error|" . mysqli_error($con);
}

mysqli_close($con);
?>
