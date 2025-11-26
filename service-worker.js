// Service Worker para CashSpace PWA
const CACHE_NAME = 'cashspace-v2';
const BASE_PATH = '/CashSpace/';

// URLs estáticas para cachear
const urlsToCache = [
  BASE_PATH,
  BASE_PATH + 'index.php',
  BASE_PATH + 'login.php',
  BASE_PATH + 'register.php',
  BASE_PATH + 'mio.php',
  BASE_PATH + 'ingresos.php',
  BASE_PATH + 'equipo.php',
  BASE_PATH + 'historial.php',
  BASE_PATH + 'acerca_de.php',
  BASE_PATH + 'asistencia.php',
  BASE_PATH + 'descargar.php',
  BASE_PATH + 'cambiar_contraseña.php',
  BASE_PATH + 'css/style.css',
  BASE_PATH + 'assets/images/logo.png',
  BASE_PATH + 'js/main.js',
  BASE_PATH + 'js/pwa-install.js',
  BASE_PATH + 'manifest.json',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Instalación del Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache abierto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activación del Service Worker
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Eliminando cache antiguo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Manejar notificaciones push
self.addEventListener('push', (event) => {
  let data = {};
  
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data = { title: 'CashSpace', body: event.data.text() };
    }
  }
  
  const options = {
    body: data.body || 'Tienes una nueva notificación',
    icon: '/CashSpace/assets/images/icons/icon-192x192.png',
    badge: '/CashSpace/assets/images/icons/icon-192x192.png',
    vibrate: [200, 100, 200],
    data: data.data || {},
    actions: data.actions || []
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title || 'CashSpace', options)
  );
});

// Manejar clics en notificaciones
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  event.waitUntil(
    clients.openWindow(event.notification.data.url || '/CashSpace/')
  );
});

// Estrategia: Network First, luego Cache
self.addEventListener('fetch', (event) => {
  // Solo cachear peticiones GET
  if (event.request.method !== 'GET') {
    return;
  }
  
  // No cachear peticiones a APIs
  if (event.request.url.includes('/api/')) {
    return;
  }
  
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Solo cachear respuestas exitosas
        if (response.status === 200) {
          // Clonar la respuesta
          const responseToCache = response.clone();
          
          caches.open(CACHE_NAME)
            .then((cache) => {
              cache.put(event.request, responseToCache);
            });
        }
        
        return response;
      })
      .catch(() => {
        // Si falla la red, intentar desde cache
        return caches.match(event.request)
          .then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Si no hay cache, devolver página offline
            if (event.request.destination === 'document') {
              return caches.match(BASE_PATH + 'index.php');
            }
            return new Response('Sin conexión', { status: 503 });
          });
      })
  );
});

