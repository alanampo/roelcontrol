<?php
session_name("roel-control");
session_start();
date_default_timezone_set("America/Santiago");
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/.env';
if (!file_exists($filePath)) {
  throw new Exception("Archivo .env no encontrado.");
}

$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
  if (strpos($line, '=') !== false) {
    list($name, $value) = explode('=', $line, 2);
    $name = trim($name);
    $value = str_replace('"', '', trim($value));
    
    if (!array_key_exists($name, $_ENV)) {
      putenv("$name=$value");
      $_ENV[$name] = $value;
      $_SERVER[$name] = $value;
    }
  }
}
$version = 670;
header('Content-type: text/html; charset=utf-8');
if (!isset($_SESSION["roel-token"]) || !isset($_COOKIE["roel-token"])) {
  setcookie('roel-usuario', '', time() - 3600, '/');
  setcookie('roel-token', '', time() - 3600, '/');
  header("Location: index.php");
  exit;
}

if ($_SESSION["roel-token"] != $_COOKIE["roel-token"]) {
  setcookie('roel-usuario', '', time() - 3600, '/');
  setcookie('roel-token', '', time() - 3600, '/');
  header("Location: index.php");
  exit;
}
?>