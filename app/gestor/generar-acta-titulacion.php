<?php
require '../config/config.php';
require_once('../../TCPDF-main/tcpdf.php');

// Verificar ID
if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}
$id = intval($_GET['id']);

$sql = "SELECT 
    t.tema,
    t.j1_nota_sustentar AS nota_uno,
    t.j2_nota_sustentar AS nota_dos,
    t.j3_nota_sustentar AS nota_tres,
    /* Agrega estas tres columnas */
    t.j1_nota_sustentar_2,
    t.j2_nota_sustentar_2,
    t.j3_nota_sustentar_2,

    COALESCE(t.nota_revisor_tesis, 0) AS nota_doc, 
    u.id AS postulante_id, 
    u.cedula, 
    u.nombres AS estudiante_nombre, 
    u.apellidos AS estudiante_apellidos,
    p.id AS pareja_id, 
    p.cedula AS pareja_cedula, 
    p.nombres AS pareja_nombre, 
    p.apellidos AS pareja_apellidos
FROM tema t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
WHERE t.id = $id";

$result = $conn->query($sql);
if ($result->num_rows === 0) {
    die("No se encontró información para este ID.");
}
$row = $result->fetch_assoc();

// ------------------------------------------------------
// Clase PDF con imagen de fondo en Header
// ------------------------------------------------------
class ActaPDF extends TCPDF
{
    public function Header() {
        // Imagen de fondo a página completa
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);

        $this->Image(
            '../../images/acta-formato-con.png',
            0,
            0,
            $this->getPageWidth(),
            $this->getPageHeight(),
            '',
            '',
            '',
            false,
            300,
            '',
            false,
            false,
            0
        );
    }

    public function Footer() {
        // Dejamos vacío para no tener pie de página
    }

    // Helper para filas de 2 celdas
    public function MultiCellRow($data, $widths, $height) {
        $nb = 0;
        foreach ($data as $key => $value) {
            $nb = max($nb, $this->getNumLines($value, $widths[$key]));
        }
        $h = $height * $nb;
        $this->CustomCheckPageBreak($h);

        foreach ($data as $key => $value) {
            $w = $widths[$key];
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, $h);
            $this->setCellPaddings(1, 0, 1, 0);
            $this->MultiCell($w, $height, trim($value), 0, 'L', 0, 0, '', '', true, 0, false, true, $h, 'M', true);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    public function CustomCheckPageBreak($h) {
        if ($this->GetY() + $h > ($this->getPageHeight() - $this->getBreakMargin())) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(25);
        }
    }
}

