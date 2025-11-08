<?php
// config.php

date_default_timezone_set('America/Santiago');

/* ===== Exposici��n temporal del bot =====
   true  => cualquiera puede escribir
   false => solo IDs permitidos en el webhook
   Alternativa: fija un epoch futuro en BOT_PUBLIC_UNTIL
*/
define('BOT_PUBLIC_MODE', false);
define('BOT_PUBLIC_UNTIL', 0); // ej: strtotime('2025-09-01 23:59 America/Santiago')

/* ===== Base de datos ===== */
$host     = "127.0.0.1";
$user     = "roeluser1_usercli";
$password = "SergioVM2022!!";
$dbname   = "roeluser1_bdsys";

try {
    $DB = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error DB: " . $e->getMessage());
    exit("�7�4 Error de conexi��n a la base de datos.");
}

/* ===== Helper Telegram ===== */
function tgRequest(string $method, array $params = []): array {
    $ch = curl_init(API_URL . $method);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_POSTFIELDS     => $params,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err || $code >= 400 || $raw === false) return ['ok' => false, 'error' => $err, 'code' => $code];
    $j = json_decode($raw, true);
    return is_array($j) ? $j : ['ok' => false, 'error' => 'json_decode'];
}

function sendMessage($chat_id, $text, $mode = 'HTML', $disable_preview = false, $protect_content = false, $reply_to = null, $reply_markup = null): array {
    $params = [
        'chat_id'                  => $chat_id,
        'text'                     => $text,
        'parse_mode'               => $mode,
        'disable_web_page_preview' => $disable_preview ? 'true' : 'false',
        'protect_content'          => $protect_content ? 'true' : 'false',
    ];
    if ($reply_to !== null)   $params['reply_to_message_id'] = $reply_to;
    if ($reply_markup !== null) $params['reply_markup'] = is_string($reply_markup) ? $reply_markup : json_encode($reply_markup);
    return tgRequest('sendMessage', $params);
}

function sendChatAction($chat_id, $action = 'typing'): array {
    return tgRequest('sendChatAction', ['chat_id' => $chat_id, 'action' => $action]);
}
