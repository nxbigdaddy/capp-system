<?php
require_once __DIR__ . '/../../Core/BaseService.php';
require_once __DIR__ . '/../Approval/ApprovalService.php';
require_once __DIR__ . '/../../Utils/QrGenerator.php';

/**
 * 工艺路线服务类
 */
class ProcessRouteService extends BaseService
{
    private $approvalService;
    private $qrGenerator;

    public function __construct()
    {
        parent::__construct();
        $this->approvalService = new ApprovalService();
        $this->qrGenerator = new QrGenerator();
    }

    /**
     * 创建工艺路线
     */
    public function create(array $data, int $userId): int
    {
        $this->beginTransaction();
        try {
            $code = $this->generateCode('RT', 'capp_process_route');

            $stmt = $this->db->prepare("
                INSERT INTO capp_process_route
                (code, product_id, product_code, product_name, name, version, status, description, created_by)
                VALUES (?, ?, ?, ?, ?, 'V1.0', 'drafting', ?, ?)
            ");
            $stmt->execute([
                $code,
                $data['product_id'],
                $data['product_code'],
                $data['product_name'],
                $data['name'],
                $data['description'] ?? null,
                $userId,
            ]);

            $routeId = (int) $this->db->lastInsertId();

            if (!empty($data['operations'])) {
                $this->saveOperations($routeId, $data['operations']);
            }

            $this->commit();

            AuditLog::log('process_route', $routeId, 'create', $userId, null, ['code' => $code]);

            return $routeId;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 更新工艺路线
     */
    public function update(int $routeId, array $data, int $userId): void
    {
        $route = $this->getById($routeId);
        if (!$route) {
            throw new Exception('工艺路线不存在', 404);
        }
        if ($route['status'] !== 'drafting') {
            throw new Exception('只有编制中的工艺路线可以编辑', 400);
        }

        $this->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE capp_process_route
                SET name = ?, description = ?, product_id = ?, product_code = ?, product_name = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['product_id'],
                $data['product_code'],
                $data['product_name'],
                $routeId,
            ]);

            if (isset($data['operations'])) {
                $this->db->prepare("DELETE FROM capp_route_operation WHERE route_id = ?")->execute([$routeId]);
                $this->saveOperations($routeId, $data['operations']);
            }

            $this->commit();

            AuditLog::log('process_route', $routeId, 'update', $userId);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 保存工序明细
     */
    private function saveOperations(int $routeId, array $operations): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO capp_route_operation
            (route_id, operation_no, operation_name, std_operation_id, work_center_id, description,
             equipment_ids, tooling_ids, cutting_tool_ids, gauge_ids, standard_time, sort_order, predecessor_no, is_key_point, parameters)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($operations as $op) {
            $stmt->execute([
                $routeId,
                $op['operation_no'],
                $op['operation_name'],
                $op['std_operation_id'] ?? null,
                $op['work_center_id'] ?? null,
                $op['description'] ?? null,
                isset($op['equipment_ids']) ? implode(',', $op['equipment_ids']) : null,
                isset($op['tooling_ids']) ? implode(',', $op['tooling_ids']) : null,
                isset($op['cutting_tool_ids']) ? implode(',', $op['cutting_tool_ids']) : null,
                isset($op['gauge_ids']) ? implode(',', $op['gauge_ids']) : null,
                $op['standard_time'] ?? null,
                $op['sort_order'] ?? 0,
                $op['predecessor_no'] ?? null,
                $op['is_key_point'] ?? 0,
                isset($op['parameters']) ? json_encode($op['parameters'], JSON_UNESCAPED_UNICODE) : null,
            ]);
        }

        $this->recalculateTotalTime($routeId);
    }

    /**
     * 重新计算总工时
     */
    private function recalculateTotalTime(int $routeId): void
    {
        $stmt = $this->db->prepare("SELECT SUM(standard_time) as total FROM capp_route_operation WHERE route_id = ?");
        $stmt->execute([$routeId]);
        $row = $stmt->fetch();

        $update = $this->db->prepare("UPDATE capp_process_route SET total_time = ? WHERE id = ?");
        $update->execute([$row['total'] ?? 0, $routeId]);
    }

    /**
     * 提交审批
     */
    public function submitForApproval(int $routeId, int $userId): void
    {
        $route = $this->getById($routeId);
        if (!$route || $route['status'] !== 'drafting') {
            throw new Exception('工艺路线状态不正确', 400);
        }

        $this->db->prepare("UPDATE capp_process_route SET status = 'submitted' WHERE id = ?")
            ->execute([$routeId]);

        $this->approvalService->startApproval('process_route', $routeId, $userId);

        AuditLog::log('process_route', $routeId, 'submit', $userId);
    }

    /**
     * 审批通过
     */
    public function approve(int $routeId, int $approverId, ?string $comment = null): void
    {
        $this->beginTransaction();
        try {
            $this->db->prepare("
                UPDATE capp_process_route SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?
            ")->execute([$approverId, $routeId]);

            $this->approvalService->approve('process_route', $routeId, $approverId, $comment);

            $this->commit();

            AuditLog::log('process_route', $routeId, 'approve', $approverId);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 审批驳回
     */
    public function reject(int $routeId, int $approverId, string $comment): void
    {
        $this->beginTransaction();
        try {
            $this->db->prepare("UPDATE capp_process_route SET status = 'drafting' WHERE id = ?")
                ->execute([$routeId]);

            $this->approvalService->reject('process_route', $routeId, $approverId, $comment);

            $this->commit();

            AuditLog::log('process_route', $routeId, 'reject', $approverId);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 发布工艺路线
     */
    public function publish(int $routeId, int $userId): void
    {
        $route = $this->getById($routeId);
        if (!$route || $route['status'] !== 'approved') {
            throw new Exception('工艺路线未审批通过', 400);
        }

        $this->beginTransaction();
        try {
            $this->db->prepare("UPDATE capp_process_route SET is_active = 0 WHERE product_id = ? AND is_active = 1")
                ->execute([$route['product_id']]);

            $this->db->prepare("UPDATE capp_process_route SET status = 'published', is_active = 1, published_at = NOW() WHERE id = ?")
                ->execute([$routeId]);

            $this->generateQrCodes($routeId);

            $this->commit();

            AuditLog::log('process_route', $routeId, 'publish', $userId);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 生成二维码
     */
    private function generateQrCodes(int $routeId): void
    {
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';

        $route = $this->getById($routeId);
        $qrUrl = $this->qrGenerator->generate("{$baseUrl}/mobile/route/{$routeId}");

        $stmt = $this->db->prepare("
            INSERT INTO capp_qr_code (code_type, target_id, target_no, qr_content, qr_image_url, created_at)
            VALUES ('route', ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE qr_content = ?, qr_image_url = ?
        ");
        $stmt->execute([$routeId, $route['code'], "{$baseUrl}/mobile/route/{$routeId}", $qrUrl, "{$baseUrl}/mobile/route/{$routeId}", $qrUrl]);

        $opStmt = $this->db->prepare("SELECT id, operation_no, operation_name FROM capp_route_operation WHERE route_id = ?");
        $opStmt->execute([$routeId]);
        foreach ($opStmt->fetchAll() as $op) {
            $opQrUrl = $this->qrGenerator->generate("{$baseUrl}/mobile/operation/{$op['id']}");
            $stmt->execute([$op['id'], $op['operation_no'], "{$baseUrl}/mobile/operation/{$op['id']}", $opQrUrl, "{$baseUrl}/mobile/operation/{$op['id']}", $opQrUrl]);
        }
    }

    /**
     * 获取工艺路线详情
     */
    public function getById(int $routeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM capp_process_route WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$routeId]);
        $route = $stmt->fetch();
        if (!$route) {
            return null;
        }

        $opStmt = $this->db->prepare("
            SELECT o.*, w.name as work_center_name, s.name as std_operation_name
            FROM capp_route_operation o
            LEFT JOIN capp_work_center w ON o.work_center_id = w.id
            LEFT JOIN capp_standard_operation s ON o.std_operation_id = s.id
            WHERE o.route_id = ?
            ORDER BY o.sort_order, o.operation_no
        ");
        $opStmt->execute([$routeId]);
        $route['operations'] = $opStmt->fetchAll();

        return $route;
    }

    /**
     * 分页查询工艺路线
     */
    public function paginate(array $filters, int $page = 1, int $pageSize = 20): array
    {
        $where = ['r.is_deleted = 0'];
        $params = [];

        if (!empty($filters['product_code'])) {
            $where[] = 'r.product_code LIKE ?';
            $params[] = '%' . $filters['product_code'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'r.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['is_active'])) {
            $where[] = 'r.is_active = ?';
            $params[] = $filters['is_active'];
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM capp_process_route r WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $pageSize;
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as creator_name, au.name as approver_name
            FROM capp_process_route r
            LEFT JOIN capp_user u ON r.created_by = u.id
            LEFT JOIN capp_user au ON r.approved_by = au.id
            WHERE {$whereStr}
            ORDER BY r.id DESC
            LIMIT {$offset}, {$pageSize}
        ");
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pageSize' => $pageSize];
    }

    /**
     * 删除工艺路线
     */
    public function delete(int $routeId, int $userId): void
    {
        $route = $this->getById($routeId);
        if (!$route) {
            throw new Exception('工艺路线不存在', 404);
        }
        if ($route['status'] === 'published') {
            throw new Exception('已发布的工艺路线不能删除', 400);
        }

        $this->db->prepare("UPDATE capp_process_route SET is_deleted = 1 WHERE id = ?")
            ->execute([$routeId]);

        AuditLog::log('process_route', $routeId, 'delete', $userId);
    }
}
