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
        INNER JOIN reservas_productos r ON e.id_reserva = r.id
        WHERE r.id_variedad = v.id AND r.estado = 2) as cantidad_entregada
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
                    
                    <button onclick='modalReservar($ww[id_variedad], \"$ww[nombre_variedad]\", $disponible_reserva)' class='btn btn-success btn-sm'><i class='fa fa-shopping-basket'></i> RESERVAR</button>
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
        $errors = array();

        // Obtener información de la reserva
        $query = "SELECT t.codigo, rp.id_variedad, v.id_interno, rp.cantidad, rp.estado 
                  FROM reservas_productos rp 
                  INNER JOIN variedades_producto v ON rp.id_variedad = v.id 
                  INNER JOIN tipos_producto t ON t.id = v.id_tipo 
                  WHERE rp.id = $id_reserva";

        $val = mysqli_query($con, $query);

        if ($val && mysqli_num_rows($val)) {
            $v = mysqli_fetch_assoc($val);

            // Verificar que la reserva no esté ya cancelada
            if ($v["estado"] < 0) {
                echo "error: La reserva ya está cancelada";
                mysqli_close($con);
                return;
            }

            mysqli_autocommit($con, false);

            // Actualizar estado de la reserva a cancelada (-1)
            $query = "UPDATE reservas_productos SET estado = -1 WHERE id = $id_reserva";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }

            // Confirmar o revertir transacción
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
            echo "error: No se encontró la reserva";
        }

        mysqli_close($con);

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
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

    } catch (\Throwable $th) {
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "guardar_entrega") {
    $id_reserva = $_POST["id_reserva"];
    $cantidad = mysqli_real_escape_string($con, $_POST["cantidad"]);
    $comentario = mysqli_real_escape_string($con, $_POST["comentario"]);
    $errors = array();

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

            // Calcular disponible (nota: parece que falta cantidad_reservada en la consulta original)
            // Asumo que querías validar contra el stock disponible
            $disponible = ((int) $ww["cantidad_stock"] - (int) $ww["cantidad_entregada"]);

            if ((int) $disponible >= (int) $cantidad) { // HAY STOCK DISPONIBLE
                // Insertar entrega
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

                // Actualizar estado de la reserva
                $query = "UPDATE reservas_productos SET estado = 2, visto = 1, comentario_empresa = '$comentario' WHERE id = $id_reserva;";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }

                // Confirmar o revertir transacción
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
                echo "max:" . ($disponible <= 0 ? "0" : $disponible);
            }
        }
    } else {
        echo "error: No se encontró la reserva";
    }

    mysqli_close($con);
} else if ($consulta == "check_reservas_nuevas") {
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
} else if ($consulta == "get_stock_variedad") {
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
    (
        IFNULL(s.cantidad,0) 
        - (
            SELECT IFNULL(SUM(e.cantidad),0) 
            FROM entregas_stock e
            INNER JOIN reservas_productos r2 ON e.id_reserva = r2.id
            WHERE r2.id_variedad = v.id 
            AND r2.estado = 2
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

    // Verificar si ya existe stock
    $stock_check = "SELECT id, cantidad FROM stock_productos WHERE id_artpedido = $id_artpedido";
    $stock_result = mysqli_query($con, $stock_check);

    if (mysqli_num_rows($stock_result) > 0) {
        $row = mysqli_fetch_assoc($stock_result);
        $cantidad_actual = (int) $row["cantidad"];

        if ($accion === "sumar") {
             $update_query = "UPDATE stock_productos 
                         SET cantidad = cantidad + $cantidad 
                         WHERE id_artpedido = $id_artpedido";
        } else {
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
    $id_variedad = $_POST["id_variedad"];
    $id_cliente = $_POST["id_cliente"];
    $cantidad = mysqli_real_escape_string($con, $_POST["cantidad"]);
    $comentario = mysqli_real_escape_string($con, $_POST["comentario"]);

    try {
        $errors = array();

        // Obtener stock disponible y cantidad reservada
        $query = "SELECT * FROM (
            (SELECT IFNULL(SUM(s.cantidad),0) as cantidad_stock FROM stock_productos s
            INNER JOIN articulospedidos p ON s.id_artpedido = p.id
            INNER JOIN variedades_producto v ON v.id = p.id_variedad
            WHERE p.id_variedad = $id_variedad) as q1,
            (SELECT IFNULL(SUM(r.cantidad),0) as cantidad_reservada FROM reservas_productos r
            INNER JOIN variedades_producto v ON v.id = r.id_variedad
            WHERE r.id_variedad = $id_variedad AND r.estado >= 0) as q2,
            (SELECT t.codigo, v.id_interno FROM variedades_producto v 
            INNER JOIN tipos_producto t ON t.id = v.id_tipo 
            WHERE v.id = $id_variedad) as q4
        )";

        $val = mysqli_query($con, $query);

        if (mysqli_num_rows($val) > 0) {
            $ww = mysqli_fetch_assoc($val);

            mysqli_autocommit($con, false);

            // Calcular cantidad disponible
            $disponible = ((int) $ww["cantidad_stock"] - (int) $ww["cantidad_reservada"]);

            if ((int) $disponible >= (int) $cantidad) {
                // Insertar la reserva
                $query = "INSERT INTO reservas_productos (
                    cantidad,
                    fecha,
                    id_variedad,
                    id_cliente,
                    comentario,
                    estado,
                    origen
                ) VALUES (
                    $cantidad,
                    NOW(),
                    $id_variedad,
                    $id_cliente,
                    '$comentario',
                    0,
                    'ADMINISTRACION'
                )";

                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . $query;
                }

                // Confirmar o revertir transacción
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
                echo "max:" . ($disponible <= 0 ? "0" : $disponible);
            }
        } else {
            echo "error: No se pudo obtener información de la variedad";
        }

        mysqli_close($con);

    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    }
}