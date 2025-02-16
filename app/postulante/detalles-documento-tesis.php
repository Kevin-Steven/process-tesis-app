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

// Consulta para obtener el estado del tema y el documento de tesis
$sql_tema = "SELECT estado_tema, estado_tesis, documento_tesis, observaciones_tesis, pareja_id 
             FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

$estado_tema = $tema['estado_tema'] ?? null;
$estado_tesis = $tema['estado_tesis'] ?? null;
$documento_tesis = $tema['documento_tesis'] ?? null;
$observaciones_tesis = $tema['observaciones_tesis'] ?? 'Sin Observaciones';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalles Documento Tesis</title>
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
            <a class="nav-link" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
            <a class="nav-link active" href="enviar-documento-tesis.php"><i class='bx bx-file'></i> Documento Tesis</a>
            <!-- if ($estado_tesis === 'Aprobado'): ?> agregar la etiqueta php antes del if -->
            <?php if ($estado_tema === 'Aprobado'): ?>
                <a class="nav-link" href="estado-plagio.php"><i class='bx bx-file'></i> Antiplagio</a>
                <a class="nav-link" href="sustentacion.php"><i class='bx bx-file'></i> Sustentacion</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container py-4">
            <h1 class="mb-4 text-center fw-bold">Detalles del Documento de Tesis</h1>

            <div class="card shadow-lg mb-4">
                <div class="card-body">
                    <!-- Mostrar enlace para descargar el documento actual -->
                    <?php if (!empty($documento_tesis)): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Documento Actual: </label>
                            <a href="../uploads/documento-tesis/<?php echo htmlspecialchars($documento_tesis); ?>" class="text-decoration-none">
                                Descargar Documento Actual
                            </a>
                        </div>
                    <?php else: ?>
                        <p>No hay documento disponible.</p>
                    <?php endif; ?>

                    <!-- Formulario para actualizar el documento -->
                    <form action="logica-actualizar-documento-tesis.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_postulante" value="<?php echo $usuario_id; ?>">

                        <div class="mb-3">
                            <label for="documentoTesis" class="form-label fw-bold">Subir Nuevo Documento (ZIP MÁXIMO 5 MB)</label>
                            <input type="file" class="form-control" id="documentoCarpeta" name="documentoTesis" accept=".zip" required onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">El archivo ZIP debe contener: Documento de Tesis en Word, PDF e Informe de Antiplagio.</small>
                        </div>

                        <div class="text-center mt-4">
                            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">
                                <!-- Botón para enviar nuevo documento -->
                                <button type="submit" name="accion" value="actualizar" class="btn mb-2 mb-md-0">Actualizar Documento</button>

                                <!-- Botón para abrir la modal de confirmación para eliminar documento -->
                                <button type="button" class="btn color-rojo" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                    Eliminar Documento
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar el documento -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="logica-actualizar-documento-tesis.php" method="POST">
                        <input type="hidden" name="id_postulante" value="<?php echo $usuario_id; ?>">
                        <button type="submit" name="accion" value="eliminar" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast para error de tamaño de archivo -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="fileSizeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
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
    <script src="../js/toast.js" defer></script>
    <script src="../js/validarTamaño.js" defer></script>

</body>

</html>