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

/// Verificar que el usuario es un docente
$sql_docente = "SELECT cedula FROM usuarios WHERE id = ? AND rol = 'docente'";
$stmt_docente = $conn->prepare($sql_docente);
$stmt_docente->bind_param("i", $usuario_id);
$stmt_docente->execute();
$result_docente = $stmt_docente->get_result();
$docente = $result_docente->fetch_assoc();

if ($docente) {
    // Consulta para obtener las tesis asignadas al revisor actual
    $sql_temas = "SELECT 
    t.id, 
    t.tema, 
    t.documento_tesis, 
    t.anteproyecto,
    t.revisor_anteproyecto_id,
    t.revisor_tesis_id,
    t.id_revisor_plagio,
    u.nombres AS postulante_nombres, 
    u.apellidos AS postulante_apellidos, 
    p.nombres AS pareja_nombres, 
    p.apellidos AS pareja_apellidos,
    tu1.cedula AS cedula_jurado_1, 
    tu2.cedula AS cedula_jurado_2, 
    tu3.cedula AS cedula_jurado_3,
    t.id_jurado_uno AS j1,
    t.id_jurado_dos AS j2,
    t.id_jurado_tres AS j3
FROM tema t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
LEFT JOIN tutores tu1 ON t.id_jurado_uno = tu1.id
LEFT JOIN tutores tu2 ON t.id_jurado_dos = tu2.id
LEFT JOIN tutores tu3 ON t.id_jurado_tres = tu3.id
WHERE 
    (t.revisor_anteproyecto_id = ? OR t.revisor_tesis_id = ? OR t.id_revisor_plagio = ? OR tu1.cedula = ? OR tu2.cedula = ? OR tu3.cedula = ?)
    AND t.estado_tema = 'Aprobado'
    AND t.estado_registro = 0
ORDER BY t.fecha_subida DESC";


    $stmt_temas = $conn->prepare($sql_temas);
    $stmt_temas->bind_param("iiiiii", $usuario_id, $usuario_id, $usuario_id, $docente, $docente, $docente);
    $stmt_temas->execute();
    $result_temas = $stmt_temas->get_result();
} else {
    echo "No se encontró el docente.";
    exit();
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listado Postulantes</title>
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
            <a class="nav-link active" href="listado-postulantes.php"><i class='bx bx-user'></i> Listado Postulantes</a>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuAnteproyecto" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-file'></i> Anteproyecto</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse" id="submenuAnteproyecto">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-anteproyecto.php' ? 'active bg-secondary' : ''; ?>" href="revisar-anteproyecto.php">
                            <i class="bx bx-file"></i> Revisar
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'obs-realizadas-anteproyecto.php' ? 'active bg-secondary' : ''; ?>" href="obs-realizadas-anteproyecto.php">
                            <i class="bx bx-file"></i> Observaciones
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#RevisarTesis" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-file'></i> Tesis</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse" id="RevisarTesis">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-tesis.php' ? 'active bg-secondary' : ''; ?>" href="revisar-tesis.php">
                            <i class="bx bx-book-reader"></i> Revisar
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'obs-realizadas-tesis.php' ? 'active bg-secondary' : ''; ?>" href="obs-realizadas-tesis.php">
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
                            <i class="bx bx-file"></i> Rubrica Calificación
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'certificados.php' ? 'active bg-secondary' : ''; ?>" href="certificados.php">
                            <i class='bx bx-certification'></i> Certificado revisor
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuPlagio" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-certification'></i> Plagio</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse" id="submenuPlagio">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-plagio.php' ? 'active bg-secondary' : ''; ?>" href="revisar-plagio.php">
                            <i class="bx bx-file"></i> Revisar
                        </a>
                    </li>
                </ul>
            </div>
            <a class="nav-link collapsed d-flex justify-content-between align-items-center" href="#submenuSustentacion" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="submenuInformes">
                <span><i class='bx bx-book-open'></i> Sustentación</span>
                <i class="bx bx-chevron-down"></i>
            </a>
            <div class="collapse" id="submenuSustentacion">
                <ul class="list-unstyled ps-4">
                    <li>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'revisar-sustentacion.php' ? 'active bg-secondary' : ''; ?>" href="revisar-sustentacion.php">
                            <i class="bx bx-file"></i> Revisar
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
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-3">

            <h1 class="text-center mb-4 fw-bold">Listado de Postulantes Asignados</h1>

            <?php if ($result_temas && $result_temas->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-header-fixed">
                            <tr>
                                <th>Tema</th>
                                <th>Estudiante 1</th>
                                <th>Estudiante 2</th>
                                <th>Anteproyecto</th>
                                <th>Documento Tesis</th>
                                <th>Revisor Antiplagio</th>
                                <th>Jurado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_temas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['tema']); ?></td>

                                    <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                                    <td>
                                        <?php if (!empty($row['pareja_nombres']) && !empty($row['pareja_apellidos'])): ?>
                                            <?php echo htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No aplica</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['revisor_anteproyecto_id'] == $usuario_id) {
                                            echo '<span class="badge bg-success"><i class="bx bx-check"></i> Asignado</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary"><i class="bx bx-x"></i> No asignado</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['revisor_tesis_id'] == $usuario_id) {
                                            echo '<span class="badge bg-success"><i class="bx bx-check"></i> Asignado</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary"><i class="bx bx-x"></i> No asignado</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['id_revisor_plagio'] == $usuario_id) {
                                            echo '<span class="badge bg-success"><i class="bx bx-check"></i> Asignado</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary"><i class="bx bx-x"></i> No asignado</span>';
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        // Cédula del docente actual
                                        $cedula_docente = $docente['cedula'];

                                        // Obtener el ID del tema y los jurados del tema actual
                                        $tema_id = $row['id']; // Asegúrate de que $row contenga el ID del tema

                                        // Consulta para obtener las cédulas de los tres jurados basados en los IDs de los jurados
                                        $sql_jurados = "SELECT 
                                    tu1.cedula AS cedula_jurado_1,
                                    tu2.cedula AS cedula_jurado_2,
                                    tu3.cedula AS cedula_jurado_3,
                                    t.obs_jurado_uno,
                                    t.obs_jurado_dos,
                                    t.obs_jurado_tres
                                  FROM tema t
                                  LEFT JOIN tutores tu1 ON t.id_jurado_uno = tu1.id
                                  LEFT JOIN tutores tu2 ON t.id_jurado_dos = tu2.id
                                  LEFT JOIN tutores tu3 ON t.id_jurado_tres = tu3.id
                                  WHERE t.id = ?";

                                        // Preparamos la consulta y vinculamos el ID del tema
                                        $stmt_jurados = $conn->prepare($sql_jurados);
                                        $stmt_jurados->bind_param("i", $tema_id); // Asumimos que $tema_id es un entero
                                        $stmt_jurados->execute();
                                        $result_jurados = $stmt_jurados->get_result();

                                        // Verificamos si obtenemos datos
                                        if ($result_jurados->num_rows > 0) {
                                            $row_jurados = $result_jurados->fetch_assoc();

                                            // Extraemos las cédulas de los jurados
                                            $cedula_jurado_1 = $row_jurados['cedula_jurado_1'];
                                            $cedula_jurado_2 = $row_jurados['cedula_jurado_2'];
                                            $cedula_jurado_3 = $row_jurados['cedula_jurado_3'];

                                            // Comprobamos qué observación mostrar dependiendo de la cédula del docente
                                            $asignad = "";
                                            if ($cedula_docente == $cedula_jurado_1) {
                                                $asignad = True;
                                            } elseif ($cedula_docente == $cedula_jurado_2) {
                                                $asignad = True;
                                            } elseif ($cedula_docente == $cedula_jurado_3) {
                                                $asignad = True;
                                            }

                                            // Si el archivo está especificado y existe, mostrar el enlace de descarga
                                            if ($asignad):
                                        ?>
                                                <span class="badge bg-success"><i class="bx bx-check"></i> Asignado</span>
                                        <?php
                                            else:
                                                // Si no hay archivo o no existe, mostramos un mensaje
                                                echo '<span class="badge bg-secondary"><i class="bx bx-x"></i> No asignado</span>';
                                            endif;
                                        } else {
                                            echo '<span class="text-muted">Datos de jurados no encontrados</span>';
                                        }
                                        ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No hay Postulantes asignados.</p>
            <?php endif; ?>
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