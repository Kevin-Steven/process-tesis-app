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

// Consulta para obtener los temas y las observaciones de los jurados
$sql_temas = "SELECT 
    t.id, 
    t.tema, 
    t.correcciones_tesis, 
    t.estado_tesis,
    t.sede, t.aula, t.fecha_sustentar, t.hora_sustentar,
    u.nombres AS postulante_nombres, 
    u.apellidos AS postulante_apellidos, 
    p.nombres AS pareja_nombres, 
    p.apellidos AS pareja_apellidos,
    tu1.cedula AS cedula_jurado_1, 
    tu2.cedula AS cedula_jurado_2, 
    tu3.cedula AS cedula_jurado_3,
    t.id_jurado_uno,
    t.id_jurado_dos,
    t.id_jurado_tres
FROM tema t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
LEFT JOIN tutores tu1 ON t.id_jurado_uno = tu1.id
LEFT JOIN tutores tu2 ON t.id_jurado_dos = tu2.id
LEFT JOIN tutores tu3 ON t.id_jurado_tres = tu3.id
WHERE 
    (tu1.cedula = ? OR tu2.cedula = ? OR tu3.cedula = ?) -- Compara cédula del docente con los 3 jurados
    AND t.estado_registro = 0 -- Estado de registro
ORDER BY t.fecha_subida DESC";

// Preparamos la consulta y vinculamos la cédula del docente actual a los 3 jurados
$stmt_temas = $conn->prepare($sql_temas);
$stmt_temas->bind_param("sss", $cedula_docente, $cedula_docente, $cedula_docente);
$stmt_temas->execute();
$result_temas = $stmt_temas->get_result();

