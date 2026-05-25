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
            // 1. Connect without dbname to create database if it doesn't exist
            $tempPdo = new PDO("mysql:host=$host", $user, $pass);
            $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            
            // 2. Connect directly to the database
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // 3. Self-healing: Ensure 'users' table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'patient',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Create default admin if users table is empty
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            if ($stmt->fetchColumn() == 0) {
                $defaultPass = password_hash('admin123', PASSWORD_DEFAULT);
                $pdo->exec("INSERT INTO users (name, email, password, role) VALUES ('Administrator', 'admin@simrs.com', '$defaultPass', 'admin')");
            }

            // 4. Self-healing: Ensure 'tb_patients' table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS tb_patients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                no_rm VARCHAR(50) NOT NULL UNIQUE,
                nik VARCHAR(20) NOT NULL,
                nama VARCHAR(150) NOT NULL,
                tanggal_lahir DATE,
                jenis_kelamin ENUM('L','P'),
                alamat TEXT,
                no_telepon VARCHAR(20),
                kategori_tb VARCHAR(50),
                tipe_pasien VARCHAR(50),
                fase_pengobatan VARCHAR(50),
                status VARCHAR(50),
                tanggal_mulai_pengobatan DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // 5. Self-healing: Ensure 'cephalo_patients' table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS cephalo_patients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nik VARCHAR(20) NOT NULL UNIQUE,
                nama VARCHAR(150) NOT NULL,
                tanggal_lahir DATE,
                jenis_kelamin ENUM('L','P'),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    return $pdo;
}
?>
