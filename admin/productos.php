<?php
// admin/productos.php
require_once('includes/header.php');

// OBTENER PARÁMETROS DE BÚSQUEDA
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$stock_filter = $_GET['stock'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

function getPaginationUrl($page) {
    global $search, $category, $stock_filter;
    $params = [];
    if ($search) $params[] = "search=" . urlencode($search);
    if ($category) $params[] = "category=" . urlencode($category);
    if ($stock_filter) $params[] = "stock=" . urlencode($stock_filter);
    $params[] = "pagina=" . $page;
    return "?" . implode('&', $params);
}

// Usar la función con filtros
$resultados = obtenerProductosAdmin($pagina, 20, $search, $category, $stock_filter);

$productos = $resultados['productos'];
$total_paginas = $resultados['total_paginas'];
$pagina_actual = $resultados['pagina_actual'];
?>

<div class="products-management">
    <!-- Header with Actions -->
    <div class="page-header">
        <div class="header-left">
            <h2>Gestión de Productos</h2>
            <p>Administra todos los productos de la tienda</p>
        </div>
        <div class="header-actions">
            <a href="?action=crear" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
            <button class="btn btn-secondary" id="export-products">
                <i class="fas fa-file-export"></i> Exportar
            </button>
        </div>
    </div>
    
   <!-- Filters -->
<div class="filters">
    <div class="search-box">
        <input type="text" id="search-products" 
               placeholder="Buscar productos..." 
               value="<?php echo htmlspecialchars($search); ?>">
        <i class="fas fa-search"></i>
    </div>
    
    <div class="filter-options">
        <select id="filter-category">
            <option value="">Todas las categorías</option>
            <option value="aires" <?php echo $category == 'aires' ? 'selected' : ''; ?>>
                Aires Acondicionados
            </option>
            <option value="freezers" <?php echo $category == 'freezers' ? 'selected' : ''; ?>>
                Freezers
            </option>
            <option value="neveras" <?php echo $category == 'neveras' ? 'selected' : ''; ?>>
                Neveras
            </option>
            <option value="servicios" <?php echo $category == 'servicios' ? 'selected' : ''; ?>>
                Servicios
            </option>
        </select>
        
        <select id="filter-stock">
            <option value="">Todo el stock</option>
            <option value="low" <?php echo $stock_filter == 'low' ? 'selected' : ''; ?>>
                Stock bajo (< 5)
            </option>
            <option value="out" <?php echo $stock_filter == 'out' ? 'selected' : ''; ?>>
                Sin stock
            </option>
            <option value="in" <?php echo $stock_filter == 'in' ? 'selected' : ''; ?>>
                En stock
            </option>
        </select>
        
        <button class="btn btn-filter" id="apply-filters">
            <i class="fas fa-filter"></i> Filtrar
        </button>
        <button class="btn btn-clear" id="clear-filters">
            <i class="fas fa-times"></i> Limpiar
        </button>
    </div>
</div>
    
    <!-- Products Table -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="products-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="80">Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td>#<?php echo $producto['id']; ?></td>
                        <td>
                            <?php if (!empty($producto['imagen']) && file_exists("../../assets/img/" . $producto['imagen'])): ?>
                            <img src="../../assets/img/<?php echo $producto['imagen']; ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                 class="product-thumb">
                            <?php else: ?>
                            <div class="no-image">Sin imagen</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="product-info-cell">
                                <div class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                                <div class="product-specs"><?php echo htmlspecialchars(substr($producto['especificaciones'], 0, 50)) . '...'; ?></div>
                            </div>
                        </td>
                        <td>
                            <span class="category-badge category-<?php echo $producto['categoria']; ?>">
                                <?php 
                                $categorias = [
                                    'aires' => 'Aires',
                                    'freezers' => 'Freezers',
                                    'neveras' => 'Neveras',
                                    'servicios' => 'Servicios'
                                ];
                                echo $categorias[$producto['categoria']] ?? $producto['categoria'];
                                ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($producto['marca']); ?></td>
                        <td class="price-cell">
                            RD$ <?php echo number_format($producto['precio'], 2); ?>
                        </td>
                        <td>
                            <div class="stock-cell">
                                <span class="stock-amount"><?php echo $producto['stock']; ?></span>
                                <?php if ($producto['stock'] < 5): ?>
                                <span class="stock-warning">Bajo stock</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $producto['stock'] > 0 ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $producto['stock'] > 0 ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="?action=editar&id=<?php echo $producto['id']; ?>" 
                                   class="btn-action btn-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?action=eliminar&id=<?php echo $producto['id']; ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('¿Estás seguro de eliminar este producto?')"
                                   title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="?action=ver&id=<?php echo $producto['id']; ?>" 
                                   class="btn-action btn-view" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_paginas > 1): ?>
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
           <a href="<?php echo getPaginationUrl(1); ?>" class="page-link first">« Primera</a>
           <a href="<?php echo getPaginationUrl($pagina_actual - 1); ?>" class="page-link prev">‹ Anterior</a>
            <?php endif; ?>
            
            <?php 
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
           <a href="<?php echo getPaginationUrl($i); ?>" 
              class="page-link <?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
            <a href="<?php echo getPaginationUrl($pagina_actual + 1); ?>" class="page-link next">Siguiente ›</a>
            <a href="<?php echo getPaginationUrl($total_paginas); ?>" class="page-link last">Última »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para crear/editar producto -->
