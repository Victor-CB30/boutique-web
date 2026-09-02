<?php
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/../config/funciones.php';
$rutaBase='../'; $empresa=obtenerEmpresa($conexion); $tituloPagina='Confirmar pedido | '.htmlspecialchars($empresa['nombre_empresa']??'Boutique');
$wa=preg_replace('/[^0-9]/','',$empresa['telefono_whatsapp']??'');
include __DIR__.'/../includes/header.php';
?>
<main class="pagina-pago"><div class="contenedor checkout-clean">
<section class="checkout-heading"><span class="eyebrow">Último paso</span><h1>Confirmar pedido</h1><p>Completa tus datos y envía el pedido directamente por WhatsApp.</p></section>
<div class="pago-grid">
<form class="pago-card checkout-form" id="formPedido">
<h2>Datos del cliente</h2>
<div class="admin-form-row"><label>Nombre completo<input id="nombreComprador" required autocomplete="name"></label><label>Teléfono / WhatsApp<input id="telefonoComprador" required autocomplete="tel"></label></div>
<div class="admin-form-row"><label>Correo (opcional)<input type="email" id="emailComprador" autocomplete="email"></label><label>Forma de retiro<select id="tipoRetiro" required><option value="local">Retiro en el local</option><option value="delivery">Delivery</option></select></label></div>
<div class="campo" id="campoDireccion" hidden><label>Dirección de entrega<input id="direccionComprador" autocomplete="street-address" placeholder="Barrio, calle, número y referencia"></label></div>
<label>Método de pago<select id="metodoPago"><option value="whatsapp">Coordinar por WhatsApp</option><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option></select></label>
<label>Notas (opcional)<textarea id="notasCompra" rows="3" placeholder="Referencia, horario, indicaciones..."></textarea></label>
<button class="boton-principal ancho-completo" id="btnConfirmar" type="submit">Enviar pedido por WhatsApp</button>
<p class="checkout-security">El pedido se guarda primero en la base de datos y luego se abre WhatsApp.</p>
</form>
<aside class="pago-card resumen-card"><div class="admin-panel-header"><div><h2>Tu pedido</h2><p id="cantidadResumen">0 productos</p></div><button type="button" class="admin-btn ghost small" onclick="window.history.back()">Editar</button></div><div id="resumenPedido"></div><div class="resumen-total"><span>Total</span><strong id="totalResumen">Gs. 0</strong></div></aside>
</div></div></main>
<script>
document.addEventListener('DOMContentLoaded',()=>{
const WHATSAPP_TIENDA=<?=json_encode($wa)?>;
const retiro=document.getElementById('tipoRetiro'), campo=document.getElementById('campoDireccion'), direccion=document.getElementById('direccionComprador');
retiro.addEventListener('change',()=>{const d=retiro.value==='delivery';campo.hidden=!d;direccion.required=d;});
function renderResumen(){const items=leerCarrito();const box=document.getElementById('resumenPedido');let total=0;box.innerHTML=items.map(i=>{const sub=normalizarNumero(i.precioProducto)*(parseInt(i.cantidad)||1);total+=sub;return `<div class="resumen-item"><div><strong>${escHtml(i.nombreProducto)}</strong><small>${i.talla?'Talla '+escHtml(i.talla)+' · ':''}${i.color?'Color '+escHtml(i.color)+' · ':''}Cantidad ${parseInt(i.cantidad)||1}</small></div><span>${formatGs(sub)}</span></div>`}).join('')||'<p class="admin-empty">Tu carrito está vacío.</p>';document.getElementById('totalResumen').textContent=formatGs(total);document.getElementById('cantidadResumen').textContent=`${items.length} producto(s)`;}
renderResumen();
document.getElementById('formPedido').addEventListener('submit',async e=>{e.preventDefault();const items=leerCarrito();if(!WHATSAPP_TIENDA){alert('Configura el teléfono de WhatsApp de la boutique en la base de datos.');return;}if(!items.length){alert('Agrega al menos un producto.');return;}const btn=document.getElementById('btnConfirmar');btn.disabled=true;btn.textContent='Guardando pedido...';const cliente={nombre:document.getElementById('nombreComprador').value.trim(),telefono:document.getElementById('telefonoComprador').value.trim(),email:document.getElementById('emailComprador').value.trim(),tipoRetiro:retiro.value,direccion:direccion.value.trim(),metodoPago:document.getElementById('metodoPago').value,notas:document.getElementById('notasCompra').value.trim()};try{const r=await fetch('../api/guardar_pedido.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({cliente,items})});const data=await r.json();if(!r.ok||!data.ok)throw new Error(data.mensaje||'No se pudo guardar el pedido.');let texto=`Hola, quiero confirmar el pedido ${data.codigo}.\n\nCliente: ${cliente.nombre}\nTeléfono: ${cliente.telefono}\nRetiro: ${cliente.tipoRetiro==='delivery'?'Delivery':'Retiro en el local'}${cliente.direccion?`\nDirección: ${cliente.direccion}`:''}\nPago: ${cliente.metodoPago}\n\nProductos:\n`;items.forEach(i=>{texto+=`• ${i.nombreProducto} x${i.cantidad}${i.talla?` | Talla: ${i.talla}`:''}${i.color?` | Color: ${i.color}`:''}\n`;});texto+=`\nTotal: ${formatGs(data.total)}${cliente.notas?`\nNotas: ${cliente.notas}`:''}`;vaciarCarrito();window.location.href=`https://wa.me/${WHATSAPP_TIENDA}?text=${encodeURIComponent(texto)}`;}catch(err){alert(err.message);btn.disabled=false;btn.textContent='Enviar pedido por WhatsApp';}});
});
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
