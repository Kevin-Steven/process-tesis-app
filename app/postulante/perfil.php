<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos del usuario y la inscripción
$sql = "SELECT u.nombres, u.apellidos, u.email, u.cedula, u.telefono, u.whatsapp, u.carrera, u.fecha_subida, 
                d.estado_inscripcion, d.fecha_subida AS fecha_envio_formulario, u.foto_perfil, d.estado_registro
          FROM usuarios u 
          LEFT JOIN documentos_postulante d ON u.id = d.usuario_id 
          WHERE u.id = ? 
          ORDER BY d.fecha_subida DESC 
          LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$primer_nombre = explode(' ', $usuario['nombres'])[0];
$primer_apellido = explode(' ', $usuario['apellidos'])[0];

$stmt->close();

// Nueva consulta: Obtener el registro más reciente del tema del usuario
$sql_tema = "SELECT tema, estado_tema, estado_registro, observaciones_anteproyecto, fecha_subida FROM tema 
             WHERE usuario_id = ? 
             ORDER BY fecha_subida DESC 
             LIMIT 1";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombres = strtoupper(mysqli_real_escape_string($conn, $_POST['nombres']));
  $apellidos = strtoupper(mysqli_real_escape_string($conn, $_POST['apellidos']));
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $cedula = $_POST['cedula'];
  $telefono = $_POST['telefono'];
  $whatsapp = $_POST['whatsapp'];

  // Verificar si se ha subido una imagen de perfil
  if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $target_dir = "../photos/";
    $foto_perfil = $target_dir . basename($_FILES["foto_perfil"]["name"]);
    move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $foto_perfil);

    // Actualizar la variable de sesión con la nueva foto
    $_SESSION['usuario_foto'] = $foto_perfil;
  } else {
    $foto_perfil = $usuario['foto_perfil'];
  }

  header("Location: perfil.php");
  exit();
}

