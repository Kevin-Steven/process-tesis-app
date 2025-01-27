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
    $tema_id = $_GET['id'];

    // Obtener los detalles del tema, incluyendo el postulante, su pareja (si tiene), y el tema
    $sql = "SELECT t.tema, t.correcciones_tesis, t.usuario_id, t.pareja_id, 
               u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
               pareja.nombres AS pareja_nombres, pareja.apellidos AS pareja_apellidos
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN usuarios pareja ON t.pareja_id = pareja.id
        WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tema = $result->fetch_assoc();
    } else {
        echo "No se encontraron detalles para este tema.";
        exit();
    }
} else {
    echo "No se especificó ningún ID de tema.";
    exit();
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de las correcciones</title>
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
            <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-user'></i> Listado Postulantes</a>
            <a class="nav-link" href="revisar-anteproyecto.php"><i class='bx bx-file'></i> Revisar Anteproyecto</a>
            <a class="nav-link" href="revisar-tesis.php"><i class='bx bx-book-reader'></i> Revisar Tesis</a>
            <a class="nav-link" href="ver-observaciones.php"><i class='bx bx-file'></i> Ver Observaciones</a>
            <a class="nav-link" href="revisar-correcciones-tesis.php"><i class='bx bx-file'></i> Ver Correcciones</a>
            <a class="nav-link" href="revisar-plagio.php"><i class='bx bx-certification'></i> Revisar Plagio</a>
            <a class="nav-link" href="revisar-sustentacion.php"><i class='bx bx-file'></i> Revisar Sustentación</a>
            <a class="nav-link" href="informe.php"><i class='bx bx-file'></i> Informe</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-2">
            <h1 class="mb-4 text-center fw-bold">Detalle de las Correcciones</h1>
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5 class="card-title text-primary text-center fw-bold mb-4">Información de las correcciones</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th><i class="bx bx-user"></i> Postulante</th>
                                    <td><?php echo htmlspecialchars($tema['postulante_nombres'] . ' ' . $tema['postulante_apellidos']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bx bx-user"></i> Pareja</th>
                                    <td>
                                        <?php
                                        if (!empty($tema['pareja_nombres']) && !empty($tema['pareja_apellidos'])) {
                                            echo htmlspecialchars($tema['pareja_nombres'] . ' ' . $tema['pareja_apellidos']);
                                        } else {
                                            echo "Sin pareja";
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><i class="bx bx-book"></i> Tema</th>
                                    <td><?php echo htmlspecialchars($tema['tema']); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bx bx-file"></i> Documento correcciones tesis</th>
                                    <td>
                                        <?php if (!empty($tema['correcciones_tesis'])): ?>
                                            <a class="text-decoration-none d-inline-flex align-items-center" href="../uploads/correcciones/<?php echo urlencode($tema['correcciones_tesis']); ?>" download>
                                                <i class='bx bx-cloud-download'></i> Descargar documento
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-4 formulario-aceptar-rechazar">
                        <button type="button" class="btn aprobar" data-bs-toggle="modal" data-bs-target="#modalConfirmarAprobar">Aprobar</button>
                        <button type="button" class="btn color-rojo" data-bs-toggle="modal" data-bs-target="#modalConfirmarRechazar">Rechazar</button>
                        <button type="button" id="cancelar-btn" class="btn" onclick="history.back()">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Aprobación -->
    <div class="modal fade" id="modalConfirmarAprobar" tabindex="-1" aria-labelledby="modalConfirmarAprobarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="aprobar_correcciones.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalConfirmarAprobarLabel">Confirmar Aprobación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas aprobar las correcciones de esta tesis?
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Aprobar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Confirmar Rechazo -->
    <div class="modal fade" id="modalConfirmarRechazar" tabindex="-1" aria-labelledby="modalConfirmarRechazarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="rechazar_correcciones.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalConfirmarRechazarLabel">Confirmar Rechazo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="motivo_rechazo" class="form-label">Motivo del Rechazo</label>
                            <textarea class="form-control" id="motivo_rechazo" name="motivo_rechazo" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="tema_id" value="<?php echo $tema_id; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Rechazar</button>
                    </div>
                </form>
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
</body>

</html>

<?php $conn->close(); ?>