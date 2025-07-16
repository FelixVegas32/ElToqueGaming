<?php
session_start();
require 'db_connect.php';

// Seguridad: if ($_SESSION['rol'] !== 'admin') { header('Location: principal.php'); exit(); }

// Obtenemos todos los torneos y contamos los inscritos para cada uno
$sql = "SELECT t.*, j.nombre AS nombre_juego, 
               (SELECT COUNT(id) FROM inscripciones WHERE id_torneo = t.id) AS total_inscritos
        FROM torneos t 
        JOIN juegos j ON t.id_juego = j.id 
        ORDER BY t.fecha_inicio DESC";
$torneos = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Administrar Torneos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #1a1a2e; color: #fff;">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Panel de Administración de Torneos</h1>
            <a href="crear_torneo.php" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i>Crear Nuevo Torneo
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Torneo</th>
                        <th>Juego</th>
                        <th>Inscritos</th>
                        <th>Fecha de Inicio</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($torneos) > 0): ?>
                        <?php foreach ($torneos as $torneo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($torneo['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($torneo['nombre_juego']); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $torneo['total_inscritos']; ?> / <?php echo $torneo['max_equipos']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($torneo['fecha_inicio'])); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($torneo['estado']); ?></span></td>
                                <td class="text-center">
                                    <a href="inscritos.php?torneo_id=<?php echo $torneo['id']; ?>" class="btn btn-info btn-sm" title="Ver Inscritos">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <a href="gestionar_partidos.php?torneo_id=<?php echo $torneo['id']; ?>" class="btn btn-primary btn-sm" title="Gestionar Partidos">
                                        <i class="fas fa-sitemap"></i>
                                    </a>
                                    <a href="editar_torneo.php?id=<?php echo $torneo['id']; ?>" class="btn btn-warning btn-sm" title="Editar Torneo">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar_torneo.php?id=<?php echo $torneo['id']; ?>" class="btn btn-danger btn-sm" title="Eliminar Torneo" onclick="return confirm('¿Estás seguro de que quieres eliminar este torneo? Esta acción es irreversible.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay torneos creados todavía.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>


