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
            $this->SetY(30); // Ajusta la posición tras el encabezado en cada nueva página
        }
    }
}

// ------------------------------
// 1. INICIALIZAR EL PDF
// ------------------------------
$pdf = new CustomPDF();
$pdf->AddPage();
$pdf->SetY(25); // Ajuste para la primera página

// Configurar la fuente para el título de la tabla
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Jurado', 0, 1, 'C');
$pdf->Ln(5); // Espacio debajo del título

// ------------------------------
// 2. ENCABEZADOS DE LA TABLA
// ------------------------------
$pdf->SetFont('helvetica', 'B', 12);

// Ajusta el ancho de las 5 columnas según tus necesidades
$widths = [40, 40, 35, 35, 35];
$height = 7;

// Definir los textos de los encabezados
$headers = ['Postulante 1', 'Postulante 2', 'Jurado 1', 'Jurado 2', 'Jurado 3'];
$pdf->MultiCellRow($headers, $widths, $height);

// ------------------------------
// 3. CONSULTA A LA BASE DE DATOS
// ------------------------------
$sql = "SELECT 
    t.id, 
    t.sede, 
    t.aula, 
    t.fecha_sustentar, 
    t.hora_sustentar, 
    t.tema, 
    t.estado_tema, 
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
WHERE t.estado_tema = 'Aprobado' 
AND t.estado_registro = 0";

$result = $conn->query($sql);

// ------------------------------
// 4. LLENAR EL CONTENIDO DE LA TABLA
// ------------------------------
$pdf->SetFont('helvetica', '', 12);

while ($row = $result->fetch_assoc()) {
    // Postulante 1
    $postulante1 = $row['postulante_nombres'] . ' ' . $row['postulante_apellidos'];

    // Postulante 2 (o "No aplica")
    $postulante2 = (!empty($row['pareja_nombres']) && !empty($row['pareja_apellidos'])) 
        ? $row['pareja_nombres'] . ' ' . $row['pareja_apellidos']
        : 'No aplica';

    // Jurado 1
    $jurado1 = $row['jurado1_nombre'] ? mb_strtoupper($row['jurado1_nombre']) : 'Sin asignar';

    // Jurado 2
    $jurado2 = $row['jurado2_nombre'] ? mb_strtoupper($row['jurado2_nombre']) : 'Sin asignar';

    // Jurado 3
    $jurado3 = $row['jurado3_nombre'] ? mb_strtoupper($row['jurado3_nombre']) : 'Sin asignar';

    // Agregar la fila
    $pdf->MultiCellRow([
        $postulante1,
        $postulante2,
        $jurado1,
        $jurado2,
        $jurado3
    ], $widths, $height);
}

// ------------------------------
// 5. SALIDA DEL PDF
// ------------------------------
$pdf->Output();
?>