<div id="product-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Nuevo Producto</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="product-form" enctype="multipart/form-data">
                <input type="hidden" id="product-id" name="id" value="">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria">Categoría *</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="aires">Aires Acondicionados</option>
                            <option value="freezers">Freezers</option>
                            <option value="neveras">Neveras</option>
                            <option value="servicios">Servicios</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca">Marca *</label>
                        <input type="text" id="marca" name="marca" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio">Precio (RD$) *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock *</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="especificaciones">Especificaciones</label>
                        <textarea id="especificaciones" name="especificaciones" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen">Imagen del Producto</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    <button type="button" class="btn btn-secondary close-modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Funcionalidades del CRUD de productos
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('product-modal');
    const form = document.getElementById('product-form');
    
    // Abrir modal para crear producto
    document.querySelector('a[href="?action=crear"]').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('modal-title').textContent = 'Nuevo Producto';
        document.getElementById('product-id').value = '';
        form.reset();
        document.getElementById('image-preview').innerHTML = '';
        modal.style.display = 'block';
    });
    
    // Abrir modal para editar producto
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('href').split('id=')[1];
            
            fetch(`../ajax/admin.php?action=obtener_producto&id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const producto = data.producto;
                    document.getElementById('modal-title').textContent = 'Editar Producto';
                    document.getElementById('product-id').value = producto.id;
                    document.getElementById('nombre').value = producto.nombre;
                    document.getElementById('categoria').value = producto.categoria;
                    document.getElementById('marca').value = producto.marca;
                    document.getElementById('precio').value = producto.precio;
                    document.getElementById('stock').value = producto.stock;
                    document.getElementById('descripcion').value = producto.descripcion || '';
                    document.getElementById('especificaciones').value = producto.especificaciones || '';
                    
                    // Mostrar imagen actual si existe
                    if (producto.imagen) {
                        document.getElementById('image-preview').innerHTML = `
                            <img src="../../assets/img/${producto.imagen}" alt="${producto.nombre}" width="100">
                            <p>Imagen actual: ${producto.imagen}</p>
                        `;
                    }
                    
                    modal.style.display = 'block';
                }
            });
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
        formData.append('action', 'guardar_producto');
        
        fetch('../ajax/admin.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto guardado exitosamente');
                modal.style.display = 'none';
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    
    // Previsualizar imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').innerHTML = `
                    <img src="${e.target.result}" alt="Previsualización" width="100">
                `;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Filtros y búsqueda - VERSIÓN MEJORADA
let searchTimeout;

document.getElementById('search-products').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    
    // Buscar inmediatamente si está vacío
    if (this.value.length === 0) {
        buscarProductos();
        return;
    }
    
    // Esperar al menos 2 caracteres
    if (this.value.length < 2) {
        return;
    }
    
    // Esperar 1 segundo después de escribir
    searchTimeout = setTimeout(() => {
        buscarProductos();
    }, 1000);
});

// Buscar con Enter
document.getElementById('search-products').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        clearTimeout(searchTimeout);
        buscarProductos();
    }
});

// Botón Filtrar
document.getElementById('apply-filters').addEventListener('click', function() {
    clearTimeout(searchTimeout);
    buscarProductos();
});

// Botón Limpiar
document.getElementById('clear-filters').addEventListener('click', function() {
    document.getElementById('search-products').value = '';
    document.getElementById('filter-category').value = '';
    document.getElementById('filter-stock').value = '';
    buscarProductos();
});
    
    document.getElementById('apply-filters').addEventListener('click', buscarProductos);
    
    document.getElementById('clear-filters').addEventListener('click', function() {
        document.getElementById('search-products').value = '';
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-stock').value = '';
        buscarProductos();
    });
});

function buscarProductos() {
    const search = document.getElementById('search-products').value;
    const category = document.getElementById('filter-category').value;
    const stock = document.getElementById('filter-stock').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (category) params.append('category', category);
    if (stock) params.append('stock', stock);
    
    window.location.href = `?${params.toString()}`;
}
</script>

<?php include('includes/footer.php'); ?>