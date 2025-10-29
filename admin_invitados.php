<?php
/**
 * Panel de Administración - Invitados de Boda
 * Página para ver y gestionar todas las reservas
 */

require_once 'config.php';

// Verificar token de acceso
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($token !== ADMIN_TOKEN) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Denegado</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            .error-container {
                background: white;
                border-radius: 20px;
                padding: 60px 40px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            }
            .error-icon {
                font-size: 80px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                margin-bottom: 15px;
                font-size: 32px;
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
                transition: transform 0.3s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
            }
            code {
                background: #f5f5f5;
                padding: 3px 8px;
                border-radius: 4px;
                font-family: monospace;
                color: #e83e8c;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">🔒</div>
            <h1>Acceso Denegado</h1>
            <p>No tienes permiso para acceder a esta página. Se requiere un token válido en la URL.</p>
            <p style="font-size: 14px; color: #999;">Formato: <code>admin_invitados.php?token=TU_TOKEN</code></p>
            <a href="index.html" class="btn">← Volver a la Invitación</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Parámetros de paginación y filtros
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$offset = ($page - 1) * $per_page;

$filtro_asistencia = isset($_GET['asistencia']) ? sanitizeInput($_GET['asistencia']) : '';
$busqueda = isset($_GET['buscar']) ? sanitizeInput($_GET['buscar']) : '';

// Obtener conexión
$conn = getDBConnection();
if (!$conn) {
    die("Error de conexión a la base de datos");
}

// Construir query con filtros
$where_clauses = [];
$params = [];
$types = '';

if ($filtro_asistencia !== '') {
    $where_clauses[] = "asistencia = ?";
    $params[] = $filtro_asistencia;
    $types .= 's';
}

if ($busqueda !== '') {
    $where_clauses[] = "(nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)";
    $search_term = "%{$busqueda}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'sss';
}

$where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Contar total de registros
$count_sql = "SELECT COUNT(*) as total FROM reservas $where_sql";
$stmt = $conn->prepare($count_sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$total_registros = $result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $per_page);

// Obtener registros
$sql = "SELECT * FROM reservas $where_sql ORDER BY fecha_registro DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservas = $stmt->get_result();

// Obtener estadísticas
$stats_sql = "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN asistencia = 'Acepto con mucho placer' THEN 1 ELSE 0 END) as aceptan,
        SUM(CASE WHEN asistencia = 'Declino asistencia' THEN 1 ELSE 0 END) as declinan,
        SUM(CASE WHEN asistencia = 'Acepto con mucho placer' THEN num_adultos ELSE 0 END) as total_adultos,
        SUM(CASE WHEN asistencia = 'Acepto con mucho placer' THEN num_ninos ELSE 0 END) as total_ninos,
        SUM(CASE WHEN asistencia = 'Acepto con mucho placer' THEN carne ELSE 0 END) as total_carne,
        SUM(CASE WHEN asistencia = 'Acepto con mucho placer' THEN pescado ELSE 0 END) as total_pescado
    FROM reservas
";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Invitados - Boda Fátima & David</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 32px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filters {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .filters form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            color: #333;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .filter-group input,
        .filter-group select {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-export {
            background: #28a745;
            color: white;
            margin-left: 10px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f8f9fa;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .pagination {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pagination-links {
            display: flex;
            gap: 10px;
        }

        .pagination-links a,
        .pagination-links span {
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .pagination-links a:hover {
            background: #667eea;
            color: white;
        }

        .pagination-links .active {
            background: #667eea;
            color: white;
        }

        .detail-btn {
            padding: 8px 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .detail-btn:hover {
            background: #764ba2;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 20px;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .modal-header h2 {
            color: #667eea;
            font-size: 24px;
        }

        .close {
            font-size: 32px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
            line-height: 1;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #333;
        }

        .detail-grid {
            display: grid;
            gap: 15px;
        }

        .detail-item {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 10px;
        }

        .detail-label {
            font-weight: bold;
            color: #667eea;
        }

        .detail-value {
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .filters form {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .pagination {
                flex-direction: column;
                gap: 20px;
            }

            .table-container {
                overflow-x: scroll;
            }

            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💒 Panel de Administración</h1>
            <p>Gestión de invitados - Boda Fátima & David - 11 de Abril 2026</p>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo $stats['total']; ?></div>
                <div class="label">Total Respuestas</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['aceptan']; ?></div>
                <div class="label">Confirman</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['declinan']; ?></div>
                <div class="label">Declinan</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['total_adultos'] + $stats['total_ninos']; ?></div>
                <div class="label">Total Personas</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['total_adultos']; ?></div>
                <div class="label">Adultos</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['total_ninos']; ?></div>
                <div class="label">Niños</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['total_carne']; ?></div>
                <div class="label">Menú Carne</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $stats['total_pescado']; ?></div>
                <div class="label">Menú Pescado</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters">
            <form method="GET" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="filter-group">
                    <label for="buscar">Buscar:</label>
                    <input type="text" id="buscar" name="buscar" placeholder="Nombre, email o teléfono..." value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <div class="filter-group">
                    <label for="asistencia">Asistencia:</label>
                    <select id="asistencia" name="asistencia">
                        <option value="">Todos</option>
                        <option value="Acepto con mucho placer" <?php echo $filtro_asistencia === 'Acepto con mucho placer' ? 'selected' : ''; ?>>Confirman</option>
                        <option value="Declino asistencia" <?php echo $filtro_asistencia === 'Declino asistencia' ? 'selected' : ''; ?>>Declinan</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="per_page">Por página:</label>
                    <select id="per_page" name="per_page">
                        <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>

        <!-- Botones de acción -->
        <div style="margin-bottom: 20px; display: flex; gap: 10px;">
            <a href="exportar_excel.php?token=<?php echo urlencode($token); ?>" class="btn btn-export">📊 Exportar a Excel</a>
            <a href="index.html" class="btn btn-secondary">← Volver a la Invitación</a>
        </div>

        <!-- Tabla de invitados -->
        <div class="table-container">
            <?php if ($reservas->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Asistencia</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Personas</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($reserva = $reservas->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($reserva['nombre']); ?></strong></td>
                                <td>
                                    <?php if ($reserva['asistencia'] === 'Acepto con mucho placer'): ?>
                                        <span class="badge badge-success">✓ Confirma</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">✗ Declina</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($reserva['email'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($reserva['telefono'] ?: '-'); ?></td>
                                <td>
                                    <?php
                                    $total_personas = $reserva['num_adultos'] + $reserva['num_ninos'];
                                    echo $total_personas > 0 ? $total_personas : '-';
                                    if ($total_personas > 0) {
                                        echo " ({$reserva['num_adultos']}A, {$reserva['num_ninos']}N)";
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($reserva['fecha_registro'])); ?></td>
                                <td>
                                    <button class="detail-btn" onclick="mostrarDetalle(<?php echo htmlspecialchars(json_encode($reserva)); ?>)">
                                        Ver Detalle
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3>No se encontraron invitados</h3>
                    <p>Prueba con otros filtros o espera a que los invitados confirmen su asistencia</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
            <div class="pagination">
                <div>
                    Mostrando <?php echo ($offset + 1); ?> - <?php echo min($offset + $per_page, $total_registros); ?> de <?php echo $total_registros; ?> registros
                </div>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="?token=<?php echo urlencode($token); ?>&page=<?php echo $page - 1; ?>&per_page=<?php echo $per_page; ?>&asistencia=<?php echo urlencode($filtro_asistencia); ?>&buscar=<?php echo urlencode($busqueda); ?>">« Anterior</a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_paginas, $page + 2);

                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?token=<?php echo urlencode($token); ?>&page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>&asistencia=<?php echo urlencode($filtro_asistencia); ?>&buscar=<?php echo urlencode($busqueda); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_paginas): ?>
                        <a href="?token=<?php echo urlencode($token); ?>&page=<?php echo $page + 1; ?>&per_page=<?php echo $per_page; ?>&asistencia=<?php echo urlencode($filtro_asistencia); ?>&buscar=<?php echo urlencode($busqueda); ?>">Siguiente »</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de detalle -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalNombre"></h2>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div id="modalBody" class="detail-grid"></div>
        </div>
    </div>

    <script>
        function mostrarDetalle(reserva) {
            document.getElementById('modalNombre').textContent = reserva.nombre;

            let html = `
                <div class="detail-item">
                    <div class="detail-label">Asistencia:</div>
                    <div class="detail-value">${reserva.asistencia}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">${reserva.email || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Teléfono:</div>
                    <div class="detail-value">${reserva.telefono || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Acompañantes:</div>
                    <div class="detail-value">${reserva.viene_acompanante || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Adultos:</div>
                    <div class="detail-value">${reserva.num_adultos || 0}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Niños:</div>
                    <div class="detail-value">${reserva.num_ninos || 0}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Menú Carne:</div>
                    <div class="detail-value">${reserva.carne || 0}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Menú Pescado:</div>
                    <div class="detail-value">${reserva.pescado || 0}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nombres invitados:</div>
                    <div class="detail-value">${reserva.nombres_invitados || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Alergias:</div>
                    <div class="detail-value">${reserva.alergias_invitados || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Canciones:</div>
                    <div class="detail-value">${reserva.canciones || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Comentarios:</div>
                    <div class="detail-value">${reserva.comentario || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Especificaciones:</div>
                    <div class="detail-value">${reserva.especificaciones_alimentarias || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Fecha registro:</div>
                    <div class="detail-value">${new Date(reserva.fecha_registro).toLocaleString('es-ES')}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Última actualización:</div>
                    <div class="detail-value">${new Date(reserva.fecha_actualizacion).toLocaleString('es-ES')}</div>
                </div>
            `;

            document.getElementById('modalBody').innerHTML = html;
            document.getElementById('modalDetalle').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalDetalle').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalle');
            if (event.target === modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
