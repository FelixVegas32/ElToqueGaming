<?php
session_start();
require 'db_connect.php';
// Seguridad: if ($_SESSION['rol'] !== 'admin') { header('Location: principal.php'); exit(); }

if (!isset($_GET['torneo_id']) || !is_numeric($_GET['torneo_id'])) {
    header('Location: admin_torneos.php'); exit();
}
$id_torneo = $_GET['torneo_id'];

// --- OBTENER DATOS CLAVE ---
$stmt_torneo = $conn->prepare("SELECT * FROM torneos WHERE id = ?");
$stmt_torneo->bind_param("i", $id_torneo);
$stmt_torneo->execute();
$torneo = $stmt_torneo->get_result()->fetch_assoc();

$stmt_inscritos = $conn->prepare("SELECT id_equipo FROM inscripciones WHERE id_torneo = ?");
$stmt_inscritos->bind_param("i", $id_torneo);
$stmt_inscritos->execute();
$inscritos_result = $stmt_inscritos->get_result();
$equipos_inscritos_ids = array_column($inscritos_result->fetch_all(MYSQLI_ASSOC), 'id_equipo');
$total_inscritos = count($equipos_inscritos_ids);

$stmt_partidos = $conn->prepare("SELECT p.*, e1.nombre_equipo AS nombre1, e2.nombre_equipo AS nombre2 
                                FROM partidos p 
                                LEFT JOIN equipos e1 ON p.id_equipo1 = e1.id 
                                LEFT JOIN equipos e2 ON p.id_equipo2 = e2.id 
                                WHERE p.id_torneo = ? ORDER BY p.ronda, p.id");
$stmt_partidos->bind_param("i", $id_torneo);
$stmt_partidos->execute();
$partidos = $stmt_partidos->get_result()->fetch_all(MYSQLI_ASSOC);

// --- LÓGICA DE PROCESAMIENTO DE FORMULARIOS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- LÓGICA PARA GENERAR EL BRACKET INICIAL ---
    if (isset($_POST['generar_bracket'])) {
        if ($total_inscritos >= $torneo['max_equipos'] && count($partidos) == 0) {
            shuffle($equipos_inscritos_ids);
            $conn->begin_transaction();
            try {
                for ($i = 0; $i < $total_inscritos; $i += 2) {
                    $equipo1_id = $equipos_inscritos_ids[$i];
                    $equipo2_id = isset($equipos_inscritos_ids[$i + 1]) ? $equipos_inscritos_ids[$i + 1] : null;
                    $stmt_insert = $conn->prepare("INSERT INTO partidos (id_torneo, id_equipo1, id_equipo2, fecha_partido, ronda) VALUES (?, ?, ?, ?, 'Ronda 1')");
                    $stmt_insert->bind_param("iiss", $id_torneo, $equipo1_id, $equipo2_id, $torneo['fecha_inicio']);
                    $stmt_insert->execute();
                }
                $stmt_capitanes = $conn->prepare("SELECT e.id_capitan FROM equipos e WHERE e.id IN (" . implode(',', $equipos_inscritos_ids) . ")");
                $stmt_capitanes->execute();
                $capitanes_ids = array_column($stmt_capitanes->get_result()->fetch_all(MYSQLI_ASSOC), 'id_capitan');
                $mensaje = "¡Los partidos para el torneo '".htmlspecialchars($torneo['nombre'])."' han sido programados!";
                $enlace = "detalle_torneo.php?id=" . $id_torneo;
                $stmt_notif = $conn->prepare("INSERT INTO notificaciones (id_usuario, mensaje, url_enlace) VALUES (?, ?, ?)");
                foreach ($capitanes_ids as $id_cap) {
                    $stmt_notif->bind_param("iss", $id_cap, $mensaje, $enlace);
                    $stmt_notif->execute();
                }
                $conn->query("UPDATE torneos SET estado = 'En Curso' WHERE id = $id_torneo");
                $conn->commit();
                header("Location: gestionar_partidos.php?torneo_id=$id_torneo");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Error al generar el bracket: " . $e->getMessage();
            }
        }
    }

    // --- LÓGICA PARA ACTUALIZAR PARTIDO ---
    if (isset($_POST['actualizar_partido'])) {
        $partido_id = $_POST['partido_id'];
        $score1 = !empty($_POST['score1']) ? (int)$_POST['score1'] : null;
        $score2 = !empty($_POST['score2']) ? (int)$_POST['score2'] : null;
        $stream_url = trim($_POST['stream_url']);
        
        $estado_partido = 'Programado';
        $ganador_id = null;

        if ($score1 !== null && $score2 !== null) {
            $estado_partido = 'Finalizado';
            if ($score1 > $score2) {
                $ganador_id = $_POST['equipo1_id'];
            } elseif ($score2 > $score1) {
                $ganador_id = $_POST['equipo2_id'];
            }
        } elseif (!empty($stream_url)) {
            $estado_partido = 'En Vivo';
        }

        $stmt = $conn->prepare("UPDATE partidos SET resultado_equipo1 = ?, resultado_equipo2 = ?, id_ganador = ?, estado = ?, stream_url = ? WHERE id = ?");
        $stmt->bind_param("iiissi", $score1, $score2, $ganador_id, $estado_partido, $stream_url, $partido_id);
        $stmt->execute();
        header("Location: gestionar_partidos.php?torneo_id=$id_torneo");
        exit();
    }
}

