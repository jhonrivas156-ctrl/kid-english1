<?php
    $servidor = "localhost";
    $usuario  = "root";
    $password = "";
    $base_datos = "kids_english_db";

    $conexion = mysqli_connect($servidor, $usuario, $password, $base_datos);

    if (!$conexion) {
        die("Error al conectar: " . mysqli_connect_error());
    }

    mysqli_set_charset($conexion, "utf8mb4");
?>