<?php
/**
 * 待办列表页 (移动端)
 */
require_once __DIR__ . '/../Core/Database.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: /mobile');
    exit;
}

$db = Database::get();

// 获取用户角色
$roleStmt = $db->prepare("SELECT role_id FROM capp_user_role WHERE user_id = ?");
$roleStmt->execute([$userId]);
$roleIds = array_column($roleStmt->fetchAll(), 'role_id');
$rolePlaceholders = implode(',', array_fill(0, count($roleIds), '?'));

// 获取待办列表
$where = "i.status = 'pending' AND (";
$where .= "(n.approver_type = 'user' AND n.approver_value = ?)";
if ($roleIds) {
    $where .= " OR (n.approver_type = 'role' AND n.approver_value IN ({$rolePlaceholders}))";
}
$where .= ")";

$params = array_merge([$userId], $roleIds);

$stmt = $db->prepare("
    SELECT i.*, n.name as node_name, n.type as node_type,
           r.name as business_name, r.code as business_code, r.version
    FROM capp_approval_instance i
    INNER JOIN capp_approval_node n ON i.current_node_id = n.id
    LEFT JOIN capp_process_route r ON i.business_id = r.id AND i.business_type = 'process_route'
    WHERE {$where}
    ORDER BY i.created_at DESC
");
$stmt->execute($params);
$todos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>待办审批</title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <style>
        .todo-list { padding: 12px; }
        .todo-item {
            background: #fff;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .todo-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .todo-title { font-size: 15px; font-weight: 500; color: #333; }
        .todo-type { font-size: 12px; color: #1890ff; background: #e6f7ff; padding: 2px 8px; border-radius: 4px; }
        .todo-info { font-size: 13px; color: #666; line-height: 1.8; }
        .todo-actions { display: flex; gap: 10px; margin-top: 12px; }
        .btn-sm { flex: 1; padding: 8px; border-radius: 6px; font-size: 13px; border: none; cursor: pointer; text-align: center; }
        .btn-approve { background: #52c41a; color: #fff; }
        .btn-reject { background: #fff; color: #f5222d; border: 1px solid #ffccc7; }
        .todo-empty { text-align: center; padding: 60px 20px; color: #999; }
    </style>
</head>
<body>
    <div class="mobile-container">
        <div class="mobile-header" style="padding:16px 20px;">
            <a href="/mobile" style="color:#fff; text-decoration:none; font-size:14px;">&larr; 返回</a>
            <h1 style="margin-top:8px; font-size:18px;">待办审批 (<?php echo count($todos); ?>)</h1>
        </div>

        <div class="mobile-content" style="padding:0;">
            <?php if (empty($todos)): ?>
            <div class="todo-empty">
                <div style="font-size:48px; margin-bottom:12px;">&#127881;</div>
                <div>暂无待办事项</div>
            </div>
            <?php else: ?>
            <div class="todo-list">
                <?php foreach ($todos as $todo): ?>
                <div class="todo-item">
                    <div class="todo-header">
                        <span class="todo-title"><?php echo htmlspecialchars($todo['business_name'] ?? '未知'); ?></span>
                        <span class="todo-type"><?php echo htmlspecialchars($todo['node_name']); ?></span>
                    </div>
                    <div class="todo-info">
                        <div>编码：<?php echo htmlspecialchars($todo['business_code'] ?? '-'); ?></div>
                        <div>版本：<?php echo htmlspecialchars($todo['version'] ?? '-'); ?></div>
                        <div>提交时间：<?php echo date('Y-m-d H:i', strtotime($todo['created_at'])); ?></div>
                    </div>
                    <div class="todo-actions">
                        <button class="btn-sm btn-reject" onclick="doReject(<?php echo (int)$todo['business_id']; ?>, '<?php echo $todo['business_type']; ?>')">驳回</button>
                        <button class="btn-sm btn-approve" onclick="doApprove(<?php echo (int)$todo['business_id']; ?>, '<?php echo $todo['business_type']; ?>')">通过</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function doApprove(businessId, businessType) {
        const comment = prompt('审批意见（可选）：');
        if (comment === null) return;

        fetch('/api/process-routes/' + businessId + '/approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment: comment })
        })
        .then(r => r.json())
        .then(res => {
            if (res.code === 0) {
                alert('审批通过');
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }

    function doReject(businessId, businessType) {
        const comment = prompt('驳回原因（必填）：');
        if (!comment) return;

        fetch('/api/process-routes/' + businessId + '/reject', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment: comment })
        })
        .then(r => r.json())
        .then(res => {
            if (res.code === 0) {
                alert('已驳回');
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }
    </script>
</body>
</html>
