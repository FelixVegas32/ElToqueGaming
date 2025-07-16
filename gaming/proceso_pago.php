<?php
session_start();
require 'db_connect.php';

// ==========================================================
// INICIO DE LA CORRECCIÓN CLAVE: DEFINIR LA RUTA DE LAS FUENTES
// ==========================================================
// Esta constante le dice a FPDF dónde encontrar archivos como 'helveticab.php'.
// DEBE definirse ANTES de requerir fpdf.php.
define('FPDF_FONTPATH', __DIR__ . '/fpdf/font/');
// ==========================================================
// FIN DE LA CORRECCIÓN CLAVE
// ==========================================================

// Requerimos la librería FPDF.
require(__DIR__ . '/fpdf/fpdf.php');


// --- SEGURIDAD Y VALIDACIÓN INICIAL (sin cambios) ---
if (!isset($_SESSION['loggedin']) || empty($_SESSION['id_equipo_actual']) || !isset($_GET['torneo_id'])) {
    header('Location: principal.php');
    exit();
}

$id_torneo = $_GET['torneo_id'];
$id_equipo = $_SESSION['id_equipo_actual'];

// --- OBTENER DATOS PARA EL PDF Y VALIDACIÓN (sin cambios) ---
$sql = "SELECT t.nombre AS nombre_torneo, t.precio_entrada, e.nombre_equipo, u.nombre_usuario AS nombre_capitan
        FROM torneos t, equipos e, usuarios u
        WHERE t.id = ? AND e.id = ? AND u.id = e.id_capitan";
$stmt_data = $conn->prepare($sql);
$stmt_data->bind_param("ii", $id_torneo, $id_equipo);
$stmt_data->execute();
$data = $stmt_data->get_result()->fetch_assoc();

if (!$data) {
    die("Error de validación: No se pudieron obtener los datos del torneo o del equipo.");
}

// --- SIMULACIÓN DE PAGO E INSCRIPCIÓN (sin cambios) ---
$id_transaccion_simulada = 'ETG-' . time() . '-' . $id_equipo;
$estado_pago = 'Pagado'; 

$stmt_inscribir = $conn->prepare("INSERT INTO inscripciones (id_torneo, id_equipo, estado_pago, id_transaccion_pago) VALUES (?, ?, ?, ?)");
$stmt_inscribir->bind_param("iiss", $id_torneo, $id_equipo, $estado_pago, $id_transaccion_simulada);

if (!$stmt_inscribir->execute()) {
    die("Error: No se pudo completar la inscripción. Es posible que tu equipo ya esté inscrito en este torneo.");
}
$stmt_inscribir->close();

// --- GENERACIÓN DEL PDF CON FPDF (sin cambios) ---
class PDF extends FPDF
{
    // Cambiamos 'helvetica' por 'Arial' que es una fuente estándar y suele dar menos problemas.
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, 'Comprobante de Inscripcion', 0, 0, 'C');
        $this->Ln(20);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'ElToqueGaming - Torneos', 0, 1, 'C');
$pdf->Ln(10);

// ... (El resto del código que genera el contenido del PDF es exactamente el mismo)
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha: ' . date('d/m/Y'), 0, 1);
$pdf->Cell(0, 10, 'ID de Transaccion: ' . $id_transaccion_simulada, 0, 1);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Torneo:', 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($data['nombre_torneo']), 0, 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Equipo Inscrito:', 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($data['nombre_equipo']), 0, 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Capitan:', 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode($data['nombre_capitan']), 0, 1);
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(130, 10, 'Total Pagado:', 1, 0, 'R');
$pdf->Cell(40, 10, '$' . number_format($data['precio_entrada'], 2), 1, 1, 'C');
$pdf->Ln(20);
$pdf->SetFont('Arial', 'I', 10);
$pdf->MultiCell(0, 5, utf8_decode('Gracias por tu inscripción. Guarda este comprobante. Deberás presentarlo si es requerido por los administradores del torneo. ¡Mucha suerte en la competencia!'));


// --- FORZAR LA DESCARGA DEL PDF ---
if (ob_get_contents()) ob_end_clean();

$pdf->Output('D', 'comprobante_inscripcion_' . str_replace(' ', '_', $data['nombre_equipo']) . '.pdf');
exit;
?>