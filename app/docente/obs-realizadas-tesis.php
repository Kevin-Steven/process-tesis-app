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

// Consultar el último anteproyecto asignado al docente que tenga el estado de registro en 0 y no tenga observaciones
$sql = "SELECT t.id, t.tema, t.anteproyecto, t.observaciones_tesis, 
               u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
               IF(u.pareja_tesis = -1 OR u.pareja_tesis IS NULL, 'Sin pareja', CONCAT(pareja.nombres, ' ', pareja.apellidos)) AS pareja_nombres_apellidos
        FROM tema t
        JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN usuarios pareja ON u.pareja_tesis = pareja.id
        WHERE t.revisor_tesis_id = ? 
        AND t.estado_registro = 0
        AND (t.usuario_id = IF(u.pareja_tesis = -1, t.usuario_id, LEAST(t.usuario_id, u.pareja_tesis)))
        AND t.observaciones_tesis IS NOT NULL
        AND t.observaciones_tesis != ''
        ORDER BY t.fecha_subida DESC";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tus Observaciones</title>
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
                        <a class="dropdown-item d-flex align-items-center" href="cambioClave.php">
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
            <a class="nav-link" href="revisar-anteproyecto.php"><i class='bx bx-file'></i> Revisar Anteproyecto</a>
            <a class="nav-link" href="revisar-tesis.php"><i class='bx bx-book-reader'></i> Revisar Tesis</a>
            <a class="nav-link" href="ver-observaciones.php"><i class='bx bx-file'></i> Ver Observaciones</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-3">
            <h1 class="text-center mb-4 fw-bold">Revisar Tesis Asignadas</h1>
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <i class='bx bx-send fs-4 me-2'></i>
                            <strong class="me-auto">Estado de Actualización</strong>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo "Observaciones enviadas con éxito.";
                                    break;
                                case 'not_found':
                                    echo "No se encontraron detalles para el postulante.";
                                    break;
                                case 'invalid_extension':
                                    echo "Tipo de archivo no permitido. Solo se aceptan archivos ZIP, DOC y DOCX.";
                                    break;
                                case 'too_large':
                                    echo "El archivo excede el tamaño máximo permitido de 20MB.";
                                    break;
                                case 'db_error':
                                    echo "Error al actualizar la base de datos.";
                                    break;
                                case 'upload_error':
                                    echo "Hubo un error al subir el archivo.";
                                    break;
                                case 'no_file':
                                    echo "Por favor, selecciona un archivo para subir.";
                                    break;
                                case 'form_error':
                                    echo "No se ha enviado el formulario correctamente.";
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

            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-header-fixed">
                            <tr>
                                <th>Postulante</th>
                                <th>Pareja</th>
                                <th>Tema</th>
                                <th>Observaciones</th> 
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($row['pareja_nombres_apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tema']); ?></td>
                                    <td><?php echo htmlspecialchars($row['observaciones_tesis']); ?></td> 
                                    <td class="text-center">
                                        <?php if (!empty($row['anteproyecto'])): ?>
                                            <a href="detalles-observaciones-tesis.php?id=<?php echo $row['id']; ?>" class="text-decoration-none d-flex align-items-center justify-content-center">
                                                <i class='bx bx-search'></i> Ver detalles
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                </div>
            <?php else: ?>
                <p class="text-center">No hay observaciones realizadas.</p>
            <?php endif; ?>

        </div>
    </div>

    <div class="btn-regresarA">
        <a href="ver-observaciones.php" class="regresar-enlace">
            <i class='bx bx-left-arrow-circle'></i>
        </a>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light text-center">
        <div class="container">
            <p class="mb-0">&copy; 2024 Gestoria de Titulación Desarrollo de Software - Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js" defer></script>
    <script src="../js/toast.js" defer></script>
</body>

</html>