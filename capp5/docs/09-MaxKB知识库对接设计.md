# CAPP系统 - MaxKB知识库对接设计

> 版本：V1.0 | 日期：2026-06-07 | 状态：初稿

---

## 一、对接概述

### 1.1 MaxKB简介

MaxKB专业版是企业级RAG（检索增强生成）知识库平台，提供知识库管理和智能体服务。CAPP系统与MaxKB深度集成，实现：
- **工艺知识沉淀与检索**：将工艺知识结构化存储到知识库，提供语义检索
- **AI智能问答**：通过MaxKB智能体实现工艺数据问答
- **智能推荐**：基于知识库实现相似工艺推荐

### 1.2 对接方式

MaxKB提供标准RESTful API，CAPP通过HTTP客户端调用：

| 功能 | MaxKB接口 | 说明 |
|------|-----------|------|
| 知识库检索 | POST /api/v1/dataset/{id}/hit_test | 语义检索知识 |
| 应用对话 | POST /api/v1/application/{id}/chat | 智能体对话 |
| 文档管理 | POST /api/v1/dataset/{id}/document | 知识入库 |
| 知识库列表 | GET /api/v1/dataset | 获取知识库列表 |

---

## 二、配置与客户端

```php
// config/maxkb.php
return [
    'api' => [
        'base_url'  => env('MAXKB_API_URL', 'http://maxkb-server:8080'),
        'api_key'   => env('MAXKB_API_KEY'),
        'timeout'   => 60,
    ],
    
    // 知识库配置
    'datasets' => [
        'process_knowledge' => [    // 工艺知识库
            'id'   => env('MAXKB_DATASET_PROCESS'),
            'name' => '工艺知识库',
        ],
        'product_manual' => [       // 产品手册库
            'id'   => env('MAXKB_DATASET_PRODUCT'),
            'name' => '产品手册库',
        ],
        'equipment_params' => [     // 设备参数库
            'id'   => env('MAXKB_DATASET_EQUIPMENT'),
            'name' => '设备参数库',
        ],
        'standard_library' => [     // 标准规范库
            'id'   => env('MAXKB_DATASET_STANDARD'),
            'name' => '标准规范库',
        ],
    ],
    
    // 智能体配置
    'applications' => [
        'process_assistant' => [    // 工艺助手智能体
            'id'   => env('MAXKB_APP_ASSISTANT'),
            'name' => '工艺助手',
        ],
        'process_decision' => [     // 工艺决策智能体
            'id'   => env('MAXKB_APP_DECISION'),
            'name' => '工艺决策',
        ],
    ],
    
    // 同步配置
    'sync' => [
        'auto_sync_on_publish' => true,  // 工艺发布时自动入库
        'sync_cron' => '0 4 * * *',      // 每日凌晨4点全量同步
    ],
];
```

### 2.2 客户端封装

