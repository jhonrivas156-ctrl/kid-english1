<?php
require_once "conexion/conexion.php";
$sql = "SELECT * FROM categorias WHERE estado = 1 ORDER BY id_categoria";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kids English</title>
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
    <div class="rainbow" aria-hidden="true"></div>
    <span class="animal animal-1">🦋</span>
    <span class="animal animal-2">🐰</span>
    <span class="animal animal-3">🦁</span>
    <span class="animal animal-4">🐻</span>
</div>


<header class="hero">
    <h1>🌟 Kids English 🌟</h1>
    <p class="lead mb-0">¡Aprende inglés jugando y practicando!</p>
</header>

<main class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-primary">¿Qué quieres aprender?</h2>
        <p>Elige una sección para comenzar.</p>
    </div>

    <div class="row g-4">
        <?php while ($c = mysqli_fetch_assoc($resultado)): ?>
            <div class="col-md-4">
                <div class="card categoria shadow-sm h-100">
                    <?php if (!empty($c['icono']) && file_exists(__DIR__ . '/' . $c['icono'])): ?>
                        <img src="<?php echo htmlspecialchars($c['icono']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($c['nombre']); ?>">
                    <?php endif; ?>

                    <div class="card-body text-center p-4">
                        <h3 class="fw-bold"><?php echo htmlspecialchars($c['nombre']); ?></h3>
                        <p><?php echo htmlspecialchars($c['descripcion']); ?></p>
                        <a href="lecciones.php?id=<?php echo $c['id_categoria']; ?>" class="btn btn-primary px-4">Ver contenido</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</main>

<div class="quick-links" aria-label="Accesos rápidos">
    <a class="btn btn-warning quick-link credits-link shadow" href="creditos.php">🌟 Créditos</a>
    <a class="btn btn-success quick-link learning-link shadow" href="aprendizaje.php">📚 Zona de aprendizaje</a>
    <a class="btn btn-dark quick-link admin-link shadow" href="admin/index.php">⚙️ Acceso Admin</a>
</div>
<footer class="text-center py-4 text-secondary">Kids English · Aprende, practica y gana ⭐</footer>
</body>
</html>
