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
          v.nombre as nombre_variedad, v.descripcion, t.codigo, v.precio, v.precio_detalle, v.precio_produccion, v.id_interno, v.dias_produccion
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
        echo "<th>Tipo</th><th>Variedad</th>$headersAtributos<th>Precio Mayorista</th><th>Precio Mayorista +IVA</th><th>Precio Detalle</th><th>Precio Detalle +IVA</th><th>Precio Producción</th><th>Días en Producción</th><th>Descripción</th><th></th>";
        echo "</tr></thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_variedad = $ww['id_variedad'];
            $tipo = $ww['nombre_tipo'];
            $variedad = $ww['nombre_variedad'];
            $precio = $ww['precio'];
            $precio_detalle = $ww['precio_detalle'] ?? "";
            $precio_produccion = $ww['precio_produccion'] ?? "";
            $precio_detalle_iva = $ww["precio_detalle"] ? number_format(round((float) $ww['precio_detalle'] * 1.19, 0, PHP_ROUND_HALF_UP), 2) : "";
            $precio_iva = number_format(round((float) $ww['precio'] * 1.19, 0, PHP_ROUND_HALF_UP), 2);
            $btneliminar = $_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash' onClick='eliminar($id_variedad)'></button>" : "";

            $btneditar = "<button class='btn btn-primary fa fa-edit' onClick='editarVariedad(event, this)'></button>";

            echo "
            <tr class='text-center' style='cursor:pointer' x-codigo-tipo='$ww[codigo]' x-id-interno='$ww[id_interno]' x-dias-produccion='$ww[dias_produccion]' x-id='$id_variedad' x-id-tipo='$id_tipo' x-precio='$precio' x-precio-iva='$precio_iva' x-precio-detalle='$precio_detalle' x-precio-detalle-iva='$precio_detalle_iva' x-precio-produccion='$precio_produccion' x-nombre='$variedad' x-descripcion='$ww[descripcion]'>
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
                  <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>" . ($precio_produccion != "" ? "$ $precio_produccion" : "") . "</td>
                  <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ww[dias_produccion]</td>
                    <td class='clickable'><small>$ww[descripcion]</small></td>
                  
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
}  else if ($consulta == "eliminar_variedad") {
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
// Agregar estas consultas al archivo data_ver_variedades.php

else if ($consulta == "obtener_imagenes_variedad") {
    $id_variedad = $_POST["id_variedad"];
    
    try {
        $query = "SELECT id, nombre_archivo, fecha_subida 
                  FROM imagenes_variedades 
                  WHERE id_variedad = $id_variedad 
                  ORDER BY fecha_subida ASC";
        
        $result = mysqli_query($con, $query);
        $imagenes = [];
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $imagenes[] = $row;
            }
        }
        
        echo json_encode($imagenes);
        
    } catch (\Throwable $th) {
        echo json_encode([]);
    }
}

else if ($consulta == "eliminar_imagen_variedad") {
    $id_imagen = $_POST["id_imagen"];
    
    try {
        // Obtener información de la imagen
        $query = "SELECT nombre_archivo FROM imagenes_variedades WHERE id = $id_imagen";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $imagen = mysqli_fetch_assoc($result);
            $ruta_archivo = "uploads/variedades/" . $imagen['nombre_archivo'];
            
            // Eliminar archivo físico
            if (file_exists($ruta_archivo)) {
                unlink($ruta_archivo);
            }
            
            // Eliminar registro de base de datos
            $query = "DELETE FROM imagenes_variedades WHERE id = $id_imagen";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                echo "error: " . mysqli_error($con);
            }
        } else {
            echo "error: Imagen no encontrada";
        }
        
    } catch (\Throwable $th) {
        echo "error: " . $th->getMessage();
    }
}

