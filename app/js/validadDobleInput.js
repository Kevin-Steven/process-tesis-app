function validarTamanoArchivo(input) {
    const archivo = input.files[0];
    const tamanoMaximo = 5 * 1024 * 1024; // 5 MB en bytes

    if (archivo && archivo.size > tamanoMaximo) {
        // Limpiar el campo del archivo
        // input.value = ''; 

        // Actualizar el parámetro en la URL para mostrar el toast
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

// Añadir eventos a todos los inputs con la clase 'documentoCarpeta'
document.querySelectorAll('.documentoCarpeta').forEach((input) => {
    input.addEventListener('change', () => validarTamanoArchivo(input));
});
