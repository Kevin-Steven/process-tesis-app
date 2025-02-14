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

// Obtener el ID del tema desde la URL
if (isset($_GET['id'])) {
    $tema_id = intval($_GET['id']);

    // Consulta para obtener los detalles del tema
    $sql = "SELECT t.*, 
                u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
                p.id AS pareja_id, p.nombres AS pareja_nombres, p.apellidos AS pareja_apellidos, 
                tut.nombres AS tutor_nombres
            FROM tema t
            JOIN usuarios u ON t.usuario_id = u.id
            LEFT JOIN usuarios p ON t.pareja_id = p.id
            JOIN tutores tut ON t.tutor_id = tut.id
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tema = $result->fetch_assoc();
    } else {
        echo "No se encontraron detalles para este tema.";
        exit();
    }
} else {
    echo "No se especificó ningún ID de tema.";
    exit();
}

// Obtener datos de la pareja actual
$pareja_actual = null;
if (!empty($tema['pareja_id'])) {
    $sql_pareja_actual = "SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre_completo FROM usuarios WHERE id = ?";
    $stmt_pareja_actual = $conn->prepare($sql_pareja_actual);
    $stmt_pareja_actual->bind_param("i", $tema['pareja_id']);
    $stmt_pareja_actual->execute();
    $result_pareja_actual = $stmt_pareja_actual->get_result();
    if ($result_pareja_actual->num_rows > 0) {
        $pareja_actual = $result_pareja_actual->fetch_assoc();
    }
}

// Obtener lista de postulantes sin pareja (excluyendo al usuario actual y su pareja actual)
$sql_parejas = "SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre_completo 
                FROM usuarios 
                WHERE rol = 'postulante' AND id != ?";

$stmt_parejas = $conn->prepare($sql_parejas);
$stmt_parejas->bind_param("i", $tema['usuario_id']);
$stmt_parejas->execute();
$result_parejas = $stmt_parejas->get_result();

?>


