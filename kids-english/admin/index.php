<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

require_once "../conexion/conexion.php";

$msg = '';
$error = '';

/* =========================================================
   FUNCIONES PARA SUBIR ARCHIVOS SIN ESCRIBIR RUTAS
   ========================================================= */
function limpiarNombre($texto) {
    $texto = trim((string) $texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}

function guardarArchivo($archivo, $carpeta, $nombreBase, $tipo) {
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se pudo subir el archivo seleccionado.');
    }

    $max = $tipo === 'imagen' ? 5 * 1024 * 1024 : 10 * 1024 * 1024;
    if ((int) $archivo['size'] > $max) {
        throw new Exception($tipo === 'imagen'
            ? 'La imagen supera el máximo de 5 MB.'
            : 'El audio supera el máximo de 10 MB.');
    }

    $permitidosImagen = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    $permitidosAudio = [
        'audio/wav'      => 'wav',
        'audio/x-wav'    => 'wav',
        'audio/mpeg'     => 'mp3',
        'audio/ogg'      => 'ogg',
        'audio/mp4'      => 'm4a',
        'audio/x-m4a'    => 'm4a'
    ];

    $permitidos = $tipo === 'imagen' ? $permitidosImagen : $permitidosAudio;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if (!isset($permitidos[$mime])) {
        throw new Exception($tipo === 'imagen'
            ? 'Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.'
            : 'Formato de audio no permitido. Usa WAV, MP3, OGG o M4A.');
    }

    $extension = $permitidos[$mime];
    $nombre = limpiarNombre($nombreBase);
    if ($nombre === '') {
        $nombre = $tipo === 'imagen' ? 'imagen' : 'audio';
    }

    $directorio = __DIR__ . '/../' . trim($carpeta, '/') . '/';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
        throw new Exception('No se pudo crear la carpeta de archivos.');
    }

    $archivoFinal = $nombre . '.' . $extension;
    $contador = 2;
    while (file_exists($directorio . $archivoFinal)) {
        $archivoFinal = $nombre . '-' . $contador . '.' . $extension;
        $contador++;
    }

    if (!move_uploaded_file($archivo['tmp_name'], $directorio . $archivoFinal)) {
        throw new Exception('No se pudo guardar el archivo en el servidor.');
    }

    return trim($carpeta, '/') . '/' . $archivoFinal;
}

function carpetaCategoria($nombre) {
    $slug = limpiarNombre($nombre);
    if ($slug === 'numero' || $slug === 'numeros') {
        return 'numeros';
    }
    if ($slug === 'vocal' || $slug === 'vocales') {
        return 'vocales';
    }
    if ($slug === 'color' || $slug === 'colores') {
        return 'colores';
    }
    return $slug !== '' ? $slug : 'otros';
}

/* =========================================================
   ACTIVAR / INACTIVAR
   ========================================================= */
