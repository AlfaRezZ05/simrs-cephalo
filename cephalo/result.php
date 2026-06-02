<?php
/**
 * Poli Gigi & Mulut — Cephalo AI Diagnosis results
 * Unified SIMRS Design System.
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
startSession();

$user = getCurrentUser();
$userName = $user['name'] ?? 'Pakar Medis';
$userInitials = getUserInitials();

// 1. Tangkap ID Analisis dari URL
$id_analisis = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_analisis === 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// 2. Tarik data pasien dan foto rontgen spesifik berdasarkan ID
$pdo = getDBConnection();
$sql = "SELECT p.*, s.id_analisis, s.foto_rontgen, s.waktu_upload, s.data_landmark, s.hasil_diagnosis 
        FROM modul_11_sefalometri s
        JOIN modul_11_pasien p ON s.id_pasien = p.id_pasien
        WHERE s.id_analisis = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_analisis]);
$data = $stmt->fetch();

if (!$data) {
    echo "<script>alert('Rekam medis tidak valid!'); window.location.href='index.php';</script>";
    exit();
}

$pageTitle = 'Hasil Diagnosis — Cephalo AI';
?>
<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<!-- Result page specific styles (leveraging global tokens) -->
<style>
    /* Angle grid */
    .ceph-angle-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
    .ceph-angle-box { 
        background: rgba(15, 23, 42, 0.5); border: 1px solid var(--glass-border); 
        border-radius: var(--radius-md); padding: 18px 10px; text-align: center; 
        transition: all var(--duration-normal) var(--ease-smooth); 
    }
    .ceph-angle-box:hover { background: rgba(15, 23, 42, 0.8); border-color: rgba(6, 182, 212, 0.3); transform: translateY(-2px); }
    .ceph-angle-name { font-size: 0.8rem; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; letter-spacing: 1px; }
    .ceph-angle-value { font-size: 1.65rem; color: var(--text-dim); font-weight: 800; } 
    .ceph-angle-value-active { color: #fde047 !important; text-shadow: 0 0 15px rgba(253, 224, 71, 0.3); }

    /* Assistant box */
    .ceph-assistant { 
        background: linear-gradient(145deg, rgba(6, 182, 212, 0.06), rgba(6, 182, 212, 0.02)); 
        border: 1px solid rgba(6, 182, 212, 0.15); border-radius: var(--radius-md); 
        padding: 22px; margin-top: 20px;
    }

    /* Config sliders */
    .ceph-config { background: rgba(0,0,0,0.2); border-radius: var(--radius-sm); padding: 18px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.04);}
    .ceph-slider-header { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 6px; }
    .ceph-slider-val { color: var(--accent-sky); font-weight: bold; }
    .ceph-range { width: 100%; cursor: pointer; accent-color: var(--accent-cyan); height: 5px; border-radius: 5px;}
    
    /* Metrics badge */
    .ceph-metrics { display: flex; justify-content: space-around; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; border: 1px dashed rgba(6, 182, 212, 0.25); background: rgba(15,23,42,0.6);}
    .ceph-metric-title { font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
    .ceph-metric-num { font-size: 1.05rem; font-weight: bold; }

    /* Scanning overlay */
    .ceph-scan-overlay { 
        position: absolute; top: 0; left: 0; right: 0; bottom: 0; 
        background: rgba(2, 6, 23, 0.82); backdrop-filter: blur(4px); 
        display: flex; justify-content: center; align-items: center; 
        color: var(--accent-sky); font-weight: bold; flex-direction: column; z-index: 20; text-align: center; 
    }

    @media (max-width: 900px) {
        .ceph-result-grid { grid-template-columns: 1fr !important; }
        .ceph-angle-grid { grid-template-columns: 1fr !important; gap: 8px; }
        .ceph-angle-box { padding: 12px; }
        .ceph-angle-value { font-size: 1.4rem; }
    }

    @media (max-width: 1024px) {
        /* Hide liquid cursor blobs on touch devices to improve rendering & ease interaction */
        .sim-liquid-layer, #blob-container-cephalo-res {
            display: none !important;
        }
    }

    .ceph-res-container {
        padding: 40px 20px 80px 20px;
    }
    @media (max-width: 640px) {
        .ceph-res-container {
            padding: 16px 12px 40px 12px !important;
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
        <filter id="goo-cephalo-res">
            <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
            <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -10" result="goo" />
            <feBlend in="SourceGraphic" in2="goo" />
        </filter>
    </defs>
</svg>
<div class="sim-liquid-layer" style="filter: url(#goo-cephalo-res);" id="blob-container-cephalo-res"></div>

<div class="sim-section sim-container-sm ceph-res-container">
    
    <a href="index.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-white text-sm font-medium mb-6 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Poli Gigi
    </a>
    
    <!-- Patient Info Card -->
    <div class="sim-card mb-6 flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-xl font-extrabold mb-1 sim-gradient-text">Pasien: <?= htmlspecialchars($data['nama_pasien']) ?></h2>
            <p class="text-slate-400 text-sm">No. RM (NIK): <?= htmlspecialchars($data['nik']) ?> &nbsp;|&nbsp; <?= htmlspecialchars($data['usia']) ?> Tahun &nbsp;|&nbsp; Sp. Ortodonti (<?= htmlspecialchars($data['jenis_kelamin']) ?>)</p>
        </div>
        <div id="badgeStatus" class="sim-badge sim-badge-amber">
            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5"></circle>
                <path d="M12 6v6l4 2" stroke-width="2.5" stroke-linecap="round"></path>
            </svg>
            Menunggu AI
        </div>
    </div>

    <div class="ceph-result-grid grid gap-6" style="grid-template-columns: 1.2fr 1fr;">
        
        <div class="sim-card">
            <div class="font-bold mb-4 text-white text-sm border-b border-white/[0.06] pb-3 tracking-widest uppercase">Citra Rontgen Sefalogram Lateral</div>
            <div class="rounded-xl overflow-hidden relative border border-white/[0.06] min-h-[400px] flex items-center justify-center bg-slate-900/50" style="background-color: var(--bg-surface);">
                <?php
                $img_src = htmlspecialchars($data['foto_rontgen']);
                if (strpos($data['foto_rontgen'], 'data:image/') !== 0) {
                    $img_src = 'uploads/' . $img_src;
                }
                ?>
                <img id="patientImg" src="<?= $img_src ?>" alt="Rontgen Pasien" class="w-full h-auto max-h-[70vh] object-contain block" style="opacity: 0.85;">
                
                <!-- HTML5 INTERACTIVE COORDINATE OVERLAY CANVAS -->
                <canvas id="landmarkCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:10; pointer-events:none;"></canvas>

                <div id="overlayAi" class="ceph-scan-overlay">
                    <div class="sim-spinner mb-4"></div>
                    <div class="text-sm tracking-wider uppercase font-semibold">Sistem Siap Eksekusi...</div>
                </div>
            </div>
        </div>

        <!-- Right Card: Diagnostics & Controls -->
        <div class="sim-card">
            <div class="font-bold mb-4 text-white text-sm border-b border-white/[0.06] pb-3 tracking-widest uppercase">Kalkulasi Geometris Otomatis</div>
            
            <!-- Angle values boxes -->
            <div class="ceph-angle-grid">
                <div class="ceph-angle-box">
                    <div class="ceph-angle-name">SUDUT SNA</div>
                    <div class="ceph-angle-value" id="valSNA">--°</div>
                </div>
                <div class="ceph-angle-box">
                    <div class="ceph-angle-name">SUDUT SNB</div>
                    <div class="ceph-angle-value" id="valSNB">--°</div>
                </div>
                <div class="ceph-angle-box">
                    <div class="ceph-angle-name">SUDUT ANB</div>
                    <div class="ceph-angle-value" id="valANB">--°</div>
                </div>
            </div>

            <!-- Deep Learning metrics badge -->
            <div class="ceph-metrics">
                <div class="text-center"><div class="ceph-metric-title">mAP@50</div><div class="ceph-metric-num sim-gradient-text">91.4%</div></div>
                <div class="text-center"><div class="ceph-metric-title">Precision</div><div class="ceph-metric-num sim-gradient-text">93.2%</div></div>
                <div class="text-center"><div class="ceph-metric-title">Recall</div><div class="ceph-metric-num sim-gradient-text">92.6%</div></div>
            </div>

            <!-- Configuration panel slider -->
            <div class="ceph-config">
                <div class="mb-4">
                    <div class="ceph-slider-header"><span>Confidence Threshold</span><span id="confVal" class="ceph-slider-val">30%</span></div>
                    <input type="range" class="ceph-range" id="confSlider" min="1" max="100" value="30">
                </div>
                <div>
                    <div class="ceph-slider-header"><span>Overlap (NMS) Threshold</span><span id="overVal" class="ceph-slider-val">50%</span></div>
                    <input type="range" class="ceph-range" id="overSlider" min="1" max="100" value="50">
                </div>
            </div>

            <!-- Run AI button trigger -->
            <button id="btnRunAi" class="sim-btn sim-btn-accent w-full py-4 text-base tracking-wide">Jalankan Analisis AI Sekarang</button>

            <!-- AI Diagnostic Assistant box -->
            <div class="ceph-assistant">
                <div class="flex items-center gap-2.5 text-sky-400 font-bold mb-3 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Asisten Pintar Diagnosis
                </div>
                <div class="text-slate-400 text-sm leading-relaxed" id="assistantContent">
                    <p style="margin-top: 0; margin-bottom: 0;">Infrastruktur landmarking berada dalam mode <em>standby</em>. Silakan klik tombol inisialisasi di atas untuk memulai pemrosesan citra medis (ekstraksi batas keras & jaringan lunak rahang).</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // ── Deep Learning AI Script ──
    const idAnalisis = <?= $id_analisis ?>;
    const btnRunAi = document.getElementById('btnRunAi');
    const overlayAi = document.getElementById('overlayAi');
    const img = document.getElementById('patientImg');
    const canvas = document.getElementById('landmarkCanvas');
    const ctx = canvas.getContext('2d');
    const badgeStatus = document.getElementById('badgeStatus');
    const assistantContent = document.getElementById('assistantContent');

    // Sembunyikan overlay saat pertama kali load
    overlayAi.style.display = 'none';

    // Logika Sinkronisasi Teks Slider
    const confSlider = document.getElementById('confSlider');
    const overSlider = document.getElementById('overSlider');
    confSlider.oninput = () => document.getElementById('confVal').innerText = confSlider.value + '%';
    overSlider.oninput = () => document.getElementById('overVal').innerText = overSlider.value + '%';

    // Dynamic drawing of cache landmarks if already exist in DB
    const dbLandmarks = <?= json_encode($data['data_landmark']) ?>;
    
    function drawPoints(landmarks, source = 'db_cached') {
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Match canvas coordinates with client image scale
        canvas.width = img.clientWidth;
        canvas.height = img.clientHeight;
        
        // Resolving natural ratios
        const ratioX = img.clientWidth / img.naturalWidth;
        const ratioY = img.clientHeight / img.naturalHeight;
        
        landmarks.forEach((point, index) => {
            const cx = point.x * ratioX;
            const cy = point.y * ratioY;
            
            // Draw Keypoint
            ctx.beginPath();
            ctx.arc(cx, cy, 5, 0, 2 * Math.PI);
            ctx.fillStyle = '#fde047'; // Neon yellow
            ctx.fill();
            ctx.lineWidth = 1.8;
            ctx.strokeStyle = '#000000';
            ctx.stroke();
            
            // Draw Keypoint Label ID
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = '#000000';
            ctx.shadowBlur = 3;
            ctx.font = 'bold 11px Plus Jakarta Sans, sans-serif';
            ctx.fillText(index + 1, cx + 8, cy + 4);
            ctx.shadowBlur = 0; // reset
        });

        calculateSteiner(landmarks, source);
    }

    function calculateSteiner(landmarks, source) {
        let ptS, ptN, ptA, ptB;
        
        landmarks.forEach(p => {
            const lab = p.label.toLowerCase();
            // Matching keypoints using Roboflow labels (Michael Andraus landmarks format) and Local AI labels
            if (lab.includes('sella') || lab.includes('-(s)') || lab.includes('selli')) ptS = p;
            if (lab.includes('nasion') || lab.includes('-(n)') || lab.includes('nasio')) ptN = p;
            if (lab.includes('subnasal') || lab.includes('-a-') || lab.includes('(a)') || lab.includes('point a') || lab.includes('a-point')) ptA = p;
            if (lab.includes('supramentale') || lab.includes('menton') || lab.includes('-b-') || lab.includes('(b)') || lab.includes('point b') || lab.includes('b-point')) ptB = p;
        });

        const dotsCount = landmarks.length;
        let diagnosisText = `<p style="color:#ef4444; margin:0;"><b>Titik Anatomi Esensial Tidak Lengkap / Hilang!</b><br>Terdapat <b>${dotsCount} titik terdeteksi</b>. Namun, Cloud AI gagal mendeteksi formasi kuartet inti (Sella, Nasion, Point A, dan Point B) secara utuh sehingga kalkulasi diagnostik medis tulang rahang tidak dapat dikerjakan. Coba geser / turunkan <i>Confidence Threshold</i> lalu jalankan ulang!</p>`;

        if (ptS && ptN && ptA && ptB) {
            // Steiner Geometry Trigonometry
            function getAngle(P1, P2, P3) {
                let ang = Math.abs(Math.atan2(P3.y - P2.y, P3.x - P2.x) - Math.atan2(P1.y - P2.y, P1.x - P2.x)) * 180 / Math.PI;
                return ang > 180 ? 360 - ang : ang;
            }
            
            let valSNA = getAngle(ptS, ptN, ptA);
            let valSNB = getAngle(ptS, ptN, ptB);
            let valANB = Math.abs(valSNA - valSNB); // Angle Difference
            
            const snaEl = document.getElementById('valSNA');
            const snbEl = document.getElementById('valSNB');
            const anbEl = document.getElementById('valANB');

            snaEl.innerText = valSNA.toFixed(1) + '°';
            snbEl.innerText = valSNB.toFixed(1) + '°';
            anbEl.innerText = valANB.toFixed(1) + '°';

            snaEl.classList.add('ceph-angle-value-active');
            snbEl.classList.add('ceph-angle-value-active');
            anbEl.classList.add('ceph-angle-value-active');
            
            // Steiner's Diagnostic Jaw skeletal malocclusion
            let kelasSkeletal = "Kelas I (Normal - Pertumbuhan Rahang Harmonis)";
            if (valANB < 0) {
                kelasSkeletal = "Kelas III (Mandibular Prognathism / Rahang Bawah Maju)";
            } else if (valANB > 4.5) {
                kelasSkeletal = "Kelas II (Maxillary Prognathism / Rahang Atas Tonggos / Maju)";
            }
            
            let engineName = "Arsip Database Cache";
            if (source === 'local_python_ai') {
                engineName = "Mesin AI CEPHMark-Net Lokal";
            } else if (source === 'roboflow_cloud_ai' || source === 'roboflow_cloud') {
                engineName = "Cloud AI Roboflow";
            }
            
            diagnosisText = `<p style="color:var(--accent-emerald); margin:0; line-height: 1.6;"><b>Sukses! ${dotsCount} Landmark Ditemukan. Analisis Selesai:</b><br>
            Algoritma sukses mengunci titik krusial (A, B, N, S) menggunakan <b>${engineName}</b>.<br>
            Berdasarkan Steiner's Analysis yang telah dikalkulasi di atas, rasio pertumbuhan kerangka gigi wajah pasien ini diindikasikan masuk ke taksonomi medis: <br><b style="color:white; font-size:1.15rem; display:block; margin-top:5px;">Skeletal ${kelasSkeletal}</b></p>`;
            
            badgeStatus.className = "sim-badge sim-badge-emerald";
            badgeStatus.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0"/>
                </svg>
                Terdiagnosis
            `;
        } else {
            badgeStatus.className = "sim-badge sim-badge-amber";
            badgeStatus.innerHTML = `
                <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5"></circle>
                    <path d="M12 6v6l4 2" stroke-width="2.5" stroke-linecap="round"></path>
                </svg>
                Landmark Parsial
            `;
        }

        assistantContent.innerHTML = diagnosisText;
    }

    // Auto-load if landmarks already exist in DB
    img.addEventListener('load', () => {
        if (dbLandmarks) {
            try {
                const parsed = JSON.parse(dbLandmarks);
                if (parsed && parsed.length > 0) {
                    drawPoints(parsed, 'db_cached');
                }
            } catch (e) {
                console.error("Gagal parsing data landmark dari DB:", e);
            }
        }
    });

    // Run AI scanner
    btnRunAi.addEventListener('click', async () => {
        overlayAi.style.display = 'flex';
        overlayAi.innerHTML = '<div class="sim-spinner mb-4"></div><div class="text-sm tracking-wider uppercase font-semibold">AI sedang mengkalkulasi matriks Rontgen...</div>';
        
        try {
            document.querySelectorAll('.ceph-angle-value').forEach(el => {
                el.innerHTML = '--°';
                el.classList.remove('ceph-angle-value-active');
            });
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const response = await fetch(`api_bridge.php?id=${idAnalisis}&conf=${confSlider.value}&overlap=${overSlider.value}`);
            const result = await response.json();
            
            if (result.status === 'success') {
                overlayAi.style.display = 'none';
                drawPoints(result.landmarks, result.source);
            } else {
                overlayAi.innerHTML = `<div style="color:var(--accent-rose); font-size:0.9rem; text-align:center; padding: 20px;"><b>KONEKSI GAGAL</b><br>${result.message}</div>`;
            }
        } catch (error) {
            overlayAi.innerHTML = `<div style="color:var(--accent-rose); font-size:0.9rem; text-align:center; padding: 20px;"><b>FATAL ERROR</b><br>Gagal menyambung ke Mesin AI. Silakan periksa apakah file app.py menyala di latar belakang atau Anda memiliki koneksi internet!</div>`;
        }
    });

    // ── Script Liquid Cursor Effect ──
    const containerBlob = document.getElementById('blob-container-cephalo-res');
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
