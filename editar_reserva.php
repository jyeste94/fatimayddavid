<?php
/**
 * Editar Reserva de Boda
 * Página para buscar y editar una reserva existente
 */

require_once 'config.php';

$mostrar_formulario_busqueda = true;
$mostrar_formulario_edicion = false;
$error = '';
$reserva = null;

// Procesar búsqueda de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_reserva'])) {
    $nombre = sanitizeInput($_POST['nombre_buscar'] ?? '');
    $password = sanitizeInput($_POST['password_buscar'] ?? '');

    if (empty($nombre) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $conn = getDBConnection();
        if ($conn) {
            $reserva = verificarCredenciales($conn, $nombre, $password);

            if ($reserva) {
                $mostrar_formulario_busqueda = false;
                $mostrar_formulario_edicion = true;
            } else {
                $error = 'No se encontró ninguna reserva con ese nombre y contraseña.';
            }

            $conn->close();
        } else {
            $error = 'Error de conexión a la base de datos.';
        }
    }
}

// Procesar actualización de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_reserva'])) {
    $conn = getDBConnection();

    if ($conn) {
        $id = intval($_POST['reserva_id']);
        $asistencia = sanitizeInput($_POST['asistencia'] ?? '');
        $telefono = sanitizeInput($_POST['telefono'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $viene_acompanante = sanitizeInput($_POST['viene_acompanante'] ?? '');
        $num_adultos = intval($_POST['num_adultos'] ?? 0);
        $num_ninos = intval($_POST['num_ninos'] ?? 0);
        $carne = intval($_POST['carne'] ?? 0);
        $pescado = intval($_POST['pescado'] ?? 0);
        $nombres_invitados = sanitizeInput($_POST['nombres_invitados'] ?? '');
        $alergias_invitados = sanitizeInput($_POST['alergias_invitados'] ?? '');
        $canciones = sanitizeInput($_POST['canciones'] ?? '');
        $comentario = sanitizeInput($_POST['comentario'] ?? '');
        $especificaciones_alimentarias = sanitizeInput($_POST['especificaciones_alimentarias'] ?? '');

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
            "ssssiiissssssi",
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
            $id
        );

        if ($stmt->execute()) {
            header('Location: procesar_reserva.php?actualizado=1');
            exit;
        } else {
            $error = 'Error al actualizar la reserva.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reserva - Boda Fátima & David</title>
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
            padding: 40px 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }

        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #333;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 600px) {
            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✨ Editar Reserva ✨</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($mostrar_formulario_busqueda): ?>
            <div class="info-box">
                <strong>ℹ️ Información:</strong> Introduce tu nombre y la contraseña que usaste al confirmar tu asistencia para poder editar tu reserva.
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre_buscar">Nombre:</label>
                    <input type="text" id="nombre_buscar" name="nombre_buscar" required>
                </div>

                <div class="form-group">
                    <label for="password_buscar">Contraseña:</label>
                    <input type="password" id="password_buscar" name="password_buscar" required>
                </div>

                <button type="submit" name="buscar_reserva" class="btn">Buscar mi Reserva</button>
            </form>

            <a href="index.html" class="back-link">← Volver a la invitación</a>
        <?php endif; ?>

        <?php if ($mostrar_formulario_edicion && $reserva): ?>
            <h2>Editar Reserva de: <?php echo htmlspecialchars($reserva['nombre']); ?></h2>

            <form method="POST" action="">
                <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">

                <div class="form-group">
                    <label for="asistencia">¿Asistirás?</label>
                    <select id="asistencia" name="asistencia" required>
                        <option value="">Selecciona una opción</option>
                        <option value="Acepto con mucho placer" <?php echo ($reserva['asistencia'] == 'Acepto con mucho placer') ? 'selected' : ''; ?>>Acepto con mucho placer</option>
                        <option value="Declino asistencia" <?php echo ($reserva['asistencia'] == 'Declino asistencia') ? 'selected' : ''; ?>>Declino asistencia</option>
                    </select>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($reserva['telefono']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($reserva['email']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="viene_acompanante">¿Vienes con acompañantes?</label>
                    <select id="viene_acompanante" name="viene_acompanante">
                        <option value="">Selecciona una opción</option>
                        <option value="No, Solo yo" <?php echo ($reserva['viene_acompanante'] == 'No, Solo yo') ? 'selected' : ''; ?>>No, Solo yo</option>
                        <option value="Si, voy con acompañantes" <?php echo ($reserva['viene_acompanante'] == 'Si, voy con acompañantes') ? 'selected' : ''; ?>>Sí, voy con acompañantes</option>
                    </select>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="num_adultos">Nº de adultos:</label>
                        <select id="num_adultos" name="num_adultos">
                            <?php for ($i = 0; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($reserva['num_adultos'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="num_ninos">Nº de niños:</label>
                        <select id="num_ninos" name="num_ninos">
                            <?php for ($i = 0; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($reserva['num_ninos'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label for="carne">Eligen carne:</label>
                        <select id="carne" name="carne">
                            <?php for ($i = 0; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($reserva['carne'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="pescado">Eligen pescado:</label>
                        <select id="pescado" name="pescado">
                            <?php for ($i = 0; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($reserva['pescado'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="nombres_invitados">Nombres de los acompañantes:</label>
                    <textarea id="nombres_invitados" name="nombres_invitados"><?php echo htmlspecialchars($reserva['nombres_invitados']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="alergias_invitados">Alergias o intolerancias:</label>
                    <textarea id="alergias_invitados" name="alergias_invitados"><?php echo htmlspecialchars($reserva['alergias_invitados']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="canciones">Canciones favoritas:</label>
                    <textarea id="canciones" name="canciones"><?php echo htmlspecialchars($reserva['canciones']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="comentario">Comentarios o sugerencias:</label>
                    <textarea id="comentario" name="comentario"><?php echo htmlspecialchars($reserva['comentario']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="especificaciones_alimentarias">Especificaciones alimentarias:</label>
                    <input type="text" id="especificaciones_alimentarias" name="especificaciones_alimentarias" value="<?php echo htmlspecialchars($reserva['especificaciones_alimentarias']); ?>" placeholder="Ej: Vegetariano, Vegano, Celíaco...">
                </div>

                <button type="submit" name="actualizar_reserva" class="btn">Actualizar Reserva</button>
                <a href="editar_reserva.php" class="btn btn-secondary" style="display: block; text-align: center; text-decoration: none;">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
