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

$sql = "SELECT t.id, t.tema, t.enlace_plagio as link, t.estado_tema, t.fecha_subida, t.tutor_id, t.documento_tesis,
       u.nombres AS postulante_nombres, u.apellidos AS postulante_apellidos, 
       p.nombres AS pareja_nombres, t.anteproyecto, t.observaciones_anteproyecto, t.observaciones_tesis, 
       t.rubrica_calificacion as doc_calificacion, t.certificados as tesis_certificado, t.doc_plagio as cert_plagio, 
       p.apellidos AS pareja_apellidos, up.nombres as nombres_plagio, up.apellidos as apellidos_plagio,
       tutores.nombres AS tutor_nombre, p.id AS pareja_id, t.correcciones_tesis as tesis, t.estado_tesis,
       urt.nombres as nombres_rev_tesis, urt.apellidos as apellidos_rev_tesis, t.nota_revisor_tesis as nota_documento, u1.nombres AS jurado1_nombre, 
       u2.nombres AS jurado2_nombre, u3.nombres AS jurado3_nombre, t.j1_nota_sustentar as nota_uno,t.j2_nota_sustentar as nota_dos,t.j3_nota_sustentar as nota_tres,
       t.j1_nota_sustentar_2 as nota_uno_2, t.j2_nota_sustentar_2 as nota_dos_2, t.j3_nota_sustentar_2 as nota_tres_2
FROM tema t
LEFT JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN usuarios up ON t.id_revisor_plagio = up.id
LEFT JOIN usuarios urt ON t.revisor_tesis_id = urt.id
LEFT JOIN usuarios p ON t.pareja_id = p.id
LEFT JOIN tutores ON t.tutor_id = tutores.id
LEFT JOIN tutores u1 ON t.id_jurado_uno = u1.id
LEFT JOIN tutores u2 ON t.id_jurado_dos = u2.id
LEFT JOIN tutores u3 ON t.id_jurado_tres = u3.id
WHERE t.estado_tema = 'Aprobado' 
AND t.estado_registro = 0;
";

$result = $conn->query($sql);

?>

