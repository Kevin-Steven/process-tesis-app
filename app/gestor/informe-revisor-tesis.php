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

// Consulta para obtener los temas aprobados con estado de registro 0
$sql = "SELECT t.id, t.tema, t.estado_tema, t.fecha_subida, t.tutor_id, 
           t.id_jurado_uno, t.id_jurado_dos, t.id_jurado_tres, 
           u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
           p.nombres AS pareja_nombres, t.anteproyecto, t.observaciones_anteproyecto, t.observaciones_tesis, 
           p.apellidos AS pareja_apellidos, 
           tutores.nombres AS tutor_nombre, p.id AS pareja_id, 
           u1.nombres AS jurado1_nombre, 
        u2.nombres AS jurado2_nombre, 
        u3.nombres AS jurado3_nombre
    FROM tema t
    JOIN usuarios u ON t.usuario_id = u.id
    LEFT JOIN usuarios p ON t.pareja_id = p.id
    LEFT JOIN tutores u1 ON t.id_jurado_uno = u1.id
    LEFT JOIN tutores u2 ON t.id_jurado_dos = u2.id
    LEFT JOIN tutores u3 ON t.id_jurado_tres = u3.id
    JOIN tutores ON t.tutor_id = tutores.id
    WHERE t.estado_tema = 'Aprobado' 
    AND t.estado_registro = 0";

$result = $conn->query($sql);

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informe revisor tesis</title>
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
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
                            <i class='bx bx-user me-2'></i>
                            Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
                            <i class='bx bx-lock me-2'></i>
                            Cambio de Clave
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="../cerrar-sesion/logout.php">
                            <i class='bx bx-log-out me-2'></i>
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
            <div class="collapse show" id="submenuInformes">
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
            <h1 class="mb-3 text-center fw-bold">Observaciones Sustentación</h1>

            <!-- Tabla de informes -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tema</th>
                            <th>Jurado 1</th>
                            <th>Jurado 2</th>
                            <th>Jurado 3</th>
                            <th>Estudiante 1</th>
                            <th>Estudiante 2</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tema']); ?></td>
                                    <td><?php echo $row['jurado1_nombre'] ? htmlspecialchars(mb_strtoupper($row['jurado1_nombre'])) : 'No asignado';?></td>
                                    <td><?php echo $row['jurado2_nombre'] ? htmlspecialchars(mb_strtoupper($row['jurado2_nombre'])) : 'No asignado';?></td>
                                    <td><?php echo $row['jurado3_nombre'] ? htmlspecialchars(mb_strtoupper($row['jurado3_nombre'])) : 'No asignado';?></td>
                                    
                                    </td>
                                    <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                                    <td><?php echo $row['pareja_nombres'] ? htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']) : 'No aplica'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron registros.</td>
                            </tr>
                        <?php endif; ?>
                        <!-- Fila para "No se encontraron resultados" -->
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="8" class="text-center">No se encontraron resultados.</td>
                        </tr>
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

</body>

</html>