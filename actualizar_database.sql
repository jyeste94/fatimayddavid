-- Script para actualizar la base de datos existente
-- Ejecuta este script si ya tienes la base de datos creada

USE boda_fatima_david;

-- Cambiar password a NULL (opcional)
ALTER TABLE reservas MODIFY COLUMN password VARCHAR(255) DEFAULT NULL;

-- Cambiar email a NOT NULL (obligatorio)
-- IMPORTANTE: Antes de ejecutar este comando, asegúrate de que no hay registros con email NULL
-- Si hay registros con email NULL, actualízalos primero:
-- UPDATE reservas SET email = 'sin-email@ejemplo.com' WHERE email IS NULL OR email = '';

ALTER TABLE reservas MODIFY COLUMN email VARCHAR(255) NOT NULL;

-- Eliminar el índice único en nombre (permite nombres duplicados)
-- Primero verificar si existe y luego eliminarlo
DROP INDEX IF EXISTS idx_nombre ON reservas;

-- Agregar índice en email para búsquedas rápidas (si no existe)
CREATE INDEX IF NOT EXISTS idx_email ON reservas(email);
