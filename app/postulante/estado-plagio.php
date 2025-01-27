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

// Consulta para verificar el estado de la tesis
$sql_estado_tesis = "SELECT estado_tesis FROM tema WHERE usuario_id = ? LIMIT 1";
$stmt_estado_tesis = $conn->prepare($sql_estado_tesis);
$stmt_estado_tesis->bind_param("i", $usuario_id);
$stmt_estado_tesis->execute();
$result_estado_tesis = $stmt_estado_tesis->get_result();
$estado_tesis = $result_estado_tesis->fetch_assoc()['estado_tesis'] ?? null;
$stmt_estado_tesis->close();

// Consulta para obtener los datos del revisor (nombre y foto)
$sql_revisor = "SELECT u.nombres, u.apellidos, t.doc_plagio FROM usuarios u
                INNER JOIN tema t ON t.id_revisor_plagio = u.id
                WHERE t.usuario_id = ? LIMIT 1";
$stmt_revisor = $conn->prepare($sql_revisor);
$stmt_revisor->bind_param("i", $usuario_id);
$stmt_revisor->execute();
$result_revisor = $stmt_revisor->get_result();
$revisor = $result_revisor->fetch_assoc();
$stmt_revisor->close();

?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estado Plagio</title>
    <link href="estilos.css" rel="stylesheet">
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
            <a class="nav-link" href="enviar-documento-tesis.php"><i class='bx bx-file'></i> Documento Tesis</a>
            <?php if ($estado_tesis === 'Aprobado'): ?>
                <a class="nav-link active" href="estado-plagio.php"><i class='bx bx-file'></i> Documento Plagio</a>
                <a class="nav-link" href="sustentacion.php"><i class='bx bx-file'></i> Sustentacion</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container py-4">
            <h1 class="mb-4 text-center fw-bold">Estado de Revisión de Plagio</h1>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-bordered shadow-lg">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Revisor de Plagio</th>
                            <th>Documentos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <?php if ($revisor): ?>
                                    <?php echo $revisor['nombres'] . ' ' . $revisor['apellidos']; ?>
                                <?php else: ?>
                                    <span class="text-muted">No asignado</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($revisor['doc_plagio'])): ?>
                                    <a class="text-decoration-none d-inline-flex align-items-center" href="../uploads/documento-plagio/<?php echo basename($revisor['doc_plagio']); ?>" download>
                                        Descargar
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No hay documentos</span>
                                <?php endif; ?>
                            </td>
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