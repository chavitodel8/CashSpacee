// Sistema de Notificaciones Push para CashSpace PWA

// Verificar si el navegador soporta notificaciones
function isNotificationSupported() {
    return 'Notification' in window && 'serviceWorker' in navigator;
}

// Solicitar permiso para notificaciones
async function requestNotificationPermission() {
    if (!isNotificationSupported()) {
        console.log('Las notificaciones no están soportadas en este navegador');
        return false;
    }
    
    if (Notification.permission === 'granted') {
        return true;
    }
    
    if (Notification.permission === 'denied') {
        console.log('El usuario ha denegado los permisos de notificación');
        return false;
    }
    
    // Solicitar permiso
    const permission = await Notification.requestPermission();
    return permission === 'granted';
}

// Suscribirse a notificaciones push
async function subscribeToPushNotifications() {
    if (!isNotificationSupported()) {
        return null;
    }
    
    // Verificar que el service worker esté registrado
    const registration = await navigator.serviceWorker.ready;
    
    try {
        // Obtener la clave pública del servidor (debe configurarse)
        const vapidPublicKey = 'BEl62iUYgUivxIkv69yViEuiBIa40HIe8y8vK0F5g5L0vK0F5g5L0vK0F5g5L0vK0F5g5L0vK0F5g5L0'; // Reemplazar con tu clave real
        
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
        });
        
        // Enviar la suscripción al servidor
        await sendSubscriptionToServer(subscription);
        
        console.log('Suscripción a notificaciones push exitosa');
        return subscription;
    } catch (error) {
        console.error('Error al suscribirse a notificaciones push:', error);
        return null;
    }
}

// Convertir clave VAPID de base64 a Uint8Array
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Enviar suscripción al servidor
async function sendSubscriptionToServer(subscription) {
    try {
        const response = await fetch('api/push-subscribe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                subscription: subscription,
                user_id: getUserId() // Función para obtener el ID del usuario
            })
        });
        
        if (!response.ok) {
            throw new Error('Error al enviar suscripción al servidor');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error al enviar suscripción:', error);
    }
}

// Obtener ID del usuario (debe implementarse según tu sistema)
function getUserId() {
    // Obtener del sessionStorage o de una variable global
    return sessionStorage.getItem('user_id') || null;
}

// Mostrar notificación local
function showLocalNotification(title, options) {
    if (!isNotificationSupported() || Notification.permission !== 'granted') {
        return;
    }
    
    const notification = new Notification(title, {
        icon: '/CashSpace/assets/images/icons/icon-192x192.png',
        badge: '/CashSpace/assets/images/icons/icon-192x192.png',
        ...options
    });
    
    notification.onclick = function(event) {
        event.preventDefault();
        window.focus();
        notification.close();
    };
}

// Inicializar notificaciones
async function initPushNotifications() {
    if (!isNotificationSupported()) {
        return;
    }
    
    // Verificar si ya hay una suscripción
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    
    if (subscription) {
        console.log('Ya existe una suscripción a notificaciones push');
        return subscription;
    }
    
    // Solicitar permiso
    const hasPermission = await requestNotificationPermission();
    if (!hasPermission) {
        console.log('Permiso de notificaciones denegado');
        return null;
    }
    
    // Suscribirse
    return await subscribeToPushNotifications();
}

// Escuchar mensajes del service worker
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', function(event) {
        if (event.data && event.data.type === 'NOTIFICATION') {
            showLocalNotification(event.data.title, event.data.options);
        }
    });
}

// Exportar funciones globales
window.requestNotificationPermission = requestNotificationPermission;
window.subscribeToPushNotifications = subscribeToPushNotifications;
window.initPushNotifications = initPushNotifications;
window.showLocalNotification = showLocalNotification;

// Auto-inicializar si el usuario está logueado
if (getUserId()) {
    initPushNotifications();
}

