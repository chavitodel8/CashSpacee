# 🚀 Resumen Rápido - Despliegue en Wasmer

## Pasos Esenciales

### 1️⃣ Preparar el Proyecto
```bash
# Ejecutar script de preparación (Linux/Mac)
chmod +x wasmer-setup.sh
./wasmer-setup.sh

# O manualmente:
mkdir -p logs
touch logs/error.log
```

### 2️⃣ Subir a GitHub (Recomendado)
```bash
git init
git add .
git commit -m "Listo para Wasmer"
git remote add origin https://github.com/tu-usuario/cashspace.git
git push -u origin main
```

### 3️⃣ Configurar Base de Datos Externa
**Opciones 100% gratuitas (recomendadas):**
- **Railway** ⭐: https://railway.app (Más fácil, $5 crédito gratis/mes)
- **Clever Cloud**: https://www.clever-cloud.com (Gratis sin límite de tiempo)
- **Aiven**: https://aiven.io ($300 crédito gratis por 30 días)
- **AWS RDS**: https://aws.amazon.com/rds/free/ (750 horas/mes gratis)

**📖 Ver `BASES_DE_DATOS_GRATUITAS.md` para guía completa**

**Pasos rápidos (Railway - Recomendado):**
1. Ve a https://railway.app y regístrate con GitHub
2. Crea un nuevo proyecto → "+ New" → "Database" → "MySQL"
3. Copia las credenciales de la pestaña "Variables"
4. Importa `database/cashspace.sql` usando el panel de Railway

### 4️⃣ Desplegar en Wasmer
1. Ve a https://wasmer.io y regístrate
2. En el panel: **"Importar desde GitHub"**
3. Selecciona tu repositorio `cashspace`
4. Configura las variables de entorno:

```
DB_HOST=tu-host-de-planetscale
DB_USER=tu-usuario
DB_PASS=tu-contraseña
DB_NAME=cashspace
ENVIRONMENT=production
```

### 5️⃣ Verificar
- Accede a tu URL: `https://tu-proyecto.wasmer.app`
- Prueba login/registro
- Verifica que todo funcione

## ⚠️ Importante

1. **Base de Datos**: Wasmer NO incluye MySQL. Necesitas una base de datos externa.
2. **Variables de Entorno**: Configúralas en el panel de Wasmer antes de desplegar.
3. **Errores**: Revisa los logs en Wasmer si algo falla.

## 📚 Documentación Completa

Lee `GUIA_DESPLIEGUE_WASMER.md` para más detalles.

