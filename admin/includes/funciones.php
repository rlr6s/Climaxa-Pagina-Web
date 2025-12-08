<?php
// admin/includes/funciones.php - CORREGIDO

require_once(__DIR__ . "/../../config/database.php");

// Verificar si el usuario es administrador
function esAdministrador() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return false;
    }
    
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
        return false;
    }
    
    return true;
}

// Redirigir si no es administrador
function requerirAdmin() {
    if (!esAdministrador()) {
        header("Location: " . (strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'login.php' : '../admin/login.php'));
        exit;
    }
}

// Función para obtener conexión sin cerrarla prematuramente
function getAdminDBConnection() {
    static $conn = null;
    if ($conn === null || !$conn->ping()) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    return $conn;
}

// Obtener estadísticas para el dashboard
function obtenerEstadisticas() {
    $conn = getAdminDBConnection();
    
    $estadisticas = [];
    
    // Total de productos
    $sql = "SELECT COUNT(*) as total FROM productos";
    $result = $conn->query($sql);
    $estadisticas['total_productos'] = $result->fetch_assoc()['total'];
    
    // Total de usuarios
    $sql = "SELECT COUNT(*) as total FROM usuarios";
    $result = $conn->query($sql);
    $estadisticas['total_usuarios'] = $result->fetch_assoc()['total'];
    
    // Total de pedidos
    $sql = "SELECT COUNT(*) as total FROM pedidos";
    $result = $conn->query($sql);
    $estadisticas['total_pedidos'] = $result->fetch_assoc()['total'];
    
    // Total de ventas
    $sql = "SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado'";
    $result = $conn->query($sql);
    $estadisticas['total_ventas'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Ventas del mes actual
    $sql = "SELECT SUM(total) as total FROM pedidos 
            WHERE estado = 'completado' 
            AND MONTH(creado_en) = MONTH(CURRENT_DATE()) 
            AND YEAR(creado_en) = YEAR(CURRENT_DATE())";
    $result = $conn->query($sql);
    $estadisticas['ventas_mes_actual'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Pedidos pendientes
    $sql = "SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'";
    $result = $conn->query($sql);
    $estadisticas['pedidos_pendientes'] = $result->fetch_assoc()['total'];
    
    // Productos con bajo stock (< 5 unidades)
    $sql = "SELECT COUNT(*) as total FROM productos WHERE stock < 5";
    $result = $conn->query($sql);
    $estadisticas['productos_bajo_stock'] = $result->fetch_assoc()['total'];
    
    // Nuevos usuarios este mes
    $sql = "SELECT COUNT(*) as total FROM usuarios 
            WHERE MONTH(creado_en) = MONTH(CURRENT_DATE()) 
            AND YEAR(creado_en) = YEAR(CURRENT_DATE())";
    $result = $conn->query($sql);
    $estadisticas['nuevos_usuarios_mes'] = $result->fetch_assoc()['total'];
    
    return $estadisticas;
}

// Obtener pedidos recientes
function obtenerPedidosRecientes($limite = 10) {
    $conn = getAdminDBConnection();
    
    $sql = "SELECT p.*, u.nombre as cliente_nombre 
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.creado_en DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
    
    $stmt->close();
    
    return $pedidos;
}

// Obtener todos los usuarios
function obtenerUsuarios() {
    $conn = getAdminDBConnection();
    
    $sql = "SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY creado_en DESC";
    $result = $conn->query($sql);
    
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    
    return $usuarios;
}

// Cambiar rol de usuario
function cambiarRolUsuario($usuario_id, $nuevo_rol) {
    $conn = getAdminDBConnection();
    
    $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_rol, $usuario_id);
    $success = $stmt->execute();
    
    $stmt->close();
    
    return $success;
}

// Eliminar usuario
function eliminarUsuario($usuario_id) {
    $conn = getAdminDBConnection();
    
    // No permitir eliminar al propio administrador
    if (isset($_SESSION['usuario_id']) && $usuario_id == $_SESSION['usuario_id']) {
        return false;
    }
    
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $success = $stmt->execute();
    
    $stmt->close();
    
    return $success;
}

// Obtener todos los productos con paginación
function obtenerProductosAdmin($pagina = 1, $por_pagina = 20) {
    $conn = getAdminDBConnection();
    
    $offset = ($pagina - 1) * $por_pagina;
    
    $sql = "SELECT * FROM productos ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $por_pagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    // Obtener total de productos para paginación
    $sql_total = "SELECT COUNT(*) as total FROM productos";
    $result_total = $conn->query($sql_total);
    $total = $result_total->fetch_assoc()['total'];
    
    $stmt->close();
    
    return [
        'productos' => $productos,
        'total' => $total,
        'pagina_actual' => $pagina,
        'total_paginas' => ceil($total / $por_pagina)
    ];
}

// Crear nuevo producto
function crearProducto($datos) {
    $conn = getAdminDBConnection();
    
    $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria, marca, especificaciones, imagen, stock) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssdssssi",
        $datos['nombre'],
        $datos['descripcion'],
        $datos['precio'],
        $datos['categoria'],
        $datos['marca'],
        $datos['especificaciones'],
        $datos['imagen'],
        $datos['stock']
    );
    
    $success = $stmt->execute();
    $producto_id = $stmt->insert_id;
    
    $stmt->close();
    
    return $success ? $producto_id : false;
}

