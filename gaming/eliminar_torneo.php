<?php
session_start();
require 'db_connect.php';

// --- SEGURIDAD ---
// 1. Asegurarse de que el usuario es un administrador
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
//     header('Location: principal.php');
//     exit();
// }

// 2. Asegurarse de que se recibió un ID y es un número
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si no hay ID, no hay nada que hacer. Volver al panel.
    header('Location: admin_torneos.php');
    exit();
}

$id_torneo = $_GET['id'];

// --- LÓGICA DE ELIMINACIÓN ---
// Gracias a que configuramos la base de datos con 'ON DELETE CASCADE',
// al eliminar un torneo, se borrarán automáticamente todas las filas
// relacionadas en las tablas 'inscripciones' y 'partidos'.
// Por lo tanto, solo necesitamos una única consulta DELETE.

$stmt = $conn->prepare("DELETE FROM torneos WHERE id = ?");
$stmt->bind_param("i", $id_torneo);

if ($stmt->execute()) {
    // Si la eliminación fue exitosa, guardar un mensaje en la sesión.
    if ($stmt->affected_rows > 0) {
        $_SESSION['success_message'] = "El torneo ha sido eliminado correctamente.";
    } else {
        // Esto puede pasar si el ID no existía.
        $_SESSION['error_message'] = "No se encontró ningún torneo con ese ID.";
    }
} else {
    // Si hubo un error en la base de datos.
    $_SESSION['error_message'] = "Error al intentar eliminar el torneo.";
}

$stmt->close();
$conn->close();

// --- REDIRECCIÓN ---
// Después de la operación, redirigir siempre de vuelta al panel.
header('Location: admin_torneos.php');
exit();
?>