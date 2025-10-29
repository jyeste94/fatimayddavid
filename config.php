<?php
/**
 * Configuración de la Base de Datos
 * Archivo de configuración para la conexión a MySQL
 */

// Configuración de la base de datos
// IMPORTANTE: Actualiza estos valores con los datos de tu base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Actualiza con tu usuario de MySQL
define('DB_PASS', '');      // Actualiza con tu contraseña de MySQL
define('DB_NAME', 'boda_fatima_david');  // Actualiza con el nombre de tu base de datos

// Token de acceso para el panel de administración
// IMPORTANTE: Cambia este token por uno seguro y único
define('ADMIN_TOKEN', 'fatima-david-2026-admin');  // Cambia este valor

// Configuración de zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores (desactiva en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Función para obtener la conexión a la base de datos
 * @return mysqli|false Objeto de conexión o false en caso de error
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Verificar la conexión
    if ($conn->connect_error) {
        error_log("Error de conexión a la base de datos: " . $conn->connect_error);
        return false;
    }

    // Establecer el charset a UTF-8
    $conn->set_charset("utf8mb4");

    return $conn;
}

/**
 * Función para sanitizar entrada de datos
 * @param string $data Datos a sanitizar
 * @return string Datos sanitizados
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Función para verificar si una reserva existe
 * @param mysqli $conn Conexión a la base de datos
 * @param string $nombre Nombre del invitado
 * @return int|false ID de la reserva o false si no existe
 */
function reservaExiste($conn, $nombre) {
    $stmt = $conn->prepare("SELECT id FROM reservas WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }

    return false;
}

/**
 * Función para verificar contraseña de reserva
 * @param mysqli $conn Conexión a la base de datos
 * @param string $nombre Nombre del invitado
 * @param string $password Contraseña
 * @return array|false Datos de la reserva o false si las credenciales son incorrectas
 */
function verificarCredenciales($conn, $nombre, $password) {
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE nombre = ? AND password = ?");
    $stmt->bind_param("ss", $nombre, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return false;
}

?>
