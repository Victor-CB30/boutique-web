<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/funciones.php';
require_once __DIR__.'/../config/auth.php';
require_once __DIR__.'/../config/esquema.php';
protegerPanelAdmin();
asegurarEsquemaBoutique($conexion);
$empresa=obtenerEmpresa($conexion); $admin=adminActual();
function irClientes($m,$t='ok'){$_SESSION['admin_mensaje']=$m;$_SESSION['admin_tipo_mensaje']=$t;header('Location: clientes.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $a=$_POST['accion']??''; $id=(int)($_POST['id_cliente']??0);
  if($a==='guardar'){
   $n=trim($_POST['nombre_cliente']??''); $tel=trim($_POST['telefono_cliente']??''); $tel2=trim($_POST['telefono_secundario']??'');
   $em=trim($_POST['email_cliente']??''); $dir=trim($_POST['direccion_cliente']??'');
   if($n===''||$tel==='') throw new RuntimeException('Nombre y teléfono principal son obligatorios.');
   $dup=$conexion->prepare('SELECT id_cliente,nombre_cliente FROM clientes WHERE telefono_cliente=:t AND id_cliente<>:id LIMIT 1');
   $dup->execute(['t'=>$tel,'id'=>$id]);
   if($existente=$dup->fetch()) throw new RuntimeException('Ese teléfono principal ya pertenece a '.$existente['nombre_cliente'].'. Puedes editar ese cliente en lugar de duplicarlo.');
   if($tel2!=='' && $tel2===$tel) throw new RuntimeException('El teléfono secundario debe ser diferente al principal.');
   if($id){
    $q=$conexion->prepare('UPDATE clientes SET nombre_cliente=:n,telefono_cliente=:t,telefono_secundario=:t2,email_cliente=:e,direccion_cliente=:d,estado_cliente=1 WHERE id_cliente=:id');
    $q->execute(['n'=>$n,'t'=>$tel,'t2'=>$tel2?:null,'e'=>$em?:null,'d'=>$dir?:null,'id'=>$id]);
   }else{
    $q=$conexion->prepare('INSERT INTO clientes(nombre_cliente,telefono_cliente,telefono_secundario,email_cliente,direccion_cliente) VALUES(:n,:t,:t2,:e,:d)');
    $q->execute(['n'=>$n,'t'=>$tel,'t2'=>$tel2?:null,'e'=>$em?:null,'d'=>$dir?:null]);
   }
   irClientes($id?'Cliente actualizado.':'Cliente registrado.');
  }
  if($a==='eliminar'&&$id){
   $conexion->prepare('DELETE FROM clientes WHERE id_cliente=:id')->execute(['id'=>$id]);
   irClientes('Cliente eliminado correctamente.');
  }
 }catch(Throwable $e){irClientes('No se pudo guardar: '.$e->getMessage(),'error');}
}
$editar=null;if(isset($_GET['editar'])){$q=$conexion->prepare('SELECT * FROM clientes WHERE id_cliente=:id');$q->execute(['id'=>(int)$_GET['editar']]);$editar=$q->fetch();}
$buscar=trim($_GET['buscar']??'');
$q=$conexion->prepare("SELECT * FROM clientes WHERE nombre_cliente LIKE :b OR telefono_cliente LIKE :b OR COALESCE(telefono_secundario,'') LIKE :b OR COALESCE(email_cliente,'') LIKE :b ORDER BY id_cliente DESC");
$q->execute(['b'=>'%'.$buscar.'%']);$clientes=$q->fetchAll();
$mensaje=$_SESSION['admin_mensaje']??'';$tipo=$_SESSION['admin_tipo_mensaje']??'ok';unset($_SESSION['admin_mensaje'],$_SESSION['admin_tipo_mensaje']);
?>
<!doctype html><html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/webp" href="../assets/img/favicon-boutique.jpg">
<title>Clientes | Boutique</title>
<link rel="stylesheet" href="../assets/css/estilos.css?v=16.0">
</head>
<body class="admin-body">
    <div class="admin-layout clean-admin">
        <aside class="admin-sidebar admin-sidebar-full">
            <div class="admin-sidebar-brand">
                <span class="admin-logo-mini">Boutique</span>
                <div>
                    <strong><?=htmlspecialchars($empresa['nombre_empresa']??'Boutique')?></strong>
                    <small>Administración</small>
                </div>
            </div>
                
            <nav class="admin-nav" aria-label="Menú administrador">
                <a href="administrador.php">Dashboard</a>
                <a href="productos.php">Productos</a>
                <a class="activo" href="clientes.php">Clientes</a>
                <a href="pedidos.php">Pedidos</a>
                <a href="categorias.php">Categorías</a>
                <a href="marcas.php">Marcas</a>
                <a href="../index.php" target="_blank">Ver tienda</a>
            </nav>
            <div class="admin-sidebar-box">
                <span>Clientes</span>
                <strong><?=count($clientes)?></strong>
                <small>registros encontrados</small>
            </div>
            <div class="admin-sidebar-shortcuts">
                <a href="#form-cliente">+ Nuevo cliente</a>
                <a href="#tabla-clientes">Ver listado</a>
                <a href="clientes.php">Limpiar búsqueda</a>
            </div>
            <div class="admin-sidebar-footer">
                <span><?=htmlspecialchars($admin['nombre']??'Admin')?></span>
                <a href="logout.php">Salir</a>
            </div>
        </aside>
            <main class="admin-main">
                <header class="admin-topbar">
                    <div><span class="admin-kicker">Personas</span>
                    <h1>Clientes</h1>
                    <p>Registra y consulta los datos de tus compradores.</p>
                </div>
            </header><?php if($mensaje):?>
                <div class="admin-alert <?=$tipo==='error'?'error':'ok'?>">
                    <?=htmlspecialchars($mensaje)?></div><?php endif;?>
            <section class="admin-crud-grid compact-form-grid">
                <article class="admin-panel-card admin-form-card" id="form-cliente">
                    <h2><?=$editar?'Editar cliente':'Nuevo cliente'?></h2>
                    <form method="post" class="admin-form">
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="id_cliente" value="<?=intval($editar['id_cliente']??0)?>">
                        <label>Nombre<input name="nombre_cliente" required value="<?=htmlspecialchars($editar['nombre_cliente']??'')?>">
                        </label>
                    <label>Teléfono principal / WhatsApp<input name="telefono_cliente" required value="<?=htmlspecialchars($editar['telefono_cliente']??'')?>">
                    </label>
                    <label>Teléfono secundario (opcional)<input name="telefono_secundario" value="<?=htmlspecialchars($editar['telefono_secundario']??'')?>">
                </label>
                <label>Correo<input type="email" name="email_cliente" value="<?=htmlspecialchars($editar['email_cliente']??'')?>">
            </label>
            <label>Dirección<textarea name="direccion_cliente" rows="3"><?=htmlspecialchars($editar['direccion_cliente']??'')?></textarea>
        </label>
        <div class="admin-form-actions">
            <button class="admin-btn primary">Guardar</button>
            <?php if($editar):?>
                <a class="admin-btn ghost" href="clientes.php">Cancelar</a>
                <?php endif;?></div></form></article>
            <article class="admin-panel-card admin-table-card" id="tabla-clientes">
                <div class="admin-panel-header">
                    <div>
                        <h2>Listado</h2>
                        <p><?=count($clientes)?> cliente(s)</p>
                    </div>
                    <form class="admin-search">
                        <input name="buscar" value="<?=htmlspecialchars($buscar)?>" placeholder="Buscar...">
                        <button>Buscar</button>
                    </form>
                </div>
                <div class="admin-table-wrap normal-table">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Teléfonos</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clientes as $c):?>
                                <tr>
                                    <td>
                                        <strong><?=htmlspecialchars($c['nombre_cliente'])?></strong>
                                    </td>
                                    <td>
                                        <?=htmlspecialchars($c['telefono_cliente'])?>
                                        <?php if(!empty($c['telefono_secundario'])):?>
                                            <small class="table-subtext"><?=htmlspecialchars($c['telefono_secundario'])?></small>
                                            <?php endif;?></td><td><?=htmlspecialchars($c['email_cliente']?:'—')?>
                                        </td>
                                        <td>
                                            <span class="admin-badge <?=$c['estado_cliente']?'ok':'muted'?>"><?=$c['estado_cliente']?'Activo':'Inactivo'?></span>
                                        </td>
                                        <td class="admin-actions-cell">
                                        <details class="admin-actions-menu table-actions-menu">
                                            <summary aria-label="Acciones" title="Acciones">•••</summary>
                                            <div class="admin-actions-dropdown">
                                                <a href="?editar=<?=$c['id_cliente']?>#form-cliente">
                                                    <span aria-hidden="true">✎</span> Editar
                                                </a>
                                                    <form method="post" onsubmit="return confirm('¿Eliminar definitivamente este cliente?')">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id_cliente" value="<?=$c['id_cliente']?>">
                                                        <button type="submit" class="danger-action"><span aria-hidden="true">🗑</span> Eliminar</button>
                                                    </form>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                                <?php endforeach;?><?php if(!$clientes):?>
                                    <tr>
                                        <td colspan="5" class="admin-empty">No hay clientes.</td>
                                    </tr><?php endif;?>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </body>
</html>
<!--  
  -->
