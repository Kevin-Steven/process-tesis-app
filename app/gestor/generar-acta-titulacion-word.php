<?php
require_once '../../PHPWord-master/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
require '../config/config.php';

if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

$sql = "SELECT t.tema, 
               COALESCE(t.nota_revisor_tesis, 0) AS nota_doc, 
               u.cedula, 
               u.nombres AS estudiante_nombre, 
               u.apellidos AS estudiante_apellidos
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.id = $id";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("No se encontró información para este ID.");
}

$row = $result->fetch_assoc();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Section;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\SimpleType\Jc;

$phpWord = new PhpWord();

// Configuración de márgenes en twips
$section = $phpWord->addSection([
    'marginTop'    => 165,   // 0.29 cm
    'marginBottom' => 709,   // 1.25 cm
    'marginLeft'   => 1276,  // 2.25 cm
    'marginRight'  => 1276,  // 2.25 cm
]);

// Agregar encabezado
$header = $section->addHeader();
$header->addImage('../../images/acta-formato-con.png', [
    'width' => 595,
    'height' => 840,
    'positioning' => 'absolute',
    'posHorizontal' => 'absolute',
    'posHorizontalRel' => 'page',
    'posVertical' => 'absolute',
    'posVerticalRel' => 'page',
    'marginTop' => -4,
    'marginLeft' => 0,
]);

// Formatear el ID del tema con 3 dígitos
$id_formateado = sprintf("%03d", $id);

// Títulos Centrados
$section->addText(
    'ACTA FINAL DE TITULACIÓN',
    ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 1.0]
);

$section->addText(
    'Nro. ISTJBA-GT-TDS-' . $id_formateado . '-2024-1P',
    ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
    ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 1.5]
);


// Crear un TextRun para aplicar distintos formatos en una sola línea
$textRun = $section->addTextRun([
    'alignment' => Jc::BOTH,
    'spaceAfter' => 0,   // Eliminar espacio adicional entre líneas
    'lineHeight' => 1.0  // Establecer interlineado de 1.0
]);

