<?php
require_once __DIR__ . '/../../Core/BaseService.php';

/**
 * 工序卡片服务类
 */
class OperationCardService extends BaseService
{
    /**
     * 创建/更新工序卡片
     */
    public function saveCard(int $routeOperationId, array $data, int $userId): void
    {
        $exists = $this->getByRouteOperationId($routeOperationId);

        if ($exists) {
            $stmt = $this->db->prepare("
                UPDATE capp_operation_card
                SET card_no = ?, operation_steps = ?, drawing_refs = ?, image_refs = ?,
                    safety_notes = ?, kcp_list = ?, process_params = ?, updated_at = NOW()
                WHERE route_operation_id = ?
            ");
            $stmt->execute([
                $data['card_no'] ?? null,
                $data['operation_steps'] ?? null,
                $data['drawing_refs'] ?? null,
                $data['image_refs'] ?? null,
                $data['safety_notes'] ?? null,
                isset($data['kcp_list']) ? json_encode($data['kcp_list'], JSON_UNESCAPED_UNICODE) : null,
                isset($data['process_params']) ? json_encode($data['process_params'], JSON_UNESCAPED_UNICODE) : null,
                $routeOperationId,
            ]);
            AuditLog::log('operation_card', $exists['id'], 'update', $userId);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO capp_operation_card
                (route_operation_id, card_no, operation_steps, drawing_refs, image_refs, safety_notes, kcp_list, process_params, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $routeOperationId,
                $data['card_no'] ?? null,
                $data['operation_steps'] ?? null,
                $data['drawing_refs'] ?? null,
                $data['image_refs'] ?? null,
                $data['safety_notes'] ?? null,
                isset($data['kcp_list']) ? json_encode($data['kcp_list'], JSON_UNESCAPED_UNICODE) : null,
                isset($data['process_params']) ? json_encode($data['process_params'], JSON_UNESCAPED_UNICODE) : null,
            ]);
            AuditLog::log('operation_card', (int)$this->db->lastInsertId(), 'create', $userId);
        }
    }

    /**
     * 获取工序卡片
     */
    public function getByRouteOperationId(int $routeOperationId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM capp_operation_card WHERE route_operation_id = ?");
        $stmt->execute([$routeOperationId]);
        $row = $stmt->fetch();
        if ($row) {
            $row['kcp_list'] = $row['kcp_list'] ? json_decode($row['kcp_list'], true) : [];
            $row['process_params'] = $row['process_params'] ? json_decode($row['process_params'], true) : [];
        }
        return $row ?: null;
    }

    /**
     * 获取工艺路线下所有工序卡片
     */
    public function getCardsByRouteId(int $routeId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, o.operation_no, o.operation_name, o.work_center_id, w.name as work_center_name
            FROM capp_operation_card c
            INNER JOIN capp_route_operation o ON c.route_operation_id = o.id
            LEFT JOIN capp_work_center w ON o.work_center_id = w.id
            WHERE o.route_id = ?
            ORDER BY o.sort_order, o.operation_no
        ");
        $stmt->execute([$routeId]);
        return $stmt->fetchAll();
    }
}
