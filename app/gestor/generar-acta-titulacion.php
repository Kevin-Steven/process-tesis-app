<?php
require '../config/config.php';
require_once('../../TCPDF-main/tcpdf.php');

if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

// Consulta para obtener los datos del tema por su ID
$sql = "SELECT t.tema, t.nota_revisor_tesis as nota_doc, u.cedula, t.nota_revisor_tesis, u.nombres AS estudiante_nombre, u.apellidos AS estudiante_apellidos
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("No se encontró información para este ID.");
}

$row = $result->fetch_assoc();

// Clase personalizada para el PDF
class ActaPDF extends TCPDF {
    function Header() {
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);

        // Imagen de fondo (antes del contenido)
        $this->Image('../../images/acta-formato-con.png', 0, 0, $this->getPageWidth(), $this->getPageHeight(), '', '', '', false, 300, '', false, false, 0);
    }

    function MultiCellRow($data, $widths, $height) {
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

    function CustomCheckPageBreak($h) {
        if ($this->GetY() + $h > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(30);
        }
    }
}

$pdf = new ActaPDF();

// Establecer márgenes generales (izquierdo, superior, derecho)
$margen_izquierdo = 18;  // Margen izquierdo de 30mm
$margen_superior = 25;   // Margen superior de 20mm
$margen_derecho = 18;    // Margen derecho de 30mm

$pdf->SetMargins($margen_izquierdo, $margen_superior, $margen_derecho);
$pdf->SetAutoPageBreak(true, 20); // Activa salto de página automático con margen inferior de 20mm

$pdf->AddPage();

// Ajustar la posición inicial del contenido
$pdf->SetY($margen_superior);

// ======================= CONTENIDO FORMATEADO =======================
$pdf->SetFont('times', 'B', 14);
$pdf->Cell(0, 0, 'ACTA FINAL DE TITULACION', 0, 1, 'C');
$pdf->Cell(0, 5, 'Nro. ISTJBA-GT-TDS-058-2024-1P', 0, 1, 'C');
$pdf->Ln(1);

