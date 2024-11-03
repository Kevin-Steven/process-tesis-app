<?php
require '../config/config.php';
require_once('../../TCPDF-main/tcpdf.php');

class CustomPDF extends TCPDF
{
    function Header()
    {
        // Agregar el logo izquierdo
        $this->Image('../../images/logoJBA.png', 5, 7, 50);

        // Agregar el logo derecho
        $this->Image('../../images/TDSL.png', 140, 10, 60);

        // Salto de línea para separar el encabezado del contenido
        $this->Ln(10);

        // Ajustar la posición del inicio del contenido después del encabezado
        if ($this->PageNo() == 1) {
            $this->SetY(25); // Menor separación para la primera página
        } else {
            $this->SetY(30); 
        }
    }

    function MultiCellRow($data, $widths, $height)
    {
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

    function CustomCheckPageBreak($h)
    {
        if ($this->GetY() + $h > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->AddPage($this->CurOrientation);
            $this->SetY(30); // Asegura el espacio después del encabezado en cada nueva página
        }
    }
}

// Inicializar TCPDF
$pdf = new CustomPDF();
$pdf->AddPage();
$pdf->SetY(25); // Ajusta la posición del contenido en la primera página

// Configurar la fuente para el título de la tabla
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Listado de Postulantes Aprobados', 0, 1, 'C');
$pdf->Ln(5); // Ajuste para añadir más espacio debajo del título

// Encabezados de la tabla
$pdf->SetFont('helvetica', 'B', 12);
$widths = [50, 70, 70];
$height = 7;
$headers = ['Cédula', 'Nombres', 'Apellidos'];
$pdf->MultiCellRow($headers, $widths, $height);

// Consulta para obtener los postulantes aprobados
$sql = "SELECT u.cedula, u.nombres, u.apellidos
        FROM documentos_postulante dp
        JOIN usuarios u ON dp.usuario_id = u.id
        WHERE dp.estado_inscripcion = 'Aprobado' 
        AND dp.estado_registro = 0
        AND dp.fecha_subida = (
            SELECT MAX(dp2.fecha_subida)
            FROM documentos_postulante dp2
            WHERE dp2.usuario_id = dp.usuario_id
        )";
$result = $conn->query($sql);

// Llenar la tabla con datos
$pdf->SetFont('helvetica', '', 12);
while ($row = $result->fetch_assoc()) {
    $cedula = $row['cedula'];
    $nombres = $row['nombres'];
    $apellidos = $row['apellidos'];

    // Agregar la fila a la tabla
    $pdf->MultiCellRow([$cedula, $nombres, $apellidos], $widths, $height);
}

// Salida del PDF
$pdf->Output();
