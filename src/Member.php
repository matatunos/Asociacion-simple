<?php
/**
 * Clase Member - Gestión de socios de la asociación
 * Almacena información de contacto de los socios para uso interno (Agenda)
 */
class Member {
    /**
     * Crear un nuevo socio
     */
    public static function create(Database $db, array $data) {
        $pdo = $db->pdo();
        $stmt = $pdo->prepare("
            INSERT INTO members (name, email, phone, address, city, postal_code, notes)
            VALUES (:name, :email, :phone, :address, :city, :postal_code, :notes)
        ");
        $stmt = $pdo->prepare(
            "INSERT INTO members (name, email, phone, address, city, postal_code, notes) 
             VALUES (:name, :email, :phone, :address, :city, :postal_code, :notes)"
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

    /**
     * Obtener todos los socios
     */
    public static function all(Database $db) {
        $stmt = $db->pdo()->query("
            SELECT id, name, email, phone, address, city, postal_code, notes, created_at, updated_at
            FROM members
            ORDER BY name ASC
        ");
        $stmt = $db->pdo()->query(
            "SELECT id, name, email, phone, address, city, postal_code, notes, created_at, updated_at 
             FROM members 
             ORDER BY id DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar un socio por ID
     */
    public static function find(Database $db, $id) {
        $stmt = $db->pdo()->prepare("
            SELECT id, name, email, phone, address, city, postal_code, notes, created_at, updated_at
            FROM members
            WHERE id = :id
            LIMIT 1
        ");
     * Obtener un socio por ID
     */
    public static function find(Database $db, $id) {
        $stmt = $db->pdo()->prepare(
            "SELECT id, name, email, phone, address, city, postal_code, notes, created_at, updated_at 
             FROM members 
             WHERE id = :id 
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar un socio
     */
    public static function update(Database $db, $id, array $data) {
        $pdo = $db->pdo();
        $stmt = $pdo->prepare("
            UPDATE members
            SET name = :name,
                email = :email,
                phone = :phone,
                address = :address,
                city = :city,
                postal_code = :postal_code,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt = $pdo->prepare(
            "UPDATE members 
             SET name = :name, 
                 email = :email, 
                 phone = :phone, 
                 address = :address, 
                 city = :city, 
                 postal_code = :postal_code, 
                 notes = :notes,
                 updated_at = CURRENT_TIMESTAMP
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

    /**
     * Eliminar un socio
     */
    public static function delete(Database $db, $id) {
        $stmt = $db->pdo()->prepare("DELETE FROM members WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
