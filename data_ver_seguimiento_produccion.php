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

if ($consulta == "obtener_usuarios") {
    $query = "SELECT id as id_usuario, nombre_real as nombre_completo FROM usuarios WHERE tipo_usuario = 1 ORDER BY nombre_Real";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $usuarios = array();
        while ($row = mysqli_fetch_assoc($val)) {
            array_push($usuarios, array(
                "id_usuario" => $row["id_usuario"],
                "nombre_completo" => $row["nombre_completo"]
            ));
        }
        echo json_encode($usuarios);
    }
}
else if ($consulta == "obtener_variedades") {
    $query = "SELECT vp.id as id_variedad, vp.nombre as nombre_variedad, vp.precio_produccion
              FROM variedades_producto vp
              WHERE vp.eliminada IS NULL OR vp.eliminada = 0
              ORDER BY vp.nombre";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $variedades = array();
        while ($row = mysqli_fetch_assoc($val)) {
            array_push($variedades, array(
                "id_variedad" => $row["id_variedad"],
                "nombre_variedad" => $row["nombre_variedad"],
                "precio_produccion" => $row["precio_produccion"]
            ));
        }
        echo json_encode($variedades);
    }
}
else if ($consulta == "obtener_produccion") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);

    $query = "SELECT spt.*,
              vp.nombre as descripcion_variedad
              FROM seguimiento_produccion_trabajadoras spt
              LEFT JOIN variedades_producto vp ON spt.id_variedad = vp.id
              WHERE spt.mes = $mes AND spt.anio = $anio AND spt.id_usuario = $id_usuario
              ORDER BY spt.id";

    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $datos = array();
        while ($row = mysqli_fetch_assoc($val)) {
            array_push($datos, $row);
        }
        echo json_encode($datos);
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "agregar_fila") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);
    $item_tipo = mysqli_real_escape_string($con, $_POST["item_tipo"]);
    $id_variedad = isset($_POST["id_variedad"]) && $_POST["id_variedad"] != "" ? intval($_POST["id_variedad"]) : "NULL";
    $descripcion_manual = isset($_POST["descripcion_manual"]) ? "'" . mysqli_real_escape_string($con, $_POST["descripcion_manual"]) . "'" : "NULL";
    $precio = floatval($_POST["precio"]);

    $query = "INSERT INTO seguimiento_produccion_trabajadoras
              (mes, anio, id_usuario, item_tipo, id_variedad, descripcion_manual, precio)
              VALUES ($mes, $anio, $id_usuario, '$item_tipo', $id_variedad, $descripcion_manual, $precio)";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "guardar_cambio") {
    $id = intval($_POST["id"]);
    $campo = mysqli_real_escape_string($con, $_POST["campo"]);
    $valor = mysqli_real_escape_string($con, $_POST["valor"]);

    // Validar que el campo sea válido
    $campos_validos = array("precio");
    for ($i = 1; $i <= 31; $i++) {
        $campos_validos[] = "dia_" . str_pad($i, 2, '0', STR_PAD_LEFT);
    }

    if (!in_array($campo, $campos_validos)) {
        echo "Campo no válido";
        exit;
    }

    $query = "UPDATE seguimiento_produccion_trabajadoras SET $campo = '$valor' WHERE id = $id";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "eliminar_fila") {
    $id = intval($_POST["id"]);

    $query = "DELETE FROM seguimiento_produccion_trabajadoras WHERE id = $id";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "obtener_pagos") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);

    $query = "SELECT * FROM pagos_produccion
              WHERE mes = $mes AND anio = $anio AND id_usuario = $id_usuario
              ORDER BY fecha_pago DESC";

    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $pagos = array();
        while ($row = mysqli_fetch_assoc($val)) {
            array_push($pagos, $row);
        }
        echo json_encode($pagos);
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "registrar_pago") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);
    $monto = floatval($_POST["monto"]);
    $fecha_pago = mysqli_real_escape_string($con, $_POST["fecha_pago"]);
    $observaciones = isset($_POST["observaciones"]) && !empty($_POST["observaciones"])
        ? "'" . mysqli_real_escape_string($con, $_POST["observaciones"]) . "'"
        : "NULL";

    $query = "INSERT INTO pagos_produccion (id_usuario, mes, anio, monto, fecha_pago, observaciones)
              VALUES ($id_usuario, $mes, $anio, $monto, '$fecha_pago', $observaciones)";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "eliminar_pago") {
    $id = intval($_POST["id"]);

    $query = "DELETE FROM pagos_produccion WHERE id = $id";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}

mysqli_close($con);
?>
