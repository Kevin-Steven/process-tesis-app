<?php 
    $host = 'localhost';
    $usuario = 'root';
    $clave = '';
    $baseDeDatos = 'procesotitulacion';
    $puerto = '3308';

    $conn = new mysqli($host, $usuario ,$clave, $baseDeDatos, $puerto);

    if($conn -> connect_error){
        die("Error de conexión: " . $conn -> connect_error);
    }
?>