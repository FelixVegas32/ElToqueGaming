<?php
session_start();
require 'db_connect.php';

// Seguridad: Redirigir al usuario si no ha iniciado sesión.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: iniciar-sesion.php'); // Si no hay sesión, se va a la página de login
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elige tu Camino - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a2e;
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
        }
        h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .card-option {
            background-color: #162447;
            border: 1px solid #1f4068;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            text-decoration: none;
            color: #fff;
            display: block;
            transition: all 0.3s ease;
            height: 100%;
        }
        .card-option:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(232, 67, 100, 0.2);
            border-color: #e84364;
        }
        .card-option .icon {
            font-size: 4rem;
            color: #e84364;
            margin-bottom: 1.5rem;
        }
        .card-option h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <div class="container text-center py-5">
        <h1 class="mb-3">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p class="lead mb-5">Para empezar a competir, necesitas un equipo. ¿Qué deseas hacer?</p>

        <div class="row">
            <!-- Opción 1: Crear Equipo -->
            <div class="col-md-6 mb-4">
                <a href="crear_equipo.php" class="card-option">
                    <div class="icon"><i class="fas fa-users-cog"></i></div>
                    <h3>Crear un Nuevo Equipo</h3>
                    <p>Conviértete en capitán, funda tu propio equipo, diseña tu emblema y recluta a otros jugadores para alcanzar la gloria.</p>
                </a>
            </div>

            <!-- Opción 2: Unirse a Equipo -->
            <div class="col-md-6 mb-4">
                <a href="unirse_equipo.php" class="card-option">
                    <div class="icon"><i class="fas fa-search-plus"></i></div>
                    <h3>Unirme a un Equipo</h3>
                    <p>Explora la lista de equipos existentes que están buscando miembros y solicita unirte al que más te guste para empezar a jugar.</p>
                </a>
            </div>
        </div>
        <div class="mt-4">
            <a href="principal.php" class="btn btn-outline-secondary">Volver al inicio</a>
        </div>
    </div>

</body>
</html>