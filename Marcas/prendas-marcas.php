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
    <nav class="breadcrumbs" aria-label="Ruta de navegación">
        <a href="../index.php">Inicio</a>
        <span class="separador-crumb" aria-hidden="true">/</span>
        <a href="marcas.php">Marcas</a>
        <span class="separador-crumb" aria-hidden="true">/</span>
        <span aria-current="page"><?= htmlspecialchars($marca['nombre_marca']) ?></span>
    </nav>
    <div class="titulo-seccion">
        <h2><?= htmlspecialchars($marca['nombre_marca']) ?></h2>
        <p><?= htmlspecialchars($marca['descripcion_marca'] ?? 'Prendas disponibles de esta marca.') ?></p>
    </div>

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
            <p class="mensaje-sin-resultados">
                No hay productos registrados para la marca <?= htmlspecialchars($marca['nombre_marca']) ?>.
            </p>
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