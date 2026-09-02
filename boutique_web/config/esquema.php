<?php
function columnaExisteBoutique(PDO $conexion, string $tabla, string $columna): bool
{
    $stmt = $conexion->prepare("SHOW COLUMNS FROM `{$tabla}` LIKE :columna");
    $stmt->execute(['columna' => $columna]);
    return (bool)$stmt->fetch();
}

function indiceExisteBoutique(PDO $conexion, string $tabla, string $indice): bool
{
    $stmt = $conexion->prepare("SHOW INDEX FROM `{$tabla}` WHERE Key_name = :indice");
    $stmt->execute(['indice' => $indice]);
    return (bool)$stmt->fetch();
}

function agregarColumnaBoutique(PDO $conexion, string $tabla, string $columna, string $definicion): void
{
    if (!columnaExisteBoutique($conexion, $tabla, $columna)) {
        $conexion->exec("ALTER TABLE `{$tabla}` ADD COLUMN `{$columna}` {$definicion}");
    }
}

function asegurarEsquemaBoutique(PDO $conexion): void
{
    $conexion->exec("CREATE TABLE IF NOT EXISTS categorias (
        id_categoria INT AUTO_INCREMENT PRIMARY KEY,
        nombre_categoria VARCHAR(120) NOT NULL,
        descripcion_categoria TEXT NULL,
        estado_categoria TINYINT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    agregarColumnaBoutique($conexion, 'categorias', 'descripcion_categoria', "TEXT NULL");
    agregarColumnaBoutique($conexion, 'categorias', 'estado_categoria', "TINYINT NOT NULL DEFAULT 1");

    $conexion->exec("CREATE TABLE IF NOT EXISTS marcas (
        id_marca INT AUTO_INCREMENT PRIMARY KEY,
        nombre_marca VARCHAR(120) NOT NULL,
        descripcion_marca TEXT NULL,
        estado_marca TINYINT NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    agregarColumnaBoutique($conexion, 'marcas', 'descripcion_marca', "TEXT NULL");
    agregarColumnaBoutique($conexion, 'marcas', 'estado_marca', "TINYINT NOT NULL DEFAULT 1");
    $conexion->exec("CREATE TABLE IF NOT EXISTS clientes (
        id_cliente INT AUTO_INCREMENT PRIMARY KEY,
        nombre_cliente VARCHAR(150) NOT NULL,
        telefono_cliente VARCHAR(40) NOT NULL,
        telefono_secundario VARCHAR(40) NULL,
        email_cliente VARCHAR(150) NULL,
        direccion_cliente VARCHAR(255) NULL,
        estado_cliente TINYINT NOT NULL DEFAULT 1,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uk_cliente_telefono (telefono_cliente),
        INDEX idx_cliente_nombre (nombre_cliente)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    agregarColumnaBoutique($conexion, 'clientes', 'telefono_secundario', "VARCHAR(40) NULL AFTER telefono_cliente");
    agregarColumnaBoutique($conexion, 'clientes', 'email_cliente', "VARCHAR(150) NULL AFTER telefono_secundario");
    agregarColumnaBoutique($conexion, 'clientes', 'direccion_cliente', "VARCHAR(255) NULL AFTER email_cliente");
    agregarColumnaBoutique($conexion, 'clientes', 'estado_cliente', "TINYINT NOT NULL DEFAULT 1");
    agregarColumnaBoutique($conexion, 'clientes', 'fecha_registro', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    $conexion->exec("CREATE TABLE IF NOT EXISTS pedidos (
        id_pedido INT AUTO_INCREMENT PRIMARY KEY,
        codigo_pedido VARCHAR(30) NOT NULL UNIQUE,
        id_cliente INT NULL,
        nombre_cliente VARCHAR(150) NOT NULL,
        email_cliente VARCHAR(150) NULL,
        telefono_cliente VARCHAR(40) NOT NULL,
        tipo_retiro ENUM('delivery','local') NOT NULL DEFAULT 'local',
        direccion_entrega VARCHAR(255) NULL,
        metodo_pago VARCHAR(50) NOT NULL DEFAULT 'whatsapp',
        notas TEXT NULL,
        total_pedido DECIMAL(12,2) NOT NULL DEFAULT 0,
        estado_pedido VARCHAR(30) NOT NULL DEFAULT 'pendiente',
        fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    agregarColumnaBoutique($conexion, 'pedidos', 'codigo_pedido', "VARCHAR(30) NULL AFTER id_pedido");
    agregarColumnaBoutique($conexion, 'pedidos', 'id_cliente', "INT NULL AFTER codigo_pedido");
    agregarColumnaBoutique($conexion, 'pedidos', 'nombre_cliente', "VARCHAR(150) NOT NULL DEFAULT 'Cliente' AFTER id_cliente");
    agregarColumnaBoutique($conexion, 'pedidos', 'email_cliente', "VARCHAR(150) NULL AFTER nombre_cliente");
    agregarColumnaBoutique($conexion, 'pedidos', 'telefono_cliente', "VARCHAR(40) NOT NULL DEFAULT '' AFTER email_cliente");
    agregarColumnaBoutique($conexion, 'pedidos', 'tipo_retiro', "VARCHAR(20) NOT NULL DEFAULT 'local'");
    agregarColumnaBoutique($conexion, 'pedidos', 'direccion_entrega', "VARCHAR(255) NULL");
    agregarColumnaBoutique($conexion, 'pedidos', 'metodo_pago', "VARCHAR(50) NOT NULL DEFAULT 'whatsapp'");
    agregarColumnaBoutique($conexion, 'pedidos', 'notas', "TEXT NULL");
    agregarColumnaBoutique($conexion, 'pedidos', 'total_pedido', "DECIMAL(12,2) NOT NULL DEFAULT 0");
    agregarColumnaBoutique($conexion, 'pedidos', 'estado_pedido', "VARCHAR(30) NOT NULL DEFAULT 'pendiente'");
    agregarColumnaBoutique($conexion, 'pedidos', 'fecha_pedido', "TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

    // Completa códigos faltantes en instalaciones antiguas antes de crear el índice único.
    $conexion->exec("UPDATE pedidos SET codigo_pedido = CONCAT('PED-', DATE_FORMAT(COALESCE(fecha_pedido,NOW()), '%Y%m%d'), '-', LPAD(id_pedido, 6, '0')) WHERE codigo_pedido IS NULL OR codigo_pedido = ''");
    if (!indiceExisteBoutique($conexion, 'pedidos', 'uk_pedidos_codigo')) {
        try { $conexion->exec("ALTER TABLE pedidos ADD UNIQUE KEY uk_pedidos_codigo (codigo_pedido)"); } catch (Throwable $e) {}
    }

    $conexion->exec("CREATE TABLE IF NOT EXISTS detalle_pedido (
        id_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_pedido INT NOT NULL,
        id_producto INT NOT NULL,
        nombre_producto VARCHAR(180) NOT NULL,
        talla VARCHAR(40) NULL,
        color VARCHAR(60) NULL,
        cantidad INT NOT NULL,
        precio_unitario DECIMAL(12,2) NOT NULL,
        subtotal DECIMAL(12,2) NOT NULL,
        INDEX idx_detalle_pedido (id_pedido),
        INDEX idx_detalle_producto (id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'id_pedido', "INT NOT NULL DEFAULT 0 AFTER id_detalle");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'id_producto', "INT NOT NULL DEFAULT 0 AFTER id_pedido");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'nombre_producto', "VARCHAR(180) NOT NULL DEFAULT 'Producto'");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'talla', "VARCHAR(40) NULL");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'color', "VARCHAR(60) NULL");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'cantidad', "INT NOT NULL DEFAULT 1");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'precio_unitario', "DECIMAL(12,2) NOT NULL DEFAULT 0");
    agregarColumnaBoutique($conexion, 'detalle_pedido', 'subtotal', "DECIMAL(12,2) NOT NULL DEFAULT 0");

    $conexion->exec("CREATE TABLE IF NOT EXISTS tallas_producto (
        id_talla INT AUTO_INCREMENT PRIMARY KEY,
        id_producto INT NOT NULL,
        nombre_talla VARCHAR(40) NOT NULL,
        stock_talla INT NOT NULL DEFAULT 0,
        UNIQUE KEY uk_producto_talla (id_producto, nombre_talla),
        INDEX idx_talla_producto (id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $conexion->exec("CREATE TABLE IF NOT EXISTS colores_producto (
        id_color INT AUTO_INCREMENT PRIMARY KEY,
        id_producto INT NOT NULL,
        nombre_color VARCHAR(60) NOT NULL,
        codigo_hex VARCHAR(10) NULL,
        stock_color INT NOT NULL DEFAULT 0,
        UNIQUE KEY uk_producto_color (id_producto, nombre_color),
        INDEX idx_color_producto (id_producto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
