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
mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];

if ($consulta == "busca_stock_actual") {
    $query = "SELECT
  t.nombre as nombre_tipo,
  v.nombre as nombre_variedad,
  t.id as id_tipo,
  t.codigo,
  v.id_interno,
  v.precio,
  v.id as id_variedad,
  SUM(s.cantidad) as cantidad,
  (SELECT IFNULL(SUM(r.cantidad),0) FROM reservas_productos r
        WHERE r.id_variedad = v.id AND r.estado >= 0) as cantidad_reservada,
  (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e
        INNER JOIN reservas_productos r ON e.id_reserva = r.id
        WHERE r.id_variedad = v.id AND r.estado >= 0) as cantidad_entregada
  FROM stock_productos s
  INNER JOIN articulospedidos ap
  ON s.id_artpedido = ap.id
  INNER JOIN variedades_producto v
  ON v.id = ap.id_variedad
  INNER JOIN tipos_producto t
  ON t.id = v.id_tipo
  WHERE ap.estado >= 8 
  GROUP BY v.id;
          ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Stock Actual</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Producto</th><th>Stock Real</th><th>Disponible para Reservar</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $disponible = ((int) $ww["cantidad"] - (int) $ww["cantidad_entregada"]);

            $disponible_reserva = ((int) $ww["cantidad"] - (int) $ww["cantidad_reservada"] - (int) $ww["cantidad_entregada"]);

            if ($disponible > 0) {
                $cantidad = ($disponible <= 50 ? "<span class='text-danger font-weight-bold'>$disponible</span>" : "<span class='font-weight-bold'>$disponible</span>");
                $cantidad2 = ($disponible_reserva <= 50 ? "<span class='text-danger font-weight-bold'>$disponible_reserva</span>" : "<span class='font-weight-bold'>$disponible_reserva</span>");
                echo "
                <tr class='text-center' style='cursor:pointer'>
                  <td>$ww[nombre_variedad] ($ww[codigo]$ww[id_interno])</td>
                  <td>$cantidad</td>
                  <td>$cantidad2</td>
                </tr>";
            }
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron productos en stock...</b></div>";
    }
} else if ($consulta == "busca_reservas") {
    $query = "SELECT
            r.id as id_reserva,
            t.nombre as nombre_tipo,
            v.nombre as nombre_variedad,
            t.id as id_tipo,
            t.codigo,
            cl.nombre as nombre_cliente,
            cl.id_cliente,
            v.id_interno,
            r.comentario,
            r.comentario_empresa,
            v.id as id_variedad,
            r.visto,
            r.cantidad,
            r.origen,
            DATE_FORMAT(r.fecha, '%d/%m/%y<br>%H:%i') as fecha,
            DATE_FORMAT(r.fecha, '%Y%m%d %H:%i') as fecha_raw,
            (SELECT IFNULL(SUM(s.cantidad),0) FROM stock_productos s
            INNER JOIN articulospedidos p ON p.id = s.id_artpedido
        INNER JOIN variedades_producto v ON v.id = p.id_variedad
        WHERE p.id_variedad = r.id_variedad) as cantidad_stock,
        (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e
        INNER JOIN reservas_productos r ON e.id_reserva = r.id
        WHERE r.id_variedad = v.id) as cantidad_entregada_total,
        (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e
        WHERE e.id_reserva = r.id) as cantidad_entregada,
            r.estado
            FROM
            reservas_productos r
            INNER JOIN variedades_producto v
            ON v.id = r.id_variedad
            INNER JOIN tipos_producto t
            ON t.id = v.id_tipo
            INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
            GROUP BY r.id
            ;
        ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Reservas</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Producto</th><th>F.<br>Reserva</th><th>Cliente</th><th>Origen</th><th>Cant.<br>Reservada</th><th>Cant.<br>Entregada</th><th style='max-width:250px'>Comentarios</th><th>Estado</th><th style='max-width:50px'></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $btn_cancelar = ($ww["estado"] == 0 || $ww["estado"] == 1 ? "<button onclick='cancelarReserva($ww[id_reserva])' class='btn btn-danger btn-sm mb-2'><i class='fa fa-ban'></i></button>" : "");
            $btn_visto = ($ww["estado"] == 0 && $ww["visto"] == 0 ? "<button onclick='marcarVisto($ww[id_reserva])' class='btn btn-info btn-sm d-inline-block mb-2'><i class='fa fa-book'></i></button>" : "");
            $btn_proceso = ($ww["estado"] == 0 ? "<button onclick='marcarEnProceso($ww[id_reserva])' class='btn btn-primary btn-sm d-inline-block'><i class='fa fa-spinner'></i></button>" : "");

            $cant_disponible = (int) $ww["cantidad_stock"] - (int) $ww["cantidad_entregada_total"];
            $btn_entregar = ($ww["estado"] == 0 || $ww["estado"] == 1 ? "<button onclick='entregar($ww[id_reserva], \"$ww[nombre_variedad]\", $ww[cantidad], $cant_disponible)' class='btn btn-success btn-sm d-inline-block'><i class='fa fa-truck'></i></button>" : "");

            $comentario_cliente = ($ww["comentario"] != null ? "<p><small>CLIENTE: $ww[comentario]</small></p>" : "");
            $comentario_empresa = ($ww["comentario_empresa"] != null ? "<p class='text-danger'><small>EMPRESA: $ww[comentario_empresa]</small></p>" : "");

            echo "
        <tr class='text-center' style='cursor:pointer; " . ($ww["estado"] == 0 && $ww["visto"] == 0 ? "background-color:#CEF6F5" : "") . "'>
          <td><small>$ww[id_reserva]</small></td>
          <td>$ww[nombre_variedad] ($ww[codigo]$ww[id_interno])</td>
          <td><span style='display:none'>$ww[fecha_raw]</span>$ww[fecha]</td>
          <td>$ww[nombre_cliente] ($ww[id_cliente])</td>
          <td>$ww[origen]</td>
          <td>$ww[cantidad]</td>
          <td>$ww[cantidad_entregada]</td>
          <td style='text-transform:uppercase'>$comentario_cliente $comentario_empresa</td>
          <td>" . boxEstadoReserva($ww["estado"], true) . "</td>
          <td>
            <div class='d-flex flex-row justify-content-between'>
              $btn_cancelar
              $btn_visto
            </div>
            <div class='d-flex flex-row justify-content-between'>
              $btn_proceso
              $btn_entregar
            </div>
          </td>
        </tr>";

        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron reservas...</b></div>";
    }
} else if ($consulta == "cancelar_reserva") {
    $id_reserva = $_POST["id_reserva"];
    try {
        $con_tienda = mysqli_connect($host, $user, $password, $dbpresta);
        if (!$con_tienda) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $query = "SELECT t.codigo, rp.id_variedad, v.id_interno, rp.cantidad FROM reservas_productos rp INNER JOIN variedades_producto v ON rp.id_variedad = v.id INNER JOIN tipos_producto t ON t.id = v.id_tipo WHERE rp.id = $id_reserva";
        $val = mysqli_query($con, $query);
        $errors = array();
        if ($val && mysqli_num_rows($val)) {
            $v = mysqli_fetch_assoc($val);

            mysqli_autocommit($con, false);

            $cantidad = $v["cantidad"];
            $id_producto = $v["codigo"] . str_pad($v["id_interno"], 2, '0', STR_PAD_LEFT);
            $query = "SELECT pr.id_product, pr.reference, st.quantity, st.physical_quantity, st.reserved_quantity FROM ps_stock_available st INNER JOIN ps_product pr ON st.id_product = pr.id_product WHERE pr.reference = '$id_producto';";
            $estaEnTienda = false;
            $val2 = mysqli_query($con_tienda, $query);
            if ($val2 && mysqli_num_rows($val2)) {
                $vt = mysqli_fetch_assoc($val2);
                mysqli_autocommit($con_tienda, false);
                $id_product_tienda = $vt["id_product"];
                $query = "UPDATE ps_stock_available SET quantity = quantity + $cantidad, reserved_quantity = reserved_quantity - $cantidad WHERE id_product = $id_product_tienda;";
                $estaEnTienda = true;
                if (!mysqli_query($con_tienda, $query)) {
                    $errors[] = mysqli_error($con_tienda);
                }
            }

            $query = "UPDATE reservas_productos SET estado = -1 WHERE id = $id_reserva";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }

            if (count($errors) === 0) {
                if (!$estaEnTienda){
                    if (mysqli_commit($con)){
                        echo "success";
                    }
                    else {
                        mysqli_rollback($con);
                    }   
                }
                else{
                    if (mysqli_commit($con) && mysqli_commit($con_tienda)){
                        echo "success";
                    }
                    else {
                        mysqli_rollback($con);
                        mysqli_rollback($con_tienda);
                    }   
                }
            } else {
                if (!$estaEnTienda){
                    mysqli_rollback($con);
                }
                else{
                    mysqli_rollback($con);
                    mysqli_rollback($con_tienda);
                }
                print_r($errors);
            }
        }
        mysqli_close($con);
        mysqli_close($con_tienda);

    } catch (\Throwable$th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "marcar_visto") {
    if (mysqli_query($con, "UPDATE reservas_productos SET visto = 1 WHERE id = $_POST[id_reserva];")) {
        echo "success";
    }
} else if ($consulta == "marcar_en_proceso") {
    $id_reserva = $_POST["id_reserva"];
    try {
        if (mysqli_query($con, "UPDATE reservas_productos SET estado = 1, visto = 1 WHERE id = $id_reserva")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }

    } catch (\Throwable$th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_entrega") {
    $id_reserva = $_POST["id_reserva"];
    $cantidad = mysqli_real_escape_string($con, $_POST["cantidad"]);
    $comentario = mysqli_real_escape_string($con, $_POST["comentario"]);
    $errors = array();

    $con_tienda = mysqli_connect($host, $user, $password, $dbpresta);
    if (!$con_tienda) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT * FROM (
              (SELECT IFNULL(SUM(s.cantidad),0) as cantidad_stock FROM stock_productos s
                INNER JOIN articulospedidos p ON s.id_artpedido = p.id
                INNER JOIN variedades_producto v ON v.id = p.id_variedad
                WHERE p.id_variedad = (
                  SELECT r.id_variedad FROM reservas_productos r WHERE r.id = $id_reserva
                )
              ) as q1,
                (SELECT IFNULL(SUM(e.cantidad),0) as cantidad_entregada FROM entregas_stock e
                INNER JOIN reservas_productos r ON e.id_reserva = r.id
                INNER JOIN variedades_producto v ON v.id = r.id_variedad
                WHERE r.id_variedad = (
                  SELECT r.id_variedad FROM reservas_productos r WHERE r.id = $id_reserva
                ) AND r.estado >= 0) as q2,
                (SELECT r.estado as estado FROM reservas_productos r WHERE r.id = $id_reserva) as q3,
                (SELECT t.codigo, rp.id_variedad, rp.cantidad as canti FROM reservas_productos rp INNER JOIN variedades_producto v ON rp.id_variedad = v.id INNER JOIN tipos_producto t ON t.id = v.id_tipo WHERE rp.id = $id_reserva) as q4
              )";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);

        if ($ww["estado"] < 0) {
            echo "cancelada:";
        } else {
            mysqli_autocommit($con, false);
            $disponible = ((int) $ww["cantidad_stock"] - (int) $ww["cantidad_reservada"]);
            if ((int) $disponible >= (int) $cantidad) { //HAY STOCK DISPONIBLE
                $query = "INSERT INTO entregas_stock (
                    cantidad,
                    fecha,
                    id_reserva
                ) VALUES (
                    $cantidad,
                    NOW(),
                    $id_reserva
                )";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . $query;
                }

                $query = "UPDATE reservas_productos SET estado = 2, visto = 1, comentario_empresa = '$comentario' WHERE id = $id_reserva;";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }

                $id_producto = $ww["codigo"] . str_pad($ww["id_variedad"], 2, '0', STR_PAD_LEFT);
                $query = "SELECT pr.id_product, pr.reference, st.quantity, st.physical_quantity, st.reserved_quantity FROM ps_stock_available st INNER JOIN ps_product pr ON st.id_product = pr.id_product WHERE pr.reference = '$id_producto';";

                $estaEnTienda = false;
                $val2 = mysqli_query($con_tienda, $query);
                if ($val2 && mysqli_num_rows($val2)) {
                    $vt = mysqli_fetch_assoc($val2);
                    mysqli_autocommit($con_tienda, false);
                    $id_product_tienda = $vt["id_product"];
                    $query = "UPDATE ps_stock_available SET physical_quantity = physical_quantity - $cantidad WHERE id_product = $id_product_tienda;";
                    $estaEnTienda = true;
                    if (!mysqli_query($con_tienda, $query)) {
                        $errors[] = mysqli_error($con_tienda);
                    }
                }

                if (count($errors) === 0) {
                    if (!$estaEnTienda){
                        if (mysqli_commit($con)){
                            echo "success";
                        }
                        else {
                            mysqli_rollback($con);
                        }   
                    }
                    else{
                        if (mysqli_commit($con) && mysqli_commit($con_tienda)){
                            echo "success";
                        }
                        else {
                            mysqli_rollback($con);
                            mysqli_rollback($con_tienda);
                        }   
                    }
                } else {
                    if (!$estaEnTienda){
                        mysqli_rollback($con);
                    }
                    else{
                        mysqli_rollback($con);
                        mysqli_rollback($con_tienda);
                    }
                    print_r($errors);
                }
                
            } else {
                echo "max:" . ($disponible <= 0 ? "0" : $disponible);
            }
        }
    }
    mysqli_close($con);
    mysqli_close($con_tienda);
} else if ($consulta == "check_reservas_nuevas") {
    $query = "SELECT r.id as id_reserva, cl.nombre as nombre_cliente FROM reservas_productos r INNER JOIN clientes cl ON r.id_cliente = cl.id_cliente WHERE r.estado = 0 AND r.visto = 0 ORDER BY r.id DESC LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        echo json_encode(array(
            "nombre_cliente" => $ww["nombre_cliente"],
            "id_reserva" => $ww["id_reserva"],
        )
        );
    }
}
