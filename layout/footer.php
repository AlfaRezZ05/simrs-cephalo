<?php
/**
 * Footer Layout — Unified SIMRS Dark Glass Footer
 */
?>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/[0.05] mt-auto" style="background: rgba(2, 6, 23, 0.6); backdrop-filter: blur(12px);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 bg-gradient-to-br from-cyan-500 to-emerald-500 rounded-lg flex items-center justify-center">
                        <span class="text-white text-[10px] font-extrabold">S</span>
                    </div>
                    <span class="text-sm text-slate-500 font-medium">SIMRS Portal &copy; <?= date('Y') ?></span>
                </div>
                <p class="text-xs text-slate-600">
                    Teknologi Kedokteran ITS &bull; Pemrograman Web Medis &bull; AI Engine Integrated
                </p>
            </div>
        </div>
    </footer>

    <!-- Component JS -->
    <script src="<?= BASE_URL ?>/assets/js/components.js"></script>
</body>
</html>
