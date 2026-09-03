<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase = '../';
$empresa  = obtenerEmpresa($conexion);

/*
    CORRECCIÓN:
    Antes el filtro de marcas podía confundirse cuando se usaba el nombre de la marca.
    Ahora esta página trabaja principalmente con id_marca.
    Ejemplo correcto:
    Marcas/prendas-marcas.php?id=2
*/

$idMarca = 0;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idMarca = (int) $_GET['id'];
}

/*
    Compatibilidad con enlaces antiguos:
    Si todavía existe algún enlace viejo como:
    prendas-marcas.php?marca=Adidas
    lo convertimos internamente buscando esa marca en la BD.
*/
if ($idMarca <= 0 && isset($_GET['marca']) && trim($_GET['marca']) !== '') {
    $marcaPorNombre = obtenerMarcaPorNombre($conexion, trim($_GET['marca']));
    if ($marcaPorNombre) {
        $idMarca = (int) $marcaPorNombre['id_marca'];
    }
}

if ($idMarca <= 0) {
    die('Marca no encontrada. El enlace debe enviar un ID válido. Ejemplo: prendas-marcas.php?id=2');
}

$marca = obtenerMarcaPorId($conexion, $idMarca);

if (!$marca) {
    die('Marca no encontrada en la base de datos.');
}

$productos = obtenerProductosPorMarca($conexion, $idMarca);

$tituloPagina = $marca['nombre_marca'] . ' | ' . ($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="catalogo contenedor">
    <div class="grid-productos">
        <?php if (!empty($productos)): ?>
            <?php foreach ($productos as $prod): ?>
            <article class="tarjeta-producto"
                     onclick="abrirDetalleProducto(<?= (int)$prod['id_producto'] ?>)"
                     role="button"
                     tabindex="0"
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
            <p class="mensaje-sin-resultados">
                No hay productos registrados para la marca <?= htmlspecialchars($marca['nombre_marca']) ?>.
            </p>
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