<?php
session_start();
require 'db_connect.php';

// Redirige al usuario si ya ha iniciado sesión
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: principal.php');
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // ==========================================================
    // INICIO DE LA CORRECCIÓN 1: Establecer tipo de usuario por defecto
    // Todo nuevo usuario se crea como 'jugador' automáticamente.
    // ==========================================================
    $tipo_usuario = 'jugador';

    // Se elimina la validación para $tipo_usuario ya que ahora es un valor fijo.
    if (empty($nombre_usuario) || empty($email) || empty($password)) {
        $error_message = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'El formato del correo electrónico no es válido.';
    } elseif (strlen($password) < 8) {
        $error_message = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Las contraseñas no coinciden.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR email = ?");
        $stmt->bind_param("ss", $nombre_usuario, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = 'El nombre de usuario o el email ya están registrados.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (nombre_usuario, email, password_hash, tipo_usuario) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $nombre_usuario, $email, $password_hash, $tipo_usuario);

            if ($stmt_insert->execute()) {
                $_SESSION['registro_exitoso'] = '¡Registro completado! Por favor, inicia sesión con tus nuevas credenciales.';
                header('Location: iniciar-sesion.php');
                exit();
            } else {
                $error_message = 'Hubo un error al crear la cuenta. Por favor, inténtelo de nuevo.';
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #1a1a2e; color: #ffffff; font-family: 'Roboto', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .register-container { background-color: #162447; padding: 2rem 3rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 500px; }
        .register-container h2 { font-family: 'Montserrat', sans-serif; font-weight: 700; color: #e84364; margin-bottom: 1.5rem; text-align: center; }
        .form-control { background-color: #1f4068; border: 1px solid #1f4068; color: #fff; }
        .form-control:focus { background-color: #1f4068; border-color: #e84364; color: #fff; box-shadow: 0 0 0 0.25rem rgba(232, 67, 100, 0.25); }
        .form-control::placeholder { color: #a0a0a0; }
        .btn-register { background-color: #e84364; border-color: #e84364; font-weight: 700; width: 100%; padding: 0.75rem; transition: all 0.3s ease; }
        .btn-register:hover { background-color: #d13b5a; border-color: #d13b5a; transform: scale(1.02); }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Crear una Cuenta</h2>

        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="registro.php" method="POST">
            <div class="mb-3">
                <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-4">
                <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>

            <!-- ==========================================================
                 CORRECCIÓN 2: CAMPO ELIMINADO DEL FORMULARIO
                 El bloque div que contenía el <select> para "Tipo de Cuenta"
                 ha sido eliminado de aquí.
                 ========================================================== -->

            <button type="submit" class="btn btn-primary btn-register">Registrarse</button>
        </form>
    </div>
</body>
</html>