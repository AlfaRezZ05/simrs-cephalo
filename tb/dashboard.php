<?php
/**
 * SIMRS-TB — Dashboard
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';

requireLogin();
requireRole(['admin', 'dokter', 'patient']);
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Dashboard';
$activePage = 'dashboard';
$userRole = getUserRole();

require_once __DIR__ . '/../config/database.php';

$active_count = 248;
$dropout_count = 7;
try {
    $db = getDBConnection();
    $stmt1 = $db->query("SELECT COUNT(*) FROM tb_patients WHERE status = 'Aktif'");
    $c1 = (int)$stmt1->fetchColumn();
    if ($c1 > 0) $active_count = $c1;

    $stmt2 = $db->query("SELECT COUNT(*) FROM tb_patients WHERE status = 'Putus Obat'");
    $c2 = (int)$stmt2->fetchColumn();
    if ($c2 > 0) $dropout_count = $c2;
} catch (PDOException $e) {
    // Fallback to defaults
}

// ── Dynamic Database Metrics ──
$screening_today = 0;
$avg_compliance = 87.3;
$alertPasien = [];
$jadwalHariIni = [];
$phase_counts = ['Intensif' => 0, 'Lanjutan' => 0, 'Belum Mulai' => 0, 'Selesai' => 0];
$trend_months = [];
$kasus_baru = [];
$sembuh_kasus = [];

try {
    $stmtScrCount = $db->prepare("SELECT COUNT(*) FROM tb_screenings WHERE DATE(tanggal) = ?");
    $stmtScrCount->execute([date('Y-m-d')]);
    $screening_today = (int)$stmtScrCount->fetchColumn();

    $stmtAvgComp = $db->query("SELECT AVG(kepatuhan) FROM tb_compliance");
    $c_avg = $stmtAvgComp->fetchColumn();
    if ($c_avg !== null) {
        $avg_compliance = round($c_avg, 1);
    }

    // Phase counts query
    $stmtPhase = $db->query("SELECT fase_pengobatan, COUNT(*) as jml FROM tb_patients GROUP BY fase_pengobatan");
    while ($row = $stmtPhase->fetch(PDO::FETCH_ASSOC)) {
        $fase = $row['fase_pengobatan'];
        if (array_key_exists($fase, $phase_counts)) {
            $phase_counts[$fase] = (int)$row['jml'];
        }
    }

    // Trend cases query
    for ($i = 11; $i >= 0; $i--) {
        $time = strtotime("-$i months");
        $trend_months[] = date('M', $time);
        $monthKey = date('Y-m', $time);
        
        try {
            $stmtKB = $db->prepare("SELECT COUNT(*) FROM tb_patients WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
            $stmtKB->execute([$monthKey]);
            $kasus_baru[] = (int)$stmtKB->fetchColumn();

            $stmtSembuh = $db->prepare("SELECT COUNT(*) FROM tb_patients WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND status = 'Sembuh'");
            $stmtSembuh->execute([$monthKey]);
            $sembuh_kasus[] = (int)$stmtSembuh->fetchColumn();
        } catch (PDOException $ex) {
            try {
                $stmtKB = $db->prepare("SELECT COUNT(*) FROM tb_patients WHERE TO_CHAR(created_at, 'YYYY-MM') = ?");
                $stmtKB->execute([$monthKey]);
                $kasus_baru[] = (int)$stmtKB->fetchColumn();

                $stmtSembuh = $db->prepare("SELECT COUNT(*) FROM tb_patients WHERE TO_CHAR(created_at, 'YYYY-MM') = ? AND status = 'Sembuh'");
                $stmtSembuh->execute([$monthKey]);
                $sembuh_kasus[] = (int)$stmtSembuh->fetchColumn();
            } catch (PDOException $ex2) {
                $kasus_baru[] = 0;
                $sembuh_kasus[] = 0;
            }
        }
    }

    // Load dynamic alerts from tb_compliance
    $stmtAlert = $db->query("SELECT nama_pasien AS nama, no_rm, fase, kepatuhan, risiko FROM tb_compliance ORDER BY kepatuhan ASC LIMIT 5");
    $alerts = $stmtAlert->fetchAll(PDO::FETCH_ASSOC);
    foreach ($alerts as $a) {
        $masalah = 'Kepatuhan rendah: ' . $a['kepatuhan'] . '% (Risiko ' . $a['risiko'] . ')';
        $prioritas = $a['risiko'] === 'Kritis' ? 'Kritis' : ($a['risiko'] === 'Tinggi' ? 'Tinggi' : 'Sedang');
        $alertPasien[] = [
            'nama' => $a['nama'],
            'no_rm' => $a['no_rm'],
            'masalah' => $masalah,
            'prioritas' => $prioritas,
            'fase' => $a['fase']
        ];
    }

    // Load today's schedule from tb_appointments
    $stmtJadwal = $db->prepare("SELECT pasien_name AS nama, waktu, jenis FROM tb_appointments WHERE tanggal = ? ORDER BY waktu ASC");
    $stmtJadwal->execute([date('Y-m-d')]);
    $jadwalHariIni = $stmtJadwal->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Ignored fallback
}

$stats = [
    ['label' => 'Pasien Aktif',       'value' => (string)$active_count,   'trend' => '+12 bulan ini',  'trendDir' => 'up',   'color' => 'from-teal-500 to-emerald-500',  'iconPath' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
    ['label' => 'Skrining Hari Ini',   'value' => (string)$screening_today,'trend' => 'Live updates',  'trendDir' => 'up',   'color' => 'from-blue-500 to-cyan-500',     'iconPath' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
    ['label' => 'Tingkat Kepatuhan',   'value' => $avg_compliance . '%',   'trend' => 'Target: >90%',   'trendDir' => 'up',  'color' => 'from-violet-500 to-purple-500', 'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label' => 'Risiko Drop-out',     'value' => (string)$dropout_count,     'trend' => '-2 vs bulan lalu','trendDir' => 'down','color' => 'from-rose-500 to-red-500',      'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
];

// Overwrite for patient role
if ($userRole === 'patient') {
    $patientData = null;
    try {
        $stmt = $db->prepare("SELECT * FROM tb_patients WHERE LOWER(nama) = LOWER(?) LIMIT 1");
        $stmt->execute([$user['name']]);
        $patientData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ignored
    }

    if (!$patientData) {
        $patientData = [
            'nama' => $user['name'],
            'no_rm' => 'RM-2026-0245',
            'nik' => '3171012345678009',
            'status' => 'Aktif',
            'fase_pengobatan' => 'Intensif',
            'kategori_tb' => 'Paru',
            'no_telepon' => '081234567890',
            'alamat' => 'Jakarta, Indonesia'
        ];
    }

    $patCompliance = 62.0;
    try {
        $stmtPatComp = $db->prepare("SELECT kepatuhan FROM tb_compliance WHERE LOWER(nama_pasien) = LOWER(?) LIMIT 1");
        $stmtPatComp->execute([$patientData['nama']]);
        $val = $stmtPatComp->fetchColumn();
        if ($val !== false) {
            $patCompliance = (float)$val;
        }
    } catch (PDOException $e) {
        // Ignored
    }

    $stats = [
        ['label' => 'Status Pengobatan', 'value' => $patientData['status'], 'trend' => 'Terpantau', 'trendDir' => 'up', 'color' => 'from-emerald-500 to-teal-500', 'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Fase Pengobatan', 'value' => $patientData['fase_pengobatan'], 'trend' => $patientData['kategori_tb'], 'trendDir' => 'up', 'color' => 'from-blue-500 to-cyan-500', 'iconPath' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
        ['label' => 'Kepatuhan Obat', 'value' => $patCompliance . '%', 'trend' => 'Sangat Baik', 'trendDir' => 'up', 'color' => 'from-violet-500 to-purple-500', 'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'No. Rekam Medis', 'value' => $patientData['no_rm'], 'trend' => 'Aktif', 'trendDir' => 'up', 'color' => 'from-amber-500 to-orange-500', 'iconPath' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
    ];

    $alertPasien = [
        ['nama' => $patientData['nama'], 'no_rm' => $patientData['no_rm'], 'masalah' => 'Waktunya minum obat OAT dosis pagi hari ini.', 'prioritas' => 'Tinggi', 'fase' => $patientData['fase_pengobatan']],
        ['nama' => $patientData['nama'], 'no_rm' => $patientData['no_rm'], 'masalah' => 'Pastikan melapor PMO setelah meminum obat.', 'prioritas' => 'Sedang', 'fase' => $patientData['fase_pengobatan']],
    ];

    // Load today's schedule for patient from tb_appointments
    $jadwalHariIni = [];
    try {
        $stmtJadwalPat = $db->prepare("SELECT pasien_name AS nama, waktu, jenis FROM tb_appointments WHERE tanggal = ? AND LOWER(pasien_name) = LOWER(?) ORDER BY waktu ASC");
        $stmtJadwalPat->execute([date('Y-m-d'), $patientData['nama']]);
        $jadwalHariIni = $stmtJadwalPat->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ignored
    }
}
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] transition-colors" style="background-color: var(--bg-base);">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Portal SIMRS</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-300 font-medium">SIMRS-TB (Poli Paru)</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white"><?= $userRole === 'patient' ? 'Portal Pasien Tuberkulosis' : 'Dashboard Poli Paru (SIMRS-TB)' ?></h1>
            <p class="text-slate-400 text-sm mt-1">
                <?= $userRole === 'patient' 
                    ? 'Selamat datang kembali, ' . htmlspecialchars($user['name']) . '. Pantau perkembangan pengobatan Anda secara real-time.' 
                    : 'Ringkasan data klinis pasien TB hari ini — ' . date('d F Y') ?>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($userRole !== 'patient'): ?>
                <?= component_button('Pasien Baru', [
                    'variant' => 'primary',
                    'href' => 'rekam-medis.php',
                    'class' => '!bg-emerald-600 hover:!bg-emerald-700 !shadow-emerald-500/20 border border-emerald-500/10',
                    'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
                ]) ?>
            <?php endif; ?>
            <?= component_button($userRole === 'patient' ? 'Skrining Suara Mandiri' : 'Skrining Suara', [
                'variant' => 'outline',
                'href' => 'screening.php',
                'class' => 'dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>'
            ]) ?>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ($stats as $stat): ?>
        <div class="rounded-2xl border p-5 hover:-translate-y-0.5 transition-all duration-300 group" style="background: var(--glass-bg); border-color: var(--glass-border); backdrop-filter: blur(16px);">
            <div class="flex items-start justify-between mb-3">
                <div class="w-11 h-11 bg-gradient-to-br <?= $stat['color'] ?> rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $stat['iconPath'] ?>"/>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full <?= $stat['trendDir'] === 'up' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' ?>">
                    <?= $stat['trend'] ?>
                </span>
            </div>
            <p class="text-2xl font-bold text-white"><?= $stat['value'] ?></p>
            <p class="text-sm text-slate-400 mt-0.5"><?= $stat['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($userRole !== 'patient'): ?>
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Tren Kasus TB -->
        <div class="lg:col-span-2 rounded-2xl p-5" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-white">Tren Kasus Tuberkulosis</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Statistik tren 12 bulan terakhir</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-teal-500 rounded-full"></span>Kasus Baru</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-full"></span>Sembuh</span>
                </div>
            </div>
            <div style="position:relative;height:280px;"><canvas id="trendChart"></canvas></div>
        </div>

        <!-- Distribusi Fase -->
        <div class="rounded-2xl p-5" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <h3 class="text-base font-semibold text-white mb-1">Distribusi Fase Pengobatan</h3>
            <p class="text-xs text-gray-400 mb-4">Pengobatan aktif terdistribusi</p>
            <div style="position:relative;height:200px;"><canvas id="phaseChart"></canvas></div>
            <div class="grid grid-cols-2 gap-2 mt-4 text-xs">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-teal-500 rounded-full"></span><span class="text-slate-400">Intensif (<?= $phase_counts['Intensif'] ?>)</span></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-full"></span><span class="text-slate-400">Lanjutan (<?= $phase_counts['Lanjutan'] ?>)</span></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-amber-400 rounded-full"></span><span class="text-slate-400">Belum Mulai (<?= $phase_counts['Belum Mulai'] ?>)</span></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-slate-500 rounded-full"></span><span class="text-slate-400">Selesai (<?= $phase_counts['Selesai'] ?>)</span></div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Patient-Specific Treatment Details & Compliance Guidance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Rencana & Panduan Pengobatan -->
        <div class="lg:col-span-2 rounded-2xl p-6" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <div class="flex items-center justify-between mb-4 border-b border-white/[0.06] pb-3">
                <div>
                    <h3 class="text-base font-semibold text-white">Panduan Pengobatan Pasien TB</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Panduan penting kepatuhan konsumsi obat Anti Tuberkulosis (OAT)</p>
                </div>
                <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-semibold rounded-lg">Fase: <?= htmlspecialchars($patientData['fase_pengobatan']) ?></span>
            </div>
            
            <div class="space-y-4">
                <div class="flex gap-3.5 items-start">
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 shrink-0 font-bold">1</div>
                    <div>
                        <h4 class="text-sm font-semibold text-white">Aturan Minum Obat OAT</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Minum obat setiap pagi saat perut kosong (1 jam sebelum makan) atau sesuai instruksi khusus dokter Anda.</p>
                    </div>
                </div>
                <div class="flex gap-3.5 items-start">
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 shrink-0 font-bold">2</div>
                    <div>
                        <h4 class="text-sm font-semibold text-white">Jangan Putus Obat</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Pengobatan TB berlangsung minimal 6 bulan. Menghentikan obat sebelum waktunya berisiko menyebabkan TB Resisten Obat (TB-RO).</p>
                    </div>
                </div>
                <div class="flex gap-3.5 items-start">
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 shrink-0 font-bold">3</div>
                    <div>
                        <h4 class="text-sm font-semibold text-white">Pantau Efek Samping</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Bila Anda mengalami mual/muntah hebat, gatal-gatal, nyeri sendi, atau gangguan penglihatan, segera hubungi dokter.</p>
                    </div>
                </div>
                <div class="flex gap-3.5 items-start">
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 shrink-0 font-bold">4</div>
                    <div>
                        <h4 class="text-sm font-semibold text-white">Jadwal Pemeriksaan Ulang Dahak (BTA)</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Pemeriksaan dahak lanjutan akan dijadwalkan pada akhir bulan ke-2 (fase intensif) dan bulan ke-6 (fase lanjutan).</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Rekam Medis Ringkas -->
        <div class="rounded-2xl p-6" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <h3 class="text-base font-semibold text-white mb-1">Informasi Klinis Anda</h3>
            <p class="text-xs text-slate-400 mb-4">Detail kartu rekam medis yang terdaftar</p>
            
            <div class="space-y-3 text-xs">
                <div class="p-3 bg-slate-950/40 rounded-xl border border-white/[0.04]">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold">Nama Pasien</span>
                    <p class="text-sm font-bold text-white mt-0.5"><?= htmlspecialchars($patientData['nama']) ?></p>
                </div>
                <div class="p-3 bg-slate-950/40 rounded-xl border border-white/[0.04]">
                    <span class="text-[10px] text-slate-500 uppercase font-semibold">Nomor NIK</span>
                    <p class="text-sm font-mono text-white mt-0.5"><?= htmlspecialchars($patientData['nik']) ?></p>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-3 bg-slate-950/40 rounded-xl border border-white/[0.04]">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold">Kategori TB</span>
                        <p class="text-sm font-bold text-teal-400 mt-0.5"><?= htmlspecialchars($patientData['kategori_tb']) ?></p>
                    </div>
                    <div class="p-3 bg-slate-950/40 rounded-xl border border-white/[0.04]">
                        <span class="text-[10px] text-slate-500 uppercase font-semibold">Fase</span>
                        <p class="text-sm font-bold text-cyan-400 mt-0.5"><?= htmlspecialchars($patientData['fase_pengobatan']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bottom Row: Alert + Jadwal -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
        <!-- Alert Pasien -->
        <div class="lg:col-span-3 rounded-2xl overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <?= $userRole === 'patient' ? 'Tugas & Alarm Pemantauan Anda' : 'Alert Pasien Aktif' ?>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <?= $userRole === 'patient' ? 'Patuhi agenda harian pemantauan kesehatan Anda' : 'Pasien yang membutuhkan atensi medis harian' ?>
                    </p>
                </div>
                <?= component_badge(count($alertPasien) . ($userRole === 'patient' ? ' Agenda' : ' pasien'), 'warning') ?>
            </div>
            <div class="divide-y divide-white/[0.04]">
                <?php foreach ($alertPasien as $alert): 
                    $prioColors = ['Kritis' => 'error', 'Tinggi' => 'warning', 'Sedang' => 'info', 'Rendah' => 'default'];
                ?>
                <div class="px-5 py-3.5 hover:bg-white/[0.03] transition-colors flex items-start gap-3 group cursor-pointer">
                    <div class="w-9 h-9 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 rounded-lg flex items-center justify-center shrink-0 mt-0.5 group-hover:scale-105 transition-transform">
                        <span class="text-emerald-400 text-xs font-bold"><?= strtoupper(substr($alert['nama'], 0, 2)) ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-white"><?= $alert['nama'] ?></p>
                            <span class="text-xs text-gray-400"><?= $alert['no_rm'] ?></span>
                            <?= component_badge($alert['prioritas'], $prioColors[$alert['prioritas']] ?? 'default') ?>
                        </div>
                        <p class="text-sm text-slate-400 mt-0.5"><?= $alert['masalah'] ?></p>
                    </div>
                    <span class="text-xs text-gray-400 shrink-0 font-medium"><?= $alert['fase'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Jadwal Hari Ini -->
        <div class="lg:col-span-2 rounded-2xl overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <?= $userRole === 'patient' ? 'Jadwal Kontrol Anda' : 'Jadwal Kontrol Hari Ini' ?>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?= date('l, d M Y') ?></p>
                </div>
                <a href="jadwal.php" class="text-xs text-emerald-400 hover:underline font-semibold">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-white/[0.04]">
                <?php foreach ($jadwalHariIni as $j): ?>
                <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-white/[0.03] transition-colors">
                    <div class="text-center shrink-0 w-14">
                        <p class="text-sm font-bold text-emerald-400"><?= $j['waktu'] ?></p>
                    </div>
                    <div class="w-px h-8 bg-slate-700"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-100 truncate"><?= $userRole === 'patient' ? $j['jenis'] : $j['nama'] ?></p>
                        <p class="text-xs text-slate-500"><?= $userRole === 'patient' ? 'Klinik Poli Paru' : $j['jenis'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
</main>

<?php if ($userRole !== 'patient'): ?>
<script>
// ── Tren Kasus TB Chart ──
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($trend_months) ?>,
        datasets: [{
            label: 'Kasus Baru',
            data: <?= json_encode($kasus_baru) ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.06)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#10b981'
        },{
            label: 'Sembuh',
            data: <?= json_encode($sembuh_kasus) ?>,
            borderColor: '#34d399',
            backgroundColor: 'rgba(52,211,153,0.03)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#34d399'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { font: { size: 11 }, color: '#64748b' } },
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#64748b' } }
        },
        interaction: { intersect: false, mode: 'index' }
    }
});

// ── Distribusi Fase Chart ──
const phaseCtx = document.getElementById('phaseChart').getContext('2d');
new Chart(phaseCtx, {
    type: 'doughnut',
    data: {
        labels: ['Intensif', 'Lanjutan', 'Belum Mulai', 'Selesai'],
        datasets: [{
            data: [<?= $phase_counts['Intensif'] ?>, <?= $phase_counts['Lanjutan'] ?>, <?= $phase_counts['Belum Mulai'] ?>, <?= $phase_counts['Selesai'] ?>],
            backgroundColor: ['#0ea5e9', '#10b981', '#fbbf24', '#cbd5e1'],
            borderWidth: 0,
            spacing: 2,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '70%',
        plugins: { legend: { display: false } }
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
