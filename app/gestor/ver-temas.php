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

// Consulta para obtener los temas con tutor, postulante y pareja (si existe)
$sql = "SELECT t.id, u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
        p.nombres AS pareja_nombres, p.apellidos AS pareja_apellidos,
        tr.nombres AS tutor_nombres
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN usuarios p ON t.pareja_id = p.id
        JOIN tutores tr ON t.tutor_id = tr.id
        WHERE t.estado_registro = 0 AND t.estado_tema = 'Pendiente'";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ver Temas</title>
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
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
              <i class='bx bx-user me-2'></i>
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
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
      <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
      <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
      <a class="nav-link active" href="ver-temas.php"><i class='bx bx-book-open'></i> Temas Postulados</a>
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
      <h1 class="mb-4 text-center fw-bold">Temas Registrados</h1>

      <!-- Campo de búsqueda -->
      <div class="input-group mb-3">
        <span class="input-group-text"><i class='bx bx-search'></i></span>
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por postulante...">
      </div>

      <div class="table-responsive">
        <table class="table table-striped" id="postulantesList">
          <thead class="table-header-fixed">
            <tr>
              <th>Postulante 1</th>
              <th>Postulante 2</th>
              <th>Tutor</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $row['postulante_nombres'] . ' ' . $row['postulante_apellidos']; ?></td>
                  <td>
                    <?php
                    if (!empty($row['pareja_nombres'])) {
                      echo $row['pareja_nombres'] . ' ' . $row['pareja_apellidos'];
                    } else {
                      echo "No aplica";
                    }
                    ?>
                  </td>
                  <td><?php echo mb_strtoupper($row['tutor_nombres']); ?></td>
                  <td class="text-center">
                    <a href="detalle-tema.php?id=<?php echo $row['id']; ?>" class="text-decoration-none d-flex align-items-center justify-content-center">
                      <i class='bx bx-search'></i> Ver detalles
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center">No se encontraron temas registrados.</td>
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
  <script src="../js/sidebar.js"></script>
  <script src="../js/buscarPostulante.js"></script>

</body>

</html>

<?php $conn->close(); ?>