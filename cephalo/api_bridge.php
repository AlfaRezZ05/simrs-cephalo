<?php
/**
 * Poli Gigi & Mulut — Cephalo AI Hybrid API Bridge
 * 
 * Supports both local Python (Flask port 5000) and Cloud fallback (Roboflow).
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../config/database.php';

startSession();
requireRole(['admin', 'dokter']);

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "ID Analisis tidak diberikan"]);
    exit();
}

$id_analisis = (int)$_GET['id'];
$conf = isset($_GET['conf']) ? (int)$_GET['conf'] : 30; // Bawaan slider
$overlap = isset($_GET['overlap']) ? (int)$_GET['overlap'] : 50;

$pdo = getDBConnection();

// 1. Ambil nama file dari DB
$stmt = $pdo->prepare("SELECT foto_rontgen FROM modul_11_sefalometri WHERE id_analisis = ?");
$stmt->execute([$id_analisis]);
$data = $stmt->fetch();

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak ditemukan di database"]);
    exit();
}

$file_name = $data['foto_rontgen'];

if (strpos($file_name, 'data:image/') === 0) {
    // Handle Base64 image by writing it temporarily to the OS temp directory (writable in serverless)
    $parts = explode(',', $file_name);
    $base64_data = isset($parts[1]) ? $parts[1] : '';
    $decoded_data = base64_decode($base64_data);
    
    $ext = 'png';
    if (preg_match('/data:image\/([a-zA-Z0-9]+);base64/', $file_name, $matches)) {
        $ext = $matches[1];
    }
    
    $file_path = sys_get_temp_dir() . '/temp_rontgen_' . $id_analisis . '.' . $ext;
    file_put_contents($file_path, $decoded_data);
} else {
    // Physical file fallback
    $file_path = __DIR__ . '/uploads/' . $file_name;
}

if (!file_exists($file_path) || filesize($file_path) === 0) {
    echo json_encode(["status" => "error", "message" => "File gambar fisik rontgen tidak dapat dimuat atau tidak ditemukan"]);
    exit();
}

// ── OPSI A: Coba hubungi Mesin AI Lokal (Python Flask di port 5000) ──
$local_api_url = "http://localhost:5000/predict";
$connection_check = @fsockopen("localhost", 5000, $errno, $errstr, 0.4); // Fast timeout check

if ($connection_check) {
    fclose($connection_check);
    
    // Persiapkan file untuk upload form-data
    $mime_type = mime_content_type($file_path);
    $cfile = new CURLFile($file_path, $mime_type, 'image');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $local_api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $cfile]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200 && $response !== false) {
        $responseData = json_decode($response, true);
        if (isset($responseData['success']) && $responseData['success'] === true && isset($responseData['landmarks'])) {
            $results = $responseData['landmarks'];
            $jsonLandmarks = json_encode($results);
            
            // Simpan hasil landmark ke DB
            $upd = $pdo->prepare("UPDATE modul_11_sefalometri SET data_landmark = ? WHERE id_analisis = ?");
            $upd->execute([$jsonLandmarks, $id_analisis]);
            
            echo json_encode([
                "status" => "success", 
                "source" => "local_python_ai", 
                "message" => "Inferensi sukses diselesaikan oleh Mesin AI CEPHMark-Net lokal!",
                "landmarks" => $results
            ]);
            exit();
        }
    }
}

// ── OPSI B: Fallback ke Superkomputer Roboflow Cloud (AS) ──
$image_data = file_get_contents($file_path);
$base64_image = base64_encode($image_data);
$api_url = "https://detect.roboflow.com/reappciona-train-2ihu8/3?api_key=85m4iA2oYXKKT63LkMM6&confidence=" . $conf . "&overlap=" . $overlap;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $base64_image);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/x-www-form-urlencoded"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code != 200 || $response === false) {
    echo json_encode([
        "status" => "error", 
        "message" => "Gagal menghubungi Mesin AI Lokal maupun Server Roboflow Cloud!", 
        "detail" => $response ? $response : $error
    ]);
    exit();
}

$responseData = json_decode($response, true);
if (isset($responseData['predictions'])) {
    
    $results = [];
    foreach ($responseData['predictions'] as $idx => $pred) {
        $results[] = [
            "id" => isset($pred['class_id']) ? $pred['class_id'] : $idx,
            "label" => isset($pred['class']) ? $pred['class'] : 'Titik ' . $idx,
            "x" => (float)$pred['x'],
            "y" => (float)$pred['y']
        ];
    }

    $jsonLandmarks = json_encode($results);
    
    $upd = $pdo->prepare("UPDATE modul_11_sefalometri SET data_landmark = ? WHERE id_analisis = ?");
    $upd->execute([$jsonLandmarks, $id_analisis]);
    
    echo json_encode([
        "status" => "success", 
        "source" => "roboflow_cloud_ai", 
        "message" => "Inferensi sukses diselesaikan oleh Cloud AI Roboflow!",
        "landmarks" => $results
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Format balasan cloud tidak dapat dikenali.", "raw" => $response]);
}
?>
