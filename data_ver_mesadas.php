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
$consulta = $_POST['consulta'];

if ($consulta == "cargar_mesadas") {
    $arraymesadas = array();
    $queryselect = "SELECT id_mesada, id_tipo, id_interno, (select * from (SELECT IFNULL(MAX(id_interno), 1) FROM mesadas) t) as maximo FROM mesadas ORDER BY id_tipo, id_interno;";

	$val = mysqli_query($con, $queryselect);

    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            array_push($arraymesadas,
				array(
					"id_mesada" => $re["id_mesada"],
					"id_tipo" => $re["id_tipo"],
					"id_interno" => $re["id_interno"],
                    "maximo" => $re["maximo"]
				)
			);
        }
        echo json_encode($arraymesadas);
    } 
}
else if ($consulta == "crear_mesada"){
	$id_tipo = $_POST['id_tipo'];
	$query = "INSERT INTO mesadas (id_tipo, id_interno) VALUES ('$id_tipo', 
	(select * from (SELECT IFNULL(MAX(id_interno)+1, 1) FROM mesadas WHERE id_tipo = '$id_tipo') t)
	);";
	try {
		if (mysqli_query($con, $query)){
			echo "success";
		}
		else{
			print_r(mysqli_error($con));
		}
		
	} catch (\Throwable $th) {
		throw $th;
	}
	
}
else if ($consulta == "eliminar_mesada"){
	$id_mesada = $_POST["id_mesada"];
	try {
		if (mysqli_query($con, "DELETE FROM mesadas WHERE id_mesada = $id_mesada;")){
			echo "success";
		}
		else{
			print_r(mysqli_error($con));
		}
	} catch (\Throwable $th) {
		throw $th;
	}
}
else if ($consulta == "asignar_mesada"){
	$id_mesada = $_POST["id_mesada"];
    $id_artpedido = $_POST["id_artpedido"];
	try {
		if (mysqli_query($con, "INSERT INTO mesadas_productos (id_mesada, id_artpedido) VALUES ($id_mesada, $id_artpedido)")){
			echo "success";
		}
		else{
			print_r(mysqli_error($con));
		}
	} catch (\Throwable $th) {
		throw $th;
	}
}
else if ($consulta == "cargar_infomesada"){
    $id_mesada = $_POST["id_mesada"];

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
        DATE_FORMAT(ap.fecha_ingreso, '%d/%m/%y') as fecha_ingreso_solicitada,
        DATE_FORMAT(ap.fecha_entrega, '%d/%m/%y') as fecha_entrega_solicitada,
        mp.cantidad as cantidad_mesada
        FROM mesadas_productos mp
        INNER JOIN articulospedidos ap ON ap.id = mp.id_artpedido
        INNER JOIN variedades_producto v ON v.id = ap.id_variedad
        INNER JOIN tipos_producto t ON t.id = v.id_tipo
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        WHERE mp.id_mesada = $id_mesada 
        AND ap.eliminado IS NULL
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
            if($especie) {
                $producto .= " <span class='text-primary'>$especie</span>";
            }

            $cliente = $ww['nombre_cliente'] . " ($id_cliente)";
            $estado = generarBoxEstado($ww["estado"], $ww["codigo"], true);
            
            // Calcular cantidad y bandejas
            $cantidad_info = "$ww[cant_plantas]<br><small>$ww[cant_bandejas] de $ww[tipo_bandeja]</small>";
            
            // Cantidad faltante por entregar (usando la cantidad de la mesada)
            $cantidad_faltante = $ww['cantidad_mesada'];

            $onclick = "onClick='MostrarModalEstado($ww[id_artpedido], \"$id_producto\", \"$ww[nombre_cliente]\", $id_cliente)'";

            echo "<tr style='cursor:pointer;' x-codigo='$id_producto' x-etapa='$ww[estado]' x-id-artpedido='$id_artpedido'>";
            
            // Columnas según el header de tu tabla HTML:
            // Orden
            echo "<td $onclick style='text-align: center;color:#1F618D; font-weight:bold; font-size:1.0em'>$id_producto</td>";
            
            // Producto
            echo "<td $onclick>$producto</td>";
            
            // Cantidad/Bandejas
            echo "<td $onclick style='text-align: center;font-weight:bold;font-size:1.0em'>$cantidad_info</td>";
            
            // Faltan Entregar
            echo "<td $onclick style='text-align: center;font-weight:bold;font-size:1.0em'>$cantidad_faltante</td>";
            
            // Cliente
            echo "<td $onclick>$cliente</td>";
            
            
            // Fecha Ingreso Mesada
            echo "<td $onclick style='text-align: center;'>$ww[fecha_ingreso_solicitada]</td>";
            
            // Entrega Solicitada
            echo "<td $onclick style='text-align: center;'>$ww[fecha_entrega_solicitada]</td>";
            
            // Estado
            echo "<td $onclick><div style='cursor:pointer'>$estado</div></td>";
            
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='9' class='text-center'><div class='callout callout-danger'><b>No se encontraron productos en esta mesada.</b></div></td></tr>";
    }
}