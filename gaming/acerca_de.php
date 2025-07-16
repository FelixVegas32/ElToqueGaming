<?php
// Es una buena práctica iniciar la sesión en todas las páginas,
// aunque no la usemos directamente aquí, la navbar sí lo hace.
session_start();
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acerca de Nosotros - ElToqueGaming</title>
    <!-- Enlaces a CSS y Fonts (consistentes con tu sitio) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #12122e;
            color: #ffffff;
            font-family: 'Roboto', sans-serif;
            padding-top: 70px;
        }
        .navbar {
            background-color: rgba(22, 36, 71, 0.9);
            backdrop-filter: blur(5px);
        }
        .about-header {
            padding: 5rem 0;
            background: linear-gradient(rgba(18, 18, 46, 0.9), rgba(18, 18, 46, 0.9)), url('https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=1740&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            text-align: center;
        }
        .about-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
        }
        .content-section {
            background-color: #162447;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 3rem;
        }
        .team-section {
            padding-bottom: 3rem;
        }
        .team-card {
            background-color: #1f4068;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        .team-member-img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #e84364;
            margin-bottom: 1.5rem;
        }
        .team-member-title {
            color: #e84364;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <header class="about-header">
        <div class="container">
            <h1 class="display-4">Sobre ElToqueGaming</h1>
            <p class="lead">Conectando jugadores y forjando leyendas en la arena competitiva.</p>
        </div>
    </header>

    <main class="container my-5">
        
        <!-- Sección de Nuestra Misión -->
        <section class="content-section">
            <div class="row align-items-center">
                <div class="col-md-4 text-center">
                    <i class="fas fa-bullseye fa-5x text-warning mb-4 mb-md-0"></i>
                </div>
                <div class="col-md-8">
                    <h2 class="mb-3">Nuestra Misión</h2>
                    <p class="text-white-50">En ElToqueGaming, nuestra misión es crear el ecosistema de torneos más justo, emocionante y accesible para jugadores de todos los niveles. Creemos en el poder de los eSports para unir a las personas, fomentar la competencia sana y celebrar el talento. Nos dedicamos a proporcionar una plataforma robusta y profesional donde cada jugador y cada equipo tengan la oportunidad de brillar y escribir su propia historia de éxito.</p>
                </div>
            </div>
        </section>

        <!-- Sección del Equipo -->
        <section class="team-section text-center">
            <h2 class="display-5 mb-5">Conoce al Equipo</h2>
            <div class="row justify-content-center g-4">
                
                <!-- Tarjeta del Presidente -->
                <div class="col-lg-5 col-md-6">
                    <div class="team-card">
                        <img src="https://i.imgur.com/P2Z1Zzj.jpeg" alt="Foto de Felix Vegas" class="team-member-img">
                        <h4 class="mb-1">Felix Vegas</h4>
                        <p class="team-member-title">Presidente y Fundador</p>
                        <p class="text-white-50 small">Con más de una década de experiencia en la industria del gaming y la gestión de eventos, Felix es la mente visionaria detrás de ElToqueGaming. Su pasión es transformar el panorama competitivo amateur en una experiencia profesional.</p>
                    </div>
                </div>

                <!-- Tarjeta del Community Manager -->
                <div class="col-lg-5 col-md-6">
                    <div class="team-card">
                        <img src="https://i.imgur.com/RoWoNNc.jpeg" alt="Foto de Luis Ruiz" class="team-member-img">
                        <h4 class="mb-1">Luis Ruiz</h4>
                        <p class="team-member-title">Community Manager</p>
                        <p class="text-white-50 small">Luis es el corazón de nuestra comunidad. Como jugador ávido y comunicador experto, es el puente entre la plataforma y los jugadores, asegurándose de que la voz de todos sea escuchada y que cada torneo sea una experiencia inolvidable.</p>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <?php include 'footer.php'; // Es buena práctica tener un footer separado también ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>