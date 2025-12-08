// carrito.js - Funcionalidades específicas para el carrito

document.addEventListener('DOMContentLoaded', function() {
    cargarCarrito();
});

function cargarCarrito() {
    fetch('../ajax/carrito.php?action=obtener')
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la red: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        const container = document.getElementById('carrito-container');
        const resumen = document.getElementById('carrito-resumen');
        const vacio = document.getElementById('carrito-vacio');
        
        if (!data.success) {
            container.innerHTML = '<div class="error">' + data.message + '</div>';
            return;
        }
        
        if (data.items.length === 0) {
            container.innerHTML = '';
            resumen.style.display = 'none';
            vacio.style.display = 'block';
            return;
        }
        
        let html = '';
        let subtotal = 0;
        
        data.items.forEach(item => {
            const itemTotal = item.precio * item.cantidad;
            subtotal += itemTotal;
            
            html += `
                <div class="cart-item" id="cart-item-${item.carrito_id}">
                    <div class="cart-item-image">
                        <img src="../assets/img/${item.imagen || 'producto-default.png'}" alt="${item.nombre}" 
                             onerror="this.src='../assets/img/producto-default.png'; this.alt='Producto sin imagen'">
                    </div>
                    <div class="cart-item-info">
                        <div class="cart-item-brand">${item.marca || 'CLIMAXA'}</div>
                        <div class="cart-item-name">${item.nombre}</div>
                        <div class="cart-item-price">RD$${parseFloat(item.precio).toFixed(2)}</div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn" onclick="actualizarCantidad(${item.carrito_id}, ${item.cantidad - 1})">-</button>
                            <input type="number" class="quantity-input" value="${item.cantidad}" min="1" 
                                   onchange="actualizarCantidad(${item.carrito_id}, this.value)">
                            <button class="quantity-btn" onclick="actualizarCantidad(${item.carrito_id}, ${item.cantidad + 1})">+</button>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="remove-btn" onclick="eliminarDelCarrito(${item.carrito_id})">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        actualizarResumen(subtotal);
        resumen.style.display = 'block';
        vacio.style.display = 'none';
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('carrito-container').innerHTML = 
            '<div class="error" style="text-align: center; padding: 40px; color: #e74c3c;">' +
            '<i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>' +
            '<h3>Error al cargar el carrito</h3>' +
            '<p>Por favor, recarga la página o intenta más tarde.</p>' +
            '</div>';
    });
}

function actualizarCantidad(carritoId, nuevaCantidad) {
    if (nuevaCantidad < 1) return;
    
    const formData = new FormData();
    formData.append('carrito_id', carritoId);
    formData.append('cantidad', nuevaCantidad);
    formData.append('action', 'actualizar');
    
    fetch('../ajax/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarCarrito();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión. Por favor, intente nuevamente.');
    });
}

function eliminarDelCarrito(carritoId) {
    if (!confirm('¿Estás seguro de eliminar este producto del carrito?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('carrito_id', carritoId);
    formData.append('action', 'eliminar');
    
    fetch('../ajax/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarCarrito();
            // Actualizar contador en navbar
            if (window.actualizarContadorCarrito) {
                actualizarContadorCarrito();
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión. Por favor, intente nuevamente.');
    });
}

function actualizarResumen(subtotal) {
    const envio = subtotal > 5000 ? 0 : 500; // Envío gratis sobre RD$5,000
    const total = subtotal + envio;
    
    document.getElementById('subtotal').textContent = `RD$${subtotal.toFixed(2)}`;
    document.getElementById('envio').textContent = `RD$${envio.toFixed(2)}`;
    document.getElementById('total').textContent = `RD$${total.toFixed(2)}`;
}