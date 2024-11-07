document.getElementById('searchInput').addEventListener('keyup', function () {
    const searchValue = this.value.toLowerCase(); 
    const rows = document.querySelectorAll('#temas tbody tr:not(#noResultsRow)'); 
    let hasVisibleRows = false; 

    rows.forEach(row => {
        const tema = row.cells[0].textContent.toLowerCase(); 
        const postulante1 = row.cells[1].textContent.toLowerCase(); 
        const postulante2 = row.cells[2].textContent.toLowerCase(); 
        const tutor = row.cells[3].textContent.toLowerCase(); 

        if (tema.includes(searchValue) || postulante1.includes(searchValue) || postulante2.includes(searchValue) || tutor.includes(searchValue)) {
            row.style.display = ''; // Mostrar la fila
            hasVisibleRows = true;
        } else {
            row.style.display = 'none'; // Ocultar la fila
        }
    });

    const noResultsRow = document.getElementById('noResultsRow');
    noResultsRow.style.display = hasVisibleRows ? 'none' : ''; 
});
