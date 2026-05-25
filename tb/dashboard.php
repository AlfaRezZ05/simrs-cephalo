<?php
/**
 * SIMRS-TB — Dashboard
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Dashboard';
$activePage = 'dashboard';

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

// ── Dummy Data & Live Indicators ──
$stats = [
    ['label' => 'Pasien Aktif',       'value' => (string)$active_count,   'trend' => '+12 bulan ini',  'trendDir' => 'up',   'color' => 'from-teal-500 to-emerald-500',  'iconPath' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
    ['label' => 'Skrining Hari Ini',   'value' => '18',    'trend' => '+5 vs kemarin',  'trendDir' => 'up',   'color' => 'from-blue-500 to-cyan-500',     'iconPath' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
    ['label' => 'Tingkat Kepatuhan',   'value' => '87.3%', 'trend' => '+2.1% minggu ini','trendDir' => 'up',  'color' => 'from-violet-500 to-purple-500', 'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label' => 'Risiko Drop-out',     'value' => (string)$dropout_count,     'trend' => '-2 vs bulan lalu','trendDir' => 'down','color' => 'from-rose-500 to-red-500',      'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
];

$alertPasien = [
    ['nama' => 'Ahmad Fauzi',    'no_rm' => 'RM-2026-0142', 'masalah' => 'Tidak hadir kontrol 2x berturut',  'prioritas' => 'Tinggi',  'fase' => 'Intensif'],
    ['nama' => 'Siti Aminah',    'no_rm' => 'RM-2026-0198', 'masalah' => 'Efek samping obat: mual persisten','prioritas' => 'Sedang',  'fase' => 'Intensif'],
    ['nama' => 'Budi Santoso',   'no_rm' => 'RM-2026-0076', 'masalah' => 'Belum mengambil obat 5 hari',      'prioritas' => 'Tinggi',  'fase' => 'Lanjutan'],
    ['nama' => 'Dewi Lestari',   'no_rm' => 'RM-2026-0213', 'masalah' => 'Hasil BTA bulan 2 masih positif',  'prioritas' => 'Kritis',  'fase' => 'Intensif'],
    ['nama' => 'Riko Pratama',   'no_rm' => 'RM-2026-0167', 'masalah' => 'Terlambat kontrol bulanan',        'prioritas' => 'Rendah',  'fase' => 'Lanjutan'],
];

$jadwalHariIni = [
    ['waktu' => '08:00', 'nama' => 'Rina Wijaya',    'jenis' => 'Kontrol Rutin'],
    ['waktu' => '09:30', 'nama' => 'Ahmad Fauzi',    'jenis' => 'Evaluasi Fase'],
    ['waktu' => '10:00', 'nama' => 'Dewi Lestari',   'jenis' => 'Pemeriksaan Lab'],
    ['waktu' => '11:00', 'nama' => 'Hendra Gunawan', 'jenis' => 'Kontrol Rutin'],
    ['waktu' => '13:30', 'nama' => 'Maya Sari',      'jenis' => 'Konsultasi'],
];
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
            <h1 class="text-2xl font-bold text-white">Dashboard Poli Paru (SIMRS-TB)</h1>
            <p class="text-slate-400 text-sm mt-1">Ringkasan data klinis pasien TB hari ini — <?= date('d F Y') ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?= component_button('Pasien Baru', [
                'variant' => 'primary',
                'href' => 'rekam-medis.php',
                'class' => '!bg-emerald-600 hover:!bg-emerald-700 !shadow-emerald-500/20 border border-emerald-500/10',
                'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            ]) ?>
            <?= component_button('Skrining Suara', [
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
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-teal-500 rounded-full"></span><span class="text-slate-400">Intensif (98)</span></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-full"></span><span class="text-slate-400">Lanjutan (120)</span></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-amber-400 rounded-full"></span><span class="text-slate-400">Belum Mulai (15)</span></div>
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 bg-slate-500 rounded-full"></span><span class="text-slate-400">Selesai (15)</span></div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Alert + Jadwal -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
        <!-- Alert Pasien -->
        <div class="lg:col-span-3 rounded-2xl overflow-hidden" style="background: var(--glass-bg); border: 1px solid var(--glass-border); backdrop-filter: blur(16px);">
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-white flex items-center gap-2"><svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>Alert Pasien Aktif</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Pasien yang membutuhkan atensi medis klinis harian</p>
                </div>
                <?= component_badge(count($alertPasien) . ' pasien', 'warning') ?>
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
                    <h3 class="text-base font-semibold text-white flex items-center gap-2"><svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Jadwal Kontrol Hari Ini</h3>
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
                        <p class="text-sm font-medium text-slate-100 truncate"><?= $j['nama'] ?></p>
                        <p class="text-xs text-slate-500"><?= $j['jenis'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div>
</main>

<script>
// ── Tren Kasus TB Chart ──
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar'],
        datasets: [{
            label: 'Kasus Baru',
            data: [22, 18, 25, 30, 28, 20, 26, 32, 24, 19, 22, 27],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.06)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#10b981'
        },{
            label: 'Sembuh',
            data: [15, 12, 18, 20, 22, 16, 19, 24, 21, 17, 20, 23],
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
            data: [98, 120, 15, 15],
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

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
