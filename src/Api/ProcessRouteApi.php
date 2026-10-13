<?php
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Modules/Process/ProcessRouteService.php';

/**
 * 工艺路线 API 接口
 */
class ProcessRouteApi
{
    private $service;
    private $currentUserId;

    public function __construct()
    {
        $this->service = new ProcessRouteService();
        $this->currentUserId = (int) ($_SESSION['user_id'] ?? 0);
    }

    /**
     * GET /api/process-routes
     * 查询工艺路线列表
     */
    public function index(): void
    {
        $filters = [
            'product_code' => $_GET['product_code'] ?? null,
            'status'       => $_GET['status'] ?? null,
            'is_active'    => $_GET['is_active'] ?? null,
        ];
        $page = (int) ($_GET['page'] ?? 1);
        $pageSize = (int) ($_GET['pageSize'] ?? 20);

        $result = $this->service->paginate(array_filter($filters), $page, $pageSize);
        Response::paginate($result['items'], $result['total'], $result['page'], $result['pageSize']);
    }

    /**
     * GET /api/process-routes/{id}
     * 获取工艺路线详情
     */
    public function show(int $id): void
    {
        $route = $this->service->getById($id);
        if (!$route) {
            Response::error('工艺路线不存在', 404);
        }
        Response::success($route);
    }

    /**
     * POST /api/process-routes
     * 创建工艺路线
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['product_id']) || empty($data['name'])) {
            Response::error('产品ID和路线名称为必填项', 400);
        }

        try {
            $routeId = $this->service->create($data, $this->currentUserId);
            Response::success(['id' => $routeId], '创建成功');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * PUT /api/process-routes/{id}
     * 更新工艺路线
     */
    public function update(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $this->service->update($id, $data, $this->currentUserId);
            Response::success(null, '更新成功');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * DELETE /api/process-routes/{id}
     * 删除工艺路线
     */
    public function destroy(int $id): void
    {
        try {
            $this->service->delete($id, $this->currentUserId);
            Response::success(null, '删除成功');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/process-routes/{id}/submit
     * 提交审批
     */
    public function submit(int $id): void
    {
        try {
            $this->service->submitForApproval($id, $this->currentUserId);
            Response::success(null, '提交成功');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/process-routes/{id}/approve
     * 审批通过
     */
    public function approve(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $this->service->approve($id, $this->currentUserId, $data['comment'] ?? null);
            Response::success(null, '审批通过');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/process-routes/{id}/reject
     * 审批驳回
     */
    public function reject(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['comment'])) {
            Response::error('驳回原因必填', 400);
        }
        try {
            $this->service->reject($id, $this->currentUserId, $data['comment']);
            Response::success(null, '已驳回');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * POST /api/process-routes/{id}/publish
     * 发布工艺路线
     */
    public function publish(int $id): void
    {
        try {
            $this->service->publish($id, $this->currentUserId);
            Response::success(null, '发布成功');
        } catch (Exception $e) {
            Response::error($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
