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

// Función helper para obtener atributos de una variedad
function getAtributosVariedad($con, $id_variedad) {
    $query = "SELECT av.valor, a.nombre as nombre_atributo
              FROM atributos_valores_variedades avv
              INNER JOIN atributos_valores av ON avv.id_atributo_valor = av.id
              INNER JOIN atributos a ON av.id_atributo = a.id
              WHERE avv.id_variedad = $id_variedad
              ORDER BY a.nombre ASC";

    $result = mysqli_query($con, $query);
    $atributos_html = "";

    if ($result && mysqli_num_rows($result) > 0) {
        $atributos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $atributos[] = "<span class='badge badge-secondary' style='background-color: #6c757d; color: white; font-size: 0.85em; margin-right: 3px;'>{$row['valor']}</span>";
        }
        if (!empty($atributos)) {
            $atributos_html = " " . implode("", $atributos);
        }
    }

    return $atributos_html;
}

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
        echo "<button class='btn btn-success' onclick='modalReservar()'><i class='fa fa-shopping-basket'></i> GENERAR VENTA</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Producto</th><th>Stock Real</th><th>Disponible para Comprar</th><th></th>";
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

} else if ($consulta == "busca_ventas") {
    // Eliminar automáticamente reservas con estado 100 (En espera de pago) más viejas de 5 minutos
    try {
        mysqli_autocommit($con, false);

        // Encontrar reservas con estado 100 más viejas de 5 minutos
        $query_reservas_expiradas = "SELECT r.id FROM reservas r
                                      WHERE (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) = 100
                                      AND r.fecha < DATE_SUB(NOW(), INTERVAL 5 MINUTE)";

        $result_expiradas = mysqli_query($con, $query_reservas_expiradas);
        $reservas_a_eliminar = [];

        while ($row = mysqli_fetch_assoc($result_expiradas)) {
            $reservas_a_eliminar[] = $row['id'];
        }

        if (!empty($reservas_a_eliminar)) {
            $ids_list = implode(",", $reservas_a_eliminar);

            // Obtener IDs de reservas_productos para eliminar entregas_stock
            $query_rp = "SELECT id FROM reservas_productos WHERE id_reserva IN ($ids_list)";
            $result_rp = mysqli_query($con, $query_rp);
            $rp_ids = [];
            while ($row = mysqli_fetch_assoc($result_rp)) {
                $rp_ids[] = $row['id'];
            }

            // Eliminar entregas_stock asociadas
            if (!empty($rp_ids)) {
                $rp_ids_list = implode(",", $rp_ids);
                $query_delete_entregas = "DELETE FROM entregas_stock WHERE id_reserva_producto IN ($rp_ids_list)";
                mysqli_query($con, $query_delete_entregas);
            }

            // Eliminar reservas_productos
            $query_delete_rp = "DELETE FROM reservas_productos WHERE id_reserva IN ($ids_list)";
            mysqli_query($con, $query_delete_rp);

            // Eliminar reservas
            $query_delete_reservas = "DELETE FROM reservas WHERE id IN ($ids_list)";
            mysqli_query($con, $query_delete_reservas);
        }

        mysqli_commit($con);
    } catch (\Throwable $th) {
        mysqli_rollback($con);
    }

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
            u_obs.nombre_real as usuario_obs,
            u_obs_picking.nombre_real as usuario_obs_picking,
            u_obs_packing.nombre_real as usuario_obs_packing,
            DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(r.fecha, '%Y%m%d%H%i') as fecha_raw,
            (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado
            FROM
            reservas r
            INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
            LEFT JOIN usuarios u ON u.id = r.id_usuario
            LEFT JOIN usuarios u_obs ON u_obs.id = r.id_usuario_obs
            LEFT JOIN usuarios u_obs_picking ON u_obs_picking.id = r.id_usuario_obs_picking
            LEFT JOIN usuarios u_obs_packing ON u_obs_packing.id = r.id_usuario_obs_packing
            WHERE 1=1 " . $estados_filter . "
            ORDER BY r.fecha DESC
            ;
        ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Ventas</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-reservas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th></th><th>ID</th><th>Fecha Venta</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th>";
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
                    if(in_array($producto['estado'], [0, 1])) { // PAGO ACEPTADO or EN PROCESO
                        $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)' class='btn btn-info btn-sm'><i class='fa fa-search'></i> A REVISIÓN</button>";
                    }
                    if(in_array($producto['estado'], [0, 1, 3])) { // PAGO ACEPTADO, EN PROCESO or REVISAR STOCK
                        $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 4)' class='btn btn-primary btn-sm'><i class='fa fa-arrow-right'></i> A PICKING</button>";
                    }
                }
                $botones_producto .= "</div>";

                $atributos_html = getAtributosVariedad($con, $producto['id_variedad']);

                $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center' style='border-bottom: 1px solid #d3d3d3;'>";
                $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']}{$cantidad_entregada_info} <span class='badge' style='background-color: unset;color:black;'>{$estado_producto}</span><br>{$atributos_html}</div>";
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

            $btn_cancelar = ($ww["estado"] < 2 ? "<button onclick='cancelarReserva($id_reserva)' class='btn btn-danger btn-sm mb-2' title='Cancelar Venta'><i class='fa fa-ban'></i></button>" : "");

            $btn_orden_envio = "<button onclick='modalOrdenEnvio($id_reserva)' class='btn btn-info btn-sm mb-2' title='Orden de Envío'><i class='fa fa-shipping-fast'></i> ORDEN ENVÍO</button>";

            $final_observaciones_to_display = $ww['observaciones'];
            $obs_general_text = htmlentities($ww['observaciones'], ENT_QUOTES, 'UTF-8');
            $usuario_obs_suffix = !empty($ww['usuario_obs']) ? " <small>(" . htmlentities($ww['usuario_obs'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
            $usuario_obs_picking_suffix = !empty($ww['usuario_obs_picking']) ? " <small>(" . htmlentities($ww['usuario_obs_picking'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
            $usuario_obs_packing_suffix = !empty($ww['usuario_obs_packing']) ? " <small>(" . htmlentities($ww['usuario_obs_packing'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";

            $obs_picking_text = !empty($ww['observaciones_picking']) ? "<br><small><strong>Picking:</strong> " . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . $usuario_obs_picking_suffix . "</small>" : "";
            $obs_packing_text = !empty($ww['observaciones_packing']) ? "<br><small><strong>Packing:</strong> " . htmlentities($ww['observaciones_packing'], ENT_QUOTES, 'UTF-8') . $usuario_obs_packing_suffix . "</small>" : "";

            echo "
            <tr class='text-center'>
              <td><input type='checkbox' class='venta-checkbox' value='$id_reserva'></td>
              <td><small>$id_reserva</small></td>
              <td><span style='display:none'>$ww[fecha_raw]</span>$ww[fecha]</td>
              <td>$ww[nombre_cliente] ($ww[id_cliente])</td>
              <td>$ww[nombre_usuario]</td>
              <td class='text-left'>$productos_html</td>
              <td class='text-left'>
                <div>
                  {$obs_general_text}{$usuario_obs_suffix}
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
                  $btn_orden_envio
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
        echo "<div class='callout callout-danger'><b>No se encontraron ventas...</b></div>";
    }
} else if ($consulta == "cancelar_reserva") {
    $id_reserva = $_POST["id_reserva"];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        // Verificar que La venta no esté ya cancelada o entregada
        $query_check = "SELECT * FROM reservas_productos WHERE id_reserva = $id_reserva AND estado >= 2";
        $res_check = mysqli_query($con, $query_check);
        if (mysqli_num_rows($res_check) > 0) {
            $errors[] = "La venta contiene productos que ya fueron entregados, no se puede cancelar.";
        }

        if (count($errors) == 0) {
            // Actualizar estado de todos los productos de La venta a cancelada (-1)
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
else if ($consulta == "enviar_a_transporte_reserva") {
    $id_reserva = $_POST["id_reserva"];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        // Update all products for this reservation to state 6 (EN TRANSPORTE)
        // only if their current state is 5 (A PACKING)
        $query = "UPDATE reservas_productos SET estado = 6 WHERE id_reserva = $id_reserva AND estado = 5";
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
                            WHERE rp.id_reserva = $id_reserva AND rp.estado = 6";
        
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

                // Actualizar estado de La venta
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
        echo "error: No se encontró el producto de La venta";
    }

    mysqli_close($con);
} else if ($consulta == "check_ventas_nuevas") {
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
    $id_usuario = $_SESSION['id_usuario'];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query = "UPDATE reservas SET observaciones = '$observaciones', id_usuario_obs = $id_usuario WHERE id = $id_reserva";
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
else if ($consulta == "update_picking_observacion") {
    $id_reserva = $_POST["id_reserva"];
    $observaciones_picking = mysqli_real_escape_string($con, $_POST["observaciones_picking"]);
    $id_usuario = $_SESSION['id_usuario'];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query = "UPDATE reservas SET observaciones_picking = '$observaciones_picking', id_usuario_obs_picking = $id_usuario WHERE id = $id_reserva";
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
else if ($consulta == "update_packing_observacion") {
    $id_reserva = $_POST["id_reserva"];
    $observaciones_packing = mysqli_real_escape_string($con, $_POST["observaciones_packing"]);
    $id_usuario = $_SESSION['id_usuario'];

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        $query = "UPDATE reservas SET observaciones_packing = '$observaciones_packing', id_usuario_obs_packing = $id_usuario WHERE id = $id_reserva";
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
              INNER JOIN reservas_productos r2 ON e.id_reserva_producto = r2.id
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
              INNER JOIN reservas_productos r2 ON e.id_reserva_producto = r2.id
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
            $errors[] = "Error al crear La venta: " . mysqli_error($con);
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
              u_obs.nombre_real as usuario_obs,
              u_obs_picking.nombre_real as usuario_obs_picking,
              u_obs_packing.nombre_real as usuario_obs_packing,
              (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado_general,
              DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha_formatted
              FROM reservas r
              INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
              LEFT JOIN usuarios u ON u.id = r.id_usuario
              LEFT JOIN usuarios u_obs ON u_obs.id = r.id_usuario_obs
              LEFT JOIN usuarios u_obs_picking ON u_obs_picking.id = r.id_usuario_obs_picking
              LEFT JOIN usuarios u_obs_packing ON u_obs_packing.id = r.id_usuario_obs_packing
              WHERE EXISTS (SELECT 1 FROM reservas_productos rp WHERE rp.id_reserva = r.id AND rp.estado = 4)
              ORDER BY r.fecha DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Ventas en Picking</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-primary btn-sm' onclick='printTable(\"tabla-picking\")'><i class='fa fa-print'></i> IMPRIMIR</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-picking' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        // Table headers
        echo "<thead><tr><th></th><th>ID</th><th>Fecha Venta</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th></tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_reserva = $ww['id'];
            $productos_pendientes_picking = 0;

            $productos_query = "SELECT rp.id as id_reserva_producto, rp.id_variedad, v.nombre as nombre_variedad, t.codigo, v.id_interno, rp.cantidad, rp.estado,
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
                    $atributos_html = getAtributosVariedad($con, $producto['id_variedad']);

                    $botones_producto = "<div class='d-flex flex-column' style='gap: 5px;'>";
                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)' class='btn btn-info btn-sm'><i class='fa fa-search'></i> A REVISIÓN</button>";
                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 5)' class='btn btn-warning btn-sm'><i class='fa fa-archive'></i> A PACKING</button>";
                    $botones_producto .= "</div>";

                    $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center' style='border-bottom: 1px solid #d3d3d3;'>";
                    $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']} <span class='badge' style='background-color: unset;color:black;'>".boxEstadoReserva($producto['estado'], true)."</span><br>{$atributos_html}</div>";
                    $productos_html .= $botones_producto;
                    $productos_html .= "</li>";
                }
            }
            $productos_html .= "</ul>";

            if($productos_pendientes_picking > 0){
                $btn_quick_packing = "<button onclick='enviarAPackingReserva($id_reserva)' class='btn btn-warning btn-sm mb-2' title='Enviar a Packing'><i class='fa fa-archive'></i></button>";

                echo "<tr class='text-center'>";
                echo "<td><input type='checkbox' class='venta-checkbox' value='$id_reserva'></td>";
                echo "<td><small>$id_reserva</small></td>";
                echo "<td>{$ww['fecha_formatted']}</td>";
                echo "<td>{$ww['nombre_cliente']} ({$ww['id_cliente']})</td>";
                echo "<td>{$ww['nombre_usuario']}</td>";
                echo "<td class='text-left'>$productos_html</td>";
                $usuario_obs_suffix = !empty($ww['usuario_obs']) ? " <small>(" . htmlentities($ww['usuario_obs'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_picking_suffix = !empty($ww['usuario_obs_picking']) ? " <small>(" . htmlentities($ww['usuario_obs_picking'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";

                echo "<td class='text-left'>";
                echo "  <div>" . htmlentities($ww['observaciones'], ENT_QUOTES, 'UTF-8') . $usuario_obs_suffix . "</div>"; // General observations
                echo "  <div><strong>Picking:</strong> " . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . $usuario_obs_picking_suffix . " <button class='btn btn-default btn-xs' onclick='modalEditarObservacionPicking(\"$id_reserva\", \"" . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . "\")'><i class='fa fa-pencil'></i></button></div>"; // Picking observations with edit button
                echo "</td>";
                echo "<td>".boxEstadoReserva($ww['estado_general'], true)."</td>";
                echo "<td><div class='d-flex flex-column'>$btn_quick_packing</div></td>";
                echo "</tr>";
            }
        }
        echo "</tbody></table></div></div>";
    } else {
        echo "<div class='callout callout-info'><b>No se encontraron ventas en la etapa de picking.</b></div>";
    }
}
else if ($consulta == "busca_packing") {

    $query = "SELECT r.*,
                     cl.nombre AS nombre_cliente,
                     u.nombre_real AS nombre_usuario,
                     u_obs.nombre_real as usuario_obs,
                     u_obs_picking.nombre_real as usuario_obs_picking,
                     u_obs_packing.nombre_real as usuario_obs_packing,
                     (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) AS estado_general,
                     DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') AS fecha_formatted
              FROM reservas r
              INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
              LEFT JOIN usuarios u ON u.id = r.id_usuario
              LEFT JOIN usuarios u_obs ON u_obs.id = r.id_usuario_obs
              LEFT JOIN usuarios u_obs_picking ON u_obs_picking.id = r.id_usuario_obs_picking
              LEFT JOIN usuarios u_obs_packing ON u_obs_packing.id = r.id_usuario_obs_packing
              WHERE EXISTS (
                  SELECT 1
                  FROM reservas_productos rp
                  WHERE rp.id_reserva = r.id AND rp.estado = 5
              )
              ORDER BY r.fecha DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Ventas en Packing</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-primary btn-sm' onclick='printTable(\"tabla-packing\")'><i class='fa fa-print'></i> IMPRIMIR</button>";
        echo "</div></div>";

        echo "<div class='box-body'>";
        echo "<table id='tabla-packing' class='table table-bordered table-responsive w-100 d-block d-md-table'>";

        echo "<thead><tr>
                <th></th>
                <th>ID</th>
                <th>Fecha Venta</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Productos</th>
                <th>Observaciones</th>
                <th>Estado</th>
                <th></th>
              </tr></thead>";

        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {

            $id_reserva = $ww['id'];
            $productos_pendientes_packing = 0;

            $productos_query = "SELECT
                        rp.id AS id_reserva_producto,
                        rp.id_variedad,
                        v.nombre AS nombre_variedad,
                        t.codigo,
                        v.id_interno,
                        rp.cantidad,
                        rp.estado,
                        (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) AS cantidad_entregada,
                        (
                            (SELECT IFNULL(SUM(s.cantidad),0)
                             FROM stock_productos s
                             INNER JOIN articulospedidos ap ON s.id_artpedido = ap.id
                             WHERE ap.id_variedad = rp.id_variedad AND ap.estado = 8)
                            -
                            (SELECT IFNULL(SUM(r2.cantidad),0)
                             FROM reservas_productos r2
                             WHERE r2.id_variedad = rp.id_variedad AND r2.estado >= 0 AND r2.id != rp.id)
                        ) AS stock_disponible
                    FROM reservas_productos rp
                    INNER JOIN variedades_producto v ON v.id = rp.id_variedad
                    INNER JOIN tipos_producto t ON t.id = v.id_tipo
                    WHERE rp.id_reserva = $id_reserva";

            $productos_result = mysqli_query($con, $productos_query);
            $productos_html = "<ul class='list-group'>";

            while ($producto = mysqli_fetch_array($productos_result)) {

                if ($producto['estado'] == 5) {

                    $productos_pendientes_packing++;
                    $atributos_html = getAtributosVariedad($con, $producto['id_variedad']);

                    $botones_producto = "<div class='d-flex flex-column' style='gap:5px;'>";

                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)'
                                            class='btn btn-info btn-sm'>
                                            <i class='fa fa-search'></i> A REVISIÓN
                                          </button>";

                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 6)'
                                            class='btn btn-primary btn-sm'>
                                            <i class='fa fa-shipping-fast'></i> A TRANSPORTE
                                          </button>";

                    $stock_disponible_real = $producto['stock_disponible'] + ($producto['cantidad'] - $producto['cantidad_entregada']);

                    $botones_producto .= "<button onclick='entregarProducto(
                                                {$producto['id_reserva_producto']},
                                                \"{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']})\",
                                                " . ($producto['cantidad'] - $producto['cantidad_entregada']) . ",
                                                $stock_disponible_real
                                            )'
                                            class='btn btn-success btn-sm'>
                                            <i class='fa fa-truck'></i> ENTREGAR
                                          </button>";

                    $botones_producto .= "</div>";

                    $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center' style='border-bottom: 1px solid #d3d3d3;'>";
                    $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']})
                                        - Cant: {$producto['cantidad']}
                                        <span class='badge' style='background-color:unset;color:black;'>"
                                        . boxEstadoReserva($producto['estado'], true) . "</span><br>{$atributos_html}</div>";
                    $productos_html .= $botones_producto;
                    $productos_html .= "</li>";
                }
            }

            $productos_html .= "</ul>";

            if ($productos_pendientes_packing > 0) {

                $btn_quick_entrega = "<button onclick='entregaRapida($id_reserva)' 
                                        class='btn btn-success btn-sm mb-2'>
                                        <i class='fa fa-truck'></i> ENTREGAR
                                      </button>";

                $btn_enviar_a_transporte = "<button onclick='enviarATransporteReserva($id_reserva)' 
                                                class='btn btn-primary btn-sm mb-2'>
                                                <i class='fa fa-shipping-fast'></i> A TRANSPORTE
                                           </button>";

                echo "<tr class='text-center'>";
                echo "<td><input type='checkbox' class='venta-checkbox' value='$id_reserva'></td>";
                echo "<td><small>$id_reserva</small></td>";
                echo "<td>{$ww['fecha_formatted']}</td>";
                echo "<td>{$ww['nombre_cliente']} ({$ww['id_cliente']})</td>";
                echo "<td>{$ww['nombre_usuario']}</td>";
                echo "<td class='text-left'>$productos_html</td>";

                $usuario_obs_suffix = !empty($ww['usuario_obs']) ? " <small>(" . htmlentities($ww['usuario_obs'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_picking_suffix = !empty($ww['usuario_obs_picking']) ? " <small>(" . htmlentities($ww['usuario_obs_picking'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_packing_suffix = !empty($ww['usuario_obs_packing']) ? " <small>(" . htmlentities($ww['usuario_obs_packing'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";

                echo "<td class='text-left'>
                        <div>" . htmlentities($ww['observaciones'], ENT_QUOTES, 'UTF-8') . $usuario_obs_suffix . "</div>
                        <div><small><strong>Picking:</strong> " . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . $usuario_obs_picking_suffix . "</small></div>
                        <div><strong>Packing:</strong> " . htmlentities($ww['observaciones_packing'], ENT_QUOTES, 'UTF-8') . $usuario_obs_packing_suffix . "
                            <button class='btn btn-default btn-xs'
                                onclick='modalEditarObservacionPacking(\"$id_reserva\", \"" . htmlentities($ww['observaciones_packing'], ENT_QUOTES, 'UTF-8') . "\")'>
                                <i class='fa fa-pencil'></i>
                            </button>
                        </div>
                      </td>";

                echo "<td>" . boxEstadoReserva($ww['estado_general'], true) . "</td>";

                echo "<td>
                        <div class='d-flex flex-column'>
                            $btn_quick_entrega
                            $btn_enviar_a_transporte
                        </div>
                      </td>";

                echo "</tr>";
            }
        }

        echo "</tbody></table></div></div>";

    } else {
        echo "<div class='callout callout-info'><b>No se encontraron ventas en la etapa de packing.</b></div>";
    }
}

else if ($consulta == "busca_en_transporte") { // NEW BLOCK
    $query = "SELECT r.*, cl.nombre as nombre_cliente, u.nombre_real as nombre_usuario,
              u_obs.nombre_real as usuario_obs,
              u_obs_picking.nombre_real as usuario_obs_picking,
              u_obs_packing.nombre_real as usuario_obs_packing,
              (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado_general,
              DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha_formatted
              FROM reservas r
              INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
              LEFT JOIN usuarios u ON u.id = r.id_usuario
              LEFT JOIN usuarios u_obs ON u_obs.id = r.id_usuario_obs
              LEFT JOIN usuarios u_obs_picking ON u_obs_picking.id = r.id_usuario_obs_picking
              LEFT JOIN usuarios u_obs_packing ON u_obs_packing.id = r.id_usuario_obs_packing
              WHERE EXISTS (SELECT 1 FROM reservas_productos rp WHERE rp.id_reserva = r.id AND rp.estado = 6)
              ORDER BY r.fecha DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Ventas en Transporte</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-primary btn-sm' onclick='printTable(\"tabla-en-transporte\")'><i class='fa fa-print'></i> IMPRIMIR</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-en-transporte' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead><tr><th></th><th>ID</th><th>Fecha Reserva</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th></tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_reserva = $ww['id'];
            $productos_pendientes_transporte = 0;

            $productos_query = "SELECT rp.id as id_reserva_producto, rp.id_variedad, v.nombre as nombre_variedad, t.codigo, v.id_interno, rp.cantidad, rp.estado,
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
                if ($producto['estado'] == 6) { // EN TRANSPORTE
                    $productos_pendientes_transporte++;
                    $atributos_html = getAtributosVariedad($con, $producto['id_variedad']);

                    $botones_producto = "<div class='d-flex flex-column' style='gap:5px;'>";
                    $botones_producto .= "<button onclick='cambiarEstadoProducto({$producto['id_reserva_producto']}, 3)' class='btn btn-info btn-sm'><i class='fa fa-search'></i> A REVISIÓN</button>";

                    $stock_disponible_real = $producto['stock_disponible'] + ($producto['cantidad'] - $producto['cantidad_entregada']);

                    $botones_producto .= "<button onclick='entregarProducto({$producto['id_reserva_producto']}, \"{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']})\", {$producto['cantidad']} - {$producto['cantidad_entregada']}, $stock_disponible_real)' class='btn btn-success btn-sm'><i class='fa fa-truck'></i> ENTREGAR</button>";
                    $botones_producto .= "</div>";

                    $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center' style='border-bottom: 1px solid #d3d3d3;'>";
                    $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']} <span class='badge' style='background-color: unset;color:black;'>".boxEstadoReserva($producto['estado'], true)."</span><br>{$atributos_html}</div>";
                    $productos_html .= $botones_producto;
                    $productos_html .= "</li>";
                }
            }

            $productos_html .= "</ul>";

            if ($productos_pendientes_transporte > 0) {
                $btn_quick_entrega = "<button onclick='entregaRapida($id_reserva)' class='btn btn-success btn-sm mb-2' title='Entrega Rápida'><i class='fa fa-truck'></i></button>";

                echo "<tr class='text-center'>";
                echo "<td><input type='checkbox' class='venta-checkbox' value='$id_reserva'></td>";
                echo "<td><small>$id_reserva</small></td>";
                echo "<td>{$ww['fecha_formatted']}</td>";
                echo "<td>{$ww['nombre_cliente']} ({$ww['id_cliente']})</td>";
                echo "<td>{$ww['nombre_usuario']}</td>";
                echo "<td class='text-left'>$productos_html</td>";

                $usuario_obs_suffix = !empty($ww['usuario_obs']) ? " <small>(" . htmlentities($ww['usuario_obs'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_picking_suffix = !empty($ww['usuario_obs_picking']) ? " <small>(" . htmlentities($ww['usuario_obs_picking'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_packing_suffix = !empty($ww['usuario_obs_packing']) ? " <small>(" . htmlentities($ww['usuario_obs_packing'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";

                echo "<td class='text-left'>";
                echo "<div>" . htmlentities($ww['observaciones'], ENT_QUOTES, 'UTF-8') . $usuario_obs_suffix . "</div>";
                echo "<div><small><strong>Picking:</strong> " . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . $usuario_obs_picking_suffix . "</small></div>";
                echo "<div><small><strong>Packing:</strong> " . htmlentities($ww['observaciones_packing'], ENT_QUOTES, 'UTF-8') . $usuario_obs_packing_suffix . "</small></div>";
                echo "</td>";
                echo "<td>" . boxEstadoReserva($ww['estado_general'], true) . "</td>";
                echo "<td><div class='d-flex flex-column'>$btn_quick_entrega</div></td>";
                echo "</tr>";
            }
        }

        echo "</tbody></table></div></div>";
    } else {
        echo "<div class='callout callout-info'><b>No se encontraron ventas en la etapa de transporte.</b></div>";
    }
}
else if ($consulta == "busca_entregadas") {
    $query = "SELECT r.id,
              r.id_cliente,
              r.fecha,
              r.observaciones,
              r.observaciones_picking,
              r.observaciones_packing,
              r.id_usuario,
              r.id_usuario_obs,
              r.id_usuario_obs_picking,
              r.id_usuario_obs_packing,
              cl.nombre as nombre_cliente,
              u.nombre_real as nombre_usuario,
              u_obs.nombre_real as usuario_obs,
              u_obs_picking.nombre_real as usuario_obs_picking,
              u_obs_packing.nombre_real as usuario_obs_packing,
              (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) as estado_general,
              DATE_FORMAT(r.fecha, '%d/%m/%y %H:%i') as fecha_formatted,
              (SELECT MAX(e.fecha) FROM entregas_stock e
               INNER JOIN reservas_productos rp ON e.id_reserva_producto = rp.id
               WHERE rp.id_reserva = r.id) as fecha_entrega_max,
              DATE_FORMAT((SELECT MAX(e.fecha) FROM entregas_stock e
               INNER JOIN reservas_productos rp ON e.id_reserva_producto = rp.id
               WHERE rp.id_reserva = r.id), '%Y%m%d%H%i') as fecha_entrega_raw,
              DATE_FORMAT((SELECT MAX(e.fecha) FROM entregas_stock e
               INNER JOIN reservas_productos rp ON e.id_reserva_producto = rp.id
               WHERE rp.id_reserva = r.id), '%d/%m/%y %H:%i') as fecha_entrega_formatted
              FROM reservas r
              INNER JOIN clientes cl ON cl.id_cliente = r.id_cliente
              LEFT JOIN usuarios u ON u.id = r.id_usuario
              LEFT JOIN usuarios u_obs ON u_obs.id = r.id_usuario_obs
              LEFT JOIN usuarios u_obs_picking ON u_obs_picking.id = r.id_usuario_obs_picking
              LEFT JOIN usuarios u_obs_packing ON u_obs_packing.id = r.id_usuario_obs_packing
              WHERE (SELECT MIN(rp.estado) FROM reservas_productos rp WHERE rp.id_reserva = r.id) = 2
              ORDER BY fecha_entrega_max DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Ventas Entregadas</h3>";
        echo "<div class='box-tools pull-right'>";
        echo "<button class='btn btn-primary btn-sm' onclick='printTable(\"tabla-entregadas\")'><i class='fa fa-print'></i> IMPRIMIR</button>";
        echo "</div>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-entregadas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead><tr><th>ID</th><th>Fecha Reserva</th><th>Cliente</th><th>Vendedor</th><th>Productos</th><th>Observaciones</th><th>Estado</th><th></th></tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_reserva = $ww['id'];
            $productos_entregados_count = 0;

            $productos_query = "SELECT rp.id as id_reserva_producto, rp.id_variedad, v.nombre as nombre_variedad, t.codigo, v.id_interno, rp.cantidad, rp.estado,
                                   (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_entregada
                                FROM reservas_productos rp
                                INNER JOIN variedades_producto v ON v.id = rp.id_variedad
                                INNER JOIN tipos_producto t ON t.id = v.id_tipo
                                WHERE rp.id_reserva = $id_reserva";

            $productos_result = mysqli_query($con, $productos_query);
            $productos_html = "<ul class='list-group'>";

            while ($producto = mysqli_fetch_array($productos_result)) {
                if ($producto['estado'] == 2) { // ENTREGADA
                    $productos_entregados_count++;
                    $atributos_html = getAtributosVariedad($con, $producto['id_variedad']);

                    $productos_html .= "<li class='list-group-item d-flex justify-content-between align-items-center'>";
                    $productos_html .= "<div>{$producto['nombre_variedad']} ({$producto['codigo']}{$producto['id_interno']}) - Cant: {$producto['cantidad']} <span class='badge' style='background-color: unset;color:black;'>".boxEstadoReserva($producto['estado'], true)."</span><br>{$atributos_html}</div>";
                    $productos_html .= "</li>";
                }
            }

            $productos_html .= "</ul>";

            if ($productos_entregados_count > 0) {
                echo "<tr class='text-center'>";
                echo "<td><small>$id_reserva</small></td>";
                echo "<td><span style='display:none'>{$ww['fecha_entrega_raw']}</span>{$ww['fecha_entrega_formatted']}</td>";
                echo "<td>{$ww['nombre_cliente']} ({$ww['id_cliente']})</td>";
                echo "<td>{$ww['nombre_usuario']}</td>";
                echo "<td class='text-left'>$productos_html</td>";

                $usuario_obs_suffix = !empty($ww['usuario_obs']) ? " <small>(" . htmlentities($ww['usuario_obs'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_picking_suffix = !empty($ww['usuario_obs_picking']) ? " <small>(" . htmlentities($ww['usuario_obs_picking'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";
                $usuario_obs_packing_suffix = !empty($ww['usuario_obs_packing']) ? " <small>(" . htmlentities($ww['usuario_obs_packing'], ENT_QUOTES, 'UTF-8') . ")</small>" : "";

                echo "<td class='text-left'>";
                echo "<div>" . htmlentities($ww['observaciones'], ENT_QUOTES, 'UTF-8') . $usuario_obs_suffix . "</div>";
                echo "<div><small><strong>Picking:</strong> " . htmlentities($ww['observaciones_picking'], ENT_QUOTES, 'UTF-8') . $usuario_obs_picking_suffix . "</small></div>";
                echo "<div><small><strong>Packing:</strong> " . htmlentities($ww['observaciones_packing'], ENT_QUOTES, 'UTF-8') . $usuario_obs_packing_suffix . "</small></div>";
                echo "</td>";
                echo "<td>" . boxEstadoReserva($ww['estado_general'], true) . "</td>";
                echo "<td></td>";
                echo "</tr>";
            }
        }

        echo "</tbody></table></div></div>";
    } else {
        echo "<div class='callout callout-info'><b>No se encontraron ventas entregadas.</b></div>";
    }
} else if ($consulta == "cambiar_estado_ventas_masa") {
    $ids = json_decode($_POST["ids"], true);
    $estado = (int)$_POST["estado"];

    if (empty($ids) || !is_numeric($estado)) {
        echo "error: Datos inválidos.";
        exit;
    }

    $ids_list = implode(",", array_map('intval', $ids));

    try {
        mysqli_autocommit($con, false);
        $errors = array();

        // Si el estado es CANCELADO (-1), hacer validaciones especiales
        if ($estado === -1) {
            // Verificar que NINGUNA de las reservas tenga productos entregados (estado >= 2)
            $query_check_entregados = "SELECT rp.id, rp.id_reserva FROM reservas_productos rp
                                       WHERE rp.id_reserva IN ($ids_list) AND rp.estado >= 2";
            $result_check = mysqli_query($con, $query_check_entregados);

            if (mysqli_num_rows($result_check) > 0) {
                // Hay productos entregados, no permitir cancelación
                $errors[] = "No se puede cancelar. Una o más ventas contienen productos que ya fueron entregados.";
            } else {
                // Si todo está bien, cambiar estado de todos los productos a -1
                $query_update = "UPDATE reservas_productos SET estado = -1 WHERE id_reserva IN ($ids_list)";
                if (!mysqli_query($con, $query_update)) {
                    $errors[] = mysqli_error($con);
                }
            }
        } else if ($estado === 2) {
            // ENTREGA MASIVA - Entregar automáticamente sin validar stock
            // Primero, verificar que ninguna de las reservas esté completamente cancelada
            $query_check_canceladas = "SELECT r.id FROM reservas r
                                       WHERE r.id IN ($ids_list)
                                       AND NOT EXISTS (
                                           SELECT 1 FROM reservas_productos rp
                                           WHERE rp.id_reserva = r.id AND rp.estado != -1
                                       )";
            $result_check = mysqli_query($con, $query_check_canceladas);

            if (mysqli_num_rows($result_check) > 0) {
                $errors[] = "No se puede entregar. Una o más ventas están completamente canceladas.";
            } else {
                // Obtener todos los productos no entregados ni cancelados de las reservas seleccionadas
                $query_productos = "SELECT
                                    rp.id as id_reserva_producto,
                                    rp.cantidad as cantidad_reservada,
                                    rp.id_variedad,
                                    rp.estado,
                                    (SELECT IFNULL(SUM(e.cantidad),0) FROM entregas_stock e WHERE e.id_reserva_producto = rp.id) as cantidad_ya_entregada
                                FROM reservas_productos rp
                                WHERE rp.id_reserva IN ($ids_list) AND rp.estado != 2 AND rp.estado != -1";

                $result_productos = mysqli_query($con, $query_productos);

                if (mysqli_num_rows($result_productos) > 0) {
                    while ($producto = mysqli_fetch_assoc($result_productos)) {
                        $id_reserva_producto = $producto['id_reserva_producto'];
                        $cantidad_reservada = (int)$producto['cantidad_reservada'];
                        $cantidad_ya_entregada = (int)$producto['cantidad_ya_entregada'];
                        $cantidad_pendiente = $cantidad_reservada - $cantidad_ya_entregada;

                        // Solo procesar si hay cantidad pendiente
                        if ($cantidad_pendiente > 0) {
                            // Insertar entrega
                            $query_entrega = "INSERT INTO entregas_stock (cantidad, fecha, id_reserva_producto) VALUES ($cantidad_pendiente, NOW(), $id_reserva_producto)";
                            if (!mysqli_query($con, $query_entrega)) {
                                $errors[] = "Error al registrar entrega para producto $id_reserva_producto: " . mysqli_error($con);
                                break;
                            }

                            // Cambiar estado a ENTREGADO (2)
                            $query_update = "UPDATE reservas_productos SET estado = 2 WHERE id = $id_reserva_producto";
                            if (!mysqli_query($con, $query_update)) {
                                $errors[] = "Error al actualizar estado para producto $id_reserva_producto: " . mysqli_error($con);
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            // Para otros estados, aplicar el comportamiento normal
            // Find products that are currently 'ENTREGADA' (estado = 2) to revert their delivery record
            $query_productos_a_revertir = "SELECT id FROM reservas_productos WHERE id_reserva IN ($ids_list) AND estado = 2";
            $result_revertir = mysqli_query($con, $query_productos_a_revertir);
            $productos_ids_a_revertir = [];
            while($row = mysqli_fetch_assoc($result_revertir)){
                $productos_ids_a_revertir[] = $row['id'];
            }

            // If there are products to revert, delete their records from entregas_stock
            if (!empty($productos_ids_a_revertir)) {
                $productos_ids_list = implode(",", $productos_ids_a_revertir);
                $query_delete_entregas = "DELETE FROM entregas_stock WHERE id_reserva_producto IN ($productos_ids_list)";
                if (!mysqli_query($con, $query_delete_entregas)) {
                    $errors[] = "Error al eliminar registros de entrega: " . mysqli_error($con);
                }
            }

            // Update the state of all non-cancelled products for the selected reservations
            if(count($errors) == 0){
                $query_update = "UPDATE reservas_productos SET estado = $estado WHERE id_reserva IN ($ids_list) AND estado != -1";
                if (!mysqli_query($con, $query_update)) {
                    $errors[] = "Error al actualizar estados: " . mysqli_error($con);
                }
            }
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                echo "error: No se pudo confirmar la transacción.";
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
} else if ($consulta == "get_transportistas_select") {
    $query = "SELECT
                    t.nombre,
                    t.id
                     FROM
                     transportistas t
                     ORDER BY t.nombre ASC";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            $nombre = mysqli_real_escape_string($con, $re["nombre"]);

            echo "<option value='$re[id]' x-nombre='$nombre'>$re[nombre] ($re[id])</option>";
        }
    }
} else if ($consulta == "get_sucursales_select") {
    $id_transportista = $_POST["id_transportista"];

    if ((int) $id_transportista != 1) {
        $query = "SELECT s.id,
        s.nombre as nombre_sucursal,
        s.direccion
         FROM
         transportistas_sucursales s
         WHERE s.id_transportista = $id_transportista
         ORDER BY s.nombre ASC";
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $nombre = htmlspecialchars($re["nombre_sucursal"], ENT_QUOTES, 'UTF-8');
                $dire = $re["direccion"];
                if (strlen($dire) > 14) {
                    $dire = substr($dire, 0, 14) . "...";
                }
                $dire_escaped = htmlspecialchars($dire, ENT_QUOTES, 'UTF-8');
                $sucu = $re["nombre_sucursal"];
                if (strlen($sucu) > 12) {
                    $sucu = substr($sucu, 0, 12) . "...";
                }
                $sucu_escaped = htmlspecialchars($sucu, ENT_QUOTES, 'UTF-8');
                $re_direccion_escaped = htmlspecialchars($re["direccion"], ENT_QUOTES, 'UTF-8');
                $re_id = htmlspecialchars($re["id"], ENT_QUOTES, 'UTF-8');

                echo "<option x-direccion=\"$re_direccion_escaped\" value=\"$re_id\" x-nombre=\"$nombre\">$sucu_escaped [$dire_escaped] ($re_id)</option>";
            }
        }
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "https://gateway.starken.cl/externo/integracion/agency/agency");
        $token = "7b14bb8a-9df5-4cea-bb71-c6bc285b2ad7";
        $headers = array(
            "Content-Type: application/json; charset=utf-8",
            "Authorization: Bearer " . $token,
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($ch);
        try {
            $respArray = json_decode($resp);
            foreach ($respArray as $sucursal) {
                # code...
                $direccion = $sucursal->address;
                $id = $sucursal->id;
                $nombre = $sucursal->name;

                if (strlen($direccion) > 40) {
                    $direccion = substr($direccion, 0, 40) . "...";
                }
                if (strlen($nombre) > 40) {
                    $nombre = substr($nombre, 0, 40) . "...";
                }

                // Escapar atributos para HTML
                $nombre_escaped = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $direccion_escaped = htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8');
                $id_escaped = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

                echo "<option x-direccion=\"$direccion_escaped\" value=\"$id_escaped\" x-nombre=\"$nombre_escaped\">$nombre_escaped [$direccion_escaped] ($id_escaped)</option>";
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
} else if ($consulta == "get_datos_cliente_para_orden_envio") {
    $id_reserva = $_POST["id_reserva"];

    $query = "SELECT
                c.nombre,
                c.domicilio,
                c.domicilio2,
                c.telefono,
                c.mail,
                c.rut,
                c.provincia,
                c.region,
                com.nombre as comuna
            FROM reservas r
            INNER JOIN clientes c ON c.id_cliente = r.id_cliente
            LEFT JOIN comunas com ON c.comuna = com.id
            WHERE r.id = $id_reserva
            LIMIT 1";

    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        $cliente = mysqli_fetch_assoc($result);
        header('Content-Type: application/json');
        echo json_encode($cliente);
    } else {
        header('Content-Type: application/json');
        echo json_encode(array("error" => "Cliente no encontrado"));
    }
} else if ($consulta == "guardar_orden_envio") {
    $data = $_POST["data"];
    $id_reserva = $_POST["id_reserva"];

    $query = "INSERT INTO ordenes_envio (codigo, id_cliente, id_reserva, fecha) VALUES ('$data', (SELECT id_cliente FROM reservas WHERE id = $id_reserva), $id_reserva, NOW())";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
}

