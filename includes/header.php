<?php

// Verificar sesión solo si $is_protected es true
if (isset($is_protected) && $is_protected && !isset($_SESSION)) {
    session_start();
}

// Si es una página protegida y no hay sesión, redirigir
if (isset($is_protected) && $is_protected) {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: ../index.php");
        exit;
    }
}

// Definir título por defecto
$page_title = isset($page_title) ? $page_title : 'CLIMAXA';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if (isset($css_files)): ?>
        <?php foreach ($css_files as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($show_navbar) && $show_navbar): ?>
        <?php include(__DIR__ . '/navbar.php'); ?>
    <?php endif; ?>