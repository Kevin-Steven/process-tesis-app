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

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignar Revisores</title>
    <link href="estilos-gestor.css" rel="stylesheet">
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
                <input type="text" id="search" class="form-control" placeholder="Search">
            </div>
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li><a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php"><i class='bx bx-user me-2'></i> Perfil</a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php"><i class='bx bx-lock me-2'></i> Cambio de Clave</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php"><i class='bx bx-log-out me-2'></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </div>

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
            <a class="nav-link" href="generar-reportes.php"><i class='bx bx-line-chart'></i> Generar Reportes</a>
            <a class="nav-link" href="comunicados.php"><i class='bx bx-message'></i> Comunicados</a>
        </nav>
    </div>

    <div class="content" id="content">
        <div class="container mt-2">
            <h1 class="mb-4 text-center fw-bold">Asignar Revisor de Tesis</h1>
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <i class='bx bx-send fs-4 me-2'></i>
                            <strong class="me-auto">Estado de Asignación</strong>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo "El revisor ha sido asignado correctamente.";
                                    break;
                                case 'not_found':
                                    echo "No se encontró el tema especificado.";
                                    break;
                                case 'db_error':
                                    echo "Error al actualizar la base de datos.";
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

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-header-fixed">
                        <tr>
                            <th>Tema</th>
                            <th>Revisor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta para temas aprobados
                        $sql_temas_aprobados = "
                        SELECT t.id, t.tema, 
                               CONCAT(r.nombres, ' ', r.apellidos) AS revisor
                        FROM tema t
                        LEFT JOIN usuarios r ON t.revisor_tesis_id = r.id
                        WHERE t.estado_tema = 'Aprobado' 
                        AND t.estado_registro = 0
                        AND (t.pareja_id IS NULL OR t.pareja_id = -1 OR t.usuario_id < t.pareja_id)";
                        $result_temas_aprobados = $conn->query($sql_temas_aprobados);

                        if ($result_temas_aprobados->num_rows > 0) {
                            while ($tema = $result_temas_aprobados->fetch_assoc()) {
                                $revisor = $tema['revisor'] ? htmlspecialchars($tema['revisor']) : 'Revisor no asignado';
                                echo "<tr>
                                    <td>" . htmlspecialchars($tema['tema']) . "</td>
                                    <td>" . $revisor . "</td>
                                    <td class='text-center'>
                                        <a href='detalle-revisor-tesis.php?id=" . $tema['id'] . "' class='text-decoration-none d-flex align-items-center justify-content-center'>
                                            <i class='bx bx-search'></i> Ver detalles
                                        </a>
                                    </td>
                                  </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No hay temas aprobados</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="btn-regresarA">
        <a href="asignar-revisores.php" class="regresar-enlace">
            <i class='bx bx-left-arrow-circle'></i>
        </a>
    </div>

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