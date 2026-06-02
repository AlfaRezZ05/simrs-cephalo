<?php
/**
 * SIMRS-TB — Farmasi & PMO
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';

requireLogin();
requireRole(['admin', 'dokter', 'farmasi']);
startSession();

$user = getCurrentUser();
$pageTitle = 'Poli Paru — Farmasi & PMO';
$activePage = 'farmasi';

// ── Dummy: Stok Obat ──
$stokObat = [
    ['kode' => 'FDC-4',  'nama' => 'FDC 4 Kombinasi (RHZE)', 'kategori' => 'FDC',    'stok' => 1200, 'min' => 100, 'kadaluarsa' => '2027-06-30', 'alert' => false],
    ['kode' => 'FDC-2',  'nama' => 'FDC 2 Kombinasi (RH)',   'kategori' => 'FDC',    'stok' => 800,  'min' => 100, 'kadaluarsa' => '2027-08-15', 'alert' => false],
    ['kode' => 'EMB-400','nama' => 'Etambutol 400mg',         'kategori' => 'Lini 1', 'stok' => 45,   'min' => 50,  'kadaluarsa' => '2027-07-15', 'alert' => true],
    ['kode' => 'STREP',  'nama' => 'Streptomisin 1g Injeksi', 'kategori' => 'Lini 1', 'stok' => 30,   'min' => 20,  'kadaluarsa' => '2027-03-30', 'alert' => true],
    ['kode' => 'INH-300','nama' => 'Isoniazid 300mg',         'kategori' => 'Lini 1', 'stok' => 500,  'min' => 50,  'kadaluarsa' => '2027-05-20', 'alert' => false],
    ['kode' => 'B6-10',  'nama' => 'Vitamin B6 10mg',         'kategori' => 'Sisipan','stok' => 2000, 'min' => 200, 'kadaluarsa' => '2028-01-01', 'alert' => false],
];

// ── Dummy: Distribusi Obat ──
$distribusi = [
    ['pasien' => 'Ahmad Fauzi',   'obat' => 'FDC 4 (RHZE)',   'dosis' => '3 tab/hari', 'jumlah' => 90,  'tanggal' => '2026-05-01', 'status' => 'Sudah Diambil'],
    ['pasien' => 'Siti Aminah',   'obat' => 'FDC 4 (RHZE)',   'dosis' => '3 tab/hari', 'jumlah' => 90,  'tanggal' => '2026-05-01', 'status' => 'Sudah Diambil'],
    ['pasien' => 'Budi Santoso',  'obat' => 'FDC 2 (RH)',     'dosis' => '3 tab/hari', 'jumlah' => 90,  'tanggal' => '2026-05-05', 'status' => 'Belum Diambil'],
    ['pasien' => 'Dewi Lestari',  'obat' => 'FDC 4 (RHZE)',   'dosis' => '2 tab/hari', 'jumlah' => 60,  'tanggal' => '2026-05-03', 'status' => 'Terlambat'],
    ['pasien' => 'Riko Pratama',  'obat' => 'FDC 2 (RH)',     'dosis' => '3 tab/hari', 'jumlah' => 90,  'tanggal' => '2026-05-08', 'status' => 'Sudah Diambil'],
];

// ── Dummy: Log PMO ──
$pmoLogs = [
    ['pasien' => 'Ahmad Fauzi',  'pmo' => 'Istri Pasien',    'tanggal' => '2026-05-13', 'waktu' => '07:30', 'status' => 'Diminum',      'metode' => 'Langsung'],
    ['pasien' => 'Siti Aminah',  'pmo' => 'Kader TB',        'tanggal' => '2026-05-13', 'waktu' => '08:00', 'status' => 'Diminum',      'metode' => 'Video Call'],
    ['pasien' => 'Budi Santoso', 'pmo' => 'Anak Pasien',     'tanggal' => '2026-05-13', 'waktu' => '—',     'status' => 'Tidak Diminum','metode' => '—'],
    ['pasien' => 'Dewi Lestari', 'pmo' => 'Kader TB',        'tanggal' => '2026-05-13', 'waktu' => '07:45', 'status' => 'Efek Samping', 'metode' => 'Foto'],
    ['pasien' => 'Riko Pratama', 'pmo' => 'Suami Pasien',    'tanggal' => '2026-05-13', 'waktu' => '06:30', 'status' => 'Diminum',      'metode' => 'Langsung'],
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
        <span class="text-gray-700 dark:text-slate-350 font-medium">Farmasi & PMO</span>
    </nav>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2"><svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>Farmasi & PMO</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Manajemen distribusi obat TB dan kepatuhan minum obat harian</p>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-1 bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-slate-800 p-1 mb-6 w-fit">
        <button onclick="switchTab('stok')" id="tab-stok" class="px-4 py-2 text-sm font-medium rounded-lg bg-teal-50 dark:bg-emerald-500/15 text-teal-700 dark:text-emerald-400 transition-colors">Stok Obat</button>
        <button onclick="switchTab('distribusi')" id="tab-distribusi" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">Distribusi</button>
        <button onclick="switchTab('pmo')" id="tab-pmo" class="px-4 py-2 text-sm font-medium rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">Log PMO</button>
    </div>

    <!-- TAB: Stok Obat -->
    <div id="panel-stok">
        <!-- Alert Stok Rendah -->
        <?php 
        $alertItems = array_filter($stokObat, fn($o) => $o['alert']);
        if (count($alertItems) > 0): ?>
        <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 rounded-xl p-4 mb-5 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-400 flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>Stok Sangat Rendah</p>
                <p class="text-xs text-amber-700 dark:text-amber-350 mt-0.5"><?= count($alertItems) ?> jenis obat OAT berada di bawah ambang batas minimum. Segera buat laporan pengadaan.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-slate-850 bg-gray-50/50 dark:bg-slate-950">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode Obat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Obat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stok Sisa</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Min. Alert</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kadaluarsa</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-850/40">
                        <?php foreach ($stokObat as $obat): ?>
                        <tr class="hover:bg-teal-50/30 dark:hover:bg-slate-800/10 transition-colors <?= $obat['alert'] ? 'bg-amber-50/30 dark:bg-amber-500/5' : '' ?>">
                            <td class="px-5 py-3 font-mono text-xs text-gray-500"><?= $obat['kode'] ?></td>
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-slate-100"><?= $obat['nama'] ?></td>
                            <td class="px-5 py-3"><?= component_badge($obat['kategori'], 'default') ?></td>
                            <td class="px-5 py-3">
                                <span class="font-bold <?= $obat['alert'] ? 'text-red-500' : 'text-gray-800 dark:text-slate-200' ?>"><?= number_format($obat['stok']) ?></span>
                            </td>
                            <td class="px-5 py-3 text-gray-500"><?= $obat['min'] ?></td>
                            <td class="px-5 py-3 text-gray-500 dark:text-slate-400"><?= date('d M Y', strtotime($obat['kadaluarsa'])) ?></td>
                            <td class="px-5 py-3">
                                <?php if ($obat['alert']): ?>
                                    <?= component_badge('Stok Rendah', 'error', ['icon' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>']) ?>
                                <?php else: ?>
                                    <?= component_badge('Tersedia', 'success') ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: Distribusi -->
    <div id="panel-distribusi" class="hidden">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-850 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Distribusi Obat Pasien</h3>
                <?= component_button('+ Distribusi Baru', ['variant' => 'primary', 'size' => 'sm', 'class' => '!bg-emerald-600 hover:!bg-emerald-700']) ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-slate-850 bg-gray-50/50 dark:bg-slate-950">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis OAT</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dosis</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Tablet</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Distribusi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Resep</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-850/40">
                        <?php foreach ($distribusi as $d): 
                            $sColor = match($d['status']) { 'Sudah Diambil' => 'success', 'Terlambat' => 'error', default => 'warning' };
                        ?>
                        <tr class="hover:bg-teal-50/30 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-slate-100"><?= $d['pasien'] ?></td>
                            <td class="px-5 py-3 text-gray-600 dark:text-slate-350"><?= $d['obat'] ?></td>
                            <td class="px-5 py-3 text-gray-500"><?= $d['dosis'] ?></td>
                            <td class="px-5 py-3 text-gray-800 dark:text-slate-200 font-semibold"><?= $d['jumlah'] ?></td>
                            <td class="px-5 py-3 text-gray-500 dark:text-slate-400"><?= date('d M Y', strtotime($d['tanggal'])) ?></td>
                            <td class="px-5 py-3"><?= component_badge($d['status'], $sColor) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: PMO Logs -->
    <div id="panel-pmo" class="hidden">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-850 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white">Log Pengawas Menelan Obat (PMO)</h3>
                <?= component_button('+ Input Catatan PMO', ['variant' => 'primary', 'size' => 'sm', 'class' => '!bg-emerald-600 hover:!bg-emerald-700']) ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-slate-850 bg-gray-50/50 dark:bg-slate-950">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pasien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">PMO Pendamping</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Laporan</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu Minum</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kepatuhan Harian</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-850/40">
                        <?php foreach ($pmoLogs as $l): 
                            $lColor = match($l['status']) { 'Diminum' => 'success', 'Efek Samping' => 'warning', default => 'error' };
                        ?>
                        <tr class="hover:bg-teal-50/30 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-800 dark:text-slate-100"><?= $l['pasien'] ?></td>
                            <td class="px-5 py-3 text-gray-600 dark:text-slate-350"><?= $l['pmo'] ?></td>
                            <td class="px-5 py-3 text-gray-500 dark:text-slate-400"><?= date('d M Y', strtotime($l['tanggal'])) ?></td>
                            <td class="px-5 py-3 text-gray-500 dark:text-slate-400"><?= $l['waktu'] ?></td>
                            <td class="px-5 py-3"><?= component_badge($l['status'], $lColor) ?></td>
                            <td class="px-5 py-3"><?= component_badge($l['metode'], 'default') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</main>

<script>
function switchTab(tabName) {
    document.querySelectorAll('[id^="panel-"]').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(t => {
        t.classList.remove('bg-teal-50', 'text-teal-700', 'dark:bg-emerald-500/15', 'dark:text-emerald-400');
        t.classList.add('text-gray-500', 'dark:text-slate-400');
    });
    
    document.getElementById('panel-' + tabName).classList.remove('hidden');
    const tab = document.getElementById('tab-' + tabName);
    
    // style active
    if (document.body.classList.contains('dark') || true) {
        tab.classList.add('dark:bg-emerald-500/15', 'dark:text-emerald-400', 'bg-teal-50', 'text-teal-700');
    } else {
        tab.classList.add('bg-teal-50', 'text-teal-700');
    }
    tab.classList.remove('text-gray-500', 'dark:text-slate-400');
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
