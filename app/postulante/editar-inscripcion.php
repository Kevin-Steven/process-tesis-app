<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Obtener el ID del usuario
$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener la inscripción del usuario
$sql = "SELECT documento_carpeta, estado_inscripcion FROM documentos_postulante WHERE usuario_id = ? AND estado_registro = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$inscripcion = $result->fetch_assoc();
$stmt->close();

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Inscripción</title>
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
            <!-- Iconos adicionales a la derecha -->
            <i class='bx bx-envelope'></i>
            <i class='bx bx-bell'></i>
            <!-- Menú desplegable para el usuario -->
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span> <!-- Mostramos solo el primer nombre y primer apellido -->
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="perfil.php">
                            <i class='bx bx-user me-2'></i> <!-- Ícono para "Perfil" -->
                            Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="cambioClave.php">
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
            <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
            <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5>
            <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="inicio-postulante.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link" href="perfil.php"><i class='bx bx-user'></i> Perfil</a>
            <a class="nav-link" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
            <a class="nav-link" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container py-2">
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

            <h1 class="mb-4 text-center fw-bold">Editar Inscripción</h1>
            <div class="card shadow-lg mb-4">
                <div class="card-header text-center">
                    <h5 class="mb-0">Información</h5>
                </div>
                <div class="card-body">
                    <form action="logica-editar-inscripcion.php" class="actualizar-inscripcion" method="POST" enctype="multipart/form-data">

                        <!-- Carpeta de Documentos -->
                        <div class="mb-4">
                            <label for="documentoCarpeta" class="form-label">Subir Nueva Carpeta de Documentos (ZIP o RAR)</label>
                            <input type="file" class="form-control" id="documentoCarpeta" name="documentoCarpeta" accept=".zip,.rar" required onchange="validarTamanoArchivo()">
                            <p class="mt-2">Documento actual: <a href="../uploads/<?php echo $inscripcion['documento_carpeta']; ?>" target="_blank" class="link-primary">Ver Documentos</a></p>
                        </div>

                        <!-- Botón de guardar cambios -->
                        <div class="text-center">
                            <button type="submit" class="btn"><i class="bi bi-save"></i> Guardar Cambios</button>
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
    <script src="../js/validarTamaño.js" defer></script>
    <script src="../js/toast.js" defer></script>

</body>

</html>