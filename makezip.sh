#!/bin/bash
set -e
ROOT="$(pwd)"
echo "Creando estructura y ficheros en $ROOT"

# Directorios
mkdir -p sql public src templates

# README.md
cat > README.md <<'EOF'
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
EOF

# INSTALL.md
cat > INSTALL.md <<'EOF'
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
- Borra o protege `install.php` tras la instalación.

3. Archivos importantes
- `sql/schema.sql` — esquema SQL si prefieres ejecutar manualmente.
- `templates/` — edita las plantillas HTML para personalizar vales y documentos.
- `src/` — lógica de la aplicación.

4. Recomendaciones
- Activa HTTPS en producción.
- Cambia la contraseña admin y crea roles/usuarios.
- Realiza copias de seguridad de la base de datos periódicamente.
- Limita el acceso a ficheros de configuración.
EOF

# config.example.php
cat > config.example.php <<'EOF'
<?php
// Copiar a config.php y editar
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'asociacion_db',
        'user' => 'asoc_user',
        'pass' => 'change_me'
    ],
    'base_url' => 'http://localhost/asociacion', // sin barra final
    'session_name' => 'asoc_session',
    'admin_default_password' => 'admin123',
];
EOF

# sql/schema.sql
cat > sql/schema.sql <<'EOF'
-- Esquema básico para la asociación
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(200) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','member') NOT NULL DEFAULT 'member',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS memberships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  year SMALLINT NOT NULL,
  paid TINYINT(1) NOT NULL DEFAULT 0,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  paid_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY user_year (user_id, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  membership_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(100) NOT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vouchers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(100) NOT NULL UNIQUE,
  user_id INT NULL,
  event_name VARCHAR(255) NOT NULL,
  template TEXT NOT NULL,
  valid_from DATE NULL,
  valid_to DATE NULL,
  used TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF

# install.php
cat > install.php <<'EOF'
<?php
// Script simple para crear tablas y usuario admin.
// Úsalo una sola vez desde el navegador y luego bórralo o protégelo.
$config = require __DIR__ . '/config.php';
$dsn = "mysql:host={$config['db']['host']};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db']['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$config['db']['name']}`");
    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    $pdo->exec($sql);

    // Crear usuario admin por defecto si no existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => 'admin@example.org']);
    if (!$stmt->fetch()) {
        $pwd = password_hash($config['admin_default_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, name, password, role) VALUES (:email, :name, :pwd, 'admin')");
        $stmt->execute([':email' => 'admin@example.org', ':name' => 'Administrador', ':pwd' => $pwd]);
        echo "Usuario admin creado: admin@example.org / {$config['admin_default_password']}<br>";
    } else {
        echo "Usuario admin ya existe.<br>";
    }

    echo "Instalación completada. BORRA este archivo install.php por seguridad.";
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
EOF

# .gitignore
cat > .gitignore <<'EOF'
# Ignorar config con credenciales reales
config.php

# Dependencias y entorno
/vendor
/.env
/.idea
*.log
EOF

# public/.htaccess
cat > public/.htaccess <<'EOF'
# Si usas Apache, redirige todas las peticiones a index.php para un enrutado sencillo
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]
EOF

# public/index.php
cat > public/index.php <<'EOF'
<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Payment.php';
require_once __DIR__ . '/../src/Voucher.php';

$config = require __DIR__ . '/../config.php';
$db = new Database($config['db']);
$auth = new Auth($db, $config);

$page = $_GET['page'] ?? 'login';
$action = $_POST['action'] ?? null;

if ($page === 'logout') {
    $auth->logout();
    header('Location: index.php');
    exit;
}

if ($page === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
        if ($auth->login($_POST['email'], $_POST['password'])) {
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
    require __DIR__ . '/../templates/login.php';
    exit;
}

// todas las páginas siguientes requieren autenticación
if (!$auth->check()) {
    header('Location: index.php?page=login');
    exit;
}

$user = $auth->user();

switch ($page) {
    case 'dashboard':
        require __DIR__ . '/../templates/dashboard.php';
        break;

    case 'users':
        if ($user['role'] !== 'admin') { http_response_code(403); echo "Acceso denegado."; exit; }
        // acciones simples: crear, borrar, editar
        if ($action === 'create' && !empty($_POST['email'])) {
            User::create($db, $_POST['email'], $_POST['name'], $_POST['password'] ?? null, $_POST['role'] ?? 'member');
            header('Location: index.php?page=users'); exit;
        }
        if ($action === 'delete' && !empty($_POST['id'])) {
            User::delete($db, (int)$_POST['id']);
            header('Location: index.php?page=users'); exit;
        }
        $users = User::all($db);
        require __DIR__ . '/../templates/users.php';
        break;

    case 'payments':
        // listar pagos y gestionar cuotas
        if ($action === 'record_payment') {
            $uid = (int)$_POST['user_id'];
            $amount = (float)$_POST['amount'];
            $method = $_POST['method'] ?? 'efectivo';
            Payment::record($db, $uid, null, $amount, $method, $_POST['notes'] ?? null);
            header('Location: index.php?page=payments'); exit;
        }
        $payments = Payment::all($db);
        $memberships = Payment::membershipsForYear($db, date('Y'));
        require __DIR__ . '/../templates/payments.php';
        break;

    case 'vouchers':
        // Crear y listar vales
        if ($action === 'create_voucher') {
            Voucher::create($db, $_POST['code'], $_POST['event_name'], $_POST['template'], $_POST['valid_from'] ?: null, $_POST['valid_to'] ?: null, $_POST['user_id'] ?: null);
            header('Location: index.php?page=vouchers'); exit;
        }
        $vouchers = Voucher::all($db);
        require __DIR__ . '/../templates/vouchers.php';
        break;

    default:
        echo "Página no encontrada.";
        break;
}
EOF

# src/Database.php
cat > src/Database.php <<'EOF'
<?php
class Database {
    private $pdo;
    public function __construct(array $cfg) {
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
    public function pdo() { return $this->pdo; }
}
EOF

# src/Auth.php
cat > src/Auth.php <<'EOF'
<?php
class Auth {
    private $db;
    private $cfg;
    public function __construct(Database $db, array $cfg) {
        $this->db = $db->pdo();
        $this->cfg = $cfg;
        session_name($cfg['session_name'] ?? 'app_session');
    }
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :e AND active = 1 LIMIT 1");
        $stmt->execute([':e' => $email]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u && password_verify($password, $u['password'])) {
            $_SESSION['user_id'] = $u['id'];
            return true;
        }
        return false;
    }
    public function check() {
        return !empty($_SESSION['user_id']);
    }
    public function logout() {
        session_unset();
        session_destroy();
    }
    public function user() {
        if (empty($_SESSION['user_id'])) return null;
        $stmt = $this->db->prepare("SELECT id, email, name, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
EOF

# src/User.php
cat > src/User.php <<'EOF'
<?php
class User {
    public static function create(Database $db, $email, $name, $password = null, $role = 'member') {
        $pdo = $db->pdo();
        $pwd = $password ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, name, password, role) VALUES (:e, :n, :p, :r)");
        $stmt->execute([':e'=>$email, ':n'=>$name, ':p'=>$pwd, ':r'=>$role]);
    }
    public static function all(Database $db) {
        $stmt = $db->pdo()->query("SELECT id, email, name, role, active, created_at FROM users ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function delete(Database $db, $id) {
        $stmt = $db->pdo()->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id'=>$id]);
    }
}
EOF

# src/Payment.php
cat > src/Payment.php <<'EOF'
<?php
class Payment {
    public static function record(Database $db, $user_id, $membership_id = null, $amount = 0.0, $method = 'efectivo', $notes = null) {
        $pdo = $db->pdo();
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, membership_id, amount, method, notes) VALUES (:u, :m_id, :amt, :method, :notes)");
        $stmt->execute([':u'=>$user_id, ':m_id'=>$membership_id, ':amt'=>$amount, ':method'=>$method, ':notes'=>$notes]);
        if ($membership_id) {
            $stmt2 = $pdo->prepare("UPDATE memberships SET paid=1, paid_at=NOW() WHERE id = :id");
            $stmt2->execute([':id'=>$membership_id]);
        }
    }
    public static function all(Database $db) {
        $stmt = $db->pdo()->query("SELECT p.*, u.email, u.name FROM payments p LEFT JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function membershipsForYear(Database $db, $year) {
        $stmt = $db->pdo()->prepare("SELECT m.*, u.email, u.name FROM memberships m JOIN users u ON u.id = m.user_id WHERE m.year = :y ORDER BY u.name");
        $stmt->execute([':y'=>$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
EOF

# src/Voucher.php
cat > src/Voucher.php <<'EOF'
<?php
class Voucher {
    public static function create(Database $db, $code, $event_name, $template, $valid_from = null, $valid_to = null, $user_id = null) {
        $pdo = $db->pdo();
        $stmt = $pdo->prepare("INSERT INTO vouchers (code, user_id, event_name, template, valid_from, valid_to) VALUES (:code, :uid, :evt, :tpl, :vf, :vt)");
        $stmt->execute([
            ':code'=>$code, ':uid'=>$user_id ?: null, ':evt'=>$event_name, ':tpl'=>$template,
            ':vf'=>$valid_from ?: null, ':vt'=>$valid_to ?: null
        ]);
    }
    public static function all(Database $db) {
        $stmt = $db->pdo()->query("SELECT v.*, u.email, u.name FROM vouchers v LEFT JOIN users u ON u.id = v.user_id ORDER BY v.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function renderTemplate($template, $data=[]) {
        // reemplaza {{key}} en la plantilla por valores en $data
        $out = $template;
        foreach ($data as $k=>$v) {
            $out = str_replace('{{'.$k.'}}', htmlspecialchars($v), $out);
        }
        return $out;
    }
}
EOF

# templates/layout.php
cat > templates/layout.php <<'EOF'
<?php
// Variables esperadas: $title, $content, $user, $config
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title><?=htmlspecialchars($title ?? 'Asociación')?></title>
  <style>
    body { font-family: Arial, sans-serif; max-width:1000px; margin:20px auto; }
    nav { margin-bottom: 1rem; }
    nav a { margin-right: 10px; }
    table { width:100%; border-collapse:collapse; }
    th,td{ border:1px solid #ddd; padding:8px; }
    .error { color:red; }
  </style>
</head>
<body>
  <header>
    <h1>Gestor de Asociación</h1>
    <nav>
      <a href="index.php?page=dashboard">Inicio</a>
      <a href="index.php?page=payments">Pagos</a>
      <a href="index.php?page=vouchers">Vales</a>
      <?php if($user && $user['role']==='admin'): ?><a href="index.php?page=users">Usuarios</a><?php endif; ?>
      <a href="index.php?page=logout">Cerrar sesión</a>
    </nav>
    <hr/>
  </header>
  <main>
    <?= $content ?>
  </main>
  <footer><hr/>Gestor básico · <?=date('Y')?></footer>
</body>
</html>
EOF

# templates/login.php
cat > templates/login.php <<'EOF'
<?php
$title = "Acceso";
ob_start();
?>
<h2>Acceder</h2>
<?php if(!empty($error)): ?><p class="error"><?=htmlspecialchars($error)?></p><?php endif; ?>
<form method="post" action="index.php?page=login">
  <label>Email:<br/><input type="email" name="email" required></label><br/><br/>
  <label>Contraseña:<br/><input type="password" name="password" required></label><br/><br/>
  <button type="submit">Entrar</button>
</form>
<?php
$content = ob_get_clean();
$user = null;
$config = $config ?? [];
require __DIR__ . '/layout.php';
EOF

# templates/dashboard.php
cat > templates/dashboard.php <<'EOF'
<?php
$title = "Panel";
ob_start();
?>
<h2>Bienvenido, <?=htmlspecialchars($user['name'])?></h2>
<p>Últimas acciones: panel resumido. Usa el menú para navegar.</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
EOF

# templates/users.php
cat > templates/users.php <<'EOF'
<?php
$title = "Usuarios";
ob_start();
?>
<h2>Usuarios</h2>
<h3>Crear usuario</h3>
<form method="post" action="index.php?page=users">
  <input type="hidden" name="action" value="create">
  <label>Email: <input type="email" name="email" required></label><br/>
  <label>Nombre: <input type="text" name="name" required></label><br/>
  <label>Contraseña (opcional): <input type="text" name="password"></label><br/>
  <label>Rol:
    <select name="role">
      <option value="member">Socio</option>
      <option value="admin">Admin</option>
    </select>
  </label><br/><br/>
  <button type="submit">Crear</button>
</form>

<h3>Lista</h3>
<table>
  <tr><th>ID</th><th>Email</th><th>Nombre</th><th>Rol</th><th>Acciones</th></tr>
  <?php foreach($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= $u['role'] ?></td>
      <td>
        <form method="post" style="display:inline">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $u['id'] ?>">
          <button type="submit" onclick="return confirm('Borrar usuario?')">Borrar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
EOF

# templates/payments.php
cat > templates/payments.php <<'EOF'
<?php
$title = "Pagos y Cuotas";
ob_start();
?>
<h2>Pagos</h2>

<h3>Registrar pago</h3>
<form method="post" action="index.php?page=payments">
  <input type="hidden" name="action" value="record_payment">
  <label>ID Usuario: <input type="number" name="user_id" required></label><br/>
  <label>Importe: <input type="number" step="0.01" name="amount" required></label><br/>
  <label>Método: <input type="text" name="method" value="efectivo"></label><br/>
  <label>Notas: <textarea name="notes"></textarea></label><br/>
  <button type="submit">Registrar pago</button>
</form>

<h3>Pagos recientes</h3>
<table>
<tr><th>ID</th><th>Usuario</th><th>Importe</th><th>Método</th><th>Fecha</th></tr>
<?php foreach($payments as $p): ?>
  <tr>
    <td><?= $p['id'] ?></td>
    <td><?= htmlspecialchars($p['name'] . ' <' . $p['email'] . '>') ?></td>
    <td><?= $p['amount'] ?></td>
    <td><?= htmlspecialchars($p['method']) ?></td>
    <td><?= $p['created_at'] ?></td>
  </tr>
<?php endforeach; ?>
</table>

<h3>Cuotas del año <?=date('Y')?></h3>
<table>
<tr><th>Usuario</th><th>Año</th><th>Pagado</th><th>Importe</th></tr>
<?php foreach($memberships as $m): ?>
  <tr>
    <td><?=htmlspecialchars($m['name'].' <'.$m['email'].'>')?></td>
    <td><?= $m['year'] ?></td>
    <td><?= $m['paid'] ? 'Sí' : 'No' ?></td>
    <td><?= $m['amount'] ?></td>
  </tr>
<?php endforeach; ?>
</table>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
EOF

# templates/vouchers.php
cat > templates/vouchers.php <<'EOF'
<?php
$title = "Vales y Documentos";
ob_start();
?>
<h2>Vales</h2>

<h3>Crear vale</h3>
<form method="post" action="index.php?page=vouchers">
  <input type="hidden" name="action" value="create_voucher">
  <label>Código: <input type="text" name="code" required></label><br/>
  <label>Nombre evento: <input type="text" name="event_name" required></label><br/>
  <label>Usuario asociado (id) opcional: <input type="number" name="user_id"></label><br/>
  <label>Válido desde: <input type="date" name="valid_from"></label><br/>
  <label>Válido hasta: <input type="date" name="valid_to"></label><br/>
  <label>Plantilla (usa {{name}}, {{email}}, {{event_name}}, {{code}}):<br/>
    <textarea name="template" rows="6">Vale para {{name}} ({{email}}) — Evento: {{event_name}} — Código: {{code}}</textarea>
  </label><br/>
  <button type="submit">Crear vale</button>
</form>

<h3>Lista de vales</h3>
<table>
<tr><th>ID</th><th>Código</th><th>Usuario</th><th>Evento</th><th>Usado</th><th>Acción</th></tr>
<?php foreach($vouchers as $v): ?>
  <tr>
    <td><?= $v['id'] ?></td>
    <td><?= htmlspecialchars($v['code']) ?></td>
    <td><?= htmlspecialchars($v['name'] . ' <' . $v['email'] . '>') ?></td>
    <td><?= htmlspecialchars($v['event_name']) ?></td>
    <td><?= $v['used'] ? 'Sí' : 'No' ?></td>
    <td>
      <?php
        $tpl = $v['template'];
        $rendered = \Voucher::renderTemplate($tpl, [
            'name'=>$v['name'] ?? '',
            'email'=>$v['email'] ?? '',
            'event_name'=>$v['event_name'],
            'code'=>$v['code']
        ]);
      ?>
      <a href="#" onclick="alert(<?=json_encode($rendered)?>); return false;">Vista</a>
    </td>
  </tr>
<?php endforeach; ?>
</table>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
EOF

# Crear zip
ZIP_NAME="asociacion.zip"
echo "Generando $ZIP_NAME ..."

# Excluir .git y el zip mismo si ya existe
if command -v zip >/dev/null 2>&1; then
  zip -r "$ZIP_NAME" . -x "*.git*" "$ZIP_NAME" >/dev/null
  echo "Zip creado: $ROOT/$ZIP_NAME"
else
  echo "zip no está instalado. Intentando usar tar para crear .tar.gz en su lugar."
  TAR_NAME="asociacion.tar.gz"
  tar --exclude='.git' -czf "$TAR_NAME" .
  echo "Archivo creado: $ROOT/$TAR_NAME"
fi

echo "Hecho."
