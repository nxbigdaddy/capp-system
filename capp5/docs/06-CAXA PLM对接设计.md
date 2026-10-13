# CAPP系统 - CAXA PLM对接设计

> 版本：V1.0 | 日期：2026-06-07 | 状态：初稿

---

## 一、对接概述

### 1.1 对接目标

CAXA PLM 作为企业产品设计的源头系统，CAPP 需要与其深度集成，实现设计数据到工艺数据的无缝流转。

### 1.2 对接方式

CAXA PLM 底层使用 SQL Server 数据库，CAPP 采用两种对接方式：

| 方式 | 用途 | 说明 |
|------|------|------|
| 数据库直连(只读) | 定时批量同步 | 读取PLM数据库视图，定期同步产品结构/EBOM/物料 |
| API接口 | 实时交互 | 通过CAXA PLM提供的Web API进行实时数据查询和回写 |

### 1.3 数据流向

```
CAXA PLM (SQL Server)                CAPP (MySQL)
┌──────────────────┐              ┌──────────────────┐
│ 产品结构树        │──定时同步────▶│ capp_product      │
│ EBOM(设计BOM)    │              │ (product_type=plm) │
│ 物料主数据        │              │ capp_material      │
│ 图纸文件          │              │ (图纸引用URL)      │
└──────────────────┘              └──────────────────┘
         ▲                               │
         │        工艺数据回传              │
         │  (工艺路线/MBOM/工时定额)         │
         └───────────────────────────────┘
```

---

## 二、数据同步设计

### 2.1 PLM数据库连接配置

```php
// config/caxa.php
return [
    // PLM数据库连接（只读）
    'database' => [
        'driver'   => 'sqlsrv',
        'host'     => env('CAXA_DB_HOST', '203.0.113.10'),
        'port'     => env('CAXA_DB_PORT', '1433'),
        'database' => env('CAXA_DB_NAME', 'CAXA_PLM_DB'),
        'username' => env('CAXA_DB_USER', 'capp_readonly'),
        'password' => env('CAXA_DB_PASS', ''),
    ],
    
    // PLM API配置
    'api' => [
        'base_url' => env('CAXA_API_URL', 'http://plm-server/api'),
        'username' => env('CAXA_API_USER', 'capp_integration'),
        'password' => env('CAXA_API_PASS', ''),
    ],
    
    // 同步策略
    'sync' => [
        'full_sync_cron'  => '0 3 * * 0',      // 每周日凌晨3点全量
        'delta_sync_cron' => '0 */4 * * *',     // 每4小时增量
        'batch_size'      => 500,                // 每批处理条数
    ],
    
    // 图纸文件服务器
    'file_server' => [
        'base_url' => env('CAXA_FILE_URL', 'http://plm-server/files'),
        'cache_dir'=> STORAGE_PATH . '/plm_files/',
    ],
];
```

### 2.2 同步数据表映射

#### 产品/零部件同步 (PLM → capp_product)

| PLM表/视图字段 | CAPP字段 | 转换说明 |
|---------------|---------|---------|
| PartID | plm_id | PLM唯一标识 |
| PartCode | code | 零部件编码 |
| PartName | name | 零部件名称 |
| Specification | specification | 规格 |
| Material | material_type | 材质 |
| DrawingNo | drawing_no | 图号 |
| Unit | unit | 计量单位 |
| Weight | weight | 重量(kg) |
| PartType | product_type | 类型转换映射 |
| ParentPartID | parent_id | 父级(需查CAPP本地ID) |
| Status | status | 1=有效 0=失效 |

#### 物料同步 (PLM → capp_material)

| PLM表/视图字段 | CAPP字段 | 转换说明 |
|---------------|---------|---------|
| PartCode | code | |
| PartName | name | |
| Specification | specification | |
| Material | material_type | |
| DrawingNo | drawing_no | |
| Unit | unit | |
| SourceType | type | self_made/outsource/standard |

### 2.3 同步脚本核心逻辑

