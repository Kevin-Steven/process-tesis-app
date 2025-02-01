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

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ver Observaciones</title>
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
                <input type="text" id="search" class="form-control" placeholder="Search">
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
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#RevisarTesis" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-file'></i> Tesis</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse show" id="RevisarTesis">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-tesis.php">
                            <i class="bx bx-book-reader"></i> Revisar Tesis
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ver-observaciones.php' ? 'active bg-secondary' : ''; ?>" href="ver-observaciones.php">
                            <i class="bx bx-file"></i> Observaciones
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-correcciones-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-correcciones-tesis.php">
                            <i class="bx bx-file"></i> Correcciones
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rubrica-calificacion.php' ? 'active bg-secondary' : ''; ?>" href="rubrica-calificacion.php">
                            <i class="bx bx-file"></i> Calificación
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuInformes" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-file'></i> Informes</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse" id="submenuInformes">
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
        <div class="container-fluid py-3">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="mb-3 fw-bold">Revisa las Observaciones Realizadas</h1>
                    <p class="lead mb-5">Desde este panel, podrás revisar las observaciones que ya has realizado a los anteproyectos y tesis. Además, tendrás la opción de actualizar o corregir cualquier observación en caso de haber cometido un error al enviarla.</p>


                    <!-- Cards con acciones rápidas -->
                    <div class="row justify-content-center">
                        <div class="col-md-6 mb-3">
                            <div class="card card-principal h-100 shadow">
                                <div class="card-body text-center">
                                    <i class='bx bx-book bx-lg mb-3'></i>
                                    <h5 class="card-title fw-bold mb-3">Ver Observaciones del Anteproyecto</h5>
                                    <p class="card-text mb-4">Revisa las observaciones realizadas sobre el anteproyecto.</p>
                                    <a href="obs-realizadas-anteproyecto.php" class="btn">Acceder</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card card-principal h-100 shadow">
                                <div class="card-body text-center">
                                    <i class='bx bx-file bx-lg mb-3'></i>
                                    <h5 class="card-title fw-bold mb-3">Ver Observaciones del Documento de Tesis</h5>
                                    <p class="card-text mb-4">Revisa las observaciones realizadas sobre el documento de tesis.</p>
                                    <a href="obs-realizadas-tesis.php" class="btn">Acceder</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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
</body>

</html>