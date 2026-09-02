<?php
// Página principal de la tienda.
require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/funciones.php';

$rutaBase = '';
$empresa  = obtenerEmpresa($conexion);
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$productos = $busqueda !== '' ? buscarProductos($conexion, $busqueda) : obtenerProductos($conexion);

$tituloPagina = htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') . ' | Inicio';

// Variables para header
$categoriasMenu = obtenerCategoriasActivas($conexion);
$marcasMenu     = obtenerMarcasActivas($conexion);
$paginaActual   = basename($_SERVER['PHP_SELF']);
$dirActual      = basename(dirname($_SERVER['PHP_SELF']));
$idCategoriaActual = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : (($dirActual === 'Categorias' && isset($_GET['id'])) ? (int)$_GET['id'] : 0);
$idMarcaActual     = isset($_GET['id_marca']) ? (int)$_GET['id_marca'] : (($dirActual === 'Marcas' && isset($_GET['id'])) ? (int)$_GET['id'] : 0);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main>

    <!-- ══ Buscador ══ -->
    <section class="buscador-principal" aria-label="Buscador de productos">
        <div class="contenedor">
            <form class="buscador-wrapper" action="index.php" method="GET" role="search">
                <label for="campoBuscar" class="visually-hidden">Buscar productos</label>
                <input
                    type="search"
                    id="campoBuscar"
                    name="buscar"
                    class="input-buscar"
                    placeholder="Buscar producto, categoría o marca…"
                    value="<?= htmlspecialchars($busqueda) ?>"
                    autocomplete="off"
                >
                <button class="boton-buscar" type="submit" aria-label="Buscar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </form>

            <?php if ($busqueda !== ''): ?>
            <div class="resultado-busqueda">
                <p>Resultado de búsqueda para: <strong>"<?= htmlspecialchars($busqueda) ?>"</strong>
                    — <?= count($productos) ?> resultado(s)</p>
                <a href="index.php">✕ Limpiar búsqueda</a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- ══ Catálogo ══ -->
    <section class="catalogo contenedor" id="catalogo" aria-label="Catálogo de productos">
        <div class="titulo-seccion">
            <?php if ($busqueda !== ''): ?>
                <h2>Productos encontrados</h2>
            <?php else: ?>
                <h2>Colección Destacada</h2>
                <p>Descubre nuestras piezas más exclusivas, seleccionadas cuidadosamente para ti.</p>
            <?php endif; ?>
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
                            alt="<?= htmlspecialchars($prod['nombre_producto']) ?>"
                            loading="lazy"
                            onerror="<?= atributoOnErrorImagen() ?>">
                        <span class="etiqueta-nuevo">Nuevo</span>
                    </div>
                    <div class="producto-info">
                        <h3><?= htmlspecialchars($prod['nombre_producto']) ?></h3>
                        <p class="producto-meta">
                            <?= htmlspecialchars($prod['nombre_marca'] ?: 'Marca desconocida') ?> ·
                            <?= htmlspecialchars($prod['nombre_categoria'] ?: 'Categoría desconocida') ?>
                        </p>
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
                    No se encontraron productos<?= $busqueda !== '' ? ' con esa búsqueda' : '' ?>.
                </p>
            <?php endif; ?>
        </div>
    </section>

</main>
<!-- ══ Modal detalle ══ -->
<div class="modal-producto" id="modalProducto" role="dialog" aria-modal="true" aria-label="Detalle del producto">
    <div class="modal-contenido">
        <button class="cerrar-modal" type="button" onclick="cerrarDetalleProducto()" aria-label="Cerrar">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
        </button>
        <div id="contenidoModalProducto"></div>
    </div>
</div>

<style>.visually-hidden{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;}</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
 