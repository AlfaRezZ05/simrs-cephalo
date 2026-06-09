<?php
/**
 * Login Page
 */

require_once __DIR__ . '/../core/auth.php';
requireGuest();
startSession();

$pageTitle = 'Sign In';
$error   = getFlash('error');
$success = getFlash('success');
$oldEmail = getFlash('old_email');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem — SIMRS Portal</title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts — Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --glass-bg: rgba(15, 23, 42, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary: #0ea5e9;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #020617;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* 3D Moving Shapes */
        .bg-shapes { position: absolute; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; }
        .shape { position: absolute; border-radius: 50%; filter: blur(90px); animation: float 25s infinite ease-in-out alternate; opacity: 0.55; }
        .shape-1 { width: 550px; height: 550px; background: #0284c7; top: -15%; left: -10%; }
        .shape-2 { width: 450px; height: 450px; background: #059669; bottom: -15%; right: -10%; animation-delay: -5s; }
        .shape-3 { width: 350px; height: 350px; background: #4f46e5; top: 35%; left: 35%; animation-delay: -10s; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 40px) scale(1.08); }
            100% { transform: translate(-30px, 50px) scale(0.95); }
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-60px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideOutLeft {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-60px); }
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--glass-border);
            padding: 45px 35px;
            border-radius: 28px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
            z-index: 10;
            animation: slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        .login-card.slide-out {
            animation: slideOutLeft 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .icon-brand {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #0ea5e9, #10b981);
            border-radius: 18px;
            display: inline-flex; justify-content: center; align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.35);
        }

        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%; padding: 15px 20px !important; box-sizing: border-box;
            background: rgba(2, 6, 23, 0.65) !important; border: 1px solid var(--glass-border);
            border-radius: 14px; color: white !important; font-size: 0.95rem;
            transition: all 0.3s;
        }
        input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus { 
            outline: none; border-color: var(--primary); background: rgba(2, 6, 23, 0.85) !important; box-shadow: 0 0 12px rgba(14,165,233,0.2); 
        }

        .btn-submit {
            background: #fff; color: #0f172a; width: 100%; border: none; padding: 16px;
            border-radius: 14px; font-weight: 700; font-size: 1rem; cursor: pointer;
            margin-top: 15px; transition: all 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2); }
        .btn-submit:active { transform: translateY(0); }
    </style>
</head>
<body>

<div class="bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
</div>

<div class="login-card">
    <div class="text-center">
        <div class="icon-brand">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        
        <h1 class="text-2xl font-extrabold text-white tracking-tight mb-1">Portal SIMRS AI</h1>
        <p class="text-slate-400 text-sm mb-6 leading-relaxed">Selamat datang di sistem manajemen terintegrasi.<br>Silakan masuk menggunakan kredensial Anda.</p>
    </div>

    <?php if ($error): ?>
    <div class="mb-5 flex items-center gap-2.5 px-4 py-3 bg-red-500/10 text-red-400 text-xs rounded-xl border border-red-500/20">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="mb-5 flex items-center gap-2.5 px-4 py-3 bg-emerald-500/10 text-emerald-400 text-xs rounded-xl border border-emerald-500/20">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/auth/process_login.php" method="POST" id="loginForm" novalidate>
        
        <!-- Email -->
        <div class="mb-5">
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-widest mb-2">Alamat Email</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($oldEmail ?? '') ?>"
                   placeholder="dokter@simrs.id"
                   required>
            <p class="mt-1.5 text-xs text-red-400 hidden" id="email-error"></p>
        </div>

        <!-- Password -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-widest">Kata Sandi</label>
            </div>
            <div class="relative">
                <input type="password" id="password" name="password" 
                       placeholder="••••••••"
                       required>
                <button type="button" onclick="togglePassword('password', this)" 
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            <p class="mt-1.5 text-xs text-red-400 hidden" id="password-error"></p>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit">
            Masuk Portal
        </button>
    </form>

    <!-- Register link -->
    <p class="text-center text-sm text-slate-400 mt-6">
        Belum memiliki akun pakar? 
        <a href="<?= BASE_URL ?>/auth/register.php" class="text-sky-400 hover:text-sky-300 font-semibold transition-colors ml-1">Buat Akun Baru</a>
    </p>
</div>

<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        let valid = true;
        valid = validateRequired('email', 'Email') && valid;
        valid = validateEmail('email') && valid;
        valid = validateRequired('password', 'Kata Sandi') && valid;
        if (!valid) e.preventDefault();
    });

    // Page Transition Animation
    const registerLink = document.querySelector('a[href*="register.php"]');
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.href;
            document.querySelector('.login-card').classList.add('slide-out');
            setTimeout(() => { window.location.href = href; }, 350);
        });
    }
</script>
</body>
</html>
