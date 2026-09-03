<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/funciones.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/esquema.php';
asegurarEsquemaBoutique($conexion);

protegerPanelAdmin();

$rutaBase = '../';
$empresa = obtenerEmpresa($conexion);
$admin = adminActual();
$tituloPagina = 'Gestión de productos | ' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique');

function columnaExiste(PDO $conexion, string $tabla, string $columna): bool {
    $stmt = $conexion->prepare("SHOW COLUMNS FROM {$tabla} LIKE :columna");
    $stmt->execute(['columna' => $columna]);
    return (bool)$stmt->fetch();
}

try {
    if (!columnaExiste($conexion, 'productos', 'stock_producto')) {
        $conexion->exec("ALTER TABLE productos ADD COLUMN stock_producto INT NOT NULL DEFAULT 0 AFTER precio_producto");
    }
} catch (Throwable $e) {
    // El SQL mejorado también agrega esta columna. Este bloque evita romper el panel si ya existe.
}

$mensaje = $_SESSION['admin_mensaje'] ?? '';
$tipoMensaje = $_SESSION['admin_tipo_mensaje'] ?? 'ok';
unset($_SESSION['admin_mensaje'], $_SESSION['admin_tipo_mensaje']);

/** TODO: conservar página y búsqueda después de editar/eliminar. */
function redirigirProductos(string $mensaje, string $tipo = 'ok', ?string $retorno = null): void {
    $_SESSION['admin_mensaje'] = $mensaje;
    $_SESSION['admin_tipo_mensaje'] = $tipo;
    $retorno = $retorno ?? ($_POST['return_to'] ?? 'productos.php');
    if (!is_string($retorno) || !preg_match('/^productos\.php(?:\?[^\r\n]*)?(?:#tabla-productos)?$/', $retorno)) {
        $retorno = 'productos.php';
    }
    header('Location: ' . $retorno);
    exit;
}

function guardarImagenProducto(array $archivo): ?string {
    if (empty($archivo['name']) || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($archivo['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se pudo subir la imagen seleccionada.');
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $permitidos, true)) {
        throw new RuntimeException('Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.');
    }

    if (($archivo['size'] ?? 0) > 4 * 1024 * 1024) {
        throw new RuntimeException('La imagen no debe superar los 4 MB.');
    }

    $directorio = __DIR__ . '/../assets/img/productos/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0775, true);
    }

    $nombreSeguro = 'producto_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destino = $directorio . $nombreSeguro;

    if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
        throw new RuntimeException('No se pudo guardar la imagen en la carpeta de productos.');
    }

    return 'assets/img/productos/' . $nombreSeguro;
}


