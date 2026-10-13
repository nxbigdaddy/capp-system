<?php
/**
 * 审计日志工具类
 */
class AuditLog
{
    /**
     * 记录操作日志
     */
    public static function log(string $module, int $targetId, string $action, int $userId = null, ?string $userName = null, array $extra = []): void
    {
        try {
            $db = Database::get();

            $data = [
                'user_id'     => $userId,
                'user_name'   => $userName,
                'module'      => $module,
                'action'      => $action,
                'target_type' => $extra['target_type'] ?? $module,
                'target_id'   => $targetId,
                'description' => $extra['description'] ?? null,
                'before_data' => isset($extra['before']) ? json_encode($extra['before'], JSON_UNESCAPED_UNICODE) : null,
                'after_data'  => isset($extra['after']) ? json_encode($extra['after'], JSON_UNESCAPED_UNICODE) : null,
                'ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ];

            $stmt = $db->prepare("
                INSERT INTO capp_audit_log
                (user_id, user_name, module, action, target_type, target_id, description, before_data, after_data, ip, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $data['user_id'], $data['user_name'], $data['module'], $data['action'],
                $data['target_type'], $data['target_id'], $data['description'],
                $data['before_data'], $data['after_data'], $data['ip'], $data['user_agent'],
            ]);
        } catch (Exception $e) {
            error_log('AuditLog error: ' . $e->getMessage());
        }
    }
}
