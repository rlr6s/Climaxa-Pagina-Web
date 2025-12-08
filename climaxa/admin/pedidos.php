<?php
// admin/pedidos.php - CORREGIDO
require_once('includes/header.php');

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$resultados = obtenerPedidos($pagina, 20);

$pedidos = $resultados['pedidos'];
$total_paginas = $resultados['total_paginas'];
$pagina_actual = $resultados['pagina_actual'];
?>

<div class="products-management">
    <!-- Header with Actions -->
    <div class="page-header">
        <div class="header-left">
            <h2>Gestión de Pedidos</h2>
            <p>Administra y monitorea todos los pedidos de la tienda</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" id="export-orders">
                <i class="fas fa-file-export"></i> Exportar
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <div class="search-box">
            <input type="text" id="search-orders" placeholder="Buscar pedidos...">
            <i class="fas fa-search"></i>
        </div>
        
        <div class="filter-options">
            <select id="filter-status">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="procesando">Procesando</option>
                <option value="enviado">Enviado</option>
                <option value="completado">Completado</option>
                <option value="cancelado">Cancelado</option>
            </select>
            
            <button class="btn btn-filter" id="apply-filters">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <button class="btn btn-clear" id="clear-filters">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Método Pago</th>
                        <th>Fecha</th>
                        <th width="150">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pedidos)): ?>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></div>
                                    <div class="customer-email"><?php echo htmlspecialchars($pedido['cliente_correo']); ?></div>
                                </div>
                            </td>
                            <td class="price-cell">
                                RD$ <?php echo number_format($pedido['total'], 2); ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                    <?php echo ucfirst($pedido['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo isset($pedido['metodo_pago']) ? ucfirst($pedido['metodo_pago']) : 'No especificado'; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($pedido['creado_en'])); ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="#" 
                                       class="btn-action btn-view view-order-btn" 
                                       data-order-id="<?php echo $pedido['id']; ?>"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <div class="status-dropdown" style="display: inline-block; position: relative;">
                                        <button class="btn-action btn-edit status-toggle" 
                                                data-order-id="<?php echo $pedido['id']; ?>"
                                                title="Cambiar estado">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <div class="status-options" style="display: none; position: absolute; background: white; border: 1px solid var(--border); border-radius: 6px; z-index: 1000; top: 100%; left: 0;">
                                            <button type="button" class="status-option" data-status="pendiente">Pendiente</button>
                                            <button type="button" class="status-option" data-status="procesando">Procesando</button>
                                            <button type="button" class="status-option" data-status="enviado">Enviado</button>
                                            <button type="button" class="status-option" data-status="completado">Completado</button>
                                            <button type="button" class="status-option" data-status="cancelado">Cancelado</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px;">
                                No hay pedidos registrados
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_paginas > 1): ?>
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=1" class="page-link first">« Primera</a>
            <a href="?pagina=<?php echo $pagina_actual - 1; ?>" class="page-link prev">‹ Anterior</a>
            <?php endif; ?>
            
            <?php 
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
            <a href="?pagina=<?php echo $i; ?>" 
               class="page-link <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo $pagina_actual + 1; ?>" class="page-link next">Siguiente ›</a>
            <a href="?pagina=<?php echo $total_paginas; ?>" class="page-link last">Última »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para ver detalles del pedido -->
<div id="order-details-modal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailsModal = document.getElementById('order-details-modal');
    
    // Abrir modal para ver detalles del pedido
    document.querySelectorAll('.view-order-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.getAttribute('data-order-id');
            
            // Cargar detalles del pedido
            cargarDetallesPedido(orderId);
        });
    });
    
    // Toggle de opciones de estado
    document.querySelectorAll('.status-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const options = this.nextElementSibling;
            options.style.display = options.style.display === 'block' ? 'none' : 'block';
        });
    });
    
    // Cambiar estado del pedido
    document.querySelectorAll('.status-option').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.closest('.status-dropdown').querySelector('.status-toggle').getAttribute('data-order-id');
            const newStatus = this.getAttribute('data-status');
            
            if (confirm(`¿Cambiar estado del pedido #${orderId} a "${newStatus}"?`)) {
                cambiarEstadoPedido(orderId, newStatus);
            }
        });
    });
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function() {
        document.querySelectorAll('.status-options').forEach(options => {
            options.style.display = 'none';
        });
    });
    
    // Cerrar modales
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function() {
            detailsModal.style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target === detailsModal) {
            detailsModal.style.display = 'none';
        }
    });
    
    // Filtros y búsqueda
    const searchInput = document.getElementById('search-orders');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            buscarPedidos();
        });
    }
    
    const applyFiltersBtn = document.getElementById('apply-filters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', buscarPedidos);
    }
    
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            document.getElementById('search-orders').value = '';
            document.getElementById('filter-status').value = '';
            buscarPedidos();
        });
    }
});

