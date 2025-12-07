<?php

include "./class_lib/sesionSecurity.php";

error_reporting(0);
include 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

$tipo = $_POST["tipo"];

if ($tipo == "seguimiento") {
    $consulta2 = "Select COUNT(id) as cantidad FROM articulospedidos WHERE eliminado IS NULL AND estado IN (0, 1, 2, 3, 4, 5, 6);";
    $val = mysqli_query($con, $consulta2);
    $cantidad = 0;
    if (mysqli_num_rows($val) > 0) {
        while ($rf = mysqli_fetch_array($val)) {
            $cantidad = $rf['cantidad'];
        }
    }

    echo "
        <a href=\"ver_seguimiento.php\">
            <div class=\"small-box bg-red\">
                <div class=\"inner\">
                    <h3>$cantidad</h3>
                    <p>Seguimiento de Pedidos</p>
                </div>
                <div class=\"icon\">
                    <i class=\"ion ion-calendar\"></i>
                </div>
                <span class=\"small-box-footer\">Ver Seguimiento <i class=\"fa fa-arrow-circle-right\"></i></span>
            </div>
        </a>
    ";
}
else if ($tipo == "vivero") {
    echo "
        <a href=\"vivero.php\">
            <div class=\"small-box bg-blue\">
                <div class=\"inner\">
                    <p>Seguimiento Vivero</p>
                </div>
                <div class=\"icon\">
                    <i class=\"ion ion-calendar\"></i>
                </div>
                <h3 style=\"visibility:hidden;\">0</h3>
                <span class=\"small-box-footer\">Ver Seguimiento <i class=\"fa fa-arrow-circle-right\"></i></span>
            </div>
        </a>
    ";
} 
else if ($tipo == "laboratorio") {
    echo "
        <a href=\"laboratorio.php\">
            <div class=\"small-box bg-success\">
                <div class=\"inner\">
                    <p class='text-light'>Laboratorio</p>
                </div>
                <div class=\"icon\">
                    <i class=\"ion ion-waterdrop\"></i>
                </div>
                <h3 style=\"visibility:hidden;\">0</h3>
                <span class=\"small-box-footer\">Ver Módulo <i class=\"fa fa-arrow-circle-right\"></i></span>
            </div>
        </a>
    ";
}
else if ($tipo == "pedidos") {
    $i = "0";
    $consulta = "SELECT  (SELECT COUNT(*)
                FROM   articulospedidos WHERE eliminado IS NULL
                ) AS todos,
                (
                SELECT COUNT(*)
                FROM   articulospedidos WHERE estado = -10 AND eliminado IS NULL
                ) AS pendientes";
    $val = mysqli_query($con, $consulta);

    if (mysqli_num_rows($val) > 0) {
        $r = mysqli_fetch_assoc($val);
        $i = $r['todos'];
        if ($i > 999999){
            $i = "+999999";
        }
        $pendientes = $r["pendientes"];
    }
    echo "
    <a href=\"ver_pedidos.php\">
        <div class=\"small-box bg-aqua\">
        <div class=\"inner\">
            <h3>$i</h3>
            <p class=\"titulo-seccion\">Pedidos <span class=\"text-danger\" style=\"font-size:11px;font-weight:bold;display: inline-block;\">($pendientes PENDIENTES)</span></p>
        </div>
        <div class=\"icon\">
            <i class=\"ion ion-bag\"></i>
        </div>
        <span class=\"small-box-footer\">Ver Pedidos <i class=\"fa fa-arrow-circle-right\"></i></span>
        </div>
    </a>
    ";
} else if ($tipo == "mesadas") {
    $total_mesadas = "0";
    $consulta = "Select count(id_mesada) as total from mesadas;";
    $val = mysqli_query($con, $consulta);

    if (mysqli_num_rows($val) > 0) {
        while ($rf = mysqli_fetch_array($val)) {
            $total_mesadas = $rf['total'];
        }
    }

    echo "
    <a href=\"ver_mesadas.php\">
        <div class=\"small-box bg-gray\">
        <div class=\"inner\">
            <h3>$total_mesadas</h3>
            <p>Mesones</p>
        </div>
        <div class=\"icon\">
            <i class=\"fa fa-table\"></i>
        </div>
        <span class=\"small-box-footer\">Ver Mesones <i class=\"fa fa-arrow-circle-right\"></i></span>
        </div>
    </a>
    ";
} 
else if ($tipo == "reservas") {
    $i = "0";
    $consulta = "SELECT * FROM (Select IFNULL(COUNT(id),0) as reservas FROM reservas_productos WHERE estado >= 0) as q1,
    (Select IFNULL(COUNT(id),0) as reservas_nuevas FROM reservas_productos WHERE estado >= 0 AND visto = 0) as q2";
    $val = mysqli_query($con, $consulta);
    if (mysqli_num_rows($val) > 0) {
        $r = mysqli_fetch_assoc($val); 
        $i = $r['reservas'];
        
    }

    $nuevas = ($r["reservas_nuevas"] > 0 ? "<br>($r[reservas_nuevas] NUEVAS)" : "");

    echo "
    
    
    <a href=\"ver_ventas.php\">
    <div class=\"small-box bg-primary\">
        <div class=\"inner\"  style=\"height:7.1em;\">    
            <p ".($r["reservas_nuevas"] > 0 ? "style='font-weight:bold'" : "").">Ventas y Stock$nuevas</p>
        </div>
        <div class=\"icon\">
          <i class=\"fa fa-shopping-cart".($r["reservas_nuevas"] > 0 ? " blink":"")."\"></i>
        </div>
         <span class=\"small-box-footer\">Ver Reservas <i class=\"fa fa-arrow-circle-right\"></i></span>
   
    </div>
</a>
    
        
    ";
}
else if ($tipo == "historial") {
    $i = "0";
    $consulta = "Select IFNULL(COUNT(id_entrega),0) as cuenta FROM entregas";
    $val = mysqli_query($con, $consulta);
    if (mysqli_num_rows($val) > 0) {
        while ($r = mysqli_fetch_array($val)) {
            $i = $r['cuenta'];
        }
    }

    echo "
        <a href=\"ver_historial.php\">
            <div class=\"small-box bg-purple\">
                <div class=\"inner\">
                  <h3>$i</h3>
                  <p class=\"titulo-seccion\">Historial de Entregas</p>
                </div>
                <div class=\"icon\">
                  <i class=\"fa fa-history\"></i>
                </div>
                 <span class=\"small-box-footer\">Ver Historial <i class=\"fa fa-arrow-circle-right\"></i></span>
            </div>
        </a>
    ";
} else if ($tipo == "stock") {
    $i = "0";
    $consulta = "SELECT  (
    SELECT IFNULL(SUM(cantidad),0)
    FROM stock_bandejas
    )
    AS stock_bandejas,
    (
    SELECT IFNULL(SUM(cantidad),0)
    FROM stock_bandejas_retiros
    )
    AS stock_bandejas_retiros";
    $val = mysqli_query($con, $consulta);

    if (mysqli_num_rows($val) > 0) {
        $r = mysqli_fetch_assoc($val);
        $i = $r['stock_bandejas'] - $r['stock_bandejas_retiros'];
        if ($i > 999999){
            $i = "+999999";
        }
    }

    echo "
    <a href=\"ver_stock_bandejas.php\">
        <div class=\"small-box bg-orange\">
            <div class=\"inner\">
            <h3>$i</h3>
            <p class=\"titulo-seccion\">Bandejas en Stock</p>
            </div>
            <div class=\"icon\">
            <i class=\"fa fa-align-justify\"></i>
            </div>
            <span class=\"small-box-footer\"
            >Ver Stock <i class=\"fa fa-arrow-circle-right\"></i
            ></span>
        </div>
    </a>
    ";
} else if ($tipo == "semillas") {
    $i = "0";
    $consulta = "SELECT  (
        SELECT IFNULL(SUM(cantidad),0)
        FROM stock_semillas
        )
        AS stock_semillas,
        (
        SELECT IFNULL(SUM(cantidad),0)
        FROM stock_semillas_retiros
        )
        AS stock_semillas_retiros";
    $val = mysqli_query($con, $consulta);

    if (mysqli_num_rows($val) > 0) {
        $r = mysqli_fetch_assoc($val);
        $i = $r['stock_semillas'] - $r['stock_semillas_retiros'];
        if ($i > 999999){
            $i = "+999999";
        }
    }

    echo "
    <a href=\"ver_semillas.php\">
        <div class=\"small-box bg-lime\">
            <div class=\"inner\">
            <h3 class='text-dark'>$i</h3>
            <p class=\"titulo-seccion text-dark\">Semillas</p>
            </div>
            <div class=\"icon\">
            <i class=\"fa fa-leaf\"></i>
            </div>
            <span class=\"small-box-footer\"
            >Ver Semillas <i class=\"fa fa-arrow-circle-right\"></i
            ></span>
        </div>
    </a>
    ";
}
else if ($tipo == "alertas") {
    if (is_array($_SESSION["arraypermisos"]) && !in_array("pedidos", $_SESSION["arraypermisos"]))
        return;

    $date = date('Y-m-d');
    $consulta = "SELECT  (SELECT COUNT(*)
                FROM   articulospedidos WHERE fecha_entrega < '$date' AND estado >= 0 AND estado <= 5 AND eliminado IS NULL
                ) AS atrasados,
                (
                SELECT COUNT(*)
                FROM   articulospedidos WHERE estado = 5 OR estado = 6 AND eliminado IS NULL
                ) AS paraentregar,
                (
                SELECT COUNT(*)
                FROM articulospedidos WHERE estado >= 0 AND estado <= 5 AND problema IS NOT NULL AND eliminado IS NULL
                ) AS problemas";
    $val = mysqli_query($con, $consulta);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        $array = array(
            "atrasados" => $ww["atrasados"],
            "paraentregar" => $ww["paraentregar"],
            "problemas" => $ww["problemas"],
        );
        echo json_encode($array);
    }   
}