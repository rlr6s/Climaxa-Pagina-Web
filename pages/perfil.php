<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit;
}

require_once("../includes/funciones.php");

$usuario_id = $_SESSION['usuario_id'];

// Obtener información del usuario
$conn = getDBConnection();
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Obtener pedidos recientes
$pedidos_recientes = obtenerPedidosUsuario($usuario_id, 5);

// Obtener lista de deseos
$lista_deseos = obtenerListaDeseos($usuario_id);

$page_title = "Mi Perfil - CLIMAXA";
$current_page = "perfil.php";
$is_protected = true;
$show_navbar = true;
$show_footer = true;
$css_files = ['../assets/css/dashboard.css', '../assets/css/products.css'];
$js_files = ['../assets/js/perfil.js'];
?>

<?php include('../includes/header.php'); ?>

<div class="main-content">
    <div class="back-container">
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver a la pantalla principal
        </a>
    </div>

    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($usuario['avatar'])): ?>
                        <img src="../assets/uploads/avatars/<?php echo $usuario['avatar']; ?>" 
                             alt="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                    <?php else: ?>
                        <div class="avatar-default">
                            <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                    <p class="profile-email"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                    <p class="profile-role">
                        <span class="role-badge <?php echo $usuario['rol']; ?>">
                            <?php echo $usuario['rol'] == 'admin' ? 'Administrador' : 'Usuario'; ?>
                        </span>
                    </p>
                </div>
            </div>

            <nav class="profile-nav">
                <a href="#datos" class="nav-link active">
                    <i class="fas fa-user"></i> Mis Datos
                </a>
                <a href="#pedidos" class="nav-link">
                    <i class="fas fa-shopping-bag"></i> Mis Pedidos
                </a>
                <a href="#deseos" class="nav-link">
                    <i class="fas fa-heart"></i> Lista de Deseos
                    <span class="badge"><?php echo count($lista_deseos); ?></span>
                </a>
                <a href="#seguridad" class="nav-link">
                    <i class="fas fa-lock"></i> Seguridad
                </a>
                <a href="../logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </div>

        <div class="profile-content">
            <!-- Sección: Mis Datos -->
            <section id="datos" class="profile-section active">
                <div class="section-header">
                    <h3><i class="fas fa-user-edit"></i> Mis Datos Personales</h3>
                    <button class="btn btn-primary" id="btn-editar-datos">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </div>

                <form id="form-datos" class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono" 
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" 
                                   placeholder="809-555-5555" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Dirección</label>
                        <textarea name="direccion" rows="2" readonly><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Ciudad</label>
                            <input type="text" name="ciudad" 
                                   value="<?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?>" 
                                   readonly>
                        </div>
                        <div class="form-group">
                            <label>Provincia</label>
                            <input type="text" name="provincia" 
                                   value="<?php echo htmlspecialchars($usuario['provincia'] ?? ''); ?>" 
                                   readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Foto de Perfil</label>
                        <div class="avatar-upload">
                            <div class="avatar-preview">
                                <?php if (!empty($usuario['avatar'])): ?>
                                    <img src="../assets/uploads/avatars/<?php echo $usuario['avatar']; ?>" 
                                         id="avatar-preview">
                                <?php else: ?>
                                    <div class="avatar-default-preview" id="avatar-preview">
                                        <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-secondary" id="btn-cambiar-avatar">
                                <i class="fas fa-camera"></i> Cambiar Foto
                            </button>
                        </div>
                    </div>

                    <div class="form-actions" style="display: none;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-cancelar">
                            Cancelar
                        </button>
                    </div>
                </form>
            </section>

            <!-- Sección: Mis Pedidos -->
            <section id="pedidos" class="profile-section">
                <div class="section-header">
                    <h3><i class="fas fa-shopping-bag"></i> Mis Pedidos</h3>
                    <a href="historial_pedidos.php" class="btn btn-primary">
                        <i class="fas fa-history"></i> Ver Historial Completo
                    </a>
                </div>

                <?php if (!empty($pedidos_recientes)): ?>
                    <div class="orders-list">
                        <?php foreach ($pedidos_recientes as $pedido): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h4>Pedido #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></h4>
                                        <span class="order-date">
                                            <?php echo date('d/m/Y', strtotime($pedido['creado_en'])); ?>
                                        </span>
                                    </div>
                                    <div class="order-status">
                                        <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                            <?php echo ucfirst($pedido['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-body">
                                    <div class="order-total">
                                        <strong>Total: </strong> RD$ <?php echo number_format($pedido['total'], 2); ?>
                                    </div>
                                    <div class="order-actions">

                                        <?php if ($pedido['estado'] == 'completado'): ?>
                                            <button class="btn btn-sm btn-secondary reorder-btn" 
                                                    data-order-id="<?php echo $pedido['id']; ?>">
                                                <i class="fas fa-redo"></i> Volver a Pedir
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h4>No tienes pedidos aún</h4>
                        <p>¡Comienza a comprar ahora!</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-store"></i> Ir a la Tienda
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Sección: Lista de Deseos -->
            <section id="deseos" class="profile-section">
                <div class="section-header">
                    <h3><i class="fas fa-heart"></i> Mi Lista de Deseos</h3>
                </div>

                <?php if (!empty($lista_deseos)): ?>
                    <div class="wishlist-grid">
                        <?php foreach ($lista_deseos as $producto): ?>
                            <div class="wishlist-item">
                                <div class="product-image">
                                    <img src="../assets/img/<?php echo $producto['imagen'] ?? 'producto-default.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                </div>
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                    <p class="product-brand"><?php echo htmlspecialchars($producto['marca']); ?></p>
                                    <div class="product-price">
                                        RD$ <?php echo number_format($producto['precio'], 2); ?>
                                    </div>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-primary add-to-cart" 
                                            data-product-id="<?php echo $producto['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                    </button>
                                    <button class="btn btn-danger remove-wishlist" 
                                            data-product-id="<?php echo $producto['id']; ?>">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-heart"></i>
                        <h4>Tu lista de deseos está vacía</h4>
                        <p>Agrega productos que te gusten para comprarlos después</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-store"></i> Explorar Productos
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Sección: Seguridad -->
            <section id="seguridad" class="profile-section">
                <div class="section-header">
                    <h3><i class="fas fa-lock"></i> Seguridad</h3>
                </div>

                <div class="security-forms">
                    <!-- Formulario para cambiar contraseña -->
                    <form id="form-cambiar-contrasena" class="security-form">
                        <h4>Cambiar Contraseña</h4>
                        
                        <div class="form-group">
                            <label>Contraseña Actual</label>
                            <div class="password-input">
                                <input type="password" name="contrasena_actual" required>
                                <button type="button" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nueva Contraseña</label>
                            <div class="password-input">
                                <input type="password" name="nueva_contrasena" required>
                                <button type="button" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="password-hint">
                                Mínimo 8 caracteres, con mayúsculas, minúsculas y números
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Confirmar Nueva Contraseña</label>
                            <div class="password-input">
                                <input type="password" name="confirmar_contrasena" required>
                                <button type="button" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </form>

                    <!-- Configuraciones de seguridad -->
                    <div class="security-settings">
                        <h4>Configuraciones de Seguridad</h4>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h5>Notificaciones por Correo</h5>
                                <p>Recibir notificaciones sobre tu cuenta y pedidos</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h5>Verificación en Dos Pasos</h5>
                                <p>Añade una capa extra de seguridad a tu cuenta</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="setting-item">
                            <div class="setting-info">
                                <h5>Sesión Activa</h5>
                                <p>Tu sesión actual comenzó el <?php echo date('d/m/Y H:i'); ?></p>
                            </div>
                            <a href="../logout.php" class="btn btn-sm btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Todas las Sesiones
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
/* Estilos para la página de perfil */
.profile-container {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.profile-sidebar {
    width: 300px;
    flex-shrink: 0;
}

.profile-content {
    flex: 1;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 30px;
}

.profile-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 25px;
    text-align: center;
    margin-bottom: 20px;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--primary);
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-default {
    width: 100%;
    height: 100%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: bold;
}

.profile-info h2 {
    font-size: 24px;
    margin-bottom: 5px;
    color: var(--text-dark);
}

.profile-email {
    color: var(--text-light);
    margin-bottom: 15px;
}

.role-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-badge.admin {
    background: rgba(52, 152, 219, 0.1);
    color: var(--primary);
}

.role-badge.usuario {
    background: rgba(46, 204, 113, 0.1);
    color: var(--success);
}

.profile-nav {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 20px 0;
}

.profile-nav .nav-link {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    color: var(--text-dark);
    text-decoration: none;
    transition: all 0.3s;
    border-left: 4px solid transparent;
}

.profile-nav .nav-link:hover,
.profile-nav .nav-link.active {
    background: rgba(0, 170, 255, 0.05);
    border-left: 4px solid var(--primary);
    color: var(--primary);
}

.profile-nav .nav-link i {
    width: 24px;
    margin-right: 12px;
    font-size: 18px;
}

.profile-nav .nav-link.logout {
    color: var(--danger);
}

.profile-nav .nav-link.logout:hover {
    background: rgba(231, 76, 60, 0.05);
    border-left: 4px solid var(--danger);
}

.profile-nav .badge {
    margin-left: auto;
    background: var(--danger);
    color: white;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
}

/* Secciones del perfil */
.profile-section {
    display: none;
}

.profile-section.active {
    display: block;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border);
}

.section-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Formularios */
.profile-form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
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
    background: var(--light-gray);
    transition: all 0.3s;
}

