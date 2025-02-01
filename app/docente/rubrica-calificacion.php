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

if (!$docente) {
  echo "No se encontró el docente.";
  exit();
}

$cedula_docente = $docente['cedula'];

$sql_temas = "SELECT 
            t.id, 
            t.tema, 
            t.documento_tesis, 
            t.rubrica_calificacion, 
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
            AND t.documento_tesis IS NOT NULL 
        ORDER BY t.fecha_subida DESC";

$stmt_temas = $conn->prepare($sql_temas);
$stmt_temas->bind_param("i", $usuario_id);
$stmt_temas->execute();
$result_temas = $stmt_temas->get_result();

?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rúbrica de calificación</title>
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
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#RevisarTesis" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Tesis</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse show" id="RevisarTesis">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-tesis.php">
              <i class="bx bx-book-reader"></i> Revisar Tesis
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ver-observaciones.php' ? 'active bg-secondary' : ''; ?>" href="ver-observaciones.php">
              <i class="bx bx-file"></i> Observaciones
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-correcciones-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-correcciones-tesis.php">
              <i class="bx bx-file"></i> Correcciones
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rubrica-calificacion.php' ? 'active bg-secondary' : ''; ?>" href="rubrica-calificacion.php">
              <i class="bx bx-file"></i> Calificación
            </a>
          </li>
        </ul>
      </div>
      
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Informes</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuInformes">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe.php' ? 'active bg-secondary' : ''; ?>" href="informe.php">
              <i class="bx bx-file"></i> Informe Tutor
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe-revisor-tesis.php' ? 'active bg-secondary' : ''; ?>" href="informe-revisor-tesis.php">
              <i class="bx bx-file"></i> Informe tesis
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link" href="revisar-plagio.php"><i class='bx bx-certification'></i> Revisar Plagio</a>
      <a class="nav-link" href="revisar-sustentacion.php"><i class='bx bx-file'></i> Revisar Sustentación</a>
    </nav>
  </div>

  <!-- Contenido -->
  <div class="content" id="content">
    <div class="container mt-3">
      <h1 class="text-center mb-4 fw-bold">Rúbrica de Calificación</h1>

      <!-- Toast -->
      <?php if (isset($_GET['estado'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
          <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
              <?php if ($_GET['estado'] === 'exito'): ?>
                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                <strong class="me-auto">Operación Exitosa</strong>
              <?php elseif ($_GET['estado'] === 'exito_eliminar'): ?>
                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                <strong class="me-auto">Archivo Eliminado</strong>
              <?php elseif ($_GET['estado'] === 'error_conexion'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error de Conexión</strong>
              <?php elseif ($_GET['estado'] === 'error_archivo'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Archivo no Seleccionado</strong>
              <?php elseif ($_GET['estado'] === 'error_tamano_archivo'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Archivo Demasiado Grande</strong>
              <?php elseif ($_GET['estado'] === 'error_tipo_archivo'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Extensión Inválida</strong>
              <?php elseif ($_GET['estado'] === 'error_subida'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error al Subir el Archivo</strong>
              <?php elseif ($_GET['estado'] === 'error_bd'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error en la Base de Datos</strong>
              <?php else: ?>
                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                <strong class="me-auto">Error Desconocido</strong>
              <?php endif; ?>
              <small>Justo ahora</small>
              <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
              <?php
              switch ($_GET['estado']) {
                case 'exito':
                  echo "El archivo se subió correctamente.";
                  break;
                case 'exito_eliminar':
                  echo "El archivo ha sido eliminado exitosamente.";
                  break;
                case 'error_conexion':
                  echo "No se pudo conectar a la base de datos. Por favor, inténtalo más tarde.";
                  break;
                case 'error_archivo':
                  echo "No se seleccionó ningún archivo. Por favor, selecciona un archivo antes de continuar.";
                  break;
                case 'error_tamano_archivo':
                  echo "El archivo que intentas subir es demasiado grande. Asegúrate de que no exceda los 20 MB.";
                  break;
                case 'error_tipo_archivo':
                  echo "La extensión del archivo no es válida. Solo se permiten archivos .zip, .pdf, .doc, .docx.";
                  break;
                case 'error_subida':
                  echo "Ocurrió un error al mover el archivo. Por favor, inténtalo nuevamente.";
                  break;
                case 'error_bd':
                  echo "Ocurrió un error al actualizar la base de datos. Por favor, contacta al administrador.";
                  break;
                default:
                  echo "Ha ocurrido un error desconocido. Por favor, intenta nuevamente.";
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
                <th>Estudiante 1</th>
                <th>Estudiante 2</th>
                <th>Tema</th>
                <th>Calificación</th>
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
                    // Verifica el contenido de correcciones_tesis
                    if (!empty($row['rubrica_calificacion'])):
                    ?>
                      <a class="text-decoration-none d-inline-flex align-items-center" href="../uploads/calificaciones-tesis/<?php echo basename($row['rubrica_calificacion']); ?>" download>
                        Descargar
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No hay documentos</span>
                    <?php endif; ?>
                  </td>
                  </td>

                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                      <!-- Botón Subir Observaciones -->
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirCalificaciones<?php echo $row['id']; ?>">
                        <i class='bx bx-upload'></i>
                      </button>

                      <!-- Botón Editar -->
                      <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $row['id']; ?>">
                        <i class='bx bx-edit-alt'></i>
                      </button>

                      <!-- Botón Eliminar -->
                      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar<?php echo $row['id']; ?>">
                        <i class='bx bx-trash'></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Modal Subir -->
                <div class="modal fade" id="modalSubirCalificaciones<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalSubirObservacionesLabel<?php echo $row['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form action="subir-doc-calificacion.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          <input type="hidden" name="tesis_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="accion" value="subir">

                          <h5 class="modal-title" id="modalEditarLabel">Subir documento</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <div class="mb-3">
                            <label for="archivoEditar<?php echo $row['id']; ?>" class="form-label">Subir Nuevo Archivo</label>
                            <input type="file" class="form-control documentoCarpeta" name="archivo_tesis" accept=".doc,.docx,.pdf,.zip" required onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">Se permiten archivos .zip, .pdf, .doc, .docx con un tamaño máximo de 10 MB.</small>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Subir</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <!-- Modal Editar -->
                <div class="modal fade" id="modalEditar<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form action="subir-doc-calificacion.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          <h5 class="modal-title" id="modalEditarLabel<?php echo $row['id']; ?>">Editar documento</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="tesis_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="accion" value="editar">

                          <div class="mb-3">
                            <label for="archivoEditar<?php echo $row['id']; ?>" class="form-label">Subir Nuevo Archivo</label>
                            <input type="file" class="form-control documentoCarpeta" name="archivo_tesis" accept=".doc,.docx,.pdf,.zip" required onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">Se permiten archivos .zip, .pdf, .doc, .docx con un tamaño máximo de 10 MB.</small>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <!-- Modal Eliminar -->
                <div class="modal fade" id="modalEliminar<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalEliminarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form action="subir-doc-calificacion.php" method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title" id="modalEliminarLabel<?php echo $row['id']; ?>">Eliminar Observaciones</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="tesis_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="accion" value="eliminar">

                          <p>¿Está seguro de que desea eliminar la calificación de este tema?</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-center">No hay documentos de tesis asignadas.</p>
      <?php endif; ?>

    </div>
  </div>

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
        El archivo supera el límite de 10 MB. Por favor, sube un archivo más pequeño.
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
  <script src="../js/validadDobleInput.js" defer></script>
</body>

</html>