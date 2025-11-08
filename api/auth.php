<?php
// api/auth.php
// Middleware de autenticación para la API

require_once __DIR__ . '/../class_lib/class_conecta_mysql.php';

function authenticateRequest() {
    global $host, $user, $password, $dbname;

    // Obtener token del header Authorization
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (empty($authHeader)) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token de autenticación no proporcionado'
        ]);
        exit;
    }

    // Extraer token (formato: "Bearer TOKEN")
    $token = null;
    if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
        $token = $matches[1];
    } else {
        $token = $authHeader; // Si no tiene "Bearer", asumir que es el token directo
    }

    if (empty($token)) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token inválido'
        ]);
        exit;
    }

    // Conectar a la base de datos
    $con = mysqli_connect($host, $user, $password, $dbname);
    if (!$con) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error de conexión a la base de datos'
        ]);
        exit;
    }

    mysqli_query($con, "SET NAMES 'utf8'");

    // Hash del token para comparar con la BD
    $tokenHash = hash('sha256', $token);

    // Verificar token en la base de datos
    $query = "SELECT
                at.id,
                at.user_id,
                at.user_type,
                at.type as token_type,
                at.expires_at,
                at.revoked
              FROM auth_tokens at
              WHERE at.token_hash = '" . mysqli_real_escape_string($con, $tokenHash) . "'
              AND at.type = 'access'
              LIMIT 1";

    $result = mysqli_query($con, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_close($con);
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token no válido'
        ]);
        exit;
    }

    $tokenData = mysqli_fetch_assoc($result);

    // Verificar si el token está revocado
    if ($tokenData['revoked'] == 1) {
        mysqli_close($con);
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token revocado'
        ]);
        exit;
    }

    // Verificar si el token ha expirado
    $now = new DateTime('now', new DateTimeZone('America/Santiago'));
    $expiresAt = new DateTime($tokenData['expires_at'], new DateTimeZone('America/Santiago'));

    if ($now > $expiresAt) {
        mysqli_close($con);
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Token expirado'
        ]);
        exit;
    }

    mysqli_close($con);

    // Retornar información del usuario autenticado
    return [
        'user_id' => (int)$tokenData['user_id'],
        'user_type' => $tokenData['user_type'],
        'token_id' => (int)$tokenData['id']
    ];
}

// Helper para respuestas de error
function apiError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

// Helper para respuestas exitosas
function apiSuccess($data, $message = null) {
    $response = ['status' => 'ok'];
    if ($message !== null) {
        $response['message'] = $message;
    }
    $response = array_merge($response, $data);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
