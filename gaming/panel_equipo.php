<?php
session_start();
require 'db_connect.php';

// --- SEGURIDAD DE ACCESO ---
if (!isset($_SESSION['loggedin']) || empty($_SESSION['id_equipo_actual'])) {
    header('Location: principal.php');
    exit();
}

// --- INICIALIZACIÓN DE VARIABLES ---
$id_equipo = $_SESSION['id_equipo_actual'];
$id_usuario = $_SESSION['userid'];
$error_message = '';
$success_message = '';

// --- OBTENER DATOS DEL EQUIPO ---
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->bind_param("i", $id_equipo);
$stmt->execute();
$equipo = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$equipo) {
    header('Location: principal.php'); exit();
}
$es_capitan = ($equipo['id_capitan'] == $id_usuario);


// --- PROCESAR FORMULARIOS DE EDICIÓN (SOLO SI ES CAPITÁN) ---
if ($es_capitan && $_SERVER["REQUEST_METHOD"] == "POST") {
    // --- LÓGICA PARA CAMBIAR NOMBRE ---
    if (isset($_POST['cambiar_nombre'])) {
        if ($equipo['nombre_cambiado'] == 0) {
            $nuevo_nombre = trim($_POST['nuevo_nombre_equipo']);
            if (!empty($nuevo_nombre)) {
                $stmt_update_name = $conn->prepare("UPDATE equipos SET nombre_equipo = ?, nombre_cambiado = 1 WHERE id = ?");
                $stmt_update_name->bind_param("si", $nuevo_nombre, $id_equipo);
                if ($stmt_update_name->execute()) {
                    $_SESSION['success_message'] = "El nombre del equipo se ha actualizado correctamente.";
                    header("Location: panel_equipo.php");
                    exit();
                }
            } else {
                $error_message = "El nuevo nombre no puede estar vacío.";
            }
        }
    }

    // --- LÓGICA PARA CAMBIAR LOGO ---
    if (isset($_POST['cambiar_logo'])) {
        if (isset($_FILES['nuevo_logo_equipo']) && $_FILES['nuevo_logo_equipo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/team_logos/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_extension = strtolower(pathinfo($_FILES['nuevo_logo_equipo']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_extension, $allowed_types)) {
                $new_filename = 'team_' . $id_equipo . '_' . time() . '.' . $file_extension;
                
                // ==========================================================
                // INICIO DE LA CORRECCIÓN: Definir y usar $target_file
                // Esta variable contiene la ruta completa que se debe guardar.
                // ==========================================================
                $target_file = $upload_dir . $new_filename; 

                if (move_uploaded_file($_FILES['nuevo_logo_equipo']['tmp_name'], $target_file)) {
                    $stmt_update_logo = $conn->prepare("UPDATE equipos SET logo_url = ? WHERE id = ?");
                    // Se guarda la ruta completa ($target_file) en la base de datos.
                    $stmt_update_logo->bind_param("si", $target_file, $id_equipo);
                    // ==========================================================
                    // FIN DE LA CORRECCIÓN
                    // ==========================================================
                    
                    $stmt_update_logo->execute();
                    $_SESSION['success_message'] = "El logo se ha actualizado.";
                    header("Location: panel_equipo.php");
                    exit();
                }
            } else {
                $error_message = "Formato de imagen no válido.";
            }
        } else {
            $error_message = "Debes seleccionar un archivo de logo.";
        }
    }
}

// --- OBTENER SOLICITUDES DE UNIÓN PENDIENTES ---
$solicitudes_pendientes = [];
if ($es_capitan) {
    $sql_solicitudes = "SELECT su.id, u.nombre_usuario, u.foto_perfil_ruta 
                        FROM solicitudes_union su 
                        JOIN usuarios u ON su.id_jugador = u.id 
                        WHERE su.id_equipo = ? AND su.estado = 'pendiente'";
    $stmt_solicitudes = $conn->prepare($sql_solicitudes);
    $stmt_solicitudes->bind_param("i", $id_equipo);
    $stmt_solicitudes->execute();
    $solicitudes_pendientes = $stmt_solicitudes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_solicitudes->close();
}

// --- OBTENER MIEMBROS ACTUALES DEL EQUIPO ---
$sql_miembros = "SELECT u.nombre_usuario, u.foto_perfil_ruta, u.id AS id_usuario
                 FROM usuarios u 
                 WHERE u.id_equipo_actual = ?";
$stmt_miembros = $conn->prepare($sql_miembros);
$stmt_miembros->bind_param("i", $id_equipo);
$stmt_miembros->execute();
$miembros_equipo = $stmt_miembros->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_miembros->close();

