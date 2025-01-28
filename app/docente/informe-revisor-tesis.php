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

// Verificar si el usuario es un docente
$sql_docente = "SELECT id FROM usuarios WHERE id = ? AND rol = 'docente'";
$stmt_docente = $conn->prepare($sql_docente);
$stmt_docente->bind_param("i", $usuario_id);
$stmt_docente->execute();
$result_docente = $stmt_docente->get_result();

if ($result_docente->num_rows === 0) {
    echo "Acceso denegado. Solo los docentes pueden gestionar informes.";
    exit();
}

// Obtener informes activos
$sql_informes = "SELECT id, informe_tesis, estado, fecha_subida FROM informes_tesis WHERE tutor_id = ? AND estado = 0";
$stmt_informes = $conn->prepare($sql_informes);
$stmt_informes->bind_param("i", $usuario_id);
$stmt_informes->execute();
$result_informes = $stmt_informes->get_result();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informe Tesis</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../../images/favicon.png" type="image/png">
</head>

<body>
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
            <a class="nav-link" href="revisar-tesis.php"><i class='bx bx-book-reader'></i> Revisar Tesis</a>
            <a class="nav-link" href="ver-observaciones.php"><i class='bx bx-file'></i> Ver Observaciones</a>
            <a class="nav-link" href="revisar-correcciones-tesis.php"><i class='bx bx-file'></i> Ver Correcciones</a>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-file'></i> Informes</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse show" id="submenuInformes">
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

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-3">
            <h1 class="text-center mb-4 fw-bold">Informes Tesis</h1>

            <!-- Toast -->
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <?php if ($_GET['status'] === 'updated'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Actualización Exitosa</strong>
                            <?php elseif ($_GET['status'] === 'error_updated'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error de Actualización</strong>
                            <?php elseif ($_GET['status'] === 'invalid_file'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error de Tamaño</strong>
                            <?php elseif ($_GET['status'] === 'file_error'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error de Tamaño</strong>
                            <?php elseif ($_GET['status'] === 'success'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Subida Exitosa</strong>
                            <?php elseif ($_GET['status'] === 'deleted'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-warning'></i>
                                <strong class="me-auto">Eliminación Exitosa</strong>
                            <?php elseif ($_GET['status'] === 'dlt_error'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error al Eliminar</strong>
                            <?php endif; ?>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($_GET['status']) {
                                case 'updated':
                                    echo "El informe ha sido actualizado con éxito.";
                                    break;
                                case 'error_updated':
                                    echo "Hubo un problema al intentar actualizar el informe. Por favor, intente de nuevo.";
                                    break;
                                case 'invalid_file':
                                    echo "El archivo supera el límite de 20 MB. Por favor, sube un archivo más pequeño.";
                                    break;
                                case 'file_error':
                                    echo "El archivo supera el límite de 20 MB. Por favor, sube un archivo más pequeño.";
                                    break;
                                case 'success':
                                    echo "El informe se ha subido correctamente.";
                                    break;
                                case 'deleted':
                                    echo "El informe ha sido eliminado correctamente.";
                                    break;
                                case 'dlt_error':
                                    echo "No se pudo eliminar el informe. Intente nuevamente.";
                                    break;
                                default:
                                    echo "Ha ocurrido un error desconocido. Por favor, contacte al administrador.";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mb-3 inf">
                <button class="btn subir-informe" data-bs-toggle="modal" data-bs-target="#modalSubirInforme">
                    <i class='bx bx-upload'></i> Subir Informe Tesis
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped custom-scroll-table">
                    <thead>
                        <tr>
                            <th>Informe</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_informes->num_rows > 0): ?>
                            <?php while ($informe = $result_informes->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $informe['informe_tesis']; ?>" download>
                                            <?php echo basename($informe['informe_tesis']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo date("Y-m-d", strtotime($informe['fecha_subida'])); ?></td>
                                    <td>
                                        <form action="editar_informe.php" method="POST" class="d-inline" enctype="multipart/form-data">
                                            <input type="hidden" name="informe_id" value="<?php echo $informe['id']; ?>">
                                            <button type="button" class="btn btn-warning " data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $informe['id']; ?>">
                                                <i class='bx bx-edit-alt'></i>
                                            </button>
                                        </form>

                                        <form action="eliminar_informe.php" method="POST" class="d-inline">
                                            <input type="hidden" name="informe_id" value="<?php echo $informe['id']; ?>">
                                            <button type="button" class="btn btn-danger " data-bs-toggle="modal" data-bs-target="#modalEliminar<?php echo $informe['id']; ?>">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Editar -->
                                <div class="modal fade" id="modalEditar<?php echo $informe['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="editar_informe-tesis.php" method="POST" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalEditarLabel">Editar Informe</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="informe_id" value="<?php echo $informe['id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="archivoEditar" class="form-label">Subir Nuevo Archivo</label>
                                                        <input type="file" class="form-control documentoCarpeta" name="archivo_informe" accept=".doc,.docx,.pdf,.zip" required onchange="validarTamanoArchivo()">
                                                        <small class="form-text text-muted">Se permiten archivos .zip, .pdf, .doc, .docx con un tamaño máximo de 20 MB.</small>
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
                                <div class="modal fade" id="modalEliminar<?php echo $informe['id']; ?>" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="eliminar_informe-tesis.php" method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalEliminarLabel">Eliminar Informe</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="informe_id" value="<?php echo $informe['id']; ?>">
                                                    <p>¿Está seguro de que desea eliminar este informe?</p>
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
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay informes disponibles.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para subir informe -->
    <div class="modal fade" id="modalSubirInforme" tabindex="-1" aria-labelledby="modalSubirInformeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="subir_informe_tesis.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSubirInformeLabel">Subir Nuevo Informe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="archivo_informe" class="form-label">Archivo</label>
                            <input type="file" class="form-control documentoCarpeta" name="archivo_informe" accept=".doc,.docx,.pdf,.zip" required onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">Se permiten archivos .zip, .pdf, .doc, .docx con un tamaño máximo de 20 MB.</small>
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
                El archivo supera el límite de 20 MB. Por favor, sube un archivo más pequeño.
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
    <script src="../js/validadDobleInput.js" defer></script>

</body>

</html>