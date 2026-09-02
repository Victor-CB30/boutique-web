/* Scripts principales de Boutique Aurora */

// ── Header scroll effect ──
window.addEventListener('scroll', () => {
    const header = document.querySelector('.encabezado');
    if (!header) return;
    header.classList.toggle('scrolled', window.scrollY > 50);
}, { passive: true });

// ── Menú móvil ──
const botonMenu     = document.getElementById('botonMenu');
const menuPrincipal = document.getElementById('menuPrincipal');

if (botonMenu && menuPrincipal) {
    const iconoAbrir  = botonMenu.querySelector('.icono-menu-abrir');
    const iconoCerrar = botonMenu.querySelector('.icono-menu-cerrar');
    const alternarIconoMenu = (abierto) => {
        if (iconoAbrir)  iconoAbrir.style.display  = abierto ? 'none' : '';
        if (iconoCerrar) iconoCerrar.style.display = abierto ? '' : 'none';
    };

    const cerrarMenuMovil = () => {
        menuPrincipal.classList.remove('activo');
        botonMenu.setAttribute('aria-expanded', false);
        alternarIconoMenu(false);
        document.body.style.overflow = '';
    };

    botonMenu.addEventListener('click', () => {
        const abierto = menuPrincipal.classList.toggle('activo');
        botonMenu.setAttribute('aria-expanded', abierto);
        alternarIconoMenu(abierto);
        document.body.style.overflow = abierto && window.innerWidth <= 768 ? 'hidden' : '';
    });
    // Cerrar al hacer clic fuera
    document.addEventListener('click', e => {
        if (!botonMenu.contains(e.target) && !menuPrincipal.contains(e.target)) {
            cerrarMenuMovil();
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && menuPrincipal.classList.contains('activo')) cerrarMenuMovil();
    });

    // Acordeón de submenús (Categorías / Marcas) dentro del panel móvil
    menuPrincipal.querySelectorAll('.menu-item-submenu > a').forEach(enlace => {
        enlace.addEventListener('click', e => {
            if (window.innerWidth > 768) return;
            e.preventDefault();
            const item = enlace.closest('.menu-item-submenu');
            const yaAbierto = item.classList.contains('submenu-abierto');
            menuPrincipal.querySelectorAll('.menu-item-submenu').forEach(li => li.classList.remove('submenu-abierto'));
            if (!yaAbierto) item.classList.add('submenu-abierto');
        });
    });
}

// ── Cerrar menús "•••" (details) del admin al hacer clic afuera ──
document.addEventListener('click', e => {
    document.querySelectorAll('details[open].admin-actions-menu').forEach(det => {
        if (!det.contains(e.target)) det.removeAttribute('open');
    });
});

// ── Toast notifications ──
const ICONOS_TOAST = {
    exito: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="color:#fff"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>',
    error: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="color:#fff"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>',
    aviso: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="color:#fff"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.964 0L.165 13.233c-.457.778.091 1.767.982 1.767h13.706c.89 0 1.438-.99.982-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>',
    info:  '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" style="color:#fff"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/></svg>'
};

function obtenerContenedorToast() {
    let contenedor = document.getElementById('toastContenedor');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'toastContenedor';
        contenedor.className = 'toast-contenedor';
        contenedor.setAttribute('aria-live', 'polite');
        document.body.appendChild(contenedor);
    }
    return contenedor;
}

function mostrarToast(mensaje, tipo = 'exito') {
    if (!ICONOS_TOAST[tipo]) tipo = 'exito';
    const contenedor = obtenerContenedorToast();
    const duracion = 3400;

    const toast = document.createElement('div');
    toast.className = `toast-notif toast-notif--${tipo}`;
    toast.setAttribute('role', 'status');
    toast.innerHTML = `
        <span class="toast-icono">${ICONOS_TOAST[tipo]}</span>
        <span class="toast-texto">${mensaje}</span>
        <button type="button" class="toast-cerrar" aria-label="Cerrar notificación">&times;</button>
        <span class="toast-barra" style="animation-duration:${duracion}ms"></span>`;
    contenedor.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('visible'));

    const cerrar = () => {
        toast.classList.add('saliendo');
        toast.classList.remove('visible');
        setTimeout(() => toast.remove(), 320);
    };
    const temporizador = setTimeout(cerrar, duracion);
    toast.querySelector('.toast-cerrar').addEventListener('click', () => {
        clearTimeout(temporizador);
        cerrar();
    });
}

