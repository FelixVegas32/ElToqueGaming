<?php
session_start();
require 'db_connect.php';

// --- CONSULTAS A LA BASE DE DATOS (sin cambios) ---
// 1. Obtener partidos EN VIVO
$sql_en_vivo = "SELECT p.*, t.nombre AS nombre_torneo, e1.nombre_equipo AS nombre1, e1.logo_url AS logo1, e2.nombre_equipo AS nombre2, e2.logo_url AS logo2
                FROM partidos p
                JOIN torneos t ON p.id_torneo = t.id
                LEFT JOIN equipos e1 ON p.id_equipo1 = e1.id
                LEFT JOIN equipos e2 ON p.id_equipo2 = e2.id
                WHERE p.estado = 'En Vivo'
                ORDER BY p.fecha_partido ASC";
$partidos_en_vivo = $conn->query($sql_en_vivo)->fetch_all(MYSQLI_ASSOC);

// 2. Obtener PRÓXIMOS partidos
$sql_proximos = "SELECT p.*, t.nombre AS nombre_torneo, e1.nombre_equipo AS nombre1, e1.logo_url AS logo1, e2.nombre_equipo AS nombre2, e2.logo_url AS logo2
                 FROM partidos p
                 JOIN torneos t ON p.id_torneo = t.id
                 LEFT JOIN equipos e1 ON p.id_equipo1 = e1.id
                 LEFT JOIN equipos e2 ON p.id_equipo2 = e2.id
                 WHERE p.estado = 'Programado' AND p.fecha_partido > NOW()
                 ORDER BY p.fecha_partido ASC
                 LIMIT 10";
$partidos_proximos = $conn->query($sql_proximos)->fetch_all(MYSQLI_ASSOC);

// 3. Obtener RESULTADOS RECIENTES
$sql_finalizados = "SELECT p.*, t.nombre AS nombre_torneo, e1.nombre_equipo AS nombre1, e1.logo_url AS logo1, e2.nombre_equipo AS nombre2, e2.logo_url AS logo2, e1.id as id_equipo1, e2.id as id_equipo2
                    FROM partidos p
                    JOIN torneos t ON p.id_torneo = t.id
                    LEFT JOIN equipos e1 ON p.id_equipo1 = e1.id
                    LEFT JOIN equipos e2 ON p.id_equipo2 = e2.id
                    WHERE p.estado = 'Finalizado'
                    ORDER BY p.fecha_partido DESC
                    LIMIT 10";
$partidos_finalizados = $conn->query($sql_finalizados)->fetch_all(MYSQLI_ASSOC);


// ==========================================================
// INICIO DE LA MODIFICACIÓN: Función para generar URL de Embed
// ==========================================================
/**
 * Convierte una URL de Twitch/YouTube a una URL para incrustar (embed).
 * @param string $url La URL original.
 * @return string|null La URL para el iframe o null si no es válida.
 */
