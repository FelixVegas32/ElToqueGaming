<?php
session_start();
require 'db_connect.php';

// --- SEGURIDAD: Proteger la página ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: iniciar-sesion.php');
    exit;
}

// --- INICIALIZACIÓN DE VARIABLES ---
$user_id = $_SESSION['userid'];
$id_equipo_actual = $_SESSION['id_equipo_actual'] ?? null;

// --- OBTENER DATOS PARA EL DASHBOARD ---

// 1. Obtener notificaciones del usuario (las 5 más recientes)
$stmt_notif = $conn->prepare("SELECT * FROM notificaciones WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 5");
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$notificaciones = $stmt_notif->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_notif->close();

// 2. Obtener datos del equipo del usuario, si tiene uno
$equipo = null;
if ($id_equipo_actual) {
    $stmt_equipo = $conn->prepare("SELECT nombre_equipo, logo_url FROM equipos WHERE id = ?");
    $stmt_equipo->bind_param("i", $id_equipo_actual);
    $stmt_equipo->execute();
    $equipo = $stmt_equipo->get_result()->fetch_assoc();
    $stmt_equipo->close();
}

// 3. (EXTRA) Obtener el próximo partido del equipo
$proximo_partido = null;
if ($id_equipo_actual) {
    $sql_partido = "SELECT p.*, t.nombre AS nombre_torneo, e1.nombre_equipo AS nombre1, e2.nombre_equipo AS nombre2
                    FROM partidos p
                    JOIN torneos t ON p.id_torneo = t.id
                    LEFT JOIN equipos e1 ON p.id_equipo1 = e1.id
                    LEFT JOIN equipos e2 ON p.id_equipo2 = e2.id
                    WHERE (p.id_equipo1 = ? OR p.id_equipo2 = ?) AND p.estado = 'Programado' AND p.fecha_partido > NOW()
                    ORDER BY p.fecha_partido ASC LIMIT 1";
    $stmt_partido = $conn->prepare($sql_partido);
    $stmt_partido->bind_param("ii", $id_equipo_actual, $id_equipo_actual);
    $stmt_partido->execute();
    $proximo_partido = $stmt_partido->get_result()->fetch_assoc();
    $stmt_partido->close();
}

// Obtener foto de perfil de la sesión (establecida en profile.php o login)
$foto_perfil = $_SESSION['user_photo'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #12122e; color: #fff; font-family: 'Roboto', sans-serif; }
        .dashboard-card { background-color: #162447; border-radius: 15px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .profile-card img, .team-card img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #e84364; }
        .list-group-item-dark { background-color: #1f4068; border-color: #334b7a; }
        .list-group-item-action:hover { background-color: #334b7a; }
        .notification-unread { border-left: 4px solid #e84364; }
        .match-opponent { font-size: 1.2rem; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <h1 class="display-5 fw-bold mb-4">¡Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

        <div class="row">
            <!-- Columna Principal -->
            <div class="col-lg-8">
                <!-- Tarjeta de Próximo Partido -->
                <div class="dashboard-card">
                    <h4 class="mb-3"><i class="fas fa-gamepad me-2"></i>Próximo Partido</h4>
                    <?php if ($proximo_partido): ?>
                        <div class="text-center">
                            <div class="row align-items-center">
                                <div class="col"><span class="match-opponent"><?php echo htmlspecialchars($proximo_partido['nombre1']); ?></span></div>
                                <div class="col-auto"><span class="text-danger fw-bold">VS</span></div>
                                <div class="col"><span class="match-opponent"><?php echo htmlspecialchars($proximo_partido['nombre2']); ?></span></div>
                            </div>
                            <div class="text-white-50 mt-3">
                                <p class="mb-0">Torneo: <?php echo htmlspecialchars($proximo_partido['nombre_torneo']); ?></p>
                                <p><i class="far fa-calendar-alt"></i> <?php echo date('d M, Y - H:i', strtotime($proximo_partido['fecha_partido'])); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-white-50">No tienes próximos partidos programados.</p>
                    <?php endif; ?>
                </div>

                <!-- Tarjeta de Notificaciones -->
                <div class="dashboard-card">
                    <h4 class="mb-3"><i class="fas fa-bell me-2"></i>Notificaciones Recientes</h4>
                    <?php if (count($notificaciones) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($notificaciones as $notif): ?>
                                <a href="<?php echo htmlspecialchars($notif['url_enlace'] ?? '#'); ?>" class="list-group-item list-group-item-action list-group-item-dark <?php if(!$notif['leido']) echo 'notification-unread fw-bold'; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <p class="mb-1"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                        <small><?php echo date('d/m/y', strtotime($notif['fecha_creacion'])); ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-white-50">No tienes notificaciones.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Lateral (Sidebar) -->
            <div class="col-lg-4">
                <!-- Tarjeta de Perfil -->
                <div class="dashboard-card profile-card text-center">
                    <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de Perfil" class="mx-auto mb-3">
                    <h5 class="mb-0"><?php echo htmlspecialchars($_SESSION['username']); ?></h5>
                    <p class="text-white-50 small">Jugador</p>
                    <a href="profile.php" class="btn btn-outline-light btn-sm mt-2">Editar Perfil</a>
                </div>

                <!-- Tarjeta de Equipo -->
                <?php if ($equipo): ?>
                    <div class="dashboard-card team-card text-center">
                        <img src="<?php echo htmlspecialchars($equipo['logo_url'] ?? 'URL_LOGO_DEFECTO.jpg'); ?>" alt="Logo del Equipo" class="mx-auto mb-3">
                        <h5 class="mb-0"><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></h5>
                        <p class="text-white-50 small">Mi Equipo</p>
                        <a href="panel_equipo.php" class="btn btn-outline-light btn-sm mt-2">Gestionar Equipo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>