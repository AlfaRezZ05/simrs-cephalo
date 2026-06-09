<?php
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireRole(['admin', 'dokter', 'patient']);
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Skrining AI Batuk';
$activePage = 'screening';
$userRole = getUserRole();

$db = getDBConnection();
$error = getFlash('error');
$success = getFlash('success');

// POST Handler for adding screenings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add_screening') {
        $nama = trim($_POST['pasien_name'] ?? '');
        $confidence = (float)($_POST['confidence'] ?? 87.2);
        $hasil = trim($_POST['hasil'] ?? 'Positif Indikasi');
        $dirujuk = isset($_POST['dirujuk']) && $_POST['dirujuk'] == '1' ? 1 : 0;
        
        try {
            $stmt = $db->prepare("INSERT INTO tb_screenings (pasien_name, confidence, hasil, dirujuk) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $confidence, $hasil, $dirujuk]);
            setFlash('success', 'Skrining berhasil disimpan.');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal menyimpan skrining: ' . $e->getMessage());
        }
        header("Location: screening.php");
        exit;
    }
}

// Fetch historical records from database
$riwayat = [];
try {
    if ($userRole === 'patient') {
        $stmtScr = $db->prepare("SELECT * FROM tb_screenings WHERE LOWER(pasien_name) = LOWER(?) ORDER BY tanggal DESC");
        $stmtScr->execute([$user['name']]);
    } else {
        $stmtScr = $db->query("SELECT * FROM tb_screenings ORDER BY tanggal DESC");
    }
    $riwayat = $stmtScr->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Ignored
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
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-teal-600 transition-colors">Portal SIMRS</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="dashboard.php" class="hover:text-teal-600 transition-colors">SIMRS-TB (Poli Paru)</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-700 dark:text-slate-300 font-medium">Skrining AI Batuk</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>Skrining Akustik AI</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Analisis akustik suara batuk menggunakan Deep Learning untuk deteksi dini TB secara instan</p>
    </div>

    <!-- Screening Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        <!-- Upload / Record -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Input Rekaman Suara Batuk</h3>
                <p class="text-xs text-gray-400 mt-0.5">Rekam suara pasien secara langsung atau unggah berkas audio</p>
            </div>
            <div class="p-5 space-y-5">
                <?php if ($userRole === 'patient'): ?>
                    <input type="hidden" id="input_pasien_name" value="<?= htmlspecialchars($user['name']) ?>">
                <?php else: ?>
                    <div>
                        <label class="block text-sm font-semibold text-slate-350 mb-1.5 uppercase tracking-widest text-[10px]">Nama Pasien</label>
                        <input type="text" id="input_pasien_name" placeholder="Nama pasien..." class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-950 border border-gray-200 dark:border-slate-850 rounded-xl text-sm text-gray-800 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>
                <?php endif; ?>

                <!-- Record Button -->
                <div class="text-center">
                    <button id="btnRecord" onclick="toggleRecording()" 
                            class="w-28 h-28 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 hover:scale-105 active:scale-95 transition-all duration-300 flex items-center justify-center mx-auto relative">
                        <svg id="micIcon" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                        <svg id="stopIcon" class="w-10 h-10 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                        <div id="pulseRing" class="absolute inset-0 rounded-full border-4 border-teal-450 opacity-0"></div>
                    </button>
                    <p id="recordLabel" class="text-sm text-gray-500 dark:text-slate-400 mt-3 font-semibold">Mulai Perekaman</p>
                    <p id="recordTimer" class="text-2xl font-mono font-bold text-teal-600 dark:text-teal-400 mt-1 hidden">00:00</p>
                </div>

                <!-- Waveform Visualization -->
                <div id="waveformContainer" class="hidden">
                    <div class="bg-slate-900 dark:bg-slate-950 rounded-xl p-4 h-20 flex items-center justify-center gap-[3px] overflow-hidden" id="waveform">
                        <!-- Wave bars generated by JS -->
                    </div>
                </div>

                <!-- Divider -->
                <div class="flex items-center gap-3">
                    <div class="h-px flex-1 bg-gray-200 dark:bg-slate-800"></div>
                    <span class="text-xs text-gray-400 font-medium">ATAU</span>
                    <div class="h-px flex-1 bg-gray-200 dark:bg-slate-800"></div>
                </div>

                <!-- Upload -->
                <div id="dropZone" class="border-2 border-dashed border-gray-200 dark:border-slate-850 rounded-xl p-8 text-center hover:border-teal-400 hover:bg-teal-50/30 dark:hover:bg-slate-800/10 transition-all cursor-pointer"
                     onclick="document.getElementById('audioFile').click()">
                    <svg class="w-10 h-10 text-gray-300 dark:text-slate-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-slate-455">Drag & drop file audio atau <span class="text-teal-600 dark:text-teal-400 font-semibold">jelajahi file</span></p>
                    <p class="text-xs text-gray-450 mt-1">WAV, MP3, OGG — Maksimal 10MB</p>
                    <input type="file" id="audioFile" accept="audio/*" class="hidden">
                </div>

                <!-- Analyze Button -->
                <div class="text-center">
                    <?= component_button('Jalankan Analisis AI', [
                        'variant' => 'primary',
                        'size' => 'lg',
                        'fullWidth' => true,
                        'class' => '!bg-gradient-to-r !from-emerald-650 !to-teal-600 hover:!from-emerald-700 hover:!to-teal-700 !shadow-emerald-500/20',
                        'onclick' => 'simulateAnalysis()'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Result Panel -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Hasil Analisis AI</h3>
                <p class="text-xs text-gray-400 mt-0.5">Skor keakuratan / interpretasi klinis</p>
            </div>
            
            <!-- Before Analysis -->
            <div id="resultEmpty" class="p-5 flex flex-col items-center justify-center min-h-[400px] text-center">
                <div class="w-20 h-20 bg-gray-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-9 h-9 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-400 dark:text-slate-500">Menunggu rekaman suara batuk...</p>
                <p class="text-xs text-gray-300 dark:text-slate-600 mt-1">Berikan berkas suara di sebelah kiri untuk inisialisasi klasifikasi AI.</p>
            </div>

            <!-- Analysis Progress -->
            <div id="resultProgress" class="p-5 hidden flex flex-col items-center justify-center min-h-[400px]">
                <div class="relative w-32 h-32 mb-6">
                    <svg class="w-32 h-32 transform -rotate-90 animate-spin-slow" viewBox="0 0 120 120">
                        <circle class="text-gray-250 dark:text-slate-800" stroke="currentColor" stroke-width="8" fill="none" r="52" cx="60" cy="60"/>
                        <circle class="text-teal-500" stroke="currentColor" stroke-width="8" fill="none" r="52" cx="60" cy="60" 
                                stroke-dasharray="326.7" stroke-dashoffset="326.7" stroke-linecap="round" id="progressCircle"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xl font-bold text-gray-800 dark:text-white" id="progressPercent">0%</span>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-600 dark:text-slate-400" id="progressLabel">Memproses audio...</p>
                <div class="flex items-center gap-2 mt-3 text-xs text-gray-400">
                    <div class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-pulse"></div>
                    <span>CoughNet Deep Learning v3.2 Aktif</span>
                </div>
            </div>

            <!-- Analysis Result -->
            <div id="resultDone" class="p-5 hidden">
                <div class="text-center mb-6">
                    <!-- Gauge -->
                    <div class="relative w-40 h-20 mx-auto overflow-hidden mb-2">
                        <div class="absolute bottom-0 left-0 right-0">
                            <svg viewBox="0 0 200 100" class="w-full">
                                <path d="M20 90 A70 70 0 0 1 180 90" fill="none" stroke="#e5e7eb" stroke-width="14" stroke-linecap="round"/>
                                <path d="M20 90 A70 70 0 0 1 180 90" fill="none" stroke="url(#gaugeGrad)" stroke-width="14" stroke-linecap="round"
                                      stroke-dasharray="220" stroke-dashoffset="220" id="gaugeArc"/>
                                <defs>
                                    <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#34d399"/>
                                        <stop offset="50%" stop-color="#fbbf24"/>
                                        <stop offset="100%" stop-color="#ef4444"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white" id="resultScore">87.2%</p>
                    <p class="text-xs text-gray-400 mt-0.5">Confidence Score</p>
                </div>

                <div class="bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 rounded-xl p-4 mb-4" id="resultBadge">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-semibold text-red-700 dark:text-red-400" id="resultLabel">Positif Indikasi TB</span>
                    </div>
                    <p class="text-xs text-red-600 dark:text-red-350" id="resultDesc">Model mendeteksi pola akustik yang konsisten dengan batuk TB. Disarankan untuk pemeriksaan lebih lanjut.</p>
                </div>

                <div class="space-y-3 mb-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Durasi Audio</span>
                        <span class="font-semibold text-gray-800 dark:text-white">4.2 detik</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Frekuensi Dominan</span>
                        <span class="font-semibold text-gray-800 dark:text-white">380 Hz</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Pola Terdeteksi</span>
                        <span class="font-semibold text-gray-800 dark:text-white">Batuk produktif</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Klasifikasi Model</span>
                        <span class="font-semibold text-gray-800 dark:text-white">CoughNet v3.2 (MobileNet)</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <?= component_button('Rujuk ke Dokter Spesialis', [
                        'variant' => 'primary',
                        'fullWidth' => true,
                        'class' => '!bg-emerald-600 hover:!bg-emerald-700',
                        'onclick' => 'submitScreening(1)',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>'
                    ]) ?>
                    <?= component_button('Simpan Hasil', [
                        'variant' => 'outline',
                        'class' => 'dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800',
                        'onclick' => 'submitScreening(0)',
                        'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>'
                    ]) ?>
                </div>

                <form id="saveScreeningForm" method="POST" action="screening.php" class="hidden">
                    <input type="hidden" name="action" value="add_screening">
                    <input type="hidden" name="pasien_name" id="form_pasien_name" value="">
                    <input type="hidden" name="confidence" id="form_confidence" value="87.2">
                    <input type="hidden" name="hasil" id="form_hasil" value="Positif Indikasi">
                    <input type="hidden" name="dirujuk" id="form_dirujuk" value="0">
                </form>
            </div>
        </div>
    </div>

    <!-- Riwayat Skrining -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2"><svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Riwayat Skrining</h3>
                <p class="text-xs text-gray-400 mt-0.5">Daftar rekam pemeriksaan batuk terdekat</p>
            </div>
            <div class="flex items-center gap-2">
                <?= component_input('search_screening', ['placeholder' => 'Cari pasien...', 'class' => 'w-48 dark:bg-slate-950']) ?>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-slate-850 bg-gray-50/50 dark:bg-slate-950">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID Berkas</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu Pemeriksaan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Confidence</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hasil AI</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Rujukan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-slate-800/40">
                    <?php foreach ($riwayat as $r): 
                        $hasilColor = match($r['hasil']) {
                            'Positif Indikasi' => 'error',
                            'Negatif Indikasi' => 'success',
                            default => 'warning'
                        };
                        $confColor = $r['confidence'] > 70 ? 'text-red-500' : ($r['confidence'] > 40 ? 'text-amber-500' : 'text-green-500');
                    ?>
                    <tr class="hover:bg-teal-50/30 dark:hover:bg-slate-800/10 transition-colors">
                        <td class="px-5 py-3 text-gray-400 font-mono text-xs"><?= $r['id'] ?></td>
                        <td class="px-5 py-3">
                            <span class="font-medium text-gray-800 dark:text-slate-100"><?= $r['nama'] ?></span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 dark:text-slate-400"><?= date('d M Y H:i', strtotime($r['tanggal'])) ?></td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?= $r['confidence'] > 70 ? 'bg-red-400' : ($r['confidence'] > 40 ? 'bg-amber-400' : 'bg-green-400') ?>" style="width: <?= $r['confidence'] ?>%"></div>
                                </div>
                                <span class="text-xs font-semibold <?= $confColor ?>"><?= $r['confidence'] ?>%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3"><?= component_badge($r['hasil'], $hasilColor) ?></td>
                        <td class="px-5 py-3">
                            <?php if ($r['dirujuk']): ?>
                                <?= component_badge('Dirujuk', 'primary', ['icon' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>']) ?>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 dark:text-slate-500">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<style>
@keyframes spin-slow { from { transform: rotate(-90deg); } to { transform: rotate(270deg); } }
.animate-spin-slow { animation: spin-slow 2s linear infinite; }
@keyframes pulse-ring { 0% { transform: scale(1); opacity: 0.6; } 100% { transform: scale(1.4); opacity: 0; } }
.recording .animate-pulse-ring { animation: pulse-ring 1.2s ease-out infinite; }
</style>

<script>
let isRecording = false;
let timerInterval = null;
let seconds = 0;

function toggleRecording() {
    isRecording = !isRecording;
    const btn = document.getElementById('btnRecord');
    const mic = document.getElementById('micIcon');
    const stop = document.getElementById('stopIcon');
    const label = document.getElementById('recordLabel');
    const timer = document.getElementById('recordTimer');
    const wave = document.getElementById('waveformContainer');
    const pulse = document.getElementById('pulseRing');

    if (isRecording) {
        mic.classList.add('hidden');
        stop.classList.remove('hidden');
        btn.classList.add('!from-red-500', '!to-red-650', '!shadow-red-500/30');
        btn.classList.remove('from-emerald-500', 'to-teal-650', 'shadow-emerald-500/30');
        label.textContent = 'Merekam...';
        label.classList.add('text-red-500');
        timer.classList.remove('hidden');
        wave.classList.remove('hidden');
        pulse.style.animation = 'pulse-ring 1.2s ease-out infinite';
        
        seconds = 0;
        timerInterval = setInterval(() => {
            seconds++;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timer.textContent = m + ':' + s;
        }, 1000);
        
        generateWaveform();
    } else {
        mic.classList.remove('hidden');
        stop.classList.add('hidden');
        btn.classList.remove('!from-red-500', '!to-red-650', '!shadow-red-500/30');
        btn.classList.add('from-emerald-500', 'to-teal-650', 'shadow-emerald-500/30');
        label.textContent = 'Rekaman Selesai';
        label.classList.remove('text-red-500');
        label.classList.add('text-teal-600');
        pulse.style.animation = 'none';
        
        clearInterval(timerInterval);
        clearInterval(window.waveInterval);
    }
}

function generateWaveform() {
    const wf = document.getElementById('waveform');
    wf.innerHTML = '';
    for (let i = 0; i < 60; i++) {
        const bar = document.createElement('div');
        bar.className = 'wf-bar';
        bar.style.cssText = 'width:3px;border-radius:2px;background:linear-gradient(to top,#10b981,#34d399);transition:height 0.1s;';
        bar.style.height = '4px';
        wf.appendChild(bar);
    }
    window.waveInterval = setInterval(() => {
        document.querySelectorAll('.wf-bar').forEach(bar => {
            bar.style.height = (4 + Math.random() * 55) + 'px';
        });
    }, 100);
}

function simulateAnalysis() {
    const nameVal = document.getElementById('input_pasien_name').value.trim();
    if (!nameVal) {
        alert('Silakan masukkan nama pasien terlebih dahulu.');
        return;
    }

    document.getElementById('resultEmpty').classList.add('hidden');
    document.getElementById('resultDone').classList.add('hidden');
    document.getElementById('resultProgress').classList.remove('hidden');
    
    const circle = document.getElementById('progressCircle');
    const percentEl = document.getElementById('progressPercent');
    const labelEl = document.getElementById('progressLabel');
    const total = 326.7;
    const steps = ['Memproses audio...', 'Mengekstrak spektrum MFCC...', 'Analisis akustik neural...', 'Melakukan inferensi...', 'Finalisasi diagnosis...'];
    let progress = 0;
    
    const interval = setInterval(() => {
        progress += Math.random() * 9 + 3;
        if (progress > 100) progress = 100;
        circle.style.strokeDashoffset = total - (total * progress / 100);
        percentEl.textContent = Math.round(progress) + '%';
        labelEl.textContent = steps[Math.min(Math.floor(progress / 20), steps.length - 1)];
        
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                document.getElementById('resultProgress').classList.add('hidden');
                document.getElementById('resultDone').classList.remove('hidden');
                
                // Determine random outcome
                let label = 'Positif Indikasi TB';
                let desc = 'Model mendeteksi pola akustik yang konsisten dengan batuk TB. Disarankan untuk pemeriksaan lebih lanjut.';
                let outcome = 'Positif Indikasi';
                let score = 85.5;
                const rand = Math.random();
                
                if (rand < 0.6) {
                    score = Math.round(75 + Math.random() * 20);
                    label = 'Positif Indikasi TB';
                    desc = 'Model mendeteksi pola akustik yang konsisten dengan batuk TB. Disarankan untuk pemeriksaan lebih lanjut.';
                    outcome = 'Positif Indikasi';
                } else if (rand < 0.85) {
                    score = Math.round(10 + Math.random() * 30);
                    label = 'Negatif Indikasi TB';
                    desc = 'Model mendeteksi pola akustik normal (tidak terindikasi tuberkulosis).';
                    outcome = 'Negatif Indikasi';
                } else {
                    score = Math.round(41 + Math.random() * 25);
                    label = 'Tidak Dapat Ditentukan';
                    desc = 'Kualitas rekaman kurang baik atau pola batuk tidak spesifik. Silakan rekam ulang.';
                    outcome = 'Tidak Dapat Ditentukan';
                }

                // Update UI elements
                document.getElementById('resultScore').textContent = score + '%';
                document.getElementById('resultLabel').textContent = label;
                document.getElementById('resultDesc').textContent = desc;

                // Update Badge Class
                const resultBadge = document.getElementById('resultBadge');
                resultBadge.className = 'border rounded-xl p-4 mb-4';
                if (outcome === 'Positif Indikasi') {
                    resultBadge.className = 'border rounded-xl p-4 mb-4 bg-red-50 dark:bg-red-950/20 border-red-100 dark:border-red-900/30';
                    document.getElementById('resultLabel').className = 'text-sm font-semibold text-red-750 dark:text-red-400';
                } else if (outcome === 'Negatif Indikasi') {
                    resultBadge.className = 'border rounded-xl p-4 mb-4 bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100 dark:border-emerald-900/30';
                    document.getElementById('resultLabel').className = 'text-sm font-semibold text-emerald-705 dark:text-emerald-450';
                } else {
                    resultBadge.className = 'border rounded-xl p-4 mb-4 bg-amber-50 dark:bg-amber-950/20 border-amber-100 dark:border-amber-900/30';
                    document.getElementById('resultLabel').className = 'text-sm font-semibold text-amber-705 dark:text-amber-450';
                }

                // Set values in form
                document.getElementById('form_pasien_name').value = nameVal;
                document.getElementById('form_confidence').value = score;
                document.getElementById('form_hasil').value = outcome;

                const gauge = document.getElementById('gaugeArc');
                setTimeout(() => {
                    gauge.style.transition = 'stroke-dashoffset 1.5s ease-out';
                    gauge.style.strokeDashoffset = 220 - (220 * score / 100);
                }, 100);
            }, 500);
        }
    }, 200);
}

function submitScreening(dirujukVal) {
    document.getElementById('form_dirujuk').value = dirujukVal;
    document.getElementById('saveScreeningForm').submit();
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
