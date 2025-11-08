<?php
// api/logout.php
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

try {
    $userId = $authUser['user_id'];
    $userType = $authUser['user_type'];

    // Revocar TODOS los tokens del usuario (access y refresh)
    $query = "UPDATE auth_tokens
              SET revoked = 1
              WHERE user_id = $userId
              AND user_type = '$userType'
              AND revoked = 0";

    if (!mysqli_query($con, $query)) {
        throw new Exception('Error al revocar tokens: ' . mysqli_error($con));
    }

    $tokensRevoked = mysqli_affected_rows($con);

    echo json_encode([
        'status' => 'ok',
        'message' => 'Sesión cerrada exitosamente',
        'tokens_revoked' => $tokensRevoked
    ], JSON_UNESCAPED_UNICODE);

    mysqli_close($con);

} catch (Throwable $e) {
    mysqli_close($con);
    apiError('Error al cerrar sesión: ' . $e->getMessage(), 500);
}
