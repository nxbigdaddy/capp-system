# CAPP系统 - MES系统对接设计

> 版本：V1.0 | 日期：2026-06-07 | 状态：初稿

---

## 一、对接概述

### 1.1 对接目标

MES（制造执行系统）是车间现场执行的核心系统，CAPP与MES对接实现**工艺数据下发**和**执行数据回传**的双向闭环。

### 1.2 对接方式

MES系统通常采用标准WebService或RESTful接口方式，CAPP作为服务端提供API供MES调用，同时CAPP也作为客户端调用MES接口。

| 方向 | 数据内容 | 接口方式 |
|------|---------|---------|
| CAPP → MES | 工艺路线、工序信息、作业指导书、工艺变更通知 | CAPP暴露API，MES定时拉取 |
| MES → CAPP | 报工数据(工时/数量)、质量检验数据、设备状态 | MES主动推送或CAPP定时拉取 |

### 1.3 数据流向

```
CAPP                                    MES
┌────────────────┐                 ┌────────────────┐
│ 工艺路线         │────下发────────▶│ 车间工位终端     │
│ 工序卡片/SOP     │────下发────────▶│ 电子看板        │
│ 工艺变更通知      │────推送────────▶│ 版本管理器      │
│                 │                 │                │
│ 工时统计对比      │◀───报工回传────│ 报工终端        │
│ 质量数据分析      │◀───质量回传────│ 检验终端        │
│ 设备利用率       │◀───设备状态────│ 设备采集        │
└────────────────┘                 └────────────────┘
```

---

## 二、MES对接配置

```php
// config/mes.php
return [
    'api' => [
        'base_url' => env('MES_API_URL', 'http://mes-server/api'),
        'app_key'  => env('MES_APP_KEY'),
        'secret'   => env('MES_SECRET'),
        'timeout'  => 30,
    ],
    
    // CAPP对外暴露API (供MES调用)
    'endpoint' => [
        'route_pull'     => '/api/mes/route/pull',       // MES拉取工艺路线
        'sop_download'   => '/api/mes/sop/download',     // MES下载作业指导书
        'change_check'   => '/api/mes/change/check',     // MES检查工艺变更
        'version_verify' => '/api/mes/version/verify',   // MES验证版本有效性
        'report_receive' => '/api/mes/report/receive',   // CAPP接收报工数据
        'quality_receive'=> '/api/mes/quality/receive',  // CAPP接收质量数据
    ],
];
```

---

## 三、工艺数据下发 (CAPP → MES)

### 3.1 工艺路线下发

MES通过定时任务拉取已发布的最新工艺路线：

```php
// src/Modules/ProcessRoute/Controllers/Api/MesRouteController.php

class MesRouteController extends Controller {
    /**
     * MES拉取工艺路线 (GET /api/mes/route/pull)
     * 参数: ?since=2026-06-01 00:00:00 (增量拉取) 或 ?route_id=xxx (单条拉取)
     */
    public function pull() {
        $since = $_GET['since'] ?? null;
        $routeId = $_GET['route_id'] ?? null;
        
        $routeService = new ProcessRouteService();
        
        if ($routeId) {
            // 单条拉取
            $routes = [$routeService->getFullRouteById($routeId)];
        } else {
            // 增量拉取（基于发布时间）
            $routes = $routeService->getPublishedRoutesSince($since);
        }
        
        // 格式化为MES需要的结构
        $result = [];
        foreach ($routes as $route) {
            $result[] = $this->formatRouteForMes($route);
        }
        
        return $this->json([
            'code' => 0,
            'data' => [
                'routes'     => $result,
                'total'      => count($result),
                'pulled_at'  => date('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * 格式化工艺路线为MES标准格式
     */
    private function formatRouteForMes($route) {
        return [
            'route_id'        => $route['id'],
            'route_code'      => $route['code'],
            'route_version'   => $route['version'],
            'product_code'    => $route['product_code'],
            'product_name'    => $route['product_name'],
            'drawing_no'      => $route['drawing_no'],
            'operations'      => array_map(function($op) {
                return [
                    'operation_no'      => $op['operation_no'],
                    'operation_name'    => $op['operation_name'],
                    'work_center_code'  => $op['work_center_code'],
                    'work_center_name'  => $op['work_center_name'],
                    'description'       => $op['description'],
                    'standard_time'     => $op['standard_time'],     // 分钟
                    'equipment_codes'   => $op['equipment_codes'],
                    'tooling_codes'     => $op['tooling_codes'],
                    'parameters'        => $op['parameters'],         // JSON工艺参数
                    'is_key_point'      => $op['is_key_point'],
                    'has_sop'           => !empty($op['operation_steps']),
                    'sop_url'           => $op['sop_url'] ?? null,
                    'inspection_card_url'=> $op['inspection_card_url'] ?? null,
                ];
            }, $route['operations']),
            'total_standard_time' => $route['total_standard_time'],
            'published_at'        => $route['published_at'],
        ];
    }
}
```

