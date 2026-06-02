<?php
/**
 * Navbar Layout — Unified SIMRS Dark Glass Navigation
 * With Role-Based Access Control (RBAC) visibility.
 */

$currentUser = getCurrentUser();
$initials = getUserInitials();
$userRole = getUserRole();

// Detect active section for nav highlighting
$currentPath = $_SERVER['PHP_SELF'] ?? '';
$isPortal = strpos($currentPath, '/index.php') !== false && strpos($currentPath, '/tb/') === false && strpos($currentPath, '/cephalo/') === false;
$isTB = strpos($currentPath, '/tb/') !== false;
$isCephalo = strpos($currentPath, '/cephalo/') !== false;
$isAdmin = strpos($currentPath, '/admin/') !== false;

// Role-based visibility flags
$showPoliParu   = in_array($userRole, ['admin', 'dokter']);
$showPoliGigi   = in_array($userRole, ['admin', 'dokter']);
$showFarmasi    = in_array($userRole, ['admin', 'dokter', 'farmasi']);
$showAdminPanel = $userRole === 'admin';

// Role badge config
$roleBadges = [
    'admin'   => ['label' => 'Admin',   'color' => 'text-red-400 bg-red-500/10 border-red-500/20'],
    'dokter'  => ['label' => 'Dokter',  'color' => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'],
    'farmasi' => ['label' => 'Farmasi', 'color' => 'text-amber-400 bg-amber-500/10 border-amber-500/20'],
    'patient' => ['label' => 'Pasien',  'color' => 'text-sky-400 bg-sky-500/10 border-sky-500/20'],
];
$badge = $roleBadges[$userRole] ?? $roleBadges['patient'];
?>

<!-- Unified Glass Navbar -->
<nav class="sticky top-0 z-50 border-b border-white/[0.06] backdrop-blur-xl bg-slate-950/70" style="transition: background var(--duration-normal) var(--ease-smooth);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-cyan-500/15 group-hover:shadow-cyan-500/30 group-hover:scale-105 transition-all duration-300">
                        <span class="text-white text-sm font-extrabold">S</span>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-white font-extrabold text-sm tracking-wide leading-none">SIMRS Portal</span>
                        <span class="text-slate-500 text-[10px] font-semibold tracking-wider block -mt-0.5">INTEGRATED AI SUITE</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex items-center gap-1">
                <a href="<?= BASE_URL ?>/index.php" 
                   class="nav-link px-3.5 py-2 text-xs font-semibold rounded-lg transition-all duration-200 <?= $isPortal 
                       ? 'text-cyan-400 bg-cyan-500/10 border border-cyan-500/20' 
                       : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' ?>">
                    Dashboard Utama
                </a>

                <?php if ($showPoliParu): ?>
                <a href="<?= BASE_URL ?>/tb/index.php" 
                   class="nav-link px-3.5 py-2 text-xs font-semibold rounded-lg transition-all duration-200 <?= $isTB 
                       ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' 
                       : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' ?>">
                    <span class="flex items-center gap-1.5">
                        <?php if ($isTB): ?>
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                        <?php endif; ?>
                        Poli Paru
                    </span>
                </a>
                <?php endif; ?>

                <?php if ($showPoliGigi): ?>
                <a href="<?= BASE_URL ?>/cephalo/index.php" 
                   class="nav-link px-3.5 py-2 text-xs font-semibold rounded-lg transition-all duration-200 <?= $isCephalo 
                       ? 'text-sky-400 bg-sky-500/10 border border-sky-500/20' 
                       : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' ?>">
                    <span class="flex items-center gap-1.5">
                        <?php if ($isCephalo): ?>
                            <span class="w-1.5 h-1.5 bg-sky-400 rounded-full animate-pulse"></span>
                        <?php endif; ?>
                        Poli Gigi
                    </span>
                </a>
                <?php endif; ?>

                <?php if ($showFarmasi && !$showPoliParu): ?>
                <!-- Farmasi-only users get a direct link to the pharmacy page -->
                <a href="<?= BASE_URL ?>/tb/farmasi.php" 
                   class="nav-link px-3.5 py-2 text-xs font-semibold rounded-lg transition-all duration-200 <?= $isTB 
                       ? 'text-amber-400 bg-amber-500/10 border border-amber-500/20' 
                       : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' ?>">
                    <span class="flex items-center gap-1.5">
                        <?php if ($isTB): ?>
                            <span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span>
                        <?php endif; ?>
                        💊 Farmasi
                    </span>
                </a>
                <?php endif; ?>

                <?php if ($showAdminPanel): ?>
                <a href="<?= BASE_URL ?>/admin/users.php" 
                   class="nav-link px-3.5 py-2 text-xs font-semibold rounded-lg transition-all duration-200 <?= $isAdmin 
                       ? 'text-red-400 bg-red-500/10 border border-red-500/20' 
                       : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' ?>">
                    <span class="flex items-center gap-1.5">
                        <?php if ($isAdmin): ?>
                            <span class="w-1.5 h-1.5 bg-red-400 rounded-full animate-pulse"></span>
                        <?php endif; ?>
                        Admin Panel
                    </span>
                </a>
                <?php endif; ?>
            </div>

            <!-- User Menu (Desktop) -->
            <div class="hidden md:flex items-center gap-3">
                <div class="flex items-center gap-3 pl-3 border-l border-white/[0.06]">
                    
                    <!-- Role Badge -->
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border <?= $badge['color'] ?>">
                        <?= $badge['label'] ?>
                    </span>

                    <!-- User Dropdown -->
                    <div class="relative" id="userDropdown">
                        <button onclick="toggleDropdown('userDropdownMenu')" 
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-white/5 sim-focus transition-all duration-200">
                            <div class="w-8 h-8 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-full flex items-center justify-center shadow-sm">
                                <span class="text-white text-xs font-bold"><?= htmlspecialchars($initials) ?></span>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-white leading-none"><?= htmlspecialchars($currentUser['name'] ?? 'Pengguna') ?></p>
                                <p class="text-[10px] text-slate-500 mt-0.5"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdownMenu" 
                             class="hidden absolute right-0 mt-2 w-56 rounded-xl py-1.5 z-50 border border-white/[0.08] shadow-2xl"
                             style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px); opacity: 0; transform: translateY(-4px); transition: opacity 0.15s ease, transform 0.15s ease;">
                            <div class="px-4 py-2.5 border-b border-white/[0.06]">
                                <p class="text-sm font-semibold text-white"><?= htmlspecialchars($currentUser['name'] ?? 'Pengguna') ?></p>
                                <p class="text-xs text-slate-500"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                            </div>
                            <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar Aplikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" 
                        class="p-2 rounded-lg hover:bg-white/5 transition-colors text-slate-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu panel -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-white/[0.06]">
        <div class="px-4 py-3 space-y-1">
            <a href="<?= BASE_URL ?>/index.php" class="block px-3 py-2.5 text-sm font-medium rounded-lg transition-colors <?= $isPortal ? 'text-cyan-400 bg-cyan-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                Dashboard Utama
            </a>

            <?php if ($showPoliParu): ?>
            <a href="<?= BASE_URL ?>/tb/index.php" class="block px-3 py-2.5 text-sm font-medium rounded-lg transition-colors <?= $isTB ? 'text-emerald-400 bg-emerald-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                Poli Paru
            </a>
            <?php endif; ?>

            <?php if ($showPoliGigi): ?>
            <a href="<?= BASE_URL ?>/cephalo/index.php" class="block px-3 py-2.5 text-sm font-medium rounded-lg transition-colors <?= $isCephalo ? 'text-sky-400 bg-sky-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                Poli Gigi (Cephalo)
            </a>
            <?php endif; ?>

            <?php if ($showFarmasi && !$showPoliParu): ?>
            <a href="<?= BASE_URL ?>/tb/farmasi.php" class="block px-3 py-2.5 text-sm font-medium rounded-lg transition-colors <?= $isTB ? 'text-amber-400 bg-amber-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                💊 Farmasi
            </a>
            <?php endif; ?>

            <?php if ($showAdminPanel): ?>
            <a href="<?= BASE_URL ?>/admin/users.php" class="block px-3 py-2.5 text-sm font-medium rounded-lg transition-colors <?= $isAdmin ? 'text-red-400 bg-red-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5' ?>">
                ⚙️ Admin Panel
            </a>
            <?php endif; ?>
        </div>
        <div class="border-t border-white/[0.06] px-4 py-3">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-bold"><?= htmlspecialchars($initials) ?></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white"><?= htmlspecialchars($currentUser['name'] ?? 'Pengguna') ?></p>
                    <p class="text-xs text-slate-500"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                </div>
                <span class="ml-auto px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-md border <?= $badge['color'] ?>">
                    <?= $badge['label'] ?>
                </span>
            </div>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar Aplikasi
            </a>
        </div>
    </div>
</nav>