if (isset($_GET['toggle_categoria'])) {
    $id = (int) $_GET['toggle_categoria'];
    $stmt = $conexion->prepare("UPDATE categorias SET estado = IF(estado = 1, 0, 1) WHERE id_categoria = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: index.php?ok=categoria');
    exit;
}

if (isset($_GET['toggle_leccion'])) {
    $id = (int) $_GET['toggle_leccion'];
    $stmt = $conexion->prepare("UPDATE lecciones SET estado = IF(estado = 1, 0, 1) WHERE id_leccion = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: index.php?ok=leccion');
    exit;
}

/* =========================================================
   ELIMINAR
   ========================================================= */
if (isset($_GET['delete_leccion'])) {
    $id = (int) $_GET['delete_leccion'];
    $stmt = $conexion->prepare("DELETE FROM lecciones WHERE id_leccion = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: index.php?ok=delete_leccion');
    exit;
}

if (isset($_GET['delete_categoria'])) {
    $id = (int) $_GET['delete_categoria'];
    $stmt = $conexion->prepare("DELETE FROM categorias WHERE id_categoria = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: index.php?ok=delete_categoria');
    exit;
}

if (isset($_GET['ok'])) {
    $mensajes = [
        'categoria' => 'Estado de la categoría actualizado.',
        'leccion' => 'Estado de la lección actualizado.',
        'delete_leccion' => 'Lección eliminada.',
        'delete_categoria' => 'Categoría eliminada.'
    ];
    $msg = $mensajes[$_GET['ok']] ?? 'Operación realizada correctamente.';
}

/* =========================================================
   GUARDAR CATEGORÍA
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tipo'] ?? '') === 'categoria') {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = isset($_POST['estado']) ? 1 : 0;

    if ($nombre === '') {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        try {
            $icono = trim($_POST['icono_actual'] ?? '');

            if (!empty($_FILES['imagen_categoria']['name'])) {
                $carpeta = 'assets/img/categorias';
                $icono = guardarArchivo(
                    $_FILES['imagen_categoria'],
                    $carpeta,
                    $nombre,
                    'imagen'
                );
            }

            if ($id > 0) {
                $stmt = $conexion->prepare(
                    "UPDATE categorias SET nombre = ?, descripcion = ?, icono = ?, estado = ? WHERE id_categoria = ?"
                );
                $stmt->bind_param('sssii', $nombre, $descripcion, $icono, $estado, $id);
                $stmt->execute();
                $msg = 'Categoría actualizada correctamente.';
            } else {
                $stmt = $conexion->prepare(
                    "INSERT INTO categorias (nombre, descripcion, icono, estado) VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param('sssi', $nombre, $descripcion, $icono, $estado);
                $stmt->execute();
                $msg = 'Categoría agregada correctamente.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

/* =========================================================
   GUARDAR LECCIÓN + IMAGEN + AUDIO
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tipo'] ?? '') === 'leccion') {
    $id = (int) ($_POST['id'] ?? 0);
    $categoria = (int) ($_POST['categoria'] ?? 0);
    $es = trim($_POST['es'] ?? '');
    $en = trim($_POST['en'] ?? '');
    $pron = trim($_POST['pron'] ?? '');
    $imagen = trim($_POST['imagen_actual'] ?? '');
    $audio = trim($_POST['audio_actual'] ?? '');
    $orden = max(1, (int) ($_POST['orden'] ?? 1));
    $estado = isset($_POST['estado']) ? 1 : 0;

    if ($categoria <= 0 || $es === '' || $en === '') {
        $error = 'Debes seleccionar una categoría y completar Español e Inglés.';
    } else {
        try {
            $stmtCat = $conexion->prepare("SELECT nombre FROM categorias WHERE id_categoria = ? LIMIT 1");
            $stmtCat->bind_param('i', $categoria);
            $stmtCat->execute();
            $catData = $stmtCat->get_result()->fetch_assoc();

            if (!$catData) {
                throw new Exception('La categoría seleccionada no existe.');
            }

            $carpeta = carpetaCategoria($catData['nombre']);
            $nombreArchivo = $en !== '' ? $en : $es;

            if (!empty($_FILES['imagen']['name'])) {
                $imagen = guardarArchivo(
                    $_FILES['imagen'],
                    'assets/img/' . $carpeta,
                    $nombreArchivo,
                    'imagen'
                );
            }

            if (!empty($_FILES['audio']['name'])) {
                $audio = guardarArchivo(
                    $_FILES['audio'],
                    'assets/audio/' . $carpeta,
                    $nombreArchivo,
                    'audio'
                );
            }

            if ($id > 0) {
                $stmt = $conexion->prepare(
                    "UPDATE lecciones SET id_categoria = ?, palabra_espanol = ?, palabra_ingles = ?, pronunciacion = ?, ruta_imagen = ?, ruta_audio = ?, orden = ?, estado = ? WHERE id_leccion = ?"
                );
                $stmt->bind_param('isssssiii', $categoria, $es, $en, $pron, $imagen, $audio, $orden, $estado, $id);
                $stmt->execute();
                $msg = 'Lección actualizada correctamente.';
            } else {
                $stmt = $conexion->prepare(
                    "INSERT INTO lecciones (id_categoria, palabra_espanol, palabra_ingles, pronunciacion, ruta_imagen, ruta_audio, orden, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('isssssii', $categoria, $es, $en, $pron, $imagen, $audio, $orden, $estado);
                $stmt->execute();
                $msg = 'Lección agregada correctamente.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$cats = $conexion->query("SELECT * FROM categorias ORDER BY id_categoria")->fetch_all(MYSQLI_ASSOC);

$ls = $conexion->query(
    "SELECT l.*, c.nombre AS categoria, c.estado AS categoria_estado
     FROM lecciones l
     INNER JOIN categorias c ON c.id_categoria = l.id_categoria
     ORDER BY c.id_categoria, l.orden, l.id_leccion"
)->fetch_all(MYSQLI_ASSOC);

$editCat = null;
if (isset($_GET['edit_cat'])) {
    $id = (int) $_GET['edit_cat'];
    foreach ($cats as $cat) {
        if ((int) $cat['id_categoria'] === $id) {
            $editCat = $cat;
            break;
        }
    }
}

$editL = null;
if (isset($_GET['edit_l'])) {
    $id = (int) $_GET['edit_l'];
    foreach ($ls as $lesson) {
        if ((int) $lesson['id_leccion'] === $id) {
            $editL = $lesson;
            break;
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin - Kids English</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/kids.css">
</head>
<body>

<nav class="navbar navbar-dark admin-nav">
    <div class="container">
        <span class="navbar-brand">🌟 Kids English Admin</span>
        <div>
            <a class="btn btn-outline-light btn-sm me-2" href="../index.php">Ver sitio</a>
            <a class="btn btn-danger btn-sm" href="logout.php">🚪 Cerrar sesión</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2>Panel de administración 👋</h2>
            <p class="text-secondary mb-0">
                Agrega contenido sin escribir rutas de archivos.
            </p>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success">✅ <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="alert alert-info shadow-sm">
        <strong>💡 Fácil de usar:</strong>
        para una nueva lección solo escribe Español e Inglés y pulsa
        <strong>Elegir imagen</strong> y <strong>Elegir audio</strong>.
        El sistema organiza los archivos automáticamente.
    </div>

    <div class="row g-4">

        <!-- CATEGORÍAS -->
        <div class="col-lg-4">
            <div class="card admin-card shadow p-3 h-100">
                <h4>📚 Categorías</h4>
                <p class="text-secondary">Crea, edita, activa o inactiva categorías.</p>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="tipo" value="categoria">
                    <input type="hidden" name="id" value="<?php echo $editCat['id_categoria'] ?? 0; ?>">
                    <input type="hidden" name="icono_actual" value="<?php echo htmlspecialchars($editCat['icono'] ?? ''); ?>">

                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control mb-2" required
                           value="<?php echo htmlspecialchars($editCat['nombre'] ?? ''); ?>"
                           placeholder="Ej: Colores">

                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control mb-2"
                              placeholder="Descripción de la categoría"><?php echo htmlspecialchars($editCat['descripcion'] ?? ''); ?></textarea>

                    <label class="form-label">🖼️ Imagen de la categoría</label>
                    <input type="file" name="imagen_categoria" class="form-control mb-2"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="text-secondary d-block mb-3">JPG, PNG, GIF o WEBP · máximo 5 MB.</small>

                    <?php if (!empty($editCat['icono']) && file_exists(__DIR__ . '/../' . $editCat['icono'])): ?>
                        <img src="../<?php echo htmlspecialchars($editCat['icono']); ?>"
                             class="admin-preview mb-3" alt="Imagen actual">
                    <?php endif; ?>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="estado" id="estadoCategoria"
                            <?php echo (!$editCat || (int) $editCat['estado'] === 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="estadoCategoria">Categoría activa</label>
                    </div>

                    <button class="btn btn-primary">
                        <?php echo $editCat ? '💾 Guardar cambios' : '➕ Agregar categoría'; ?>
                    </button>

                    <?php if ($editCat): ?>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </form>

                <hr>
                <h5>Estado de categorías</h5>

                <?php foreach ($cats as $cat): ?>
                    <div class="border rounded p-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <strong><?php echo htmlspecialchars($cat['nombre']); ?></strong>
                                <?php if ((int) $cat['estado'] === 1): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a class="btn btn-sm btn-warning" href="?edit_cat=<?php echo $cat['id_categoria']; ?>">Editar</a>
                            <?php if ((int) $cat['estado'] === 1): ?>
                                <a class="btn btn-sm btn-secondary" href="?toggle_categoria=<?php echo $cat['id_categoria']; ?>"
                                   onclick="return confirm('¿Quieres inactivar esta categoría?')">Inactivar</a>
                            <?php else: ?>
                                <a class="btn btn-sm btn-success" href="?toggle_categoria=<?php echo $cat['id_categoria']; ?>">Activar</a>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-danger" href="?delete_categoria=<?php echo $cat['id_categoria']; ?>"
                               onclick="return confirm('¿Eliminar esta categoría y todas sus lecciones?')">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- LECCIONES -->
        <div class="col-lg-8">
            <div class="card admin-card shadow p-3">
                <h4>📝 <?php echo $editL ? 'Editar lección' : 'Agregar lección'; ?></h4>
                <p class="text-secondary">Aquí puedes agregar más colores, números, vocales o cualquier lección.</p>

                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="tipo" value="leccion">
                    <input type="hidden" name="id" value="<?php echo $editL['id_leccion'] ?? 0; ?>">
                    <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($editL['ruta_imagen'] ?? ''); ?>">
                    <input type="hidden" name="audio_actual" value="<?php echo htmlspecialchars($editL['ruta_audio'] ?? ''); ?>">

                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ($cats as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>"
                                    <?php echo (($editL['id_categoria'] ?? 0) == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                    <?php echo ((int) $cat['estado'] === 0) ? ' (Inactiva)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">🇪🇸 Español</label>
                        <input type="text" name="es" class="form-control" required
                               value="<?php echo htmlspecialchars($editL['palabra_espanol'] ?? ''); ?>"
                               placeholder="Ej: Morado">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">🇬🇧 Inglés</label>
                        <input type="text" name="en" class="form-control" required
                               value="<?php echo htmlspecialchars($editL['palabra_ingles'] ?? ''); ?>"
                               placeholder="Ej: Purple">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">🗣️ Pronunciación</label>
                        <input type="text" name="pron" class="form-control"
                               value="<?php echo htmlspecialchars($editL['pronunciacion'] ?? ''); ?>"
                               placeholder="Ej: pérpol">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">🖼️ Imagen</label>
                        <input type="file" name="imagen" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-secondary">Elige una imagen desde tu PC · máximo 5 MB.</small>
                        <?php if (!empty($editL['ruta_imagen']) && file_exists(__DIR__ . '/../' . $editL['ruta_imagen'])): ?>
                            <img src="../<?php echo htmlspecialchars($editL['ruta_imagen']); ?>"
                                 class="admin-preview mt-2" alt="Imagen actual">
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">🔊 Audio</label>
                        <input type="file" name="audio" class="form-control"
                               accept="audio/wav,audio/mpeg,audio/ogg,audio/mp4,.wav,.mp3,.ogg,.m4a">
                        <small class="text-secondary">WAV, MP3, OGG o M4A · máximo 10 MB.</small>
                        <?php if (!empty($editL['ruta_audio']) && file_exists(__DIR__ . '/../' . $editL['ruta_audio'])): ?>
                            <audio controls class="w-100 mt-2">
                                <source src="../<?php echo htmlspecialchars($editL['ruta_audio']); ?>">
                            </audio>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Orden</label>
                        <input type="number" name="orden" class="form-control" min="1"
                               value="<?php echo htmlspecialchars($editL['orden'] ?? 1); ?>">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="estado" id="estadoLeccion"
                                <?php echo (!$editL || (int) $editL['estado'] === 1) ? 'checked' : ''; ?> >
                            <label class="form-check-label" for="estadoLeccion">Activa</label>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <button class="btn btn-success">
                            <?php echo $editL ? '💾 Guardar cambios' : '➕ Agregar lección'; ?>
                        </button>
                        <?php if ($editL): ?>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>

                <hr>
                <h4>📋 Contenido actual</h4>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Categoría</th>
                                <th>ES</th>
                                <th>EN</th>
                                <th>Audio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ls as $lesson): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($lesson['ruta_imagen']) && file_exists(__DIR__ . '/../' . $lesson['ruta_imagen'])): ?>
                                            <img src="../<?php echo htmlspecialchars($lesson['ruta_imagen']); ?>"
                                                 class="admin-thumb" alt="<?php echo htmlspecialchars($lesson['palabra_ingles']); ?>">
                                        <?php else: ?>
                                            <span class="text-secondary">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($lesson['categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($lesson['palabra_espanol']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($lesson['palabra_ingles']); ?></strong></td>
                                    <td>
                                        <?php if (!empty($lesson['ruta_audio']) && file_exists(__DIR__ . '/../' . $lesson['ruta_audio'])): ?>
                                            <audio controls class="admin-audio">
                                                <source src="../<?php echo htmlspecialchars($lesson['ruta_audio']); ?>">
                                            </audio>
                                        <?php else: ?>
                                            <span class="text-secondary">Sin audio</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int) $lesson['estado'] === 1): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a class="btn btn-sm btn-warning" href="?edit_l=<?php echo $lesson['id_leccion']; ?>">Editar</a>
                                            <?php if ((int) $lesson['estado'] === 1): ?>
                                                <a class="btn btn-sm btn-secondary" href="?toggle_leccion=<?php echo $lesson['id_leccion']; ?>">Inactivar</a>
                                            <?php else: ?>
                                                <a class="btn btn-sm btn-success" href="?toggle_leccion=<?php echo $lesson['id_leccion']; ?>">Activar</a>
                                            <?php endif; ?>
                                            <a class="btn btn-sm btn-danger" href="?delete_leccion=<?php echo $lesson['id_leccion']; ?>"
                                               onclick="return confirm('¿Eliminar esta lección?')">Eliminar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