### 3.2 作业指导书(SOP)下载

```php
/**
 * MES下载作业指导书文件 (GET /api/mes/sop/download)
 * 参数: ?operation_id=xxx&format=pdf
 */
public function downloadSop() {
    $operationId = $_GET['operation_id'] ?? 0;
    $format      = $_GET['format'] ?? 'pdf';
    
    $sopService = new SopService();
    $sop = $sopService->getByOperationId($operationId);
    
    if (!$sop) {
        return $this->json(['code' => 1, 'message' => '作业指导书不存在']);
    }
    
    // 生成PDF文件
    if ($format === 'pdf') {
        $pdfPath = $sopService->generatePdf($operationId);
        $downloadUrl = ASSETS_URL . '/exports/' . basename($pdfPath);
        return $this->json([
            'code' => 0,
            'data' => ['download_url' => $downloadUrl, 'version' => $sop['version']]
        ]);
    }
    
    // 返回JSON结构化数据
    return $this->json([
        'code' => 0,
        'data' => [
            'operation_steps' => $sop['operation_steps'],
            'safety_notes'    => $sop['safety_notes'],
            'kcp_list'        => $sop['kcp_list'],
            'image_urls'      => $sop['image_urls'],
        ]
    ]);
}
```

### 3.3 工艺变更通知推送

CAPP主动向MES推送工艺变更通知：

```php
// src/Integration/MES/MesClient.php

class MesClient {
    /**
     * 推送工艺变更通知到MES
     */
    public function notifyProcessChange($changeNotice) {
        $payload = [
            'change_notice_no'  => $changeNotice['ecn_no'],
            'change_type'       => $changeNotice['change_type'],  // route/sop/inspection
            'product_code'      => $changeNotice['product_code'],
            'route_code'        => $changeNotice['route_code'],
            'old_version'       => $changeNotice['old_version'],
            'new_version'       => $changeNotice['new_version'],
            'change_summary'    => $changeNotice['change_summary'],
            'affected_operations'=> $changeNotice['affected_operations'],
            'effective_date'    => $changeNotice['effective_date'],
            'notified_at'       => date('Y-m-d H:i:s'),
        ];
        
        $response = $this->callMesApi('POST', '/change/notify', $payload);
        
        // 记录通知日志
        $this->logChangeNotification($changeNotice['ecn_no'], $response);
        
        return $response;
    }

    /**
     * 调用MES API
     */
    private function callMesApi($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $timestamp = time();
        $signature = $this->generateSignature($timestamp, $method, $endpoint, $data);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-App-Key: ' . $this->appKey,
            'X-Timestamp: ' . $timestamp,
            'X-Signature: ' . $signature,
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("MES API调用失败: HTTP {$httpCode}");
        }
        
        return json_decode($result, true);
    }
    
    private function generateSignature($timestamp, $method, $endpoint, $data) {
        $str = $this->appKey . $method . $endpoint . $timestamp;
        if ($data) $str .= json_encode($data);
        return hash_hmac('sha256', $str, $this->secret);
    }
}
```

---

## 四、执行数据回传 (MES → CAPP)

### 4.1 报工数据接收

MES将每道工序的实际执行数据推送给CAPP：

```php
// src/Modules/ProcessRoute/Controllers/Api/MesReportController.php

class MesReportController extends Controller {
    /**
     * 接收MES报工数据 (POST /api/mes/report/receive)
     */
    public function receiveWorkReport() {
        $input = $this->getJsonBody();
        
        // 数据校验
        $validator = new Validator($input, [
            'route_id'         => 'required|int',
            'operation_no'     => 'required',
            'worker_id'        => 'required',
            'completed_qty'    => 'required|int|min:1',
            'actual_hours'     => 'required|numeric|min:0',
            'report_time'      => 'required|datetime',
        ]);
        
        if ($validator->fails()) {
            return $this->json(['code' => 1, 'message' => '参数验证失败', 'errors' => $validator->errors()]);
        }
        
        // 写入报工记录
        $reportService = new WorkReportService();
        $reportId = $reportService->create([
            'route_id'         => $input['route_id'],
            'operation_no'     => $input['operation_no'],
            'worker_id'        => $input['worker_id'],
            'worker_name'      => $input['worker_name'] ?? '',
            'completed_qty'    => $input['completed_qty'],
            'defect_qty'       => $input['defect_qty'] ?? 0,
            'actual_hours'     => $input['actual_hours'],
            'equipment_code'   => $input['equipment_code'] ?? '',
            'work_order_no'    => $input['work_order_no'] ?? '',
            'report_time'      => $input['report_time'],
            'remark'           => $input['remark'] ?? '',
        ]);
        
        // 更新工艺路线的实际工时(累计)
        $this->updateActualTime($input['route_id'], $input['operation_no'], $input['actual_hours']);
        
        return $this->json(['code' => 0, 'message' => '报工接收成功', 'data' => ['report_id' => $reportId]]);
    }

    /**
     * 接收批量报工数据
     */
    public function receiveBatchWorkReport() {
        $input = $this->getJsonBody();
        
        if (empty($input['reports']) || !is_array($input['reports'])) {
            return $this->json(['code' => 1, 'message' => '报表数据为空']);
        }
        
        $results = ['success' => 0, 'fail' => 0, 'errors' => []];
        $reportService = new WorkReportService();
        
        foreach ($input['reports'] as $report) {
            try {
                $reportService->createBatch($report);
                $results['success']++;
            } catch (Exception $e) {
                $results['fail']++;
                $results['errors'][] = [
                    'route_id'     => $report['route_id'],
                    'operation_no' => $report['operation_no'],
                    'error'        => $e->getMessage()
                ];
            }
        }
        
        return $this->json(['code' => 0, 'data' => $results]);
    }
}
```

