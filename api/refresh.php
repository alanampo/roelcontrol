<?php
// api/refresh.php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

// Conexión a la base de datos
require_once __DIR__ . '/../class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']);
    exit;
}
mysqli_query($con, "SET NAMES 'utf8'");

// Obtener refresh token
$input = json_decode(file_get_contents('php://input'), true);
$refreshToken = trim($input['refresh_token'] ?? $_POST['refresh_token'] ?? '');

if (empty($refreshToken)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Refresh token es requerido'
    ]);
    exit;
}

// Función para generar token seguro
function generateSecureToken(int $length = 64): string {
    return bin2hex(random_bytes($length));
}

try {
    // Verificar refresh token
    $tokenHash = hash('sha256', $refreshToken);

    $query = "SELECT
                at.id,
                at.user_id,
                at.user_type,
                at.expires_at,
                at.revoked
              FROM auth_tokens at
              WHERE at.token_hash = '$tokenHash'
              AND at.type = 'refresh'
              LIMIT 1";

    $result = mysqli_query($con, $query);

    if (!$result || mysqli_num_rows($result) === 0) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Refresh token inválido'
        ]);
        exit;
    }

    $tokenData = mysqli_fetch_assoc($result);

    // Verificar si está revocado
    if ($tokenData['revoked'] == 1) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Refresh token revocado'
        ]);
        exit;
    }

    // Verificar expiración
    $now = new DateTime('now', new DateTimeZone('America/Santiago'));
    $expiresAt = new DateTime($tokenData['expires_at'], new DateTimeZone('America/Santiago'));

    if ($now > $expiresAt) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Refresh token expirado'
        ]);
        exit;
    }

    // Generar nuevo access token
    $newAccessToken = generateSecureToken(32);
    $newAccessTokenHash = hash('sha256', $newAccessToken);
    $accessExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $userId = (int)$tokenData['user_id'];
    $userType = $tokenData['user_type'];

    // Revocar tokens de acceso anteriores del usuario (opcional, para mayor seguridad)
    $queryRevoke = "UPDATE auth_tokens
                    SET revoked = 1
                    WHERE user_id = $userId
                    AND user_type = '$userType'
                    AND type = 'access'
                    AND revoked = 0";
    mysqli_query($con, $queryRevoke);

    // Insertar nuevo access token
    $queryInsert = "INSERT INTO auth_tokens (user_id, user_type, token_hash, type, expires_at, revoked)
                    VALUES ($userId, '$userType', '$newAccessTokenHash', 'access', '$accessExpiresAt', 0)";

    if (!mysqli_query($con, $queryInsert)) {
        throw new Exception('Error al crear nuevo access token: ' . mysqli_error($con));
    }

    // Respuesta exitosa
    echo json_encode([
        'status' => 'ok',
        'message' => 'Token renovado exitosamente',
        'tokens' => [
            'access_token' => $newAccessToken,
            'access_expires_at' => $accessExpiresAt,
            'token_type' => 'Bearer'
        ]
    ], JSON_UNESCAPED_UNICODE);

    mysqli_close($con);

} catch (Throwable $e) {
    mysqli_close($con);
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
