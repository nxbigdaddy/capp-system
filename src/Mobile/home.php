<?php
/**
 * 移动端首页
 */
require_once __DIR__ . '/../Core/Database.php';

$userId = $_SESSION['user_id'] ?? null;
$isWechat = strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'MicroMessenger') !== false;

// 获取最近查看记录
$recentViews = [];
if ($userId) {
    $db = Database::get();
    $stmt = $db->prepare("
        SELECT a.*, r.name as route_name, r.version
        FROM capp_mobile_access_log a
        LEFT JOIN capp_qr_code q ON a.qr_code_id = q.id
        LEFT JOIN capp_process_route r ON q.target_id = r.id AND q.code_type = 'route'
        WHERE a.user_id = ? AND a.access_type = 'scan_route'
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$userId]);
    $recentViews = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>CAPP 工艺管理</title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
</head>
<body>
    <div class="mobile-container">
        <!-- 头部 -->
        <div class="mobile-header">
            <h1>CAPP 工艺管理</h1>
            <p class="subtitle">扫码查看工艺路线</p>
        </div>

        <!-- 内容区 -->
        <div class="mobile-content">
            <!-- 扫码区域 -->
            <div class="scan-area" onclick="startScan()">
                <div class="scan-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                    </svg>
                </div>
                <p class="scan-text">点击扫码查看工艺</p>
                <p class="scan-hint">支持工艺路线、工序卡片二维码</p>
            </div>

            <!-- 快捷入口 -->
            <div class="quick-actions">
                <a href="/mobile/todos" class="action-item">
                    <span class="action-icon">&#128203;</span>
                    <span class="action-label">待办审批</span>
                </a>
                <a href="javascript:void(0)" onclick="startScan()" class="action-item">
                    <span class="action-icon">&#128247;</span>
                    <span class="action-label">扫一扫</span>
                </a>
                <a href="/mobile/profile" class="action-item">
                    <span class="action-icon">&#128100;</span>
                    <span class="action-label">个人中心</span>
                </a>
            </div>

            <!-- 最近查看记录 -->
            <?php if (!empty($recentViews)): ?>
            <div class="recent-section">
                <div class="section-header">
                    <h3>最近查看</h3>
                    <a href="javascript:void(0)" onclick="clearRecent()">清空</a>
                </div>
                <?php foreach ($recentViews as $view): ?>
                <div class="recent-card" onclick="location.href='/mobile/route/<?php echo (int)$view['target_id']; ?>'">
                    <div class="card-info">
                        <div class="card-title"><?php echo htmlspecialchars($view['route_name'] ?? '未知工艺'); ?> <?php echo htmlspecialchars($view['version'] ?? ''); ?></div>
                        <div class="card-time"><?php echo date('m-d H:i', strtotime($view['created_at'])); ?></div>
                    </div>
                    <span class="card-arrow">&rsaquo;</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- 底部导航 -->
        <div class="mobile-tabbar">
            <a href="/mobile" class="tab-item active">
                <span class="tab-icon">&#127968;</span>
                <span class="tab-label">首页</span>
            </a>
            <a href="/mobile/todos" class="tab-item">
                <span class="tab-icon">&#128203;</span>
                <span class="tab-label">待办</span>
            </a>
            <a href="/mobile/profile" class="tab-item">
                <span class="tab-icon">&#128100;</span>
                <span class="tab-label">我的</span>
            </a>
        </div>
    </div>

    <!-- 扫码弹窗 -->
    <div id="scanModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>二维码扫描</h3>
                <button class="close-btn" onclick="closeScan()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="scanReader"></div>
                <p class="scan-tip">将二维码放入框内即可自动扫描</p>
            </div>
        </div>
    </div>

    <!-- 企业微信 JS-SDK -->
    <?php if ($isWechat): ?>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
    <?php endif; ?>

    <!-- HTML5 二维码扫描库 -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
    // 是否企业微信环境
    const isWechat = <?php echo $isWechat ? 'true' : 'false'; ?>;
    let html5QrCode = null;

    /**
     * 开始扫码
     */
    function startScan() {
        if (isWechat && typeof wx !== 'undefined') {
            // 企业微信环境使用 JS-SDK
            wx.scanQRCode({
                needResult: 1,
                scanType: ["qrCode"],
                success: function(res) {
                    handleScanResult(res.resultStr);
                },
                fail: function(res) {
                    alert('扫码失败: ' + JSON.stringify(res));
                }
            });
        } else {
            // 浏览器环境使用 HTML5 扫码
            openHtml5Scan();
        }
    }

    /**
     * 打开 HTML5 扫码界面
     */
    function openHtml5Scan() {
        const modal = document.getElementById('scanModal');
        modal.style.display = 'flex';

        html5QrCode = new Html5Qrcode('scanReader');
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.error('摄像头启动失败:', err);
            alert('无法启动摄像头，请确保已授予摄像头权限');
            closeScan();
        });
    }

    /**
     * 扫码成功回调
     */
    function onScanSuccess(decodedText, decodedResult) {
        console.log('扫码结果:', decodedText);
        html5QrCode.stop().then(() => {
            closeScan();
            handleScanResult(decodedText);
        });
    }

    /**
     * 扫码失败回调
     */
    function onScanFailure(error) {
        // 静默处理，持续扫描
    }

    /**
     * 关闭扫码弹窗
     */
    function closeScan() {
        const modal = document.getElementById('scanModal');
        modal.style.display = 'none';

        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                html5QrCode = null;
            }).catch(err => {
                console.error('停止扫码失败:', err);
            });
        }
    }

    /**
     * 处理扫码结果
     */
    function handleScanResult(result) {
        // 解析二维码内容
        if (result.indexOf('http') === 0) {
            // 完整URL，直接跳转
            location.href = result;
        } else if (result.indexOf('RT') === 0 || /^\d+$/.test(result)) {
            // 工艺路线编码或ID
            location.href = '/mobile/route/' + encodeURIComponent(result);
        } else {
            alert('无法识别的二维码内容: ' + result);
        }
    }

    /**
     * 清空最近查看记录
     */
    function clearRecent() {
        if (confirm('确定清空最近查看记录吗？')) {
            localStorage.removeItem('recent_views');
            location.reload();
        }
    }

    // 点击弹窗外部关闭
    document.getElementById('scanModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeScan();
        }
    });

    <?php if ($isWechat): ?>
    // 初始化企业微信 JS-SDK
    wx.config({
        beta: true,
        debug: false,
        appId: '<?php echo htmlspecialchars($_ENV['WEWORK_CORP_ID'] ?? ""); ?>',
        timestamp: <?php echo time(); ?>,
        nonceStr: '<?php echo uniqid(); ?>',
        signature: '<?php echo htmlspecialchars($_ENV['WEWORK_JS_SDK_SIGN'] ?? ""); ?>',
        jsApiList: ['scanQRCode', 'getLocation']
    });

    wx.ready(function() {
        console.log('企业微信 JS-SDK 初始化成功');
    });

    wx.error(function(res) {
        console.error('企业微信 JS-SDK 初始化失败:', res);
    });
    <?php endif; ?>
    </script>
</body>
</html>
