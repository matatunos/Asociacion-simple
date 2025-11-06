# Gestor de Asociación (PHP + MariaDB)

Proyecto básico para gestionar una asociación: usuarios, cuotas anuales, pagos, eventos y emisión de vales. Pensado para ser simple, extensible y fácil de instalar.

Características:
- Autenticación con usuario y contraseña (bcrypt).
- Roles: admin y socio.
- Gestión de usuarios (alta, baja, edición).
- Gestión de cuotas anuales y registro de pagos.
- Emisión de documentos/vales para eventos (plantillas HTML personalizables).
- Conexión con MariaDB vía PDO.
- Estructura de ficheros y un script de instalación para crear las tablas.

Estructura propuesta:
- public/ : punto de entrada (index.php) y recursos públicos.
- src/ : código principal (DB, Auth, modelos).
- templates/ : vistas HTML simples.
- sql/schema.sql : esquema SQL.
- config.example.php : ejemplo de configuración.
- install.php : script que crea las tablas y usuario admin.
- README.md, INSTALL.md : documentación.

Licencia: MIT (ajusta según necesites).
