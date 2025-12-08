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

function obtenerProductosAdmin($pagina = 1, $por_pagina = 20, $search = '', $category = '', $stock_filter = '') {
    $conn = getAdminDBConnection();
    
    $offset = ($pagina - 1) * $por_pagina;
    
    // CONSTRUIR CONSULTA CON FILTROS
    $where = [];
    $params = [];
    $param_types = '';
    
    if (!empty($search)) {
        $where[] = "(nombre LIKE ? OR marca LIKE ? OR descripcion LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= 'sss';
    }
    
    if (!empty($category)) {
        $where[] = "categoria = ?";
        $params[] = $category;
        $param_types .= 's';
    }
    
    if (!empty($stock_filter)) {
        switch ($stock_filter) {
            case 'low':
                $where[] = "stock < 5 AND stock > 0";
                break;
            case 'out':
                $where[] = "stock = 0";
                break;
            case 'in':
                $where[] = "stock > 0";
                break;
        }
    }
    
    $sql_where = '';
    if (!empty($where)) {
        $sql_where = 'WHERE ' . implode(' AND ', $where);
    }
    
    // OBTENER PRODUCTOS
    $sql = "SELECT * FROM productos $sql_where ORDER BY id DESC LIMIT ? OFFSET ?";
    $params[] = $por_pagina;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    // OBTENER TOTAL (con los mismos filtros)
    $sql_total = "SELECT COUNT(*) as total FROM productos $sql_where";
    $stmt_total = $conn->prepare($sql_total);
    
    if (!empty($where)) {
        $count_params = array_slice($params, 0, count($params) - 2);
        $count_types = substr($param_types, 0, -2);
        if (!empty($count_params)) {
            $stmt_total->bind_param($count_types, ...$count_params);
        }
    }
    
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total = $result_total->fetch_assoc()['total'];
    
    $stmt->close();
    if (isset($stmt_total)) {
        $stmt_total->close();
    }
    
    return [
        'productos' => $productos,
        'total' => $total,
        'pagina_actual' => $pagina,
        'total_paginas' => ceil($total / $por_pagina)
    ];
}

