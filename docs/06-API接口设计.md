# CAPP 系统 - API 接口设计

> 版本：V1.0 | 日期：2026-06-07 | 状态：初稿

---

## 一、接口规范

### 1.1 基础约定

| 项目 | 约定 |
|------|------|
| 协议 | HTTPS（生产）/ HTTP（开发） |
| 数据格式 | JSON |
| 字符编码 | UTF-8 |
| 认证方式 | JWT Bearer Token |
| 时间格式 | ISO 8601 (`2026-06-07T14:30:00+08:00`) |

### 1.2 统一响应格式

```json
{
  "code": 0,
  "message": "success",
  "data": {},
  "timestamp": "2026-06-07T14:30:00+08:00"
}
```

### 1.3 错误码定义

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 40001 | 请求参数错误 |
| 40101 | 未登录/Token 无效 |
| 40102 | 账号已禁用 |
| 40301 | 无权限 |
| 40401 | 资源不存在 |
| 40901 | 资源冲突（如编码重复） |
| 50001 | 服务器内部错误 |
| 50002 | 外部系统调用失败 |

### 1.4 分页响应格式

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 100,
      "total_pages": 5
    }
  },
  "timestamp": "2026-06-07T14:30:00+08:00"
}
```

---

## 二、认证接口

### 2.1 账号密码登录

```
POST /api/auth/login
```

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| username | string | 是 | 工号或手机号 |
| password | string | 是 | 密码 |

**请求示例：**

```json
{
  "username": "1001",
  "password": "abc123456"
}
```

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200,
    "user": {
      "id": 1,
      "name": "张三",
      "employee_no": "1001",
      "department": "工艺部",
      "position": "工艺员",
      "avatar": "https://..."
    }
  }
}
```

### 2.2 刷新 Token

```
POST /api/auth/refresh
```

**请求头：**

```
Authorization: Bearer {refresh_token}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200
  }
}
```

### 2.3 获取当前用户信息

```
GET /api/auth/user-info
```

**请求头：**

```
Authorization: Bearer {token}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "name": "张三",
    "employee_no": "1001",
    "department": "工艺部",
    "position": "工艺员",
    "roles": ["工艺员"],
    "permissions": ["process_route:create", "process_route:update", "process_route:view"]
  }
}
```

### 2.4 登出

```
POST /api/auth/logout
```

**响应示例：**

```json
{
  "code": 0,
  "message": "success"
}
```

---

## 三、系统管理接口

### 3.1 用户管理

#### 获取用户列表

```
GET /api/admin/users?page=1&page_size=20&keyword=&status=1
```

**查询参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码，默认 1 |
| page_size | int | 否 | 每页条数，默认 20 |
| keyword | string | 否 | 搜索关键词（姓名/工号/手机号） |
| status | int | 否 | 状态筛选 |

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "list": [
      {
        "id": 1,
        "name": "张三",
        "employee_no": "1001",
        "department": "工艺部",
        "position": "工艺员",
        "status": 1,
        "last_login_at": "2026-06-07T10:00:00+08:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 50,
      "total_pages": 3
    }
  }
}
```

#### 更新用户角色

```
POST /api/admin/users/{id}/roles
```

**请求参数：**

```json
{
  "role_ids": [1, 2]
}
```

#### 同步企业微信用户

```
POST /api/admin/users/sync
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "total": 100,
    "created": 5,
    "updated": 10,
    "failed": 0
  }
}
```

### 3.2 角色管理

#### 获取角色列表

```
GET /api/admin/roles
```

**响应示例：**

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "code": "process_engineer",
      "name": "工艺员",
      "description": "负责工艺路线编制",
      "permissions": ["process_route:create", "process_route:update", "process_route:view"]
    }
  ]
}
```

#### 更新角色权限

```
POST /api/admin/roles/{id}/permissions
```

**请求参数：**

```json
{
  "permission_ids": [1, 2, 3, 4]
}
```

### 3.3 操作日志

#### 获取操作日志

