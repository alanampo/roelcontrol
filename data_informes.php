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

if ($consulta == "exportar_clientes") {
    $tipo = $_POST["tipo"];
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $field = array(
        "ID",
        "Nombre",   
        "Domicilio",
        "Teléfono",
        "E-Mail",
        "RUT",
        "Comuna",
        "Ciudad",
    );

    $cadena = "SELECT c.id_cliente as id_cliente, 
                    UPPER(c.razon_social) as razon_social, 
                    c.nombre as nombre, 
                    c.domicilio as domicilio, 
                    c.telefono, 
                    c.mail as mail, 
                    c.rut as rut, 
                    com.ciudad as ciudad, 
                    com.nombre as comuna, 
                    com.id as id_comuna  
                    FROM clientes c 
                    LEFT JOIN comunas com ON c.comuna = com.id
                    ORDER BY nombre ASC;";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {

        try {
            $fecha = date('Ymd_His', time());
            $filename = 'clientes.csv';
            $fp = fopen($filename, 'w');
            $array = str_replace('"', '', $field);    
            fputs($fp, implode(';', $array)."\n");
            while ($ww = mysqli_fetch_array($val)) {
                $field = array(
                    $ww["id_cliente"],
                    $ww["nombre"],
                    $ww["domicilio"],
                    $ww["telefono"],
                    $ww["mail"],
                    $ww["rut"],
                    $ww["comuna"],
                    $ww["ciudad"],
                );
                $array = str_replace('"', '', $field);    
                fputs($fp, implode(';', $array)."\n");
            }
            fclose($fp);
            echo "success";
        } catch (\Throwable$th) {
            throw $th;
        }
    }
}
else if ($consulta == "exportar_productos") {
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $field = array(
        "ID",
        "Variedad",
        "Precio",
        "Días Producción",        
    );

    $cadena = "SELECT v.id as id_variedad, t.id as id_tipo, t.nombre as nombre_tipo,
          v.nombre as nombre_variedad, t.codigo, v.precio, v.id_interno, v.dias_produccion
          FROM variedades_producto v INNER JOIN tipos_producto t ON t.id = v.id_tipo
          WHERE v.eliminada IS NULL
          ";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {

        try {
            $filename = 'productos.csv';
            $fp = fopen($filename, 'w');
            $array = str_replace('"', '', $field);  
            fputs($fp, implode(';', $array)."\n");  
            while ($ww = mysqli_fetch_array($val)) {

                $id = "$ww[codigo]".str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT);
                $field = array(
                    $id,
                    $ww["nombre_variedad"],
                    (int)$ww["precio"],
                    $ww["dias_produccion"]
                );
                $array = str_replace('"', '', $field);    
                fputs($fp, implode(';', $array)."\n");
            }
            fclose($fp);
            echo "success";
        } catch (\Throwable$th) {
            throw $th;
        }
    }
}

