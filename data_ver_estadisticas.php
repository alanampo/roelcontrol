<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");
$fechai = $_POST['fechai'];
$fechaf = $_POST['fechaf'];

$fechai = str_replace("/", "-", $fechai);
$fechaf = str_replace("/", "-", $fechaf);

if (strlen($fechai) == 0) {
    $fechai = (string) date('y-m-d', strtotime("first day of -3 month"));
}
if (strlen($fechaf) == 0) {
    $fechaf = "NOW()";
}

$filtros = json_decode($_POST['filtros'], true);

$consulta = $_POST["consulta"];

$tipo_busqueda = $_POST["tipo_busqueda"];

$tipo_filtro = $_POST["tipo_filtro"];

$tipo_pedido = $_POST["tipo_pedido"];

if ($consulta == "busca_estadisticas") {
    if ($tipo_pedido == "esquejes") {
        $query = "SELECT
    t.codigo as tipo,
    v.nombre as nombre_variedad,
    v.id as id_variedad,
    SUM(ap.cant_plantas) as cant_plantas,
    SUM(ap.cant_bandejas) as cant_bandejas,
    COUNT(ap.id) as cant_pedidos
    FROM tipos_producto t
    INNER JOIN variedades_producto v ON v.id_tipo = t.id
    INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
    WHERE ap.eliminado IS NULL AND ap.id_especie IS NULL ";
        if ($tipo_busqueda == "pendientes") {
            $query .= "AND ap.estado = -10";
        } else if ($tipo_busqueda == "produccion") {
            $query .= "AND ap.estado >= 0 AND ap.estado <= 6";
        } else if ($tipo_busqueda == "parcial") {
            $query .= "AND ap.estado = 6";
        } else if ($tipo_busqueda == "entregados") {
            $query .= "AND ap.estado = 7";
        }

        $query .= " GROUP BY v.id;";
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $array = array();
            while ($ww = mysqli_fetch_array($val)) {
                array_push($array, array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $ww["nombre_variedad"],
                    "id_variedad" => $ww["id_variedad"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                ));
            }
            echo json_encode($array);
        }
    } else {
        $query = "SELECT
    t.codigo as tipo,
    e.nombre as nombre_variedad,
    e.id as id_variedad,
    SUM(ap.cant_plantas) as cant_plantas,
    SUM(ap.cant_bandejas) as cant_bandejas,
    COUNT(ap.id) as cant_pedidos
    FROM tipos_producto t
    INNER JOIN especies_provistas e ON e.id_tipo = t.id
    INNER JOIN articulospedidos ap ON ap.id_especie = e.id
    WHERE ap.eliminado IS NULL AND ap.id_especie IS NOT NULL ";
        if ($tipo_busqueda == "pendientes") {
            $query .= "AND ap.estado = -10";
        } else if ($tipo_busqueda == "produccion") {
            $query .= "AND ap.estado >= 0 AND ap.estado <= 6";
        } else if ($tipo_busqueda == "parcial") {
            $query .= "AND ap.estado = 6";
        } else if ($tipo_busqueda == "entregados") {
            $query .= "AND ap.estado = 7";
        }

        $query .= " GROUP BY e.id;";
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $array = array();
            while ($ww = mysqli_fetch_array($val)) {
                array_push($array, array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $ww["nombre_variedad"],
                    "id_variedad" => $ww["id_variedad"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                ));
            }
            echo json_encode($array);
        }
    }
} else if ($consulta == "carga_cantidad_pedidos") {
    try {
        $arraypedidos = array();

        if ($tipo_pedido == "esquejes"){
            $tipo_pedido_busqueda = " AND id_especie IS NULL";
        }
        else{
            $tipo_pedido_busqueda = " AND id_especie IS NOT NULL";
        }

        if ($tipo_filtro == "pedidos"){
            $val = mysqli_query($con, "SELECT  (
                SELECT IFNULL(COUNT(*),0)
                FROM   articulospedidos WHERE estado = -10 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS pendientes,
            (
                SELECT IFNULL(COUNT(*),0)
                FROM   articulospedidos WHERE estado >= 0 AND estado <= 6 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS produccion,
            (
                SELECT IFNULL(COUNT(*),0)
                FROM   articulospedidos WHERE estado = 7 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS entregados,
            (
                SELECT IFNULL(COUNT(*),0)
                FROM   articulospedidos WHERE estado = 6 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS parcial");
        }
        else if ($tipo_filtro == "bandejas"){
            $val = mysqli_query($con, "SELECT  (
                SELECT IFNULL(SUM(cant_bandejas),0)
                FROM   articulospedidos WHERE estado = -10 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS pendientes,
            (
                SELECT IFNULL(SUM(cant_bandejas),0)
                FROM   articulospedidos WHERE estado >= 0 AND estado <= 6 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS produccion,
            (
                SELECT IFNULL(SUM(cant_bandejas),0)
                FROM   articulospedidos WHERE estado = 7 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS entregados,
            (
                SELECT IFNULL(SUM(cant_bandejas),0)
                FROM   articulospedidos WHERE estado = 6 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS parcial");
        }
        else if ($tipo_filtro == "plantas"){
            $val = mysqli_query($con, "SELECT  (
                SELECT IFNULL(SUM(cant_plantas),0)
                FROM   articulospedidos WHERE estado = -10 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS pendientes,
            (
                SELECT IFNULL(SUM(cant_plantas),0)
                FROM   articulospedidos WHERE estado >= 0 AND estado <= 6 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS produccion,
            (
                SELECT IFNULL(SUM(cant_plantas),0)
                FROM   articulospedidos WHERE estado = 7 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS entregados,
            (
                SELECT IFNULL(SUM(cant_plantas),0)
                FROM   articulospedidos WHERE estado = 6 AND eliminado IS NULL $tipo_pedido_busqueda
            ) AS parcial");
        }
        

        if (mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            echo json_encode(array(
                "pendientes" => $re["pendientes"],
                "produccion" => $re["produccion"],
                "entregados" => $re["entregados"],
                "parcial" => $re["parcial"],
            ));
        }
        
        
    } catch (\Throwable $th) {
        throw $th;
    }
}