// --- MOSTRAR MENSAJES ---
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
    <title>Panel de Equipo: <?php echo htmlspecialchars($equipo['nombre_equipo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #1a1a2e; color: #ffffff; font-family: 'Roboto', sans-serif; }
        .form-container, .management-section { background-color: #162447; padding: 2rem; border-radius: 15px; margin-top: 2rem; }
        .team-header { text-align: center; margin-bottom: 2rem; padding: 2rem; background-color: #162447; border-radius: 15px;}
        .team-logo { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #e84364; }
        .form-control, .form-control:focus { background-color: #1f4068; border: 1px solid #1f4068; color: #fff; }
        .btn-submit { background-color: #e84364; border-color: #e84364; font-weight: 700; }
        .btn-submit:disabled { background-color: #555; border-color: #555;}
        .list-item { display: flex; align-items: center; justify-content: space-between; background-color: #1f4068; padding: 0.75rem 1.25rem; border-radius: 10px; margin-bottom: 1rem; }
        .list-item img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="team-header">
            <img src="<?php echo htmlspecialchars($equipo['logo_url']); ?>" alt="Logo del Equipo" class="team-logo mb-3">
            <h1>Panel de Control del Equipo</h1>
            <h2><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></h2>
        </div>

        <?php if (!empty($success_message)): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if (!empty($error_message)): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

        <!-- SECCIÓN DE EDICIÓN (SOLO VISIBLE PARA EL CAPITÁN) -->
        <?php if ($es_capitan): ?>
            <div class="management-section">
                <h4 class="mb-4">Editar Información del Equipo</h4>
                <div class="row g-4">
                    <!-- Columna para cambiar nombre -->
                    <div class="col-lg-6">
                        <h5>Cambiar Nombre</h5>
                        <hr>
                        <?php if ($equipo['nombre_cambiado'] == 0): ?>
                            <form action="panel_equipo.php" method="POST">
                                <div class="mb-3">
                                    <label for="nuevo_nombre_equipo" class="form-label">Nuevo Nombre del Equipo</label>
                                    <input type="text" class="form-control" id="nuevo_nombre_equipo" name="nuevo_nombre_equipo" required>
                                    <div class="form-text text-white-50">¡Atención! Solo puedes cambiar el nombre una vez.</div>
                                </div>
                                <button type="submit" name="cambiar_nombre" class="btn btn-submit">Actualizar Nombre</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">El nombre de este equipo ya no puede ser modificado.</div>
                        <?php endif; ?>
                    </div>
                    <!-- Columna para cambiar logo -->
                    <div class="col-lg-6">
                        <h5>Cambiar Logo</h5>
                        <hr>
                        <form action="panel_equipo.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nuevo_logo_equipo" class="form-label">Seleccionar Nuevo Logo</label>
                                <input class="form-control" type="file" id="nuevo_logo_equipo" name="nuevo_logo_equipo" required>
                            </div>
                            <button type="submit" name="cambiar_logo" class="btn btn-submit">Actualizar Logo</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- SECCIÓN DE GESTIÓN DE SOLICITUDES (SOLO CAPITÁN) -->
        <?php if ($es_capitan): ?>
            <div class="management-section">
                <h4 class="mb-4">Solicitudes Pendientes para Unirse</h4>
                <?php if (count($solicitudes_pendientes) > 0): ?>
                    <?php foreach($solicitudes_pendientes as $solicitud): ?>
                        <div class="list-item">
                            <div class="d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($solicitud['foto_perfil_ruta'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" alt="Avatar">
                                <span><?php echo htmlspecialchars($solicitud['nombre_usuario']); ?></span>
                            </div>
                            <div>
                                <form action="gestionar_solicitud_union.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id']; ?>">
                                    <button type="submit" name="accion" value="aprobar" class="btn btn-success btn-sm">Aprobar</button>
                                </form>
                                <form action="gestionar_solicitud_union.php" method="POST" class="d-inline">
                                    <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id']; ?>">
                                    <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-white-50">No hay solicitudes pendientes en este momento.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- SECCIÓN DE LISTA DE MIEMBROS (VISIBLE PARA TODOS) -->
        <div class="management-section">
            <h4 class="mb-4">Miembros del Equipo (<?php echo count($miembros_equipo); ?>)</h4>
            <?php if (count($miembros_equipo) > 0): ?>
                <?php foreach($miembros_equipo as $miembro): ?>
                    <div class="list-item">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($miembro['foto_perfil_ruta'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" alt="Avatar">
                            <span>
                                <?php echo htmlspecialchars($miembro['nombre_usuario']); ?>
                                <?php if ($miembro['id_usuario'] == $equipo['id_capitan']) echo '<span class="badge bg-warning ms-2">Capitán</span>'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                 <p class="text-white-50">Aún no hay miembros en este equipo.</p>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="principal.php" class="btn btn-outline-light">Volver a la página principal</a>
        </div>
    </div>
</body>
</html>