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
    public static function createMembership(Database $db, $user_id, $year, $amount) {
        $pdo = $db->pdo();
        // ON DUPLICATE KEY UPDATE: only update amount, keep paid status unchanged
        $stmt = $pdo->prepare("INSERT INTO memberships (user_id, year, amount, paid) VALUES (:u, :y, :amt, 0) ON DUPLICATE KEY UPDATE amount=VALUES(amount)");
        $stmt->execute([':u'=>$user_id, ':y'=>$year, ':amt'=>$amount]);
    }
}
