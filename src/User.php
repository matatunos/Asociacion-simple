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
