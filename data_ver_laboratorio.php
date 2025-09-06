<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

header('Content-type: text/html; charset=utf-8');

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");

$consulta = $_POST["consulta"];
if ($consulta == "cargar_esquejes" || $consulta == "cargar_semillas") {
    try {
        $busqueda = mysqli_escape_string($con, $_POST["busqueda"]);
        $strbusqueda = strlen($busqueda) >= 3 ? " AND (v.nombre REGEXP '$busqueda' OR e.nombre REGEXP '$busqueda' OR c.nombre REGEXP '$busqueda')" : "";
        $arraypedidos = array();
        if ($consulta == "cargar_esquejes") {
            $tipo_producto = "('E','HE')";
        } else if ($consulta == "cargar_semillas") {
            $tipo_producto = "('S','HS')";
        }

        $cadenaselect = "SELECT t.nombre as nombre_tipo, v.nombre as nombre_variedad, c.nombre as nombre_cliente, p.fecha, p.id_pedido, ap.id as id_artpedido, ap.cant_plantas, ap.cant_bandejas, ap.tipo_bandeja, t.codigo, v.id_interno, ap.estado, p.id_interno as id_pedido_interno,
        ap.problema, ap.observacionproblema, c.id_cliente, ap.observacion, u.iniciales, ap.id_especie, e.nombre as nombre_especie, ap.en_revision, ur.nombre_real as nombre_responsable
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        LEFT JOIN usuarios ur ON ur.id = ap.id_usuario_responsable_laboratorio
        WHERE ap.eliminado IS NULL AND ap.estado >= 0 AND ap.estado <= 6 AND t.codigo IN $tipo_producto
        $strbusqueda
        ORDER BY p.fecha ASC;
        ";

        $val = mysqli_query($con, $cadenaselect);

        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arraypedidos, array(
                    "nombre_tipo" => $re["nombre_tipo"],
                    "nombre_variedad" => $re["nombre_variedad"],
                    "nombre_cliente" => $re["nombre_cliente"],
                    "nombre_especie" => $re["nombre_especie"],
                    "fecha" => $re["fecha"],
                    "cant_plantas" => $re["cant_plantas"],
                    "cant_bandejas" => $re["cant_bandejas"],
                    "tipo_bandeja" => $re["tipo_bandeja"],
                    "codigo" => $re["codigo"],
                    "id_interno" => $re["id_interno"],
                    "estado" => $re["estado"],
                    "id_pedido" => $re["id_pedido"],
                    "id_artpedido" => $re["id_artpedido"],
                    "id_pedido_interno" => $re["id_pedido_interno"],
                    "problema" => $re["problema"],
                    "observacionproblema" => $re["observacionproblema"],
                    "observacion" => $re["observacion"],
                    "iniciales" => $re["iniciales"],
                    "id_especie" => $re["id_especie"],
                    "id_cliente" => $re["id_cliente"],
                    "en_revision" => $re["en_revision"],
                    "nombre_responsable" => $re["nombre_responsable"],
                    "query" => $cadenaselect
                ));

            }
            $mijson = json_encode($arraypedidos);
            echo $mijson;
        }
    } catch (\Throwable $th) {
        throw $th;
    }
} else if ($consulta == "cargar_pedidos") {
    $tipo = $_POST["tipo"];
    try {
        $busqueda = mysqli_escape_string($con, $_POST["busqueda"]);
        $strbusqueda = strlen($busqueda) >= 3 ? " AND (v.nombre REGEXP '$busqueda' OR e.nombre REGEXP '$busqueda' OR c.nombre REGEXP '$busqueda')" : "";
        $arraypedidos = array();

        // Determinar el valor del atributo según el tipo
        $valor_atributo = "";
        switch ($tipo) {
            case "exterior":
                $valor_atributo = "PLANTAS DE EXTERIOR";
                break;
            case "interior":
                $valor_atributo = "PLANTAS DE INTERIOR";
                break;
            case "vivero":
                $valor_atributo = "PLANTAS PARA JARDÍN";
                break;
            case "packs":
                $valor_atributo = "PACKS";
                break;
            default:
                $valor_atributo = "";
        }

        // Condición para filtrar por atributo (normalizada para manejar acentos)
        $condicion_atributo = "";
        if ($valor_atributo != "") {
            if ($valor_atributo == "PACKS") {
                $condicion_atributo = " AND a.nombre = 'TIPO DE PLANTA' AND av.valor IN ('PACKS INTERIOR', 'PACKS EXTERIOR') ";
            } else {
                $condicion_atributo = " AND UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.nombre, 'Á', 'A'), 'É', 'E'), 'Í', 'I'), 'Ó', 'O'), 'Ú', 'U')) = 'TIPO DE PLANTA' 
                                   AND UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(av.valor, 'Á', 'A'), 'É', 'E'), 'Í', 'I'), 'Ó', 'O'), 'Ú', 'U')) = UPPER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE('$valor_atributo', 'Á', 'A'), 'É', 'E'), 'Í', 'I'), 'Ó', 'O'), 'Ú', 'U'))";
            }
        }

        if ($tipo == "invitro"){
            $condicion_atributo.=" AND t.nombre = 'INVITRO'";
        }

        $cadenaselect = "SELECT t.nombre as nombre_tipo, v.nombre as nombre_variedad, c.nombre as nombre_cliente, p.fecha, p.id_pedido, ap.id as id_artpedido, ap.cant_plantas, ap.cant_bandejas, ap.tipo_bandeja, t.codigo, v.id_interno, ap.estado, p.id_interno as id_pedido_interno,
        ap.problema, ap.observacionproblema, c.id_cliente, ap.observacion, u.iniciales, ap.id_especie, e.nombre as nombre_especie, ap.en_revision, ur.nombre_real as nombre_responsable
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        LEFT JOIN usuarios ur ON ur.id = ap.id_usuario_responsable_laboratorio
        INNER JOIN atributos_valores_variedades avv ON avv.id_variedad = v.id
        INNER JOIN atributos_valores av ON av.id = avv.id_atributo_valor
        INNER JOIN atributos a ON a.id = av.id_atributo
        WHERE ap.eliminado IS NULL AND ap.estado >= 0 AND ap.estado <= 6
        $condicion_atributo
        $strbusqueda
        AND (ap.sector != 'plantinera' OR ap.sector IS NULL)
        ORDER BY p.fecha ASC;
        ";

        $val = mysqli_query($con, $cadenaselect);

        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arraypedidos, array(
                    "nombre_tipo" => $re["nombre_tipo"],
                    "nombre_variedad" => $re["nombre_variedad"],
                    "nombre_cliente" => $re["nombre_cliente"],
                    "nombre_especie" => $re["nombre_especie"],
                    "fecha" => $re["fecha"],
                    "cant_plantas" => $re["cant_plantas"],
                    "cant_bandejas" => $re["cant_bandejas"],
                    "tipo_bandeja" => $re["tipo_bandeja"],
                    "codigo" => $re["codigo"],
                    "id_interno" => $re["id_interno"],
                    "estado" => $re["estado"],
                    "id_pedido" => $re["id_pedido"],
                    "id_artpedido" => $re["id_artpedido"],
                    "id_pedido_interno" => $re["id_pedido_interno"],
                    "problema" => $re["problema"],
                    "observacionproblema" => $re["observacionproblema"],
                    "observacion" => $re["observacion"],
                    "iniciales" => $re["iniciales"],
                    "id_especie" => $re["id_especie"],
                    "id_cliente" => $re["id_cliente"],
                    "en_revision" => $re["en_revision"],
                    "nombre_responsable" => $re["nombre_responsable"],
                    "query" => $cadenaselect
                ));
            }
            $mijson = json_encode($arraypedidos);
            echo $mijson;
        }
    } catch (\Throwable $th) {
        throw $th;
    }
} else if ($consulta == "cargar_detalle_pedido") {
    $id_artpedido = $_POST['id_artpedido'];

    try {
        $arraypedido = array();

        $cadenaselect = "SELECT
        t.nombre as nombre_tipo,
        v.nombre as nombre_variedad,
        p.fecha,
        p.observaciones as observacion_pedido,
        p.id_pedido,
        ap.id as id_artpedido,
        ap.cant_plantas,
        ap.cant_semillas,
        ap.cant_bandejas,
        ap.cant_bandejas_usadas,
        ap.cant_bandejas_nuevas,
        ap.tipo_bandeja,
        t.codigo,
        v.id_interno,
        ap.estado,
        p.id_interno as id_pedido_interno,
        ap.problema,
        ap.observacion,
        ap.observacionproblema,
        ap.fecha_etapa1,
        ap.fecha_etapa2,
        ap.fecha_etapa3,
        ap.fecha_etapa4,
        ap.fecha_etapa5,
        ap.fecha_entrega_real,
        ap.fecha_ingreso,
        u.iniciales,
        e.nombre as nombre_especie,
        (SELECT IFNULL(SUM(e.cantidad), 0) FROM entregas e WHERE e.id_artpedido = ap.id) as cantidad_entregada,
        (select * from (SELECT
                CONCAT(id_tipo, '', id_interno) as mesada
                FROM mesadas_productos mp
                INNER JOIN mesadas m ON mp.id_mesada = m.id_mesada
                INNER JOIN articulospedidos ap ON ap.id = mp.id_artpedido
                WHERE  ap.id = $id_artpedido GROUP BY mp.id_mesada
                ) t) as mesa
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        WHERE ap.id = $id_artpedido;
        ";

        $val = mysqli_query($con, $cadenaselect);

        if (mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);

            $querysemillas = "
            SELECT
            ss.id_cliente as id_cliente_semillas,
            sp.id_stock_semillas as id_stock_semillas,
            sp.cantidad as cantidad_semillas_stock,
            ss.codigo as codigo_semillas,
            ss.porcentaje as porcentaje_semillas,
            ms.nombre as marca_semillas,
            ps.nombre as proveedor_semillas
            FROM semillas_pedidos sp
            INNER JOIN stock_semillas ss ON ss.id_stock = sp.id_stock_semillas
            INNER JOIN semillas_marcas ms ON ms.id = ss.id_marca
            INNER JOIN semillas_proveedores ps ON ps.id = ss.id_proveedor
            WHERE sp.id_artpedido = $re[id_artpedido]
            ";

            $valsemillas = mysqli_query($con, $querysemillas);
            $datasemillas = null;
            if (mysqli_num_rows($valsemillas) > 0) {
                $datasemillas = array();
                while ($data = mysqli_fetch_array($valsemillas)) {
                    array_push($datasemillas, array(
                        "id_cliente" => $data["id_cliente_semillas"],
                        "id_stock" => $data["id_stock_semillas"],
                        "cantidad" => $data["cantidad_semillas_stock"],
                        "codigo" => $data["codigo_semillas"],
                        "porcentaje" => $data["porcentaje_semillas"],
                        "marca" => $data["marca_semillas"],
                        "proveedor" => $data["proveedor_semillas"],
                    ));
                }
            }

            $arraypedido = array(
                "nombre_tipo" => $re["nombre_tipo"],
                "nombre_variedad" => $re["nombre_variedad"],
                "nombre_especie" => $re["nombre_especie"],
                "fecha" => $re["fecha"],
                "cant_plantas" => $re["cant_plantas"],
                "cant_bandejas" => $re["cant_bandejas"],
                "cant_bandejas_nuevas" => $re["cant_bandejas_nuevas"],
                "cant_bandejas_usadas" => $re["cant_bandejas_usadas"],
                "tipo_bandeja" => $re["tipo_bandeja"],
                "codigo" => $re["codigo"],
                "id_interno" => $re["id_interno"],
                "estado" => $re["estado"],
                "id_pedido" => $re["id_pedido"],
                "id_artpedido" => $re["id_artpedido"],
                "id_pedido_interno" => $re["id_pedido_interno"],
                "problema" => $re["problema"],
                "observacionproblema" => $re["observacionproblema"],
                "observacion" => $re["observacion"],
                "observacion_pedido" => $re["observacion_pedido"],
                "fecha_etapa1" => $re["fecha_etapa1"],
                "fecha_etapa2" => $re["fecha_etapa2"],
                "fecha_etapa3" => $re["fecha_etapa3"],
                "fecha_etapa4" => $re["fecha_etapa4"],
                "fecha_etapa5" => $re["fecha_etapa5"],
                "fecha_entrega_real" => $re["fecha_entrega_real"],
                "fecha_ingreso" => $re["fecha_ingreso"],
                "mesada" => $re["mesa"],
                "cant_semillas" => $re["cant_semillas"],
                "semillas" => $datasemillas,
                "cantidad_entregada" => $re["cantidad_entregada"],
            );
            echo json_encode($arraypedido);
        } else {
            echo "nodata";
        }
    } catch (\Throwable $th) {
        throw $th;
    }
} else if ($consulta == "guardar_observaciones") {
    $id_artpedido = $_POST["id_artpedido"];
    $observaciones = mysqli_escape_string($con, $_POST["observaciones"]);

    try {
        $query = "UPDATE articulospedidos SET observacion = UPPER('$observaciones') WHERE id = $id_artpedido;";
        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_problema") {
    $id_artpedido = $_POST["id_artpedido"];
    $problema = mysqli_escape_string($con, $_POST["problema"]);
    try {
        if (strlen($problema) > 1) {
            $query = "UPDATE articulospedidos SET observacionproblema = UPPER('$problema'), problema = 1 WHERE id = $id_artpedido;";
        } else {
            $query = "UPDATE articulospedidos SET observacionproblema = NULL WHERE id = $id_artpedido;";
        }
        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "solucionar_problema") {
    $id_artpedido = $_POST["id_artpedido"];
    try {
        $query = "UPDATE articulospedidos SET problema = NULL WHERE id = $id_artpedido;";
        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "enviar_a_produccion") {
    $id_artpedido = $_POST["id_artpedido"];
    try {
        $query = "UPDATE articulospedidos SET estado = 0 WHERE id = $id_artpedido;";
        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "enviar_stock") {
    $id_artpedido = $_POST["id_artpedido"];
    $cantidad_enviar = isset($_POST["cantidad_enviar"]) ? (int) $_POST["cantidad_enviar"] : null;

    try {
        $val = mysqli_query($con, "SELECT v.nombre as nombre_variedad, v.precio, v.id_interno as id_variedad, t.codigo, ap.cant_plantas,
                                (SELECT IFNULL(MIN(ape.tipo_bandeja), 162) FROM articulospedidos ape WHERE ape.id_variedad = ap.id_variedad) as tipo_bandeja,
                                (SELECT IFNULL(SUM(e.cantidad), 0) FROM entregas e WHERE e.id_artpedido = ap.id) as cantidad_entregada
                                FROM articulospedidos ap 
                                INNER JOIN variedades_producto v 
                                ON v.id = ap.id_variedad
                                INNER JOIN tipos_producto t
                                ON t.id = v.id_tipo
                                WHERE ap.id = $id_artpedido;");

        if ($val && mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            $errors = array();
            mysqli_autocommit($con, false);

            // Calcular la cantidad disponible (total - entregada)
            $cantidad_disponible = $re["cant_plantas"] - $re["cantidad_entregada"];

            // Validar que haya cantidad disponible
            if ($cantidad_disponible <= 0) {
                echo "error: No hay cantidad disponible para enviar a stock";
                mysqli_close($con);
                return;
            }

            // Si no se especifica cantidad_enviar, usar toda la disponible
            if ($cantidad_enviar === null) {
                $cantidad_enviar = $cantidad_disponible;
            }

            // Validar que la cantidad a enviar no exceda la disponible
            if ($cantidad_enviar > $cantidad_disponible) {
                echo "error: La cantidad a enviar ($cantidad_enviar) excede la disponible ($cantidad_disponible)";
                mysqli_close($con);
                return;
            }

            // Validar que la cantidad a enviar sea mayor a 0
            if ($cantidad_enviar <= 0) {
                echo "error: La cantidad a enviar debe ser mayor a 0";
                mysqli_close($con);
                return;
            }

            $id_producto = $re["codigo"] . str_pad($re["id_variedad"], 2, '0', STR_PAD_LEFT);

            // Actualizar estado del artículo
            $query = "UPDATE articulospedidos SET estado = 8, fecha_stock = NOW() WHERE id = $id_artpedido;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }

            // Insertar en stock_productos con la cantidad especificada
            $query = "INSERT INTO stock_productos (
                id_artpedido,
                fecha,
                cantidad,
                cantidadinicial
            ) VALUES (
                $id_artpedido,
                NOW(),
                $cantidad_enviar,
                $cantidad_enviar
            )";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con) . $query;
            }

            // Si la cantidad enviada es menor a la disponible, registrar el resto como entregado
            if ($cantidad_enviar < $cantidad_disponible) {
                $cantidad_restante = $cantidad_disponible - $cantidad_enviar;
                $query_entrega = "INSERT INTO entregas (
                    id_artpedido,
                    cantidad,
                    fecha,
                    comentario
                ) VALUES (
                    $id_artpedido,
                    $cantidad_restante,
                    NOW(),
                    'Cantidad no enviada a stock - Enviado: $cantidad_enviar de $cantidad_disponible disponibles'
                )";
                if (!mysqli_query($con, $query_entrega)) {
                    $errors[] = "Error al registrar cantidad restante: " . mysqli_error($con);
                }
            }

            if (count($errors) === 0) {
                if (mysqli_commit($con)) {
                    echo "success";
                } else {
                    mysqli_rollback($con);
                    echo "error: No se pudo confirmar la transacción";
                }
            } else {
                mysqli_rollback($con);
                echo "error: " . implode(", ", $errors);
            }
        } else {
            echo "error: No se encontró el artículo del pedido";
        }

        mysqli_close($con);
    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: $th";
    }
} else if ($consulta == "cambiar_etapa") {
    $id_artpedido = $_POST["id_artpedido"];
    $productos = json_decode($_POST["productos"], true);
    $etapa = $_POST["etapa"];
    $errors = array();
    mysqli_autocommit($con, false);
    for ($i = 0; $i < count($productos); $i++) {
        if ($etapa == -10 || $etapa == "-10") { //DEVOLVER A PENDIENTES (DESDE PRODUCCION)
            $query = "UPDATE articulospedidos SET estado = $etapa, fecha_etapa1 = NULL, fecha_etapa2 = NULL, fecha_etapa3 = NULL, fecha_etapa4 = NULL, fecha_etapa5 = NULL, en_revision = NULL WHERE id = $productos[$i];";
        } else if ($etapa == 0 || $etapa == "0") {
            $query = "UPDATE articulospedidos SET estado = $etapa, fecha_etapa1 = NULL, fecha_etapa2 = NULL, fecha_etapa3 = NULL, fecha_etapa4 = NULL, fecha_etapa5 = NULL WHERE id = $productos[$i];";
        } else if ($etapa == 1 || $etapa == "1") { // ETAPA 1 - ASIGNAR RESPONSABLE
            $id_usuario = $_SESSION["id_usuario"];
            $query = "UPDATE articulospedidos SET estado = $etapa, fecha_etapa$etapa = NOW(), en_revision = NULL, id_usuario_responsable_laboratorio = $id_usuario WHERE id = $productos[$i];";
        } else { // ETAPAS 2-5 - ASIGNAR SOLO SI NO TIENE RESPONSABLE
            $id_usuario = $_SESSION["id_usuario"];
            $query = "UPDATE articulospedidos SET estado = $etapa, fecha_etapa$etapa = NOW(), en_revision = NULL, id_usuario_responsable_laboratorio = COALESCE(id_usuario_responsable_laboratorio, $id_usuario) WHERE id = $productos[$i];";
        }
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
        if ($etapa == -10 || $etapa == "-10") { //DEVOLVER A PENDIENTES
            $query = "DELETE FROM stock_bandejas_retiros WHERE id_artpedido = $productos[$i];";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
        if ($etapa == -10 || $etapa == "-10") { //DEVOLVER A PENDIENTES
            $query = "DELETE FROM stock_semillas_retiros WHERE id_artpedido = $productos[$i];";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
    }

    if (count($errors) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($errors);
    }
    mysqli_close($con);
} else if ($consulta == "enviar_plantinera") {
    $productos = json_decode($_POST["productos"], true);
    $errors = array();
    mysqli_autocommit($con, false);
    for ($i = 0; $i < count($productos); $i++) {
        $query = "UPDATE articulospedidos SET estado = 0, sector = 'plantinera', fecha_etapa1 = NULL, fecha_etapa2 = NULL, fecha_etapa3 = NULL, fecha_etapa4 = NULL, fecha_etapa5 = NULL WHERE id = $productos[$i];";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
    }

    if (count($errors) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($errors);
    }
    mysqli_close($con);
} else if ($consulta == "revision_contaminacion") {
    $productos = json_decode($_POST["productos"], true);
    $errors = array();
    mysqli_autocommit($con, false);
    for ($i = 0; $i < count($productos); $i++) {
        $query = "UPDATE articulospedidos SET en_revision = 1 WHERE id = $productos[$i];";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
    }

    if (count($errors) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($errors);
    }
    mysqli_close($con);
} else if ($consulta == "cancelar_pedido") {
    $id_artpedido = $_POST["id_artpedido"];
    try {
        $errors = array();
        mysqli_autocommit($con, false);
        $query = "UPDATE articulospedidos SET estado = -1 WHERE id = $id_artpedido;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
        $query = "DELETE FROM stock_bandejas_retiros WHERE id_artpedido = $id_artpedido;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_rollback($con);
            print_r($errors);
        }
        mysqli_close($con);

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "eliminar_pedido") {
    $id_artpedido = $_POST["id_artpedido"];
    try {
        mysqli_autocommit($con, false);
        $errors = array();
        $query = "UPDATE articulospedidos SET estado = -1, eliminado = 1 WHERE id = $id_artpedido;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
        $query = "DELETE FROM stock_bandejas_retiros WHERE id_artpedido = $id_artpedido;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_rollback($con);
            print_r($errors);
        }
        mysqli_close($con);

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "modificar_cantidad") {
    $id_artpedido = $_POST["id_artpedido"];
    $cantidad = $_POST["cantidad"];
    try {
        if (mysqli_query($con, "UPDATE articulospedidos SET cant_plantas = '$cantidad' WHERE id = $id_artpedido;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "cargar_plantas_entregadas") {
    $id_artpedido = $_POST["id_artpedido"];
    try {
        $val = mysqli_query($con, "SELECT IFNULL(SUM(cantidad), 0) as cantidad FROM entregas WHERE id_artpedido = $id_artpedido;");

        if ($val && mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            echo "entregado:$re[cantidad]";
        }

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_entrega") {
    $id_artpedido = $_POST["id_artpedido"];
    $cantidad_entrega = $_POST["cantidad_entrega"];
    $falta_entregar = $_POST["falta_entregar"];
    $errors = array();
    try {
        mysqli_autocommit($con, false);
        if ((int) $cantidad_entrega >= (int) $falta_entregar) { //ENTREGA COMPLETA
            $query = "INSERT INTO entregas (cantidad, id_artpedido, fecha, tipo) VALUES ('$cantidad_entrega', $id_artpedido, NOW(), 0);";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
            $query = "UPDATE articulospedidos SET estado = 7, fecha_entrega_real = NOW() WHERE id = $id_artpedido";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        } else { //ENTREGA PARCIAL
            $query = "INSERT INTO entregas (cantidad, id_artpedido, fecha, tipo) VALUES ('$cantidad_entrega', $id_artpedido, NOW(), 1);";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
            $query = "UPDATE articulospedidos SET estado = 6, fecha_entrega_real = NOW() WHERE id = $id_artpedido";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_rollback($con);
            print_r($errors);
        }
        mysqli_close($con);

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_control_0") {
    $id_artpedido = $_POST["id_artpedido"];
    $id_interno = $_POST["id_interno"];
    $fecha_siembra = $_POST["fecha_siembra"];
    if ($fecha_siembra != null) {
        $fecha = explode("/", $fecha_siembra);
        $fecha_siembra = "$fecha[2]-$fecha[1]-$fecha[0]";
    }
    $bandejas_sembradas = strlen($_POST["bandejas_sembradas"]) > 0 ? $_POST["bandejas_sembradas"] : "NULL";
    $t_s_am = strlen($_POST["t_s_am"]) > 0 ? $_POST["t_s_am"] : "NULL";
    $t_s_pm = strlen($_POST["t_s_pm"]) > 0 ? $_POST["t_s_pm"] : "NULL";
    $t_a_am = strlen($_POST["t_a_am"]) > 0 ? $_POST["t_a_am"] : "NULL";
    $t_a_pm = strlen($_POST["t_a_pm"]) > 0 ? $_POST["t_a_pm"] : "NULL";

    $observacion = mysqli_real_escape_string($con, $_POST["observacion"]);

    try {
        $val = mysqli_query($con, "SELECT * FROM control_0 WHERE id_interno = $id_interno AND id_artpedido = $id_artpedido;");
        if ($val && mysqli_num_rows($val) > 0) {
            $query = "UPDATE control_0 SET
            fecha_siembra = '$fecha_siembra',
            bandejas_sembradas = $bandejas_sembradas,
            t_s_am = $t_s_am,
            t_s_pm = $t_s_pm,
            t_a_am = $t_a_am,
            t_a_pm = $t_a_pm,
            observacion = '$observacion'
            WHERE id_artpedido = $id_artpedido
            AND id_interno = $id_interno;
            ";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } else {
            $query =
                "INSERT INTO control_0 (
                    id_artpedido,
                    id_interno,
                    fecha_siembra,
                    bandejas_sembradas,
                    t_s_am,
                    t_s_pm,
                    t_a_am,
                    t_a_pm,
                    observacion
                ) VALUES (
                    $id_artpedido,
                    $id_interno,
                    '$fecha_siembra',
                    $bandejas_sembradas,
                    $t_s_am,
                    $t_s_pm,
                    $t_a_am,
                    $t_a_pm,
                    '$observacion'
                )";

            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        }
    } catch (\Throwable $th) {
        echo "error: $th";
    }
} else if ($consulta == "cargar_control_0") {
    $id_artpedido = $_POST["id_artpedido"];

    try {
        $arraycontroles = array();
        $val = mysqli_query($con, "SELECT id_interno, bandejas_sembradas, t_s_am, t_s_pm, t_a_am, t_a_pm, observacion, DATE_FORMAT(fecha_siembra, '%d/%m/%Y') as fecha_siembra FROM control_0 WHERE id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arraycontroles, array(
                    "id_interno" => $re["id_interno"],
                    "bandejas_sembradas" => $re["bandejas_sembradas"],
                    "t_s_am" => $re["t_s_am"],
                    "t_s_pm" => $re["t_s_pm"],
                    "t_a_am" => $re["t_a_am"],
                    "t_a_pm" => $re["t_a_pm"],
                    "observacion" => $re["observacion"],
                    "fecha_siembra" => $re["fecha_siembra"],
                ));
            }
            echo json_encode($arraycontroles);
        }

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_control_1_a_3") {
    $id_artpedido = $_POST["id_artpedido"];
    $id_interno = $_POST["id_interno"];
    $fecha_control = $_POST["fecha_control"];
    if ($fecha_control != null) {
        $fecha = explode("/", $fecha_control);
        $fecha_control = "$fecha[2]-$fecha[1]-$fecha[0]";
    }
    $cantidad_bandejas = strlen($_POST["cantidad_bandejas"]) > 0 ? $_POST["cantidad_bandejas"] : "NULL";
    $porcentaje1 = strlen($_POST["porcentaje1"]) > 0 ? $_POST["porcentaje1"] : "NULL";

    $meson = strlen($_POST["meson"]) > 0 ? "'" . $_POST["meson"] . "'" : "NULL";
    $etapa = strlen($_POST["etapa"]) > 0 ? $_POST["etapa"] : "NULL";

    $t_s_am = strlen($_POST["t_s_am"]) > 0 ? $_POST["t_s_am"] : "NULL";
    $t_s_pm = strlen($_POST["t_s_pm"]) > 0 ? $_POST["t_s_pm"] : "NULL";
    $t_a_am = strlen($_POST["t_a_am"]) > 0 ? $_POST["t_a_am"] : "NULL";
    $t_a_pm = strlen($_POST["t_a_pm"]) > 0 ? $_POST["t_a_pm"] : "NULL";

    $observacion = mysqli_real_escape_string($con, $_POST["observacion"]);

    try {
        $val = mysqli_query($con, "SELECT * FROM control_$etapa WHERE id_interno = $id_interno AND id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            $query = "UPDATE control_$etapa SET
            fecha_control = '$fecha_control',
            cantidad_bandejas = $cantidad_bandejas,
            porcentaje_1 = $porcentaje1,
            t_s_am = $t_s_am,
            t_s_pm = $t_s_pm,
            t_a_am = $t_a_am,
            t_a_pm = $t_a_pm,
            meson = $meson,
            observacion = '$observacion'
            WHERE id_artpedido = $id_artpedido
            AND id_interno = $id_interno;
            ";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } else {
            $query = "INSERT INTO control_$etapa (
                id_artpedido,
                id_interno,
                fecha_control,
                cantidad_bandejas,
                porcentaje_1,
                meson,
                t_s_am,
                t_s_pm,
                t_a_am,
                t_a_pm,
                observacion
            ) VALUES (
                $id_artpedido,
                $id_interno,
                '$fecha_control',
                $cantidad_bandejas,
                $porcentaje1,
                $meson,
                $t_s_am,
                $t_s_pm,
                $t_a_am,
                $t_a_pm,
                '$observacion'
            )";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        }

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "cargar_control_1" || $consulta == "cargar_control_2" || $consulta == "cargar_control_3") {
    $id_artpedido = $_POST["id_artpedido"];
    $etapa = $_POST["etapa"];
    try {
        $arraycontroles = array();
        $val = mysqli_query($con, "SELECT id_interno, cantidad_bandejas, fecha_control, porcentaje_1, meson, t_s_am, t_s_pm, t_a_am, t_a_pm, observacion, DATE_FORMAT(fecha_control, '%d/%m/%Y') as fecha_control FROM control_$etapa WHERE id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arraycontroles, array(
                    "id_interno" => $re["id_interno"],
                    "cantidad_bandejas" => $re["cantidad_bandejas"],
                    "meson" => $re["meson"],
                    "porcentaje_1" => $re["porcentaje_1"],
                    "t_s_am" => $re["t_s_am"],
                    "t_s_pm" => $re["t_s_pm"],
                    "t_a_am" => $re["t_a_am"],
                    "t_a_pm" => $re["t_a_pm"],
                    "observacion" => $re["observacion"],
                    "fecha_control" => $re["fecha_control"],
                ));
            }
            echo json_encode($arraycontroles);
        }

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_control_4") {
    $id_artpedido = $_POST["id_artpedido"];
    $id_interno = $_POST["id_interno"];
    $fecha_repique = $_POST["fecha_repique"];
    if ($fecha_repique != null) {
        $fecha = explode("/", $fecha_repique);
        $fecha_repique = "$fecha[2]-$fecha[1]-$fecha[0]";
    }
    $bandejas_repicadas = strlen($_POST["bandejas_repicadas"]) > 0 ? $_POST["bandejas_repicadas"] : "NULL";
    $bandejas_perdidas = strlen($_POST["bandejas_perdidas"]) > 0 ? $_POST["bandejas_perdidas"] : "NULL";
    $t_s_am = strlen($_POST["t_s_am"]) > 0 ? $_POST["t_s_am"] : "NULL";
    $t_s_pm = strlen($_POST["t_s_pm"]) > 0 ? $_POST["t_s_pm"] : "NULL";
    $t_a_am = strlen($_POST["t_a_am"]) > 0 ? $_POST["t_a_am"] : "NULL";
    $t_a_pm = strlen($_POST["t_s_pm"]) > 0 ? $_POST["t_s_pm"] : "NULL";
    $meson = strlen($_POST["meson"]) > 0 ? "'" . $_POST["meson"] . "'" : "NULL";

    $observacion = mysqli_real_escape_string($con, $_POST["observacion"]);

    try {
        $val = mysqli_query($con, "SELECT * FROM control_4 WHERE id_interno = $id_interno AND id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            $query = "UPDATE control_4 SET
            fecha_repique = '$fecha_repique',
            bandejas_repicadas = $bandejas_repicadas,
            bandejas_perdidas = $bandejas_perdidas,
            meson = $meson,
            t_s_am = $t_s_am,
            t_s_pm = $t_s_pm,
            t_a_am = $t_a_am,
            t_a_pm = $t_a_pm,
            observacion = '$observacion'
            WHERE id_artpedido = $id_artpedido
            AND id_interno = $id_interno;
            ";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } else {
            $query = "INSERT INTO control_4 (
                id_artpedido,
                id_interno,
                fecha_repique,
                bandejas_repicadas,
                bandejas_perdidas,
                meson,
                t_s_am,
                t_s_pm,
                t_a_am,
                t_a_pm,
                observacion
            ) VALUES (
                $id_artpedido,
                $id_interno,
                '$fecha_repique',
                $bandejas_repicadas,
                $bandejas_perdidas,
                $meson,
                $t_s_am,
                $t_s_pm,
                $t_a_am,
                $t_a_pm,
                '$observacion'
            )";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        }

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "cargar_control_4") {
    $id_artpedido = $_POST["id_artpedido"];

    try {
        $arraycontroles = array();
        $val = mysqli_query($con, "SELECT id_interno, bandejas_repicadas, bandejas_perdidas, meson, t_s_am, t_s_pm, t_a_am, t_a_pm, observacion, DATE_FORMAT(fecha_repique, '%d/%m/%Y') as fecha_repique FROM control_4 WHERE id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arraycontroles, array(
                    "id_interno" => $re["id_interno"],
                    "bandejas_repicadas" => $re["bandejas_repicadas"],
                    "bandejas_perdidas" => $re["bandejas_perdidas"],
                    "meson" => $re["meson"],
                    "t_s_am" => $re["t_s_am"],
                    "t_s_pm" => $re["t_s_pm"],
                    "t_a_am" => $re["t_a_am"],
                    "t_a_pm" => $re["t_a_pm"],
                    "observacion" => $re["observacion"],
                    "fecha_repique" => $re["fecha_repique"],
                ));
            }
            echo json_encode($arraycontroles);
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_control_5") {
    $id_artpedido = $_POST["id_artpedido"];
    $id_interno = $_POST["id_interno"];
    $fecha_disponibilidad = $_POST["fecha_disponibilidad"];
    if ($fecha_disponibilidad != null) {
        $fecha = explode("/", $fecha_disponibilidad);
        $fecha_disponibilidad = "$fecha[2]-$fecha[1]-$fecha[0]";
    }
    $fecha_entrega = $_POST["fecha_entrega"];
    if ($fecha_entrega != null) {
        $fecha = explode("/", $fecha_entrega);
        $fecha_entrega = "$fecha[2]-$fecha[1]-$fecha[0]";
    }
    $bandejas_finales = strlen($_POST["bandejas_finales"]) > 0 ? $_POST["bandejas_finales"] : "NULL";
    $meson = strlen($_POST["meson"]) > 0 ? "'" . $_POST["meson"] . "'" : "NULL";
    $estado = mysqli_real_escape_string($con, $_POST["estado"]);
    $observacion = mysqli_real_escape_string($con, $_POST["observacion"]);

    try {
        $val = mysqli_query($con, "SELECT * FROM control_5 WHERE id_interno = $id_interno AND id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            $query = "UPDATE control_5 SET
            fecha_disponibilidad = '$fecha_disponibilidad',
            fecha_entrega = '$fecha_entrega',
            bandejas_finales = $bandejas_finales,
            meson = $meson,
            estado = '$estado',
            observacion = '$observacion'
            WHERE id_artpedido = $id_artpedido
            AND id_interno = $id_interno;
            ";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } else {
            $query = "INSERT INTO control_5 (
                id_artpedido,
                id_interno,
                fecha_disponibilidad,
                fecha_entrega,
                bandejas_finales,
                meson,
                estado,
                observacion
            ) VALUES (
                $id_artpedido,
                $id_interno,
                '$fecha_disponibilidad',
                '$fecha_entrega',
                $bandejas_finales,
                $meson,
                '$estado',
                '$observacion'
            )";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "cargar_control_5") {
    $id_artpedido = $_POST["id_artpedido"];

    try {
        $arraycontroles = array();
        $val = mysqli_query($con, "SELECT id_interno, bandejas_finales, fecha_disponibilidad, fecha_entrega, meson, observacion, estado, DATE_FORMAT(fecha_disponibilidad, '%d/%m/%Y') as fecha_disponibilidad, DATE_FORMAT(fecha_entrega, '%d/%m/%Y') as fecha_entrega FROM control_5 WHERE id_artpedido = $id_artpedido;");
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arraycontroles, array(
                    "id_interno" => $re["id_interno"],
                    "bandejas_finales" => $re["bandejas_finales"],
                    "estado" => $re["estado"],
                    "meson" => $re["meson"],
                    "observacion" => $re["observacion"],
                    "fecha_disponibilidad" => $re["fecha_disponibilidad"],
                    "fecha_entrega" => $re["fecha_entrega"],
                ));
            }
            echo json_encode($arraycontroles);
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "modificar_cliente") {
    $id_artpedido = $_POST["id_artpedido"];
    $id_cliente = $_POST["id_nuevo_cliente"];
    $id_usuario = $_SESSION["id_usuario"];

    $first_day = date('Y-m-01', strtotime('this month'));
    $last_day = date('Y-m-01', strtotime('first day of +1 month'));

    try {
        $errors = array();

        $valor = mysqli_query($con, "SELECT IFNULL(MAX(ID_PEDIDO)+1, 1) as maximo FROM pedidos");
        if (mysqli_num_rows($valor) > 0) {
            $ww = mysqli_fetch_assoc($valor);

            $id_pedido = $ww["maximo"];
            if ((int) $id_pedido > 0) {
                mysqli_autocommit($con, false);

                $query = "INSERT INTO pedidos (ID_PEDIDO, id_cliente, id_usuario, observaciones, fecha, id_interno) VALUES ($id_pedido, $id_cliente, $id_usuario, NULL, NOW(),
                    (select * from (SELECT IFNULL(MAX(id_interno)+1, 1) FROM pedidos WHERE fecha BETWEEN '$first_day' AND '$last_day') t)
                  )"
                ;
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                $query = "UPDATE articulospedidos SET id_pedido = $id_pedido WHERE id = $id_artpedido;";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                if (count($errors) === 0) {
                    if (mysqli_commit($con)) {
                        echo "success";
                    } else {
                        mysqli_rollback($con);
                    }
                } else {
                    mysqli_rollback($con);
                    print_r($errors);
                }
                mysqli_close($con);
            } else {
                echo "Error al guardar el pedido. Intentalo de nuevo";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "marcar_entregado") {
    $id_artpedido = $_POST["id_artpedido"];
    $query = "UPDATE articulospedidos SET estado = 7 WHERE id = $id_artpedido";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
}


function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}