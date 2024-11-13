<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

// Obtener el primer nombre, apellido, y foto de perfil
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener el estado del tema y de la tesis
$sql_tema = "SELECT estado_tema, estado_tesis, documento_tesis, observaciones_tesis, pareja_id FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

$estado_tema = $tema['estado_tema'] ?? null;
$estado_tesis = $tema['estado_tesis'] ?? null;
$documento_tesis = $tema['documento_tesis'] ?? null;
$observaciones_tesis = $tema['observaciones_tesis'] ?? 'Sin Observaciones';
$pareja_id = $tema['pareja_id'] ?? null;

// Obtener el nombre de la pareja si existe
$nombre_pareja = '';
if ($pareja_id) {
  $sql_pareja = "SELECT nombres, apellidos FROM usuarios WHERE id = ?";
  $stmt_pareja = $conn->prepare($sql_pareja);
  $stmt_pareja->bind_param("i", $pareja_id);
  $stmt_pareja->execute();
  $result_pareja = $stmt_pareja->get_result();
  $pareja = $result_pareja->fetch_assoc();
  $stmt_pareja->close();
  $nombre_pareja = $pareja ? $pareja['nombres'] . ' ' . $pareja['apellidos'] : 'N/A';
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Enviar Documento Tesis</title>
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
      <i class='bx bx-envelope'></i>
      <i class='bx bx-bell'></i>
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li><a class="dropdown-item d-flex align-items-center" href="perfil.php"><i class='bx bx-user me-2'></i>Perfil</a></li>
          <li><a class="dropdown-item d-flex align-items-center" href="cambioClave.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a></li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li><a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php"><i class='bx bx-log-out me-2'></i>Cerrar Sesión</a></li>
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
      <a class="nav-link" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
      <a class="nav-link" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
      <a class="nav-link" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
      <?php if ($estado_tema === 'Aprobado'): ?>
        <a class="nav-link active" href="enviar-documento-tesis.php"><i class='bx bx-file'></i> Documento Tesis</a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Toast -->
  <?php if (isset($_GET['status'])): ?>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <?php if ($_GET['status'] === 'success'): ?>
            <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
            <strong class="me-auto">Subida Exitosa</strong>
          <?php elseif ($_GET['status'] === 'deleted'): ?>
            <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
            <strong class="me-auto">Documento Eliminado</strong>
          <?php elseif ($_GET['status'] === 'update'): ?>
            <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
            <strong class="me-auto">Documento Actualizado</strong>
          <?php else: ?>
            <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
            <strong class="me-auto">Error</strong>
          <?php endif; ?>
          <small>Justo ahora</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?php
          switch ($_GET['status']) {
            case 'success':
              echo "El documento se ha subido correctamente.";
              break;
            case 'deleted':
              echo "El documento se ha eliminado correctamente.";
              break;
            case 'update':
              echo "El documento se ha actualizado correctamente.";
              break;
            case 'invalid_extension':
              echo "Solo se permiten archivos ZIP.";
              break;
            case 'too_large':
              echo "El archivo supera el tamaño máximo de 2 MB.";
              break;
            case 'upload_error':
              echo "Hubo un error al mover el archivo.";
              break;
            case 'db_error':
              echo "Error al actualizar la base de datos.";
              break;
            case 'no_file':
              echo "No se ha seleccionado ningún archivo.";
              break;
            case 'form_error':
              echo "Error en el envío del formulario.";
              break;
            case 'not_found':
              echo "No se encontraron datos del usuario.";
              break;
            case 'missing_data':
              echo "Faltan datos en el formulario.";
              break;
            default:
              echo "Ocurrió un error desconocido.";
              break;
          }
          ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container py-4">
      <h1 class="mb-4 text-center fw-bold">Enviar Documento de Tesis</h1>

      <?php if (empty($estado_tesis)  || $estado_tesis === 'Eliminado'): ?>
        <!-- Formulario para subir el documento si el estado es NULL o Eliminado -->
        <div class="card shadow-lg">
          <div class="card-body">
            <form action="logica-procesar-documento-tesis.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id_postulante" value="<?php echo $usuario_id; ?>">
              <div class="mb-3">
                <label for="documentoTesis" class="form-label fw-bold">Subir Documento (ZIP MÁXIMO 2 MB)</label>
                <input type="file" class="form-control" id="documentoCarpeta" name="documentoTesis" accept=".zip" required onchange="validarTamanoArchivo()">
                <small class="form-text text-muted">El archivo ZIP debe contener: Documento de Tesis en Word, PDF e Informe de Antiplagio</small>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-primary">Enviar Documento</button>
              </div>
            </form>
          </div>
        </div>
      <?php elseif ($estado_tesis === 'Pendiente'): ?>
        <!-- Tabla con la información del documento enviado si el estado es Pendiente -->
        <h3 class="text-center mt-4 mb-3">Documento de Tesis Enviado</h3>
        <div class="table-responsive">
          <table class="table table-bordered shadow-lg">
            <thead class="table-light text-center">
              <tr>
                <th>Documento Tesis</th>
                <th>Pareja Tesis</th>
                <th>Observaciones</th>
                <th>Enviar Correcciones</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <!-- Columna: Documento Tesis -->
                <td class="text-center">
                  <?php if (!empty($documento_tesis)): ?>
                    <!-- Enlace para ver detalles en lugar de descargar -->
                    <a href="detalles-documento-tesis.php?usuario_id=<?php echo $usuario_id; ?>" class="btn btn-link text-decoration-none">
                      Ver detalles
                    </a>
                  <?php else: ?>
                    No disponible
                  <?php endif; ?>
                </td>

                <!-- Columna: Pareja Tesis -->
                <td class="text-center">
                  <?php if ($nombre_pareja): ?>
                    <?php echo htmlspecialchars($nombre_pareja); ?>
                  <?php else: ?>
                    No aplica
                  <?php endif; ?>
                </td>

                <!-- Columna: Observaciones -->
                <td class="text-center">
                  <?php
                  // Verificar si hay un archivo y si existe en el servidor
                  if (!empty($observaciones_tesis) && file_exists("../uploads/observaciones-tesis/" . $observaciones_tesis)): ?>
                    <a href="../uploads/observaciones-tesis/<?php echo htmlspecialchars($observaciones_tesis); ?>" download class="text-decoration-none">
                      <i class="bx bx-download me-1 text-primary fw-bold"></i> Descargar
                    </a>
                  <?php else: ?>
                    No hay observaciones
                  <?php endif; ?>
                </td>

                <!-- Columna: Enviar Correcciones -->
                <td class="text-center">
                  <?php
                  // Verificar si existen observaciones para habilitar el enlace
                  if (!empty($observaciones_tesis) && file_exists("../uploads/observaciones-tesis/" . $observaciones_tesis)): ?>
                    <a href="enviar-correcciones.php?tesis_id=<?php echo $usuario_id; ?>" class="text-decoration-none">
                      Enviar Correcciones
                    </a>
                  <?php else: ?>
                    <span class="text-muted">No disponible</span>
                  <?php endif; ?>
                </td>

                <!-- Columna: Estado -->
                <td class="text-center">
                  <?php if ($estado_tesis === 'Pendiente'): ?>
                    <span class="badge bg-warning text-dark">Pendiente</span>
                  <?php elseif ($estado_tesis === 'Aprobado'): ?>
                    <span class="badge bg-success">Aprobado</span>
                  <?php elseif ($estado_tesis === 'Rechazado'): ?>
                    <span class="badge bg-danger">Rechazado</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Desconocido</span>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Toast para error de tamaño de archivo -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="fileSizeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
        <strong class="me-auto">Error de Tamaño</strong>
        <small>Justo ahora</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body">
        El archivo supera el límite de 2 MB. Por favor, sube un archivo más pequeño.
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