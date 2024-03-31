<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';
header('Content-type: text/html; charset=utf-8');


if((int)$_SESSION["id_usuario"] != 1){
    header("Location: index.php");
}


$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");


$consulta = $_POST["consulta"];

if ($consulta == "busca_clicks") {
        $query = "SELECT
        cl.nombre as nombre_cliente,
        cl.id_cliente,
        u.nombre as nombre_usuario,
        cli.id as id_click,
        DATE_FORMAT(cli.fecha, '%d/%m/%Y %H:%i') as fecha,
        DATE_FORMAT(cli.fecha, '%Y%m%d %H:%i') as fecha_raw
        FROM clientes cl
        INNER JOIN usuarios u
        ON u.id_cliente = cl.id_cliente
        INNER JOIN clicks cli
        ON cli.id_usuario = u.id
        ";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Usuarios que clickearon en 'Control Vivero'</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table-pedidos table table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Nombre</th><th>Usuario</th><th>Fecha Click</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        while ($ww = mysqli_fetch_array($val)) {
            echo "<tr>";
            echo "<td>$ww[nombre_cliente]</td>";
            echo "<td>$ww[nombre_usuario]</td>";
            echo "<td><span style='display:none;'>$ww[fecha_raw]</span>$ww[fecha]</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>Los usuarios aún no clickearon en 'Control Vivero' ...</b></div>";
    }
} 