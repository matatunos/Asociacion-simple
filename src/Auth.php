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
