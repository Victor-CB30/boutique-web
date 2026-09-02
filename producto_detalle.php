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
        <button class="control-carrusel" type="button"
                onclick="moverCarrusel(-1)" aria-label="Imagen anterior">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        </button>

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

        <button class="control-carrusel" type="button"
                onclick="moverCarrusel(1)" aria-label="Imagen siguiente">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </button>
    </div>

    <!-- ═══ INFORMACIÓN ═══ -->
    <div class="detalle-info">

        <!-- Badges categoría / marca -->
        <div class="badges-producto">
            <?php if (!empty($producto['nombre_categoria'])): ?>
            <span class="badge badge-categoria"><?= htmlspecialchars($producto['nombre_categoria']) ?></span>
            <?php endif; ?>
            <?php if (!empty($producto['nombre_marca'])): ?>
            <span class="badge badge-marca"><?= htmlspecialchars($producto['nombre_marca']) ?></span>
            <?php endif; ?>
        </div>

        <h2><?= htmlspecialchars($producto['nombre_producto']) ?></h2>

        <p class="detalle-precio"><?= formatearPrecio($producto['precio_producto']) ?></p>

        <?php if (!empty($producto['descripcion_producto'])): ?>
        <p class="detalle-descripcion"><?= htmlspecialchars($producto['descripcion_producto']) ?></p>
        <?php endif; ?>

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
            <!-- Botón añadir al carrito (acción principal) -->
            <button class="boton-principal ancho-completo" type="button"
                    onclick="agregarAlCarrito(
                        <?= (int)$producto['id_producto'] ?>,
                        '<?= htmlspecialchars($producto['nombre_producto'], ENT_QUOTES) ?>',
                        <?= (float)$producto['precio_producto'] ?>
                    )">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                </svg>
                Añadir al carrito
            </button>

            <!-- Botón COMPRAR → pagos.php (acción secundaria: compra directa) -->
            <button class="boton-secundario ancho-completo" type="button"
                    onclick="comprarProducto(
                        <?= (int)$producto['id_producto'] ?>,
                        '<?= htmlspecialchars($producto['nombre_producto'], ENT_QUOTES) ?>',
                        <?= (float)$producto['precio_producto'] ?>
                    )">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                </svg>
                Comprar ahora
            </button>
        </div>
        <!-- Características decorativas -->
        <div class="caracteristicas-producto">
            <span class="caracteristica">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4z"/></svg>
                Compra segura
            </span>
            <span class="caracteristica">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>
                Soporte disponible
            </span>
        </div>

    </div><!-- /detalle-info -->
</div><!-- /detalle-producto -->