?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Revisar sustentación</title>
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
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuAnteproyecto" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Anteproyecto</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuAnteproyecto">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-anteproyecto.php' ? 'active bg-secondary' : ''; ?>" href="revisar-anteproyecto.php">
              <i class="bx bx-file"></i> Revisar
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'obs-realizadas-anteproyecto.php' ? 'active bg-secondary' : ''; ?>" href="obs-realizadas-anteproyecto.php">
              <i class="bx bx-file"></i> Observaciones
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#RevisarTesis" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-file'></i> Tesis</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="RevisarTesis">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-tesis.php">
              <i class="bx bx-book-reader"></i> Revisar
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'obs-realizadas-tesis.php' ? 'active bg-secondary' : ''; ?>" href="obs-realizadas-tesis.php">
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
              <i class="bx bx-file"></i> Rubrica Calificación
            </a>
          </li>
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'certificados.php' ? 'active bg-secondary' : ''; ?>" href="certificados.php">
              <i class='bx bx-certification'></i> Certificado revisor
            </a>
          </li>
        </ul>
      </div>

      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuPlagio" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-certification'></i> Plagio</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse" id="submenuPlagio">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-plagio.php' ? 'active bg-secondary' : ''; ?>" href="revisar-plagio.php">
              <i class="bx bx-file"></i> Revisar
            </a>
          </li>
        </ul>
      </div>
      <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuSustentacion" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
        <span><i class='bx bx-book-open'></i> Sustentación</span>
        <i class="bx bx-chevron-down"></i>
      </a>
      <div class="collapse show" id="submenuSustentacion">
        <ul class="list-unstyled ps-4">
          <li>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-sustentacion.php' ? 'active bg-secondary' : ''; ?>" href="revisar-sustentacion.php">
              <i class="bx bx-file"></i> Revisar
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
    </nav>
  </div>

  <!-- Contenido -->
  <div class="content" id="content">
    <div class="container mt-3">
      <h1 class="text-center mb-4 fw-bold">Revisar Sustentación</h1>

      <!-- Toast -->
      <?php if (isset($_GET['status'])): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
          <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
              <?php if ($_GET['status'] === 'success'): ?>
                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                <strong class="me-auto">Operación Exitosa</strong>
              <?php elseif ($_GET['status'] === 'too_large'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Archivo Demasiado Grande</strong>
              <?php elseif ($_GET['status'] === 'invalid_extension'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Extensión de Archivo Inválida</strong>
              <?php elseif ($_GET['status'] === 'upload_error'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error al Subir el Archivo</strong>
              <?php elseif ($_GET['status'] === 'no_file'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">No se ha Seleccionado Ningún Archivo</strong>
              <?php elseif ($_GET['status'] === 'deleted'): ?>
                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                <strong class="me-auto">Archivo Eliminado</strong>
              <?php elseif ($_GET['status'] === 'file_not_found'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                <strong class="me-auto">Archivo No Encontrado</strong>
              <?php elseif ($_GET['status'] === 'error'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                <strong class="me-auto">Error al Procesar la Solicitud</strong>
              <?php elseif ($_GET['status'] === 'invalid_action'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                <strong class="me-auto">Acción Inválida</strong>
              <?php endif; ?>
              <small>Justo ahora</small>
              <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
              <?php
              switch ($_GET['status']) {
                case 'success':
                  echo "La operación se realizó con éxito.";
                  break;
                case 'too_large':
                  echo "El archivo que intentas subir es demasiado grande. Asegúrate de que no exceda los 5 MB.";
                  break;
                case 'invalid_extension':
                  echo "La extensión del archivo no es válida. Solo se permiten archivos .zip, .pdf, .doc, .docx.";
                  break;
                case 'upload_error':
                  echo "Ocurrió un error al subir el archivo. Inténtalo nuevamente.";
                  break;
                case 'no_file':
                  echo "No se ha seleccionado ningún archivo. Por favor, selecciona un archivo antes de proceder.";
                  break;
                case 'deleted':
                  echo "El archivo ha sido eliminado exitosamente.";
                  break;
                case 'file_not_found':
                  echo "El archivo no se encuentra en el servidor.";
                  break;
                case 'error':
                  echo "Ocurrió un error inesperado. Intenta nuevamente.";
                  break;
                case 'invalid_action':
                  echo "La acción solicitada no es válida.";
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

      <!-- Campo de búsqueda -->
      <div class="input-group mb-3">
        <span class="input-group-text"><i class='bx bx-search'></i></span>
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por tema o postulante">
      </div>

      <?php if ($result_temas && $result_temas->num_rows > 0): ?>

        <div class="table-responsive">
          <table class="table table-striped" id="temas">
            <thead class="table-header-fixed">
              <tr>
                <th>Tema</th>
                <th>Estudiante 1</th>
                <th>Estudiante 2</th>
                <th>Documento Tesis</th>
                <th>Sede</th>
                <th>Aula</th>
                <th>Fecha</th>
                <th>Calificación</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result_temas->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['tema']); ?></td>

                  <td><?php echo $row['postulante_nombres'] . ' ' . $row['postulante_apellidos']; ?></td>
                  <td>
                    <?php
                    if (!empty($row['pareja_nombres']) && !empty($row['pareja_apellidos'])) {
                      echo $row['pareja_nombres'] . ' ' . $row['pareja_apellidos'];
                    } else {
                      echo '<span class="text-muted">No aplica</span>';
                    }
                    ?>
                  </td>

                  <td>
                    <?php
                    if (!empty($row['correcciones_tesis']) && $row['estado_tesis'] == 'Aprobado'):
                    ?>
                      <a class="text-decoration-none d-inline-flex align-items-center" href="../uploads/correcciones/<?php echo urlencode($row['correcciones_tesis']); ?>" download>
                        Descargar Tesis
                      </a>
                    <?php else: ?>
                      <span class="text-muted">No disponible</span>
                    <?php endif; ?>
                  </td>


                  <td><?php echo $row['sede'] ? htmlspecialchars($row['sede']) : '<span class="text-muted">No asignado</span>'; ?></td>
                  <td><?php echo $row['aula'] ? htmlspecialchars($row['aula']) : '<span class="text-muted">No asignado</span>'; ?></td>
                  <td><?php echo $row['fecha_sustentar'] ? htmlspecialchars($row['fecha_sustentar']) : '<span class="text-muted">No asignado</span>'; ?></td>

                  <td>
                    <?php
                    $cedula_docente = $docente['cedula'];
                    $tema_id = $row['id'];

                    $sql_jurados = "SELECT 
                                    tu1.cedula AS cedula_jurado_1,
                                    tu2.cedula AS cedula_jurado_2,
                                    tu3.cedula AS cedula_jurado_3,
                                    t.j1_nota_sustentar,
                                    t.j2_nota_sustentar,
                                    t.j3_nota_sustentar
                                  FROM tema t
                                  LEFT JOIN tutores tu1 ON t.id_jurado_uno = tu1.id
                                  LEFT JOIN tutores tu2 ON t.id_jurado_dos = tu2.id
                                  LEFT JOIN tutores tu3 ON t.id_jurado_tres = tu3.id
                                  WHERE t.id = ?";

                    $stmt_jurados = $conn->prepare($sql_jurados);
                    $stmt_jurados->bind_param("i", $tema_id); // Asumimos que $tema_id es un entero
                    $stmt_jurados->execute();
                    $result_jurados = $stmt_jurados->get_result();

                    // Verificamos si obtenemos datos
                    if ($result_jurados->num_rows > 0) {
                      $row_jurados = $result_jurados->fetch_assoc();

                      // Extraemos las cédulas de los jurados
                      $cedula_jurado_1 = $row_jurados['cedula_jurado_1'];
                      $cedula_jurado_2 = $row_jurados['cedula_jurado_2'];
                      $cedula_jurado_3 = $row_jurados['cedula_jurado_3'];

                      // Comprobamos qué observación mostrar dependiendo de la cédula del docente
                      $nota_jurado = "";
                      if ($cedula_docente == $cedula_jurado_1 && !empty($row_jurados['j1_nota_sustentar'])) {
                        $nota_jurado = $row_jurados['j1_nota_sustentar'];
                      } elseif ($cedula_docente == $cedula_jurado_2 && !empty($row_jurados['j2_nota_sustentar'])) {
                        $nota_jurado = $row_jurados['j2_nota_sustentar'];
                      } elseif ($cedula_docente == $cedula_jurado_3 && !empty($row_jurados['j3_nota_sustentar'])) {
                        $nota_jurado = $row_jurados['j3_nota_sustentar'];
                      }

                      // Si el archivo está especificado y existe, mostrar el enlace de descarga
                      if ($nota_jurado && !empty($nota_jurado)):
                    ?>
                        <?php echo $nota_jurado; ?>
                    <?php
                      else:
                        // Si no hay archivo o no existe, mostramos un mensaje
                        echo '<span class="text-muted">No hay nota</span>';
                      endif;
                    } else {
                      echo '<span class="text-muted">Datos de jurados no encontrados</span>';
                    }
                    ?>
                  </td>

                  <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                      <!-- Botón Subir Observaciones -->
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirObservaciones<?php echo $row['id']; ?>">
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
                <div class="modal fade" id="modalSubirObservaciones<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalSubirObservacionesLabel<?php echo $row['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form action="subir-observaciones-sust.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          <input type="hidden" name="tesis_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="cedula_docente" value="<?php echo $cedula_docente; ?>">
                          <input type="hidden" name="accion" value="subir">

                          <h5 class="modal-title" id="modalEditarLabel">Subir Nota</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <!-- <div class="mb-3">
                            <label for="archivoEditar<?php echo $row['id']; ?>" class="form-label">Subir Nuevo Archivo</label>
                            <input type="file" class="form-control documentoCarpeta" name="archivo_tesis" accept=".doc,.docx,.pdf,.zip" required onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">Se permiten archivos .zip, .pdf, .doc, .docx con un tamaño máximo de 5 MB.</small>
                          </div> -->
                          <div class="mb-3">
                            <label for="nota_sustentar" class="form-label">Subir Nota de sustentación</label>
                            <input type="number" class="form-control" name="nota_sustentar" id="nota_sustentar-<?php echo $row['id']; ?>" required min="0" max="10" step="0.01" placeholder="Ingrese la nota">
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
                      <form action="subir-observaciones-sust.php" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                          
                          <h5 class="modal-title" id="modalEditarLabel<?php echo $row['id']; ?>">Editar Nota</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="tesis_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="cedula_docente" value="<?php echo $cedula_docente; ?>">
                          <input type="hidden" name="accion" value="editar">

                          <!-- <div class="mb-3">
                            <label for="archivoEditar<?php echo $row['id']; ?>" class="form-label">Subir Nuevo Archivo</label>
                            <input type="file" class="form-control documentoCarpeta" name="observaciones-tesis-sust" accept=".doc,.docx,.pdf,.zip" onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">Se permiten archivos .zip, .pdf, .doc, .docx con un tamaño máximo de 5 MB.</small>
                          </div> -->
                          <div class="mb-3">
                            <label for="nota_sustentar" class="form-label">Subir Nueva Nota</label>
                            <input type="number" class="form-control" name="nota_sustentar" required id="nota_sustentar-<?php echo $row['id']; ?>" value="<?php echo htmlspecialchars($nota_jurado); ?>" min="0" max="10" step="0.01" placeholder="Ingrese la nueva nota">
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
                      <form action="subir-observaciones-sust.php" method="POST">
                        <div class="modal-header">
                          <h5 class="modal-title" id="modalEliminarLabel<?php echo $row['id']; ?>">Eliminar Observaciones</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="tesis_id" value="<?php echo $row['id']; ?>">
                          <input type="hidden" name="cedula_docente" value="<?php echo $cedula_docente; ?>">
                          <input type="hidden" name="accion" value="eliminar">

                          <p>¿Está seguro de que desea eliminar la nota tema?</p>
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
        <p class="text-center">No hay sustentaciones de tesis asignadas.</p>
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
        El archivo supera el límite de 5 MB. Por favor, sube un archivo más pequeño.
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
  <script src="../js/buscarTema.js" defer></script>

</body>

</html>