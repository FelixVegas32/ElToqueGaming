<?php
session_start();
require 'db_connect.php';

// 1. VALIDAR Y OBTENER ID DEL TORNEO
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: principal.php'); // O a una futura página de listado de torneos
    exit();
}
$id_torneo = $_GET['id'];

// 2. OBTENER DATOS DEL TORNEO Y DEL JUEGO
$sql_torneo = "SELECT t.*, j.nombre AS nombre_juego 
               FROM torneos t 
               JOIN juegos j ON t.id_juego = j.id 
               WHERE t.id = ?";
$stmt = $conn->prepare($sql_torneo);
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$torneo = $stmt->get_result()->fetch_assoc();

// Si el torneo no existe, detener la ejecución.
if (!$torneo) {
    // Puedes crear una página de error 404 más elegante
    die("Error: Torneo no encontrado.");
}

// 3. LÓGICA PARA EL BOTÓN DE INSCRIPCIÓN (LA PARTE MÁS IMPORTANTE)
$puede_inscribirse = false;
$equipo_ya_inscrito = false;
$mensaje_usuario = ''; // Mensaje para usuarios que no son capitanes

// Solo realizamos estas comprobaciones si el usuario está logueado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (!empty($_SESSION['id_equipo_actual'])) {
        $id_equipo_actual = $_SESSION['id_equipo_actual'];
        
        // Comprobar si este equipo ya está inscrito
        $stmt_inscripcion = $conn->prepare("SELECT id FROM inscripciones WHERE id_torneo = ? AND id_equipo = ?");
        $stmt_inscripcion->bind_param("ii", $id_torneo, $id_equipo_actual);
        $stmt_inscripcion->execute();
        $equipo_ya_inscrito = $stmt_inscripcion->get_result()->num_rows > 0;
        
        if ($equipo_ya_inscrito) {
            $mensaje_usuario = '¡Tu equipo ya está inscrito en este torneo!';
        } else {
            // Si no está inscrito, comprobar si es el capitán
            $stmt_capitan = $conn->prepare("SELECT id FROM equipos WHERE id = ? AND id_capitan = ?");
            $stmt_capitan->bind_param("ii", $id_equipo_actual, $_SESSION['userid']);
            $stmt_capitan->execute();
            $es_capitan = $stmt_capitan->get_result()->num_rows > 0;
            
            // El botón se muestra si es capitán Y el torneo está 'Programado'
            if ($es_capitan && $torneo['estado'] === 'Programado') {
                $puede_inscribirse = true;
            } elseif (!$es_capitan) {
                $mensaje_usuario = 'Solo el capitán de tu equipo puede realizar la inscripción.';
            }
        }
    } else {
        $mensaje_usuario = 'Necesitas pertenecer a un equipo para participar.';
    }
}

// 4. OBTENER LISTA DE EQUIPOS INSCRITOS
$sql_inscritos = "SELECT e.nombre_equipo, e.logo_url
                  FROM inscripciones i
                  JOIN equipos e ON i.id_equipo = e.id
                  WHERE i.id_torneo = ?
                  ORDER BY i.fecha_inscripcion ASC";
