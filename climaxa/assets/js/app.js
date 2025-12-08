// app.js - Funcionalidades JavaScript comunes

document.addEventListener('DOMContentLoaded', function() {
    // Actualizar contador del carrito al cargar la página
    actualizarContadorCarrito();

    // Agregar productos al carrito - PREVENIR DOBLE CLIC
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Prevenir doble clic
            if (this.classList.contains('adding')) {
                return;
            }
            
            this.classList.add('adding');
            const productId = this.getAttribute('data-product-id');
            
            if (!productId) {
                alert('Error: Producto no válido');
                this.classList.remove('adding');
                return;
            }
            
            agregarProductoAlCarrito(productId, this);
        });
    });

    // Solicitar servicios - PREVENIR DOBLE CLIC
    const serviceButtons = document.querySelectorAll('.service-btn');
    serviceButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (this.classList.contains('adding')) {
                return;
            }
            
            this.classList.add('adding');
            const serviceId = this.getAttribute('data-product-id');
            
            if (!serviceId) {
                alert('Error: Servicio no válido');
                this.classList.remove('adding');
                return;
            }
            
            agregarServicioAlCarrito(serviceId, this);
        });
    });

    // Buscar productos
    const searchInput = document.querySelector('.nav-search input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            const serviceCards = document.querySelectorAll('.service-card');
            
            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                const productBrand = card.querySelector('.product-brand').textContent.toLowerCase();
                
                if (productName.includes(searchTerm) || productBrand.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            serviceCards.forEach(card => {
                const serviceName = card.querySelector('.service-name').textContent.toLowerCase();
                
                if (serviceName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

// Función para agregar producto al carrito via AJAX - CORREGIDA
// Función para agregar producto al carrito via AJAX - CON DEPURACIÓN
function agregarProductoAlCarrito(productoId, buttonElement) {
    console.log("DEBUG: agregarProductoAlCarrito llamado con ID:", productoId);
    
    // PREVENIR DOBLE CLIC: Deshabilitar botón inmediatamente
    if (buttonElement) {
        buttonElement.disabled = true;
        buttonElement.classList.add('adding');
    }
    
    const formData = new FormData();
    formData.append('producto_id', productoId);
    formData.append('action', 'agregar');
    formData.append('cantidad', '1'); // EXPLÍCITAMENTE enviar cantidad = 1
    
    console.log("DEBUG: Enviando datos:", {
        producto_id: productoId,
        cantidad: 1
    });
    
    fetch('../ajax/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("DEBUG: Respuesta recibida, status:", response.status);
        if (!response.ok) {
            throw new Error('Error en la red: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("DEBUG: Datos recibidos:", data);
        
        if (data.success) {
            mostrarNotificacion('Producto agregado al carrito');
            actualizarContadorCarrito();
            
            if (buttonElement) {
                const originalText = buttonElement.textContent;
                buttonElement.textContent = '✓ Agregado';
                buttonElement.style.background = '#2ecc71';
                
                setTimeout(() => {
                    buttonElement.textContent = originalText;
                    buttonElement.style.background = '';
                    buttonElement.disabled = false;
                    buttonElement.classList.remove('adding');
                }, 2000);
            }
        } else {
            alert('Error: ' + data.message);
            if (buttonElement) {
                buttonElement.disabled = false;
                buttonElement.classList.remove('adding');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar al carrito.');
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.classList.remove('adding');
        }
    });
}



// Función para agregar servicio al carrito - CORREGIDA
function agregarServicioAlCarrito(servicioId, buttonElement) {
    const formData = new FormData();
    formData.append('producto_id', servicioId);
    formData.append('action', 'agregar');
    
    const originalText = buttonElement.textContent;
    buttonElement.textContent = 'Solicitando...';
    buttonElement.disabled = true;
    
    fetch('../ajax/carrito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la red');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            mostrarNotificacion('Servicio agregado al carrito');
            actualizarContadorCarrito();
            
            buttonElement.textContent = '✓ Solicitado';
            buttonElement.style.background = '#2ecc71';
            
            setTimeout(() => {
                buttonElement.textContent = originalText;
                buttonElement.style.background = '';
                buttonElement.disabled = false;
                buttonElement.classList.remove('adding');
            }, 2000);
        } else {
            alert('Error: ' + data.message);
            buttonElement.textContent = originalText;
            buttonElement.disabled = false;
            buttonElement.classList.remove('adding');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al solicitar servicio. Verifica tu conexión.');
        buttonElement.textContent = originalText;
        buttonElement.disabled = false;
        buttonElement.classList.remove('adding');
    });
}

// Función para actualizar contador del carrito - MEJORADA
function actualizarContadorCarrito() {
    fetch('../ajax/carrito.php?action=contar')
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la red');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const contador = document.getElementById('carrito-contador');
            if (contador) {
                const count = parseInt(data.count) || 0;
                contador.textContent = count;
                contador.style.display = count > 0 ? 'flex' : 'none';
                
                // DEBUG
                console.log('Contador actualizado:', count);
            }
        } else {
            console.error('Error en contador:', data.message);
        }
    })
    .catch(error => {
        console.error('Error al obtener contador:', error);
        const contador = document.getElementById('carrito-contador');
        if (contador) {
            contador.style.display = 'none';
        }
    });
}

// Función para mostrar notificaciones - CORREGIDA
function mostrarNotificacion(mensaje) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion';
    notificacion.innerHTML = `
        <span>${mensaje}</span>
        <a href="carrito.php" class="ver-carrito-btn">Ver Carrito</a>
    `;
    
    // Estilos para la notificación
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #2ecc71;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    // Estilos para el botón de ver carrito - CORREGIDO
    const verCarritoBtn = notificacion.querySelector('.ver-carrito-btn');
    verCarritoBtn.style.cssText = `
        background: white;
        color: #2ecc71;
        padding: 8px 15px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s;
    `;
    
    // Agregar hover effect
    verCarritoBtn.onmouseover = function() {
        this.style.background = '#f8f9fa';
        this.style.transform = 'translateY(-2px)';
    };
    
    verCarritoBtn.onmouseout = function() {
        this.style.background = 'white';
        this.style.transform = 'translateY(0)';
    };
    
    // Agregar al documento
    document.body.appendChild(notificacion);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        notificacion.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notificacion.parentNode) {
                document.body.removeChild(notificacion);
            }
        }, 300);
    }, 5000);
}

// Asegurarse de que las animaciones CSS estén definidas
if (!document.querySelector('#app-js-styles')) {
    const style = document.createElement('style');
    style.id = 'app-js-styles';
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        /* Estilo para prevenir doble clic */
        .add-to-cart.adding,
        .service-btn.adding {
            opacity: 0.7;
            cursor: not-allowed;
        }
    `;
    document.head.appendChild(style);
}