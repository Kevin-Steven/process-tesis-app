<?php
session_start();
require '../config/config.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Obtener el ID del usuario
$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener la inscripción del usuario
$sql = "SELECT documento_carpeta, estado_inscripcion FROM documentos_postulante WHERE usuario_id = ? AND estado_registro = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$inscripcion = $result->fetch_assoc();
$stmt->close();

// Verificar el estado de la inscripción
$estado_inscripcion = $inscripcion['estado_inscripcion'] ?? null;

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

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
  <title>Formulario de Inscripción</title>
  <link href="estilos.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="icon" href="../../images/favicon.png" type="image/png">

</head>

<body>

  <!-- Topbar -->
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
      <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5> <!-- Aquí también mostramos solo el primer nombre y primer apellido -->
      <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p> <!-- Mostramos el rol del usuario -->
    </div>
    <nav class="nav flex-column">
      <a class="nav-link" href="inicio-postulante.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="perfil.php"><i class='bx bx-user'></i> Perfil</a>
      <a class="nav-link" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
      <a class="nav-link active" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
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
    <div class="container py-2">
      <!-- Toast para varios estados -->
      <?php if (isset($_GET['status']) || isset($_GET['file_error'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
          <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
              <?php
              if ($_GET['status'] === 'success') {
                echo "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
                echo "<strong class='me-auto'>Documentos enviados</strong>";
              } elseif ($_GET['status'] === 'error' || isset($_GET['file_error'])) {
                echo "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
                echo "<strong class='me-auto'>Error de Tamaño</strong>";
              } elseif ($_GET['status'] === 'invalid_request') {
                echo "<i class='bx bx-error-circle fs-4 me-2 text-danger'></i>";
                echo "<strong class='me-auto'>Error en el Formulario</strong>";
              } elseif ($_GET['status'] === 'deleted') {
                echo "<i class='bx bx-check-circle fs-4 me-2 text-success'></i>";
                echo "<strong class='me-auto'>Inscripción Eliminada</strong>";
              }
              ?>
              <small>Justo ahora</small>
              <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
              <?php
              // Utilizamos `file_error` como un caso más dentro del switch
              $status = $_GET['status'] ?? (isset($_GET['file_error']) ? 'file_error' : null);
              switch ($status) {
                case 'success':
                  echo "Documentos enviados con éxito.";
                  break;
                case 'error':
                  echo "Ha sobrepasado el límite especificado.";
                  break;
                case 'invalid_request':
                  echo "Hubo un error en el envío del formulario.";
                  break;
                case 'file_error':
                  echo "El archivo supera el límite de 5 MB. Por favor, sube un archivo más pequeño.";
                  break;
                case 'deleted':
                  echo "La inscripción ha sido eliminada correctamente.";
                  break;
                default:
                  echo "Ha ocurrido un error desconocido.";
                  break;
              }
              ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Toast para error de tamaño de archivo -->
      <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="fileSizeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header">
            <i class="bx bx-error-circle fs-4 me-2 text-danger"></i>
            <strong class="me-auto">Error de Tamaño</strong>
            <small>Justo ahora</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">
            El archivo supera el límite de 5 MB. Por favor, sube un archivo más pequeño.
          </div>
        </div>
      </div>

      <h1 class="mb-4 text-center fw-bold">Formulario de Inscripción</h1>

      <?php if ($inscripcion && $inscripcion['estado_inscripcion'] === 'Aprobado'): ?>
        <div class="card shadow-lg mb-4">
          <div class="card-header text-center mb-3">
            <h5 class="mb-0 fw-bold">¡Felicidades!</h5>
          </div>
          <div class="card-body text-center">
            <p>Tu inscripción ha sido <strong>aprobada</strong>. ¡Felicidades por completar este importante paso!</p>
            <p>Ahora solo te queda seguir adelante con tu proyecto de tesis. Si tienes alguna duda o necesitas más información, no dudes en contactarnos.</p>
            <p>¡Te deseamos mucho éxito en esta nueva etapa!</p>
          </div>
          <div class="card-footer text-center">
            <a href="enviar-tema.php" class="btn btn-success">Enviar Tema</a>
          </div>
        </div>

      <?php elseif ($inscripcion): ?>
        <div class="card shadow-lg mb-4">
          <div class="card-header text-center mb-3">
            <h5 class="mb-0 fw-bold">Información de la Inscripción</h5>
          </div>
          <div class="card-body text-center">
            <div class="row mb-3">
              <div class="col-12">
                <p><strong>Carpeta de Documentos:</strong> <a href="../uploads/<?php echo $inscripcion['documento_carpeta']; ?>" target="_blank" class="link-primary">Ver Documentos</a></p>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <a href="editar-inscripcion.php" class="btn"><i class='bx bxs-edit'></i> Editar Inscripción</a>
            <!-- Botón para abrir el modal de eliminación de inscripción -->
            <button type="button" class="btn color-rojo" data-bs-toggle="modal" data-bs-target="#modalConfirmarEliminarInscripcion">
              <i class="bx bxs-trash"></i> Eliminar Inscripción
            </button>
          </div>
        </div>

      <?php else: ?>
        <!-- Formulario de nueva inscripción -->
        <div class="card shadow-lg">
          <div class="card-body">
            <h5 class="card-title title-crd text-center mb-4 fw-bold">Sube la carpeta con todos tus documentos en formato ZIP.</h5>
            <form id="inscripcionForm" action="logica-inscripcion.php" class="envio-inscripcion" method="POST" enctype="multipart/form-data">
              <div class="row">
                <!-- Cargar carpeta ZIP/RAR -->
                <div class="col-md-12 mb-12 text-center">
                  <label for="documentoCarpeta" class="form-label">Subir Carpeta de Documentos (ZIP MÁXIMO 5 MB)</label>
                  <input type="file" class="form-control" id="documentoCarpeta" name="documentoCarpeta" accept=".zip" required onchange="validarTamanoArchivo()">
                </div>
              </div>

              <div class="row">
                <div class="col-12 text-center mt-3">
                  <button type="submit" class="btn">Enviar Documentos</button>
                </div>
              </div>

            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal para confirmar eliminación de inscripción -->
  <div class="modal fade" id="modalConfirmarEliminarInscripcion" tabindex="-1" aria-labelledby="modalConfirmarEliminarInscripcionLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmarEliminarInscripcionLabel">Confirmar Eliminación de Inscripción</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que deseas eliminar esta inscripción? Esta acción no se puede deshacer y todos los documentos y datos asociados serán eliminados permanentemente.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <!-- Formulario para eliminar la inscripción -->
          <form action="logica-eliminar-inscripcion.php" method="POST">
            <input type="hidden" name="id_postulante" value="<?php echo $usuario_id; ?>">
            <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
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
  <script src="../js/toast.js" defer></script>
  <script src="../js/validarTamaño.js" defer></script>

</body>

</html>