# Manual de instalación

1. Requisitos
- PHP 7.4+ con PDO_MySQL.
- MariaDB (o MySQL).
- Servidor web (Apache/Nginx). Si usas Apache, activa mod_rewrite si quieres rutas más limpias.

2. Pasos
- Clona o copia el repositorio al servidor.
- Copia `config.example.php` a `config.php` y rellena los datos de conexión a la base de datos y la URL base:
  - DB_HOST, DB_NAME, DB_USER, DB_PASS.
  - BASE_URL (p. ej. http://localhost/mi-asociacion).
- Crea la base de datos indicada en `config.php` si no existe.
- Desde navegador, ejecuta `http://TU_BASE_URL/install.php` para crear las tablas y un usuario admin inicial.
  - Usuario admin por defecto: admin@example.org
  - Contraseña por defecto: admin123 (cámbiala inmediatamente desde el panel).
- Para habilitar la funcionalidad de Agenda, importa también el archivo `sql/members.sql`:
  ```bash
  mysql -u usuario -p nombre_base_datos < sql/members.sql
  ```
- Borra o protege `install.php` tras la instalación.

3. Archivos importantes
- `sql/schema.sql` — esquema SQL si prefieres ejecutar manualmente.
- `sql/members.sql` — tabla para la Agenda de socios (ejecutar después de schema.sql).
- `templates/` — edita las plantillas HTML para personalizar vales y documentos.
- `src/` — lógica de la aplicación.

4. Recomendaciones
- Activa HTTPS en producción.
- Cambia la contraseña admin y crea roles/usuarios.
- Realiza copias de seguridad de la base de datos periódicamente.
- Limita el acceso a ficheros de configuración.
