<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/funciones.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/esquema.php';
protegerPanelAdmin();
asegurarEsquemaBoutique($conexion);
$empresa = obtenerEmpresa($conexion);
$admin = adminActual();

function irClientes($m, $t = 'ok') {
    $_SESSION['admin_mensaje'] = $m;
    $_SESSION['admin_tipo_mensaje'] = $t;
    header('Location: clientes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $a = $_POST['accion'] ?? '';
        $id = (int)($_POST['id_cliente'] ?? 0);
        if ($a === 'guardar') {
            $n = trim($_POST['nombre_cliente'] ?? '');
            $tel = trim($_POST['telefono_cliente'] ?? '');
            $tel2 = trim($_POST['telefono_secundario'] ?? '');
            $em = trim($_POST['email_cliente'] ?? '');
            $dir = trim($_POST['direccion_cliente'] ?? '');
            if ($n === '' || $tel === '') throw new RuntimeException('Nombre y teléfono principal son obligatorios.');
            $dup = $conexion->prepare('SELECT id_cliente,nombre_cliente FROM clientes WHERE telefono_cliente=:t AND id_cliente<>:id LIMIT 1');
            $dup->execute(['t' => $tel, 'id' => $id]);
            if ($existente = $dup->fetch()) throw new RuntimeException('Ese teléfono principal ya pertenece a ' . $existente['nombre_cliente'] . '. Puedes editar ese cliente en lugar de duplicarlo.');
            if ($tel2 !== '' && $tel2 === $tel) throw new RuntimeException('El teléfono secundario debe ser diferente al principal.');
            if ($id) {
                $q = $conexion->prepare('UPDATE clientes SET nombre_cliente=:n,telefono_cliente=:t,telefono_secundario=:t2,email_cliente=:e,direccion_cliente=:d,estado_cliente=1 WHERE id_cliente=:id');
                $q->execute(['n' => $n, 't' => $tel, 't2' => $tel2 ?: null, 'e' => $em ?: null, 'd' => $dir ?: null, 'id' => $id]);
            } else {
                $q = $conexion->prepare('INSERT INTO clientes(nombre_cliente,telefono_cliente,telefono_secundario,email_cliente,direccion_cliente) VALUES(:n,:t,:t2,:e,:d)');
                $q->execute(['n' => $n, 't' => $tel, 't2' => $tel2 ?: null, 'e' => $em ?: null, 'd' => $dir ?: null]);
            }
            irClientes($id ? 'Cliente actualizado.' : 'Cliente registrado.');
        }
        if ($a === 'eliminar' && $id) {
            $conexion->prepare('DELETE FROM clientes WHERE id_cliente=:id')->execute(['id' => $id]);
            irClientes('Cliente eliminado correctamente.');
        }
    } catch (Throwable $e) {
        irClientes('No se pudo guardar: ' . $e->getMessage(), 'error');
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $q = $conexion->prepare('SELECT * FROM clientes WHERE id_cliente=:id');
    $q->execute(['id' => (int)$_GET['editar']]);
    $editar = $q->fetch();
}
$buscar = trim($_GET['buscar'] ?? '');
$q = $conexion->prepare("SELECT * FROM clientes WHERE nombre_cliente LIKE :b OR telefono_cliente LIKE :b OR COALESCE(telefono_secundario,'') LIKE :b OR COALESCE(email_cliente,'') LIKE :b ORDER BY id_cliente DESC");
$q->execute(['b' => '%' . $buscar . '%']);
$clientes = $q->fetchAll();
$mensaje = $_SESSION['admin_mensaje'] ?? '';
$tipo = $_SESSION['admin_tipo_mensaje'] ?? 'ok';
unset($_SESSION['admin_mensaje'], $_SESSION['admin_tipo_mensaje']);

$tituloPagina = 'Clientes | ' . ($empresa['nombre_empresa'] ?? 'Boutique');
$paginaActivaAdmin = 'clientes';
$kickerAdmin = 'Personas';
$tituloSeccionAdmin = 'Clientes';
$subtituloSeccionAdmin = 'Registra y consulta los datos de tus compradores.';
$mensaje = $mensaje; $tipoMensaje = $tipo;
$cajaLateralAdmin = ['titulo' => 'Clientes', 'valor' => count($clientes), 'nota' => 'registros encontrados'];
$accesosRapidosAdmin = ['Nuevo cliente' => '#form-cliente', 'Ver listado' => '#tabla-clientes'];
include __DIR__ . '/parciales/layout-inicio.php';
?>

<section class="admin-crud-grid compact-form-grid">
    <article class="admin-panel-card admin-form-card" id="form-cliente">
        <h2><?= $editar ? 'Editar cliente' : 'Nuevo cliente' ?></h2>
        <form method="post" class="admin-form">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id_cliente" value="<?= intval($editar['id_cliente'] ?? 0) ?>">
            <label>Nombre<input name="nombre_cliente" required value="<?= htmlspecialchars($editar['nombre_cliente'] ?? '') ?>"></label>
            <label>Teléfono principal / WhatsApp<input name="telefono_cliente" required value="<?= htmlspecialchars($editar['telefono_cliente'] ?? '') ?>"></label>
            <label>Teléfono secundario (opcional)<input name="telefono_secundario" value="<?= htmlspecialchars($editar['telefono_secundario'] ?? '') ?>"></label>
            <label>Correo<input type="email" name="email_cliente" value="<?= htmlspecialchars($editar['email_cliente'] ?? '') ?>"></label>
            <label>Dirección<textarea name="direccion_cliente" rows="3"><?= htmlspecialchars($editar['direccion_cliente'] ?? '') ?></textarea></label>
            <div class="admin-form-actions">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <?php if ($editar): ?><a class="btn btn-ghost" href="clientes.php">Cancelar</a><?php endif; ?>
            </div>
        </form>
    </article>

    <article class="admin-panel-card admin-table-card" id="tabla-clientes">
        <div class="admin-panel-header">
            <div><h2>Listado</h2><p><?= count($clientes) ?> cliente(s)</p></div>
            <form class="admin-search"><input name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar..."><button>Buscar</button></form>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Cliente</th><th>Teléfonos</th><th>Correo</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['nombre_cliente']) ?></strong></td>
                        <td>
                            <?= htmlspecialchars($c['telefono_cliente']) ?>
                            <?php if (!empty($c['telefono_secundario'])): ?>
                                <small class="table-subtext"><?= htmlspecialchars($c['telefono_secundario']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($c['email_cliente'] ?: '—') ?></td>
                        <td><span class="admin-badge <?= $c['estado_cliente'] ? 'ok' : 'muted' ?>"><?= $c['estado_cliente'] ? 'Activo' : 'Inactivo' ?></span></td>
                        <td class="admin-actions-cell">
                            <details class="admin-actions-menu table-actions-menu">
                                <summary aria-label="Acciones" title="Acciones">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                </summary>
                                <div class="admin-actions-dropdown">
                                    <a href="?editar=<?= $c['id_cliente'] ?>#form-cliente">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        Editar
                                    </a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar definitivamente este cliente?')">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?>">
                                        <button type="submit" class="danger-action">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$clientes): ?>
                    <tr><td colspan="5" class="admin-empty">No hay clientes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/parciales/layout-fin.php'; ?>
