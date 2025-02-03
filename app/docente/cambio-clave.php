<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

$mensaje = '';
$tipo_mensaje = 'danger';
if (isset($_SESSION['mensaje'])) {
  $mensaje = $_SESSION['mensaje'];

  if (isset($_SESSION['tipo_mensaje']) && $_SESSION['tipo_mensaje'] == 'success') {
    $tipo_mensaje = 'success';
  }
  unset($_SESSION['mensaje']);
  unset($_SESSION['tipo_mensaje']);
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cambio de Clave</title>
  <link href="../gestor/estilos-gestor.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" href="../../images/favicon.png" type="image/png">
</head>

<body>
  <!-- Topbar con ícono de menú hamburguesa (fuera del menú) -->
  <div class="topbar z-1">
    <div class="menu-toggle">
      <i class='bx bx-menu'></i>
    </div>
    <div class="topbar-right">
      <div class="input-group search-bar">
        <span class="input-group-text" id="search-icon"><i class='bx bx-search'></i></span>
        <input type="text" id="search" class="form-control" placeholder="Search">
      </div>
      <!-- Iconos adicionales a la derecha -->
      <i class='bx bx-envelope'></i>
      <i class='bx bx-bell'></i>
      <!-- Menú desplegable para el usuario -->
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php">
              <i class='bx bx-user me-2'></i>
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php">
              <i class='bx bx-lock me-2'></i>
              Cambio de Clave
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
              <i class='bx bx-log-out me-2'></i>
              Cerrar Sesión
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar z-2" id="sidebar">
    <div class="profile">
      <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
      <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
      <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
    </div>
    <nav class="nav flex-column">
      <a class="nav-link" href="docente-inicio.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-user'></i> Listado Postulantes</a>
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuAnteproyecto" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Anteproyecto</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuAnteproyecto">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-anteproyecto.php' ? 'active bg-secondary' : ''; ?>" href="revisar-anteproyecto.php">
              <i class="bx bx-file"></i> Revisar
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ver-observaciones-anteproyecto.php' ? 'active bg-secondary' : ''; ?>" href="ver-observaciones-anteproyecto.php">
              <i class="bx bx-file"></i> Observaciones
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#RevisarTesis" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Tesis</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="RevisarTesis">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-tesis.php">
              <i class="bx bx-book-reader"></i> Revisar
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ver-observaciones.php' ? 'active bg-secondary' : ''; ?>" href="ver-observaciones.php">
              <i class="bx bx-file"></i> Observaciones
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-correcciones-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-correcciones-tesis.php">
              <i class="bx bx-file"></i> Correcciones
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rubrica-calificacion.php' ? 'active bg-secondary' : ''; ?>" href="rubrica-calificacion.php">
              <i class="bx bx-file"></i> Rubrica Calificación
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'certificados.php' ? 'active bg-secondary' : ''; ?>" href="certificados.php">
              <i class='bx bx-certification'></i> Certificado revisor
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Informes</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuInformes">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe.php' ? 'active bg-secondary' : ''; ?>" href="informe.php">
              <i class="bx bx-file"></i> Informe Tutor
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe-revisor-tesis.php' ? 'active bg-secondary' : ''; ?>" href="informe-revisor-tesis.php">
              <i class="bx bx-file"></i> Informe tesis
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link" href="revisar-plagio.php"><i class='bx bx-certification'></i> Revisar Plagio</a>
      <a class="nav-link" href="revisar-sustentacion.php"><i class='bx bx-file'></i> Revisar Sustentación</a>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <form action="logica-cambio-clave.php" class="formularioCambioClave" method="post">
            <h2 class="title-crd text-center mb-4 fw-bold">Cambio de Contraseña</h2>

            <!-- Mostrar el mensaje si es que existe -->
            <?php if (!empty($mensaje)): ?>
              <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
                <?php echo $mensaje; ?>
              </div>
            <?php endif; ?>

            <!-- Campo para la clave actual -->
            <div class="form-group mb-3">
              <label for="actualPassword">Clave Actual</label>
              <input type="password" class="form-control" id="actualPassword" name="actualPassword" required>
            </div>

            <!-- Campo para la nueva clave -->
            <div class="form-group mb-3">
              <label for="newPassword">Nueva Clave</label>
              <input type="password" class="form-control" id="newPassword" name="newPassword" required>
            </div>

            <!-- Campo para confirmar la nueva clave -->
            <div class="form-group mb-5">
              <label for="confirmPassword">Confirmar Nueva Clave</label>
              <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
            </div>

            <!-- Botón para enviar el formulario -->
            <div class="d-grid gap-2 mt-4 mb-2">
              <button type="submit" class="btn btn-primary">Actualizar Clave</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer mt-auto py-3 bg-light text-center">
    <div class="container">
      <p class="mb-0">&copy; 2024 Gestoria de Titulación Desarrollo de Software - Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/sidebar.js"></script>

</body>

</html>