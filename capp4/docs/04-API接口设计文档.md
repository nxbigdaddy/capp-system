
# CAPP系统API接口设计文档

## 1. 接口概述

### 1.1 设计原则
- **RESTful风格**：使用标准HTTP方法
- **统一响应格式**：JSON格式，统一错误码
- **版本控制**：API版本化管理
- **安全认证**：JWT令牌认证

### 1.2 基础信息
- **API基础路径**：`/api/v1`
- **请求格式**：JSON
- **响应格式**：JSON
- **字符编码**：UTF-8

### 1.3 认证方式
- **JWT Token**：在请求头中携带 `Authorization: Bearer <token>`
- **企业微信OAuth2.0**：用于扫码登录

---

## 2. 接口分类

| 模块 | 路径前缀 | 说明 |
| :--- | :--- | :--- |
| 认证模块 | `/auth` | 登录、退出、Token刷新 |
| 用户模块 | `/users` | 用户管理、权限查询 |
| 产品模块 | `/products` | 产品信息管理 |
| 工艺模块 | `/process` | 工艺路线、工序管理 |
| 物料模块 | `/materials` | 物料、BOM管理 |
| 企业微信 | `/wechat` | 扫码登录、用户同步、消息推送 |
| AI模块 | `/ai` | AI决策、AI助手 |
| 集成模块 | `/integration` | CAXAPLM、U8、MES对接 |
| 系统模块 | `/system` | 系统配置、日志查询 |

---

## 3. 认证模块接口

### 3.1 企业微信扫码登录

**接口地址**：`GET /api/v1/auth/wechat/login`

**请求参数**：无

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "redirect_url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=xxx&redirect_uri=xxx&response_type=code&scope=snsapi_base&state=xxx#wechat_redirect"
  }
}
```

### 3.2 登录回调

**接口地址**：`GET /api/v1/auth/wechat/callback`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| code | string | 是 | 企业微信授权码 |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "username": "zhangsan",
      "real_name": "张三",
      "role": "工艺工程师"
    }
  }
}
```

### 3.3 Token刷新

**接口地址**：`POST /api/v1/auth/refresh`

**请求体**：
```json
{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

### 3.4 退出登录

**接口地址**：`POST /api/v1/auth/logout`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "退出成功"
}
```

---

## 4. 用户模块接口

### 4.1 获取当前用户信息

**接口地址**：`GET /api/v1/users/me`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "username": "zhangsan",
    "real_name": "张三",
    "email": "zhangsan@example.com",
    "phone": "13800138000",
    "department": "工艺部",
    "role": "工艺工程师",
    "permissions": ["process_create", "process_edit", "process_view"]
  }
}
```

### 4.2 获取用户列表

**接口地址**：`GET /api/v1/users`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| page | int | 否 | 页码，默认1 |
| size | int | 否 | 每页数量，默认20 |
| keyword | string | 否 | 搜索关键词 |
| department_id | int | 否 | 部门ID |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "username": "zhangsan",
        "real_name": "张三",
        "department": "工艺部",
        "role": "工艺工程师",
        "status": 1
      }
    ],
    "pagination": {
      "page": 1,
      "size": 20,
      "total": 100,
      "pages": 5
    }
  }
}
```

### 4.3 创建用户

**接口地址**：`POST /api/v1/users`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "username": "lisi",
  "real_name": "李四",
  "email": "lisi@example.com",
  "phone": "13900139000",
  "department_id": 1,
  "role_id": 2
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "创建成功",
  "data": {
    "id": 2,
    "username": "lisi",
    "real_name": "李四"
  }
}
```

---

## 5. 工艺模块接口

### 5.1 获取工艺路线列表

**接口地址**：`GET /api/v1/process/routes`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| page | int | 否 | 页码 |
| size | int | 否 | 每页数量 |
| product_id | int | 否 | 产品ID |
| status | int | 否 | 状态（0-草稿，1-审核中，2-已通过） |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "route_code": "PR-2024-001",
        "name": "工业压缩机工艺路线V1.0",
        "product_name": "GM-100工业压缩机",
        "status": 2,
        "version": "V1.0",
        "operation_count": 15,
        "created_by": "张三",
        "created_at": "2024-01-15 10:30:00"
      }
    ],
    "pagination": {...}
  }
}
```

### 5.2 创建工艺路线

**接口地址**：`POST /api/v1/process/routes`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "product_id": 1,
  "product_version": "V1.0",
  "route_code": "PR-2024-002",
  "name": "高压容器工艺路线",
  "description": "高压容器标准工艺路线",
  "operations": [
    {
      "code": "OP-001",
      "name": "下料",
      "work_center_id": 1,
      "equipment_id": 1,
      "sequence": 1,
      "standard_time": 2.5,
      "setup_time": 0.5,
      "machine_time": 2.0
    }
  ]
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "创建成功",
  "data": {
    "id": 2,
    "route_code": "PR-2024-002",
    "name": "高压容器工艺路线",
    "status": 0
  }
}
```

### 5.3 获取工艺路线详情

**接口地址**：`GET /api/v1/process/routes/{id}`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "route_code": "PR-2024-001",
    "name": "工业压缩机工艺路线V1.0",
    "product_id": 1,
    "product_version": "V1.0",
    "description": "...",
    "status": 2,
    "created_by": 1,
    "created_at": "2024-01-15 10:30:00",
    "operations": [
      {
        "id": 1,
        "code": "OP-001",
        "name": "下料",
        "sequence": 1,
        "work_center": "机加工车间",
        "equipment": "数控切割机",
        "standard_time": 2.5,
        "setup_time": 0.5,
        "machine_time": 2.0,
        "details": [...]
      }
    ]
  }
}
```

