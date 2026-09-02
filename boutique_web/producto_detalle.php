<?php
/**
 * producto_detalle.php
 * Cargado vía AJAX dentro del modal de la página principal.
 * NO incluye header/footer — solo el HTML del detalle.
 */
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/funciones.php';

$idProducto = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$producto   = obtenerProductoPorId($conexion, $idProducto);

if (!$producto) {
    echo '<p style="text-align:center;padding:60px 20px;color:#888;font-size:1rem;">Producto no encontrado o no disponible.</p>';
    exit;
}

$imagenes = obtenerImagenesProducto($conexion, $idProducto);
$tallas   = obtenerTallasProducto($conexion, $idProducto);
$colores  = obtenerColoresProducto($conexion, $idProducto);
$stockProducto = obtenerStockProducto($conexion, $idProducto);

// Si además existen imágenes en imagenes_producto, se agregan después sin duplicar rutas.
$imagenesNormalizadas = [];
$rutasUsadas = [];

if (!empty($producto['imagen_principal'])) {
    $imagenesNormalizadas[] = [
        'ruta_imagen' => $producto['imagen_principal'],
        'texto_alt'   => $producto['nombre_producto'],
    ];
    $rutasUsadas[] = trim((string)$producto['imagen_principal']);
}

foreach ($imagenes as $img) {
    $ruta = trim((string)($img['ruta_imagen'] ?? ''));
    if ($ruta === '' || in_array($ruta, $rutasUsadas, true)) {
        continue;
    }
    $imagenesNormalizadas[] = $img;
    $rutasUsadas[] = $ruta;
}

$imagenes = $imagenesNormalizadas;

if (empty($imagenes)) {
    $imagenes[] = [
        'ruta_imagen' => '',
        'texto_alt'   => $producto['nombre_producto'],
    ];
}
?>

<div class="detalle-producto">

    <!-- ═══ CARRUSEL ═══ -->
    <div class="carrusel-producto">
        <?php if (count($imagenes) > 1): ?>
        <button class="control-carrusel" type="button"
                onclick="moverCarrusel(-1)" aria-label="Imagen anterior">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <?php endif; ?>

        <div class="imagenes-carrusel" id="imagenesCarrusel" role="list">
            <?php foreach ($imagenes as $img):
                $src = !empty(trim($img['ruta_imagen'] ?? ''))
                     ? $img['ruta_imagen']
                     : $producto['imagen_principal'];
            ?>
            <div class="imagen-carrusel-item" role="listitem">
                <img src="<?= htmlspecialchars(obtenerUrlImagen($src)) ?>"
                     alt="<?= htmlspecialchars($img['texto_alt'] ?? $producto['nombre_producto']) ?>"
                     loading="lazy"
                     onerror="<?= atributoOnErrorImagen() ?>">
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($imagenes) > 1): ?>
        <button class="control-carrusel" type="button"
                onclick="moverCarrusel(1)" aria-label="Imagen siguiente">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
        <?php endif; ?>
    </div>

    <!-- ═══ INFORMACIÓN ═══ -->
    <div class="detalle-info">

        <!-- Badges categoría / marca -->
        <div class="badges-producto">
            <?php if (!empty($producto['nombre_marca'])): ?>
            <span class="badge badge-marca"><?= htmlspecialchars($producto['nombre_marca']) ?></span>
            <?php endif; ?>
            <?php if (!empty($producto['nombre_categoria'])): ?>
            <span class="badge badge-categoria"><?= htmlspecialchars($producto['nombre_categoria']) ?></span>
            <?php endif; ?>
        </div>

        <h2><?= htmlspecialchars($producto['nombre_producto']) ?></h2>

        <?php if (!empty($producto['descripcion_producto'])): ?>
        <p class="detalle-descripcion"><?= htmlspecialchars($producto['descripcion_producto']) ?></p>
        <?php endif; ?>

        <p class="detalle-precio"><?= formatearPrecio($producto['precio_producto']) ?></p>
        <span class="stock-producto-card"><?= $stockProducto > 0 ? 'Stock disponible: ' . (int)$stockProducto . ' unidad(es)' : 'Sin stock disponible' ?></span>

        <hr class="separador">

        <!-- Talla -->
        <div class="grupo-seleccion">
            <label for="tallaProducto">Talla</label>
            <select id="tallaProducto" name="talla" aria-label="Seleccionar talla">
                <?php if (!empty($tallas)): ?>
                    <?php foreach ($tallas as $t): ?>
                    <option value="<?= htmlspecialchars($t['nombre_talla']) ?>">
                        <?= htmlspecialchars($t['nombre_talla']) ?>
                    </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="Única">Talla única</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Color -->
        <div class="grupo-seleccion">
            <label for="colorProducto">Color</label>
            <select id="colorProducto" name="color" aria-label="Seleccionar color">
                <?php if (!empty($colores)): ?>
                    <?php foreach ($colores as $c): ?>
                    <option value="<?= htmlspecialchars($c['nombre_color']) ?>">
                        <?= htmlspecialchars($c['nombre_color']) ?>
                    </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="Disponible">Color disponible</option>
                <?php endif; ?>
            </select>
        </div>

        <!-- Cantidad -->
        <div class="grupo-cantidad">
            <label for="cantidadProducto">Cantidad</label>
            <div class="contador-cantidad">
                <button type="button" onclick="cambiarCantidad(-1)" aria-label="Restar">−</button>
                <input type="number" id="cantidadProducto" value="1" min="1" readonly
                       aria-label="Cantidad seleccionada">
                <button type="button" onclick="cambiarCantidad(1)" aria-label="Sumar">+</button>
            </div>
        </div>

        <!-- Acciones -->
        <div class="acciones-producto">
            <!-- Botón añadir al carrito -->
            <button class="boton-secundario ancho-completo" type="button"
                    onclick="agregarAlCarrito(
                        <?= (int)$producto['id_producto'] ?>,
                        '<?= htmlspecialchars($producto['nombre_producto'], ENT_QUOTES) ?>',
                        <?= (float)$producto['precio_producto'] ?>
                    )">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                Añadir al carrito
            </button>

            <!-- Botón COMPRAR → pagos.php -->
            <button class="boton-principal ancho-completo" type="button"
                    onclick="comprarProducto(
                        <?= (int)$producto['id_producto'] ?>,
                        '<?= htmlspecialchars($producto['nombre_producto'], ENT_QUOTES) ?>',
                        <?= (float)$producto['precio_producto'] ?>
                    )">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                Comprar ahora
            </button>
        </div>
        <!-- Características decorativas -->
        <div class="caracteristicas-producto">
            <span class="caracteristica">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V5z"/></svg>
                Compra segura
            </span>
            <span class="caracteristica">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
                Soporte disponible
            </span>
        </div>

    </div><!-- /detalle-info -->
</div><!-- /detalle-producto -->
