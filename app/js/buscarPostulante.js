document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase(); // Convertir el valor de búsqueda a minúsculas
    const rows = document.querySelectorAll('#postulantesList tbody tr'); // Obtener todas las filas de la tabla
    let hasVisibleRows = false; // Controlar si hay filas visibles

    rows.forEach(row => {
        const postulante1 = row.cells[0].textContent.toLowerCase(); // Texto de "Postulante 1"
        const postulante2 = row.cells[1].textContent.toLowerCase(); // Texto de "Postulante 2"

        // Verificar si el valor de búsqueda coincide con alguno de los postulantes
        if (postulante1.includes(searchValue) || postulante2.includes(searchValue)) {
            row.style.display = ''; // Mostrar la fila
            hasVisibleRows = true;
        } else {
            row.style.display = 'none'; // Ocultar la fila
        }
    });

    
    const noResultsRow = document.getElementById('noResultsRow');
    if (noResultsRow) {
        noResultsRow.style.display = hasVisibleRows ? 'none' : '';
    }
});
