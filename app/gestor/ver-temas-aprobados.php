<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Consulta para obtener los temas aprobados con estado de registro 0
$sql = "
    SELECT t.id, t.tema, t.estado_tema, t.fecha_subida, t.tutor_id,
           u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
           p.nombres AS pareja_nombres, p.apellidos AS pareja_apellidos, 
           tutores.nombres AS tutor_nombre, p.id AS pareja_id
    FROM tema t
    JOIN usuarios u ON t.usuario_id = u.id
    LEFT JOIN usuarios p ON t.pareja_id = p.id
    JOIN tutores ON t.tutor_id = tutores.id
    WHERE t.estado_tema = 'Aprobado' 
    AND t.estado_registro = 0
    AND (t.pareja_id IS NULL OR t.pareja_id = -1 OR t.usuario_id < t.pareja_id)";

$result = $conn->query($sql);
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Temas Aprobados</title>
  <link href="estilos-gestor.css" rel="stylesheet">
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
        <input type="text" id="search" class="form-control" placeholder="Buscar...">
      </div>
      <i class='bx bx-envelope'></i>
      <i class='bx bx-bell'></i>
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1'></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
              <i class='bx bx-user me-2'></i> Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
              <i class='bx bx-lock me-2'></i> Cambio de Clave
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
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
      <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
      <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
      <a class="nav-link" href="ver-temas.php"><i class='bx bx-book-open'></i> Temas Postulados</a>
      <a class="nav-link active" href="ver-temas-aprobados.php"><i class='bx bx-file'></i> Temas aprobados</a>
      <a class="nav-link" href="generar-reportes.php"><i class='bx bx-line-chart'></i> Generar Reportes</a>
      <a class="nav-link" href="comunicados.php"><i class='bx bx-message'></i> Comunicados</a>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-2">
      <h1 class="mb-4 text-center fw-bold">Temas Aprobados</h1>

      <!-- Toast -->
      <?php if (isset($_GET['status'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
          <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
              <?php if ($_GET['status'] === 'success'): ?>
                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                <strong class="me-auto">Actualización Exitosa</strong>
              <?php elseif ($_GET['status'] === 'not_found'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error</strong>
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
                  echo "El tutor ha sido actualizado correctamente.";
                  break;
                case 'not_found':
                  echo "No se encontró el tema.";
                  break;
                case 'invalid_tutor':
                  echo "El tutor seleccionado no es válido.";
                  break;
                case 'form_error':
                  echo "Hubo un error en el envío del formulario.";
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

      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="table-header-fixed">
            <tr>
              <th>Tema</th>
              <th>Postulante</th>
              <th>Pareja</th>
              <th>Tutor</th> <!-- Columna Tutor agregada -->
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['tema']); ?></td>
                  <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                  <td>
                    <?php if ($row['pareja_nombres']): ?>
                      <?php
                      // Verificar si la pareja está en estado "Rechazado" para mostrar el estado
                      $sql_pareja_estado = "SELECT estado_tema FROM tema WHERE usuario_id = ? LIMIT 1";
                      $stmt_pareja_estado = $conn->prepare($sql_pareja_estado);
                      $stmt_pareja_estado->bind_param("i", $row['pareja_id']);
                      $stmt_pareja_estado->execute();
                      $result_pareja_estado = $stmt_pareja_estado->get_result();
                      $pareja_estado = $result_pareja_estado->fetch_assoc();
                      $stmt_pareja_estado->close();
                      ?>

                      <?php if ($pareja_estado && $pareja_estado['estado_tema'] === 'Rechazado'): ?>
                        <?php echo htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']); ?> (Pendiente)
                      <?php else: ?>
                        <?php echo htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']); ?>
                      <?php endif; ?>
                    <?php else: ?>
                      Sin Pareja
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($row['tutor_nombre']); ?></td> <!-- Tutor mostrado -->
                  <td class="text-center">
                    <a href="editar-tutor-ap.php?id=<?php echo $row['id']; ?>" class="text-decoration-none d-flex align-items-center justify-content-center">
                      <i class='bx bx-search'></i> Ver detalles
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center">No se encontraron temas aprobados.</td> <!-- Colspan ajustado a 5 -->
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
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
  <script src="../js/toast.js" defer></script>
</body>

</html>
