<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase     = '../';
$empresa      = obtenerEmpresa($conexion);
$marcas       = obtenerMarcasActivas($conexion);
$tituloPagina = 'Marcas | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="contenedor pagina-simple" id="contenidoPrincipal">
    <div class="titulo-seccion">
        <span class="eyebrow" style="justify-content:center;display:flex;">Explorar</span>
        <h2>Marcas</h2>
        <p>Encuentra tus marcas favoritas y descubre lo mejor de cada colección.</p>
    </div>

    <div class="grid-simple">
        <?php foreach ($marcas as $marca): ?>
        <article class="card-simple al-vista">
            <h2><?= htmlspecialchars($marca['nombre_marca']) ?></h2>
            <p><?= htmlspecialchars($marca['descripcion_marca'] ?? 'Prendas exclusivas de esta marca.') ?></p>
            <a class="btn btn-secondary" href="prendas-marcas.php?id=<?= (int)$marca['id_marca'] ?>">
                Ver prendas
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
            </a>
        </article>
        <?php endforeach; ?>
        <?php if (empty($marcas)): ?>
            <p class="mensaje-sin-resultados">Todavía no hay marcas activas.</p>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
