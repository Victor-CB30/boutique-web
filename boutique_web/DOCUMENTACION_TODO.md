# Documentación técnica y TODO del proyecto Boutique

## Estados activo, inactivo y oculto

- **Producto activo (1):** se muestra en la tienda y puede agregarse a pedidos.
- **Producto inactivo (0):** permanece en la base de datos para conservar historial, pero no debe venderse ni mostrarse al público.
- **Producto oculto (2):** sirve para retirar temporalmente una publicación sin borrar el producto. El panel puede seguir administrándolo.
- **Categoría o marca activa:** aparece como opción al registrar/editar productos y puede mostrarse en los filtros públicos.
- **Categoría o marca inactiva:** se conserva para no romper productos históricos, pero deja de ofrecerse en nuevas cargas o filtros.

Usar estados es más seguro que eliminar registros relacionados. Eliminar una categoría, marca o producto utilizado en pedidos puede romper referencias históricas o provocar errores de claves foráneas.

## Función de `detalle_pedido`

`pedidos` almacena la cabecera de la venta: cliente, fecha, estado, forma de retiro, pago y total.

`detalle_pedido` almacena las líneas de esa venta: cada producto, cantidad, talla, color, precio unitario y subtotal. Un pedido puede tener muchas filas en `detalle_pedido`.

La relación correcta es:

- `pedidos.id_pedido` → un pedido.
- `detalle_pedido.id_pedido` → todos los productos pertenecientes a ese pedido.

El módulo `PanelAdministrador/pedidos.php` ahora registra y actualiza ambas tablas dentro de una transacción. La API pública `api/guardar_pedido.php` también registra sus detalles.

## TODO importantes

1. **TODO seguridad:** mantener consultas preparadas y validar toda entrada antes de guardar.
2. **TODO pedidos:** cualquier cambio en productos de un pedido debe sincronizar `pedidos.total_pedido` y `detalle_pedido` dentro de la misma transacción.
3. **TODO stock:** definir formalmente si un pedido manual debe descontar stock al registrarse, al confirmarse o al entregarse. Actualmente la tienda pública descuenta al crear el pedido; el panel manual prioriza no alterar stock histórico durante ediciones.
4. **TODO estados:** evitar eliminar datos con historial. Preferir estado inactivo/oculto.
5. **TODO paginación:** conservar siempre `pagina` y `buscar` al editar, cancelar o eliminar.
6. **TODO interfaz:** no añadir reglas CSS globales para columnas específicas; usar clases de página como `admin-catalog-page` y `admin-orders-page`.
7. **TODO base de datos:** ejecutar `database/actualizacion_2026.sql` en instalaciones antiguas y conservar copias de seguridad antes de cambios estructurales.
8. **TODO pruebas:** probar pedidos con uno y varios productos, delivery sin dirección, edición de cantidades, eliminación y pedidos creados desde la tienda.
