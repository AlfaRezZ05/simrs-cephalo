<?php
/**
 * Header Layout — Unified SIMRS Design System
 * Always-dark mode, Plus Jakarta Sans, global CSS tokens.
 */

if (!isset($pageTitle)) {
    $pageTitle = 'SIMRS Portal';
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIMRS Portal — Integrated AI Diagnostic Platform">
    <title><?= htmlspecialchars($pageTitle) ?> — MedWeb SIMRS</title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#06b6d4',
                            hover: '#0891b2',
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Unified Global CSS Design System -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
</head>
<body class="preload">

<script>
    // Remove preload class after page loads to enable transitions
    window.addEventListener('load', () => document.body.classList.remove('preload'));
</script>
