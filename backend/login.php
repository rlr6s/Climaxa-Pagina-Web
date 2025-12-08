<?php
session_start();
require_once("../config/database.php");

// Validar que los campos existan
if (empty($_POST['correo']) || empty($_POST['contrasena'])) {
    $_SESSION['error'] = "Por favor, complete todos los campos";
    header("Location: ../index.php");
    exit;
}

$correo = trim($_POST['correo']);
$contrasena = $_POST['contrasena'];

// Validar formato de email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Formato de correo electrónico inválido";
    header("Location: ../index.php");
    exit;
}

$conn = getDBConnection();

try {
    // Obtener usuario con su rol
    $sql = "SELECT id, nombre, correo, contrasena, rol FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($contrasena, $usuario['contrasena'])) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['loggedin'] = true;
            
            $stmt->close();
            $conn->close();
            
            // Redirigir según el rol
            if ($usuario['rol'] === 'admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../pages/dashboard.php");
            }
            exit;
        }
    }
    
    // Error genérico por seguridad
    $_SESSION['error'] = "Credenciales inválidas";
    header("Location: ../index.php");
    exit;
    
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    $_SESSION['error'] = "Error del sistema. Por favor, intente más tarde.";
    header("Location: ../index.php");
    exit;
}
?>