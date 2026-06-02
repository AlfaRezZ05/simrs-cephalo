<?php
/**
 * SIMRS-TB — Cetak Ketersediaan Obat (PDF)
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['admin', 'dokter', 'farmasi']);

$db = getDBConnection();
$medications = [];
try {
    $stmt = $db->query("SELECT * FROM tb_medications ORDER BY kode ASC");
    $medications = $stmt->fetchAll();
} catch (PDOException $e) {
    // fallback empty
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_Ketersediaan_Obat_<?= date('Y-m-d') ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 40px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .logo-area h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #0f766e;
            letter-spacing: -0.025em;
        }
        .logo-area p {
            margin: 4px 0 0 0;
            font-size: 12px;
            color: #64748b;
        }
        .meta-area {
            text-align: right;
        }
        .meta-area p {
            margin: 2px 0;
            font-size: 11px;
            color: #64748b;
        }
        .meta-area strong {
            color: #0f172a;
        }
        .title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            padding: 12px 10px;
            text-align: left;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc/50;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .font-mono {
            font-family: monospace;
            font-size: 12px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer-sig {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }
        .sig-space {
            height: 70px;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            margin-top: 5px;
            padding-top: 5px;
            font-weight: 600;
            color: #0f172a;
        }
        @media print {
            body {
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
        /* Top Print Alert bar */
        .print-actions {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .print-btn {
            background-color: #0f766e;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .print-btn:hover {
            background-color: #0d9488;
        }
    </style>
</head>
<body>

    <!-- Print Actions Bar (hidden during printing) -->
    <div class="print-actions no-print">
        <span>Klik tombol untuk mencetak laporan atau menyimpannya sebagai file PDF.</span>
        <button onclick="window.print()" class="print-btn">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="header">
        <div class="logo-area">
            <h1>SIMRS CEPHALO</h1>
            <p>Unit Pelayanan Kesehatan Poli Paru & OAT</p>
        </div>
        <div class="meta-area">
            <p>Tanggal Cetak: <strong><?= date('d M Y H:i') ?></strong></p>
            <p>Petugas: <strong><?= htmlspecialchars($user['name'] ?? 'Apoteker Farmasi') ?></strong></p>
        </div>
    </div>

    <div class="title">Laporan Ketersediaan & Stok Obat</div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Kode Obat</th>
                <th style="width: 35%;">Nama Obat</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 12%;" class="text-right">Stok Sisa</th>
                <th style="width: 10%;" class="text-right">Min. Alert</th>
                <th style="width: 13%;">Kadaluarsa</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medications as $m): 
                $isAlert = (int)$m['stok'] < (int)$m['min_stok'];
            ?>
                <tr>
                    <td class="font-mono"><?= htmlspecialchars($m['kode']) ?></td>
                    <td style="font-weight: 500;"><?= htmlspecialchars($m['nama']) ?></td>
                    <td><?= htmlspecialchars($m['kategori']) ?></td>
                    <td class="text-right" style="font-weight: bold; <?= $isAlert ? 'color: #b91c1c;' : '' ?>">
                        <?= number_format($m['stok']) ?>
                    </td>
                    <td class="text-right"><?= (int)$m['min_stok'] ?></td>
                    <td><?= date('d M Y', strtotime($m['kadaluarsa'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-box">
            <p>Petugas Farmasi,</p>
            <div class="sig-space"></div>
            <div class="sig-line"><?= htmlspecialchars($user['name'] ?? 'Apoteker') ?></div>
            <p style="margin: 2px 0 0 0; font-size: 10px; color: #64748b;">NIP. 19920822 202606 1 002</p>
        </div>
    </div>

    <script>
        // Trigger print dialog automatically when loaded
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
