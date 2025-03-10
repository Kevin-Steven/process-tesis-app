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

// Consulta para obtener la información del tema, incluyendo correcciones
$sql_tema = "SELECT estado_tesis, correcciones_tesis FROM tema WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

$estado_tema = $tema['estado_tema'] ?? null;
$estado_tesis = $tema['estado_tesis'] ?? null;
$correcciones_tesis = $tema['correcciones_tesis'] ?? null;
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enviar Correcciones</title>
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
            <a class="nav-link" href="estado-plagio.php"><i class='bx bx-file'></i> Antiplagio</a>
            <a class="nav-link" href="sustentacion.php"><i class='bx bx-file'></i> Sustentacion</a>
        </nav>
    </div>

    <div class="content" id="content">
        <div class="container py-4">
            <h1 class="mb-4 text-center fw-bold">Enviar Correcciones</h1>

            <!-- Toast -->
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <?php if ($_GET['status'] === 'success'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Subida Exitosa</strong>
                            <?php elseif ($_GET['status'] === 'deleted'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Documento Eliminado</strong>
                            <?php elseif ($_GET['status'] === 'update'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Documento Actualizado</strong>
                            <?php else: ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error</strong>
                            <?php endif; ?>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo "El documento se ha subido correctamente.";
                                    break;
                                case 'deleted':
                                    echo "El documento se ha eliminado correctamente.";
                                    break;
                                case 'update':
                                    echo "El documento se ha actualizado correctamente.";
                                    break;
                                case 'invalid_extension':
                                    echo "Solo se permiten archivos ZIP.";
                                    break;
                                case 'too_large':
                                    echo "El archivo supera el tamaño máximo de 5 MB.";
                                    break;
                                case 'file_error':
                                    echo "El archivo supera el tamaño máximo de 5 MB.";
                                    break;
                                case 'upload_error':
                                    echo "Hubo un error al mover el archivo.";
                                    break;
                                case 'db_error':
                                    echo "Error al actualizar la base de datos.";
                                    break;
                                case 'no_file':
                                    echo "No se ha seleccionado ningún archivo.";
                                    break;
                                case 'form_error':
                                    echo "Error en el envío del formulario.";
                                    break;
                                case 'not_found':
                                    echo "No se encontraron datos del usuario.";
                                    break;
                                case 'missing_data':
                                    echo "Faltan datos en el formulario.";
                                    break;
                                default:
                                    echo "Ocurrió un error desconocido.";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($correcciones_tesis)): ?>
                <!-- Si ya hay un archivo subido, mostrar opciones para descargar y editar -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Archivo de Correcciones Subido</h5>
                    </div>
                    <div class="card-body">
                        <!-- Enlace para descargar el archivo existente -->
                        <p class="mb-3 d-flex align-items-center">
                            <!-- Enlace para descargar el archivo existente -->
                            <a href="../uploads/correcciones/<?php echo htmlspecialchars($correcciones_tesis); ?>" class="text-decoration-none">
                                <i class='bx bx-download'></i> Descargar Correcciones
                            </a>

                            <span class="d-none d-md-inline mx-2"> - </span>

                            <!-- Enlace para abrir el modal de eliminación -->
                            <a href="#" class="text-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalEliminarCorrecciones">
                                <i class='bx bx-trash'></i> Eliminar Correcciones
                            </a>
                        </p>

                        <!-- Formulario para reemplazar el archivo existente -->
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <form action="editar_correcciones.php" method="POST" class="enviar-tema" enctype="multipart/form-data">
                                    <input type="hidden" name="correcciones_tesis_anterior" value="<?php echo htmlspecialchars($correcciones_tesis); ?>">
                                    <div class="mb-3">
                                        <label for="correcciones_nuevas" class="form-label fw-bold">Reemplazar Correcciones (ZIP MÁXIMO 5 MB)</label>
                                        <input type="file" class="form-control" id="documentoCarpeta" name="correcciones_nuevas" accept=".zip" required onchange="validarTamanoArchivo()">
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn">Actualizar Correcciones</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Si no hay correcciones subidas, mostrar el formulario de subida -->
                <div class="card shadow-lg">
                    <div class="card-body">
                        <form action="logica-procesar-correcciones.php" class="enviar-tema" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="correcciones" class="form-label fw-bold">Subir Archivo de Correcciones (ZIP MÁXIMO 2 MB)</label>
                                <input type="file" class="form-control" id="documentoCarpeta" name="correcciones" accept=".zip" required onchange="validarTamanoArchivo()">
                                <small class="form-text text-muted">El archivo debe ser un ZIP que contenga las correcciones.</small>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn">Enviar Correcciones</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Modal de Confirmación para eliminar -->
            <div class="modal fade" id="modalEliminarCorrecciones" tabindex="-1" aria-labelledby="modalLabelEliminar" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabelEliminar">Confirmar Eliminación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>¿Estás seguro de que deseas eliminar este archivo de correcciones? Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <!-- Formulario para eliminar el archivo -->
                            <form action="eliminar_correcciones.php" method="POST">
                                <input type="hidden" name="correcciones_tesis" value="<?php echo htmlspecialchars($correcciones_tesis); ?>">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
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
                El archivo supera el límite de 2 MB. Por favor, sube un archivo más pequeño.
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