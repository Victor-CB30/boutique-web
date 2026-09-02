<?php
/**
 * Tarjeta de producto reutilizable.
 * Espera la variable $prod (fila de productos con nombre_categoria / nombre_marca via JOIN).
 */
?>
<article class="tarjeta-producto al-vista"
        onclick="abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)"
        role="button" tabindex="0"
        aria-label="Ver detalles de <?= htmlspecialchars($prod['nombre_producto']) ?>"
        onkeydown="if(event.key==='Enter')abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)">
    <div class="producto-imagen">
        <img src="<?= htmlspecialchars(obtenerUrlImagen($prod['imagen_principal'])) ?>"
            alt="<?= htmlspecialchars($prod['nombre_producto']) ?>"
            loading="lazy"
            onerror="<?= atributoOnErrorImagen() ?>">
        <span class="etiqueta-nuevo">Nuevo</span>
    </div>
    <div class="producto-info">
        <p class="producto-meta">
            <?= htmlspecialchars($prod['nombre_marca'] ?: 'Sin marca') ?>
        </p>
        <h3><?= htmlspecialchars($prod['nombre_producto']) ?></h3>
        <p class="producto-precio"><?= formatearPrecio($prod['precio_producto']) ?></p>
        <div class="producto-card-actions">
            <button class="boton-agregar-card" type="button"
                onclick="event.stopPropagation(); agregarAlCarrito(<?= (int)$prod['id_producto'] ?>, '<?= htmlspecialchars($prod['nombre_producto'], ENT_QUOTES) ?>', <?= (float)$prod['precio_producto'] ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                Agregar
            </button>
            <button class="boton-detalle-card" type="button" aria-label="Ver detalles" title="Ver detalles"
                onclick="event.stopPropagation(); abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.938 7.152a1 1 0 0 1 0 .696C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.938-7.152Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>
    </div>
</article>
