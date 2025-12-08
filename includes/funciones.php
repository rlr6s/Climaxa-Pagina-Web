<?php
// includes/funciones.php

require_once("../config/database.php");

// Conexión global optimizada
$global_conn = null;

function getDBConnectionGlobal() {
    global $global_conn;
    if ($global_conn === null) {
        $db = new Database();
        $global_conn = $db->getConnection();
    }
    return $global_conn;
}

// FUNCIONES DEL CARRITO - COMPLETAS Y CORREGIDAS
function agregarAlCarrito($usuario_id, $producto_id, $cantidad = 1) {
    $conn = getDBConnectionGlobal();
    
    // Verificar si el producto ya está en el carrito
    $sql = "SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Error en preparar consulta agregarAlCarrito SELECT: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ii", $usuario_id, $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // El producto ya existe, obtener la cantidad actual
        $item = $result->fetch_assoc();
        $cantidad_actual = $item['cantidad'];
        
        // ACTUALIZAR: En lugar de sumar, mantenemos la cantidad existente
        // y solo aumentamos si el usuario específicamente quiere más
        // Por defecto, solo agregamos 1
        $nueva_cantidad = $cantidad_actual + 1; // Solo aumenta en 1
        
        $sql = "UPDATE carrito SET cantidad = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error en preparar consulta agregarAlCarrito UPDATE: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $nueva_cantidad, $item['id']);
        $success = $stmt->execute();
        
        // DEBUG
        error_log("DEBUG agregarAlCarrito UPDATE: usuario_id=$usuario_id, producto_id=$producto_id, cantidad_actual=$cantidad_actual, nueva_cantidad=$nueva_cantidad");
    } else {
        // Producto no existe, insertar nuevo con cantidad = 1
        $sql = "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error en preparar consulta agregarAlCarrito INSERT: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $usuario_id, $producto_id);
        $success = $stmt->execute();
        
        // DEBUG
        error_log("DEBUG agregarAlCarrito INSERT: usuario_id=$usuario_id, producto_id=$producto_id, cantidad=1");
    }
    
    if ($stmt) {
        $stmt->close();
    }
    
    return $success ?? false;
}

function obtenerCarrito($usuario_id) {
    $conn = getDBConnectionGlobal();
    
    $sql = "SELECT c.id as carrito_id, c.cantidad, p.id, p.nombre, p.precio, p.imagen, p.marca, p.especificaciones 
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.usuario_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta obtenerCarrito: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $stmt->close();
    
    return $items;
}

function obtenerTotalCarrito($usuario_id) {
    $items = obtenerCarrito($usuario_id);
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    
    return $total;
}

function contarItemsCarrito($usuario_id) {
    $conn = getDBConnectionGlobal();
    
    $sql = "SELECT SUM(cantidad) as total_items FROM carrito WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Error en preparar consulta contarItemsCarrito: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $row = $result->fetch_assoc();
    $total = $row['total_items'] ?? 0;
    
    $stmt->close();
    
    // DEBUG: Agrega este log para verificar
    error_log("DEBUG contarItemsCarrito: usuario_id=$usuario_id, total=$total");
    
    return (int)$total;
}

