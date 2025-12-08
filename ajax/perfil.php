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

function errorResponse($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function successResponse($data = []) {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

switch ($action) {
    case 'actualizar_perfil':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Método no permitido');
        }
        
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'provincia' => $_POST['provincia'] ?? ''
        ];
        
        // Validar nombre
        if (empty($datos['nombre'])) {
            errorResponse('El nombre es requerido');
        }
        
        $conn = getDBConnection();
        
        // Manejar carga de avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array(strtolower($extension), $extensiones_permitidas)) {
                errorResponse('Formato de imagen no válido. Use JPG, PNG o GIF');
            }
            
            // Crear directorio si no existe
            $upload_dir = '../assets/uploads/avatars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $nombre_archivo = 'avatar_' . $usuario_id . '_' . time() . '.' . $extension;
            $destino = $upload_dir . $nombre_archivo;
            
            // Redimensionar imagen si es muy grande
            list($ancho, $alto) = getimagesize($_FILES['avatar']['tmp_name']);
            if ($ancho > 1000 || $alto > 1000) {
                // Redimensionar a máximo 500x500
                redimensionarImagen($_FILES['avatar']['tmp_name'], $destino, 500, 500);
            } else {
                move_uploaded_file($_FILES['avatar']['tmp_name'], $destino);
            }
            
            $datos['avatar'] = $nombre_archivo;
        }
        
        // Construir query de actualización
        $campos = [];
        $valores = [];
        $tipos = '';
        
        foreach ($datos as $campo => $valor) {
            if (!empty($valor) || $campo == 'avatar') {
                $campos[] = "$campo = ?";
                $valores[] = $valor;
                $tipos .= 's';
            }
        }
        
        if (empty($campos)) {
            errorResponse('No hay datos para actualizar');
        }
        
        $valores[] = $usuario_id;
        $tipos .= 'i';
        
        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param($tipos, ...$valores);
            $success = $stmt->execute();
            
            if ($success) {
                // Actualizar datos en sesión
                $_SESSION['usuario_nombre'] = $datos['nombre'];
                
                successResponse(['message' => 'Perfil actualizado exitosamente']);
            } else {
                errorResponse('Error al actualizar el perfil');
            }
            
            $stmt->close();
        } else {
            errorResponse('Error en la consulta: ' . $conn->error);
        }
        
        $conn->close();
        break;
        
    case 'cambiar_contrasena':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            errorResponse('Método no permitido');
        }
        
        $contrasena_actual = $_POST['contrasena_actual'] ?? '';
        $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
        $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
        
        // Validaciones
        if (empty($contrasena_actual) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
            errorResponse('Todos los campos son requeridos');
        }
        
        if ($nueva_contrasena !== $confirmar_contrasena) {
            errorResponse('Las contraseñas no coinciden');
        }
        
        if (strlen($nueva_contrasena) < 8) {
            errorResponse('La nueva contraseña debe tener al menos 8 caracteres');
        }
        
        $conn = getDBConnection();
        
        // Verificar contraseña actual
        $sql = "SELECT contrasena FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            errorResponse('Usuario no encontrado');
        }
        
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($contrasena_actual, $usuario['contrasena'])) {
            errorResponse('La contraseña actual es incorrecta');
        }
        
        // Actualizar contraseña
        $hash_nueva_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hash_nueva_contrasena, $usuario_id);
        $success = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        if ($success) {
            successResponse(['message' => 'Contraseña cambiada exitosamente']);
        } else {
            errorResponse('Error al cambiar la contraseña');
        }
        break;
        
    case 'obtener_pedidos':
        try {
            $pedidos = obtenerPedidosUsuario($usuario_id);
            successResponse(['pedidos' => $pedidos]);
        } catch (Exception $e) {
            errorResponse('Error al obtener pedidos: ' . $e->getMessage());
        }
        break;
        
    case 'agregar_deseo':
        if (!isset($_POST['producto_id'])) {
            errorResponse('ID de producto requerido');
        }
        
        $producto_id = intval($_POST['producto_id']);
        $success = agregarListaDeseos($usuario_id, $producto_id);
        
        if ($success) {
            successResponse(['message' => 'Producto agregado a la lista de deseos']);
        } else {
            errorResponse('Error al agregar a la lista de deseos');
        }
        break;
        
    case 'eliminar_deseo':
        if (!isset($_POST['producto_id'])) {
            errorResponse('ID de producto requerido');
        }
        
        $producto_id = intval($_POST['producto_id']);
        $success = eliminarListaDeseos($usuario_id, $producto_id);
        
        if ($success) {
            successResponse(['message' => 'Producto eliminado de la lista de deseos']);
        } else {
            errorResponse('Error al eliminar de la lista de deseos');
        }
        break;
        
    case 'obtener_deseos':
        try {
            $deseos = obtenerListaDeseos($usuario_id);
            successResponse(['deseos' => $deseos]);
        } catch (Exception $e) {
            errorResponse('Error al obtener lista de deseos: ' . $e->getMessage());
        }
        break;
        // Agregar esto en el switch, después del case 'obtener_deseos':
case 'obtener_detalles_pedido':
    if (!isset($_GET['pedido_id'])) {
        errorResponse('ID de pedido requerido');
    }
    
    $pedido_id = intval($_GET['pedido_id']);
    
    try {
        // Primero verificar que el pedido pertenece al usuario
        $conn = getDBConnection();
        $sql = "SELECT id, total, estado, metodo_pago, creado_en FROM pedidos 
                WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $pedido_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            errorResponse('Pedido no encontrado o no autorizado');
        }
        
        $pedido = $result->fetch_assoc();
        $stmt->close();
        
        // Obtener detalles del pedido
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
        $conn->close();
        
        successResponse([
            'pedido' => $pedido,
            'detalles' => $detalles
        ]);
    } catch (Exception $e) {
        errorResponse('Error al obtener detalles del pedido: ' . $e->getMessage());
    }
    break;

case 'reordenar_pedido':
    if (!isset($_POST['order_id'])) {
        errorResponse('ID de pedido requerido');
    }
    
    $pedido_id = intval($_POST['order_id']);
    
    try {
        // Verificar que el pedido pertenece al usuario
        $conn = getDBConnection();
        $sql = "SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $pedido_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            errorResponse('Pedido no encontrado o no autorizado');
        }
        $stmt->close();
        
        // Reordenar usando la función que agregamos
        require_once("../includes/funciones.php");
        $success = reordenarPedido($usuario_id, $pedido_id);
        
        if ($success) {
            $count = contarItemsCarrito($usuario_id);
            successResponse([
                'message' => 'Productos agregados al carrito exitosamente',
                'count' => $count
            ]);
        } else {
            errorResponse('No se pudieron agregar los productos al carrito');
        }
    } catch (Exception $e) {
        errorResponse('Error al reordenar pedido: ' . $e->getMessage());
    }
    break;
    
    default:
        errorResponse('Acción no válida');
        break;
}
?>