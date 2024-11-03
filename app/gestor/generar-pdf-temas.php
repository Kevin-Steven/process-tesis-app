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
            $this->SetY(30); // Espaciado estándar para las siguientes páginas
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
$pdf->Cell(0, 10, 'Listado de Temas Aprobados', 0, 1, 'C');
$pdf->Ln(5); // Ajuste para añadir más espacio debajo del título

// Encabezados de la tabla
$pdf->SetFont('helvetica', 'B', 12);
$widths = [50, 50, 90];
$height = 7;
$headers = ['Postulante 1', 'Postulante 2', 'Tema'];
$pdf->MultiCellRow($headers, $widths, $height);

// Consulta para obtener los temas aprobados
$sql = "SELECT t.tema, 
       u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos,
       p.nombres AS pareja_nombres, p.apellidos AS pareja_apellidos
FROM tema t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
WHERE t.estado_tema = 'Aprobado'
AND t.estado_registro = 0;
";
$result = $conn->query($sql);

// Llenar la tabla con datos
$pdf->SetFont('helvetica', '', 12);
while ($row = $result->fetch_assoc()) {
    $postulante = $row['postulante_nombres'] . ' ' . $row['postulante_apellidos'];
    $pareja = ($row['pareja_nombres'] && $row['pareja_apellidos']) 
        ? $row['pareja_nombres'] . ' ' . $row['pareja_apellidos'] 
        : 'No aplica';
    $tema = $row['tema'];

    // Agregar la fila a la tabla
    $pdf->MultiCellRow([$postulante, $pareja, $tema], $widths, $height);
}

// Salida del PDF
$pdf->Output();

?>
