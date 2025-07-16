<?php
session_start();
require 'db_connect.php';

// ... (Seguridad y obtención de juegos sin cambios) ...
$error_message = '';
$juegos = $conn->query("SELECT id, nombre FROM juegos ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Recolectar datos del formulario ---
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $id_juego = $_POST['id_juego'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $formato = $_POST['formato'];
    $max_equipos = $_POST['max_equipos'];
    $premio = trim($_POST['premio']);
    $reglas = trim($_POST['reglas']);
    $precio_entrada = $_POST['precio_entrada'];
    $id_organizador = $_SESSION['userid'];

    // --- Validación de campos (sin cambios) ---
    if (empty($nombre) || empty($id_juego) || empty($fecha_inicio) || empty($max_equipos)) {
        $error_message = "Los campos obligatorios no pueden estar vacíos.";
    } else {
        
        // ==========================================================
        // INICIO DE LA CORRECCIÓN 1: Lógica de subida de imagen
        // ==========================================================
        $imagen_portada_url = null; // Empezamos con null

        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/tournament_covers/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_info = pathinfo($_FILES["imagen_portada"]["name"]);
            $file_type = strtolower($file_info['extension']);
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_type, $allowed_types)) {
                // Crear un nombre de archivo único
                $new_file_name = 'torneo_' . time() . '.' . $file_type;
                $target_file = $upload_dir . $new_file_name;

                if (move_uploaded_file($_FILES["imagen_portada"]["tmp_name"], $target_file)) {
                    $imagen_portada_url = $target_file; // Guardamos la ruta si la subida fue exitosa
                } else {
                    $error_message = "Hubo un error al subir la imagen de portada.";
                }
            } else {
                $error_message = "Formato de imagen de portada no permitido.";
            }
        }
        // ==========================================================
        // FIN DE LA CORRECCIÓN 1
        // ==========================================================
        
        // Procedemos solo si no hubo errores con la subida de la imagen
        if (empty($error_message)) {
            // Consulta SQL actualizada para incluir la imagen
            $sql = "INSERT INTO torneos 
                        (nombre, descripcion, id_juego, id_organizador, fecha_inicio, formato, max_equipos, premio, reglas, precio_entrada, imagen_portada_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // bind_param actualizado con el nuevo campo 's' para la URL de la imagen
                $stmt->bind_param("ssiisissids", 
                    $nombre, $descripcion, $id_juego, $id_organizador, $fecha_inicio, 
                    $formato, $max_equipos, $premio, $reglas, $precio_entrada, $imagen_portada_url
                );
                
                if ($stmt->execute()) {
                    header("Location: admin_torneos.php");
                    exit();
                } else {
                    $error_message = "Error al crear el torneo: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_message = "Error al preparar la consulta: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Torneo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #1a1a2e; color: #fff;">
    <div class="container my-5">
        <h1 class="mb-4">Crear Nuevo Torneo</h1>
        <?php if($error_message): ?><div class="alert alert-danger"><?php echo $error_message; ?></div><?php endif; ?>

        <form action="crear_torneo.php" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- Columna Izquierda: Información Principal -->
                <div class="col-md-8">
                    <div class="p-3 bg-dark rounded">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Torneo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        
                        <!-- ========================================================== -->
                        <!-- INICIO DE LA CORRECCIÓN 2: Campo para subir la imagen      -->
                        <!-- ========================================================== -->
                        <div class="mb-3">
                            <label for="imagen_portada" class="form-label">Imagen de Portada del Torneo</label>
                            <input class="form-control" type="file" id="imagen_portada" name="imagen_portada">
                            <div class="form-text">Se usará como la miniatura en la lista de torneos.</div>
                        </div>
                        <!-- ========================================================== -->
                        <!-- FIN DE LA CORRECCIÓN 2                                     -->
                        <!-- ========================================================== -->

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="reglas" class="form-label">Reglas</label>
                            <textarea class="form-control" id="reglas" name="reglas" rows="6"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Detalles Específicos -->
                <div class="col-md-4">
                     <div class="p-3 bg-dark rounded">
                        <!-- ... (los demás campos de la columna derecha no cambian) ... -->
                        <div class="mb-3">
                            <label for="id_juego" class="form-label">Juego</label>
                            <select class="form-select" id="id_juego" name="id_juego" required>
                                <option value="" disabled selected>-- Elige un juego --</option>
                                <?php foreach ($juegos as $juego): ?>
                                    <option value="<?php echo $juego['id']; ?>"><?php echo htmlspecialchars($juego['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha y Hora de Inicio</label>
                            <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="mb-3">
                            <label for="formato" class="form-label">Formato</label>
                            <select class="form-select" id="formato" name="formato" required>
                                <option value="Eliminación Simple">Eliminación Simple</option>
                                <option value="Doble Eliminación">Doble Eliminación</option>
                                <option value="Liga">Liga (Round Robin)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="max_equipos" class="form-label">Máximo de Equipos</label>
                            <input type="number" class="form-control" id="max_equipos" name="max_equipos" min="2" required>
                        </div>
                        <div class="mb-3">
                            <label for="premio" class="form-label">Premio</label>
                            <input type="text" class="form-control" id="premio" name="premio">
                        </div>
                        <div class="mb-3">
                            <label for="precio_entrada" class="form-label">Precio de Entrada ($)</label>
                            <input type="number" class="form-control" id="precio_entrada" name="precio_entrada" min="0.00" step="0.01" value="0.00" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Crear Torneo</button>
                <a href="admin_torneos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>