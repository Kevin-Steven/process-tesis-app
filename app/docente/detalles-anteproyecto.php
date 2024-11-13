<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
    header("Location: ../../index.php");
    exit();
}

$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

if (isset($_GET['id'])) {
    $anteproyecto_id = $_GET['id'];

    // Obtener los detalles del anteproyecto, incluyendo el postulante, su pareja (si tiene), y el tema
    $sql = "SELECT t.tema, t.anteproyecto, t.usuario_id, t.pareja_id, 
               u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
               pareja.nombres AS pareja_nombres, pareja.apellidos AS pareja_apellidos
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN usuarios pareja ON t.pareja_id = pareja.id
        WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $anteproyecto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $anteproyecto = $result->fetch_assoc();
    } else {
        echo "No se encontraron detalles para este anteproyecto.";
        exit();
    }
} else {
    echo "No se especificó ningún ID de anteproyecto.";
    exit();
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle del Anteproyecto</title>
    <link href="../gestor/estilos-gestor.css" rel="stylesheet">
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
                <input type="text" id="search" class="form-control" placeholder="Buscar...">
            </div>
            <i class='bx bx-envelope'></i>
            <i class='bx bx-bell'></i>
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1'></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li><a class="dropdown-item d-flex align-items-center" href="perfil.php"><i class='bx bx-user me-2'></i>Perfil</a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="cambio-clave.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a></li>
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
            <a class="nav-link" href="docente-inicio.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link" href="revisar-anteproyecto.php"><i class='bx bx-file'></i> Revisar Anteproyecto</a>
            <a class="nav-link" href="revisar-tesis.php"><i class='bx bx-book-reader'></i> Revisar Tesis</a>
            <a class="nav-link" href="ver-observaciones.php"><i class='bx bx-file'></i> Ver Observaciones</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-2">
            <h1 class="mb-4 text-center fw-bold">Detalle del Anteproyecto</h1>
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5 class="card-title text-primary text-center fw-bold mb-4">Información del Anteproyecto</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th><i class="bx bx-user"></i> Postulante</th>
                                    <td><?php echo $anteproyecto['postulante_nombres'] . ' ' . $anteproyecto['postulante_apellidos']; ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bx bx-user"></i> Pareja</th>
                                    <td>
                                        <?php
                                        if (!empty($anteproyecto['pareja_nombres']) && !empty($anteproyecto['pareja_apellidos'])) {
                                            echo $anteproyecto['pareja_nombres'] . ' ' . $anteproyecto['pareja_apellidos'];
                                        } else {
                                            echo "Sin pareja";
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bx bx-book"></i> Tema</th>
                                    <td><?php echo htmlspecialchars($anteproyecto['tema']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bx bx-file"></i> Documento de Anteproyecto</th>
                                    <td>
                                        <a class="text-decoration-none d-inline-flex align-items-center" href="../uploads/<?php echo urlencode($anteproyecto['anteproyecto']); ?>" download>
                                            <i class='bx bx-cloud-download'></i> Descargar documento
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-4 formulario-aceptar-rechazar">
                        <button type="button" class="btn aprobar" data-bs-toggle="modal" data-bs-target="#modalConfirmarEliminarSolicitud">Enviar observaciones</button>
                        <button type="button" id="cancelar-btn" class="btn" onclick="history.back()">Cancelar</button>
                    </div>
                </div>
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
                El archivo supera el límite de 10 MB. Por favor, sube un archivo más pequeño.
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarEliminarSolicitud" tabindex="-1" aria-labelledby="modalConfirmarEliminarSolicitudLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarEliminarSolicitudLabel">Enviar observaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formObservaciones" action="enviar-observaciones.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_anteproyecto" value="<?php echo $anteproyecto_id; ?>">
                        <input type="hidden" name="id_postulante" value="<?php echo $anteproyecto['usuario_id']; ?>">
                        <!-- Campo oculto para el ID de la pareja si existe -->
                        <?php if (!empty($anteproyecto['pareja_id'])): ?>
                            <input type="hidden" name="id_pareja" value="<?php echo $anteproyecto['pareja_id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="archivoObservaciones" class="form-label fw-bold">Subir archivo con las observaciones</label>
                            <input type="file" class="form-control" id="documentoCarpeta" name="archivo_observaciones" accept=".zip,.doc,.docx" required onchange="validarTamanoArchivo()">
                            <small class="form-text text-muted">Se permiten archivos .zip, .doc, .docx con un tamaño máximo de 10 MB.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formObservaciones" name="enviar_observaciones" class="btn btn-primary">Enviar Observaciones</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light text-center">
        <div class="container">
            <p class="mb-0">&copy; 2024 Gestoria de Titulación Desarrollo de Software - Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/validarTamañoDocente.js" defer></script>
</body>

</html>

<?php $conn->close(); ?>