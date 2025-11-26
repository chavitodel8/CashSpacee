// Funciones principales de CashSpace

// Variable para guardar la posición del scroll
let scrollPosition = 0;

// Funciones para manejar modales
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Guardar posición actual del scroll
        scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Bloquear scroll del body
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollPosition}px`;
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';
        
        modal.classList.add('active');
        
        // Actualizar saldo disponible cuando se abre el modal de retiro
        if (modalId === 'retiroModal') {
            setTimeout(updateSaldoDisponible, 100);
        }
        
        // Inicializar modal de recarga
        if (modalId === 'recargaModal') {
            setTimeout(function() {
                const seccionQR = document.getElementById('seccionQR');
                const seccionTransferencia = document.getElementById('seccionTransferencia');
                const metodoPago = document.getElementById('metodoPagoRecarga');
                const montoSelect = document.getElementById('recargaMonto');
                const qrContainer = document.getElementById('qrCodeContainer');
                
                if (seccionQR) seccionQR.style.display = 'none';
                if (seccionTransferencia) seccionTransferencia.style.display = 'none';
                if (metodoPago) metodoPago.value = 'yape';
                if (montoSelect) {
                    montoSelect.value = '';
                    // Agregar listener para generar QR automáticamente
                    montoSelect.addEventListener('change', function() {
                        if (this.value && metodoPago && metodoPago.value === 'yape') {
                            generarQR();
                        }
                    });
                }
                if (qrContainer) qrContainer.innerHTML = '<p style="color: #6b7280; font-size: 14px;">Selecciona un monto para generar el QR</p>';
            }, 100);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        
        // Restaurar scroll del body
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        document.body.style.overflow = '';
        document.body.style.overflowY = 'auto';
        
        // Restaurar posición del scroll
        window.scrollTo(0, scrollPosition);
        scrollPosition = 0;
        
        // Forzar scroll en móviles
        if (window.innerWidth <= 768) {
            document.body.style.webkitOverflowScrolling = 'touch';
        }
    }
}

// Asegurar que el body tenga scroll habilitado al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar scroll correcto
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.body.style.overflowY = 'auto';
    document.body.style.overflowX = 'hidden';
    
    // Verificar que no haya modales activos al cargar
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });
});

// Prevenir scroll del fondo cuando se hace scroll dentro del modal
document.addEventListener('touchmove', function(e) {
    const modal = document.querySelector('.modal.active');
    if (!modal) return;
    
    const modalContent = e.target.closest('.modal-content');
    
    // Si el scroll es dentro del modal-content, verificar límites
    if (modalContent) {
        const scrollTop = modalContent.scrollTop;
        const scrollHeight = modalContent.scrollHeight;
        const clientHeight = modalContent.clientHeight;
        const isAtTop = scrollTop <= 0;
        const isAtBottom = scrollTop + clientHeight >= scrollHeight - 1;
        
        // Obtener dirección del scroll (aproximado)
        const touch = e.touches[0];
        const lastTouchY = modalContent.dataset.lastTouchY || touch.clientY;
        const isScrollingDown = touch.clientY > lastTouchY;
        const isScrollingUp = touch.clientY < lastTouchY;
        
        // Guardar última posición
        modalContent.dataset.lastTouchY = touch.clientY;
        
        // Si está en el tope y trata de hacer scroll hacia arriba, prevenir
        if (isAtTop && isScrollingUp) {
            e.preventDefault();
            return;
        }
        // Si está en el fondo y trata de hacer scroll hacia abajo, prevenir
        if (isAtBottom && isScrollingDown) {
            e.preventDefault();
            return;
        }
        // Permitir scroll dentro del modal
        return;
    }
    
    // Si el scroll es en el fondo del modal (no en el contenido), prevenirlo siempre
    e.preventDefault();
}, { passive: false });

// Prevenir scroll con rueda del mouse cuando el modal está abierto
document.addEventListener('wheel', function(e) {
    const modal = document.querySelector('.modal.active');
    if (!modal) return;
    
    const modalContent = e.target.closest('.modal-content');
    
    if (modalContent) {
        const scrollTop = modalContent.scrollTop;
        const scrollHeight = modalContent.scrollHeight;
        const clientHeight = modalContent.clientHeight;
        const isAtTop = scrollTop <= 0;
        const isAtBottom = scrollTop + clientHeight >= scrollHeight - 1;
        const isScrollingDown = e.deltaY > 0;
        const isScrollingUp = e.deltaY < 0;
        
        // Si está en el tope y trata de hacer scroll hacia arriba, prevenir
        if (isAtTop && isScrollingUp) {
            e.preventDefault();
            return;
        }
        // Si está en el fondo y trata de hacer scroll hacia abajo, prevenir
        if (isAtBottom && isScrollingDown) {
            e.preventDefault();
            return;
        }
        // Permitir scroll dentro del modal
        return;
    }
    
    // Si no es dentro del modal-content, prevenir siempre
    e.preventDefault();
}, { passive: false });

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        const modalId = e.target.id;
        closeModal(modalId);
    }
});

// Función para realizar inversión
async function invertir(investmentId, event) {
    event.preventDefault();
    
    const messageDiv = document.getElementById('inversionMessage');
    messageDiv.innerHTML = '<div style="padding: 15px; background: #fef3c7; border-radius: 10px; color: #92400e;"><i class="fas fa-spinner fa-spin"></i> Procesando inversión...</div>';
    
    try {
        const response = await fetch('api/invest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tipo_inversion_id: investmentId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #d1fae5; border-radius: 10px; color: #065f46;"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
        }
    } catch (error) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> Error al procesar la inversión. Intenta nuevamente.</div>`;
    }
}

