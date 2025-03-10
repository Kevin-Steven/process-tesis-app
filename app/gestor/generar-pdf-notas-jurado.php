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

// ------------------------------
// 1. INICIALIZAR EL PDF
// ------------------------------
$pdf = new CustomPDF();
$pdf->AddPage();
$pdf->SetY(25);

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Calificación Jurados', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 7.5);

// **Ajuste de columnas**
$widths = [22, 22, 22, 22, 22, 13, 13, 13, 13, 13, 13, 13];
$height = 6;

$headers = ['Postulante 1', 'Postulante 2', 'Jurado 1', 'Jurado 2', 'Jurado 3', 'Nota 1 (P1)', 'Nota 2 (P1)', 'Nota 3 (P1)', 'Nota 1 (P2)', 'Nota 2 (P2)', 'Nota 3 (P2)',];
$pdf->MultiCellRow($headers, $widths, $height);

$sql = "SELECT 
    t.id, 
    t.tema,
    t.j1_nota_sustentar, 
    t.j2_nota_sustentar, 
    t.j3_nota_sustentar, 
    t.j1_nota_sustentar_2, 
    t.j2_nota_sustentar_2, 
    t.j3_nota_sustentar_2, 
    t.estado_tesis,
    u.nombres AS postulante_nombres, 
    u.apellidos AS postulante_apellidos, 
    p.nombres AS pareja_nombres, 
    p.apellidos AS pareja_apellidos,
    j1.nombres AS jurado1_nombre, 
    j2.nombres AS jurado2_nombre, 
    j3.nombres AS jurado3_nombre
FROM tema t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
LEFT JOIN tutores j1 ON t.id_jurado_uno = j1.id
LEFT JOIN tutores j2 ON t.id_jurado_dos = j2.id
LEFT JOIN tutores j3 ON t.id_jurado_tres = j3.id
WHERE t.estado_tesis = 'Aprobado' 
AND t.estado_registro = 0
ORDER BY t.fecha_sustentar ASC, t.hora_sustentar ASC";

$result = $conn->query($sql);

// ------------------------------
// 4. LLENAR EL CONTENIDO DE LA TABLA
// ------------------------------
$pdf->SetFont('helvetica', '', 7);

while ($row = $result->fetch_assoc()) {
    $tema = mb_strtoupper($row['tema']);

    $postulante1 = trim($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']);
    $postulante2 = (!empty($row['pareja_nombres']) && !empty($row['pareja_apellidos'])) 
        ? trim($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']) 
        : 'No aplica';

    $jurado1 = $row['jurado1_nombre'] ? mb_strtoupper($row['jurado1_nombre']) : 'Sin asignar';
    $jurado2 = $row['jurado2_nombre'] ? mb_strtoupper($row['jurado2_nombre']) : 'Sin asignar';
    $jurado3 = $row['jurado3_nombre'] ? mb_strtoupper($row['jurado3_nombre']) : 'Sin asignar';

    // Notas sustentación estudiante 1
    $nota1_1 = (float) ($row['j1_nota_sustentar'] ?? 0);
    $nota2_1 = (float) ($row['j2_nota_sustentar'] ?? 0);
    $nota3_1 = (float) ($row['j3_nota_sustentar'] ?? 0);

    // Notas sustentación estudiante 2
    $nota1_2 = (float) ($row['j1_nota_sustentar_2'] ?? 0);
    $nota2_2 = (float) ($row['j2_nota_sustentar_2'] ?? 0);
    $nota3_2 = (float) ($row['j3_nota_sustentar_2'] ?? 0);

    $pdf->MultiCellRow([
        $tema, $postulante1, $postulante2, $jurado1, $jurado2, $jurado3,
        $nota1_1, $nota2_1, $nota3_1, $nota1_2, $nota2_2, $nota3_2,
    ], $widths, $height);
}
$pdf->Output();
?>
