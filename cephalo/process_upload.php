<?php
/**
 * Poli Gigi & Mulut — Cephalo AI Form Processor
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['admin', 'dokter', 'patient']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdo = getDBConnection();
    $user = getCurrentUser();
    $userRole = getUserRole();

    // 1. Tangkap data teks dari form
    $nama = $_POST['nama_pasien'];
    if ($userRole === 'patient') {
        $nama = $user['name'];
    }
    $nik = $_POST['nik'];
    $usia = (int)$_POST['usia'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    // 2. Tangkap file foto rontgen
    $foto = $_FILES['foto_rontgen'];
    
    // Validasi jika tidak ada foto yang terkirim (Mencegah Kode Error 4)
    if ($foto['error'] != 0) {
        echo "<script>alert('Terjadi kesalahan saat membaca file gambar. Silakan coba pilih ulang fotonya.'); window.location.href='index.php';</script>";
        exit();
    }

    $nama_file_asli = $foto['name'];
    $tmp_file = $foto['tmp_name'];
    
    // Ganti nama file agar unik (mencegah bentrok jika nama file sama)
    $nama_file_baru = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", $nama_file_asli);
    
    // Tentukan alamat folder utama
    $folder_direktori = __DIR__ . '/uploads/';

    $use_base64_fallback = false;
    $foto_data_db = $nama_file_baru;

    // CEK OTOMATIS: Buat folder uploads jika belum ada
    if (!is_dir($folder_direktori)) {
        if (!@mkdir($folder_direktori, 0777, true)) {
            $use_base64_fallback = true;
        }
    }

    if (!$use_base64_fallback) {
        $folder_tujuan = $folder_direktori . $nama_file_baru;
        if (!@move_uploaded_file($tmp_file, $folder_tujuan)) {
            $use_base64_fallback = true;
        }
    }

    if ($use_base64_fallback) {
        $ext = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
        $data_img = file_get_contents($tmp_file);
        $foto_data_db = 'data:image/' . $ext . ';base64,' . base64_encode($data_img);
    }

    try {
        // 4. Simpan biodata ke tabel modul_11_pasien (Cek jika NIK sudah terdaftar)
        $check_sql = "SELECT id_pasien FROM modul_11_pasien WHERE nik = ? LIMIT 1";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$nik]);
        $existing_patient = $check_stmt->fetch();

        // Deteksi driver untuk penanganan ID Insert yang tepat
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($existing_patient) {
            $id_pasien_baru = $existing_patient['id_pasien'];
            // Update biodata pasien jika ada perubahan
            $update_sql = "UPDATE modul_11_pasien SET nama_pasien = ?, usia = ?, jenis_kelamin = ? WHERE id_pasien = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$nama, $usia, $jenis_kelamin, $id_pasien_baru]);
        } else {
            if ($driver === 'pgsql') {
                $sql_pasien = "INSERT INTO modul_11_pasien (nama_pasien, nik, usia, jenis_kelamin) VALUES (?, ?, ?, ?) RETURNING id_pasien";
                $stmt_pasien = $pdo->prepare($sql_pasien);
                $stmt_pasien->execute([$nama, $nik, $usia, $jenis_kelamin]);
                $id_pasien_baru = $stmt_pasien->fetchColumn();
            } else {
                $sql_pasien = "INSERT INTO modul_11_pasien (nama_pasien, nik, usia, jenis_kelamin) VALUES (?, ?, ?, ?)";
                $stmt_pasien = $pdo->prepare($sql_pasien);
                $stmt_pasien->execute([$nama, $nik, $usia, $jenis_kelamin]);
                $id_pasien_baru = $pdo->lastInsertId();
            }
        }

        // 5. Simpan nama foto rontgen ke tabel modul_11_sefalometri
        if ($driver === 'pgsql') {
            $sql_foto = "INSERT INTO modul_11_sefalometri (id_pasien, foto_rontgen) VALUES (?, ?) RETURNING id_analisis";
            $stmt_foto = $pdo->prepare($sql_foto);
            $stmt_foto->execute([$id_pasien_baru, $foto_data_db]);
            $id_analisis = $stmt_foto->fetchColumn();
        } else {
            $sql_foto = "INSERT INTO modul_11_sefalometri (id_pasien, foto_rontgen) VALUES (?, ?)";
            $stmt_foto = $pdo->prepare($sql_foto);
            $stmt_foto->execute([$id_pasien_baru, $foto_data_db]);
            $id_analisis = $pdo->lastInsertId();
        }

        // 6. Alihkan otomatis ke halaman UI Hasil Diagnosis
        header("Location: result.php?id=" . $id_analisis);
        exit();
    } catch (PDOException $e) {
        die("Error Database: " . $e->getMessage());
    }
} else {
    // Jika file diakses langsung tanpa lewat form
    header("Location: index.php");
    exit();
}
?>