### 5.4 更新工艺路线

**接口地址**：`PUT /api/v1/process/routes/{id}`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "name": "工业压缩机工艺路线V1.1",
  "description": "更新后的工艺路线描述",
  "operations": [...]
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "更新成功",
  "data": {
    "id": 1,
    "name": "工业压缩机工艺路线V1.1"
  }
}
```

### 5.5 删除工艺路线

**接口地址**：`DELETE /api/v1/process/routes/{id}`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "删除成功"
}
```

### 5.6 提交审批

**接口地址**：`POST /api/v1/process/routes/{id}/submit`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "已提交审批",
  "data": {
    "approval_id": 1001,
    "status": 1
  }
}
```

---

## 6. 物料模块接口

### 6.1 获取物料列表

**接口地址**：`GET /api/v1/materials`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| page | int | 否 | 页码 |
| size | int | 否 | 每页数量 |
| code | string | 否 | 物料编码 |
| name | string | 否 | 物料名称 |
| material_group | string | 否 | 物料组 |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "code": "MAT-001",
        "name": "不锈钢板材",
        "specification": "304, 2mm",
        "unit": "kg",
        "material_group": "原材料",
        "status": 1
      }
    ],
    "pagination": {...}
  }
}
```

### 6.2 创建BOM

**接口地址**：`POST /api/v1/materials/bom`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "product_id": 1,
  "product_version": "V1.0",
  "items": [
    {
      "parent_id": 0,
      "material_id": 1,
      "quantity": 100.0,
      "unit": "kg",
      "level": 1
    }
  ]
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "创建成功",
  "data": {
    "product_id": 1,
    "product_version": "V1.0",
    "item_count": 1
  }
}
```

### 6.3 获取BOM结构

**接口地址**：`GET /api/v1/materials/bom/{product_id}`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| version | string | 否 | 版本号 |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "product_id": 1,
    "product_version": "V1.0",
    "total_items": 15,
    "tree": [
      {
        "id": 1,
        "material_code": "MAT-001",
        "material_name": "不锈钢板材",
        "quantity": 100.0,
        "unit": "kg",
        "level": 1,
        "children": [
          {
            "id": 2,
            "material_code": "MAT-002",
            "material_name": "螺栓",
            "quantity": 50.0,
            "unit": "个",
            "level": 2
          }
        ]
      }
    ]
  }
}
```

---

## 7. 企业微信模块接口

### 7.1 同步企业微信用户

**接口地址**：`POST /api/v1/wechat/sync/users`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "sync_department": true,
  "sync_permission": true
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "同步完成",
  "data": {
    "total_count": 50,
    "created_count": 10,
    "updated_count": 40,
    "failed_count": 0
  }
}
```

### 7.2 同步企业微信部门

**接口地址**：`POST /api/v1/wechat/sync/departments`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "同步完成",
  "data": {
    "total_count": 10,
    "created_count": 2,
    "updated_count": 8
  }
}
```

### 7.3 发送企业微信消息

**接口地址**：`POST /api/v1/wechat/message/send`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "user_ids": [1, 2, 3],
  "message_type": "text",
  "content": "您有一条新的审批待处理，请及时处理。",
  "title": "审批通知"
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "消息发送成功",
  "data": {
    "success_count": 3,
    "failed_count": 0
  }
}
```

---

## 8. AI模块接口

### 8.1 AI工艺优化

**接口地址**：`POST /api/v1/ai/optimize/process`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "product_id": 1,
  "current_route_id": 1,
  "optimization_goals": ["time", "cost", "quality"],
  "constraints": {
    "max_time": 100,
    "max_cost": 5000
  }
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "优化完成",
  "data": {
    "original_time": 120.0,
    "optimized_time": 95.0,
    "original_cost": 6000.0,
    "optimized_cost": 4800.0,
    "suggestions": [
      "建议将工序3的设备更换为CNC加工中心，可节省工时20%",
      "建议优化焊接工艺参数，减少焊接时间"
    ],
    "optimized_route": {...}
  }
}
```

### 8.2 AI工时预测

