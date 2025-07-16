<?php
session_start();
require 'db_connect.php';

// Seguridad: if ($_SESSION['rol'] !== 'admin') { header('Location: principal.php'); exit(); }

// 1. Validar que se recibió un ID de torneo válido
if (!isset($_GET['torneo_id']) || !is_numeric($_GET['torneo_id'])) {
    header('Location: admin_torneos.php');
    exit();
}
$id_torneo = $_GET['torneo_id'];

// 2. Obtener los datos del torneo para el encabezado
$stmt_torneo = $conn->prepare("SELECT nombre, precio_entrada FROM torneos WHERE id = ?");
$stmt_torneo->bind_param("i", $id_torneo);
$stmt_torneo->execute();
$torneo = $stmt_torneo->get_result()->fetch_assoc();

if (!$torneo) {
    die("Error: Torneo no encontrado.");
}

// 3. Obtener la lista de equipos inscritos en este torneo
$sql_inscritos = "SELECT 
                        e.nombre_equipo, 
                        e.logo_url, 
                        u.nombre_usuario AS nombre_capitan, 
                        i.fecha_inscripcion, 
                        i.id_transaccion_pago
                  FROM inscripciones i
                  JOIN equipos e ON i.id_equipo = e.id
                  JOIN usuarios u ON e.id_capitan = u.id
                  WHERE i.id_torneo = ?
                  ORDER BY i.fecha_inscripcion ASC";
$stmt_inscritos = $conn->prepare($sql_inscritos);
$stmt_inscritos->bind_param("i", $id_torneo);
$stmt_inscritos->execute();
$inscritos = $stmt_inscritos->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Inscritos en <?php echo htmlspecialchars($torneo['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .team-logo-small { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
    </style>
</head>
<body style="background-color: #1a1a2e; color: #fff;">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="admin_torneos.php" class="text-white-50 text-decoration-none">« Volver a Torneos</a>
                <h1 class="mt-2">Equipos Inscritos</h1>
                <h3 class="text-white-50"><?php echo htmlspecialchars($torneo['nombre']); ?></h3>
            </div>
            <div class="text-end">
                <h4>Total Recaudado</h4>
                <p class="display-6 text-success fw-bold">
                    $<?php echo number_format($torneo['precio_entrada'] * count($inscritos), 2); ?>
                </p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Capitán</th>
                        <th>Fecha de Inscripción</th>
                        <th>ID Transacción</th>
                        <th>Monto Pagado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($inscritos) > 0): ?>
                        <?php foreach ($inscritos as $inscrito): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($inscrito['logo_url'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" class="team-logo-small">
                                    <?php echo htmlspecialchars($inscrito['nombre_equipo']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($inscrito['nombre_capitan']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($inscrito['fecha_inscripcion'])); ?></td>
                                <td><span class="font-monospace small"><?php echo htmlspecialchars($inscrito['id_transaccion_pago']); ?></span></td>
                                <td class="text-success">$<?php echo number_format($torneo['precio_entrada'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Aún no hay equipos inscritos en este torneo.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>