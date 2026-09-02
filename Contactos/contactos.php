<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase     = '../';
$empresa      = obtenerEmpresa($conexion);
$tituloPagina = 'Contacto | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');

$errores = [];
$exito   = '';

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

<main class="contenedor pagina-simple">
    <div class="titulo-seccion">
        <h2>Contacto</h2>
        <p>¿Tienes dudas o quieres asesoría personalizada? Estamos aquí para ayudarte.</p>
    </div>

    <div class="contacto-grid">

        <!-- Formulario -->
        <section class="contacto-card" aria-labelledby="titulo-form">
            <h2 id="titulo-form">Envíanos un mensaje</h2>

            <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="alerta-icono"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                <ul class="alerta-lista">
                    <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($exito): ?>
            <div class="alerta alerta-exito" role="status">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="alerta-icono"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                <span><?= htmlspecialchars($exito) ?></span>
            </div>
            <?php endif; ?>

            <form method="post" action="" novalidate>
                <div class="campo">
                    <label for="nombre">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text"
                           value="<?= htmlspecialchars($nombre ?? '') ?>"
                           placeholder="Tu nombre" required autocomplete="name">
                </div>
                <div class="campo">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email"
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           placeholder="tucorreo@email.com" required autocomplete="email">
                </div>
                <div class="campo">
                    <label for="telefono">Teléfono (opcional)</label>
                    <input id="telefono" name="telefono" type="tel"
                           value="<?= htmlspecialchars($telefono ?? '') ?>"
                           placeholder="+595 9XX XXX XXX" autocomplete="tel">
                </div>
                <div class="campo">
                    <label for="asunto">Asunto</label>
                    <input id="asunto" name="asunto" type="text"
                           value="<?= htmlspecialchars($asunto ?? 'Consulta desde web') ?>"
                           placeholder="¿En qué podemos ayudarte?">
                </div>
                <div class="campo">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6"
                              placeholder="Escribe tu mensaje aquí…" required><?= htmlspecialchars($mensaje ?? '') ?></textarea>
                </div>

                <!-- Honeypot anti-spam -->
                <div class="campo-honeypot" aria-hidden="true">
                    <input name="url" type="text" autocomplete="off" tabindex="-1">
                </div>

                <button class="boton-enviar" type="submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    Enviar mensaje
                </button>
            </form>
        </section>

        <!-- Información -->
        <aside class="contacto-card info-contacto" aria-labelledby="titulo-info">
            <h2 id="titulo-info">Información</h2>

            <?php if (!empty($empresa['ubicacion'])): ?>
            <h3>Dirección</h3>
            <p><?= nl2br(htmlspecialchars($empresa['ubicacion'])) ?></p>
            <?php endif; ?>

            <h3>Teléfonos</h3>
            <?php if (!empty($empresa['telefono_llamadas'])): ?>
            <p class="info-contacto-linea">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58z"/><path d="M1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/></svg>
                <a href="tel:<?= htmlspecialchars($empresa['telefono_llamadas']) ?>"><?= htmlspecialchars($empresa['telefono_llamadas']) ?></a>
            </p>
            <?php endif; ?>
            <?php if (!empty($empresa['telefono_whatsapp'])): ?>
            <p class="info-contacto-linea">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="#25d366" viewBox="0 0 16 16" aria-hidden="true"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/','', $empresa['telefono_whatsapp']) ?>" target="_blank" rel="noopener">WhatsApp</a>
            </p>
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
            <div class="info-contacto-mapa">
                <iframe loading="lazy"
                        src="https://www.google.com/maps?q=<?= rawurlencode($empresa['ubicacion']) ?>&output=embed"
                        width="100%" height="200"
                        allowfullscreen referrerpolicy="no-referrer-when-downgrade"
                        title="Mapa de ubicación"></iframe>
            </div>
            <?php endif; ?>
        </aside>

    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
