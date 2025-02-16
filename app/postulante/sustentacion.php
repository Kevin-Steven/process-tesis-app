<?php
session_start();
require '../config/config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener el primer nombre, apellido, y foto de perfil
$primer_nombre = explode(' ', $_SESSION['usuario_nombre'])[0];
$primer_apellido = explode(' ', $_SESSION['usuario_apellido'])[0];
$foto_perfil = isset($_SESSION['usuario_foto']) ? $_SESSION['usuario_foto'] : '../../images/user.png';
$usuario_id = $_SESSION['usuario_id'];

/// Consulta para obtener el estado del tema, de la tesis, y el revisor de tesis
$sql_tema = "SELECT t.estado_tema, 
       t.estado_tesis, 
       t.documento_tesis, 
       t.observaciones_tesis, 
       t.pareja_id, 
       t.motivo_rechazo_correcciones, 
       CONCAT(u.nombres, ' ', u.apellidos) AS revisor_tesis_nombre
FROM tema t
LEFT JOIN usuarios u ON t.revisor_tesis_id = u.id
WHERE t.usuario_id = ? 
ORDER BY t.id DESC 
LIMIT 1
";
$stmt_tema = $conn->prepare($sql_tema);
$stmt_tema->bind_param("i", $usuario_id);
$stmt_tema->execute();
$result_tema = $stmt_tema->get_result();
$tema = $result_tema->fetch_assoc();
$stmt_tema->close();

// Variables del tema y de la tesis
$estado_tema = $tema['estado_tema'] ?? null;
$estado_tesis = $tema['estado_tesis'] ?? null;
$documento_tesis = $tema['documento_tesis'] ?? null;
$observaciones_tesis = $tema['observaciones_tesis'] ?? 'Sin Observaciones';
$pareja_id = $tema['pareja_id'] ?? null;
$motivo_rechazo_correcciones = $tema['motivo_rechazo_correcciones'] ?? null;

// Nombre del revisor de tesis
$revisor_tesis_nombre = $tema['revisor_tesis_nombre'] ?? 'No asignado';


// Obtener el nombre de la pareja si existe
$nombre_pareja = '';
if ($pareja_id) {
    $sql_pareja = "SELECT nombres, apellidos FROM usuarios WHERE id = ?";
    $stmt_pareja = $conn->prepare($sql_pareja);
    $stmt_pareja->bind_param("i", $pareja_id);
    $stmt_pareja->execute();
    $result_pareja = $stmt_pareja->get_result();
    $pareja = $result_pareja->fetch_assoc();
    $stmt_pareja->close();
    $nombre_pareja = $pareja ? $pareja['nombres'] . ' ' . $pareja['apellidos'] : 'No aplica';
}

// Consulta para obtener los nombres de los jurados y sus observaciones
$sql_jurados = "SELECT 
        u1.nombres AS jurado1_nombre, 
        u2.nombres AS jurado2_nombre, 
        u3.nombres AS jurado3_nombre,
        t.j1_nota_sustentar,
        t.j2_nota_sustentar,
        t.j3_nota_sustentar,
        t.sede,
        t.aula,
        t.fecha_sustentar,
        t.hora_sustentar
    FROM tema t
    LEFT JOIN tutores u1 ON t.id_jurado_uno = u1.id
    LEFT JOIN tutores u2 ON t.id_jurado_dos = u2.id
    LEFT JOIN tutores u3 ON t.id_jurado_tres = u3.id
    WHERE t.usuario_id = ? 
    ORDER BY t.id DESC
    LIMIT 1
";

// Preparar y ejecutar la consulta
$stmt_jurados = $conn->prepare($sql_jurados);
$stmt_jurados->bind_param("i", $usuario_id); // $usuario_id es el ID del usuario actual (autor del tema)
$stmt_jurados->execute();
$result_jurados = $stmt_jurados->get_result();
$jurados = $result_jurados->fetch_assoc();
$stmt_jurados->close();

// Obtener los nombres de los jurados y las observaciones
$jurado1_nombre = $jurados['jurado1_nombre'] ?? 'No asignado';
$jurado2_nombre = $jurados['jurado2_nombre'] ?? 'No asignado';
$jurado3_nombre = $jurados['jurado3_nombre'] ?? 'No asignado';

