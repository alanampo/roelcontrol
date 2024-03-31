<?php

require "./class_lib/sesionSecurity.php";
error_reporting(0);

include './class_lib/class_conecta_mysql.php';
include './class_lib/funciones.php';
header('Content-type: text/html; charset=utf-8');

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}


$consulta = $_POST["consulta"];

if ($consulta == "guardar_size_etiquetas"){
    $table = $_POST["table"];
    $wEtiqueta = $_POST["wEtiqueta"];
    $hEtiqueta = $_POST["hEtiqueta"];
    $wQR = $_POST["wQR"];
    $hQR = $_POST["hQR"];
    $wLogo = $_POST["wLogo"];
    $hLogo = $_POST["hLogo"];
    $fontSize = $_POST["fontSize"];
    $unit = $_POST["unit"];

    $query = "SELECT * FROM config_etiquetas WHERE tbl_name = '$table';";
    $val = mysqli_query($con, $query);
    if (!$val){
        die("Error al guardar los cambios en la BD");
    }

    if (mysqli_num_rows($val) > 0){
        $query = "UPDATE config_etiquetas SET 
                    width = '$wEtiqueta', 
                    height = '$hEtiqueta',
                    qr_width = '$wQR',
                    qr_height = '$hQR',
                    logo_width = '$wLogo',
                    logo_height = '$hLogo',
                    font_size = '$fontSize',
                    unit = '$unit'
                    WHERE tbl_name = '$table'
                    ";
    }
    else{
        $query = "INSERT INTO config_etiquetas (
            width, height, qr_width, qr_height, logo_width, logo_height, font_size, tbl_name, unit
        )  VALUES (
            '$wEtiqueta',
            '$hEtiqueta',
            '$wQR',
            '$hQR',
            '$wLogo',
            '$hLogo',
            '$fontSize',
            '$table',
            '$unit'
        )";
    }

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con).$query);
    }
}
else if ($consulta == "get_size_etiquetas"){
    $table = $_POST["table"];
    $query = "SELECT * FROM config_etiquetas WHERE tbl_name = '$table' LIMIT 1;";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0){
        $ww = mysqli_fetch_assoc($val);
        echo json_encode(array(
            "wEtiqueta" => $ww["width"],
            "hEtiqueta" => $ww["height"],
            "wQR" => $ww["qr_width"],
            "hQR" => $ww["qr_height"],
            "wLogo" => $ww["logo_width"],
            "hLogo" => $ww["logo_height"],
            "fontSize" => $ww["font_size"],
            "unit" => $ww["unit"],
        ));
    }
    else{
        echo json_encode(array(
            "wEtiqueta" => 300,
            "hEtiqueta" => 300,
            "wQR" => 200,
            "hQR" => 200,
            "wLogo" => 200,
            "hLogo" => 200,
            "fontSize" => 20,
            "unit" => "px"
        ));
    }
}
else if ($consulta == "get_etiquetas") {
    $table = $_POST["table"];
    $productos = rtrim("(" . substr($_POST["productos"], 1), "]") . ")";
    if ($table == "articulospedidos"){
        $query = "SELECT
        t.nombre as nombre_tipo,
        ap.uniqid,
        v.nombre as nombre_variedad,
        c.nombre as nombre_cliente,
        t.id as id_tipo,
        c.id_cliente,
        p.fecha,
        p.id_pedido,
        ap.id as id_artpedido,
        ap.cant_plantas,
        ap.cant_bandejas,
        ap.tipo_bandeja,
        t.codigo,
        v.id_interno,
        ap.estado,
        p.id_interno as id_pedido_interno,
        DATE_FORMAT(p.fecha, '%m/%d') AS mes_dia,
        ap.problema,
        ap.observacionproblema,
        ap.observacion,
        p.id_pedido,
        u.iniciales,
        e.nombre as nombre_especie,
        ap.id_especie,
        ap.eliminado,
        DATE_FORMAT(p.fecha, '%Y%m%d') AS fecha_pedido_raw,
        DATE_FORMAT(p.fecha, '%d/%m/%y') as fecha_pedido,
        DATE_FORMAT(p.fecha, '%d/%m/%Y') as fecha_pedido_full,
        DATE_FORMAT(ap.fecha_ingreso, '%Y%m%d') AS fecha_ingreso_solicitada_raw,
        DATE_FORMAT(ap.fecha_ingreso, '%d/%m/%y') as fecha_ingreso_solicitada,
        DATE_FORMAT(ap.fecha_entrega, '%Y%m%d') AS fecha_entrega_solicitada_raw,
        DATE_FORMAT(ap.fecha_entrega, '%d/%m/%y') as fecha_entrega_solicitada
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        WHERE ap.eliminado IS NULL 

                AND ap.id IN $productos
                ORDER BY ap.id ASC
                ";
    }else{
        $query = "SELECT 
                b.nombre,
                b.uniqid,
                b.id
                FROM $table b
                WHERE b.id IN $productos                
                ";
    }
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $array = array();

        while ($ww = mysqli_fetch_array($val)) {
            $especie = $ww["nombre_especie"] ? ("- ".$ww["nombre_especie"]) : "";
            $producto = "$ww[nombre_variedad] ($ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . ") $especie";
            $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
            
            $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);
            
            $tmp = array(
                "id_artpedido" => $ww["id_artpedido"],
                "codigo" => $id_producto,
                "uniqid" => $ww["uniqid"],
                "nombre_producto" => $producto,
                "fecha_pedido" => $ww["fecha_pedido_full"]
                
            );
            array_push($array, $tmp);
        }
        echo json_encode($array);
    }
}
else if ($consulta == "generar_uniqid"){
    $query = "SELECT id FROM articulospedidos";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val)){
        $errors = array();
        mysqli_autocommit($con, FALSE);
        while ($ww=mysqli_fetch_array($val)){
            $id = $ww["id"];
            $uniqid = uniqid("prod", true);

            $query = "UPDATE articulospedidos SET uniqid = '$uniqid' WHERE id = $id";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                print_r(mysqli_error($con));
            }
        } else {
            mysqli_rollback($con);
            print_r($errors);
        }
        mysqli_close($con);
    }
    
}