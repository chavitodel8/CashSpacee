#!/bin/bash
# Script de configuración para Wasmer
# Este script prepara el proyecto para desplegar en Wasmer

echo "🚀 Preparando CashSpace para Wasmer..."

# Crear directorio de logs si no existe
if [ ! -d "logs" ]; then
    mkdir -p logs
    echo "✅ Directorio logs creado"
fi

# Crear archivo de log de errores
touch logs/error.log
chmod 644 logs/error.log
echo "✅ Archivo de log creado"

# Verificar que existe config/database.php
if [ ! -f "config/database.php" ]; then
    echo "⚠️  config/database.php no existe. Creando desde template..."
    cp config/database.production.php config/database.php
    echo "✅ Archivo database.php creado"
fi

# Crear .htaccess si no existe (para Apache)
if [ ! -f ".htaccess" ]; then
    cat > .htaccess << 'EOF'
# CashSpace - Configuración Apache
RewriteEngine On
RewriteBase /

# Redirigir a HTTPS si no está en HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos sensibles
<FilesMatch "\.(env|log|sql|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Configuración PHP
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
EOF
    echo "✅ Archivo .htaccess creado"
fi

# Verificar permisos
echo "📝 Verificando permisos..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 logs
chmod 644 logs/error.log

echo ""
echo "✅ Preparación completada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Configura las variables de entorno en Wasmer:"
echo "   - DB_HOST"
echo "   - DB_USER"
echo "   - DB_PASS"
echo "   - DB_NAME"
echo "   - ENVIRONMENT=production"
echo ""
echo "2. Importa la base de datos usando database/cashspace.sql"
echo ""
echo "3. Sube el proyecto a Wasmer"
echo ""
echo "¡Listo para desplegar! 🎉"

