<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/funciones.php");

$usuario_id = $_SESSION['usuario_id'];
$pedidos = obtenerPedidosUsuario($usuario_id);

$page_title = "Mi Historial de Pedidos - CLIMAXA";
$current_page = "historial_pedidos.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
?>

<?php include('../includes/header.php'); ?>

<div class="main-content">
    <div class="back-container">
        <a href="perfil.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver a Mi Perfil
        </a>
    </div>

    <h1 class="page-title">Mi Historial de Pedidos</h1>
    <p class="page-subtitle">Revisa todos tus pedidos realizados en CLIMAXA</p>

    <?php if (!empty($pedidos)): ?>
        <div class="orders-history">
            <div class="history-filters">
                <input type="text" placeholder="Buscar por número de pedido..." id="search-orders">
                <select id="filter-status">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="procesando">Procesando</option>
                    <option value="enviado">Enviado</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <button class="btn btn-primary" id="apply-filters">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>

            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>N° Pedido</th>
                            <th>Fecha</th>
                            <th>Productos</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): 
                            // Usar la nueva función obtenerDetallePedido
                            $detalles = obtenerDetallePedido($pedido['id']);
                        ?>
                            <tr>
                                <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pedido['creado_en'])); ?></td>
                                <td>
                                    <div class="order-products">
                                        <?php if (!empty($detalles)): ?>
                                            <?php foreach ($detalles as $detalle): ?>
                                                <span class="product-badge"><?php echo $detalle['cantidad']; ?>x <?php echo htmlspecialchars($detalle['nombre']); ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="product-badge">Sin detalles</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="price">RD$ <?php echo number_format($pedido['total'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                        <?php echo ucfirst($pedido['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary view-order-details" 
                                            data-order-id="<?php echo $pedido['id']; ?>">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </button>
                                    <?php if ($pedido['estado'] == 'completado'): ?>
                                        <button class="btn btn-sm btn-secondary reorder-btn" 
                                                data-order-id="<?php echo $pedido['id']; ?>">
                                            <i class="fas fa-redo"></i> Volver a Pedir
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-history">
            <i class="fas fa-shopping-bag"></i>
            <h3>No tienes pedidos aún</h3>
            <p>¡Comienza a comprar ahora!</p>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-store"></i> Ir a la Tienda
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para ver detalles del pedido -->
<div id="order-details-modal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="modal-order-title">Detalles del Pedido</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="order-details-content">
                <!-- Los detalles se cargarán aquí via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
.orders-history {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 25px;
    margin-top: 20px;
}

.history-filters {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.history-filters input,
.history-filters select {
    padding: 10px 15px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

.history-filters input {
    flex: 1;
}

.orders-table {
    overflow-x: auto;
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table th {
    background: var(--light-gray);
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: var(--text-dark);
    border-bottom: 2px solid var(--border);
}

.orders-table td {
    padding: 15px;
    border-bottom: 1px solid var(--border);
}

.order-products {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    max-width: 300px;
}

.product-badge {
    background: var(--light-gray);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
}

.orders-table .price {
    font-weight: 700;
    color: var(--text-dark);
}

.empty-history {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.empty-history i {
    font-size: 60px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-history h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: var(--text-dark);
}

.empty-history p {
    color: var(--text-light);
    margin-bottom: 30px;
    font-size: 16px;
}

/* Estilos para el modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-dark);
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--gray);
}

.modal-body {
    padding: 25px;
}

/* Estilos para los badges de estado */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pendiente {
    background: rgba(243, 156, 18, 0.1);
    color: #f39c12;
}

.status-procesando {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.status-enviado {
    background: rgba(155, 89, 182, 0.1);
    color: #9b59b6;
}

.status-completado {
    background: rgba(46, 204, 113, 0.1);
    color: #2ecc71;
}

.status-cancelado {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
}

.btn {
    padding: 8px 15px;
    border-radius: 6px;
    border: none;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
    text-decoration: none;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

.btn-secondary {
    background: var(--light-gray);
    color: var(--dark);
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: #e9ecef;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

@media (max-width: 768px) {
    .history-filters {
        flex-direction: column;
    }
    
    .orders-table th,
    .orders-table td {
        padding: 10px;
        font-size: 14px;
    }
    
    .order-products {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad para "Volver a Pedir"
    document.querySelectorAll('.reorder-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            if (confirm('¿Volver a pedir los productos de este pedido?')) {
                reordenarPedidoAjax(orderId);
            }
        });
    });
    
    // Filtros
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            const search = document.getElementById('search-orders').value.toLowerCase();
            const status = document.getElementById('filter-status').value;
            
            const rows = document.querySelectorAll('.orders-table tbody tr');
            
            rows.forEach(row => {
                const orderNumber = row.cells[0].textContent.toLowerCase();
                const orderStatus = row.querySelector('.status-badge').className.includes(status) || status === '';
                
                if ((orderNumber.includes(search) || search === '') && orderStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Ver detalles del pedido
    document.querySelectorAll('.view-order-details').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            verDetallesPedido(orderId);
        });
    });
    
    // Cerrar modal
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('order-details-modal').style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('order-details-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});

function reordenarPedidoAjax(orderId) {
    // Crear un endpoint AJAX para reordenar
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('action', 'reordenar_pedido');
    
    fetch('../ajax/perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Productos agregados al carrito exitosamente');
            // Redirigir al carrito
            window.location.href = 'carrito.php';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

function verDetallesPedido(orderId) {
    fetch('../ajax/perfil.php?action=obtener_detalles_pedido&pedido_id=' + orderId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarModalDetalles(data.pedido, data.detalles);
        } else {
            alert('Error al cargar detalles: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

function mostrarModalDetalles(pedido, detalles) {
    const modal = document.getElementById('order-details-modal');
    const content = document.getElementById('order-details-content');
    const title = document.getElementById('modal-order-title');
    
    title.textContent = `Pedido #${String(pedido.id).padStart(6, '0')}`;
    
    let html = `
        <div class="order-details-container">
            <div class="order-info-section">
                <h4>Información del Pedido</h4>
                <p><strong>Fecha:</strong> ${new Date(pedido.creado_en).toLocaleDateString('es-ES')}</p>
                <p><strong>Total:</strong> RD$ ${parseFloat(pedido.total).toFixed(2)}</p>
                <p><strong>Estado:</strong> <span class="status-badge status-${pedido.estado}">${pedido.estado}</span></p>
                <p><strong>Método de pago:</strong> ${pedido.metodo_pago || 'No especificado'}</p>
            </div>`;
    
    if (detalles && detalles.length > 0) {
        html += `
            <div class="order-items-section">
                <h4>Productos</h4>
                <div class="order-items-list">`;
        
        detalles.forEach(item => {
            html += `
                <div class="order-item">
                    <div class="item-info">
                        <div class="item-name">${item.nombre}</div>
                        <div class="item-brand">${item.marca || 'Sin marca'}</div>
                    </div>
                    <div class="item-details">
                        <span class="item-quantity">${item.cantidad} x RD$ ${parseFloat(item.precio_unitario).toFixed(2)}</span>
                        <span class="item-subtotal">RD$ ${(item.cantidad * item.precio_unitario).toFixed(2)}</span>
                    </div>
                </div>`;
        });
        
        html += `</div></div>`;
    }
    
    html += `</div>`;
    
    content.innerHTML = html;
    modal.style.display = 'flex';
    
    // Agregar estilos para el modal
    if (!document.querySelector('#modal-details-styles')) {
        const style = document.createElement('style');
        style.id = 'modal-details-styles';
        style.textContent = `
            .order-details-container {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            .order-info-section, .order-items-section {
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
            }
            .order-info-section h4, .order-items-section h4 {
                margin-bottom: 15px;
                color: #333;
                border-bottom: 2px solid #007bff;
                padding-bottom: 8px;
            }
            .order-info-section p {
                margin-bottom: 8px;
                display: flex;
                gap: 10px;
            }
            .order-info-section p strong {
                min-width: 150px;
            }
            .order-items-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            .order-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px;
                background: white;
                border-radius: 6px;
                border: 1px solid #dee2e6;
            }
            .item-info {
                flex: 1;
            }
            .item-name {
                font-weight: 500;
                margin-bottom: 5px;
            }
            .item-brand {
                font-size: 12px;
                color: #6c757d;
            }
            .item-details {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 5px;
            }
            .item-quantity {
                font-size: 14px;
                color: #495057;
            }
            .item-subtotal {
                font-weight: 600;
                color: #007bff;
            }
        `;
        document.head.appendChild(style);
    }
}
</script>

<?php include('../includes/footer.php'); ?>