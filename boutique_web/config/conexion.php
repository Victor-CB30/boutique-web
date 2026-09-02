<?php
$host = 'localhost';
$base_datos = 'boutique_genesis';
$usuario = 'root';
$contrasena = '';

try {
    $conexion = new PDO(
        "mysql:host={$host};dbname={$base_datos};charset=utf8mb4",
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Error de conexión con la base de datos: ' . $e->getMessage());
}

