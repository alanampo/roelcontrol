<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set environment to local to load correct db credentials
//$_SERVER['HTTP_HOST'] = 'local';

require 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

// Get all orphan reservas_productos
$query_orphans = "SELECT * FROM reservas_productos WHERE id_reserva IS NULL";
$result_orphans = mysqli_query($con, $query_orphans);

if (mysqli_num_rows($result_orphans) > 0) {
    echo "Found " . mysqli_num_rows($result_orphans) . " orphan records. Starting migration...<br>";

    while ($orphan = mysqli_fetch_assoc($result_orphans)) {
        $id_orphan = $orphan['id'];
        $id_cliente = $orphan['id_cliente'];
        $id_usuario = $orphan['id_usuario'];
        $fecha = $orphan['fecha'] ? "'" . $orphan['fecha'] . "'" : "NOW()";
        $observaciones = mysqli_real_escape_string($con, $orphan['comentario']);

        if($id_cliente && $id_usuario){
            // Start transaction
            mysqli_autocommit($con, false);

            // 1. Create new reserva
            $query_new_reserva = "INSERT INTO reservas (fecha, id_cliente, observaciones, id_usuario) VALUES ($fecha, $id_cliente, '$observaciones', $id_usuario)";
            
            if (mysqli_query($con, $query_new_reserva)) {
                $new_reserva_id = mysqli_insert_id($con);
                echo "Created new reserva with ID: $new_reserva_id for orphan record ID: $id_orphan <br>";

                // 2. Update the orphan record
                $query_update_orphan = "UPDATE reservas_productos SET id_reserva = $new_reserva_id WHERE id = $id_orphan";
                if (mysqli_query($con, $query_update_orphan)) {
                    echo "Updated orphan record ID: $id_orphan with new reserva ID: $new_reserva_id <br>";
                    mysqli_commit($con);
                } else {
                    echo "Error updating orphan record ID: $id_orphan. Rolling back...<br>";
                    mysqli_rollback($con);
                }
            } else {
                echo "Error creating new reserva for orphan record ID: $id_orphan. Rolling back...<br>";
                mysqli_rollback($con);
            }
        } else {
            echo "Skipping orphan record ID: $id_orphan because id_cliente or id_usuario is null.<br>";
        }
        echo "--------------------------------<br>";
    }
    echo "Migration finished.";
} else {
    echo "No orphan records found.";
}

mysqli_close($con);
?>