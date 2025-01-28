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

// Consultas para obtener los postulantes de anteproyecto y de tesis, y los revisores
$sql_temas_anteproyecto = "SELECT t.id, u.nombres, u.apellidos, t.pareja_id 
                           FROM tema t
                           JOIN usuarios u ON t.usuario_id = u.id
                           WHERE t.estado_tema = 'Pendiente' 
                           AND t.estado_registro = 0
                           AND t.revisor_anteproyecto_id IS NULL"; // Verificar que no tengan revisor asignado
$result_temas_anteproyecto = $conn->query($sql_temas_anteproyecto);
if (!$result_temas_anteproyecto) {
    die("Error al obtener postulantes de anteproyecto: " . $conn->error);
}

$sql_temas_tesis = "SELECT t.id, u.nombres, u.apellidos, t.pareja_id 
                    FROM tema t
                    JOIN usuarios u ON t.usuario_id = u.id
                    WHERE t.documento_tesis IS NOT NULL
                    AND t.revisor_tesis_id IS NULL"; // Verificar que no tengan revisor asignado
$result_temas_tesis = $conn->query($sql_temas_tesis);
if (!$result_temas_tesis) {
    die("Error al obtener postulantes de tesis: " . $conn->error);
}


$sql_revisores = "SELECT id, nombres, apellidos FROM usuarios WHERE rol = 'docente'";
$result_revisores = $conn->query($sql_revisores);
if (!$result_revisores) {
    die("Error al obtener revisores: " . $conn->error);
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
        <div class="container-fluid py-2">
            <div class="row justify-content-center">
                <div class="col-md-10 text-center mb-5">
                    <h1 class="fw-bold">Asignación de Revisores</h1>
                    <p class="lead">Selecciona y asigna revisores para la evaluación de los temas de anteproyecto y tesis, asegurando un proceso de revisión exhaustivo y de calidad.</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card text-center mb-4 border border-secondary rounded-3 shadow-md">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Asignar Revisor de Anteproyecto</h5>
                            <p class="card-text mb-4">Asigna un revisor para evaluar el anteproyecto de un tema de tesis y proporcionar retroalimentación.</p>
                            <a href="tabla-revisor-anteproyecto.php" class="btn">Acceder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card text-center mb-4 border border-secondary rounded-3 shadow-md">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Asignar Revisor de Tesis</h5>
                            <p class="card-text mb-4">Designa un revisor para la evaluación del documento final de la tesis y garantizar su calidad académica.</p>
                            <a href="tabla-revisor-tesis.php" class="btn">Acceder</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card text-center mb-4 border border-secondary rounded-3 shadow-md">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Asignar Revisor de Plagio</h5>
                            <p class="card-text mb-4">Designa un revisor para verificar la originalidad del documento final y asegurar su calidad académica.</p>
                            <a href="tabla-revisor-plagio.php" class="btn">Acceder</a>
                        </div>
                    </div>
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
</body>

</html>