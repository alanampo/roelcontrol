<?php
session_name("roel-control");session_start();
session_destroy();
$parametros_cookies = session_get_cookie_params();
setcookie(session_name(),0,1,$parametros_cookies["path"]);
setcookie('roel-usuario', '', time() - 3600, '/');
setcookie('roel-token', '', time() - 3600, '/');

header("Location: index.php");

?>