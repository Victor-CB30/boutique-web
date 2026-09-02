<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';
require_once __DIR__ . '/../config/auth.php';

$rutaBase = '../';
$empresa = obtenerEmpresa($conexion);
$tituloPagina = 'Acceso administrador | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');

if (adminAutenticado()) {
    header('Location: administrador.php');
    exit;
}

asegurarTablaAdministradores($conexion);
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Completa el correo y la contraseña para ingresar.';
    } else {
        $resultado = iniciarSesionAdmin($conexion, $email, $password);
        if ($resultado['ok']) {
            header('Location: administrador.php');
            exit;
        }
        $error = $resultado['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?></title>
    <link rel="icon" type="image/jpeg" href="../assets/img/favicon-boutique.jpg">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=20">
</head>
<body class="login-admin-body">
    <main class="login-admin-main">
        <section class="login-admin-card">
            <div class="login-admin-brand">
                <div class="login-admin-icon-container">
                    <div class="login-admin-icon"><?= htmlspecialchars(strtoupper(substr($empresa['nombre_empresa'] ?? 'B', 0, 1))) ?></div>
                </div>
                <a href="../index.php" class="logo-tienda-login"><?= htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?></a>
                <span class="login-admin-chip"><h1>Bienvenido de nuevo</h1></span>
            </div>

            <?php if ($error): ?>
                <div class="alerta-login-mejorada" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0l-5.708 9.637a1.13 1.13 0 0 0 .98 1.707H13.88a1.13 1.13 0 0 0 .98-1.707L8.982 1.566ZM8 5a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 5Zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-login-admin-mejorada" autocomplete="on">
                <div class="campo-login-mejorado">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="admin@boutique.com" required autofocus>
                </div>

                <div class="campo-login-mejorado">
                    <label for="password">Contraseña</label>
                    <div class="password-admin-wrap-mejorada">
                        <input type="password" id="password" name="password" placeholder="" required>
                        <button type="button" id="togglePassword" class="toggle-password-btn" aria-label="Mostrar u ocultar contraseña">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button class="boton-principal-login ancho-completo" type="submit">
                    <span>Ingresar al panel</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                    </svg>
                </button>
                <a href="olvide_password.php" class="admin-forgot-link">¿Olvidaste tu contraseña?</a>
            </form>

            <div class="login-footer">
                <a class="login-admin-volver-mejorado" href="../index.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M14.5 1.5a.5.5 0 0 1 .5.5v4.8a2.5 2.5 0 0 1-2.5 2.5H2.707l3.347 3.346a.5.5 0 0 1-.708.708l-4.2-4.2a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 8.3H12.5A1.5 1.5 0 0 0 14 6.8V2a.5.5 0 0 1 .5-.5"/>
                    </svg>
                    <span>Volver a la tienda</span>
                </a>
            </div>
        </section>
    </main>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.setAttribute('aria-pressed', String(isPassword));
            });
        }
    </script>
</body>
</html>
