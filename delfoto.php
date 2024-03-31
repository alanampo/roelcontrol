<?php
  include "./class_lib/sesionSecurity.php";

    error_reporting(0);

    try {
        if (isset($_POST['filename'])) {
            if (unlink(htmlentities( $_POST['filename']))) {
              echo "success";
            }
         }
    } catch (\Throwable $th) {
        echo "error: $th";
    }

    
?>