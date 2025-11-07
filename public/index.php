<?php
// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

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

// Helper function for input validation
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

$page = $_GET['page'] ?? 'login';
$action = $_POST['action'] ?? null;
$error = null;

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
        // Obtener estadísticas para el dashboard
        $stats = [];
        $stats['total_users'] = $db->pdo()->query("SELECT COUNT(*) FROM users WHERE active=1")->fetchColumn();
        $stats['total_payments'] = $db->pdo()->query("SELECT COUNT(*) FROM payments")->fetchColumn();
        $stats['total_vouchers'] = $db->pdo()->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();
        $stats['current_year'] = date('Y');
        $stmt = $db->pdo()->prepare("SELECT COUNT(*) FROM memberships WHERE year = :y AND paid = 1");
        $stmt->execute([':y' => $stats['current_year']]);
        $stats['paid_memberships'] = $stmt->fetchColumn();
        require __DIR__ . '/../templates/dashboard.php';
        break;

    case 'users':
        if ($user['role'] !== 'admin') { http_response_code(403); echo "Acceso denegado."; exit; }
        // acciones simples: crear, borrar, editar
        if ($action === 'create' && !empty($_POST['email'])) {
            if (!validate_email($_POST['email'])) {
                $error = "Email inválido.";
            } elseif (empty($_POST['name']) || strlen($_POST['name']) < 2) {
                $error = "El nombre debe tener al menos 2 caracteres.";
            } else {
                try {
                    User::create($db, $_POST['email'], $_POST['name'], $_POST['password'] ?? null, $_POST['role'] ?? 'member');
                    header('Location: index.php?page=users'); exit;
                } catch (PDOException $e) {
                    $error = "Error al crear usuario: " . (strpos($e->getMessage(), 'Duplicate') !== false ? "El email ya existe." : "Error de base de datos.");
                }
            }
        }
        if ($action === 'delete' && !empty($_POST['id'])) {
            User::delete($db, (int)$_POST['id']);
            header('Location: index.php?page=users'); exit;
        }
        if ($action === 'update' && !empty($_POST['id'])) {
            if (!validate_email($_POST['email'])) {
                $error = "Email inválido.";
            } elseif (empty($_POST['name']) || strlen($_POST['name']) < 2) {
                $error = "El nombre debe tener al menos 2 caracteres.";
            } else {
                try {
                    User::update($db, (int)$_POST['id'], $_POST['email'], $_POST['name'], $_POST['role'], $_POST['password'] ?: null);
                    header('Location: index.php?page=users'); exit;
                } catch (PDOException $e) {
                    $error = "Error al actualizar usuario: " . (strpos($e->getMessage(), 'Duplicate') !== false ? "El email ya existe." : "Error de base de datos.");
                }
            }
        }
        $editUser = isset($_GET['edit']) ? User::get($db, (int)$_GET['edit']) : null;
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
        if ($action === 'create_membership') {
            $uid = (int)$_POST['user_id'];
            $year = (int)$_POST['year'];
            $amount = (float)$_POST['amount'];
            Payment::createMembership($db, $uid, $year, $amount);
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
        if ($action === 'mark_used' && !empty($_POST['id'])) {
            Voucher::markAsUsed($db, (int)$_POST['id']);
            header('Location: index.php?page=vouchers'); exit;
        }
        $vouchers = Voucher::all($db);
        require __DIR__ . '/../templates/vouchers.php';
        break;

    default:
        echo "Página no encontrada.";
        break;
}