// Función para enviar recarga
async function submitRecarga(event) {
    event.preventDefault();
    
    const form = document.getElementById('recargaForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('recargaMessage');
    const metodoPago = formData.get('metodo_pago');
    const monto = parseFloat(formData.get('monto'));
    
    // Validar monto mínimo
    if (monto < 100) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> El monto mínimo de recarga es de 100 Bs</div>`;
        return;
    }
    
    // Validar horario de recarga (10:00 - 22:00)
    const ahora = new Date();
    const hora = ahora.getHours();
    const minutos = ahora.getMinutes();
    const horaActual = hora + (minutos / 60);
    
    if (horaActual < 10 || horaActual >= 22) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> Las recargas solo están disponibles de 10:00 a 22:00</div>`;
        return;
    }
    
    // Si es Yape, obtener el QR generado y datos de Yape
    if (metodoPago === 'yape') {
        const qrImg = document.querySelector('#qrCodeContainer img');
        if (qrImg) {
            formData.append('qr_code', qrImg.src);
        }
        
        // Obtener datos de Yape de la cuenta bancaria configurada
        try {
            const response = await fetch('api/get_cuenta_bancaria.php');
            const data = await response.json();
            if (data.success && data.data.tipo_cartera === 'Yape') {
                if (data.data.cuenta_bancaria) {
                    formData.append('yape_numero', data.data.cuenta_bancaria);
                }
                if (data.data.nombre_titular) {
                    formData.append('yape_nombre', data.data.nombre_titular);
                }
            }
        } catch (error) {
            console.error('Error al obtener datos de Yape:', error);
        }
    }
    
    // Si es transferencia, no hay QR
    if (metodoPago === 'transferencia') {
        // No se envía QR para transferencia bancaria
    }
    
    messageDiv.innerHTML = '<div style="padding: 15px; background: #fef3c7; border-radius: 10px; color: #92400e;"><i class="fas fa-spinner fa-spin"></i> Enviando solicitud...</div>';
    
    try {
        const response = await fetch('api/recarga.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #d1fae5; border-radius: 10px; color: #065f46;"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
            form.reset();
            setTimeout(() => {
                closeModal('recargaModal');
                window.location.reload();
            }, 2000);
        } else {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
        }
    } catch (error) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> Error al enviar la solicitud. Intenta nuevamente.</div>`;
    }
}

// Función para canjear código
async function submitCodigo(event) {
    event.preventDefault();
    
    const form = document.getElementById('codigoForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('codigoMessage');
    
    messageDiv.innerHTML = '<div style="padding: 15px; background: #fef3c7; border-radius: 10px; color: #92400e;"><i class="fas fa-spinner fa-spin"></i> Canjeando código...</div>';
    
    try {
        const response = await fetch('api/canje_codigo.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error HTTP:', response.status, errorText);
            throw new Error('Error HTTP: ' + response.status);
        }
        
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Respuesta del servidor (no JSON):', text);
            throw new Error('El servidor no devolvió una respuesta válida');
        }
        
        if (data.success) {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #d1fae5; border-radius: 10px; color: #065f46;"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
            form.reset();
            setTimeout(() => {
                closeModal('codigoModal');
                window.location.reload();
            }, 2000);
        } else {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
        }
    } catch (error) {
        console.error('Error completo al canjear código:', error);
        let errorMessage = 'Error al canjear el código. Intenta nuevamente.';
        
        // Intentar obtener el texto de la respuesta si no es JSON
        if (error.message) {
            errorMessage = error.message;
        }
        
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> ${errorMessage}</div>`;
    }
}