**接口地址**：`POST /api/v1/ai/predict/time`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "product_id": 1,
  "operation_ids": [1, 2, 3],
  "historical_data": {
    "batch_size": 10,
    "material_type": "不锈钢"
  }
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "预测完成",
  "data": {
    "predictions": [
      {
        "operation_id": 1,
        "operation_name": "下料",
        "predicted_time": 2.5,
        "confidence": 0.95
      }
    ],
    "total_time": 15.5,
    "confidence_level": 0.92
  }
}
```

### 8.3 AI问答助手

**接口地址**：`POST /api/v1/ai/chat`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "question": "工业压缩机的焊接工艺要求是什么？",
  "context": {
    "product_id": 1,
    "process_type": "welding"
  }
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "answer": "工业压缩机焊接工艺要求如下：1. 焊接材料应符合GB/T 983标准...",
    "sources": [
      {
        "id": 1,
        "title": "工业压缩机焊接工艺规范",
        "type": "document"
      }
    ],
    "confidence": 0.88
  }
}
```

---

## 9. 集成模块接口

### 9.1 从CAXAPLM导入数据

**接口地址**：`POST /api/v1/integration/caxa/import`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "import_type": "product",
  "caxa_ids": ["CAXA-PROD-001"],
  "sync_bom": true,
  "sync_drawing": true
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "导入完成",
  "data": {
    "imported_count": 1,
    "bom_imported": true,
    "drawing_imported": true,
    "product_id": 100
  }
}
```

### 9.2 同步到U8系统

**接口地址**：`POST /api/v1/integration/u8/sync`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "sync_type": "process_route",
  "route_ids": [1, 2, 3],
  "update_existing": true
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "同步完成",
  "data": {
    "synced_count": 3,
    "failed_count": 0,
    "u8_documents": [
      {"route_id": 1, "u8_id": "U8-PR-001"}
    ]
  }
}
```

### 9.3 下发工艺文件到MES

**接口地址**：`POST /api/v1/integration/mes/send`

**请求头**：`Authorization: Bearer <token>`

**请求体**：
```json
{
  "route_id": 1,
  "work_center_ids": [1, 2],
  "document_types": ["process_card", "operation_card"]
}
```

**响应示例**：
```json
{
  "code": 200,
  "message": "下发完成",
  "data": {
    "sent_count": 2,
    "work_centers": ["机加工车间", "焊接车间"]
  }
}
```

### 9.4 从MES采集生产数据

**接口地址**：`GET /api/v1/integration/mes/collect`

**请求头**：`Authorization: Bearer <token>`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| route_id | int | 是 | 工艺路线ID |
| start_time | string | 是 | 开始时间(YYYY-MM-DD HH:MM:SS) |
| end_time | string | 是 | 结束时间(YYYY-MM-DD HH:MM:SS) |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "production_records": [
      {
        "operation_id": 1,
        "actual_time": 2.3,
        "standard_time": 2.5,
        "quality_rate": 0.98,
        "machine_id": "CNC-001"
      }
    ],
    "total_records": 100
  }
}
```

---

## 10. 系统模块接口

### 10.1 获取系统配置

**接口地址**：`GET /api/v1/system/config`

**请求头**：`Authorization: Bearer <token>`

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "system_name": "CAPP系统",
    "system_version": "1.0.0",
    "wechat_enabled": true,
    "ai_enabled": true,
    "integration_enabled": {
      "caxa": true,
      "u8": true,
      "mes": true,
      "maxkb": true
    }
  }
}
```

### 10.2 获取操作日志

**接口地址**：`GET /api/v1/system/logs`

**请求头**：`Authorization: Bearer <token>`

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| page | int | 否 | 页码 |
| size | int | 否 | 每页数量 |
| user_id | int | 否 | 用户ID |
| start_time | string | 否 | 开始时间 |
| end_time | string | 否 | 结束时间 |

**响应示例**：
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "user_name": "张三",
        "action": "创建工艺路线",
        "module": "工艺管理",
        "target": "PR-2024-001",
        "ip": "203.0.113.10",
        "created_at": "2024-01-15 10:30:00"
      }
    ],
    "pagination": {...}
  }
}
```

---

## 11. 统一错误码

| 错误码 | 说明 | HTTP状态码 |
| :--- | :--- | :--- |
| 0 | 成功 | 200 |
| 1001 | 未授权 | 401 |
| 1002 | 权限不足 | 403 |
| 1003 | 参数错误 | 400 |
| 1004 | 资源不存在 | 404 |
| 1005 | 服务器错误 | 500 |
| 2001 | 企业微信授权失败 | 401 |
| 2002 | 用户同步失败 | 500 |
| 3001 | CAXA接口调用失败 | 500 |
| 3002 | U8接口调用失败 | 500 |
| 3003 | MES接口调用失败 | 500 |
| 4001 | AI服务调用失败 | 500 |

---

## 12. 响应格式规范

### 12.1 成功响应

```json
{
  "code": 200,
  "message": "success",
  "data": {...},
  "timestamp": 1705312200
}
```

### 12.2 失败响应

```json
{
  "code": 1001,
  "message": "未授权，请登录",
  "data": null,
  "timestamp": 1705312200
}
```

### 12.3 分页响应

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [...],
    "pagination": {
      "page": 1,
      "size": 20,
      "total": 100,
      "pages": 5
    }
  },
  "timestamp": 1705312200
}
```