$stmt_inscritos = $conn->prepare($sql_inscritos);
$stmt_inscritos->bind_param("i", $id_torneo);
$stmt_inscritos->execute();
$equipos_inscritos = $stmt_inscritos->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Torneo: <?php echo htmlspecialchars($torneo['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #12122e; color: #fff; font-family: 'Roboto', sans-serif; }
        .hero-torneo { 
            padding: 5rem 0; 
            background: linear-gradient(rgba(18, 18, 46, 0.85), rgba(18, 18, 46, 0.85)), url('<?php echo htmlspecialchars($torneo['imagen_portada_url'] ?? 'https://images.unsplash.com/photo-1542751371-380018935b62?q=80&w=1740&auto=format&fit=crop'); ?>') no-repeat center center; 
            background-size: cover; 
        }
        .info-box { background-color: #162447; padding: 1.5rem; border-radius: 15px; position: sticky; top: 20px;}
        .details-section { background-color: #162447; padding: 2rem; border-radius: 15px;}
        .btn-pagar { background-color: #28a745; border-color: #28a745; font-weight: bold; padding: 0.75rem 0; font-size: 1.2rem; }
        .team-list-item { display: flex; align-items: center; background-color: #1f4068; padding: 0.5rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; }
        .team-list-item img { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <header class="hero-torneo text-center text-white">
        <div class="container">
            <p class="badge bg-warning text-dark fs-6 mb-2"><?php echo htmlspecialchars($torneo['estado']); ?></p>
            <h1 class="display-4 fw-bold"><?php echo htmlspecialchars($torneo['nombre']); ?></h1>
            <p class="lead"><i class="fas fa-gamepad"></i> Juego: <?php echo htmlspecialchars($torneo['nombre_juego']); ?></p>
        </div>
    </header>

    <main class="container my-5">
        <div class="row g-4">
            <!-- Columna Principal de Información -->
            <div class="col-lg-8">
                <div class="details-section">
                    <h3 class="mb-3">Descripción del Torneo</h3>
                    <p class="text-white-50"><?php echo nl2br(htmlspecialchars($torneo['descripcion'])); ?></p>
                    <hr class="my-4">
                    <h3 class="mb-3">Reglas</h3>
                    <div class="text-white-50"><?php echo nl2br(htmlspecialchars($torneo['reglas'])); ?></div>
                    <hr class="my-4">
                    <h3 class="mb-3">Equipos Inscritos (<?php echo count($equipos_inscritos); ?>/<?php echo $torneo['max_equipos']; ?>)</h3>
                    <?php if (count($equipos_inscritos) > 0): ?>
                        <div class="row">
                            <?php foreach ($equipos_inscritos as $equipo): ?>
                                <div class="col-md-6">
                                    <div class="team-list-item">
                                        <img src="<?php echo htmlspecialchars($equipo['logo_url'] ?? 'https://wallpapers.com/images/hd/gaming-pictures-lfpbnfbogyadihpf.jpg'); ?>" alt="Logo">
                                        <span><?php echo htmlspecialchars($equipo['nombre_equipo']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-white-50">Aún no hay equipos inscritos. ¡Sé el primero!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Lateral de Detalles Rápidos -->
            <div class="col-lg-4">
                <div class="info-box">
                    <h4 class="mb-3">Detalles</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong><i class="fas fa-sitemap fa-fw me-2"></i>Formato:</strong> <?php echo htmlspecialchars($torneo['formato']); ?></li>
                        <li class="mb-2"><strong><i class="fas fa-trophy fa-fw me-2"></i>Premio:</strong> <?php echo htmlspecialchars($torneo['premio']); ?></li>
                        <li class="mb-2"><strong><i class="fas fa-calendar-alt fa-fw me-2"></i>Inicio:</strong> <?php echo date('d M, Y - H:i', strtotime($torneo['fecha_inicio'])); ?></li>
                    </ul>
                    <hr>
                    <div>
                        <h5 class="text-uppercase">Precio de Entrada</h5>
                        <p class="display-5 fw-bold text-warning">$<?php echo htmlspecialchars(number_format($torneo['precio_entrada'], 2)); ?></p>
                    </div>
                    
                    <div class="d-grid mt-3">
                        <?php if ($puede_inscribirse): ?>
                            <!-- Este botón llevaría a una pasarela de pago real -->
                            <a href="proceso_pago.php?torneo_id=<?php echo $id_torneo; ?>" class="btn btn-pagar">
                                <i class="fas fa-credit-card me-2"></i>Pagar e Inscribir Equipo
                            </a>
                        <?php elseif (!empty($mensaje_usuario)): ?>
                            <div class="alert alert-info text-center small">
                                <?php echo htmlspecialchars($mensaje_usuario); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>