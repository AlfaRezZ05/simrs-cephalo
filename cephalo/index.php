<?php
/**
 * Poli Gigi & Mulut — Cephalo AI Main Suite
 * Unified SIMRS Design System.
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$userName = $user['name'] ?? 'Pakar Medis';
$userInitials = getUserInitials();

// ── Database Initialization ──
$pdo = getDBConnection();

// Deteksi driver database yang sedang digunakan
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($driver === 'mysql') {
    // Create patient table if not exists (MySQL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS modul_11_pasien (
        id_pasien INT AUTO_INCREMENT PRIMARY KEY,
        nama_pasien VARCHAR(255) NOT NULL,
        nik VARCHAR(50) NOT NULL UNIQUE,
        usia INT NOT NULL,
        jenis_kelamin VARCHAR(20) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Create cephalometry table if not exists (MySQL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS modul_11_sefalometri (
        id_analisis INT AUTO_INCREMENT PRIMARY KEY,
        id_pasien INT NOT NULL,
        foto_rontgen VARCHAR(255) NOT NULL,
        data_landmark LONGTEXT DEFAULT NULL,
        hasil_diagnosis VARCHAR(255) DEFAULT NULL,
        waktu_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pasien) REFERENCES modul_11_pasien(id_pasien) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} else {
    // Create patient table if not exists (PostgreSQL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS modul_11_pasien (
        id_pasien SERIAL PRIMARY KEY,
        nama_pasien VARCHAR(255) NOT NULL,
        nik VARCHAR(50) NOT NULL UNIQUE,
        usia INT NOT NULL,
        jenis_kelamin VARCHAR(20) NOT NULL
    );");

    // Create cephalometry table if not exists (PostgreSQL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS modul_11_sefalometri (
        id_analisis SERIAL PRIMARY KEY,
        id_pasien INT NOT NULL,
        foto_rontgen VARCHAR(255) NOT NULL,
        data_landmark TEXT DEFAULT NULL,
        hasil_diagnosis VARCHAR(255) DEFAULT NULL,
        waktu_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_pasien) REFERENCES modul_11_pasien(id_pasien) ON DELETE CASCADE
    );");
}

// Fetch database records
$sql = "SELECT p.nama_pasien, p.nik, p.usia, p.jenis_kelamin, s.id_analisis, s.foto_rontgen, s.waktu_upload, s.data_landmark 
        FROM modul_11_pasien p 
        JOIN modul_11_sefalometri s ON p.id_pasien = s.id_pasien 
        ORDER BY s.waktu_upload DESC";
$stmt = $pdo->query($sql);
$riwayat_pasien = $stmt->fetchAll();

$pageTitle = 'Poli Gigi — Cephalo AI';
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<!-- Cephalo-specific enhancements (layered on top of global.css) -->
<style>
    /* Hero Section */
    .ceph-hero {
        height: 85vh;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        position: relative; padding: 0 40px; z-index: 2;
    }
    
    .ceph-corner { position: absolute; font-size: 0.75rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; color: var(--text-muted); line-height: 1.5; }
    .ceph-corner-tl { top: 60px; left: 40px; }
    .ceph-corner-tr { top: 60px; right: 40px; text-align: right; }
    .ceph-corner-bl { bottom: 60px; left: 40px; }
    .ceph-corner-br { bottom: 60px; right: 40px; max-width: 400px; text-align: right; color: var(--text-secondary); font-weight: 400; text-transform: none; letter-spacing: 0.5px; }
    
    .ceph-title {
        font-size: 9.5vw;
        font-weight: 850;
        line-height: 0.9;
        letter-spacing: -0.04em;
        text-align: center;
        margin: 0;
        z-index: 2;
    }

    .ceph-scroll {
        position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%);
        display: flex; flex-direction: column; align-items: center; gap: 10px;
        color: var(--text-muted); font-size: 0.75rem; letter-spacing: 3px; text-decoration: none; text-transform: uppercase;
        animation: cephBounce 2s infinite; z-index: 10;
    }
    @keyframes cephBounce { 
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); } 
        40% { transform: translateY(-10px) translateX(-50%); } 
        60% { transform: translateY(-5px) translateX(-50%); } 
    }

    /* Upload Area */
    .ceph-upload-area { 
        border: 1px dashed rgba(6, 182, 212, 0.4); border-radius: var(--radius-lg); padding: 50px 30px; 
        text-align: center; background: rgba(6, 182, 212, 0.02); cursor: pointer; position: relative; transition: all var(--duration-normal) var(--ease-smooth); 
    }
    .ceph-upload-area:hover { border-color: var(--accent-cyan); background: rgba(6, 182, 212, 0.06); }

    /* Submit button */
    .ceph-btn-submit { 
        background: #fff; color: var(--bg-surface); border: none; padding: 18px 24px; border-radius: var(--radius-md); 
        cursor: pointer; font-weight: 700; font-size: 1.05rem; width: 100%; margin-top: 20px; transition: all var(--duration-normal) var(--ease-smooth);
        font-family: inherit;
    }
    .ceph-btn-submit:hover { background: #e0f2fe; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255, 255, 255, 0.12); }

    /* Make card padding responsive on mobile/tablet */
    .ceph-card {
        padding: 40px;
    }

    /* Mobile and Tablet Media Queries */
    @media (max-width: 1024px) {
        .ceph-corner {
            display: none !important;
        }
    /* Mobile and Tablet Media Queries */
    @media (max-width: 1024px) {
        .ceph-corner {
            display: none !important;
        }
        .ceph-hero {
            height: auto !important;
            min-height: 120px !important;
            padding: 30px 16px 5px 16px !important;
        }
        .ceph-title {
            font-size: 2.2rem !important;
            margin-bottom: 5px !important;
        }
        .ceph-scroll {
            display: none !important; /* Hide bouncing down scroll helper on mobile to save valuable vertical screen space */
        }
        .sim-liquid-layer, #blob-container-cephalo {
            display: none !important;
        }
        .ceph-card {
            padding: 20px 16px !important;
            border-radius: var(--radius-xl) !important;
            margin-bottom: 20px !important;
        }
        /* Tighten section padding & eliminate nested padding */
        #upload-section {
            padding-top: 10px !important;
            padding-bottom: 30px !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        .sim-container-sm {
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 100% !important;
        }
    }

    @media (max-width: 640px) {
        .ceph-hero {
            min-height: 90px !important;
            padding: 24px 12px 0px 12px !important;
        }
        .ceph-title {
            font-size: 1.85rem !important;
        }
        .ceph-card {
            padding: 16px 12px !important;
            border-radius: var(--radius-lg) !important;
        }
        .sim-input, .sim-select {
            padding: 11px 12px !important;
            font-size: 0.875rem !important;
        }
        .sim-label {
            font-size: 0.75rem !important;
            margin-bottom: 6px !important;
        }
        .ceph-upload-area {
            padding: 24px 12px !important;
        }
        .ceph-upload-area svg {
            width: 32px !important;
            height: 32px !important;
        }
        .ceph-btn-submit {
            padding: 12px 16px !important;
            font-size: 0.9rem !important;
            margin-top: 12px !important;
        }
        /* Make history table look flawless on mobile screens without stretching */
        .sim-table th, .sim-table td {
            padding: 10px 8px !important;
            font-size: 0.775rem !important;
        }
        .sim-table td strong {
            font-size: 0.8rem !important;
        }
        .sim-badge {
            padding: 3px 8px !important;
            font-size: 0.65rem !important;
        }
    }

    @media (max-width: 380px) {
        .ceph-title {
            font-size: 1.6rem !important;
        }
        .ceph-card {
            padding: 12px 10px !important;
        }
    }
</style>

<!-- Floating Background Shapes -->
<div class="sim-bg-shapes">
    <div class="sim-shape sim-shape-1"></div>
    <div class="sim-shape sim-shape-2"></div>
    <div class="sim-shape sim-shape-3"></div>
</div>

<!-- Liquid Blob Cursor Filter -->
<svg style="visibility: hidden; position: absolute;" width="0" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1">
    <defs>
        <filter id="goo-cephalo">
            <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
            <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -10" result="goo" />
            <feBlend in="SourceGraphic" in2="goo" />
        </filter>
    </defs>
</svg>
<div class="sim-liquid-layer" style="filter: url(#goo-cephalo);" id="blob-container-cephalo"></div>

<!-- Hero Section -->
<section class="ceph-hero relative">
    <div class="ceph-corner ceph-corner-tl">SIMRS Poli Gigi<br>Cephalo AI Suite</div>
    <div class="ceph-corner ceph-corner-tr">Web Programming<br>Dept. Teknologi Kedokteran</div>
    <div class="ceph-corner ceph-corner-bl">Teknologi ITS Surabaya<br>Orthodontic Diagnostic Tool</div>
    <div class="ceph-corner ceph-corner-br">
        Cephalo AI adalah sistem cerdas berbasis Deep Learning terintegrasi yang dirancang untuk mendeteksi landmark anatomi sefalometri secara otomatis guna memproyeksikan diagnosis ortodonti yang sangat presisi dan efisien.
    </div>

    <h1 class="ceph-title sim-gradient-text-white">Cephalo AI</h1>

    <a href="#upload-section" class="ceph-scroll">
        Unggah Rontgen Pasien
        <svg class="w-5 h-5 mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
        </svg>
    </a>
</section>

<!-- Main Scan App Section -->
<section id="upload-section" class="sim-section min-h-screen py-16 px-5 flex flex-col items-center justify-center border-t border-white/[0.04]">
    <div class="sim-container-sm">
        
        <div class="sim-card ceph-card">
            <h2 class="text-2xl font-bold text-white mb-2 tracking-tight">Analisis Sefalometri Cerdas</h2>
            <p class="text-slate-400 text-base mb-8 leading-relaxed">Integrasi Cloud AI untuk ekstraksi parameter rahang otomatis. Lengkapi biodata pasien di bawah ini.</p>
            
            <form action="process_upload.php" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="sim-label" for="nama_pasien">Nama Lengkap Pasien</label>
                        <input type="text" id="nama_pasien" name="nama_pasien" class="sim-input" placeholder="Nama pasien..." required>
                    </div>
                    <div>
                        <label class="sim-label" for="nik">Nomor Rekam Medis (NIK)</label>
                        <input type="number" id="nik" name="nik" class="sim-input" placeholder="16 digit NIK..." required>
                    </div>
                    <div>
                        <label class="sim-label" for="usia">Usia Klinis (Tahun)</label>
                        <input type="number" id="usia" name="usia" class="sim-input" min="1" placeholder="Contoh: 25" required>
                    </div>
                    <div>
                        <label class="sim-label" for="jenis_kelamin">Jenis Kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="sim-select" required>
                            <option value="" disabled selected>Pilih klasifikasi...</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="sim-label">Citra Rontgen Sefalogram Lateral</label>
                    <div class="ceph-upload-area relative" id="drop-area-cephalo">
                        <input type="file" id="foto_rontgen" name="foto_rontgen" accept="image/jpeg, image/png" required style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; z-index: 10;">
                        <div id="upload-ui-cephalo">
                            <div class="flex justify-center mb-4 text-sky-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <div class="font-semibold text-slate-200 mb-2 text-lg">Tarik & Letakkan Citra Medis Disini</div>
                            <div class="text-sm text-slate-500">Format berkas yang diterima: JPG, PNG (Resolusi Tinggi)</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="ceph-btn-submit">Inisialisasi Pemindaian AI</button>
            </form>
        </div>

        <div class="sim-card ceph-card">
            <h2 class="text-xl font-bold text-white mb-2 tracking-tight">Arsip Pemindaian Sefalometri</h2>
            <p class="text-slate-400 text-sm mb-5">Daftar rekam medis ortodonti yang telah dipindai.</p>
            
            <?php if (count($riwayat_pasien) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="sim-table">
                        <thead>
                            <tr>
                                <th>Identitas Pasien</th>
                                <th>Demografi</th>
                                <th>Waktu Pemindaian</th>
                                <th>Status Diagnostik</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_pasien as $row): 
                                $hasLandmark = !empty($row['data_landmark']);
                                $badgeClass = $hasLandmark ? 'sim-badge sim-badge-emerald' : 'sim-badge sim-badge-cyan';
                                $badgeText = $hasLandmark ? 'Terdiagnosis' : 'Antrean AI';
                                $iconSvg = $hasLandmark ? 
                                    '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/></svg>' :
                                    '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>';
                            ?>
                            <tr>
                                <td>
                                    <strong class="text-white block text-sm"><?= htmlspecialchars($row['nama_pasien']) ?></strong>
                                    <span class="text-slate-500 text-xs font-mono"><?= htmlspecialchars($row['nik']) ?></span>
                                </td>
                                <td>
                                    <span class="text-slate-300"><?= htmlspecialchars($row['usia']) ?> Thn</span><br>
                                    <span class="text-slate-500 text-xs"><?= htmlspecialchars($row['jenis_kelamin']) ?></span>
                                </td>
                                <td class="text-xs text-slate-500">
                                    <?= date('d M Y H:i', strtotime($row['waktu_upload'])) ?>
                                </td>
                                <td>
                                    <span class="<?= $badgeClass ?>">
                                        <?= $iconSvg ?>
                                        <?= $badgeText ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="result.php?id=<?= $row['id_analisis'] ?>" class="text-sky-400 hover:text-sky-300 hover:underline font-bold text-xs transition-colors">
                                        Buka Hasil →
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 border border-dashed border-white/[0.08] rounded-xl text-slate-500">
                    <p class="m-0">Arsip kosong. Lakukan inisialisasi foto rontgen pertama pasien di atas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    // ── Script Form Upload UI Reaction ──
    const fileInput = document.getElementById('foto_rontgen');
    const uploadUI = document.getElementById('upload-ui-cephalo');
    const dropArea = document.getElementById('drop-area-cephalo');

    fileInput.addEventListener('change', function() {
        if(this.files && this.files.length > 0) {
            const fileName = this.files[0].name;
            uploadUI.innerHTML = `
                <div class="flex justify-center mb-4" style="color: var(--accent-emerald);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <div class="font-bold text-white mb-1 text-lg">Citra Siap Diunggah: ${fileName}</div>
                <div class="text-emerald-400 font-semibold text-sm">File tervalidasi. Klik tombol di bawah untuk menjalankan AI.</div>
            `;
            dropArea.style.borderColor = 'var(--accent-emerald)';
            dropArea.style.background = 'rgba(16, 185, 129, 0.04)';
        }
    });

    // ── Script Liquid Cursor Effect ──
    const containerBlob = document.getElementById('blob-container-cephalo');
    const isMobileOrTablet = window.matchMedia("(max-width: 1024px)").matches || ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    
    if(containerBlob && !isMobileOrTablet) {
        const blobstores = [];
        const BLOB_COUNT = 15;
        for(let i=0; i<BLOB_COUNT; i++) {
            let b = document.createElement('div');
            b.className = 'sim-water-droplet';
            containerBlob.appendChild(b);
            blobstores.push({el: b, x: window.innerWidth/2, y: window.innerHeight/2});
        }

        let tX = window.innerWidth/2;
        let tY = window.innerHeight/2;

        document.addEventListener('mousemove', (e) => {
            tX = e.clientX;
            tY = e.clientY;
        });

        function animateBlobs() {
            let prevX = tX;
            let prevY = tY;
            for(let i=0; i<BLOB_COUNT; i++) {
                let blob = blobstores[i];
                blob.x += (prevX - blob.x) * 0.35;
                blob.y += (prevY - blob.y) * 0.35;
                blob.el.style.transform = `translate(${blob.x}px, ${blob.y}px) scale(${1 - (i/BLOB_COUNT)})`;
                prevX = blob.x;
                prevY = blob.y;
            }
            requestAnimationFrame(animateBlobs);
        }
        animateBlobs();
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
