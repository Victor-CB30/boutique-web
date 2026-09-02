<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';

$rutaBase     = '../';
$empresa      = obtenerEmpresa($conexion);
$marcas       = obtenerMarcasActivas($conexion);
$tituloPagina = 'Marcas | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="contenedor pagina-simple">
    <div class="titulo-seccion">
        <h2>Marcas</h2>
        <p>Encuentra tus marcas favoritas y descubre lo mejor de cada colección.</p>
    </div>

    <div class="grid-simple">
        <?php foreach ($marcas as $marca): ?>
        <article class="card-simple">
            <span class="card-simple-monograma" aria-hidden="true"><?= htmlspecialchars(mb_substr($marca['nombre_marca'], 0, 1)) ?></span>
            <span class="card-simple-eyebrow">Marca</span>
            <h2><?= htmlspecialchars($marca['nombre_marca']) ?></h2>
            <p><?= htmlspecialchars($marca['descripcion_marca'] ?? 'Prendas exclusivas de esta marca.') ?></p>
            <a class="boton-principal" href="prendas-marcas.php?id=<?= (int)$marca['id_marca'] ?>">
                Ver prendas →
            </a>
        </article>
        <?php endforeach; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
