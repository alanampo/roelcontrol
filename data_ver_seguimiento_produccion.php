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
    $query = "SELECT u.id as id_usuario, u.nombre_real as nombre_completo
              FROM usuarios u
              LEFT JOIN permisos p ON u.id = p.id_usuario
              WHERE u.tipo_usuario = 1 AND u.inhabilitado = 0
              GROUP BY u.id, u.nombre_real
              HAVING FIND_IN_SET('seguimiento_produccion', GROUP_CONCAT(p.modulo)) > 0
              ORDER BY u.nombre_real";
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
else if ($consulta == "obtener_meta_usuario") {
    $id_usuario = intval($_POST["id_usuario"]);

    $query = "SELECT meta_semanal, fecha_desde
              FROM metas_produccion
              WHERE id_usuario = $id_usuario AND activo = 1
              ORDER BY id DESC
              LIMIT 1";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $row = mysqli_fetch_assoc($val);
        echo json_encode($row);
    } else {
        echo json_encode(array("meta_semanal" => null));
    }
}
else if ($consulta == "establecer_meta") {
    $id_usuario = intval($_POST["id_usuario"]);
    $meta_semanal = intval($_POST["meta_semanal"]);
    $fecha_desde = mysqli_real_escape_string($con, $_POST["fecha_desde"]);

    // Desactivar metas anteriores
    $query_desactivar = "UPDATE metas_produccion SET activo = 0 WHERE id_usuario = $id_usuario";
    mysqli_query($con, $query_desactivar);

    // Insertar nueva meta
    $query = "INSERT INTO metas_produccion (id_usuario, meta_semanal, fecha_desde, activo)
              VALUES ($id_usuario, $meta_semanal, '$fecha_desde', 1)";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "obtener_registros_diarios") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);

    $query = "SELECT rpd.*,
              vp.nombre as variedad_nombre,
              (SELECT COUNT(*) FROM evidencias_produccion WHERE id_registro = rpd.id) as num_evidencias
              FROM registro_produccion_diario rpd
              LEFT JOIN variedades_producto vp ON rpd.id_variedad = vp.id
              WHERE rpd.id_usuario = $id_usuario
              AND MONTH(rpd.fecha) = $mes
              AND YEAR(rpd.fecha) = $anio
              ORDER BY rpd.fecha DESC, rpd.turno ASC";

    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $registros = array();
        while ($row = mysqli_fetch_assoc($val)) {
            // Obtener evidencias si hay
            if ($row['num_evidencias'] > 0) {
                $id_reg = $row['id'];
                $query_ev = "SELECT ruta_imagen, tamano_kb FROM evidencias_produccion WHERE id_registro = $id_reg";
                $val_ev = mysqli_query($con, $query_ev);
                $evidencias = array();
                while ($ev = mysqli_fetch_assoc($val_ev)) {
                    $evidencias[] = $ev;
                }
                $row['evidencias'] = $evidencias;
            } else {
                $row['evidencias'] = array();
            }
            array_push($registros, $row);
        }
        echo json_encode($registros);
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "validar_registro") {
    $id_registro = intval($_POST["id_registro"]);
    $id_admin = intval($_SESSION["id_usuario"]);

    $query = "UPDATE registro_produccion_diario
              SET validado = 1, fecha_validacion = NOW(), validado_por = $id_admin
              WHERE id = $id_registro";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "obtener_evidencias_dia") {
    $id_usuario = intval($_POST["id_usuario"]);
    $fecha = mysqli_real_escape_string($con, $_POST["fecha"]);

    $query = "SELECT rpd.id, rpd.turno, rpd.cantidad_plantines, rpd.estado, rpd.motivo_rechazo,
              vp.nombre as variedad_nombre, rpd.descripcion_manual, rpd.item_tipo,
              ep.id as id_evidencia, ep.ruta_imagen, ep.tamano_kb
              FROM registro_produccion_diario rpd
              LEFT JOIN variedades_producto vp ON rpd.id_variedad = vp.id
              LEFT JOIN evidencias_produccion ep ON ep.id_registro = rpd.id
              WHERE rpd.id_usuario = $id_usuario
              AND rpd.fecha = '$fecha'
              ORDER BY rpd.turno, ep.id";

    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $registros = array();
        $registro_actual = null;

        while ($row = mysqli_fetch_assoc($val)) {
            $id_registro = $row['id'];

            if (!isset($registros[$id_registro])) {
                $registros[$id_registro] = array(
                    'id' => $id_registro,
                    'turno' => $row['turno'],
                    'cantidad' => $row['cantidad_plantines'],
                    'descripcion' => $row['item_tipo'] == 'variedad' ? $row['variedad_nombre'] : $row['descripcion_manual'],
                    'estado' => $row['estado'],
                    'motivo_rechazo' => $row['motivo_rechazo'],
                    'evidencias' => array()
                );
            }

            if ($row['id_evidencia']) {
                $registros[$id_registro]['evidencias'][] = array(
                    'ruta_imagen' => $row['ruta_imagen'],
                    'tamano_kb' => $row['tamano_kb']
                );
            }
        }

        echo json_encode(array_values($registros));
    } else {
        echo json_encode(array());
    }
}
else if ($consulta == "guardar_comentarios") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);
    $comentarios = mysqli_real_escape_string($con, $_POST["comentarios"]);

    // Buscar si existe algún registro para este usuario/mes/año
    $query_check = "SELECT id FROM seguimiento_produccion_trabajadoras
                    WHERE id_usuario = $id_usuario AND mes = $mes AND anio = $anio
                    LIMIT 1";
    $check = mysqli_query($con, $query_check);

    if (mysqli_num_rows($check) > 0) {
        // Actualizar comentarios en todos los registros de ese usuario/mes
        $query = "UPDATE seguimiento_produccion_trabajadoras
                  SET comentarios = '$comentarios'
                  WHERE id_usuario = $id_usuario AND mes = $mes AND anio = $anio";
    } else {
        // Crear un registro solo para guardar comentarios
        $query = "INSERT INTO seguimiento_produccion_trabajadoras
                  (mes, anio, id_usuario, item_tipo, precio, comentarios)
                  VALUES ($mes, $anio, $id_usuario, 'variedad', 0, '$comentarios')";
    }

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con);
    }
}
else if ($consulta == "obtener_comentarios") {
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $id_usuario = intval($_POST["id_usuario"]);

    $query = "SELECT comentarios FROM seguimiento_produccion_trabajadoras
              WHERE id_usuario = $id_usuario AND mes = $mes AND anio = $anio
              AND comentarios IS NOT NULL AND comentarios != ''
              LIMIT 1";
    $val = mysqli_query($con, $query);

    if ($val && mysqli_num_rows($val) > 0) {
        $row = mysqli_fetch_assoc($val);
        echo json_encode(array("comentarios" => $row["comentarios"]));
    } else {
        echo json_encode(array("comentarios" => ""));
    }
}
else if ($consulta == "aprobar_registro") {
    $id_registro = intval($_POST["id_registro"]);
    $id_admin = intval($_SESSION["id_usuario"]);

    // Obtener datos del registro
    $query_reg = "SELECT * FROM registro_produccion_diario WHERE id = $id_registro";
    $val_reg = mysqli_query($con, $query_reg);

    if (!$val_reg || mysqli_num_rows($val_reg) == 0) {
        echo json_encode(array("error" => "Registro no encontrado"));
        mysqli_close($con);
        exit;
    }

    $reg = mysqli_fetch_assoc($val_reg);
    $estado_anterior = $reg['estado'];

    // Actualizar estado a aprobado
    $query = "UPDATE registro_produccion_diario
              SET estado = 'aprobado', validado = 1, fecha_validacion = NOW(), validado_por = $id_admin
              WHERE id = $id_registro";

    if (mysqli_query($con, $query)) {
        // Si estaba rechazado, volver a sumar en la tabla mensual
        if ($estado_anterior == 'rechazado') {
            actualizarTablaMensual($con, $reg, 'sumar');
        }

        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("error" => mysqli_error($con)));
    }
}
else if ($consulta == "rechazar_registro") {
    $id_registro = intval($_POST["id_registro"]);
    $motivo = isset($_POST["motivo"]) && !empty($_POST["motivo"])
        ? "'" . mysqli_real_escape_string($con, $_POST["motivo"]) . "'"
        : "NULL";
    $id_admin = intval($_SESSION["id_usuario"]);

    // Obtener datos del registro
    $query_reg = "SELECT * FROM registro_produccion_diario WHERE id = $id_registro";
    $val_reg = mysqli_query($con, $query_reg);

    if (!$val_reg || mysqli_num_rows($val_reg) == 0) {
        echo json_encode(array("error" => "Registro no encontrado"));
        mysqli_close($con);
        exit;
    }

    $reg = mysqli_fetch_assoc($val_reg);
    $estado_anterior = $reg['estado'];

    // Actualizar estado a rechazado
    $query = "UPDATE registro_produccion_diario
              SET estado = 'rechazado', motivo_rechazo = $motivo, validado = 0, validado_por = $id_admin, fecha_validacion = NOW()
              WHERE id = $id_registro";

    if (mysqli_query($con, $query)) {
        // Si estaba aprobado o pendiente, restar de la tabla mensual
        if ($estado_anterior != 'rechazado') {
            actualizarTablaMensual($con, $reg, 'restar');
        }

        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("error" => mysqli_error($con)));
    }
}

