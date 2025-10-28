-- Script de migración para eliminar la columna 'dormir' de la tabla reservas
-- Ejecuta este script si ya tienes la base de datos creada anteriormente

USE boda_fatima_david;

-- Eliminar la columna 'dormir' si existe
ALTER TABLE reservas DROP COLUMN IF EXISTS dormir;

-- Verificar que se eliminó correctamente
SHOW COLUMNS FROM reservas;
