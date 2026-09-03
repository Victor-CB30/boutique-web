CREATE TABLE IF NOT EXISTS clientes (
 id_cliente INT AUTO_INCREMENT PRIMARY KEY,
 nombre_cliente VARCHAR(150) NOT NULL,
 telefono_cliente VARCHAR(40) NOT NULL,
 email_cliente VARCHAR(150) NULL,
 direccion_cliente VARCHAR(255) NULL,
 estado_cliente TINYINT NOT NULL DEFAULT 1,
 fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uk_cliente_telefono (telefono_cliente),
 INDEX idx_cliente_nombre (nombre_cliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ejecutar sobre la base de datos boutique_genesis si se desea instalar manualmente.
CREATE TABLE IF NOT EXISTS pedidos (
 id_pedido INT AUTO_INCREMENT PRIMARY KEY,
 codigo_pedido VARCHAR(30) NOT NULL UNIQUE,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detalle_pedido (
 id_detalle INT AUTO_INCREMENT PRIMARY KEY,
 id_pedido INT NOT NULL,
 id_producto INT NOT NULL,
 nombre_producto VARCHAR(180) NOT NULL,
 talla VARCHAR(40) NULL,
 color VARCHAR(60) NULL,
 cantidad INT NOT NULL,
 precio_unitario DECIMAL(12,2) NOT NULL,
 subtotal DECIMAL(12,2) NOT NULL,
 CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
 INDEX idx_detalle_pedido (id_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tallas_producto (
 id_talla INT AUTO_INCREMENT PRIMARY KEY,
 id_producto INT NOT NULL,
 nombre_talla VARCHAR(40) NOT NULL,
 stock_talla INT NOT NULL DEFAULT 0,
 UNIQUE KEY uk_producto_talla (id_producto,nombre_talla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS colores_producto (
 id_color INT AUTO_INCREMENT PRIMARY KEY,
 id_producto INT NOT NULL,
 nombre_color VARCHAR(60) NOT NULL,
 codigo_hex VARCHAR(10) NULL,
 stock_color INT NOT NULL DEFAULT 0,
 UNIQUE KEY uk_producto_color (id_producto,nombre_color)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recuperacion_password_admin (
 id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
 id_admin INT NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 fecha_expiracion DATETIME NOT NULL,
 utilizado TINYINT(1) NOT NULL DEFAULT 0,
 fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_recuperacion_admin (id_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compatibilidad con instalaciones anteriores (v7)
ALTER TABLE clientes ADD COLUMN IF NOT EXISTS telefono_secundario VARCHAR(40) NULL AFTER telefono_cliente;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS codigo_pedido VARCHAR(30) NULL AFTER id_pedido;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS id_cliente INT NULL AFTER codigo_pedido;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS nombre_cliente VARCHAR(150) NOT NULL DEFAULT 'Cliente' AFTER id_cliente;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS email_cliente VARCHAR(150) NULL AFTER nombre_cliente;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS telefono_cliente VARCHAR(40) NOT NULL DEFAULT '' AFTER email_cliente;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS tipo_retiro VARCHAR(20) NOT NULL DEFAULT 'local';
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS direccion_entrega VARCHAR(255) NULL;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS metodo_pago VARCHAR(50) NOT NULL DEFAULT 'whatsapp';
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS notas TEXT NULL;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS total_pedido DECIMAL(12,2) NOT NULL DEFAULT 0;
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS estado_pedido VARCHAR(30) NOT NULL DEFAULT 'pendiente';
ALTER TABLE pedidos ADD COLUMN IF NOT EXISTS fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
UPDATE pedidos SET codigo_pedido=CONCAT('PED-',DATE_FORMAT(COALESCE(fecha_pedido,NOW()),'%Y%m%d'),'-',LPAD(id_pedido,6,'0')) WHERE codigo_pedido IS NULL OR codigo_pedido='';
