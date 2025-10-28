# Sistema de Reservas para Boda - Instrucciones de Instalación

## 📋 Archivos Creados

1. **config.php** - Configuración de la base de datos
2. **database.sql** - Script para crear la base de datos y tabla
3. **procesar_reserva.php** - Procesa el formulario de confirmación
4. **editar_reserva.php** - Permite editar reservas existentes

## 🗄️ Paso 1: Crear la Base de Datos

### Opción A: Usando phpMyAdmin (Recomendado)

1. Abre tu navegador y ve a: `http://localhost/phpmyadmin`
2. Haz clic en la pestaña **"SQL"** en el menú superior
3. Abre el archivo `database.sql` con un editor de texto
4. Copia todo el contenido del archivo
5. Pégalo en el área de texto de phpMyAdmin
6. Haz clic en el botón **"Continuar"** o **"Ejecutar"**

### Opción B: Usando línea de comandos

```bash
mysql -u root -p < database.sql
```

## ⚙️ Paso 2: Configurar la Conexión a la Base de Datos

1. Abre el archivo **config.php**
2. Modifica las siguientes líneas con tus datos:

```php
define('DB_HOST', 'localhost');         // Normalmente es 'localhost'
define('DB_USER', 'root');              // Tu usuario de MySQL
define('DB_PASS', '');                  // Tu contraseña de MySQL
define('DB_NAME', 'boda_fatima_david'); // Nombre de la base de datos
```

### Configuración típica para WAMP:
- **DB_HOST**: `localhost`
- **DB_USER**: `root`
- **DB_PASS**: `` (vacío por defecto)
- **DB_NAME**: `boda_fatima_david`

## 🧪 Paso 3: Probar el Sistema

1. **Asegúrate de que WAMP esté ejecutándose** (icono verde en la barra de tareas)

2. **Accede a la invitación**:
   ```
   http://localhost/documents/FatimayDavid/index.html
   ```

3. **Prueba el formulario**:
   - Completa el formulario de confirmación
   - Usa una contraseña que recuerdes
   - Haz clic en "Enviar"

4. **Prueba editar una reserva**:
   ```
   http://localhost/documents/FatimayDavid/editar_reserva.php
   ```
   - Introduce el nombre y contraseña que usaste
   - Modifica los datos y guarda

## 🔒 Seguridad Implementada

✅ **Protección contra inyección SQL**: Todas las consultas usan prepared statements
✅ **Sanitización de entrada**: Todos los datos del formulario son sanitizados
✅ **Validación de datos**: Se validan campos obligatorios
✅ **Contraseña requerida**: Para crear y editar reservas
✅ **Prevención XSS**: Uso de htmlspecialchars en las salidas

## 📊 Estructura de la Base de Datos

### Tabla: `reservas`

| Campo                         | Tipo          | Descripción                           |
|-------------------------------|---------------|---------------------------------------|
| id                            | INT           | ID único (autoincremental)            |
| nombre                        | VARCHAR(255)  | Nombre del invitado                   |
| password                      | VARCHAR(255)  | Contraseña (texto plano)              |
| asistencia                    | VARCHAR(50)   | Acepto/Declino                        |
| telefono                      | VARCHAR(20)   | Teléfono de contacto                  |
| email                         | VARCHAR(255)  | Email de contacto                     |
| viene_acompanante             | VARCHAR(50)   | Si/No viene con acompañantes          |
| num_adultos                   | INT           | Número de adultos                     |
| num_ninos                     | INT           | Número de niños                       |
| carne                         | INT           | Cantidad que eligen carne             |
| pescado                       | INT           | Cantidad que eligen pescado           |
| dormir                        | VARCHAR(50)   | Si/No se quedan a dormir              |
| nombres_invitados             | TEXT          | Nombres de los acompañantes           |
| alergias_invitados            | TEXT          | Alergias o intolerancias              |
| canciones                     | TEXT          | Canciones favoritas                   |
| comentario                    | TEXT          | Comentarios adicionales               |
| especificaciones_alimentarias | TEXT          | Vegetariano, vegano, etc.             |
| fecha_registro                | TIMESTAMP     | Fecha de creación de la reserva       |
| fecha_actualizacion           | TIMESTAMP     | Última actualización                  |

## 🔍 Consultar Reservas

Para ver todas las reservas, ejecuta esta consulta en phpMyAdmin:

```sql
SELECT * FROM reservas ORDER BY fecha_registro DESC;
```

Para ver estadísticas:

```sql
-- Total de confirmaciones
SELECT asistencia, COUNT(*) as total
FROM reservas
GROUP BY asistencia;

-- Total de personas (adultos + niños)
SELECT
    SUM(num_adultos) as total_adultos,
    SUM(num_ninos) as total_ninos,
    SUM(num_adultos + num_ninos) as total_personas
FROM reservas
WHERE asistencia = 'Acepto con mucho placer';

-- Preferencias de comida
SELECT
    SUM(carne) as total_carne,
    SUM(pescado) as total_pescado
FROM reservas
WHERE asistencia = 'Acepto con mucho placer';
```

## 🚨 Solución de Problemas

### Error: "Error de conexión a la base de datos"
- Verifica que WAMP esté ejecutándose
- Comprueba que los datos en `config.php` sean correctos
- Asegúrate de que la base de datos `boda_fatima_david` exista

### Error: "Table 'boda_fatima_david.reservas' doesn't exist"
- Ejecuta el script `database.sql` en phpMyAdmin

### El formulario no envía datos
- Verifica que la URL sea `http://localhost/...` (no `file://...`)
- Comprueba la consola del navegador (F12) para ver errores JavaScript

### Los cambios no se guardan
- Verifica que estés usando la contraseña correcta
- Comprueba los permisos de escritura en la base de datos

## 📝 Notas Importantes

1. **Contraseñas en texto plano**: Por requerimiento del usuario, las contraseñas se guardan en texto plano. En producción, se recomienda usar `password_hash()` y `password_verify()` de PHP.

2. **Entorno de desarrollo**: Este sistema está configurado para desarrollo local con WAMP. Para producción, ajusta la configuración de errores en `config.php`.

3. **Backup**: Realiza backups regulares de la base de datos:
   ```sql
   mysqldump -u root -p boda_fatima_david > backup.sql
   ```

## 📧 Funcionalidades Adicionales (Opcional)

Si deseas añadir notificaciones por email cuando alguien confirma:

1. Configura un servidor SMTP en PHP
2. Añade la función de envío de email en `procesar_reserva.php`
3. Usa la librería PHPMailer para mayor facilidad

## ✅ Checklist de Instalación

- [ ] Base de datos creada ejecutando `database.sql`
- [ ] Archivo `config.php` configurado con datos correctos
- [ ] WAMP ejecutándose (icono verde)
- [ ] Probado formulario de confirmación
- [ ] Probada edición de reserva
- [ ] Verificadas reservas en phpMyAdmin

---

## 🎉 ¡Listo!

Tu sistema de reservas está funcionando. Los invitados pueden:
- Confirmar su asistencia
- Crear una contraseña para su reserva
- Editar su reserva cuando quieran usando su nombre y contraseña
- Ver una página de confirmación bonita

Tú puedes:
- Ver todas las reservas en phpMyAdmin
- Exportar los datos a Excel
- Consultar estadísticas de asistencia