// Actualizar producto
function actualizarProducto($id, $datos) {
    $conn = getAdminDBConnection();
    
    $sql = "UPDATE productos SET 
            nombre = ?,
            descripcion = ?,
            precio = ?,
            categoria = ?,
            marca = ?,
            especificaciones = ?,
            imagen = ?,
            stock = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssdssssii",
        $datos['nombre'],
        $datos['descripcion'],
        $datos['precio'],
        $datos['categoria'],
        $datos['marca'],
        $datos['especificaciones'],
        $datos['imagen'],
        $datos['stock'],
        $id
    );
    
    $success = $stmt->execute();
    
    $stmt->close();
    
    return $success;
}

// Eliminar producto
function eliminarProducto($id) {
    $conn = getAdminDBConnection();
    
    // Primero verificar si el producto está en algún pedido
    $sql_check = "SELECT COUNT(*) as total FROM detalle_pedido WHERE producto_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        // No eliminar, solo marcar como inactivo o cambiar nombre
        return false;
    }
    
    // Eliminar producto
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    
    $stmt->close();
    
    return $success;
}

// Obtener ventas por mes
function obtenerVentasPorMes($anio = null) {
    if ($anio === null) {
        $anio = date('Y');
    }
    
    $conn = getAdminDBConnection();
    
    $sql = "SELECT 
                MONTH(creado_en) as mes,
                COUNT(*) as total_pedidos,
                SUM(total) as total_ventas
            FROM pedidos 
            WHERE estado = 'completado' 
            AND YEAR(creado_en) = ?
            GROUP BY MONTH(creado_en)
            ORDER BY mes";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $anio);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ventas = [];
    for ($i = 1; $i <= 12; $i++) {
        $ventas[$i] = [
            'mes' => $i,
            'total_pedidos' => 0,
            'total_ventas' => 0
        ];
    }
    
    while ($row = $result->fetch_assoc()) {
        $ventas[$row['mes']] = $row;
    }
    
    $stmt->close();
    
    return $ventas;
}

// Obtener productos más vendidos
function obtenerProductosMasVendidos($limite = 10) {
    $conn = getAdminDBConnection();
    
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.marca,
                p.imagen,
                SUM(dp.cantidad) as total_vendido,
                SUM(dp.cantidad * dp.precio_unitario) as ingresos_totales
            FROM detalle_pedido dp
            JOIN productos p ON dp.producto_id = p.id
            JOIN pedidos ped ON dp.pedido_id = ped.id
            WHERE ped.estado = 'completado'
            GROUP BY p.id
            ORDER BY total_vendido DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    $stmt->close();
    
    return $productos;
}

// Obtener todos los pedidos
function obtenerPedidos($pagina = 1, $por_pagina = 20) {
    $conn = getAdminDBConnection();
    
    $offset = ($pagina - 1) * $por_pagina;
    
    $sql = "SELECT p.*, u.nombre as cliente_nombre, u.correo as cliente_correo
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.creado_en DESC 
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $por_pagina, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = $row;
    }
    
    // Obtener total de pedidos para paginación
    $sql_total = "SELECT COUNT(*) as total FROM pedidos";
    $result_total = $conn->query($sql_total);
    $total = $result_total->fetch_assoc()['total'];
    
    $stmt->close();
    
    return [
        'pedidos' => $pedidos,
        'total' => $total,
        'pagina_actual' => $pagina,
        'total_paginas' => ceil($total / $por_pagina)
    ];
}

// Obtener detalles de un pedido
function obtenerDetallePedido($pedido_id) {
    $conn = getAdminDBConnection();
    
    $sql = "SELECT dp.*, p.nombre, p.marca, p.imagen
            FROM detalle_pedido dp
            JOIN productos p ON dp.producto_id = p.id
            WHERE dp.pedido_id = ?";
    
    $stmt = $conn->prepare($sql);
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

// Actualizar estado del pedido
function actualizarEstadoPedido($pedido_id, $estado) {
    $conn = getAdminDBConnection();
    
    $sql = "UPDATE pedidos SET estado = ?, actualizado_en = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $estado, $pedido_id);
    $success = $stmt->execute();
    
    $stmt->close();
    
    return $success;
}

// Obtener información de un pedido específico
function obtenerPedidoPorId($pedido_id) {
    $conn = getAdminDBConnection();
    
    $sql = "SELECT p.*, u.nombre as cliente_nombre, u.correo as cliente_correo 
            FROM pedidos p 
            JOIN usuarios u ON p.usuario_id = u.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido = $result->fetch_assoc();
    
    $stmt->close();
    
    return $pedido;
}

// Función para limpiar conexiones al final del script
function cerrarConexiones() {
    // La conexión se cerrará automáticamente al final del script
    // No es necesario hacer nada aquí
}
?>