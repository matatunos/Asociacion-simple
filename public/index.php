<?php
// Cargar configuración primero para poder usar session_name antes de iniciar la sesión
$config = require __DIR__ . '/../config.php';

// Fijar nombre de sesión (si procede) y luego iniciar sesión
session_name($config['session_name'] ?? 'asoc_session');
session_start();

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Payment.php';
require_once __DIR__ . '/../src/Voucher.php';
require_once __DIR__ . '/../src/Member.php';


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
        if ($action === 'create_voucher') {
            Voucher::create($db, $_POST['code'], $_POST['event_name'], $_POST['template'], $_POST['valid_from'] ?: null, $_POST['valid_to'] ?: null, $_POST['user_id'] ?: null);
            header('Location: index.php?page=vouchers'); exit;
        }
        $vouchers = Voucher::all($db);
        require __DIR__ . '/../templates/vouchers.php';
        break;


// ... dentro del switch($page) agrega el case 'agenda' exactamente:
case 'agenda':
    // Solo administradores pueden gestionar la agenda
    if ($user['role'] !== 'admin') { http_response_code(403); echo "Acceso denegado."; exit; }
    $year = date('Y');

    // Acciones POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? null;
        if ($action === 'create') {
            $data = [
                'name'=>trim($_POST['name'] ?? ''),
                'email'=>trim($_POST['email'] ?? ''),
                'phone'=>trim($_POST['phone'] ?? ''),
                'address'=>trim($_POST['address'] ?? '')
            ];
            Member::create($db, $data);
            header('Location: index.php?page=agenda'); exit;
        }
        if ($action === 'update' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $data = [
                'name'=>trim($_POST['name'] ?? ''),
                'email'=>trim($_POST['email'] ?? ''),
                'phone'=>trim($_POST['phone'] ?? ''),
                'address'=>trim($_POST['address'] ?? '')
            ];
            Member::update($db, $id, $data);
            header('Location: index.php?page=agenda'); exit;
        }
        if ($action === 'delete' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            Member::delete($db, $id);
            header('Location: index.php?page=agenda'); exit;
        }
        if ($action === 'toggle_payment' && !empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            Member::togglePayment($db, $id, $year);
            header('Location: index.php?page=agenda'); exit;
        }
    }

    // GET: lista y posible edición
    if (isset($_GET['edit'])) {
        $editMember = Member::find($db, (int)$_GET['edit']);
    }
    $members = Member::allWithPaid($db, $year);
    require __DIR__ . '/../templates/agenda.php';
    break;

    default:
        echo "Página no encontrada.";
        break;
}