### 4.2 质量数据接收

```php
/**
 * 接收MES质量检验数据 (POST /api/mes/quality/receive)
 */
public function receiveQualityData() {
    $input = $this->getJsonBody();

    $validator = new Validator($input, [
        'route_id'         => 'required|int',
        'operation_no'     => 'required',
        'inspection_result'=> 'required',   // pass/fail/conditional_pass
        'inspector_id'     => 'required',
        'inspection_time'  => 'required|datetime',
    ]);
    
    if ($validator->fails()) {
        return $this->json(['code' => 1, 'message' => '参数验证失败', 'errors' => $validator->errors()]);
    }
    
    $qualityService = new QualityService();
    $recordId = $qualityService->create([
        'route_id'           => $input['route_id'],
        'operation_no'       => $input['operation_no'],
        'inspection_item'    => $input['inspection_item'] ?? '',
        'inspection_standard'=> $input['inspection_standard'] ?? '',
        'measured_value'     => $input['measured_value'] ?? null,
        'tolerance_upper'    => $input['tolerance_upper'] ?? null,
        'tolerance_lower'    => $input['tolerance_lower'] ?? null,
        'inspection_result'  => $input['inspection_result'],
        'defect_type'        => $input['defect_type'] ?? '',
        'defect_description' => $input['defect_description'] ?? '',
        'inspector_id'       => $input['inspector_id'],
        'inspector_name'     => $input['inspector_name'] ?? '',
        'inspection_time'    => $input['inspection_time'],
    ]);
    
    return $this->json(['code' => 0, 'message' => '质量数据接收成功', 'data' => ['record_id' => $recordId]]);
}
```

---

## 五、版本验证机制

MES在执行前需要验证当前使用的工艺版本是否为最新有效版本：

```php
/**
 * MES验证工艺版本有效性 (GET /api/mes/version/verify)
 * 参数: ?route_id=xxx&version=V1.0
 */
public function verifyVersion() {
    $routeId = $_GET['route_id'] ?? 0;
    $version = $_GET['version'] ?? '';
    
    $routeService = new ProcessRouteService();
    $currentRoute = $routeService->getById($routeId);
    
    if (!$currentRoute) {
        return $this->json(['code' => 1, 'message' => '工艺路线不存在']);
    }
    
    $isLatest = ($currentRoute['version'] === $version && $currentRoute['status'] === 'published');
    $hasNewer = !$isLatest && $currentRoute['status'] === 'published';
    
    return $this->json([
        'code' => 0,
        'data' => [
            'is_latest'     => $isLatest,
            'has_newer'     => $hasNewer,
            'current_version'=> $currentRoute['version'],
            'checked_version'=> $version,
            'route_status'   => $currentRoute['status'],
            'warn_message'   => $hasNewer ? "工艺路线已有新版本 {$currentRoute['version']}，请更新" : null,
        ]
    ]);
}
```

---

## 六、MES报工数据表

```sql
CREATE TABLE capp_work_report (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    operation_no VARCHAR(20) NOT NULL,
    worker_id VARCHAR(50) NOT NULL,
    worker_name VARCHAR(50),
    completed_qty INT NOT NULL DEFAULT 0,
    defect_qty INT DEFAULT 0,
    actual_hours DECIMAL(10,2) NOT NULL,
    equipment_code VARCHAR(50),
    work_order_no VARCHAR(50),
    report_time DATETIME NOT NULL,
    remark TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_route_operation (route_id, operation_no),
    INDEX idx_report_time (report_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE capp_quality_record (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_id INT NOT NULL,
    operation_no VARCHAR(20) NOT NULL,
    inspection_item VARCHAR(200),
    inspection_standard TEXT,
    measured_value DECIMAL(10,4),
    tolerance_upper DECIMAL(10,4),
    tolerance_lower DECIMAL(10,4),
    inspection_result VARCHAR(20) NOT NULL,
    defect_type VARCHAR(50),
    defect_description TEXT,
    inspector_id VARCHAR(50) NOT NULL,
    inspector_name VARCHAR(50),
    inspection_time DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_route_operation (route_id, operation_no),
    INDEX idx_result (inspection_result)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
