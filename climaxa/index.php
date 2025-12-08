<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: pages/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CLIMAXA - Iniciar Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Estilos específicos para el botón de administrador */
    .admin-login-section {
      margin: 30px 0;
      padding: 20px 0;
      border-top: 1px solid #e8e8e8;
      border-bottom: 1px solid #e8e8e8;
      text-align: center;
    }
    
    .admin-login-title {
      color: #7f8c8d;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 15px;
      display: block;
    }
    
    .admin-login-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 14px 30px;
      background: linear-gradient(135deg, #764ba2, #667eea);
      color: white;
      border: none;
      border-radius: 25px;
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
      width: 100%;
      max-width: 300px;
    }
    
    .admin-login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(118, 75, 162, 0.4);
      background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .admin-login-btn i {
      font-size: 16px;
    }
    
    .login-container {
      position: relative;
      overflow: hidden;
    }
    
    /* Mejorar separación entre elementos */
    .separator {
      height: 1px;
      background: linear-gradient(to right, transparent, #e8e8e8, transparent);
      margin: 30px 0;
    }
    
    .login-btn {
      margin-bottom: 10px;
    }
    
    /* Efecto de brillo para el botón */
    .admin-login-btn::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }
    
    .admin-login-btn:hover::after {
      left: 100%;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="header">
      <h1 class="main-title">CLIMAXA</h1>
      <p class="subtitle">Sistema de Ventas</p>
    </div>

    <h2 class="form-title">Iniciar Sesión</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    ?>

    <form action="backend/login.php" method="POST">
      <div class="form-group">
        <label for="correo" class="input-label">Correo electrónico</label>
        <input type="email" name="correo" id="correo" class="form-input" placeholder="Ejemplo@gmail.com" required>
      </div>

      <div class="form-group">
        <label for="contrasena" class="input-label">Contraseña</label>
        <input type="password" name="contrasena" id="contrasena" class="form-input" placeholder="Ingresa tu contraseña" required>
      </div>

      <div class="recover-password">
        <span>¿Olvidaste tu contraseña?</span>
        <a href="recuperar.php" class="recover-link">Recupérala aquí</a>
      </div>

      <div class="separator"></div>

      <button type="submit" class="login-btn">
        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
      </button>

      <div class="create-account">
        <span>¿No tienes una cuenta?</span>
        <a href="register.php" class="create-link">Crear una cuenta</a>
      </div>

      <!-- Sección de administrador dentro del formulario -->
      <div class="admin-login-section">
        <span class="admin-login-title">Acceso para administradores</span>
        <a href="admin/login.php" class="admin-login-btn" style="position: relative; overflow: hidden;">
          <i class="fas fa-user-shield"></i> Soy administrador
        </a>
      </div>

    </form>
  </div>

  <script>
    // Añadir efecto de carga al botón de administrador
    document.addEventListener('DOMContentLoaded', function() {
      const adminBtn = document.querySelector('.admin-login-btn');
      if (adminBtn) {
        adminBtn.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-3px)';
        });
        
        adminBtn.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
        });
        
        // Efecto de clic
        adminBtn.addEventListener('click', function(e) {
          this.style.transform = 'scale(0.98)';
          setTimeout(() => {
            this.style.transform = '';
          }, 150);
        });
      }
    });
  </script>
</body>
</html>