function cargarDetallesPedido(orderId) {
    fetch(`../ajax/admin.php?action=obtener_detalles_pedido&id=${orderId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = document.getElementById('order-details-modal');
            const content = document.getElementById('order-details-content');
            
            let html = `
                <div class="order-details">
                    <div class="order-header">
                        <h4>Pedido #${data.pedido.id}</h4>
                        <span class="status-badge status-${data.pedido.estado}">
                            ${data.pedido.estado}
                        </span>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-section">
                            <h5>Información del Cliente</h5>
                            <p><strong>Nombre:</strong> ${data.pedido.cliente_nombre}</p>
                            <p><strong>Correo:</strong> ${data.pedido.cliente_correo}</p>
                            <p><strong>Teléfono:</strong> ${data.pedido.telefono || 'No especificado'}</p>
                        </div>
                        
                        <div class="info-section">
                            <h5>Dirección de Envío</h5>
                            <p>${data.pedido.direccion || 'No especificado'}</p>
                        </div>
                        
                        <div class="info-section">
                            <h5>Información del Pedido</h5>
                            <p><strong>Fecha:</strong> ${data.pedido.fecha}</p>
                            <p><strong>Método de Pago:</strong> ${data.pedido.metodo_pago || 'No especificado'}</p>
                            <p><strong>Notas:</strong> ${data.pedido.notas || 'Ninguna'}</p>
                        </div>
                    </div>`;
            
            if (data.detalles && data.detalles.length > 0) {
                html += `
                    <div class="order-items">
                        <h5>Productos</h5>
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                data.detalles.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.nombre} (${item.marca || 'Sin marca'})</td>
                            <td>${item.cantidad}</td>
                            <td>RD$ ${parseFloat(item.precio_unitario).toFixed(2)}</td>
                            <td>RD$ ${(item.cantidad * item.precio_unitario).toFixed(2)}</td>
                        </tr>`;
                });
                
                html += `
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                    <td><strong>RD$ ${parseFloat(data.pedido.total).toFixed(2)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>`;
            } else {
                html += `<p>No hay detalles disponibles para este pedido.</p>`;
            }
            
            html += `</div>`;
            
            content.innerHTML = html;
            detailsModal.style.display = 'block';
        } else {
            alert('Error al cargar los detalles del pedido: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al cargar los detalles del pedido');
    });
}

function cambiarEstadoPedido(orderId, newStatus) {
    const formData = new FormData();
    formData.append('pedido_id', orderId);
    formData.append('nuevo_estado', newStatus);
    formData.append('action', 'actualizar_estado_pedido');
    
    fetch('../ajax/admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Estado actualizado exitosamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al actualizar el estado');
    });
}

function buscarPedidos() {
    const search = document.getElementById('search-orders').value;
    const status = document.getElementById('filter-status').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (status) params.append('status', status);
    
    window.location.href = `?${params.toString()}`;
}
</script>

<style>
.customer-info {
    padding: 5px 0;
}

.customer-name {
    font-weight: 500;
    margin-bottom: 5px;
}

.customer-email {
    font-size: 12px;
    color: var(--gray);
}

.status-dropdown {
    position: relative;
    display: inline-block;
}

.status-options {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 6px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    min-width: 150px;
    z-index: 1000;
}

.status-option {
    display: block;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.status-option:hover {
    background: var(--light-gray);
}

.order-details {
    padding: 10px;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border);
}

.info-section {
    margin-bottom: 20px;
    padding: 15px;
    background: var(--light-gray);
    border-radius: 6px;
}

.info-section h5 {
    margin-bottom: 10px;
    color: var(--dark);
    font-size: 16px;
}

.order-items table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.order-items th,
.order-items td {
    padding: 10px;
    border: 1px solid var(--border);
    text-align: left;
}

.order-items th {
    background: var(--light-gray);
    font-weight: 600;
}

.order-items tfoot tr {
    background: var(--light-gray);
    font-weight: bold;
}
</style>

<?php include('includes/footer.php'); ?>