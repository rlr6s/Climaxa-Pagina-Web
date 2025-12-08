<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/funciones.php");

$page_title = "CLIMAXA - Productos";
$current_page = "dashboard.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$search_placeholder = "¿Qué producto buscas?";
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
$js_files = ['../assets/js/app.js'];
?>

<?php include(__DIR__ . '/../includes/header.php'); ?>

<div class="main-content">
    <h1 class="page-title">Descubre todos nuestros productos</h1>
    <p class="page-subtitle">Ven y prueba la calidad de todos nuestros aires acondicionados, freezers, neveras y servicios técnicos.</p>
    
    <!-- Categories Buttons Section -->
    <div class="categories-buttons">
        <h2 class="categories-title">Explora Nuestras Categorías</h2>
        <div class="categories-grid">
            <!-- Aires Acondicionados -->
            <div class="category-item">
                <button class="category-button" onclick="scrollToSection('aires-section')">
                    Ver Aires Acondicionados
                </button>
                <div class="category-image">
                    <?php
                    if (file_exists('../assets/img/producto1.png')) {
                        echo '<img src="../assets/img/producto1.png" alt="Aire Acondicionado">';
                    } else {
                        echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 14px;">';
                        echo '<i class="fas fa-snowflake" style="font-size: 50px;"></i>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Freezers -->
            <div class="category-item">
                <button class="category-button" onclick="scrollToSection('freezers-section')">
                    Ver Freezers
                </button>
                <div class="category-image">
                    <?php
                    if (file_exists('../assets/img/freezer1.png')) {
                        echo '<img src="../assets/img/freezer1.png" alt="Freezer">';
                    } else {
                        echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 14px;">';
                        echo '<i class="fas fa-temperature-low" style="font-size: 50px;"></i>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Neveras -->
            <div class="category-item">
                <button class="category-button" onclick="scrollToSection('neveras-section')">
                    Ver Neveras
                </button>
                <div class="category-image">
                    <?php
                    if (file_exists('../assets/img/nevera1.png')) {
                        echo '<img src="../assets/img/nevera1.png" alt="Nevera">';
                    } else {
                        echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 14px;">';
                        echo '<i class="fas fa-refrigerator" style="font-size: 50px;"></i>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Aires Acondicionados -->
    <h2 class="section-title" id="aires-section">Aires Acondicionados</h2>
    <div class="products-grid">
        <?php
        $aires = obtenerProductosPorCategoria('aires', 3);
        if (empty($aires)) {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: var(--shadow);">';
            echo '<i class="fas fa-snowflake" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>';
            echo '<p style="color: #666;">No hay productos disponibles en esta categoría.</p>';
            echo '</div>';
        } else {
            foreach ($aires as $producto) {
                echo renderProductoDB($producto);
            }
        }
        ?>
    </div>

    <!-- Botón Ver Más para Aires -->
    <div class="view-more-container">
        <a href="aires.php" class="view-more-btn">Ver Más Aires Acondicionados</a>
    </div>

    <!-- Sección de Freezers -->
    <h2 class="section-title" id="freezers-section">Freezers</h2>
    <div class="products-grid">
        <?php
        $freezers = obtenerProductosPorCategoria('freezers', 3);
        if (empty($freezers)) {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: var(--shadow);">';
            echo '<i class="fas fa-temperature-low" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>';
            echo '<p style="color: #666;">No hay productos disponibles en esta categoría.</p>';
            echo '</div>';
        } else {
            foreach ($freezers as $producto) {
                echo renderProductoDB($producto);
            }
        }
        ?>
    </div>

    <!-- Botón Ver Más para Freezers -->
    <div class="view-more-container">
        <a href="freezers.php" class="view-more-btn">Ver Más Freezers</a>
    </div>

    <!-- Sección de Neveras -->
    <h2 class="section-title" id="neveras-section">Neveras</h2>
    <div class="products-grid">
        <?php
        $neveras = obtenerProductosPorCategoria('neveras', 3);
        if (empty($neveras)) {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: var(--shadow);">';
            echo '<i class="fas fa-refrigerator" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>';
            echo '<p style="color: #666;">No hay productos disponibles en esta categoría.</p>';
            echo '</div>';
        } else {
            foreach ($neveras as $producto) {
                echo renderProductoDB($producto);
            }
        }
        ?>
    </div>

    <!-- Botón Ver Más para Neveras -->
    <div class="view-more-container">
        <a href="neveras.php" class="view-more-btn">Ver Más Neveras</a>
    </div>

    <!-- Sección de Servicios Técnicos -->
    <h2 class="section-title" id="servicios-section">Servicios Técnicos</h2>
    <div class="products-grid">
        <?php
        $servicios = obtenerProductosPorCategoria('servicios');
        if (empty($servicios)) {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: var(--shadow);">';
            echo '<i class="fas fa-tools" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>';
            echo '<p style="color: #666;">No hay servicios disponibles.</p>';
            echo '</div>';
        } else {
            foreach ($servicios as $servicio) {
                // Usar función específica para servicios
                ob_start(); ?>
                <div class="service-card">
                    <div class="service-icon">
                        <?php
                        if ($servicio['id'] == 66) {
                            echo '🔧';
                        } elseif ($servicio['id'] == 67) {
                            echo '🛠️';
                        } else {
                            echo '⚡';
                        }
                        ?>
                    </div>
                    <h3 class="service-name"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                    <p class="service-description"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                    <div class="service-price">RD$<?php echo number_format($servicio['precio'], 2); ?>+</div>
                    <button class="service-btn" data-product-id="<?php echo $servicio['id']; ?>">
                        Solicitar Servicio
                    </button>
                </div>
                <?php 
                echo ob_get_clean();
            }
        }
        ?>
    </div>
</div>

<script>
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        window.scrollTo({
            top: section.offsetTop - 80,
            behavior: 'smooth'
        });
    }
}
</script>

<style>
.service-card {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    text-align: center;
    padding: 30px 20px;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.service-icon {
    font-size: 48px;
    margin-bottom: 15px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.service-name {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--text-dark);
}

.service-description {
    font-size: 14px;
    color: var(--text-light);
    margin-bottom: 20px;
    line-height: 1.5;
    min-height: 60px;
}

.service-price {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 20px;
}

.service-btn {
    background: var(--primary);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s;
    width: 100%;
}

.service-btn:hover {
    background: var(--primary-dark);
}
</style>

<?php 
// Asegurarnos de que el footer se muestre
$show_footer = true;
include('../includes/footer.php'); 
?>