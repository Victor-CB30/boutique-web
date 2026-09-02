<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase     = '../';
$empresa      = obtenerEmpresa($conexion);
$categorias   = obtenerCategoriasActivas($conexion);
$tituloPagina = 'Categorías | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="contenedor pagina-simple" id="contenidoPrincipal">
    <div class="titulo-seccion">
        <span class="eyebrow" style="justify-content:center;display:flex;">Explorar</span>
        <h2>Categorías</h2>
        <p>Explora nuestras colecciones organizadas por estilo y tipo de prenda.</p>
    </div>

    <div class="grid-simple">
        <?php foreach ($categorias as $cat): ?>
        <article class="card-simple al-vista">
            <h2><?= htmlspecialchars($cat['nombre_categoria']) ?></h2>
            <p><?= htmlspecialchars($cat['descripcion_categoria'] ?? 'Prendas exclusivas en esta categoría.') ?></p>
            <a class="btn btn-secondary" href="prendas-categorias.php?id=<?= (int)$cat['id_categoria'] ?>">
                Ver prendas
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </a>
        </article>
        <?php endforeach; ?>
        <?php if (empty($categorias)): ?>
            <p class="mensaje-sin-resultados">Todavía no hay categorías activas.</p>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
