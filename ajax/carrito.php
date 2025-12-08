<?php
session_start();
require_once("../config/database.php");
require_once("../includes/funciones.php");

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// Función para devolver error
function errorResponse($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Función para devolver éxito
function successResponse($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

switch ($action) {
    case 'agregar':
        if (!isset($_POST['producto_id'])) {
            errorResponse('ID de producto requerido');
        }
        
        $producto_id = intval($_POST['producto_id']);
        $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
        
        try {
            $success = agregarAlCarrito($usuario_id, $producto_id, $cantidad);
            
            if ($success) {
                $count = contarItemsCarrito($usuario_id);
                successResponse([
                    'message' => 'Producto agregado al carrito',
                    'count' => $count
                ]);
            } else {
                errorResponse('Error al agregar al carrito');
            }
        } catch (Exception $e) {
            errorResponse('Error: ' . $e->getMessage());
        }
        break;
        
    case 'eliminar':
        if (!isset($_POST['carrito_id'])) {
            errorResponse('ID de carrito requerido');
        }
        
        $carrito_id = intval($_POST['carrito_id']);
        $success = eliminarDelCarrito($carrito_id, $usuario_id);
        
        if ($success) {
            $count = contarItemsCarrito($usuario_id);
            successResponse([
                'message' => 'Producto eliminado del carrito',
                'count' => $count
            ]);
        } else {
            errorResponse('Error al eliminar del carrito');
        }
        break;
        
    case 'actualizar':
        if (!isset($_POST['carrito_id']) || !isset($_POST['cantidad'])) {
            errorResponse('Datos requeridos');
        }
        
        $carrito_id = intval($_POST['carrito_id']);
        $cantidad = intval($_POST['cantidad']);
        $success = actualizarCantidadCarrito($carrito_id, $usuario_id, $cantidad);
        
        if ($success) {
            successResponse(['message' => 'Cantidad actualizada']);
        } else {
            errorResponse('Error al actualizar cantidad');
        }
        break;
        
    case 'obtener':
        try {
            $items = obtenerCarrito($usuario_id);
            $total = obtenerTotalCarrito($usuario_id);
            
            successResponse([
                'items' => $items,
                'total' => $total,
                'count' => count($items)
            ]);
        } catch (Exception $e) {
            errorResponse('Error al obtener carrito: ' . $e->getMessage());
        }
        break;
        
    case 'contar':
        try {
            $count = contarItemsCarrito($usuario_id);
            
            successResponse(['count' => $count]);
        } catch (Exception $e) {
            successResponse(['count' => 0, 'message' => 'Error al contar items: ' . $e->getMessage()]);
        }
        break;
        
    default:
        errorResponse('Acción no válida');
        break;
}
?>