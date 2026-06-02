<?php
/**
 * Database Configuration (Supports Local MySQL & Supabase PostgreSQL)
 * Project: MedWeb SIMRS-Cephalo
 */

// Deteksi cerdas apakah aplikasi berjalan di dalam sub-folder (seperti XAMPP)
$isSubFolder = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/simrs-cephalo') === 0;

if ($isSubFolder) {
    define('BASE_URL', '/simrs-cephalo'); // Mode XAMPP Lokal
} else {
    define('BASE_URL', ''); // Mode Vercel / Cloud (Root)
}

// Simple helper to load .env file if it exists (useful for local development)
function loadEnv() {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove quotes if any
            $value = trim($value, '"\'');
            if (!getenv($name)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv();

function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $connection = getenv('DB_CONNECTION') ?: 'mysql'; // Default to mysql locally, pgsql on production
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_DATABASE') ?: 'backbone_medweb';
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';

        try {
            if ($connection === 'mysql') {
                // 1. Connect without dbname to create database if it doesn't exist
                $tempPdo = new PDO("mysql:host=$host", $user, $pass);
                $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
                
                // 2. Connect directly to the database
                $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // 3. Self-healing: Ensure 'users' table exists (MySQL)
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(50) DEFAULT 'patient',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

                // Self-healing role column check for MySQL
                try {
                    $pdo->query("SELECT role FROM users LIMIT 1");
                } catch (PDOException $e) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'patient'");
                }

                // 4. Self-healing: Ensure 'tb_patients' table exists (MySQL)
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

                // 5. Self-healing: Ensure 'cephalo_patients' table exists (MySQL)
                $pdo->exec("CREATE TABLE IF NOT EXISTS cephalo_patients (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nik VARCHAR(20) NOT NULL UNIQUE,
                    nama VARCHAR(150) NOT NULL,
                    tanggal_lahir DATE,
                    jenis_kelamin ENUM('L','P'),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            } else {
                // PostgreSQL / Supabase
                // Standard default port for Postgres is 5432
                if ($port === '3306') {
                    $port = '5432';
                }
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
                $pdo = new PDO($dsn, $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // 3. Self-healing: Ensure 'users' table exists (PostgreSQL)
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(50) DEFAULT 'patient',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );");

                // Self-healing role column check for PostgreSQL
                try {
                    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'patient';");
                } catch (PDOException $e) {
                    // Fallback in case of older version or permissions
                }

                // 4. Self-healing: Ensure 'tb_patients' table exists (PostgreSQL)
                $pdo->exec("CREATE TABLE IF NOT EXISTS tb_patients (
                    id SERIAL PRIMARY KEY,
                    no_rm VARCHAR(50) NOT NULL UNIQUE,
                    nik VARCHAR(20) NOT NULL,
                    nama VARCHAR(150) NOT NULL,
                    tanggal_lahir DATE,
                    jenis_kelamin VARCHAR(2) CHECK (jenis_kelamin IN ('L','P')),
                    alamat TEXT,
                    no_telepon VARCHAR(20),
                    kategori_tb VARCHAR(50),
                    tipe_pasien VARCHAR(50),
                    fase_pengobatan VARCHAR(50),
                    status VARCHAR(50),
                    tanggal_mulai_pengobatan DATE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );");

                // 5. Self-healing: Ensure 'cephalo_patients' table exists (PostgreSQL)
                $pdo->exec("CREATE TABLE IF NOT EXISTS cephalo_patients (
                    id SERIAL PRIMARY KEY,
                    nik VARCHAR(20) NOT NULL UNIQUE,
                    nama VARCHAR(150) NOT NULL,
                    tanggal_lahir DATE,
                    jenis_kelamin VARCHAR(2) CHECK (jenis_kelamin IN ('L','P')),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );");
            }

            // Create default users if users table is empty (MySQL & PostgreSQL compatible)
            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
            if ($stmt->fetchColumn() == 0) {
                $seeds = [
                    ['Administrator', 'admin@simrs.com', 'admin123', 'admin'],
                    ['Dr. Medika Pratama', 'dokter@simrs.com', 'dokter123', 'dokter'],
                    ['Apt. Farhan Nugroho', 'farmasi@simrs.com', 'farmasi123', 'farmasi'],
                    ['Budi Santoso', 'pasien@simrs.com', 'pasien123', 'patient'],
                ];
                $ins = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                foreach ($seeds as $s) {
                    $ins->execute([$s[0], $s[1], password_hash($s[2], PASSWORD_DEFAULT), $s[3]]);
                }
            }

        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    return $pdo;
}
?>