// Modificar la función agregar_variedad existente
else if ($consulta == "agregar_variedad") {

    mysqli_set_charset($con, "utf8mb4");
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $codigo = mysqli_real_escape_string($con, $_POST['codigo']);
    $dias_produccion = $_POST["dias_produccion"] == null || $_POST["dias_produccion"] == '' ? "NULL" : $_POST["dias_produccion"];
    $precio = $_POST['precio'];
    $precio_detalle = isset($_POST['precio_detalle']) && !empty($_POST["precio_detalle"]) ? $_POST['precio_detalle'] : "NULL";
    $precio_produccion = isset($_POST['precio_produccion']) && !empty($_POST["precio_produccion"]) ? $_POST['precio_produccion'] : "NULL";
    $id_tipo = $_POST["id_tipo"];
    $descripcion = isset($_POST['descripcion']) && !empty($_POST["descripcion"]) ? "'".mysqli_real_escape_string($con, $_POST['descripcion'])."'" : "NULL";     
    try {
        $errors = [];
        
        // Verificar código único
        $val = mysqli_query($con, "SELECT * FROM variedades_producto WHERE id_tipo = $id_tipo AND id_interno = $codigo;");
        if (mysqli_num_rows($val) > 0) {
            echo "EL CÓDIGO INGRESADO YA ESTÁ EN USO. ELIGE OTRO.";
            return;
        }
        
        mysqli_autocommit($con, FALSE);

        // Insertar variedad
        $query = "INSERT INTO variedades_producto (nombre, precio, precio_detalle, precio_produccion, id_tipo, id_interno, dias_produccion, descripcion) VALUES (UPPER('$nombre'), '$precio', $precio_detalle, $precio_produccion, '$id_tipo', '$codigo', $dias_produccion, $descripcion);";
        
        if (mysqli_query($con, $query)) {
            $id_variedad = mysqli_insert_id($con);
            
            // Procesar atributos
            if (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) {
                $atributos = json_decode($_POST["atributos"], true);
                foreach ($atributos as $atr) {
                    if (isset($atr["valorSelect"]) && $atr["valorSelect"] != "0") {
                        $item = $atr["valorSelect"];
                        $query = "INSERT INTO atributos_valores_variedades (id_variedad, id_atributo_valor) VALUES ($id_variedad, $item)";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                    }
                }
            }
            
            // Procesar imágenes
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                $resultado_imagenes = procesarImagenesVariedad($con, $id_variedad, $_FILES['imagenes']);
                if (!$resultado_imagenes['success']) {
                    $errors = array_merge($errors, $resultado_imagenes['errors']);
                }
            }
            
        } else {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        if (count($errors) === 0 && mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
            if (count($errors) > 0) {
                echo "error: " . implode("<br>", $errors);
            } else {
                echo "error: " . mysqli_error($con);
            }
        }
        
    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    }
}

// Modificar la función editar_variedad existente
else if ($consulta == "editar_variedad") {

    mysqli_set_charset($con, "utf8mb4");
    $id_variedad = $_POST['id_variedad'];
    $descripcion = isset($_POST['descripcion']) && !empty($_POST["descripcion"]) ? "'".mysqli_real_escape_string($con, $_POST['descripcion'])."'" : "NULL";
    $nombre = mysqli_real_escape_string($con, $_POST["nombre"]);
    $precio = $_POST["precio"];
    $precio_detalle = isset($_POST['precio_detalle']) && !empty($_POST["precio_detalle"]) ? $_POST['precio_detalle'] : "NULL";
    $precio_produccion = isset($_POST['precio_produccion']) && !empty($_POST["precio_produccion"]) ? $_POST['precio_produccion'] : "NULL";
    $dias_produccion = $_POST["dias_produccion"] == null || $_POST["dias_produccion"] == '' ? "NULL" : $_POST["dias_produccion"];

    try {
        $errors = [];
        mysqli_autocommit($con, FALSE);

        // Actualizar variedad
        $query = "UPDATE variedades_producto SET nombre = UPPER('$nombre'), precio = '$precio', precio_detalle = $precio_detalle, precio_produccion = $precio_produccion, dias_produccion = $dias_produccion, descripcion = $descripcion WHERE id = $id_variedad";
        
        if (mysqli_query($con, $query)) {
            
            // Procesar atributos
            if (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) {
                $query = "DELETE FROM atributos_valores_variedades WHERE id_variedad = $id_variedad;";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                $atributos = json_decode($_POST["atributos"], true);
                foreach ($atributos as $atr) {
                    if (isset($atr["valorSelect"]) && $atr["valorSelect"] != "0") {
                        $item = $atr["valorSelect"];
                        $query = "INSERT INTO atributos_valores_variedades (id_variedad, id_atributo_valor) VALUES ($id_variedad, $item)";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                    }
                }
            }
            
            // Eliminar imágenes marcadas para eliminar
            if (isset($_POST["imagenes_eliminar"]) && !empty($_POST["imagenes_eliminar"])) {
                $imagenes_eliminar = json_decode($_POST["imagenes_eliminar"], true);
                foreach ($imagenes_eliminar as $id_imagen) {
                    $resultado = eliminarImagenVariedad($con, $id_imagen);
                    if (!$resultado['success']) {
                        $errors[] = $resultado['error'];
                    }
                }
            }
            
            // Procesar nuevas imágenes
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                $resultado_imagenes = procesarImagenesVariedad($con, $id_variedad, $_FILES['imagenes']);
                if (!$resultado_imagenes['success']) {
                    $errors = array_merge($errors, $resultado_imagenes['errors']);
                }
            }
            
        } else {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        if (count($errors) === 0 && mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
            if (count($errors) > 0) {
                echo "error: " . implode("<br>", $errors);
            } else {
                echo "error: " . mysqli_error($con);
            }
        }
        
    } catch (\Throwable $th) {
        mysqli_rollback($con);
        echo "error: " . $th->getMessage();
    }
}

