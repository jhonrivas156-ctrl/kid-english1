<?php
require_once "conexion/conexion.php";

$id = (int) ($_GET['id'] ?? 0);
$c = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT * FROM categorias WHERE id_categoria = $id AND estado = 1"));

if (!$c) {
    header("Location: index.php");
    exit;
}

$stmt = $conexion->prepare("SELECT * FROM lecciones WHERE id_categoria = ? AND estado = 1 ORDER BY orden, id_leccion");
$stmt->bind_param("i", $id);
$stmt->execute();
$rs = $stmt->get_result();
$lecciones = [];

while ($r = $rs->fetch_assoc()) {
    $lecciones[] = $r;
}

$nombreCategoria = mb_strtolower($c['nombre']);
$carpetaAudio = $nombreCategoria === 'colores' ? 'colores' : ($nombreCategoria === 'números' ? 'numeros' : 'vocales');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($c['nombre']); ?> - Kids English</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/kids.css">
</head>
<body>

<div class="kids-sky" aria-hidden="true">
    <span class="sun"></span>
    <span class="cloud cloud-1"></span>
    <span class="cloud cloud-2"></span>
    <span class="cloud cloud-3"></span>
    <span class="tree tree-1"></span>
    <span class="tree tree-2"></span>
    <span class="tree tree-3"></span>
    <span class="flower flower-1">🌼</span>
    <span class="flower flower-2">🌻</span>
    <span class="flower flower-3">🌷</span>
</div>


<header class="hero py-4">
    <h1><?php echo htmlspecialchars($c['nombre']); ?> 📚</h1>
    <p>Conoce las palabras y escucha su pronunciación.</p>
</header>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="index.php" class="btn btn-outline-primary">← Volver</a>
    </div>

    <div class="row g-4">
        <?php foreach ($lecciones as $l): ?>
            <?php
            $archivoAudio = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $l['palabra_ingles'])) . '.wav';
            $rutaAudio = trim($l['ruta_audio'] ?? '');

            // Si no hay una ruta guardada en la base de datos, usa la ruta automática.
            if ($rutaAudio === '') {
                $rutaAudio = 'assets/audio/' . $carpetaAudio . '/' . $archivoAudio;
            }

            $rutaImagen = trim($l['ruta_imagen'] ?? '');
            ?>

            <div class="col-md-4">
                <div class="card lesson-card shadow-sm h-100">
                    <div class="card-body text-center p-4">

                        <?php if ($rutaImagen !== '' && file_exists(__DIR__ . '/' . $rutaImagen)): ?>
                            <img
                                src="<?php echo htmlspecialchars($rutaImagen); ?>"
                                class="img-fluid rounded mb-3 lesson-image"
                                alt="<?php echo htmlspecialchars($l['palabra_ingles']); ?>"
                            >
                        <?php endif; ?>

                        <div class="word"><?php echo htmlspecialchars($l['palabra_ingles']); ?></div>
                        <h5><?php echo htmlspecialchars($l['palabra_espanol']); ?></h5>
                        <div class="pron">Pronunciación: <?php echo htmlspecialchars($l['pronunciacion']); ?></div>

                        <?php if (file_exists(__DIR__ . '/' . $rutaAudio)): ?>
                            <audio controls class="w-100 mt-3">
                                <source src="<?php echo htmlspecialchars($rutaAudio); ?>" type="audio/wav">
                            </audio>
                        <?php else: ?>
                            <button class="btn btn-info text-white speak mt-3" onclick="hablar('<?php echo htmlspecialchars(addslashes($l['palabra_ingles'])); ?>')">
                                🔊 Escuchar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
function hablar(texto) {
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        const voz = new SpeechSynthesisUtterance(texto);
        voz.lang = 'en-US';
        voz.rate = 0.75;
        speechSynthesis.speak(voz);
    }
}
</script>
</body>
</html>
