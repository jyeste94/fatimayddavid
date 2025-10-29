<?php
/**
 * Configuración de la Base de Datos
 * Archivo de configuración para la conexión a MySQL
 * Lee las credenciales desde el archivo .env
 */

/**
 * Función para cargar variables de entorno desde archivo .env
 */
function loadEnv($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        die("Error: El archivo .env no existe. Copia .env.example a .env y configura tus credenciales.");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear línea KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remover comillas si existen
            $value = trim($value, '"\'');

            // Establecer variable de entorno
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Cargar variables de entorno
loadEnv();

// Configuración de la base de datos desde .env
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'boda_fatima_david');

// Token de acceso para el panel de administración desde .env
define('ADMIN_TOKEN', getenv('ADMIN_TOKEN') ?: 'fatima-david-2026-admin');

// Configuración de zona horaria
$timezone = getenv('TIMEZONE') ?: 'Europe/Madrid';
date_default_timezone_set($timezone);

// Configuración de errores
$debug_mode = getenv('DEBUG_MODE') === 'true';
if ($debug_mode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

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
