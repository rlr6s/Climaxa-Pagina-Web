<?php
session_start();
require_once("config/database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    // Validaciones
    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        $_SESSION['error'] = "Todos los campos son obligatorios";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Formato de correo electrónico inválido";
    } elseif ($contrasena !== $confirmar_contrasena) {
        $_SESSION['error'] = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 6) {
        $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres";
    } else {
        $conn = getDBConnection();
        try {
            // Verificar si el correo ya existe
            $sql = "SELECT id FROM usuarios WHERE correo = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['error'] = "El correo electrónico ya está registrado";
            } else {
                // Hash de la contraseña
                $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

                // Insertar nuevo usuario
                $sql = "INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $nombre, $correo, $hash_contrasena);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Usuario registrado correctamente. Ahora puedes iniciar sesión.";
                    header("Location: index.php");
                    exit;
                } else {
                    $_SESSION['error'] = "Error al registrar el usuario";
                }
            }
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error del sistema: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CLIMAXA - Crear Cuenta</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <div class="register-container">
    <!-- Encabezado con logo -->
<div class="auth-header" style="text-align: center;">
  <?php 
  // Intentar cargar logo
  $logo_path = 'assets/img/logo.png';
  $logo1_path = 'assets/img/logo1.png';
  
  if (file_exists($logo_path) || file_exists($logo1_path)): 
  ?>
  <div style="
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 20px;
    padding: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
  ">
    <?php if (file_exists($logo_path)): ?>
      <img src="<?php echo $logo_path; ?>" alt="CLIMAXA" style="
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
      ">
    <?php elseif (file_exists($logo1_path)): ?>
      <img src="<?php echo $logo1_path; ?>" alt="CLIMAXA" style="
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
      ">
    <?php endif; ?>
  </div>
  <?php else: ?>
    <h1 class="auth-logo-text">CLIMAXA</h1>
  <?php endif; ?>
</div>

    <h2 class="form-title">Crear una Cuenta</h2>

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

    <form action="register.php" method="POST">
      <div class="form-group">
        <label for="nombre" class="input-label">Nombre completo</label>
        <input 
          type="text" 
          name="nombre" 
          id="nombre" 
          class="form-input"
          placeholder="Escribe tu nombre" 
          required 
          value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
        />
      </div>

      <div class="form-group">
        <label for="correo" class="input-label">Correo electrónico</label>
        <input 
          type="email" 
          name="correo" 
          id="correo" 
          class="form-input"
          placeholder="Ejemplo@gmail.com" 
          required 
          value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
        />
      </div>

      <div class="form-group">
        <label for="contrasena" class="input-label">Contraseña</label>
        <input 
          type="password" 
          name="contrasena" 
          id="contrasena" 
          class="form-input"
          placeholder="Mínimo 6 caracteres" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="confirmar_contrasena" class="input-label">Confirmar Contraseña</label>
        <input 
          type="password" 
          name="confirmar_contrasena" 
          id="confirmar_contrasena" 
          class="form-input"
          placeholder="Repite tu contraseña" 
          required 
        />
      </div>

      <button type="submit" class="register-btn">Crear Cuenta</button>

      <div class="login-link">
        <span class="login-link-text">¿Ya tienes una cuenta?</span>
        <a href="index.php" class="login-link-btn">Iniciar Sesión</a>
      </div>
    </form>
  </div>
</body>
</html>