.form-group input:read-only,
.form-group textarea:read-only {
    background: #f5f5f5;
    color: #666;
    cursor: not-allowed;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--primary);
    background: white;
    outline: none;
}

.avatar-upload {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 10px;
}

.avatar-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid var(--border);
}

.avatar-preview img,
.avatar-default-preview {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-default-preview {
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: bold;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

/* Lista de pedidos */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-card {
    background: var(--light-gray);
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s;
}

.order-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.order-info h4 {
    font-size: 16px;
    margin-bottom: 5px;
    color: var(--text-dark);
}

.order-date {
    font-size: 14px;
    color: var(--text-light);
}

.order-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-total {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
}

.order-actions {
    display: flex;
    gap: 10px;
}

.btn-sm {
    padding: 8px 15px;
    font-size: 14px;
}

/* Lista de deseos */
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.wishlist-item {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    display: flex;
    gap: 15px;
    align-items: center;
    transition: all 0.3s;
}

.wishlist-item:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(0, 170, 255, 0.1);
}

.wishlist-item .product-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
}

.wishlist-item .product-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.wishlist-item .product-info {
    flex: 1;
}

.wishlist-item .product-info h4 {
    font-size: 16px;
    margin-bottom: 5px;
}

.wishlist-item .product-price {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
    margin-top: 5px;
}

