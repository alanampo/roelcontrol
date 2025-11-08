<?php
// api/login.php
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

// Obtener datos de entrada
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? $_POST['username'] ?? '');
$passwordInput = trim($input['password'] ?? $_POST['password'] ?? '');

if (empty($username) || empty($passwordInput)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Username y password son requeridos'
    ]);
    exit;
}

// Función para generar token seguro
function generateSecureToken(int $length = 64): string {
    return bin2hex(random_bytes($length));
}

// Función para crear tokens en la BD
function createTokens($con, int $userId, string $userType): array {
    $accessToken = generateSecureToken(32);  // 64 caracteres
    $refreshToken = generateSecureToken(32); // 64 caracteres

    $accessTokenHash = hash('sha256', $accessToken);
    $refreshTokenHash = hash('sha256', $refreshToken);

    // Tokens de acceso válidos por 24 horas
    $accessExpiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
    // Tokens de refresh válidos por 30 días
    $refreshExpiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Insertar access token
    $queryAccess = "INSERT INTO auth_tokens (user_id, user_type, token_hash, type, expires_at, revoked)
                    VALUES ($userId, '$userType', '$accessTokenHash', 'access', '$accessExpiresAt', 0)";

    if (!mysqli_query($con, $queryAccess)) {
        throw new Exception('Error al crear access token: ' . mysqli_error($con));
    }

    // Insertar refresh token
    $queryRefresh = "INSERT INTO auth_tokens (user_id, user_type, token_hash, type, expires_at, revoked)
                     VALUES ($userId, '$userType', '$refreshTokenHash', 'refresh', '$refreshExpiresAt', 0)";

    if (!mysqli_query($con, $queryRefresh)) {
        throw new Exception('Error al crear refresh token: ' . mysqli_error($con));
    }

    return [
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken,
        'access_expires_at' => $accessExpiresAt,
        'refresh_expires_at' => $refreshExpiresAt
    ];
}

try {
    $userId = null;
    $userType = null;
    $userData = null;

    // Intentar autenticar como USUARIO
    $usernameEscaped = mysqli_real_escape_string($con, $username);
    $queryUsuario = "SELECT id, nombre, nombre_real, password, tipo_usuario, inhabilitado
                     FROM usuarios
                     WHERE nombre = '$usernameEscaped'
                     LIMIT 1";

    $resultUsuario = mysqli_query($con, $queryUsuario);

    if ($resultUsuario && mysqli_num_rows($resultUsuario) > 0) {
        $usuario = mysqli_fetch_assoc($resultUsuario);

        // Verificar si está inhabilitado
        if ($usuario['inhabilitado'] == 1) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario inhabilitado'
            ]);
            exit;
        }

        // Verificar contraseña (texto plano - considera migrar a password_hash)
        if ($usuario['password'] === $passwordInput) {
            $userId = (int)$usuario['id'];
            $userType = 'usuario';
            $userData = [
                'id' => $userId,
                'username' => $usuario['nombre'],
                'nombre_real' => $usuario['nombre_real'],
                'tipo_usuario' => (int)$usuario['tipo_usuario']
            ];
        }
    }

    // Si no es usuario, intentar autenticar como CLIENTE
    if ($userId === null) {
        $queryCliente = "SELECT id_cliente, nombre, mail, password_hash, activo
                         FROM clientes
                         WHERE mail = '$usernameEscaped'
                         LIMIT 1";

        $resultCliente = mysqli_query($con, $queryCliente);

        if ($resultCliente && mysqli_num_rows($resultCliente) > 0) {
            $cliente = mysqli_fetch_assoc($resultCliente);

            // Verificar si está activo
            if ($cliente['activo'] != 1) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Cliente inactivo'
                ]);
                exit;
            }

            // Verificar contraseña (puede estar hasheada o no)
            $passwordValid = false;
            if (!empty($cliente['password_hash'])) {
                // Intentar verificar con password_verify (hash)
                if (password_verify($passwordInput, $cliente['password_hash'])) {
                    $passwordValid = true;
                } else if ($cliente['password_hash'] === $passwordInput) {
                    // Fallback: comparación directa (texto plano)
                    $passwordValid = true;
                }
            }

            if ($passwordValid) {
                $userId = (int)$cliente['id_cliente'];
                $userType = 'cliente';
                $userData = [
                    'id' => $userId,
                    'nombre' => $cliente['nombre'],
                    'email' => $cliente['mail']
                ];
            }
        }
    }

    // Si no se autenticó
    if ($userId === null) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales inválidas'
        ]);
        exit;
    }

    // Generar tokens
    $tokens = createTokens($con, $userId, $userType);

    // Respuesta exitosa
    echo json_encode([
        'status' => 'ok',
        'message' => 'Login exitoso',
        'user' => $userData,
        'user_type' => $userType,
        'tokens' => [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'access_expires_at' => $tokens['access_expires_at'],
            'refresh_expires_at' => $tokens['refresh_expires_at'],
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
