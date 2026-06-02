<?php
/**
 * SIMRS-TB — Monitoring Kepatuhan
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';

requireLogin();
requireRole(['admin', 'dokter']);
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Monitoring Kepatuhan';
$activePage = 'monitoring';

// ── Dummy: Kepatuhan Pasien ──
$patients = [
    ['nama' => 'Ahmad Fauzi',   'no_rm' => 'RM-2026-0142', 'fase' => 'Intensif', 'kepatuhan' => 92,  'hari_patuh' => 55, 'total_hari' => 60, 'risiko' => 'Rendah',  'streak' => 14, 'heatmap' => [1,1,1,1,0,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1]],
    ['nama' => 'Siti Aminah',   'no_rm' => 'RM-2026-0198', 'fase' => 'Intensif', 'kepatuhan' => 85,  'hari_patuh' => 34, 'total_hari' => 40, 'risiko' => 'Sedang',  'streak' => 5,  'heatmap' => [1,1,0,1,1,1,0,1,1,1,1,0,0,1,1,1,1,1,0,1,1,1,1,1,0,1,1,1,1,1]],
    ['nama' => 'Budi Santoso',  'no_rm' => 'RM-2026-0076', 'fase' => 'Lanjutan', 'kepatuhan' => 62,  'hari_patuh' => 93, 'total_hari' => 150,'risiko' => 'Tinggi',  'streak' => 0,  'heatmap' => [1,1,0,0,1,0,1,1,0,0,1,1,1,0,0,0,1,1,0,1,0,0,1,0,0,1,1,0,0,0]],
    ['nama' => 'Dewi Lestari',  'no_rm' => 'RM-2026-0213', 'fase' => 'Intensif', 'kepatuhan' => 78,  'hari_patuh' => 25, 'total_hari' => 32, 'risiko' => 'Sedang',  'streak' => 3,  'heatmap' => [1,1,1,0,1,1,0,1,1,1,0,1,1,0,1,1,1,1,0,0,1,1,1,1,0,1,1,1,1,1]],
    ['nama' => 'Riko Pratama',  'no_rm' => 'RM-2026-0167', 'fase' => 'Lanjutan', 'kepatuhan' => 95,  'hari_patuh' => 115,'total_hari' => 120,'risiko' => 'Rendah',  'streak' => 22, 'heatmap' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]],
    ['nama' => 'Rina Wijaya',   'no_rm' => 'RM-2026-0089', 'fase' => 'Lanjutan', 'kepatuhan' => 45,  'hari_patuh' => 68, 'total_hari' => 150,'risiko' => 'Kritis',  'streak' => 0,  'heatmap' => [0,0,1,0,0,0,1,0,0,1,0,0,0,1,0,0,1,0,0,0,0,1,0,0,1,0,0,0,1,0]],
];

$summaryStats = [
    ['label' => 'Rata-rata Kepatuhan', 'value' => '87.3%', 'color' => 'from-teal-500 to-emerald-500'],
    ['label' => 'Pasien Patuh (>80%)',  'value' => '198',   'color' => 'from-emerald-500 to-green-500'],
    ['label' => 'Risiko Sedang',        'value' => '43',    'color' => 'from-amber-500 to-orange-500'],
    ['label' => 'Risiko Drop-out',      'value' => '7',     'color' => 'from-rose-500 to-red-500'],
];
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] bg-gray-50 dark:bg-slate-950 transition-colors">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Portal Hub</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="dashboard.php" class="hover:text-teal-600 transition-colors">SIMRS-TB (Poli Paru)</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 dark:text-slate-350 font-medium">Monitoring Kepatuhan</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Monitoring Kepatuhan</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Pantau kepatuhan minum OAT harian dan deteksi dini risiko drop-out berobat</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ($summaryStats as $s): ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
            <div class="w-10 h-10 bg-gradient-to-br <?= $s['color'] ?> rounded-xl flex items-center justify-center mb-3 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800 dark:text-white"><?= $s['value'] ?></p>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5"><?= $s['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Patient Compliance Cards -->
    <div class="space-y-4">
        <?php foreach ($patients as $p): 
            $riskColor = match($p['risiko']) {
                'Kritis' => ['bg' => 'bg-red-50 border-red-200 dark:bg-red-950/20 dark:border-red-900/30', 'text' => 'text-red-700 dark:text-red-400', 'badge' => 'error'],
                'Tinggi' => ['bg' => 'bg-amber-50 border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-400', 'badge' => 'warning'],
                'Sedang' => ['bg' => 'bg-blue-50 border-blue-200 dark:bg-blue-950/20 dark:border-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-400', 'badge' => 'info'],
                default  => ['bg' => 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-450', 'badge' => 'success'],
            };
        ?>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
            <div class="p-5">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <!-- Patient Info -->
                    <div class="flex items-center gap-3 lg:w-56 shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-emerald-500/10 dark:to-teal-500/10 rounded-lg flex items-center justify-center">
                            <span class="text-teal-700 dark:text-emerald-400 text-xs font-bold"><?= strtoupper(substr($p['nama'], 0, 2)) ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white"><?= $p['nama'] ?></p>
                            <p class="text-xs text-gray-450 dark:text-slate-450"><?= $p['no_rm'] ?> &bull; <?= $p['fase'] ?></p>
                        </div>
                    </div>

                    <!-- Progress Circle -->
                    <div class="flex items-center gap-4 lg:w-48 shrink-0">
                        <div class="relative w-14 h-14">
                            <svg class="w-14 h-14 transform -rotate-90" viewBox="0 0 56 56">
                                <circle cx="28" cy="28" r="24" fill="none" stroke="#e5e7eb" stroke-width="4" class="dark:stroke-slate-800"/>
                                <circle cx="28" cy="28" r="24" fill="none" stroke="<?= $p['kepatuhan'] >= 80 ? '#10b981' : ($p['kepatuhan'] >= 60 ? '#fbbf24' : '#f87171') ?>" 
                                        stroke-width="4" stroke-linecap="round"
                                        stroke-dasharray="<?= 2 * 3.14159 * 24 ?>" 
                                        stroke-dashoffset="<?= 2 * 3.14159 * 24 * (1 - $p['kepatuhan'] / 100) ?>"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs font-bold text-gray-800 dark:text-white"><?= $p['kepatuhan'] ?>%</span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Tingkat Kepatuhan</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-slate-200"><?= $p['hari_patuh'] ?>/<?= $p['total_hari'] ?> hari</p>
                            <p class="text-xs text-gray-450">Streak: <span class="font-semibold <?= $p['streak'] > 0 ? 'text-emerald-500' : 'text-red-500' ?>"><?= $p['streak'] ?> hari</span></p>
                        </div>
                    </div>

                    <!-- Heat Map (30 days) -->
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-gray-400 mb-1.5 font-bold tracking-widest uppercase">KALENDER KEPATUHAN 30 HARI</p>
                        <div class="flex flex-wrap gap-[3px]">
                            <?php foreach ($p['heatmap'] as $day => $val): ?>
                            <div class="w-[14px] h-[14px] rounded-sm <?= $val ? 'bg-emerald-450 hover:bg-emerald-500' : 'bg-red-300 dark:bg-red-500/20 hover:bg-red-400' ?> transition-colors cursor-pointer" 
                                 title="Hari ke-<?= $day + 1 ?>: <?= $val ? 'Patuh' : 'Absen' ?>"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex items-center gap-3 mt-1.5 text-[10px] text-gray-405">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-emerald-400 rounded-sm"></span>Patuh Minum Obat</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-red-300 rounded-sm"></span>Absen / Tidak Melapor</span>
                        </div>
                    </div>

                    <!-- Risk Badge -->
                    <div class="shrink-0">
                        <div class="px-3 py-2 rounded-xl border <?= $riskColor['bg'] ?> text-center">
                            <p class="text-[9px] text-gray-500 mb-0.5 uppercase tracking-widest font-semibold">RISIKO DO</p>
                            <p class="text-sm font-extrabold <?= $riskColor['text'] ?>"><?= $p['risiko'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Legend -->
    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-3">📌 Indikator Klasifikasi Risiko Drop-out (DO)</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="flex items-center gap-2 text-xs">
                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                <span class="text-gray-600 dark:text-slate-400"><strong class="text-gray-800 dark:text-slate-200">Rendah</strong> — Kepatuhan ≥ 80%</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                <span class="text-gray-600 dark:text-slate-400"><strong class="text-gray-800 dark:text-slate-200">Sedang</strong> — Kepatuhan 65-79%</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                <span class="text-gray-600 dark:text-slate-400"><strong class="text-gray-800 dark:text-slate-200">Tinggi</strong> — Kepatuhan 50-64%</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <span class="text-gray-600 dark:text-slate-400"><strong class="text-gray-800 dark:text-slate-200">Kritis</strong> — Kepatuhan &lt; 50%</span>
            </div>
        </div>
    </div>

</div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
