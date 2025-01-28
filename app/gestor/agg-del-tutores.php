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

$sql_tutores = "SELECT id, nombres FROM tutores";
$result_tutores = $conn->query($sql_tutores);

?>



<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle Asignar Revisor</title>
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

    <!-- Content -->
    <div class="content" id="content">
        <div class="container mt-2">
            <!-- Toast -->
            <?php if (isset($_GET['status'])): ?>
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <?php
                            // Filtrar el valor para evitar problemas con inyecciones
                            $status = htmlspecialchars($_GET['status']);

                            // Seleccionar el ícono y el mensaje según el estado
                            if ($status === 'success'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Acción Exitosa</strong>
                            <?php elseif ($status === 'error'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error</strong>
                            <?php elseif ($status === 'repeat-ci'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                                <strong class="me-auto">Cédula Duplicada</strong>
                            <?php elseif ($status === 'empty-tutor'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Tutor no Seleccionado</strong>
                            <?php elseif ($status === 'empty-fields'): ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Campos vacios</strong>
                            <?php elseif ($status === 'tutor_deleted'): ?>
                                <i class='bx bx-check-circle fs-4 me-2 text-success'></i>
                                <strong class="me-auto">Tutor Eliminado</strong>
                            <?php else: ?>
                                <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                                <strong class="me-auto">Error Desconocido</strong>
                            <?php endif; ?>
                            <small>Justo ahora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <?php
                            switch ($status) {
                                case 'success':
                                    echo "El tutor ha sido agregado correctamente.";
                                    break;
                                case 'error':
                                    echo "Ocurrió un error al agregar el tutor.";
                                    break;
                                case 'repeat-ci':
                                    echo "La cédula ya está registrada. Por favor, ingresa una cédula diferente.";
                                    break;
                                case 'empty-tutor':
                                    echo "No has seleccionado un tutor para eliminar. Intenta nuevamente.";
                                    break;
                                case 'tutor_deleted':
                                    echo "El tutor ha sido eliminado exitosamente.";
                                    break;
                                default:
                                    echo "Ha ocurrido un error desconocido. Inténtalo nuevamente.";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <h1 class="mb-4 text-center fw-bold">Gestión de Tutores</h1>

            <div class="row g-4">
                <!-- Agregar Tutor -->
                <div class="col-md-12">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold mb-3 text-center">Agregar Tutor</h5>
                            <form id="formAgregarTutor" method="POST" action="logica-agregar-tutores.php">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre del Tutor</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cedula" class="form-label fw-bold">Cédula</label>
                                    <input type="text" name="cedula" id="cedula" maxlength="10" class="form-control" required oninput="validateInput(this)">
                                </div>
                                <div class="text-center formulario-aceptar-rechazar">
                                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#modalConfirmarAgregarTutor">Agregar Tutor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de confirmación para agregar tutor -->
                <div class="modal fade" id="modalConfirmarAgregarTutor" tabindex="-1" aria-labelledby="modalConfirmarActualizarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalConfirmarActualizarLabel">Confirmar Agregar Tutor</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ¿Estás seguro de que deseas agregar un nuevo tutor?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('formAgregarTutor').submit();">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- Eliminar Tutor 
                <div class="col-md-6">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h5 class="card-title text-danger fw-bold mb-3">Eliminar Tutor</h5>
                            <form id="formEliminarTutor" method="POST" action="logica-borrar-tutores.php">
                                <div class="mb-3">
                                    <label for="tutor_id" class="form-label fw-bold">Seleccionar Tutor para Eliminar</label>
                                    <select class="form-select" id="tutor_id" name="tutor_id" required>
                                        <option value="">Seleccionar tutor</option>
                                        <?php while ($tutor = $result_tutores->fetch_assoc()): ?>
                                            <option value="<?php echo $tutor['id']; ?>">
                                                <?php echo htmlspecialchars($tutor['nombres']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="text-center formulario-aceptar-rechazar">
                                    <button type="button" class="btn color-rojo" data-bs-toggle="modal" data-bs-target="#modalConfirmarEliminarTutor">Eliminar Tutor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                Modal de confirmación para eliminar tutor
                <div class="modal fade" id="modalConfirmarEliminarTutor" tabindex="-1" aria-labelledby="modalConfirmarActualizarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalConfirmarActualizarLabel">Confirmar Eliminar Tutor</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ¿Estás seguro de que deseas eliminar el tutor seleccionado?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('formEliminarTutor').submit();">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div> -->
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
    <script src="/app/js/number.js" defer></script>

</body>

</html>

<?php $conn->close(); ?>