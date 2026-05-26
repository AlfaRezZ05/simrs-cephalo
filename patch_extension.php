<?php
/**
 * VSIX Engine Compatibility Patcher
 * 
 * A beautiful, single-page utility tool to automatically modify the vscode engine requirement 
 * in .vsix packages to make them compatible with Code/Cursor/Windsurf 2.0.1+.
 */

$error = '';
$success = false;
$downloadPath = '';

// Create temp directory if not exists
$tempDir = __DIR__ . '/patch_temp';
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0777, true);
}

// Handle File Upload and Patching
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['vsix_file'])) {
    $file = $_FILES['vsix_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Error code: ' . $file['error'];
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'vsix') {
            $error = 'Invalid file type. Please upload a file with a .vsix extension.';
        } else {
            $tempVsix = $tempDir . '/' . uniqid('patch_', true) . '.vsix';
            if (move_uploaded_file($file['tmp_name'], $tempVsix)) {
                if (class_exists('ZipArchive')) {
                    $zip = new ZipArchive();
                    if ($zip->open($tempVsix) === TRUE) {
                        $jsonContent = $zip->getFromName('extension/package.json');
                        if ($jsonContent !== false) {
                            $data = json_decode($jsonContent, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                // Force compatibility with any VS Code engine version
                                if (isset($data['engines'])) {
                                    $data['engines']['vscode'] = '*';
                                } else {
                                    $data['engines'] = ['vscode' => '*'];
                                }
                                
                                $newJsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                
                                // Replace package.json in zip file
                                $zip->deleteName('extension/package.json');
                                $zip->addFromString('extension/package.json', $newJsonContent);
                                $zip->close();
                                
                                $success = true;
                                $downloadName = pathinfo($file['name'], PATHINFO_FILENAME) . '_patched.vsix';
                                $downloadPath = 'patch_extension.php?download=' . urlencode(basename($tempVsix)) . '&name=' . urlencode($downloadName);
                            } else {
                                $error = 'Failed to parse package.json inside the VSIX file.';
                                $zip->close();
                                @unlink($tempVsix);
                            }
                        } else {
                            $error = 'Could not find extension/package.json inside the .vsix file.';
                            $zip->close();
                            @unlink($tempVsix);
                        }
                    } else {
                        $error = 'Failed to open the .vsix file as a ZIP archive. The file might be corrupted.';
                        @unlink($tempVsix);
                    }
                } else {
                    $error = 'PHP ZipArchive extension is not enabled in your XAMPP installation. Please enable it in php.ini.';
                    @unlink($tempVsix);
                }
            } else {
                $error = 'Failed to move the uploaded file to the temporary folder.';
            }
        }
    }
}

