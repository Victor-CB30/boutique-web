<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/funciones.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/esquema.php';
protegerPanelAdmin();
asegurarEsquemaBoutique($conexion);

$admin = adminActual();
$empresa = obtenerEmpresa($conexion);

$totalProductos = (int)$conexion->query("SELECT COUNT(*) FROM productos WHERE estado_producto=1")->fetchColumn();
$totalClientes  = (int)$conexion->query("SELECT COUNT(*) FROM clientes WHERE estado_cliente=1")->fetchColumn();
$totalPedidos   = (int)$conexion->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$pendientes     = (int)$conexion->query("SELECT COUNT(*) FROM pedidos WHERE estado_pedido='pendiente'")->fetchColumn();
$pedidos        = $conexion->query("SELECT * FROM pedidos ORDER BY id_pedido DESC LIMIT 8")->fetchAll();

$tituloPagina = 'Panel | ' . ($empresa['nombre_empresa'] ?? 'Boutique');
$paginaActivaAdmin = 'dashboard';
$kickerAdmin = 'Resumen';
$tituloSeccionAdmin = 'Panel de control';
$subtituloSeccionAdmin = 'Accesos rápidos para clientes, pedidos y catálogo.';
$mostrarUsuarioAdmin = true;
$cajaLateralAdmin = ['titulo' => 'Resumen', 'valor' => $totalPedidos, 'nota' => 'pedidos registrados'];
$accesosRapidosAdmin = ['Cliente' => 'clientes.php#form-cliente', 'Pedido' => 'pedidos.php#form-pedido', 'Producto' => 'productos.php#form-producto'];
include __DIR__ . '/parciales/layout-inicio.php';
?>

<section class="admin-stats-grid">
    <article><span>Productos</span><strong><?= $totalProductos ?></strong></article>
    <article><span>Clientes</span><strong><?= $totalClientes ?></strong></article>
    <article><span>Pedidos</span><strong><?= $totalPedidos ?></strong></article>
    <article><span>Pendientes</span><strong><?= $pendientes ?></strong></article>
</section>

<section class="admin-panel-card">
    <div class="admin-panel-header">
        <div><h2>Pedidos recientes</h2><p>Últimos pedidos registrados.</p></div>
        <a class="btn btn-ghost btn-sm" href="pedidos.php">Ver todos</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>Código</th><th>Cliente</th><th>Retiro</th><th>Total</th><th>Estado</th><th>Fecha</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['codigo_pedido']) ?></strong></td>
                    <td><?= htmlspecialchars($p['nombre_cliente']) ?></td>
                    <td><?= $p['tipo_retiro'] === 'delivery' ? 'Delivery' : 'Local' ?></td>
                    <td><?= formatearPrecio($p['total_pedido']) ?></td>
                    <td><span class="admin-badge <?= $p['estado_pedido'] === 'pendiente' ? 'muted' : 'ok' ?>"><?= htmlspecialchars(ucfirst($p['estado_pedido'])) ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$pedidos): ?>
                <tr><td colspan="6" class="admin-empty">Aún no hay pedidos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/parciales/layout-fin.php'; ?>
