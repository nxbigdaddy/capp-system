<?php
/**
 * 工序详情页 (移动端)
 */
require_once __DIR__ . '/../Core/Database.php';

$operationId = (int) ($_GET['id'] ?? 0);
if (!$operationId) {
    http_response_code(404);
    echo '工序不存在';
    exit;
}

$db = Database::get();

// 获取工序信息
$stmt = $db->prepare("
    SELECT o.*, r.name as route_name, r.code as route_code, r.version, r.product_name, r.product_code,
           w.name as work_center_name, s.name as std_operation_name
    FROM capp_route_operation o
    INNER JOIN capp_process_route r ON o.route_id = r.id
    LEFT JOIN capp_work_center w ON o.work_center_id = w.id
    LEFT JOIN capp_standard_operation s ON o.std_operation_id = s.id
    WHERE o.id = ?
");
$stmt->execute([$operationId]);
$operation = $stmt->fetch();

if (!$operation) {
    http_response_code(404);
    echo '工序不存在';
    exit;
}

// 获取工序卡片
$cardStmt = $db->prepare("SELECT * FROM capp_operation_card WHERE route_operation_id = ?");
$cardStmt->execute([$operationId]);
$card = $cardStmt->fetch();

// 获取检验卡片
$inspStmt = $db->prepare("SELECT * FROM capp_inspection_card WHERE route_operation_id = ?");
$inspStmt->execute([$operationId]);
$inspection = $inspStmt->fetch();

// 获取工时定额
$quotaStmt = $db->prepare("SELECT * FROM capp_quota_time WHERE route_operation_id = ?");
$quotaStmt->execute([$operationId]);
$quota = $quotaStmt->fetch();

// 获取二维码
$qrStmt = $db->prepare("SELECT * FROM capp_qr_code WHERE code_type = 'operation' AND target_id = ?");
$qrStmt->execute([$operationId]);
$qrCode = $qrStmt->fetch();

// 解析设备/工装/刀具/量具
function getNamesByIds($db, $table, $ids) {
    if (empty($ids)) return [];
    $idArr = array_filter(explode(',', $ids));
    if (empty($idArr)) return [];
    $placeholders = implode(',', array_fill(0, count($idArr), '?'));
    $stmt = $db->prepare("SELECT id, name FROM {$table} WHERE id IN ({$placeholders})");
    $stmt->execute($idArr);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$equipments = getNamesByIds($db, 'capp_equipment', $operation['equipment_ids']);
$toolings = getNamesByIds($db, 'capp_tooling', $operation['tooling_ids']);
$cuttingTools = getNamesByIds($db, 'capp_cutting_tool', $operation['cutting_tool_ids']);
$gauges = getNamesByIds($db, 'capp_gauge', $operation['gauge_ids']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>工序 - <?php echo htmlspecialchars($operation['operation_name']); ?></title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <style>
        .detail-section { margin-bottom: 16px; }
        .detail-section h3 { font-size: 15px; font-weight: 600; margin-bottom: 10px; padding-left: 8px; border-left: 3px solid #1890ff; }
        .detail-card { background: #f8f9fa; border-radius: 8px; padding: 12px; font-size: 13px; line-height: 1.8; }
        .detail-card .label { color: #666; }
        .detail-card .value { color: #333; font-weight: 500; }
        .tag-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
        .tag { background: #e6f7ff; color: #1890ff; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
        .steps-content { background: #fff; border-radius: 8px; padding: 12px; font-size: 13px; line-height: 1.8; border: 1px solid #f0f0f0; }
        .kcp-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .kcp-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="route-detail">
        <div class="route-header">
            <a href="/mobile/route/<?php echo (int)$operation['route_id']; ?>" style="color:#fff; text-decoration:none; font-size:14px;">&larr; 返回路线</a>
            <h2 style="margin-top:8px;"><?php echo htmlspecialchars($operation['operation_name']); ?></h2>
            <div class="route-meta">
                <span>工序 <?php echo htmlspecialchars($operation['operation_no']); ?></span>
                <span><?php echo htmlspecialchars($operation['work_center_name'] ?? '-'); ?></span>
            </div>
        </div>

        <div class="route-info">
            <!-- 基本信息 -->
            <div class="detail-section">
                <h3>基本信息</h3>
                <div class="detail-card">
                    <div><span class="label">所属路线：</span><span class="value"><?php echo htmlspecialchars($operation['route_name']); ?> <?php echo htmlspecialchars($operation['version']); ?></span></div>
                    <div><span class="label">产品：</span><span class="value"><?php echo htmlspecialchars($operation['product_code'] . ' ' . $operation['product_name']); ?></span></div>
                    <div><span class="label">标准工时：</span><span class="value"><?php echo $operation['standard_time'] ? $operation['standard_time'] . ' 分钟' : '-'; ?></span></div>
                    <div><span class="label">前序工序：</span><span class="value"><?php echo htmlspecialchars($operation['predecessor_no'] ?? '无'); ?></span></div>
                    <?php if ($operation['is_key_point']): ?>
                    <div><span class="label">关键工序：</span><span class="value" style="color:#f5222d;">是</span></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 资源 -->
            <div class="detail-section">
                <h3>使用资源</h3>
                <div class="detail-card">
                    <?php if ($equipments): ?>
                    <div><span class="label">设备：</span>
                        <div class="tag-list"><?php foreach ($equipments as $name): ?><span class="tag"><?php echo htmlspecialchars($name); ?></span><?php endforeach; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($toolings): ?>
                    <div style="margin-top:8px;"><span class="label">工装：</span>
                        <div class="tag-list"><?php foreach ($toolings as $name): ?><span class="tag"><?php echo htmlspecialchars($name); ?></span><?php endforeach; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($cuttingTools): ?>
                    <div style="margin-top:8px;"><span class="label">刀具：</span>
                        <div class="tag-list"><?php foreach ($cuttingTools as $name): ?><span class="tag"><?php echo htmlspecialchars($name); ?></span><?php endforeach; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($gauges): ?>
                    <div style="margin-top:8px;"><span class="label">量具：</span>
                        <div class="tag-list"><?php foreach ($gauges as $name): ?><span class="tag"><?php echo htmlspecialchars($name); ?></span><?php endforeach; ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 工时定额 -->
            <?php if ($quota): ?>
            <div class="detail-section">
                <h3>工时定额</h3>
                <div class="detail-card">
                    <div><span class="label">准备时间：</span><span class="value"><?php echo $quota['prepare_time'] ?? '-'; ?> min</span></div>
                    <div><span class="label">加工时间：</span><span class="value"><?php echo $quota['process_time'] ?? '-'; ?> min</span></div>
                    <div><span class="label">辅助时间：</span><span class="value"><?php echo $quota['assist_time'] ?? '-'; ?> min</span></div>
                    <div><span class="label">宽放时间：</span><span class="value"><?php echo $quota['allowance_time'] ?? '-'; ?> min</span></div>
                    <div><span class="label">总工时：</span><span class="value" style="color:#1890ff; font-size:15px;"><?php echo $quota['total_time'] ?? '-'; ?> min</span></div>
                    <div><span class="label">操作人数：</span><span class="value"><?php echo (int)($quota['worker_count'] ?? 1); ?> 人</span></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 工序卡片 -->
            <?php if ($card): ?>
            <div class="detail-section">
                <h3>操作步骤</h3>
                <div class="steps-content">
                    <?php echo nl2br(htmlspecialchars($card['operation_steps'] ?? '暂无操作步骤')); ?>
                </div>
                <?php if ($card['safety_notes']): ?>
                <div style="margin-top:12px; background:#fff1f0; border-radius:8px; padding:12px; font-size:13px; color:#cf1322;">
                    <strong>安全注意事项：</strong><br>
                    <?php echo nl2br(htmlspecialchars($card['safety_notes'])); ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- 关键控制点 -->
            <?php if ($card['kcp_list']): ?>
            <?php $kcpList = json_decode($card['kcp_list'], true) ?? []; ?>
            <?php if ($kcpList): ?>
            <div class="detail-section">
                <h3>关键控制点 (KCP)</h3>
                <div class="detail-card" style="padding:0 12px;">
                    <?php foreach ($kcpList as $kcp): ?>
                    <div class="kcp-item">
                        <span><?php echo htmlspecialchars($kcp['name'] ?? ''); ?></span>
                        <span style="color:#666;"><?php echo htmlspecialchars($kcp['value'] ?? ''); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>

            <!-- 检验卡片 -->
            <?php if ($inspection): ?>
            <div class="detail-section">
                <h3>检验要求</h3>
                <div class="detail-card">
                    <div><span class="label">抽样方案：</span><span class="value"><?php echo htmlspecialchars($inspection['sampling_plan'] ?? '-'); ?></span></div>
                    <?php if ($inspection['inspection_items']): ?>
                    <?php $items = json_decode($inspection['inspection_items'], true) ?? []; ?>
                    <?php if ($items): ?>
                    <div style="margin-top:8px;"><span class="label">检验项目：</span></div>
                    <div class="tag-list">
                        <?php foreach ($items as $item): ?>
                        <span class="tag"><?php echo htmlspecialchars($item['name'] ?? ''); ?>: <?php echo htmlspecialchars($item['standard'] ?? ''); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 二维码 -->
            <?php if ($qrCode): ?>
            <div class="detail-section">
                <h3>工序二维码</h3>
                <div style="text-align:center; padding:12px;">
                    <img src="<?php echo htmlspecialchars($qrCode['qr_image_url']); ?>" alt="二维码" style="max-width:180px; border-radius:8px;">
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
