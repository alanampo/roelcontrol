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
        WHERE r.id_variedad = v.id AND (r.estado = 0 OR r.estado = 1)) as cantidad_reservada,
  (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e
        INNER JOIN reservas_productos rp ON e.id_reserva_producto = rp.id
        WHERE rp.id_variedad = v.id AND rp.estado = 2) as cantidad_entregada
  FROM stock_productos s
  INNER JOIN articulospedidos ap
  ON s.id_artpedido = ap.id
  INNER JOIN variedades_producto v
  ON v.id = ap.id_variedad
  INNER JOIN tipos_producto t
  ON t.id = v.id_tipo
  WHERE ap.estado = 8 
  GROUP BY v.id;
          ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Stock Actual</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-success' onclick='modalReservar()'><i class='fa fa-shopping-basket'></i> CREAR RESERVA</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Producto</th><th>Stock Real</th><th>Disponible para Reservar</th><th></th>";
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
                  <td>
                  <div class='d-flex flex-row justify-content-center' style='gap:5px;'>  
                    <button onclick='modalEditStock($ww[id_variedad])' class='btn btn-primary btn-sm'><i class='fa fa-edit'></i></button>
                  </div>
                    </td>
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
} else if ($consulta == "get_productos_para_reserva") {
    $query = "SELECT
        v.id as id_variedad,
        v.nombre as nombre_variedad,
        t.codigo,
        v.id_interno,
        (SUM(s.cantidad) - 
         IFNULL((SELECT SUM(r.cantidad) FROM reservas_productos r WHERE r.id_variedad = v.id AND r.estado >= 0), 0)
        ) as disponible
    FROM stock_productos s
    INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id
    INNER JOIN variedades_producto v ON v.id = ap.id_variedad
    INNER JOIN tipos_producto t ON t.id = v.id_tipo
    WHERE ap.estado = 8
    GROUP BY v.id
    HAVING disponible > 0;
    ";
    $val = mysqli_query($con, $query);
    $productos = array();
    while ($ww = mysqli_fetch_assoc($val)) {
        $productos[] = $ww;
    }
    echo json_encode($productos);
} else if ($consulta == "get_clientes") {
    try {
        $query = "SELECT ID_CLIENTE, nombre FROM clientes WHERE activo = 1 ORDER BY nombre ASC";
        $val = mysqli_query($con, $query);
        $clientes = array();
        while ($ww = mysqli_fetch_assoc($val)) {
            $clientes[] = $ww;
        }
        echo json_encode($clientes);
    } catch (\Throwable $th) {
        echo "error: " . $th->getMessage()." ".$th->getTraceAsString() ;
    }

} else if ($consulta == "busca_reservas") {
    $estados_filter = "";
    if (isset($_POST["estados"]) && !empty($_POST["estados"])) {
        $selected_estados = json_decode($_POST["estados"], true);
        if (!empty($selected_estados)) {
            // Sanitize each state value
            $sanitized_estados = array_map('intval', $selected_estados);
            $estados_list = implode(",", $sanitized_estados);
            $estados_filter = " AND (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) IN ($estados_list)";
        }
    }

    $query = "SELECT
            r.id as id_reserva,
            cl.nombre as nombre_cliente,
            cl.id_cliente,
            r.observaciones,
            r.observaciones_picking,
            r.observaciones_packing,
            u.nombre_real as nombre_usuario,
            DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(r.fecha, '%Y%m%d%H%i') as fecha_raw,
            (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado
            FROM
            reservas r
            INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
            LEFT JOIN usuarios u ON u.id = r.id_usuario
            WHERE 1=1 " . $estados_filter . "
            ORDER BY r.fecha DESC
            ;
        ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Reservas</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-reservas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Fecha Reserva</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_reserva = $ww['id_reserva'];
            $all_rp_comments_for_this_reservation = [];

            $productos_query = "SELECT 
                                    rp.id as id_reserva_producto,
                                    v.nombre as nombre_variedad,
                                    t.codigo,
                                    v.id_interno,
                                    rp.cantidad,
                                    rp.id_variedad,
                                    rp.comentario,
                                    rp.comentario_empresa,
                                    (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_entregada,
                                    rp.estado,
                                    (
                                        (SELECT IFNULL(SUM(s.cantidad),0) 
                                         FROM stock_productos s 
                                         INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id 
                                         WHERE ap.id_variedad = rp.id_variedad AND ap.estado = 8) 
                                        - 
                                        (SELECT IFNULL(SUM(r.cantidad),0) 
                                         FROM reservas_productos r 
                                         WHERE r.id_variedad = rp.id_variedad AND r.estado >= 0 AND r.id != rp.id)
                                    ) as stock_disponible
                                FROM reservas_productos rp
                                INNER JOIN variedades_producto v ON v.id = rp.id_variedad
                                INNER JOIN tipos_producto t ON t.id = v.id_tipo
                                WHERE rp.id_reserva = $id_reserva";

            $productos_result = mysqli_query($con, $productos_query);
            $productos_html = "<ul class='list-group'>";
            $productos_pendientes = 0;

            while ($producto = mysqli_fetch_array($productos_result)) {
                $estado_producto = boxEstadoReserva($producto['estado'], true);
                $cantidad_entregada_info = $producto['cantidad_entregada'] > 0 ? " (Entregado: {$producto['cantidad_entregada']})" : "";
                $cantidad_pendiente = $producto['cantidad'] - $producto['cantidad_entregada'];

                if (!empty($producto['comentario'])) {
                    $all_rp_comments_for_this_reservation[] = $producto['comentario'];
                }
                if (!empty($producto['comentario_empresa'])) {
                    $all_rp_comments_for_this_reservation[] = $producto['comentario_empresa'];
                }

                $botones_producto = "<div class='d-flex flex-column' style='gap: 5px;'>";
                if ($producto['estado'] < 4) { // General condition for buttons
                    if ($producto['estado'] != 2) {
                        $productos_pendientes++;
                    }
                    if(in_array($producto['estado'], [0, 1])) { // PENDIENTE or EN PROCESO
                        $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)' class='btn btn-info btn-sm'><i class='fa fa-search'></i> A REVISIÓN</button>";
                    }
                    if(in_array($producto['estado'], [0, 1, 3])) { // PENDIENTE, EN PROCESO or REVISAR STOCK
                        $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 4)' class='btn btn-primary btn-sm'><i class='fa fa-arrow-right'></i> A PICKING</button>";
                    }
                }
                $botones_producto .= "</div>";

                $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']}{$cantidad_entregada_info} <span class='badge' style='background-color: unset;color:black;'>{$estado_producto}</span></div>";
                $productos_html .= $botones_producto;
                $productos_html .= "</li>";
            }
            $productos_html .= "</ul>";

            $estado_general = boxEstadoReserva($ww["estado"], true);
            
            $btn_quick_picking = "";
            if($productos_pendientes > 0){
                 $btn_quick_picking = "<button onclick='enviarAPickingReserva($id_reserva)' class='btn btn-primary btn-sm mb-2' title='Enviar a Picking'><i class='fa fa-arrow-right'></i></button>";
            }
            
            $btn_entrega_rapida = "";
            // if($productos_pendientes > 0){
            //     $btn_entrega_rapida = "<button onclick='entregaRapida($id_reserva)' class='btn btn-success btn-sm mb-2' title='Entrega Rápida'><i class='fa fa-rocket'></i></button>";
            // }

            $btn_cancelar = ($ww["estado"] < 2 ? "<button onclick='cancelarReserva($id_reserva)' class='btn btn-danger btn-sm mb-2' title='Cancelar Reserva'><i class='fa fa-ban'></i></button>" : "");

            $final_observaciones_to_display = $ww['observaciones'];
            $obs_general_text = htmlentities($ww['observaciones'], ENT_QUOTES, 'UTF-8');
            $obs_picking_text = !empty($ww['observaciones_picking']) ? "<br><small><strong>Picking:</strong> " . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . "</small>" : "";
            $obs_packing_text = !empty($ww['observaciones_packing']) ? "<br><small><strong>Packing:</strong> " . htmlentities($ww['observaciones_packing'], ENT_QUOTES, 'UTF-8') . "</small>" : "";

            echo "
            <tr class='text-center'>
              <td><small>$id_reserva</small></td>
              <td><span style='display:none'>$ww[fecha_raw]</span>$ww[fecha]</td>
              <td>$ww[nombre_cliente] ($ww[id_cliente])</td>
              <td>$ww[nombre_usuario]</td>
              <td class='text-left'>$productos_html</td>
              <td class='text-left'>
                <div>
                  {$obs_general_text} 
                  <button class='btn btn-default btn-xs' onclick='modalEditarObservacionGeneral(\"$id_reserva\", \"$obs_general_text\")'><i class='fa fa-pencil'></i></button>
                </div>
                {$obs_picking_text}
                {$obs_packing_text}
              </td>
              <td>{$estado_general}</td>
              <td>
                <div class='d-flex flex-column'>
                  $btn_quick_picking
                  $btn_entrega_rapida
                  $btn_cancelar
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
        mysqli_autocommit($con, false);
        $errors = array();

        // Verificar que la reserva no esté ya cancelada o entregada
        $query_check = "SELECT * FROM reservas_productos WHERE id_reserva = $id_reserva AND estado >= 2";
        $res_check = mysqli_query($con, $query_check);
        if (mysqli_num_rows($res_check) > 0) {
            $errors[] = "La reserva contiene productos que ya fueron entregados, no se puede cancelar.";
        }

        if (count($errors) == 0) {
            // Actualizar estado de todos los productos de la reserva a cancelada (-1)
            $query = "UPDATE reservas_productos SET estado = -1 WHERE id_reserva = $id_reserva";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode(", ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
} else if ($consulta == "cambiar_estado_producto") {
    $id_reserva_producto = $_POST["id_reserva_producto"];
    $estado = $_POST["estado"];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query = "UPDATE reservas_productos SET estado = $estado WHERE id = $id_reserva_producto";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode(", ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
} else if ($consulta == "enviar_a_picking_reserva") {
    $id_reserva = $_POST["id_reserva"];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query = "UPDATE reservas_productos SET estado = 4 WHERE id_reserva = $id_reserva AND estado < 2";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode(", ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
}
else if ($consulta == "enviar_a_packing_reserva") {
    $id_reserva = $_POST["id_reserva"];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        // Update all products for this reservation to state 5 (A PACKING)
        // only if their current state is 4 (A PICKING)
        $query = "UPDATE reservas_productos SET estado = 5 WHERE id_reserva = $id_reserva AND estado = 4";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode(", ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
}
else if ($consulta == "entrega_rapida") {
    $id_reserva = $_POST["id_reserva"];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query_productos = "SELECT 
                                rp.id as id_reserva_producto,
                                rp.cantidad,
                                rp.id_variedad,
                                (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_entregada,
                                (
                                    (SELECT IFNULL(SUM(s.cantidad),0) 
                                     FROM stock_productos s 
                                     INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id 
                                     WHERE ap.id_variedad = rp.id_variedad AND ap.estado = 8) 
                                    - 
                                    (SELECT IFNULL(SUM(r.cantidad),0) 
                                     FROM reservas_productos r 
                                     WHERE r.id_variedad = rp.id_variedad AND r.estado >= 0 AND r.id != rp.id)
                                ) as stock_disponible
                            FROM reservas_productos rp
                            WHERE rp.id_reserva = $id_reserva AND rp.estado = 5";
        
        $productos_result = mysqli_query($con, $query_productos);

        if(mysqli_num_rows($productos_result) > 0){
            $productos_a_entregar = [];
            while($producto = mysqli_fetch_assoc($productos_result)){
                $cantidad_pendiente = $producto['cantidad'] - $producto['cantidad_entregada'];
                if($cantidad_pendiente > 0){
                    if($producto['stock_disponible'] < $cantidad_pendiente){
                        $errors[] = "Stock insuficiente para el producto con ID de variedad: {$producto['id_variedad']}. Solicitado: $cantidad_pendiente, Disponible: {$producto['stock_disponible']}";
                    }
                    $productos_a_entregar[] = $producto;
                }
            }

            if(count($errors) == 0){
                foreach($productos_a_entregar as $producto){
                    $id_reserva_producto = $producto['id_reserva_producto'];
                    $cantidad_pendiente = $producto['cantidad'] - $producto['cantidad_entregada'];

                    $query_entrega = "INSERT INTO entregas_stock (cantidad, fecha, id_reserva_producto) VALUES ($cantidad_pendiente, NOW(), $id_reserva_producto)";
                    if (!mysqli_query($con, $query_entrega)) {
                        $errors[] = "Error al registrar entrega para el producto $id_reserva_producto: " . mysqli_error($con);
                    }

                    $query_update = "UPDATE reservas_productos SET estado = 2 WHERE id = $id_reserva_producto;";
                    if (!mysqli_query($con, $query_update)) {
                        $errors[] = "Error al actualizar estado para el producto $id_reserva_producto: " . mysqli_error($con);
                    }
                }
            }
        } else {
            $errors[] = "No se encontraron productos pendientes de entrega para esta reserva.";
        }


        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode(", ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
} else if ($consulta == "guardar_entrega") {
    $id_reserva_producto = $_POST["id_reserva_producto"];
    $cantidad = mysqli_real_escape_string($con, $_POST["cantidad"]);
    $comentario = mysqli_real_escape_string($con, $_POST["comentario"]);
    $errors = array();

    $query = "SELECT 
        rp.cantidad as cantidad_reservada,
        rp.id_variedad,
        (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_ya_entregada,
        (
            (SELECT IFNULL(SUM(s.cantidad),0) 
             FROM stock_productos s 
             INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id 
             WHERE ap.id_variedad = rp.id_variedad AND ap.estado = 8) 
            - 
            (SELECT IFNULL(SUM(r.cantidad),0) 
             FROM reservas_productos r 
             WHERE r.id_variedad = rp.id_variedad AND r.estado >= 0 AND r.id != rp.id)
        ) as stock_disponible,
        rp.estado
        FROM reservas_productos rp
        WHERE rp.id = $id_reserva_producto";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);

        if ($ww["estado"] < 0) {
            echo "cancelada:";
        } else {
            mysqli_autocommit($con, false);

            $disponible_para_entregar = $ww["cantidad_reservada"] - $ww["cantidad_ya_entregada"];
            $stock_real_disponible = $ww["stock_disponible"];

            if ((int) $stock_real_disponible >= (int) $cantidad && (int) $disponible_para_entregar >= (int) $cantidad) {
                // Insertar entrega
                $query_entrega = "INSERT INTO entregas_stock (cantidad, fecha, id_reserva_producto) VALUES ($cantidad, NOW(), $id_reserva_producto)";
                if (!mysqli_query($con, $query_entrega)) {
                    $errors[] = mysqli_error($con) . $query_entrega;
                }

                // Actualizar estado de la reserva
                $total_entregado = $ww["cantidad_ya_entregada"] + $cantidad;
                $nuevo_estado = ($total_entregado >= $ww["cantidad_reservada"]) ? 2 : 1;

                $query_update = "UPDATE reservas_productos SET estado = $nuevo_estado, comentario_empresa = '$comentario' WHERE id = $id_reserva_producto;";
                if (!mysqli_query($con, $query_update)) {
                    $errors[] = mysqli_error($con);
                }

                if (count($errors) === 0) {
                    if (mysqli_commit($con)) {
                        echo "success";
                    } else {
                        mysqli_rollback($con);
                        echo "error: No se pudo confirmar la transacción";
                    }
                } else {
                    mysqli_rollback($con);
                    echo "error: " . implode(", ", $errors);
                }

            } else {
                echo "max:" . min($disponible_para_entregar, $stock_real_disponible);
            }
        }
    } else {
        echo "error: No se encontró el producto de la reserva";
    }

    mysqli_close($con);
} else if ($consulta == "check_reservas_nuevas") {
    if (is_array($_SESSION["arraypermisos"]) && !in_array("pedidos", $_SESSION["arraypermisos"]))
        return;

    $query = "SELECT r.id as id_reserva, cl.nombre as nombre_cliente FROM reservas_productos r INNER JOIN clientes cl ON r.id_cliente = cl.id_cliente WHERE r.estado = 0 AND r.visto = 0 ORDER BY r.id DESC LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        echo json_encode(
            array(
                "nombre_cliente" => $ww["nombre_cliente"],
                "id_reserva" => $ww["id_reserva"],
            )
        );
    }
}
else if ($consulta == "update_general_observacion") {
    $id_reserva = $_POST["id_reserva"];
    $observaciones = mysqli_real_escape_string($con, $_POST["observaciones"]);

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query = "UPDATE reservas SET observaciones = '$observaciones' WHERE id = $id_reserva";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode(", ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
}
else if ($consulta == "get_stock_variedad") {
    $id_variedad = $_POST["id_variedad"];

    $query = "SELECT
    p.id_interno as id_pedido_interno,
    t.nombre as nombre_tipo,
    v.nombre as nombre_variedad,
    c.nombre as nombre_cliente,
    t.id as id_tipo,
    c.id_cliente,
    p.fecha,
    p.id_pedido,
    ap.id as id_artpedido,
    ap.cant_plantas,
    ap.cant_bandejas,
    ap.tipo_bandeja,
    t.codigo,
    v.id_interno,
    ap.estado,
    DATE_FORMAT(p.fecha, '%m/%d') AS mes_dia,
    u.iniciales,
    e.nombre as nombre_especie,
    ap.id_especie,
    DATE_FORMAT(ap.fecha_entrega, '%d/%m/%y') as fecha_entrega_solicitada,
    mp.cantidad as cantidad_mesada,
    GREATEST(0, IFNULL(s.cantidad,0) -
        ROUND(IFNULL(s.cantidad,0) *
            ((SELECT IFNULL(SUM(r.cantidad),0)
              FROM reservas_productos r
              WHERE r.id_variedad = v.id AND (r.estado = 0 OR r.estado = 1)) +
             (SELECT IFNULL(SUM(e.cantidad),0)
              FROM entregas_stock e
              INNER JOIN reservas_productos r2 ON e.id_reserva = r2.id
              WHERE r2.id_variedad = v.id AND r2.estado = 2)) /
            NULLIF((SELECT SUM(s2.cantidad)
                    FROM stock_productos s2
                    INNER JOIN articulospedidos ap2 ON s2.id_artpedido = ap2.id
                    WHERE ap2.id_variedad = v.id AND ap2.estado = 8), 0)
        )
    ) AS stock_actual
    FROM articulospedidos ap
    LEFT JOIN mesadas_productos mp ON mp.id_artpedido = ap.id
    LEFT JOIN stock_productos s ON s.id_artpedido = ap.id
    INNER JOIN variedades_producto v ON v.id = ap.id_variedad
    INNER JOIN tipos_producto t ON t.id = v.id_tipo
    INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
    INNER JOIN clientes c ON c.id_cliente = p.id_cliente
    LEFT JOIN usuarios u ON u.id = p.id_usuario
    LEFT JOIN especies_provistas e ON e.id = ap.id_especie
    WHERE v.id = $id_variedad
    AND ap.estado = 8
    ORDER BY p.id_pedido, ap.id";

    try {
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($ww = mysqli_fetch_array($val)) {
                $id_cliente = $ww['id_cliente'];
                $id_pedido = $ww['id_pedido'];
                $id_artpedido = $ww['id_artpedido'];

                // Generar ID del producto
                $especie = $ww["nombre_especie"] ? $ww["nombre_especie"] : "";
                $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
                $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);

                // Nombre del producto con especie
                $producto = "$ww[nombre_variedad] ($ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . ")";
                if ($especie) {
                    $producto .= " <span class='text-primary'>$especie</span>";
                }

                $cliente = $ww['nombre_cliente'] . " ($id_cliente)";
                $estado = generarBoxEstado($ww["estado"], $ww["codigo"], true);

                // Usar stock actual en lugar de cantidad_info
                $stock_actual = $ww['stock_actual'] ? $ww['stock_actual'] : 0;

                // Crear input editable para el stock
                $stock_input = "
    <div class='d-flex align-items-center justify-content-center'>
        <span class='badge bg-success text-white me-2 mr-2' style='min-width:60px;font-size:14px;'>Real actual: $stock_actual</span>

        <div class='form-check form-check-inline'>
            <input class='form-check-input' type='radio' name='accion-$id_artpedido' id='sumar-$id_artpedido' value='sumar' checked>
            <label class='form-check-label' for='sumar-$id_artpedido'>Sumar</label>
        </div>
        <div class='form-check form-check-inline'>
            <input class='form-check-input' type='radio' name='accion-$id_artpedido' id='restar-$id_artpedido' value='restar'>
            <label class='form-check-label' for='restar-$id_artpedido'>Restar</label>
        </div>

        <input type='number' 
               id='cantidad-ajuste-$id_artpedido' 
               class='form-control form-control-sm text-center mx-2' 
               style='width: 80px;' 
               min='0' value='0'>

        <button onclick='guardarStockArticulo($id_artpedido, $id_variedad)' 
                class='btn btn-success btn-sm' 
                title='Guardar stock'>
            <i class='fa fa-save'></i>
        </button>
    </div>";


                $onclick = "onClick='MostrarModalEstado($ww[id_artpedido], \"$id_producto\", \"$ww[nombre_cliente]\", $id_cliente)'";

                echo "<tr style='cursor:pointer;' x-codigo='$id_producto' x-etapa='$ww[estado]' x-id-artpedido='$id_artpedido'>";

                // Columnas según el header de tu tabla HTML:
                // Orden
                echo "<td $onclick style='text-align: center;color:#1F618D; font-weight:bold; font-size:1.0em'>$id_producto</td>";

                // Producto
                echo "<td $onclick>$producto</td>";

                // Stock Actual (input editable) - Esta columna no tiene onclick para permitir edición
                echo "<td style='text-align: center;'>$stock_input</td>";

                // Cliente
                echo "<td $onclick>$cliente</td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4' class='text-center'><div class='callout callout-danger'><b>No se encontraron productos en esta mesada.</b></div></td></tr>";
        }
    } catch (\Throwable $th) {
        echo $th->getMessage();
    }

} else if ($consulta == "actualizar_stock_articulo") {
    $id_artpedido = $_POST["id_artpedido"];
    $id_variedad = $_POST["id_variedad"];
    $accion = $_POST["accion"]; // sumar o restar
    $cantidad = (int) $_POST["cantidad"];

    if (!is_numeric($id_artpedido) || !is_numeric($id_variedad) || $cantidad < 0) {
        echo "error:Datos inválidos";
        exit;
    }

    // Validar existencia
    $check_query = "SELECT ap.id, ap.estado 
                    FROM articulospedidos ap 
                    WHERE ap.id = $id_artpedido 
                    AND ap.id_variedad = $id_variedad 
                    AND ap.estado = 8";
    $check_result = mysqli_query($con, $check_query);
    if (mysqli_num_rows($check_result) == 0) {
        echo "error:El artículo no existe o no está en estado de stock";
        exit;
    }

    // Verificar si ya existe stock y calcular cantidades comprometidas
    $stock_check = "SELECT
        s.id,
        s.cantidad as stock_fisico,
        ROUND(s.cantidad *
            ((SELECT IFNULL(SUM(r.cantidad),0)
              FROM reservas_productos r
              WHERE r.id_variedad = $id_variedad AND (r.estado = 0 OR r.estado = 1)) +
             (SELECT IFNULL(SUM(e.cantidad),0)
              FROM entregas_stock e
              INNER JOIN reservas_productos r2 ON e.id_reserva = r2.id
              WHERE r2.id_variedad = $id_variedad AND r2.estado = 2)) /
            NULLIF((SELECT SUM(s2.cantidad)
                    FROM stock_productos s2
                    INNER JOIN articulospedidos ap2 ON s2.id_artpedido = ap2.id
                    WHERE ap2.id_variedad = $id_variedad AND ap2.estado = 8), 0)
        ) as cantidad_comprometida
        FROM stock_productos s
        WHERE s.id_artpedido = $id_artpedido";
    $stock_result = mysqli_query($con, $stock_check);

    if (mysqli_num_rows($stock_result) > 0) {
        $row = mysqli_fetch_assoc($stock_result);
        $stock_fisico_actual = (int) $row["stock_fisico"];
        $cantidad_comprometida = (int) $row["cantidad_comprometida"];

        if ($accion === "sumar") {
            $update_query = "UPDATE stock_productos
                         SET cantidad = cantidad + $cantidad
                         WHERE id_artpedido = $id_artpedido";
        } else {
            // Validar que no se reste más del stock físico disponible
            $stock_fisico_resultante = $stock_fisico_actual - $cantidad;

            if ($stock_fisico_resultante < $cantidad_comprometida) {
                $disponible_para_restar = $stock_fisico_actual - $cantidad_comprometida;
                echo "error:No se puede restar $cantidad. Solo hay $disponible_para_restar disponibles para restar (stock físico: $stock_fisico_actual - comprometido: $cantidad_comprometida)";
                exit;
            }

            $update_query = "UPDATE stock_productos
                         SET cantidad = cantidad - $cantidad
                         WHERE id_artpedido = $id_artpedido";
        }


        if (mysqli_query($con, $update_query)) {
            echo "success";
        } else {
            echo "error:Error al actualizar en la base de datos - " . mysqli_error($con);
        }
    } else {
        // Si no existe, solo permitimos sumar (crear stock)
        if ($accion === "restar") {
            echo "error:No se puede restar stock de un artículo sin registro";
            exit;
        }

        $insert_query = "INSERT INTO stock_productos (id_artpedido, cantidad) 
                         VALUES ($id_artpedido, $cantidad)";
        if (mysqli_query($con, $insert_query)) {
            echo "success";
        } else {
            echo "error:Error al insertar en la base de datos - " . mysqli_error($con);
        }
    }
} else if ($consulta == "guardar_reserva") {
    $id_cliente = $_POST["id_cliente"];
    $observaciones = mysqli_real_escape_string($con, $_POST["observaciones"]);
    $productos = json_decode($_POST["productos"], true);

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        // 1. Validar stock para todos los productos ANTES de insertar nada.
        foreach ($productos as $producto) {
            $id_variedad = (int) $producto['id_variedad'];
            $cantidad_solicitada = (int) $producto['cantidad'];

            $query_stock = "SELECT
                (SUM(s.cantidad) - 
                 IFNULL((SELECT SUM(r.cantidad) FROM reservas_productos r WHERE r.id_variedad = $id_variedad AND r.estado >= 0), 0)
                ) as disponible
                FROM stock_productos s
                INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id
                WHERE ap.id_variedad = $id_variedad AND ap.estado = 8";

            $res_stock = mysqli_query($con, $query_stock);
            $stock_data = mysqli_fetch_assoc($res_stock);
            $disponible = (int) $stock_data['disponible'];

            if ($disponible < $cantidad_solicitada) {
                $errors[] = "Stock insuficiente para el producto con ID $id_variedad. Solicitado: $cantidad_solicitada, Disponible: $disponible";
            }
        }

        if (count($errors) > 0) {
            mysqli_rollback($con);
            echo "error: " . implode("; ", $errors);
            exit;
        }

        // 2. Si hay stock para todo, proceder con las inserciones.
        $query_reserva = "INSERT INTO reservas (fecha, id_cliente, observaciones, id_usuario) VALUES (NOW(), $id_cliente, '$observaciones', $_SESSION[id_usuario])";

        if (!mysqli_query($con, $query_reserva)) {
            $errors[] = "Error al crear la reserva: " . mysqli_error($con);
        } else {
            $id_reserva = mysqli_insert_id($con);

            foreach ($productos as $producto) {
                $id_variedad = (int) $producto['id_variedad'];
                $cantidad = (int) $producto['cantidad'];

                $query_producto = "INSERT INTO reservas_productos (id_reserva, id_variedad, cantidad, estado, origen, id_usuario) VALUES ($id_reserva, $id_variedad, $cantidad, 0, 'ADMINISTRACION', $_SESSION[id_usuario])";

                if (!mysqli_query($con, $query_producto)) {
                    $errors[] = "Error al reservar producto ID $id_variedad: " . mysqli_error($con);
                }
            }
        }

        // 3. Commit o Rollback final.
        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción.";
            }
        } else {
            mysqli_rollback($con);
            echo "error: " . implode("; ", $errors);
        }

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    } finally {
        mysqli_close($con);
    }
}
else if ($consulta == "busca_picking") {
    $query = "SELECT r.*, cl.nombre as nombre_cliente, u.nombre_real as nombre_usuario,
              (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado_general,
              DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha_formatted
              FROM reservas r
              INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
              LEFT JOIN usuarios u ON u.id = r.id_usuario
              WHERE EXISTS (SELECT 1 FROM reservas_productos rp WHERE rp.id_reserva = r.id AND rp.estado = 4)
              ORDER BY r.fecha DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Reservas en Picking</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-primary btn-sm' onclick='printTable(\"tabla-picking\")'><i class='fa fa-print'></i> IMPRIMIR</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-picking' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        // Table headers
        echo "<thead><tr><th>ID</th><th>Fecha Reserva</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th></tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_reserva = $ww['id'];
            $productos_pendientes_picking = 0;

            $productos_query = "SELECT rp.id as id_reserva_producto, v.nombre as nombre_variedad, t.codigo, v.id_interno, rp.cantidad, rp.estado,
                                  (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_entregada
                                  FROM reservas_productos rp
                                  INNER JOIN variedades_producto v ON v.id = rp.id_variedad
                                  INNER JOIN tipos_producto t ON t.id = v.id_tipo
                                  WHERE rp.id_reserva = $id_reserva";
            
            $productos_result = mysqli_query($con, $productos_query);
            $productos_html = "<ul class='list-group'>";

            while ($producto = mysqli_fetch_array($productos_result)) {
                if($producto['estado'] == 4) { // LISTO PARA PICKING
                    $productos_pendientes_picking++;
                    $botones_producto = "<div class='d-flex flex-column' style='gap: 5px;'>";
                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)' class='btn btn-info btn-sm'><i class='fa fa-search'></i> A REVISIÓN</button>";
                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 5)' class='btn btn-warning btn-sm'><i class='fa fa-archive'></i> A PACKING</button>";
                    $botones_producto .= "</div>";

                    $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                    $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']} <span class='badge' style='background-color: unset;color:black;'>".boxEstadoReserva($producto['estado'], true)."</span></div>";
                    $productos_html .= $botones_producto;
                    $productos_html .= "</li>";
                }
            }
            $productos_html .= "</ul>";

            if($productos_pendientes_picking > 0){
                $btn_quick_packing = "<button onclick='enviarAPackingReserva($id_reserva)' class='btn btn-warning btn-sm mb-2' title='Enviar a Packing'><i class='fa fa-archive'></i></button>";

                echo "<tr class='text-center'>";
                echo "<td><small>$id_reserva</small></td>";
                echo "<td>{$ww['fecha_formatted']}</td>";
                echo "<td>{$ww['nombre_cliente']} ({$ww['id_cliente']})</td>";
                echo "<td>{$ww['nombre_usuario']}</td>";
                echo "<td class='text-left'>$productos_html</td>";
                echo "<td class='text-left'>{$ww['observaciones']}</td>";
                echo "<td>".boxEstadoReserva($ww['estado_general'], true)."</td>";
                echo "<td><div class='d-flex flex-column'>$btn_quick_packing</div></td>";
                echo "</tr>";
            }
        }
        echo "</tbody></table></div></div>";
    } else {
        echo "<div class='callout callout-info'><b>No se encontraron reservas en la etapa de picking.</b></div>";
    }
}
else if ($consulta == "busca_packing") {

    $query = "SELECT r.*, cl.nombre as nombre_cliente, u.nombre_real as nombre_usuario,
              (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado_general,
              DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha_formatted
              FROM reservas r
              INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
              LEFT JOIN usuarios u ON u.id = r.id_usuario
              WHERE EXISTS (SELECT 1 FROM reservas_productos rp WHERE rp.id_reserva = r.id AND rp.estado = 5)
              ORDER BY r.fecha DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Reservas en Packing</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-primary btn-sm' onclick='printTable(\"tabla-packing\")'><i class='fa fa-print'></i> IMPRIMIR</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-packing' class='table table-bordered table-responsive w-100 d-block d-md-table'>";

        echo "<thead><tr><th>ID</th><th>Fecha Reserva</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th></tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_reserva = $ww['id'];
            $productos_pendientes_packing = 0;

            $productos_query = "SELECT rp.id as id_reserva_producto, v.nombre as nombre_variedad, t.codigo, v.id_interno, rp.cantidad, rp.estado,
                                   (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_entregada,
                                   (
                                       (SELECT IFNULL(SUM(s.cantidad),0)
                                        FROM stock_productos s 
                                        INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id 
                                        WHERE ap.id_variedad = rp.id_variedad AND ap.estado = 8) 
                                       - 
                                       (SELECT IFNULL(SUM(r.cantidad),0)
                                        FROM reservas_productos r 
                                        WHERE r.id_variedad = rp.id_variedad AND r.estado >= 0 AND r.id != rp.id)
                                   ) as stock_disponible
                                 FROM reservas_productos rp
                                 INNER JOIN variedades_producto v ON v.id = rp.id_variedad
                                 INNER JOIN tipos_producto t ON t.id = v.id_tipo
                                 WHERE rp.id_reserva = $id_reserva";

            $productos_result = mysqli_query($con, $productos_query);
            $productos_html = "<ul class='list-group'>";

            while ($producto = mysqli_fetch_array($productos_result)) {
                if($producto['estado'] == 5) { 
                    $productos_pendientes_packing++;
                    $botones_producto = "<div class='d-flex flex-column' style='gap:5px;'>";
                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)' class='btn btn-info btn-sm'><i class='fa fa-search'></i> A REVISIÓN</button>";

                    $stock_disponible_real = $producto['stock_disponible'] + ($producto['cantidad'] - $producto['cantidad_entregada']);

                    $botones_producto .= "<button onclick='entregarProducto({$producto['id_reserva_producto']}, \"{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']})\", {$producto['cantidad']} - {$producto['cantidad_entregada']}, $stock_disponible_real)' class='btn btn-success btn-sm'><i class='fa fa-truck'></i> ENTREGAR</button>";
                    $botones_producto .= "</div>";

                    $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                    $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']} <span class='badge' style='background-color: unset;color:black;'>".boxEstadoReserva($producto['estado'], true)."</span></div>";
                    $productos_html .= $botones_producto;
                    $productos_html .= "</li>";
                }
            }

            $productos_html .= "</ul>";

            if($productos_pendientes_packing > 0){
                $btn_quick_entrega = "<button onclick='entregaRapida($id_reserva)' class='btn btn-success btn-sm mb-2' title='Entrega Rápida'><i class='fa fa-truck'></i></button>";

                echo "<tr class='text-center'>";
                echo "<td><small>$id_reserva</small></td>";
                echo "<td>{$ww['fecha_formatted']}</td>";
                echo "<td>{$ww['nombre_cliente']} ({$ww['id_cliente']})</td>";
                echo "<td>{$ww['nombre_usuario']}</td>";
                echo "<td class='text-left'>$productos_html</td>";
                echo "<td class='text-left'>{$ww['observaciones']}</td>";
                echo "<td>".boxEstadoReserva($ww['estado_general'], true)."</td>";
                echo "<td><div class='d-flex flex-column'>$btn_quick_entrega</div></td>";
                echo "</tr>";
            }
        }

        echo "</tbody></table></div></div>";
    } else {
        echo "<div class='callout callout-info'><b>No se encontraron reservas en la etapa de packing.</b></div>";
    }
}
