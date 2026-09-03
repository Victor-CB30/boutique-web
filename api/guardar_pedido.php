<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/esquema.php';

try {
    asegurarEsquemaBoutique($conexion);
    $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    $cliente = $data['cliente'] ?? [];
    $items = $data['items'] ?? [];
    $nombre = trim((string)($cliente['nombre'] ?? ''));
    $telefono = trim((string)($cliente['telefono'] ?? ''));
    $email = trim((string)($cliente['email'] ?? ''));
    $retiro = ($cliente['tipoRetiro'] ?? '') === 'delivery' ? 'delivery' : 'local';
    $direccion = trim((string)($cliente['direccion'] ?? ''));
    if ($nombre === '' || $telefono === '' || !$items) throw new RuntimeException('Completa nombre, teléfono y agrega productos.');
    if ($retiro === 'delivery' && $direccion === '') throw new RuntimeException('La dirección es obligatoria para delivery.');

    $conexion->beginTransaction();
    $total = 0;
    $validos = [];
    $q = $conexion->prepare("SELECT id_producto,nombre_producto,precio_producto,stock_producto FROM productos WHERE id_producto=:id AND estado_producto=1 LIMIT 1");
    foreach ($items as $item) {
        $id = (int)($item['idProducto'] ?? 0); $cantidad = max(1,(int)($item['cantidad'] ?? 1));
        $q->execute(['id'=>$id]); $p=$q->fetch();
        if (!$p) throw new RuntimeException('Uno de los productos ya no está disponible.');
        if ((int)$p['stock_producto'] > 0 && $cantidad > (int)$p['stock_producto']) throw new RuntimeException('Stock insuficiente para '.$p['nombre_producto'].'.');
        $subtotal=(float)$p['precio_producto']*$cantidad; $total += $subtotal;
        $validos[]=['p'=>$p,'cantidad'=>$cantidad,'talla'=>trim((string)($item['talla']??'')),'color'=>trim((string)($item['color']??'')),'subtotal'=>$subtotal];
    }
    // Registrar o actualizar automáticamente al cliente del pedido.
    $clienteStmt=$conexion->prepare("INSERT INTO clientes (nombre_cliente,telefono_cliente,email_cliente,direccion_cliente,estado_cliente)
        VALUES (:nombre,:telefono,:email,:direccion,1)
        ON DUPLICATE KEY UPDATE nombre_cliente=VALUES(nombre_cliente), email_cliente=VALUES(email_cliente), direccion_cliente=VALUES(direccion_cliente), estado_cliente=1");
    $clienteStmt->execute(['nombre'=>$nombre,'telefono'=>$telefono,'email'=>$email?:null,'direccion'=>$direccion?:null]);
    $idClienteStmt=$conexion->prepare('SELECT id_cliente FROM clientes WHERE telefono_cliente=:telefono LIMIT 1');
    $idClienteStmt->execute(['telefono'=>$telefono]);
    $idCliente=(int)$idClienteStmt->fetchColumn();

    $codigo='PED-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
    $stmt=$conexion->prepare("INSERT INTO pedidos (codigo_pedido,id_cliente,nombre_cliente,email_cliente,telefono_cliente,tipo_retiro,direccion_entrega,metodo_pago,notas,total_pedido) VALUES (:codigo,:id_cliente,:nombre,:email,:telefono,:retiro,:direccion,:pago,:notas,:total)");
    $stmt->execute(['codigo'=>$codigo,'id_cliente'=>$idCliente?:null,'nombre'=>$nombre,'email'=>$email?:null,'telefono'=>$telefono,'retiro'=>$retiro,'direccion'=>$retiro==='delivery'?$direccion:null,'pago'=>trim((string)($cliente['metodoPago']??'whatsapp')),'notas'=>trim((string)($cliente['notas']??''))?:null,'total'=>$total]);
    $idPedido=(int)$conexion->lastInsertId();
    $det=$conexion->prepare("INSERT INTO detalle_pedido (id_pedido,id_producto,nombre_producto,talla,color,cantidad,precio_unitario,subtotal) VALUES (:pedido,:producto,:nombre,:talla,:color,:cantidad,:precio,:subtotal)");
    $stock=$conexion->prepare("UPDATE productos SET stock_producto=GREATEST(stock_producto-:cantidad,0) WHERE id_producto=:id AND stock_producto>0");
    foreach($validos as $v){$det->execute(['pedido'=>$idPedido,'producto'=>$v['p']['id_producto'],'nombre'=>$v['p']['nombre_producto'],'talla'=>$v['talla']?:null,'color'=>$v['color']?:null,'cantidad'=>$v['cantidad'],'precio'=>$v['p']['precio_producto'],'subtotal'=>$v['subtotal']]);$stock->execute(['cantidad'=>$v['cantidad'],'id'=>$v['p']['id_producto']]);}
    $conexion->commit();
    echo json_encode(['ok'=>true,'codigo'=>$codigo,'total'=>$total], JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){if($conexion->inTransaction())$conexion->rollBack();http_response_code(422);echo json_encode(['ok'=>false,'mensaje'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
