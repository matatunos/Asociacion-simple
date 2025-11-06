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
