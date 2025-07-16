<?php
session_start();
require 'db_connect.php';

// Seguridad: Solo jugadores logueados y sin equipo pueden acceder.
if (!isset($_SESSION['loggedin']) || $_SESSION['tipo_usuario'] !== 'jugador' || !empty($_SESSION['id_equipo_actual'])) {
    header('Location: principal.php');
    exit();
}

$id_jugador_actual = $_SESSION['userid'];
$error_message = '';
$success_message = '';

// --- PROCESAR UNA NUEVA SOLICITUD DE UNIÓN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['solicitar_union'])) {
    $id_equipo_solicitado = $_POST['id_equipo'];

    // Insertar la nueva solicitud en la tabla 'solicitudes_union'.
    // La clave UNIQUE en la BD previene duplicados, pero podemos añadir un mensaje amigable.
    $stmt = $conn->prepare("INSERT INTO solicitudes_union (id_jugador, id_equipo) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_jugador_actual, $id_equipo_solicitado);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "¡Tu solicitud para unirte al equipo ha sido enviada!";
    } else {
        // Esto probablemente ocurra si ya existe una solicitud (por la clave UNIQUE).
        $_SESSION['error_message'] = "Ya tienes una solicitud pendiente para este equipo.";
    }
    header("Location: unirse_equipo.php");
    exit();
}

// --- OBTENER DATOS PARA MOSTRAR LA PÁGINA ---

// 1. Obtener la lista de todos los equipos junto con el nombre de su capitán.
// Usamos un JOIN para conectar la tabla 'equipos' con la tabla 'usuarios'.
$sql_equipos = "SELECT e.id, e.nombre_equipo, e.logo_url, u.nombre_usuario AS nombre_capitan 
                FROM equipos e 
                JOIN usuarios u ON e.id_capitan = u.id 
                ORDER BY e.nombre_equipo ASC";
$resultado_equipos = $conn->query($sql_equipos);
$equipos = $resultado_equipos->fetch_all(MYSQLI_ASSOC);

// 2. Obtener una lista de los equipos a los que este jugador YA ha enviado una solicitud pendiente.
$stmt_pendientes = $conn->prepare("SELECT id_equipo FROM solicitudes_union WHERE id_jugador = ? AND estado = 'pendiente'");
$stmt_pendientes->bind_param("i", $id_jugador_actual);
$stmt_pendientes->execute();
$resultado_pendientes = $stmt_pendientes->get_result();
$solicitudes_pendientes = [];
while($fila = $resultado_pendientes->fetch_assoc()) {
    $solicitudes_pendientes[] = $fila['id_equipo'];
}

// Gestionar mensajes de la sesión.
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unirse a un Equipo - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #1a1a2e; color: #ffffff; font-family: 'Roboto', sans-serif; }
        .team-card { background-color: #162447; border-radius: 15px; padding: 1.5rem; text-align: center; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; justify-content: space-between;}
        .team-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .team-logo { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #e84364; margin-bottom: 1rem; }
        .team-card h5 { font-family: 'Montserrat', sans-serif; font-weight: 700; }
        .btn-solicitar { background-color: #e84364; border-color: #e84364; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold">Únete a un Equipo</h1>
            <p class="lead text-white-50">Explora los equipos disponibles y envía una solicitud para unirte a la batalla.</p>
        </div>

        <?php if (!empty($success_message)): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if (!empty($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

        <div class="row">
            <?php if (count($equipos) > 0): ?>
                <?php foreach ($equipos as $equipo): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="team-card">
                            <div>
                                <img src="<?php echo htmlspecialchars($equipo['logo_url']); ?>" alt="Logo de <?php echo htmlspecialchars($equipo['nombre_equipo']); ?>" class="team-logo">
                                <h5><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></h5>
                                <p class="text-white-50 mb-3">Capitán: <?php echo htmlspecialchars($equipo['nombre_capitan']); ?></p>
                            </div>
                            <form action="unirse_equipo.php" method="POST">
                                <input type="hidden" name="id_equipo" value="<?php echo $equipo['id']; ?>">
                                <?php 
                                // Comprobar si el ID de este equipo está en la lista de solicitudes pendientes del jugador.
                                $ya_solicitado = in_array($equipo['id'], $solicitudes_pendientes);
                                ?>
                                <button type="submit" name="solicitar_union" class="btn btn-solicitar w-100" <?php if ($ya_solicitado) echo 'disabled'; ?>>
                                    <?php echo $ya_solicitado ? 'Solicitud Pendiente' : 'Solicitar Unirse'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">Actualmente no hay equipos disponibles para unirse. ¡Anímate a crear el primero!</div>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="seccion.php" class="btn btn-outline-light">Volver a la selección</a>
        </div>
    </div>
</body>
</html>