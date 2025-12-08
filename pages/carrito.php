<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/funciones.php");

$usuario_id = $_SESSION['usuario_id'];

$page_title = "Carrito de Compras - CLIMAXA";
$current_page = "carrito.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
$js_files = ['../assets/js/carrito.js'];
?>

<?php include('../includes/header.php'); ?>

<div class="main-content">
    <div class="back-container">
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver a la pantalla principal
        </a>
    </div>

    <h1 class="page-title">Carrito de Compras</h1>
    <p class="page-subtitle">Revisa y gestiona los productos en tu carrito</p>

    <div id="carrito-container">
        <div class="loading-cart">
            <i class="fas fa-spinner fa-spin"></i> Cargando carrito...
        </div>
    </div>

    <div id="carrito-resumen" style="display: none;">
        <div class="cart-summary">
            <div class="summary-header">
                <h3>Resumen del Pedido</h3>
            </div>
            <div class="summary-content">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">RD$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Envío:</span>
                    <span id="envio">RD$0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total">RD$0.00</span>
                </div>
            </div>
            <div class="summary-actions">
                <a href="checkout.php" class="view-more-btn" style="text-decoration: none; display: block;">
                    Proceder al Pago
                </a>
                <a href="dashboard.php" class="continue-shopping">
                    Seguir Comprando
                </a>
            </div>
        </div>
    </div>

    <div id="carrito-vacio" style="display: none; text-align: center; padding: 50px;">
        <i class="fas fa-shopping-cart" style="font-size: 60px; color: #ccc; margin-bottom: 20px;"></i>
        <h3>Tu carrito está vacío</h3>
        <p>¡Agrega algunos productos para comenzar!</p>
        <a href="dashboard.php" class="view-more-btn" style="margin-top: 20px; text-decoration: none; display: inline-block;">
            Ver Productos
        </a>
    </div>
</div>

<style>
.cart-summary {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 25px;
    margin-top: 30px;
}

.summary-header {
    border-bottom: 2px solid var(--border);
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.summary-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-dark);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 16px;
}

.summary-row.total {
    font-size: 18px;
    font-weight: 700;
    border-top: 2px solid var(--border);
    padding-top: 15px;
    margin-top: 15px;
}

.summary-actions {
    margin-top: 25px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.continue-shopping {
    text-align: center;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    padding: 10px;
    border: 1px solid var(--primary);
    border-radius: 6px;
    transition: all 0.3s;
}

.continue-shopping:hover {
    background: var(--primary);
    color: white;
}

.cart-item {
    display: flex;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 20px;
    margin-bottom: 20px;
    align-items: center;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    margin-right: 20px;
    flex-shrink: 0;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.cart-item-info {
    flex: 1;
    min-width: 0;
}

.cart-item-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 5px;
    word-wrap: break-word;
}

.cart-item-brand {
    color: var(--primary);
    font-size: 14px;
    margin-bottom: 10px;
}

.cart-item-price {
    font-weight: 700;
    font-size: 18px;
    color: var(--text-dark);
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid var(--border);
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 5px;
    font-family: 'Poppins', sans-serif;
}

.cart-item-actions {
    margin-left: 20px;
    flex-shrink: 0;
}

.remove-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.remove-btn:hover {
    background: #c0392b;
}

.loading-cart {
    text-align: center;
    padding: 50px;
    font-size: 18px;
    color: var(--text-light);
}

.fa-spin {
    margin-right: 10px;
}

@media (max-width: 768px) {
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cart-item-image {
        width: 100%;
        height: 150px;
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .cart-item-actions {
        margin-left: 0;
        margin-top: 15px;
        width: 100%;
    }
    
    .remove-btn {
        width: 100%;
    }
}
</style>

<?php include('../includes/footer.php'); ?>