```
GET /api/admin/logs?page=1&page_size=20&module=&action=&start_date=&end_date=
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "list": [
      {
        "id": 1,
        "user_name": "张三",
        "module": "process_route",
        "action": "create",
        "description": "创建工艺路线 [ID:123]",
        "ip": "203.0.113.10",
        "created_at": "2026-06-07T14:30:00+08:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 1000
    }
  }
}
```

---

## 四、基础数据接口

### 4.1 产品/零部件

#### 获取产品列表

```
GET /api/products?page=1&page_size=20&keyword=&type=
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "list": [
      {
        "id": 1,
        "code": "P-001",
        "name": "工业压缩机主机",
        "specification": "GD-100/40",
        "material_type": "Q345R",
        "drawing_no": "GD-100-00",
        "unit": "台",
        "status": 1
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 500
    }
  }
}
```

#### 获取产品详情（含 BOM）

```
GET /api/products/{id}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "code": "P-001",
    "name": "工业压缩机主机",
    "children": [
      {
        "id": 2,
        "code": "P-001-01",
        "name": "缸体",
        "parent_id": 1
      }
    ]
  }
}
```

### 4.2 物料

#### 获取物料列表

```
GET /api/materials?page=1&page_size=20&keyword=&category_id=
```

#### 获取物料详情

```
GET /api/materials/{id}
```

### 4.3 工作中心

#### 获取工作中心列表

```
GET /api/work-centers
```

**响应示例：**

```json
{
  "code": 0,
  "data": [
    {
      "id": 1,
      "code": "WC-001",
      "name": "数控车削中心",
      "type": "machining",
      "workshop": "机加车间",
      "capacity_per_day": 480,
      "rate_per_hour": 80.00
    }
  ]
}
```

### 4.4 标准工序

#### 获取标准工序列表

```
GET /api/std-operations?page=1&page_size=20&keyword=&type=
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "list": [
      {
        "id": 1,
        "code": "OP-001",
        "name": "车削外圆",
        "type": "turning",
        "default_duration": 60,
        "default_work_center_id": 1,
        "description": "使用数控车床加工外圆"
      }
    ]
  }
}
```

### 4.5 设备/工装

#### 获取设备列表

```
GET /api/equipment?work_center_id=
```

#### 获取工装列表

```
GET /api/tooling?type=
```

---

## 五、工艺路线接口

### 5.1 工艺路线 CRUD

#### 获取工艺路线列表

```
GET /api/process/routes?page=1&page_size=20&keyword=&status=&product_id=
```