// ------------------------------------------------------
// Función para generar el PDF (postulante o pareja)
// ------------------------------------------------------
function generarPDFCompleto($rowData, $id_formateado) {
    // Márgenes deseados para el contenido
    $margen_izquierdo = 18;
    $margen_superior  = 25;
    $margen_derecho   = 18;

    // Instanciamos
    $pdf = new ActaPDF();

    // Definir salto auto
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    // Ajustar la posición inicial del contenido
    $pdf->SetMargins($margen_izquierdo, $margen_superior, $margen_derecho);
    $pdf->SetY($margen_superior);
    $pdf->SetX($margen_izquierdo);

    // TÍTULOS
    $pdf->SetFont('times', 'B', 14);
    // Título principal centrado
    $pdf->Cell(0, 0, 'ACTA FINAL DE TITULACIÓN', 0, 1, 'C');
    $pdf->Ln(1);
    // Subtítulo con la numeración
    $pdf->Cell(0, 5, 'Nro. ISTJBA-GT-TDS-' . date("Y") . '-' . $id_formateado, 0, 1, 'C');
    $pdf->Ln(2);

    // PÁRRAFO 1
    $pdf->SetFont('helvetica', '', 11);
    $texto1 = ''
        . '<b>El Comité Específico de Revisión y Aprobación de la Carrera: </b>, '
        . 'Tecnología en Superior en Desarrollo de Software del Instituto Superior Tecnológico “Juan Bautista Aguirre”, '
        . 'conforme al proceso de Titulación correspondiente al '
        . '<b>II periodo académico del año 2024 del </b>'
        . '<b>Comité Específico de Revisión y Aprobación de la Carrera,</b> '
        . 'y en cumplimiento de lo establecido en el Art. 32 del Reglamento de Régimen Académico:';
    $pdf->writeHTMLCell(0, 0, '', '', $texto1, 0, 1, false, true, 'J', true);
    $pdf->Ln(5);

    // PÁRRAFO 2 (ARTÍCULO 32) con mayor margen X
    $pdf->SetFont('helvetica', '', 10);
    $texto2 = '<i><strong>Artículo 32.-</strong> Diseño, acceso y aprobación de la unidad de integración curricular del tercer nivel. - Cada IES diseñará la unidad de integración curricular, estableciendo su estructura, contenidos y parámetros para el correspondiente desarrollo y evaluación. Para acceder a la unidad de integración curricular, es necesario haber completado las horas y/o créditos mínimos establecidos por la IES, así como cualquier otro requisito establecido en su normativa interna. Su aprobación se realizará a través de las siguientes opciones: a) Desarrollo de un trabajo de integración curricular; o, b) La aprobación de un examen de carácter complexivo, mediante el cual el estudiante deberá demostrar el manejo integral de los conocimientos adquiridos a lo largo de su formación.</i>';
    // Le damos un margen extra
    $margen_izq_parr2 = $margen_izquierdo + 9; 
    $margen_der_parr2 = $margen_derecho + 9;
    $ancho_pagina   = $pdf->getPageWidth();
    $ancho_contenido= $ancho_pagina - ($margen_izq_parr2 + $margen_der_parr2);
    $pdf->SetX($margen_izq_parr2);
    $pdf->writeHTMLCell($ancho_contenido, 0, $margen_izq_parr2, $pdf->GetY(), $texto2, 0, 1, false, true, 'J', true);
    $pdf->Ln(5);

    // PÁRRAFO 3
    $pdf->SetFont('helvetica', '', 11);
    $texto3 = ''
        . 'El suscrito en calidad de <b>Coordinador Académico de la carrera Tecnología Superior en Desarrollo de Software</b>, '
        . 'después del análisis de los requisitos legales para el proceso de titulación a través de la modalidad: '
        . '<b>“Proyecto de Titulación”</b> y por autorización del '
        . '<b>Comité Específico de Revisión y Aprobación de la Carrera,</b> '
        . 'ratifica la aprobación del proceso al siguiente estudiante:';
    $pdf->writeHTMLCell(0, 0, '', '', $texto3, 0, 1, false, true, 'J', true);
    $pdf->Ln(5);

    // TABLA DE DATOS DEL ESTUDIANTE
    $pdf->SetFont('helvetica', '', 11);
    $widths = [50, 122];
    $height = 7;
    $pdf->MultiCellRow(['CARRERA:', 'TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE'], $widths, $height);
    $pdf->MultiCellRow(['APELLIDOS Y NOMBRES:', $rowData['estudiante_nombre'].' '.$rowData['estudiante_apellidos']], $widths, $height);
    $pdf->MultiCellRow(['CÉDULA:', $rowData['cedula']], $widths, $height);
    $pdf->MultiCellRow(['TEMA DE PROYECTO:', mb_strtoupper($rowData['tema'])], $widths, $height);
    $pdf->Ln(5);

    // ENCABEZADO NOTAS
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 6, 'Otorga la siguiente calificación:', 0, 1);
    $pdf->Ln(2);

    // Cálculo de notas
    $nota_doc    = floatval($rowData['nota_doc']);
    $nota_uno    = floatval($rowData['nota_uno']);
    $nota_dos    = floatval($rowData['nota_dos']);
    $nota_tres   = floatval($rowData['nota_tres']);

    $peso_revision      = 6.00; // 60%
    $peso_sustentacion  = 4.00; // 40%
    $nota_maxima        = 10.00;

    $nota_equiv_doc     = ($nota_doc / $nota_maxima) * $peso_revision;
    $prom_sust          = ($nota_uno + $nota_dos + $nota_tres) / 3;
    $nota_equiv_sustent = ($prom_sust / $nota_maxima) * $peso_sustentacion;
    $nota_final         = $nota_equiv_doc + $nota_equiv_sustent;

    // Tabla 3 columnas
    $col1_width = 95;
    $col2_width = 30;
    $col3_width = 35;

    // FILA ENCABEZADO TABLA
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell($col1_width, 7, 'Detalle', 1, 0, 'L');
    $pdf->Cell($col2_width, 7, 'Nota Parcial', 1, 0, 'C');
    $pdf->Cell($col3_width, 7, 'Nota Equivalente', 1, 1, 'C');

    // REVISIÓN
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell($col1_width, 7, 'REVISIÓN DE DOCUMENTO (60%)', 1, 0, 'L');
    $pdf->Cell($col2_width, 7, number_format($nota_doc, 2). ' / 10.00', 1, 0, 'R');
    $pdf->Cell($col3_width, 7, number_format($nota_equiv_doc, 2). ' / 6.00', 1, 1, 'R');

    // SUSTENTACIÓN
    $pdf->Cell($col1_width, 7, 'NOTA DE SUSTENTACIÓN DE PROYECTO (40%)', 1, 0, 'L');
    $pdf->Cell($col2_width, 7, number_format($prom_sust, 2). ' / 10.00', 1, 0, 'R');
    $pdf->Cell($col3_width, 7, number_format($nota_equiv_sustent, 2). ' / 4.00', 1, 1, 'R');

    // NOTA FINAL
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell($col1_width + $col2_width, 7, 'NOTA FINAL TRABAJO DE TITULACIÓN:', 1, 0, 'L');
    $pdf->Cell($col3_width, 7, number_format($nota_final, 2). ' / 10.00', 1, 1, 'R');

    $pdf->Ln(5);

    // FIRMAS
    $pdf->SetFont('helvetica', '', 11);

    // Fecha actual
    $meses = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
    ];
    $dia  = date('j');
    $mes  = $meses[intval(date('n'))];
    $anio = date('Y');
    $fecha_actual = "$dia de $mes de $anio";

    $pdf->Cell(0, 7, "Para constancia firman los que en ella intervinieron en la ciudad de Daule, el $fecha_actual.", 0, 1);
    $pdf->Ln(10);

    $pdf->Cell(90, 5, '_____________________________________', 0, 0, 'C');
    $pdf->Cell(90, 5, '_____________________________________', 0, 1, 'C');

    $nombreCompleto = mb_convert_case($rowData['estudiante_nombre'].' '.$rowData['estudiante_apellidos'], MB_CASE_TITLE, "UTF-8");

    $pdf->Cell(90, 6, 'Ing. Jonathan Cevallos Guambuguete, Mgtr.', 0, 0, 'C');
    $pdf->Cell(90, 6, $nombreCompleto, 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(90, 5, 'Coordinador de Carrera', 0, 0, 'C');
    $pdf->Cell(90, 5, 'Alumno Egresado', 0, 1, 'C');
    $pdf->Cell(90, 5, 'Tecnología Superior en Desarrollo de Software', 0, 0, 'C');
    $pdf->Cell(90, 5, 'Tecnología Superior en Desarrollo de Software', 0, 1, 'C');

    // Devolvemos el PDF en MEMORIA para luego ver cómo lo servimos
    return $pdf->Output('', 'S'); 
    // 'S' => devuelve como cadena (string) el contenido binario del PDF
}

