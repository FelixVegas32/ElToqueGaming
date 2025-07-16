<?php
session_start();
require 'db_connect.php';

// Consulta para obtener todos los torneos que no estén finalizados o cancelados
$sql = "SELECT t.*, j.nombre AS nombre_juego 
        FROM torneos t 
        JOIN juegos j ON t.id_juego = j.id
        WHERE t.estado IN ('Programado', 'En Curso')
        ORDER BY t.fecha_inicio ASC";
$torneos = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneos Disponibles - ElToqueGaming</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #12122e; color: #fff; }
        .card-torneo {
            background-color: #162447;
            border: 1px solid #1f4068;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-torneo:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(232, 67, 100, 0.2);
        }
        .card-torneo img {
            height: 200px;
            object-fit: cover;
        }
        .card-title { font-family: 'Montserrat', sans-serif; font-weight: 700; }
        .card-footer { background-color: rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container my-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Torneos y Eventos</h1>
            <p class="lead text-white-50">Encuentra tu próxima competencia y demuestra tu habilidad.</p>
        </div>

        <div class="row g-4">
            <?php if (count($torneos) > 0): ?>
                <?php foreach ($torneos as $torneo): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card card-torneo h-100">
                            <img src="<?php echo htmlspecialchars($torneo['imagen_portada_url'] ?? 'https://images.unsplash.com/photo-1542751371-380018935b62?q=80&w=1740&auto=format&fit=crop'); ?>" class="card-img-top" alt="Imagen del torneo">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($torneo['nombre']); ?></h5>
                                <p class="card-text text-white-50 small flex-grow-1">
                                    <i class="fas fa-gamepad"></i> <?php echo htmlspecialchars($torneo['nombre_juego']); ?><br>
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('d M, Y', strtotime($torneo['fecha_inicio'])); ?>
                                </p>
                                <a href="detalle_torneo.php?id=<?php echo $torneo['id']; ?>" class="btn btn-primary mt-auto">Ver Detalles</a>
                            </div>
                            <div class="card-footer">
                                <small class="text-white-50">Entrada: <span class="fw-bold text-warning">$<?php echo number_format($torneo['precio_entrada'], 2); ?></span></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">No hay torneos programados en este momento. ¡Vuelve pronto!</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>