function guardarVariantesProducto(PDO $conexion, int $idProducto, string $tallasTexto, string $coloresTexto, int $stock): void {
    $separar = static function(string $texto): array {
        $items = preg_split('/[,;\n]+/', $texto);
        return array_values(array_unique(array_filter(array_map('trim', $items))));
    };
    $conexion->prepare('DELETE FROM tallas_producto WHERE id_producto=:id')->execute(['id'=>$idProducto]);
    $conexion->prepare('DELETE FROM colores_producto WHERE id_producto=:id')->execute(['id'=>$idProducto]);
    $tallas=$separar($tallasTexto); $colores=$separar($coloresTexto);
    $stockTalla=$tallas ? max(0,(int)floor($stock/count($tallas))) : 0;
    $stockColor=$colores ? max(0,(int)floor($stock/count($colores))) : 0;
    $st=$conexion->prepare('INSERT INTO tallas_producto (id_producto,nombre_talla,stock_talla) VALUES (:id,:nombre,:stock)');
    foreach($tallas as $nombre){$st->execute(['id'=>$idProducto,'nombre'=>$nombre,'stock'=>$stockTalla]);}
    $sc=$conexion->prepare('INSERT INTO colores_producto (id_producto,nombre_color,stock_color) VALUES (:id,:nombre,:stock)');
    foreach($colores as $nombre){$sc->execute(['id'=>$idProducto,'nombre'=>$nombre,'stock'=>$stockColor]);}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'guardar') {
            $id = (int)($_POST['id_producto'] ?? 0);
            $nombre = trim($_POST['nombre_producto'] ?? '');
            $descripcion = trim($_POST['descripcion_producto'] ?? '');
            $idCategoria = (int)($_POST['id_categoria'] ?? 0);
            $idMarca = (int)($_POST['id_marca'] ?? 0);
            $precio = (float)($_POST['precio_producto'] ?? 0);
            $stock = max(0, (int)($_POST['stock_producto'] ?? 0));
            $imagen = trim($_POST['imagen_principal'] ?? '');
            $imagenSubida = guardarImagenProducto($_FILES['imagen_local'] ?? []);
            if ($imagenSubida !== null) {
                $imagen = $imagenSubida;
            }
            $estado = (int)($_POST['estado_producto'] ?? 1);
            $tallasTexto = trim($_POST['tallas_producto'] ?? '');
            $coloresTexto = trim($_POST['colores_producto'] ?? '');

            if ($nombre === '' || $idCategoria <= 0 || $precio <= 0) {
                redirigirProductos('Completa nombre, categoría y precio correctamente.', 'error');
            }

            if ($id > 0) {
                $stmt = $conexion->prepare("UPDATE productos SET
                    id_categoria = :id_categoria,
                    id_marca = :id_marca,
                    nombre_producto = :nombre,
                    descripcion_producto = :descripcion,
                    precio_producto = :precio,
                    stock_producto = :stock,
                    imagen_principal = :imagen,
                    estado_producto = :estado
                    WHERE id_producto = :id");
                $stmt->execute([
                    'id_categoria' => $idCategoria,
                    'id_marca' => $idMarca ?: null,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'stock' => $stock,
                    'imagen' => $imagen,
                    'estado' => $estado,
                    'id' => $id,
                ]);
                guardarVariantesProducto($conexion, $id, $tallasTexto, $coloresTexto, $stock);
                redirigirProductos('Producto actualizado correctamente.');
            }
            /* Quiero que modifiques la fuente de la tipografia principal del sitio web por este (calligraphy) y los subtitulos por este (caneletter-sans).
            Si crees que estas dos tipografias no se veria bien juntas, puedes cambiarlo por otra fuente mas elegante que conozcas o puedes elegir de esta web "https://www.1001freefonts.com/es/fancy-fonts-3.php"
            para uso personal. y retornarme el archivo en .zip para ver los cambios hechos.  */

            $stmt = $conexion->prepare("INSERT INTO productos
                (id_categoria, id_marca, nombre_producto, descripcion_producto, precio_producto, stock_producto, imagen_principal, estado_producto)
                VALUES (:id_categoria, :id_marca, :nombre, :descripcion, :precio, :stock, :imagen, :estado)");
            $stmt->execute([
                'id_categoria' => $idCategoria,
                'id_marca' => $idMarca ?: null,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'stock' => $stock,
                'imagen' => $imagen,
                'estado' => $estado,
            ]);
            $nuevoId = (int)$conexion->lastInsertId();
            guardarVariantesProducto($conexion, $nuevoId, $tallasTexto, $coloresTexto, $stock);
            redirigirProductos('Producto registrado correctamente.');
        }

        if ($accion === 'eliminar') {
            $id = (int)($_POST['id_producto'] ?? 0);
            if ($id > 0) {
                $conexion->beginTransaction();
                $conexion->prepare('DELETE FROM tallas_producto WHERE id_producto=:id')->execute(['id'=>$id]);
                $conexion->prepare('DELETE FROM colores_producto WHERE id_producto=:id')->execute(['id'=>$id]);
                $stmt = $conexion->prepare("DELETE FROM productos WHERE id_producto = :id");
                $stmt->execute(['id' => $id]);
                $conexion->commit();
                redirigirProductos('Producto eliminado correctamente.');
            }
        }
    } catch (Throwable $e) {
        if ($conexion->inTransaction()) { $conexion->rollBack(); }
        $msg = str_contains($e->getMessage(), 'foreign key constraint') ? 'No se puede eliminar este producto porque forma parte de un pedido registrado.' : $e->getMessage();
        redirigirProductos('No se pudo procesar la operación: ' . $msg, 'error');
    }
}

$idEditar = isset($_GET['editar']) ? (int)$_GET['editar'] : 0;
$productoEditar = null;
$tallasEditar = ''; $coloresEditar = '';
if ($idEditar > 0) {
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id_producto = :id LIMIT 1");
    $stmt->execute(['id' => $idEditar]);
    $productoEditar = $stmt->fetch();
    $q=$conexion->prepare('SELECT nombre_talla FROM tallas_producto WHERE id_producto=:id ORDER BY id_talla');$q->execute(['id'=>$idEditar]);$tallasEditar=implode(', ',array_column($q->fetchAll(),'nombre_talla'));
    $q=$conexion->prepare('SELECT nombre_color FROM colores_producto WHERE id_producto=:id ORDER BY id_color');$q->execute(['id'=>$idEditar]);$coloresEditar=implode(', ',array_column($q->fetchAll(),'nombre_color'));
}

