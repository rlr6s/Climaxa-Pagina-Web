<?php
// ajax/admin.php - CORREGIDO
session_start();

// Desactivar visualización de errores para no romper el JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once("../config/database.php");

// CORRECCIÓN 1: Importar las funciones del ADMIN, no las públicas
require_once("../admin/includes/funciones.php"); 

header('Content-Type: application/json');

// Verificar que sea administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'contar_pedidos_pendientes':
        $estadisticas = obtenerEstadisticas();
        echo json_encode([
            'success' => true,
            'count' => $estadisticas['pedidos_pendientes'] ?? 0
        ]);
        break;
        
    case 'ventas_por_mes':
        $anio = $_GET['anio'] ?? date('Y');
        $ventas = obtenerVentasPorMes($anio);
        echo json_encode([
            'success' => true,
            'ventas' => $ventas,
            'anio' => $anio
        ]);
        break;
        
    case 'obtener_producto':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }
        
        $conn = getAdminDBConnection();
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();
        
        $stmt->close();
        
        echo json_encode([
            'success' => $producto ? true : false,
            'producto' => $producto
        ]);
        break;
        
    case 'guardar_producto':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'precio' => floatval($_POST['precio'] ?? 0),
            'categoria' => $_POST['categoria'] ?? '',
            'marca' => $_POST['marca'] ?? '',
            'especificaciones' => $_POST['especificaciones'] ?? '',
            'stock' => intval($_POST['stock'] ?? 0),
            'imagen' => ''
        ];
        
        // Manejar carga de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = uniqid() . '.' . $extension;
            
            // CORRECCIÓN 2: Ruta correcta (subir un nivel, no dos)
            $destino = "../assets/img/" . $nombre_archivo;
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                $datos['imagen'] = $nombre_archivo;
            }
        } elseif (isset($_POST['id']) && !empty($_POST['id'])) {
            // Si no se subió nueva imagen y estamos editando, mantener la existente
            $conn = getAdminDBConnection();
            $sql = "SELECT imagen FROM productos WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_POST['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $producto = $result->fetch_assoc();
            $datos['imagen'] = $producto['imagen'] ?? '';
            $stmt->close();
        }
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Actualizar producto existente
            $success = actualizarProducto(intval($_POST['id']), $datos);
            $message = $success ? 'Producto actualizado exitosamente' : 'Error al actualizar producto';
            $producto_id = $_POST['id'];
        } else {
            // Crear nuevo producto
            $producto_id = crearProducto($datos);
            $success = $producto_id !== false;
            $message = $success ? 'Producto creado exitosamente' : 'Error al crear producto';
        }
        
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'producto_id' => $producto_id ?? null
        ]);
        break;
        
    case 'eliminar_producto':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }
        
        $id = intval($_GET['id']);
        $success = eliminarProducto($id);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Producto eliminado exitosamente' : 'No se puede eliminar (posiblemente tiene ventas asociadas)'
        ]);
        break;
        
    case 'cambiar_rol_usuario':
        if (!isset($_POST['usuario_id']) || !isset($_POST['nuevo_rol'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        $usuario_id = intval($_POST['usuario_id']);
        $nuevo_rol = $_POST['nuevo_rol'];
        
        // No permitir cambiar el rol del propio administrador
        if ($usuario_id == $_SESSION['usuario_id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio rol']);
            exit;
        }
        
        $success = cambiarRolUsuario($usuario_id, $nuevo_rol);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Rol actualizado exitosamente' : 'Error al actualizar rol'
        ]);
        break;
        
    case 'eliminar_usuario':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }
        
        $usuario_id = intval($_GET['id']);
        $success = eliminarUsuario($usuario_id);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Usuario eliminado exitosamente' : 'Error al eliminar usuario'
        ]);
        break;
        
    case 'obtener_detalles_pedido':
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }
        
        $pedido_id = intval($_GET['id']);
        $pedido = obtenerPedidoPorId($pedido_id); // Usamos la función de admin/includes/funciones.php
        
        if ($pedido) {
            $pedido['fecha'] = date('d/m/Y H:i', strtotime($pedido['creado_en']));
            $detalles = obtenerDetallePedido($pedido_id);
            
            echo json_encode([
                'success' => true,
                'pedido' => $pedido,
                'detalles' => $detalles
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ]);
        }
        break;

    case 'actualizar_estado_pedido':
        if (!isset($_POST['pedido_id']) || !isset($_POST['nuevo_estado'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        $pedido_id = intval($_POST['pedido_id']);
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $estados_permitidos = ['pendiente', 'procesando', 'enviado', 'completado', 'cancelado'];
        
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido']);
            exit;
        }
        
        $success = actualizarEstadoPedido($pedido_id, $nuevo_estado);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Estado actualizado exitosamente' : 'Error al actualizar estado'
        ]);
        break;
        
    case 'obtener_productos_mas_vendidos':
        $anio = $_GET['anio'] ?? date('Y');
        $limite = $_GET['limite'] ?? 10;
        
        $productos = obtenerProductosMasVendidos($limite); // Esta función ya filtra por año internamente si se modifica
        
        echo json_encode([
            'success' => true,
            'productos' => $productos,
            'anio' => $anio
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>