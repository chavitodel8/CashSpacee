// Funciones del panel de administración

// Función auxiliar para obtener la ruta correcta de la API
function getApiUrl(endpoint) {
    // Usar ruta relativa simple que funciona en cualquier entorno
    // Si estamos en admin/index.php, ../api/endpoint nos lleva a admin/api/endpoint
    // Pero mejor usar ruta relativa desde la raíz del admin
    const pathname = window.location.pathname;
    
    // Detectar si estamos en una subcarpeta admin
    if (pathname.includes('/admin/')) {
        // Estamos en admin, usar ruta relativa
        return 'api/' + endpoint;
    } else {
        // Fallback: ruta absoluta desde la raíz
        return '/admin/api/' + endpoint;
    }
}

// Aprobar recarga
async function aprobarRecarga(recargaId) {
    if (!confirm('¿Estás seguro de aprobar esta recarga?')) {
        return;
    }
    
    try {
        const apiUrl = getApiUrl('aprobar_recarga.php');
        
        console.log('Pathname actual:', window.location.pathname);
        console.log('Llamando a:', apiUrl);
        console.log('URL completa:', window.location.origin + apiUrl);
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: recargaId,
                accion: 'aprobar'
            })
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error HTTP:', response.status);
            console.error('Respuesta del error:', errorText);
            throw new Error('Error HTTP: ' + response.status);
        }
        
        const text = await response.text();
        console.log('Respuesta del servidor (raw):', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            console.error('Texto recibido:', text);
            alert('Error: El servidor no devolvió una respuesta válida. Revisa la consola (F12) para más detalles.');
            return;
        }
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error completo al aprobar recarga:', error);
        alert('Error al procesar la solicitud: ' + error.message + '\n\nAbre la consola (F12) para ver más detalles.');
    }
}

// Rechazar recarga
async function rechazarRecarga(recargaId) {
    const motivo = prompt('Ingresa el motivo del rechazo (opcional):');
    if (motivo === null) return; // Usuario canceló
    
    try {
        const apiUrl = getApiUrl('aprobar_recarga.php');
        
        console.log('Llamando a:', apiUrl);
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: recargaId,
                accion: 'rechazar',
                observaciones: motivo || ''
            })
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
            alert('Error: El servidor no devolvió una respuesta válida. Revisa la consola (F12) para más detalles.');
            return;
        }
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error completo al rechazar recarga:', error);
        alert('Error al procesar la solicitud: ' + error.message + '\n\nAbre la consola (F12) para ver más detalles.');
    }
}

// Aprobar retiro
async function aprobarRetiro(retiroId) {
    if (!confirm('¿Estás seguro de aprobar este retiro? El monto será descontado del saldo del usuario.')) {
        return;
    }
    
    try {
        const apiUrl = getApiUrl('aprobar_retiro.php');
        
        console.log('Llamando a:', apiUrl);
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: retiroId,
                accion: 'aprobar'
            })
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
            alert('Error: El servidor no devolvió una respuesta válida. Revisa la consola (F12) para más detalles.');
            return;
        }
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error completo al aprobar retiro:', error);
        alert('Error al procesar la solicitud: ' + error.message + '\n\nAbre la consola (F12) para ver más detalles.');
    }
}

// Rechazar retiro
async function rechazarRetiro(retiroId) {
    const motivo = prompt('Ingresa el motivo del rechazo (opcional):');
    if (motivo === null) return; // Usuario canceló
    
    try {
        const apiUrl = getApiUrl('aprobar_retiro.php');
        
        console.log('Llamando a:', apiUrl);
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: retiroId,
                accion: 'rechazar',
                observaciones: motivo || ''
            })
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
            alert('Error: El servidor no devolvió una respuesta válida. Revisa la consola (F12) para más detalles.');
            return;
        }
        
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error completo al rechazar retiro:', error);
        alert('Error al procesar la solicitud: ' + error.message + '\n\nAbre la consola (F12) para ver más detalles.');
    }
}
