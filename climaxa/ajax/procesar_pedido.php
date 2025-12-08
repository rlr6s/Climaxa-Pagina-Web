<?php
session_start();
require_once("../config/database.php");
require_once("../includes/funciones.php");

header('Content-Type: application/json');

// 1. Verificar autenticación
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión']);
    exit;
}

// 2. Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// 3. Validar datos requeridos
$required_fields = ['nombre_completo', 'telefono', 'direccion', 'ciudad', 'metodo_pago'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "El campo $field es requerido"]);
        exit;
    }
}

// 4. Validar términos
if (!isset($_POST['terminos'])) {
    echo json_encode(['success' => false, 'message' => 'Debe aceptar los términos y condiciones']);
    exit;
}

// 5. Obtener carrito
$carrito = obtenerCarrito($usuario_id);
if (empty($carrito)) {
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
    exit;
}

// 6. Calcular totales
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = $subtotal > 5000 ? 0 : 500;
$total = $subtotal + $envio;

// --- INICIO DE TRANSACCIÓN DE BASE DE DATOS ---
$conn = getDBConnectionGlobal();
$conn->begin_transaction();

try {
    // A. Insertar el Pedido en la tabla 'pedidos'
    // Nota: Asegúrate de que tu tabla tenga estas columnas. Basado en tu archivo SQL, las agregamos.
    $sql_pedido = "INSERT INTO pedidos (usuario_id, nombre_completo, email, telefono, direccion, ciudad, provincia, codigo_postal, metodo_pago, total, estado, creado_en) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())";
    
    $stmt = $conn->prepare($sql_pedido);
    
    $email = $_POST['email'] ?? $_SESSION['usuario_correo'];
    $provincia = $_POST['provincia'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    
    $stmt->bind_param("issssssssd", 
        $usuario_id, 
        $_POST['nombre_completo'], 
        $email,
        $_POST['telefono'],
        $_POST['direccion'],
        $_POST['ciudad'],
        $provincia,
        $codigo_postal,
        $_POST['metodo_pago'],
        $total
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear el pedido: " . $stmt->error);
    }
    
    $pedido_id = $conn->insert_id;
    $stmt->close();

    // B. Insertar detalles y actualizar stock
    $sql_detalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);
    
    $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ?";
    $stmt_stock = $conn->prepare($sql_stock);

    foreach ($carrito as $item) {
        // Insertar detalle
        $stmt_detalle->bind_param("iiid", $pedido_id, $item['id'], $item['cantidad'], $item['precio']);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al guardar detalle del producto ID: " . $item['id']);
        }

        // Descontar stock (opcional, pero recomendado)
        $stmt_stock->bind_param("ii", $item['cantidad'], $item['id']);
        $stmt_stock->execute();
    }
    
    $stmt_detalle->close();
    $stmt_stock->close();

    // C. Vaciar el carrito del usuario
    $sql_delete_cart = "DELETE FROM carrito WHERE usuario_id = ?";
    $stmt_delete = $conn->prepare($sql_delete_cart);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // D. Confirmar transacción (Commit)
    $conn->commit();

    // Guardar ID en sesión solo para mostrarlo en la página de confirmación
    $_SESSION['pedido_simulado'] = [
        'id' => $pedido_id,
        'total' => $total,
        'fecha' => date('Y-m-d H:i:s'),
        'cliente' => $_POST['nombre_completo']
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Pedido procesado exitosamente',
        'pedido_id' => $pedido_id,
        'redirect' => 'confirmacion.php'
    ]);

} catch (Exception $e) {
    // Si algo falla, revertir todo (Rollback)
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error al procesar el pedido: ' . $e->getMessage()
    ]);
}
?>