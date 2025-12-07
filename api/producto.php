<?php
// api/producto.php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

// Autenticación requerida
require_once __DIR__ . '/auth.php';
$authUser = authenticateRequest();

// Conexión a la base de datos
require_once __DIR__ . '/../class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    apiError('Error de conexión a la base de datos', 500);
}
mysqli_query($con, "SET NAMES 'utf8'");

// Debug opcional ?debug=1
$DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';

// ---------- Entrada ----------
$nombre = trim((string)($_GET['nombre'] ?? $_POST['nombre'] ?? ''));
if ($nombre === '') {
    apiError("Falta el parámetro 'nombre'");
}

$limit  = max(1, min(50, (int)($_GET['limit']  ?? $_POST['limit']  ?? 10)));
$offset = max(0, (int)($_GET['offset'] ?? $_POST['offset'] ?? 0));
$ivaPct = 0.19;
$IMG_BASE = 'https://control.roelplant.cl/uploads/variedades';

// Helper imagen
function buildImagenUrl(int $id, ?string $archivo, string $base): ?string {
    if (!$archivo) return null;
    $archivo = trim($archivo);
    if ($archivo === '') return null;
    if (preg_match('~^https?://~i', $archivo)) return $archivo;
    if (preg_match('~^[a-z0-9]+$~i', $archivo)) return rtrim($base,'/') . "/variedad_{$id}_{$archivo}.jpeg";
    return rtrim($base,'/') . '/' . ltrim($archivo, '/');
}

