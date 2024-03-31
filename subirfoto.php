<?php
 include "./class_lib/sesionSecurity.php";

    error_reporting(0);

    try {
        if (isset($_FILES["file"])){
            $file = $_FILES["file"];
            $id_art = $_POST["id_artpedido"];
            
            $tipo = $file["type"];
            $ruta_provisional = $file["tmp_name"];
            $size = $file["size"];
            $dimensiones = getimagesize($ruta_provisional);
            $width = $dimensiones[0];
            $height = $dimensiones[1];
            $carpeta = "imagenes/";
            if ($tipo != "image/jpg" && $tipo != "image/jpeg" && $tipo != "image/png"){
                echo "error: el archivo no es una imagen";
            }
            else{
                $src = $carpeta.$id_art.".jpg";
                $imgData = resize_image($ruta_provisional, 300, 300);
            
                imagejpeg($imgData, $src, 50);
    
                chmod($src, 0666);
            }
        }
    } catch (\Throwable $th) {
        echo "error: $th";
    }

    function compressImage($source, $destination, $quality) { 
        // Get image info 
        $imgInfo = getimagesize($source); 
        $mime = $imgInfo['mime']; 
         
        // Create a new image from file 
        switch($mime){ 
            case 'image/jpeg': 
                $image = imagecreatefromjpeg($source); 
                break; 
            case 'image/png': 
                $image = imagecreatefrompng($source); 
                break; 
            case 'image/gif': 
                $image = imagecreatefromgif($source); 
                break; 
            default: 
                $image = imagecreatefromjpeg($source); 
        } 
         
        // Save image 
        imagejpeg($image, $destination, $quality); 
         
        // Return compressed image 
        return $destination; 
    } 

   function resize_image($file, $w, $h, $crop=false) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    
    
    //Get file extension
    $exploding = explode(".",$file);
    $ext = end($exploding);
    
    switch($ext){
        case "png":
            $src = imagecreatefrompng($file);
        break;
        case "jpeg":
        case "jpg":
            $src = imagecreatefromjpeg($file);
        break;
        case "gif":
            $src = imagecreatefromgif($file);
        break;
        default:
            $src = imagecreatefromjpeg($file);
        break;
    }
    
    $dst = imagecreatetruecolor($width*0.65, $height*0.65);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $width*0.65, $height*0.65, $width, $height);

    return $dst;
}
?>