// ------------------------------------------------------
// LÓGICA PRINCIPAL
// ------------------------------------------------------

// 1) Generar el PDF principal
$id_formateado_postulante = sprintf("%03d", $row['postulante_id']);
$pdfStringPostulante = generarPDFCompleto($row, $id_formateado_postulante);

// 2) Revisar si hay pareja
if (!empty($row['pareja_id'])) {
    // Hay pareja => generamos el PDF de la pareja y empaquetamos en ZIP

    $rowPareja = $row;

    // Cambiar nombres, apellidos y cédula a la pareja
    $rowPareja['estudiante_nombre']    = $row['pareja_nombre'];
    $rowPareja['estudiante_apellidos'] = $row['pareja_apellidos'];
    $rowPareja['cedula']               = $row['pareja_cedula'];
    $rowPareja['postulante_id']        = $row['pareja_id'];

    // **Reemplazar** notas del primer, segundo y tercer jurado con sus equivalentes _2
    $rowPareja['nota_uno']  = $row['j1_nota_sustentar_2'];
    $rowPareja['nota_dos']  = $row['j2_nota_sustentar_2'];
    $rowPareja['nota_tres'] = $row['j3_nota_sustentar_2'];

    // Generar ID formateado de la pareja
    $id_formateado_pareja = sprintf("%03d", $row['pareja_id']);

    // Generar el PDF para la pareja
    $pdfStringPareja = generarPDFCompleto($rowPareja, $id_formateado_pareja);

    // Crear en un directorio temporal dos archivos .pdf y luego meterlos a un ZIP
    $directorioTemporal = sys_get_temp_dir();

    // PDF postulante
    $nombrePdfPostulante = "ACTA_TITULACION_ISTJBA_" . date("Y") . "_" . $id_formateado_postulante . ".pdf";
    $rutaPdfPostulante   = $directorioTemporal . '/' . $nombrePdfPostulante;
    file_put_contents($rutaPdfPostulante, $pdfStringPostulante);

    // PDF pareja
    $nombrePdfPareja = "ACTA_TITULACION_ISTJBA_" . date("Y") . "_" . $id_formateado_pareja . ".pdf";
    $rutaPdfPareja   = $directorioTemporal . '/' . $nombrePdfPareja;
    file_put_contents($rutaPdfPareja, $pdfStringPareja);

    // Crear ZIP
    $zipFileName = "ACTAS_TITULACION_PDFs_{$id}.zip";
    $zipFilePath = "$directorioTemporal/$zipFileName";

    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFile($rutaPdfPostulante, $nombrePdfPostulante);
        $zip->addFile($rutaPdfPareja, $nombrePdfPareja);
        $zip->close();
    } else {
        die("Error al crear ZIP");
    }

    // Descargamos el ZIP
    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=\"$zipFileName\"");
    readfile($zipFilePath);

    // Borrar archivos temporales
    @unlink($rutaPdfPostulante);
    @unlink($rutaPdfPareja);
    @unlink($zipFilePath);
    exit();

} else {
    // NO hay pareja => sirve el PDF directamente en el navegador (inline)
    $filenameSolo = "ACTA_TITULACION_ISTJBA_" . date("Y") . "_" . $id_formateado_postulante . ".pdf";

    header("Content-Type: application/pdf");
    // 'inline' hace que se intente visualizar en el navegador
    header("Content-Disposition: inline; filename=\"$filenameSolo\""); 
    echo $pdfStringPostulante; 
    exit();
}
