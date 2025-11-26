<?php
// Configuración general de la aplicación
session_start();

// Zona horaria
date_default_timezone_set('America/Caracas');

// Configuración de errores (en producción cambiar a 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Base URL de la aplicación
// Detectar automáticamente el protocolo y host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
             !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
             ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$path = dirname($_SERVER['SCRIPT_NAME']);
// Si estamos en la raíz, usar /CashSpace/, si no, usar la ruta actual
$base_path = (strpos($path, '/CashSpace') !== false) ? '/CashSpace/' : $path . '/';
define('BASE_URL', $protocol . '://' . $host . $base_path);

// Rutas
define('BASE_PATH', dirname(__DIR__));

// Incluir configuración de base de datos
// Intentar cargar database.php, si no existe usar database.production.php
if (file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
} else {
    require_once __DIR__ . '/database.production.php';
}

// Función para verificar si el usuario está autenticado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Función para verificar si el usuario es admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Función para requerir autenticación
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Función para requerir permisos de admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

// Función para sanitizar entrada
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para formatear moneda
function formatCurrency($amount) {
    return number_format($amount, 2, ',', '.') . ' Bs';
}

// Función para generar código único
function generateUniqueCode($length = 10) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
