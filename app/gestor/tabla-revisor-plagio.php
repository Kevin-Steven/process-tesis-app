<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener los temas aprobados junto con el tutor y revisor de tesis
$sql_temas_aprobados = $sql_temas_aprobados = "SELECT 
    t.id, 
    t.tema, 
    tu.nombres AS tutor_nombre,
    CONCAT(r.nombres, ' ', r.apellidos) AS revisor,
    CONCAT(u.nombres, ' ', u.apellidos) AS postulante,
    CONCAT(p.nombres, ' ', p.apellidos) AS pareja
FROM tema t
LEFT JOIN tutores tu ON t.tutor_id = tu.id
LEFT JOIN usuarios r ON t.id_revisor_plagio = r.id
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
WHERE t.estado_tema = 'Aprobado' 
AND t.estado_registro = 0";


$result_temas_aprobados = $conn->query($sql_temas_aprobados);
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

    <div class="content" id="content">
        <div class="container mt-2">
            <h1 class="mb-4 text-center fw-bold">Asignar Revisor de Plagio</h1>

            <!-- Campo de búsqueda -->
            <div class="input-group mb-3">
                <span class="input-group-text"><i class='bx bx-search'></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar por tema, postulante o tutor">
            </div>

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
                <table class="table table-striped" id="temas">
                    <thead class="table-header-fixed">
                        <tr>
                            <th>Tema</th>
                            <th>Estudiante 1</th>
                            <th>Estudiante 2</th>
                            <th>Tutor</th>
                            <th>Revisor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_temas_aprobados->num_rows > 0) {
                        while ($tema = $result_temas_aprobados->fetch_assoc()) {
                                $tema2 = !empty($tema['tema']);
                                $tutor_nombre = !empty($tema['tutor_nombre']) ? mb_strtoupper($tema['tutor_nombre'], 'UTF-8') : '<span class="text-muted">Tutor no asignado</span>';
                                $revisor = !empty($tema['revisor']) ? htmlspecialchars($tema['revisor']) : '<span class="text-muted">Revisor no asignado</span>';
                                $postulante = !empty($tema['postulante']) ? htmlspecialchars($tema['postulante']) : '<span class="text-muted">Sin postulante</span>';
                                $pareja = !empty($tema['pareja']) ? htmlspecialchars($tema['pareja']) : '<span class="text-muted">Sin pareja</span>';

                                echo "<tr>
                    <td>" . htmlspecialchars($tema['tema']) . "</td>
                    <td>{$postulante}</td>
                    <td>{$pareja}</td>
                    <td>{$tutor_nombre}</td>
                    <td>{$revisor}</td>
                    <td class='text-center'>
                        <a href='detalle-revisor-plagio.php?id={$tema['id']}' class='text-decoration-none d-flex align-items-center justify-content-center'>
                            <i class='bx bx-search'></i> Ver detalles
                        </a>
                    </td>
                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No hay temas aprobados</td></tr>";
                        }
                        ?>
                    </tbody>

                </table>
            </div>
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
    <script src="../js/sidebar.js"></script>
    <script src="../js/toast.js" defer></script>
    <script src="../js/buscarTema.js" defer></script>

</body>

</html>