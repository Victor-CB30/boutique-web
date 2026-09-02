<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase     = '../';
$empresa      = obtenerEmpresa($conexion);
$tituloPagina = 'Contacto | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');

$errores = [];
$exito   = '';
$nombre = $email = $telefono = $asunto = $mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $asunto   = trim($_POST['asunto']   ?? 'Consulta desde web');
    $mensaje  = trim($_POST['mensaje']  ?? '');
    $honeypot = trim($_POST['url']      ?? '');

    if ($honeypot !== '') {
        header('Location: ' . ($_SERVER['REQUEST_URI'] ?? './'));
        exit;
    }

    if ($nombre === '')                                           $errores[] = 'Por favor ingresa tu nombre.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Ingresa un correo electrónico válido.';
    if ($mensaje === '')                                        $errores[] = 'Por favor escribe tu mensaje.';

    if (empty($errores)) {
        $destino   = $empresa['correo'] ?? 'no-reply@localhost';
        $cabeceras = "From: " . ($empresa['nombre_empresa'] ?? 'Boutique') . " <{$destino}>\r\n";
        $cabeceras .= "Reply-To: {$email}\r\n";
        $cuerpo    = "Nuevo mensaje desde el formulario de contacto:\n\n";
        $cuerpo   .= "Nombre: {$nombre}\nEmail: {$email}\n";
        if ($telefono) $cuerpo .= "Teléfono: {$telefono}\n";
        $cuerpo   .= "Asunto: {$asunto}\n\nMensaje:\n{$mensaje}\n";

        @mail($destino, $asunto, $cuerpo, $cabeceras);
        $exito    = '¡Gracias! Tu mensaje fue enviado. Te responderemos a la brevedad.';
        $nombre   = $email = $telefono = $asunto = $mensaje = '';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="contenedor pagina-simple" id="contenidoPrincipal">
    <div class="titulo-seccion">
        <span class="eyebrow" style="justify-content:center;display:flex;">Estamos para ayudarte</span>
        <h2>Contacto</h2>
        <p>¿Tienes dudas o quieres asesoría personalizada? Escríbenos.</p>
    </div>

    <div class="contacto-grid">

        <!-- Formulario -->
        <section class="contacto-card al-vista" aria-labelledby="titulo-form">
            <h2 id="titulo-form">Envíanos un mensaje</h2>

            <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error" role="alert">
                <ul style="padding-left:18px;margin:0;">
                    <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($exito): ?>
            <div class="alerta alerta-exito" role="status"><?= htmlspecialchars($exito) ?></div>
            <?php endif; ?>

            <form method="post" action="" novalidate>
                <div class="campo">
                    <label for="nombre">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text"
                           value="<?= htmlspecialchars($nombre) ?>"
                           placeholder="Tu nombre" required autocomplete="name">
                </div>
                <div class="campo">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email"
                           value="<?= htmlspecialchars($email) ?>"
                           placeholder="tucorreo@email.com" required autocomplete="email">
                </div>
                <div class="campo">
                    <label for="telefono">Teléfono (opcional)</label>
                    <input id="telefono" name="telefono" type="tel"
                           value="<?= htmlspecialchars($telefono) ?>"
                           placeholder="+595 9XX XXX XXX" autocomplete="tel">
                </div>
                <div class="campo">
                    <label for="asunto">Asunto</label>
                    <input id="asunto" name="asunto" type="text"
                           value="<?= htmlspecialchars($asunto ?: 'Consulta desde web') ?>"
                           placeholder="¿En qué podemos ayudarte?">
                </div>
                <div class="campo">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6"
                              placeholder="Escribe tu mensaje aquí…" required><?= htmlspecialchars($mensaje) ?></textarea>
                </div>

                <!-- Honeypot anti-spam -->
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <input name="url" type="text" autocomplete="off" tabindex="-1">
                </div>

                <button class="boton-enviar btn-block" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    Enviar mensaje
                </button>
            </form>
        </section>

        <!-- Información -->
        <aside class="contacto-card info-contacto al-vista" aria-labelledby="titulo-info">
            <h2 id="titulo-info">Información</h2>

            <?php if (!empty($empresa['ubicacion'])): ?>
            <h3>Dirección</h3>
            <p><?= nl2br(htmlspecialchars($empresa['ubicacion'])) ?></p>
            <?php endif; ?>

            <h3>Teléfonos</h3>
            <?php if (!empty($empresa['telefono_llamadas'])): ?>
            <p><a href="tel:<?= htmlspecialchars($empresa['telefono_llamadas']) ?>"><?= htmlspecialchars($empresa['telefono_llamadas']) ?></a></p>
            <?php endif; ?>
            <?php if (!empty($empresa['telefono_whatsapp'])): ?>
            <p><a href="https://wa.me/<?= preg_replace('/[^0-9]/','', $empresa['telefono_whatsapp']) ?>" target="_blank" rel="noopener">WhatsApp</a></p>
            <?php endif; ?>

            <?php if (!empty($empresa['correo'])): ?>
            <h3>Correo</h3>
            <p><a href="mailto:<?= htmlspecialchars($empresa['correo']) ?>"><?= htmlspecialchars($empresa['correo']) ?></a></p>
            <?php endif; ?>

            <h3>Horario</h3>
            <p><?= htmlspecialchars($empresa['horario_atencion'] ?? 'Lun – Vie: 09:00 – 18:00') ?></p>

            <?php if (!empty($empresa['instagram']) || !empty($empresa['facebook'])): ?>
            <h3>Redes sociales</h3>
            <p>
                <?php if (!empty($empresa['instagram'])): ?>
                <a href="<?= htmlspecialchars($empresa['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>
                <?php endif; ?>
                <?php if (!empty($empresa['facebook'])): ?>
                &nbsp;·&nbsp;
                <a href="<?= htmlspecialchars($empresa['facebook']) ?>" target="_blank" rel="noopener">Facebook</a>
                <?php endif; ?>
            </p>
            <?php endif; ?>

            <?php if (!empty($empresa['ubicacion'])): ?>
            <h3>Mapa</h3>
            <div style="border-radius:14px;overflow:hidden;margin-top:8px;border:1px solid var(--border);">
                <iframe loading="lazy"
                        src="https://www.google.com/maps?q=<?= rawurlencode($empresa['ubicacion']) ?>&output=embed"
                        width="100%" height="220" style="border:0;display:block;"
                        allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                        title="Mapa de ubicación"></iframe>
            </div>
            <?php endif; ?>
        </aside>

    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
