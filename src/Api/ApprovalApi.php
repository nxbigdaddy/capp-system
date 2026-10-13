<?php
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Modules/Approval/ApprovalService.php';

/**
 * 审批流程 API 接口
 */
class ApprovalApi
{
    private $service;
    private $currentUserId;

    public function __construct()
    {
        $this->service = new ApprovalService();
        $this->currentUserId = (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * GET /api/approvals/todo
     * 获取待办列表
     */
    public function todo(): void
    {
        $page = (int) ($_GET['page'] ?? 1);
        $pageSize = (int) ($_GET['pageSize'] ?? 20);

        $result = $this->service->getTodoList($this->currentUserId, $page, $pageSize);
        Response::paginate($result['items'], $result['total'], $result['page'], $result['pageSize']);
    }

    /**
     * GET /api/approvals/history
     * 获取审批历史
     */
    public function history(): void
    {
        $businessId = (int) ($_GET['business_id'] ?? 0);
        $businessType = $_GET['business_type'] ?? '';

        if (!$businessId || !$businessType) {
            Response::error('business_id 和 business_type 必填', 400);
        }

        $history = $this->service->getApprovalHistory($businessId, $businessType);
        Response::success($history);
    }
}
