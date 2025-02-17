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

// Consulta para obtener el estado de la inscripción
$sql = "SELECT estado_inscripcion FROM documentos_postulante WHERE usuario_id = ? AND estado_registro = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$inscripcion = $result->fetch_assoc();
$stmt->close();
$estado_inscripcion = $inscripcion['estado_inscripcion'] ?? null;

// Consulta para obtener los tutores
$sql_tutores = "SELECT id, nombres, estado FROM tutores where estado = 0";
$result_tutores = $conn->query($sql_tutores);

// Consulta para obtener los postulantes disponibles para ser pareja de tesis
$sql_parejas = "SELECT u.id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo 
                FROM usuarios u 
                INNER JOIN documentos_postulante d ON u.id = d.usuario_id 
                WHERE d.estado_inscripcion = 'Aprobado' 
                AND u.pareja_tesis = 0 
                AND u.id != ?";
$stmt_parejas = $conn->prepare($sql_parejas);
$stmt_parejas->bind_param("i", $usuario_id);
$stmt_parejas->execute();
$result_parejas = $stmt_parejas->get_result();

// Consulta para verificar si alguien ha elegido al usuario como pareja
$sql_pareja_seleccionado = "SELECT u.nombres, u.apellidos, u.id AS seleccionador_id 
                            FROM usuarios u 
                            WHERE u.pareja_tesis = ?";
$stmt_pareja_seleccionado = $conn->prepare($sql_pareja_seleccionado);
$stmt_pareja_seleccionado->bind_param("i", $usuario_id);
$stmt_pareja_seleccionado->execute();
$result_pareja_seleccionado = $stmt_pareja_seleccionado->get_result();
$pareja_seleccionado = $result_pareja_seleccionado->fetch_assoc();
$stmt_pareja_seleccionado->close();
$pareja_seleccionado_id = $pareja_seleccionado['seleccionador_id'] ?? null;

// Consulta para obtener el tema aprobado del usuario
$sql_tema_aprobado = "SELECT t.*, 
           CONCAT(u.nombres, ' ', u.apellidos) AS revisor_nombre
    FROM tema t
    LEFT JOIN usuarios u ON t.revisor_anteproyecto_id = u.id
    WHERE t.usuario_id = ? AND t.estado_tema = 'Aprobado' AND t.estado_registro = 0 
    LIMIT 1
";
$stmt_tema_aprobado = $conn->prepare($sql_tema_aprobado);
$stmt_tema_aprobado->bind_param("i", $usuario_id);
$stmt_tema_aprobado->execute();
$result_tema_aprobado = $stmt_tema_aprobado->get_result();
$tema_aprobado = $result_tema_aprobado->fetch_assoc();
$stmt_tema_aprobado->close();

// Consulta para obtener el tutor seleccionado por la pareja si existe
$tutor_seleccionado = null;
if ($pareja_seleccionado_id) {
  $sql_tutor_seleccionado = "SELECT t.tutor_id, tut.nombres AS tutor_nombre 
                               FROM tema t
                               JOIN tutores tut ON t.tutor_id = tut.id
                               WHERE t.usuario_id = ?
                               ORDER BY t.id DESC 
                               LIMIT 1";
  $stmt_tutor_seleccionado = $conn->prepare($sql_tutor_seleccionado);
  $stmt_tutor_seleccionado->bind_param("i", $pareja_seleccionado_id);
  $stmt_tutor_seleccionado->execute();
  $result_tutor_seleccionado = $stmt_tutor_seleccionado->get_result();
  $tutor_seleccionado = $result_tutor_seleccionado->fetch_assoc();
  $stmt_tutor_seleccionado->close();
}

// Verificar si el tema de la pareja ha sido aprobado
$tema_pareja_aprobado = false;
$tema_pareja = null;
if ($pareja_seleccionado_id) {
  $sql_tema_pareja = "SELECT * FROM tema WHERE usuario_id = ? AND estado_tema = 'Aprobado' LIMIT 1";
  $stmt_tema_pareja = $conn->prepare($sql_tema_pareja);
  $stmt_tema_pareja->bind_param("i", $pareja_seleccionado_id);
  $stmt_tema_pareja->execute();
  $result_tema_pareja = $stmt_tema_pareja->get_result();
  $tema_pareja = $result_tema_pareja->fetch_assoc();
  $tema_pareja_aprobado = $result_tema_pareja->num_rows > 0;
  $stmt_tema_pareja->close();
}

