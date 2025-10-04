<?php
// Script para actualizar la fecha de último contacto de los clientes
// basado en la última cotización, cotización directa o factura

include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");

// Verificar qué tablas existen y construir el query dinámicamente
$tables_check = mysqli_query($con, "SHOW TABLES");
$existing_tables = array();
while ($row = mysqli_fetch_array($tables_check)) {
    $existing_tables[] = $row[0];
}

// Construir las partes del UNION según las tablas que existan
$union_parts = array();

// Verificar cotizaciones
if (in_array('cotizaciones', $existing_tables)) {
    // Verificar estructura de cotizaciones
    $columns_cotiz = mysqli_query($con, "SHOW COLUMNS FROM cotizaciones");
    $has_id_cliente = false;
    $id_column = null;

    while ($col = mysqli_fetch_array($columns_cotiz)) {
        if ($col[0] == 'id_cliente') $has_id_cliente = true;
        if (stripos($col[0], 'id') === 0 && stripos($col[0], 'cotizacion') !== false) {
            $id_column = $col[0];
        }
    }

    if ($has_id_cliente) {
        $union_parts[] = "SELECT id_cliente, MAX(fecha) as fecha_ultimo FROM cotizaciones WHERE id_cliente IS NOT NULL GROUP BY id_cliente";
    }
}

// Verificar cotizaciones_directas
if (in_array('cotizaciones_directas', $existing_tables)) {
    $columns_cotiz_dir = mysqli_query($con, "SHOW COLUMNS FROM cotizaciones_directas");
    $has_id_cliente = false;
    $id_column_dir = null;

    while ($col = mysqli_fetch_array($columns_cotiz_dir)) {
        if ($col[0] == 'id_cliente') $has_id_cliente = true;
        if (stripos($col[0], 'id') === 0 && stripos($col[0], 'cotizacion') !== false) {
            $id_column_dir = $col[0];
        }
    }

    if ($has_id_cliente) {
        $union_parts[] = "SELECT id_cliente, MAX(fecha) as fecha_ultimo FROM cotizaciones_directas WHERE id_cliente IS NOT NULL GROUP BY id_cliente";
    }
}

// Verificar facturas
if (in_array('facturas', $existing_tables) && isset($id_column) && $has_id_cliente) {
    // Facturas vinculadas a cotizaciones
    $columns_fact = mysqli_query($con, "SHOW COLUMNS FROM facturas");
    while ($col = mysqli_fetch_array($columns_fact)) {
        if ($col[0] == 'id_cotizacion') {
            $union_parts[] = "SELECT c.id_cliente, MAX(f.fecha) as fecha_ultimo FROM facturas f INNER JOIN cotizaciones c ON f.id_cotizacion = c.$id_column WHERE c.id_cliente IS NOT NULL GROUP BY c.id_cliente";
        }
        if ($col[0] == 'id_cotizacion_directa' && isset($id_column_dir)) {
            $union_parts[] = "SELECT cd.id_cliente, MAX(f.fecha) as fecha_ultimo FROM facturas f INNER JOIN cotizaciones_directas cd ON f.id_cotizacion_directa = cd.$id_column_dir WHERE cd.id_cliente IS NOT NULL GROUP BY cd.id_cliente";
        }
    }
}

if (count($union_parts) == 0) {
    echo "error|No hay tablas válidas para calcular fecha de último contacto";
    exit;
}

$query = "UPDATE clientes c
LEFT JOIN (
    SELECT id_cliente, MAX(fecha_ultimo) as fecha_ultimo_contacto
    FROM (" . implode(" UNION ALL ", $union_parts) . ") AS todas_fechas
    GROUP BY id_cliente
) AS ultimas_fechas ON c.id_cliente = ultimas_fechas.id_cliente
SET c.fecha_ultimo_contacto = ultimas_fechas.fecha_ultimo_contacto
WHERE ultimas_fechas.fecha_ultimo_contacto IS NOT NULL";

if (mysqli_query($con, $query)) {
    $affected = mysqli_affected_rows($con);
    echo "success|Actualizado correctamente. $affected clientes actualizados.";
} else {
    echo "error|" . mysqli_error($con);
}

mysqli_close($con);
?>
