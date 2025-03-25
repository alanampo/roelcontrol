<?php
  session_name("roel-control");session_start();
  date_default_timezone_set("America/Santiago");
  $version = 1;
  header('Content-type: text/html; charset=utf-8');
  if(!isset($_SESSION["roel-token"]) || !isset($_COOKIE["roel-token"])){
    setcookie('roel-usuario', '', time() - 3600, '/');
    setcookie('roel-token', '', time() - 3600, '/');
    header("Location: index.php");
    exit;
  }

  if($_SESSION["roel-token"] != $_COOKIE["roel-token"]){
    setcookie('roel-usuario', '', time() - 3600, '/');
    setcookie('roel-token', '', time() - 3600, '/');
    header("Location: index.php");
    exit;
  }
?>