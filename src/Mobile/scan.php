<?php
/**
 * 二维码扫描页面 (HTML5 摄像头方案)
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>二维码扫描</title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <style>
        body { background: #000; }
        .scan-container {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            flex-direction: column;
        }
        .scan-header {
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 10;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), transparent);
        }
        .scan-header h2 { color: #fff; font-size: 16px; }
        .back-btn { color: #fff; text-decoration: none; font-size: 14px; }
        #reader { flex: 1; width: 100%; }
        #reader video { object-fit: cover; }
        .scan-overlay {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 260px; height: 260px;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 12px;
            pointer-events: none;
        }
        .scan-overlay::before, .scan-overlay::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
            border-color: #1890ff;
            border-style: solid;
        }
        .scan-overlay::before { top: -2px; left: -2px; border-width: 4px 0 0 4px; }
        .scan-overlay::after { bottom: -2px; right: -2px; border-width: 0 4px 4px 0; }
        .scan-line {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: #1890ff;
            animation: scan 2s linear infinite;
            box-shadow: 0 0 8px #1890ff;
        }
        @keyframes scan {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
        .scan-footer {
            position: absolute;
            bottom: 40px; left: 0; right: 0;
            text-align: center;
            color: #fff;
            font-size: 13px;
            z-index: 10;
        }
        .scan-tip { margin-bottom: 16px; opacity: 0.8; }
        .file-input-wrapper {
            display: inline-block;
            position: relative;
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%; height: 100%;
            cursor: pointer;
        }
        .btn-album {
            background: rgba(255,255,255,0.2);
            color: #fff;
            padding: 10px 24px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.3);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="scan-container">
        <div class="scan-header">
            <a href="/mobile" class="back-btn">&larr; 返回</a>
            <h2>扫描二维码</h2>
            <span style="width:40px;"></span>
        </div>

        <div id="reader"></div>

        <div class="scan-overlay">
            <div class="scan-line"></div>
        </div>

        <div class="scan-footer">
            <p class="scan-tip">将二维码放入框内即可自动扫描</p>
            <div class="file-input-wrapper">
                <button class="btn-album">从相册选择</button>
                <input type="file" id="fileInput" accept="image/*">
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    const html5QrCode = new Html5Qrcode('reader');

    // 启动摄像头扫描
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess,
        onScanFailure
    ).catch(err => {
        console.error('摄像头启动失败:', err);
        alert('无法启动摄像头，请确保已授予摄像头权限');
    });

    function onScanSuccess(decodedText, decodedResult) {
        console.log('扫码成功:', decodedText);
        html5QrCode.stop().then(() => {
            handleResult(decodedText);
        });
    }

    function onScanFailure(error) {
        // 静默处理
    }

    // 相册选择扫描
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        html5QrCode.scanFile(file, true)
            .then(decodedText => {
                handleResult(decodedText);
            })
            .catch(err => {
                alert('未能识别二维码，请尝试其他图片');
            });
    });

    function handleResult(result) {
        if (result.indexOf('http') === 0) {
            location.href = result;
        } else if (result.indexOf('RT') === 0 || /^\d+$/.test(result)) {
            location.href = '/mobile/route/' + encodeURIComponent(result);
        } else {
            alert('无法识别的二维码: ' + result);
            // 重新启动扫描
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                onScanFailure
            );
        }
    }
    </script>
</body>
</html>