**查询参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| page | int | 否 | 页码 |
| page_size | int | 否 | 每页条数 |
| keyword | string | 否 | 搜索关键词 |
| status | string | 否 | 状态筛选 |
| product_id | int | 否 | 产品筛选 |

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "list": [
      {
        "id": 1,
        "code": "PR20260001",
        "product_code": "P-001",
        "product_name": "工业压缩机主机",
        "name": "主机装配工艺",
        "version": "V1.0",
        "status": "published",
        "total_time": 480,
        "creator_name": "张三",
        "created_at": "2026-06-01T10:00:00+08:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 100
    }
  }
}
```

#### 创建工艺路线

```
POST /api/process/routes
```

**请求参数：**

```json
{
  "product_id": 1,
  "product_code": "P-001",
  "product_name": "工业压缩机主机",
  "name": "主机装配工艺",
  "description": "...",
  "operations": [
    {
      "operation_no": "10",
      "operation_name": "下料",
      "std_operation_id": 1,
      "work_center_id": 2,
      "description": "...",
      "equipment_ids": [1, 2],
      "standard_time": 30,
      "is_key_point": 0,
      "parameters": {"length": 500, "diameter": 200}
    }
  ]
}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "id": 123,
    "code": "PR20260001"
  }
}
```

#### 获取工艺路线详情

```
GET /api/process/routes/{id}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "code": "PR20260001",
    "product_id": 1,
    "product_code": "P-001",
    "product_name": "工业压缩机主机",
    "name": "主机装配工艺",
    "version": "V1.0",
    "status": "published",
    "description": "...",
    "total_time": 480,
    "created_by": 1,
    "creator_name": "张三",
    "approved_by": 2,
    "approver_name": "李四",
    "approved_at": "2026-06-02T10:00:00+08:00",
    "published_at": "2026-06-03T10:00:00+08:00",
    "operations": [
      {
        "id": 1,
        "operation_no": "10",
        "operation_name": "下料",
        "work_center_id": 2,
        "work_center_name": "切割中心",
        "std_operation_name": "等离子切割",
        "description": "...",
        "standard_time": 30,
        "is_key_point": 0,
        "parameters": {"length": 500}
      }
    ],
    "approval_records": [
      {
        "id": 1,
        "node_name": "工艺组长审核",
        "approver_name": "李四",
        "action": "approve",
        "comment": "同意",
        "created_at": "2026-06-02T10:00:00+08:00"
      }
    ]
  }
}
```

#### 更新工艺路线

```
PUT /api/process/routes/{id}
```

**请求参数：** 同创建

#### 删除工艺路线

```
DELETE /api/process/routes/{id}
```

### 5.2 工艺路线审批

#### 提交审核

```
POST /api/process/routes/{id}/submit
```

**响应示例：**

```json
{
  "code": 0,
  "message": "已提交审核"
}
```

#### 审批通过

```
POST /api/process/routes/{id}/approve
```

**请求参数：**

```json
{
  "comment": "工艺合理，同意通过"
}
```

#### 审批驳回

```
POST /api/process/routes/{id}/reject
```

**请求参数：**

```json
{
  "comment": "工序30参数有误，请修改"
}
```

#### 发布工艺路线

```
POST /api/process/routes/{id}/publish
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "version": "V1.1",
    "qr_code_url": "/qr/route_1_V1.1.png"
  }
}
```

### 5.3 工序管理

#### 添加工序

```
POST /api/process/routes/{route_id}/operations
```

**请求参数：**

```json
{
  "operation_no": "20",
  "operation_name": "车削",
  "std_operation_id": 2,
  "work_center_id": 1,
  "description": "...",
  "equipment_ids": [3],
  "standard_time": 60,
  "predecessor_no": "10",
  "is_key_point": 1,
  "parameters": {"diameter": 200, "tolerance": "±0.05"}
}
```

#### 更新工序

```
PUT /api/process/routes/{route_id}/operations/{op_id}
```

#### 删除工序

```
DELETE /api/process/routes/{route_id}/operations/{op_id}
```

#### 调整工序顺序

```
POST /api/process/routes/{route_id}/operations/reorder
```

**请求参数：**

```json
{
  "operation_ids": [3, 1, 2]
}
```

---

## 六、工艺规程接口

### 6.1 工序卡片

#### 获取工序卡片列表

```
GET /api/operation-cards?page=1&page_size=20&route_id=&keyword=
```

#### 创建/更新工序卡片

```
POST /api/operation-cards
```

**请求参数：**

```json
{
  "route_operation_id": 1,
  "card_no": "OC-001",
  "operation_steps": "1. 检查原材料...\n2. 调整参数...",
  "drawing_refs": ["http://.../drawing1.jpg"],
  "safety_notes": "注意防护眼镜...",
  "kcp_list": [
    {"item": "尺寸A", "standard": "100mm", "tolerance": "±0.1", "method": "游标卡尺"}
  ],
  "process_params": {"speed": 1000, "feed": 0.2}
}
```

#### 获取工序卡片详情

```
GET /api/operation-cards/{id}
```

### 6.2 检验卡片

#### 获取检验卡片列表

```
GET /api/inspection-cards?page=1&page_size=20&route_id=
```

#### 创建/更新检验卡片

```
POST /api/inspection-cards
```

**请求参数：**

```json
{
  "route_operation_id": 1,
  "card_no": "IC-001",
  "inspection_items": [
    {"item": "外观", "standard": "无裂纹", "method": "目视"},
    {"item": "尺寸", "standard": "100±0.1", "method": "卡尺"}
  ],
  "sampling_plan": "AQL 1.0",
  "key_dimensions": [
    {"dimension": "直径", "nominal": 100, "upper": 100.1, "lower": 99.9}
  ]
}
```

### 6.3 焊接工艺规程(WPS)

#### 获取 WPS 列表

```
GET /api/wps?page=1&page_size=20&route_id=
```

#### 创建/更新 WPS

```
POST /api/wps
```

**请求参数：**

```json
{
  "route_operation_id": 1,
  "wps_no": "WPS-001",
  "pqr_no": "PQR-001",
  "welding_method": "GTAW",
  "base_material": "Q345R",
  "filler_material": "ER50-6",
  "preheat_temp": 100,
  "interpass_temp": 250,
  "electrical_params": {"current": 180, "voltage": 22, "polarity": "DCEN"},
  "welding_speed": "150-250 mm/min"
}
```

---

## 七、定额管理接口

### 7.1 工时定额

#### 获取工时定额

```
GET /api/quota/time?route_id=1
```

**响应示例：**

```json
{
  "code": 0,
  "data": [
    {
      "route_operation_id": 1,
      "operation_no": "10",
      "operation_name": "下料",
      "work_center_name": "切割中心",
      "prepare_time": 15,
      "process_time": 30,
      "assist_time": 5,
      "allowance_time": 3,
      "total_time": 53,
      "method": "estimate",
      "worker_count": 1
    }
  ]
}
```

#### 批量更新工时定额

```
POST /api/quota/time/batch
```

**请求参数：**

```json
{
  "items": [
    {
      "route_operation_id": 1,
      "prepare_time": 15,
      "process_time": 30,
      "assist_time": 5,
      "allowance_time": 3,
      "method": "estimate",
      "worker_count": 1
    }
  ]
}
```

#### 自动计算工时定额

```
POST /api/quota/time/calculate
```

**请求参数：**

```json
{
  "route_operation_id": 1,
  "method": "statistic"
}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "prepare_time": 0,
    "process_time": 45.5,
    "assist_time": 0,
    "allowance_time": 4.55,
    "sample_count": 30
  }
}
```

### 7.2 材料定额

#### 获取材料定额

```
GET /api/quota/material?route_id=1
```

#### 更新材料定额

```
POST /api/quota/material/batch
```

**请求参数：**

```json
{
  "items": [
    {
      "route_id": 1,
      "material_id": 1,
      "material_code": "M-001",
      "gross_weight": 120.5,
      "net_weight": 100.0,
      "waste_weight": 20.5,
      "utilization_rate": 82.99,
      "quantity_per_unit": 1
    }
  ]
}
```

---

## 八、审批接口

### 8.1 获取待办列表

```
GET /api/approvals/pending?page=1&page_size=20
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "list": [
      {
        "instance_id": 1,
        "business_type": "process_route",
        "business_id": 123,
        "route_code": "PR20260001",
        "route_name": "主机装配工艺",
        "node_name": "工艺组长审核",
        "initiator_name": "张三",
        "created_at": "2026-06-07T10:00:00+08:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 5
    }
  }
}
```

### 8.2 获取已办列表

```
GET /api/approvals/done?page=1&page_size=20
```

### 8.3 审批操作

#### 通过

```
POST /api/approvals/{instance_id}/approve
```

**请求参数：**

```json
{
  "comment": "同意"
}
```

#### 驳回

```
POST /api/approvals/{instance_id}/reject
```

**请求参数：**

```json
{
  "comment": "参数有误，请修改"
}
```

#### 转审

```
POST /api/approvals/{instance_id}/transfer
```

**请求参数：**

```json
{
  "to_user_id": 3,
  "comment": "请王工审核"
}
```

---

## 九、变更管理接口

### 9.1 变更申请

#### 获取变更申请列表

```
GET /api/change/requests?page=1&page_size=20&status=
```

#### 创建变更申请

```
POST /api/change/requests
```

**请求参数：**

```json
{
  "business_id": 1,
  "business_type": "process_route",
  "change_reason": "process_opt",
  "change_desc": "优化工序顺序，减少换刀时间",
  "impact_analysis": "影响在制品约20件"
}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "ecr_no": "ECR20260001"
  }
}
```

#### 批准变更申请

```
POST /api/change/requests/{id}/approve
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "ecn_no": "ECN20260001"
  }
}
```

### 9.2 变更通知

#### 获取变更通知列表

```
GET /api/change/notices?page=1&page_size=20&status=
```

#### 获取变更通知详情

```
GET /api/change/notices/{id}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "ecn_no": "ECN20260001",
    "ecr_no": "ECR20260001",
    "change_content": "工序20和30顺序对调",
    "before_snapshot": {"operations": [...]},
    "after_snapshot": {"operations": [...]},
    "status": "pending",
    "issued_at": "2026-06-07T14:00:00+08:00"
  }
}
```

---

## 十、文件输出接口

### 10.1 打印预览

#### 获取打印数据

```
GET /api/output/print-data?type=operation_card&id=1
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "type": "operation_card",
    "title": "工序卡片",
    "content": {
      "product_name": "工业压缩机主机",
      "operation_no": "10",
      "operation_name": "下料",
      "steps": "1. 检查...",
      "qr_code_url": "/qr/route_1_V1.0.png"
    }
  }
}
```

### 10.2 PDF 导出

#### 导出 PDF

```
GET /api/output/pdf?type=operation_card&id=1
```

**响应：** 直接返回 PDF 文件流

### 10.3 标签打印

#### 生成标签

```
POST /api/output/labels
```

**请求参数：**

```json
{
  "product_code": "P-001",
  "batch_no": "20260607-01",
  "quantity": 5
}
```

**响应：** 返回 PDF 文件流

---

## 十一、移动端接口

### 11.1 扫码查看

#### 获取工艺路线（扫码）

```
GET /api/mobile/routes/{id}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "id": 1,
    "code": "PR20260001",
    "product_name": "工业压缩机主机",
    "version": "V1.0",
    "status": "published",
    "operations": [
      {
        "operation_no": "10",
        "operation_name": "下料",
        "standard_time": 30,
        "qr_code_url": "/qr/op_1_10.png"
      }
    ]
  }
}
```

#### 获取工序详情（扫码）

```
GET /api/mobile/operations/{route_id}/{operation_no}
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "operation_no": "10",
    "operation_name": "下料",
    "work_center_name": "切割中心",
    "operation_steps": "1. 检查...",
    "safety_notes": "注意防护...",
    "kcp_list": [...],
    "qr_code_url": "/qr/op_1_10.png"
  }
}
```

### 11.2 移动端待办

#### 获取移动端待办

```
GET /api/mobile/todos
```

**响应示例：**

```json
{
  "code": 0,
  "data": [
    {
      "type": "approval",
      "title": "工艺路线审核",
      "description": "PR20260001 待审核",
      "business_id": 1,
      "created_at": "2026-06-07T10:00:00+08:00"
    },
    {
      "type": "change_notice",
      "title": "变更通知",
      "description": "ECN20260001 工艺变更",
      "notice_id": 1,
      "created_at": "2026-06-07T12:00:00+08:00"
    }
  ]
}
```

### 11.3 移动端审批

#### 移动端审批通过

```
POST /api/mobile/approvals/{instance_id}/approve
```

**请求参数：**

```json
{
  "comment": "同意"
}
```

---

## 十二、同步接口

### 12.1 企业微信同步

#### 手动触发同步

```
POST /api/sync/wework
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "users_synced": 100,
    "departments_synced": 10
  }
}
```

### 12.2 PLM 同步

#### 同步产品结构

```
POST /api/sync/plm/products
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "synced": 50,
    "failed": 0
  }
}
```

### 12.3 U8 同步

#### 同步物料

```
POST /api/sync/u8/materials
```

### 12.4 MES 同步

#### 同步报工数据

```
POST /api/sync/mes/work-reports
```

**请求参数：**

```json
{
  "start_date": "2026-06-01",
  "end_date": "2026-06-07"
}
```

---

## 十三、健康检查接口

### 13.1 系统健康检查

```
GET /api/health
```

**响应示例：**

```json
{
  "code": 0,
  "data": {
    "status": "healthy",
    "services": {
      "mysql": "up",
      "redis": "up",
      "disk_usage": "45%"
    },
    "timestamp": "2026-06-07T14:30:00+08:00"
  }
}
```
