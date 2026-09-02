<?php
// Funciones reutilizables del proyecto.

function formatearPrecio($precio)
{
    return 'Gs. ' . number_format((float)$precio, 0, ',', '.');
}

function obtenerEmpresa($conexion)
{
    $sql = "SELECT * FROM informacion_empresa LIMIT 1";
    $stmt = $conexion->query($sql);
    return $stmt->fetch();
}

function obtenerCategoriasActivas($conexion)
{
    $sql = "SELECT * FROM categorias WHERE estado_categoria = 1 ORDER BY nombre_categoria ASC";
    return $conexion->query($sql)->fetchAll();
}

function obtenerMarcasActivas($conexion)
{
    $sql = "SELECT * FROM marcas WHERE estado_marca = 1 ORDER BY nombre_marca ASC";
    return $conexion->query($sql)->fetchAll();
}

function obtenerProductos($conexion)
{
    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE p.estado_producto = 1
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->query($sql);
    return $stmt->fetchAll();
}

function obtenerProductoPorId($conexion, $idProducto)
{
    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE p.id_producto = :id_producto AND p.estado_producto = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_producto' => (int)$idProducto]);
    return $stmt->fetch();
}

function obtenerImagenesProducto($conexion, $idProducto)
{
    $sql = "SELECT * FROM imagenes_producto
            WHERE id_producto = :id_producto
            ORDER BY orden_imagen ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_producto' => (int)$idProducto]);
    return $stmt->fetchAll();
}

function obtenerTallasProducto($conexion, $idProducto)
{
    $sql = "SELECT * FROM tallas_producto
            WHERE id_producto = :id_producto AND stock_talla > 0";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_producto' => (int)$idProducto]);
    return $stmt->fetchAll();
}

function obtenerColoresProducto($conexion, $idProducto)
{
    $sql = "SELECT * FROM colores_producto
            WHERE id_producto = :id_producto AND stock_color > 0";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_producto' => (int)$idProducto]);
    return $stmt->fetchAll();
}


function obtenerStockProducto(PDO $conexion, int $idProducto): int
{
    try {
        $stmt = $conexion->prepare("SELECT stock_producto FROM productos WHERE id_producto = :id LIMIT 1");
        $stmt->execute(['id' => $idProducto]);
        $stock = $stmt->fetchColumn();
        if ($stock !== false) {
            return (int)$stock;
        }
    } catch (Throwable $e) {
        // Compatibilidad con bases anteriores sin la columna stock_producto.
    }

    try {
        $stmt = $conexion->prepare("SELECT COALESCE(SUM(stock_talla), 0) FROM tallas_producto WHERE id_producto = :id");
        $stmt->execute(['id' => $idProducto]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function obtenerCategoriaPorId($conexion, $idCategoria)
{
    $sql = "SELECT * FROM categorias
            WHERE id_categoria = :id_categoria AND estado_categoria = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_categoria' => (int)$idCategoria]);
    return $stmt->fetch();
}

function obtenerMarcaPorId($conexion, $idMarca)
{
    $sql = "SELECT * FROM marcas
            WHERE id_marca = :id_marca AND estado_marca = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_marca' => (int)$idMarca]);
    return $stmt->fetch();
}

function obtenerProductosPorMarca($conexion, $idMarca)
{
    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE p.id_marca = :id_marca AND p.estado_producto = 1
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_marca' => (int)$idMarca]);
    return $stmt->fetchAll();
}

function obtenerProductosPorCategoriaId($conexion, $idCategoria)
{
    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE p.id_categoria = :id_categoria AND p.estado_producto = 1
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id_categoria' => (int)$idCategoria]);
    return $stmt->fetchAll();
}

function obtenerCategoriaPorNombre($conexion, $nombreCategoria)
{
    $sql = "SELECT * FROM categorias
            WHERE LOWER(nombre_categoria) = LOWER(:nombre_categoria) AND estado_categoria = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['nombre_categoria' => trim($nombreCategoria)]);
    return $stmt->fetch();
}

function obtenerProductosPorCategoriaNombre($conexion, $nombreCategoria)
{
    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE LOWER(c.nombre_categoria) = LOWER(:nombre_categoria)
            AND p.estado_producto = 1
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['nombre_categoria' => trim($nombreCategoria)]);
    return $stmt->fetchAll();
}

function obtenerMarcaPorNombre($conexion, $nombreMarca)
{
    $sql = "SELECT * FROM marcas
            WHERE LOWER(nombre_marca) = LOWER(:nombre_marca) AND estado_marca = 1
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['nombre_marca' => trim($nombreMarca)]);
    return $stmt->fetch();
}

