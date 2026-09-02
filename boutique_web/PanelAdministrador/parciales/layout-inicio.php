<?php
/**
 * Apertura del layout administrativo compartido (sidebar + topbar).
 * Variables esperadas desde la página que incluye este parcial:
 * $empresa, $admin, $paginaActivaAdmin, $tituloPagina,
 * $kickerAdmin, $tituloSeccionAdmin, $subtituloSeccionAdmin,
 * $mensaje (opcional), $tipoMensaje (opcional), $topbarExtra (opcional, HTML)
 */
$nombreEmpresaAdmin = $empresa['nombre_empresa'] ?? 'Boutique';
$inicialesAdmin = strtoupper(substr($admin['nombre'] ?? 'A', 0, 1));

$itemsNavAdmin = [
    'dashboard'   => ['href' => 'administrador.php', 'label' => 'Dashboard',
        'icon' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>'],
    'productos'   => ['href' => 'productos.php', 'label' => 'Productos',
        'icon' => '<path d="M20 7 12 3 4 7v10l8 4 8-4Z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/>'],
    'clientes'    => ['href' => 'clientes.php', 'label' => 'Clientes',
        'icon' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6.5 6.5-6.5s6.5 2.9 6.5 6.5"/><path d="M16.5 4.5a3.5 3.5 0 0 1 0 7"/><path d="M18.5 13.8c2.3.7 4 2.8 4 5.2"/>'],
    'pedidos'     => ['href' => 'pedidos.php', 'label' => 'Pedidos',
        'icon' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>'],
    'categorias'  => ['href' => 'categorias.php', 'label' => 'Categorías',
        'icon' => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>'],
    'marcas'      => ['href' => 'marcas.php', 'label' => 'Marcas',
        'icon' => '<path d="m12 2 2.9 6.3 6.9.9-5 4.8 1.2 6.9L12 17.6l-6 3.3 1.2-6.9-5-4.8 6.9-.9Z"/>'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'Panel administrador') ?></title>
    <link rel="icon" type="image/jpeg" href="../assets/img/favicon-boutique.jpg">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=20">
</head>
<body class="admin-body">
<div class="admin-layout">
    <div class="admin-overlay" id="adminOverlay"></div>
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <span class="admin-logo-mini"><?= htmlspecialchars(strtoupper(substr($nombreEmpresaAdmin, 0, 2))) ?></span>
            <div>
                <strong><?= htmlspecialchars($nombreEmpresaAdmin) ?></strong>
                <small>Administración</small>
            </div>
            <button class="admin-sidebar-close" id="adminSidebarClose" type="button" aria-label="Cerrar menú">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <nav class="admin-nav" aria-label="Menú administrador">
            <?php foreach ($itemsNavAdmin as $clave => $item): ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $paginaActivaAdmin === $clave ? 'activo' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $item['icon'] ?></svg>
                <?= htmlspecialchars($item['label']) ?>
            </a>
            <?php endforeach; ?>
            <a href="../index.php" target="_blank" class="admin-nav-store">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>
                Ver tienda
            </a>
        </nav>

        <?php if (!empty($cajaLateralAdmin)): ?>
        <div class="admin-sidebar-box">
            <span><?= htmlspecialchars($cajaLateralAdmin['titulo']) ?></span>
            <strong><?= htmlspecialchars((string)$cajaLateralAdmin['valor']) ?></strong>
            <small><?= htmlspecialchars($cajaLateralAdmin['nota']) ?></small>
        </div>
        <?php endif; ?>

        <?php if (!empty($accesosRapidosAdmin)): ?>
        <div class="admin-sidebar-shortcuts">
            <?php foreach ($accesosRapidosAdmin as $texto => $href): ?>
            <a href="<?= htmlspecialchars($href) ?>">+ <?= htmlspecialchars($texto) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="admin-sidebar-footer">
            <span><?= htmlspecialchars($admin['nombre'] ?? 'Admin') ?></span>
            <a href="logout.php">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                Salir
            </a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar<?= !empty($mostrarUsuarioAdmin) ? ' admin-topbar-actions' : '' ?>">
            <div>
                <button class="admin-menu-toggle" id="adminMenuToggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="adminSidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <span class="admin-kicker"><?= htmlspecialchars($kickerAdmin ?? '') ?></span>
                <h1><?= htmlspecialchars($tituloSeccionAdmin ?? '') ?></h1>
                <?php if (!empty($subtituloSeccionAdmin)): ?><p><?= htmlspecialchars($subtituloSeccionAdmin) ?></p><?php endif; ?>
            </div>
            <?php if (!empty($mostrarUsuarioAdmin)): ?>
            <div class="admin-user-card">
                <span class="admin-avatar"><?= htmlspecialchars($inicialesAdmin) ?></span>
                <div><strong><?= htmlspecialchars($admin['nombre'] ?? 'Administrador') ?></strong><small><?= htmlspecialchars($admin['email'] ?? '') ?></small></div>
            </div>
            <?php endif; ?>
        </header>

        <?php if (!empty($mensaje)): ?>
        <div class="admin-alert <?= ($tipoMensaje ?? 'ok') === 'error' ? 'error' : 'ok' ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
