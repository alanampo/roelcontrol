<?php
// api/disponibles.php
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

// Opcional: /disponibles.php?debug=1
$DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';

// Parámetros
$tipoParam = trim((string)($_GET['tipo'] ?? ''));  // '', 'interior', 'exterior'
$q         = trim((string)($_GET['q'] ?? ''));
$limitRaw  = $_GET['limit'] ?? '200';              // 'all'|número
$offset    = (int)($_GET['offset'] ?? 0);

// Límite
$limit = 200;
if ($limitRaw === 'all') $limit = 1000;
elseif (is_numeric($limitRaw)) $limit = max(1, min(1000, (int)$limitRaw));

// Normaliza patrón para tipo
$tipo = mb_strtolower($tipoParam, 'UTF-8');
$pattern = null;
if ($tipo !== '') {
    if (strpos($tipo, 'inter') !== false)      $pattern = '%INTERIOR%';
    elseif (strpos($tipo, 'exter') !== false)  $pattern = '%EXTERIOR%';
    else                                       $pattern = '%'.mb_strtoupper($tipo, 'UTF-8').'%';
}

try {
    $whereExtra = '';
    if ($q !== '') {
        $whereExtra .= ' AND v.nombre LIKE "%' . mysqli_real_escape_string($con, $q) . '%"';
    }

    // Query robusta: evita ANY_VALUE, usa MAX()
    // Cálculo correcto: stock_total - reservas_pendientes - entregas
    $sql = "
        SELECT
            v.id AS id_variedad,
            v.nombre AS nombre,
            CONCAT(t.codigo, LPAD(v.id_interno, 2, '0')) AS referencia,
            t.nombre AS tipo_producto,
            MAX(av.valor) AS tipo_planta,
            MAX(v.descripcion) AS descripcion,
            v.precio         AS precio_mayorista_sin_iva,
            v.precio_detalle AS precio_detalle_sin_iva,
            (
                IFNULL(SUM(s.cantidad), 0) -
                IFNULL((
                    SELECT SUM(r.cantidad)
                    FROM reservas_productos r
                    WHERE r.id_variedad = v.id AND (r.estado = 0 OR r.estado = 1)
                ), 0) -
                IFNULL((
                    SELECT SUM(e.cantidad)
                    FROM entregas_stock e
                    JOIN reservas_productos r2 ON r2.id = e.id_reserva_producto
                    WHERE r2.id_variedad = v.id AND r2.estado = 2
                ), 0)
            ) AS disponible_para_reservar,
            (
                SELECT CONCAT('https://control.roelplant.cl/uploads/variedades/', iv.nombre_archivo)
                FROM imagenes_variedades iv
                WHERE iv.id_variedad = v.id
                ORDER BY iv.id DESC
                LIMIT 1
            ) AS imagen_url
        FROM stock_productos s
        JOIN articulospedidos ap ON ap.id = s.id_artpedido
        JOIN variedades_producto v ON v.id = ap.id_variedad
        JOIN tipos_producto t ON t.id = v.id_tipo
        LEFT JOIN atributos_valores_variedades avv ON avv.id_variedad = v.id
        LEFT JOIN atributos_valores av ON av.id = avv.id_atributo_valor
        LEFT JOIN atributos a ON a.id = av.id_atributo AND a.nombre = 'TIPO DE PLANTA'
        WHERE ap.estado >= 8
          AND (v.eliminada IS NULL OR v.eliminada = 0)
          {$whereExtra}
        GROUP BY v.id, v.nombre, v.id_interno, v.precio, v.precio_detalle, t.codigo, t.nombre
        HAVING
            disponible_para_reservar > 0
            " . ($pattern ? " AND ( (tipo_planta IS NOT NULL AND tipo_planta LIKE '$pattern') OR (tipo_producto LIKE '$pattern') )" : "") . "
        ORDER BY disponible_para_reservar DESC, v.nombre ASC
        LIMIT {$limit} OFFSET {$offset}
    ";

    $result = mysqli_query($con, $sql);
    if (!$result) {
        throw new Exception('Error en consulta: ' . mysqli_error($con));
    }

    $iva = 0.19;
    $items = [];

    while ($r = mysqli_fetch_assoc($result)) {
        $items[] = [
            'id_variedad' => (int)$r['id_variedad'],
            'nombre'      => $r['nombre'],
            'referencia'  => $r['referencia'],
            'tipo_planta' => $r['tipo_planta'] ?: $r['tipo_producto'],
            'descripcion' => $r['descripcion'] !== null ? trim((string)$r['descripcion']) : null,
            'precios'     => [
                'detalle_bruto'   => (int)round((float)$r['precio_detalle_sin_iva']   * (1 + $iva)),
                'mayorista_bruto' => (int)round((float)$r['precio_mayorista_sin_iva'] * (1 + $iva)),
            ],
            'stock'       => max(0, (int)$r['disponible_para_reservar']),
            'imagen_url'  => $r['imagen_url'] ?: null,
        ];
    }

    echo json_encode([
        'status' => 'ok',
        'tipo'   => $tipoParam !== '' ? $tipoParam : null,
        'count'  => count($items),
        'items'  => $items
    ], JSON_UNESCAPED_UNICODE);

    mysqli_close($con);

} catch (Throwable $e) {
    mysqli_close($con);
    $msg = $DEBUG ? ('Error en consulta: '.$e->getMessage()) : 'Error en consulta';
    apiError($msg, 500);
}
