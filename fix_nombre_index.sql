-- Script rápido para eliminar el índice único en nombre
-- Ejecuta este script en phpMyAdmin para solucionar el error "Duplicate entry"

USE boda_fatima_david;

-- Eliminar el índice único en nombre
ALTER TABLE reservas DROP INDEX idx_nombre;
