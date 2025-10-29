# 💒 Sistema de Gestión de Invitados - Boda Fátima & David

Sistema completo de invitación digital y gestión de confirmaciones para bodas, desarrollado con PHP, MySQL y JavaScript.

## 🌟 Características Principales

### Para los Invitados:
- ✅ Invitación digital elegante y responsive
- ✅ Formulario de confirmación de asistencia
- ✅ Sistema de contraseñas para editar reservas
- ✅ Información del evento (itinerario, ubicaciones)
- ✅ Contador regresivo en tiempo real
- ✅ Integración con Google Calendar

### Para los Novios:
- ✅ Panel de administración completo
- ✅ Estadísticas en tiempo real
- ✅ Búsqueda y filtros avanzados
- ✅ Exportación a Excel (CSV)
- ✅ Vista detallada de cada invitado
- ✅ Paginación para grandes cantidades de invitados
- ✅ Autenticación por token (seguro)

## 📋 Requisitos

- **WAMP/XAMPP/MAMP** (Apache + MySQL + PHP)
- **PHP 7.4+**
- **MySQL 5.7+**
- Navegador web moderno

## 🚀 Instalación Rápida

### 1️⃣ Clonar o Descargar el Proyecto

Coloca los archivos en tu carpeta de servidor web:
```
C:\wamp64\www\documents\FatimayDavid\
```

### 2️⃣ Configurar Variables de Entorno

1. Copia el archivo `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edita el archivo `.env` con tus credenciales:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=boda_fatima_david
   ADMIN_TOKEN=cambia-esto-por-algo-seguro
   ```

### 3️⃣ Crear la Base de Datos

1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Ve a la pestaña **SQL**
3. Copia el contenido de `database.sql`
4. Pégalo y haz clic en **Ejecutar**

### 4️⃣ Acceder al Sistema

**Invitación para invitados:**
```
http://localhost/documents/FatimayDavid/index.html
```

**Panel de administración:**
```
http://localhost/documents/FatimayDavid/admin_invitados.php?token=TU_TOKEN
```

## 📁 Estructura del Proyecto

```
FatimayDavid/
├── assets/
│   ├── css/           # Estilos
│   ├── js/            # JavaScript
│   └── images/        # Imágenes
├── .env               # Variables de entorno (NO compartir)
├── .env.example       # Plantilla de configuración
├── .gitignore         # Archivos ignorados por Git
├── config.php         # Configuración y funciones
├── database.sql       # Script de base de datos
├── index.html         # Invitación principal
├── procesar_reserva.php      # Procesa formulario
├── editar_reserva.php        # Editar reservas existentes
├── admin_invitados.php       # Panel de administración
├── exportar_excel.php        # Exportar datos a CSV
├── URL_ADMIN.txt             # URL de acceso rápido
├── INSTRUCCIONES_INSTALACION.md  # Guía completa
└── README.md                 # Este archivo
```

## 🔒 Seguridad

### Variables de Entorno
- ✅ Credenciales en archivo `.env` (no en código)
- ✅ `.env` excluido de Git mediante `.gitignore`
- ✅ `.env.example` como plantilla (sin credenciales)

### Protección contra Ataques
- ✅ **SQL Injection**: Prepared statements en todas las consultas
- ✅ **XSS**: Sanitización con `htmlspecialchars()`
- ✅ **Validación**: Validación de datos de entrada
- ✅ **Autenticación**: Token único para panel de administración

### Buenas Prácticas
- 🔑 Cambia el `ADMIN_TOKEN` por uno único
- 🔒 En producción, establece `DEBUG_MODE=false`
- 🔐 Usa contraseñas seguras para MySQL
- 📝 NO compartas tu archivo `.env`

## 📊 Base de Datos

### Tabla: `reservas`

| Campo                     | Tipo         | Descripción                    |
|---------------------------|--------------|--------------------------------|
| id                        | INT          | ID único                       |
| nombre                    | VARCHAR(255) | Nombre del invitado            |
| password                  | VARCHAR(255) | Contraseña (texto plano)       |
| asistencia                | VARCHAR(50)  | Acepto/Declino                 |
| email                     | VARCHAR(255) | Email                          |
| telefono                  | VARCHAR(20)  | Teléfono                       |
| viene_acompanante         | VARCHAR(50)  | Si/No acompañantes             |
| num_adultos               | INT          | Número de adultos              |
| num_ninos                 | INT          | Número de niños                |
| carne                     | INT          | Cantidad menú carne            |
| pescado                   | INT          | Cantidad menú pescado          |
| nombres_invitados         | TEXT         | Nombres acompañantes           |
| alergias_invitados        | TEXT         | Alergias e intolerancias       |
| canciones                 | TEXT         | Canciones favoritas            |
| comentario                | TEXT         | Comentarios                    |
| especificaciones_alimentarias | TEXT     | Vegetariano, vegano, etc.      |
| fecha_registro            | TIMESTAMP    | Fecha de creación              |
| fecha_actualizacion       | TIMESTAMP    | Última actualización           |

## 🎨 Personalización

### Cambiar Fecha de la Boda
Edita `index.html` línea 75:
```javascript
var endTimestamp = parseInt('1775901600') * 1000; // 11 abril 2026
```

### Cambiar Nombres
Busca y reemplaza "Fátima" y "David" en:
- `index.html`
- `admin_invitados.php`
- `.env.example`

### Cambiar Colores
Edita los gradientes en los archivos CSS/PHP:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

## 🛠️ Solución de Problemas

### Error: "El archivo .env no existe"
**Solución**: Copia `.env.example` a `.env` y configura los valores.

### Error: "Error de conexión a la base de datos"
**Solución**: Verifica las credenciales en `.env` y que MySQL esté ejecutándose.

### Error: "Acceso denegado" en panel admin
**Solución**: Verifica que el token en la URL coincida con el de `.env`.

### El contador no se mueve
**Solución**: Limpia la caché del navegador (Ctrl+F5).

## 📚 Documentación Completa

Para instrucciones detalladas, consulta:
- **[INSTRUCCIONES_INSTALACION.md](INSTRUCCIONES_INSTALACION.md)** - Guía paso a paso
- **[URL_ADMIN.txt](URL_ADMIN.txt)** - URL de acceso al panel

## 🤝 Contribuir

Este es un proyecto privado para una boda específica, pero puedes:
1. Hacer un fork para tu propia boda
2. Personalizar según tus necesidades
3. Compartir mejoras (opcional)

## 📝 Licencia

Este proyecto es de uso personal y educativo.

## 👨‍💻 Soporte

Si tienes problemas:
1. Lee la documentación completa
2. Verifica los logs de errores de PHP
3. Revisa la consola del navegador (F12)

---

**Hecho con ❤️ para Fátima & David**
*11 de Abril de 2026*
