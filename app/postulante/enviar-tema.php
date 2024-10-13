<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Obtener el ID del usuario
$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener el estado de la inscripción
$sql = "SELECT estado_inscripcion FROM documentos_postulante WHERE usuario_id = ? AND estado_registro = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$inscripcion = $result->fetch_assoc();
$stmt->close();

// Verificar el estado de la inscripción
$estado_inscripcion = $inscripcion['estado_inscripcion'] ?? null;

// Consulta para obtener los tutores
$sql_tutores = "SELECT id, nombres FROM tutores";
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

// Validar si existe una pareja seleccionada antes de continuar
$pareja_seleccionado_id = $pareja_seleccionado['seleccionador_id'] ?? null;

// Consulta para obtener el estado del tema
$sql_tema = "SELECT * FROM tema WHERE usuario_id = ? AND estado_registro = 0 LIMIT 1";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

// Consulta para obtener el tutor seleccionado por la pareja
$tutor_seleccionado = null;
if ($pareja_seleccionado_id) {
  $sql_tutor_seleccionado = "SELECT t.tutor_id, tut.nombres AS tutor_nombre 
                               FROM tema t
                               JOIN tutores tut ON t.tutor_id = tut.id
                               WHERE t.usuario_id = ?";
  $stmt_tutor_seleccionado = $conn->prepare($sql_tutor_seleccionado);
  $stmt_tutor_seleccionado->bind_param("i", $pareja_seleccionado_id);
  $stmt_tutor_seleccionado->execute();
  $result_tutor_seleccionado = $stmt_tutor_seleccionado->get_result();
  $tutor_seleccionado = $result_tutor_seleccionado->fetch_assoc();
  $stmt_tutor_seleccionado->close();
}

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
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container py-2">
      <h1 class="mb-4 text-center fw-bold">Enviar Tema</h1>

      <?php if (isset($tema) && $tema['estado_tema'] === 'Aprobado' && $tema['estado_registro'] === 0): ?>
        <div class="card shadow-lg mb-4 border-0">
          <div class="card-header bg-light text-center mb-3 py-3">
            <h3 class="mb-0 fw-bold text-success">¡Felicitaciones!</h3>
          </div>
          <div class="card-body text-center">
            <p class="fs-5 mb-3">
              <i class="bx bxs-award me-1"></i>
              <strong>Tu tema de tesis "<?php echo htmlspecialchars($tema['tema']); ?>" ha sido aprobado.</strong>
            </p>
            <?php if (!empty($tema['observaciones_anteproyecto'])): ?>
              <p>Descarga aquí las observaciones realizadas por el revisor.</p>
                <p class="mb-3 d-flex justify-content-center align-items-center">
                  <i class="bx bx-download me-1 text-primary fw-bold "></i>
                  <strong><a class="text-decoration-none" href="../uploads/observaciones/<?php echo htmlspecialchars($tema['observaciones_anteproyecto']); ?>" download>Descargar observaciones</a></strong>
                </p>
            <?php endif; ?>
          </div>
        </div>

      <?php elseif (isset($tema) && $tema['estado_tema'] === 'Pendiente' && $tema['estado_registro'] === 0): ?>
        <div class="card shadow-lg mb-4">
          <div class="card-header text-center mb-3">
            <h5 class="mb-0 fw-bold">Información del Tema</h5>
          </div>
          <div class="card-body text-center">
            <p><strong>Tema:</strong> <?php echo htmlspecialchars($tema['tema']); ?></p>
            <p><strong>Pareja:</strong>
              <?php
              if ($tema['pareja_id'] == -1) {
                echo "Sin Pareja";
              } else {

                $sql_pareja = "SELECT CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM usuarios WHERE id = ?";
                $stmt_pareja = $conn->prepare($sql_pareja);
                $stmt_pareja->bind_param("i", $tema['pareja_id']);
                $stmt_pareja->execute();
                $result_pareja = $stmt_pareja->get_result();
                $pareja = $result_pareja->fetch_assoc();
                echo htmlspecialchars($pareja['nombre_completo']);
              }
              ?>
            </p>
          </div>
          <div class="card-footer text-center">
            <a href="editar-tema.php?id=<?php echo $tema['id']; ?>" class="btn"><i class='bx bxs-edit'></i> Editar Tema</a>
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
        <!-- Mostrar formulario para enviar tema si no hay tema pendiente -->
        <div class="card shadow-lg">
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
                    <label for="anteproyecto" class="form-label fw-bold">Subir Anteproyecto (ZIP o RAR MÁXIMO 20MB)</label>
                    <input type="file" class="form-control" id="anteproyecto" name="anteproyecto" accept=".zip, .rar" required>
                  </div>
                </div>
              </div>

              <!-- Botón para enviar el tema -->
              <div class="text-center mt-4 d-flex justify-content-center align-items-center gap-3">
                <button type="submit" class="btn d-inline-block">Enviar Tema</button>
            </form> <!-- Cierre del formulario principal -->

            <!-- Formulario separado para eliminar pareja -->
            <?php if ($pareja_seleccionado && $pareja_seleccionado_id): ?>
              <form action="logica-eliminar-pareja.php" method="POST" class="enviar-tema">
                <input type="hidden" name="pareja_id" value="<?php echo $pareja_seleccionado_id; ?>">
                <button type="submit" class="btn color-rojo ms-2">Eliminar Pareja</button>
              </form>
            <?php endif; ?>

          </div>
        </div>
    </div>
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
  <script src="../js/sidebar.js" defer></script>

</body>

</html>