<?php
session_start();
require 'db_connect.php';

// Seguridad: Solo capitanes logueados pueden procesar.
if (!isset($_SESSION['loggedin']) || empty($_SESSION['id_equipo_actual']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: principal.php');
    exit();
}

$id_equipo_capitan = $_SESSION['id_equipo_actual'];
$id_solicitud = $_POST['id_solicitud'];
$accion = $_POST['accion'];

// Obtener los datos de la solicitud para verificar.
$stmt = $conn->prepare("SELECT id_jugador, id_equipo FROM solicitudes_union WHERE id = ?");
$stmt->bind_param("i", $id_solicitud);
$stmt->execute();
$solicitud = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Verificar que la solicitud es para el equipo del capitán actual.
if (!$solicitud || $solicitud['id_equipo'] != $id_equipo_capitan) {
    header('Location: panel_equipo.php');
    exit();
}

$id_jugador_solicitante = $solicitud['id_jugador'];

if ($accion === 'aprobar') {
    $conn->begin_transaction();
    try {
        // 1. Asignar el equipo al usuario. ¡Esto lo convierte en miembro!
        $stmt_update_user = $conn->prepare("UPDATE usuarios SET id_equipo_actual = ? WHERE id = ?");
        $stmt_update_user->bind_param("ii", $id_equipo_capitan, $id_jugador_solicitante);
        $stmt_update_user->execute();
        $stmt_update_user->close();
        
        // 2. Actualizar el estado de la solicitud.
        $stmt_update_request = $conn->prepare("UPDATE solicitudes_union SET estado = 'aprobado' WHERE id = ?");
        $stmt_update_request->bind_param("i", $id_solicitud);
        $stmt_update_request->execute();
        $stmt_update_request->close();

        $conn->commit();
        // 3. ¡Guardar el mensaje de éxito en la sesión!
        $_SESSION['success_message'] = "¡Miembro aprobado! El jugador ha sido añadido al equipo.";

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error al aprobar la solicitud.";
    }

} elseif ($accion === 'rechazar') {
    $stmt_update_request = $conn->prepare("UPDATE solicitudes_union SET estado = 'rechazado' WHERE id = ?");
    $stmt_update_request->bind_param("i", $id_solicitud);
    $stmt_update_request->execute();
    $stmt_update_request->close();
    $_SESSION['success_message'] = "La solicitud ha sido rechazada.";
}

// 4. Redirigir de vuelta al panel para ver los resultados.
header('Location: panel_equipo.php');
exit();
?>