// Función auxiliar para procesar imágenes
function procesarImagenesVariedad($con, $id_variedad, $files) {
    $errors = [];
    $success = true;
    $max_imagenes = 3;
    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    
    // Crear directorio si no existe
    $upload_dir = "uploads/variedades/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Verificar límite de imágenes
    $query = "SELECT COUNT(*) as total FROM imagenes_variedades WHERE id_variedad = $id_variedad";
    $result = mysqli_query($con, $query);
    $current_count = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $current_count = $row['total'];
    }
    
    $files_count = count(array_filter($files['name']));
    if ($current_count + $files_count > $max_imagenes) {
        return [
            'success' => false,
            'errors' => ["Máximo $max_imagenes imágenes permitidas. Actualmente tienes $current_count."]
        ];
    }
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if (empty($files['name'][$i])) continue;
        
        $file_name = $files['name'][$i];
        $file_tmp = $files['tmp_name'][$i];
        $file_size = $files['size'][$i];
        $file_type = $files['type'][$i];
        $file_error = $files['error'][$i];
        
        // Validaciones
        if ($file_error !== UPLOAD_ERR_OK) {
            $errors[] = "Error al subir $file_name";
            continue;
        }
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipo de archivo no válido para $file_name";
            continue;
        }
        
        if ($file_size > $max_size) {
            $errors[] = "El archivo $file_name es muy grande (máximo 5MB)";
            continue;
        }
        
        // Generar nombre único
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $nuevo_nombre = "variedad_" . $id_variedad . "_" . uniqid() . "." . $extension;
        $ruta_destino = $upload_dir . $nuevo_nombre;
        
        // Comprimir y redimensionar imagen
        $resultado_compresion = comprimirYRedimensionarImagen($file_tmp, $ruta_destino, $file_type);
        
        if ($resultado_compresion['success']) {
            // Guardar en base de datos
            $query = "INSERT INTO imagenes_variedades (id_variedad, nombre_archivo, fecha_subida) 
                      VALUES ($id_variedad, '$nuevo_nombre', NOW())";
            
            if (!mysqli_query($con, $query)) {
                $errors[] = "Error al guardar $file_name en base de datos: " . mysqli_error($con);
                // Eliminar archivo si no se pudo guardar en BD
                if (file_exists($ruta_destino)) {
                    unlink($ruta_destino);
                }
                $success = false;
            }
        } else {
            $errors[] = "Error al procesar $file_name: " . $resultado_compresion['error'];
            $success = false;
        }
    }
    
    return [
        'success' => $success && count($errors) === 0,
        'errors' => $errors
    ];
}

