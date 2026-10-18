<?php
require_once "conexion/conexion.php";
$id=(int)($_GET['id']??0);
$c=$conexion->query("SELECT * FROM categorias WHERE id_categoria=$id AND estado=1")->fetch_assoc();
if(!$c){header('Location:aprendizaje.php');exit;}
$stmt=$conexion->prepare("SELECT * FROM lecciones WHERE id_categoria=? AND estado=1 ORDER BY orden,id_leccion");$stmt->bind_param('i',$id);$stmt->execute();$rs=$stmt->get_result();$data=$rs->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Evaluación <?php echo htmlspecialchars($c['nombre']); ?></title><link rel="stylesheet" href="css/bootstrap.min.css"><link rel="stylesheet" href="css/kids.css"></head>
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
<header class="hero py-4"><h1>📝 Evaluación: <?php echo htmlspecialchars($c['nombre']); ?></h1><p>Responde todas las preguntas y descubre tu puntuación.</p></header>
<main class="container py-5"><div class="d-flex justify-content-between mb-4"><a href="aprendizaje.php" class="btn btn-outline-primary">← Zona de aprendizaje</a><div class="starbar">⭐ <span id="stars">0</span></div></div>
<div id="quiz" class="quiz-box"></div><div id="resultado" class="result-box text-center mt-4"></div></main>
<script>
const data=<?php echo json_encode($data,JSON_UNESCAPED_UNICODE); ?>;
const categoria=<?php echo json_encode($c['nombre'],JSON_UNESCAPED_UNICODE); ?>;
let pos=0,aciertos=0;
function hablar(t){if('speechSynthesis' in window){speechSynthesis.cancel();const u=new SpeechSynthesisUtterance(t);u.lang='en-US';u.rate=.75;speechSynthesis.speak(u)}}
function mostrar(){
if(pos>=data.length){terminar();return;}
const x=data[pos];
let pregunta='¿Cuál es la respuesta correcta?';
const cat=categoria.toLowerCase();
if(cat.includes('color')) pregunta='¿Qué color es?';
else if(cat.includes('número') || cat.includes('numero')) pregunta='¿Qué número es?';
else if(cat.includes('vocal')) pregunta='¿Qué vocal es?';
let opciones=[x.palabra_ingles];while(opciones.length<Math.min(4,data.length)){const r=data[Math.floor(Math.random()*data.length)].palabra_ingles;if(!opciones.includes(r))opciones.push(r)}opciones.sort(()=>Math.random()-.5);document.getElementById('quiz').innerHTML=`<div class="card shadow-sm p-4 text-center"><div class="small text-secondary">Pregunta ${pos+1} de ${data.length}</div><h2 class="mt-3">${pregunta}</h2><button class="btn btn-info text-white mt-2" onclick="hablar('${x.palabra_ingles.replace(/'/g,"\\'")}')">🔊 Escuchar</button><div class="mt-4">${opciones.map(o=>`<button class="btn btn-outline-primary option" onclick="responder(this,'${o.replace(/'/g,"\\'")}','${x.palabra_ingles.replace(/'/g,"\\'")}')">${o}</button>`).join('')}</div></div>`}
function responder(btn,a,b){document.querySelectorAll('.option').forEach(x=>x.disabled=true);if(a===b){aciertos++;btn.classList.add('correct');}else btn.classList.add('wrong');pos++;setTimeout(mostrar,700)}
function terminar(){const score=data.length?Math.round(aciertos*100/data.length):0;const stars=Math.round(score/20);localStorage.setItem('score_<?php echo $id; ?>',score);localStorage.setItem('stars_<?php echo $id; ?>',stars);document.getElementById('quiz').innerHTML='';document.getElementById('stars').textContent=stars;document.getElementById('resultado').innerHTML=`<div class="card shadow-sm p-4"><h2>🎉 ¡Evaluación terminada!</h2><p class="display-6">${score}%</p><p class="display-5">${'⭐'.repeat(stars)}${'☆'.repeat(5-stars)}</p><p>Acertaste <b>${aciertos}</b> de <b>${data.length}</b>.</p><a href="aprendizaje.php" class="btn btn-success me-2">Ver puntuaciones</a><button class="btn btn-primary" onclick="pos=0;aciertos=0;document.getElementById('resultado').innerHTML='';mostrar()">Repetir</button></div>`}mostrar();
</script></body></html>
