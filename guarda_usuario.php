<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$tipo = $_POST['tipo'];
$id_usuario = $_POST['id_usuario'];

$tipo_usuario = $_POST['tipo_usuario'];
$id_cliente = $_POST['id_cliente'];

$email = mysqli_real_escape_string($con, $_POST['email']);

$nombre = mysqli_real_escape_string($con, $_POST['nombre']);
$nombre_real = mysqli_real_escape_string($con, $_POST['nombre_real']);
$password = mysqli_real_escape_string($con, $_POST['password']);
$permisos = json_decode($_POST["permisos"]);

if ($tipo == "agregar") {
    if ($tipo_usuario == "cliente") {
        $query = "SELECT * FROM usuarios WHERE nombre = '$email'";
    } else if ($tipo_usuario == "trabajador") {
        $query = "SELECT * FROM usuarios WHERE nombre = '$nombre'";
    }

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        echo "yaexiste";
    } else {
        if ($tipo_usuario == "trabajador") {
            $inicial = $nombre[0];
            $query = "SELECT * FROM usuarios WHERE iniciales = '$inicial'";

            $val = mysqli_query($con, $query);
            if (mysqli_num_rows($val) > 0) {
                $inicial = $nombre[0].$nombre[1];
            }

            $usuario = mysqli_query($con, "SELECT (IFNULL(MAX(id),0)+1) as id_usuario FROM usuarios;");
            if (mysqli_num_rows($usuario) > 0) {
                $id_usuario = mysqli_fetch_assoc($usuario)["id_usuario"];
                mysqli_autocommit($con, false);
                $errors = array();
                // $query = "INSERT INTO usuarios (nombre, password) VALUES ('$nombre', '$password');";
                // if (!mysqli_query($con, $query)) {
                //     $errors[] = mysqli_error($con);
                // }
                
                $query = "INSERT INTO usuarios (nombre, nombre_real, password, tipo_usuario, iniciales) VALUES (LOWER('$nombre'), '$nombre_real', '$password', 1, UPPER('$inicial'));";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }

                for ($i = 0; $i < count($permisos); $i++) {
                    $modulo = $permisos[$i];
                    $query = "INSERT INTO permisos (id_usuario, modulo) VALUES ($id_usuario, '$modulo');";
                    if (!mysqli_query($con, $query)) {
                        $errors[] = mysqli_error($con);
                    }
                }
                if (count($errors) === 0) {
                    if (mysqli_commit($con)) {
                        echo "success";
                    } else {
                        mysqli_rollback($con);
                    }
                } else {
                    mysqli_rollback($con);
                    print_r($errors);
                }
                mysqli_close($con);
            }

            
        } else if ($tipo_usuario == "cliente") {
            $query = "INSERT INTO usuarios (nombre, password, id_cliente, tipo_usuario) VALUES (LOWER('$email'), '$password', $id_cliente, 0);";
            if (mysqli_query($con, $query)){
                echo "success";
            }
            else{
                echo "Error al guardar el cliente. Intenta de nuevo";
            }
        }
    }
} else if ($tipo == "editar") {
    $id_usuario = $_POST["id_usuario"];
    if ($tipo_usuario == "cliente") {
        $query = "SELECT * FROM usuarios WHERE nombre = '$email' AND id <> $id_usuario;";
    } else if ($tipo_usuario == "trabajador") {
        $query = "SELECT * FROM usuarios WHERE nombre = '$nombre' AND id <> $id_usuario;";
    }

    $val = mysqli_query($con, $query);
    $errors = array();

    if (mysqli_num_rows($val) > 0) {
        echo "yaexiste";
    } else {
        if ($tipo_usuario == "trabajador") {
            mysqli_autocommit($con, false);
            $query = "UPDATE usuarios SET nombre = LOWER('$nombre'), nombre_real = '$nombre_real', password = '$password' WHERE id = $id_usuario;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
            $query = "DELETE FROM permisos WHERE id_usuario = $id_usuario";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }

            for ($i = 0; $i < count($permisos); $i++) {
                $modulo = $permisos[$i];
                $query = "INSERT INTO permisos (id_usuario, modulo) VALUES ($id_usuario, '$modulo');";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }
            }
            if (count($errors) === 0) {
                if (mysqli_commit($con)) {
                    echo "success";
                } else {
                    mysqli_rollback($con);
                }
            } else {
                mysqli_rollback($con);
                print_r($errors);
            }
            mysqli_close($con);
        } else if ($tipo_usuario == "cliente") {
            $query = "UPDATE usuarios SET nombre = LOWER('$email'), password = '$password' WHERE id = $id_usuario;";
            if (mysqli_query($con, $query)){
                echo "success";
            }
            else{
                echo "Error al editar el usuario. Intenta de nuevo";
            }
        }
    }
}
