<?php
/**
 * Admin Panel — User Management
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../components/components.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);
startSession();

$currentUser = getCurrentUser();
$pageTitle = 'Admin Panel — Kelola Pengguna';

$db = getDBConnection();
$error = getFlash('error');
$success = getFlash('success');

// Fetch all users
try {
    $stmt = $db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    setFlash('error', 'Gagal memuat daftar pengguna: ' . $e->getMessage());
}
?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php require_once __DIR__ . '/../layout/navbar.php'; ?>

<!-- Floating Background Shapes -->
<div class="sim-bg-shapes">
    <div class="sim-shape sim-shape-1" style="background: rgba(239, 68, 68, 0.08);"></div>
    <div class="sim-shape sim-shape-2" style="background: rgba(124, 58, 237, 0.08);"></div>
</div>

<main class="relative z-10 flex-1 max-w-7xl mx-auto w-full px-6 py-12">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Admin Panel — Kelola Pengguna
            </h1>
            <p class="text-slate-400 text-sm mt-1">Konfigurasi hak akses klinis, peran fungsional, dan status keanggotaan pengguna.</p>
        </div>
    </div>

    <!-- Feedback Message -->
    <?php if ($error): ?>
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-red-500/10 text-red-400 text-xs rounded-xl border border-red-500/20">
            <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-500/10 text-emerald-400 text-xs rounded-xl border border-emerald-500/20">
            <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Users Table Container (Glassmorphism card) -->
    <div class="border border-white/[0.06] rounded-2xl overflow-hidden shadow-2xl" style="background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(24px);">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/[0.06] text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-900/30">
                        <th class="py-4 px-6">Nama Pengguna</th>
                        <th class="py-4 px-6">Alamat Email</th>
                        <th class="py-4 px-6">Peran (Role)</th>
                        <th class="py-4 px-6">Terdaftar Pada</th>
                        <th class="py-4 px-6 text-center">Aksi / Operasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-slate-500">Tidak ada data pengguna ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <!-- Name/Avatar -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center border border-white/[0.08] text-xs font-bold text-slate-300">
                                            <?= strtoupper(substr($u['name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold text-white block"><?= htmlspecialchars($u['name']) ?></span>
                                            <?php if ($u['id'] == $currentUser['id']): ?>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-400/10 text-cyan-400 border border-cyan-400/20 mt-0.5">Akun Anda</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="py-4 px-6 text-sm text-slate-300">
                                    <?= htmlspecialchars($u['email']) ?>
                                </td>

                                <!-- Role Badges / Select dropdown -->
                                <td class="py-4 px-6">
                                    <?php if ($u['id'] == $currentUser['id']): ?>
                                        <!-- Self-account cannot change role to prevent lockouts -->
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border border-red-500/20 text-red-400 bg-red-500/10">
                                            ADMIN
                                        </span>
                                    <?php else: ?>
                                        <form action="process_users.php" method="POST" class="inline-block">
                                            <input type="hidden" name="action_type" value="update_role">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="role" onchange="this.form.submit()" 
                                                    class="bg-slate-900 border border-white/[0.08] text-xs font-semibold rounded-lg text-slate-300 px-2 py-1.5 focus:outline-none focus:border-cyan-500 cursor-pointer">
                                                <option value="patient" <?= $u['role'] === 'patient' ? 'selected' : '' ?>>Pasien</option>
                                                <option value="dokter" <?= $u['role'] === 'dokter' ? 'selected' : '' ?>>Dokter</option>
                                                <option value="farmasi" <?= $u['role'] === 'farmasi' ? 'selected' : '' ?>>Farmasi</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </form>
                                    <?php endif; ?>
                                </td>

                                <!-- Created At -->
                                <td class="py-4 px-6 text-sm text-slate-400">
                                    <?= date('d M Y, H:i', strtotime($u['created_at'])) ?>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-center">
                                    <?php if ($u['id'] == $currentUser['id']): ?>
                                        <span class="text-xs text-slate-500">—</span>
                                    <?php else: ?>
                                        <form action="process_users.php" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini secara permanen?')">
                                            <input type="hidden" name="action_type" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" 
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-500/15 text-xs font-semibold text-red-400 bg-red-500/5 hover:bg-red-500/15 hover:border-red-500/25 transition-all duration-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus Akun
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
