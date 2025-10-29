<?php
/**
 * Exportar Invitados a Excel (CSV)
 * Exporta todas las reservas a un archivo CSV compatible con Excel
 */

require_once 'config.php';

// Verificar token de acceso
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($token !== ADMIN_TOKEN) {
    die("Acceso denegado. Token inválido.");
}

// Obtener conexión
$conn = getDBConnection();
if (!$conn) {
    die("Error de conexión a la base de datos");
}

// Obtener todas las reservas
$sql = "SELECT * FROM reservas ORDER BY fecha_registro DESC";
$result = $conn->query($sql);

// Configurar headers para descarga de CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=invitados_boda_' . date('Y-m-d') . '.csv');

// Crear salida
$output = fopen('php://output', 'w');

// Escribir BOM para UTF-8 (para que Excel lo reconozca correctamente)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados del CSV
$headers = [
    'ID',
    'Nombre',
    'Asistencia',
    'Email',
    'Teléfono',
    'Viene con acompañantes',
    'Nº Adultos',
    'Nº Niños',
    'Total Personas',
    'Menú Carne',
    'Menú Pescado',
    'Nombres Invitados',
    'Alergias',
    'Canciones',
    'Comentarios',
    'Especificaciones Alimentarias',
    'Fecha Registro',
    'Última Actualización'
];

fputcsv($output, $headers, ';');

// Escribir datos
while ($row = $result->fetch_assoc()) {
    $total_personas = $row['num_adultos'] + $row['num_ninos'];

    $data = [
        $row['id'],
        $row['nombre'],
        $row['asistencia'],
        $row['email'],
        $row['telefono'],
        $row['viene_acompanante'],
        $row['num_adultos'],
        $row['num_ninos'],
        $total_personas,
        $row['carne'],
        $row['pescado'],
        $row['nombres_invitados'],
        $row['alergias_invitados'],
        $row['canciones'],
        $row['comentario'],
        $row['especificaciones_alimentarias'],
        $row['fecha_registro'],
        $row['fecha_actualizacion']
    ];

    fputcsv($output, $data, ';');
}

fclose($output);
$conn->close();
exit;
?>
