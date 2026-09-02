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

<main class="catalogo contenedor" id="contenidoPrincipal">
    <div class="titulo-seccion">
        <span class="eyebrow" style="justify-content:center;display:flex;">Categoría</span>
        <h2><?= htmlspecialchars($categoria['nombre_categoria']) ?></h2>
        <p><?= htmlspecialchars($categoria['descripcion_categoria'] ?? 'Prendas disponibles en esta categoría.') ?></p>
    </div>

    <div class="grid-productos">
        <?php if (count($productos) > 0): ?>
            <?php foreach ($productos as $prod): ?>
                <?php include __DIR__ . '/../includes/tarjeta-producto.php'; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="mensaje-sin-resultados">No hay productos disponibles en esta categoría.</p>
        <?php endif; ?>
    </div>
</main>

<div class="modal-producto" id="modalProducto" role="dialog" aria-modal="true">
    <div class="modal-contenido">
        <button class="cerrar-modal" type="button" onclick="cerrarDetalleProducto()" aria-label="Cerrar"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
        <div id="contenidoModalProducto"></div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
