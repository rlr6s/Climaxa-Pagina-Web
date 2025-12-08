<?php
function renderProductCard($product) {
    $defaults = [
        'image' => '',
        'alt' => 'Producto',
        'brand' => 'Marca',
        'name' => 'Nombre del Producto',
        'specs' => 'Especificaciones',
        'price' => 'RD$0.00',
        'image_path' => 'assets/img/'
    ];
    
    $product = array_merge($defaults, $product);
    
    ob_start(); ?>
    <div class="product-card">
        <div class="product-image">
            <?php
            $full_image_path = $product['image_path'] . $product['image'];
            if (file_exists($full_image_path) && !empty($product['image'])) {
                echo '<img src="' . $full_image_path . '" alt="' . htmlspecialchars($product['alt']) . '">';
            } else {
                echo '[Imagen del Producto]';
            }
            ?>
        </div>
        <div class="product-info">
            <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
            <div class="product-specs"><?php echo htmlspecialchars($product['specs']); ?></div>
            <div class="product-price"><?php echo htmlspecialchars($product['price']); ?></div>
            <button class="add-to-cart">Agregar al carrito</button>
        </div>
    </div>
    <?php return ob_get_clean();
}

function renderServiceCard($service) {
    $defaults = [
        'icon' => '🔧',
        'name' => 'Servicio',
        'description' => 'Descripción del servicio',
        'price' => 'RD$0.00'
    ];
    
    $service = array_merge($defaults, $service);
    
    ob_start(); ?>
    <div class="service-card">
        <div class="service-icon"><?php echo htmlspecialchars($service['icon']); ?></div>
        <h3 class="service-name"><?php echo htmlspecialchars($service['name']); ?></h3>
        <p class="service-description"><?php echo htmlspecialchars($service['description']); ?></p>
        <div class="service-price"><?php echo htmlspecialchars($service['price']); ?></div>
        <button class="service-btn">Solicitar Servicio</button>
    </div>
    <?php return ob_get_clean();
}
?>