// "El Comité Específico de Revisión y Aprobación de la Carrera: " en negrita
$textRun->addText(
    "El Comité Específico de Revisión y Aprobación de la Carrera: ",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// El resto del texto en Calibri (Cuerpo)
$textRun->addText(
    "Tecnología en Superior en Desarrollo de Software del Instituto Tecnológico Superior “Juan Bautista Aguirre”, conforme al proceso de Titulación correspondiente al ",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "I periodo académico del año 2024" en negrita
$textRun->addText(
    "II periodo académico del año 2024",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// Continuación del texto en Calibri (Cuerpo)
$textRun->addText(
    ", aprobado en ",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "Acta: " en negrita
$textRun->addText(
    "Acta: ",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "ISTJBA-GT-TDS-002-2024-1P" en negrita y con Times New Roman
$textRun->addText(
    "ISTJBA-GT-TDS-002-2024-1P",
    ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']
);

// Continuación del texto en Calibri (Cuerpo)
$textRun->addText(
    " de fecha 1 de abril del 2024 y en cumplimiento de lo establecido en el Art. 32 del Reglamento de Régimen Académico:",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

$section->addTextBreak(1);

// Crear un TextRun con alineación justificada y espacio después
$textRun2 = $section->addTextRun([
    'alignment' => Jc::BOTH,
    'spaceAfter' => 0,   // Eliminar espacio adicional entre líneas
    'lineHeight' => 1.0,
    'indentation' => ['left' => 300, 'right' => 300] // Aumenta márgenes en el eje X (izquierda y derecha)
]);

// "Artículo 32.- " en negrita
$textRun2->addText(
    "Artículo 32.- ",
    ['bold' => true, 'size' => 10, 'italic' => true, 'name' => 'Calibri (Cuerpo)']
);

// El resto del texto en Calibri (Cuerpo) y en cursiva
$textRun2->addText(
    "Diseño, acceso y aprobación de la unidad de integración curricular del tercer nivel. - Cada IES diseñará la unidad de integración curricular, estableciendo su estructura, contenidos y parámetros para el correspondiente desarrollo y evaluación. Para acceder a la unidad de integración curricular, es necesario haber completado las horas y/o créditos mínimos establecidos por la IES, así como cualquier otro requisito establecido en su normativa interna. Su aprobación se realizará a través de las siguientes opciones: a) Desarrollo de un trabajo de integración curricular; o, b) La aprobación de un examen de carácter complexivo, mediante el cual el estudiante deberá demostrar el manejo integral de los conocimientos adquiridos a lo largo de su formación.”",
    ['size' => 10, 'italic' => true, 'name' => 'Calibri (Cuerpo)']
);

$section->addTextBreak(1);

// Crear un TextRun con alineación justificada y espacio después
$textRun3 = $section->addTextRun([
    'alignment' => Jc::BOTH,
    'spaceAfter' => 0,   // Eliminar espacio adicional entre líneas
    'lineHeight' => 1.0
]);

// Parte inicial en Calibri
$textRun3->addText(
    "El suscrito en calidad de ",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "Coordinador Académico de la carrera Tecnología Superior en Desarrollo de Software" en negrita
$textRun3->addText(
    "Coordinador Académico de la carrera Tecnología Superior en Desarrollo de Software",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// Continuación en Calibri
$textRun3->addText(
    ", después del análisis de los requisitos legales para el proceso de titulación a través de la modalidad: “Proyecto de Titulación” y por autorización del ",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "Comité Específico de Revisión y Aprobación de la Carrera" en negrita
$textRun3->addText(
    "Comité Específico de Revisión y Aprobación de la Carrera",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// Continuación en Calibri
$textRun3->addText(
    " en reunión realizada el 7 de agosto del 2024, ratifica la aprobación del proceso según nomina que consta en el ",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "Acta Nro." en negrita
$textRun3->addText(
    "Acta Nro.",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "ISTJBA-GT-TDS-089-2024-1P" en negrita y con Times New Roman
$textRun3->addText(
    " ISTJBA-GT-TDS-089-2024-1P",
    ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']
);

// Continuación en Calibri
$textRun3->addText(
    " el cual fue aplicado a:",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

$section->addTextBreak(1);

// Tabla de Información del Estudiante
$table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

$datosEstudiante = $row; // Guardamos la consulta en una variable nueva

$rows = [
    ['CARRERA:', 'TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE', 12, 'Calibri (Cuerpo)'],
    ['APELLIDOS Y NOMBRES:', $datosEstudiante['estudiante_nombre'] . ' ' . $datosEstudiante['estudiante_apellidos'], 10, 'Arial'],
    ['CÉDULA:', $datosEstudiante['cedula'], 12, 'Calibri (Cuerpo)'],
    ['TEMA DE PROYECTO:', strtoupper($datosEstudiante['tema']), 9, 'Arial']
];

foreach ($rows as $row) {
    $table->addRow();
    
    // Primera columna alineada a la izquierda
    $table->addCell(4000, ['valign' => 'center'])->addText(
        $row[0], 
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'], 
        ['alignment' => Jc::START]
    );

    // Segunda columna con tamaño y fuente específicos
    $cell = $table->addCell(8000, ['valign' => 'center']);
    $cell->addText(
        $row[1], 
        ['size' => $row[2], 'name' => $row[3]], 
        ['alignment' => Jc::CENTER]
    );
}


// Tabla de Calificaciones
$section->addText('Otorga la siguiente calificación:', ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']);

// Definir el peso de cada evaluación
$peso_revision = 6.00; // 60% de 10 es 6.00
$nota_maxima = 10.00; // Nota máxima posible

// Obtener la nota parcial y manejar valores NULL o vacíos
$nota_parcial_revision = !empty($datosEstudiante['nota_doc']) ? floatval($datosEstudiante['nota_doc']) : null;

// Calcular Nota Equivalente de Revisión de Documento si hay nota
if ($nota_parcial_revision !== null) {
    $nota_equivalente_revision = ($nota_parcial_revision / $nota_maxima) * $peso_revision;
    $nota_equivalente_revision = number_format($nota_equivalente_revision, 2); // Formatear a 2 decimales
} else {
    $nota_parcial_revision = "N/A";  // Indica que no hay nota
    $nota_equivalente_revision = "N/A";
}

// Crear la tabla
$table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

// **Primera fila: Encabezado con menor altura**
$table->addRow(200);
$table->addCell(6000, ['valign' => 'center'])->addText(
    'Detalle',
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)'],
    ['alignment' => Jc::CENTER]
);
$table->addCell(1500, ['valign' => 'center'])->addText(
    'Nota Parcial',
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)'],
    ['alignment' => Jc::CENTER]
);
$table->addCell(1500, ['valign' => 'center'])->addText(
    'Nota Equivalente',
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)'],
    ['alignment' => Jc::CENTER]
);

// **Datos de calificaciones con tamaño de fuente 12**
$calificaciones = [
    ['REVISIÓN DE DOCUMENTO (60%)', $nota_parcial_revision . ' / 10.00', $nota_equivalente_revision . ' / 6.00'],
    ['NOTA DE SUSTENTACIÓN DE PROYECTO (40%)', '/ 10.00', '/ 4.00']
];

foreach ($calificaciones as $cal) {
    $table->addRow();
    $table->addCell(6000, ['valign' => 'center'])->addText(
        $cal[0],
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::START]
    );
    $table->addCell(1500, ['valign' => 'center'])->addText(
        $cal[1],
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );
    $table->addCell(1500, ['valign' => 'center'])->addText(
        $cal[2],
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );
}


// **Última fila combinando celdas (dos columnas) con tamaño de fuente 12**
$table->addRow();
$table->addCell(8000, ['gridSpan' => 2, 'valign' => 'center'])->addText(
    'NOTA FINAL TRABAJO DE TITULACIÓN:',
    ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
    ['alignment' => Jc::START]
);
$table->addCell(2000, ['valign' => 'center'])->addText(
    '/ 10.00',
    ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
    ['alignment' => Jc::END] // Alineado a la derecha
);



$section->addTextBreak(1);

// Firmas
// Crear un TextRun para aplicar diferentes estilos en la misma línea
$textRun = $section->addTextRun(['alignment' => Jc::BOTH]);

// Parte inicial del texto en Calibri (Cuerpo), tamaño 11
$textRun->addText(
    "Para constancia firman los que en ella intervinieron en la ciudad de Daule, el ",
    ['size' => 11, 'name' => 'Calibri (Cuerpo)']
);

// "6 de agosto de 2024." en negrita
$textRun->addText(
    "6 de agosto de 2024.",
    ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
);

$section->addTextBreak(2);

// Crear una tabla para las firmas con dos columnas
$table = $section->addTable(['alignment' => Jc::CENTER]);

// Definir el estilo de párrafo para interlineado 1.0 y sin espacio después
$paragraphStyle = [
    'alignment' => Jc::CENTER, 
    'lineHeight' => 1.0, 
    'spaceAfter' => 0,
    'spaceBefore' => 0
];

// Primera fila: Línea de firma
$table->addRow();
$nombreEstudiante = isset($datosEstudiante['estudiante_nombre']) ? trim($datosEstudiante['estudiante_nombre']) : 'NOMBRE NO DISPONIBLE';
$apellidoEstudiante = isset($datosEstudiante['estudiante_apellidos']) ? trim($datosEstudiante['estudiante_apellidos']) : 'APELLIDO NO DISPONIBLE';

// Convertir correctamente a formato Title Case sin perder las tildes
$nombreCompleto = ucwords(mb_strtolower("$nombreEstudiante $apellidoEstudiante", 'UTF-8'));

$table->addCell(5000, ['valign' => 'center'])->addText(
    '_____________________________________',
    ['size' => 11, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);
$table->addCell(5000, ['valign' => 'center'])->addText(
    '_____________________________________',
    ['size' => 11, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);

// Segunda fila: Nombre y título
$table->addRow();
$table->addCell(5000, ['valign' => 'center'])->addText(
    'Ing. Jonathan Cevallos Guambuguete, Mgtr.',
    ['size' => 11, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);
$table->addCell(5000, ['valign' => 'center'])->addText(
    $nombreCompleto,
    ['size' => 11, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);

// Tercera fila: Cargo en negrita
$table->addRow();
$table->addCell(5000, ['valign' => 'center'])->addText(
    'Coordinador de Carrera',
    ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);
$table->addCell(5000, ['valign' => 'center'])->addText(
    'Alumno Egresado',
    ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);

// Cuarta fila: Carrera en negrita
$table->addRow();
$table->addCell(5000, ['valign' => 'center'])->addText(
    'Tecnología Superior en Desarrollo de Software',
    ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);
$table->addCell(5000, ['valign' => 'center'])->addText(
    'Tecnología Superior en Desarrollo de Software',
    ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'],
    $paragraphStyle
);

// Definir el nombre del archivo con el ID formateado
$nombreArchivo = "ACTA DE TITULACIÓN ISTJBA - No. GT-TDS-{$id_formateado}-2024-1P.docx";

// Configurar encabezados para la descarga
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");

// Guardar y enviar el documento
flush();
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit();
// Guardar y enviar el documento
header("Content-Disposition: attachment; filename=ACTA_FINAL_TITULACION.docx");
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
flush();
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit();