// Función auxiliar para actualizar tabla mensual
function actualizarTablaMensual($con, $registro, $operacion) {
    $id_usuario = $registro['id_usuario'];
    $fecha = $registro['fecha'];
    $cantidad = intval($registro['cantidad_plantines']);
    $item_tipo = $registro['item_tipo'];
    $id_variedad = $registro['id_variedad'];
    $descripcion_manual = $registro['descripcion_manual'];

    $dia = intval(date('d', strtotime($fecha)));
    $mes = intval(date('m', strtotime($fecha)));
    $anio = intval(date('Y', strtotime($fecha)));
    $columna_dia = "dia_" . str_pad($dia, 2, '0', STR_PAD_LEFT);

    // Construir WHERE para encontrar la fila
    if ($item_tipo == 'variedad') {
        $where = "id_variedad = $id_variedad AND item_tipo = 'variedad'";
    } else {
        $desc_escaped = mysqli_real_escape_string($con, $descripcion_manual);
        $where = "descripcion_manual = '$desc_escaped' AND item_tipo = 'manual'";
    }

    // Buscar la fila
    $query_buscar = "SELECT id, $columna_dia FROM seguimiento_produccion_trabajadoras
                     WHERE id_usuario = $id_usuario AND mes = $mes AND anio = $anio AND $where
                     LIMIT 1";
    $res = mysqli_query($con, $query_buscar);

    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $id_fila = $row['id'];
        $cantidad_actual = intval($row[$columna_dia]);

        if ($operacion == 'sumar') {
            $nueva_cantidad = $cantidad_actual + $cantidad;
        } else { // restar
            $nueva_cantidad = max(0, $cantidad_actual - $cantidad);
        }

        $query_update = "UPDATE seguimiento_produccion_trabajadoras
                        SET $columna_dia = $nueva_cantidad
                        WHERE id = $id_fila";
        mysqli_query($con, $query_update);
    }
}

