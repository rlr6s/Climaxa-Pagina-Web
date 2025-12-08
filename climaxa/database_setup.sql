-- CLIMAXA - Script de Base de Datos
-- Este archivo crea la base de datos y tabla para el sistema CLIMAXA

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS climaxa 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE climaxa;

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario de prueba
INSERT IGNORE INTO usuarios (nombre, correo, contrasena) 
VALUES (
    'Emanuel', 
    'ejemplo@gmail.com', 
    '$2y$10$hLGK8Dd6EpG7j0MZDC3BWezBQDrfbqPwOyZ1/zPwb.jRo94hPQUCu'
);

-- Mensaje de confirmación
SELECT 'Base de datos CLIMAXA configurada correctamente' AS Mensaje;

-- Tabla de productos (si aún no existe)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria ENUM('aires', 'freezers', 'neveras', 'servicios'),
    marca VARCHAR(100),
    especificaciones TEXT,
    imagen VARCHAR(255),
    stock INT DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla del carrito
CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    agregado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pedidos (para después)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'procesando', 'completado', 'cancelado') DEFAULT 'pendiente',
    direccion TEXT,
    telefono VARCHAR(20),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de items del pedido
CREATE TABLE IF NOT EXISTS pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar productos de CLIMAXA en la base de datos
INSERT INTO productos (nombre, descripcion, precio, categoria, marca, especificaciones, imagen) VALUES

-- Aires Acondicionados (categoria: 'aires')
('Aire Acondicionado Gree Inverter 12,000 BTU - 22 SEER WIFI', 
 'Aire acondicionado con tecnología inverter y control WiFi', 
 28900.00, 'aires', 'GREE', '108 BTU 228 SEER', 'producto1.png'),

('Aire Acondicionado AirMax 12,000 BTU Inverter 21 SEER WIFI', 
 'Aire acondicionado eficiente con control WiFi', 
 27200.00, 'aires', 'AirMax', '108 BTU 167 SEER', 'producto2.png'),

('Aire Acondicionado Gree Inverter 12,000 BTU - 22 SEER WIFI', 
 'Aire acondicionado con tecnología inverter', 
 28900.00, 'aires', 'GREE', '108 BTU 208 SEER', 'producto3.png'),

('Aire Acondicionado Samsung Wind-Free 12,000 BTU', 
 'Tecnología Wind-Free para un enfriamiento sin corrientes de aire', 
 31500.00, 'aires', 'SAMSUNG', 'Tecnología Wind-Free • 22 SEER • WiFi', 'producto4.png'),

('Aire Acondicionado LG Dual Inverter 18,000 BTU', 
 'Dual Inverter para mayor eficiencia energética', 
 35800.00, 'aires', 'LG', 'Dual Inverter • 24 SEER • Plasmaster', 'producto5.png'),

('Aire Acondicionado TCL Inverter 24,000 BTU', 
 'Aire acondicionado de alta capacidad con WiFi', 
 42300.00, 'aires', 'TCL', '24,000 BTU • Inverter • WiFi • 20 SEER', 'producto6.png'),

-- Freezers (categoria: 'freezers')
('Freezer Vertical Mabe 5.5 Pies Cúbicos Plateado', 
 'Freezer vertical con tecnología No Frost', 
 18500.00, 'freezers', 'MABE', 'Capacidad: 5.5 pies³ • Tecnología No Frost', 'freezer1.png'),

('Freezer Horizontal Indurama 7 Pies Cúbicos Blanco', 
 'Freezer horizontal para mayor capacidad', 
 22300.00, 'freezers', 'INDURAMA', 'Capacidad: 7 pies³ • Caja Fuerte Fría', 'freezer2.png'),

('Freezer Vertical LG 6.2 Pies Cúbicos Inverter', 
 'Freezer vertical con tecnología Inverter', 
 25800.00, 'freezers', 'LG', 'Capacidad: 6.2 pies³ • Linear Inverter • No Frost', 'freezer3.png'),

('Freezer Vertical Samsung 8 Pies Digital Inverter', 
 'Freezer vertical con Digital Inverter', 
 29900.00, 'freezers', 'SAMSUNG', 'Capacidad: 8 pies³ • Digital Inverter • No Frost', 'freezer4.png'),

('Freezer Vertical Whirlpool 6.5 Pies Cúbicos', 
 'Freezer vertical con control digital', 
 23700.00, 'freezers', 'WHIRLPOOL', 'Capacidad: 6.5 pies³ • Control Digital • No Frost', 'freezer5.png'),

('Freezer Horizontal Mabe 10 Pies Cúbicos', 
 'Freezer horizontal de gran capacidad', 
 27500.00, 'freezers', 'MABE', 'Capacidad: 10 pies³ • Caja Fuerte • Alta Eficiencia', 'freezer6.png'),

-- Neveras (categoria: 'neveras')
('Nevera Mabe 18 Pies Cúbicos French Door', 
 'Nevera francesa con dispensador de agua', 
 45900.00, 'neveras', 'MABE', 'Capacidad: 18 pies³ • Dispensador de Agua • No Frost', 'nevera1.png'),

