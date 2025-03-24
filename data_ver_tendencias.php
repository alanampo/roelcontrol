<?php

include "./class_lib/sesionSecurity.php";

require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

header('Content-type: text/html; charset=utf-8');
$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];

if ($consulta == "busca_top") {
    $tipo_pedido = $_POST["tipo_pedido"];
    $anio = $_POST["anio"];
    $mes = $_POST["mes"];
    $wherefecha = "";
    $id_cliente = $_POST["id_cliente"];
    $tab_name = $_POST["tab_name"];
    $limite = strpos($tab_name, 'top') !== false ? " LIMIT " . str_replace("top", "", $tab_name) : "";

    $wherecliente = strlen($id_cliente) > 0 ? " AND p.id_cliente = $id_cliente" : "";
    //BUSCANDO POR MES y AÑO ESPECIFICO
    if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
        $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $dt = strtotime("$anio-$mesito-01");
        $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

        $wherefecha .= " AND p.fecha >= '$anio-$mesito-01 00:00:00'
                AND p.fecha < '$fechafin 00:00:00'
        ";
    }
    //BUSCANDO TODO EL AÑO
    else if (strlen($anio) > 0) {
        $aniofin = (int) $anio + 1;
        $wherefecha .= " AND p.fecha >= '$anio-01-01 00:00:00'
                AND p.fecha < '$aniofin-01-01 00:00:00'
        ";
    }

    if ($tipo_pedido == "esquejes") {
        $query = "SELECT
        t.codigo as tipo,
        v.nombre as nombre_variedad,
        v.id as id_variedad,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'E'
        AND ap.estado >= -10 $wherefecha $wherecliente  GROUP BY v.id $limite";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $array = array();
            while ($ww = mysqli_fetch_array($val)) {
                array_push($array, array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $ww["nombre_variedad"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                    "query" => $query,
                ));
            }
            echo json_encode($array);
        }
        if (!$val) {
            print_r(mysqli_error());
        }

    } else if ($tipo_pedido == "hechuraesquejes") {
        $query = "SELECT
        t.codigo as tipo,
        e.nombre as nombre_especie,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN especies_provistas e ON e.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'HE'
        AND ap.estado >= -10 $wherefecha  $wherecliente GROUP BY e.id $limite";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $array = array();
            while ($ww = mysqli_fetch_array($val)) {
                array_push($array, array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $ww["nombre_especie"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                    "query" => $query,
                ));
            }
            echo json_encode($array);
        }
        if (!$val) {
            print_r(mysqli_error());
        }

    } else if ($tipo_pedido == "semillas") {
        $query = "SELECT
        t.codigo as tipo,
        v.nombre as nombre_variedad,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'S'
        AND ap.estado >= -10 $wherefecha $wherecliente GROUP BY v.id $limite";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $array = array();
            while ($ww = mysqli_fetch_array($val)) {
                array_push($array, array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $ww["nombre_variedad"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                    "query" => $query,
                ));
            }
            echo json_encode($array);
        }
        if (!$val) {
            print_r(mysqli_error());
        }

    } else if ($tipo_pedido == "hechurasemillas") {
        $query = "SELECT
        t.codigo as tipo,
        e.nombre as nombre_especie,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN especies_provistas e ON e.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'HS'
        AND ap.estado >= -10 $wherefecha $wherecliente GROUP BY e.id $limite";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $array = array();
            while ($ww = mysqli_fetch_array($val)) {
                array_push($array, array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $ww["nombre_especie"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                    "query" => $query,
                ));
            }
            echo json_encode($array);
        }
        if (!$val) {
            print_r(mysqli_error());
        }

    }
} else if ($consulta == "busca_general") {
    $tipo_pedido = $_POST["tipo_pedido"];
    $anio = $_POST["anio"];
    $limite = " LIMIT 5";
    $id_cliente = $_POST["id_cliente"] && !empty($_POST["id_cliente"]) ? $_POST["id_cliente"] : NULL;
    $wherecliente = isset($id_cliente) ? " AND p.id_cliente = $id_cliente" : "";

    $arraymeses = array(
        "1" => array(),
        "2" => array(),
        "3" => array(),
        "4" => array(),
        "5" => array(),
        "6" => array(),
        "7" => array(),
        "8" => array(),
        "9" => array(),
        "10" => array(),
        "11" => array(),
        "12" => array(),
    );

    for ($i = 1; $i <= 12; $i++) {
        $mes = $i;
        $wherefecha = "";

        if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
            $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
            $dt = strtotime("$anio-$mesito-01");
            $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

            $wherefecha .= " AND p.fecha >= '$anio-$mesito-01 00:00:00'
                AND p.fecha < '$fechafin 00:00:00'
        ";
        }

        if ($tipo_pedido == "esquejes") {
            $query = "SELECT
        t.codigo as tipo,
        v.nombre as nombre_variedad,
        v.id as id_variedad,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'E'
        AND ap.estado >= -10 $wherefecha $wherecliente  GROUP BY v.id $limite";
        } else if ($tipo_pedido == "hechuraesquejes") {
            $query = "SELECT
        t.codigo as tipo,
        e.nombre as nombre_especie,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN especies_provistas e ON e.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'HE'
        AND ap.estado >= -10 $wherefecha  $wherecliente GROUP BY e.id $limite";
        } else if ($tipo_pedido == "semillas") {
            $query = "SELECT
        t.codigo as tipo,
        v.nombre as nombre_variedad,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'S'
        AND ap.estado >= -10 $wherefecha $wherecliente GROUP BY v.id $limite";
        } else if ($tipo_pedido == "hechurasemillas") {
            $query = "SELECT
        t.codigo as tipo,
        e.nombre as nombre_especie,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN especies_provistas e ON e.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'HS'
        AND ap.estado >= -10 $wherefecha $wherecliente GROUP BY e.id $limite";
        }

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($ww = mysqli_fetch_array($val)) {
                array_push($arraymeses[$i], array(
                    "tipo" => $ww["tipo"],
                    "nombre_variedad" => $tipo_pedido == "esquejes" || $tipo_pedido == "semillas" ? $ww["nombre_variedad"] : $ww["nombre_especie"],
                    "cant_plantas" => $ww["cant_plantas"],
                    "cant_bandejas" => $ww["cant_bandejas"],
                    "cant_pedidos" => $ww["cant_pedidos"],
                    //"query" => $query,
                ));
            }
        }
    }
    echo json_encode($arraymeses);
} else if ($consulta == "busca_variedades_especies_select") {
    try {
        $tipo = $_POST["tipo"];

        if ($tipo == "E" || $tipo == "S") {
            $cadena = "SELECT
                                v.id,
                                v.id_interno,
                                v.nombre,
                                t.codigo
                                FROM variedades_producto v
                                INNER JOIN tipos_producto t ON t.id = v.id_tipo
                                WHERE v.eliminada IS NULL AND t.codigo = '$tipo'
                                ORDER BY t.codigo DESC, v.id_interno ASC
                        ";
        } else if ($tipo == "HS" || $tipo == "HE") {
            $cadena = "SELECT
                            e.id,
                            e.id as id_interno,
                            e.nombre,
                            t.codigo
                            FROM especies_provistas e
                            INNER JOIN tipos_producto t ON t.id = e.id_tipo
                            WHERE e.eliminada IS NULL AND t.codigo = '$tipo'
                            ORDER BY t.codigo DESC, e.id ASC
                    ";
        }

        $val = mysqli_query($con, $cadena);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $id_interno = str_pad($re["id_interno"], 2, '0', STR_PAD_LEFT);
                echo "<option x-codigo='$re[codigo]' value='$re[id]'>$re[nombre] ($re[codigo]$id_interno)</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "busca_stats_producto") {
    $tipo_pedido = $_POST["tipo_pedido"];
    $anio = $_POST["anio"];
    $id_producto = $_POST["id_producto"];

    $arraymeses = array(
        "1" => null,
        "2" => null,
        "3" => null,
        "4" => null,
        "5" => null,
        "6" => null,
        "7" => null,
        "8" => null,
        "9" => null,
        "10" => null,
        "11" => null,
        "12" => null,
    );

    for ($i = 1; $i <= 12; $i++) {
        $mes = $i;
        $wherefecha = "";

        if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
            $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
            $dt = strtotime("$anio-$mesito-01");
            $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

            $wherefecha .= " AND p.fecha >= '$anio-$mesito-01 00:00:00'
                AND p.fecha < '$fechafin 00:00:00'
        ";
        }

        if ($tipo_pedido == "esquejes") {
            $query = "SELECT
        t.codigo as tipo,
        v.nombre as nombre_variedad,
        v.id as id_variedad,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'E'
        AND ap.estado >= -10 $wherefecha AND v.id = $id_producto";
        } else if ($tipo_pedido == "hechuraesquejes") {
            $query = "SELECT
        t.codigo as tipo,
        e.nombre as nombre_especie,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN especies_provistas e ON e.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'HE'
        AND ap.estado >= -10 $wherefecha AND e.id = $id_producto";

        } else if ($tipo_pedido == "semillas") {
            $query = "SELECT
        t.codigo as tipo,
        v.nombre as nombre_variedad,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'S'
        AND ap.estado >= -10 $wherefecha AND v.id = $id_producto";
        } else if ($tipo_pedido == "hechurasemillas") {
            $query = "SELECT
        t.codigo as tipo,
        e.nombre as nombre_especie,
        SUM(ap.cant_plantas) as cant_plantas,
        SUM(ap.cant_bandejas) as cant_bandejas,
        COUNT(ap.id) as cant_pedidos
        FROM tipos_producto t
        INNER JOIN especies_provistas e ON e.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        WHERE ap.eliminado IS NULL AND t.codigo = 'HS'
        AND ap.estado >= -10 $wherefecha AND e.id = $id_producto";
        }

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($ww = mysqli_fetch_array($val)) {
                $arraymeses[$i] = array(
                    "cant_plantas" => $ww["cant_plantas"] ?? 0,
                    "cant_bandejas" => $ww["cant_bandejas"] ?? 0,
                    "cant_pedidos" => $ww["cant_pedidos"] ?? 0,
                    //"query" => $query,
                );
            }
        }
    }
    echo json_encode($arraymeses);
} else if ($consulta == "busca_stats_lineal") {
    $tipo_pedido = $_POST["tipo_pedido"];
    $tipo_filtro = $_POST["tipo_filtro"];

    $anio = $_POST["anio"];
    $mes = $_POST["mes"];
    $wherefecha = "";

    if ($tipo_filtro == "bandejas") {
        $querycantidad = "SUM(ap.cant_bandejas)";
    } else if ($tipo_filtro == "pedidos") {
        $querycantidad = "COUNT(ap.id)";
    } else {
        $querycantidad = "SUM(ap.cant_plantas)";
    }

    $aniofin = (int) $anio + 1;
    $wherefechaanual .= " AND p.fecha >= '$anio-01-01 00:00:00'
            AND p.fecha < '$aniofin-01-01 00:00:00'
    ";

    $querymeses = "";
    if ($tipo_pedido == "semillas" || $tipo_pedido == "esquejes") {
        $array = array();
        for ($i = 1; $i <= 12; $i++) {
            $mes = $i;
            if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
                $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
                $dt = strtotime("$anio-$mesito-01");
                $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));
                $querytipo = ($tipo_pedido == "semillas") ? "S" : "E";
                $query = "
                    SELECT
                        t.codigo as tipo,
                        IFNULL($querycantidad,0) as cantidad,
                        v.nombre as nombre_variedad,
                        v.id as id_variedad
                        FROM tipos_producto t
                        INNER JOIN variedades_producto v ON v.id_tipo = t.id
                        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
                        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
                        WHERE ap.eliminado IS NULL
                        AND t.codigo = '$querytipo'
                        AND ap.estado >= 0
                        AND p.fecha >= '$anio-$mesito-01 00:00:00'
                        AND p.fecha < '$fechafin 00:00:00'
                        GROUP BY v.id
                ";

                $val = mysqli_query($con, $query);
                if (mysqli_num_rows($val) > 0) {
                    while ($ww = mysqli_fetch_array($val)) {
                        if (!$array[$ww["id_variedad"]]){
                            $array[$ww["id_variedad"]] = array(
                                "cantidades" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                                "nombre_variedad" => $ww["nombre_variedad"],
                                "tipo" => $ww["tipo"],
                                "vendidas" => (int)$ww["cantidad"]
                            );
                        }
                        else{
                            $array[$ww["id_variedad"]]["vendidas"] += (int)$ww["cantidad"];
                        }
                        $array[$ww["id_variedad"]]["cantidades"][$i-1] = (int)$ww["cantidad"];
                    }
                }
            }
        }
        echo json_encode($array);

    }
    else if ($tipo_pedido == "hechurasemillas" || $tipo_pedido == "hechuraesquejes") {
        $array = array();
        for ($i = 1; $i <= 12; $i++) {
            $mes = $i;
            if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
                $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
                $dt = strtotime("$anio-$mesito-01");
                $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));
                $querytipo = ($tipo_pedido == "hechurasemillas") ? "HS" : "HE";
                $query = "
                    SELECT
                        t.codigo as tipo,
                        IFNULL($querycantidad,0) as cantidad,
                        e.nombre as nombre_variedad,
                        e.id as id_variedad
                        FROM tipos_producto t
                        INNER JOIN especies_provistas e ON e.id_tipo = t.id
                        INNER JOIN articulospedidos ap ON ap.id_especie = e.id
                        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
                        WHERE ap.eliminado IS NULL
                        AND t.codigo = '$querytipo'
                        AND ap.estado >= 0
                        AND p.fecha >= '$anio-$mesito-01 00:00:00'
                        AND p.fecha < '$fechafin 00:00:00'
                        GROUP BY e.id
                ";

                $val = mysqli_query($con, $query);
                if (mysqli_num_rows($val) > 0) {
                    while ($ww = mysqli_fetch_array($val)) {
                        if (!$array[$ww["id_variedad"]]){
                            $array[$ww["id_variedad"]] = array(
                                "cantidades" => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                                "nombre_variedad" => $ww["nombre_variedad"],
                                "tipo" => $ww["tipo"],
                                "vendidas" => (int)$ww["cantidad"]
                            );
                        }
                        else{
                            $array[$ww["id_variedad"]]["vendidas"] += (int)$ww["cantidad"];
                        }
                        $array[$ww["id_variedad"]]["cantidades"][$i-1] = (int)$ww["cantidad"];
                    }
                }
            }
        }
        echo json_encode($array);

    }
    
    
    
    
   
}
