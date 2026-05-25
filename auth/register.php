<?php
/**
 * Register Page
 */

require_once __DIR__ . '/../core/auth.php';
requireGuest();
startSession();

$pageTitle = 'Sign Up';
$errors   = $_SESSION['validation_errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

unset($_SESSION['validation_errors']);
unset($_SESSION['old_input']);

$error = getFlash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pakar — SIMRS Portal</title>
    
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
            overflow-x: hidden;
            position: relative;
            padding: 40px 10px;
            box-sizing: border-box;
        }

        /* 3D Shapes */
        .bg-shapes { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; }
        .shape { position: absolute; border-radius: 50%; filter: blur(95px); animation: float 25s infinite ease-in-out alternate; opacity: 0.55; }
        .shape-1 { width: 550px; height: 550px; background: #0284c7; top: -20%; left: -10%; }
        .shape-2 { width: 450px; height: 450px; background: #059669; bottom: -20%; right: -10%; animation-delay: -5s; }
        
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 40px) scale(1.08); }
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--glass-border);
            padding: 40px 35px;
            border-radius: 28px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
            z-index: 10;
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
</div>

<div class="login-card">
    <div class="text-center">
        <h1 class="text-2xl font-extrabold text-white tracking-tight mb-1">Registrasi Pakar Baru</h1>
        <p class="text-slate-400 text-sm mb-6 leading-relaxed">Buat akun klinis Anda untuk mengakses platform SIMRS.</p>
    </div>

    <?php if ($error): ?>
    <div class="mb-5 flex items-center gap-2.5 px-4 py-3 bg-red-500/10 text-red-400 text-xs rounded-xl border border-red-500/20">
        <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/auth/process_register.php" method="POST" id="registerForm" novalidate>
        
        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-widest mb-1.5">Nama Lengkap & Gelar</label>
            <input type="text" id="name" name="name" 
                   value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>"
                   placeholder="Contoh: dr. Budi Santoso, Sp.P"
                   required>
            <p class="mt-1 text-xs text-red-400 <?= isset($errors['name']) ? '' : 'hidden' ?>" id="name-error">
                <?= htmlspecialchars($errors['name'] ?? '') ?>
            </p>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-widest mb-1.5">Alamat Email Resmi</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                   placeholder="dokter@simrs.id"
                   required>
            <p class="mt-1 text-xs text-red-400 <?= isset($errors['email']) ? '' : 'hidden' ?>" id="email-error">
                <?= htmlspecialchars($errors['email'] ?? '') ?>
            </p>
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-widest mb-1.5">Kata Sandi</label>
            <div class="relative">
                <input type="password" id="password" name="password" 
                       placeholder="Minimal 6 karakter"
                       required>
                <button type="button" onclick="togglePassword('password', this)" 
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            <p class="mt-1 text-xs text-red-400 <?= isset($errors['password']) ? '' : 'hidden' ?>" id="password-error">
                <?= htmlspecialchars($errors['password'] ?? '') ?>
            </p>
        </div>

        <!-- Password Confirm -->
        <div class="mb-5">
            <label for="password_confirm" class="block text-xs font-semibold text-slate-300 uppercase tracking-widest mb-1.5">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <input type="password" id="password_confirm" name="password_confirm" 
                       placeholder="Ulangi kata sandi"
                       required>
            </div>
            <p class="mt-1 text-xs text-red-400 <?= isset($errors['password_confirm']) ? '' : 'hidden' ?>" id="password_confirm-error">
                <?= htmlspecialchars($errors['password_confirm'] ?? '') ?>
            </p>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit">
            Daftar Akun
        </button>
    </form>

    <!-- Login link -->
    <p class="text-center text-sm text-slate-400 mt-5">
        Sudah memiliki akun pakar? 
        <a href="<?= BASE_URL ?>/auth/login.php" class="text-sky-400 hover:text-sky-300 font-semibold transition-colors ml-1">Masuk Sekarang</a>
    </p>
</div>

<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let valid = true;
        valid = validateRequired('name', 'Nama Lengkap') && valid;
        valid = validateRequired('email', 'Email') && valid;
        valid = validateEmail('email') && valid;
        valid = validateRequired('password', 'Kata Sandi') && valid;
        valid = validateMinLength('password', 6, 'Kata Sandi') && valid;
        valid = validateRequired('password_confirm', 'Konfirmasi Kata Sandi') && valid;
        valid = validateMatch('password', 'password_confirm', 'Konfirmasi Kata Sandi') && valid;
        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