// Función auxiliar para eliminar imagen
function eliminarImagenVariedad($con, $id_imagen) {
    try {
        // Obtener información de la imagen
        $query = "SELECT nombre_archivo FROM imagenes_variedades WHERE id = $id_imagen";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $imagen = mysqli_fetch_assoc($result);
            $ruta_archivo = "uploads/variedades/" . $imagen['nombre_archivo'];
            
            // Eliminar archivo físico
            if (file_exists($ruta_archivo)) {
                unlink($ruta_archivo);
            }
            
            // Eliminar registro de base de datos
            $query = "DELETE FROM imagenes_variedades WHERE id = $id_imagen";
            if (mysqli_query($con, $query)) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => mysqli_error($con)];
            }
        } else {
            return ['success' => false, 'error' => 'Imagen no encontrada'];
        }
        
    } catch (\Throwable $th) {
        return ['success' => false, 'error' => $th->getMessage()];
    }
}

// Función para comprimir y redimensionar imágenes
function comprimirYRedimensionarImagen($archivo_origen, $ruta_destino, $tipo_mime) {
    try {
        $max_ancho = 600;
        $calidad_jpg = 85; // Calidad para JPEG (0-100)
        $calidad_png = 8;  // Nivel de compresión para PNG (0-9)
        
        // Obtener dimensiones originales
        $info_imagen = getimagesize($archivo_origen);
        if (!$info_imagen) {
            return ['success' => false, 'error' => 'No se pudo leer la imagen'];
        }
        
        $ancho_original = $info_imagen[0];
        $alto_original = $info_imagen[1];
        
        // Calcular nuevas dimensiones manteniendo proporción
        if ($ancho_original <= $max_ancho) {
            // Si la imagen ya es menor o igual al ancho máximo, solo comprimir
            $nuevo_ancho = $ancho_original;
            $nuevo_alto = $alto_original;
        } else {
            // Redimensionar manteniendo proporción
            $nuevo_ancho = $max_ancho;
            $nuevo_alto = round(($alto_original * $max_ancho) / $ancho_original);
        }
        
        // Crear imagen desde archivo según el tipo
        switch ($tipo_mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $imagen_origen = imagecreatefromjpeg($archivo_origen);
                break;
            case 'image/png':
                $imagen_origen = imagecreatefrompng($archivo_origen);
                break;
            case 'image/gif':
                $imagen_origen = imagecreatefromgif($archivo_origen);
                break;
            default:
                return ['success' => false, 'error' => 'Tipo de imagen no soportado'];
        }
        
        if (!$imagen_origen) {
            return ['success' => false, 'error' => 'No se pudo crear la imagen desde el archivo'];
        }
        
        // Crear nueva imagen con las dimensiones calculadas
        $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
        
        // Preservar transparencia para PNG y GIF
        if ($tipo_mime == 'image/png' || $tipo_mime == 'image/gif') {
            imagealphablending($imagen_nueva, false);
            imagesavealpha($imagen_nueva, true);
            $transparente = imagecolorallocatealpha($imagen_nueva, 255, 255, 255, 127);
            imagefill($imagen_nueva, 0, 0, $transparente);
        }
        
        // Redimensionar imagen
        imagecopyresampled(
            $imagen_nueva, 
            $imagen_origen, 
            0, 0, 0, 0, 
            $nuevo_ancho, 
            $nuevo_alto, 
            $ancho_original, 
            $alto_original
        );
        
        // Guardar imagen según el tipo
        $resultado = false;
        switch ($tipo_mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $resultado = imagejpeg($imagen_nueva, $ruta_destino, $calidad_jpg);
                break;
            case 'image/png':
                $resultado = imagepng($imagen_nueva, $ruta_destino, $calidad_png);
                break;
            case 'image/gif':
                $resultado = imagegif($imagen_nueva, $ruta_destino);
                break;
        }
        
        // Liberar memoria
        imagedestroy($imagen_origen);
        imagedestroy($imagen_nueva);
        
        if ($resultado) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'No se pudo guardar la imagen procesada'];
        }
        
    } catch (\Throwable $th) {
        return ['success' => false, 'error' => 'Error al procesar imagen: ' . $th->getMessage()];
    }
}