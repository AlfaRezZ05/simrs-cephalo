<?php
/**
 * Database Configuration (XAMPP MySQL)
 * Project: MedWeb SIMRS-Cephalo
 */

define('BASE_URL', '/simrs-cephalo');

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'backbone_medweb';
        $user = 'root';
        $pass = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Self-healing: Ensure database users table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Self-healing: Ensure role column exists in users table
            $q = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
            if (!$q->fetch()) {
                $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'patient' AFTER password;");
            }

        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    return $pdo;
}
?>
