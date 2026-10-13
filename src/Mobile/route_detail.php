<?php
/**
 * 工艺路线详情页 (移动端)
 */
require_once __DIR__ . '/../Core/Database.php';

$routeId = (int) ($_GET['id'] ?? 0);
if (!$routeId) {
    http_response_code(404);
    echo '工艺路线不存在';
    exit;
}

$db = Database::get();

// 获取工艺路线
$stmt = $db->prepare("
    SELECT r.*, p.name as product_name, p.code as product_code, p.specification,
           u.name as creator_name, au.name as approver_name
    FROM capp_process_route r
    LEFT JOIN capp_product p ON r.product_id = p.id
    LEFT JOIN capp_user u ON r.created_by = u.id
    LEFT JOIN capp_user au ON r.approved_by = au.id
    WHERE r.id = ? AND r.is_deleted = 0
");
$stmt->execute([$routeId]);
$route = $stmt->fetch();

if (!$route) {
    http_response_code(404);
    echo '工艺路线不存在';
    exit;
}

// 获取工序列表
$opStmt = $db->prepare("
    SELECT o.*, w.name as work_center_name, s.name as std_operation_name
    FROM capp_route_operation o
    LEFT JOIN capp_work_center w ON o.work_center_id = w.id
    LEFT JOIN capp_standard_operation s ON o.std_operation_id = s.id
    WHERE o.route_id = ?
    ORDER BY o.sort_order, o.operation_no
");
$opStmt->execute([$routeId]);
$operations = $opStmt->fetchAll();

// 获取二维码
$qrStmt = $db->prepare("SELECT * FROM capp_qr_code WHERE code_type = 'route' AND target_id = ?");
$qrStmt->execute([$routeId]);
$qrCode = $qrStmt->fetch();

// 记录访问日志
$userId = $_SESSION['user_id'] ?? null;
if ($qrCode && $userId) {
    $logStmt = $db->prepare("
        INSERT INTO capp_mobile_access_log (user_id, qr_code_id, access_type, device_info, ip_address, created_at)
        VALUES (?, ?, 'scan_route', ?, ?, NOW())
    ");
    $logStmt->execute([$userId, $qrCode['id'], $_SERVER['HTTP_USER_AGENT'] ?? null, $_SERVER['REMOTE_ADDR'] ?? null]);
}

$statusMap = [
    'drafting'  => ['text' => '编制中', 'class' => 'status-drafting'],
    'submitted' => ['text' => '已提交', 'class' => 'status-submitted'],
    'approved'  => ['text' => '已审批', 'class' => 'status-approved'],
    'published' => ['text' => '已发布', 'class' => 'status-published'],
    'rejected'  => ['text' => '已驳回', 'class' => 'status-rejected'],
];
$statusInfo = $statusMap[$route['status']] ?? ['text' => $route['status'], 'class' => ''];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>工艺路线 - <?php echo htmlspecialchars($route['name']); ?></title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
</head>
<body>
    <div class="route-detail">
        <!-- 头部信息 -->
        <div class="route-header">
            <a href="/mobile" style="color:#fff; text-decoration:none; font-size:14px;">&larr; 返回</a>
            <h2 style="margin-top:8px;"><?php echo htmlspecialchars($route['name']); ?></h2>
            <div class="route-meta">
                <span><?php echo htmlspecialchars($route['product_code']); ?></span>
                <span><?php echo htmlspecialchars($route['product_name']); ?></span>
                <span class="status-tag <?php echo $statusInfo['class']; ?>"><?php echo $statusInfo['text']; ?></span>
            </div>
        </div>

        <div class="route-info">
            <!-- 基本信息 -->
            <div class="info-section">
                <h3>基本信息</h3>
                <div style="background:#f8f9fa; border-radius:8px; padding:12px; font-size:13px; line-height:2;">
                    <div>路线编码：<?php echo htmlspecialchars($route['code']); ?></div>
                    <div>版本：<?php echo htmlspecialchars($route['version']); ?></div>
                    <div>规格型号：<?php echo htmlspecialchars($route['specification'] ?? '-'); ?></div>
                    <div>总工时：<?php echo $route['total_time'] ? $route['total_time'] . ' 分钟' : '-'; ?></div>
                    <div>创建人：<?php echo htmlspecialchars($route['creator_name'] ?? '-'); ?></div>
                    <div>创建时间：<?php echo date('Y-m-d H:i', strtotime($route['created_at'])); ?></div>
                    <?php if ($route['approved_at']): ?>
                    <div>审批人：<?php echo htmlspecialchars($route['approver_name'] ?? '-'); ?></div>
                    <div>审批时间：<?php echo date('Y-m-d H:i', strtotime($route['approved_at'])); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 二维码 -->
            <?php if ($qrCode): ?>
            <div class="info-section">
                <h3>二维码</h3>
                <div style="text-align:center; padding:12px;">
                    <img src="<?php echo htmlspecialchars($qrCode['qr_image_url']); ?>" alt="二维码" style="max-width:200px; border-radius:8px;">
                    <p style="font-size:12px; color:#999; margin-top:8px;">扫描次数：<?php echo (int)$qrCode['scan_count']; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- 工序列表 -->
            <div class="info-section">
                <h3>工序列表 (<?php echo count($operations); ?>)</h3>
                <?php if (empty($operations)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#128221;</div>
                    <div class="empty-state-text">暂无工序</div>
                </div>
                <?php else: ?>
                <ul class="operation-list">
                    <?php foreach ($operations as $op): ?>
                    <li class="operation-item" onclick="location.href='/mobile/operation/<?php echo (int)$op['id']; ?>'">
                        <div class="operation-info">
                            <div class="operation-no">工序 <?php echo htmlspecialchars($op['operation_no']); ?></div>
                            <div class="operation-name"><?php echo htmlspecialchars($op['operation_name']); ?></div>
                            <div class="operation-center"><?php echo htmlspecialchars($op['work_center_name'] ?? '-'); ?></div>
                        </div>
                        <div class="operation-time">
                            <?php echo $op['standard_time'] ? $op['standard_time'] . 'min' : '-'; ?>
                            <div style="font-size:18px; color:#ccc;">&rsaquo;</div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
