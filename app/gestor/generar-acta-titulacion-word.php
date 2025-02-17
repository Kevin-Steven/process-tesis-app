<?php
require_once '../../PHPWord-master/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
require '../config/config.php';

if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

// Consulta para obtener datos de postulante y pareja
$sql = "SELECT 
        t.tema,
        t.j1_nota_sustentar AS nota_uno,
        t.j2_nota_sustentar AS nota_dos,
        t.j3_nota_sustentar AS nota_tres,
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
    WHERE t.id = $id
";


$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("No se encontró información para este ID.");
}

$row = $result->fetch_assoc();

// -----------------------------------------
// Librerías
// -----------------------------------------
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use ZipArchive;

// -----------------------------------------
// Función para generar todo el texto y formato del acta
// -----------------------------------------
function generarActaCompleta($row, $rutaSalida, $id_formateado)
{

    $phpWord = new PhpWord();

    // Sección con márgenes
    $section = $phpWord->addSection([
        'marginTop'    => 165,   // 0.29 cm
        'marginBottom' => 709,   // 1.25 cm
        'marginLeft'   => 1276,  // 2.25 cm
        'marginRight'  => 1276,  // 2.25 cm
    ]);

    // Agregar encabezado con la imagen
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

    // TITULOS CENTRADOS
    $section->addText(
        'ACTA FINAL DE TITULACIÓN',
        ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
        ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 1.0]
    );

    $section->addText(
        'Nro. ISTJBA-GT-TDS-' . date("Y") . '-' . $id_formateado,
        ['bold' => true, 'size' => 14, 'name' => 'Times New Roman'],
        ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 1.5]
    );

    // Crear un TextRun para aplicar distintos formatos en una sola línea
    $textRun = $section->addTextRun([
        'alignment' => Jc::BOTH,
        'spaceAfter' => 0,   // Eliminar espacio adicional entre líneas
        'lineHeight' => 1.0  // Establecer interlineado de 1.0
    ]);
    // Fragmento: "El Comité Específico..."
    $textRun->addText(
        "El Comité Específico de Revisión y Aprobación de la Carrera: ",
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    $textRun->addText(
        "Tecnología en Superior en Desarrollo de Software del Instituto Superior Tecnológico “Juan Bautista Aguirre”, conforme al proceso de Titulación correspondiente al ",
        ['size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    // "II periodo académico del año 2024" en negrita
    $textRun->addText(
        "II periodo académico del año 2024 del Comité Específico de Revisión y Aprobación de la Carrera,",
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    // Continuación
    $textRun->addText(
        " y en cumplimiento de lo establecido en el Art. 32 del Reglamento de Régimen Académico:",
        ['size' => 11, 'name' => 'Calibri (Cuerpo)']
    );

    $section->addTextBreak(1);

    // Nuevo TextRun (Artículo 32)
    $textRun2 = $section->addTextRun([
        'alignment' => Jc::BOTH,
        'spaceAfter' => 0,
        'lineHeight' => 1.0,
        'indentation' => ['left' => 300, 'right' => 300]
    ]);
    $textRun2->addText(
        "Artículo 32.- ",
        ['bold' => true, 'size' => 10, 'italic' => true, 'name' => 'Calibri (Cuerpo)']
    );
    $textRun2->addText(
        "Diseño, acceso y aprobación de la unidad de integración curricular del tercer nivel. - Cada IES diseñará la unidad de integración curricular, estableciendo su estructura, contenidos y parámetros para el correspondiente desarrollo y evaluación. Para acceder a la unidad de integración curricular, es necesario haber completado las horas y/o créditos mínimos establecidos por la IES, así como cualquier otro requisito establecido en su normativa interna. Su aprobación se realizará a través de las siguientes opciones: a) Desarrollo de un trabajo de integración curricular; o, b) La aprobación de un examen de carácter complexivo, mediante el cual el estudiante deberá demostrar el manejo integral de los conocimientos adquiridos a lo largo de su formación.”",
        ['size' => 10, 'italic' => true, 'name' => 'Calibri (Cuerpo)']
    );

    $section->addTextBreak(1);

    // Nuevo TextRun
    $textRun3 = $section->addTextRun([
        'alignment' => Jc::BOTH,
        'spaceAfter' => 0,
        'lineHeight' => 1.0
    ]);
    $textRun3->addText(
        "El suscrito en calidad de ",
        ['size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    $textRun3->addText(
        "Coordinador Académico de la carrera Tecnología Superior en Desarrollo de Software",
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    $textRun3->addText(
        ", después del análisis de los requisitos legales para el proceso de titulación a través de la modalidad: “Proyecto de Titulación” y por autorización del ",
        ['size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    $textRun3->addText(
        "Comité Específico de Revisión y Aprobación de la Carrera,",
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    $textRun3->addText(
        " ratifica la aprobación del proceso al siguiente estudiante:",
        ['size' => 11, 'name' => 'Calibri (Cuerpo)']
    );

    $section->addTextBreak(1);

    // --------------------------------------------------------------------
    // Tabla de Información del Estudiante
    // --------------------------------------------------------------------
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

    // Se asume que en $row tenemos 'estudiante_nombre' y 'estudiante_apellidos'
    $nombreCompleto = $row['estudiante_nombre'] . ' ' . $row['estudiante_apellidos'];
    $temaMayus = mb_strtoupper($row['tema']);

    $info = [
        ['CARRERA:', 'TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE', 12, 'Calibri (Cuerpo)'],
        ['APELLIDOS Y NOMBRES:', $nombreCompleto, 10, 'Arial'],
        ['CÉDULA:', $row['cedula'], 12, 'Calibri (Cuerpo)'],
        ['TEMA DE PROYECTO:', $temaMayus, 9, 'Arial']
    ];

    foreach ($info as $inf) {
        $table->addRow();
        // Primera columna
        $table->addCell(4000, ['valign' => 'center'])
            ->addText($inf[0], ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'], ['alignment' => Jc::START]);
        // Segunda columna
        $cell = $table->addCell(8000, ['valign' => 'center']);
        $cell->addText($inf[1], ['size' => $inf[2], 'name' => $inf[3]], ['alignment' => Jc::CENTER]);
    }

    // --------------------------------------------------------------------
    // Calcular Notas
    // --------------------------------------------------------------------
    $nota_doc   = floatval($row['nota_doc']);
    $nota_uno   = floatval($row['nota_uno']);
    $nota_dos   = floatval($row['nota_dos']);
    $nota_tres  = floatval($row['nota_tres']);

    $peso_revision = 6.00;
    $peso_sustentacion = 4.00;
    $nota_maxima = 10.00;

    $nota_equivalente_revision = ($nota_doc / $nota_maxima) * $peso_revision;
    $promedio_sustentacion = ($nota_uno + $nota_dos + $nota_tres) / 3;
    $nota_equivalente_sustentacion = ($promedio_sustentacion / $nota_maxima) * $peso_sustentacion;
    $nota_final = $nota_equivalente_revision + $nota_equivalente_sustentacion;

    // Tabla de Calificaciones
    $section->addText('Otorga la siguiente calificación:', ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']);
    $tableNotas = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);

    // Fila Encabezado
    $tableNotas->addRow(200);
    $tableNotas->addCell(6000)->addText(
        'Detalle',
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::CENTER]
    );
    $tableNotas->addCell(1500)->addText(
        'Nota Parcial',
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::CENTER]
    );
    $tableNotas->addCell(1500)->addText(
        'Nota Equivalente',
        ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::CENTER]
    );

    // Fila Revisión
    $tableNotas->addRow();
    $tableNotas->addCell(6000)->addText(
        'REVISIÓN DE DOCUMENTO (60%)',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::START]
    );
    $tableNotas->addCell(1500)->addText(
        number_format($nota_doc, 2) . ' / 10.00',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );
    $tableNotas->addCell(1500)->addText(
        number_format($nota_equivalente_revision, 2) . ' / 6.00',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );

    // Fila Sustentación
    $tableNotas->addRow();
    $tableNotas->addCell(6000)->addText(
        'NOTA DE SUSTENTACIÓN DE PROYECTO (40%)',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::START]
    );
    $tableNotas->addCell(1500)->addText(
        number_format($promedio_sustentacion, 2) . ' / 10.00',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );
    $tableNotas->addCell(1500)->addText(
        number_format($nota_equivalente_sustentacion, 2) . ' / 4.00',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );

    // Fila Nota Final
    $tableNotas->addRow();
    $tableNotas->addCell(8000, ['gridSpan' => 2])->addText(
        'NOTA FINAL TRABAJO DE TITULACIÓN:',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::START]
    );
    $tableNotas->addCell(2000)->addText(
        number_format($nota_final, 2) . ' / 10.00',
        ['bold' => true, 'size' => 12, 'name' => 'Calibri (Cuerpo)'],
        ['alignment' => Jc::END]
    );

    // Firmas
    $section->addTextBreak(1);

    $textRunFirmas = $section->addTextRun(['alignment' => Jc::BOTH]);
    $textRunFirmas->addText(
        "Para constancia firman los que en ella intervinieron en la ciudad de Daule, el ",
        ['size' => 11, 'name' => 'Calibri (Cuerpo)']
    );
    // Fecha actual
    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];
    $dia = date('j');
    $mes = $meses[intval(date('n'))];
    $anio = date('Y');
    $fecha_actual = "$dia de $mes de $anio.";
    $textRunFirmas->addText($fecha_actual, ['bold' => true, 'size' => 11, 'name' => 'Calibri (Cuerpo)']);

    $section->addTextBreak(2);

    $paragraphStyle = [
        'alignment' => Jc::CENTER,
        'lineHeight' => 1.0,
        'spaceAfter' => 0,
        'spaceBefore' => 0
    ];

    $tableFirmas = $section->addTable(['alignment' => Jc::CENTER]);
    $tableFirmas->addRow();
    $tableFirmas->addCell(5000)->addText("_____________________________________", ['size' => 11, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);
    $tableFirmas->addCell(5000)->addText("_____________________________________", ['size' => 11, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);

    // Coordinador - Nombre Estudiante
    $tableFirmas->addRow();
    $tableFirmas->addCell(5000)->addText("Ing. Jonathan Cevallos Guambuguete, Mgs.", ['size' => 11, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);
    // 1) Combinar nombre y apellidos
    $estudianteNombreCompleto = $row['estudiante_nombre'] . " " . $row['estudiante_apellidos'];

    // 2) Convertir a Title Case (la primera letra de cada palabra a mayúscula)
    $estudianteNombreCompleto = mb_convert_case($estudianteNombreCompleto, MB_CASE_TITLE, 'UTF-8');

    // 3) Usar esa variable en el addText
    $tableFirmas->addCell(5000)->addText(
        $estudianteNombreCompleto,
        ['size' => 11, 'name' => 'Calibri (Cuerpo)'],
        $paragraphStyle
    );


    // Cargos
    $tableFirmas->addRow();
    $tableFirmas->addCell(5000)->addText("Coordinador de Carrera", ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);
    $tableFirmas->addCell(5000)->addText("Alumno Egresado", ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);

    // Carrera
    $tableFirmas->addRow();
    $tableFirmas->addCell(5000)->addText("Tecnología Superior en Desarrollo de Software", ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);
    $tableFirmas->addCell(5000)->addText("Tecnología Superior en Desarrollo de Software", ['bold' => true, 'size' => 10, 'name' => 'Calibri (Cuerpo)'], $paragraphStyle);

    // Guardar en archivo
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($rutaSalida);
}

// -----------------------------------------------------------------------------------
// A) Preparamos el array de archivos que van a componer el ZIP
// -----------------------------------------------------------------------------------
$archivos = [];
$directorioTemporal = sys_get_temp_dir();

// 1) Generar el Acta del Postulante Principal
$id_formateado_postulante = sprintf("%03d", $row['postulante_id']);
$nombreArchivoPostulante = "ACTA_DE_TITULACIÓN_ISTJBA_ISTJBA-GT-TDS-" . date("Y") . "-" . $id_formateado_postulante . ".docx";
$rutaPostulante = "$directorioTemporal/$nombreArchivoPostulante";

generarActaCompleta($row, $rutaPostulante, $id_formateado_postulante);
$archivos[] = $rutaPostulante;

// 2) Si existe pareja, generamos su acta
if (!empty($row['pareja_id'])) {
    // Copiamos todo lo que tiene $row
    $rowPareja = $row;

    // Cambiamos nombres, apellidos y cédula al de la pareja
    $rowPareja['estudiante_nombre']    = $row['pareja_nombre'];
    $rowPareja['estudiante_apellidos'] = $row['pareja_apellidos'];
    $rowPareja['cedula']              = $row['pareja_cedula'];
    $rowPareja['postulante_id']       = $row['pareja_id'];

    // CAMBIAR AHORA las notas del jurado a las *segunda* columnas
    $rowPareja['nota_uno']  = $row['j1_nota_sustentar_2'];
    $rowPareja['nota_dos']  = $row['j2_nota_sustentar_2'];
    $rowPareja['nota_tres'] = $row['j3_nota_sustentar_2'];

    // Preparamos ID formateado
    $id_formateado_pareja = sprintf("%03d", $row['pareja_id']);

    // Nombre del archivo para la pareja
    $nombreArchivoPareja = "ACTA_DE_TITULACIÓN_ISTJBA_ISTJBA-GT-TDS-" . date("Y") . "-" . $id_formateado_pareja . ".docx";
    $rutaPareja = "$directorioTemporal/$nombreArchivoPareja";

    // Generar acta para la pareja (usa la MISMA función pero con rowPareja)
    generarActaCompleta($rowPareja, $rutaPareja, $id_formateado_pareja);

    // Añadir al ZIP
    $archivos[] = $rutaPareja;
}

// -----------------------------------------------------------------------------------
// B) Crear el ZIP con los archivos
// -----------------------------------------------------------------------------------
$zipFileName = "ACTAS_TITULACION_{$id}.zip";
$zipFilePath = "$directorioTemporal/$zipFileName";

$zip = new ZipArchive();
if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    foreach ($archivos as $filePath) {
        $zip->addFile($filePath, basename($filePath));
    }
    $zip->close();
} else {
    die("Error al crear el archivo ZIP.");
}

// -----------------------------------------------------------------------------------
// C) Descargar el archivo ZIP
// -----------------------------------------------------------------------------------
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"$zipFileName\"");
readfile($zipFilePath);

// -----------------------------------------------------------------------------------
// D) Limpiar archivos temporales
// -----------------------------------------------------------------------------------
foreach ($archivos as $filePath) {
    unlink($filePath);
}
unlink($zipFilePath);

exit();
