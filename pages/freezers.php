<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/funciones.php");

$page_title = "Freezers - CLIMAXA";
$current_page = "freezers.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$search_placeholder = "Buscar freezers...";
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
$js_files = [];
?>

<?php include('../includes/header.php'); ?>

<div class="main-content">
    <div class="back-container">
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver a la pantalla principal
        </a>
    </div>

    <h1 class="page-title">Freezers</h1>
    <p class="page-subtitle">Encuentra el freezer perfecto para tus necesidades de almacenamiento y conservación.</p>

    <div class="products-grid">
        <?php
        $productos = obtenerProductosPorCategoria('freezers');
        if (empty($productos)) {
            echo '<p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">No hay productos disponibles en esta categoría.</p>';
        } else {
            foreach ($productos as $producto) {
                echo renderProductoDB($producto);
            }
        }
        ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>