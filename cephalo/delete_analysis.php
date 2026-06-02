<?php
/**
 * Poli Gigi & Mulut — Hapus Riwayat Analisis Sefalometri
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['admin', 'dokter']);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_analisis'])) {
    $id_analisis = (int)$_POST['id_analisis'];
    $pdo = getDBConnection();

    try {
        // Fetch image path to delete the physical file
        $stmt = $pdo->prepare("SELECT foto_rontgen FROM modul_11_sefalometri WHERE id_analisis = ?");
        $stmt->execute([$id_analisis]);
        $row = $stmt->fetch();

        if ($row) {
            $foto_rontgen = $row['foto_rontgen'];
            // If it's not a base64 image, delete the physical file from uploads folder
            if (strpos($foto_rontgen, 'data:image/') !== 0) {
                $filePath = __DIR__ . '/uploads/' . $foto_rontgen;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // Delete database record
            $del = $pdo->prepare("DELETE FROM modul_11_sefalometri WHERE id_analisis = ?");
            $del->execute([$id_analisis]);

            echo "<script>alert('Data analisis sefalometri berhasil dihapus.'); window.location.href='index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Data analisis tidak ditemukan.'); window.location.href='index.php';</script>";
            exit();
        }
    } catch (PDOException $e) {
        $msg = addslashes($e->getMessage());
        echo "<script>alert('Gagal menghapus data: $msg'); window.location.href='index.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
