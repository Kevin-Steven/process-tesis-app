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

$sql_tutores = "SELECT id, nombres, cedula, estado FROM tutores";
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
                        <strong class="me-auto">Tutor Agregado</strong>
                    <?php elseif ($status === 'updated'): ?>
                        <i class='bx bx-check-circle fs-4 me-2 text-primary'></i>
                        <strong class="me-auto">Tutor Actualizado</strong>
                    <?php elseif ($status === 'deleted'): ?>
                        <i class='bx bx-trash-alt fs-4 me-2 text-warning'></i>
                        <strong class="me-auto">Tutor Eliminado</strong>
                    <?php elseif ($status === 'repeat-ci'): ?>
                        <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                        <strong class="me-auto">Cédula Duplicada</strong>
                    <?php elseif ($status === 'empty-fields'): ?>
                        <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                        <strong class="me-auto">Campos Vacíos</strong>
                    <?php elseif ($status === 'error'): ?>
                        <i class='bx bx-error-circle fs-4 me-2 text-danger'></i>
                        <strong class="me-auto">Error</strong>
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
                        case 'updated':
                            echo "Los datos del tutor han sido actualizados correctamente.";
                            break;
                        case 'deleted':
                            echo "El tutor ha sido eliminado (borrado lógico).";
                            break;
                        case 'repeat-ci':
                            echo "La cédula ya está registrada. Ingresa una cédula diferente.";
                            break;
                        case 'empty-fields':
                            echo "Todos los campos son obligatorios. Por favor, completa el formulario.";
                            break;
                        case 'error':
                            echo "Ocurrió un error inesperado. Inténtalo de nuevo.";
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
    <div class="content" id="content">
        <div class="container mt-2">

            <h1 class="mb-4 text-center fw-bold">Gestión de Tutores</h1>
            
            <div class="row g-4">
                <!-- Agregar Tutor -->
                <div class="col-md-5">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold mb-3 text-center">Agregar Tutor</h5>
                            <form id="formAgregarTutor" method="POST" action="logica-agregar-tutores.php">
                                <input type="hidden" name="accion" value="agregar">
                                
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-bold">Nombre del Tutor</label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Apellidos y Nombres" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cedula" class="form-label fw-bold">Cédula</label>
                                    <input type="text" name="cedula" id="cedula" maxlength="10" class="form-control" placeholder="Ingrese la cédula" required oninput="validateInput(this)">
                                </div>
                                
                                <div class="text-center formulario-aceptar-rechazar">
                                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#modalConfirmarAgregarTutor">Agregar Tutor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Modal de confirmación para agregar tutor -->
                <div class="modal fade" id="modalConfirmarAgregarTutor" tabindex="-1" aria-labelledby="modalConfirmarAgregarTutorLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalConfirmarAgregarTutorLabel">Confirmar Agregar Tutor</h5>
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
                
                <!-- Tabla de Tutores -->
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tutores">
                            <thead class="table-header-fixed">
                                <tr>
                                    <th>Tutores</th>
                                    <th>Cédula</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result_tutores->fetch_assoc()): ?>
                                    <?php if ($row['estado'] === '0'): ?>
                                        <tr>
                                            <td><?php echo mb_strtoupper($row['nombres']); ?></td>
                                            <td><?php echo mb_strtoupper($row['cedula']); ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <!-- Botón Editar -->
                                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar<?php echo $row['id']; ?>">
                                                        <i class='bx bx-edit-alt'></i>
                                                    </button>

                                                    <!-- Botón Eliminar -->
                                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar<?php echo $row['id']; ?>">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="modalEditar<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="logica-agregar-tutores.php" method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Tutor</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="accion" value="editar">
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                                            <div class="mb-3">
                                                                <label for="nombre-<?php echo $row['id']; ?>" class="form-label">Nombre del Tutor</label>
                                                                <input type="text" name="nombre" id="nombre-<?php echo $row['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($row['nombres']); ?>" required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="cedula-<?php echo $row['id']; ?>" class="form-label">Cédula</label>
                                                                <input type="text" name="cedula" id="cedula-<?php echo $row['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($row['cedula']); ?>" required maxlength="10">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Eliminar -->
                                        <div class="modal fade" id="modalEliminar<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalEliminarLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="logica-agregar-tutores.php" method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Eliminar Tutor</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            ¿Estás seguro de que deseas eliminar al tutor <strong><?php echo htmlspecialchars($row['nombres']); ?></strong>?
                                                            <input type="hidden" name="accion" value="eliminar">
                                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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
    <script src="/app/js/number.js" defer></script>

</body>

</html>

<?php $conn->close(); ?>