// Función para generar el enlace de incrustación (embed)
function getEmbedUrl($url) {
    if (empty($url)) return null;
    if (preg_match('/twitch\.tv\/([a-zA-Z0-9_]+)/', $url, $matches)) {
        return 'https://player.twitch.tv/?channel=' . $matches[1] . '&parent=' . $_SERVER['HTTP_HOST'];
    }
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestionar Partidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .embed-responsive { position: relative; display: block; width: 100%; padding: 0; overflow: hidden; }
        .embed-responsive::before { content: ""; display: block; padding-top: 56.25%; }
        .embed-responsive iframe { position: absolute; top: 0; bottom: 0; left: 0; width: 100%; height: 100%; border: 0; }
    </style>
</head>
<body style="background-color: #1a1a2e; color: #fff;">
    <div class="container my-5">
        <a href="admin_torneos.php" class="text-white-50 text-decoration-none">« Volver a la lista de torneos</a>
        <h1 class="mt-2">Gestionar Partidos de: <?php echo htmlspecialchars($torneo['nombre']); ?></h1>

        <!-- ========================================================== -->
        <!-- INICIO DE LA CORRECCIÓN: Bloque de lógica condicional      -->
        <!-- ========================================================== -->
        <div class="card bg-dark my-4">
            <div class="card-body">
                <?php if ($total_inscritos < $torneo['max_equipos']): ?>
                    <div class="alert alert-warning text-center">
                        Esperando a que se llenen las inscripciones. 
                        (Inscritos actualmente: <?php echo $total_inscritos; ?> / <?php echo $torneo['max_equipos']; ?>)
                    </div>
                <?php elseif (count($partidos) == 0): ?>
                    <div class="alert alert-success text-center">
                        <h4 class="alert-heading">¡Inscripciones Completas!</h4>
                        <p>Es hora de crear la primera ronda de partidos para el torneo.</p>
                        <hr>
                        <form action="gestionar_partidos.php?torneo_id=<?php echo $id_torneo; ?>" method="POST" class="mb-0">
                            <button type="submit" name="generar_bracket" class="btn btn-lg btn-primary">
                                <i class="fas fa-sitemap me-2"></i>Generar Bracket Inicial
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                     <div class="alert alert-info text-center">
                        El bracket ya ha sido generado. Ahora puedes gestionar los resultados de cada partido a continuación.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- ========================================================== -->
        <!-- FIN DE LA CORRECCIÓN                                       -->
        <!-- ========================================================== -->

        <!-- LISTA DE PARTIDOS -->
        <?php if (count($partidos) > 0): ?>
        <h4 class="mt-5">Bracket del Torneo</h4>
        <?php foreach ($partidos as $partido): ?>
            <div class="card bg-dark mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?php echo htmlspecialchars($partido['ronda']); ?></strong>
                    <?php 
                        $estado_clase = 'bg-secondary';
                        if ($partido['estado'] == 'En Vivo') $estado_clase = 'bg-danger';
                        if ($partido['estado'] == 'Finalizado') $estado_clase = 'bg-success';
                    ?>
                    <span class="badge <?php echo $estado_clase; ?>"><?php echo htmlspecialchars($partido['estado']); ?></span>
                </div>
                <div class="card-body">
                    <form action="gestionar_partidos.php?torneo_id=<?php echo $id_torneo; ?>" method="POST" class="row align-items-center g-3">
                        <input type="hidden" name="partido_id" value="<?php echo $partido['id']; ?>">
                        <input type="hidden" name="equipo1_id" value="<?php echo $partido['id_equipo1']; ?>">
                        <input type="hidden" name="equipo2_id" value="<?php echo $partido['id_equipo2']; ?>">
                        <div class="col-sm-5 text-end"><h5><?php echo htmlspecialchars($partido['nombre1'] ?? 'Equipo por definir'); ?></h5></div>
                        <div class="col-sm-2"><div class="d-flex justify-content-center"><input type="number" name="score1" class="form-control text-center fw-bold" value="<?php echo $partido['resultado_equipo1']; ?>" min="0"><span class="mx-2 fs-5">-</span><input type="number" name="score2" class="form-control text-center fw-bold" value="<?php echo $partido['resultado_equipo2']; ?>" min="0"></div></div>
                        <div class="col-sm-5 text-start"><h5><?php echo htmlspecialchars($partido['nombre2'] ?? 'Equipo por definir'); ?></h5></div>
                        <div class="col-12 mt-4"><div class="input-group"><span class="input-group-text bg-secondary border-secondary text-white"><i class="fas fa-video"></i></span><input type="url" class="form-control bg-dark text-white border-secondary" name="stream_url" value="<?php echo htmlspecialchars($partido['stream_url'] ?? ''); ?>" placeholder="Pega aquí enlace de Twitch/YouTube para poner el partido 'En Vivo'"></div></div>
                        <div class="col-12 text-center mt-3"><button type="submit" name="actualizar_partido" class="btn btn-success"><i class="fas fa-save me-1"></i> Guardar Cambios</button></div>
                    </form>
                    <?php $embed_url = getEmbedUrl($partido['stream_url']); ?>
                    <?php if ($partido['estado'] === 'En Vivo' && $embed_url): ?>
                        <hr class="my-4 border-secondary">
                        <h6 class="text-danger mb-2"><i class="fas fa-broadcast-tower"></i> Transmisión en Vivo</h6>
                        <div class="embed-responsive"><iframe src="<?php echo htmlspecialchars($embed_url); ?>" allowfullscreen="true" scrolling="no"></iframe></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

