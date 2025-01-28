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

  // Consulta para obtener los detalles del tema
  $sql = "SELECT t.*, u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
            p.nombres AS pareja_nombres, p.apellidos AS pareja_apellidos, tut.nombres AS tutor_nombres
            FROM tema t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN usuarios p ON t.pareja_id = p.id
            JOIN tutores tut ON t.tutor_id = tut.id
            WHERE t.id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $tema_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $tema = $result->fetch_assoc();

    // Verificar si el tema de la pareja está aprobado
    $pareja_id = $tema['pareja_id'];
    $sql_pareja_tema = "SELECT estado_tema FROM tema WHERE usuario_id = ? AND estado_tema = 'Aprobado' LIMIT 1";
    $stmt_pareja_tema = $conn->prepare($sql_pareja_tema);
    $stmt_pareja_tema->bind_param("i", $pareja_id);
    $stmt_pareja_tema->execute();
    $result_pareja_tema = $stmt_pareja_tema->get_result();
    $pareja_tema_aprobado = $result_pareja_tema->fetch_assoc();
    $stmt_pareja_tema->close();
  } else {
    echo "No se encontraron detalles para este tema.";
    exit();
  }
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
  <title>Detalle Tema</title>
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
      <h1 class="mb-4 text-center fw-bold">Detalles del Tema</h1>
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

      <!-- Ajustar el ancho del card -->
      <div class="card shadow-lg mx-auto">
        <div class="card-body">

          <!-- Detalles del Tema -->
          <h5 class="card-title text-primary text-center fw-bold">Información del Tema</h5>
          <div class="table-responsive">
            <table class="table">
              <tbody>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-user"></i> Postulante</th>
                  <td><?php echo $tema['postulante_nombres'] . ' ' . $tema['postulante_apellidos']; ?></td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-user"></i> Pareja</th>
                  <td>
                    <?php if ($tema['pareja_nombres']): ?>
                      <?php
                      // Verificar si el tema de la pareja está aprobado
                      if ($pareja_tema_aprobado):
                        echo $tema['pareja_nombres'] . ' ' . $tema['pareja_apellidos'] . ' - Aprobado';
                      else:
                        echo $tema['pareja_nombres'] . ' ' . $tema['pareja_apellidos'];
                      endif;
                      ?>
                    <?php else: ?>
                      Sin pareja
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-book-reader"></i> Tutor</th>
                  <td><?php echo mb_strtoupper($tema['tutor_nombres']); ?></td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-file"></i> Tema</th>
                  <td>
                    <textarea class="form-control no-modificable" readonly rows="2"><?php echo htmlspecialchars($tema['tema']); ?></textarea>
                  </td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo General</th>
                  <td>
                    <textarea class="form-control no-modificable" readonly rows="2"><?php echo htmlspecialchars($tema['objetivo_general']); ?></textarea>
                  </td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo Específico 1</th>
                  <td>
                    <textarea class="form-control no-modificable" readonly rows="2"><?php echo htmlspecialchars($tema['objetivo_especifico_uno']); ?></textarea>
                  </td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo Específico 2</th>
                  <td>
                    <textarea class="form-control no-modificable" readonly rows="2"><?php echo htmlspecialchars($tema['objetivo_especifico_dos']); ?></textarea>
                  </td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo Específico 3</th>
                  <td>
                    <textarea class="form-control no-modificable" readonly rows="2"><?php echo htmlspecialchars($tema['objetivo_especifico_tres']); ?></textarea>
                  </td>
                </tr>
                <tr>
                  <th class="tabla-anchura-th"><i class="bx bx-download"></i> Anteproyecto</th>
                  <td>
                    <?php if (!empty($tema['anteproyecto'])): ?>
                      <a href="../uploads/<?php echo htmlspecialchars($tema['anteproyecto']); ?>" download="<?php echo htmlspecialchars($tema['anteproyecto']); ?>" class="btn btn-link">
                        Descargar Anteproyecto
                      </a>
                    <?php else: ?>
                      <span>No hay un anteproyecto disponible.</span>
                    <?php endif; ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Botones para aprobar, eliminar y devolver -->
          <div class="text-center mt-4 botones-detalle-tema">
            <button type="button" class="btn  me-2" data-bs-toggle="modal" data-bs-target="#modalAprobar">Aprobar</button>
            <button type="button" class="btn devolver-tema me-2" onclick="location.href='editar-tutor.php?id=<?php echo $tema['id']; ?>'">Editar Tutor</button>
            <button type="button" class="btn color-rojo me-2" data-bs-toggle="modal" data-bs-target="#modalEliminar">Rechazar</button>
            <!-- <button type="button" class="btn text-white devolver-tema" data-bs-toggle="modal" data-bs-target="#modalDevolverTema">Devolver Tema</button> -->
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Modales para Aprobar, Eliminar y Devolver -->
  <!-- Modal Aprobar -->
  <div class="modal fade" id="modalAprobar" tabindex="-1" aria-labelledby="modalAprobarLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAprobarLabel">Confirmar Aprobación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que deseas aprobar este tema?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <!-- Formulario para aprobar el tema -->
          <form action="aprobar-tema.php" method="POST">
            <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">
            <button type="submit" class="btn btn-primary">Aprobar</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Eliminar (Rechazar) -->
  <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEliminarLabel">Confirmar rechazo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="eliminar-tema.php" method="POST">
            <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">

            <!-- Motivo de rechazo -->
            <div class="mb-3">
              <label for="motivo_rechazo" class="form-label">Motivo de Rechazo</label>
              <textarea class="form-control" id="motivo_rechazo" name="motivo_rechazo" rows="4" required></textarea>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-danger">Rechazar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para devolver el tema -->
  <div class="modal fade" id="modalDevolverTema" tabindex="-1" aria-labelledby="modalDevolverTemaLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDevolverTemaLabel">Devolver Tema</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="devolver-tema.php" method="POST">
            <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">

            <!-- Motivo de la devolución -->
            <div class="mb-3">
              <label for="motivo" class="form-label">Motivo de la Devolución</label>
              <textarea class="form-control" id="motivo" name="motivo" rows="4" required></textarea>
            </div>

            <!-- Checkboxes para seleccionar destinatarios -->
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="enviarPostulante" name="enviar_postulante" value="1" checked>
              <label class="form-check-label" for="enviarPostulante">
                Enviar al Postulante
              </label>
            </div>
            <?php if (!empty($tema['pareja_nombres'])): ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="enviarPareja"
                  value="1" <?php echo ($pareja_tema_aprobado) ? 'disabled checked' : 'name="enviar_pareja"'; ?>>
                <label class="form-check-label" for="enviarPareja">
                  <?php echo ($pareja_tema_aprobado) ? 'Pareja (Aprobado)' : 'Enviar al Compañero'; ?>
                </label>
              </div>
            <?php else: ?>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="enviarPareja" disabled>
                <label class="form-check-label" for="enviarPareja">
                  Sin Pareja
                </label>
              </div>
            <?php endif; ?>

            <!-- Botones de acción -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Devolver Tema</button>
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
  <script src="../js/toast.js"></script>

</body>

</html>

<?php $conn->close(); ?>