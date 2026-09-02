<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/funciones.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/esquema.php';
protegerPanelAdmin();
asegurarEsquemaBoutique($conexion);
$empresa = obtenerEmpresa($conexion);
$admin = adminActual();

function irCategorias($m, $t = 'ok') {
    $_SESSION['admin_mensaje'] = $m;
    $_SESSION['admin_tipo_mensaje'] = $t;
    header('Location: categorias.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $a = $_POST['accion'] ?? '';
        $id = (int)($_POST['id_categoria'] ?? 0);
        if ($a === 'guardar') {
            $n = trim($_POST['nombre_categoria'] ?? '');
            $d = trim($_POST['descripcion_categoria'] ?? '');
            if ($n === '') throw new RuntimeException('Escribe el nombre de la categoría.');
            $dup = $conexion->prepare('SELECT id_categoria FROM categorias WHERE LOWER(nombre_categoria)=LOWER(:n) AND id_categoria<>:id LIMIT 1');
            $dup->execute(['n' => $n, 'id' => $id]);
            if ($dup->fetch()) throw new RuntimeException('Ya existe una categoría con ese nombre.');
            if ($id) {
                $q = $conexion->prepare('UPDATE categorias SET nombre_categoria=:n,descripcion_categoria=:d,estado_categoria=1 WHERE id_categoria=:id');
                $q->execute(['n' => $n, 'd' => $d ?: null, 'id' => $id]);
            } else {
                $q = $conexion->prepare('INSERT INTO categorias(nombre_categoria,descripcion_categoria,estado_categoria) VALUES(:n,:d,1)');
                $q->execute(['n' => $n, 'd' => $d ?: null]);
            }
            irCategorias($id ? 'Categoría actualizada.' : 'Categoría creada.');
        }
        if ($a === 'eliminar' && $id) {
            $q = $conexion->prepare('SELECT COUNT(*) FROM productos WHERE id_categoria=:id');
            $q->execute(['id' => $id]);
            if ((int)$q->fetchColumn() > 0) throw new RuntimeException('No se puede eliminar porque tiene productos asociados. Reasigna esos productos primero.');
            $conexion->prepare('DELETE FROM categorias WHERE id_categoria=:id')->execute(['id' => $id]);
            irCategorias('Categoría eliminada.');
        }
    } catch (Throwable $e) {
        irCategorias('No se pudo procesar: ' . $e->getMessage(), 'error');
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $q = $conexion->prepare('SELECT * FROM categorias WHERE id_categoria=:id');
    $q->execute(['id' => (int)$_GET['editar']]);
    $editar = $q->fetch();
}
$categorias = $conexion->query('SELECT * FROM categorias ORDER BY nombre_categoria')->fetchAll();
$mensaje = $_SESSION['admin_mensaje'] ?? '';
$tipo = $_SESSION['admin_tipo_mensaje'] ?? 'ok';
unset($_SESSION['admin_mensaje'], $_SESSION['admin_tipo_mensaje']);

$tituloPagina = 'Categorías | ' . ($empresa['nombre_empresa'] ?? 'Boutique');
$paginaActivaAdmin = 'categorias';
$kickerAdmin = 'Catálogo';
$tituloSeccionAdmin = 'Categorías';
$subtituloSeccionAdmin = 'Crea y organiza las categorías disponibles en la tienda.';
$tipoMensaje = $tipo;
include __DIR__ . '/parciales/layout-inicio.php';
?>

<section class="admin-crud-grid compact-form-grid">
    <article class="admin-panel-card admin-form-card">
        <h2><?= $editar ? 'Editar categoría' : 'Nueva categoría' ?></h2>
        <form method="post" class="admin-form">
            <input type="hidden" name="accion" value="guardar">
            <input type="hidden" name="id_categoria" value="<?= intval($editar['id_categoria'] ?? 0) ?>">
            <label>Nombre<input name="nombre_categoria" required value="<?= htmlspecialchars($editar['nombre_categoria'] ?? '') ?>"></label>
            <label>Descripción<textarea name="descripcion_categoria" rows="4"><?= htmlspecialchars($editar['descripcion_categoria'] ?? '') ?></textarea></label>
            <div class="admin-form-actions">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <?php if ($editar): ?><a class="btn btn-ghost" href="categorias.php">Cancelar</a><?php endif; ?>
            </div>
        </form>
    </article>
    <article class="admin-panel-card admin-table-card">
        <div class="admin-panel-header"><div><h2>Listado</h2><p><?= count($categorias) ?> categoría(s)</p></div></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['nombre_categoria']) ?></strong></td>
                        <td><?= htmlspecialchars($c['descripcion_categoria'] ?? '—') ?></td>
                        <td><span class="admin-badge <?= ($c['estado_categoria'] ?? 1) ? 'ok' : 'muted' ?>"><?= ($c['estado_categoria'] ?? 1) ? 'Activa' : 'Inactiva' ?></span></td>
                        <td class="admin-actions-cell">
                            <details class="admin-actions-menu table-actions-menu">
                                <summary aria-label="Acciones" title="Acciones">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                                </summary>
                                <div class="admin-actions-dropdown">
                                    <a href="?editar=<?= $c['id_categoria'] ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        Editar
                                    </a>
                                    <form method="post" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_categoria" value="<?= $c['id_categoria'] ?>">
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
                <?php if (!$categorias): ?><tr><td colspan="4" class="admin-empty">No hay categorías.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<?php include __DIR__ . '/parciales/layout-fin.php'; ?>
