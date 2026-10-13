<?php
require_once __DIR__ . '/../../Core/BaseService.php';

/**
 * 审批流程服务类
 */
class ApprovalService extends BaseService
{
    /**
     * 启动审批流程
     */
    public function startApproval(string $businessType, int $businessId, int $initiatorId): void
    {
        $flow = $this->getActiveFlow($businessType);
        if (!$flow) {
            throw new Exception('未找到有效的审批流程', 500);
        }

        $firstNode = $this->getFirstNode($flow['id']);
        if (!$firstNode) {
            throw new Exception('审批流程未配置节点', 500);
        }

        $stmt = $this->db->prepare("
            INSERT INTO capp_approval_instance
            (flow_id, business_id, business_type, current_node_id, status, initiator_id, created_at)
            VALUES (?, ?, ?, ?, 'pending', ?, NOW())
        ");
        $stmt->execute([$flow['id'], $businessId, $businessType, $firstNode['id'], $initiatorId]);

        $instanceId = (int) $this->db->lastInsertId();

        $this->notifyApprovers($firstNode, $instanceId, $businessType, $businessId);
    }

    /**
     * 审批通过
     */
    public function approve(string $businessType, int $businessId, int $approverId, ?string $comment = null): void
    {
        $instance = $this->getPendingInstance($businessType, $businessId);
        if (!$instance) {
            throw new Exception('没有待审批的实例', 404);
        }

        $this->beginTransaction();
        try {
            $this->db->prepare("
                INSERT INTO capp_approval_record (instance_id, node_id, approver_id, action, comment, created_at)
                VALUES (?, ?, ?, 'approve', ?, NOW())
            ")->execute([$instance['id'], $instance['current_node_id'], $approverId, $comment]);

            $nextNode = $this->getNextNode($instance['flow_id'], $instance['current_node_id']);

            if ($nextNode) {
                $this->db->prepare("UPDATE capp_approval_instance SET current_node_id = ? WHERE id = ?")
                    ->execute([$nextNode['id'], $instance['id']]);
                $this->notifyApprovers($nextNode, $instance['id'], $businessType, $businessId);
            } else {
                $this->db->prepare("
                    UPDATE capp_approval_instance
                    SET status = 'approved', current_node_id = NULL, completed_at = NOW()
                    WHERE id = ?
                ")->execute([$instance['id']]);
            }

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 审批驳回
     */
    public function reject(string $businessType, int $businessId, int $approverId, string $comment): void
    {
        $instance = $this->getPendingInstance($businessType, $businessId);
        if (!$instance) {
            throw new Exception('没有待审批的实例', 404);
        }

        $this->beginTransaction();
        try {
            $this->db->prepare("
                INSERT INTO capp_approval_record (instance_id, node_id, approver_id, action, comment, created_at)
                VALUES (?, ?, ?, 'reject', ?, NOW())
            ")->execute([$instance['id'], $instance['current_node_id'], $approverId, $comment]);

            $this->db->prepare("
                UPDATE capp_approval_instance
                SET status = 'rejected', current_node_id = NULL, completed_at = NOW()
                WHERE id = ?
            ")->execute([$instance['id']]);

            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * 获取待办列表
     */
    public function getTodoList(int $userId, int $page = 1, int $pageSize = 20): array
    {
        $roleIds = $this->getUserRoleIds($userId);
        $rolePlaceholders = implode(',', array_fill(0, count($roleIds), '?'));

        $where = "i.status = 'pending' AND (";
        $where .= "(n.approver_type = 'user' AND n.approver_value = ?)";
        if ($roleIds) {
            $where .= " OR (n.approver_type = 'role' AND n.approver_value IN ({$rolePlaceholders}))";
        }
        $where .= ")";

        $params = array_merge([$userId], $roleIds);

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM capp_approval_instance i
            INNER JOIN capp_approval_node n ON i.current_node_id = n.id
            WHERE {$where}
        ");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $pageSize;
        $stmt = $this->db->prepare("
            SELECT i.*, n.name as node_name, n.type as node_type,
                   r.name as business_name, r.code as business_code
            FROM capp_approval_instance i
            INNER JOIN capp_approval_node n ON i.current_node_id = n.id
            LEFT JOIN capp_process_route r ON i.business_id = r.id AND i.business_type = 'process_route'
            WHERE {$where}
            ORDER BY i.created_at DESC
            LIMIT {$offset}, {$pageSize}
        ");
        $stmt->execute($params);

        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pageSize' => $pageSize];
    }

    /**
     * 获取审批历史
     */
    public function getApprovalHistory(int $businessId, string $businessType): array
    {
        $stmt = $this->db->prepare("
            SELECT ar.*, u.name as approver_name, n.name as node_name
            FROM capp_approval_record ar
            INNER JOIN capp_approval_instance ai ON ar.instance_id = ai.id
            LEFT JOIN capp_user u ON ar.approver_id = u.id
            LEFT JOIN capp_approval_node n ON ar.node_id = n.id
            WHERE ai.business_id = ? AND ai.business_type = ?
            ORDER BY ar.created_at DESC
        ");
        $stmt->execute([$businessId, $businessType]);
        return $stmt->fetchAll();
    }

    /**
     * 获取有效的审批流程
     */
    private function getActiveFlow(string $businessType): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM capp_approval_flow WHERE business_type = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$businessType]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * 获取第一个节点
     */
    private function getFirstNode(int $flowId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM capp_approval_node WHERE flow_id = ? ORDER BY sort_order LIMIT 1");
        $stmt->execute([$flowId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * 获取下一个节点
     */
    private function getNextNode(int $flowId, int $currentNodeId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM capp_approval_node
            WHERE flow_id = ? AND sort_order > (SELECT sort_order FROM capp_approval_node WHERE id = ?)
            ORDER BY sort_order LIMIT 1
        ");
        $stmt->execute([$flowId, $currentNodeId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * 获取待审批实例
     */
    private function getPendingInstance(string $businessType, int $businessId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM capp_approval_instance
            WHERE business_type = ? AND business_id = ? AND status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([$businessType, $businessId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * 获取用户角色ID列表
     */
    private function getUserRoleIds(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT role_id FROM capp_user_role WHERE user_id = ?");
        $stmt->execute([$userId]);
        return array_column($stmt->fetchAll(), 'role_id');
    }

    /**
     * 通知审批人
     */
    private function notifyApprovers(array $node, int $instanceId, string $businessType, int $businessId): void
    {
        $userIds = [];

        if ($node['approver_type'] === 'user') {
            $userIds[] = (int) $node['approver_value'];
        } elseif ($node['approver_type'] === 'role') {
            $stmt = $this->db->prepare("SELECT user_id FROM capp_user_role WHERE role_id = ?");
            $stmt->execute([$node['approver_value']]);
            $userIds = array_column($stmt->fetchAll(), 'user_id');
        }

        foreach ($userIds as $uid) {
            $this->db->prepare("
                INSERT INTO capp_notification (user_id, title, content, type, business_type, business_id, created_at)
                VALUES (?, '待审批通知', '您有一条新的待审批任务', 'approval', ?, ?, NOW())
            ")->execute([$uid, $businessType, $businessId]);
        }
    }
}