// ── Animación de aparición al hacer scroll ──
function inicializarRevelado() {
    const selectores = '.tarjeta-producto, .titulo-seccion, .pago-card, .contacto-card, .detalle-producto, .footer-grid > div';
    const elementos = document.querySelectorAll(selectores);
    if (!elementos.length) return;

    if (!('IntersectionObserver' in window)) {
        elementos.forEach(el => el.classList.add('al-vista', 'visible'));
        return;
    }

    const observador = new IntersectionObserver((entradas) => {
        entradas.forEach((entrada, i) => {
            if (entrada.isIntersecting) {
                setTimeout(() => entrada.target.classList.add('visible'), i * 45);
                observador.unobserve(entrada.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    elementos.forEach(el => {
        el.classList.add('al-vista');
        observador.observe(el);
    });
}
document.addEventListener('DOMContentLoaded', inicializarRevelado);

// ── Ruta al detalle del producto según sub-directorio ──
function obtenerRutaDetalleProducto() {
    const ruta = window.location.pathname;
    if (ruta.includes('/Categorias/') || ruta.includes('/Marcas/')) {
        return '../producto_detalle.php';
    }
    return 'producto_detalle.php';
}

// ── Índice del carrusel ──
let indiceCarrusel = 0;
let elementoConFocoPrevio = null;

// ── Abrir modal de detalle ──
function abrirDetalleProducto(idProducto) {
    const modal    = document.getElementById('modalProducto');
    const contenido = document.getElementById('contenidoModalProducto');
    if (!modal || !contenido) return;

    elementoConFocoPrevio = document.activeElement;
    contenido.innerHTML = `
        <div class="cargando-producto">
            <div class="spinner"></div>
            Cargando producto…
        </div>`;
    modal.classList.add('activo');
    document.body.style.overflow = 'hidden';
    modal.querySelector('.cerrar-modal')?.focus();

    fetch(`${obtenerRutaDetalleProducto()}?id=${encodeURIComponent(idProducto)}`)
        .then(r => {
            if (!r.ok) throw new Error('Error al cargar el producto');
            return r.text();
        })
        .then(html => {
            contenido.innerHTML = html;
            indiceCarrusel = 0;
            actualizarCarrusel();
            crearPuntosCarrusel();
        })
        .catch(() => {
            contenido.innerHTML = '<p style="text-align:center;padding:40px;color:#888;">No se pudo cargar el producto. Verifica la conexión con la base de datos.</p>';
        });
}

function cerrarDetalleProducto() {
    const modal    = document.getElementById('modalProducto');
    const contenido = document.getElementById('contenidoModalProducto');
    if (modal) modal.classList.remove('activo');
    if (contenido) contenido.innerHTML = '';
    document.body.style.overflow = '';
    if (elementoConFocoPrevio && typeof elementoConFocoPrevio.focus === 'function') {
        elementoConFocoPrevio.focus();
    }
}

// Trampa de foco (Tab) dentro del modal mientras está abierto
document.addEventListener('keydown', e => {
    if (e.key !== 'Tab') return;
    const modal = document.getElementById('modalProducto');
    if (!modal || !modal.classList.contains('activo')) return;
    const focosVisibles = modal.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (!focosVisibles.length) return;
    const primero = focosVisibles[0];
    const ultimo = focosVisibles[focosVisibles.length - 1];
    if (e.shiftKey && document.activeElement === primero) {
        e.preventDefault(); ultimo.focus();
    } else if (!e.shiftKey && document.activeElement === ultimo) {
        e.preventDefault(); primero.focus();
    }
});

// ── Carrusel ──
function moverCarrusel(direccion) {
    const carrusel = document.getElementById('imagenesCarrusel');
    if (!carrusel) return;
    const total = carrusel.querySelectorAll('img').length;
    if (total === 0) return;
    indiceCarrusel = (indiceCarrusel + direccion + total) % total;
    actualizarCarrusel();
}

function actualizarCarrusel() {
    const carrusel = document.getElementById('imagenesCarrusel');
    if (!carrusel) return;
    carrusel.style.transform = `translateX(-${indiceCarrusel * 100}%)`;
    document.querySelectorAll('.punto-carrusel').forEach((p, i) => {
        p.classList.toggle('activo', i === indiceCarrusel);
    });
}

function crearPuntosCarrusel() {
    const carrusel   = document.getElementById('imagenesCarrusel');
    const contenedor = document.querySelector('.carrusel-producto');
    if (!carrusel || !contenedor) return;
    const imgs = carrusel.querySelectorAll('img');
    if (imgs.length <= 1) return;

    const puntos = document.createElement('div');
    puntos.className = 'puntos-carrusel';
    imgs.forEach((_, i) => {
        const p = document.createElement('button');
        p.className = 'punto-carrusel' + (i === 0 ? ' activo' : '');
        p.setAttribute('aria-label', `Imagen ${i + 1}`);
        p.addEventListener('click', () => {
            indiceCarrusel = i;
            actualizarCarrusel();
        });
        puntos.appendChild(p);
    });
    contenedor.appendChild(puntos);
}

// Swipe táctil en carrusel
let touchStartX = 0;
document.addEventListener('touchstart', e => {
    const c = e.target.closest('.carrusel-producto');
    if (c) touchStartX = e.touches[0].clientX;
}, { passive: true });
document.addEventListener('touchend', e => {
    const c = e.target.closest('.carrusel-producto');
    if (!c) return;
    const diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) moverCarrusel(diff > 0 ? 1 : -1);
});

// ── Cantidad ──
function cambiarCantidad(valor) {
    const input = document.getElementById('cantidadProducto');
    if (!input) return;
    let cantidad = Math.max(1, (parseInt(input.value, 10) || 1) + valor);
    input.value = cantidad;
}

// ── Carrito (localStorage) ──
const CLAVE_CARRITO = 'carritoBoutique';

function leerCarrito() {
    try {
        const data = JSON.parse(localStorage.getItem(CLAVE_CARRITO) || '[]');
        return Array.isArray(data) ? data : [];
    } catch (e) {
        return [];
    }
}

function guardarCarrito(carrito) {
    localStorage.setItem(CLAVE_CARRITO, JSON.stringify(carrito));
    localStorage.removeItem('compraDirecta');
    actualizarCarritoMenu();
}

function normalizarNumero(n) {
    return Number(String(n).replace(',', '.')) || 0;
}

function formatGs(n) {
    return 'Gs. ' + Math.round(normalizarNumero(n)).toLocaleString('es-PY');
}

function escHtml(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function crearClaveItem(item) {
    return [item.idProducto, item.talla || '', item.color || ''].join('|');
}

function agregarAlCarrito(idProducto, nombreProducto, precioProducto) {
    const talla    = document.getElementById('tallaProducto')?.value || '';
    const color    = document.getElementById('colorProducto')?.value || '';
    const cantidad = parseInt(document.getElementById('cantidadProducto')?.value, 10) || 1;

    const carrito = leerCarrito();
    const nuevoItem = {
        idProducto: String(idProducto),
        nombreProducto: String(nombreProducto),
        precioProducto: normalizarNumero(precioProducto),
        talla,
        color,
        cantidad
    };

    const existente = carrito.find(i => crearClaveItem(i) === crearClaveItem(nuevoItem));

    if (existente) {
        existente.cantidad = (parseInt(existente.cantidad, 10) || 1) + cantidad;
    } else {
        carrito.push(nuevoItem);
    }

    guardarCarrito(carrito);
    mostrarToast(`"${nombreProducto}" añadido al carrito ✓`);
}

function cambiarCantidadCarrito(indice, cambio) {
    const carrito = leerCarrito();
    if (!carrito[indice]) return;

    carrito[indice].cantidad = Math.max(1, (parseInt(carrito[indice].cantidad, 10) || 1) + cambio);
    guardarCarrito(carrito);
}

function eliminarItemCarrito(indice) {
    const carrito = leerCarrito();
    if (!carrito[indice]) return;

    const eliminado = carrito[indice].nombreProducto || 'Producto';
    carrito.splice(indice, 1);
    guardarCarrito(carrito);
    mostrarToast(`"${eliminado}" eliminado del carrito`, 'info');
}

function vaciarCarrito() {
    localStorage.removeItem(CLAVE_CARRITO);
    localStorage.removeItem('compraDirecta');
    actualizarCarritoMenu();
}

function actualizarCarritoMenu() {
    const contador = document.getElementById('contadorCarritoMenu');
    const totalEl  = document.getElementById('totalCarritoMenu');
    const itemsEl  = document.getElementById('itemsCarritoMenu');
    const carrito  = leerCarrito();

    const totalCantidad = carrito.reduce((acc, item) => acc + (parseInt(item.cantidad, 10) || 1), 0);
    const totalCompra   = carrito.reduce((acc, item) => acc + (normalizarNumero(item.precioProducto) * (parseInt(item.cantidad, 10) || 1)), 0);

    if (contador) contador.textContent = totalCantidad;
    if (totalEl) totalEl.textContent = formatGs(totalCompra);

    if (!itemsEl) return;

    if (!carrito.length) {
        itemsEl.innerHTML = '<p class="carrito-vacio-menu">Todavía no agregaste productos.</p>';
        return;
    }

    itemsEl.innerHTML = carrito.map((item, i) => {
        const cantidad = parseInt(item.cantidad, 10) || 1;
        const subtotal = normalizarNumero(item.precioProducto) * cantidad;
        return `
            <div class="item-carrito-menu">
                <div class="item-carrito-info">
                    <strong>${escHtml(item.nombreProducto)}</strong>
                    <small>
                        ${item.talla ? 'Talla: ' + escHtml(item.talla) + ' · ' : ''}
                        ${item.color ? 'Color: ' + escHtml(item.color) + ' · ' : ''}
                        ${formatGs(subtotal)}
                    </small>
                </div>
                <div class="item-carrito-controles">
                    <button type="button" onclick="cambiarCantidadCarrito(${i}, -1)" aria-label="Restar cantidad">−</button>
                    <span>${cantidad}</span>
                    <button type="button" onclick="cambiarCantidadCarrito(${i}, 1)" aria-label="Agregar cantidad">+</button>
                    <button type="button" class="btn-eliminar-carrito" onclick="eliminarItemCarrito(${i})" aria-label="Eliminar producto">×</button>
                </div>
            </div>`;
    }).join('');
}

function inicializarCarritoMenu() {
    const boton = document.getElementById('botonCarritoMenu');
    const dropdown = document.getElementById('dropdownCarrito');
    if (!boton || !dropdown) return;

    boton.addEventListener('click', (e) => {
        e.stopPropagation();
        const abierto = dropdown.classList.toggle('activo');
        boton.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        actualizarCarritoMenu();
    });

    dropdown.addEventListener('click', e => e.stopPropagation());

    document.addEventListener('click', () => {
        dropdown.classList.remove('activo');
        boton.setAttribute('aria-expanded', 'false');
    });

    actualizarCarritoMenu();
}

document.addEventListener('DOMContentLoaded', inicializarCarritoMenu);

// ── Comprar (redirect a pagos con datos) ──
function comprarProducto(idProducto, nombreProducto, precioProducto) {
    agregarAlCarrito(idProducto, nombreProducto, precioProducto);
    const ruta = window.location.pathname;
    const base = (ruta.includes('/Categorias/') || ruta.includes('/Marcas/')) ? '../' : '';
    window.location.href = `${base}Pagos/pagos.php`;
}

// ── Método de pago (toggle en pagos.php) ──
function mostrarMetodoPago(idMetodo) {
    document.querySelectorAll('.detalle-pago').forEach(s => s.classList.remove('activo'));
    document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('activo'));
    const metodo = document.getElementById(idMetodo);
    if (metodo) metodo.classList.add('activo');
    const boton = document.querySelector(`[data-metodo="${idMetodo}"]`);
    if (boton) boton.classList.add('activo');
}

// ── Cerrar modal con clic afuera o ESC ──
document.addEventListener('click', e => {
    const modal = document.getElementById('modalProducto');
    if (modal && e.target === modal) cerrarDetalleProducto();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarDetalleProducto();
});

// ── Sidebar del panel administrador (drawer en mobile) ──
function inicializarSidebarAdmin() {
    const toggle = document.getElementById('adminMenuToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.getElementById('adminOverlay');
    const cerrar = document.getElementById('adminSidebarClose');
    if (!toggle || !sidebar || !overlay) return;

    const abrir = () => { sidebar.classList.add('abierto'); overlay.classList.add('activo'); toggle.setAttribute('aria-expanded', 'true'); };
    const ocultar = () => { sidebar.classList.remove('abierto'); overlay.classList.remove('activo'); toggle.setAttribute('aria-expanded', 'false'); };

    toggle.addEventListener('click', abrir);
    overlay.addEventListener('click', ocultar);
    cerrar?.addEventListener('click', ocultar);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') ocultar(); });
}
document.addEventListener('DOMContentLoaded', inicializarSidebarAdmin);