<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Tema</title>
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
                <div class="d-flex align-items-center" data-bs-toggle="dropdown" id="user-profile-toggle" aria-expanded="false">
                    <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
                    <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
                    <i class='bx bx-chevron-down ms-1'></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li><a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php"><i class='bx bx-user me-2'></i>Perfil</a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php"><i class='bx bx-lock me-2'></i>Cambio de Clave</a></li>
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
            <a class="nav-link" href="inicio-gestor.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link" href="ver-inscripciones.php"><i class='bx bx-user'></i> Ver Inscripciones</a>
            <a class="nav-link" href="listado-postulantes.php"><i class='bx bx-file'></i> Listado Postulantes</a>
            <a class="nav-link" href="ver-temas.php"><i class='bx bx-book-open'></i> Temas Postulados</a>
            <a class="nav-link active" href="ver-temas-aprobados.php"><i class='bx bx-file'></i> Temas aprobados</a>
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
            <h1 class="mb-4 text-center fw-bold">Editar Tema</h1>
            <!-- Toast -->
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <?php if ($_GET['status'] === 'success'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Actualización Exitosa</strong>
                            <?php elseif ($_GET['status'] === 'not_found'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error</strong>
                            <?php elseif ($_GET['status'] === 'form_error'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error en el Formulario</strong>
                            <?php endif; ?>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($_GET['status']) {
                                case 'success':
                                    echo "El tema ha sido actualizado correctamente.";
                                    break;
                                case 'not_found':
                                    echo "No se encontró el tema. Por favor, verifica el ID.";
                                    break;
                                case 'form_error':
                                    echo "Hubo un error al procesar la actualización del tema. Inténtalo nuevamente.";
                                    break;
                                default:
                                    echo "Ha ocurrido un error inesperado.";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>


            <!-- Ajustar el ancho del card -->
            <div class="card shadow-lg mx-auto">
                <div class="card-body">

                    <!-- Detalles del Tema -->
                    <h5 class="card-title text-primary text-center fw-bold">Información del Tema</h5>
                    <div class="table-responsive">
                        <form id="formActualizarTema" action="actualizar-tema-postulante.php" method="POST">
                            <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">
                            <input type="hidden" name="postulante_id" value="<?php echo $tema['usuario_id']; ?>">
                            <input type="hidden" name="pareja_id" value="<?php echo $tema['pareja_id']; ?>">

                            <table class="table">
                                <tbody>
                                    <?php
                                    $sql_parejas_repetido = "SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre_completo 
                          FROM usuarios 
                          WHERE rol = 'postulante' AND id != ?";
                                    $stmt_parejas_repetido = $conn->prepare($sql_parejas_repetido);
                                    $stmt_parejas_repetido->bind_param("i", $tema['usuario_id']);
                                    $stmt_parejas_repetido->execute();
                                    $result_parejas_repetido = $stmt_parejas_repetido->get_result();
                                    ?>

                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-user"></i> Postulante</th>
                                        <td>
                                            <select class="form-select" id="postulante_id" name="postulante_id" required>
                                                <option value="<?php echo htmlspecialchars($tema['usuario_id']); ?>" selected>
                                                    <?php echo htmlspecialchars($tema['postulante_nombres'] . ' ' . $tema['postulante_apellidos']); ?> (Principal)
                                                </option>

                                                <?php while ($row = $result_parejas_repetido->fetch_assoc()): ?>
                                                    <?php if ($row['id'] != $tema['usuario_id']): ?>
                                                        <option value="<?php echo htmlspecialchars($row['id']); ?>">
                                                            <?php echo htmlspecialchars($row['nombre_completo']); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-user"></i> Pareja</th>
                                        <td>
                                            <select class="form-select" id="pareja_id" name="pareja_id" required>
                                                <!-- Mostrar "Sin Pareja" como seleccionado si la pareja_id es -1 -->
                                                <option value="-1" <?php echo ($tema['pareja_id'] == -1) ? 'selected' : ''; ?>>Sin Pareja</option>

                                                <!-- Mostrar la pareja actual si existe y no es -1 -->
                                                <?php if ($pareja_actual && $tema['pareja_id'] != -1): ?>
                                                    <option value="<?php echo $pareja_actual['id']; ?>" selected>
                                                        <?php echo $pareja_actual['nombre_completo']; ?> (Pareja Actual)
                                                    </option>
                                                <?php endif; ?>

                                                <!-- Mostrar el listado de parejas disponibles, indicando si ya tienen pareja -->
                                                <?php
                                                $sql_parejas = "SELECT u.id, CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo, u.pareja_tesis 
                            FROM usuarios u 
                            WHERE u.rol = 'postulante' AND u.id != ?";
                                                $stmt_parejas = $conn->prepare($sql_parejas);
                                                $stmt_parejas->bind_param("i", $tema['usuario_id']);
                                                $stmt_parejas->execute();
                                                $result_parejas = $stmt_parejas->get_result();

                                                while ($row = $result_parejas->fetch_assoc()):
                                                    $ya_tiene_pareja = ($row['pareja_tesis'] != 0 && $row['pareja_tesis'] != -1); // Si tiene pareja, marcarlo
                                                ?>
                                                    <option value="<?php echo $row['id']; ?>" <?php echo ($ya_tiene_pareja ? 'data-ocupado="true"' : ''); ?>>
                                                        <?php echo htmlspecialchars($row['nombre_completo']); ?>
                                                        <?php echo ($ya_tiene_pareja ? ' (Ya tiene pareja)' : ''); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </td>
                                    </tr>


                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-file"></i> Tema</th>
                                        <td>
                                            <textarea class="form-control" name="tema" rows="2" required><?php echo htmlspecialchars($tema['tema']); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo General</th>
                                        <td>
                                            <textarea class="form-control" name="objetivo_general" rows="2" required><?php echo htmlspecialchars($tema['objetivo_general']); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo Específico 1</th>
                                        <td>
                                            <textarea class="form-control" name="objetivo_especifico_uno" rows="2" required><?php echo htmlspecialchars($tema['objetivo_especifico_uno']); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo Específico 2</th>
                                        <td>
                                            <textarea class="form-control" name="objetivo_especifico_dos" rows="2" required><?php echo htmlspecialchars($tema['objetivo_especifico_dos']); ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="tabla-anchura-th"><i class="bx bx-target-lock"></i> Objetivo Específico 3</th>
                                        <td>
                                            <textarea class="form-control" name="objetivo_especifico_tres" rows="2" required><?php echo htmlspecialchars($tema['objetivo_especifico_tres']); ?></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Botones -->
                            <div class="text-center mt-3 botones-detalle-tema">
                                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalConfirmacion">
                                    Actualizar
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='ver-temas-aprobados.php'">
                                    Regresar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmación -->
            <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalConfirmacionLabel">Confirmar Actualización</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ¿Estás seguro de que deseas actualizar la información del tema?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" form="formActualizarTema" class="btn btn-primary">Sí, actualizar</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const parejaSelect = document.getElementById("pareja_id");
                    const modalConfirmacionCambio = new bootstrap.Modal(document.getElementById("modalConfirmacionCambio"));
                    const btnConfirmarCambio = document.getElementById("btnConfirmarCambio");

                    let continuarCambio = false;

                    parejaSelect.addEventListener("change", function() {
                        if (continuarCambio) {
                            continuarCambio = false; // Evita que el modal se muestre de nuevo si ya fue confirmado
                            return;
                        }

                        const selectedOption = parejaSelect.options[parejaSelect.selectedIndex];

                        if (selectedOption.hasAttribute("data-ocupado")) {
                            document.getElementById("parejaNombre").innerText = selectedOption.text;
                            modalConfirmacionCambio.show();
                        }
                    });

                    btnConfirmarCambio.addEventListener("click", function() {
                        continuarCambio = true; // Permitir el cambio después de confirmar
                        modalConfirmacionCambio.hide(); // Cerrar el modal y permitir continuar con las modificaciones
                    });
                });
            </script>

            <!-- Modal de Confirmación -->
            <div class="modal fade" id="modalConfirmacionCambio" tabindex="-1" aria-labelledby="modalConfirmacionCambioLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalConfirmacionCambioLabel">Advertencia</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>El postulante <strong id="parejaNombre"></strong> ya tiene una pareja asignada. ¿Deseas continuar con el cambio?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnConfirmarCambio">Sí, continuar</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- Modal Actualizar -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAprobarLabel">Confirmar Cambio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas actualizar este tema?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <!-- Formulario para aprobar el tema -->
                    <form action="actualizar-tema-postulante.php" method="POST">
                        <input type="hidden" name="tema_id" value="<?php echo $tema['id']; ?>">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </form>
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
    <script src="../js/toast.js"></script>

</body>

</html>

<?php $conn->close(); ?>