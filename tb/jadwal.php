<?php
/**
 * SIMRS-TB — Jadwal Kontrol
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';

requireLogin();
requireRole(['admin', 'dokter']);
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Jadwal Kontrol';
$activePage = 'jadwal';

// ── Dummy: Jadwal ──
$jadwalHariIni = [
    ['waktu' => '08:00', 'pasien' => 'Rina Wijaya',       'jenis' => 'Kontrol Rutin',   'dokter' => 'dr. Rina Susanti',  'status' => 'Selesai'],
    ['waktu' => '09:30', 'pasien' => 'Ahmad Fauzi',       'jenis' => 'Evaluasi Fase',   'dokter' => 'dr. Rina Susanti',  'status' => 'Selesai'],
    ['waktu' => '10:00', 'pasien' => 'Dewi Lestari',      'jenis' => 'Pemeriksaan Lab',  'dokter' => 'dr. Aditya Putra', 'status' => 'Terjadwal'],
    ['waktu' => '11:00', 'pasien' => 'Hendra Gunawan',    'jenis' => 'Kontrol Rutin',   'dokter' => 'dr. Hendra Wijaya', 'status' => 'Terjadwal'],
    ['waktu' => '13:30', 'pasien' => 'Maya Sari',         'jenis' => 'Konsultasi',      'dokter' => 'dr. Rina Susanti',  'status' => 'Terjadwal'],
    ['waktu' => '14:00', 'pasien' => 'Budi Santoso',      'jenis' => 'Rontgen',         'dokter' => 'dr. Aditya Putra',  'status' => 'Terjadwal'],
    ['waktu' => '15:30', 'pasien' => 'Siti Aminah',       'jenis' => 'Kontrol Rutin',   'dokter' => 'dr. Rina Susanti',  'status' => 'Terjadwal'],
];

$jadwalMendatang = [
    ['tanggal' => '2026-05-14', 'pasien' => 'Riko Pratama',   'jenis' => 'Kontrol Rutin', 'dokter' => 'dr. Rina Susanti'],
    ['tanggal' => '2026-05-14', 'pasien' => 'Lina Marlina',   'jenis' => 'Pemeriksaan Lab','dokter' => 'dr. Hendra Wijaya'],
    ['tanggal' => '2026-05-15', 'pasien' => 'Agus Supriyadi', 'jenis' => 'Evaluasi Fase', 'dokter' => 'dr. Aditya Putra'],
    ['tanggal' => '2026-05-16', 'pasien' => 'Fitriani',       'jenis' => 'Rontgen',       'dokter' => 'dr. Hendra Wijaya'],
];

// ── Kalender bulan ini ──
$currentMonth = date('n');
$currentYear = date('Y');
$daysInMonth = date('t');
$firstDay = date('N', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
$today = date('j');

// Jadwal per tanggal (dummy)
$jadwalPerTanggal = [3 => 2, 5 => 1, 8 => 3, 10 => 1, 13 => 7, 14 => 2, 15 => 1, 16 => 1, 18 => 2, 22 => 4, 25 => 3, 28 => 1];
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
        <span class="text-gray-700 dark:text-slate-350 font-medium">Jadwal Kontrol</span>
    </nav>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Jadwal Kontrol</h1>
            <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Kelola agenda kunjungan pemeriksaan klinis pasien TB</p>
        </div>
        <?= component_button('+ Tambah Jadwal', [
            'variant' => 'primary',
            'class' => '!bg-emerald-600 hover:!bg-emerald-700 !shadow-emerald-500/20 border border-emerald-500/10',
            'onclick' => "openModal('addScheduleModal')"
        ]) ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Kalender -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-850 flex items-center justify-between bg-gray-55 dark:bg-slate-950">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white"><?= date('F Y') ?></h3>
                <div class="flex items-center gap-2">
                    <button class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="px-3 py-1 rounded-lg bg-teal-50 dark:bg-emerald-500/15 text-teal-700 dark:text-emerald-450 text-xs font-semibold">Hari Ini</button>
                    <button class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
            <div class="p-4">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $day): ?>
                    <div class="text-center text-xs font-semibold text-gray-400 py-2"><?= $day ?></div>
                    <?php endforeach; ?>
                </div>
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <?php 
                    for ($i = 1; $i < $firstDay; $i++):
                    ?>
                    <div class="h-16 rounded-lg"></div>
                    <?php endfor; ?>
                    
                    <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                        $isToday = ($d == $today);
                        $hasSchedule = isset($jadwalPerTanggal[$d]);
                        $scheduleCount = $jadwalPerTanggal[$d] ?? 0;
                    ?>
                    <div class="h-16 rounded-lg p-1.5 text-sm cursor-pointer hover:bg-teal-50 dark:hover:bg-slate-800/20 transition-colors relative <?= $isToday ? 'bg-teal-50/50 dark:bg-emerald-500/10 ring-2 ring-teal-400 dark:ring-emerald-500/50' : 'hover:bg-gray-50' ?>">
                        <span class="text-xs font-semibold <?= $isToday ? 'text-teal-700 dark:text-emerald-400 font-bold' : 'text-gray-600 dark:text-slate-450' ?>"><?= $d ?></span>
                        <?php if ($hasSchedule): ?>
                        <div class="mt-1">
                            <div class="text-[9px] font-bold px-1 py-0.5 rounded bg-teal-100 dark:bg-emerald-500/15 text-teal-750 dark:text-emerald-400 truncate"><?= $scheduleCount ?> jadwal</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="space-y-5">
            <!-- Hari ini -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-850">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Jadwal Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5"><?= date('l, d F Y') ?></p>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-slate-850/40 max-h-96 overflow-y-auto">
                    <?php foreach ($jadwalHariIni as $j): 
                        $sClass = match($j['status']) {
                            'Selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-250 dark:bg-emerald-500/10 dark:text-emerald-450 dark:border-emerald-900/30',
                            'Tidak Hadir' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-900/30',
                            default => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-900/30'
                        };
                    ?>
                    <div class="px-5 py-3.5 hover:bg-gray-50/50 dark:hover:bg-slate-800/10 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="text-center shrink-0 w-12">
                                <p class="text-sm font-bold text-teal-600 dark:text-emerald-450"><?= $j['waktu'] ?></p>
                            </div>
                            <div class="w-px h-10 bg-teal-200 dark:bg-slate-700"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-slate-100"><?= $j['pasien'] ?></p>
                                <p class="text-xs text-gray-400 dark:text-slate-450 truncate"><?= $j['jenis'] ?> &bull; <?= $j['dokter'] ?></p>
                            </div>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border <?= $sClass ?>"><?= $j['status'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Mendatang -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-850">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Jadwal Esok & Mendatang</h3>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-slate-850/40">
                    <?php foreach ($jadwalMendatang as $jm): ?>
                    <div class="px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-slate-800/10 transition-colors">
                        <p class="text-[10px] font-bold text-emerald-500 mb-0.5"><?= date('D, d M Y', strtotime($jm['tanggal'])) ?></p>
                        <p class="text-sm font-semibold text-gray-850 dark:text-slate-200"><?= $jm['pasien'] ?></p>
                        <p class="text-xs text-gray-450 dark:text-slate-450"><?= $jm['jenis'] ?> &bull; <?= $jm['dokter'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Add Schedule Modal -->
<?= component_modal('addScheduleModal', [
    'title' => 'Tambah Jadwal Kontrol',
    'content' => '
    <div class="space-y-4">
        ' . component_input('jadwal_pasien', ['label' => 'Nama Pasien', 'placeholder' => 'Cari nama pasien...', 'required' => true, 'class' => 'dark:bg-slate-950']) . '
        <div class="grid grid-cols-2 gap-4">
            ' . component_input('jadwal_tanggal', ['label' => 'Tanggal', 'type' => 'date', 'required' => true, 'class' => 'dark:bg-slate-950']) . '
            ' . component_input('jadwal_waktu', ['label' => 'Waktu', 'type' => 'time', 'required' => true, 'class' => 'dark:bg-slate-950']) . '
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-350 mb-1.5 uppercase tracking-widest text-xs">Jenis Pemeriksaan</label>
            <select class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-950 border border-gray-200 dark:border-slate-850 rounded-xl text-sm text-gray-800 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <option>Kontrol Rutin</option>
                <option>Pemeriksaan Lab</option>
                <option>Rontgen</option>
                <option>Evaluasi Fase</option>
                <option>Konsultasi</option>
            </select>
        </div>
        ' . component_input('jadwal_catatan', ['label' => 'Catatan Tambahan', 'type' => 'textarea', 'placeholder' => 'Tulis catatan janji kontrol...', 'class' => 'dark:bg-slate-950']) . '
    </div>',
    'footer' => component_button('Batal', ['variant' => 'outline', 'onclick' => "closeModal('addScheduleModal')"])
        . ' ' . component_button('Simpan Jadwal', ['variant' => 'primary', 'class' => '!bg-emerald-600 hover:!bg-emerald-700', 'onclick' => "closeModal('addScheduleModal')"])
]) ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
