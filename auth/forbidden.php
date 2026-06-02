<?php
/**
 * Akses Ditolak — Halaman ini ditampilkan ketika user tidak memiliki izin.
 */
require_once __DIR__ . '/../core/auth.php';
startSession();
$user = getCurrentUser();
$role = getUserRole();
$pageTitle = 'Akses Ditolak';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak — SIMRS Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .bg-shapes { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; overflow: hidden; }
        .shape { position: absolute; border-radius: 50%; filter: blur(90px); animation: float 25s infinite ease-in-out alternate; opacity: 0.35; }
        .shape-1 { width: 500px; height: 500px; background: #dc2626; top: -15%; left: -10%; }
        .shape-2 { width: 400px; height: 400px; background: #7c3aed; bottom: -15%; right: -10%; animation-delay: -5s; }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 40px) scale(1.08); }
            100% { transform: translate(-30px, 50px) scale(0.95); }
        }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
</div>

<div class="text-center px-6 z-10 max-w-lg">
    <div class="w-20 h-20 bg-red-500/10 border border-red-500/20 rounded-3xl flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
    </div>
    <h1 class="text-3xl font-extrabold text-white mb-3">Akses Ditolak</h1>
    <p class="text-slate-400 text-sm leading-relaxed mb-8">
        Akun Anda dengan peran <span class="text-red-400 font-semibold uppercase"><?= htmlspecialchars($role) ?></span> 
        tidak memiliki izin untuk mengakses halaman yang diminta. Silakan hubungi Administrator jika Anda merasa ini adalah kesalahan.
    </p>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="<?= BASE_URL ?>/index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 font-bold rounded-xl hover:scale-105 transition-transform text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Kembali ke Dashboard
        </a>
        <a href="<?= BASE_URL ?>/auth/logout.php" class="inline-flex items-center gap-2 px-6 py-3 border border-white/10 text-slate-400 font-semibold rounded-xl hover:bg-white/5 transition-colors text-sm">
            Keluar Aplikasi
        </a>
    </div>
</div>

</body>
</html>