// Función para solicitar retiro
async function submitRetiro(event) {
    event.preventDefault();
    
    const form = document.getElementById('retiroForm');
    const formData = new FormData(form);
    const messageDiv = document.getElementById('retiroMessage');
    const monto = parseFloat(formData.get('monto'));
    
    // Validar monto mínimo
    if (monto < 150) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> El monto mínimo de retiro es de 150 Bs</div>`;
        return;
    }
    
    // Validar horario de retiro (10:00 - 22:00)
    const ahora = new Date();
    const hora = ahora.getHours();
    const minutos = ahora.getMinutes();
    const horaActual = hora + (minutos / 60);
    
    if (horaActual < 10 || horaActual >= 22) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> Los retiros solo están disponibles de 10:00 a.m. a 10:00 p.m.</div>`;
        return;
    }
    
    // Calcular comisión del 5%
    const comision = monto * 0.05;
    const montoTotal = monto + comision;
    
    // Mostrar información de comisión
    const confirmar = confirm(`Monto a retirar: ${monto.toFixed(2)} Bs\nComisión (5%): ${comision.toFixed(2)} Bs\nTotal a descontar: ${montoTotal.toFixed(2)} Bs\n\n¿Desea continuar?`);
    if (!confirmar) {
        return;
    }
    
    messageDiv.innerHTML = '<div style="padding: 15px; background: #fef3c7; border-radius: 10px; color: #92400e;"><i class="fas fa-spinner fa-spin"></i> Enviando solicitud...</div>';
    
    try {
        const response = await fetch('api/retiro.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageDiv.innerHTML = `<div style="padding: 15px; background: #d1fae5; border-radius: 10px; color: #065f46;"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
            form.reset();
            setTimeout(() => {
                closeModal('retiroModal');
                window.location.reload();
            }, 2000);
        } else {
            // Si está bloqueado, mostrar mensaje especial
            const isBlocked = data.bloqueado || false;
            const bgColor = isBlocked ? '#fef3c7' : '#fee2e2';
            const textColor = isBlocked ? '#92400e' : '#991b1b';
            const icon = isBlocked ? 'fa-ban' : 'fa-exclamation-circle';
            
            messageDiv.innerHTML = `<div style="padding: 15px; background: ${bgColor}; border-radius: 10px; color: ${textColor};"><i class="fas ${icon}"></i> ${data.message}</div>`;
            
            // Si está bloqueado, hacer scroll al mensaje
            if (isBlocked) {
                messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    } catch (error) {
        messageDiv.innerHTML = `<div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #991b1b;"><i class="fas fa-exclamation-circle"></i> Error al enviar la solicitud. Intenta nuevamente.</div>`;
    }
}

// Actualizar saldo disponible cuando se abre el modal de retiro
function updateSaldoDisponible() {
    const saldoElement = document.getElementById('saldoDisponible');
    if (saldoElement) {
        // Obtener saldo del elemento en la página o del balance
        const userBalance = document.querySelector('.user-balance');
        if (userBalance) {
            saldoElement.textContent = userBalance.textContent.trim();
        }
    }
}

// Generar QR cuando se selecciona un monto
async function generarQR() {
    const montoSelect = document.getElementById('recargaMonto');
    if (!montoSelect) return;
    
    const monto = montoSelect.value;
    const qrContainer = document.getElementById('qrCodeContainer');
    const seccionQR = document.getElementById('seccionQR');
    
    if (!qrContainer || !seccionQR) {
        console.error('Elementos del QR no encontrados');
        return;
    }
    
    if (!monto || monto === '') {
        qrContainer.innerHTML = '<p style="color: #6b7280; font-size: 14px;">Selecciona un monto para generar el QR</p>';
        seccionQR.style.display = 'none';
        return;
    }
    
    // Solo mostrar QR si el método de pago es Yape
    const metodoPagoSelect = document.getElementById('metodoPagoRecarga');
    if (!metodoPagoSelect) return;
    
    const metodoPago = metodoPagoSelect.value;
    if (metodoPago !== 'yape') {
        seccionQR.style.display = 'none';
        return;
    }
    
    console.log('Generando QR para Yape, monto:', monto);
    
    // Mostrar sección QR y spinner
    seccionQR.style.display = 'block';
    qrContainer.innerHTML = '<div style="text-align: center;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i><p style="margin-top: 10px; color: #6b7280;">Generando QR...</p></div>';
    
    try {
        const formData = new FormData();
        formData.append('monto', monto);
        
        const response = await fetch('api/generar_qr.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        
        const responseText = await response.text();
        console.log('Respuesta del servidor (primeros 200 chars):', responseText.substring(0, 200));
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            console.error('Respuesta completa:', responseText);
            qrContainer.innerHTML = '<p style="color: #ef4444; font-size: 14px;">Error: El servidor no devolvió una respuesta válida. Revisa la consola (F12).</p>';
            return;
        }
        
        if (data.success && data.qr_url) {
            console.log('QR generado exitosamente. Tipo:', data.qr_type, 'Monto:', data.monto);
            console.log('QR URL (primeros 100 chars):', data.qr_url.substring(0, 100));
            
            qrContainer.innerHTML = `
                <img src="${data.qr_url}" alt="QR Code" style="max-width: 300px; width: 100%; height: auto; border: 2px solid #e5e7eb; border-radius: 10px; padding: 10px; background: white; display: block; margin: 0 auto;" onerror="console.error('Error al cargar imagen QR'); this.style.display='none'; this.parentElement.innerHTML='<p style=\\'color: #ef4444;\\'>Error al cargar la imagen QR</p>';">
                <p style="margin-top: 10px; color: #1f2937; font-weight: 600;">Monto: ${parseFloat(monto).toLocaleString('es-BO', {style: 'currency', currency: 'BOB'})}</p>
            `;
        } else {
            qrContainer.innerHTML = '<p style="color: #ef4444; font-size: 14px;">Error al generar el QR. Intenta nuevamente.</p>';
            console.error('Error en respuesta:', data);
        }
    } catch (error) {
        console.error('Error al generar QR:', error);
        qrContainer.innerHTML = '<p style="color: #ef4444; font-size: 14px;">Error al generar el QR. Revisa la consola (F12) para más detalles.</p>';
    }
}

// Mostrar/ocultar secciones según el método de pago
async function mostrarMetodoPago() {
    const metodoPagoSelect = document.getElementById('metodoPagoRecarga');
    if (!metodoPagoSelect) return;
    
    const metodoPago = metodoPagoSelect.value;
    const seccionQR = document.getElementById('seccionQR');
    const seccionTransferencia = document.getElementById('seccionTransferencia');
    
    if (!seccionQR || !seccionTransferencia) return;
    
    if (metodoPago === 'transferencia') {
        seccionQR.style.display = 'none';
        seccionTransferencia.style.display = 'block';
    } else if (metodoPago === 'yape') {
        seccionTransferencia.style.display = 'none';
        // Generar QR si hay un monto seleccionado
        const montoSelect = document.getElementById('recargaMonto');
        if (montoSelect && montoSelect.value) {
            await generarQR();
        } else {
            seccionQR.style.display = 'none';
        }
    }
}

// Cargar datos de Yape
async function cargarDatosYape() {
    const yapeNumero = document.getElementById('yapeNumero');
    const yapeNombre = document.getElementById('yapeNombre');
    
    try {
        const response = await fetch('api/get_cuenta_bancaria.php');
        const data = await response.json();
        
        if (data.success && data.data.tipo_cartera === 'Yape') {
            yapeNumero.textContent = data.data.cuenta_bancaria || 'No configurado';
            yapeNombre.textContent = data.data.nombre_titular || 'No configurado';
        } else {
            yapeNumero.textContent = 'No configurado';
            yapeNombre.textContent = 'No configurado';
        }
    } catch (error) {
        console.error('Error al cargar datos de Yape:', error);
        yapeNumero.textContent = 'Error al cargar';
        yapeNombre.textContent = 'Error al cargar';
    }
}

// Cargar datos bancarios en el modal de retiro
async function cargarDatosBancarios() {
    const cuentaDestino = document.getElementById('cuentaDestinoRetiro');
    const infoCuenta = document.getElementById('infoCuentaBancaria');
    const cuentaConfigurada = document.getElementById('cuentaConfigurada');
    const tipoCuentaConfigurada = document.getElementById('tipoCuentaConfigurada');
    
    try {
        const response = await fetch('api/get_cuenta_bancaria.php');
        const data = await response.json();
        
        if (data.success && data.data.cuenta_bancaria) {
            cuentaDestino.value = data.data.cuenta_bancaria;
            cuentaConfigurada.textContent = data.data.cuenta_bancaria;
            tipoCuentaConfigurada.textContent = data.data.tipo_cartera || 'No especificado';
            infoCuenta.style.display = 'block';
        } else {
            alert('No tienes una cuenta bancaria configurada. Por favor, configura tu cuenta en "Mío" → "Cuenta bancaria"');
        }
    } catch (error) {
        console.error('Error al cargar datos bancarios:', error);
        alert('Error al cargar los datos bancarios. Intenta nuevamente.');
    }
}

// Actualizar función openModal para cargar datos cuando se abre el modal de recarga
const originalOpenModal = openModal;
openModal = function(modalId) {
    originalOpenModal(modalId);
    
    if (modalId === 'recargaModal') {
        // Resetear el formulario y ocultar secciones
        const seccionQR = document.getElementById('seccionQR');
        const seccionYape = document.getElementById('seccionYape');
        const metodoPago = document.getElementById('metodoPagoRecarga');
        const montoSelect = document.getElementById('recargaMonto');
        const qrContainer = document.getElementById('qrCodeContainer');
        
        if (seccionQR) seccionQR.style.display = 'none';
        if (seccionYape) seccionYape.style.display = 'none';
        if (metodoPago) metodoPago.value = 'transferencia';
        if (montoSelect) montoSelect.value = '';
        if (qrContainer) qrContainer.innerHTML = '<p style="color: #6b7280; font-size: 14px;">Selecciona un monto para generar el QR</p>';
    }
    
    if (modalId === 'retiroModal') {
        // Ocultar info de cuenta bancaria
        const infoCuenta = document.getElementById('infoCuentaBancaria');
        if (infoCuenta) infoCuenta.style.display = 'none';
    }
};

