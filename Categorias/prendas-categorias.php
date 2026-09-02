<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase = '../';
$empresa  = obtenerEmpresa($conexion);

// Se filtra por ID para evitar que una categoría con nombre parecido cargue otra sección.
$idCategoria = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idCategoria <= 0 && isset($_GET['categoria'])) {
    // Compatibilidad con enlaces antiguos: prendas-categorias.php?categoria=Camisa
    $categoriaTemporal = obtenerCategoriaPorNombre($conexion, trim($_GET['categoria']));
    $idCategoria = $categoriaTemporal ? (int)$categoriaTemporal['id_categoria'] : 0;
}

$categoria = $idCategoria > 0 ? obtenerCategoriaPorId($conexion, $idCategoria) : null;

if (!$categoria) {
    die('Categoría no encontrada. Verifica que el enlace tenga un ID de categoría válido.');
}

$productos = obtenerProductosPorCategoriaId($conexion, $idCategoria);
$tituloPagina = htmlspecialchars($categoria['nombre_categoria']) . ' | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="catalogo contenedor">
    <div class="titulo-seccion">
        <h2><?= htmlspecialchars($categoria['nombre_categoria']) ?></h2>
        <p><?= htmlspecialchars($categoria['descripcion_categoria'] ?? 'Prendas disponibles en esta categoría.') ?></p>
    </div>

    <div class="grid-productos">
        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $prod): ?>
            <article class="tarjeta-producto"
                     onclick="abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)"
                     role="button" tabindex="0"
                     aria-label="Ver detalles de <?= htmlspecialchars($prod['nombre_producto']) ?>"
                     onkeydown="if(event.key==='Enter')abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)">
                <div class="producto-imagen">
                    <img src="<?= htmlspecialchars(obtenerUrlImagen($prod['imagen_principal'])) ?>"
                         alt="<?= htmlspecialchars($prod['nombre_producto']) ?>" loading="lazy"
                         onerror="<?= atributoOnErrorImagen() ?>">
                    <span class="etiqueta-nuevo">Nuevo</span>
                </div>
                <div class="producto-info">
                    <h3><?= htmlspecialchars($prod['nombre_producto']) ?></h3>
                    <p class="producto-precio"><?= formatearPrecio($prod['precio_producto']) ?></p>
                    <div class="producto-card-actions">
                            <button class="boton-agregar-card" type="button"
                                onclick="event.stopPropagation(); agregarAlCarrito(<?= (int)$prod['id_producto'] ?>, '<?= htmlspecialchars($prod['nombre_producto'], ENT_QUOTES) ?>', <?= (float)$prod['precio_producto'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                Agregar al carrito
                            </button>
                            <button class="boton-detalle-card" type="button" aria-label="Ver detalles" title="Ver detalles"
                                onclick="event.stopPropagation(); abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="mensaje-sin-resultados">No hay productos disponibles en esta categoría.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-producto" id="modalProducto" role="dialog" aria-modal="true">
    <div class="modal-contenido">
        <button class="cerrar-modal" type="button" onclick="cerrarDetalleProducto()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
        </button>
        <div id="contenidoModalProducto"></div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
