<?php
// admin/login.php - SIMPLIFICADO
session_start();

// Si ya está logueado como admin, redirigir al panel
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
    header("Location: index.php");
    exit;
}

// Incluir base de datos
require_once(__DIR__ . '/../config/database.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($correo) || empty($contrasena)) {
        $error = "Por favor, complete todos los campos";
    } else {
        $conn = getDBConnection();
        
        // Primero buscar el usuario
        $sql = "SELECT id, nombre, correo, contrasena FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            if (password_verify($contrasena, $usuario['contrasena'])) {
                // Verificar si es administrador
                $sql_rol = "SELECT rol FROM usuarios WHERE id = ?";
                $stmt_rol = $conn->prepare($sql_rol);
                $stmt_rol->bind_param("i", $usuario['id']);
                $stmt_rol->execute();
                $result_rol = $stmt_rol->get_result();
                $rol = $result_rol->fetch_assoc();
                
                if ($rol['rol'] === 'admin') {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_correo'] = $usuario['correo'];
                    $_SESSION['usuario_rol'] = $rol['rol'];
                    $_SESSION['loggedin'] = true;
                    
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "No tiene permisos de administrador";
                }
                
                $stmt_rol->close();
            } else {
                $error = "Contraseña incorrecta";
            }
        } else {
            $error = "Usuario no encontrado";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLIMAXA - Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
        }
        
        .form-title {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 22px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .form-input:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background: #5a6fd8;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
            text-align: center;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .admin-badge {
            display: inline-block;
            background: #764ba2;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .success-message {
            background: #dfd;
            color: #383;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #cfc;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>CLIMAXA <span class="admin-badge">Admin</span></h1>
            <p>Panel de Administración</p>
        </div>
        
        <h2 class="form-title">Iniciar Sesión</h2>
        
        <?php if (isset($_GET['logout']) && $_GET['logout'] == 'true'): ?>
        <div class="success-message">
            Sesión cerrada exitosamente
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" name="correo" id="correo" class="form-input" placeholder="admin@climaxa.com" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" name="contrasena" id="contrasena" class="form-input" placeholder="Tu contraseña" required>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">
                <i class="fas fa-arrow-left"></i> Volver a la tienda
            </a>
        </div>
    </div>
</body>
</html>