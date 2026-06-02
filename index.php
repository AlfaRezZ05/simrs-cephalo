<?php
/**
 * SIMRS Integrated AI Suite — Landing Hub
 * Now uses the unified layout system.
 */

require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/components/components.php';

requireLogin();
startSession();

$user = getCurrentUser();
$userRole = getUserRole();
$pageTitle = 'Portal SIMRS Terintegrasi';
?>
<?php require_once __DIR__ . '/layout/header.php'; ?>
<?php require_once __DIR__ . '/layout/navbar.php'; ?>

<!-- Floating Background Shapes -->
<div class="sim-bg-shapes">
    <div class="sim-shape sim-shape-1"></div>
    <div class="sim-shape sim-shape-2"></div>
    <div class="sim-shape sim-shape-3"></div>
</div>

<!-- Neural Network Particle Backdrop -->
<canvas id="neural-bg" class="fixed top-0 left-0 w-full h-full z-[1] pointer-events-none"></canvas>

<!-- Main Portal Container -->
<main class="relative z-10 flex-1 max-w-7xl mx-auto w-full px-6 py-12 flex flex-col justify-center">
    
    <!-- Hero / Introduction -->
    <div class="text-center mb-12 max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold text-cyan-300 mb-5 border border-cyan-500/15" style="background: rgba(6, 182, 212, 0.06); backdrop-filter: blur(12px);">
            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
            SIMRS Sistem Informasi Manajemen Rumah Sakit V2.0
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight mb-4 text-white leading-tight">
            Portal Diagnosis <span class="sim-gradient-text">Kecerdasan Buatan (AI)</span>
        </h1>
        <p class="text-slate-400 text-base leading-relaxed">
            Selamat datang di gerbang pelayanan terintegrasi. Sistem kami mengadopsi modul analitik tingkat lanjut dan <em>deep learning</em> untuk memaksimalkan presisi diagnostik serta rekam medis klinis secara terpusat.
        </p>
    </div>

    <!-- Poli (Clinics) Grid System -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <?php if (in_array($userRole, ['admin', 'dokter'])): ?>
        <!-- ======================= -->
        <!-- 1. POLI PARU (ACTIVE) -->
        <!-- ======================= -->
        <div class="sim-card sim-card-interactive rounded-3xl p-6 flex flex-col justify-between relative overflow-hidden group hover:border-emerald-500/25" style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
            <!-- Subtle gradient shine -->
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl transition-opacity group-hover:opacity-100 opacity-60"></div>
            
            <div>
                <!-- Badge & Header -->
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge sim-badge-emerald">POLI AKTIF</span>
                    <div class="w-2 h-2 bg-emerald-400 rounded-full sim-pulse-green"></div>
                </div>

                <!-- Title & Icon -->
                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-emerald-400 border border-emerald-500/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-emerald-300 transition-colors">Poli Paru</h3>
                        <p class="text-xs text-slate-500">Spesialis Tuberkulosis (SIMRS-TB)</p>
                    </div>
                </div>

                <!-- Features list -->
                <ul class="text-slate-400 text-xs space-y-2 mb-8">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Skrining Akustik Suara Batuk AI (CNN)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Rekam Medis TB (BTA, GeneXpert, Rontgen)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Farmasi OAT & Pengawas Menelan Obat (PMO)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Sinkronisasi SITB Kemenkes Nasional</span>
                    </li>
                </ul>
            </div>

            <!-- CTA Button -->
            <a href="tb/index.php" class="sim-btn w-full text-center py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/25 transition-all duration-300 text-white">
                Masuk Poli Paru
            </a>
        </div>
        <?php endif; ?>

        <?php if (in_array($userRole, ['admin', 'dokter'])): ?>
        <!-- ======================= -->
        <!-- 2. POLI GIGI (ACTIVE) -->
        <!-- ======================= -->
        <div class="sim-card sim-card-interactive rounded-3xl p-6 flex flex-col justify-between relative overflow-hidden group hover:border-cyan-500/25" style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
            <!-- Subtle gradient shine -->
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-cyan-500/10 rounded-full blur-2xl transition-opacity group-hover:opacity-100 opacity-60"></div>
            
            <div>
                <!-- Badge & Header -->
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge sim-badge-cyan">POLI AKTIF</span>
                    <div class="w-2 h-2 bg-cyan-400 rounded-full sim-pulse-cyan"></div>
                </div>

                <!-- Title & Icon -->
                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-cyan-500/10 rounded-2xl flex items-center justify-center text-cyan-400 border border-cyan-500/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">Poli Gigi & Mulut</h3>
                        <p class="text-xs text-slate-500">Ortodonti & Sefalometri (Cephalo AI)</p>
                    </div>
                </div>

                <!-- Features list -->
                <ul class="text-slate-400 text-xs space-y-2 mb-8">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Deteksi Landmark Sefalometri Otomatis</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Kalkulasi Steiner's Geometri Otomatis (SNA, SNB)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Diagnosis Klasifikasi Skeletal Wajah Pasien</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Parameter Cloud AI Real-time Interactive</span>
                    </li>
                </ul>
            </div>

            <!-- CTA Button -->
            <a href="cephalo/index.php" class="sim-btn w-full text-center py-3 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 rounded-xl text-xs font-bold shadow-lg shadow-cyan-500/10 hover:shadow-cyan-500/25 transition-all duration-300 text-white">
                Masuk Poli Gigi (Cephalo)
            </a>
        </div>
        <?php endif; ?>

        <?php if ($userRole === 'admin'): ?>
        <!-- ADMIN PANEL CARD -->
        <div class="sim-card sim-card-interactive rounded-3xl p-6 flex flex-col justify-between relative overflow-hidden group hover:border-red-500/25" style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-red-500/10 rounded-full blur-2xl opacity-60"></div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge" style="color:#f87171;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);font-size:0.65rem;padding:4px 10px;border-radius:8px;font-weight:700;">ADMIN</span>
                </div>
                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-red-500/10 rounded-2xl flex items-center justify-center text-red-400 border border-red-500/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-red-300 transition-colors">Kelola Pengguna</h3>
                        <p class="text-xs text-slate-500">Manajemen Akun & Hak Akses</p>
                    </div>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed mb-8">Kelola seluruh akun pengguna sistem, ubah peran (Admin, Dokter, Farmasi, Pasien), dan hapus akun yang tidak diperlukan.</p>
            </div>
            <a href="admin/users.php" class="sim-btn w-full text-center py-3 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 rounded-xl text-xs font-bold shadow-lg shadow-red-500/10 hover:shadow-red-500/25 transition-all duration-300 text-white">Buka Admin Panel</a>
        </div>
        <?php endif; ?>

        <?php if ($userRole === 'farmasi'): ?>
        <!-- FARMASI SHORTCUT CARD -->
        <div class="sim-card sim-card-interactive rounded-3xl p-6 flex flex-col justify-between relative overflow-hidden group hover:border-amber-500/25" style="transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl opacity-60"></div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge" style="color:#fbbf24;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);font-size:0.65rem;padding:4px 10px;border-radius:8px;font-weight:700;">FARMASI AKTIF</span>
                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                </div>
                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center text-amber-400 border border-amber-500/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white group-hover:text-amber-300 transition-colors">Modul Farmasi</h3>
                        <p class="text-xs text-slate-500">Kelola Stok Obat OAT & Supply</p>
                    </div>
                </div>
                <p class="text-slate-400 text-xs leading-relaxed mb-8">Kelola ketersediaan obat anti-tuberkulosis, pantau stok masuk dan keluar, serta distribusi obat ke pasien rawat jalan.</p>
            </div>
            <a href="tb/farmasi.php" class="sim-btn w-full text-center py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl text-xs font-bold shadow-lg shadow-amber-500/10 hover:shadow-amber-500/25 transition-all duration-300 text-white">Buka Modul Farmasi</a>
        </div>
        <?php endif; ?>

        <?php if (in_array($userRole, ['admin', 'dokter'])): ?>
        <!-- ======================= -->
        <!-- 3. POLI JANTUNG (LOCKED) -->
        <!-- ======================= -->
        <div class="rounded-3xl p-6 flex flex-col justify-between relative group border border-white/[0.04] opacity-50" style="background: rgba(15, 23, 42, 0.25);">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge sim-badge-slate">POLI SEGERA HADIR</span>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-slate-500/5 rounded-2xl flex items-center justify-center text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-500">Poli Jantung & Pembuluh</h3>
                        <p class="text-xs text-slate-600">Kardiologi & Deteksi Aritmia AI</p>
                    </div>
                </div>

                <p class="text-slate-600 text-xs leading-relaxed mb-8">
                    Modul diagnosis anomali detak jantung otomatis menggunakan sensor akustik phonocardiogram dan analitik EKG AI adaptif untuk pasien rawat jalan.
                </p>
            </div>

            <button disabled class="w-full text-center py-3 bg-slate-800/50 text-slate-600 rounded-xl text-xs font-semibold cursor-not-allowed border border-white/[0.04]">
                Modul Terkunci
            </button>
        </div>

        <!-- ======================= -->
        <!-- 4. POLI ANAK (LOCKED) -->
        <!-- ======================= -->
        <div class="rounded-3xl p-6 flex flex-col justify-between relative group border border-white/[0.04] opacity-50" style="background: rgba(15, 23, 42, 0.25);">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge sim-badge-slate">POLI SEGERA HADIR</span>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-slate-500/5 rounded-2xl flex items-center justify-center text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-500">Poli Anak</h3>
                        <p class="text-xs text-slate-600">Tumbuh Kembang & Stunting AI</p>
                    </div>
                </div>

                <p class="text-slate-600 text-xs leading-relaxed mb-8">
                    Klasifikasi antropometri balita cerdas berdasarkan standar WHO dan algoritma pendeteksi risiko malnutrisi/stunting melalui rekaman riwayat pertumbuhan.
                </p>
            </div>

            <button disabled class="w-full text-center py-3 bg-slate-800/50 text-slate-600 rounded-xl text-xs font-semibold cursor-not-allowed border border-white/[0.04]">
                Modul Terkunci
            </button>
        </div>

        <!-- ======================= -->
        <!-- 5. POLI PENYAKIT DALAM -->
        <!-- ======================= -->
        <div class="rounded-3xl p-6 flex flex-col justify-between relative group border border-white/[0.04] opacity-50" style="background: rgba(15, 23, 42, 0.25);">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge sim-badge-slate">POLI SEGERA HADIR</span>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-slate-500/5 rounded-2xl flex items-center justify-center text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-500">Poli Penyakit Dalam</h3>
                        <p class="text-xs text-slate-600">Metabolik & Ginjal Prognosis AI</p>
                    </div>
                </div>

                <p class="text-slate-600 text-xs leading-relaxed mb-8">
                    Infrastruktur komparasi lab untuk menguji biomarker ginjal dan organ dalam dengan model regresi multi-output guna memproyeksikan laju kesembuhan.
                </p>
            </div>

            <button disabled class="w-full text-center py-3 bg-slate-800/50 text-slate-600 rounded-xl text-xs font-semibold cursor-not-allowed border border-white/[0.04]">
                Modul Terkunci
            </button>
        </div>

        <!-- ======================= -->
        <!-- 6. POLI KANDUNGAN -->
        <!-- ======================= -->
        <div class="rounded-3xl p-6 flex flex-col justify-between relative group border border-white/[0.04] opacity-50" style="background: rgba(15, 23, 42, 0.25);">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="sim-badge sim-badge-slate">POLI SEGERA HADIR</span>
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>

                <div class="flex items-center gap-3.5 mb-4">
                    <div class="w-12 h-12 bg-slate-500/5 rounded-2xl flex items-center justify-center text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-500">Poli Kandungan</h3>
                        <p class="text-xs text-slate-600">Ginekologi & Rujukan Medis</p>
                    </div>
                </div>

                <p class="text-slate-600 text-xs leading-relaxed mb-8">
                    Modul screening visual USG cerdas dan rujukan medis berbasis analisis pola jaringan untuk menjaga kesehatan janin dan ibu hamil secara optimal.
                </p>
            </div>

            <button disabled class="w-full text-center py-3 bg-slate-800/50 text-slate-600 rounded-xl text-xs font-semibold cursor-not-allowed border border-white/[0.04]">
                Modul Terkunci
            </button>
        </div>
        <?php endif; ?>

    </div>