function obtenerProductosPorMarcaNombre($conexion, $nombreMarca)
{
    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE LOWER(m.nombre_marca) = LOWER(:nombre_marca)
            AND p.estado_producto = 1
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['nombre_marca' => trim($nombreMarca)]);
    return $stmt->fetchAll();
}



function obtenerProductosPorCategoriaMarca($conexion, $idCategoria = 0, $idMarca = 0)
{
    $condiciones = ['p.estado_producto = 1'];
    $params = [];

    if ((int)$idCategoria > 0) {
        $condiciones[] = 'p.id_categoria = :id_categoria';
        $params['id_categoria'] = (int)$idCategoria;
    }

    if ((int)$idMarca > 0) {
        $condiciones[] = 'p.id_marca = :id_marca';
        $params['id_marca'] = (int)$idMarca;
    }

    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE " . implode(' AND ', $condiciones) . "
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function buscarProductos($conexion, $busqueda)
{
    $busqueda = trim((string)$busqueda);

    if ($busqueda === '') {
        return obtenerProductos($conexion);
    }

    $sql = "SELECT p.*, c.nombre_categoria, m.nombre_marca
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE p.estado_producto = 1
            AND (
                p.nombre_producto LIKE :busqueda
                OR p.descripcion_producto LIKE :busqueda
                OR c.nombre_categoria LIKE :busqueda
                OR m.nombre_marca LIKE :busqueda
            )
            ORDER BY p.fecha_creacion DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['busqueda' => '%' . $busqueda . '%']);
    return $stmt->fetchAll();
}

function obtenerBaseUrlProyecto()
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $directorio = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    // Si estamos dentro de subcarpetas del proyecto, subimos a la raíz.
    $carpetaActual = basename($directorio);
    $subcarpetas = ['Categorias', 'Marcas', 'Contactos', 'Pagos', 'PanelAdministrador'];

    if (in_array($carpetaActual, $subcarpetas, true)) {
        $directorio = dirname($directorio);
    }

    return ($directorio === '/' || $directorio === '.') ? '' : $directorio;
}

function obtenerPlaceholderImagen()
{
    $baseUrl = obtenerBaseUrlProyecto();
    return (rtrim($baseUrl, '/') ?: '') . '/assets/img/no-image.svg';
}

function obtenerUrlImagen($rutaImagen)
{
    $rutaImagen = trim((string)$rutaImagen);

    if ($rutaImagen === '') {
        return obtenerPlaceholderImagen();
    }

    // IMPORTANTE: primero se validan URLs externas.
    // No se debe aplicar preg_replace('/+/') antes, porque rompe https:// en https:/.
    if (preg_match('/^https?:\/\//i', $rutaImagen)) {
        return $rutaImagen;
    }

    // Permite imágenes embebidas o URLs relativas al protocolo.
    if (preg_match('/^(data:image\/|\/\/)/i', $rutaImagen)) {
        return $rutaImagen;
    }

    // Normaliza rutas locales guardadas con barras de Windows.
    $rutaImagen = str_replace('\\', '/', $rutaImagen);

    // Si por error se guardó una ruta absoluta del disco local, nos quedamos desde assets/.
    $posAssets = stripos($rutaImagen, 'assets/');
    if ($posAssets !== false) {
        $rutaImagen = substr($rutaImagen, $posAssets);
    }

    // Evita duplicar la carpeta del proyecto en la URL.
    $rutaImagen = preg_replace('#^/?boutique_web_ii/#i', '', $rutaImagen);
    $rutaImagen = preg_replace('#^/?boutique_web/#i', '', $rutaImagen);

    $baseUrl = obtenerBaseUrlProyecto();
    $url = rtrim($baseUrl, '/') . '/' . ltrim($rutaImagen, '/');

    return $url;
}
function atributoOnErrorImagen()
{
    return "this.onerror=null;this.src=\"" . htmlspecialchars(obtenerPlaceholderImagen(), ENT_QUOTES) . "\";";
}
