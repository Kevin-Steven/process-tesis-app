// form.js

document.addEventListener('DOMContentLoaded', function() {
  const formaProceso = document.getElementById('formaProceso');
  const anteProyecto = document.getElementById('anteProyecto'); 
  const anteproyectoContainer = anteProyecto.parentElement; 

  // Función que muestra u oculta el campo AnteProyecto y cambia el atributo 'required'
  function toggleAnteproyecto() {
      if (formaProceso.value === 'examen') {
          anteproyectoContainer.style.display = 'none';
          anteProyecto.removeAttribute('required'); 
      } else {
          anteproyectoContainer.style.display = 'block';
          anteProyecto.setAttribute('required', 'required'); 
      }
  }

  toggleAnteproyecto();

  formaProceso.addEventListener('change', toggleAnteproyecto);
});
