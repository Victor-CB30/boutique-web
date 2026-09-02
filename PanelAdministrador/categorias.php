<?php
require_once __DIR__.'/../config/conexion.php';require_once __DIR__.'/../config/funciones.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/esquema.php';
protegerPanelAdmin();asegurarEsquemaBoutique($conexion);
$empresa=obtenerEmpresa($conexion);$admin=adminActual();
function irCategorias($m,$t='ok'){$_SESSION['admin_mensaje']=$m;$_SESSION['admin_tipo_mensaje']=$t;header('Location: categorias.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){try{$a=$_POST['accion']??'';$id=(int)($_POST['id_categoria']??0);
 if($a==='guardar'){$n=trim($_POST['nombre_categoria']??'');
    $d=trim($_POST['descripcion_categoria']??'');if($n==='')
    throw new RuntimeException('Escribe el nombre de la categoría.');
    $dup=$conexion->prepare('SELECT id_categoria FROM categorias WHERE LOWER(nombre_categoria)=LOWER(:n) AND id_categoria<>:id LIMIT 1');
    $dup->execute(['n'=>$n,'id'=>$id]);if($dup->fetch())
    throw new RuntimeException('Ya existe una categoría con ese nombre.');
    if($id){$q=$conexion->prepare('UPDATE categorias SET nombre_categoria=:n,descripcion_categoria=:d,estado_categoria=1 WHERE id_categoria=:id');
    $q->execute(['n'=>$n,'d'=>$d?:null,'id'=>$id]);}else{$q=$conexion->prepare('INSERT INTO categorias(nombre_categoria,descripcion_categoria,estado_categoria) VALUES(:n,:d,1)');$q->execute(['n'=>$n,'d'=>$d?:null]);}irCategorias($id?'Categoría actualizada.':'Categoría creada.');}
 if($a==='eliminar'&&$id){$q=$conexion->prepare('SELECT COUNT(*) FROM productos WHERE id_categoria=:id');$q->execute(['id'=>$id]);
 if((int)$q->fetchColumn()>0)
    throw new RuntimeException('No se puede eliminar porque tiene productos asociados. Reasigna esos productos primero.');
 $conexion->prepare('DELETE FROM categorias WHERE id_categoria=:id')->execute(['id'=>$id]);irCategorias('Categoría eliminada.');}
}catch(Throwable $e){irCategorias('No se pudo procesar: '.$e->getMessage(),'error');}}
$editar=null;if(isset($_GET['editar'])){$q=$conexion->prepare('SELECT * FROM categorias WHERE id_categoria=:id');$q->execute(['id'=>(int)$_GET['editar']]);
$editar=$q->fetch();}$categorias=$conexion->query('SELECT * FROM categorias ORDER BY nombre_categoria')->fetchAll();$mensaje=$_SESSION['admin_mensaje']??'';
$tipo=$_SESSION['admin_tipo_mensaje']??'ok';unset($_SESSION['admin_mensaje'],$_SESSION['admin_tipo_mensaje']);
?>
<!doctype html><html lang="es">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/webp" href="../assets/img/favicon-boutique.jpg">
    <title>Categorías | Boutique</title><link rel="stylesheet" href="../assets/css/estilos.css?v=16.0">
</head>
<body class="admin-body admin-catalog-page">
    <div class="admin-layout clean-admin">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-brand">
                <span class="admin-logo-mini">BG</span>
                <div>
                    <strong><?=htmlspecialchars($empresa['nombre_empresa']??'Boutique')?></strong>
                    <small>Administración</small>
                </div>
            </div>
                <nav class="admin-nav">
                    <a href="administrador.php">Dashboard</a>
                    <a href="productos.php">Productos</a>
                    <a href="clientes.php">Clientes</a>
                    <a href="pedidos.php">Pedidos</a>
                    <a class="activo" href="categorias.php">Categorías</a>
                    <a href="marcas.php">Marcas</a>
                    <a href="../index.php" target="_blank">Ver tienda</a>
                </nav>
                <div class="admin-sidebar-footer">
                    <span><?=htmlspecialchars($admin['nombre']??'Admin')?></span>
                    <a href="logout.php">Salir</a>
                </div>
            </aside>
                <main class="admin-main">
                    <header class="admin-topbar"><div>
                        <span class="admin-kicker">Catálogo</span>
                        <h1>Categorías</h1>
                        <p>Crea y organiza las categorías disponibles en la tienda.</p>
                    </div>
                </header>
                <?php if($mensaje):?>
                    <div class="admin-alert <?=$tipo==='error'?'error':'ok'?>">
                        <?=htmlspecialchars($mensaje)?>
                    </div>
                    <?php endif;?>
                    <section class="admin-crud-grid compact-form-grid">
                        <article class="admin-panel-card admin-form-card">
                            <h2><?=$editar?'Editar categoría':'Nueva categoría'?></h2>
                            <form method="post" class="admin-form">
                                <input type="hidden" name="accion" value="guardar">
                                <input type="hidden" name="id_categoria" value="<?=intval($editar['id_categoria']??0)?>">
                                <label>Nombre<input name="nombre_categoria" required value="<?=htmlspecialchars($editar['nombre_categoria']??'')?>"></label>
                                <label>Descripción<textarea name="descripcion_categoria" rows="4"><?=htmlspecialchars($editar['descripcion_categoria']??'')?></textarea></label>
                                <div class="admin-form-actions">
                                    <button class="admin-btn primary">Guardar</button>
                                    <?php if($editar):?>
                                        <a class="admin-btn ghost" href="categorias.php">Cancelar</a>
                                        <?php endif;?>
                                            </div>
                                        </form>
                                    </article>
                                    <article class="admin-panel-card admin-table-card">
                                            <div class="admin-panel-header"><div>
                                                <h2>Listado</h2><p><?=count($categorias)?> categoría(s)</p>
                                            </div>
                                        </div>
                                        <div class="admin-table-wrap"><table class="admin-table">
                                            <thead>
                                                <tr>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody><?php foreach($categorias as $c):?>
                                                <tr>
                                                    <td>
                                                        <strong><?=htmlspecialchars($c['nombre_categoria'])?></strong>
                                                    </td>
                                                    <td><?=htmlspecialchars($c['descripcion_categoria']??'—')?></td>
                                                    <td>
                                                        <span class="admin-badge <?=($c['estado_categoria']??1)?'ok':'muted'?>"><?=($c['estado_categoria']??1)?'Activa':'Inactiva'?>
                                                        </span>
                                                    </td>
                                                    <td class="admin-actions-cell">
                                                        <details class="admin-actions-menu table-actions-menu">
                                                            <summary aria-label="Acciones" title="Acciones">•••</summary>
                                                            <div class="admin-actions-dropdown"><a href="?editar=<?=$c['id_categoria']?>">
                                                                <span aria-hidden="true">✎</span> Editar</a>
                                                                <form method="post" onsubmit="return confirm('¿Eliminar esta categoría?')">
                                                                    <input type="hidden" name="accion" value="eliminar">
                                                                <input type="hidden" name="id_categoria" value="<?=$c['id_categoria']?>"><button type="submit" class="danger-action">
                                                                    <span aria-hidden="true">🗑</span> Eliminar</button>
                                                                </form>
                                                            </div>
                                                        </details>
                                                    </td>
                                                </tr><?php endforeach;?>
                                            </tbody>
                                        </table>
                                    </div>
                                </article>
                            </section>
                        </main>
                    </div>
                </body>
            </html>
<!--  -->