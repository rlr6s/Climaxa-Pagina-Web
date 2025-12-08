<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

// Verificar si hay un pedido reciente (simulado)
if (!isset($_SESSION['pedido_simulado'])) {
    // Si no hay pedido simulado, redirigir al dashboard
    header("Location: dashboard.php");
    exit;
}

$pedido_id = $_SESSION['pedido_simulado']['id'] ?? 'CLX-' . rand(1000, 9999);
$total = $_SESSION['pedido_simulado']['total'] ?? 0;

// Mantener el pedido en sesión para mostrar, pero podemos limpiarlo después
// unset($_SESSION['pedido_simulado']);

$page_title = "Confirmación de Pedido - CLIMAXA";
$current_page = "confirmacion.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
?>

<?php include('../includes/header.php'); ?>

<div class="main-content">
    <div class="confirmation-container">
        <div class="confirmation-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="page-title">¡Pedido Confirmado!</h1>
        <p class="page-subtitle">Gracias por tu compra en CLIMAXA</p>
        
        <div class="confirmation-details">
            <div class="detail-card">
                <h3><i class="fas fa-receipt"></i> Detalles del Pedido</h3>
                <div class="detail-row">
                    <span>Número de Pedido:</span>
                    <strong><?php echo $pedido_id; ?></strong>
                </div>
                <div class="detail-row">
                    <span>Fecha:</span>
                    <span><?php echo date('d/m/Y H:i:s'); ?></span>
                </div>
                <div class="detail-row">
                    <span>Total:</span>
                    <span class="total-amount">RD$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="detail-row">
                    <span>Estado:</span>
                    <span class="status-badge status-pendiente">En Proceso</span>
                </div>
            </div>
            
            <div class="detail-card">
                <h3><i class="fas fa-info-circle"></i> Información Importante</h3>
                <ul class="info-list">
                    <li><i class="fas fa-clock"></i> Tu pedido será procesado en las próximas 24 horas</li>
                    <li><i class="fas fa-phone"></i> Te contactaremos para coordinar la entrega</li>
                    <li><i class="fas fa-truck"></i> Tiempo de entrega estimado: 3-5 días hábiles</li>
                    <li><i class="fas fa-headset"></i> Para consultas: 829-839-1755</li>
                </ul>
            </div>
        </div>
        
        <div class="confirmation-actions">
            <a href="dashboard.php" class="action-btn primary-btn">
                <i class="fas fa-store"></i>
                Seguir Comprando
            </a>
            
            <a href="carrito.php" class="action-btn secondary-btn">
                <i class="fas fa-shopping-cart"></i>
                Ver Otros Productos
            </a>
            
            <a href="dashboard.php#servicios-section" class="action-btn tertiary-btn">
                <i class="fas fa-tools"></i>
                Ver Servicios
            </a>
        </div>
        
        <div class="whatsapp-section">
            <h3><i class="fab fa-whatsapp"></i> ¿Tienes preguntas?</h3>
            <p>Contáctanos por WhatsApp para asistencia inmediata</p>
            <a href="https://wa.me/18298391755" class="whatsapp-btn" target="_blank">
                <i class="fab fa-whatsapp"></i>
                Contactar por WhatsApp
            </a>
        </div>
    </div>
</div>

<style>
.confirmation-container {
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
    padding: 30px 20px;
}

.confirmation-icon {
    font-size: 80px;
    color: #2ecc71;
    margin: 30px 0;
    animation: bounce 1s ease;
}

.confirmation-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin: 40px 0;
}

.detail-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 25px;
    text-align: left;
}

.detail-card h3 {
    font-size: 18px;
    margin-bottom: 20px;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border);
}

.detail-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.total-amount {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
}

.status-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.status-pendiente {
    background: #ffc107;
    color: #000;
}

.info-list {
    list-style: none;
    padding: 0;
}

.info-list li {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-list li i {
    color: var(--primary);
    width: 20px;
}

.confirmation-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin: 40px 0;
    flex-wrap: wrap;
}

.action-btn {
    padding: 15px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s;
    min-width: 200px;
}

.primary-btn {
    background: var(--primary);
    color: white;
    border: none;
}

.primary-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.secondary-btn {
    background: white;
    color: var(--text-dark);
    border: 2px solid var(--border);
}

.secondary-btn:hover {
    background: var(--main-bg);
    transform: translateY(-2px);
}

.tertiary-btn {
    background: #6c757d;
    color: white;
    border: none;
}

.tertiary-btn:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.whatsapp-section {
    background: #25D366;
    color: white;
    border-radius: 12px;
    padding: 30px;
    margin-top: 40px;
}

.whatsapp-section h3 {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 24px;
    margin-bottom: 15px;
}

.whatsapp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: white;
    color: #25D366;
    padding: 15px 30px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 20px;
    transition: all 0.3s;
}

.whatsapp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-20px);
    }
    60% {
        transform: translateY(-10px);
    }
}

@media (max-width: 768px) {
    .confirmation-details {
        grid-template-columns: 1fr;
    }
    
    .confirmation-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 100%;
    }
}
</style>

<?php include('../includes/footer.php'); ?>