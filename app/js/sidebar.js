// sidebar.js

const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');
const footer = document.querySelector('.footer');
const menuToggle = document.querySelector('.menu-toggle');

menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('sidebar-hidden');
    content.classList.toggle('content-full');
    footer.classList.toggle('footer-full');
    menuToggle.classList.toggle('menu-toggle-small');

    // Verificar si el ancho de la ventana es menor o igual a 768px
    if (window.innerWidth <= 768) {
        // Aplicar o quitar el efecto blur al contenido y pie de página solo en móviles
        content.classList.toggle('blurred');
        footer.classList.toggle('blurred');
    }
});