<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Temas Aprobados</title>
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
        <div class="d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil">
          <span><?php echo $primer_nombre . ' ' . $primer_apellido; ?></span>
          <i class='bx bx-chevron-down ms-1'></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-end mt-2">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="perfil-gestor.php">
              <i class='bx bx-user me-2'></i> Perfil
            </a>
          </li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="cambio-clave-gestor.php">
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
      <h1 class="mb-4 text-center fw-bold">Temas Aprobados</h1>

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
              <?php elseif ($_GET['status'] === 'invalid_tutor'): ?>
                <i class='bx bx-error-circle fs-4 me-2 text-warning'></i>
                <strong class="me-auto">Tutor Inválido</strong>
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
                  echo "El tutor ha sido actualizado correctamente.";
                  break;
                case 'not_found':
                  echo "No se encontró el tema.";
                  break;
                case 'invalid_tutor':
                  echo "El tutor seleccionado no es válido.";
                  break;
                case 'form_error':
                  echo "Hubo un error en el envío del formulario.";
                  break;
                default:
                  echo "Ha ocurrido un error desconocido.";
                  break;
              }
              ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Campo de búsqueda -->
      <div class="input-group mb-3">
        <span class="input-group-text"><i class='bx bx-search'></i></span>
        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por tema, postulante o tutor">
      </div>

      <!-- Tabla de temas aprobados -->
      <div class="table-responsive">
        <div class="table-responsive">
          <table class="table table-striped" id="temas">
            <thead class="table-header-fixed">
              <tr>
                <th>Tema</th>
                <th>Estudiante 1</th>
                <th>Estudiante 2</th>
                <th>Tutor</th>
                <th>Anteproyecto</th>
                <th>Observaciones Anteproyecto</th>
                <th>Documento Tesis</th>
                <th>Observaciones Tesis</th>
                <th>Tesis Corregida</th>
                <th>Rubrica de calificación</th>
                <th>Certificado Revisor Tesis</th>
                <th>Revisor Tesis</th>
                <th>Informe de plagio</th>
                <th>Certificado AntiPlagio</th>
                <th>Fiscal Plagio</th>
                <th>Jurado 1</th>
                <th>Jurado 2</th>
                <th>Jurado 3</th>
                <th>Nota Documento</th>
                <th>Nota Exposición Estudiante 1</th>
                <th>Nota Exposición Estudiante 2</th>
                <th>Nota Final Titulación Estudiante 1</th>
                <th>Nota Final Titulación Estudiante 2</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['tema']); ?></td>
                    <td><?php echo htmlspecialchars($row['postulante_nombres'] . ' ' . $row['postulante_apellidos']); ?></td>
                    <td><?php echo $row['pareja_nombres'] ? htmlspecialchars($row['pareja_nombres'] . ' ' . $row['pareja_apellidos']) : '<span class="text-muted">No aplica</span>'; ?></td>
                    <td><?php echo mb_strtoupper($row['tutor_nombre']); ?></td>
                    <td>
                      <?php if (!empty($row['anteproyecto'])): ?>
                        <a href="<?php echo '../uploads/' . htmlspecialchars($row['anteproyecto']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($row['observaciones_anteproyecto'])): ?>
                        <a href="<?php echo '../uploads/observaciones/' . htmlspecialchars($row['observaciones_anteproyecto']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($row['documento_tesis'])): ?>
                        <a href="<?php echo '../uploads/documento-tesis/' . htmlspecialchars($row['documento_tesis']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($row['observaciones_tesis']) && file_exists("../uploads/observaciones-tesis/" . $row['observaciones_tesis'])): ?>
                        <a href="descargar.php?archivo=<?php echo urlencode($row['observaciones_tesis']); ?>" class="text-decoration-none">Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <?php if (!empty($row['tesis']) && $row['estado_tesis'] === "Aprobado"): ?>
                        <a href="<?php echo '../uploads/correcciones/' . htmlspecialchars($row['tesis']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($row['doc_calificacion'])): ?>
                        <a href="<?php echo htmlspecialchars($row['doc_calificacion']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($row['tesis_certificado'])): ?>
                        <a href="<?php echo htmlspecialchars($row['tesis_certificado']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                      if (!empty($row['nombres_rev_tesis']) && !empty($row['apellidos_rev_tesis'])) {
                        echo htmlspecialchars($row['nombres_rev_tesis'] . ' ' . $row['apellidos_rev_tesis']);
                      } else {
                        echo '<span class="text-muted">No asignado</span>';
                      }
                      ?>
                    </td>

                    <td>
                      <?php if (!empty($row['link'])): ?>
                        <a href="<?php echo htmlspecialchars($row['link']); ?>" target="_blank">Enlace</a>
                      <?php else: ?>
                        <span class="text-muted">No hay enlace</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <?php if (!empty($row['cert_plagio'])): ?>
                        <a href="<?php echo htmlspecialchars($row['cert_plagio']); ?>" target="_blank" download>Descargar</a>
                      <?php else: ?>
                        <span class="text-muted">No hay documentos</span>
                      <?php endif; ?>
                    </td>
                    <td> <!--Revisor de plagio-->
                      <?php
                      if (!empty($row['nombres_plagio']) && !empty($row['apellidos_plagio'])) {
                        echo htmlspecialchars($row['nombres_plagio'] . ' ' . $row['apellidos_plagio']);
                      } else {
                        echo '<span class="text-muted">No asignado</span>';
                      }
                      ?>
                    </td>
                    <td>
                      <?php
                      if (!empty($row['jurado1_nombre'])) {
                        echo mb_strtoupper($row['jurado1_nombre']);
                      } else {
                        echo '<span class="text-muted">No asignado</span>';
                      }
                      ?>
                    </td>
                    <td>
                      <?php
                      if (!empty($row['jurado2_nombre'])) {
                        echo mb_strtoupper($row['jurado2_nombre']);
                      } else {
                        echo '<span class="text-muted">No asignado</span>';
                      }
                      ?>
                    </td>
                    <td>
                      <?php
                      if (!empty($row['jurado3_nombre'])) {
                        echo mb_strtoupper($row['jurado3_nombre']);
                      } else {
                        echo '<span class="text-muted">No asignado</span>';
                      }
                      ?>
                    </td>

                    <td>
                      <?php
                      if (!empty($row['nota_documento'])) {
                        $nota_documento = floatval($row['nota_documento']);
                        $nota_equivalente_documento = ($nota_documento / 10) * 6;
                        echo number_format($nota_documento, 2);
                      } else {
                        $nota_documento = null;
                        $nota_equivalente_documento = null;
                        echo '<span class="text-muted">Sin nota</span>';
                      }
                      ?>
                    </td>

                    <td>
                      <?php
                      $nota1 = isset($row['nota_uno']) ? floatval($row['nota_uno']) : null;
                      $nota2 = isset($row['nota_dos']) ? floatval($row['nota_dos']) : null;
                      $nota3 = isset($row['nota_tres']) ? floatval($row['nota_tres']) : null;

                      if ($nota1 !== null && $nota2 !== null && $nota3 !== null) {
                        $promedio_sustentacion = ($nota1 + $nota2 + $nota3) / 3;
                        $nota_equivalente_sustentacion = ($promedio_sustentacion / 10) * 4;
                        echo number_format($promedio_sustentacion, 2);
                      } else {
                        $nota_equivalente_sustentacion = null;
                        echo '<span class="text-muted">Sin nota</span>';
                      }
                      ?>
                    </td>

                    <td>
                      <?php
                      $nota_uno_2 = isset($row['nota_uno_2']) ? floatval($row['nota_uno_2']) : null;
                      $nota_dos_2 = isset($row['nota_dos_2']) ? floatval($row['nota_dos_2']) : null;
                      $nota_tres_2 = isset($row['nota_tres_2']) ? floatval($row['nota_tres_2']) : null;

                      if ($nota_uno_2 !== null && $nota_dos_2 !== null && $nota_tres_2 !== null) {
                        $promedio_sustentacion_2 = ($nota_uno_2 + $nota_dos_2 + $nota_tres_2) / 3;
                        $nota_equivalente_sustentacion_2 = ($promedio_sustentacion_2 / 10) * 4;
                        echo number_format($promedio_sustentacion_2, 2);
                      } else {
                        $nota_equivalente_sustentacion_2 = null;
                        echo '<span class="text-muted">Sin nota</span>';
                      }
                      ?>
                    </td>

                    <td>
                      <?php
                      if ($nota_equivalente_documento !== null && $nota_equivalente_sustentacion !== null) {
                        $nota_final = $nota_equivalente_documento + $nota_equivalente_sustentacion;
                        echo number_format($nota_final, 2);
                      } else {
                        echo '<span class="text-muted">Sin nota final</span>';
                      }
                      ?>
                    </td>

                    <td>
                      <?php
                      if ($nota_equivalente_documento !== null && $nota_equivalente_sustentacion_2 !== null) {
                        $nota_final_2 = $nota_equivalente_documento + $nota_equivalente_sustentacion_2;
                        echo number_format($nota_final_2, 2);
                      } else {
                        echo '<span class="text-muted">Sin nota final</span>';
                      }
                      ?>
                    </td>

                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        <!-- Botón Ver detalles -->
                        <button type="button" class="btn btn-primary" onclick="window.location.href='editar-tutor-ap.php?id=<?php echo $row['id']; ?>'">
                          <i class='bx bx-search-alt-2'></i>
                        </button>

                        <!-- Botón Imprimir -->
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalImprimir<?php echo $row['id']; ?>">
                          <i class='bx bx-printer'></i>
                        </button>

                        <!-- Botón para ajustar el tema -->
                        <button type="button" class="btn btn-warning" onclick="window.location.href='editar-tema.php?id=<?php echo $row['id']; ?>'">
                          <i class='bx bx-edit-alt'></i>
                        </button>

                        <!-- Botón para generar word -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalWord<?php echo $row['id']; ?>">
                          <i class='bx bx-file'></i>
                        </button>
                      </div>
                    </td>

                    <div class="modal fade" id="modalImprimir<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalImprimirLabel<?php echo $row['id']; ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form action="generar-acta-titulacion.php" method="GET" target="_blank">
                            <div class="modal-header">
                              <h5 class="modal-title" id="modalImprimirLabel<?php echo $row['id']; ?>">Desea imprimir la acta?</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <p>Se generará un documento en formato PDF con la información del acta de titulación.</p>
                              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                              <button type="submit" class="btn btn-primary">Imprimir</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="modalWord<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalWordLabel<?php echo $row['id']; ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form action="generar-acta-titulacion-word.php" method="GET" target="_blank">
                            <div class="modal-header">
                              <h5 class="modal-title" id="modalImprimirLabel<?php echo $row['id']; ?>">Desea generar la acta?</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <p>Se generará un documento en formato Word con la información del acta de titulación.</p>
                              <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                              <button type="submit" class="btn btn-primary">Generar</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="24" class="text-center">No se encontraron temas aprobados.</td>
                </tr>
              <?php endif; ?>
              <!-- Fila para "No se encontraron resultados" -->
              <tr id="noResultsRow" style="display: none;">
                <td colspan="24" class="text-center">No se encontraron resultados.</td>
              </tr>
            </tbody>
          </table>
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
  <script src="../js/buscarTema.js" defer></script>
</body>

</html>