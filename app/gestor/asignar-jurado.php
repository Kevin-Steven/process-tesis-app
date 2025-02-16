<?php
session_start();
require '../config/config.php';

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

$sql = "SELECT 
        t.id,
        t.sede,
        t.aula,
        t.fecha_sustentar,
        t.hora_sustentar,
        t.tema,
        t.estado_tema,
        u.nombres AS postulante_nombres,
        u.apellidos AS postulante_apellidos,
        p.nombres AS pareja_nombres,
        p.apellidos AS pareja_apellidos,
        j1.nombres AS jurado1_nombre,
        j2.nombres AS jurado2_nombre,
        j3.nombres AS jurado3_nombre
    FROM tema t
    JOIN usuarios u ON t.usuario_id = u.id
    LEFT JOIN usuarios p ON t.pareja_id = p.id
    LEFT JOIN tutores j1 ON t.id_jurado_uno = j1.id
    LEFT JOIN tutores j2 ON t.id_jurado_dos = j2.id
    LEFT JOIN tutores j3 ON t.id_jurado_tres = j3.id
    WHERE t.estado_tema = 'Aprobado'
    AND t.estado_registro = 0
    ORDER BY t.fecha_sustentar ASC, t.hora_sustentar ASC
";


$result = $conn->query($sql);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignar jurado</title>
    <link href="estilos-gestor.css" rel="stylesheet">
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
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1'></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
                            <i class='bx bx-user me-2'></i> Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
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
            <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
            <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
            <a class="nav-link" href="ver-temas.php"><i class='bx bx-book-open'></i> Temas Postulados</a>
            <a class="nav-link" href="ver-temas-aprobados.php"><i class='bx bx-file'></i> Temas aprobados</a>
            <!-- Módulo Informes con submenú -->
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-file'></i> Informes</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse" id="submenuInformes">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'informe-tutor.php' ? 'active bg-secondary' : ''; ?>" href="informe-tutor.php">
                            <i class="bx bx-file"></i> Informe Tutor
                        </a>
                    </li>
                    <li>
                        <a class="nav-link  <?php echo basename($_SERVER['PHP_SELF']) == 'informe-tesis.php' ? 'active bg-secondary' : ''; ?>" href="informe-tesis.php">
                            <i class="bx bx-file"></i> Informe Tesis
                        </a>
                    </li>
                    <li>
                        <a class="nav-link  <?php echo basename($_SERVER['PHP_SELF']) == 'informe-revisor-tesis.php' ? 'active bg-secondary' : ''; ?>" href="informe-revisor-tesis.php">
                            <i class="bx bx-file"></i> Jurado tesis
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link" href="generar-reportes.php"><i class='bx bx-line-chart'></i> Reportes</a>
            <a class="nav-link" href="comunicados.php"><i class='bx bx-message'></i> Comunicados</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-2">
            <h1 class="mb-4 text-center fw-bold">Asignar Jurado</h1>

            <!-- Toast -->
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <?php if ($_GET['status'] === 'success'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Asignación Exitosa</strong>
                            <?php elseif ($_GET['status'] === 'error'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error</strong>
                            <?php elseif ($_GET['status'] === 'error_duplicado'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                                <strong class="me-auto">Jurados Duplicados</strong>
                            <?php elseif ($_GET['status'] === 'form_error'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error en el Formulario</strong>
                            <?php else: ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error Desconocido</strong>
                            <?php endif; ?>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo "Los jurados han sido asignados correctamente.";
                                    break;
                                case 'error':
                                    echo "Ocurrió un error al asignar los jurados.";
                                    break;
                                case 'error_duplicado':
                                    echo "No se pueden asignar jurados duplicados. Por favor, selecciona diferentes jurados.";
                                    break;
                                case 'form_error':
                                    echo "Hubo un error en el envío del formulario. Asegúrate de completar todos los campos.";
                                    break;
                                default:
                                    echo "Ha ocurrido un error desconocido. Inténtalo nuevamente.";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Campo de búsqueda -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class='bx bx-search'></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por tema o postulante">
            </div>

            <!-- Tabla de temas aprobados -->
            <div class="table-responsive">
                <table class="table table-striped" id="temas">
                    <thead>
                        <tr>
                            <th>Tema</th>
                            <th>Estudiante 1</th>
                            <th>Estudiante 2</th>
                            <th>Jurado 1</th>
                            <th>Jurado 2</th>
                            <th>Jurado 3</th>
                            <th>Sede</th>
                            <th>Aula</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tema']); ?></td>
                                    <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                                    <td><?php echo $row['pareja_nombres'] ? htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']) : '<span class="text-muted">No aplica</span>'; ?></td>
                                    <td><?php echo $row['jurado1_nombre'] ? mb_strtoupper(htmlspecialchars($row['jurado1_nombre'])) : '<span class="text-muted">No asignado</span>'; ?></td>
                                    <td><?php echo $row['jurado2_nombre'] ? mb_strtoupper(htmlspecialchars($row['jurado2_nombre'])) : '<span class="text-muted">No asignado</span>'; ?></td>
                                    <td><?php echo $row['jurado3_nombre'] ? mb_strtoupper(htmlspecialchars($row['jurado3_nombre'])) : '<span class="text-muted">No asignado</span>'; ?></td>
                                    <td><?php echo $row['sede'] ? htmlspecialchars($row['sede']) : '<span class="text-muted">No asignado</span>'; ?></td>
                                    <td><?php echo $row['aula'] ? htmlspecialchars($row['aula']) : '<span class="text-muted">No asignado</span>'; ?></td>
                                    <td><?php echo $row['fecha_sustentar'] ? htmlspecialchars($row['fecha_sustentar']) : '<span class="text-muted">No asignado</span>'; ?></td>
                                    <td>
                                        <?php
                                        echo $row['hora_sustentar']
                                            ? date("g:i A", strtotime($row['hora_sustentar']))
                                            : '<span class="text-muted">No asignado</span>';
                                        ?>
                                    </td>

                                    <td class="text-center">
                                        <a href="detalles-asignar-jurado.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                                            <i class='bx bx-search'></i> Ver detalles
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center">No se encontraron temas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    <script src="../js/buscarTema.js" defer></script>
</body>

</html>