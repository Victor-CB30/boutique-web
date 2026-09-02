<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/auth.php';
$mensaje = '';
$tipo = 'ok';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Ingresa un correo válido.';
        $tipo = 'error';
    } else {
        $token = crearTokenRecuperacion($conexion, $email);
        if ($token) {
            $base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['REQUEST_URI'] ?? ''), '/');
            $enlace = $base . '/restablecer_password.php?token=' . urlencode($token);
            $asunto = 'Restablecer contraseña - Boutique';
            $cuerpo = "Solicitaste restablecer tu contraseña. Abre este enlace (válido por 30 minutos):\n\n$enlace\n\nSi no hiciste esta solicitud, ignora el mensaje.";
            $enviado = @mail($email, $asunto, $cuerpo, "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\nContent-Type: text/plain; charset=UTF-8");
            $mensaje = $enviado ? 'Revisa tu correo. Te enviamos un enlace válido por 30 minutos.' : 'El enlace fue generado, pero el servidor local no pudo enviar el correo. Configura SMTP/mail() según el README.';
        } else {
            $mensaje = 'Si el correo existe, recibirás un enlace de recuperación.';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Recuperar contraseña</title>
    <link rel="icon" type="image/jpeg" href="../assets/img/favicon-boutique.jpg">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=20">
</head>
<body class="admin-login-body">
    <main class="admin-login-shell">
        <section class="admin-login-card">
            <div class="admin-login-form-wrap">
                <a href="index.php" class="admin-login-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    Volver
                </a>
                <span class="admin-kicker">Seguridad</span>
                <h1>Recuperar acceso</h1>
                <p>Ingresa el correo del administrador.</p>
                <?php if ($mensaje): ?><div class="admin-alert <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
                <form method="post" class="admin-login-form">
                    <label>Correo<input type="email" name="email" required autocomplete="email"></label>
                    <button class="btn btn-primary btn-block" type="submit">Enviar enlace</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
