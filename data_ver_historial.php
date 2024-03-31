<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

header('Content-type: text/html; charset=utf-8');

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");

$consulta = $_POST["consulta"];

if ($consulta == "busca_pedidos") {
    $fechai = $_POST['fechai'];
    $fechaf = $_POST['fechaf'];

    $fechai = str_replace("/", "-", $fechai);
    $fechaf = str_replace("/", "-", $fechaf);

    if (strlen($fechai) == 0) {
        $fechai = (string) date('y-m-d', strtotime("first day of -3 month"));
    }
    if (strlen($fechaf) == 0) {
        $fechaf = "NOW()";
    }

    $filtros = json_decode($_POST['filtros'], true);

    $query = "SELECT 
        t.nombre as nombre_tipo, 
        v.nombre as nombre_variedad, 
        c.nombre as nombre_cliente,
        t.id as id_tipo,
        c.id_cliente, 
        p.fecha, 
        e.fecha,
        p.id_pedido, 
        ap.id as id_artpedido, 
        e.cantidad as cant_plantas, 
        t.codigo, 
        v.id_interno, 
        ap.estado, 
        p.id_interno as id_pedido_interno, 
        DATE_FORMAT(p.fecha, '%m/%d') AS mes_dia, 
        ap.problema, 
        ap.observacionproblema, 
        ap.observacion, 
        p.id_pedido,
        u.iniciales,
        e.tipo as tipo_entrega,
        e.id_entrega,
        ep.nombre as nombre_especie,
        ap.id_especie,
        ap.eliminado,
        DATE_FORMAT(e.fecha, '%Y%m%d%H%i') AS fecha_entrega_raw, 
        DATE_FORMAT(e.fecha, '%d/%m/%Y %H:%i') as fecha_entrega
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        INNER JOIN entregas e ON ap.id = e.id_artpedido
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas ep ON ep.id = ap.id_especie
        GROUP BY e.id_entrega  
        HAVING e.fecha >= '$fechai'
        AND ap.eliminado IS NULL 
        AND 
        ";


    if ($fechaf == "NOW()") {
        $query .= "e.fecha <= NOW() ";
    } else {
        $query .= " e.fecha <= '$fechaf' ";
    }

    if ($filtros["tipo"] != null) {
        $query .= " AND id_tipo IN " . $filtros["tipo"] . " ";
    }

    if ($filtros["variedad"] != null) {
        $query .= " AND nombre_variedad REGEXP '" . $filtros["variedad"] . "' ";
    }

    if ($filtros["cliente"] != null) {
        $query .= " AND nombre_cliente REGEXP '" . $filtros["cliente"] . "' ";
    }

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Entregas</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-responsive w-100 d-block d-md-table table-historial'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Ped</th><th>Fecha</th><th>Producto</th><th>Cliente</th><th>Cantidad</th><th>Tipo Entrega</th><th>ID Prod.</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $array = array();

        while ($ww = mysqli_fetch_array($val)) {
            $id_cliente = $ww['id_cliente'];
            $id_pedido = $ww['id_pedido'];
            $id_artpedido = $ww['id_artpedido'];
            $fecha = $ww['fecha_entrega_raw'];
            $tipo = "";
            $id_orden = $ww['id_orden_alternativa'];
            if ($id_orden != null) {
                $tipo = strtoupper(substr($ww["nombre_tipo"], 0, 3));
            }

            $especie = $ww["nombre_especie"] ? $ww["nombre_especie"] : "";
            $producto = "$ww[nombre_variedad] ($ww[codigo]".str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT).") <span class='text-primary'>$especie</span>";

            
            $id_especie = $ww["id_especie"] ? "-".str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
            $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]".str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT).$id_especie."/$ww[cant_plantas]/".str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);
            $cliente = $ww['nombre_cliente']." ($id_cliente)";
            
            
            $fecha_entrega = $ww["fecha_entrega"];
            $estado = generarBoxEstado($ww["tipo_entrega"] == 0 ? 7 : 6, $ww["codigo"], true);
            $onclick = "onClick='registroEntregas($ww[id_artpedido], \"$id_producto\", \"$ww[nombre_cliente]\", $ww[id_entrega], \"$ww[nombre_variedad]\")'";
            echo "<tr style='cursor:pointer;' 
                x-codigo='$id_producto'
                x-producto=\"$producto\"
                x-cliente='$cliente'
                x-id-cliente='$ww[id_cliente]'
                x-cant-plantas='$ww[cant_plantas]'
                x-pedido='$id_pedido'
                x-fecha-entrega='$ww[fecha_entrega]'
                >";
            echo "<td id='pedido_$id_pedido' $onclick style='text-align: center; color:#1F618D; font-weight:bold; font-size:1.0em'>$id_pedido</td>";
            echo "<td style='text-align: center' $onclick><span style='display:none;'>" . $fecha . "</span>" . $fecha_entrega . "</td>";
            echo "<td $onclick>$producto</td>";
            echo "<td $onclick>$cliente</td>";
            echo "<td $onclick  style='text-align: center;font-weight:bold;font-size:1.0em'>$ww[cant_plantas]</td>";
            echo "<td class='td-estado' $onclick ><div>$estado</div></td>";
            echo "<td $onclick style='text-align: center; font-size:1.0em; font-weight:bold'>
               <span style='font-size:1em;'>$id_producto</span>
            </td>";
            echo "<td onclick='setSelected(this)'><button class='btn btn-secondary btn-sm fa fa-arrow-circle-left'></button></td>";
            echo "</tr>";
            array_push($array, $ww['id_pedido']);
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron pedidos en las fechas indicadas...</b></div>";
    }
}
else if ($consulta == "cargar_registro_entregas"){
    try {
        $id_artpedido = $_POST["id_artpedido"];
        $arrayentregas = array();
        $cadenaselect = "SELECT 
        e.cantidad, 
        e.tipo as tipo_entrega,
        e.id_entrega,
        DATE_FORMAT(e.fecha, '%d/%m/%Y %H:%i') as fecha_ingreso
        FROM entregas e
        WHERE e.id_artpedido = $id_artpedido
        ORDER BY e.fecha DESC
        ";

        $val = mysqli_query($con, $cadenaselect);

        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                array_push($arrayentregas, array(
                    "fecha_ingreso" => $re["fecha_ingreso"],
                    "cantidad" => $re["cantidad"],
                    "id_entrega" => $re["id_entrega"],
                    "tipo_entrega" => $re["tipo_entrega"],
                ));
            }
            echo json_encode($arrayentregas);
        }

    } catch (\Throwable $th) {
    throw $th;
    }
}

