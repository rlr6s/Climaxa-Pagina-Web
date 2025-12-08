<?php
// admin/usuarios.php
require_once('includes/header.php');

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$usuarios = obtenerUsuarios();
?>

<div class="products-management">
    <!-- Header with Actions -->
    <div class="page-header">
        <div class="header-left">
            <h2>Gestión de Usuarios</h2>
            <p>Administra todos los usuarios registrados en el sistema</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" id="export-users">
                <i class="fas fa-file-export"></i> Exportar
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <div class="search-box">
            <input type="text" id="search-users" placeholder="Buscar usuarios...">
            <i class="fas fa-search"></i>
        </div>
        
        <div class="filter-options">
            <select id="filter-role">
                <option value="">Todos los roles</option>
                <option value="admin">Administradores</option>
                <option value="usuario">Usuarios</option>
            </select>
            
            <button class="btn btn-filter" id="apply-filters">
                <i class="fas fa-filter"></i> Filtrar
            </button>
            <button class="btn btn-clear" id="clear-filters">
                <i class="fas fa-times"></i> Limpiar
            </button>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Nombre</th>
                        <th>Correo Electrónico</th>
                        <th>Rol</th>
                        <th>Fecha Registro</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>#<?php echo $usuario['id']; ?></td>
                        <td>
                            <div class="user-info-cell">
                                <div class="user-name"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $usuario['rol'] == 'admin' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($usuario['rol']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['creado_en'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit change-role-btn" 
                                        data-user-id="<?php echo $usuario['id']; ?>"
                                        data-current-role="<?php echo $usuario['rol']; ?>"
                                        title="Cambiar rol">
                                    <i class="fas fa-user-cog"></i>
                                </button>
                                <a href="?action=eliminar&id=<?php echo $usuario['id']; ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?')"
                                   title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para cambiar rol -->
<div id="role-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cambiar Rol de Usuario</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="role-form">
                <input type="hidden" id="user-id" name="usuario_id" value="">
                
                <div class="form-group">
                    <label for="nuevo_rol">Nuevo Rol *</label>
                    <select id="nuevo_rol" name="nuevo_rol" required>
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('role-modal');
    const form = document.getElementById('role-form');
    
    // Abrir modal para cambiar rol
    document.querySelectorAll('.change-role-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const currentRole = this.getAttribute('data-current-role');
            
            document.getElementById('user-id').value = userId;
            document.getElementById('nuevo_rol').value = currentRole;
            
            modal.style.display = 'block';
        });
    });
    
    // Cerrar modal
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'cambiar_rol_usuario');
        
        fetch('../ajax/admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Rol actualizado exitosamente');
                modal.style.display = 'none';
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    
    // Filtros y búsqueda
    document.getElementById('search-users').addEventListener('input', function() {
        buscarUsuarios();
    });
    
    document.getElementById('apply-filters').addEventListener('click', buscarUsuarios);
    
    document.getElementById('clear-filters').addEventListener('click', function() {
        document.getElementById('search-users').value = '';
        document.getElementById('filter-role').value = '';
        buscarUsuarios();
    });
});

function buscarUsuarios() {
    const search = document.getElementById('search-users').value;
    const role = document.getElementById('filter-role').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (role) params.append('role', role);
    
    window.location.href = `?${params.toString()}`;
}
</script>

<style>
.user-info-cell {
    padding: 5px 0;
}

.user-name {
    font-weight: 500;
    margin-bottom: 5px;
}

.user-email {
    font-size: 12px;
    color: var(--gray);
}
</style>

<?php include('includes/footer.php'); ?>