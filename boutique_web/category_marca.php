<?php
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/funciones.php';

$rutaBase = '';
$empresa  = obtenerEmpresa($conexion);

$idCategoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;
$idMarca     = isset($_GET['id_marca']) ? (int)$_GET['id_marca'] : 0;

$categoria = $idCategoria > 0 ? obtenerCategoriaPorId($conexion, $idCategoria) : null;
$marca     = $idMarca > 0 ? obtenerMarcaPorId($conexion, $idMarca) : null;

// Si no llega ningún filtro válido, se muestran todos los productos activos.
$productos = obtenerProductosPorCategoriaMarca($conexion, $categoria ? $idCategoria : 0, $marca ? $idMarca : 0);

if ($categoria && $marca) {
    $tituloFiltro = $categoria['nombre_categoria'] . ' de ' . $marca['nombre_marca'];
} elseif ($categoria) {
    $tituloFiltro = $categoria['nombre_categoria'];
} elseif ($marca) {
    $tituloFiltro = $marca['nombre_marca'];
} else {
    $tituloFiltro = 'Todos los productos';
}

$tituloPagina = htmlspecialchars($tituloFiltro) . ' | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="catalogo contenedor" id="contenidoPrincipal">
    <div class="titulo-seccion">
        <span class="eyebrow" style="justify-content:center;display:flex;">Catálogo filtrado</span>
        <h2><?= htmlspecialchars($tituloFiltro) ?></h2>
        <p>
            <?php if ($categoria): ?>Categoría: <strong><?= htmlspecialchars($categoria['nombre_categoria']) ?></strong><?php endif; ?>
            <?php if ($categoria && $marca): ?> · <?php endif; ?>
            <?php if ($marca): ?>Marca: <strong><?= htmlspecialchars($marca['nombre_marca']) ?></strong><?php endif; ?>
        </p>
    </div>

    <div class="filtros-rapidos">
        <a href="index.php#catalogo">Limpiar filtros</a>
        <?php if ($categoria): ?><a href="Categorias/prendas-categorias.php?id=<?= (int)$categoria['id_categoria'] ?>">Solo esta categoría</a><?php endif; ?>
        <?php if ($marca): ?><a href="Marcas/prendas-marcas.php?id=<?= (int)$marca['id_marca'] ?>">Solo esta marca</a><?php endif; ?>
    </div>

    <div class="grid-productos">
        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $prod): ?>
                <?php include __DIR__ . '/includes/tarjeta-producto.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="mensaje-sin-resultados">
                No se encontraron productos con este conjunto de filtros.
                <?php if ($categoria && $marca): ?>Probá seleccionando solo la categoría o solo la marca.<?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-producto" id="modalProducto" role="dialog" aria-modal="true" aria-label="Detalle del producto">
    <div class="modal-contenido">
        <button class="cerrar-modal" type="button" onclick="cerrarDetalleProducto()" aria-label="Cerrar"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
        <div id="contenidoModalProducto"></div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
