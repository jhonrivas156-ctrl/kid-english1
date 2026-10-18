<?php
session_start();
require_once "../conexion/conexion.php";

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conexion->prepare("SELECT id_usuario, usuario, password, nombre FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $admin['id_usuario'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        header('Location: index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Acceso Admin - Kids English</title>
<link rel="stylesheet" href="../css/bootstrap.min.css"><link rel="stylesheet" href="../css/kids.css">
</head>
<body>
<div class="container py-5" style="max-width:520px">
    <div class="card shadow p-4 mt-5">
        <div class="text-center mb-4"><div class="display-4">🔐</div><h2>Acceso Administrador</h2><p class="text-secondary">Ingresa con los datos de la base de datos.</p></div>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <label class="form-label">Usuario</label>
            <input type="text" name="usuario" class="form-control mb-3" required autocomplete="username">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control mb-4" required autocomplete="current-password">
            <button class="btn btn-primary w-100">Entrar al panel</button>
        </form>
        <a href="../index.php" class="btn btn-outline-secondary w-100 mt-3">← Volver al inicio</a>
    </div>
</div>
</body>
</html>
