<?php
session_start();
require '../config/config.php'; // Asegúrate de que esta línea está correctamente incluida

// Verificar si el usuario ha iniciado sesión
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

// Consulta para obtener el estado del tema y de la tesis
$sql_tema = "SELECT estado_tema, estado_tesis FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

$estado_tema = $tema['estado_tema'] ?? null;
$estado_tesis = $tema['estado_tesis'] ?? null;
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Requisitos</title>
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
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
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
      <a class="nav-link" href="inicio-postulante.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="perfil.php"><i class='bx bx-user'></i> Perfil</a>
      <a class="nav-link active" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
      <a class="nav-link" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
      <!-- Mostrar Enviar Tema solo si el estado es 'Aprobado' -->
      <?php if ($estado_inscripcion === 'Aprobado'): ?>
        <a class="nav-link" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
      <?php endif; ?>
      <?php if ($estado_tema === 'Aprobado'): ?>
        <a class="nav-link" href="enviar-documento-tesis.php"><i class='bx bx-file'></i> Documento Tesis</a>
      <?php endif; ?>
      <!-- if ($estado_tesis === 'Aprobado'): ?> agregar la etiqueta php antes del if -->
      <?php if ($estado_tema === 'Aprobado'): ?>
        <a class="nav-link" href="estado-plagio.php"><i class='bx bx-file'></i> Antiplagio</a>
        <a class="nav-link" href="sustentacion.php"><i class='bx bx-file'></i> Sustentacion</a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container requisitos py-2">
      <h1 class="mb-4 text-center fw-bold">Requisitos para la Titulación</h1>
      <p class="lead text-center">A continuación te mostramos los documentos y pasos necesarios para comenzar el proceso de titulación:</p>

      <!-- Requisitos en formato de cards -->
      <div class="row justify-content-center">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-id-card bx-lg mb-3'></i>
              <h5 class="card-title">Documento de Identidad</h5>
              <p class="card-text">Debes presentar una copia de tu cédula o pasaporte.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-book bx-lg mb-3'></i>
              <h5 class="card-title">Record Académico</h5>
              <p class="card-text">Una copia actualizada de tu record académico del instituto.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-money bx-lg mb-3'></i>
              <h5 class="card-title">Comprobante de Pago</h5>
              <p class="card-text">Debes adjuntar el comprobante de pago correspondiente a la titulación.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Más Requisitos -->
      <div class="row justify-content-center">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-file bx-lg mb-3'></i>
              <h5 class="card-title">Anteproyecto</h5>
              <p class="card-text">Una copia impresa y digital de tu anteproyecto de titulación.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-message-square-detail bx-lg mb-3'></i>
              <h5 class="card-title">Formato de Inscripción</h5>
              <p class="card-text">Llenar el formulario de inscripción en la página de inscribirse.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-file bx-lg mb-3'></i>
              <h5 class="card-title">Currículum Vitae</h5>
              <p class="card-text">Adjunta tu currículum vitae con el formato Actual color Café de "Encuentra Empleo"</p>
            </div>
          </div>
        </div>
      </div>

      <!--CARD FINAL -->
      <div class="row justify-content-center">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-news bx-lg mb-3'></i>
              <h5 class="card-title">Solicitud de matrícula</h5>
              <p class="card-text">Solicita tu matrícula directamente a través del sistema SIGA Institutos.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-award bx-lg mb-3'></i>
              <h5 class="card-title">Título de Bachiller</h5>
              <p class="card-text">Es obligatorio presentar una copia certificada de tu Título de Bachiller para la inscripción.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body text-center">
              <i class='bx bx-certification bx-lg mb-3'></i>
              <h5 class="card-title">Certificados</h5>
              <p class="card-text">Certificados de Prácticas pre-profesionales, Vinculación y Votación.</p>
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
  <script src="../js/sidebar.js"></script>

</body>

</html>