<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
  header("Location: ../../index.php");
  exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Obtener el ID del tema desde la URL
if (isset($_GET['id'])) {
  $tema_id = intval($_GET['id']);

  // Consulta para obtener los detalles del tema y el tutor actual
  $sql = "SELECT t.tema, tut.nombres AS tutor_nombres, tut.id AS tutor_id 
            FROM tema t
            JOIN tutores tut ON t.tutor_id = tut.id
            WHERE t.id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $tema_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $tema = $result->fetch_assoc();
  } else {
    echo "No se encontraron detalles para este tema.";
    exit();
  }

  // Obtener todos los tutores disponibles
  $sql_tutores = "SELECT id, nombres, estado FROM tutores where estado = 0";
  $result_tutores = $conn->query($sql_tutores);
} else {
  echo "No se especificó ningún ID de tema.";
  exit();
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Tutor</title>
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
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1'></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li><a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php"><i class='bx bx-user me-2'></i>Perfil</a></li>
          <li><a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a></li>
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
      <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
      <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
      <a class="nav-link" href="ver-temas.php"><i class='bx bx-book-open'></i> Temas Postulados</a>
      <a class="nav-link" href="ver-temas-aprobados.php"><i class='bx bx-file'></i> Temas aprobados</a>
      <!-- Módulo Informes con submenú -->
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Informes</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuInformes">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe-tutor.php' ? 'active bg-secondary' : ''; ?>" href="informe-tutor.php">
              <i class="bx bx-file"></i> Informe Tutor
            </a>
          </li>
          <li>
            <a class="nav-link  <?php echo basename($_SERVER['PHP_SELF']) == 'informe-tesis.php' ? 'active bg-secondary' : ''; ?>" href="informe-tesis.php">
              <i class="bx bx-file"></i> Informe Tesis
            </a>
          </li>
          <li>
            <a class="nav-link  <?php echo basename($_SERVER['PHP_SELF']) == 'informe-revisor-tesis.php' ? 'active bg-secondary' : ''; ?>" href="informe-revisor-tesis.php">
              <i class="bx bx-file"></i> Jurado tesis
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link" href="generar-reportes.php"><i class='bx bx-line-chart'></i> Reportes</a>
      <a class="nav-link" href="comunicados.php"><i class='bx bx-message'></i> Comunicados</a>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-2">
      <h1 class="mb-4 text-center fw-bold">Editar Tutor</h1>

      <!-- Formulario para cambiar el tutor -->
      <div class="card shadow-lg mx-auto">
        <div class="card-body">
          <h5 class="card-title text-center fw-bold mb-3">Editar Tutor del Tema: "<?php echo htmlspecialchars($tema['tema']); ?>"</h5>
          <form action="logica-actualizar-tutor.php" class="actualizar-tutor" method="POST">
            <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">

            <!-- Tutor actual -->
            <div class="mb-3">
              <label for="tutor_actual" class="form-label fw-bold">Tutor Actual</label>
              <input type="text" class="form-control" id="tutor_actual" value="<?php echo htmlspecialchars($tema['tutor_nombres']); ?>" disabled>
            </div>

            <!-- Selección de nuevo tutor -->
            <div class="mb-3">
              <label for="tutor_id" class="form-label fw-bold">Nuevo Tutor</label>
              <select class="form-select" id="tutor_id" name="tutor_id" required>
                <option value="">Seleccionar nuevo tutor</option>
                <?php while ($tutor = $result_tutores->fetch_assoc()): ?>
                  <option value="<?php echo $tutor['id']; ?>">
                    <?php echo htmlspecialchars($tutor['nombres']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Botones -->
            <div class="text-center botones-detalle-tema mt-4 d-flex justify-content-center gap-4">
              <button type="button" id="cancelar-btn" class="btn" onclick="history.back()">Cancelar</button>
              <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#modalConfirmarActualizar">
                Actualizar Tutor
              </button>
            </div>

            <!-- Modal de confirmación -->
            <div class="modal fade" id="modalConfirmarActualizar" tabindex="-1" aria-labelledby="modalConfirmarActualizarLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarActualizarLabel">Confirmar Actualización</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    ¿Estás seguro de que deseas actualizar el tutor de este tema?
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                  </div>
                </div>
              </div>
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

<?php $conn->close(); ?>