<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$usuario_id = $_SESSION['usuario_id'];

if (!$conn) {
  die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Verificar que el usuario es un docente
$sql_docente = "SELECT cedula FROM usuarios WHERE id = ? AND rol = 'docente'";
$stmt_docente = $conn->prepare($sql_docente);
$stmt_docente->bind_param("i", $usuario_id);
$stmt_docente->execute();
$result_docente = $stmt_docente->get_result();
$docente = $result_docente->fetch_assoc();

if ($docente) {
  // Consulta para obtener las correcciones de tesis pendientes
  $sql_temas = "SELECT 
            t.id, 
            t.tema, 
            t.correcciones_tesis, 
            t.estado_tesis,
            u.nombres AS postulante_nombres, 
            u.apellidos AS postulante_apellidos, 
            p.nombres AS pareja_nombres, 
            p.apellidos AS pareja_apellidos
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN usuarios p ON t.pareja_id = p.id
        WHERE 
            t.revisor_tesis_id = ?
            AND t.estado_tema = 'Aprobado'
            AND t.estado_registro = 0
            AND t.correcciones_tesis IS NOT NULL 
            AND t.correcciones_tesis != ''
        ORDER BY t.fecha_subida DESC";

  $stmt_temas = $conn->prepare($sql_temas);
  $stmt_temas->bind_param("i", $usuario_id);
  $stmt_temas->execute();
  $result_temas = $stmt_temas->get_result();
} else {
  echo "No se encontró el docente.";
  exit();
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Revisar Correcciones</title>
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
        <input type="text" id="search" class="form-control" placeholder="Buscar...">
      </div>
      <i class='bx bx-envelope'></i>
      <i class='bx bx-bell'></i>
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php">
              <i class='bx bx-user me-2'></i> Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php">
              <i class='bx bx-lock me-2'></i> Cambio de Clave
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
              <i class='bx bx-log-out me-2'></i> Cerrar Sesión
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
      <a class="nav-link" href="revisar-anteproyecto.php"><i class='bx bx-file'></i> Revisar Anteproyecto</a>
      <a class="nav-link" href="revisar-tesis.php"><i class='bx bx-book-reader'></i> Revisar Tesis</a>
      <a class="nav-link" href="ver-observaciones.php"><i class='bx bx-file'></i> Ver Observaciones</a>
      <a class="nav-link active" href="revisar-correcciones-tesis.php"><i class='bx bx-file'></i> Ver Correcciones</a>
      <a class="nav-link" href="revisar-plagio.php"><i class='bx bx-certification'></i> Revisar Plagio</a>
            <a class="nav-link" href="revisar-sustentacion.php"><i class='bx bx-file'></i> Revisar Sustentación</a>
      <a class="nav-link" href="informe.php"><i class='bx bx-file'></i> Informe</a>
    </nav>
  </div>

  <!-- Contenido -->
  <div class="content" id="content">
    <div class="container mt-3">
      <h1 class="text-center mb-4 fw-bold">Revisar Correcciones de Tesis</h1>

      <!-- Toast -->
      <?php if (isset($_GET['status'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
          <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
              <?php if ($_GET['status'] === 'success'): ?>
                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                <strong class="me-auto">Aprobación Exitosa</strong>
              <?php elseif ($_GET['status'] === 'rejected'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Rechazo Registrado</strong>
              <?php elseif ($_GET['status'] === 'invalid_tutor'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                <strong class="me-auto">Tutor Inválido</strong>
              <?php elseif ($_GET['status'] === 'form_error'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error en el Formulario</strong>
              <?php endif; ?>
              <small>Justo ahora</small>
              <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
              <?php
              switch ($_GET['status']) {
                case 'success':
                  echo "Las correcciones de tesis han sido aprobadas con éxito.";
                  break;
                case 'rejected':
                  echo "Las correcciones de tesis han sido rechazadas.";
                  break;
                case 'error':
                  echo "Ocurrió un error al procesar la solicitud.";
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

      <?php if ($result_temas && $result_temas->num_rows > 0): ?>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead class="table-header-fixed">
              <tr>
                <th>Postulante</th>
                <th>Pareja</th>
                <th>Tema</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result_temas->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                  <td>
                    <?php if (!empty($row['pareja_nombres']) && !empty($row['pareja_apellidos'])): ?>
                      <?php echo htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']); ?>
                    <?php else: ?>
                      No aplica
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($row['tema']); ?></td>
                  <td>
                    <?php
                    if ($row['estado_tesis'] == 'Pendiente') {
                      echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                    } elseif ($row['estado_tesis'] == 'Aprobado') {
                      echo '<span class="badge bg-success">Aprobado</span>';
                    } elseif ($row['estado_tesis'] == 'Rechazado' || $row['estado_tesis'] == 'Correcciones Rechazadas') {
                      echo '<span class="badge bg-danger">Rechazado</span>';
                    } else {
                      echo '<span class="badge bg-secondary">Desconocido</span>';
                    }
                    ?>
                  </td>
                  <td class="text-center">
                    <?php if (!empty($row['correcciones_tesis'])): ?>
                      <a href="detalles-correcciones-tesis.php?id=<?php echo $row['id']; ?>" class="text-decoration-none d-flex align-items-center justify-content-center">
                        <i class='bx bx-search'></i> Ver detalles
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No disponible</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-center">No hay correcciones de tesis para revisar.</p>
      <?php endif; ?>
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
  <script src="../js/toast.js"></script>
</body>

</html>