<?php
session_start();
require 'db_connect.php';

// Redirige al usuario si ya ha iniciado sesión
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: principal.php');
    exit();
}

$error_message = '';

// Procesar el formulario de login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "El email y la contraseña son obligatorios.";
    } else {
        // ==========================================================
        //  INICIO DE LA CORRECCIÓN 1: Modificar la consulta SQL
        //  Añadimos 'tipo_usuario' y 'id_equipo_actual' a la consulta
        //  para obtenerlos de la base de datos.
        // ==========================================================
        $stmt = $conn->prepare("SELECT id, nombre_usuario, password_hash, tipo_usuario, id_equipo_actual FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar la contraseña
            if (password_verify($password, $usuario['password_hash'])) {
                // ¡Contraseña correcta!
                // Previene la fijación de sesión
                session_regenerate_id(true); 
                
                // ==========================================================
                //  INICIO DE LA CORRECCIÓN 2: Guardar los nuevos datos en la sesión
                //  Estos datos son los que usará la barra de navegación para
                //  mostrar o no el botón "Elegir Sección".
                // ==========================================================
                $_SESSION['loggedin'] = true;
                $_SESSION['userid'] = $usuario['id']; // El ID del usuario
                $_SESSION['username'] = $usuario['nombre_usuario']; // El nombre de usuario a mostrar
                $_SESSION['tipo_usuario'] = $usuario['tipo_usuario']; // El tipo de cuenta (jugador, etc.)
                $_SESSION['id_equipo_actual'] = $usuario['id_equipo_actual']; // El ID del equipo al que pertenece (o NULL si no tiene)
                // ==========================================================
                //  FIN DE LA CORRECCIÓN
                // ==========================================================
                
                // Redirigir a la página principal
                header("Location: principal.php");
                exit();
            } else {
                $error_message = "La contraseña es incorrecta.";
            }
        } else {
            $error_message = "No se encontró ninguna cuenta con ese correo electrónico.";
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
    <title>Iniciar Sesión - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Tus estilos permanecen exactamente igual, no necesitan cambios */
        body { background-color: #1a1a2e; color: #ffffff; font-family: 'Roboto', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-container { background-color: #162447; padding: 2rem 3rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 100%; max-width: 450px; }
        .login-container h2 { font-family: 'Montserrat', sans-serif; font-weight: 700; color: #e84364; margin-bottom: 1.5rem; text-align: center; }
        .form-control { background-color: #1f4068; border: 1px solid #1f4068; color: #fff; }
        .form-control:focus { background-color: #1f4068; border-color: #e84364; color: #fff; box-shadow: 0 0 0 0.25rem rgba(232, 67, 100, 0.25); }
        .btn-login { background-color: #e84364; border-color: #e84364; font-weight: 700; width: 100%; padding: 0.75rem; transition: all 0.3s ease; }
        .btn-login:hover { background-color: #d13b5a; border-color: #d13b5a; transform: scale(1.02); }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="iniciar-sesion.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-login">Entrar</button>
        </form>

        <div class="text-center mt-3">
            <a href="registro.php" class="text-white-50">¿No tienes una cuenta? Regístrate</a>
        </div>
    </div>
</body>
</html>