<?php
require_once '../../PHPWord-master/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Section;
use PhpOffice\PhpWord\Style\Cell;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\SimpleType\Jc;

// Crear el documento Word con los márgenes correctos
$phpWord = new PhpWord();
$section = $phpWord->addSection([
    'marginTop' => 290, // 0.29 cm
    'marginBottom' => 1250, // 1.25 cm
    'marginLeft' => 2250, // 2.25 cm
    'marginRight' => 2250, // 2.25 cm
]);

// Agregar imagen de fondo en el encabezado sin modificar su altura
$header = $section->addHeader();
$header->addImage('../../images/acta-formato-con.png', [
    'width' => 620, 
    'height' => 150, 
    'alignment' => Jc::CENTER,
    'wrappingStyle' => 'behind'
]);

// Encabezado del documento con formato correcto
$section->addText('ACTA FINAL DE TITULACIÓN', ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'], ['alignment' => 'center']);
$section->addText('Nro. ISTJBA-GT-TDS-058-2024-1P', ['size' => 12, 'name' => 'Times New Roman'], ['alignment' => 'center']);
$section->addTextBreak(1);

// Contenido principal con texto justificado y negritas donde corresponda
$texto = "El Comité Específico de Revisión y Aprobación de la Carrera: Tecnología en Superior en Desarrollo de Software del Instituto Tecnológico Superior “Juan Bautista Aguirre”, conforme al proceso de Titulación correspondiente al I periodo académico del año 2024, aprobado en Acta: ISTJBA-GT-TDS-002-2024-1P de fecha 1 de abril del 2024 y en cumplimiento de lo establecido en el Art. 32 del Reglamento de Régimen Académico:";
$section->addText($texto, ['size' => 11, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH]);
$section->addTextBreak(1);

$articulo = "“Artículo 32.- Diseño, acceso y aprobación de la unidad de integración curricular del tercer nivel. - Cada IES diseñará la unidad de integración curricular, estableciendo su estructura, contenidos y parámetros para el correspondiente desarrollo y evaluación. Para acceder a la unidad de integración curricular, es necesario haber completado las horas y/o créditos mínimos establecidos por la IES, así como cualquier otro requisito establecido en su normativa interna. Su aprobación se realizará a través de las siguientes opciones: a) Desarrollo de un trabajo de integración curricular; o, b) La aprobación de un examen de carácter complexivo, mediante el cual el estudiante deberá demostrar el manejo integral de los conocimientos adquiridos a lo largo de su formación.”";
$section->addText($articulo, ['size' => 10, 'italic' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH]);
$section->addTextBreak(1);

// Tabla de información del estudiante con formato correcto
$table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
$table->addRow();
$table->addCell(4000)->addText('CARRERA:', ['bold' => true, 'name' => 'Times New Roman']);
$table->addCell(8000)->addText('TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE');

$table->addRow();
$table->addCell(4000)->addText('APELLIDOS Y NOMBRES:', ['bold' => true, 'name' => 'Times New Roman']);
$table->addCell(8000)->addText('RUIZ NAVARRETE ALEXANDER JAVIER');

$table->addRow();
$table->addCell(4000)->addText('CÉDULA:', ['bold' => true, 'name' => 'Times New Roman']);
$table->addCell(8000)->addText('0957238975');

$table->addRow();
$table->addCell(4000)->addText('TEMA DE PROYECTO:', ['bold' => true, 'name' => 'Times New Roman']);
$table->addCell(8000)->addText('DESARROLLO DE UNA APLICACIÓN WEB PARA MEJORAR LA PRODUCTIVIDAD DE ESTUDIANTES DE LA ESCUELA HUGO SERRANO VALENCIA');
$section->addTextBreak(1);

// Tabla de calificaciones con alineación correcta
$section->addText('Otorga la siguiente calificación:', ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']);
$table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
$table->addRow();
$table->addCell(6000)->addText('Detalle', ['bold' => true, 'name' => 'Times New Roman']);
$table->addCell(3000)->addText('Nota Parcial', ['bold' => true, 'name' => 'Times New Roman']);
$table->addCell(3000)->addText('Nota Equivalente', ['bold' => true, 'name' => 'Times New Roman']);

$table->addRow();
$table->addCell(6000)->addText('REVISIÓN DE DOCUMENTO (60%)');
$table->addCell(3000)->addText('8.80 / 10.00');
$table->addCell(3000)->addText('5.28 / 6.00');

$table->addRow();
$table->addCell(6000)->addText('NOTA DE SUSTENTACIÓN DE PROYECTO (40%)');
$table->addCell(3000)->addText('/ 10.00');
$table->addCell(3000)->addText('/ 4.00');

$table->addRow();
$table->addCell(9000)->addText('NOTA FINAL TRABAJO DE TITULACIÓN:', ['bold' => true]);
$table->addCell(3000)->addText('/ 10.00', ['bold' => true]);

$section->addTextBreak(2);

// Firmas
$section->addText('Para constancia firman los que en ella intervinieron en la ciudad de Daule, el 6 de agosto de 2024.', ['size' => 11, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH]);
$section->addTextBreak(2);

$section->addText('_____________________________________           _____________________________________', ['size' => 11, 'name' => 'Times New Roman'], ['alignment' => 'center']);
$section->addText('Ing. Jonathan Cevallos Guambuguete, Mgtr.         Alexander Javier Ruiz Navarrete', ['size' => 11, 'name' => 'Times New Roman'], ['alignment' => 'center']);
$section->addText('Coordinador de Carrera                                             Alumno Egresado', ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'], ['alignment' => 'center']);

// Guardar el documento y enviarlo
header("Content-Disposition: attachment; filename=ACTA_FINAL_TITULACION.docx");
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
flush();
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit();