```php
// src/Integration/MaxKB/MaxKbClient.php

class MaxKbClient {
    private $baseUrl;
    private $apiKey;
    private $timeout;

    public function __construct($config) {
        $this->baseUrl = rtrim($config['base_url'], '/');
        $this->apiKey  = YOUR_SECRET'api_key'];
        $this->timeout = $config['timeout'] ?? 60;
    }

    /**
     * 语义检索知识库
     */
    public function searchKnowledge($datasetId, $query, $topK = 5) {
        $url = "{$this->baseUrl}/api/v1/dataset/{$datasetId}/hit_test";
        
        $payload = [
            'query'          => $query,
            'top_k'          => $topK,
            'similarity_threshold' => 0.6,
        ];
        
        $response = $this->httpPost($url, $payload);
        
        return array_map(function($item) {
            return [
                'content'   => $item['content'],
                'score'     => $item['similarity'],
                'source'    => $item['dataset_name'] ?? '',
                'title'     => $item['title'] ?? '',
            ];
        }, $response['data'] ?? []);
    }

    /**
     * 智能体对话
     */
    public function chatWithAgent($applicationId, $message, $history = [], $parameters = []) {
        $url = "{$this->baseUrl}/api/v1/application/{$applicationId}/chat";
        
        $payload = [
            'message'    => $message,
            'history'    => $history,
            'stream'     => false,
            'parameters' => array_merge([
                'temperature' => 0.3,  // 低温度保证专业准确性
            ], $parameters),
        ];
        
        return $this->httpPost($url, $payload);
    }

    /**
     * 知识入库 - 添加文档
     */
    public function addDocument($datasetId, $document) {
        $url = "{$this->baseUrl}/api/v1/dataset/{$datasetId}/document";
        
        $payload = [
            'name'    => $document['name'],
            'content' => $document['content'],
            'type'    => $document['type'] ?? 'TEXT',  // TEXT/HTML/MARKDOWN
            'meta'    => $document['meta'] ?? [],
        ];
        
        return $this->httpPost($url, $payload);
    }

    /**
     * 批量知识入库
     */
    public function batchAddDocuments($datasetId, array $documents) {
        $results = ['success' => 0, 'fail' => 0];
        foreach ($documents as $doc) {
            try {
                $this->addDocument($datasetId, $doc);
                $results['success']++;
            } catch (Exception $e) {
                $results['fail']++;
                Logger::error('MaxKB入库失败', ['doc' => $doc['name'], 'error' => $e->getMessage()]);
            }
        }
        return $results;
    }

    /**
     * 获取知识库列表
     */
    public function listDatasets() {
        $url = "{$this->baseUrl}/api/v1/dataset";
        return $this->httpGet($url);
    }

    private function httpPost($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("MaxKB API调用失败: HTTP {$httpCode}, Body: {$result}");
        }
        
        return json_decode($result, true);
    }

    private function httpGet($url, $params = []) {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
        ]);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($result, true);
    }
}
```

---

## 三、工艺知识检索

### 3.1 相似工艺推荐

在编制新工艺路线时，通过MaxKB知识库检索历史相似工艺：

```php
// src/AI/AiRecommendService.php

class AiRecommendService {
    private $maxKb;

    public function __construct() {
        $this->maxKb = new MaxKbClient(config('maxkb'));
    }

    /**
     * 推荐相似工艺路线
     */
    public function recommendSimilarRoutes($productInfo) {
        // 构建检索查询
        $query = $this->buildSearchQuery($productInfo);
        
        // 在工艺知识库中语义检索
        $datasetId = config('maxkb.datasets.process_knowledge.id');
        $results = $this->maxKb->searchKnowledge($datasetId, $query, 10);
        
        // 格式化推荐结果
        return array_map(function($item) {
            return [
                'title'      => $item['title'],
                'relevance'  => round($item['score'] * 100, 1) . '%',
                'preview'    => mb_substr(strip_tags($item['content']), 0, 200) . '...',
                'source'     => $item['source'],
                'route_code' => $item['route_code'] ?? null,
            ];
        }, $results);
    }

    /**
     * 构建语义搜索查询
     */
    private function buildSearchQuery($productInfo) {
        $parts = [];
        if (!empty($productInfo['product_type'])) {
            $parts[] = $productInfo['product_type'];
        }
        if (!empty($productInfo['material_type'])) {
            $parts[] = '材质' . $productInfo['material_type'];
        }
        if (!empty($productInfo['specification'])) {
            $parts[] = $productInfo['specification'];
        }
        $parts[] = '工艺路线';
        return implode(' ', $parts);
    }

    /**
     * 检索工艺参数参考
     */
    public function searchProcessParams($operationName, $materialType, $equipmentType) {
        $query = "{$operationName} 工艺参数 {$materialType} {$equipmentType}";
        $datasetId = config('maxkb.datasets.process_knowledge.id');
        
        $results = $this->maxKb->searchKnowledge($datasetId, $query, 5);
        
        return array_map(function($item) {
            return [
                'content'  => $item['content'],
                'relevance'=> round($item['score'] * 100, 1) . '%',
            ];
        }, $results);
    }
}
```

