BOUTIQUE WEB - VERSIÓN MEJORADA 2026

ACCESO ADMINISTRADOR
Ruta: http://localhost/boutique_web/PanelAdministrador/
Usuario inicial: admin@boutique.com
Contraseña inicial: Admin123

MEJORAS IMPLEMENTADAS
- Recuperación de contraseña mediante enlace temporal de 30 minutos.
- Registro de pedidos en las tablas pedidos y detalle_pedido antes de abrir WhatsApp.
- Selección de retiro: delivery o retiro en local.
- Envío del resumen completo al WhatsApp configurado en informacion_empresa.telefono_whatsapp.
- Productos con tallas y colores, administrables mediante listas separadas por comas.
- Botón directo Agregar al carrito y botón compacto para ver detalles.
- Panel administrador simplificado con métricas y pedidos recientes.

BASE DE DATOS
Las tablas nuevas se crean automáticamente al entrar al panel o confirmar un pedido.
También puede ejecutarse manualmente database/actualizacion_2026.sql en phpMyAdmin.

CORREO DE RECUPERACIÓN
La función utiliza mail() de PHP. En un servidor web normalmente funciona al configurar el correo del hosting.
En Laragon/XAMPP es necesario configurar SMTP/sendmail en php.ini. Sin SMTP, el sistema genera el token pero no podrá entregar el correo.
Para producción se recomienda PHPMailer con SMTP autenticado (Gmail, Brevo, Mailgun o el correo del dominio).

WHATSAPP
Verificar que informacion_empresa.telefono_whatsapp incluya código de país, por ejemplo: 595981123456.

ACTUALIZACIÓN V7
- Migración automática de tablas antiguas de pedidos y detalle_pedido.
- Clientes con teléfono principal y secundario.
- Pedidos manuales seleccionando un cliente registrado.
- CRUD de categorías y marcas dentro del panel administrador.
- Eliminación de clientes, pedidos y productos con validaciones.
- Botones de acciones ajustados para visualizarse completos.
