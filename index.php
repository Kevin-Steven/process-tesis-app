<?php session_start(); ?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="/app/registrar/estilos.css" rel="stylesheet">
    <link rel="icon" href="/images/favicon.png" type="image/png">
  </head>
  <body>

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-login p-4 shadow-lg">
        <img src="../../images/TDSL.png" alt="Logo-tds" class="imagen-tds">
        <h2 class="text-center mb-4 fw-bold">Iniciar Sesión</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form action="/app/registrar/logica-login.php" class="login" method="POST">
            <div class="p-n4 mb-3">
                <label for="cedula" class="form-label fw-bold">Cédula</label>
                <input type="text" class="form-control" maxlength="10" id="cedula" name="cedula" placeholder="Ingrese su Cédula" required oninput="validateInput(this)">
            </div>
            <div class="mb-3">
                <label for="inputPassword5" class="form-label fw-bold">Contraseña</label>
                <div class="input-group">
                    <input type="password" id="inputPassword5" name="password" class="form-control" placeholder="Ingrese su clave" required>
                </div>
            </div>
            <div class="text-center mb-3">
                <a href="/app/registrar/recuperar-cuenta.php" class="text-decoration-none">¿Has olvidado la contraseña?</a>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn">Iniciar Sesión</button>
            </div>
        </form>

        <!-- <p class="text-center mt-3">
            ¿No tienes una cuenta? <a href="/app/registrar/registro.php" class="text-decoration-none">Regístrate aquí</a>
        </p> -->
    </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="/app/js/number.js" defer></script>
  </body>
</html>
