<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/esquema.php';

protegerPanelAdmin();
asegurarEsquemaBoutique($conexion);

$empresa = obtenerEmpresa($conexion);
$admin = adminActual();
$estadosPermitidos = [
    'pendiente',
    'confirmado',
    'preparando',
    'entregado',
    'cancelado',
];

function redirigirPedidos(string $mensaje, string $tipo = 'ok'): void
{
    $_SESSION['admin_mensaje'] = $mensaje;
    $_SESSION['admin_tipo_mensaje'] = $tipo;
    header('Location: pedidos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'guardar' || $accion === 'actualizar') {
            $idPedido = (int) ($_POST['id_pedido'] ?? 0);
            $idCliente = (int) ($_POST['id_cliente'] ?? 0);
            $total = max(0, (float) ($_POST['total_pedido'] ?? 0));
            $tipoRetiro = ($_POST['tipo_retiro'] ?? 'local') === 'delivery'
                ? 'delivery'
                : 'local';
            $direccion = trim($_POST['direccion_entrega'] ?? '');
            $metodoPago = $_POST['metodo_pago'] ?? 'efectivo';
            $notas = trim($_POST['notas'] ?? '');

            if ($idCliente <= 0) {
                throw new RuntimeException('Selecciona un cliente registrado.');
            }

            $consultaCliente = $conexion->prepare(
                'SELECT * FROM clientes WHERE id_cliente = :id LIMIT 1'
            );
            $consultaCliente->execute(['id' => $idCliente]);
            $cliente = $consultaCliente->fetch();

            if (!$cliente) {
                throw new RuntimeException('El cliente seleccionado ya no existe.');
            }

            if ($tipoRetiro === 'delivery' && $direccion === '') {
                $direccion = trim((string) ($cliente['direccion_cliente'] ?? ''));
            }

            if ($tipoRetiro === 'delivery' && $direccion === '') {
                throw new RuntimeException('Indica una dirección para el delivery.');
            }

            $datosComunes = [
                'id_cliente' => $idCliente,
                'nombre_cliente' => $cliente['nombre_cliente'],
                'email_cliente' => $cliente['email_cliente'] ?: null,
                'telefono_cliente' => $cliente['telefono_cliente'],
                'tipo_retiro' => $tipoRetiro,
                'direccion_entrega' => $tipoRetiro === 'delivery' ? $direccion : null,
                'metodo_pago' => $metodoPago,
                'notas' => $notas !== '' ? $notas : null,
                'total_pedido' => $total,
            ];

            if ($accion === 'guardar') {
                $codigo = 'MAN-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

                $consulta = $conexion->prepare(
                    'INSERT INTO pedidos (
                        codigo_pedido,
                        id_cliente,
                        nombre_cliente,
                        email_cliente,
                        telefono_cliente,
                        tipo_retiro,
                        direccion_entrega,
                        metodo_pago,
                        notas,
                        total_pedido,
                        estado_pedido
                    ) VALUES (
                        :codigo_pedido,
                        :id_cliente,
                        :nombre_cliente,
                        :email_cliente,
                        :telefono_cliente,
                        :tipo_retiro,
                        :direccion_entrega,
                        :metodo_pago,
                        :notas,
                        :total_pedido,
                        :estado_pedido
                    )'
                );

                $consulta->execute($datosComunes + [
                    'codigo_pedido' => $codigo,
                    'estado_pedido' => 'pendiente',
                ]);

                redirigirPedidos('Pedido manual registrado.');
            }

            if ($idPedido <= 0) {
                throw new RuntimeException('Pedido inválido.');
            }

            $estado = $_POST['estado_pedido'] ?? 'pendiente';

            if (!in_array($estado, $estadosPermitidos, true)) {
                $estado = 'pendiente';
            }

            $consulta = $conexion->prepare(
                'UPDATE pedidos SET
                    id_cliente = :id_cliente,
                    nombre_cliente = :nombre_cliente,
                    email_cliente = :email_cliente,
                    telefono_cliente = :telefono_cliente,
                    tipo_retiro = :tipo_retiro,
                    direccion_entrega = :direccion_entrega,
                    metodo_pago = :metodo_pago,
                    notas = :notas,
                    total_pedido = :total_pedido,
                    estado_pedido = :estado_pedido
                WHERE id_pedido = :id_pedido'
            );

            $consulta->execute($datosComunes + [
                'estado_pedido' => $estado,
                'id_pedido' => $idPedido,
            ]);

            redirigirPedidos('Pedido actualizado correctamente.');
        }

        if ($accion === 'eliminar') {
            $idPedido = (int) ($_POST['id_pedido'] ?? 0);

            if ($idPedido <= 0) {
                throw new RuntimeException('Pedido inválido.');
            }

            $conexion->beginTransaction();

            $conexion->prepare('DELETE FROM detalle_pedido WHERE id_pedido = :id')
                ->execute(['id' => $idPedido]);
            $conexion->prepare('DELETE FROM pedidos WHERE id_pedido = :id')
                ->execute(['id' => $idPedido]);

            $conexion->commit();
            redirigirPedidos('Pedido eliminado correctamente.');
        }
    } catch (Throwable $error) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }

        redirigirPedidos('No se pudo procesar: ' . $error->getMessage(), 'error');
    }
}