.wishlist-item .product-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 150px;
}

/* Seguridad */
.security-forms {
    max-width: 600px;
}

.security-form {
    background: var(--light-gray);
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 30px;
}

.security-form h4 {
    font-size: 18px;
    margin-bottom: 20px;
    color: var(--text-dark);
}

.password-input {
    position: relative;
}

.password-input input {
    padding-right: 40px;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    font-size: 16px;
}

.password-hint {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: var(--text-light);
}

.security-settings {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 25px;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid var(--border);
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-info h5 {
    font-size: 16px;
    margin-bottom: 5px;
    color: var(--text-dark);
}

.setting-info p {
    font-size: 14px;
    color: var(--text-light);
}

/* Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: var(--primary);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-light);
}

.empty-state i {
    font-size: 60px;
    margin-bottom: 20px;
    color: #ddd;
}

.empty-state h4 {
    font-size: 20px;
    margin-bottom: 10px;
    color: var(--text-dark);
}

.empty-state p {
    margin-bottom: 30px;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 992px) {
    .profile-container {
        flex-direction: column;
    }
    
    .profile-sidebar {
        width: 100%;
    }
    
    .profile-content {
        padding: 20px;
    }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
    
    .wishlist-item {
        flex-direction: column;
        text-align: center;
    }
    
    .order-body {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .section-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Navegación entre secciones
    const navLinks = document.querySelectorAll('.profile-nav .nav-link');
    const sections = document.querySelectorAll('.profile-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            
            // Actualizar enlaces activos
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Mostrar sección correspondiente
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === targetId) {
                    section.classList.add('active');
                }
            });
            
            // Scroll suave a la sección
            document.getElementById(targetId).scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    
    // Edición de datos
    const btnEditarDatos = document.getElementById('btn-editar-datos');
    const formDatos = document.getElementById('form-datos');
    const formActions = document.querySelector('.form-actions');
    
    if (btnEditarDatos) {
        btnEditarDatos.addEventListener('click', function() {
            const inputs = formDatos.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.style.background = 'white';
            });
            
            formActions.style.display = 'flex';
            this.style.display = 'none';
        });
    }
    
    // Cancelar edición
    const btnCancelar = document.getElementById('btn-cancelar');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Cambiar foto de perfil
    const btnCambiarAvatar = document.getElementById('btn-cambiar-avatar');
    const avatarInput = document.getElementById('avatar-input');
    
    if (btnCambiarAvatar) {
        btnCambiarAvatar.addEventListener('click', function() {
            avatarInput.click();
        });
    }
    
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Nueva foto">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Mostrar/ocultar contraseñas
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Enviar formulario de datos
    formDatos.addEventListener('submit', function(e) {
        e.preventDefault();
        actualizarDatosPerfil();
    });
    
    // Enviar formulario de contraseña
    const formCambiarContrasena = document.getElementById('form-cambiar-contrasena');
    if (formCambiarContrasena) {
        formCambiarContrasena.addEventListener('submit', function(e) {
            e.preventDefault();
            cambiarContrasena();
        });
    }
});

function actualizarDatosPerfil() {
    const form = document.getElementById('form-datos');
    const formData = new FormData(form);
    formData.append('action', 'actualizar_perfil');
    
    // Agregar avatar si se seleccionó
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput.files[0]) {
        formData.append('avatar', avatarInput.files[0]);
    }
    
    fetch('../ajax/perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Datos actualizados correctamente');
            setTimeout(() => location.reload(), 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar los datos');
    });
}

function cambiarContrasena() {
    const form = document.getElementById('form-cambiar-contrasena');
    const formData = new FormData(form);
    formData.append('action', 'cambiar_contrasena');
    
    fetch('../ajax/perfil.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Contraseña cambiada correctamente');
            form.reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar la contraseña');
    });
}

function mostrarNotificacion(mensaje) {
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion';
    notificacion.textContent = mensaje;
    
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #2ecc71;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        notificacion.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notificacion.parentNode) {
                document.body.removeChild(notificacion);
            }
        }, 300);
    }, 3000);
}

// Agregar animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>

<?php include('../includes/footer.php'); ?>