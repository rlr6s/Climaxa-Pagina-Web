<?php
// admin/config.php
// Configuración básica para el panel de administración

// Definir constantes para rutas
define('ADMIN_PATH', __DIR__);
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));

// Incluir configuración de base de datos
require_once(BASE_PATH . '/config/database.php');
?>