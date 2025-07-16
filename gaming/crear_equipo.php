<?php
session_start();
require 'db_connect.php';

// ==========================================================
// INICIO DE LA CORRECCIÓN DEFINITIVA
// ==========================================================
// Cambiamos la condición `$_SESSION['id_equipo_actual']` por `!empty($_SESSION['id_equipo_actual'])`.
// `!empty()` solo será VERDADERO si hay un ID de equipo real (ej: 1, 2, 3...).
// Si es NULL, 0, o un string vacío (como puede venir de la sesión), será FALSO, permitiendo el acceso.
if (!isset($_SESSION['loggedin']) || $_SESSION['tipo_usuario'] !== 'jugador' || !empty($_SESSION['id_equipo_actual'])) {
    header('Location: principal.php');
    exit();
}
// ==========================================================
// FIN DE LA CORRECCIÓN DEFINITIVA
// ==========================================================


$error_message = '';
$solicitud_enviada = false;
$id_solicitante = $_SESSION['userid'];

// Comprobar si el usuario ya tiene una solicitud pendiente.
$stmt_check_pending = $conn->prepare("SELECT id FROM solicitudes_equipo WHERE id_solicitante = ? AND estado = 'pendiente'");
$stmt_check_pending->bind_param("i", $id_solicitante);
$stmt_check_pending->execute();
$result_pending = $stmt_check_pending->get_result();
if ($result_pending->num_rows > 0) {
    $solicitud_enviada = true;
}
$stmt_check_pending->close();


// Procesar el formulario solo si no tiene una solicitud pendiente.
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$solicitud_enviada) {
    $nombre_equipo = trim($_POST['nombre_equipo']);

    if (empty($nombre_equipo)) {
        $error_message = "El nombre del equipo no puede estar vacío.";
    } else {
        $stmt_check_name = $conn->prepare("SELECT id FROM equipos WHERE nombre_equipo = ? UNION SELECT id FROM solicitudes_equipo WHERE nombre_equipo_propuesto = ? AND estado = 'pendiente'");
        $stmt_check_name->bind_param("ss", $nombre_equipo, $nombre_equipo);
        $stmt_check_name->execute();
        if ($stmt_check_name->get_result()->num_rows > 0) {
            $error_message = "Este nombre de equipo ya está en uso o en una solicitud pendiente. Por favor, elige otro.";
        } else {
            $logo_url = 'https://img.freepik.com/vector-gratis/logotipo-juegos-diseno-plano_23-2150747169.jpg?w=740';
            if (isset($_FILES['logo_equipo']) && $_FILES['logo_equipo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/team_logos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $file_extension = pathinfo($_FILES['logo_equipo']['name'], PATHINFO_EXTENSION);
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array(strtolower($file_extension), $allowed_types)) {
                    $new_filename = 'team_request_' . $id_solicitante . '_' . time() . '.' . $file_extension;
                    if (move_uploaded_file($_FILES['logo_equipo']['tmp_name'], $upload_dir . $new_filename)) {
                        $logo_url = $upload_dir . $new_filename;
                    }
                }
            }
            
            $stmt_insert_request = $conn->prepare("INSERT INTO solicitudes_equipo (id_solicitante, nombre_equipo_propuesto, logo_url_propuesto) VALUES (?, ?, ?)");
            $stmt_insert_request->bind_param("iss", $id_solicitante, $nombre_equipo, $logo_url);

            if ($stmt_insert_request->execute()) {
                $solicitud_enviada = true;
            } else {
                $error_message = "Hubo un error al enviar la solicitud. Por favor, inténtalo de nuevo.";
            }
            $stmt_insert_request->close();
        }
        $stmt_check_name->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Creación de Equipo - ElToqueGaming</title>
    <!-- Tus enlaces a CSS y Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #1a1a2e; color: #ffffff; font-family: 'Roboto', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .form-container { background-color: #162447; padding: 2rem 3rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 600px; }
        .form-container h2 { font-family: 'Montserrat', sans-serif; font-weight: 700; color: #e84364; margin-bottom: 1.5rem; text-align: center; }
        .form-control, .form-control:focus { background-color: #1f4068; border: 1px solid #1f4068; color: #fff; }
        .btn-submit { background-color: #e84364; border-color: #e84364; font-weight: 700; width: 100%; padding: 0.75rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- El resto del HTML no cambia -->
        <?php if ($solicitud_enviada): ?>
            <div class="text-center">
                <i class="fas fa-paper-plane fa-4x text-success mb-3"></i>
                <h2 class="text-white">¡Solicitud Enviada!</h2>
                <p class="lead text-white-50">Tu solicitud para crear el equipo ha sido enviada al sistema para su revisión. Recibirás una notificación cuando sea aprobada por un administrador.</p>
                <a href="principal.php" class="btn btn-primary mt-3">Volver a la Página Principal</a>
            </div>
        <?php else: ?>
            <h2>Solicitar Creación de Equipo</h2>
            <p class="text-center text-white-50 mb-4">Tu solicitud será revisada por un administrador antes de ser aprobada.</p>

            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form action="crear_equipo.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nombre_equipo" class="form-label">Nombre del Equipo</label>
                    <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" required>
                </div>
                <div class="mb-4">
                    <label for="logo_equipo" class="form-label">Logo del Equipo (Opcional)</label>
                    <input class="form-control" type="file" id="logo_equipo" name="logo_equipo">
                    <div class="form-text text-white-50">Si no subes una imagen, se usará una por defecto.</div>
                </div>
                
                <button type="submit" class="btn btn-submit">Enviar Solicitud de Creación</button>
                <div class="text-center mt-3">
                    <a href="seccion.php" class="text-white-50">Volver a la selección</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>