$categorias = obtenerCategoriasActivas($conexion);
$marcas = obtenerMarcasActivas($conexion);
$busqueda = trim($_GET['buscar'] ?? '');

$where = '';
$params = [];
if ($busqueda !== '') {
    $where = " WHERE p.nombre_producto LIKE :busqueda OR c.nombre_categoria LIKE :busqueda OR m.nombre_marca LIKE :busqueda";
    $params['busqueda'] = '%' . $busqueda . '%';
}

$stmtTotal = $conexion->prepare("SELECT COUNT(*)
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN marcas m ON p.id_marca = m.id_marca" . $where);
$stmtTotal->execute($params);
$totalProductos = (int)$stmtTotal->fetchColumn();

$porPagina = 10;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$totalPaginas = max(1, (int)ceil($totalProductos / $porPagina));
if ($pagina > $totalPaginas) $pagina = $totalPaginas;
$offset = ($pagina - 1) * $porPagina;
$parametrosRetorno = ['pagina' => $pagina];
if ($busqueda !== '') { $parametrosRetorno['buscar'] = $busqueda; }
$retornoListado = 'productos.php?' . http_build_query($parametrosRetorno) . '#tabla-productos';

$sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN marcas m ON p.id_marca = m.id_marca" . $where . "
        ORDER BY p.id_producto DESC
        LIMIT :limite OFFSET :offset";
$stmt = $conexion->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$productos = $stmt->fetchAll();

function badgeEstadoProducto(int $estado): string {
    return $estado === 1 ? '<span class="admin-badge ok">Activo</span>' : '<span class="admin-badge muted">Inactivo</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="icon" type="image/webp" href="../assets/img/favicon-boutique.jpg">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="../assets/css/estilos.css?v=16.0">
</head>
<body class="admin-body">
<div class="admin-layout clean-admin products-admin-layout">
    <aside class="admin-sidebar admin-sidebar-full">
        <div class="admin-sidebar-brand">
            <span class="admin-logo-mini">BG</span>
            <div><strong><?= htmlspecialchars($empresa['nombre_empresa'] ?? 'Boutique') ?></strong><small>Administración</small></div>
        </div>
        <nav class="admin-nav" aria-label="Menú administrador">
            <a  href="administrador.php">Dashboard</a>
            <a class="activo" href="productos.php">Productos</a>
            <a  href="clientes.php">Clientes</a>
            <a  href="pedidos.php">Pedidos</a>
            <a href="categorias.php">Categorías</a>
            <a href="marcas.php">Marcas</a>
            <a href="../index.php" target="_blank">Ver tienda</a>
        </nav>

        <div class="admin-sidebar-box">
            <span>Catálogo</span>
            <strong><?= $totalProductos ?></strong>
            <small>productos registrados</small>
        </div>

        <div class="admin-sidebar-shortcuts">
            <a href="#form-producto">+ Nuevo producto</a>
            <a href="#tabla-productos">Ver listado</a>
            <a href="productos.php?buscar=">Limpiar búsqueda</a>
        </div>

        <div class="admin-sidebar-footer">
            <span><?= htmlspecialchars($admin['rol'] ?? 'Administrador') ?></span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </aside>

    <main class="admin-main products-admin-main">
        <header class="admin-topbar admin-topbar-actions">
            <div>
                <span class="admin-kicker">Catálogo</span>
                <h1>Gestión de productos</h1>
                <p>Administra productos, imágenes, precios y stock desde una sola pantalla.</p>
            </div>
            <div class="admin-user-card"><span class="admin-avatar"><?= strtoupper(substr($admin['nombre'] ?? 'A', 0, 1)) ?></span><div><strong><?= htmlspecialchars($admin['nombre'] ?? 'Administrador') ?></strong><small><?= htmlspecialchars($admin['email'] ?? '') ?></small></div></div>
        </header>

        <?php if ($mensaje): ?>
            <div class="admin-alert <?= $tipoMensaje === 'error' ? 'error' : 'ok' ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <section class="admin-crud-grid">
            <article class="admin-panel-card admin-form-card" id="form-producto">
                <h2><?= $productoEditar ? 'Editar producto' : 'Nuevo producto' ?></h2>
                <p><?= $productoEditar ? 'Modifica los datos principales del producto seleccionado.' : 'Carga una nueva prenda al catálogo de la tienda.' ?></p>
                <form method="POST" class="admin-form" enctype="multipart/form-data">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($retornoListado) ?>">
                    <input type="hidden" name="id_producto" value="<?= (int)($productoEditar['id_producto'] ?? 0) ?>">
                    <label>Nombre del producto
                        <input type="text" name="nombre_producto" value="<?= htmlspecialchars($productoEditar['nombre_producto'] ?? '') ?>" required>
                    </label>
                    <div class="admin-form-row">
                        <label>Categoría
                            <select name="id_categoria" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= (int)$cat['id_categoria'] ?>" <?= (int)($productoEditar['id_categoria'] ?? 0) === (int)$cat['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Marca
                            <select name="id_marca">
                                <option value="">Sin marca</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?= (int)$marca['id_marca'] ?>" <?= (int)($productoEditar['id_marca'] ?? 0) === (int)$marca['id_marca'] ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre_marca']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="admin-form-row">
                        <label>Precio
                            <input type="number" name="precio_producto" min="0" step="1000" value="<?= htmlspecialchars($productoEditar['precio_producto'] ?? '') ?>" required>
                        </label>
                        <label>Stock
                            <input type="number" name="stock_producto" min="0" step="1" value="<?= htmlspecialchars($productoEditar['stock_producto'] ?? 0) ?>" required>
                        </label>
                    </div>
                    <div class="admin-form-row">
                        <label>Tallas disponibles
                            <input type="text" name="tallas_producto" value="<?= htmlspecialchars($tallasEditar) ?>" placeholder="XS, S, M, L, XL">
                            <small>Separar por comas.</small>
                        </label>
                        <label>Colores disponibles
                            <input type="text" name="colores_producto" value="<?= htmlspecialchars($coloresEditar) ?>" placeholder="Negro, Blanco, Beige">
                            <small>Separar por comas.</small>
                        </label>
                    </div>
                    <div class="admin-image-tools">
                        <label>Imagen principal por link
                            <input type="text" name="imagen_principal" value="<?= htmlspecialchars($productoEditar['imagen_principal'] ?? '') ?>" placeholder="https://... o assets/img/productos/imagen.jpg">
                        </label>
                        <label class="admin-upload-tray" for="imagen_local">
                            <input type="file" id="imagen_local" name="imagen_local" accept="image/*">
                            <span class="upload-icon">⇧</span>
                            <strong>Subir imagen desde tu PC</strong>
                            <small>Arrastra una foto aquí o haz clic para seleccionarla. Formatos: JPG, PNG, WEBP o GIF.</small>
                        </label>
                        <div class="admin-image-preview" id="adminImagePreview">
                            <img src="<?= htmlspecialchars(obtenerUrlImagen($productoEditar['imagen_principal'] ?? '')) ?>" alt="Vista previa" onerror="<?= atributoOnErrorImagen() ?>">
                            <span>Vista previa de la imagen</span>
                        </div>
                    </div>
                    <label>Descripción
                        <textarea name="descripcion_producto" rows="4"><?= htmlspecialchars($productoEditar['descripcion_producto'] ?? '') ?></textarea>
                    </label>
                    <label>Estado
                        <select name="estado_producto">
                            <option value="1" <?= (int)($productoEditar['estado_producto'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= (int)($productoEditar['estado_producto'] ?? 1) === 0 ? 'selected' : '' ?>>Inactivo</option>
                            <option value="2" <?= (int)($productoEditar['estado_producto'] ?? 1) === 2 ? 'selected' : '' ?>>Oculto</option>
                        </select>
                    </label>
                    <div class="admin-form-actions">
                        <button class="admin-btn primary" type="submit"><?= $productoEditar ? 'Guardar cambios' : 'Registrar producto' ?></button>
                        <?php if ($productoEditar): ?><a class="admin-btn ghost" href="<?= htmlspecialchars($retornoListado) ?>">Cancelar</a><?php endif; ?>
                    </div>
                </form>
            </article>

            <article class="admin-panel-card admin-table-card" id="tabla-productos">
                <div class="admin-panel-header">
                    <div><h2>Listado de productos</h2><p>Mostrando <?= count($productos) ?> de <?= $totalProductos ?> producto(s).</p></div>
                    <form method="GET" class="admin-search"><input type="search" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar producto..."><button type="submit">Buscar</button></form>
                </div>
                <div class="admin-products-list" aria-label="Listado de productos del panel administrador">
                    <div class="admin-products-head">
                        <span>Producto</span>
                        <span>Categoría</span>
                        <span>Marca</span>
                        <span>Precio</span>
                        <span>Stock</span>
                        <span>Acciones</span>
                    </div>
                    <?php foreach ($productos as $producto): ?>
                        <div class="admin-product-row">
                            <div class="admin-product-main">
                                <img src="<?= htmlspecialchars(obtenerUrlImagen($producto['imagen_principal'] ?? '')) ?>" alt="<?= htmlspecialchars($producto['nombre_producto']) ?>" onerror="<?= atributoOnErrorImagen() ?>">
                                <div>
                                    <strong><?= htmlspecialchars($producto['nombre_producto']) ?></strong>
                                    <div class="admin-product-status"><?= badgeEstadoProducto((int)$producto['estado_producto']) ?></div>
                                </div>
                            </div>
                            <div class="admin-product-meta" data-label="Categoría"><?= htmlspecialchars($producto['nombre_categoria'] ?? 'Sin categoría') ?></div>
                            <div class="admin-product-meta" data-label="Marca"><?= htmlspecialchars($producto['nombre_marca'] ?? 'Sin marca') ?></div>
                            <div class="admin-product-meta" data-label="Precio"><?= formatearPrecio($producto['precio_producto']) ?></div>
                            <div class="admin-product-stock" data-label="Stock"><span class="admin-stock <?= (int)($producto['stock_producto'] ?? 0) <= 3 ? 'low' : '' ?>"><?= (int)($producto['stock_producto'] ?? 0) ?></span></div>
                            <div class="admin-product-actions" data-label="Acciones">
                                <details class="admin-actions-menu">
                                    <summary aria-label="Abrir acciones del producto" title="Acciones">•••</summary>
                                    <div class="admin-actions-dropdown">
                                        <a href="../producto_detalle.php?id=<?= (int)$producto['id_producto'] ?>" target="_blank">
                                            <span aria-hidden="true">👁</span> Ver producto
                                        </a>
                                        <a href="productos.php?editar=<?= (int)$producto['id_producto'] ?>&pagina=<?= $pagina ?><?= $busqueda !== '' ? '&buscar=' . urlencode($busqueda) : '' ?>#form-producto">
                                            <span aria-hidden="true">✎</span> Editar
                                        </a>
                                        <form method="POST" onsubmit="return confirm('¿Deseas eliminar definitivamente este producto?');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="id_producto" value="<?= (int)$producto['id_producto'] ?>">
                                            <input type="hidden" name="return_to" value="<?= htmlspecialchars($retornoListado) ?>">
                                            <button type="submit" class="danger-action"><span aria-hidden="true">🗑</span> Eliminar</button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$productos): ?><div class="admin-empty">No hay productos para mostrar.</div><?php endif; ?>
                </div>
                <?php if ($totalPaginas > 1): ?>
                <div class="admin-pagination">
                    <?php
                    $queryBase = $busqueda !== '' ? '&buscar=' . urlencode($busqueda) : '';
                    ?>
                    <a class="<?= $pagina <= 1 ? 'disabled' : '' ?>" href="productos.php?pagina=<?= max(1, $pagina - 1) ?><?= $queryBase ?>#tabla-productos">Anterior</a>
                    <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>
                    <a class="<?= $pagina >= $totalPaginas ? 'disabled' : '' ?>" href="productos.php?pagina=<?= min($totalPaginas, $pagina + 1) ?><?= $queryBase ?>#tabla-productos">Siguiente</a>
                </div>
                <?php endif; ?>

            </article>
        </section>
    </main>
</div>
<script>
const inputImagen = document.getElementById('imagen_local');
const preview = document.querySelector('#adminImagePreview img');
if (inputImagen && preview) {
    inputImagen.addEventListener('change', () => {
        const file = inputImagen.files && inputImagen.files[0];
        if (!file) return;
        preview.src = URL.createObjectURL(file);
    });
}
</script>
</body>
</html>
