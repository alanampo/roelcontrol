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
$consulta = $_POST["consulta"];

if ($consulta == "busca_usuarios") {
    $tipo = $_POST["tipo"];

    if ($tipo == 0) {
        $cadena = "SELECT u.id, u.nombre, u.password, c.nombre as nombre_cliente, c.id_cliente, u.inhabilitado FROM usuarios u INNER JOIN clientes c ON c.id_cliente = u.id_cliente WHERE u.id <> 1 AND u.tipo_usuario = 0 ORDER BY u.nombre;";
        $val = mysqli_query($con, $cadena);

        if (mysqli_num_rows($val) > 0) {
            echo "<div class='box box-primary'>";
            echo "<div class='box-header with-border'>";
            echo "</div>";
            echo "<div class='box-body'>";
            echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Id</th><th>E-Mail</th><th>Cliente</th><th></th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            while ($ww = mysqli_fetch_array($val)) {
                echo "<tr style='cursor:pointer;'>";
                echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", null, 0)' style='text-align: center; color:#1F618D; font-weight:bold; font-size:16px;'>$ww[id]</td>";
                echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", null, 0)' style='text-align: center;font-weight:bold;font-size:16px;'>$ww[nombre]</td>";
                echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", null, 0)' style='text-align: center;font-weight:bold;font-size:16px;'>$ww[nombre_cliente] ($ww[id_cliente])</td>";
                echo "<td style='text-align: center;font-weight:bold;font-size:16px;'>" . ($ww["inhabilitado"] == 1 ?
                    "<button onclick='toggleUsuario($ww[id], 0)' class='btn btn-danger btn-sm'><i class='fa fa-times'></i> INHABILITADO</button>" :
                    "<button onclick='toggleUsuario($ww[id], 1)' class='btn btn-success btn-sm'><i class='fa fa-check'></i> ACTIVO</button>") . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='callout callout-danger'><b>No se encontraron usuarios en la base de datos...</b></div>";
        }
    } else if ($tipo == 1) { //EMPLEADO
        $cadena = "SELECT u.id, u.nombre, u.nombre_real, GROUP_CONCAT(p.modulo SEPARATOR ', ') as modulos, u.password, u.tipo_usuario, u.inhabilitado FROM  usuarios u LEFT JOIN permisos p ON p.id_usuario = u.id GROUP BY u.id HAVING u.id <> 1 AND u.tipo_usuario = 1 ORDER BY u.nombre;";
        $val = mysqli_query($con, $cadena);

        if (mysqli_num_rows($val) > 0) {
            echo "<div class='box box-primary'>";
            echo "<div class='box-header with-border'>";
            echo "</div>";
            echo "<div class='box-body'>";
            echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Id</th><th>Usuario</th><th>Nombre Real</th><th>Permisos</th><th></th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            while ($ww = mysqli_fetch_array($val)) {
                echo "<tr style='cursor:pointer;'>";
                echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", \"$ww[nombre_real]\", \"$ww[modulos]\", 1)' style='text-align: center; color:#1F618D; font-weight:bold; font-size:16px;'>$ww[id]</td>";
                echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", \"$ww[nombre_real]\", \"$ww[modulos]\", 1)' style='text-align: center;font-weight:bold;font-size:16px;'>$ww[nombre]</td>";
                echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", \"$ww[nombre_real]\", \"$ww[modulos]\", 1)' style='text-align: center;text-transform:capitalize;font-weight:bold;font-size:16px;'>$ww[nombre_real]</td>";
                echo "<td style='text-align: center;font-weight:bold;font-size:16px;'>$ww[modulos]</td>";
                echo "<td style='text-align: center;font-weight:bold;font-size:16px;'>" .
                    ($ww["inhabilitado"] == 1 ?
                    "<button onclick='toggleUsuario($ww[id], 0)' class='btn btn-danger btn-sm'><i class='fa fa-times'></i> INHABILITADO</button>" :
                    "<button onclick='toggleUsuario($ww[id], 1)' class='btn btn-success btn-sm'><i class='fa fa-check'></i> ACTIVO</button>"
                ) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='callout callout-danger'><b>No se encontraron usuarios en la base de datos...</b></div>";
        }
    }
}
else if ($consulta == "toggle_usuario"){
    $id_usuario = $_POST["id_usuario"];
    $inhabilitado = $_POST["inhabilitado"];
    try {
        if (mysqli_query($con, "UPDATE usuarios SET inhabilitado = $inhabilitado WHERE id = $id_usuario;")){
            echo "success";
        }
        else{
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        throw $th;
    }
}