<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/auth.php';
$token=$_GET['token']??($_POST['token']??''); $mensaje=''; $ok=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
 $p=$_POST['password']??''; $c=$_POST['confirmar']??'';
 if(strlen($p)<8)$mensaje='La contraseña debe tener al menos 8 caracteres.';
 elseif($p!==$c)$mensaje='Las contraseñas no coinciden.';
 else {$ok=restablecerPasswordAdmin($conexion,$token,$p);$mensaje=$ok?'Contraseña actualizada correctamente.':'El enlace es inválido, expiró o ya fue utilizado.';}
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="icon" type="image/webp" href="../assets/img/favicon-boutique.jpg"><title>Nueva contraseña</title><link rel="stylesheet" href="../assets/css/estilos.css?v=16.0"></head><body class="admin-login-body"><main class="admin-login-shell"><section class="admin-login-card"><div class="admin-login-form-wrap"><span class="admin-kicker">Seguridad</span><h1>Nueva contraseña</h1><?php if($mensaje):?><div class="admin-alert <?=$ok?'ok':'error'?>"><?=htmlspecialchars($mensaje)?></div><?php endif;?><?php if(!$ok):?><form method="post" class="admin-login-form"><input type="hidden" name="token" value="<?=htmlspecialchars($token)?>"><label>Contraseña<input type="password" name="password" required minlength="8"></label><label>Confirmar<input type="password" name="confirmar" required minlength="8"></label><button class="admin-btn primary">Guardar contraseña</button></form><?php else:?><a class="admin-btn primary" href="index.php">Ingresar al panel</a><?php endif;?></div></section></main></body></html>
