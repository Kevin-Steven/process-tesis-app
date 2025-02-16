<?php
if (isset($_GET['archivo'])) {
    $archivo = basename($_GET['archivo']); // Seguridad: solo obtener el nombre del archivo
    $ruta_archivo = "../uploads/observaciones-tesis/" . $archivo;

    if (file_exists($ruta_archivo)) {
        $zip = new ZipArchive();
        $zip_nombre = "../uploads/observaciones-tesis/" . pathinfo($archivo, PATHINFO_FILENAME) . ".zip";

        if ($zip->open($zip_nombre, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($ruta_archivo, $archivo);
            $zip->close();

            // Forzar la descarga del ZIP
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zip_nombre) . '"');
            header('Content-Length: ' . filesize($zip_nombre));
            readfile($zip_nombre);

            // Eliminar el ZIP después de la descarga
            unlink($zip_nombre);
            exit;
        } else {
            die("No se pudo crear el archivo ZIP.");
        }
    } else {
        die("El archivo no existe.");
    }
} else {
    die("No se especificó el archivo.");
}
