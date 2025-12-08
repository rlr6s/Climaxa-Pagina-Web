<?php
session_start();
require_once("config/database.php");

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Ingresa un correo válido.";
    } else {
        $conn = getDBConnection(); // [cite: 408]
        
        // 1. Verificar si el usuario existe
        $sql = "SELECT id, nombre FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            // 2. Generar Token y Expiración (1 hora)
            $token = bin2hex(random_bytes(32));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));
            
            // 3. Guardar en BD
            $update = "UPDATE usuarios SET reset_token = ?, reset_expires = ? WHERE correo = ?";
            $stmt_up = $conn->prepare($update);
            $stmt_up->bind_param("sss", $token, $expira, $correo);
            
            if ($stmt_up->execute()) {
                // 4. Simulación de envío de correo (Para entorno local)
                // En producción usarías mail() o PHPMailer.
                // Aquí creamos el link para que puedas probarlo directamente.
                $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/restablecer.php?token=" . $token;
                
                $_SESSION['success'] = "Se ha generado el enlace de recuperación: <br><a href='$link'>CLICK AQUÍ PARA RESTABLECER</a>";
            } else {
                $_SESSION['error'] = "Error al generar el token.";
            }
        } else {
            // Por seguridad, no decimos si el correo existe o no, pero aquí mostraremos mensaje genérico
            $_SESSION['error'] = "Si el correo existe, recibirás instrucciones.";
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - CLIMAXA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="header">
            <h1 class="main-title">CLIMAXA</h1>
            <p class="subtitle">Recuperación de Acceso</p>
        </div>

        <h2 class="form-title">Olvidé mi contraseña</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message" style="word-break: break-all;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="recuperar.php" method="POST">
            <div class="form-group">
                <label for="correo" class="input-label">Ingresa tu correo registrado</label>
                <input type="email" name="correo" id="correo" class="form-input" placeholder="ejemplo@gmail.com" required>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-paper-plane"></i> Enviar enlace
            </button>
            
            <div class="login-link">
                <a href="index.php" class="login-link-btn">Volver al Login</a>
            </div>
        </form>
    </div>
</body>
</html>