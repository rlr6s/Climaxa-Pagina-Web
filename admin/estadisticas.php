<?php
// admin/estadisticas.php
require_once('includes/header.php');

require_once('includes/funciones.php');

// Verificar si se solicita exportación
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    exportarEstadisticasExcel();
    exit;
}

$anio_actual = date('Y');
$anio_seleccionado = isset($_GET['anio']) ? intval($_GET['anio']) : $anio_actual;
$ventas_por_mes = obtenerVentasPorMes($anio_seleccionado);
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
                <?php
                $total_ventas = 0;
                foreach ($ventas_por_mes as $mes) {
                    $total_ventas += $mes['total_ventas'];
                }
                ?>
                <p class="stat-number">RD$ <?php echo number_format($total_ventas, 2); ?></p>
                <p class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    Año <?php echo $anio_seleccionado; ?>
                </p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3>Pedidos Totales</h3>
                <?php
                $total_pedidos = 0;
                foreach ($ventas_por_mes as $mes) {
                    $total_pedidos += $mes['total_pedidos'];
                }
                ?>
                <p class="stat-number"><?php echo number_format($total_pedidos); ?></p>
                <p class="stat-change">
                    <span class="positive">Promedio: <?php echo number_format($total_pedidos / 12, 1); ?>/mes</span>
                </p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon users">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3>Valor Promedio</h3>
                <?php
                $valor_promedio = $total_pedidos > 0 ? $total_ventas / $total_pedidos : 0;
                ?>
                <p class="stat-number">RD$ <?php echo number_format($valor_promedio, 2); ?></p>
                <p class="stat-change">
                    <i class="fas fa-shopping-bag"></i>
                    Por pedido
                </p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon products">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-info">
                <h3>Mes con Más Ventas</h3>
                <?php
                $mes_max = 0;
                $ventas_max = 0;
                foreach ($ventas_por_mes as $mes => $datos) {
                    if ($datos['total_ventas'] > $ventas_max) {
                        $ventas_max = $datos['total_ventas'];
                        $mes_max = $mes;
                    }
                }
                $nombres_meses = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];
                ?>
                <p class="stat-number"><?php echo $nombres_meses[$mes_max] ?? 'N/A'; ?></p>
                <p class="stat-change positive">
                    <i class="fas fa-trophy"></i>
                    RD$ <?php echo number_format($ventas_max, 2); ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Sales Chart -->
    <div class="chart-container" style="margin-bottom: 30px;">
        <div class="chart-header">
            <h3>Ventas Mensuales - <?php echo $anio_seleccionado; ?></h3>
            <div class="chart-controls">
                <select id="chart-year" onchange="cambiarAnio(this.value)">
                    <?php for ($i = $anio_actual; $i >= $anio_actual - 5; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $anio_seleccionado ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                    <?php endfor; ?>
                </select>
                <a href="?export=excel&anio=<?php echo $anio_seleccionado; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </a>
            </div>
        </div>
        <canvas id="salesChart" height="100"></canvas>
    </div>
    
    <!-- Monthly Breakdown -->
    <div class="content-row">
        <div class="monthly-breakdown">
            <div class="table-header">
                <h3>Desglose Mensual</h3>
                <a href="?export=excel&anio=<?php echo $anio_seleccionado; ?>" class="view-all">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Pedidos</th>
                            <th>Ventas (RD$)</th>
                            <th>Promedio por Pedido</th>
                            <th>Tendencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nombres_meses_corto = [
                            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
                            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
                        ];
                        
                        $mes_anterior = 0;
                        foreach ($ventas_por_mes as $mes => $datos):
                            $promedio = $datos['total_pedidos'] > 0 ? $datos['total_ventas'] / $datos['total_pedidos'] : 0;
                            
                            // Calcular tendencia
                            $tendencia = 'neutral';
                            if ($mes_anterior > 0) {
                                $tendencia = $datos['total_ventas'] > $mes_anterior ? 'positive' : 
                                           ($datos['total_ventas'] < $mes_anterior ? 'negative' : 'neutral');
                            }
                            $mes_anterior = $datos['total_ventas'];
                        ?>
                        <tr>
                            <td><?php echo $nombres_meses_corto[$mes]; ?></td>
                            <td><?php echo $datos['total_pedidos']; ?></td>
                            <td>RD$ <?php echo number_format($datos['total_ventas'], 2); ?></td>
                            <td>RD$ <?php echo number_format($promedio, 2); ?></td>
                            <td>
                                <?php if ($tendencia == 'positive'): ?>
                                <i class="fas fa-arrow-up text-success"></i>
                                <?php elseif ($tendencia == 'negative'): ?>
                                <i class="fas fa-arrow-down text-danger"></i>
                                <?php else: ?>
                                <i class="fas fa-minus text-muted"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td><strong><?php echo number_format($total_pedidos); ?></strong></td>
                            <td><strong>RD$ <?php echo number_format($total_ventas, 2); ?></strong></td>
                            <td><strong>RD$ <?php echo number_format($valor_promedio, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="content-row">
        <div class="best-sellers">
            <div class="table-header">
                <h3>Productos Más Vendidos - <?php echo $anio_seleccionado; ?></h3>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidades Vendidas</th>
                            <th>Ingresos</th>
                            <th>% del Total</th>
                        </tr>
                    </thead>
                    <tbody id="top-products-table">
                        <!-- Los productos se cargarán via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Datos para el gráfico
const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
const datosVentas = [
    <?php
    for ($i = 1; $i <= 12; $i++) {
        echo ($ventas_por_mes[$i]['total_ventas'] ?? 0) . ($i < 12 ? ', ' : '');
    }
    ?>
];
const datosPedidos = [
    <?php
    for ($i = 1; $i <= 12; $i++) {
        echo ($ventas_por_mes[$i]['total_pedidos'] ?? 0) . ($i < 12 ? ', ' : '');
    }
    ?>
];

// Inicializar gráfico
let salesChart;
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [
                {
                    label: 'Ventas (RD$)',
                    data: datosVentas,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Pedidos',
                    data: datosPedidos,
                    type: 'line',
                    backgroundColor: 'rgba(231, 76, 60, 0.2)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Ventas (RD$)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'RD$ ' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Número de Pedidos'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return 'Ventas: RD$ ' + context.parsed.y.toLocaleString();
                            } else {
                                return 'Pedidos: ' + context.parsed.y;
                            }
                        }
                    }
                },
                legend: {
                    position: 'top',
                }
            }
        }
    });
    
    // Cargar productos más vendidos
    cargarProductosMasVendidos(<?php echo $anio_seleccionado; ?>);
});