else if ($consulta == "exportar_semillas") {
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $field = array(
        "ID",
        "Variedad/Especie",
        "Cantidad Actual",
        "Marca",        
        "Proveedor",        
        "Cliente",
        "Precio",                
        "Costo",        
    );

    $cadena = "SELECT
    s.id_stock,
    s.cantidad,
    UPPER(s.codigo) as codigo,
    s.porcentaje,
    DATE_FORMAT(s.fecha, '%d/%m/%y') as fecha_stock,
    DATE_FORMAT(s.fecha, '%y%m%d') as fecha_stock_raw,
    c.nombre as nombre_cliente,
    c.id_cliente,
    v.nombre as nombre_variedad,
    e.nombre as nombre_especie,
    t.codigo as tipo_semilla,
    m.nombre as nombre_marca,
    p.nombre as nombre_proveedor,
    sr.id_retiro,
    ROUND(IFNULL(s.precio,0)/s.cantidad) as costo,
    ROUND((IFNULL(s.precio,0)/s.cantidad)*1.19) as costo_iva,
    s.precio
    FROM stock_semillas s
    INNER JOIN clientes c ON c.id_cliente = s.id_cliente
    LEFT JOIN variedades_producto v ON v.id = s.id_variedad
    LEFT JOIN especies_provistas e ON e.id = s.id_especie
    INNER JOIN tipos_producto t ON t.id = v.id_tipo OR t.id = e.id_tipo
    INNER JOIN semillas_marcas m ON m.id = s.id_marca
    INNER JOIN semillas_proveedores p ON p.id = s.id_proveedor
    LEFT JOIN stock_semillas_retiros sr ON sr.id_stock = s.id_stock
    ;
          ";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {

        try {
            $filename = 'semillas.csv';
            $fp = fopen($filename, 'w');
            $array = str_replace('"', '', $field);  
            fputs($fp, implode(';', $array)."\n");  
            while ($ww = mysqli_fetch_array($val)) {
                $aux = mysqli_query($con, "SELECT IFNULL(SUM(cantidad),0) as canti FROM stock_semillas_retiros WHERE id_stock = $ww[id_stock];");
                $canti = $ww["cantidad"];
                if (mysqli_num_rows($aux) > 0) {
                    $re = mysqli_fetch_assoc($aux);
                    $canti = $canti - $re["canti"];
                }
                $cantidad = $canti;
                
                if ($ww["nombre_variedad"] != NULL){
                    $producto = "$ww[nombre_variedad] ($ww[tipo_semilla]) [$ww[fecha_stock]] $ww[porcentaje]%";
                }
                else{
                    $producto = "$ww[nombre_especie]  ($ww[tipo_semilla])[$ww[fecha_stock]] $ww[porcentaje]";
                }   
                $precio = $ww["precio"];
                $costo = $ww["costo"];
                
                
                $field = array(
                    $ww["codigo"],
                    $producto,
                    (int)$cantidad,
                    $ww["nombre_marca"],
                    $ww["nombre_proveedor"],
                    $ww["nombre_cliente"],
                    $ww["precio"],
                    $ww["costo"]
                );
                $array = str_replace('"', '', $field);    
                fputs($fp, implode(';', $array)."\n");
            }
            fclose($fp);
            echo "success";
        } catch (\Throwable$th) {
            throw $th;
        }
    }
}
else if ($consulta == "exportar_pedidos") {
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $field = array(
        "ID",
        "Producto",
        "Cliente",
        "Cantidad Plantas",        
        "Cantidad Bandejas",        
        "Bandeja",
        "Fecha Ingreso",                
        "Fecha Entrega Aprox",        
        "Estado"        
    );

    $cadena = "SELECT
    t.nombre as nombre_tipo,
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
    ORDER BY ap.id DESC LIMIT 1000
    ";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {

        try {
            $filename = 'pedidos.csv';
            $fp = fopen($filename, 'w');
            $array = str_replace('"', '', $field);
            fputs($fp, implode(';', $array)."\n");    
            while ($ww = mysqli_fetch_array($val)) {
                $id_cliente = $ww['id_cliente'];
                
                $id_artpedido = $ww['id_artpedido'];
                $fecha = $ww['fecha_pedido_raw'];
                $tipo = "";

                $especie = $ww["nombre_especie"] ? $ww["nombre_especie"] : "";
                $producto = trim("$ww[nombre_variedad] ($ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . ") $especie");

                $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
                $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);

                $cliente = $ww['nombre_cliente'];

                $fecha_ingreso = $ww['fecha_ingreso_original'];

                $fecha_pedido = $ww["fecha_pedido"];
                $estado = getLabelEstado($ww["estado"]);

                $field = array(
                    $id_producto,
                    $producto,
                    (int)$ww["cant_plantas"],
                    (int)$ww["cant_bandejas"],
                    $ww["tipo_bandeja"],
                    $ww["fecha_ingreso_solicitada"],
                    $ww["fecha_entrega_solicitada"],
                    $estado,
                );
                $array = str_replace('"', '', $field);    
                fputs($fp, implode(';', $array)."\n");
            }
            fclose($fp);
            echo "success";
        } catch (\Throwable$th) {
            throw $th;
        }
    }
}


$query = "SELECT
        t.nombre as nombre_tipo,
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
        AND
        ";