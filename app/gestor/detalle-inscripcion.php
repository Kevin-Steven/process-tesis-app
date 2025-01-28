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

if (isset($_GET['id'])) {
  $postulante_id = $_GET['id'];

  // Obtener el último registro más reciente del postulante
  $sql = "SELECT u.nombres, u.apellidos, u.email, u.carrera, u.whatsapp, d.documento_carpeta, d.estado_inscripcion
            FROM usuarios u
            LEFT JOIN documentos_postulante d ON u.id = d.usuario_id
            WHERE u.id = ?
            ORDER BY d.fecha_subida DESC LIMIT 1";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $postulante_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $postulante = $result->fetch_assoc();
  } else {
    echo "No se encontraron detalles para esta inscripción.";
    exit();
  }
} else {
  echo "No se especificó ningún ID de inscripción.";
  exit();
}
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle Inscripción</title>
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
      <h1 class="mb-4 text-center fw-bold">Detalles de la Inscripción</h1>

      <div class="card shadow-lg">
        <div class="card-body">

          <!-- Documentos Subidos -->
          <h5 class="card-title text-primary">Documentos Subidos</h5>
          <div class="table-responsive">
            <table class="table tabla-descargar">
              <tbody>
                <tr>
                  <th><i class="bx bx-user"></i> Nombres Completos</th>
                  <td><?php echo $postulante['nombres'] . ' ' . $postulante['apellidos']; ?></td>
                </tr>
                <tr>
                  <th><i class="bx bx-envelope"></i> Email</th>
                  <td><?php echo $postulante['email']; ?></td>
                </tr>
                <tr>
                  <th><i class="bx bx-book"></i> Carrera</th>
                  <td><?php echo $postulante['carrera']; ?></td>
                </tr>
                <tr>
                  <th><i class="bx bx-phone"></i> WhatsApp</th>
                  <td><?php echo $postulante['whatsapp']; ?></td>
                </tr>
                <tr>
                  <th><i class="bx bx-file"></i> Documento de Inscripción</th>
                  <td>
                    <a class="text-decoration-none d-inline-flex align-items-center" href="../uploads/<?php echo $postulante['documento_carpeta']; ?>" download>
                      <i class='bx bx-cloud-download'></i> <!-- Espacio entre el ícono y el texto -->
                      Descargar documento
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <hr class="my-4">


          <div class="text-center mt-4 formulario-aceptar-rechazar">

            <button type="button" class="btn aprobar" data-bs-toggle="modal" data-bs-target="#modalConfirmarAprobar">
              Aprobar
            </button>

            <button type="button" class="btn color-rojo" data-bs-toggle="modal" data-bs-target="#modalConfirmarEliminarSolicitud">
              Eliminar Solicitud
            </button>

            <!-- <button type="button" name="devolver" class="btn color-naranja" data-bs-toggle="modal" data-bs-target="#modalDevolver">Devolver documentación</button> -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para devolver documentación -->
  <div class="modal fade" id="modalDevolver" tabindex="-1" aria-labelledby="modalDevolverLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDevolverLabel">Devolver Documentación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formDevolver" method="POST" action="procesar-devolver-documentacion.php">
            <input type="hidden" name="id_postulante" value="<?php echo $postulante_id; ?>">
            <input type="hidden" name="enviar_postulante" value="1">

            <!-- Campo de mensaje -->
            <div class="mb-3">
              <label for="mensaje" class="form-label">Mensaje</label>
              <textarea class="form-control" id="mensaje" name="mensaje" rows="4" required></textarea>
            </div>

            <!-- Botones del modal -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para confirmar aprobación -->
  <div class="modal fade" id="modalConfirmarAprobar" tabindex="-1" aria-labelledby="modalConfirmarAprobarLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmarAprobarLabel">Confirmar Aprobación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que deseas aprobar esta inscripción?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <!-- Formulario para aprobar inscripción -->
          <form action="procesar-inscripcion-postulante.php" method="POST">
            <input type="hidden" name="id_postulante" value="<?php echo $postulante_id; ?>">
            <button type="submit" name="aceptar" class="btn btn-primary">Confirmar Aprobación</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para confirmar eliminación de solicitud -->
  <div class="modal fade" id="modalConfirmarEliminarSolicitud" tabindex="-1" aria-labelledby="modalConfirmarEliminarSolicitudLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalConfirmarEliminarSolicitudLabel">Confirmar Eliminación de Solicitud</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de que deseas eliminar esta solicitud de inscripción? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <!-- Formulario para eliminar la solicitud -->
          <form action="procesar-inscripcion-postulante.php" method="POST">
            <input type="hidden" name="id_postulante" value="<?php echo $postulante_id; ?>">
            <button type="submit" name="denegar" class="btn btn-danger">Confirmar Eliminación</button>
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