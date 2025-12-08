<?php
// Determinar página activa
$current_page = isset($current_page) ? $current_page : basename($_SERVER['PHP_SELF']);
?>
<!-- Navigation Bar -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <?php 
            if (file_exists(__DIR__ . '/../assets/img/logo2.png')) {
                echo '<img src="../assets/img/logo2.png" alt="CLIMAXA" class="logo">';
            } elseif (file_exists(__DIR__ . '/../assets/img/logo.png')) {
                echo '<img src="../assets/img/logo.png" alt="CLIMAXA" class="logo">';
            } else {
                echo '<h2 style="font-size: 18px; color: var(--primary);">CLIMAXA</h2>';
            }
            ?>
        </div>
        
        <div class="nav-search">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="<?php echo isset($search_placeholder) ? $search_placeholder : '¿Qué producto buscas?'; ?>">
            </div>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Inicio</a>
            <a href="aires.php" class="nav-link <?php echo ($current_page == 'aires.php') ? 'active' : ''; ?>">Aires</a>
            <a href="freezers.php" class="nav-link <?php echo ($current_page == 'freezers.php') ? 'active' : ''; ?>">Freezers</a>
            <a href="neveras.php" class="nav-link <?php echo ($current_page == 'neveras.php') ? 'active' : ''; ?>">Neveras</a>
        </div>
        
        <div class="nav-actions">
            <!-- UN SOLO ÍCONO DE CARRITO CON CONTADOR -->
            <div class="cart-icon-container" style="position: relative; margin-right: 15px;">
                <a href="carrito.php" class="nav-link" style="display: flex; align-items: center; padding: 8px 12px; border-radius: 6px; transition: background 0.3s;">
                    <i class="fas fa-shopping-cart" style="font-size: 20px; color: var(--text-dark);"></i>
                    <span id="carrito-contador" style="
                        background: #e74c3c;
                        color: white;
                        border-radius: 50%;
                        width: 20px;
                        height: 20px;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        font-size: 12px;
                        position: absolute;
                        top: -5px;
                        right: -5px;
                    ">0</span>
                </a>
            </div>
            
<a href="perfil.php" class="user-profile-link" style="text-decoration: none; display: flex; align-items: center; gap: 15px; cursor: pointer; padding: 5px 10px; border-radius: 8px; transition: background 0.3s;">
    <div class="user-avatar">
        <?php echo isset($_SESSION['usuario_nombre']) ? strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)) : 'U'; ?>
    </div>
    <div class="user-info">
        <span class="user-name"><?php echo isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Usuario'; ?></span>
        <span class="user-role" style="font-size: 12px; color: var(--text-light);">
 
        </span>
    </div>
</a>

            
            <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<script>
// Inicializar contador del carrito
document.addEventListener('DOMContentLoaded', function() {
    actualizarContadorCarrito();
});

function actualizarContadorCarrito() {
    fetch('../ajax/carrito.php?action=contar')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const contador = document.getElementById('carrito-contador');
            if (contador) {
                contador.textContent = data.count;
                contador.style.display = data.count > 0 ? 'flex' : 'none';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>