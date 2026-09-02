<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase     = '../';
$empresa      = obtenerEmpresa($conexion);
$categorias   = obtenerCategoriasActivas($conexion);
$tituloPagina = 'Categorías | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="contenedor pagina-simple">
    <div class="titulo-seccion">
        <h2>Categorías</h2>
        <p>Explora nuestras colecciones organizadas por estilo y tipo de prenda.</p>
    </div>

    <div class="grid-simple">
        <?php foreach ($categorias as $cat): ?>
        <article class="card-simple">
            <h2><?= htmlspecialchars($cat['nombre_categoria']) ?></h2>
            <p><?= htmlspecialchars($cat['descripcion_categoria'] ?? 'Prendas exclusivas en esta categoría.') ?></p>
            <a class="boton-principal" href="prendas-categorias.php?id=<?= (int)$cat['id_categoria'] ?>">
                Ver prendas →
            </a>
        </article>
        <?php endforeach; ?>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
