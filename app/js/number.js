// Función para restringir la entrada a solo números
function validateInput(input) {
    input.value = input.value.replace(/[^0-9]/g, ''); // Permitir solo números
}