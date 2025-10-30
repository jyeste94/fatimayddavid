<?php
/**
 * Procesar Reserva de Boda
 * Script para procesar el formulario de confirmación de asistencia
 */

require_once 'config.php';

// Inicializar variables de respuesta
$response = [
    'success' => false,
    'message' => '',
    'accion' => ''
];

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener conexión a la base de datos
    $conn = getDBConnection();

    if (!$conn) {
        $response['message'] = 'Error de conexión a la base de datos. Por favor, inténtalo más tarde.';
        mostrarRespuesta($response);
        exit;
    }

    // Sanitizar y obtener datos del formulario
    $asistencia = sanitizeInput($_POST['et_pb_contact_asistencia_0'] ?? '');
    $nombre = sanitizeInput($_POST['et_pb_contact_nombre_0'] ?? '');
    $password = sanitizeInput($_POST['et_pb_contact_password_0'] ?? '');
    $telefono = sanitizeInput($_POST['et_pb_contact_telefono_0'] ?? '');
    $email = sanitizeInput($_POST['et_pb_contact_email_0'] ?? '');
    $viene_acompanante = sanitizeInput($_POST['et_pb_contact_vieneacompanante_0'] ?? '');
    $num_adultos = intval($_POST['et_pb_contact_adultos_0'] ?? 0);
    $num_ninos = intval($_POST['et_pb_contact_ninos_0'] ?? 0);
    $carne = intval($_POST['et_pb_contact_carne_0'] ?? 0);
    $pescado = intval($_POST['et_pb_contact_pescado_0'] ?? 0);
    $nombres_invitados = sanitizeInput($_POST['et_pb_contact_nombreinvitados_0'] ?? '');
    $alergias_invitados = sanitizeInput($_POST['et_pb_contact_alergiasinvitados_0'] ?? '');
    $canciones = sanitizeInput($_POST['et_pb_contact_canciones_0'] ?? '');
    $comentario = sanitizeInput($_POST['et_pb_contact_comentario_0'] ?? '');

    // Procesar checkboxes de especificaciones alimentarias
    $especificaciones = [];
    if (isset($_POST['et_pb_contact_especificaciones_alimentarias_18_0'])) {
        $especificaciones[] = 'VEGETARIANO';
    }
    if (isset($_POST['et_pb_contact_especificaciones_alimentarias_18_1'])) {
        $especificaciones[] = 'VEGANO';
    }
    if (isset($_POST['et_pb_contact_especificaciones_alimentarias_18_2'])) {
        $especificaciones[] = 'CELIACO';
    }
    if (isset($_POST['et_pb_contact_especificaciones_alimentarias_18_3'])) {
        $especificaciones[] = 'INTOLERANCIA A LA LACTOSA';
    }
    if (isset($_POST['et_pb_contact_especificaciones_alimentarias_18_4'])) {
        $especificaciones[] = 'INTOLERANCIA AL HUEVO';
    }
    if (isset($_POST['et_pb_contact_especificaciones_alimentarias_18_5'])) {
        $especificaciones[] = 'INTOLERANCIA A LOS FRUTOS SECOS';
    }
    $especificaciones_alimentarias = implode(', ', $especificaciones);

    // Validar campos obligatorios
    if (empty($asistencia)) {
        $response['message'] = 'Por favor, indica si asistirás a la boda.';
        mostrarRespuesta($response);
        exit;
    }

    if (empty($nombre)) {
        $response['message'] = 'Por favor, indica tu nombre completo.';
        mostrarRespuesta($response);
        exit;
    }

    if (empty($email)) {
        $response['message'] = 'Por favor, introduce tu dirección de email.';
        mostrarRespuesta($response);
        exit;
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Por favor, introduce un email válido.';
        mostrarRespuesta($response);
        exit;
    }

    // Validación adicional: si acepta, debe indicar si viene con acompañantes
    if ($asistencia === 'Acepto con mucho placer' && empty($viene_acompanante)) {
        $response['message'] = 'Por favor, indica si vendrás solo/a o con acompañantes.';
        mostrarRespuesta($response);
        exit;
    }

    // Verificar si ya existe una reserva con este email
    $reserva_id = null;
    $es_actualizacion = false;

    // Si tiene contraseña, verificar si existe reserva con email + contraseña
    if (!empty($password)) {
        $stmt_check = $conn->prepare("SELECT id FROM reservas WHERE email = ? AND password = ?");
        $stmt_check->bind_param("ss", $email, $password);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            $reserva_id = $row['id'];
            $es_actualizacion = true;
        }
        $stmt_check->close();
    }

    if ($es_actualizacion) {
        // Actualizar reserva existente
        $stmt = $conn->prepare("
            UPDATE reservas SET
                asistencia = ?,
                telefono = ?,
                email = ?,
                viene_acompanante = ?,
                num_adultos = ?,
                num_ninos = ?,
                carne = ?,
                pescado = ?,
                nombres_invitados = ?,
                alergias_invitados = ?,
                canciones = ?,
                comentario = ?,
                especificaciones_alimentarias = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssssiiiiissssi",
            $asistencia,
            $telefono,
            $email,
            $viene_acompanante,
            $num_adultos,
            $num_ninos,
            $carne,
            $pescado,
            $nombres_invitados,
            $alergias_invitados,
            $canciones,
            $comentario,
            $especificaciones_alimentarias,
            $reserva_id
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['accion'] = 'actualizar';
            $response['message'] = '¡Tu reserva ha sido actualizada correctamente!';
        } else {
            $response['message'] = 'Error al actualizar la reserva: ' . $stmt->error;
        }

        $stmt->close();

    } else {
        // Crear nueva reserva
        $stmt = $conn->prepare("
            INSERT INTO reservas (
                nombre,
                password,
                asistencia,
                telefono,
                email,
                viene_acompanante,
                num_adultos,
                num_ninos,
                carne,
                pescado,
                nombres_invitados,
                alergias_invitados,
                canciones,
                comentario,
                especificaciones_alimentarias
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssssiiiisssss",
            $nombre,
            $password,
            $asistencia,
            $telefono,
            $email,
            $viene_acompanante,
            $num_adultos,
            $num_ninos,
            $carne,
            $pescado,
            $nombres_invitados,
            $alergias_invitados,
            $canciones,
            $comentario,
            $especificaciones_alimentarias
        );

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['accion'] = 'crear';
            $response['message'] = '¡Tu reserva ha sido confirmada correctamente! Gracias por confirmar tu asistencia.';
        } else {
            $response['message'] = 'Error al crear la reserva: ' . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();

} else {
    $response['message'] = 'Método de solicitud no válido.';
}

mostrarRespuesta($response);

/**
 * Función para mostrar la respuesta al usuario en formato JSON
 */
function mostrarRespuesta($response) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