function obtenerProductosConFiltros($pagina = 1, $por_pagina = 20, $search = '', $category = '', $stock_filter = '') {
    // Reutilizar la función modificada
    return obtenerProductosAdmin($pagina, $por_pagina, $search, $category, $stock_filter);
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

function exportarEstadisticasExcel() {
    $conn = getAdminDBConnection();
    
    // Obtener el año de los parámetros GET o usar el actual
    $anio_seleccionado = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');
    $ventas_por_mes = obtenerVentasPorMes($anio_seleccionado);
    
    // Obtener productos más vendidos
    $productos_mas_vendidos = obtenerProductosMasVendidos(20);
    
    // Configurar headers para Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="estadisticas_climaxa_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Nombres de meses
    $nombres_meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    
    // ========== CREAR ARCHIVO EXCEL ==========
    echo "<html>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<style>";
    echo "table { border-collapse: collapse; width: 100%; }";
    echo "th { background-color: #4CAF50; color: white; padding: 8px; text-align: left; }";
    echo "td { padding: 8px; border: 1px solid #ddd; }";
    echo ".total-row { background-color: #f2f2f2; font-weight: bold; }";
    echo ".title { font-size: 18px; font-weight: bold; margin-bottom: 20px; }";
    echo ".section-title { font-size: 16px; font-weight: bold; margin-top: 30px; color: #2196F3; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    
    // Título principal
    echo "<div class='title'>Reporte de Estadísticas - CLIMAXA</div>";
    echo "<div>Año: " . $anio_seleccionado . "</div>";
    echo "<div>Fecha de generación: " . date('d/m/Y H:i:s') . "</div>";
    echo "<br>";
    
    // ========== SECCIÓN 1: RESUMEN ANUAL ==========
    echo "<div class='section-title'>1. Resumen Anual " . $anio_seleccionado . "</div>";
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>Indicador</th>";
    echo "<th>Valor</th>";
    echo "</tr>";
    
    // Calcular totales
    $total_ventas = 0;
    $total_pedidos = 0;
    foreach ($ventas_por_mes as $mes) {
        $total_ventas += $mes['total_ventas'];
        $total_pedidos += $mes['total_pedidos'];
    }
    $valor_promedio = $total_pedidos > 0 ? $total_ventas / $total_pedidos : 0;
    
    // Mes con más ventas
    $mes_max = 0;
    $ventas_max = 0;
    foreach ($ventas_por_mes as $mes => $datos) {
        if ($datos['total_ventas'] > $ventas_max) {
            $ventas_max = $datos['total_ventas'];
            $mes_max = $mes;
        }
    }
    
    echo "<tr><td>Ventas Totales</td><td>RD$ " . number_format($total_ventas, 2) . "</td></tr>";
    echo "<tr><td>Pedidos Totales</td><td>" . number_format($total_pedidos) . "</td></tr>";
    echo "<tr><td>Valor Promedio por Pedido</td><td>RD$ " . number_format($valor_promedio, 2) . "</td></tr>";
    echo "<tr><td>Mes con Más Ventas</td><td>" . ($nombres_meses[$mes_max] ?? 'N/A') . " (RD$ " . number_format($ventas_max, 2) . ")</td></tr>";
    echo "<tr><td>Promedio Mensual de Pedidos</td><td>" . number_format($total_pedidos / 12, 1) . "</td></tr>";
    
    echo "</table>";
    
    // ========== SECCIÓN 2: DESGLOSE MENSUAL ==========
    echo "<br>";
    echo "<div class='section-title'>2. Desglose Mensual " . $anio_seleccionado . "</div>";
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>Mes</th>";
    echo "<th>Pedidos</th>";
    echo "<th>Ventas (RD$)</th>";
    echo "<th>Promedio por Pedido</th>";
    echo "<th>Tendencia</th>";
    echo "</tr>";
    
    $mes_anterior = 0;
    foreach ($ventas_por_mes as $mes => $datos) {
        $promedio = $datos['total_pedidos'] > 0 ? $datos['total_ventas'] / $datos['total_pedidos'] : 0;
        
        // Calcular tendencia
        $tendencia = '=';
        if ($mes_anterior > 0) {
            if ($datos['total_ventas'] > $mes_anterior) {
                $tendencia = '↑';
            } elseif ($datos['total_ventas'] < $mes_anterior) {
                $tendencia = '↓';
            }
        }
        $mes_anterior = $datos['total_ventas'];
        
        echo "<tr>";
        echo "<td>" . $nombres_meses[$mes] . "</td>";
        echo "<td>" . $datos['total_pedidos'] . "</td>";
        echo "<td>RD$ " . number_format($datos['total_ventas'], 2) . "</td>";
        echo "<td>RD$ " . number_format($promedio, 2) . "</td>";
        echo "<td>" . $tendencia . "</td>";
        echo "</tr>";
    }
    
    // Total
    echo "<tr class='total-row'>";
    echo "<td><strong>Total</strong></td>";
    echo "<td><strong>" . number_format($total_pedidos) . "</strong></td>";
    echo "<td><strong>RD$ " . number_format($total_ventas, 2) . "</strong></td>";
    echo "<td><strong>RD$ " . number_format($valor_promedio, 2) . "</strong></td>";
    echo "<td></td>";
    echo "</tr>";
    
    echo "</table>";
    
    // ========== SECCIÓN 3: PRODUCTOS MÁS VENDIDOS ==========
    echo "<br>";
    echo "<div class='section-title'>3. Productos Más Vendidos " . $anio_seleccionado . "</div>";
    
    if (!empty($productos_mas_vendidos)) {
        // Calcular total de ingresos
        $total_ingresos_productos = 0;
        foreach ($productos_mas_vendidos as $producto) {
            $total_ingresos_productos += $producto['ingresos_totales'];
        }
        
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>#</th>";
        echo "<th>Producto</th>";
        echo "<th>Marca</th>";
        echo "<th>Categoría</th>";
        echo "<th>Unidades Vendidas</th>";
        echo "<th>Ingresos (RD$)</th>";
        echo "<th>% del Total</th>";
        echo "</tr>";
        
        $contador = 1;
        foreach ($productos_mas_vendidos as $producto) {
            $porcentaje = $total_ingresos_productos > 0 ? ($producto['ingresos_totales'] / $total_ingresos_productos * 100) : 0;
            
            echo "<tr>";
            echo "<td>" . $contador++ . "</td>";
            echo "<td>" . htmlspecialchars($producto['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($producto['marca']) . "</td>";
            echo "<td>" . htmlspecialchars($producto['categoria'] ?? 'General') . "</td>";
            echo "<td>" . $producto['total_vendido'] . "</td>";
            echo "<td>RD$ " . number_format($producto['ingresos_totales'], 2) . "</td>";
            echo "<td>" . number_format($porcentaje, 1) . "%</td>";
            echo "</tr>";
        }
        
        // Total productos
        echo "<tr class='total-row'>";
        echo "<td colspan='4'><strong>Total</strong></td>";
        echo "<td><strong>" . array_sum(array_column($productos_mas_vendidos, 'total_vendido')) . "</strong></td>";
        echo "<td><strong>RD$ " . number_format($total_ingresos_productos, 2) . "</strong></td>";
        echo "<td><strong>100%</strong></td>";
        echo "</tr>";
        
        echo "</table>";
    } else {
        echo "<p>No hay datos de productos vendidos para este período.</p>";
    }
    
    echo "</body>";
    echo "</html>";
    exit;
}

?>