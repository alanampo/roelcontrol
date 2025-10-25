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
$id_usuario = intval($_SESSION['id_usuario']);

if ($consulta == "obtener_variedades") {
    $query = "SELECT id, nombre, precio_produccion
              FROM variedades_producto
              WHERE (eliminada IS NULL OR eliminada = 0)
              ORDER BY nombre";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $variedades = array();
        while ($row = mysqli_fetch_assoc($val)) {
            array_push($variedades, $row);
        }
        echo json_encode($variedades);
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "obtener_descripciones_manuales") {
    // Obtener descripciones manuales únicas usadas por este usuario
    $query = "SELECT DISTINCT descripcion_manual
              FROM registro_produccion_diario
              WHERE id_usuario = $id_usuario
              AND item_tipo = 'manual'
              AND descripcion_manual IS NOT NULL
              ORDER BY descripcion_manual";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $descripciones = array();
        while ($row = mysqli_fetch_assoc($val)) {
            array_push($descripciones, $row['descripcion_manual']);
        }
        echo json_encode($descripciones);
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "obtener_meta_semanal") {
    $query = "SELECT meta_semanal
              FROM metas_produccion
              WHERE id_usuario = $id_usuario AND activo = 1
              ORDER BY id DESC
              LIMIT 1";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $row = mysqli_fetch_assoc($val);
        echo json_encode(array("meta_semanal" => $row["meta_semanal"]));
    } else {
        // Meta por defecto si no hay configurada
        echo json_encode(array("meta_semanal" => 1000));
    }
}
else if ($consulta == "guardar_registro") {
    $fecha = mysqli_real_escape_string($con, $_POST["fecha"]);
    $turno = mysqli_real_escape_string($con, $_POST["turno"]);
    $item_tipo = mysqli_real_escape_string($con, $_POST["item_tipo"]);
    $cantidad = intval($_POST["cantidad_plantines"]);

    $id_variedad = "NULL";
    $descripcion_manual = "NULL";

    if ($item_tipo == "variedad") {
        $id_variedad = intval($_POST["id_variedad"]);
    } else {
        $descripcion_manual = "'" . mysqli_real_escape_string($con, $_POST["descripcion_manual"]) . "'";
    }

    $ubicacion = isset($_POST["ubicacion_lote"]) && !empty($_POST["ubicacion_lote"])
        ? "'" . mysqli_real_escape_string($con, $_POST["ubicacion_lote"]) . "'"
        : "NULL";
    $observaciones = isset($_POST["observaciones"]) && !empty($_POST["observaciones"])
        ? "'" . mysqli_real_escape_string($con, $_POST["observaciones"]) . "'"
        : "NULL";

    // Validar que no exista un registro duplicado
    $where_item = $item_tipo == "variedad"
        ? "id_variedad = $id_variedad"
        : "descripcion_manual = $descripcion_manual";

    $query_check = "SELECT id FROM registro_produccion_diario
                    WHERE id_usuario = $id_usuario
                    AND fecha = '$fecha'
                    AND turno = '$turno'
                    AND $where_item";
    $check = mysqli_query($con, $query_check);

    if (mysqli_num_rows($check) > 0) {
        echo json_encode(array("error" => "Ya existe un registro para este item en este turno"));
        mysqli_close($con);
        exit;
    }

    // Insertar registro diario
    $query = "INSERT INTO registro_produccion_diario
              (id_usuario, item_tipo, fecha, turno, id_variedad, descripcion_manual, cantidad_plantines, ubicacion_lote, observaciones)
              VALUES ($id_usuario, '$item_tipo', '$fecha', '$turno', $id_variedad, $descripcion_manual, $cantidad, $ubicacion, $observaciones)";

    if (mysqli_query($con, $query)) {
        $id_registro = mysqli_insert_id($con);

        // SINCRONIZACIÓN: Actualizar tabla mensual del admin
        $dia = intval(date('d', strtotime($fecha)));
        $mes = intval(date('m', strtotime($fecha)));
        $anio = intval(date('Y', strtotime($fecha)));
        $columna_dia = "dia_" . str_pad($dia, 2, '0', STR_PAD_LEFT);

        // Obtener precio de producción
        $precio = 0;
        if ($item_tipo == "variedad") {
            $query_precio = "SELECT precio_produccion FROM variedades_producto WHERE id = $id_variedad";
            $res_precio = mysqli_query($con, $query_precio);
            if ($res_precio && mysqli_num_rows($res_precio) > 0) {
                $row_precio = mysqli_fetch_assoc($res_precio);
                $precio = floatval($row_precio['precio_produccion']);
            }
        }

        // Verificar si existe la fila en la tabla mensual
        $where_mensual = $item_tipo == "variedad"
            ? "id_variedad = $id_variedad AND item_tipo = 'variedad'"
            : "descripcion_manual = $descripcion_manual AND item_tipo = 'manual'";

        $query_existe = "SELECT id, $columna_dia FROM seguimiento_produccion_trabajadoras
                        WHERE id_usuario = $id_usuario
                        AND mes = $mes
                        AND anio = $anio
                        AND $where_mensual";
        $res_existe = mysqli_query($con, $query_existe);

        if ($res_existe && mysqli_num_rows($res_existe) > 0) {
            // Actualizar cantidad sumando
            $row_existe = mysqli_fetch_assoc($res_existe);
            $cantidad_actual = intval($row_existe[$columna_dia]);
            $nueva_cantidad = $cantidad_actual + $cantidad;
            $id_fila = $row_existe['id'];

            $query_update = "UPDATE seguimiento_produccion_trabajadoras
                           SET $columna_dia = $nueva_cantidad
                           WHERE id = $id_fila";
            mysqli_query($con, $query_update);
        } else {
            // Insertar nueva fila
            $query_insert = "INSERT INTO seguimiento_produccion_trabajadoras
                           (mes, anio, id_usuario, item_tipo, id_variedad, descripcion_manual, precio, $columna_dia)
                           VALUES ($mes, $anio, $id_usuario, '$item_tipo', $id_variedad, $descripcion_manual, $precio, $cantidad)";
            mysqli_query($con, $query_insert);
        }

        echo json_encode(array("success" => true, "id_registro" => $id_registro));
    } else {
        echo json_encode(array("error" => mysqli_error($con)));
    }
}
else if ($consulta == "subir_evidencia") {
    $id_registro = intval($_POST["id_registro"]);

    // Validar que el registro pertenezca al usuario actual
    $query_check = "SELECT id FROM registro_produccion_diario
                    WHERE id = $id_registro AND id_usuario = $id_usuario";
    $check = mysqli_query($con, $query_check);

    if (mysqli_num_rows($check) == 0) {
        echo json_encode(array("error" => "Registro no encontrado"));
        mysqli_close($con);
        exit;
    }

    // Procesar archivo subido
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $filename = $_FILES['imagen']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode(array("error" => "Tipo de archivo no permitido"));
            mysqli_close($con);
            exit;
        }

        // Generar nombre único
        $timestamp = time();
        $random = rand(1000, 9999);
        $newFilename = "evidencia_{$id_usuario}_{$id_registro}_{$timestamp}_{$random}.{$ext}";
        $uploadPath = "uploads/evidencias/" . $newFilename;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadPath)) {
            $tamano_kb = round(filesize($uploadPath) / 1024);

            $query = "INSERT INTO evidencias_produccion (id_registro, ruta_imagen, tamano_kb)
                      VALUES ($id_registro, '$newFilename', $tamano_kb)";

            if (mysqli_query($con, $query)) {
                echo json_encode(array(
                    "success" => true,
                    "ruta" => $uploadPath,
                    "tamano_kb" => $tamano_kb
                ));
            } else {
                // Si falla el INSERT, eliminar archivo
                unlink($uploadPath);
                echo json_encode(array("error" => mysqli_error($con)));
            }
        } else {
            echo json_encode(array("error" => "Error al subir el archivo"));
        }
    } else {
        echo json_encode(array("error" => "No se recibió ninguna imagen"));
    }
}
else if ($consulta == "obtener_mi_produccion") {
    $fecha_desde = isset($_POST["fecha_desde"]) ? mysqli_real_escape_string($con, $_POST["fecha_desde"]) : date('Y-m-01');
    $fecha_hasta = isset($_POST["fecha_hasta"]) ? mysqli_real_escape_string($con, $_POST["fecha_hasta"]) : date('Y-m-t');

    $query = "SELECT rpd.*,
              vp.nombre as variedad_nombre,
              vp.precio_produccion,
              (SELECT COUNT(*) FROM evidencias_produccion WHERE id_registro = rpd.id) as num_evidencias
              FROM registro_produccion_diario rpd
              LEFT JOIN variedades_producto vp ON rpd.id_variedad = vp.id
              WHERE rpd.id_usuario = $id_usuario
              AND rpd.fecha BETWEEN '$fecha_desde' AND '$fecha_hasta'
              ORDER BY rpd.fecha DESC, rpd.turno DESC, rpd.id DESC";

    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $registros = array();
        while ($row = mysqli_fetch_assoc($val)) {
            // Obtener evidencias
            $id_reg = $row['id'];
            $query_ev = "SELECT ruta_imagen, tamano_kb FROM evidencias_produccion WHERE id_registro = $id_reg";
            $val_ev = mysqli_query($con, $query_ev);
            $evidencias = array();
            while ($ev = mysqli_fetch_assoc($val_ev)) {
                $evidencias[] = $ev;
            }
            $row['evidencias'] = $evidencias;

            array_push($registros, $row);
        }
        echo json_encode($registros);
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "obtener_estadisticas") {
    $hoy = date('Y-m-d');
    $inicio_semana = date('Y-m-d', strtotime('monday this week'));
    $fin_semana = date('Y-m-d', strtotime('sunday this week'));
    $inicio_mes = date('Y-m-01');
    $fin_mes = date('Y-m-t');

    // Producción diaria (hoy)
    $query_diaria = "SELECT COALESCE(SUM(cantidad_plantines), 0) as total
                     FROM registro_produccion_diario
                     WHERE id_usuario = $id_usuario AND fecha = '$hoy'";
    $val_diaria = mysqli_query($con, $query_diaria);
    $row_diaria = mysqli_fetch_assoc($val_diaria);
    $produccion_diaria = intval($row_diaria['total']);

    // Producción semanal
    $query_semanal = "SELECT COALESCE(SUM(cantidad_plantines), 0) as total
                      FROM registro_produccion_diario
                      WHERE id_usuario = $id_usuario
                      AND fecha BETWEEN '$inicio_semana' AND '$fin_semana'";
    $val_semanal = mysqli_query($con, $query_semanal);
    $row_semanal = mysqli_fetch_assoc($val_semanal);
    $produccion_semanal = intval($row_semanal['total']);

    // Producción mensual
    $query_mensual = "SELECT COALESCE(SUM(cantidad_plantines), 0) as total
                      FROM registro_produccion_diario
                      WHERE id_usuario = $id_usuario
                      AND fecha BETWEEN '$inicio_mes' AND '$fin_mes'";
    $val_mensual = mysqli_query($con, $query_mensual);
    $row_mensual = mysqli_fetch_assoc($val_mensual);
    $produccion_mensual = intval($row_mensual['total']);

    // Obtener meta semanal
    $query_meta = "SELECT meta_semanal FROM metas_produccion
                   WHERE id_usuario = $id_usuario AND activo = 1
                   ORDER BY id DESC LIMIT 1";
    $val_meta = mysqli_query($con, $query_meta);
    $meta_semanal = 1000; // Default
    if ($val_meta && mysqli_num_rows($val_meta) > 0) {
        $row_meta = mysqli_fetch_assoc($val_meta);
        $meta_semanal = intval($row_meta['meta_semanal']);
    }

    // Calcular progreso y bono estimado
    $progreso_semanal = $meta_semanal > 0 ? round(($produccion_semanal / $meta_semanal) * 100, 1) : 0;

    // Calcular bono (simplificado: $0.50 por plantín que exceda la meta)
    $bono_estimado = 0;
    if ($produccion_semanal > $meta_semanal) {
        $exceso = $produccion_semanal - $meta_semanal;
        $bono_estimado = $exceso * 0.50;
    }

    // Determinar indicador de cumplimiento
    $indicador = "red"; // Rojo por defecto
    if ($progreso_semanal >= 100) {
        $indicador = "green";
    } else if ($progreso_semanal >= 75) {
        $indicador = "yellow";
    }

    echo json_encode(array(
        "produccion_diaria" => $produccion_diaria,
        "produccion_semanal" => $produccion_semanal,
        "produccion_mensual" => $produccion_mensual,
        "meta_semanal" => $meta_semanal,
        "progreso_semanal" => $progreso_semanal,
        "bono_estimado" => $bono_estimado,
        "indicador" => $indicador,
        "fecha_actual" => $hoy,
        "rango_semana" => array(
            "inicio" => $inicio_semana,
            "fin" => $fin_semana
        )
    ));
}
else if ($consulta == "eliminar_registro") {
    $id_registro = intval($_POST["id_registro"]);

    // Validar que pertenezca al usuario
    $query_check = "SELECT id FROM registro_produccion_diario
                    WHERE id = $id_registro AND id_usuario = $id_usuario";
    $check = mysqli_query($con, $query_check);

    if (mysqli_num_rows($check) == 0) {
        echo "error: Registro no encontrado";
        mysqli_close($con);
        exit;
    }

    // Eliminar evidencias físicas primero
    $query_ev = "SELECT ruta_imagen FROM evidencias_produccion WHERE id_registro = $id_registro";
    $val_ev = mysqli_query($con, $query_ev);
    while ($ev = mysqli_fetch_assoc($val_ev)) {
        $filepath = "uploads/evidencias/" . $ev['ruta_imagen'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Las evidencias se eliminan automáticamente por CASCADE
    $query = "DELETE FROM registro_produccion_diario WHERE id = $id_registro";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
}

mysqli_close($con);
?>
