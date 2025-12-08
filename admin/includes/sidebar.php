<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>CLIMAXA</h2>
        <p>Panel de Administración</p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="productos.php" class="<?php echo $current_page == 'productos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Productos</span>
                </a>
            </li>
            
            <li>
                <a href="usuarios.php" class="<?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </a>
            </li>
            
            <li>
                <a href="pedidos.php" class="<?php echo $current_page == 'pedidos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Pedidos</span>
                    <span class="badge" id="pedidos-pendientes">0</span>
                </a>
            </li>
            
            <li>
                <a href="estadisticas.php" class="<?php echo $current_page == 'estadisticas.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Estadísticas</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="../logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </nav>
</aside>

<script>
// Actualizar contador de pedidos pendientes
function actualizarPedidosPendientes() {
    fetch('../ajax/admin.php?action=contar_pedidos_pendientes')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('pedidos-pendientes');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'inline-flex' : 'none';
            }
        }
    });
}

// Actualizar cada 30 segundos
setInterval(actualizarPedidosPendientes, 30000);
document.addEventListener('DOMContentLoaded', actualizarPedidosPendientes);
</script>