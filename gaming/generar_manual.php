<?php
// Requerimos la librería FPDF
// Asegúrate de que la ruta a la carpeta 'fpdf' sea correcta.
define('FPDF_FONTPATH', __DIR__ . '/fpdf/font/');
require(__DIR__ . '/fpdf/fpdf.php');

// Creamos una clase extendida para poder tener cabecera y pie de página personalizados
class PDF_Manual extends FPDF
{
    // Cabecera de página
    function Header()
    {
        // Logo (opcional, si tienes uno)
        // $this->Image('path/to/logo.png', 10, 8, 33);
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'ElToqueGaming - Manual de Usuario', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Tu Guia Completa para la Plataforma de Torneos', 0, 1, 'C');
        $this->Ln(10); // Salto de línea
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Función para crear un capítulo/sección con estilo
    function ChapterTitle($title)
    {
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(232, 67, 100); // Color rosado de tu tema
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, $title, 0, 1, 'L', true);
        $this->Ln(4);
        $this->SetTextColor(0, 0, 0); // Restaurar color de texto
    }

    // Función para el cuerpo del texto
    function ChapterBody($txt)
    {
        $this->SetFont('Arial', '', 12);
        // Usamos MultiCell para que el texto se ajuste automáticamente
        $this->MultiCell(0, 7, utf8_decode($txt));
        $this->Ln();
    }
}

// --- CREACIÓN Y CONTENIDO DEL DOCUMENTO PDF ---

$pdf = new PDF_Manual();
$pdf->AliasNbPages();
$pdf->AddPage();

// -- INTRODUCCIÓN --
$pdf->ChapterTitle('1. Bienvenido a ElToqueGaming');
$pdf->ChapterBody("Esta guia te ayudara a entender todas las funcionalidades de nuestra plataforma, desde registrar tu cuenta hasta competir en torneos y verlos en vivo. ¡Prepárate para la acción!");

// -- GESTIÓN DE CUENTA --
$pdf->ChapterTitle('2. Gestion de tu Cuenta');
$pdf->ChapterBody(
    "El primer paso es crear tu cuenta personal. Podras registrarte con tu nombre de usuario y correo. Una vez dentro, podras editar tu perfil, cambiar tu nombre de usuario y subir una foto de perfil personalizada para que la comunidad te reconozca."
);

// -- GESTIÓN DE EQUIPOS --
$pdf->ChapterTitle('3. Creacion y Gestion de Equipos');
$pdf->ChapterBody(
    "Nuestra plataforma esta disenada para la competencia en equipo. Como jugador, tendras dos opciones:\n\n" .
    "- Crear un equipo: Conviertete en capitan, elige un nombre y un logo. Tu solicitud sera revisada por un administrador. Una vez aprobada, podras gestionar tu equipo.\n" .
    "- Unirte a un equipo: Explora la lista de equipos existentes y envia una solicitud para unirte al que prefieras. El capitan del equipo debera aprobar tu solicitud."
);
$pdf->ChapterBody(
    "Como capitan, tendras acceso a un panel de control exclusivo para tu equipo. Desde alli podras:\n\n" .
    "- Cambiar el nombre del equipo (¡solo una vez!).\n" .
    "- Actualizar el logo del equipo cuando quieras.\n" .
    "- Aprobar o rechazar las solicitudes de otros jugadores que quieran unirse.\n" .
    "- Ver la lista completa de los miembros de tu equipo."
);

// -- TORNEOS --
$pdf->ChapterTitle('4. Participacion en Torneos');
$pdf->ChapterBody(
    "El corazon de la plataforma. Los administradores crean y gestionan torneos de diferentes juegos. Podras:\n\n" .
    "- Ver la lista de torneos disponibles y su estado (Programado, En Curso, Finalizado).\n" .
    "- Ver los detalles de cada torneo: reglas, premios, formato y equipos ya inscritos.\n" .
    "- Inscribir a tu equipo: Si eres el capitan y hay cupos, podras realizar el pago simulado para inscribir a todo tu equipo. Al hacerlo, se generara un comprobante en PDF."
);

// -- PARTIDOS Y NOTIFICACIONES --
$pdf->ChapterTitle('5. Partidos y Notificaciones');
$pdf->ChapterBody(
    "Una vez que las inscripciones a un torneo se llenan, los administradores generan los enfrentamientos (bracket).\n\n" .
    "- Notificaciones: Como capitan, recibiras una notificacion en la plataforma cuando los partidos de un torneo en el que estas inscrito hayan sido programados.\n" .
    "- Gestion de Partidos (Admins): Los administradores pueden actualizar los resultados de cada partido y avanzar a los equipos ganadores a la siguiente ronda."
);

// -- STREAMING --
$pdf->ChapterTitle('6. Servicio de Streaming');
$pdf->ChapterBody(
    "¡No te pierdas ni un segundo de la accion! Nuestra plataforma incluye una funcionalidad de streaming para que puedas ver los partidos mas importantes en tiempo real.\n\n" .
    "- Visualizacion en Vivo: Dentro de la pagina de detalles de un torneo, los partidos marcados como 'En Vivo' tendran un enlace directo a la transmision.\n" .
    "- Integracion: Los administradores pueden anadir la URL del stream (Twitch, YouTube, etc.) a cada partido, permitiendo a toda la comunidad seguir a sus equipos favoritos desde nuestra propia plataforma."
);

// --- SALIDA DEL PDF ---
if (ob_get_contents()) ob_end_clean();

$pdf->Output('D', 'Manual_Usuario_ElToqueGaming.pdf');
exit;