</main>

<script>
    // ═══════════════════════════════════════════
    // NEURAL BACKGROUND INTERACTIVE PARTICLE CANVAS
    // ═══════════════════════════════════════════
    const canvas = document.getElementById('neural-bg');
    const ctx = canvas.getContext('2d');
    
    let dots = [];
    const DOTS_COUNT = 65;
    const MAX_DISTANCE = 110;
    
    function initCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        dots = [];
        for (let i = 0; i < DOTS_COUNT; i++) {
            dots.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 0.45,
                vy: (Math.random() - 0.5) * 0.45,
                r: Math.random() * 2 + 1
            });
        }
    }
    
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw links
        ctx.lineWidth = 0.5;
        for (let i = 0; i < DOTS_COUNT; i++) {
            for (let j = i + 1; j < DOTS_COUNT; j++) {
                const dist = Math.hypot(dots[i].x - dots[j].x, dots[i].y - dots[j].y);
                if (dist < MAX_DISTANCE) {
                    const alpha = (1 - (dist / MAX_DISTANCE)) * 0.15;
                    ctx.strokeStyle = `rgba(6, 182, 212, ${alpha})`;
                    ctx.beginPath();
                    ctx.moveTo(dots[i].x, dots[i].y);
                    ctx.lineTo(dots[j].x, dots[j].y);
                    ctx.stroke();
                }
            }
        }
        
        // Draw dots
        for (let i = 0; i < DOTS_COUNT; i++) {
            const d = dots[i];
            ctx.fillStyle = 'rgba(255, 255, 255, 0.2)';
            ctx.beginPath();
            ctx.arc(d.x, d.y, d.r, 0, 2 * Math.PI);
            ctx.fill();
            
            // Move
            d.x += d.vx;
            d.y += d.vy;
            
            // Bounce
            if (d.x < 0 || d.x > canvas.width) d.vx *= -1;
            if (d.y < 0 || d.y > canvas.height) d.vy *= -1;
        }
        
        requestAnimationFrame(draw);
    }
    
    window.addEventListener('resize', initCanvas);
    initCanvas();
    draw();
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
