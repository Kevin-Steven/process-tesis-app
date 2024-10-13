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

$usuario_actual_id = $_SESSION['usuario_id'];

// Obtener la lista de usuarios de la base de datos, excluyendo al administrador actual
$sql = "SELECT id, nombres, apellidos, rol FROM usuarios WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_actual_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modificar Rol</title>
    <link href="estilos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <!-- Topbar con ícono de menú hamburguesa -->
    <div class="topbar z-1">
        <div class="menu-toggle">
            <i class='bx bx-menu'></i>
        </div>
        <div class="topbar-right">
            <div class="input-group search-bar">
                <span class="input-group-text" id="search-icon"><i class='bx bx-search'></i></span>
                <input type="text" id="search" class="form-control" placeholder="Buscar">
            </div>
            <i class='bx bx-envelope'></i>
            <i class='bx bx-bell'></i>
            <!-- Menú desplegable para el usuario -->
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
                        <a class="dropdown-item d-flex align-items-center" href="cambioClave.php">
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
            <a class="nav-link" href="inicio-administrador.php"><i class='bx bx-home-alt'></i> Inicio</a>
            <a class="nav-link active" href="modificar-rol.php"><i class='bx bx-user'></i> Modificar Rol</a>
        </nav>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <div class="container-fluid py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">

                    <div style="min-height: 60px;"> 
                        <?php if (isset($_GET['status'])): ?>
                            <div class="alert alert-<?php echo $_GET['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                <?php if ($_GET['status'] == 'success'): ?>
                                    Rol actualizado correctamente.
                                <?php elseif ($_GET['status'] == 'error'): ?>
                                    Hubo un error al actualizar el rol.
                                <?php else: ?>
                                    Solicitud inválida.
                                <?php endif; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h2 class="mb-4 fw-bold text-center">Modificar Rol de Usuarios</h2>

                            <!-- Formulario para modificar rol -->
                            <form action="procesar-modificar-rol.php" method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="usuario" class="form-label fw-bold">Seleccionar Usuario</label>
                                    <select class="form-select" id="usuario" name="usuario_id" required>
                                        <option value="">Seleccione un usuario</option>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <option value="<?php echo $row['id']; ?>">
                                                    <?php echo $row['nombres'] . ' ' . $row['apellidos'] . ' (' . ucfirst($row['rol']) . ')'; ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="mb-5">
                                    <label for="rol" class="form-label fw-bold">Nuevo Rol</label>
                                    <select class="form-select" id="rol" name="nuevo_rol" required>
                                        <option value="">Seleccione un rol</option>
                                        <option value="administrador">Administrador</option>
                                        <option value="gestor">Gestor</option>
                                        <option value="postulante">Postulante</option>
                                        <option value="docente">Docente</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn w-100">Modificar Rol</button>
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
            <p class="mb-0">&copy; 2024 Gestoria de titulación - Instituto Superior Tecnológico Juan Bautista Aguirre.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/sidebar.js" defer></script>
</body>

</html>