function cambiarAnio(anio) {
    window.location.href = `?anio=${anio}`;
}

function exportChart() {
    const link = document.createElement('a');
    link.download = `ventas-${<?php echo $anio_seleccionado; ?>}.png`;
    link.href = document.getElementById('salesChart').toDataURL();
    link.click();
}

function exportMonthlyData() {
    // En un sistema real, esto generaría un archivo Excel
    alert('Esta función exportaría los datos a Excel. En producción, implementar con una librería como SheetJS.');
}

function cargarProductosMasVendidos(anio) {
    fetch(`../ajax/admin.php?action=obtener_productos_mas_vendidos&anio=${anio}&limite=10`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tbody = document.getElementById('top-products-table');
            let html = '';
            
            // Calcular total de ingresos
            let totalIngresos = 0;
            data.productos.forEach(producto => {
                totalIngresos += parseFloat(producto.ingresos_totales);
            });
            
            data.productos.forEach(producto => {
                const porcentaje = totalIngresos > 0 ? (producto.ingresos_totales / totalIngresos * 100).toFixed(1) : 0;
                
                html += `
                    <tr>
                        <td>
                            <div class="product-info">
                                <div class="product-name">${producto.nombre}</div>
                                <div class="product-brand">${producto.marca}</div>
                            </div>
                        </td>
                        <td>
                            <span class="category-badge">${producto.categoria || 'General'}</span>
                        </td>
                        <td>${producto.total_vendido} unidades</td>
                        <td>RD$ ${parseFloat(producto.ingresos_totales).toLocaleString('es-DO', {minimumFractionDigits: 2})}</td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: ${porcentaje}%"></div>
                                <span>${porcentaje}%</span>
                            </div>
                        </td>
                    </tr>`;
            });
            
            tbody.innerHTML = html;
        }
    });
}
</script>

<style>
.chart-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 14px;
}

.monthly-breakdown {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    width: 100%;
}

.total-row {
    background: var(--light-gray);
    font-weight: bold;
}

.text-success {
    color: var(--success) !important;
}

.text-danger {
    color: var(--danger) !important;
}

.text-muted {
    color: var(--gray) !important;
}

.progress-bar-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-bar {
    height: 8px;
    background: var(--primary);
    border-radius: 4px;
    flex-grow: 1;
}
</style>

<?php include('includes/footer.php'); ?>