<?php
session_start();
require_once("config/database.php");

$token = $_GET['token'] ?? '';
$mostrar_form = false;
$conn = getDBConnection();

// Verificar si el token es válido y no ha expirado
if (!empty($token)) {
    $sql = "SELECT id FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mostrar_form = true;
    } else {
        $_SESSION['error'] = "El enlace es inválido o ha expirado.";
    }
} else if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Procesar el cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_post = $_POST['token_post'];
    $pass = $_POST['nueva_contrasena'];
    $confirm = $_POST['confirmar_contrasena'];
    
    if ($pass !== $confirm) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header("Location: restablecer.php?token=" . $token_post);
        exit;
    }
    
    if (strlen($pass) < 6) { // Validación similar al registro [cite: 1076]
        $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres.";
        header("Location: restablecer.php?token=" . $token_post);
        exit;
    }

    // Actualizar contraseña y limpiar token
    $hash = password_hash($pass, PASSWORD_DEFAULT); // 
    
    $sql_update = "UPDATE usuarios SET contrasena = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
    $stmt_up = $conn->prepare($sql_update);
    $stmt_up->bind_param("ss", $hash, $token_post);
    
    if ($stmt_up->execute()) {
        $_SESSION['success'] = "Contraseña actualizada. Inicia sesión.";
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Error al actualizar.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - CLIMAXA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h1 class="main-title">CLIMAXA</h1>
            <p class="subtitle">Nueva Contraseña</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <div class="login-link"><a href="recuperar.php" class="login-link-btn">Intentar de nuevo</a></div>
        <?php endif; ?>

        <?php if ($mostrar_form): ?>
        <form action="restablecer.php" method="POST">
            <input type="hidden" name="token_post" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label class="input-label">Nueva Contraseña</label>
                <input type="password" name="nueva_contrasena" class="form-input" placeholder="Mínimo 6 caracteres" required>
            </div>
            
            <div class="form-group">
                <label class="input-label">Confirmar Contraseña</label>
                <input type="password" name="confirmar_contrasena" class="form-input" placeholder="Repite la contraseña" required>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-save"></i> Guardar Contraseña
            </button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>