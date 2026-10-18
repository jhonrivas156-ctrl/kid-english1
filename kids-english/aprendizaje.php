<?php
require_once "conexion/conexion.php";
$cats=$conexion->query("SELECT * FROM categorias WHERE estado=1 ORDER BY id_categoria")->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Zona de aprendizaje - Kids English</title><link rel="stylesheet" href="css/bootstrap.min.css"><link rel="stylesheet" href="css/kids.css"></head>
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
<header class="hero py-4"><h1>📚 Zona de aprendizaje</h1><p>Haz una evaluación de cada categoría y consigue estrellas.</p></header>
<main class="container py-5"><div class="d-flex justify-content-between align-items-center mb-4"><a href="index.php" class="btn btn-outline-primary">← Volver</a><div><span class="total-stars me-2">⭐ <span id="totalStars">0</span> estrellas</span><button class="btn btn-outline-danger" onclick="borrarPuntuaciones()">🗑️ Borrar puntuaciones</button></div></div>
<div class="row g-4">
<?php foreach($cats as $c): ?><div class="col-md-4"><div class="card score-card shadow-sm h-100"><div class="card-body text-center p-4"><div class="display-4">⭐</div><h3><?php echo htmlspecialchars($c['nombre']); ?></h3><p><?php echo htmlspecialchars($c['descripcion']); ?></p><div class="score-box"><strong>Puntuación:</strong> <span id="score-<?php echo $c['id_categoria']; ?>">0%</span><br><span id="stars-<?php echo $c['id_categoria']; ?>">☆☆☆☆☆</span></div><a href="evaluacion.php?id=<?php echo $c['id_categoria']; ?>" class="btn btn-primary btn-aprender mt-3">Evaluación</a></div></div></div><?php endforeach; ?>
</div></main><script>
const cats=<?php echo json_encode($cats,JSON_UNESCAPED_UNICODE); ?>;
let total=0; cats.forEach(c=>{const s=Number(localStorage.getItem('score_'+c.id_categoria)||0);document.getElementById('score-'+c.id_categoria).textContent=s+'%';const n=Math.round(s/20);document.getElementById('stars-'+c.id_categoria).textContent='⭐'.repeat(n)+'☆'.repeat(5-n);total+=n;});document.getElementById('totalStars').textContent=total;
function borrarPuntuaciones(){if(confirm("¿Quieres borrar todas las puntuaciones?")){cats.forEach(c=>{localStorage.removeItem("score_"+c.id_categoria);localStorage.removeItem("stars_"+c.id_categoria);});location.reload();}}
</script></body></html>
