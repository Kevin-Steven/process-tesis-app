function validarTamanoArchivo() {
    const archivoInput = document.getElementById('documentoCarpeta');
    const archivo = archivoInput.files[0];
    const tamanoMaximo = 5 * 1024 * 1024; // 5 MB en bytes

    if (archivo && archivo.size > tamanoMaximo) {
        //archivoInput.value = ''; // Limpiar el campo de archivo

        const url = new URL(window.location);
        url.searchParams.set('status', 'file_error');
        window.history.replaceState(null, '', url);

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