if ($consulta == "obtener_estados_dias") {
    $id_usuario = intval($_POST["id_usuario"]);
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);

    // Obtener todos los registros del mes
    $query = "SELECT
              rpd.id_variedad,
              rpd.descripcion_manual,
              rpd.item_tipo,
              DAY(rpd.fecha) as dia,
              rpd.estado,
              rpd.fecha
              FROM registro_produccion_diario rpd
              WHERE rpd.id_usuario = $id_usuario
              AND MONTH(rpd.fecha) = $mes
              AND YEAR(rpd.fecha) = $anio
              ORDER BY rpd.fecha DESC, rpd.id DESC";

    $result = mysqli_query($con, $query);
    $estados_por_item = array();

    if ($result && mysqli_num_rows($result) > 0) {
        $registros_procesados = array(); // Para rastrear qué días ya procesamos por item

        while ($row = mysqli_fetch_assoc($result)) {
            // Crear clave única para el item
            $item_key = $row['item_tipo'] == 'variedad'
                ? "var_" . $row['id_variedad']
                : "man_" . md5($row['descripcion_manual']);

            $dia = intval($row['dia']);
            $dia_key = "dia_" . str_pad($dia, 2, '0', STR_PAD_LEFT);

            // Inicializar array del item si no existe
            if (!isset($estados_por_item[$item_key])) {
                $estados_por_item[$item_key] = array();
                $registros_procesados[$item_key] = array();
            }

            // Solo tomar el primer registro de cada día (el más reciente por el ORDER BY DESC)
            if (!isset($registros_procesados[$item_key][$dia])) {
                $estados_por_item[$item_key][$dia_key] = $row['estado'];
                $registros_procesados[$item_key][$dia] = true;
            }
        }
    }

    echo json_encode($estados_por_item);
}

