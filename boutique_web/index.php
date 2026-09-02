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

$totalProductosVitrina = count(obtenerProductos($conexion));
$totalCategoriasVitrina = count($categoriasMenu);
$totalMarcasVitrina = count($marcasMenu);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main id="contenidoPrincipal">

    <?php if ($busqueda === ''): ?>
    <!-- ══ Hero ══ -->
    <section class="hero contenedor" aria-label="Presentación">
        <div class="hero-content">
            <span class="eyebrow">Colección actual</span>
            <h1>Moda con carácter,<br>pensada para durar.</h1>
            <p>Piezas seleccionadas con criterio editorial: materiales nobles, cortes limpios y un guardarropa que no pasa de moda.</p>
            <div class="hero-actions">
                <a href="#catalogo" class="btn btn-primary">Explorar colección</a>
                <a href="Categorias/categorias.php" class="btn btn-secondary">Ver categorías</a>
            </div>
            <div class="hero-stats">
                <div><strong><?= $totalProductosVitrina ?></strong><span>Productos</span></div>
                <div><strong><?= $totalCategoriasVitrina ?></strong><span>Categorías</span></div>
                <div><strong><?= $totalMarcasVitrina ?></strong><span>Marcas</span></div>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <span class="hero-visual-mark"><?= htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?></span>
        </div>
    </section>
    <?php endif; ?>

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
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
                </button>
            </form>

            <?php if ($busqueda !== ''): ?>
            <div class="resultado-busqueda">
                <p>Resultado de búsqueda para: <strong>"<?= htmlspecialchars($busqueda) ?>"</strong>
                    — <?= count($productos) ?> resultado(s)</p>
                <a href="index.php"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="vertical-align:-1px;margin-right:4px;" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>Limpiar búsqueda</a>
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
                <span class="eyebrow" style="justify-content:center;display:flex;">Vitrina</span>
                <h2>Colección destacada</h2>
                <p>Piezas exclusivas, seleccionadas cuidadosamente para ti.</p>
            <?php endif; ?>
        </div>
        <div class="grid-productos">
            <?php if (count($productos) > 0): ?>
                <?php foreach ($productos as $prod): ?>
                    <?php include __DIR__ . '/includes/tarjeta-producto.php'; ?>
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
        <button class="cerrar-modal" type="button" onclick="cerrarDetalleProducto()" aria-label="Cerrar"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button>
        <div id="contenidoModalProducto"></div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
