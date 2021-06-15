<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$msg = "";


if(file_exists("archivo.txt")){ //Existe el archivo?

    // Leer el archivo y asigno variable $jsonCliente
    $jsonCliente = file_get_contents("archivo.txt");

    // Convertir json a un array $aClientes
    $aClientes= json_decode($jsonCliente, true);
} else {
    $aClientes = array();
}

$id = isset($_REQUEST["id"]) &&  $_REQUEST["id"] >= 0? $_REQUEST["id"] : ""; //Comprueba si query string id existe

if($_POST){
    $dni = $_REQUEST["txtDni"];
    $nombre = strtoupper($_REQUEST["txtNombre"]);
    $telefono = $_REQUEST["txtTelefono"];
    $correo = $_REQUEST["txtCorreo"];

    //Guardo la imagen
    if($_FILES["archivo"]["error"] === UPLOAD_ERR_OK) { // Se subio archivo?
        $nombreAleatorio = date("Ymdhmsi"); //Si se crea 09/06/2021 a las 08:21 20210609082100
        $archivo_tmp = $_FILES["archivo"]["tmp_name"];  //Carpeta temporal
        $nombreArchivo = $_FILES["archivo"]["name"]; 
        $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
        $nuevoNombre = "$nombreAleatorio.$extension";
        move_uploaded_file($archivo_tmp, "imagenes/$nuevoNombre"); // Guarda el archivo fisicamente desde carpeta temporal a donde quiero guardarlo
    } 

    if($id != "" ){ //Estoy actualizando o editando uno existente
        // Si no se subio la imagen
        if($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) { // Si no se subió la imagen:
            $nuevoNombre = $aClientes[$id]["imagen"]; // Mantengo el nombre actual que ya existía en la imagen
        } else {
            unlink("imagenes/". $aClientes[$id]["imagen"]); // Si viene la imagen, elimino la imagen anterior y guardo el nombre de la nueva imagen
        }
        $aClientes[$id] = array("dni" => $dni,
                                "nombre" => $nombre, 
                                "telefono" => $telefono, 
                                "correo" => $correo,
                                "imagen" => $nuevoNombre );                          
        $msg = "<div class='alert alert-warning mt-3 text-center' role='alert'>Cliente actualizado satisfactoriamente </div>";
    } else { // Inserta nuevo cliente    
        $msg = "<div class='alert alert-success mt-3 text-center' role='alert'>Cliente agregado satisfactoriamente </div>";       
        //Crear un array de datos
        $aClientes[] = array("dni" => $dni,
                            "nombre" => $nombre, 
                            "telefono" => $telefono, 
                            "correo" => $correo,
                            "imagen" => $nuevoNombre
                        );                         
    }

    //Convertir el array en json
    $jsonCliente = json_encode($aClientes);
    
    //Guardar json en un archivo llamado archivo.txt
    file_put_contents( "archivo.txt", $jsonCliente);   
}

if($id != "" && isset($_REQUEST["do"]) && $_REQUEST["do"] == "eliminar"){// Lee el query string y si el DO es igual a eliminar y si existe "do":
    // Elimino la imagen 
    unlink("imagenes/". $aClientes[$id]["imagen"]);
    //Elimino el cliente: 
    unset($aClientes[$id]);
    //Actualizar el archivo con el nuevo array de aClientes
    //Convertir el array en json
    $jsonCliente = json_encode($aClientes);
    //Guardar json en un archivo llamado archivo.txt
    file_put_contents( "archivo.txt", $jsonCliente);
    header("Location: index.php");

} 

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="../css/fontawesome/css/all.min.css"> <!-- Utilizar para fontawasome-->
    <link rel="stylesheet" href="../css/fontawesome/css/fontawesome.min.css"> <!-- Utilizar para fontawasome-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <title>ABM Clientes</title>
    
</head>
<body>
    <main>
        <div class="container mt-5">
            <div class="row">
                <div class="col-12 text-center">
                    <h1>Registro de Clientes</h1>
                </div>
            </div>
            <form action="" method="POST" enctype="multipart/form-data"><!-- Formulario -->
                <div class="row mt-4">
                    <div class="col-md-5 col-12 pe-5">
                        <div>
                            <label for="">DNI: *</label>
                            <input  type="text" name="txtDni" id="txtDNI" class="form-control" requiered value ="<?php echo isset($aClientes[$id])? $aClientes[$id]["dni"] : "";?>"> <!--Determina si hay un query string $id -->                     
                        </div>
                        <div>
                            <label for="">Nombre: *</label>
                            <input type="text" name="txtNombre" id="txtNombre" class="form-control" requiered value ="<?php echo isset($aClientes[$id])? $aClientes[$id]["nombre"] : "";?>" >                          
                        </div>
                        <div>
                            <label for="">Teléfono: *</label>
                            <input type="tel" name="txtTelefono" id="txtTel" class="form-control" requiered value ="<?php echo isset($aClientes[$id])? $aClientes[$id]["telefono"] : "";?>" >                        
                        </div>
                        <div>
                            <label for="">Correo: *</label>
                            <input type="mail" name="txtCorreo" id="txtCorreo" class="form-control" requiered value ="<?php echo isset($aClientes[$id])? $aClientes[$id]["correo"] : "";?>" >                         
                        </div>
                        <div class="col-12 pt-2"> Archivo Adjunto: 
                            <input type="file" name="archivo" id="archivo" accept=".jpg, .jpeg, .png" requiered >
                            <p style="font-size: 13px">Archivos admitidos: .jpg, .jpeg, .png</p>
                        </div>                        
                        <div>
                            <button class="btn btn-primary" type="submit">Guardar</button>  <!-- Botón Guardar -->
                        </div>
                        <?php echo $msg; ?>
                    </div>
                    <div class="col-md-7 col-12">
                        <table class="table table-hover table-bordered"> <!-- Tabla -->
                            <thead>
                                <th>Imagen</th>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </thead>
                            <tbody>
                                <?php
                                foreach($aClientes as $pos => $cliente){?>
                                    <tr>
                                        <td class="text-center"><img class="img-thumbnail" style="height:70px;" src="imagenes/<?php echo $cliente["imagen"]; ?>"> </td>
                                        <td><?php echo $cliente["dni"]; ?></td> 
                                        <td><?php echo $cliente["nombre"]; ?></td> 
                                        <td><?php echo $cliente["telefono"]; ?></td> 
                                        <td><?php echo $cliente["correo"]; ?></td> 
                                        <td style="width: 110px;">
                                        <a href="index.php?id=<?php echo $pos; ?>"><i class="fas fa-edit"></i></a>
                                        <a href="index.php?id=<?php echo $pos; ?>&do=eliminar"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php  } ?>
                            </tbody>
                        </table>
                    </div>                
                </div>
            </form>
        </div>
        <a href="index.php"><i class="fas fa-plus"></i></a>
    </main>

</body>
</html>