// Aplicar texto con márgenes automáticamente
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Write(5, "El Comité Específico de Revisión y Aprobación de la Carrera: ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "Tecnología en Superior en Desarrollo de Software del Instituto Tecnológico Superior “Juan Bautista Aguirre”, conforme al proceso de Titulación correspondiente al ");
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Write(5, "I periodo académico del año 2024, ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "aprobado en Acta: ");
$pdf->SetFont('times', 'B', 11);
$pdf->Write(5, "ISTJBA-GT-TDS-002-2024-1P ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "de fecha 1 de abril del 2024 y en cumplimiento de lo establecido en el Art. 32 del Reglamento de Régimen Académico:\n\n");


// Definir márgenes específicos para este párrafo
$margen_izquie = 20; // Mayor margen izquierdo
$margen_dere = 50;  // Mayor margen derecho
$ancho_pagina = $pdf->getPageWidth();
$ancho_contenido = $ancho_pagina - ($margen_izquie + $margen_dere); // Ancho disponible dentro de los márgenes
$pdf->SetX($margen_izquie);

// Definir márgenes específicos para este párrafo
$margen_izquierdo = 27; // Mayor margen izquierdo
$margen_derecho = 27;  // Mayor margen derecho
$ancho_pagina = $pdf->getPageWidth();
$ancho_contenido = $ancho_pagina - ($margen_izquierdo + $margen_derecho); // Ancho disponible dentro de los márgenes

// Configurar fuente en tamaño 10 antes de imprimir
$pdf->SetFont('helvetica', '', 10);

// Texto con negrita en la primera parte y cursiva en todo el párrafo
$texto = '<i><strong>Artículo 32.-</strong> Diseño, acceso y aprobación de la unidad de integración curricular del tercer nivel. - Cada IES diseñará la unidad de integración curricular, estableciendo su estructura, contenidos y parámetros para el correspondiente desarrollo y evaluación. Para acceder a la unidad de integración curricular, es necesario haber completado las horas y/o créditos mínimos establecidos por la IES, así como cualquier otro requisito establecido en su normativa interna. Su aprobación se realizará a través de las siguientes opciones: a) Desarrollo de un trabajo de integración curricular; o, b) La aprobación de un examen de carácter complexivo, mediante el cual el estudiante deberá demostrar el manejo integral de los conocimientos adquiridos a lo largo de su formación.</i>';

// Aplicar margen izquierdo antes de escribir
$pdf->SetX($margen_izquierdo);

// Usar writeHTMLCell() para mezclar estilos (cursiva + negrita) y tamaño 10
$pdf->writeHTMLCell($ancho_contenido, 0, $margen_izquierdo, '', $texto, 0, 1, false, true, 'J', true);

$pdf->Ln(5); // Espaciado después del párrafo

// Volver a fuente normal
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "El suscrito en calidad de ");
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Write(5, "Coordinador Académico de la carrera Tecnología Superior en Desarrollo de Software, ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "después del análisis de los requisitos legales para el proceso de titulación a través de la modalidad: ");
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Write(5, "“Proyecto de Titulación” ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "y por autorización del ");
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Write(5, "Comité Específico de Revisión y Aprobación de la Carrera ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "en reunión realizada el 7 de agosto del 2024, ratifica la aprobación del proceso según nómina que consta en el ");
$pdf->SetFont('times', 'B', 11);
$pdf->Write(5, "Acta Nro. ISTJBA-GT-TDS-089-2024-1P ");
$pdf->SetFont('helvetica', '', 11);
$pdf->Write(5, "el cual fue aplicado a:");
$pdf->Ln(10);

// Tabla de Información del Estudiante
$widths = [50, 122];
$height = 7;
$pdf->MultiCellRow(['CARRERA:', 'TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE'], $widths, $height);
$pdf->MultiCellRow(['APELLIDOS Y NOMBRES:', $row['estudiante_nombre'] . ' ' . $row['estudiante_apellidos']], $widths, $height);
$pdf->MultiCellRow(['CEDULA:', $row['cedula']], $widths, $height);
$pdf->MultiCellRow(['TEMA DE PROYECTO:', $row['tema']], $widths, $height);
$pdf->Ln(5);

// Configurar fuente y encabezado
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(0, 7, 'Otorga la siguiente calificación:', 0, 1);
$pdf->Ln(2);

// Definir anchos de columnas
$col1_width = 95;  // Ancho para "Detalle"
$col2_width = 30;  // Ancho para "Nota Parcial"
$col3_width = 35;  // Ancho para "Nota Equivalente"
$total_width = $col1_width + $col2_width + $col3_width;

// Posicionar la tabla en el centro si es necesario
$margen_izquierdo = ($pdf->getPageWidth() - $total_width) / 2;
$pdf->SetX($margen_izquierdo);

// Encabezado de la tabla
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col1_width, 7, 'Detalle', 1, 0, 'L');
$pdf->Cell($col2_width, 7, 'Nota Parcial', 1, 0, 'C');
$pdf->Cell($col3_width, 7, 'Nota Equivalente', 1, 1, 'C');

// Filas de la tabla con alineación correcta
$pdf->SetFont('helvetica', '', 11);

// Definir el peso de cada evaluación
$peso_revision = 6.00; // 60% de 10 es 6.00
$nota_maxima = 10.00; // Nota máxima posible

// Calcular Nota Equivalente de Revisión de Documento
$nota_parcial_revision = $row['nota_doc']; // Nota obtenida
$nota_equivalente_revision = ($nota_parcial_revision / $nota_maxima) * $peso_revision;
$nota_equivalente_revision = number_format($nota_equivalente_revision, 2); // Formatear a 2 decimales

// Primera fila: Tres columnas
$pdf->SetX($margen_izquierdo);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col1_width, 7, 'REVISIÓN DE DOCUMENTO (60%)', 1, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
// Imprimir la fila con la Nota Parcial y la Nota Equivalente calculada
$pdf->Cell($col2_width, 7, $nota_parcial_revision . ' / 10.00', 1, 0, 'R'); 
$pdf->Cell($col3_width, 7, $nota_equivalente_revision . ' / 6.00', 1, 1, 'R');

// Segunda fila: Tres columnas
$pdf->SetX($margen_izquierdo);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col1_width, 7, 'NOTA DE SUSTENTACIÓN DE PROYECTO (40%)', 1, 0, 'L');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col2_width, 7, '/10.00', 1, 0, 'R');
$pdf->Cell($col3_width, 7, '/ 4.00', 1, 1, 'R');

// Última fila: Fusionar primera y segunda columna
$pdf->SetX($margen_izquierdo);
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col1_width + $col2_width, 7, 'NOTA FINAL TRABAJO DE TITULACIÓN:', 1, 0, 'L'); // Fusiona las dos primeras columnas
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell($col3_width, 7, '/10.00', 1, 1, 'R'); // Solo una celda a la derecha

$pdf->Ln(5);


// Firmas
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 7, 'Para constancia firman los que en ella intervinieron en la ciudad de Daule, el 6 de agosto de 2024.', 0, 1);
$pdf->Ln(10);
$pdf->Cell(90, 7, '_____________________________________', 0, 0, 'C');
$pdf->Cell(90, 7, '_____________________________________', 0, 1, 'C');

// Nombres (Misma fuente original)
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(90, 7, 'Ing. Jonathan Cevallos Guambuguete, Mgtr.', 0, 0, 'C');
$pdf->Cell(90, 7, $row['estudiante_nombre'] . ' ' . $row['estudiante_apellidos'], 0, 1, 'C');

// Cambiar fuente a negrita y tamaño 10 SOLO para los cargos
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 5, 'Coordinador de Carrera', 0, 0, 'C');
$pdf->Cell(90, 5, 'Alumno Egresado', 0, 1, 'C');

// Mantener el mismo estilo de negrita y tamaño para la última línea
$pdf->Cell(90, 5, 'Tecnología Superior en Desarrollo de Software', 0, 0, 'C');
$pdf->Cell(90, 5, 'Tecnología Superior en Desarrollo de Software', 0, 1, 'C');

// Volver a la fuente original (opcional si hay más contenido después)
$pdf->SetFont('helvetica', '', 11);

$pdf->Output('ACTA_FINAL_TITULACION.PDF', 'I');
?>