### 3.2 工艺知识入库

工艺数据发布后自动同步到MaxKB知识库：

```php
// src/AI/KnowledgeSyncService.php

class KnowledgeSyncService {
    private $maxKb;

    public function __construct() {
        $this->maxKb = new MaxKbClient(config('maxkb'));
    }

    /**
     * 工艺路线发布后自动入库
     */
    public function syncRouteToKnowledge($routeId) {
        $route = (new ProcessRouteService())->getFullRouteById($routeId);
        
        if ($route['status'] !== 'published') {
            return false;
        }
        
        // 构建知识文档内容
        $content = $this->formatRouteAsKnowledge($route);
        
        // 入库到工艺知识库
        $datasetId = config('maxkb.datasets.process_knowledge.id');
        $result = $this->maxKb->addDocument($datasetId, [
            'name'    => "{$route['product_code']} - {$route['name']} V{$route['version']}",
            'content' => $content,
            'type'    => 'MARKDOWN',
            'meta'    => [
                'capp_route_id'  => $route['id'],
                'product_code'   => $route['product_code'],
                'product_type'   => $route['product_type'],
                'material_type'  => $route['material_type'],
                'route_code'     => $route['code'],
                'version'        => $route['version'],
                'operation_count'=> count($route['operations']),
                'sync_time'      => date('Y-m-d H:i:s'),
            ],
        ]);
        
        return $result;
    }

    /**
     * 格式化为知识文档Markdown格式
     */
    private function formatRouteAsKnowledge($route) {
        $md = "# {$route['product_code']} 工艺路线\n\n";
        $md .= "## 基本信息\n";
        $md .= "- 产品名称: {$route['product_name']}\n";
        $md .= "- 产品类型: {$route['product_type']}\n";
        $md .= "- 材质: {$route['material_type']}\n";
        $md .= "- 规格: {$route['specification']}\n";
        $md .= "- 版本: {$route['version']}\n";
        $md .= "- 总工时: {$route['total_time']} 分钟\n\n";
        
        $md .= "## 工序序列\n\n";
        foreach ($route['operations'] as $op) {
            $md .= "### 工序{$op['operation_no']}: {$op['operation_name']}\n";
            $md .= "- 工作中心: {$op['work_center_name']}\n";
            $md .= "- 设备: {$op['equipment_names']}\n";
            $md .= "- 标准工时: {$op['standard_time']} 分钟\n";
            $md .= "- 工序描述: {$op['description']}\n";
            
            if (!empty($op['parameters'])) {
                $md .= "- 工艺参数: \n";
                foreach ($op['parameters'] as $key => $val) {
                    $md .= "  - {$key}: {$val}\n";
                }
            }
            $md .= "\n";
        }
        
        return $md;
    }
}
```

---

## 四、智能体对话集成

### 4.1 工艺助手智能体

```php
// src/AI/AiAssistantService.php

class AiAssistantService {
    private $maxKb;

    public function __construct() {
        $this->maxKb = new MaxKbClient(config('maxkb'));
    }

    /**
     * 工艺助手对话
     */
    public function chat($userId, $message, $context = []) {
        $applicationId = config('maxkb.applications.process_assistant.id');
        
        // 构建对话上下文
        $history = $this->getChatHistory($userId);
        
        // 添加上下文信息
        $contextMessage = '';
        if (!empty($context['route_id'])) {
            $route = (new ProcessRouteService())->getById($context['route_id']);
            $contextMessage = "当前正在编制工艺路线：[{$route['code']}] {$route['product_name']}，"
                            . "产品类型：{$route['product_type']}，材质：{$route['material_type']}。";
        }
        
        if ($contextMessage) {
            $message = $contextMessage . "\n用户问题：" . $message;
        }
        
        // 调用智能体
        $response = $this->maxKb->chatWithAgent($applicationId, $message, $history, [
            'temperature' => 0.3,
        ]);
        
        // 保存对话历史
        $this->saveChatHistory($userId, $message, $response['data']['content'] ?? '');
        
        return $response;
    }

    /**
     * 工艺参数建议
     */
    public function suggestParameters($operationName, $materialType, $equipmentType) {
        $applicationId = config('maxkb.applications.process_assistant.id');
        
        $prompt = "请为以下工艺提供参数建议：\n"
                . "- 工序名称：{$operationName}\n"
                . "- 加工材质：{$materialType}\n"
                . "- 使用设备：{$equipmentType}\n"
                . "请给出具体的工艺参数范围和建议值。";
        
        return $this->maxKb->chatWithAgent($applicationId, $prompt);
    }
}
```

