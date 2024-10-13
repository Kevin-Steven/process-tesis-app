<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Obtener el ID del usuario
$usuario_id = $_SESSION['usuario_id'];

// Verificar que la conexión a la base de datos ($conn) esté disponible
if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Consulta para obtener el estado de la inscripción
$sql = "SELECT estado_inscripcion FROM documentos_postulante WHERE usuario_id = ? AND estado_registro = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$inscripcion = $result->fetch_assoc();
$stmt->close();

// Verificar el estado de la inscripción
$estado_inscripcion = $inscripcion['estado_inscripcion'] ?? null;
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inicio</title>
  <link href="estilos.css" rel="stylesheet">
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
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span> <!-- Mostramos solo el primer nombre y primer apellido -->
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php">
              <i class='bx bx-user me-2'></i> <!-- Ícono para "Perfil" -->
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambioClave.php">
              <i class='bx bx-lock me-2'></i> <!-- Ícono para "Cambio de Clave" -->
              Cambio de Clave
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
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
      <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5> <!-- Aquí también mostramos solo el primer nombre y primer apellido -->
      <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p> <!-- Mostramos el rol del usuario -->
    </div>
    <nav class="nav flex-column">
      <a class="nav-link active" href="inicio-postulante.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="perfil.php"><i class='bx bx-user'></i> Perfil</a>
      <a class="nav-link" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
      <a class="nav-link" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
      <!-- Mostrar Enviar Tema solo si el estado es 'Aprobado' -->
      <?php if ($estado_inscripcion === 'Aprobado'): ?>
        <a class="nav-link" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container-fluid py-2">
      <div class="row justify-content-center">
        <div class="col-md-8 text-center">
          <h1 class="display-4 mb-4">Bienvenido al Proceso de Titulación</h1>
          <p class="lead mb-5">Este portal está diseñado para guiarte a lo largo del proceso de titulación. Sigue los pasos indicados y completa el proceso de manera rápida y eficiente.</p>
          
          <!-- Card con botón para inscribirse -->
          <div class="card shadow-lg p-4">
            <div class="card-body">
              <h5 class="card-title mb-3">¡Comienza tu proceso de titulación!</h5>
              <p class="card-text mb-4">Para iniciar el proceso de titulación, debes realizar tu inscripción. Asegúrate de tener todos los documentos requeridos. Puedes consultar los documentos necesarios en la página de <a href="requisitos.php" class="text-decoration-none">Requisitos</a>.</p>
              <!-- Botón para inscribirse -->
              <a href="inscripcion.php" class="btn">Inscribirse Ahora</a>
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
