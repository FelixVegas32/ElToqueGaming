<?php
session_start();
require 'db_connect.php';

// --- SEGURIDAD: Proteger la página ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: iniciar-sesion.php');
    exit;
}

$success_message = '';
$error_message = '';
$user_id = $_SESSION['userid']; 

// --- LÓGICA PARA PROCESAR EL FORMULARIO (CUANDO SE ENVÍA) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- PROCESAR ACTUALIZACIÓN DE NOMBRE DE USUARIO ---
    if (!empty(trim($_POST['nombre_usuario']))) {
        $nuevo_nombre = trim($_POST['nombre_usuario']);
        $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? AND id != ?");
        $stmt_check->bind_param("si", $nuevo_nombre, $user_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error_message = 'Ese nombre de usuario ya está en uso.';
        } else {
            $update_stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ? WHERE id = ?");
            $update_stmt->bind_param("si", $nuevo_nombre, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['username'] = $nuevo_nombre;
                $success_message = '¡Nombre de usuario actualizado!';
            }
            $update_stmt->close();
        }
        $stmt_check->close();
    }

    // --- PROCESAR SUBIDA DE FOTO DE PERFIL ---
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profile_pics/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_info = pathinfo($_FILES["foto_perfil"]["name"]);
        $file_type = strtolower($file_info['extension']);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_type, $allowed_types)) {
            $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_type;
            $target_file = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
                $update_pic_stmt = $conn->prepare("UPDATE usuarios SET foto_perfil_ruta = ? WHERE id = ?");
                $update_pic_stmt->bind_param("si", $target_file, $user_id);
                if ($update_pic_stmt->execute()) {
                    $_SESSION['user_photo'] = $target_file; 
                    $success_message .= ' ¡Foto de perfil actualizada!';
                } else {
                     $error_message .= ' Error al guardar la ruta en la base de datos.';
                }
                $update_pic_stmt->close();
            } else {
                $error_message .= ' Hubo un error al mover el archivo subido.';
            }
        } else {
            $error_message .= ' Formato de archivo no permitido (Solo JPG, JPEG, PNG, GIF).';
        }
    }
}

// --- OBTENER DATOS ACTUALES DEL USUARIO PARA MOSTRARLOS ---
$stmt_get_data = $conn->prepare("SELECT nombre_usuario, email, foto_perfil_ruta FROM usuarios WHERE id = ?");
$stmt_get_data->bind_param("i", $user_id);
$stmt_get_data->execute();
$user_data = $stmt_get_data->get_result()->fetch_assoc();
$stmt_get_data->close();

// Lógica para mostrar la foto actual o la por defecto.
$foto_perfil_actual = (!empty($user_data['foto_perfil_ruta']) && file_exists($user_data['foto_perfil_ruta'])) 
    ? $user_data['foto_perfil_ruta'] 
    : 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg';

// Guardar la foto en la sesión para que la navbar la use.
$_SESSION['user_photo'] = $foto_perfil_actual;

// ==========================================================
// INICIO DE LA CORRECCIÓN CLAVE: Mover el cierre de la conexión aquí
// Ahora se cierra DESPUÉS de que 'navbar.php' la haya usado.
// ==========================================================
$conn->close();
// ==========================================================
// FIN DE LA CORRECIÓN CLAVE
// ==========================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a1a2e; color: #ffffff; font-family: 'Roboto', sans-serif; }
        .profile-container { margin-top: 3rem; background-color: #162447; padding: 2rem 3rem; border-radius: 15px; }
        .profile-pic { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #e84364; margin-bottom: 1rem; }
        .form-control, .form-control:disabled { background-color: #1f4068; border: 1px solid #1f4068; color: #fff; }
        .btn-submit { background-color: #e84364; border-color: #e84364; font-weight: 700; }
        .btn-back { font-weight: 700; }
    </style>
</head>
<body>
    
<?php 
// Esta inclusión ahora funciona porque la conexión sigue abierta.
include 'navbar.php'; 
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="profile-container">
                <h2 class="text-center mb-4" style="font-family: 'Montserrat', sans-serif; color: #e84364;">Editar Perfil</h2>

                <?php if(!empty($success_message)): ?><div class="alert alert-success"><?php echo trim($success_message); ?></div><?php endif; ?>
                <?php if(!empty($error_message)): ?><div class="alert alert-danger"><?php echo trim($error_message); ?></div><?php endif; ?>

                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="text-center">
                        <img src="<?php echo htmlspecialchars($foto_perfil_actual); ?>" alt="Foto de perfil" class="profile-pic">
                    </div>
                    <div class="mb-3">
                        <label for="foto_perfil" class="form-label">Cambiar foto de perfil</label>
                        <input class="form-control" type="file" id="foto_perfil" name="foto_perfil" accept="image/png, image/jpeg, image/gif">
                    </div>
                    <hr class="my-4">
                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($user_data['nombre_usuario']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                        <div class="form-text">El correo electrónico no se puede cambiar.</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-submit">Guardar Cambios</button>
                        <a href="principal.php" class="btn btn-outline-light btn-back">Volver a la Página Principal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>