<?php
session_start();
require '../config/config.php'; // Asegúrate de que esta línea está correctamente incluida

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

// Verificar que la conexión a la base de datos ($conn) esté disponible
if (!$conn) {
  die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

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

// Obtener el ID del tema a editar
if (!isset($_GET['id']) || empty($_GET['id'])) {
  die("ID de tema no proporcionado.");
}

$tema_id = intval($_GET['id']);

// Obtener los datos del tema actual para prellenar el formulario
$sql = "SELECT * FROM tema WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tema_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Tema no encontrado.");
}

$tema = $result->fetch_assoc();
$stmt->close();

// Consulta para obtener todos los tutores
$sql_tutores = "SELECT id, nombres, estado FROM tutores where estado = 0";
$result_tutores = $conn->query($sql_tutores);

// Consulta para obtener la pareja de tesis actual si existe
$sql_pareja_actual = "SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM usuarios WHERE id = ?";
$stmt_pareja_actual = $conn->prepare($sql_pareja_actual);
$stmt_pareja_actual->bind_param("i", $tema['pareja_id']);
$stmt_pareja_actual->execute();
$result_pareja_actual = $stmt_pareja_actual->get_result();
$pareja_actual = $result_pareja_actual->fetch_assoc();

// Consulta para obtener las parejas de tesis disponibles
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
?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Tema</title>
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
      <h1 class="mb-4 text-center fw-bold">Editar Tema</h1>

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
      <!-- Card con el formulario -->
      <div class="card shadow-lg">
        <div class="card-body">
          <form action="logica-actualizar-tema.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">

            <div class="row">
              <!-- Primera columna -->
              <div class="col-md-6">
                <!-- Campo Tema -->
                <div class="mb-3">
                  <label for="tema" class="form-label fw-bold">Tema</label>
                  <input type="text" class="form-control" id="tema" name="tema" value="<?php echo htmlspecialchars($tema['tema']); ?>" required>
                </div>

                <!-- Objetivo General -->
                <div class="mb-3">
                  <label for="objetivo_general" class="form-label fw-bold">Objetivo General</label>
                  <textarea class="form-control no-modificable" id="objetivo_general" name="objetivo_general" required><?php echo htmlspecialchars($tema['objetivo_general']); ?></textarea>
                </div>

                <!-- Objetivo Específico 1 -->
                <div class="mb-3">
                  <label for="objetivo_especifico_uno" class="form-label fw-bold">Objetivo Específico 1</label>
                  <textarea class="form-control no-modificable" id="objetivo_especifico_uno" name="objetivo_especifico_uno" required><?php echo htmlspecialchars($tema['objetivo_especifico_uno']); ?></textarea>
                </div>

                <!-- Objetivo Específico 2 -->
                <div class="mb-3">
                  <label for="objetivo_especifico_dos" class="form-label fw-bold">Objetivo Específico 2</label>
                  <textarea class="form-control no-modificable" id="objetivo_especifico_dos" name="objetivo_especifico_dos" required><?php echo htmlspecialchars($tema['objetivo_especifico_dos']); ?></textarea>
                </div>
              </div>

              <!-- Segunda columna -->
              <div class="col-md-6">
                <!-- Objetivo Específico 3 -->
                <div class="mb-3">
                  <label for="objetivo_especifico_tres" class="form-label fw-bold">Objetivo Específico 3</label>
                  <textarea class="form-control no-modificable" id="objetivo_especifico_tres" name="objetivo_especifico_tres" required><?php echo htmlspecialchars($tema['objetivo_especifico_tres']); ?></textarea>
                </div>

                <!-- Tutor -->
                <div class="mb-3">
                  <label for="tutor_id" class="form-label fw-bold">Tutor</label>
                  <select class="form-select" id="tutor_id" name="tutor_id" required>
                    <option value="">Seleccione un tutor</option>
                    <?php while ($row = $result_tutores->fetch_assoc()): ?>
                      <option value="<?php echo $row['id']; ?>" <?php echo ($row['id'] == $tema['tutor_id']) ? 'selected' : ''; ?>>
                        <?php echo $row['nombres']; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <!-- Pareja de Tesis -->
                <div class="mb-3">
                  <label for="pareja_id" class="form-label fw-bold">Pareja de Tesis</label>
                  <select class="form-select" id="pareja_id" name="pareja_id" required>
                    <option value="">Seleccione un compañero</option>
                    <!-- Mostrar "Sin Pareja" como seleccionado si la pareja_id es -1 -->
                    <option value="-1" <?php echo ($tema['pareja_id'] == -1) ? 'selected' : ''; ?>>Sin Pareja</option>

                    <!-- Mostrar la pareja actual si existe y no es -1 -->
                    <?php if ($pareja_actual && $tema['pareja_id'] != -1): ?>
                      <option value="<?php echo $pareja_actual['id']; ?>" selected>
                        <?php echo $pareja_actual['nombre_completo']; ?> (Pareja Actual)
                      </option>
                    <?php endif; ?>

                    <!-- Mostrar el listado de parejas disponibles, excluyendo la pareja actual -->
                    <?php while ($row = $result_parejas->fetch_assoc()): ?>
                      <?php if ($row['id'] !== $tema['pareja_id']): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nombre_completo']; ?></option>
                      <?php endif; ?>
                    <?php endwhile; ?>
                  </select>
                </div>


                <!-- Subir Anteproyecto -->
                <div class="mb-3">
                  <label for="anteproyecto" class="form-label fw-bold">Subir Anteproyecto (ZIP o RAR MÁXIMO 5 MB)</label>

                  <!-- Campo para subir un nuevo archivo -->
                  <input type="file" class="form-control" id="documentoCarpeta" name="anteproyecto" accept=".zip,.rar" onchange="validarTamanoArchivo()">

                  <!-- Enlace para visualizar el archivo actual, si existe -->
                  <?php if (!empty($tema['anteproyecto'])): ?>
                    <p class="mt-1">
                      Archivo actual:
                      <a href="../uploads/<?php echo htmlspecialchars($tema['anteproyecto']); ?>" target="_blank">
                        Documentos
                      </a>
                    </p>
                  <?php endif; ?>
                </div>


              </div>
            </div>

            <!-- Botones de acción -->
            <div class="text-center btns mt-4 d-flex justify-content-center gap-4">
              <button type="submit" class="btn">Actualizar Tema</button>
              <a href="enviar-tema.php" class="btn cancelar-btn">Cancelar</a>
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
  <script src="../js/toast.js" defer></script>
  <script src="../js/validarTamaño.js" defer></script>

</body>

</html>