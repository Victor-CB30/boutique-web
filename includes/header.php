<?php
// Header reutilizable de la tienda.
if (!isset($conexion)) {
    require_once __DIR__ . '/../config/conexion.php';
    require_once __DIR__ . '/../config/funciones.php';
}
if (!isset($rutaBase)) $rutaBase = '';
if (!isset($empresa))  $empresa  = obtenerEmpresa($conexion);

if (!isset($categoriasMenu)) $categoriasMenu = obtenerCategoriasActivas($conexion);
if (!isset($marcasMenu))     $marcasMenu     = obtenerMarcasActivas($conexion);

// Página activa para resaltar en menú
if (!isset($paginaActual)) $paginaActual = basename($_SERVER['PHP_SELF']);
if (!isset($dirActual)) $dirActual = basename(dirname($_SERVER['PHP_SELF']));
if (!isset($idCategoriaActual)) $idCategoriaActual = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : (($dirActual === 'Categorias' && isset($_GET['id'])) ? (int)$_GET['id'] : 0);
if (!isset($idMarcaActual)) $idMarcaActual = isset($_GET['id_marca']) ? (int)$_GET['id_marca'] : (($dirActual === 'Marcas' && isset($_GET['id'])) ? (int)$_GET['id'] : 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" type="image/webp" href="../assets/img/favicon-boutique.jpg">
    <meta name="description" content="<?= htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?> — Moda exclusiva para ti.">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) : htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($rutaBase) ?>assets/css/estilos.css">
    <link type="image/x-icon" href="https://tse2.mm.bing.net/th/id/OIP.JCGQk7jAPhfRM_x_XKJp2AHaHa?cb=thfvnextfalcon4&rs=1&pid=ImgDetMain&o=7&rm=3" rel="icon" />
</head>
<body>

<header class="encabezado" role="banner">
    <nav class="barra-navegacion contenedor" aria-label="Navegación principal">
        <a href="<?= htmlspecialchars($rutaBase) ?>index.php" class="logo-tienda" aria-label="Inicio">
            <div class="logo-tienda-contenedor">
                <img src="<?= htmlspecialchars($empresa['logo_url'] ?? 'https://tse2.mm.bing.net/th/id/OIP.JCGQk7jAPhfRM_x_XKJp2AHaHa?cb=thfvnextfalcon4&rs=1&pid=ImgDetMain&o=7&rm=3') ?>" alt="<?= htmlspecialchars($empresa['nombre_empresa']) ?>" class="logo-tienda-img">
                <span class="logo-tienda-nombre"><?= htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?></span>
            </div>
        </a>

        <button class="boton-menu" id="botonMenu" type="button"
                aria-label="Abrir menú de navegación"
                aria-expanded="false" aria-controls="menuPrincipal">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="icono-menu-abrir">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="icono-menu-cerrar" style="display:none">
                <line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>
            </svg>
        </button>

        <ul class="menu-principal" id="menuPrincipal" role="menubar">
            <li role="none">
                <a href="<?= htmlspecialchars($rutaBase) ?>index.php"
                   class="<?= $paginaActual === 'index.php' ? 'activo' : '' ?>"
                   role="menuitem">Inicio</a>
            </li>

            <li class="menu-item-submenu" role="none">
                <a href="<?= htmlspecialchars($rutaBase) ?>Categorias/categorias.php"
                   class="<?= $dirActual === 'Categorias' ? 'activo' : '' ?>"
                   role="menuitem" aria-haspopup="true">
                    Categorías
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 16 16" fill="currentColor" class="icono-chevron" aria-hidden="true"><path d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </a>
                <ul class="submenu-contenedor" role="menu">
                    <?php foreach ($categoriasMenu as $cat): ?>
                    <li role="none">
                        <a href="<?= htmlspecialchars($rutaBase) ?><?= $idMarcaActual > 0 ? 'category_marca.php?id_categoria=' . (int)$cat['id_categoria'] . '&id_marca=' . $idMarcaActual : 'Categorias/prendas-categorias.php?id=' . (int)$cat['id_categoria'] ?>"
                           role="menuitem">
                            <?= htmlspecialchars($cat['nombre_categoria']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li class="menu-item-submenu" role="none">
                <a href="<?= htmlspecialchars($rutaBase) ?>Marcas/marcas.php"
                   class="<?= $dirActual === 'Marcas' ? 'activo' : '' ?>"
                   role="menuitem" aria-haspopup="true">
                    Marcas
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 16 16" fill="currentColor" class="icono-chevron" aria-hidden="true"><path d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/></svg>
                </a>
                <ul class="submenu-contenedor" role="menu">
                    <?php foreach ($marcasMenu as $marca): ?>
                    <li role="none">
                        <a href="<?= htmlspecialchars($rutaBase) ?><?= $idCategoriaActual > 0 ? 'category_marca.php?id_categoria=' . $idCategoriaActual . '&id_marca=' . (int)$marca['id_marca'] : 'Marcas/prendas-marcas.php?id=' . (int)$marca['id_marca'] ?>"
                           role="menuitem">
                            <?= htmlspecialchars($marca['nombre_marca']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li class="menu-carrito" role="none">
                <button class="boton-carrito-menu" id="botonCarritoMenu" type="button"
                        aria-label="Ver carrito de compras" aria-expanded="false" aria-controls="dropdownCarrito">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1.5 7A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.49-.402L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                    </svg>
                    <span>Carrito</span>
                    <span class="contador-carrito-menu" id="contadorCarritoMenu">0</span>
                </button>
                <div class="dropdown-carrito" id="dropdownCarrito" aria-live="polite">
                    <div class="dropdown-carrito-header">
                        <strong>Mi carrito</strong>
                        <span id="totalCarritoMenu">Gs. 0</span>
                    </div>
                    <div class="dropdown-carrito-items" id="itemsCarritoMenu">
                        <p class="carrito-vacio-menu">Todavía no agregaste productos.</p>
                    </div>
                    <div class="dropdown-carrito-acciones">
                        <a class="boton-finalizar-carrito" href="<?= htmlspecialchars($rutaBase) ?>Pagos/pagos.php">Finalizar compra</a>
                        <a class="boton-seguir-carrito" href="<?= htmlspecialchars($rutaBase) ?>index.php#catalogo">Agregar más</a>
                    </div>
                </div>
            </li>

            <li role="none">
                <a href="<?= htmlspecialchars($rutaBase) ?>Contactos/contactos.php"
                   class="<?= $dirActual === 'Contactos' ? 'activo' : '' ?>"
                   role="menuitem">Contacto</a>
            </li>

        </ul>
    </nav>
</header>