$clientes = $conexion->query(
    'SELECT * FROM clientes WHERE estado_cliente = 1 ORDER BY nombre_cliente'
)->fetchAll();

$pedidoEditar = null;
$idEditar = (int) ($_GET['editar'] ?? 0);

if ($idEditar > 0) {
    $consultaEditar = $conexion->prepare(
        'SELECT * FROM pedidos WHERE id_pedido = :id LIMIT 1'
    );
    $consultaEditar->execute(['id' => $idEditar]);
    $pedidoEditar = $consultaEditar->fetch() ?: null;
}

$pedidos = $conexion->query(
    'SELECT * FROM pedidos ORDER BY id_pedido DESC'
)->fetchAll();

$mensaje = $_SESSION['admin_mensaje'] ?? '';
$tipoMensaje = $_SESSION['admin_tipo_mensaje'] ?? 'ok';
unset($_SESSION['admin_mensaje'], $_SESSION['admin_tipo_mensaje']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos | Boutique</title>
    <link rel="stylesheet" href="../assets/css/estilos.css?v=16.0">
</head>
<body class="admin-body">
<div class="admin-layout clean-admin">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-brand">
            <span class="admin-logo-mini">BG</span>
            <div>
                <strong><?= htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?></strong>
                <small>Administración</small>
            </div>
        </div>

        <nav class="admin-nav" aria-label="Menú administrador">
            <a href="administrador.php">Dashboard</a>
            <a href="productos.php">Productos</a>
            <a href="clientes.php">Clientes</a>
            <a class="activo" href="pedidos.php">Pedidos</a>
            <a href="categorias.php">Categorías</a>
            <a href="marcas.php">Marcas</a>
            <a href="../index.php" target="_blank">Ver tienda</a>
        </nav>

        <div class="admin-sidebar-footer">
            <span><?= htmlspecialchars($admin['nombre'] ?? 'Admin') ?></span>
            <a href="logout.php">Salir</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <span class="admin-kicker">Ventas</span>
                <h1>Pedidos</h1>
                <p>Registra pedidos presenciales y controla los recibidos desde la tienda.</p>
            </div>
        </header>

        <?php if ($mensaje): ?>
            <div class="admin-alert <?= $tipoMensaje === 'error' ? 'error' : 'ok' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <section class="admin-crud-grid compact-form-grid">
            <article class="admin-panel-card admin-form-card" id="form-pedido">
                <h2><?= $pedidoEditar ? 'Editar pedido' : 'Nuevo pedido' ?></h2>
                <p>
                    <?= $pedidoEditar
                        ? 'Actualiza los datos y el estado del pedido seleccionado.'
                        : 'Selecciona un cliente previamente registrado.' ?>
                </p>

                <form method="post" class="admin-form">
                    <input
                        type="hidden"
                        name="accion"
                        value="<?= $pedidoEditar ? 'actualizar' : 'guardar' ?>"
                    >

                    <?php if ($pedidoEditar): ?>
                        <input
                            type="hidden"
                            name="id_pedido"
                            value="<?= (int) $pedidoEditar['id_pedido'] ?>"
                        >
                    <?php endif; ?>

                    <label>
                        Cliente
                        <select name="id_cliente" id="clientePedido" required>
                            <option value="">Seleccionar cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option
                                    value="<?= (int) $cliente['id_cliente'] ?>"
                                    data-direccion="<?= htmlspecialchars(
                                        $cliente['direccion_cliente'] ?? '',
                                        ENT_QUOTES
                                    ) ?>"
                                    <?= $pedidoEditar
                                        && (int) $pedidoEditar['id_cliente'] === (int) $cliente['id_cliente']
                                            ? 'selected'
                                            : '' ?>
                                >
                                    <?= htmlspecialchars(
                                        $cliente['nombre_cliente'] . ' · ' . $cliente['telefono_cliente']
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <div class="admin-form-row">
                        <label>
                            Retiro
                            <select name="tipo_retiro" id="retiroManual">
                                <option
                                    value="local"
                                    <?= $pedidoEditar && $pedidoEditar['tipo_retiro'] === 'local'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Local
                                </option>
                                <option
                                    value="delivery"
                                    <?= $pedidoEditar && $pedidoEditar['tipo_retiro'] === 'delivery'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Delivery
                                </option>
                            </select>
                        </label>

                        <label>
                            Total
                            <input
                                type="number"
                                min="0"
                                step="1000"
                                name="total_pedido"
                                value="<?= htmlspecialchars(
                                    (string) ($pedidoEditar['total_pedido'] ?? 0)
                                ) ?>"
                            >
                        </label>
                    </div>

                    <label>
                        Dirección
                        <input
                            name="direccion_entrega"
                            id="direccionManual"
                            value="<?= htmlspecialchars(
                                (string) ($pedidoEditar['direccion_entrega'] ?? '')
                            ) ?>"
                        >
                    </label>

                    <label>
                        Pago
                        <select name="metodo_pago">
                            <?php
                            $metodosPago = [
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia',
                                'whatsapp' => 'Coordinar',
                            ];
                            ?>
                            <?php foreach ($metodosPago as $valor => $texto): ?>
                                <option
                                    value="<?= $valor ?>"
                                    <?= $pedidoEditar && $pedidoEditar['metodo_pago'] === $valor
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= $texto ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <?php if ($pedidoEditar): ?>
                        <label>
                            Estado
                            <select name="estado_pedido">
                                <?php foreach ($estadosPermitidos as $estado): ?>
                                    <option
                                        value="<?= $estado ?>"
                                        <?= $pedidoEditar['estado_pedido'] === $estado
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= ucfirst($estado) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php endif; ?>

                    <label>
                        Notas
                        <textarea name="notas" rows="3"><?= htmlspecialchars(
                            (string) ($pedidoEditar['notas'] ?? '')
                        ) ?></textarea>
                    </label>

                    <button class="admin-btn primary">
                        <?= $pedidoEditar ? 'Guardar cambios' : 'Registrar pedido' ?>
                    </button>

                    <?php if ($pedidoEditar): ?>
                        <a class="admin-btn secondary edit-cancel" href="pedidos.php">
                            Cancelar edición
                        </a>
                    <?php endif; ?>
                </form>
            </article>

            <article class="admin-panel-card orders-card">
                <div class="admin-panel-header">
                    <div>
                        <h2>Todos los pedidos</h2>
                        <p><?= count($pedidos) ?> registro(s)</p>
                    </div>
                </div>

                <div class="orders-list">
                    <?php foreach ($pedidos as $pedido): ?>
                        <article class="order-card">
                            <div class="order-card-main">
                                <div class="order-code-block">
                                    <span class="order-label">Pedido</span>
                                    <strong><?= htmlspecialchars($pedido['codigo_pedido']) ?></strong>
                                </div>

                                <div class="order-client-block">
                                    <span class="order-label">Cliente</span>
                                    <strong><?= htmlspecialchars($pedido['nombre_cliente']) ?></strong>
                                    <small><?= htmlspecialchars($pedido['telefono_cliente']) ?></small>
                                </div>

                                <div class="order-total-block">
                                    <span class="order-label">Total</span>
                                    <strong><?= formatearPrecio($pedido['total_pedido']) ?></strong>
                                </div>
                            </div>

                            <div class="order-card-side">
                                <div class="order-status-display">
                                    <span class="order-label">Estado</span>
                                    <span class="order-status-badge status-<?= htmlspecialchars(
                                        $pedido['estado_pedido']
                                    ) ?>">
                                        <?= ucfirst(htmlspecialchars($pedido['estado_pedido'])) ?>
                                    </span>
                                </div>

                                <time
                                    class="order-date"
                                    datetime="<?= date('c', strtotime($pedido['fecha_pedido'])) ?>"
                                >
                                    <span class="order-label">Fecha</span>
                                    <strong><?= date('d/m/Y', strtotime($pedido['fecha_pedido'])) ?></strong>
                                    <small><?= date('H:i', strtotime($pedido['fecha_pedido'])) ?></small>
                                </time>

                                <details class="admin-actions-menu table-actions-menu order-menu">
                                    <summary aria-label="Acciones" title="Acciones">•••</summary>
                                    <div class="admin-actions-dropdown">
                                        <a href="pedidos.php?editar=<?= (int) $pedido['id_pedido'] ?>#form-pedido">
                                            <span aria-hidden="true">✎</span>
                                            Editar
                                        </a>
                                        <form
                                            method="post"
                                            onsubmit="return confirm('¿Eliminar definitivamente este pedido?')"
                                        >
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input
                                                type="hidden"
                                                name="id_pedido"
                                                value="<?= (int) $pedido['id_pedido'] ?>"
                                            >
                                            <button type="submit" class="danger-action">
                                                <span aria-hidden="true">🗑</span>
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php if (!$pedidos): ?>
                        <div class="admin-empty orders-empty">
                            No hay pedidos registrados.
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    </main>
</div>

<script>
    const cliente = document.getElementById('clientePedido');
    const direccion = document.getElementById('direccionManual');

    cliente?.addEventListener('change', () => {
        const opcion = cliente.options[cliente.selectedIndex];

        if (opcion?.dataset.direccion && !direccion.value) {
            direccion.value = opcion.dataset.direccion;
        }
    });
</script>
</body>
</html>