```php
// scripts/sync_plm_data.php

class PlmSyncService {
    private $plmDb;   // PLM SQL Server连接(只读)
    private $cappDb;  // CAPP MySQL连接

    /**
     * 同步产品数据
     */
    public function syncProducts($mode = 'delta') {
        $lastSyncTime = $this->getLastSyncTime('plm_product');
        
        $sql = "SELECT * FROM v_capp_product_sync ";
        if ($mode === 'delta') {
            $sql .= "WHERE ModifiedDate > :last_sync";
        }
        
        $stmt = $this->plmDb->prepare($sql);
        if ($mode === 'delta') {
            $stmt->bindValue(':last_sync', $lastSyncTime);
        }
        $stmt->execute();
        
        $batch = [];
        $count = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $batch[] = $this->transformProduct($row);
            $count++;
            
            // 批量写入(每500条)
            if (count($batch) >= 500) {
                $this->batchUpsertProducts($batch);
                $batch = [];
            }
        }
        
        // 写入剩余数据
        if (!empty($batch)) {
            $this->batchUpsertProducts($batch);
        }
        
        $this->updateSyncTime('plm_product');
        return $count;
    }

    /**
     * PLM产品数据 → CAPP格式转换
     */
    private function transformProduct($plmRow) {
        return [
            'plm_id'        => $plmRow['PartID'],
            'code'          => $plmRow['PartCode'],
            'name'          => $plmRow['PartName'],
            'specification' => $plmRow['Specification'],
            'material_type' => $plmRow['Material'],
            'drawing_no'    => $plmRow['DrawingNo'],
            'unit'          => $plmRow['Unit'],
            'weight'        => $plmRow['Weight'],
            'product_type'  => $this->mapPartType($plmRow['PartType']),
            'source'        => 'plm',
            'status'        => $plmRow['Status'] == '已发布' ? 1 : 0,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 批量插入或更新
     */
    private function batchUpsertProducts($products) {
        // 使用 INSERT ... ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO capp_product 
                (plm_id, code, name, specification, material_type, 
                 drawing_no, unit, weight, product_type, source, status, updated_at)
                VALUES ";
        
        $values = [];
        $params = [];
        foreach ($products as $i => $p) {
            $values[] = "(:plm_id_{$i}, :code_{$i}, :name_{$i}, ...)";
            $params["plm_id_{$i}"] = $p['plm_id'];
            // ...其他参数绑定
        }
        
        $sql .= implode(',', $values);
        $sql .= " ON DUPLICATE KEY UPDATE
                  name = VALUES(name),
                  specification = VALUES(specification),
                  material_type = VALUES(material_type),
                  drawing_no = VALUES(drawing_no),
                  unit = VALUES(unit),
                  weight = VALUES(weight),
                  product_type = VALUES(product_type),
                  status = VALUES(status),
                  updated_at = VALUES(updated_at)";
        
        $this->cappDb->prepare($sql)->execute($params);
    }
}
```

### 2.4 EBOM同步

PLM中的EBOM（设计BOM）形成产品结构树，同步到CAPP后用于：
- 在工艺路线编制时展示产品结构参考
- 基于EBOM生成MBOM
- 父子关系维护

同步时注意：
- 仅同步已发布状态的零部件
- 展平BOM结构，通过 `parent_id` 维护层级
- 如果PLM中修改了BOM结构，需触发CAPP中相关工艺路线的变更提醒

---

## 三、图纸关联

### 3.1 图纸引用方式

CAPP不存储图纸文件，而是通过URL引用PLM文件服务器上的图纸：

```
图纸URL格式:
http://plm-server/files/{drawing_no}/{drawing_no}.pdf      (PDF版本)
http://plm-server/files/{drawing_no}/{drawing_no}.jpg      (缩略图)
http://plm-server/viewer?drawing_no={drawing_no}           (在线查看器)
```

### 3.2 工序卡中的图纸引用

在编制工序卡片时，系统自动获取该零部件的关联图纸列表，工序卡可引用多张图纸：

```php
// src/Integration/Caxa/CaxaPlmClient.php

class CaxaPlmClient {
    /**
     * 获取零部件的关联图纸列表
     */
    public function getDrawingsByPartCode($partCode) {
        $sql = "SELECT DrawingNo, DrawingName, DrawingType, FilePath
                FROM v_capp_drawing_sync
                WHERE PartCode = :code AND Status = '已发布'";
        
        $stmt = $this->plmDb->prepare($sql);
        $stmt->execute([':code' => $partCode]);
        
        $drawings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $drawings[] = [
                'drawing_no'   => $row['DrawingNo'],
                'name'         => $row['DrawingName'],
                'type'         => $row['DrawingType'],
                'view_url'     => $this->apiUrl . '/viewer?drawing=' . $row['DrawingNo'],
                'preview_url'  => $this->fileServerUrl . '/' . $row['FilePath'],
            ];
        }
        return $drawings;
    }
}
```

---

## 四、变更联动

### 4.1 设计变更触发机制

当PLM中发生设计变更时，需通知CAPP中相关工艺路线可能存在影响：

