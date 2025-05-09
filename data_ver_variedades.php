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

if ($consulta == "busca_variedades") {
    $filtroAtributos = $_POST["filtroAtributos"] ?? [];
    $atributos = [];
    $headersAtributos = "";
    $atributosValoresPorVariedad = [];

    if (!empty($filtroAtributos)) {
        // Obtener los atributos seleccionados
        $query = "SELECT * FROM atributos WHERE id IN (" . implode(",", $filtroAtributos) . ")";
        $result = mysqli_query($con, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $atributos[] = $row;
            $headersAtributos .= "<th>$row[nombre]</th>";
        }

        // Obtener los valores de atributos para cada variedad
        $query = "SELECT avv.id_variedad, av.valor, av.id_atributo 
                  FROM atributos_valores_variedades avv
                  INNER JOIN atributos_valores av ON avv.id_atributo_valor = av.id
                  WHERE av.id_atributo IN (" . implode(",", $filtroAtributos) . ")";
        $result = mysqli_query($con, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $atributosValoresPorVariedad[$row['id_variedad']][$row['id_atributo']] = $row['valor'];
        }
    }

    $id_variedadfiltro = $_POST['filtro'];
    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $cadena = "SELECT v.id as id_variedad, t.id as id_tipo, t.nombre as nombre_tipo,
          v.nombre as nombre_variedad, t.codigo, v.precio, v.precio_detalle, v.id_interno, v.dias_produccion
          FROM variedades_producto v 
          INNER JOIN tipos_producto t ON t.id = v.id_tipo";

    if ($id_variedadfiltro != null) {
        $cadena .= " WHERE v.eliminada IS NULL AND id_tipo = " . $id_variedadfiltro;
    } else {
        $cadena .= " WHERE v.eliminada IS NULL";
    }

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'></div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead><tr>";
        echo "<th>Tipo</th><th>Variedad</th>$headersAtributos<th>Precio Mayorista</th><th>Precio Mayorista +IVA</th><th>Precio Detalle</th><th>Precio Detalle +IVA</th><th>Días en Producción</th><th></th>";
        echo "</tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_variedad = $ww['id_variedad'];
            $tipo = $ww['nombre_tipo'];
            $variedad = $ww['nombre_variedad'];
            $precio = $ww['precio'];
            $precio_detalle = $ww['precio_detalle'] ?? "";
            $precio_detalle_iva = $ww["precio_detalle"] ? number_format(round((float) $ww['precio_detalle'] * 1.19, 0, PHP_ROUND_HALF_UP), 2) : "";
            $precio_iva = number_format(round((float) $ww['precio'] * 1.19, 0, PHP_ROUND_HALF_UP), 2);
            $btneliminar = $_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash' onClick='eliminar($id_variedad)'></button>" : "";

            $btneditar = "<button class='btn btn-primary fa fa-edit' onClick='editarVariedad(event, this)'></button>";

            echo "
            <tr class='text-center' style='cursor:pointer' x-codigo-tipo='$ww[codigo]' x-id-interno='$ww[id_interno]' x-dias-produccion='$ww[dias_produccion]' x-id='$id_variedad' x-id-tipo='$id_tipo' x-precio='$precio' x-precio-iva='$precio_iva' x-precio-detalle='$precio_detalle' x-precio-detalle-iva='$precio_detalle_iva' x-nombre='$variedad'>
            <td class='clickable'>$tipo $ww[id_interno]</td>
            <td class='clickable'>$variedad</td>";

            if (!empty($atributos)) {
                foreach ($atributos as $a) {
                    $valor = $atributosValoresPorVariedad[$id_variedad][$a['id']] ?? ''; // Obtener el valor si existe
                    echo "<td>$valor</td>";
                }
            }

            echo "<td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ $precio</td>
                  <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ $precio_iva</td>
                  <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>" . ($precio_detalle != "" ? "$ $precio_detalle" : "") . "</td>
                  <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>" . ($precio_detalle_iva != "" ? "$ $precio_detalle_iva" : "") . "</td>
                  <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ww[dias_produccion]</td>
                  <td class='text-center'>
                  <div class='d-flex flex-row justify-content-center' style='gap:5px;'>
                  $btneliminar
                  $btneditar
                  </div>
                  </td>
                  </tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron productos en la base de datos...</b></div>";
    }
} else if ($consulta == "agregar_variedad") {
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $codigo = mysqli_real_escape_string($con, $_POST['codigo']);
    $dias_produccion = $_POST["dias_produccion"] == null ? "NULL" : $_POST["dias_produccion"];
    $precio = $_POST['precio'];
    $precio_detalle = isset($_POST['precio_detalle']) && !empty($_POST["precio_detalle"]) ? $_POST['precio_detalle'] : "NULL";
    $id_tipo = $_POST["id_tipo"];
    try {
        // $val = mysqli_query($con, "SELECT * FROM variedades_producto WHERE nombre = UPPER('$nombre') AND id_tipo = $id_tipo;");
        // if (mysqli_num_rows($val) > 0) {
        //     echo "YA EXISTE UNA VARIEDAD CON ESE NOMBRE!";
        // } else {
        $errors = [];
        $val = mysqli_query($con, "SELECT * FROM variedades_producto WHERE id_tipo = $id_tipo AND id_interno = $codigo;");
        if (mysqli_num_rows($val) > 0) {
            echo "EL CÓDIGO INGRESADO YA ESTÁ EN USO. ELIGE OTRO.";
        } else {
            mysqli_autocommit($con, FALSE);
            $query = "INSERT INTO variedades_producto (nombre, precio, precio_detalle, id_tipo, id_interno, dias_produccion) VALUES (UPPER('$nombre'), '$precio', $precio_detalle, '$id_tipo', '$codigo', $dias_produccion);";
            if (mysqli_query($con, $query)) {
                $id_variedad = mysqli_insert_id($con);
                if (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) {
                    $atributos = json_decode($_POST["atributos"], true);

                    foreach ($atributos as $atr) {
                        if (isset($atr["valorSelect"]) && $atr["valorSelect"] != "0") {
                            $item = $atr["valorSelect"];
                            $query = "INSERT INTO atributos_valores_variedades (
                                        id_variedad,
                                        id_atributo_valor
                                    ) VALUES (
                                        $id_variedad,
                                        $item
                                    )";
                            if (!mysqli_query($con, $query)) {
                                $errors[] = mysqli_error($con) . "-" . $query;
                            }
                        }
                    }
                }
            } else {
                $errors[] = mysqli_error($con) . "-" . $query;
            }

            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        echo $error . "<br>";
                    }
                } else {
                    echo "error:" . mysqli_error($con);
                }
            }
        }
        //}
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "editar_variedad") {
    $id_variedad = $_POST['id_variedad'];
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $precio_detalle = isset($_POST['precio_detalle']) && !empty($_POST["precio_detalle"]) ? $_POST['precio_detalle'] : "NULL";
    $dias_produccion = $_POST["dias_produccion"] == null ? "NULL" : $_POST["dias_produccion"];

    try {
        $errors = [];
        mysqli_autocommit($con, FALSE);
        $query = "UPDATE variedades_producto SET nombre = UPPER('$nombre'), precio = '$precio', precio_detalle = $precio_detalle, dias_produccion = $dias_produccion WHERE id = $id_variedad";
        if (mysqli_query($con, $query)) {
            if (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) {
                $query = "DELETE FROM atributos_valores_variedades WHERE id_variedad = $id_variedad;";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                $atributos = json_decode($_POST["atributos"], true);

                foreach ($atributos as $atr) {
                    // if (isset($atr["valorSelect"]) && count($atr["valorSelect"]) > 0) {
                    //     foreach ($atr["valorSelect"] as $item) {
                    if (isset($atr["valorSelect"]) && $atr["valorSelect"] != "0") {
                        $item = $atr["valorSelect"];
                        $query = "INSERT INTO atributos_valores_variedades (
                                id_variedad,
                                id_atributo_valor
                            ) VALUES (
                                $id_variedad,
                                $item
                            )";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                    }
                    // }
                }
            }
        } else {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    echo $error . "<br>";
                }
            } else {
                echo "error:" . mysqli_error($con);
            }
        }
    } catch (\Throwable $th) {
        echo "error: " . $th . " " . $query;
    }
} else if ($consulta == "eliminar_variedad") {
    try {
        $id_variedad = $_POST["id_variedad"];
        if (mysqli_query($con, "UPDATE variedades_producto SET eliminada = 1 WHERE id = $id_variedad;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "busca_variedades_select") {
    try {
        $id_tipo = $_POST["id_tipo"];
        $cadena = "select v.id, v.id_interno, v.nombre, FLOOR(v.precio) as precio, t.codigo from variedades_producto v INNER JOIN tipos_producto t ON t.id = v.id_tipo WHERE v.eliminada IS NULL AND v.id_tipo = $id_tipo order by v.id_interno ASC;";
        $val = mysqli_query($con, $cadena);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $id_interno = str_pad($re["id_interno"], 2, '0', STR_PAD_LEFT);
                $precio = $re["precio"] != null ? "- $$re[precio]" : "";
                $nombre = mysqli_real_escape_string($con, $re["nombre"]);
                echo "<option x-precio='$re[precio]' x-codigo='$re[codigo]' x-nombre='$nombre' x-codigofull='$re[codigo]$id_interno' value='$re[id]'>$re[nombre] ($re[codigo]$id_interno) $precio</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "busca_especies_select") {
    try {
        $id_tipo = $_POST["id_tipo"];
        $cadena = "SELECT e.id, e.nombre FROM especies_provistas e INNER JOIN tipos_producto v ON v.id = e.id_tipo WHERE e.eliminada IS NULL AND e.id_tipo = $id_tipo order by e.id ASC;";
        $val = mysqli_query($con, $cadena);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                echo "<option x-nombre='$re[nombre]' value='$re[id]'>$re[nombre] ($re[id])</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "agregar_especie") {
    $nombre = $_POST['nombre'];
    $id_tipo = $_POST["id_tipo"];
    $dias_produccion = $_POST["dias_produccion"] == null ? "NULL" : $_POST["dias_produccion"];
    try {
        $val = mysqli_query($con, "SELECT * FROM especies_provistas WHERE nombre = UPPER('$nombre') AND id_tipo = $id_tipo;");
        if (mysqli_num_rows($val) > 0) {
            echo "Ya existe una especie con ese nombre!";
        } else {
            $query = "INSERT INTO especies_provistas (nombre, id_tipo, dias_produccion) VALUES (UPPER('$nombre'), '$id_tipo', '$dias_produccion');";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        }
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "cargar_dias_produccion") {
    $tipo = $_POST["tipo"];
    $id_producto = $_POST["id_producto"];
    try {
        $val = mysqli_query($con, "SELECT dias_produccion FROM " . ($tipo == "variedad" ? "variedades_producto" : "especies_provistas") . " WHERE id = $id_producto;");
        if (mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            echo "dias:$re[dias_produccion]";
        }
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "exportar_variedades") {
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $field = array(
        "Product ID",
        "Active (0/1)",
        "Name *",
        "Categories (x,y,z...)",
        "Price tax excluded",
        "Tax rules ID",
        "Wholesale price",
        "On sale (0/1)",
        "Discount amount",
        "Discount percent",
        "Discount from (yyyy-mm-dd)",
        "Discount to (yyyy-mm-dd)",
        "Reference #",
        "Supplier reference #",
        "Supplier",
        "Manufacturer",
        "EAN13",
        "UPC",
        "MPN",
        "Ecotax",
        "Width",
        "Height",
        "Depth",
        "Weight",
        "Delivery time of in-stock products",
        "Delivery time of out-of-stock products with allowed orders",
        "Quantity",
        "Minimal quantity",
        "Low stock level",
        "Send me an email when the quantity is under this level",
        "Visibility",
        "Additional shipping cost",
        "Unity",
        "Unit price",
        "Summary",
        "Description",
        "Tags (x,y,z...)",
        "Meta title",
        "Meta keywords",
        "Meta description",
        "URL rewritten",
        "Text when in stock",
        "Text when backorder allowed",
        "Available for order (0 = No, 1 = Yes)",
        "Product available date",
        "Product creation date",
        "Show price (0 = No, 1 = Yes)",
        "Image URLs (x,y,z...)",
        "Image alt texts (x,y,z...)",
        "Delete existing images (0 = No, 1 = Yes)",
        "Feature(Name:Value:Position)",
        "Available online only (0 = No, 1 = Yes)",
        "Condition",
        "Customizable (0 = No, 1 = Yes)",
        "Uploadable files (0 = No, 1 = Yes)",
        "Text fields (0 = No, 1 = Yes)",
        "Out of stock action",
        "Virtual product",
        "File URL",
        "Number of allowed downloads",
        "Expiration date",
        "Number of days",
        "ID / Name of shop",
        "Advanced stock management",
        "Depends On Stock",
        "Warehouse",
        "Acessories  (x,y,z...)",
    );

    $cadena = "SELECT v.id as id_variedad, t.id as id_tipo, t.nombre as nombre_tipo,
          v.nombre as nombre_variedad, t.codigo, v.precio, v.id_interno, v.dias_produccion
          FROM variedades_producto v INNER JOIN tipos_producto t ON t.id = v.id_tipo";

    $cadena .= " WHERE v.eliminada IS NULL AND t.codigo IN ('E', 'S', 'PT')";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {

        try {
            $fp = fopen('file.csv', 'w');
            $array = str_replace('"', '', $field);
            //fputs($fp, implode(';', $array)."\n");

            while ($ww = mysqli_fetch_array($val)) {
                $id_variedad = $ww['id_variedad'];
                $tipo = $ww['nombre_tipo'];
                $variedad = $ww['nombre_variedad'];
                $precio = $ww['precio'];
                $precio_iva = number_format(round((float) $ww['precio'] * 1.19, 0, PHP_ROUND_HALF_UP), 2);

                $producto = "";
                if ($ww["codigo"] == "PT") {
                    $producto = ucwords(strtolower(str_replace("/", "-", $ww["nombre_variedad"])));
                } else if ($ww["codigo"] == "E" || $ww["codigo"] == "S") {
                    $producto = ucwords(strtolower(str_replace("/", "-", $ww["nombre_variedad"])));
                }
                $field = array(
                    $ww["id_variedad"],
                    1,
                    $producto,
                    ucwords(strtolower($ww["nombre_tipo"])),
                    (int) $ww["precio"],
                    1,
                    "",
                    0,
                    "",
                    "",
                    "",
                    "",
                    $ww["codigo"] . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT),
                    "", //Supplier reference #",
                    "", //Supplier",
                    "", //Manufacturer",
                    "", //EAN13",
                    "", //UPC",
                    "",
                    "", //Ecotax",
                    "", //Width",
                    "", //Height",
                    "", //Depth",
                    "", //Weight",
                    "", //Delivery time of in-stock products",
                    "", //Delivery time of out-of-stock products with allowed orders",
                    0, //Quantity",
                    162, //"Minimal quantity",
                    "", //Low stock level",
                    "", //Send me an email when the quantity is under this level",
                    "", //Visibility",
                    "", //Additional shipping cost",
                    "", //Unity",
                    "", //Unit price",
                    "", //Summary",
                    "", //Description",
                    preg_replace('/\s+/', '-', strtolower($ww["nombre_tipo"])),
                    "Meta title-" . $ww["nombre_variedad"],
                    "Meta keywords-" . $ww["nombre_variedad"],
                    "Meta description-" . $ww["nombre_variedad"],
                    "", //URL REWRITTEN
                    "En Stock",//Text when in stock",
                    "Reserva disponible",//Text when backorder allowed",
                    0,
                    "2023-01-15", //Product available date",
                    "2023-01-15", //Product creation date",
                    1, //"Show price (0 = No, 1 = Yes)",
                    "", //Image URLs (x,y,z...)",
                    "", //Image alt texts (x,y,z...)",
                    0,//Delete existing images (0 = No, 1 = Yes)",
                    "",//Feature(Name:Value:Position)",
                    0, //Available online only (0 = No, 1 = Yes)",
                    "new", //"Condition",
                    0, //"Customizable (0 = No, 1 = Yes)",
                    0, //"Uploadable files (0 = No, 1 = Yes)",
                    0, //"Text fields (0 = No, 1 = Yes)",
                    0, //"Out of stock action",
                    0, //"Virtual product",
                    "", //File URL",
                    "", //Number of allowed downloads",
                    "", //Expiration date",
                    "", //Number of days",
                    0, //"ID / Name of shop",
                    0, //"Advanced stock management",
                    0, //"Depends On Stock",
                    0, //"Warehouse",
                    0,//"Acessories  (x,y,z...)",
                );
                $array = str_replace('"', '', $field);
                fputs($fp, implode(';', $array) . "\n");
            }
            fclose($fp);
            echo "success";
        } catch (\Throwable $th) {
            throw $th;
        }

    }
}