try {
    $like   = "%" . mysqli_real_escape_string($con, $nombre) . "%";
    $starts = mysqli_real_escape_string($con, $nombre) . "%";
    $exacto = mysqli_real_escape_string($con, $nombre);

    // FROM base
    $baseFrom = "
        FROM stock_productos s
        JOIN articulospedidos ap ON ap.id = s.id_artpedido
        JOIN variedades_producto v ON v.id = ap.id_variedad
        JOIN tipos_producto t ON t.id = v.id_tipo
        LEFT JOIN atributos_valores_variedades avv ON avv.id_variedad = v.id
        LEFT JOIN atributos_valores av ON av.id = avv.id_atributo_valor
        LEFT JOIN atributos a  ON a.id = av.id_atributo AND a.nombre = 'TIPO DE PLANTA'
        WHERE ap.estado >= 8
          AND (v.eliminada IS NULL OR v.eliminada = 0)
          AND v.nombre LIKE '$like'
    ";

    // -------- Total con stock > 0 usando subselect --------
    $sqlCount = "
        SELECT COUNT(*) AS total
        FROM (
            SELECT v.id,
                (
                    IFNULL(SUM(s.cantidad),0) -
                    IFNULL((SELECT SUM(r.cantidad) FROM reservas_productos r
                            WHERE r.id_variedad = v.id AND (r.estado = 0 OR r.estado = 1)), 0) -
                    IFNULL((SELECT SUM(e.cantidad) FROM entregas_stock e
                            JOIN reservas_productos r2 ON r2.id = e.id_reserva_producto
                            WHERE r2.id_variedad = v.id AND r2.estado = 2), 0)
                ) AS disponible_para_reservar
            $baseFrom
            GROUP BY v.id
        ) z
        WHERE z.disponible_para_reservar > 0
    ";

    $resultCount = mysqli_query($con, $sqlCount);
    if (!$resultCount) {
        throw new Exception('Error en consulta de conteo: ' . mysqli_error($con));
    }
    $rowCount = mysqli_fetch_assoc($resultCount);
    $total = (int)($rowCount['total'] ?? 0);

    // -------- Datos paginados --------
    $sql = "
        SELECT *
        FROM (
            SELECT
                v.id AS id_variedad,
                v.nombre AS nombre,
                CONCAT(t.codigo, LPAD(v.id_interno, 2, '0')) AS referencia,
                v.precio         AS precio_mayorista_sin_iva,
                v.precio_detalle AS precio_detalle_sin_iva,
                MAX(av.valor)      AS tipo_planta,
                MAX(v.descripcion) AS descripcion,
                (v.nombre = '$exacto')    AS rank_exacta,
                (v.nombre LIKE '$starts') AS rank_empieza,
                (
                    IFNULL(SUM(s.cantidad),0) -
                    IFNULL((SELECT SUM(r.cantidad) FROM reservas_productos r
                            WHERE r.id_variedad = v.id AND (r.estado = 0 OR r.estado = 1)), 0) -
                    IFNULL((SELECT SUM(e.cantidad) FROM entregas_stock e
                            JOIN reservas_productos r2 ON r2.id = e.id_reserva_producto
                            WHERE r2.id_variedad = v.id AND r2.estado = 2), 0)
                ) AS disponible_para_reservar,
                (
                    SELECT iv.nombre_archivo
                    FROM imagenes_variedades iv
                    WHERE iv.id_variedad = v.id
                    ORDER BY iv.id DESC
                    LIMIT 1
                ) AS imagen_archivo
            $baseFrom
            GROUP BY v.id, v.nombre, v.id_interno, v.precio, v.precio_detalle, t.codigo
        ) zz
        WHERE zz.disponible_para_reservar > 0
        ORDER BY zz.rank_exacta DESC, zz.rank_empieza DESC, zz.nombre ASC
        LIMIT $limit OFFSET $offset
    ";

    $result = mysqli_query($con, $sql);
    if (!$result) {
        throw new Exception('Error en consulta principal: ' . mysqli_error($con));
    }

    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (!$rows) {
        echo json_encode([
            'status' => 'not_found',
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
            'productos' => []
        ], JSON_UNESCAPED_UNICODE);
        mysqli_close($con);
        exit;
    }

    $productos = [];
    foreach ($rows as $r) {
        $idv = (int)$r['id_variedad'];
        $detalleNeto = (float)($r['precio_detalle_sin_iva'] ?? 0);
        $mayorNeto   = (float)($r['precio_mayorista_sin_iva'] ?? 0);
        $criterio = (!empty($r['rank_exacta']) ? 'exacta' : (!empty($r['rank_empieza']) ? 'empieza' : 'contiene'));
        $imagenUrl = buildImagenUrl($idv, $r['imagen_archivo'] ?? null, $IMG_BASE);

        $productos[] = [
            'id_variedad' => $idv,
            'nombre'      => $r['nombre'],
            'referencia'  => $r['referencia'],
            'tipo_planta' => $r['tipo_planta'] ?? null,
            'descripcion' => $r['descripcion'] ?? null,
            'imagen_url'  => $imagenUrl,
            'precios' => [
                'detalle'   => ['neto'=>$detalleNeto, 'iva'=>round($detalleNeto*$ivaPct), 'bruto'=>(int)round($detalleNeto*(1+$ivaPct))],
                'mayorista' => ['neto'=>$mayorNeto,   'iva'=>round($mayorNeto*$ivaPct),   'bruto'=>(int)round($mayorNeto*(1+$ivaPct))]
            ],
            'stock' => [
                'disponible_para_reservar' => max(0, (int)($r['disponible_para_reservar'] ?? 0)),
                'unidad' => 'plantines'
            ],
            'coincidencia' => ['criterio'=>$criterio,'buscado'=>$nombre]
        ];
    }

    $out = [
        'status'    => 'ok',
        'total'     => $total,
        'limit'     => $limit,
        'offset'    => $offset,
        'productos' => $productos
    ];
    if (!empty($productos)) {
        $out['producto'] = $productos[0];
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    mysqli_close($con);

} catch (Throwable $e) {
    mysqli_close($con);
    $msg = $DEBUG ? ('Error en consulta: '.$e->getMessage()) : 'Error en consulta';
    apiError($msg, 500);
}