function getEmbedUrl($url) {
    if (strpos($url, 'twitch.tv/') !== false) {
        $parts = explode('/', rtrim($url, '/'));
        $channel = end($parts);
        return 'https://player.twitch.tv/?channel=' . $channel . '&parent=' . $_SERVER['HTTP_HOST'];
    } elseif (strpos($url, 'youtube.com/watch?v=') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $vars);
        return 'https://www.youtube.com/embed/' . ($vars['v'] ?? '');
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $parts = explode('/', rtrim($url, '/'));
        $video_id = end($parts);
        return 'https://www.youtube.com/embed/' . $video_id;
    }
    return null; // No es una URL reconocida
}
// ==========================================================
// FIN DE LA MODIFICACIÓN
// ==========================================================

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Partidos - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #12122e; color: #fff; }
        .match-card { background-color: #162447; border: 1px solid #1f4068; border-radius: 15px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .match-card .team-logo { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        .vs-circle { width: 40px; height: 40px; background-color: #e84364; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .live-badge { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); } 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); } }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Historial de Partidos</h1>
            <p class="lead text-white-50">Sigue la acción en vivo, revisa los próximos enfrentamientos y consulta los resultados.</p>
        </div>

        <!-- SECCIÓN DE PARTIDOS EN VIVO -->
        <h2 class="mb-3"><i class="fas fa-satellite-dish text-danger me-2 live-badge"></i>En Vivo</h2>
        <?php if (count($partidos_en_vivo) > 0): ?>
            <?php foreach ($partidos_en_vivo as $partido): ?>
                <?php $embed_url = getEmbedUrl($partido['stream_url']); ?>
                <div class="match-card">
                    <div class="row align-items-center">
                        <div class="col-lg-5 text-center"><h5><?php echo htmlspecialchars($partido['nombre1']); ?></h5></div>
                        <div class="col-lg-2 text-center"><div class="vs-circle mx-auto">VS</div></div>
                        <div class="col-lg-5 text-center"><h5><?php echo htmlspecialchars($partido['nombre2']); ?></h5></div>
                    </div>
                    <p class="text-center text-white-50 small mt-2"><?php echo htmlspecialchars($partido['nombre_torneo']); ?></p>
                    
                    <?php if ($embed_url): ?>
                        <div class="ratio ratio-16x9 mt-3">
                            <iframe src="<?php echo $embed_url; ?>" 
                                    title="Reproductor de Streaming" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                    <?php elseif(!empty($partido['stream_url'])): ?>
                        <div class="alert alert-warning mt-3 text-center">
                            El stream está en vivo pero no se puede incrustar. 
                            <a href="<?php echo htmlspecialchars($partido['stream_url']); ?>" target="_blank" class="alert-link">Ver en enlace directo.</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-white-50">No hay partidos en vivo en este momento.</p>
        <?php endif; ?>
        <hr class="my-5">

        <!-- SECCIÓN DE PRÓXIMOS PARTIDOS -->
        <h2 class="mb-3"><i class="far fa-calendar-alt text-info me-2"></i>Próximos Partidos</h2>
        <?php if (count($partidos_proximos) > 0): ?>
            <?php foreach ($partidos_proximos as $partido): ?>
                 <div class="match-card">
                    <div class="row align-items-center">
                        <div class="col-5 text-center">
                            <img src="<?php echo htmlspecialchars($partido['logo1'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" class="team-logo mb-2">
                            <h5><?php echo htmlspecialchars($partido['nombre1'] ?? 'Por Definir'); ?></h5>
                        </div>
                        <div class="col-2 text-center"><div class="vs-circle mx-auto">VS</div></div>
                        <div class="col-5 text-center">
                            <img src="<?php echo htmlspecialchars($partido['logo2'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" class="team-logo mb-2">
                            <h5><?php echo htmlspecialchars($partido['nombre2'] ?? 'Por Definir'); ?></h5>
                        </div>
                    </div>
                    <div class="text-center text-white-50 small mt-3">
                        <?php echo htmlspecialchars($partido['nombre_torneo']); ?> - <?php echo htmlspecialchars($partido['ronda']); ?> <br>
                        <?php echo date('d M, Y - H:i', strtotime($partido['fecha_partido'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-white-50">No hay más partidos programados por ahora.</p>
        <?php endif; ?>
        <hr class="my-5">
        
        <!-- SECCIÓN DE RESULTADOS RECIENTES -->
        <h2 class="mb-3"><i class="fas fa-poll text-success me-2"></i>Resultados Recientes</h2>
        <?php if (count($partidos_finalizados) > 0): ?>
            <?php foreach ($partidos_finalizados as $partido): ?>
                <div class="match-card">
                    <div class="row align-items-center">
                        <div class="col-5 d-flex justify-content-end align-items-center">
                            <h5 class="me-3 <?php if($partido['id_ganador'] != $partido['id_equipo1']) echo 'text-white-50'; ?>"><?php echo htmlspecialchars($partido['nombre1']); ?></h5>
                            <img src="<?php echo htmlspecialchars($partido['logo1'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" class="team-logo">
                        </div>
                        <div class="col-2 text-center">
                            <h4 class="fw-bold"><?php echo $partido['resultado_equipo1']; ?> - <?php echo $partido['resultado_equipo2']; ?></h4>
                        </div>
                        <div class="col-5 d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($partido['logo2'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" class="team-logo">
                            <h5 class="ms-3 <?php if($partido['id_ganador'] != $partido['id_equipo2']) echo 'text-white-50'; ?>"><?php echo htmlspecialchars($partido['nombre2']); ?></h5>
                        </div>
                    </div>
                     <div class="text-center text-white-50 small mt-3">
                        <?php echo htmlspecialchars($partido['nombre_torneo']); ?> - Finalizado el <?php echo date('d/m/Y', strtotime($partido['fecha_partido'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-white-50">Aún no se han completado partidos.</p>
        <?php endif; ?>
    </div>
</body>
</html>