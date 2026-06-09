<?php
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['admin', 'dokter', 'patient']);
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Jadwal Kontrol';
$activePage = 'jadwal';
$userRole = getUserRole();

$db = getDBConnection();
$error = getFlash('error');
$success = getFlash('success');

// POST Handler for adding appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_appointment') {
        $pasien = trim($_POST['pasien_name'] ?? '');
        $tanggal = $_POST['tanggal'] ?? '';
        $waktu = $_POST['waktu'] ?? '';
        $jenis = $_POST['jenis'] ?? 'Kontrol Rutin';
        $catatan = trim($_POST['catatan'] ?? '');
        $dokter = 'dr. Rina Susanti'; // default doctor
        
        try {
            $stmt = $db->prepare("INSERT INTO tb_appointments (pasien_name, tanggal, waktu, jenis, catatan, dokter_name, status) VALUES (?, ?, ?, ?, ?, ?, 'Terjadwal')");
            $stmt->execute([$pasien, $tanggal, $waktu, $jenis, $catatan, $dokter]);
            setFlash('success', 'Jadwal kontrol berhasil disimpan.');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
        header("Location: jadwal.php");
        exit;
    }
}

// ── Calendar Month Init ──
$currentMonth = date('n');
$currentYear = date('Y');
$daysInMonth = date('t');
$firstDay = date('N', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
$today = date('j');

// Fetch schedules from DB
$jadwalHariIni = [];
$jadwalMendatang = [];
$jadwalPerTanggal = [];

try {
    // 1. Today's schedule
    if ($userRole === 'patient') {
        $stmtToday = $db->prepare("SELECT * FROM tb_appointments WHERE tanggal = ? AND LOWER(pasien_name) = LOWER(?) ORDER BY waktu ASC");
        $stmtToday->execute([date('Y-m-d'), $user['name']]);
        
        $stmtFuture = $db->prepare("SELECT * FROM tb_appointments WHERE tanggal > ? AND LOWER(pasien_name) = LOWER(?) ORDER BY tanggal ASC, waktu ASC LIMIT 10");
        $stmtFuture->execute([date('Y-m-d'), $user['name']]);
    } else {
        $stmtToday = $db->prepare("SELECT * FROM tb_appointments WHERE tanggal = ? ORDER BY waktu ASC");
        $stmtToday->execute([date('Y-m-d')]);

        $stmtFuture = $db->prepare("SELECT * FROM tb_appointments WHERE tanggal > ? ORDER BY tanggal ASC, waktu ASC LIMIT 10");
        $stmtFuture->execute([date('Y-m-d')]);
    }
    
    $jadwalHariIni = $stmtToday->fetchAll(PDO::FETCH_ASSOC);
    $jadwalMendatang = $stmtFuture->fetchAll(PDO::FETCH_ASSOC);

    // 2. Calendar counters
    $stmtCal = $db->prepare("SELECT tanggal FROM tb_appointments WHERE tanggal LIKE ?");
    $stmtCal->execute([$currentYear . '-' . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . '%']);
    while ($row = $stmtCal->fetch(PDO::FETCH_ASSOC)) {
        $day = (int)date('j', strtotime($row['tanggal']));
        if (!isset($jadwalPerTanggal[$day])) {
            $jadwalPerTanggal[$day] = 0;
        }
        $jadwalPerTanggal[$day]++;
    }
} catch (PDOException $e) {
    // Ignored fallback
}
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>
<?php require_once __DIR__ . '/_sidebar.php'; ?>

<main class="lg:ml-64 min-h-[calc(100vh-4rem)] bg-gray-50 dark:bg-slate-950 transition-colors">
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
    <?php if ($success): ?>
        <div class="mb-4 p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-450 rounded-xl text-sm font-medium"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mb-4 p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 text-rose-800 dark:text-rose-450 rounded-xl text-sm font-medium"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

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
        <?php if ($userRole !== 'patient'): ?>
        <?= component_button('+ Tambah Jadwal', [
            'variant' => 'primary',
            'class' => '!bg-emerald-600 hover:!bg-emerald-700 !shadow-emerald-500/20 border border-emerald-500/10',
            'onclick' => "openModal('addScheduleModal')"
        ]) ?>
        <?php endif; ?>
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
                                <p class="text-sm font-bold text-teal-600 dark:text-emerald-450"><?= date('H:i', strtotime($j['waktu'])) ?></p>
                            </div>
                            <div class="w-px h-10 bg-teal-200 dark:bg-slate-700"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-slate-100"><?= htmlspecialchars($j['pasien_name']) ?></p>
                                <p class="text-xs text-gray-400 dark:text-slate-450 truncate"><?= htmlspecialchars($j['jenis']) ?> &bull; <?= htmlspecialchars($j['dokter_name']) ?></p>
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
                        <p class="text-[10px] font-bold text-emerald-500 mb-0.5"><?= date('D, d M Y', strtotime($jm['tanggal'])) ?> - <?= date('H:i', strtotime($jm['waktu'])) ?></p>
                        <p class="text-sm font-semibold text-gray-850 dark:text-slate-200"><?= htmlspecialchars($jm['pasien_name']) ?></p>
                        <p class="text-xs text-gray-450 dark:text-slate-450"><?= htmlspecialchars($jm['jenis']) ?> &bull; <?= htmlspecialchars($jm['dokter_name']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- Add Schedule Modal -->
<form id="addScheduleForm" method="POST" action="jadwal.php">
    <input type="hidden" name="action" value="add_appointment">
    <?= component_modal('addScheduleModal', [
        'title' => 'Tambah Jadwal Kontrol',
        'content' => '
        <div class="space-y-4">
            ' . component_input('pasien_name', ['label' => 'Nama Pasien', 'placeholder' => 'Cari nama pasien...', 'required' => true, 'class' => 'dark:bg-slate-950']) . '
            <div class="grid grid-cols-2 gap-4">
                ' . component_input('tanggal', ['label' => 'Tanggal', 'type' => 'date', 'required' => true, 'class' => 'dark:bg-slate-950']) . '
                ' . component_input('waktu', ['label' => 'Waktu', 'type' => 'time', 'required' => true, 'class' => 'dark:bg-slate-950']) . '
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-350 mb-1.5 uppercase tracking-widest text-xs">Jenis Pemeriksaan</label>
                <select name="jenis" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-950 border border-gray-200 dark:border-slate-850 rounded-xl text-sm text-gray-800 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    <option>Kontrol Rutin</option>
                    <option>Pemeriksaan Lab</option>
                    <option>Rontgen</option>
                    <option>Evaluasi Fase</option>
                    <option>Konsultasi</option>
                </select>
            </div>
            ' . component_input('catatan', ['label' => 'Catatan Tambahan', 'type' => 'textarea', 'placeholder' => 'Tulis catatan janji kontrol...', 'class' => 'dark:bg-slate-950']) . '
        </div>',
        'footer' => component_button('Batal', ['variant' => 'outline', 'type' => 'button', 'onclick' => "closeModal('addScheduleModal')"])
            . ' ' . component_button('Simpan Jadwal', ['variant' => 'primary', 'type' => 'submit', 'class' => '!bg-emerald-600 hover:!bg-emerald-700'])
    ]) ?>
</form>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