('Nevera LG 20 Pies Cúbicos InstaView Door', 
 'Nevera con puerta InstaView', 
 62500.00, 'neveras', 'LG', 'Capacidad: 20 pies³ • Linear Inverter • InstaView', 'nevera2.png'),

('Nevera Samsung 19 Pies Family Hub', 
 'Nevera con Family Hub integrado', 
 58700.00, 'neveras', 'SAMSUNG', 'Capacidad: 19 pies³ • Digital Inverter • Family Hub', 'nevera3.png'),

('Nevera Whirlpool 16 Pies Cúbicos Side by Side', 
 'Nevera Side by Side', 
 38900.00, 'neveras', 'WHIRLPOOL', 'Capacidad: 16 pies³ • Dispensador de Hielo • No Frost', 'nevera4.png'),

('Nevera Indurama 14 Pies Cúbicos Dos Puertas', 
 'Nevera de dos puertas eficiente', 
 32500.00, 'neveras', 'INDURAMA', 'Capacidad: 14 pies³ • Eficiencia A+ • Congelador Superior', 'nevera5.png'),

('Nevera Haier 22 Pies French Door con Pantalla Táctil', 
 'Nevera francesa con pantalla táctil', 
 68900.00, 'neveras', 'HAIER', 'Capacidad: 22 pies³ • Pantalla Táctil • WiFi • No Frost', 'nevera6.png'),

-- Servicios (categoria: 'servicios')
('Instalación Profesional', 
 'Instalación profesional de aires acondicionados, freezers y neveras', 
 2500.00, 'servicios', 'CLIMAXA', 'Por técnicos certificados', 'servicio1.png'),

('Mantenimiento Preventivo', 
 'Limpieza y mantenimiento completo', 
 1800.00, 'servicios', 'CLIMAXA', 'Optimiza el rendimiento de tus equipos', 'servicio2.png'),

('Reparación de Emergencia', 
 'Servicio de reparación urgente las 24 horas', 
 3500.00, 'servicios', 'CLIMAXA', 'Para fallas críticas en tus equipos', 'servicio3.png');

 -- Agregar columna de rol a la tabla usuarios
ALTER TABLE usuarios ADD COLUMN rol ENUM('usuario', 'admin') DEFAULT 'usuario';

-- Marcar al menos un usuario como administrador (actualiza con tu correo)
UPDATE usuarios SET rol = 'admin' WHERE correo = 'admin@climaxa.com';

-- Crear tabla de pedidos si no existe (ya la creamos antes, pero la verificamos)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendiente', 'procesando', 'enviado', 'completado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50),
    direccion TEXT,
    telefono VARCHAR(20),
    notas TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de detalles del pedido si no existe
CREATE TABLE IF NOT EXISTS detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campos adicionales a usuarios
ALTER TABLE usuarios 
ADD COLUMN telefono VARCHAR(20) AFTER correo,
ADD COLUMN direccion TEXT AFTER telefono,
ADD COLUMN ciudad VARCHAR(100) AFTER direccion,
ADD COLUMN provincia VARCHAR(100) AFTER ciudad,
ADD COLUMN avatar VARCHAR(255) AFTER provincia,
ADD COLUMN actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Crear tabla de lista de deseos
CREATE TABLE IF NOT EXISTS lista_deseos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    agregado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deseo (usuario_id, producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de reseñas
CREATE TABLE IF NOT EXISTS reseñas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    puntuacion INT(1) NOT NULL CHECK (puntuacion >= 1 AND puntuacion <= 5),
    comentario TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reseña (usuario_id, producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asegurar que la tabla pedidos tenga todas las columnas necesarias
ALTER TABLE pedidos 
MODIFY COLUMN estado ENUM('pendiente', 'procesando', 'enviado', 'completado', 'cancelado') DEFAULT 'pendiente';

-- Agregar columnas faltantes si no existen
ALTER TABLE pedidos 
ADD COLUMN IF NOT EXISTS nombre_completo VARCHAR(255) AFTER usuario_id,
ADD COLUMN IF NOT EXISTS email VARCHAR(255) AFTER nombre_completo,
ADD COLUMN IF NOT EXISTS metodo_pago VARCHAR(50) AFTER telefono,
ADD COLUMN IF NOT EXISTS ciudad VARCHAR(100) AFTER direccion,
ADD COLUMN IF NOT EXISTS provincia VARCHAR(100) AFTER ciudad,
ADD COLUMN IF NOT EXISTS codigo_postal VARCHAR(20) AFTER provincia,
ADD COLUMN IF NOT EXISTS notas TEXT AFTER metodo_pago,
ADD COLUMN IF NOT EXISTS actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER creado_en;

ALTER TABLE usuarios 
ADD COLUMN reset_token VARCHAR(64) NULL DEFAULT NULL,
ADD COLUMN reset_expires DATETIME NULL DEFAULT NULL;