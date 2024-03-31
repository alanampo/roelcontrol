<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require('class_lib/class_conecta_mysql.php');
require('class_lib/funciones.php');

$con = mysqli_connect($host, $user, $password,$dbname);
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}
  $id_usuario = $_POST["id_usuario"];
  $cadenaselect="SELECT modulo FROM permisos WHERE id_usuario = $id_usuario";
  $array = array();
  $val = mysqli_query($con,$cadenaselect);
  if (mysqli_num_rows($val)>0){
    while($re=mysqli_fetch_array($val))
    {
      array_push($array, $re["modulo"]);
    }
    echo json_encode($array);
  }
?>