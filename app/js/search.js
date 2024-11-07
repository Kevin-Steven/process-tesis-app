document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#postulantesTable tbody tr:not(#noResultsRow)');
    let hasVisibleRows = false;

    rows.forEach(row => {
        const cedula = row.cells[1].textContent.toLowerCase();
        const nombres = row.cells[2].textContent.toLowerCase();
        const apellidos = row.cells[3].textContent.toLowerCase();
        const carrera = row.cells[4].textContent.toLowerCase();

        if (cedula.includes(searchValue) || nombres.includes(searchValue) || apellidos.includes(searchValue) || carrera.includes(searchValue)) {
            row.style.display = '';
            hasVisibleRows = true;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('noResultsRow').style.display = hasVisibleRows ? 'none' : '';
});
