<?php
// Inicia la sesión para futuras funcionalidades de login.
// Es una buena práctica tenerlo en todas las páginas.
session_start();

// Incluye el archivo de conexión a la base de datos.
// A partir de este punto, la página tiene acceso a la variable $conn para hacer consultas.
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElToqueGaming - Tu Plataforma de Torneos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (para los iconos) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #12122e; /* Un fondo ligeramente diferente para contraste */
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
            padding-top: 70px; /* IMPORTANTE: Para evitar que la navbar cubra el contenido */
        }
        .navbar {
            background-color: rgba(22, 36, 71, 0.9);
            backdrop-filter: blur(5px);
        }
        .navbar-brand {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: #ffffff !important;
            display: flex;
            align-items: center;
        }
        .navbar-logo {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .hero-section {
            position: relative;
            height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            top: -70px;
            margin-bottom: -70px;
        }
        #heroCarousel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .carousel-item {
            height: 100vh;
        }
        .carousel-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.4);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            color: #fff;
            padding: 20px;
        }
        .hero-content h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            text-transform: uppercase;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.8);
        }
        .btn-hero {
            padding: 12px 32px;
            font-size: 1rem;
            border-radius: 50px;
            text-transform: uppercase;
            font-weight: 700;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            margin: 10px;
        }
        .btn-aprende-mas {
            background-color: #e84364;
            color: #fff;
            border-color: #e84364;
        }
        .btn-ver-evento {
            background-color: transparent;
            color: #fff;
            border-color: #fff;
        }
        .btn-elegir-seccion {
            background-color: #ffc107;
            color: #000;
            border-color: #ffc107;
        }
        .btn-elegir-seccion:hover {
            background-color: #ffca2c;
            border-color: #ffc107;
            color: #000;
        }
        .genre-section {
            padding: 80px 0;
            text-align: center;
            background-color: #1a1a2e;
        }
        .genre-card {
            background-size: cover;
            background-position: center;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            transition: transform .3s, box-shadow .3s;
            position: relative;
            overflow: hidden;
            color: #fff;
            border: none;
        }
        .genre-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(18, 18, 46, 0.95), rgba(22, 36, 71, 0.8));
            z-index: 1;
        }
        .genre-card-content {
            position: relative;
            z-index: 2;
        }
        .genre-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(232, 67, 100, 0.2);
        }
        .genre-icon {
            font-size: 3rem;
            color: #e84364;
            margin-bottom: 20px;
        }
        .genre-card h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
        }
        .genre-card p {
            color: #ddd;
            font-size: 0.9rem;
        }
        .footer {
            background-color: #162447;
            padding: 20px 0;
            text-align: center;
        }
        .nav-link-mi-equipo {
            background-color: #e84364;
            color: #fff !important;
            border-radius: 50px;
            padding-left: 12px !important;
            padding-right: 12px !important;
            margin-left: 5px;
            transition: transform 0.2s ease-in-out;
        }
        .nav-link-mi-equipo:hover {
            transform: scale(1.05);
            color: #fff !important;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="https://i.imgur.com/SBAh266.png" alt="ElToqueGaming Logo" class="navbar-logo">
                ElToqueGaming
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="principal.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="acerca_de.php">Acerca De</a></li>
                    <li class="nav-item"><a class="nav-link" href="torneos.php">Torneos</a></li>
                    <li class="nav-item"><a class="nav-link" href="historial.php">Historial</a></li>
                    <?php if (!empty($_SESSION['id_equipo_actual'])): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-mi-equipo" href="panel_equipo.php">Mi Equipo</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="#">Contacto</a></li>
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                        <li class="nav-item dropdown ms-lg-3">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="dashboard.php">Mi Panel</a></li>
                                <li><a class="dropdown-item" href="profile.php">Editar Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3"><a href="iniciar-sesion.php" class="btn btn-outline-light btn-sm">Iniciar Sesión</a></li>
                        <li class="nav-item ms-lg-2"><a href="registro.php" class="btn btn-danger btn-sm">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <!-- ========================================================== -->
        <!-- INICIO DE LA MODIFICACIÓN                                    -->
        <!-- ========================================================== -->
        <!-- 1. Se ha eliminado la clase `carousel-fade` para activar el deslizamiento -->
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <!-- 2. Se han reemplazado las URLs de las imágenes -->
                <div class="carousel-item active" data-bs-interval="4000">
                    <img src="https://cdn1.epicgames.com/offer/24b9b5e323bc40eea252a10cdd3b2f10/EGS_LeagueofLegends_RiotGames_S1_2560x1440-80471666c140f790f28dff68d72c384b" alt="League of Legends">
                </div>
                <div class="carousel-item" data-bs-interval="4000">
                    <img src="https://i.blogs.es/4b99d8/fortnite-og-season-portada/1366_2000.jpeg" alt="Fortnite">
                </div>
                <div class="carousel-item" data-bs-interval="4000">
                    <img src="https://cdn.akamai.steamstatic.com/apps/csgo/images/csgo_react/social/cs2.jpg" alt="Counter-Strike 2">
                </div>
            </div>
        </div>
        <!-- ========================================================== -->
        <!-- FIN DE LA MODIFICACIÓN                                       -->
        <!-- ========================================================== -->
        <div class="hero-content">
            <h1>¿ESTÁS LISTO PARA UN EVENTO DE ESPORTS?</h1>
            <p class="lead">Únete a la arena y compite por la gloria. La comunidad te espera.</p>
            <div>
                <a href="generar_manual.php" class="btn btn-hero btn-aprende-mas">APRENDE MÁS</a>
                <a href="#" class="btn btn-hero btn-ver-evento">VER EVENTO</a>
            </div>
            
            <div class="mt-4">
            <?php if (isset($_SESSION['loggedin']) && empty($_SESSION['id_equipo_actual'])): ?>
                <a href="seccion.php" class="btn btn-hero btn-elegir-seccion">
                    <i class="fas fa-sitemap me-2"></i>Elegir Sección
                </a>
            <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="genre-section" id="genres">
        <div class="container">
            <h2 class="mb-4">DIFERENTES GÉNEROS DE JUEGOS</h2>
            <p class="lead mb-5">Explora la variedad de torneos que tenemos para ofrecer en tus géneros favoritos.</p>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="genre-card" style="background-image: url('https://images.unsplash.com/photo-1616599386412-2ce24965e863?q=80&w=1780&auto=format&fit=crop');">
                        <div class="genre-card-content">
                            <i class="fas fa-chess-knight genre-icon"></i>
                            <h3>Estrategia</h3>
                            <p>Demuestra tu genio táctico y lidera a tus ejércitos hacia la victoria.</p>
                            <a href="historial.php" class="btn btn-outline-light mt-3">VER TORNEOS</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="genre-card" style="background-image: url('https://images.unsplash.com/photo-1550100136-705b90446215?q=80&w=1887&auto=format&fit=crop');">
                        <div class="genre-card-content">
                            <i class="fas fa-dragon genre-icon"></i>
                            <h3>Juego de Rol</h3>
                            <p>Embárcate en aventuras épicas y forja tu propia leyenda en mundos fantásticos.</p>
                             <a href="historial.php" class="btn btn-outline-light mt-3">VER TORNEOS</a>
                        </div>
                    </div>
                </div>
                 <div class="col-lg-4 col-md-6">
                    <div class="genre-card" style="background-image: url('https://images.unsplash.com/photo-1534430480872-7b641f23a80f?q=80&w=1887&auto=format&fit=crop');">
                         <div class="genre-card-content">
                            <i class="fas fa-compass genre-icon"></i>
                            <h3>Aventura</h3>
                            <p>Resuelve misterios y explora mundos vastos llenos de desafíos y secretos.</p>
                             <a href="historial.php" class="btn btn-outline-light mt-3">VER TORNEOS</a>
                        </div>
                    </div>
                </div>
                 <div class="col-lg-4 col-md-6">
                    <div class="genre-card" style="background-image: url('https://images.unsplash.com/photo-1552642762-f55d36988a64?q=80&w=1770&auto=format&fit=crop');">
                         <div class="genre-card-content">
                            <i class="fas fa-car-side genre-icon"></i>
                            <h3>Carreras</h3>
                            <p>Siente la adrenalina de la velocidad y compite por ser el más rápido en la pista.</p>
                             <a href="historial.php" class="btn btn-outline-light mt-3">VER TORNEOS</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="genre-card" style="background-image: url('https://images.unsplash.com/photo-1542751371-380018935b62?q=80&w=1740&auto=format&fit=crop');">
                         <div class="genre-card-content">
                            <i class="fas fa-users genre-icon"></i>
                            <h3>Multijugador</h3>
                            <p>Coordínate con tu equipo o enfréntate a todos para demostrar quién es el mejor.</p>
                             <a href="historial.php" class="btn btn-outline-light mt-3">VER TORNEOS</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="genre-card" style="background-image: url('https://images.unsplash.com/photo-1509198397868-475647b2a1e5?q=80&w=1887&auto=format&fit=crop');">
                         <div class="genre-card-content">
                            <i class="fas fa-crosshairs genre-icon"></i>
                            <h3>Acción</h3>
                            <p>Pon a prueba tus reflejos y habilidades de combate en batallas de alta intensidad.</p>
                             <a href="historial.php" class="btn btn-outline-light mt-3">VER TORNEOS</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>© 2025 ElToqueGaming. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

