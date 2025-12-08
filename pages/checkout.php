<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/funciones.php");

$usuario_id = $_SESSION['usuario_id'];
$carrito = obtenerCarrito($usuario_id);

if (empty($carrito)) {
    $_SESSION['error'] = "Tu carrito está vacío";
    header("Location: carrito.php");
    exit;
}

$total = obtenerTotalCarrito($usuario_id);
$envio = $total > 5000 ? 0 : 500;
$total_final = $total + $envio;

$page_title = "Checkout - CLIMAXA";
$current_page = "checkout.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
$js_files = ['../assets/js/checkout.js'];
?>

<?php include('../includes/header.php'); ?>

<div class="main-content">
    <div class="back-container">
        <a href="carrito.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver al Carrito
        </a>
    </div>

    <h1 class="page-title">Finalizar Compra</h1>
    <p class="page-subtitle">Completa tu información para procesar el pedido</p>

    <div class="checkout-container">
        <div class="checkout-form">
            <form id="form-checkout" action="../ajax/procesar_pedido.php" method="POST">
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Información Personal</h3>
                    
                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo *</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" 
                               value="<?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_SESSION['usuario_correo']) ? $_SESSION['usuario_correo'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="809-555-5555" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Dirección de Envío</h3>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección Completa *</label>
                        <textarea id="direccion" name="direccion" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ciudad">Ciudad *</label>
                            <input type="text" id="ciudad" name="ciudad" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="provincia">Provincia *</label>
                            <select id="provincia" name="provincia" required>
                                <option value="">Seleccione una provincia</option>
                                <option value="DN">Distrito Nacional</option>
                                <option value="SD">Santo Domingo</option>
                                <option value="ST">Santiago</option>
                                <!-- Agrega más provincias según necesites -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal">
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-credit-card"></i> Método de Pago</h3>
                    
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" id="pago_efectivo" name="metodo_pago" value="efectivo" checked>
                            <label for="pago_efectivo">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Pago en Efectivo</span>
                                <small>Al recibir el producto</small>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="pago_transferencia" name="metodo_pago" value="transferencia">
                            <label for="pago_transferencia">
                                <i class="fas fa-university"></i>
                                <span>Transferencia Bancaria</span>
                                <small>Banco Popular - 123-4567890</small>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="pago_tarjeta" name="metodo_pago" value="tarjeta">
                            <label for="pago_tarjeta">
                                <i class="fas fa-credit-card"></i>
                                <span>Tarjeta de Crédito/Débito</span>
                                <small>Visa, MasterCard, Amex</small>
                            </label>
                        </div>
                    </div>
                    
                    <div id="tarjeta-info" style="display: none; margin-top: 20px;">
                        <div class="form-group">
                            <label for="numero_tarjeta">Número de Tarjeta</label>
                            <input type="text" id="numero_tarjeta" name="numero_tarjeta" placeholder="1234 5678 9012 3456">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_expiracion">Fecha de Expiración</label>
                                <input type="text" id="fecha_expiracion" name="fecha_expiracion" placeholder="MM/AA">
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-receipt"></i> Resumen del Pedido</h3>
                    
                    <div class="order-summary">
                        <div class="summary-items">
                            <?php foreach ($carrito as $item): ?>
                            <div class="summary-item">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                    <span class="item-qty">x<?php echo $item['cantidad']; ?></span>
                                </div>
                                <span class="item-price">RD$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>RD$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="total-row">
                                <span>Envío:</span>
                                <span><?php echo $envio == 0 ? 'GRATIS' : 'RD$' . number_format($envio, 2); ?></span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total:</span>
                                <span>RD$<?php echo number_format($total_final, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="terms-section">
                    <input type="checkbox" id="terminos" name="terminos" required>
                    <label for="terminos">
                        Acepto los <a href="#" target="_blank">Términos y Condiciones</a> y 
                        la <a href="#" target="_blank">Política de Privacidad</a> *
                    </label>
                </div>

                <button type="submit" class="submit-order-btn" id="submit-order">
                    <i class="fas fa-lock"></i>
                    Confirmar Pedido
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 800px;
    margin: 0 auto;
}

.checkout-form {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 30px;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 1px solid var(--border);
}

.form-section:last-child {
    border-bottom: none;
}

.form-section h3 {
    font-size: 18px;
    margin-bottom: 20px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-dark);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-size: 16px;
    transition: border 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--primary);
    outline: none;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.payment-option input {
    display: none;
}

.payment-option label {
    display: flex;
    flex-direction: column;
    padding: 20px;
    border: 2px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.payment-option input:checked + label {
    border-color: var(--primary);
    background: rgba(0, 170, 255, 0.05);
}

.payment-option label i {
    font-size: 32px;
    margin-bottom: 10px;
    color: var(--primary);
}

.payment-option label span {
    font-weight: 600;
    margin-bottom: 5px;
}

.payment-option label small {
    font-size: 12px;
    color: var(--text-light);
}

.order-summary {
    background: var(--main-bg);
    border-radius: 8px;
    padding: 20px;
}

.summary-items {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.item-info {
    flex: 1;
}

.item-name {
    display: block;
    font-weight: 500;
}

.item-qty {
    font-size: 14px;
    color: var(--text-light);
}

.item-price {
    font-weight: 600;
}

.summary-totals {
    border-top: 2px solid var(--border);
    padding-top: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 16px;
}

.total-row.grand-total {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
    margin-top: 10px;
    padding-top: 10px;
    border-top: 2px solid var(--border);
}

.terms-section {
    margin: 25px 0;
    display: flex;
    align-items: center;
}

.terms-section input {
    margin-right: 10px;
}

.terms-section label {
    font-size: 14px;
}

.terms-section a {
    color: var(--primary);
    text-decoration: none;
}

.terms-section a:hover {
    text-decoration: underline;
}

.submit-order-btn {
    width: 100%;
    padding: 18px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.submit-order-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 170, 255, 0.3);
}

.submit-order-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .payment-methods {
        grid-template-columns: 1fr;
    }
    
    .checkout-form {
        padding: 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CORRECCIÓN: Seleccionar TODOS los inputs de método de pago
    const metodosPago = document.querySelectorAll('input[name="metodo_pago"]');
    const tarjetaInfo = document.getElementById('tarjeta-info');
    
    // Agregar el evento a cada uno de los botones de radio
    metodosPago.forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Si el valor seleccionado es 'tarjeta', mostramos el form, sino lo ocultamos
            if (this.value === 'tarjeta') {
                tarjetaInfo.style.display = 'block';
                // Opcional: Hacer requeridos los campos de tarjeta
                document.getElementById('numero_tarjeta').required = true;
                document.getElementById('fecha_expiracion').required = true;
                document.getElementById('cvv').required = true;
            } else {
                tarjetaInfo.style.display = 'none';
                // Opcional: Quitar requeridos para que no bloquee el envío
                document.getElementById('numero_tarjeta').required = false;
                document.getElementById('fecha_expiracion').required = false;
                document.getElementById('cvv').required = false;
            }
        });
    });

    // Validar estado inicial (por si el usuario recarga y queda tarjeta seleccionada)
    const seleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    if (seleccionado && seleccionado.value === 'tarjeta') {
        tarjetaInfo.style.display = 'block';
    } else {
        tarjetaInfo.style.display = 'none';
    }
    
    // ... (Mantén el resto del código de formateo de tarjeta, fecha y CVV igual) ...
    // Formatear número de tarjeta
    const numeroTarjeta = document.getElementById('numero_tarjeta');
    // ...
});
</script>

<?php include('../includes/footer.php'); ?>