```
PLM设计变更
  ↓
定时任务检测PLM变更日志
  ↓
匹配CAPP中受影响的工艺路线（通过物料编码）
  ↓
生成变更提醒 → 企业微信通知相关工艺工程师
  ↓
工艺工程师评估影响 → 决定是否发起工艺变更(ECN)
```

### 4.2 变更检测脚本

```php
// scripts/check_plm_changes.php

class PlmChangeDetector {
    /**
     * 检测PLM中的设计变更
     */
    public function detectChanges() {
        $lastCheckTime = $this->getLastCheckTime();
        
        // 查询PLM中已发布状态变更的零部件
        $sql = "SELECT PartCode, PartName, ChangeType, ChangeDesc, ModifiedDate
                FROM v_capp_change_log
                WHERE ModifiedDate > :last_check
                ORDER BY ModifiedDate ASC";
        
        $stmt = $this->plmDb->prepare($sql);
        $stmt->execute([':last_check' => $lastCheckTime]);
        
        $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($changes as $change) {
            // 查找CAPP中受影响的工艺路线
            $affectedRoutes = $this->findAffectedRoutes($change['PartCode']);
            
            if (!empty($affectedRoutes)) {
                // 创建变更提醒记录
                $this->createChangeAlert($change, $affectedRoutes);
                
                // 发送企业微信通知
                $this->notifyEngineers($change, $affectedRoutes);
            }
        }
        
        $this->updateCheckTime();
    }
}
```

---

## 五、工艺数据回传

### 5.1 回传内容

CAPP编制的工艺数据需回传给CAXA PLM，丰富产品工艺信息：

| 回传数据 | 说明 |
|---------|------|
| MBOM | 制造BOM（基于EBOM扩展） |
| 工艺路线摘要 | 路线编号、版本、工序数量、总工时 |
| 关键工序信息 | 关键工序及控制参数 |

### 5.2 回传时机

- 工艺路线发布审批通过后
- 工艺路线版本更新后
- MBOM编制完成并发布后

### 5.3 回传接口实现

```php
// src/Integration/Caxa/CaxaPlmClient.php

class CaxaPlmClient {
    /**
     * 回传工艺路线摘要到PLM
     */
    public function syncProcessRouteToPlm($routeData) {
        $payload = [
            'PartCode'       => $routeData['product_code'],
            'ProcessRouteNo' => $routeData['route_code'],
            'RouteVersion'   => $routeData['version'],
            'OperationCount' => $routeData['operation_count'],
            'TotalTime'      => $routeData['total_time'],
            'KeyOperations'  => $routeData['key_operations'],
            'Status'         => $routeData['status'],
            'UpdatedBy'      => $routeData['updated_by'],
            'UpdatedAt'      => date('Y-m-d H:i:s'),
        ];

        $response = $this->callPlmApi('POST', '/api/process-route/sync', $payload);
        return $response['success'] ?? false;
    }

    /**
     * 回传MBOM到PLM
     */
    public function syncMbomToPlm($mbomData) {
        $payload = [
            'PartCode'    => $mbomData['product_code'],
            'MbomVersion' => $mbomData['version'],
            'Items'       => $mbomData['items'], // [{material_code, qty, route_no}]
            'CreatedBy'   => $mbomData['created_by'],
        ];

        $response = $this->callPlmApi('POST', '/api/mbom/sync', $payload);
        return $response['success'] ?? false;
    }

    private function callPlmApi($method, $endpoint, $data = null) {
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($this->apiUser . ':' . $this->apiPass),
        ]);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }
}
```

---

## 六、异常处理

### 6.1 同步异常策略

| 异常类型 | 处理策略 |
|---------|---------|
| PLM数据库连接失败 | 重试3次(间隔30秒)，仍失败则告警 |
| 单条数据转换失败 | 记录错误日志，跳过继续 |
| 网络超时 | 重试2次，记录超时日志 |
| 数据冲突 | 人工介入处理（记录冲突详情） |

### 6.2 异常告警

```php
// 同步异常后发送企业微信告警
if ($syncResult['fail_count'] > $syncResult['total_count'] * 0.1) {
    // 失败率超过10%，发送告警
    $message = new WeWorkMessage($weworkClient);
    $message->sendText(
        'admin_user_id',
        "【PLM同步异常告警】\n"
        . "同步时间：{$syncResult['time']}\n"
        . "总数：{$syncResult['total_count']} | 成功：{$syncResult['success_count']} | 失败：{$syncResult['fail_count']}\n"
        . "失败率：" . round($syncResult['fail_count'] / $syncResult['total_count'] * 100, 2) . "%"
    );
}
```