// Consulta para obtener el tema más reciente en estado Pendiente o Rechazado
$sql_tema_pendiente = "SELECT * FROM tema 
                       WHERE (usuario_id = ? OR pareja_id = ?) 
                       AND (estado_tema = 'Pendiente' OR estado_tema = 'Rechazado') 
                       ORDER BY id DESC 
                       LIMIT 1";
$stmt_tema_pendiente = $conn->prepare($sql_tema_pendiente);
$stmt_tema_pendiente->bind_param("ii", $usuario_id, $usuario_id);
$stmt_tema_pendiente->execute();
$result_tema_pendiente = $stmt_tema_pendiente->get_result();
$tema_pendiente = $result_tema_pendiente->fetch_assoc();
$stmt_tema_pendiente->close();

// Obtener el estado de 'pareja_tesis' para el usuario actual
$sql_pareja_tesis = "SELECT pareja_tesis FROM usuarios WHERE id = ?";
$stmt_pareja_tesis = $conn->prepare($sql_pareja_tesis);
$stmt_pareja_tesis->bind_param("i", $usuario_id);
$stmt_pareja_tesis->execute();
$result_pareja_tesis = $stmt_pareja_tesis->get_result();
$usuario = $result_pareja_tesis->fetch_assoc();
$estado_pareja_tesis = $usuario['pareja_tesis'] ?? null;
$stmt_pareja_tesis->close();

// Preparar variables para mostrar en HTML
$usuarioEnvio = $tema_pendiente['usuario_id'] ?? null;
$parejaId = $tema_pendiente['pareja_id'] ?? null;
$estadoTema = $tema_pendiente['estado_tema'] ?? null;
$motivo_rechazo = (isset($tema_pendiente) && $tema_pendiente['estado_tema'] === 'Rechazado') ? $tema_pendiente['motivo_rechazo'] : null;

$sql_temass = "SELECT estado_tesis FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_tema = $conn->prepare($sql_temass);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

$estado_tesis = $tema['estado_tesis'] ?? null;

