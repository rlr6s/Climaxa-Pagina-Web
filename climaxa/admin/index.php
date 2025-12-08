<?php
// admin/index.php - MEJORADO
require_once('includes/header.php');

$estadisticas = obtenerEstadisticas();
$pedidos_recientes = obtenerPedidosRecientes(5);
$productos_mas_vendidos = obtenerProductosMasVendidos(5);
?>

<div class="dashboard">
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon sales">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h3>Ventas Totales</h3>
                <p class="stat-number">RD$ <?php echo number_format($estadisticas['total_ventas'] ?? 0, 2); ?></p>
                <p class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    Ventas del mes: RD$ <?php echo number_format($estadisticas['ventas_mes_actual'] ?? 0, 2); ?>
                </p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3>Pedidos</h3>
                <p class="stat-number"><?php echo $estadisticas['total_pedidos'] ?? 0; ?></p>
                <p class="stat-change">
                    <span class="pending"><?php echo $estadisticas['pedidos_pendientes'] ?? 0; ?> pendientes</span>
                </p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon products">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <h3>Productos</h3>
                <p class="stat-number"><?php echo $estadisticas['total_productos'] ?? 0; ?></p>
                <p class="stat-change negative">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $estadisticas['productos_bajo_stock'] ?? 0; ?> con stock bajo
                </p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon users">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Usuarios</h3>
                <p class="stat-number"><?php echo $estadisticas['total_usuarios'] ?? 0; ?></p>
                <p class="stat-change">
                    <i class="fas fa-user-plus"></i>
                    <?php echo $estadisticas['nuevos_usuarios_mes'] ?? 0; ?> nuevos este mes
                </p>
            </div>
        </div>
    </div>
    
    <!-- Charts and Recent Activity -->
    <div class="content-row">
        <!-- Sales Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3>Ventas Mensuales</h3>
                <select id="chart-year" onchange="cargarGraficoVentas(this.value)">
                    <?php
                    $anio_actual = date('Y');
                    for ($i = $anio_actual; $i >= $anio_actual - 3; $i--) {
                        echo "<option value='$i'" . ($i == $anio_actual ? ' selected' : '') . ">$i</option>";
                    }
                    ?>
                </select>
            </div>
            <canvas id="salesChart"></canvas>
        </div>
        
        <!-- Recent Orders -->
        <div class="recent-orders">
            <div class="table-header">
                <h3>Pedidos Recientes</h3>
                <a href="pedidos.php" class="view-all">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pedidos_recientes)): ?>
                            <?php foreach ($pedidos_recientes as $pedido): ?>
                            <tr>
                                <td>#<?php echo isset($pedido['id']) ? str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($pedido['cliente_nombre'] ?? 'Cliente desconocido'); ?></td>
                                <td>RD$ <?php echo isset($pedido['total']) ? number_format($pedido['total'], 2) : '0.00'; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $pedido['estado'] ?? 'pendiente'; ?>">
                                        <?php echo ucfirst($pedido['estado'] ?? 'Pendiente'); ?>
                                    </span>
                                </td>
                                <td><?php echo isset($pedido['creado_en']) ? date('d/m/Y H:i', strtotime($pedido['creado_en'])) : 'N/A'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">
                                    No hay pedidos recientes
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Best Selling Products -->
    <div class="content-row">
        <div class="best-sellers">
            <div class="table-header">
                <h3>Productos Más Vendidos</h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Ventas</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($productos_mas_vendidos)): ?>
                            <?php foreach ($productos_mas_vendidos as $producto): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <?php if (!empty($producto['imagen'])): ?>
                                        <img src="../assets/img/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                             alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                             width="40" style="border-radius: 4px; margin-right: 10px;">
                                        <?php endif; ?>
                                        <div>
                                            <div class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                            <div class="product-brand"><?php echo htmlspecialchars($producto['marca']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $producto['total_vendido']; ?> unidades</td>
                                <td>RD$ <?php echo number_format($producto['ingresos_totales'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 20px;">
                                    No hay datos de productos vendidos
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar gráfico de ventas
function cargarGraficoVentas(anio) {
    fetch(`../ajax/admin.php?action=ventas_por_mes&anio=${anio}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderSalesChart(data.ventas, anio);
        } else {
            console.error('Error al cargar ventas:', data.message);
        }
    })
    .catch(error => {
        console.error('Error de red:', error);
    });
}

function renderSalesChart(ventas, anio) {
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    const datos = new Array(12).fill(0);
    
    // Llenar arrays con datos
    if (ventas) {
        Object.keys(ventas).forEach(mes => {
            const index = parseInt(mes) - 1;
            if (index >= 0 && index < 12) {
                datos[index] = ventas[mes].total_ventas || 0;
            }
        });
    }
    
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    if (window.salesChart instanceof Chart) {
        window.salesChart.destroy();
    }
    
    window.salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ventas (RD$)',
                data: datos,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: `Ventas ${anio}`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RD$ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

// Cargar gráfico inicial
document.addEventListener('DOMContentLoaded', function() {
    cargarGraficoVentas(<?php echo date('Y'); ?>);
    
    // Toggle sidebar en móvil
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            document.querySelector('.admin-container').classList.toggle('sidebar-collapsed');
        });
    }
});
</script>

<style>
.product-info {
    display: flex;
    align-items: center;
}

.product-name {
    font-weight: 500;
    margin-bottom: 2px;
}

.product-brand {
    font-size: 12px;
    color: #666;
}
</style>

<?php include('includes/footer.php'); ?>