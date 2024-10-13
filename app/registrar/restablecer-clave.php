<?php
require '../config/config.php';

// Inicializar variable para mostrar mensajes
$mensaje = "";
$tipoAlerta = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar si el token es válido y no ha expirado
    $sql = "SELECT usuario_id, expira FROM recuperacion_clave WHERE token = ? AND expira > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token válido, mostrar formulario para cambiar la contraseña
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nueva_clave = mysqli_real_escape_string($conn, $_POST['nueva_clave-clave']);

            $confirmar_clave = mysqli_real_escape_string($conn, $_POST['confirmar_clave-clave']);

            if ($nueva_clave === $confirmar_clave) {
                // Encriptar la nueva contraseña
                $hash_clave = password_hash($nueva_clave, PASSWORD_BCRYPT);

                // Obtener el ID del usuario
                $usuario = $result->fetch_assoc();
                $usuario_id = $usuario['usuario_id'];

                // Actualizar la contraseña en la base de datos
                $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hash_clave, $usuario_id);
                $stmt->execute();

                // Eliminar el token de la base de datos
                $sql = "DELETE FROM recuperacion_clave WHERE token = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $token);
                $stmt->execute();

                // Redirigir a index.php después de 3 segundos
                header("Refresh: 3; url=../../index.php");

                $mensaje = "Contraseña actualizada correctamente.";
                $tipoAlerta = "success"; // Bootstrap alert-success
            } else {
                $mensaje = "Las contraseñas no coinciden.";
                $tipoAlerta = "danger"; // Bootstrap alert-danger
            }
        }
    } else {
        $mensaje = "El token es inválido o ha expirado.";
        $tipoAlerta = "danger"; // Bootstrap alert-danger
    }
} else {
    $mensaje = "No se proporcionó ningún token.";
    $tipoAlerta = "danger"; // Bootstrap alert-danger
}
?>


<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="estilos.css" rel="stylesheet">

</head>

<body>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-lg" style="width: 100%; max-width: 500px;">
            <h2 class="fw-bold pb-3">Restablecer Contraseña</h2>


            <form method="POST" class="form-restablecer">
                <div class="mb-4">
                    <label class="form-label" for="nueva_clave">Nueva Contraseña</label>
                    <input type="password" class="form-control" name="nueva_clave" placeholder="Contraseña" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="confirmar_clave">Confirmar Contraseña</label>
                    <input type="password" class="form-control" name="confirmar_clave" placeholder="Confirmar Contraseña" required>
                </div>
                <!-- Mostrar mensaje si existe -->
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipoAlerta; ?> text-center" role="alert">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-3 justify-content-center">
                    <button type="submit" class="btn px-5">Restablecer Contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>