// $observacion_jurado1 = $jurados['obs_jurado_uno'] ?? 'Sin observación';
// $observacion_jurado2 = $jurados['obs_jurado_dos'] ?? 'Sin observación';
// $observacion_jurado3 = $jurados['obs_jurado_tres'] ?? 'Sin observación';

$j1_nota_sustentar = $jurados['j1_nota_sustentar'] ?? 'Sin nota';
$j2_nota_sustentar = $jurados['j2_nota_sustentar'] ?? 'Sin nota';
$j3_nota_sustentar = $jurados['j3_nota_sustentar'] ?? 'Sin nota';

$sede = $jurados['sede'] ?? 'No asignado';
$aula = $jurados['aula'] ?? 'No asignado';
$fecha_sustentar = $jurados['fecha_sustentar'] ?? 'No asignado';
$hora_sustentar = $jurados['hora_sustentar'] ?? 'No asignado';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sustentación</title>
    <link href="estilos.css" rel="stylesheet">
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
                <input type="text" id="search" class="form-control" placeholder="Search">
            </div>
            <i class='bx bx-envelope'></i>
            <i class='bx bx-bell'></i>
            <div class="user-profile dropdown">
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1' id="chevron-icon"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li><a class="dropdown-item d-flex align-items-center" href="perfil.php"><i class='bx bx-user me-2'></i>Perfil</a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="cambioClave.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a></li>
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
            <a class="nav-link" href="inicio-postulante.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link" href="perfil.php"><i class='bx bx-user'></i> Perfil</a>
            <a class="nav-link" href="requisitos.php"><i class='bx bx-cube'></i> Requisitos</a>
            <a class="nav-link" href="inscripcion.php"><i class='bx bx-file'></i> Inscribirse</a>
            <a class="nav-link" href="enviar-tema.php"><i class='bx bx-file'></i> Enviar Tema</a>
            <?php if ($estado_tema === 'Aprobado'): ?>
                <a class="nav-link" href="enviar-documento-tesis.php"><i class='bx bx-file'></i> Documento Tesis</a>
            <?php endif; ?>
            <!--   if ($estado_tesis === 'Aprobado'): ?> -->
            <a class="nav-link" href="estado-plagio.php"><i class='bx bx-file'></i> Antiplagio</a>
            <a class="nav-link active" href="sustentacion.php"><i class='bx bx-file'></i> Sustentacion</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container py-4">
            <h1 class="mb-4 text-center fw-bold">Sustentación</h1>

            <!-- Tabla con jurados y observaciones -->
            <div class="table-responsive">
                <table class="table table-bordered shadow-lg">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Sede</th>
                            <th>Aula</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Jurado 1</th>
                            <th>Jurado 2</th>
                            <th>Jurado 3</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><?php echo mb_strtoupper($sede); ?></td>
                            <td class="text-center"><?php echo mb_strtoupper($aula); ?></td>
                            <td class="text-center"><?php echo mb_strtoupper($fecha_sustentar); ?></td>
                            <td>
                                <?php
                                echo $hora_sustentar
                                    ? date("g:i A", strtotime($hora_sustentar))
                                    : '<span class="text-muted">No asignado</span>';
                                ?>
                            </td>
                            <td class="text-center"><?php echo mb_strtoupper($jurado1_nombre); ?></td>
                            <td class="text-center"><?php echo mb_strtoupper($jurado2_nombre); ?></td>
                            <td class="text-center"><?php echo mb_strtoupper($jurado3_nombre); ?></td>
                            <!-- <td class="text-center">
                                <?php if ($observacion_jurado1 !== 'Sin observación'): ?>
                                    <a href="<?php echo $observacion_jurado1; ?>" download>Descargar</a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($observacion_jurado2 !== 'Sin observación'): ?>
                                    <a href="<?php echo $observacion_jurado2; ?>" download>Descargar</a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($observacion_jurado3 !== 'Sin observación'): ?>
                                    <a href="<?php echo $observacion_jurado3; ?>" download>Descargar</a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td> -->
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