<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¿Has olvidado la contraseña?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css" rel="stylesheet">

  </head>
  <body>

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
      <div class="card card-recuperar p-4 shadow-lg">
          <h2 class="fw-bold pb-3">Recupera tu cuenta</h2>

          
          <form action="logica-recuperar-clave.php" class="form-recuperar" method="POST">
            <div class="mb-4">
              <label class="form-label pb-2">Introduce tu correo electrónico para realizar la recuperación de tu contraseña.</label>
              <input type="text" class="form-control" name="recuperar-clave" placeholder="Correo electrónico" required>
            </div>

            <!-- Mostrar mensaje de alerta si existe -->
            <?php if (isset($_GET['mensaje']) && isset($_GET['tipo'])): ?>
                <div class="alert alert-<?php echo htmlspecialchars($_GET['tipo']); ?> text-center" role="alert">
                    <?php echo htmlspecialchars($_GET['mensaje']); ?>
                </div>
            <?php endif; ?>

              <div class="d-flex gap-3 justify-content-end">
                  <a href="../../index.php" class="text-white text-decoration-none">
                    <button type="button" class="btn cancelar px-5">Cancelar</button>
                </a>
                  <button type="submit" class="btn px-5">Buscar</button>
              </div>
          </form>
      </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