if ($consulta == "aprobar_todos_item") {
    $id_usuario = intval($_POST["id_usuario"]);
    $mes = intval($_POST["mes"]);
    $anio = intval($_POST["anio"]);
    $item_tipo = mysqli_real_escape_string($con, $_POST["item_tipo"]);
    $id_admin = intval($_SESSION["id_usuario"]);

    $where_item = "";
    if ($item_tipo == "variedad") {
        $id_variedad = intval($_POST["id_variedad"]);
        $where_item = "id_variedad = $id_variedad";
    } else {
        $descripcion_manual = mysqli_real_escape_string($con, $_POST["descripcion_manual"]);
        $where_item = "descripcion_manual = '$descripcion_manual'";
    }

    // Obtener todos los días del mes que tienen registros de este item
    $query_dias = "SELECT DISTINCT DATE(fecha) as fecha_dia
                   FROM registro_produccion_diario
                   WHERE id_usuario = $id_usuario
                   AND item_tipo = '$item_tipo'
                   AND $where_item
                   AND MONTH(fecha) = $mes
                   AND YEAR(fecha) = $anio
                   ORDER BY fecha_dia";

    $result_dias = mysqli_query($con, $query_dias);
    $ids_a_aprobar = array();

    if ($result_dias && mysqli_num_rows($result_dias) > 0) {
        while ($row_dia = mysqli_fetch_assoc($result_dias)) {
            $fecha_dia = $row_dia['fecha_dia'];

            // Para cada día, obtener el registro MÁS RECIENTE (por fecha completa con hora)
            $query_mas_reciente = "SELECT id, estado
                                   FROM registro_produccion_diario
                                   WHERE id_usuario = $id_usuario
                                   AND item_tipo = '$item_tipo'
                                   AND $where_item
                                   AND DATE(fecha) = '$fecha_dia'
                                   ORDER BY fecha DESC, id DESC
                                   LIMIT 1";

            $result_reciente = mysqli_query($con, $query_mas_reciente);
            if ($result_reciente && mysqli_num_rows($result_reciente) > 0) {
                $row_reciente = mysqli_fetch_assoc($result_reciente);
                $id_registro = $row_reciente['id'];
                $estado_actual = $row_reciente['estado'];

                // Solo aprobar si no está ya aprobado
                if ($estado_actual != 'aprobado') {
                    $ids_a_aprobar[] = $id_registro;
                }
            }
        }
    }

    // Aprobar todos los registros encontrados
    $aprobados = 0;
    foreach ($ids_a_aprobar as $id_registro) {
        // Obtener estado anterior para sincronización
        $query_anterior = "SELECT estado FROM registro_produccion_diario WHERE id = $id_registro";
        $res_anterior = mysqli_query($con, $query_anterior);
        $estado_anterior = 'pendiente';
        if ($res_anterior && mysqli_num_rows($res_anterior) > 0) {
            $row_anterior = mysqli_fetch_assoc($res_anterior);
            $estado_anterior = $row_anterior['estado'];
        }

        // Actualizar estado a aprobado
        $query_aprobar = "UPDATE registro_produccion_diario
                         SET estado = 'aprobado', validado = 1, fecha_validacion = NOW(), validado_por = $id_admin
                         WHERE id = $id_registro";

        if (mysqli_query($con, $query_aprobar)) {
            $aprobados++;

            // Si estaba rechazado, volver a sumar en la tabla mensual
            if ($estado_anterior == 'rechazado') {
                // Obtener datos del registro para sincronización
                $query_datos = "SELECT fecha, cantidad_plantines, id_variedad, descripcion_manual, item_tipo
                               FROM registro_produccion_diario
                               WHERE id = $id_registro";
                $res_datos = mysqli_query($con, $query_datos);

                if ($res_datos && mysqli_num_rows($res_datos) > 0) {
                    $datos = mysqli_fetch_assoc($res_datos);
                    actualizarTablaMensual($con, $datos, 'sumar');
                }
            }
        }
    }

    echo json_encode(array(
        "success" => true,
        "aprobados" => $aprobados,
        "mensaje" => "$aprobados registro(s) aprobado(s)"
    ));
}

mysqli_close($con);
?>