// Handle Download Request
if (isset($_GET['download']) && isset($_GET['name'])) {
    $file = basename($_GET['download']);
    $name = basename($_GET['name']);
    $filePath = $tempDir . '/' . $file;
    
    if (file_exists($filePath) && strpos($file, 'patch_') === 0) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        
        // Clean up immediately after download
        @unlink($filePath);
        exit;
    } else {
        $error = 'File not found or the download token has expired.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSIX Compatibility Patcher</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0d0f14;
            --card-bg: rgba(22, 28, 38, 0.6);
            --accent-color: #7c4dff;
            --accent-glow: rgba(124, 77, 255, 0.4);
            --text-main: #f5f6fa;
            --text-muted: #8a94a6;
            --success-color: #00e676;
            --error-color: #ff1744;
            --border-radius: 20px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Abstract Background Glows */
        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--accent-glow) 0%, rgba(13, 15, 20, 0) 70%);
            top: -100px;
            left: -100px;
            z-index: 0;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(0, 230, 118, 0.15) 0%, rgba(13, 15, 20, 0) 70%);
            bottom: -150px;
            right: -100px;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 580px;
            padding: 20px;
            z-index: 10;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--border-radius);
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 60px rgba(124, 77, 255, 0.15);
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #a382ff, #7c4dff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            display: inline-block;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        /* Drag & Drop Upload Zone */
        .upload-zone {
            border: 2px dashed rgba(124, 77, 255, 0.4);
            border-radius: 16px;
            padding: 40px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(124, 77, 255, 0.02);
            position: relative;
            margin-bottom: 25px;
        }

        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--accent-color);
            background: rgba(124, 77, 255, 0.08);
            box-shadow: inset 0 0 20px rgba(124, 77, 255, 0.1);
        }

        .upload-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 15px;
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .upload-zone:hover .upload-icon {
            transform: translateY(-5px);
        }

        .upload-text {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .upload-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        input[type="file"] {
            display: none;
        }

        /* Selected file banner */
        .file-selected-banner {
            display: none;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 18px;
            margin-top: 15px;
            align-items: center;
            justify-content: space-between;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .file-details {
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .file-icon {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .file-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .file-size {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Beautiful Glow Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px 30px;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #7c4dff, #651fff);
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 24px rgba(124, 77, 255, 0.3);
            text-decoration: none;
        }

        .btn:hover {
            background: linear-gradient(135deg, #9575cd, #7c4dff);
            box-shadow: 0 12px 28px rgba(124, 77, 255, 0.5);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #00e676, #00c853);
            box-shadow: 0 8px 24px rgba(0, 230, 118, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #69f0ae, #00e676);
            box-shadow: 0 12px 28px rgba(0, 230, 118, 0.5);
        }

        /* Status Messages */
        .alert {
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            line-height: 1.4;
            animation: fadeIn 0.3s ease;
        }

        .alert-error {
            background: rgba(255, 23, 68, 0.1);
            border: 1px solid rgba(255, 23, 68, 0.2);
            color: #ff5252;
        }

        .alert-success {
            background: rgba(0, 230, 118, 0.1);
            border: 1px solid rgba(0, 230, 118, 0.2);
            color: #69f0ae;
        }

        /* Footer styling */
        .footer {
            margin-top: 30px;
            font-size: 0.75rem;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        .footer span {
            color: var(--accent-color);
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="logo">⚡</div>
        <h1>VSIX Compatibility Patcher</h1>
        <p class="subtitle">Quickly patch your extension package to make it fully compatible with newer IDE versions like Code 2.0.1+.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success && !empty($downloadPath)): ?>
            <div class="alert alert-success">
                <span>🎉</span>
                <div><strong>Patch Successful!</strong> The vscode engine version requirement in your extension has been bypassed. Click below to download your ready-to-install extension file.</div>
            </div>

            <a href="<?php echo htmlspecialchars($downloadPath); ?>" class="btn btn-success">
                <span>📥</span> Download Patched Extension
            </a>
            
            <div style="margin-top: 20px;">
                <a href="patch_extension.php" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none; border-bottom: 1px dashed var(--text-muted);">
                    Patch Another File
                </a>
            </div>

        <?php else: ?>
            <form action="patch_extension.php" method="POST" enctype="multipart/form-data" id="patch-form">
                <div class="upload-zone" id="drop-zone" onclick="document.getElementById('file-input').click()">
                    <span class="upload-icon">📁</span>
                    <div class="upload-text">Drag & Drop .vsix file here</div>
                    <div class="upload-hint">or click to browse from your device</div>
                    
                    <div class="file-selected-banner" id="file-banner">
                        <div class="file-details">
                            <span class="file-icon">📦</span>
                            <div>
                                <div class="file-name" id="selected-file-name">filename.vsix</div>
                                <div class="file-size" id="selected-file-size">0 KB</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="file" name="vsix_file" id="file-input" accept=".vsix" onchange="handleFileSelect(this)">
                
                <button type="submit" class="btn" id="submit-btn" disabled>
                    <span>⚡</span> Patch Extension
                </button>
            </form>
        <?php endif; ?>

        <div class="footer">
            Brought to you by your assistant <span>Antigravity</span>
        </div>
    </div>
</div>

<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const fileBanner = document.getElementById('file-banner');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const submitBtn = document.getElementById('submit-btn');

    // Drag and drop event listeners
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(fileInput);
        }
    });

    function handleFileSelect(input) {
        if (input.files.length > 0) {
            const file = input.files[0];
            const sizeInKb = (file.size / 1024).toFixed(1);
            
            selectedFileName.textContent = file.name;
            selectedFileSize.textContent = `${sizeInKb} KB`;
            
            // Show details card & enable submit
            fileBanner.style.display = 'flex';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        } else {
            fileBanner.style.display = 'none';
            submitBtn.disabled = true;
        }
    }
</script>
</body>
</html>
