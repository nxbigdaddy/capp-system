<?php
/**
 * 个人中心页 (移动端)
 */
require_once __DIR__ . '/../Core/Database.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header('Location: /mobile');
    exit;
}

$db = Database::get();
$stmt = $db->prepare("SELECT id, name, employee_no, mobile, department, position, avatar FROM capp_user WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /mobile');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>个人中心</title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            color: #fff;
            padding: 32px 20px;
            text-align: center;
        }
        .avatar {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: #fff;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            overflow: hidden;
        }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-name { font-size: 18px; font-weight: 500; }
        .profile-dept { font-size: 13px; opacity: 0.85; margin-top: 4px; }

        .menu-list {
            background: #fff;
            margin-top: 12px;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border-bottom: 1px solid #f5f5f5;
            text-decoration: none;
            color: #333;
        }
        .menu-item:last-child { border-bottom: none; }
        .menu-icon { font-size: 20px; margin-right: 12px; width: 24px; text-align: center; }
        .menu-text { flex: 1; font-size: 15px; }
        .menu-arrow { color: #ccc; font-size: 18px; }
    </style>
</head>
<body>
    <div class="mobile-container">
        <div class="profile-header">
            <a href="/mobile" style="color:#fff; text-decoration:none; font-size:14px; position:absolute; left:16px; top:16px;">&larr; 返回</a>
            <div class="avatar">
                <?php if ($user['avatar']): ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="头像">
                <?php else: ?>
                &#128100;
                <?php endif; ?>
            </div>
            <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
            <div class="profile-dept"><?php echo htmlspecialchars($user['department'] ?? ''); ?> · <?php echo htmlspecialchars($user['position'] ?? ''); ?></div>
        </div>

        <div class="menu-list">
            <div class="menu-item">
                <span class="menu-icon">&#128100;</span>
                <span class="menu-text">工号：<?php echo htmlspecialchars($user['employee_no'] ?? '-'); ?></span>
            </div>
            <div class="menu-item">
                <span class="menu-icon">&#128241;</span>
                <span class="menu-text">手机：<?php echo htmlspecialchars($user['mobile'] ?? '-'); ?></span>
            </div>
        </div>

        <div class="menu-list">
            <a href="/mobile/todos" class="menu-item">
                <span class="menu-icon">&#128203;</span>
                <span class="menu-text">我的待办</span>
                <span class="menu-arrow">&rsaquo;</span>
            </a>
        </div>

        <div style="padding: 20px;">
            <button onclick="logout()" class="btn btn-block" style="background:#fff; color:#f5222d; border:1px solid #ffccc7;">退出登录</button>
        </div>
    </div>

    <script>
    function logout() {
        if (confirm('确定退出登录吗？')) {
            fetch('/api/auth/logout', { method: 'POST' })
                .then(() => location.href = '/mobile');
        }
    }
    </script>
</body>
</html>