### 4.2 对话历史管理

```sql
CREATE TABLE capp_ai_chat_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_id VARCHAR(50),
    role VARCHAR(20) NOT NULL,        -- user/assistant
    content TEXT NOT NULL,
    context JSON,                      -- 对话上下文(route_id等)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_session (user_id, session_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 五、知识库内容规划

### 5.1 工艺知识库

| 内容类型 | 来源 | 说明 |
|---------|------|------|
| 工艺路线 | CAPP自动同步 | 已发布的完整工艺路线 |
| 工序卡片 | CAPP自动同步 | 各工序的详细参数和操作步骤 |
| 焊接工艺规程 | CAPP自动同步 | WPS/PQR数据 |
| 作业指导书 | CAPP自动同步 | SOP文档 |
| 工艺标准 | 人工上传 | 企业内部工艺标准文件 |
| 历史工艺 | 一次导入 | 历史积累的工艺数据 |

### 5.2 产品手册库

| 内容类型 | 来源 | 说明 |
|---------|------|------|
| 产品说明文档 | 人工上传 | 工业压缩机/压力容器产品手册 |
| 典型工艺案例 | 人工整理 | 典型产品的完整工艺案例 |
| 技术规格书 | PLM同步 | 产品技术规格 |

### 5.3 设备参数库

| 内容类型 | 来源 | 说明 |
|---------|------|------|
| 设备说明书 | 人工上传 | 所有加工设备的参数手册 |
| 设备加工能力 | CAPP维护 | 设备加工范围/精度/限制 |
| 工装刀具参数 | CAPP维护 | 工装和刀具的技术参数 |

### 5.4 标准规范库

| 内容类型 | 来源 | 说明 |
|---------|------|------|
| 国家标准 | 人工上传 | 压力容器GB标准、焊接标准等 |
| 行业标准 | 人工上传 | 压缩机行业标准、ASME标准 |
| 企业标准 | 人工上传 | 企业内部工艺规范 |

---

## 六、定时同步脚本

```php
// scripts/sync_maxkb_knowledge.php

class MaxKbSyncScript {
    public function run() {
        $knowledgeSync = new KnowledgeSyncService();
        
        // 获取所有已发布但未同步的工艺路线
        $db = Database::getInstance();
        $routes = $db->query(
            "SELECT r.id FROM capp_process_route r
             LEFT JOIN capp_maxkb_sync_record s ON r.id = s.route_id AND s.sync_type = 'route'
             WHERE r.status = 'published' AND s.id IS NULL
             ORDER BY r.published_at DESC"
        );
        
        $total = count($routes);
        $success = $fail = 0;
        
        foreach ($routes as $route) {
            try {
                $knowledgeSync->syncRouteToKnowledge($route['id']);
                // 记录同步状态
                $db->table('capp_maxkb_sync_record')->insert([
                    'route_id'   => $route['id'],
                    'sync_type'  => 'route',
                    'status'     => 1,
                    'synced_at'  => date('Y-m-d H:i:s'),
                ]);
                $success++;
            } catch (Exception $e) {
                $fail++;
                Logger::error('MaxKB同步失败', ['route_id' => $route['id'], 'error' => $e->getMessage()]);
            }
        }
        
        echo "MaxKB知识库同步完成: 总数{$total}, 成功{$success}, 失败{$fail}\n";
    }
}
```