function eliminarDelCarrito($carrito_id, $usuario_id) {
    $conn = getDBConnectionGlobal();
    
    $sql = "DELETE FROM carrito WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta eliminarDelCarrito: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("ii", $carrito_id, $usuario_id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

function actualizarCantidadCarrito($carrito_id, $usuario_id, $cantidad) {
    $conn = getDBConnectionGlobal();
    
    if ($cantidad <= 0) {
        // Si la cantidad es 0 o negativa, eliminar el item
        return eliminarDelCarrito($carrito_id, $usuario_id);
    }
    
    $sql = "UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta actualizarCantidadCarrito: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("iii", $cantidad, $carrito_id, $usuario_id);
    $success = $stmt->execute();
    $stmt->close();
    
    return $success;
}

// FUNCIONES DE PRODUCTOS
function obtenerProductosPorCategoria($categoria, $limite = null) {
    $conn = getDBConnectionGlobal();
    
    $sql = "SELECT * FROM productos WHERE categoria = ?";
    if ($limite) {
        $sql .= " LIMIT ?";
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta obtenerProductosPorCategoria: " . $conn->error);
        return [];
    }
    
    if ($limite) {
        $stmt->bind_param("si", $categoria, $limite);
    } else {
        $stmt->bind_param("s", $categoria);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    $stmt->close();
    
    return $productos;
}

function renderProductoDB($producto) {
    ob_start(); ?>
    <div class="product-card">
        <div class="product-image">
            <?php
            $image_path = "../assets/img/" . $producto['imagen'];
            if (!empty($producto['imagen']) && file_exists($image_path)) {
                echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($producto['nombre']) . '">';
            } else {
                echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 14px;">';
                echo '<i class="fas fa-box" style="font-size: 40px; margin-bottom: 10px; display: block; width: 100%; text-align: center;"></i>';
                echo '</div>';
            }
            ?>
        </div>
        <div class="product-info">
            <div class="product-brand"><?php echo htmlspecialchars($producto['marca']); ?></div>
            <h3 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h3>
            <div class="product-specs"><?php echo htmlspecialchars($producto['especificaciones']); ?></div>
            <div class="product-price">RD$<?php echo number_format($producto['precio'], 2); ?></div>
            <button class="add-to-cart" data-product-id="<?php echo $producto['id']; ?>">
                Agregar al carrito
            </button>
        </div>
    </div>
    <?php 
    return ob_get_clean();
}

// FUNCIONES PARA PERFIL DE USUARIO

function obtenerPedidosUsuario($usuario_id, $limite = null) {
    $conn = getDBConnectionGlobal();
    
    $sql = "SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY creado_en DESC";
    if ($limite) {
        $sql .= " LIMIT ?";
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta obtenerPedidosUsuario: " . $conn->error);
        return [];
    }
    
    if ($limite) {
        $stmt->bind_param("ii", $usuario_id, $limite);
    } else {
        $stmt->bind_param("i", $usuario_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
    
    $stmt->close();
    return $pedidos;
}

function obtenerListaDeseos($usuario_id) {
    $conn = getDBConnectionGlobal();
    
    $sql = "SELECT p.* FROM lista_deseos ld 
            JOIN productos p ON ld.producto_id = p.id 
            WHERE ld.usuario_id = ? 
            ORDER BY ld.agregado_en DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta obtenerListaDeseos: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $deseos = [];
    while ($row = $result->fetch_assoc()) {
        $deseos[] = $row;
    }
    
    $stmt->close();
    return $deseos;
}

function agregarListaDeseos($usuario_id, $producto_id) {
    $conn = getDBConnectionGlobal();
    
    // Verificar si ya existe
    $sql_check = "SELECT id FROM lista_deseos WHERE usuario_id = ? AND producto_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $usuario_id, $producto_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $stmt_check->close();
        return false; // Ya existe
    }
    $stmt_check->close();
    
    // Insertar nuevo
    $sql = "INSERT INTO lista_deseos (usuario_id, producto_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $producto_id);
    $success = $stmt->execute();
    
    $stmt->close();
    return $success;
}

function eliminarListaDeseos($usuario_id, $producto_id) {
    $conn = getDBConnectionGlobal();
    
    $sql = "DELETE FROM lista_deseos WHERE usuario_id = ? AND producto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $producto_id);
    $success = $stmt->execute();
    
    $stmt->close();
    return $success;
}

function redimensionarImagen($origen, $destino, $ancho_nuevo, $alto_nuevo) {
    list($ancho, $alto, $tipo) = getimagesize($origen);
    
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagen = imagecreatefromjpeg($origen);
            break;
        case IMAGETYPE_PNG:
            $imagen = imagecreatefrompng($origen);
            break;
        case IMAGETYPE_GIF:
            $imagen = imagecreatefromgif($origen);
            break;
        default:
            return false;
    }
    
    $nueva_imagen = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);
    
    // Mantener transparencia para PNG y GIF
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
        imagecolortransparent($nueva_imagen, imagecolorallocatealpha($nueva_imagen, 0, 0, 0, 127));
        imagealphablending($nueva_imagen, false);
        imagesavealpha($nueva_imagen, true);
    }
    
    imagecopyresampled($nueva_imagen, $imagen, 0, 0, 0, 0, 
                       $ancho_nuevo, $alto_nuevo, $ancho, $alto);
    
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($nueva_imagen, $destino, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($nueva_imagen, $destino, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($nueva_imagen, $destino);
            break;
    }
    
    imagedestroy($imagen);
    imagedestroy($nueva_imagen);
    
    return true;
}

// FUNCIÓN PARA OBTENER DETALLE DE UN PEDIDO ESPECÍFICO
function obtenerDetallePedido($pedido_id) {
    $conn = getDBConnectionGlobal();
    
    $sql = "SELECT dp.*, p.nombre, p.marca, p.imagen 
            FROM detalle_pedido dp 
            JOIN productos p ON dp.producto_id = p.id 
            WHERE dp.pedido_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error en preparar consulta obtenerDetallePedido: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $detalles = [];
    while ($row = $result->fetch_assoc()) {
        $detalles[] = $row;
    }
    
    $stmt->close();
    return $detalles;
}

// FUNCIÓN PARA REORDENAR PRODUCTOS DE UN PEDIDO ANTERIOR
function reordenarPedido($usuario_id, $pedido_id) {
    $conn = getDBConnectionGlobal();
    
    // Obtener detalles del pedido
    $sql = "SELECT producto_id, cantidad FROM detalle_pedido WHERE pedido_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $success_count = 0;
    
    while ($item = $result->fetch_assoc()) {
        // Verificar si el producto aún existe
        $sql_check = "SELECT id FROM productos WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $item['producto_id']);
        $stmt_check->execute();
        $check_result = $stmt_check->get_result();
        
        if ($check_result->num_rows > 0) {
            // Agregar al carrito
            if (agregarAlCarrito($usuario_id, $item['producto_id'], $item['cantidad'])) {
                $success_count++;
            }
        }
        $stmt_check->close();
    }
    
    $stmt->close();
    return $success_count > 0;
}

?>