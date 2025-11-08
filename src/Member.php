<?php
/**
 * Clase Member - Gestión de socios de la asociación
 *
 * Nota sobre pagos: para resolver el estado 'paid' por socio se hace LEFT JOIN con users -> memberships (year).
 * Si el socio no está vinculado a un user (por email) no se podrá crear membership; togglePayment devolverá false.
 */
class Member {
    public static function create(Database $db, array $data) {
        $pdo = $db->pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO members (name, email, phone, address, city, postal_code, notes, created_at)
             VALUES (:name, :email, :phone, :address, :city, :postal_code, :notes, NOW())"
        );
        $stmt->execute([
            ':name' => $data['name'] ?? '',
            ':email' => $data['email'] ?? '',
            ':phone' => $data['phone'] ?? '',
            ':address' => $data['address'] ?? '',
            ':city' => $data['city'] ?? '',
            ':postal_code' => $data['postal_code'] ?? '',
            ':notes' => $data['notes'] ?? ''
        ]);
        return $pdo->lastInsertId();
    }

    public static function all(Database $db) {
        $stmt = $db->pdo()->query(
            "SELECT id, name, email, phone, address, city, postal_code, notes, created_at
             FROM members
             ORDER BY name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve la lista de miembros con un campo 'paid' (1/0) para el año indicado.
     * Implementación: LEFT JOIN users por email y LEFT JOIN memberships por user_id+year.
     */
    public static function allWithPaid(Database $db, $year) {
        $sql = "
            SELECT m.*, IFNULL(mem.paid,0) AS paid
            FROM members m
            LEFT JOIN users u ON u.email = m.email
            LEFT JOIN memberships mem ON mem.user_id = u.id AND mem.year = :year
            ORDER BY m.name ASC
        ";
        $stmt = $db->pdo()->prepare($sql);
        $stmt->execute([':year'=>$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(Database $db, $id) {
        $stmt = $db->pdo()->prepare(
            "SELECT id, name, email, phone, address, city, postal_code, notes, created_at
             FROM members
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update(Database $db, $id, array $data) {
        $stmt = $db->pdo()->prepare(
            "UPDATE members
             SET name = :name, email = :email, phone = :phone, address = :address,
                 city = :city, postal_code = :postal_code, notes = :notes, updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'] ?? '',
            ':email' => $data['email'] ?? '',
            ':phone' => $data['phone'] ?? '',
            ':address' => $data['address'] ?? '',
            ':city' => $data['city'] ?? '',
            ':postal_code' => $data['postal_code'] ?? '',
            ':notes' => $data['notes'] ?? ''
        ]);
    }

    public static function delete(Database $db, $id) {
        $stmt = $db->pdo()->prepare("DELETE FROM members WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Alterna o crea el registro de membership para el user asociado a este member (por email).
     * Si el member no está vinculado a un user (users.email != members.email) devuelve false.
     * Si existe membership para ese año, alterna paid (0->1 o 1->0). Si no existe, crea con paid=1.
     */
    public static function togglePayment(Database $db, $memberId, $year) {
        $pdo = $db->pdo();
        // buscar member y su email
        $stmt = $pdo->prepare("SELECT email FROM members WHERE id = :id LIMIT 1");
        $stmt->execute([':id'=>$memberId]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$m) return false;
        // buscar user por email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email'=>$m['email']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) return false; // no hay usuario asociado para crear/alternar membership

        $user_id = (int)$u['id'];

        // buscar membership
        $stmt = $pdo->prepare("SELECT id, paid FROM memberships WHERE user_id = :uid AND year = :year LIMIT 1");
        $stmt->execute([':uid'=>$user_id, ':year'=>$year]);
        $mem = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($mem) {
            $newPaid = $mem['paid'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE memberships SET paid = :paid, paid_at = CASE WHEN :paid=1 THEN NOW() ELSE NULL END WHERE id = :id");
            $stmt->execute([':paid'=>$newPaid, ':id'=>$mem['id']]);
            return true;
        } else {
            // crear membership con cantidad 0 y paid=1
            $stmt = $pdo->prepare("INSERT INTO memberships (user_id, year, paid, amount, created_at, paid_at) VALUES (:uid, :year, 1, 0, NOW(), NOW())");
            $stmt->execute([':uid'=>$user_id, ':year'=>$year]);
            return true;
        }
    }
}
