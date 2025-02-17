<?php
require '../config/config.php';
require_once('../../TCPDF-main/tcpdf.php');

class CustomPDF extends TCPDF
{
    function Header()
    {
        $this->Image('../../images/logoJBA.png', 5, 7, 50);
        $this->Image('../../images/TDSL.png', 140, 10, 60);
        $this->Ln(10);

        if ($this->PageNo() == 1) {
            $this->SetY(25);
        } else {
            $this->SetY(30);
        }
    }

    function MultiCellRow($data, $widths, $height)
    {
        $nb = 0;
        foreach ($data as $key => $value) {
            $nb = max($nb, $this->getNumLines($value, $widths[$key] ?? 40));
        }

        $h = $height * $nb;
        $this->CustomCheckPageBreak($h);

        foreach ($data as $key => $value) {
            $w = $widths[$key] ?? 40;
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
            $this->SetY(30);
        }
    }
}

$pdf = new CustomPDF();
$pdf->AddPage();
$pdf->SetY(25);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Notas Sustentación', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 8.5);
$widths = [18, 63, 25, 25, 12, 12, 12, 12, 12];
$height = 6;

$headers = ['Periodo', 'Tema', 'Estudiante 1', 'Estudiante 2', 'Nota Doc. Tesis', 'Nota Sust. Est. 1', 'Nota Sust. Est. 2', 'Nota Final Est. 1', 'Nota Final Est. 2'];
$pdf->MultiCellRow($headers, $widths, $height);

$sql_periodo = "SELECT * FROM periodo_academico";
$result_periodo = $conn->query($sql_periodo);

$periodos = [];
while ($row_periodo = $result_periodo->fetch_assoc()) {
    $periodos[] = $row_periodo['periodo'];
}

$periodo_academico = $periodos[0];

$sql = "SELECT 
    t.id, 
    t.tema,
    t.j1_nota_sustentar, 
    t.j2_nota_sustentar, 
    t.j3_nota_sustentar,
    t.j1_nota_sustentar_2, 
    t.j2_nota_sustentar_2, 
    t.j3_nota_sustentar_2, 
    t.nota_revisor_tesis,
    u.nombres AS postulante_nombres, 
    u.apellidos AS postulante_apellidos, 
    p.nombres AS pareja_nombres, 
    p.apellidos AS pareja_apellidos
FROM tema t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
WHERE t.estado_tema = 'Aprobado' 
AND t.estado_registro = 0";

$result = $conn->query($sql);

$pdf->SetFont('helvetica', '', 8.5);

while ($row = $result->fetch_assoc()) {
    $periodo_academico;
    $tema = mb_strtoupper($row['tema']);
    $postulante1 = trim($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']);
    $postulante2 = (!empty($row['pareja_nombres']) && !empty($row['pareja_apellidos']))
        ? trim($row['pareja_nombres'] . ' ' . $row['pareja_apellidos'])
        : 'No aplica';

    // Convertir las notas a valores numéricos
    $nota_documento = (float) ($row['nota_revisor_tesis'] ?? 0);
    $nota_equivalente_documento = ($nota_documento / 10) * 6;

    // Notas sustentación estudiante 1
    $nota1_1 = (float) ($row['j1_nota_sustentar'] ?? 0);
    $nota2_1 = (float) ($row['j2_nota_sustentar'] ?? 0);
    $nota3_1 = (float) ($row['j3_nota_sustentar'] ?? 0);
    
    $promedio_sustentacion_1 = ($nota1_1 + $nota2_1 + $nota3_1) / 3;
    $nota_equivalente_sustentacion_1 = ($promedio_sustentacion_1 / 10) * 4;
    $nota_final_1 = $nota_equivalente_documento + $nota_equivalente_sustentacion_1;

    // Notas sustentación estudiante 2
    $nota1_2 = (float) ($row['j1_nota_sustentar_2'] ?? 0);
    $nota2_2 = (float) ($row['j2_nota_sustentar_2'] ?? 0);
    $nota3_2 = (float) ($row['j3_nota_sustentar_2'] ?? 0);
    
    $promedio_sustentacion_2 = ($nota1_2 + $nota2_2 + $nota3_2) / 3;
    $nota_equivalente_sustentacion_2 = ($promedio_sustentacion_2 / 10) * 4;
    $nota_final_2 = $nota_equivalente_documento + $nota_equivalente_sustentacion_2;

    // Convertir los valores a formato numérico con 2 decimales
    $nota_documento = number_format($nota_documento, 2);
    $promedio_sustentacion_1 = number_format($promedio_sustentacion_1, 2);
    $promedio_sustentacion_2 = number_format($promedio_sustentacion_2, 2);
    $nota_final_1 = number_format($nota_final_1, 2);
    $nota_final_2 = number_format($nota_final_2, 2);

    $pdf->MultiCellRow([
        $periodo_academico,
        $tema,
        $postulante1,
        $postulante2,
        $nota_documento,
        $promedio_sustentacion_1,
        $promedio_sustentacion_2,
        $nota_final_1,
        $nota_final_2
    ], $widths, $height);
}

$pdf->Output();
?>
