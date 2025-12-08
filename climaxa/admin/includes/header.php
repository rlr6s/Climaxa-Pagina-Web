<?php
// admin/includes/header.php - CORREGIDO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/funciones.php');
requerirAdmin();

$page_title = isset($page_title) ? $page_title . ' - CLIMAXA Admin' : 'CLIMAXA Admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include(__DIR__ . '/sidebar.php'); ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="menu-toggle" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo isset($page_title) ? str_replace(' - CLIMAXA Admin', '', $page_title) : 'Dashboard'; ?></h1>
                </div>
                
                <div class="top-bar-right">
                    <div class="user-menu">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo isset($_SESSION['usuario_nombre']) ? strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)) : 'A'; ?>
                            </div>
                            <span class="user-name"><?php echo isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Administrador'; ?></span>
                            <span class="user-role">Administrador</span>
                        </div>
                        <div class="dropdown">
                            <a href="../pages/dashboard.php" class="dropdown-item">
                                <i class="fas fa-store"></i> Ver Tienda
                            </a>
                            <a href="../logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-area">