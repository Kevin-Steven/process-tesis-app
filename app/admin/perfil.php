<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../../index.php");
  exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos del usuario
$sql = "SELECT u.nombres, u.apellidos, u.email, u.cedula, u.telefono, u.whatsapp, u.carrera, u.fecha_subida, 
                d.estado_inscripcion, u.foto_perfil 
          FROM usuarios u 
          LEFT JOIN documentos_postulante d ON u.id = d.usuario_id 
          WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$primer_nombre = explode(' ', $usuario['nombres'])[0];
$primer_apellido = explode(' ', $usuario['apellidos'])[0];

$stmt->close();

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

  // Redirigir para evitar reenvíos de formularios
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
          <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i> <!-- Ícono agregado -->
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil.php">
              <i class='bx bx-user me-2'></i> <!-- Ícono para "Perfil" -->
              Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave.php">
              <i class='bx bx-lock me-2'></i> <!-- Ícono para "Cambio de Clave" -->
              Cambio de Clave
            </a>
          </li>
          <li>
            <hr class="dropdown-divider">
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
              <i class='bx bx-log-out me-2'></i> <!-- Ícono para "Cerrar Sesión" -->
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
      <a class="nav-link" href="inicio-administrador.php"><i class='bx bx-home-alt'></i> Inicio</a>
      <a class="nav-link" href="modificar-rol.php"><i class='bx bx-user'></i> Modificar Rol</a>
      <a class="nav-link" href="restaurar-claves.php"><i class='bx bx-lock'></i> Restarurar clave</a>
    </nav>
  </div>

  <!-- Content -->
  <div class="content" id="content">
    <div class="container mt-3">
      <div class="row justify-content-center">
        <!-- Columna del Perfil del Usuario -->
        <div class="col-md-8 mb-4">
          <div class="card">
            <div class="card-header text-center">
              <h2 class="card-title fw-bold">Mis Datos</h2>
            </div>
            <div class="card-body">

              <!-- Formulario para actualizar datos -->
              <form action="actualizar-perfil.php" method="POST" class="formulario-perfil" enctype="multipart/form-data">
                <?php if (isset($_GET['status'])): ?>
                  <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="toast-header">
                        <i class='bx bx-send me-2'></i>
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
                          No se realizaron cambios en los datos.
                        <?php else: ?>
                          Hubo un error al actualizar el perfil.
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <script>
                    document.addEventListener('DOMContentLoaded', (event) => {
                      var toastEl = document.getElementById('liveToast');
                      var toast = new bootstrap.Toast(toastEl);
                      toast.show();
                    });
                  </script>
                <?php endif; ?>

                <!-- Imagen del perfil -->
                <div class="text-center mb-3">
                  <img id="preview" src="<?php echo $usuario['foto_perfil'] ? $usuario['foto_perfil'] : '../../images/user.png'; ?>" alt="Perfil" class="rounded-circle" width="120">
                </div>

                <!-- Botón personalizado para subir foto -->
                <div class="text-center mb-2 boton-subir-perfil">
                  <label for="foto_perfil" class="btn-upload">
                    <i class='bx bx-camera text-white'></i> Cargar Foto
                  </label>
                  <input type="file" id="foto_perfil" name="foto_perfil" class="file-input" accept="image/*" onchange="previewImage(event)">
                  <input type="hidden" name="foto_actual" value="<?php echo $usuario['foto_perfil']; ?>">
                </div>

                <!-- Distribución en dos columnas -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="nombres" class="form-label"><strong>Nombres:</strong></label>
                    <input type="text" name="nombres" class="form-control" value="<?php echo $usuario['nombres']; ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="apellidos" class="form-label"><strong>Apellidos:</strong></label>
                    <input type="text" name="apellidos" class="form-control" value="<?php echo $usuario['apellidos']; ?>" required>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label"><strong>Email:</strong></label>
                    <input type="email" name="email" class="form-control" value="<?php echo $usuario['email']; ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="cedula" class="form-label"><strong>Cédula:</strong></label>
                    <input type="text" name="cedula" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo $usuario['cedula']; ?>" readonly>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label"><strong>Teléfono:</strong></label>
                    <input type="text" name="telefono" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo $usuario['telefono']; ?>" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="whatsapp" class="form-label"><strong>WhatsApp:</strong></label>
                    <input type="text" name="whatsapp" class="form-control" maxlength="10" oninput="validateInput(this)" value="<?php echo $usuario['whatsapp']; ?>" required>
                  </div>
                </div>

                <div class="text-center mt-3">
                  <button type="submit" class="btn btn-primary w-50">Actualizar</button>
                </div>
              </form>
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
  <script src="../js/sidebar.js"></script>
  <script src="../js/number.js" defer></script>
</body>

</html>