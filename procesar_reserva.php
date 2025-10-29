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
        $response['message'] = 'Por favor, indica tu nombre.';
        mostrarRespuesta($response);
        exit;
    }

    if (empty($email)) {
        $response['message'] = 'Por favor, indica tu email.';
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
            "ssssiiiissssssi",
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
 * Función para mostrar la respuesta al usuario
 */
function mostrarRespuesta($response) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmación de Reserva</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Arial', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                width: 100%;
                padding: 40px;
                text-align: center;
            }

            .icon {
                font-size: 80px;
                margin-bottom: 20px;
            }

            .success-icon {
                color: #4caf50;
            }

            .error-icon {
                color: #f44336;
            }

            h1 {
                color: #333;
                margin-bottom: 20px;
                font-size: 28px;
            }

            p {
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
                font-size: 16px;
            }

            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 40px;
                border-radius: 50px;
                text-decoration: none;
                font-weight: bold;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            }

            .info-box {
                background: #f5f5f5;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 30px;
                text-align: left;
            }

            .info-box strong {
                color: #667eea;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <?php if ($response['success']): ?>
                <div class="icon success-icon">✓</div>
                <h1>¡Confirmación Exitosa!</h1>
                <p><?php echo $response['message']; ?></p>

                <?php if ($response['accion'] === 'crear'): ?>
                    <div class="info-box">
                        <strong>Importante:</strong> Guarda tu contraseña, la necesitarás si deseas modificar tu reserva más adelante.
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="icon error-icon">✕</div>
                <h1>Error en la Reserva</h1>
                <p><?php echo $response['message']; ?></p>
            <?php endif; ?>

            <a href="index.html" class="btn">Volver a la Invitación</a>

            <?php if ($response['success']): ?>
                <br><br>
                <a href="editar_reserva.php" class="btn" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">Editar mi Reserva</a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
?>
