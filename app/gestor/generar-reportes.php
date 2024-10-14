<?php
session_start();

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_apellido'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el primer nombre y el primer apellido
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];

// Verificar si la foto de perfil está configurada en la sesión
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generar Reportes</title>
    <link href="estilos-gestor.css" rel="stylesheet">
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
            <!-- Iconos adicionales a la derecha -->
            <i class='bx bx-envelope'></i>
            <i class='bx bx-bell'></i>
            <!-- Menú desplegable para el usuario -->
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i> <!-- Ícono agregado -->
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
                            <i class='bx bx-user me-2'></i> <!-- Ícono para "Perfil" -->
                            Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
                            <i class='bx bx-lock me-2'></i> <!-- Ícono para "Cambio de Clave" -->
                            Cambio de Clave
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
                            <i class='bx bx-log-out me-2'></i> <!-- Ícono para "Cerrar Sesión" -->
                            Cerrar Sesión
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
            <h5><?php echo $primer_nombre . ' ' . $primer_apellido; ?></h5> <!-- Aquí también mostramos solo el primer nombre y primer apellido -->
            <p><?php echo ucfirst($_SESSION['usuario_rol']); ?></p> <!-- Mostramos el rol del usuario -->
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
            <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
            <a class="nav-link" href="ver-temas.php"><i class='bx bx-book-open'></i> Temas Postulados</a>
            <a class="nav-link" href="ver-temas-aprobados.php"><i class='bx bx-file'></i> Temas aprobados</a>
            <a class="nav-link active" href="generar-reportes.php"><i class='bx bx-line-chart'></i> Generar Reportes</a>
            <a class="nav-link" href="comunicados.php"><i class='bx bx-message'></i> Comunicados</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-2">
            <h1 class="mb-5 text-center fw-bold">Generar Reportes</h1>

            <!-- Filas de Tarjetas de Reportes -->
            <div class="row justify-content-center">

                <!-- Tarjeta para generar PDF de postulantes aprobados -->
                <div class="col-md-5 mb-4">
                    <div class="card documentos-aprobados">
                        <div class="card-body text-center">
                            <h5 class="card-title">Postulantes Aprobados</h5>
                            <p class="card-text">Genera un listado en PDF de todos los postulantes con la documentación aprobada.</p>
                            <form action="generar-pdf-postulantes.php" class="generar-reporte" method="post" target="_blank">
                                <button type="submit" class="btn">
                                    <i class="bx bxs-file-pdf"></i> Generar PDF
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta para generar PDF de temas aprobados -->
                <div class="col-md-5 mb-4">
                    <div class="card temas-aprobados ">
                        <div class="card-body text-center">
                            <h5 class="card-title">Temas Aprobados</h5>
                            <p class="card-text">Genera un listado en PDF de todos los temas aprobados registrados en la base de datos.</p>
                            <form action="generar-pdf-temas.php" class="generar-reporte" method="post" target="_blank">
                                <button type="submit" class="btn" id="color-verde">
                                    <i class="bx bxs-file-pdf"></i> Generar PDF
                                </button>
                            </form>
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
    <script src="../js/sidebar.js" defer></script>

</body>

</html>