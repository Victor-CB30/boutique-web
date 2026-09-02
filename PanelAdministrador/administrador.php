<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/funciones.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/esquema.php';
protegerPanelAdmin(); asegurarEsquemaBoutique($conexion);
$admin=adminActual(); $empresa=obtenerEmpresa($conexion);
$totalProductos=(int)$conexion->query("SELECT COUNT(*) FROM productos WHERE estado_producto=1")->fetchColumn();
$totalClientes=(int)$conexion->query("SELECT COUNT(*) FROM clientes WHERE estado_cliente=1")->fetchColumn();
$totalPedidos=(int)$conexion->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$pendientes=(int)$conexion->query("SELECT COUNT(*) FROM pedidos WHERE estado_pedido='pendiente'")->fetchColumn();
$pedidos=$conexion->query("SELECT * FROM pedidos ORDER BY id_pedido DESC LIMIT 8")->fetchAll();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="icon" type="image/webp" href="../assets/img/favicon-boutique.jpg"><title>Panel | <?=htmlspecialchars($empresa['nombre_empresa']??'Boutique')?></title><link rel="stylesheet" href="../assets/css/estilos.css?v=16.0"></head>
<body class="admin-body"><div class="admin-layout clean-admin"><aside class="admin-sidebar admin-sidebar-full"><div class="admin-sidebar-brand"><span class="admin-logo-mini">BG</span><div><strong><?=htmlspecialchars($empresa['nombre_empresa']??'Boutique')?></strong><small>Administración</small></div></div>
<nav class="admin-nav" aria-label="Menú administrador">
            <a class="activo" href="administrador.php">Dashboard</a>
            <a  href="productos.php">Productos</a>
            <a  href="clientes.php">Clientes</a>
            <a  href="pedidos.php">Pedidos</a>
            <a href="categorias.php">Categorías</a>
            <a href="marcas.php">Marcas</a>
            <a href="../index.php" target="_blank">Ver tienda</a>
        </nav>
<div class="admin-sidebar-box">
    <span>Resumen</span>
    <strong><?= $totalPedidos ?></strong>
    <small>pedidos registrados</small>
</div>
<div class="admin-sidebar-shortcuts"><a href="clientes.php#form-cliente">+ Cliente</a><a href="pedidos.php#form-pedido">+ Pedido</a><a href="productos.php#form-producto">+ Producto</a></div>
<div class="admin-sidebar-footer"><span><?=htmlspecialchars($admin['nombre']??'Admin')?></span><a href="logout.php">Salir</a></div></aside>
<main class="admin-main"><header class="admin-topbar"><div><span class="admin-kicker">Resumen</span><h1>Panel de control</h1><p>Accesos rápidos para clientes, pedidos y catálogo.</p></div><div class="admin-quick-actions"><a class="admin-btn primary" href="clientes.php#form-cliente">+ Cliente</a><a class="admin-btn" href="pedidos.php#form-pedido">+ Pedido</a></div></header>
<section class="admin-stats-grid"><article><span>Productos</span><strong><?=$totalProductos?></strong></article><article><span>Clientes</span><strong><?=$totalClientes?></strong></article><article><span>Pedidos</span><strong><?=$totalPedidos?></strong></article><article><span>Pendientes</span><strong><?=$pendientes?></strong></article></section>
<section class="admin-panel-card"><div class="admin-panel-header"><div><h2>Pedidos recientes</h2><p>Últimos pedidos registrados.</p></div><a class="admin-btn small ghost" href="pedidos.php">Ver todos</a></div><div class="admin-table-wrap normal-table"><table class="admin-table"><thead><tr><th>Código</th><th>Cliente</th><th>Retiro</th><th>Total</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php foreach($pedidos as $p):?><tr><td><strong><?=htmlspecialchars($p['codigo_pedido'])?></strong></td><td><?=htmlspecialchars($p['nombre_cliente'])?></td><td><?=htmlspecialchars($p['tipo_retiro']==='delivery'?'Delivery':'Local')?></td><td><?=formatearPrecio($p['total_pedido'])?></td><td><span class="admin-badge <?=$p['estado_pedido']==='pendiente'?'muted':'ok'?>"><?=htmlspecialchars(ucfirst($p['estado_pedido']))?></span></td><td><?=date('d/m/Y H:i',strtotime($p['fecha_pedido']))?></td></tr><?php endforeach;?><?php if(!$pedidos):?><tr><td colspan="6" class="admin-empty">Aún no hay pedidos.</td></tr><?php endif;?></tbody></table></div></section>
</main></div></body></html>