$conn->close();
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil</title>
  <link href="estilos.css" rel="stylesheet">
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
        <input type="text" id="search" class="form-control" placeholder="Search">
      </div>
      <!-- Iconos adicionales a la derecha -->
      <i class='bx bx-envelope'></i>
      <i class='bx bx-bell'></i>
      <!-- Menú desplegable para el usuario -->
      <div class="user-profile dropdown">
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img id="topbar-profile" src="<?php echo $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png'; ?>" alt="Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php">
              <i class='bx bx-user me-2'></i>
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambioClave.php">
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
      <img id="sidebar-profile" src="<?php echo $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png'; ?>" alt="Profile">
      <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
      <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>

    </div>
    <nav class="nav flex-column">
      <a class="nav-link" href="inicio-postulante.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link active" href="perfil.php"><i class='bx bx-user'></i> Perfil</a>
      <a class="nav-link" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
      <a class="nav-link" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
      <!-- Mostrar "Enviar Tema" solo si el estado de inscripción es "Aprobado" -->
      <?php if ($usuario['estado_inscripcion'] === 'Aprobado'): ?>
        <a class="nav-link" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
      <?php endif; ?>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-3">
      <div class="row justify-content-center">
        <!-- Columna del Perfil del Usuario -->
        <div class="col-md-6 mb-4">
          <div class="card">
            <div class="card-header text-center">
              <h2 class="card-title fw-bold">Mis Datos</h2>
            </div>
            <div class="card-body">

              <!-- Formulario para actualizar datos -->
              <form method="POST" action="actualizar-perfil.php" class="formulario-perfil" enctype="multipart/form-data">

                <?php if (isset($_GET['status'])): ?>
                  <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="toast-header">
                        <i class='bx bx-send fs-4 me-2'></i>
                        <strong class="me-auto">Estado de Actualización</strong>
                        <small>Justo ahora</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                      </div>
                      <div class="toast-body">
                        <?php if ($_GET['status'] == 'success'): ?>
                          Perfil actualizado correctamente.
                        <?php elseif ($_GET['status'] == 'invalid_phone'): ?>
                          El número de teléfono o WhatsApp debe tener 10 dígitos.
                        <?php elseif ($_GET['status'] == 'no_changes'): ?>
                          No se realizaron cambios.
                        <?php else: ?>
                          Hubo un error al actualizar el perfil.
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="text-center mb-3">
                  <img id="preview" src="<?php echo $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png'; ?>" alt="Perfil" class="rounded-circle" width="120">
                </div>

                <!-- Botón personalizado para subir foto -->
                <div class="text-center mb-2 boton-subir-perfil">
                  <label for="foto_perfil" class="btn-upload">
                    <i class='bx bx-camera'></i> Cargar Foto
                  </label>
                  <input type="file" id="foto_perfil" name="foto_perfil" class="file-input" accept="image/*" onchange="previewImage(event)">
                  <input type="hidden" name="foto_actual" value="<?php echo $usuario['foto_perfil']; ?>"> <!-- Campo oculto para la foto actual -->
                </div>

                <ul class="list-group list-group-flush">
                  <li class="list-group-item">
                    <strong>Nombres:</strong>
                    <input type="text" name="nombres" class="form-control" value="<?php echo $usuario['nombres']; ?>" required>
                  </li>
                  <li class="list-group-item">
                    <strong>Apellidos:</strong>
                    <input type="text" name="apellidos" class="form-control" value="<?php echo $usuario['apellidos']; ?>" required>
                  </li>
                  <li class="list-group-item">
                    <strong>Email:</strong>
                    <input type="email" name="email" class="form-control" value="<?php echo $usuario['email']; ?>" required>
                  </li>
                  <li class="list-group-item">
                    <strong>Teléfono:</strong>
                    <input type="text" name="telefono" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo $usuario['telefono']; ?>" required>
                  </li>
                  <li class="list-group-item">
                    <strong>WhatsApp:</strong>
                    <input type="text" name="whatsapp" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo $usuario['whatsapp']; ?>" required>
                  </li>
                  <li class="list-group-item">
                    <strong>Cédula:</strong>
                    <input type="text" name="cedula" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo $usuario['cedula']; ?>" readonly>
                  </li>
                </ul>
                <div class="text-center mt-3">
                  <button type="submit" class="btn w-50">Actualizar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <?php
        // Función para convertir el mes en inglés al español y agregar "de"
        function mesEnEspañol($fecha)
        {
          $meses_ingles = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
          );
          $meses_espanol = array(
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
          );
          // Formatear la fecha y agregar el "de"
          return date("d", strtotime($fecha)) . ' de ' . str_replace($meses_ingles, $meses_espanol, date("F", strtotime($fecha))) . ' de ' . date("Y", strtotime($fecha));
        }
        ?>

        <!-- Columna de Actividad Reciente -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-lg border-0">
            <div class="card-header bg-light text-center py-2">
              <h4 class="card-title fw-bold">Actividad Reciente</h4>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <?php
                // Verificar si hay actividad reciente
                $actividad_reciente = false;

                // Verificar si hay fecha de envío del formulario
                if (!empty($usuario['fecha_envio_formulario']) && $usuario['estado_registro'] === 0) {
                  $actividad_reciente = true;
                ?>
                  <li class="list-group-item">
                    <i class='bx bx-file text-success'></i> Formulario de inscripción enviado el <?php echo mesEnEspañol($usuario['fecha_envio_formulario']); ?>.
                  </li>
                <?php } ?>

                <?php
                // Verificar si el estado de inscripción es "En proceso de validación"
                if ($usuario['estado_inscripcion'] === 'En proceso de validación' && $usuario['estado_registro'] === 0) {
                  $actividad_reciente = true;
                ?>
                  <li class="list-group-item">
                    <i class='bx bx-info-circle text-warning'></i> Estado de la inscripción: En proceso de validación.
                  </li>
                <?php
                }
                ?>

                <?php
                // Verificar si el estado de inscripción es "Aprobado"
                if ($usuario['estado_inscripcion'] === 'Aprobado' && $usuario['estado_registro'] === 0) {
                  $actividad_reciente = true;
                ?>
                  <li class="list-group-item">
                    <i class='bx bx-check-circle text-success'></i> Inscripción aceptada el <?php echo mesEnEspañol($usuario['fecha_envio_formulario']); ?>.
                  </li>
                <?php
                }
                ?>

                <?php
                // Verificar si el estado de inscripción es "Rechazado"
                if ($usuario['estado_inscripcion'] === 'Rechazado' && $usuario['estado_registro'] === 1) {
                  $actividad_reciente = true;
                ?>
                  <li class="list-group-item">
                    <i class='bx bx-x-circle text-danger'></i> Inscripción rechazada el <?php echo mesEnEspañol($usuario['fecha_envio_formulario']); ?>.
                  </li>
                <?php
                }
                ?>

                <!-- <?php
                      // Mostrar la confirmación de correo solo si la inscripción ha sido aprobada o rechazada
                      if (!empty($usuario['email']) && ($usuario['estado_inscripcion'] === 'Aprobado' || $usuario['estado_inscripcion'] === 'Rechazado')) {
                      ?>
                  <li class="list-group-item">
                    <i class='bx bx-envelope text-success'></i> Confirmación enviada a <?php echo $usuario['email']; ?>.
                  </li>
                <?php } ?> -->

                <?php
                // Verificar estado del tema
                if ($tema) {
                  // Si el tema fue aprobado
                  if ($tema['estado_tema'] === 'Aprobado' && $tema['estado_registro'] === 0) {
                    $actividad_reciente = true;
                ?>
                    <li class="list-group-item">
                      <i class='bx bx-check-circle text-success'></i> Tu tema de tesis ha sido aprobado el <?php echo mesEnEspañol($tema['fecha_subida']); ?>.
                    </li>
                  <?php
                    // Si el tema fue rechazado
                  } elseif ($tema['estado_tema'] === 'Rechazado' && $tema['estado_registro'] === 1) {
                    $actividad_reciente = true;
                  ?>
                    <li class="list-group-item">
                      <i class='bx bx-x-circle text-danger'></i> Tu tema de tesis ha sido rechazado el <?php echo mesEnEspañol($tema['fecha_subida']); ?>.
                    </li>
                <?php
                  }
                }
                ?>

                <?php
                // Verificar que $tema no sea null y que tenga los índices necesarios
                if (isset($tema) && isset($tema['estado_tema'], $tema['estado_registro'], $tema['observaciones_anteproyecto'])) {
                  // Mostrar la confirmación de correo solo si la inscripción ha sido aprobada o rechazada
                  if ($tema['estado_tema'] === 'Aprobado' && $tema['estado_registro'] === 0 && !empty($tema['observaciones_anteproyecto'])) {
                ?>
                    <li class="list-group-item">
                      <i class='bx bx-info-circle text-warning'></i> Tiene observaciones en su anteproyecto.
                    </li>
                <?php
                  }
                }
                ?>

                <?php if (!$actividad_reciente): ?>
                  <!-- Si no hay actividad reciente -->
                  <li class="list-group-item text-center">
                    <i class='bx bx-info-circle text-muted'></i> Ninguna actividad reciente.
                  </li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
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
  <script src="../js/previewImage.js" defer></script>
  <script src="../js/sidebar.js" defer></script>
  <script src="../js/number.js" defer></script>
  <script src="../js/toast.js" defer></script>
</body>

</html>