// checkout.js - Manejo del formulario de checkout

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-checkout');
    const submitBtn = document.getElementById('submit-order');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            procesarCheckout();
        });
    }
});

function procesarCheckout() {
    const form = document.getElementById('form-checkout');
    const submitBtn = document.getElementById('submit-order');
    const formData = new FormData(form);
    
    // Validar formulario antes de enviar
    if (!validarFormulario()) {
        return;
    }
    
    // Deshabilitar botón y mostrar loading
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    submitBtn.disabled = true;
    
    // Enviar datos via AJAX
    fetch('../ajax/procesar_pedido.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar mensaje de éxito
            mostrarMensajeExito('Pedido procesado exitosamente. Redirigiendo...');
            
            // Redirigir a la página de confirmación
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            // Mostrar error
            mostrarMensajeError(data.message || 'Error al procesar el pedido');
            
            // Rehabilitar botón
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensajeError('Error de conexión. Por favor, intente nuevamente.');
        
        // Rehabilitar botón
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function validarFormulario() {
    const telefono = document.getElementById('telefono').value;
    const email = document.getElementById('email').value;
    
    // Validar teléfono (formato simple para República Dominicana)
    const telefonoRegex = /^[0-9]{3}-[0-9]{3}-[0-9]{4}$/;
    if (!telefonoRegex.test(telefono)) {
        mostrarMensajeError('Por favor ingrese un teléfono válido (ej: 809-555-5555)');
        return false;
    }
    
    // Validar email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        mostrarMensajeError('Por favor ingrese un correo electrónico válido');
        return false;
    }
    
    // Validar que se haya seleccionado método de pago
    const metodoPagoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    if (!metodoPagoSeleccionado) {
        mostrarMensajeError('Por favor seleccione un método de pago');
        return false;
    }
    
    // Si es tarjeta, validar datos de tarjeta
    if (metodoPagoSeleccionado.value === 'tarjeta') {
        const numeroTarjeta = document.getElementById('numero_tarjeta').value;
        const fechaExpiracion = document.getElementById('fecha_expiracion').value;
        const cvv = document.getElementById('cvv').value;
        
        if (!numeroTarjeta || numeroTarjeta.replace(/\s/g, '').length !== 16) {
            mostrarMensajeError('Número de tarjeta inválido (debe tener 16 dígitos)');
            return false;
        }
        
        if (!fechaExpiracion || !/^\d{2}\/\d{2}$/.test(fechaExpiracion)) {
            mostrarMensajeError('Fecha de expiración inválida (formato: MM/AA)');
            return false;
        }
        
        if (!cvv || cvv.length < 3 || cvv.length > 4) {
            mostrarMensajeError('CVV inválido (debe tener 3-4 dígitos)');
            return false;
        }
    }
    
    return true;
}

function mostrarMensajeExito(mensaje) {
    // Crear elemento de mensaje
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = 'success-message';
    mensajeDiv.textContent = mensaje;
    
    // Estilos
    mensajeDiv.style.cssText = `
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
    
    document.body.appendChild(mensajeDiv);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        mensajeDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(mensajeDiv);
        }, 300);
    }, 3000);
}

function mostrarMensajeError(mensaje) {
    // Crear elemento de mensaje
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = 'error-message';
    mensajeDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${mensaje}</span>
    `;
    
    // Estilos
    mensajeDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #e74c3c;
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 9999;
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    
    document.body.appendChild(mensajeDiv);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        mensajeDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(mensajeDiv);
        }, 300);
    }, 5000);
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