?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Enviar Tema</title>
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
      <?php if ($estado_inscripcion === 'Aprobado'): ?>
        <a class="nav-link active" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
      <?php endif; ?>
      <?php if ($tema_aprobado): ?>
        <a class="nav-link" href="enviar-documento-tesis.php"><i class='bx bx-file'></i> Documento Tesis</a>
      <?php endif; ?>
      <!-- if ($estado_tesis === 'Aprobado'): ?> agregar la etiqueta php antes del if -->
      <?php if ($tema_aprobado): ?>
        <a class="nav-link" href="estado-plagio.php"><i class='bx bx-file'></i> Antiplagio</a>
        <a class="nav-link" href="sustentacion.php"><i class='bx bx-file'></i> Sustentacion</a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container py-2">
      <h1 class="mb-4 text-center fw-bold">Enviar Tema</h1>

      <?php if ($tema_aprobado || $tema_pareja): ?>
        <h3 class="text-center mt-4 mb-3">Estado del Tema de Tesis y Revisión de Anteproyecto</h3>
        <div class="table-responsive">
          <table class="table table-bordered shadow-lg">
            <thead class="table-light text-center">
              <tr>
                <th>Tema</th>
                <th>Pareja</th>
                <th>Revisor</th>
                <th>Observaciones</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <!-- Columna: Tema -->
                <td>
                  <?php if ($tema_pareja): ?>
                    <?php echo htmlspecialchars($tema_pareja['tema']); ?>
                  <?php else: ?>
                    <?php echo htmlspecialchars($tema_aprobado['tema']); ?>
                  <?php endif; ?>
                </td>

                <!-- Columna: Pareja -->
                <td>
                  <?php if ($pareja_seleccionado): ?>
                    <?php echo htmlspecialchars($pareja_seleccionado['nombres'] . ' ' . $pareja_seleccionado['apellidos']); ?>
                  <?php else: ?>
                    No aplica
                  <?php endif; ?>
                </td>

                <td class="text-center">
                  <?php echo isset($tema_aprobado['revisor_nombre']) ? htmlspecialchars($tema_aprobado['revisor_nombre']) : 'No asignado'; ?>
                </td>

                <!-- Columna: Observaciones -->
                <td class="text-center">
                  <?php
                  $observaciones = $tema_pareja ? $tema_pareja['observaciones_anteproyecto'] : $tema_aprobado['observaciones_anteproyecto'];
                  if (!empty($observaciones)): ?>
                    <a href="../uploads/observaciones/<?php echo htmlspecialchars($observaciones); ?>" download class="text-decoration-none">
                      <i class="bx bx-download me-1 text-primary fw-bold"></i> Descargar
                    </a>
                  <?php else: ?>
                    No hay observaciones
                  <?php endif; ?>
                </td>

                <!-- Columna: Estado -->
                <td class="text-center">
                  <span class="badge bg-success">Aprobado</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      <?php elseif ($tema_pendiente && $tema_pendiente['estado_tema'] === 'Pendiente' && $tema_pendiente['estado_registro'] === 0 && $tema_pendiente['usuario_id'] === $usuario_id): ?>
        <div class="card shadow-lg mb-4">
          <div class="card-header text-center mb-3">
            <h5 class="mb-0 fw-bold">Información del Tema</h5>
          </div>
          <div class="card-body text-center">
            <p><strong>Tema:</strong> <?php echo htmlspecialchars($tema_pendiente['tema']); ?></p>
            <p><strong>Pareja:</strong>
              <?php
              if ($tema_pendiente['pareja_id'] == -1) {
                echo "No aplica";
              } else {
                $sql_pareja = "SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM usuarios WHERE id = ?";
                $stmt_pareja = $conn->prepare($sql_pareja);
                $stmt_pareja->bind_param("i", $tema_pendiente['pareja_id']);
                $stmt_pareja->execute();
                $result_pareja = $stmt_pareja->get_result();
                $pareja = $result_pareja->fetch_assoc();
                echo htmlspecialchars($pareja['nombre_completo']);
              }
              ?>
            </p>
          </div>
          <div class="card-footer text-center">
            <a href="editar-tema.php?id=<?php echo $tema_pendiente['id']; ?>" class="btn"><i class='bx bxs-edit'></i> Editar Tema</a>
            <button type="button" class="btn color-rojo" data-bs-toggle="modal" data-bs-target="#modalConfirmarEliminarTema">
              <i class="bx bxs-trash"></i> Eliminar Tema
            </button>
          </div>
        </div>

        <!-- Modal para confirmar eliminación del tema -->
        <div class="modal fade" id="modalConfirmarEliminarTema" tabindex="-1" aria-labelledby="modalLabelEliminarTema" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalLabelEliminarTema">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                ¿Estás seguro de que quieres eliminar el tema?
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="logica-eliminar-tema.php" method="POST">
                  <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">
                  <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- Mostrar formulario para enviar tema si no hay tema pendiente o fue rechazado -->
        <?php
        // Verifica si el tema está rechazado, existe un motivo de rechazo y cumple con las condiciones de pareja o trabajo individual
        if ($estadoTema === 'Rechazado' && !empty($motivo_rechazo) && (($estado_pareja_tesis != 0 && $estado_pareja_tesis != -1) || ($estado_pareja_tesis === 0 || $estado_pareja_tesis === -1))): ?>
          <div id="card-rechazo" class="card shadow-lg mb-4 border-0">
            <div class="card-header bg-light text-center mb-3 py-3">
              <h3 class="mb-0 fw-bold text-danger">Lo sentimos, tu tema ha sido rechazado</h3>
            </div>
            <div class="card-body text-center">
              <p class="fs-5 mb-3">
                <i class="bx bxs-x-circle me-1 text-danger"></i>
                <strong>
                  <?php if ($tema_pendiente): ?>
                    El tema de tesis "<?php echo htmlspecialchars($tema_pendiente['tema']); ?>" ha sido rechazado.
                  <?php endif; ?>
                </strong>
              </p>

              <!-- Enlace para ver el motivo del rechazo -->
              <p class="mb-3">
                <a href="#" class="text-danger fw-bold" data-bs-toggle="modal" data-bs-target="#modalMotivoRechazo">
                  <i class="bx bx-info-circle"></i> Ver motivo de rechazo
                </a>
              </p>

              <div class="enviar-tema">
                <button class="btn" onclick="mostrarFormulario()">Enviar un nuevo tema</button>
              </div>
            </div>
          </div>

        <?php endif; ?>

        <div id="formulario-enviar-tema" class="card shadow-lg ">
          <div class="card-body">
            <form action="logica-procesar-tema.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
              <div class="row">
                <!-- Primera columna -->
                <div class="col-md-6">
                  <!-- Campo Tema -->
                  <div class="mb-3">
                    <label for="tema" class="form-label fw-bold">Tema</label>
                    <input type="text" class="form-control" id="tema" name="tema" required>
                  </div>

                  <!-- Objetivo General -->
                  <div class="mb-3">
                    <label for="objetivo_general" class="form-label fw-bold">Objetivo General</label>
                    <textarea class="form-control no-modificable" id="objetivo_general" name="objetivo_general" required></textarea>
                  </div>

                  <!-- Objetivo Específico 1 -->
                  <div class="mb-3">
                    <label for="objetivo_especifico_uno" class="form-label fw-bold">Objetivo Específico 1</label>
                    <textarea class="form-control no-modificable" id="objetivo_especifico_uno" name="objetivo_especifico_uno" required></textarea>
                  </div>

                  <!-- Objetivo Específico 2 -->
                  <div class="mb-3">
                    <label for="objetivo_especifico_dos" class="form-label fw-bold">Objetivo Específico 2</label>
                    <textarea class="form-control no-modificable" id="objetivo_especifico_dos" name="objetivo_especifico_dos" required></textarea>
                  </div>
                </div>

                <!-- Segunda columna -->
                <div class="col-md-6">
                  <!-- Objetivo Específico 3 -->
                  <div class="mb-2">
                    <label for="objetivo_especifico_tres" class="form-label fw-bold">Objetivo Específico 3</label>
                    <textarea class="form-control no-modificable" id="objetivo_especifico_tres" name="objetivo_especifico_tres" required></textarea>
                  </div>

                  <div class="mb-3">
                    <label for="tutor" class="form-label fw-bold">Tutor</label>
                    <select class="form-select" id="tutor_id" name="tutor_id"
                      <?php echo ($tutor_seleccionado) ? 'disabled' : ''; ?> required>
                      <option value="">Seleccione un tutor</option>
                      <?php if ($tutor_seleccionado): ?>
                        <!-- Si ya hay un tutor seleccionado, mostrarlo como opción seleccionada -->
                        <option value="<?php echo $tutor_seleccionado['tutor_id']; ?>" selected>
                          <?php echo $tutor_seleccionado['tutor_nombre']; ?>
                        </option>
                      <?php endif; ?>
                      <?php while ($row = $result_tutores->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                          <?php echo $row['nombres']; ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                    <!-- Campo hidden para enviar el valor del tutor si el select está deshabilitado -->
                    <?php if ($tutor_seleccionado): ?>
                      <input type="hidden" name="tutor_id" value="<?php echo $tutor_seleccionado['tutor_id']; ?>">
                    <?php endif; ?>

                    <!-- Mostrar el nombre del tutor seleccionado si aplica -->
                    <?php if ($tutor_seleccionado): ?>
                      <p class="fst-italic mt-1">El tutor seleccionado por tu pareja es "<?php echo $tutor_seleccionado['tutor_nombre']; ?>"</p>
                    <?php endif; ?>
                  </div>

                  <div class="mb-3">
                    <label for="pareja_id" class="form-label fw-bold">Pareja de Tesis</label>
                    <select class="form-select" id="pareja_id" name="pareja_id"
                      <?php echo ($pareja_seleccionado) ? 'disabled' : ''; ?> required>
                      <option value="">Seleccione un compañero</option>
                      <option value="-1">Sin Pareja</option> <!-- Nueva opción añadida -->
                      <?php if ($pareja_seleccionado && $pareja_seleccionado_id): ?>
                        <!-- Si ya hay una pareja seleccionada, mostrarla como opción seleccionada -->
                        <option value="<?php echo $pareja_seleccionado_id; ?>" selected>
                          <?php echo $pareja_seleccionado['nombres'] . ' ' . $pareja_seleccionado['apellidos']; ?>
                        </option>
                      <?php else: ?>
                        <?php while ($row = $result_parejas->fetch_assoc()): ?>
                          <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre_completo']; ?></option>
                        <?php endwhile; ?>
                      <?php endif; ?>
                    </select>
                    <!-- Campo hidden para enviar el valor de la pareja si el select está deshabilitado -->
                    <?php if ($pareja_seleccionado && $pareja_seleccionado_id): ?>
                      <input type="hidden" name="pareja_id" value="<?php echo $pareja_seleccionado_id; ?>">
                    <?php endif; ?>

                    <!-- Mostrar el mensaje si alguien te ha seleccionado como pareja -->
                    <?php if ($pareja_seleccionado && $pareja_seleccionado_id): ?>
                      <p class="fst-italic mt-1">El postulante "<?php echo $pareja_seleccionado['nombres'] . ' ' . $pareja_seleccionado['apellidos']; ?>" te ha elegido como pareja.</p>
                    <?php endif; ?>
                  </div>

                  <!-- Subir Anteproyecto -->
                  <div class="mb-3">
                    <label for="anteproyecto" class="form-label fw-bold">Subir Anteproyecto (ZIP MÁXIMO 5 MB)</label>
                    <input type="file" class="form-control" id="documentoCarpeta" name="anteproyecto" accept=".zip" required onchange="validarTamanoArchivo()">
                  </div>
                </div>
              </div>

              <!-- Botón para enviar el tema -->
              <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                <?php
                // Determina si el botón debe estar deshabilitado para la pareja
                $botonDeshabilitado = false;
                // Condición para deshabilitar el botón cuando el usuario es la pareja y el tema está en 'Rechazado' o 'Pendiente'
                if (($estadoTema === 'Rechazado' || $estadoTema === 'Pendiente') && $parejaId == $usuario_id) {
                  $botonDeshabilitado = true;
                }

                // Renderizado del botón
                if ($botonDeshabilitado) : ?>
                  <button type="submit" class="btn d-inline-block" disabled>Enviar Tema</button>
                <?php else: ?>
                  <button type="submit" class="btn d-inline-block">Enviar Tema</button>
                <?php endif; ?>


            </form> <!-- Cierre del formulario principal -->

            <!-- Formulario separado para eliminar pareja -->
            <?php if ($pareja_seleccionado && $pareja_seleccionado_id): ?>
              <button type="button" class="btn color-rojo ms-2" data-bs-toggle="modal" data-bs-target="#modalConfirmarEliminarPareja">Eliminar Pareja</button>
            <?php endif; ?>

          </div>
        </div>
    </div>
  <?php endif; ?>
  </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="modalMotivoRechazo" tabindex="-1" aria-labelledby="modalLabelMotivoRechazo" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabelMotivoRechazo">Motivo de Rechazo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?php echo htmlspecialchars($motivo_rechazo); ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmación para Eliminar Pareja -->
  <div class="modal fade" id="modalConfirmarEliminarPareja" tabindex="-1" aria-labelledby="modalLabelEliminarPareja" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabelEliminarPareja">Confirmar Eliminación de Pareja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que deseas eliminar a tu pareja de tesis?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <!-- Formulario de eliminación que se envía al confirmar -->
          <form action="logica-eliminar-pareja.php" method="POST" class="d-inline">
            <input type="hidden" name="pareja_id" value="<?php echo $pareja_seleccionado_id; ?>">
            <button type="submit" class="btn btn-danger">Eliminar</button>
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
  <script>
    // Función para verificar si la card de rechazo está visible
    function verificarCardRechazo() {
      const cardRechazo = document.getElementById('card-rechazo');
      const formularioEnviarTema = document.getElementById('formulario-enviar-tema');

      // Verifica si la card de rechazo existe y está visible
      if (cardRechazo && cardRechazo.style.display !== 'none') {
        formularioEnviarTema.classList.add('d-none'); // Oculta el formulario
      }
    }

    // Ejecutar la función al cargar la página
    window.onload = verificarCardRechazo;

    // Función para mostrar el formulario y ocultar la card de rechazo
    function mostrarFormulario() {
      document.getElementById('card-rechazo').style.display = 'none';
      document.getElementById('formulario-enviar-tema').classList.remove('d-none');
    }
  </script>

</body>

</html>