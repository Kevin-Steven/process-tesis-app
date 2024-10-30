function validarTamanoArchivo() {
    const archivoInput = document.getElementById('documentoCarpeta');
    const archivo = archivoInput.files[0];
    const tamanoMaximo = 2 * 1024 * 1024; // 2 MB en bytes

    if (archivo && archivo.size > tamanoMaximo) {
        archivoInput.value = ''; // Limpiar el campo de archivo

        // Cambiar el parámetro de la URL a `status=file_error` sin recargar
        const url = new URL(window.location);
        url.searchParams.set('status', 'file_error');
        window.history.replaceState(null, '', url);

        // Mostrar el toast de error de tamaño de archivo
        mostrarFileSizeToast();
    }
}

function mostrarFileSizeToast() {
    const toastEl = document.getElementById('fileSizeToast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
}
