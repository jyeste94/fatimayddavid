-- Base de datos para el sistema de reservas de boda
-- Ejecuta este script en phpMyAdmin o en tu cliente MySQL

-- Crear la base de datos (si no existe)
CREATE DATABASE IF NOT EXISTS boda_fatima_david CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE boda_fatima_david;

-- Tabla de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    asistencia VARCHAR(50) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    viene_acompanante VARCHAR(50) DEFAULT NULL,
    num_adultos INT DEFAULT 0,
    num_ninos INT DEFAULT 0,
    carne INT DEFAULT 0,
    pescado INT DEFAULT 0,
    nombres_invitados TEXT DEFAULT NULL,
    alergias_invitados TEXT DEFAULT NULL,
    canciones TEXT DEFAULT NULL,
    comentario TEXT DEFAULT NULL,
    especificaciones_alimentarias TEXT DEFAULT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices adicionales para búsquedas rápidas
CREATE INDEX idx_asistencia ON reservas(asistencia);
CREATE INDEX idx_fecha_registro ON reservas(fecha_registro);
