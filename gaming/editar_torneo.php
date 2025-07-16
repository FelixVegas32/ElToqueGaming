<?php
session_start();
require 'db_connect.php';
// Seguridad: if ($_SESSION['rol'] !== 'admin') { header('Location: principal.php'); exit(); }

// Validar que se recibió un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_torneos.php');
    exit();
}
$id_torneo = $_GET['id'];

// Obtener la lista de juegos para el dropdown
$juegos = $conn->query("SELECT id, nombre FROM juegos ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

// --- Lógica de Actualización (cuando se envía el formulario) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aquí iría toda la lógica de validación de los datos recibidos...
    $nombre = $_POST['nombre'];
    $id_juego = $_POST['id_juego'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $max_equipos = $_POST['max_equipos'];
    $estado = $_POST['estado'];
    // ... etc.

    $stmt = $conn->prepare("UPDATE torneos SET nombre = ?, id_juego = ?, fecha_inicio = ?, max_equipos = ?, estado = ? WHERE id = ?");
    $stmt->bind_param("sisisi", $nombre, $id_juego, $fecha_inicio, $max_equipos, $estado, $id_torneo);
    
    if ($stmt->execute()) {
        header("Location: admin_torneos.php");
        exit();
    } else {
        $error_message = "Error al actualizar el torneo.";
    }
}

// --- Obtener datos actuales del torneo para mostrarlos ---
$stmt = $conn->prepare("SELECT * FROM torneos WHERE id = ?");
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$torneo = $stmt->get_result()->fetch_assoc();

if (!$torneo) {
    // Si no se encuentra el torneo, redirigir
    header('Location: admin_torneos.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Torneo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #1a1a2e; color: #fff;">
    <div class="container my-5">
        <h1>Editar Torneo: <?php echo htmlspecialchars($torneo['nombre']); ?></h1>
        <?php if(isset($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

        <form action="editar_torneo.php?id=<?php echo $id_torneo; ?>" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Torneo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($torneo['nombre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="id_juego" class="form-label">Juego</label>
                <select class="form-select" id="id_juego" name="id_juego" required>
                    <?php foreach ($juegos as $juego): ?>
                        <option value="<?php echo $juego['id']; ?>" <?php if($juego['id'] == $torneo['id_juego']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($juego['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha y Hora de Inicio</label>
                <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d\TH:i', strtotime($torneo['fecha_inicio'])); ?>" required>
            </div>
             <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado" required>
                    <option value="Programado" <?php if($torneo['estado'] == 'Programado') echo 'selected'; ?>>Programado</option>
                    <option value="En Curso" <?php if($torneo['estado'] == 'En Curso') echo 'selected'; ?>>En Curso</option>
                    <option value="Finalizado" <?php if($torneo['estado'] == 'Finalizado') echo 'selected'; ?>>Finalizado</option>
                    <option value="Cancelado" <?php if($torneo['estado'] == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
                </select>
            </div>
            <!-- Añade aquí los demás campos para editar (max_equipos, etc.) -->

            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="admin_torneos.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>