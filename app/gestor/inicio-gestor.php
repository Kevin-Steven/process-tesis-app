<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio - Gestor</title>
  <link href="estilos-gestor.css" rel="stylesheet">
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
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i> <!-- Ícono agregado -->
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
              <i class='bx bx-user me-2'></i> <!-- Ícono para "Perfil" -->
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
              <i class='bx bx-lock me-2'></i> <!-- Ícono para "Cambio de Clave" -->
              Cambio de Clave
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
              <i class='bx bx-log-out me-2'></i> <!-- Ícono para "Cerrar Sesión" -->
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
      <a class="nav-link active" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
      <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
      <a class="nav-link" href="ver-temas-aprobados.php"><i class='bx bx-file'></i> Temas aprobados</a>
      <a class="nav-link" href="ver-temas.php"><i class='bx bx-book-open'></i> Ver Temas</a>
      <a class="nav-link" href="generar-reportes.php"><i class='bx bx-line-chart'></i> Generar Reportes</a>
      <a class="nav-link" href="comunicados.php"><i class='bx bx-message'></i> Comunicados</a>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container-fluid py-2">
      <div class="row justify-content-center">
        <div class="col-md-8 text-center">
          <h1 class="display-5 fw-bold mb-2">Bienvenido a tu panel de administración</h1>
          <p class="lead mb-4">Desde este panel podrás revisar las inscripciones, verificar la documentación de los postulantes y generar reportes para el proceso de titulación.</p>

          <!-- Cards con acciones rápidas -->
          <div class="row justify-content-center">
            <!-- Card 1: Revisar Documentos -->
            <div class="col-md-4 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-file bx-lg mb-3'></i>
                  <h5 class="card-title">Ver Listado</h5>
                  <p class="card-text">Revisa el listado de postulantes aprobados.</p>
                  <a href="listado-postulantes.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>

            <!-- Card 2: Ver Inscripciones -->
            <div class="col-md-4 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-user bx-lg mb-3'></i>
                  <h5 class="card-title">Ver Inscripciones</h5>
                  <p class="card-text">Revisa el estado de las inscripciones realizadas.</p>
                  <a href="ver-inscripciones.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>

            <!-- Card 3: Generar Reportes -->
            <div class="col-md-4 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-line-chart bx-lg mb-3'></i>
                  <h5 class="card-title">Generar Reportes</h5>
                  <p class="card-text">Crea reportes sobre los procesos de titulación.</p>
                  <a href="generar-reportes.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>

            <!-- Card 4: Asignar revisores -->
            <div class="col-md-12 mb-3">
              <div class="card card-principal h-100">
                <div class="card-body text-center">
                  <i class='bx bx-user-check bx-lg mb-3'></i>
                  <h5 class="card-title">Asignar Revisores</h5>
                  <p class="card-text">Asigna revisores para los anteproyectos y las tesis.</p>
                  <a href="asignar-revisores.php" class="btn">Acceder</a>
                </div>
              </div>
            </div>
          </div>
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
  <script src="../js/sidebar.js" defer></script>

</body>

</html>