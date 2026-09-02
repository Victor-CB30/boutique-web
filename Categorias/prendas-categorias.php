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
                                + Agregar al carrito
                            </button>
                            <button class="boton-detalle-card" type="button" aria-label="Ver detalles" title="Ver detalles"
                                onclick="event.stopPropagation(); abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)">⌕</button>
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
        <button class="cerrar-modal" type="button" onclick="cerrarDetalleProducto()" aria-label="Cerrar">×</button>
        <div id="contenidoModalProducto"></div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
