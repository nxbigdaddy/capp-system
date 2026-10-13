# CAPP系统接口API设计文档

> **系统名称**: CAPP（计算机辅助工艺设计系统）  
> **公司背景**: 工业压缩机、压力容器研发、设计、生产企业  
> **技术栈**: PHP原生开发 + MySQL数据库  
> **文档版本**: v1.0  
> **创建日期**: 2026-06-07

---

## 1. API设计规范

### 1.1 接口协议

#### 1.1.1 基本约定

- **通信协议**: HTTPS（生产环境）/ HTTP（开发环境）
- **数据格式**: 请求和响应均采用 JSON 格式
- **字符编码**: UTF-8
- **基础路径**: `/api/v1`

#### 1.1.2 RESTful风格

| HTTP方法 | 操作类型 | 说明 | 是否幂等 |
|---------|---------|------|---------|
| GET | 查询 | 获取资源（单条或列表） | 是 |
| POST | 创建 | 新建资源或执行操作 | 否 |
| PUT | 更新 | 全量更新资源 | 是 |
| PATCH | 更新 | 部分更新资源 | 是 |
| DELETE | 删除 | 删除资源 | 是 |

#### 1.1.3 请求头规范

```
Content-Type: application/json; charset=utf-8
Authorization: Bearer {token}
X-Request-ID: {uuid}
X-Client-Version: 1.0.0
Accept-Language: zh-CN
```

#### 1.1.4 HTTP状态码使用规范

| 状态码 | 说明 | 使用场景 |
|-------|------|---------|
| 200 | OK | 请求成功 |
| 201 | Created | 资源创建成功 |
| 204 | No Content | 删除成功，无返回内容 |
| 400 | Bad Request | 请求参数错误 |
| 401 | Unauthorized | 未认证或Token失效 |
| 403 | Forbidden | 无权限访问 |
| 404 | Not Found | 资源不存在 |
| 409 | Conflict | 资源冲突（如重复创建） |
| 422 | Unprocessable Entity | 业务验证失败 |
| 429 | Too Many Requests | 请求频率超限 |
| 500 | Internal Server Error | 服务器内部错误 |

### 1.2 统一响应格式

#### 1.2.1 成功响应

```json
{
  "code": 200,
  "message": "success",
  "data": {},
  "timestamp": 1717718400
}
```

#### 1.2.2 分页响应

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [],
    "pagination": {
      "page": 1,
      "per_page": 20,
      "total": 100,
      "total_pages": 5
    }
  },
  "timestamp": 1717718400
}
```

#### 1.2.3 错误响应

```json
{
  "code": 40001,
  "message": "参数验证失败",
  "data": {
    "errors": {
      "name": ["名称不能为空"],
      "email": ["邮箱格式不正确"]
    }
  },
  "timestamp": 1717718400
}
```

#### 1.2.4 响应字段说明

| 字段 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | integer | 是 | 业务状态码，200表示成功，其他为错误码 |
| message | string | 是 | 提示信息 |
| data | object/array/null | 否 | 返回数据 |
| timestamp | integer | 是 | 服务器响应时间戳（秒级） |

### 1.3 错误码规范

#### 1.3.1 错误码分类

| 错误码范围 | 分类 | 说明 |
|-----------|------|------|
| 200 | 成功 | 请求成功 |
| 40000-40099 | 请求错误 | 客户端请求参数错误 |
| 40100-40199 | 认证错误 | 身份认证相关错误 |
| 40300-40399 | 权限错误 | 权限不足相关错误 |
| 40400-40499 | 资源错误 | 资源不存在 |
| 40900-40999 | 冲突错误 | 资源冲突 |
| 42200-42299 | 业务错误 | 业务逻辑验证失败 |
| 42900-42999 | 限流错误 | 请求频率限制 |
| 50000-50099 | 服务器错误 | 服务器内部错误 |
| 50100-50199 | 数据库错误 | 数据库操作异常 |
| 50200-50299 | 第三方错误 | 外部系统调用失败 |

#### 1.3.2 详细错误码定义

| 错误码 | 说明 | HTTP状态码 |
|-------|------|-----------|
| 200 | 成功 | 200 |
| 40001 | 参数验证失败 | 400 |
| 40002 | 参数格式错误 | 400 |
| 40003 | 缺少必填参数 | 400 |
| 40101 | 未登录或Token无效 | 401 |
| 40102 | Token已过期 | 401 |
| 40103 | Token刷新失败 | 401 |
| 40301 | 无权限访问 | 403 |
| 40302 | 账号已被禁用 | 403 |
| 40401 | 资源不存在 | 404 |
| 40901 | 资源已存在 | 409 |
| 40902 | 资源被引用，无法删除 | 409 |
| 42201 | 业务规则验证失败 | 422 |
| 42202 | 状态不允许此操作 | 422 |
| 42901 | 请求过于频繁 | 429 |
| 50001 | 服务器内部错误 | 500 |
| 50002 | 服务暂时不可用 | 500 |
| 50101 | 数据库操作失败 | 500 |
| 50102 | 数据库连接失败 | 500 |
| 50201 | 第三方服务调用失败 | 502 |
| 50202 | 第三方服务响应超时 | 502 |

### 1.4 认证与授权

#### 1.4.1 JWT Token认证

系统采用 JWT（JSON Web Token） 进行身份认证。

**Token结构**: `Header.Payload.Signature`

**Payload内容**:
```json
{
  "sub": 1001,
  "username": "admin",
  "role_ids": [1, 2],
  "dept_id": 10,
  "iat": 1717718400,
  "exp": 1717722000,
  "jti": "uuid-string"
}
```

**Token配置**:
| 配置项 | 值 | 说明 |
|-------|-----|------|
| Access Token有效期 | 2小时 | 短期令牌 |
| Refresh Token有效期 | 7天 | 长期令牌 |
| 签名算法 | HS256 | HMAC-SHA256 |
| Token存储 | Redis | 支持主动失效 |

#### 1.4.2 Token刷新机制

- 客户端在Access Token过期前30分钟发起刷新请求
- 使用Refresh Token获取新的Access Token
- 每次刷新会轮换Refresh Token
- 连续刷新次数超过限制需重新登录

#### 1.4.3 权限校验

**RBAC模型**: 用户 → 角色 → 权限

**权限标识格式**: `模块:操作`

示例：`user:list`、`user:create`、`process:edit`、`card:approve`

**校验流程**:
1. 解析JWT Token获取用户信息
2. 查询用户关联的角色
3. 获取角色拥有的权限列表
4. 验证当前接口所需权限
5. 权限不足返回403

### 1.5 分页规范

| 参数 | 类型 | 必填 | 默认值 | 说明 |
|-----|------|-----|-------|------|
| page | integer | 否 | 1 | 页码，从1开始 |
| per_page | integer | 否 | 20 | 每页数量，最大100 |

请求示例: `GET /api/users?page=2&per_page=50`

### 1.6 排序规范

| 参数 | 类型 | 必填 | 默认值 | 说明 |
|-----|------|-----|-------|------|
| sort_by | string | 否 | created_at | 排序字段 |
| sort_order | string | 否 | desc | 排序方向：asc/desc |

请求示例: `GET /api/products?sort_by=created_at&sort_order=desc`

### 1.7 日期时间格式

| 场景 | 格式 | 示例 |
|-----|------|------|
| 请求参数 | YYYY-MM-DD | 2026-06-07 |
| 请求参数（含时间） | YYYY-MM-DD HH:mm:ss | 2026-06-07 14:30:00 |
| 响应数据 | ISO 8601 | 2026-06-07T14:30:00+08:00 |
| 时间戳 | Unix Timestamp（秒） | 1717718400 |

**时区**: 统一使用东八区（Asia/Shanghai）

---
## 2. 用户认证API

### 2.1 账号密码登录

**请求**: `POST /api/auth/login`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| username | string | 是 | 用户名/工号 |
| password | string | 是 | 密码（RSA加密） |
| captcha_id | string | 否 | 验证码ID（连续失败3次后必填） |
| captcha_code | string | 否 | 验证码 |

**请求示例**:
```json
{
  "username": "admin",
  "password": "encrypted_password_string",
  "captcha_id": "captcha_uuid",
  "captcha_code": "A3F7"
}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200,
    "token_type": "Bearer",
    "user": {
      "id": 1001,
      "username": "admin",
      "real_name": "管理员",
      "avatar": "/uploads/avatars/admin.jpg",
      "dept_name": "技术部",
      "roles": ["系统管理员"]
    }
  },
  "timestamp": 1717718400
}
```

**权限**: 无需认证

### 2.2 企业微信扫码登录

**请求**: `POST /api/auth/qrcode-login`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| auth_code | string | 是 | 企业微信授权码 |
| state | string | 是 | 状态参数（防CSRF） |

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200,
    "token_type": "Bearer",
    "user": {
      "id": 1001,
      "username": "zhangsan",
      "real_name": "张三",
      "dept_name": "工艺部",
      "roles": ["工艺工程师"]
    }
  },
  "timestamp": 1717718400
}
```

**权限**: 无需认证

### 2.3 刷新Token

**请求**: `POST /api/auth/refresh-token`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| refresh_token | string | 是 | 刷新令牌 |

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200,
    "token_type": "Bearer"
  },
  "timestamp": 1717718400
}
```

**权限**: 无需认证

### 2.4 退出登录

**请求**: `POST /api/auth/logout`

**请求头**: `Authorization: Bearer {access_token}`

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": null,
  "timestamp": 1717718400
}
```

**权限**: 已登录用户

### 2.5 获取当前用户信息

**请求**: `GET /api/auth/user-info`

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1001,
    "username": "admin",
    "real_name": "管理员",
    "email": "admin@company.com",
    "phone": "13800138000",
    "avatar": "/uploads/avatars/admin.jpg",
    "dept_id": 10,
    "dept_name": "技术部",
    "position": "技术总监",
    "roles": [{"id": 1, "name": "系统管理员", "code": "admin"}],
    "permissions": ["user:list", "user:create", "process:*", "card:*"],
    "created_at": "2024-01-01T00:00:00+08:00",
    "last_login_at": "2026-06-07T09:00:00+08:00"
  },
  "timestamp": 1717718400
}
```

**权限**: 已登录用户

---

## 3. 用户管理API

### 3.1 用户列表

**请求**: `GET /api/users`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码，默认1 |
| per_page | integer | 否 | 每页数量，默认20 |
| keyword | string | 否 | 搜索关键词 |
| dept_id | integer | 否 | 部门ID |
| status | integer | 否 | 状态：0-禁用，1-启用 |
| sort_by | string | 否 | 排序字段 |
| sort_order | string | 否 | 排序方向 |

**权限**: `user:list`

### 3.2 创建用户

**请求**: `POST /api/users`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| username | string | 是 | 用户名/工号，唯一 |
| password | string | 是 | 初始密码 |
| real_name | string | 是 | 真实姓名 |
| email | string | 否 | 邮箱 |
| phone | string | 否 | 手机号 |
| dept_id | integer | 是 | 部门ID |
| position | string | 否 | 职位 |
| role_ids | array | 是 | 角色ID数组 |
| status | integer | 否 | 状态，默认1 |

**权限**: `user:create`

### 3.3 用户详情

**请求**: `GET /api/users/{id}`

**路径参数**: id (integer, 必填) - 用户ID

**权限**: `user:view`

### 3.4 更新用户

**请求**: `PUT /api/users/{id}`

**路径参数**: id (integer, 必填) - 用户ID

**请求参数**: real_name, email, phone, dept_id, position, role_ids, avatar（均为可选）

**权限**: `user:edit`

### 3.5 删除用户

**请求**: `DELETE /api/users/{id}`

**路径参数**: id (integer, 必填) - 用户ID

**权限**: `user:delete`

### 3.6 启用/禁用用户

**请求**: `PUT /api/users/{id}/status`

**路径参数**: id (integer, 必填) - 用户ID

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| status | integer | 是 | 状态：0-禁用，1-启用 |
| reason | string | 否 | 操作原因 |

**权限**: `user:status`

---

## 4. 角色权限API

### 4.1 角色列表

**请求**: `GET /api/roles`

**请求参数**: page, per_page, keyword, status

**权限**: `role:list`

### 4.2 创建角色

**请求**: `POST /api/roles`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| name | string | 是 | 角色名称 |
| code | string | 是 | 角色编码，唯一 |
| description | string | 否 | 角色描述 |
| status | integer | 否 | 状态，默认1 |
| permission_ids | array | 否 | 权限ID数组 |

**权限**: `role:create`

### 4.3 更新角色

**请求**: `PUT /api/roles/{id}`

**路径参数**: id (integer, 必填) - 角色ID

**权限**: `role:edit`

### 4.4 删除角色

**请求**: `DELETE /api/roles/{id}`

**路径参数**: id (integer, 必填) - 角色ID

**权限**: `role:delete`

### 4.5 权限列表

**请求**: `GET /api/permissions`

**请求参数**: module (string, 可选) - 模块筛选

**权限**: `role:list`

### 4.6 分配权限

**请求**: `POST /api/roles/{id}/permissions`

**路径参数**: id (integer, 必填) - 角色ID

**请求参数**: permission_ids (array, 必填) - 权限ID数组

**权限**: `role:assign-permission`

---

## 5. 部门管理API

### 5.1 部门树

**请求**: `GET /api/departments`

**请求参数**: status (integer, 可选) - 状态筛选

**响应**: 返回树形结构的部门列表

**权限**: `dept:list`

### 5.2 创建部门

**请求**: `POST /api/departments`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| name | string | 是 | 部门名称 |
| code | string | 是 | 部门编码，唯一 |
| parent_id | integer | 否 | 父部门ID，默认0 |
| manager | string | 否 | 部门负责人 |
| phone | string | 否 | 联系电话 |
| sort | integer | 否 | 排序，默认0 |
| status | integer | 否 | 状态，默认1 |

**权限**: `dept:create`

### 5.3 更新部门

**请求**: `PUT /api/departments/{id}`

**路径参数**: id (integer, 必填) - 部门ID

**权限**: `dept:edit`

### 5.4 删除部门

**请求**: `DELETE /api/departments/{id}`

**路径参数**: id (integer, 必填) - 部门ID

**权限**: `dept:delete`

---
## 6. 产品管理API

### 6.1 产品列表

**请求**: `GET /api/products`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| keyword | string | 否 | 搜索关键词 |
| category | string | 否 | 产品分类：diaphragm_compressor/pressure_vessel |
| status | integer | 否 | 状态 |
| sort_by | string | 否 | 排序字段 |
| sort_order | string | 否 | 排序方向 |

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [{
      "id": 101,
      "code": "DVC-50/200",
      "name": "工业压缩机DVC-50/200",
      "category": "diaphragm_compressor",
      "category_name": "工业压缩机",
      "model": "DVC-50/200",
      "status": 1,
      "version": "V2.1",
      "bom_count": 45,
      "created_at": "2025-03-15T10:00:00+08:00"
    }],
    "pagination": {"page": 1, "per_page": 20, "total": 28, "total_pages": 2}
  },
  "timestamp": 1717718400
}
```

**权限**: `product:list`

### 6.2 创建产品

**请求**: `POST /api/products`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 产品编码 |
| name | string | 是 | 产品名称 |
| category | string | 是 | 产品分类 |
| model | string | 是 | 规格型号 |
| description | string | 否 | 产品描述 |
| drawing_no | string | 否 | 图号 |
| material | string | 否 | 主要材质 |
| pressure_rating | string | 否 | 压力等级 |
| volume | decimal | 否 | 容积（m³） |
| weight | decimal | 否 | 重量（kg） |
| status | integer | 否 | 状态，默认1 |

**权限**: `product:create`

### 6.3 产品详情

**请求**: `GET /api/products/{id}`

**路径参数**: id (integer, 必填) - 产品ID

**权限**: `product:view`

### 6.4 更新产品

**请求**: `PUT /api/products/{id}`

**路径参数**: id (integer, 必填) - 产品ID

**权限**: `product:edit`

### 6.5 删除产品

**请求**: `DELETE /api/products/{id}`

**路径参数**: id (integer, 必填) - 产品ID

**权限**: `product:delete`

### 6.6 获取产品BOM

**请求**: `GET /api/products/{id}/bom`

**路径参数**: id (integer, 必填) - 产品ID

**请求参数**: version (string, 可选) - BOM版本

**权限**: `product:bom-view`

### 6.7 添加BOM节点

**请求**: `POST /api/products/{id}/bom`

**路径参数**: id (integer, 必填) - 产品ID

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| parent_id | integer | 否 | 父节点ID，0表示根节点 |
| material_id | integer | 是 | 物料ID |
| quantity | decimal | 是 | 数量 |
| unit | string | 是 | 单位 |
| remark | string | 否 | 备注 |
| sort | integer | 否 | 排序 |

**权限**: `product:bom-edit`

### 6.8 更新BOM节点

**请求**: `PUT /api/products/{id}/bom/{bomId}`

**路径参数**: id (产品ID), bomId (BOM节点ID)

**权限**: `product:bom-edit`

### 6.9 删除BOM节点

**请求**: `DELETE /api/products/{id}/bom/{bomId}`

**路径参数**: id (产品ID), bomId (BOM节点ID)

**权限**: `product:bom-delete`

### 6.10 产品版本列表

**请求**: `GET /api/products/{id}/versions`

**路径参数**: id (integer, 必填) - 产品ID

**权限**: `product:version-view`

---

## 7. 物料管理API

### 7.1 物料列表

**请求**: `GET /api/materials`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| keyword | string | 否 | 搜索关键词 |
| type | string | 否 | 物料类型：raw/part/assembly/standard/consumable |
| category | string | 否 | 物料分类 |
| status | integer | 否 | 状态 |

**权限**: `material:list`

### 7.2 创建物料

**请求**: `POST /api/materials`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 物料编码 |
| name | string | 是 | 物料名称 |
| type | string | 是 | 物料类型 |
| category | string | 否 | 物料分类 |
| specification | string | 否 | 规格型号 |
| material_grade | string | 否 | 材质牌号 |
| unit | string | 是 | 计量单位 |
| standard | string | 否 | 执行标准 |
| supplier | string | 否 | 供应商 |
| price | decimal | 否 | 参考单价 |
| status | integer | 否 | 状态，默认1 |

**权限**: `material:create`

### 7.3 物料详情

**请求**: `GET /api/materials/{id}`

**路径参数**: id (integer, 必填) - 物料ID

**权限**: `material:view`

### 7.4 更新物料

**请求**: `PUT /api/materials/{id}`

**路径参数**: id (integer, 必填) - 物料ID

**权限**: `material:edit`

### 7.5 删除物料

**请求**: `DELETE /api/materials/{id}`

**路径参数**: id (integer, 必填) - 物料ID

**权限**: `material:delete`

### 7.6 搜索物料

**请求**: `GET /api/materials/search`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| keyword | string | 是 | 搜索关键词 |
| type | string | 否 | 物料类型筛选 |
| limit | integer | 否 | 返回数量，默认10 |

**权限**: `material:list`

---
## 8. 工艺设计API

### 8.1 工艺路线

#### 8.1.1 工艺路线列表

**请求**: `GET /api/process-routes`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| keyword | string | 否 | 搜索关键词 |
| product_id | integer | 否 | 产品ID |
| type | string | 否 | 工艺类型：machining/welding/assembly/heat_treatment |
| status | integer | 否 | 状态 |
| sort_by | string | 否 | 排序字段 |
| sort_order | string | 否 | 排序方向 |

**权限**: `process-route:list`

#### 8.1.2 创建工艺路线

**请求**: `POST /api/process-routes`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 工艺路线编码 |
| name | string | 是 | 工艺路线名称 |
| product_id | integer | 是 | 产品ID |
| type | string | 是 | 工艺类型 |
| description | string | 否 | 工艺描述 |
| applicable_models | string | 否 | 适用型号 |
| status | integer | 否 | 状态，默认0（草稿） |

**权限**: `process-route:create`

#### 8.1.3 工艺路线详情

**请求**: `GET /api/process-routes/{id}`

**路径参数**: id (integer, 必填) - 工艺路线ID

**权限**: `process-route:view`

#### 8.1.4 更新工艺路线

**请求**: `PUT /api/process-routes/{id}`

**路径参数**: id (integer, 必填) - 工艺路线ID

**权限**: `process-route:edit`

#### 8.1.5 删除工艺路线

**请求**: `DELETE /api/process-routes/{id}`

**路径参数**: id (integer, 必填) - 工艺路线ID

**权限**: `process-route:delete`

#### 8.1.6 复制工艺路线

**请求**: `POST /api/process-routes/{id}/copy`

**路径参数**: id (integer, 必填) - 源工艺路线ID

**请求参数**: name (string, 可选), code (string, 可选)

**权限**: `process-route:create`

### 8.2 工序

#### 8.2.1 工序列表

**请求**: `GET /api/process-routes/{routeId}/operations`

**路径参数**: routeId (integer, 必填) - 工艺路线ID

**权限**: `operation:list`

#### 8.2.2 创建工序

**请求**: `POST /api/process-routes/{routeId}/operations`

**路径参数**: routeId (integer, 必填) - 工艺路线ID

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 工序编码（如OP-10） |
| name | string | 是 | 工序名称 |
| type | string | 是 | 工序类型 |
| sort | integer | 是 | 排序序号 |
| work_center | string | 否 | 工作中心 |
| equipment_ids | array | 否 | 所需设备ID数组 |
| fixture_ids | array | 否 | 所需工装ID数组 |
| tool_ids | array | 否 | 所需刀具ID数组 |
| standard_time | integer | 否 | 标准工时（分钟） |
| setup_time | integer | 否 | 准备工时（分钟） |
| description | string | 否 | 工序说明 |
| quality_requirement | string | 否 | 质量要求 |
| status | integer | 否 | 状态，默认1 |

**权限**: `operation:create`

#### 8.2.3 工序详情

**请求**: `GET /api/operations/{id}`

**路径参数**: id (integer, 必填) - 工序ID

**权限**: `operation:view`

#### 8.2.4 更新工序

**请求**: `PUT /api/operations/{id}`

**路径参数**: id (integer, 必填) - 工序ID

**权限**: `operation:edit`

#### 8.2.5 删除工序

**请求**: `DELETE /api/operations/{id}`

**路径参数**: id (integer, 必填) - 工序ID

**权限**: `operation:delete`

#### 8.2.6 工序排序

**请求**: `PUT /api/operations/{id}/sort`

**路径参数**: id (integer, 必填) - 工序ID

**请求参数**: sort (integer, 必填) - 新的排序序号

**权限**: `operation:edit`

### 8.3 工步

#### 8.3.1 工步列表

**请求**: `GET /api/operations/{operationId}/steps`

**路径参数**: operationId (integer, 必填) - 工序ID

**权限**: `step:list`

#### 8.3.2 创建工步

**请求**: `POST /api/operations/{operationId}/steps`

**路径参数**: operationId (integer, 必填) - 工序ID

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| sort | integer | 是 | 排序序号 |
| name | string | 是 | 工步名称 |
| description | string | 是 | 工步描述 |
| parameters | object | 否 | 工艺参数（JSON） |
| equipment_params | object | 否 | 设备参数（JSON） |
| quality_check | string | 否 | 质量检验要求 |
| standard_time | integer | 否 | 标准工时（分钟） |
| remark | string | 否 | 备注 |
| status | integer | 否 | 状态，默认1 |

**权限**: `step:create`

#### 8.3.3 更新工步

**请求**: `PUT /api/steps/{id}`

**路径参数**: id (integer, 必填) - 工步ID

**权限**: `step:edit`

#### 8.3.4 删除工步

**请求**: `DELETE /api/steps/{id}`

**路径参数**: id (integer, 必填) - 工步ID

**权限**: `step:delete`

### 8.4 工艺卡片

#### 8.4.1 工艺卡片列表

**请求**: `GET /api/cards`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| keyword | string | 否 | 搜索关键词 |
| product_id | integer | 否 | 产品ID |
| route_id | integer | 否 | 工艺路线ID |
| type | string | 否 | 卡片类型：process_card/operation_card/inspection_card |
| status | integer | 否 | 状态 |

**权限**: `card:list`

#### 8.4.2 创建工艺卡片

**请求**: `POST /api/cards`

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 卡片编码 |
| name | string | 是 | 卡片名称 |
| product_id | integer | 是 | 产品ID |
| route_id | integer | 是 | 工艺路线ID |
| type | string | 是 | 卡片类型 |
| template_id | integer | 否 | 模板ID |
| description | string | 否 | 卡片说明 |
| status | integer | 否 | 状态，默认0（草稿） |

**权限**: `card:create`

#### 8.4.3 工艺卡片详情

**请求**: `GET /api/cards/{id}`

**路径参数**: id (integer, 必填) - 工艺卡片ID

**权限**: `card:view`

#### 8.4.4 更新工艺卡片

**请求**: `PUT /api/cards/{id}`

**路径参数**: id (integer, 必填) - 工艺卡片ID

**权限**: `card:edit`

#### 8.4.5 删除工艺卡片

**请求**: `DELETE /api/cards/{id}`

**路径参数**: id (integer, 必填) - 工艺卡片ID

**权限**: `card:delete`

#### 8.4.6 打印工艺卡片

**请求**: `GET /api/cards/{id}/print`

**路径参数**: id (integer, 必填) - 工艺卡片ID

**请求参数**: format (string, 可选) - 输出格式：pdf/html，默认pdf

**响应**: 返回PDF文件或HTML内容

**权限**: `card:print`

#### 8.4.7 导出工艺卡片

**请求**: `GET /api/cards/{id}/export`

**路径参数**: id (integer, 必填) - 工艺卡片ID

**请求参数**: format (string, 可选) - 导出格式：pdf/excel/word，默认pdf

**响应**: 返回文件下载

**权限**: `card:export`

### 8.5 工艺版本

#### 8.5.1 版本列表

**请求**: `GET /api/process-routes/{id}/versions`

**路径参数**: id (integer, 必填) - 工艺路线ID

**权限**: `process-route:version-view`

#### 8.5.2 创建新版本

**请求**: `POST /api/process-routes/{id}/versions`

**路径参数**: id (integer, 必填) - 工艺路线ID

**请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| change_description | string | 是 | 变更说明 |
| change_type | string | 否 | 变更类型：new/optimization/addition/deletion |
| copy_operations | boolean | 否 | 是否复制当前工序，默认true |

**权限**: `process-route:version-create`

#### 8.5.3 发布版本

**请求**: `PUT /api/process-routes/{id}/versions/{versionId}/publish`

**路径参数**: id (工艺路线ID), versionId (版本ID)

**权限**: `process-route:version-publish`

#### 8.5.4 版本对比

**请求**: `GET /api/process-routes/{id}/versions/{versionA}/compare/{versionB}`

**路径参数**: id (工艺路线ID), versionA (版本A ID), versionB (版本B ID)

**权限**: `process-route:version-view`

---
## 9. 资源管理API

### 9.1 设备

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 设备列表 | GET | /api/equipment | equipment:list |
| 创建设备 | POST | /api/equipment | equipment:create |
| 设备详情 | GET | /api/equipment/{id} | equipment:view |
| 更新设备 | PUT | /api/equipment/{id} | equipment:edit |
| 删除设备 | DELETE | /api/equipment/{id} | equipment:delete |

**设备列表请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| keyword | string | 否 | 搜索关键词 |
| type | string | 否 | 设备类型 |
| status | integer | 否 | 状态：0-停用，1-正常，2-维修中 |
| work_center | string | 否 | 工作中心 |

**创建设备请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 设备编码 |
| name | string | 是 | 设备名称 |
| type | string | 是 | 设备类型 |
| model | string | 否 | 规格型号 |
| manufacturer | string | 否 | 制造商 |
| work_center | string | 否 | 工作中心 |
| location | string | 否 | 安装位置 |
| power | string | 否 | 功率 |
| capacity | string | 否 | 加工能力 |
| purchase_date | string | 否 | 购置日期 |
| status | integer | 否 | 状态，默认1 |

### 9.2 工装夹具

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 工装夹具列表 | GET | /api/fixtures | fixture:list |
| 创建工装夹具 | POST | /api/fixtures | fixture:create |
| 工装夹具详情 | GET | /api/fixtures/{id} | fixture:view |
| 更新工装夹具 | PUT | /api/fixtures/{id} | fixture:edit |
| 删除工装夹具 | DELETE | /api/fixtures/{id} | fixture:delete |

**创建工装夹具请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 工装编码 |
| name | string | 是 | 工装名称 |
| type | string | 是 | 工装类型 |
| applicable_products | array | 否 | 适用产品 |
| drawing_no | string | 否 | 图号 |
| quantity | integer | 否 | 数量 |
| status | integer | 否 | 状态，默认1 |

### 9.3 刀具

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 刀具列表 | GET | /api/tools | tool:list |
| 创建刀具 | POST | /api/tools | tool:create |
| 刀具详情 | GET | /api/tools/{id} | tool:view |
| 更新刀具 | PUT | /api/tools/{id} | tool:edit |
| 删除刀具 | DELETE | /api/tools/{id} | tool:delete |

**创建刀具请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 刀具编码 |
| name | string | 是 | 刀具名称 |
| type | string | 是 | 刀具类型 |
| specification | string | 否 | 规格型号 |
| material | string | 否 | 材质 |
| manufacturer | string | 否 | 制造商 |
| stock_qty | integer | 否 | 库存数量 |
| status | integer | 否 | 状态，默认1 |

### 9.4 量具

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 量具列表 | GET | /api/measuring-tools | measuring-tool:list |
| 创建量具 | POST | /api/measuring-tools | measuring-tool:create |
| 量具详情 | GET | /api/measuring-tools/{id} | measuring-tool:view |
| 更新量具 | PUT | /api/measuring-tools/{id} | measuring-tool:edit |
| 删除量具 | DELETE | /api/measuring-tools/{id} | measuring-tool:delete |

**创建量具请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| code | string | 是 | 量具编码 |
| name | string | 是 | 量具名称 |
| type | string | 是 | 量具类型 |
| specification | string | 否 | 规格型号 |
| accuracy | string | 否 | 精度等级 |
| calibration_date | string | 否 | 校准日期 |
| calibration_cycle | integer | 否 | 校准周期（月） |
| status | integer | 否 | 状态，默认1 |

### 9.5 材料

> 注：材料管理API与物料管理API共用 `/api/materials` 路径，通过 `type=raw` 区分材料类型。

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 材料列表 | GET | /api/materials?type=raw | material:list |
| 创建材料 | POST | /api/materials | material:create |
| 材料详情 | GET | /api/materials/{id} | material:view |
| 更新材料 | PUT | /api/materials/{id} | material:edit |
| 删除材料 | DELETE | /api/materials/{id} | material:delete |

---

## 10. 工时定额API

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 工时定额列表 | GET | /api/time-quotas | time-quota:list |
| 创建工时定额 | POST | /api/time-quotas | time-quota:create |
| 工时定额详情 | GET | /api/time-quotas/{id} | time-quota:view |
| 更新工时定额 | PUT | /api/time-quotas/{id} | time-quota:edit |
| 删除工时定额 | DELETE | /api/time-quotas/{id} | time-quota:delete |
| 计算工时 | POST | /api/time-quotas/calculate | time-quota:calculate |

**工时定额列表请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| product_id | integer | 否 | 产品ID |
| route_id | integer | 否 | 工艺路线ID |
| operation_id | integer | 否 | 工序ID |
| type | string | 否 | 工时类型：preparation/processing/auxiliary |

**创建工时定额请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| product_id | integer | 是 | 产品ID |
| route_id | integer | 是 | 工艺路线ID |
| operation_id | integer | 是 | 工序ID |
| type | string | 是 | 工时类型 |
| preparation_time | integer | 否 | 准备工时（分钟） |
| processing_time | integer | 是 | 加工工时（分钟） |
| auxiliary_time | integer | 否 | 辅助工时（分钟） |
| batch_size | integer | 否 | 批量大小，默认1 |
| skill_level | string | 否 | 技能等级要求 |
| calculation_method | string | 否 | 计算方法 |
| remark | string | 否 | 备注 |
| status | integer | 否 | 状态，默认1 |

**计算工时请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| operation_id | integer | 是 | 工序ID |
| material | string | 否 | 材料类型 |
| dimensions | object | 否 | 加工尺寸 |
| complexity | string | 否 | 加工复杂度 |

---

## 11. 模板管理API

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 模板列表 | GET | /api/templates | template:list |
| 创建模板 | POST | /api/templates | template:create |
| 模板详情 | GET | /api/templates/{id} | template:view |
| 更新模板 | PUT | /api/templates/{id} | template:edit |
| 删除模板 | DELETE | /api/templates/{id} | template:delete |
| 应用模板 | POST | /api/templates/{id}/apply | template:apply |

**创建模板请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| name | string | 是 | 模板名称 |
| type | string | 是 | 模板类型：process_card/operation_card/inspection_card |
| category | string | 否 | 模板分类 |
| content | object | 是 | 模板内容（JSON） |
| description | string | 否 | 模板说明 |
| status | integer | 否 | 状态，默认1 |

---

## 12. 审批流程API

### 12.1 流程定义

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 流程定义列表 | GET | /api/workflows | workflow:list |
| 创建流程定义 | POST | /api/workflows | workflow:create |
| 流程定义详情 | GET | /api/workflows/{id} | workflow:view |
| 更新流程定义 | PUT | /api/workflows/{id} | workflow:edit |
| 删除流程定义 | DELETE | /api/workflows/{id} | workflow:delete |

**创建流程定义请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| name | string | 是 | 流程名称 |
| type | string | 是 | 流程类型：card_approval/route_approval/change_approval |
| nodes | array | 是 | 流程节点配置 |
| conditions | object | 否 | 流转条件 |
| status | integer | 否 | 状态，默认1 |

### 12.2 审批

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 审批列表 | GET | /api/approvals | approval:list |
| 发起审批 | POST | /api/approvals | approval:create |
| 审批详情 | GET | /api/approvals/{id} | approval:view |
| 审批通过 | POST | /api/approvals/{id}/approve | approval:approve |
| 审批驳回 | POST | /api/approvals/{id}/reject | approval:reject |
| 撤销审批 | POST | /api/approvals/{id}/cancel | approval:cancel |

**发起审批请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| workflow_id | integer | 是 | 流程定义ID |
| target_type | string | 是 | 审批对象类型 |
| target_id | integer | 是 | 审批对象ID |
| title | string | 是 | 审批标题 |
| description | string | 否 | 审批说明 |
| attachments | array | 否 | 附件列表 |

**审批通过/驳回请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| comment | string | 否 | 审批意见 |
| next_assignee_id | integer | 否 | 指定下一审批人 |

---

## 13. 文档管理API

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 文档列表 | GET | /api/documents | document:list |
| 上传文档 | POST | /api/documents/upload | document:upload |
| 文档详情 | GET | /api/documents/{id} | document:view |
| 更新文档 | PUT | /api/documents/{id} | document:edit |
| 删除文档 | DELETE | /api/documents/{id} | document:delete |
| 下载文档 | GET | /api/documents/{id}/download | document:download |
| 文档版本列表 | GET | /api/documents/{id}/versions | document:version-view |

**上传文档请求参数**: multipart/form-data
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| file | file | 是 | 文件 |
| name | string | 是 | 文档名称 |
| category | string | 否 | 文档分类 |
| tags | array | 否 | 标签 |
| description | string | 否 | 文档说明 |

---
## 14. AI功能API

### 14.1 AI助手

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| AI对话 | POST | /api/ai/chat | ai:chat |
| 会话列表 | GET | /api/ai/sessions | ai:session-list |
| 创建会话 | POST | /api/ai/sessions | ai:session-create |
| 删除会话 | DELETE | /api/ai/sessions/{id} | ai:session-delete |
| 会话消息 | GET | /api/ai/sessions/{id}/messages | ai:message-view |

**AI对话请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| session_id | string | 否 | 会话ID，不传则创建新会话 |
| message | string | 是 | 用户消息 |
| context | object | 否 | 上下文信息（产品ID、工艺路线ID等） |
| stream | boolean | 否 | 是否流式输出，默认false |

### 14.2 AI决策

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 工艺路线推荐 | POST | /api/ai/decision/process-route | ai:decision |
| 参数优化建议 | POST | /api/ai/decision/parameters | ai:decision |
| 工时智能计算 | POST | /api/ai/decision/time-quota | ai:decision |
| 材料选择建议 | POST | /api/ai/decision/material | ai:decision |
| 设备匹配推荐 | POST | /api/ai/decision/equipment | ai:decision |

**工艺路线推荐请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| product_id | integer | 是 | 产品ID |
| material | string | 是 | 材料类型 |
| process_type | string | 否 | 工艺类型 |
| constraints | object | 否 | 约束条件 |

**参数优化建议请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| operation_id | integer | 是 | 工序ID |
| current_params | object | 是 | 当前工艺参数 |
| target | string | 否 | 优化目标：quality/efficiency/cost |

### 14.3 知识库

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 知识库列表 | GET | /api/ai/knowledge | knowledge:list |
| 添加知识 | POST | /api/ai/knowledge | knowledge:create |
| 更新知识 | PUT | /api/ai/knowledge/{id} | knowledge:edit |
| 删除知识 | DELETE | /api/ai/knowledge/{id} | knowledge:delete |

**添加知识请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| title | string | 是 | 知识标题 |
| category | string | 是 | 知识分类 |
| content | string | 是 | 知识内容 |
| tags | array | 否 | 标签 |
| source | string | 否 | 来源 |
| status | integer | 否 | 状态，默认1 |

---

## 15. 企业微信API

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 企微回调验证 | GET | /api/wecom/callback | 无需认证 |
| 接收企微事件 | POST | /api/wecom/callback | 无需认证 |
| 发送企微消息 | POST | /api/wecom/message/send | wecom:message |
| 同步企微用户 | GET | /api/wecom/users | wecom:sync |
| 同步企微部门 | GET | /api/wecom/departments | wecom:sync |

**发送企微消息请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| touser | string | 否 | 成员ID列表 |
| toparty | string | 否 | 部门ID列表 |
| totag | string | 否 | 标签ID列表 |
| msgtype | string | 是 | 消息类型：text/card/file |
| content | object | 是 | 消息内容 |

---

## 16. 外部系统集成API

### 16.1 CAXA PLM

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 同步产品数据 | POST | /api/integration/plm/sync-product | integration:plm |
| 同步BOM数据 | POST | /api/integration/plm/sync-bom | integration:plm |
| PLM回调 | GET | /api/integration/plm/callback | 无需认证 |

**同步产品数据请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| action | string | 是 | 操作类型：create/update/delete |
| product_data | object | 是 | 产品数据 |
| sync_time | string | 是 | 同步时间 |

### 16.2 U8 ERP

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 同步物料 | POST | /api/integration/u8/sync-material | integration:u8 |
| 推送工艺数据 | POST | /api/integration/u8/push-process | integration:u8 |
| 查询库存 | GET | /api/integration/u8/inventory | integration:u8 |

**查询库存请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| material_code | string | 否 | 物料编码 |
| warehouse | string | 否 | 仓库编码 |

### 16.3 MES

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 下发工艺文件 | POST | /api/integration/mes/push-process | integration:mes |
| 接收MES回调 | POST | /api/integration/mes/callback | 无需认证 |
| 获取生产数据 | GET | /api/integration/mes/production-data | integration:mes |

**下发工艺文件请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| card_id | integer | 是 | 工艺卡片ID |
| production_order | string | 是 | 生产订单号 |
| quantity | integer | 是 | 生产数量 |

---

## 17. 统计分析API

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 工艺统计 | GET | /api/statistics/process | statistics:view |
| 工时分析 | GET | /api/statistics/time | statistics:view |
| 设备利用率 | GET | /api/statistics/equipment | statistics:view |
| 导出报表 | GET | /api/statistics/export | statistics:export |

**工艺统计请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| start_date | string | 是 | 开始日期 |
| end_date | string | 是 | 结束日期 |
| product_id | integer | 否 | 产品ID |
| dept_id | integer | 否 | 部门ID |
| group_by | string | 否 | 分组字段：product/dept/type |

**工时分析请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| start_date | string | 是 | 开始日期 |
| end_date | string | 是 | 结束日期 |
| type | string | 否 | 工时类型 |
| group_by | string | 否 | 分组字段 |

---

## 18. 系统设置API

| 接口 | 方法 | 路径 | 权限 |
|------|------|------|------|
| 系统参数列表 | GET | /api/settings | setting:view |
| 更新系统参数 | PUT | /api/settings | setting:edit |
| 数据字典列表 | GET | /api/dictionaries | dictionary:list |
| 获取字典类型数据 | GET | /api/dictionaries/{type} | dictionary:view |
| 操作日志 | GET | /api/logs | log:view |
| 登录日志 | GET | /api/logs/login | log:view |

**更新系统参数请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| settings | array | 是 | 参数数组 |
| settings[].key | string | 是 | 参数键 |
| settings[].value | string | 是 | 参数值 |

**操作日志请求参数**:
| 参数 | 类型 | 必填 | 说明 |
|-----|------|-----|------|
| page | integer | 否 | 页码 |
| per_page | integer | 否 | 每页数量 |
| user_id | integer | 否 | 用户ID |
| module | string | 否 | 模块 |
| action | string | 否 | 操作类型 |
| start_time | string | 否 | 开始时间 |
| end_time | string | 否 | 结束时间 |

---

## 19. Webhook回调API

| 接口 | 方法 | 路径 | 说明 |
|------|------|------|------|
| PLM系统回调 | POST | /api/webhook/plm | 接收PLM系统事件通知 |
| U8系统回调 | POST | /api/webhook/u8 | 接收U8系统事件通知 |
| MES系统回调 | POST | /api/webhook/mes | 接收MES系统事件通知 |
| 企业微信回调 | POST | /api/webhook/wecom | 接收企业微信事件通知 |

**Webhook请求体**:
```json
{
  "event": "product.updated",
  "source": "plm",
  "timestamp": 1717718400,
  "data": {},
  "signature": "hmac_signature"
}
```

---
## 20. API安全设计

### 20.1 请求签名

所有外部系统调用接口需进行请求签名验证。

**签名算法**: HMAC-SHA256

**签名步骤**:
1. 将请求参数按key字母排序
2. 拼接成 `key1=value1&key2=value2` 格式
3. 追加 `&secret={app_secret}`
4. 对拼接字符串进行HMAC-SHA256签名
5. 将签名转为大写十六进制

**请求头**:
```
X-App-ID: {app_id}
X-Timestamp: {unix_timestamp}
X-Nonce: {random_string}
X-Signature: {signature}
```

### 20.2 防重放攻击

- 请求时间戳与服务器时间差不能超过5分钟
- Nonce在5分钟内不能重复使用
- 服务器记录已使用的Nonce（Redis存储，TTL=300s）

### 20.3 接口限流

| 限流维度 | 规则 | 说明 |
|---------|------|------|
| 全局限流 | 1000次/分钟 | 防止系统过载 |
| 用户限流 | 100次/分钟 | 防止单个用户滥用 |
| 登录限流 | 5次/分钟 | 防止暴力破解 |
| 敏感操作限流 | 10次/分钟 | 防止恶意操作 |

**限流响应**:
```json
{
  "code": 42901,
  "message": "请求过于频繁，请稍后重试",
  "data": {"retry_after": 30},
  "timestamp": 1717718400
}
```

### 20.4 IP白名单

- 外部系统调用接口需配置IP白名单
- 白名单在系统设置中管理
- 支持单个IP和CIDR格式

### 20.5 数据加密

| 数据类型 | 加密方式 | 说明 |
|---------|---------|------|
| 密码 | bcrypt | 存储时加密 |
| 传输密码 | RSA-2048 | 登录时前端加密 |
| 敏感数据 | AES-256-CBC | 身份证号、手机号等 |
| HTTPS | TLS 1.2+ | 传输层加密 |

---

## 21. API版本管理

### 21.1 版本策略

- URL路径版本化: `/api/v1/...`
- 当前版本: v1
- 向后兼容原则：新增字段不影响旧版本

### 21.2 版本迁移

- 新版本发布后，旧版本保留至少6个月
- 废弃版本响应头添加 `X-API-Deprecated: true`
- 废弃版本响应头添加 `X-API-Sunset: 2026-12-31`

### 21.3 版本响应头

```
X-API-Version: v1
X-API-Deprecated: false
```

---

## 22. PHP实现示例

### 22.1 路由设计

```php
<?php
// config/routes.php
return [
    // 认证
    'POST /api/auth/login' => 'AuthController@login',
    'POST /api/auth/qrcode-login' => 'AuthController@qrcodeLogin',
    'POST /api/auth/refresh-token' => 'AuthController@refreshToken',
    'POST /api/auth/logout' => 'AuthController@logout',
    'GET /api/auth/user-info' => 'AuthController@userInfo',
    
    // 用户管理
    'GET /api/users' => 'UserController@index',
    'POST /api/users' => 'UserController@store',
    'GET /api/users/{id}' => 'UserController@show',
    'PUT /api/users/{id}' => 'UserController@update',
    'DELETE /api/users/{id}' => 'UserController@destroy',
    'PUT /api/users/{id}/status' => 'UserController@status',
    
    // 工艺路线
    'GET /api/process-routes' => 'ProcessRouteController@index',
    'POST /api/process-routes' => 'ProcessRouteController@store',
    'GET /api/process-routes/{id}' => 'ProcessRouteController@show',
    'PUT /api/process-routes/{id}' => 'ProcessRouteController@update',
    'DELETE /api/process-routes/{id}' => 'ProcessRouteController@destroy',
    'POST /api/process-routes/{id}/copy' => 'ProcessRouteController@copy',
    
    // 工序
    'GET /api/process-routes/{routeId}/operations' => 'OperationController@index',
    'POST /api/process-routes/{routeId}/operations' => 'OperationController@store',
    'GET /api/operations/{id}' => 'OperationController@show',
    'PUT /api/operations/{id}' => 'OperationController@update',
    'DELETE /api/operations/{id}' => 'OperationController@destroy',
    'PUT /api/operations/{id}/sort' => 'OperationController@sort',
];
```

### 22.2 路由分发器

```php
<?php
// core/Router.php
class Router
{
    private $routes = [];
    private $basePath = '/api/v1';
    
    public function __construct()
    {
        $this->routes = require __DIR__ . '/../config/routes.php';
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // 移除基础路径
        $uri = str_replace($this->basePath, '', $uri);
        
        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);
            
            if ($routeMethod !== $method) {
                continue;
            }
            
            $params = $this->matchRoute($routePath, $uri);
            if ($params !== false) {
                return $this->callController($handler, $params);
            }
        }
        
        Response::error(40401, '接口不存在', null, 404);
    }
    
    private function matchRoute($pattern, $uri)
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }
        
        return false;
    }
    
    private function callController($handler, $params)
    {
        [$controller, $method] = explode('@', $handler);
        $controllerClass = "App\\Controllers\\" . $controller;
        
        if (!class_exists($controllerClass)) {
            Response::error(50001, '控制器不存在', null, 500);
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            Response::error(50001, '方法不存在', null, 500);
        }
        
        return $controller->$method($params);
    }
}
```

### 22.3 控制器基类

```php
<?php
// core/BaseController.php
class BaseController
{
    protected $db;
    protected $currentUser;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    protected function validate($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $ruleArray = explode('|', $ruleSet);
            
            foreach ($ruleArray as $rule) {
                if ($rule === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
                    $errors[$field][] = "{$field}不能为空";
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if (isset($data[$field]) && strlen($data[$field]) > $max) {
                        $errors[$field][] = "{$field}长度不能超过{$max}";
                    }
                }
                
                if ($rule === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "{$field}格式不正确";
                }
            }
        }
        
        if (!empty($errors)) {
            Response::error(40001, '参数验证失败', ['errors' => $errors], 400);
        }
        
        return $data;
    }
    
    protected function paginate($query, $page = 1, $perPage = 20)
    {
        $page = max(1, (int)$page);
        $perPage = min(100, max(1, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        
        $total = $this->db->query("SELECT COUNT(*) as count FROM ({$query}) t")->fetch()['count'];
        $result = $this->db->query("{$query} LIMIT {$perPage} OFFSET {$offset}")->fetchAll();
        
        return [
            'list' => $result,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => (int)ceil($total / $perPage)
            ]
        ];
    }
    
    protected function requirePermission($permission)
    {
        if (!$this->currentUser) {
            Response::error(40101, '未登录', null, 401);
        }
        
        $permissions = $this->getUserPermissions($this->currentUser['id']);
        
        // 支持通配符权限
        foreach ($permissions as $perm) {
            if ($perm === $permission || $perm === substr($permission, 0, strpos($permission, ':')) . ':*') {
                return true;
            }
        }
        
        Response::error(40301, '无权限访问', null, 403);
    }
    
    private function getUserPermissions($userId)
    {
        // 从缓存或数据库获取用户权限
        $cacheKey = "user_permissions:{$userId}";
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            return json_decode($cached, true);
        }
        
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.code 
            FROM user_roles ur
            JOIN role_permissions rp ON ur.role_id = rp.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$userId]);
        $permissions = array_column($stmt->fetchAll(), 'code');
        
        Cache::set($cacheKey, json_encode($permissions), 3600);
        
        return $permissions;
    }
}
```

### 22.4 中间件实现

```php
<?php
// middleware/AuthMiddleware.php
class AuthMiddleware
{
    public function handle()
    {
        // 跳过认证的路由
        $skipRoutes = [
            'POST /api/auth/login',
            'POST /api/auth/qrcode-login',
            'POST /api/auth/refresh-token',
            'GET /api/wecom/callback',
            'POST /api/wecom/callback',
        ];
        
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $currentRoute = "{$method} {$uri}";
        
        foreach ($skipRoutes as $skipRoute) {
            if (strpos($currentRoute, $skipRoute) === 0) {
                return true;
            }
        }
        
        // 获取Token
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            Response::error(40101, '未提供认证令牌', null, 401);
        }
        
        $token = YOUR_SECRET];
        
        // 验证Token
        try {
            $payload = JWT::decode($token, JWT_SECRET, ['HS256']);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'expired') !== false) {
                Response::error(40102, '令牌已过期', null, 401);
            }
            Response::error(40101, '令牌无效', null, 401);
        }
        
        // 检查Token是否在Redis中被标记为失效
        if (Cache::get("token_blacklist:{$payload->jti}")) {
            Response::error(40101, '令牌已失效', null, 401);
        }
        
        // 检查用户状态
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, username, status FROM users WHERE id = ?");
        $stmt->execute([$payload->sub]);
        $user = $stmt->fetch();
        
        if (!$user) {
            Response::error(40101, '用户不存在', null, 401);
        }
        
        if ($user['status'] == 0) {
            Response::error(40302, '账号已被禁用', null, 403);
        }
        
        // 将用户信息存入全局
        $GLOBALS['currentUser'] = $user;
        
        return true;
    }
}
```

```php
<?php
// middleware/RateLimitMiddleware.php
class RateLimitMiddleware
{
    public function handle()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = $GLOBALS['currentUser']['id'] ?? null;
        $uri = $_SERVER['REQUEST_URI'];
        
        // 登录接口特殊限流
        if (strpos($uri, '/api/auth/login') !== false) {
            $key = "rate_limit:login:{$ip}";
            $limit = 5;
            $window = 60;
        } elseif ($userId) {
            $key = "rate_limit:user:{$userId}";
            $limit = 100;
            $window = 60;
        } else {
            $key = "rate_limit:ip:{$ip}";
            $limit = 1000;
            $window = 60;
        }
        
        $current = Cache::incr($key);
        
        if ($current === 1) {
            Cache::expire($key, $window);
        }
        
        if ($current > $limit) {
            Response::error(42901, '请求过于频繁', ['retry_after' => $window], 429);
        }
        
        return true;
    }
}
```

### 22.5 响应封装

```php
<?php
// core/Response.php
class Response
{
    public static function success($data = null, $message = 'success', $code = 200)
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ];
        
        self::send($response, 200);
    }
    
    public static function error($code, $message = 'error', $data = null, $httpStatus = 200)
    {
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ];
        
        self::send($response, $httpStatus);
    }
    
    public static function paginate($list, $pagination, $message = 'success')
    {
        self::success([
            'list' => $list,
            'pagination' => $pagination
        ], $message);
    }
    
    private static function send($response, $httpStatus)
    {
        http_response_code($httpStatus);
        header('Content-Type: application/json; charset=utf-8');
        header('X-API-Version: v1');
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
```

### 22.6 错误处理

```php
<?php
// core/ErrorHandler.php
class ErrorHandler
{
    public static function register()
    {
        // 捕获致命错误
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                Response::error(50001, '服务器内部错误', null, 500);
            }
        });
        
        // 捕获异常
        set_exception_handler(function($exception) {
            // 记录错误日志
            Logger::error('Uncaught Exception', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            // 开发环境返回详细错误
            if (APP_ENV === 'development') {
                Response::error(50001, $exception->getMessage(), [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ], 500);
            }
            
            Response::error(50001, '服务器内部错误', null, 500);
        });
        
        // 捕获PHP警告/通知
        set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            
            Logger::warning('PHP Error', [
                'severity' => $severity,
                'message' => $message,
                'file' => $file,
                'line' => $line
            ]);
            
            return true;
        });
    }
}

// 自定义异常类
class BusinessException extends Exception
{
    protected $code;
    protected $httpStatus;
    
    public function __construct($code, $message, $httpStatus = 200)
    {
        parent::__construct($message, $code);
        $this->httpStatus = $httpStatus;
    }
    
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }
}
```

### 22.7 入口文件

```php
<?php
// public/index.php
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/ErrorHandler.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Cache.php';
require_once __DIR__ . '/../core/JWT.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/RateLimitMiddleware.php';

// 注册错误处理
ErrorHandler::register();

// 设置时区
date_default_timezone_set('Asia/Shanghai');

// 执行中间件
$authMiddleware = new AuthMiddleware();
$authMiddleware->handle();

$rateLimitMiddleware = new RateLimitMiddleware();
$rateLimitMiddleware->handle();

// 路由分发
$router = new Router();
$router->dispatch();
```

---

## 附录

### A. 数据库表命名规范

| 模块 | 表名前缀 | 示例 |
|-----|---------|------|
| 用户权限 | sys_ | sys_users, sys_roles, sys_permissions |
| 部门 | sys_ | sys_departments |
| 产品 | prod_ | prod_products, prod_bom, prod_versions |
| 物料 | mat_ | mat_materials |
| 工艺 | process_ | process_routes, process_operations, process_steps |
| 工艺卡片 | card_ | card_templates, card_cards |
| 资源 | res_ | res_equipment, res_fixtures, res_tools, res_measuring_tools |
| 工时 | time_ | time_quotas |
| 审批 | wf_ | wf_workflows, wf_approvals, wf_nodes |
| 文档 | doc_ | doc_documents, doc_versions |
| AI | ai_ | ai_sessions, ai_messages, ai_knowledge |
| 日志 | log_ | log_operations, log_login |
| 系统 | sys_ | sys_settings, sys_dictionaries |

### B. 常用枚举值

**产品分类**:
- `diaphragm_compressor` - 工业压缩机
- `pressure_vessel` - 压力容器

**物料类型**:
- `raw` - 原材料
- `part` - 零件
- `assembly` - 组件
- `standard` - 标准件
- `consumable` - 消耗品

**工艺类型**:
- `machining` - 机加工艺
- `welding` - 焊接工艺
- `assembly` - 装配工艺
- `heat_treatment` - 热处理工艺
- `surface_treatment` - 表面处理工艺

**工序类型**:
- `cutting` - 下料
- `turning` - 车削
- `milling` - 铣削
- `drilling` - 钻孔
- `grinding` - 磨削
- `welding` - 焊接
- `assembly` - 装配
- `inspection` - 检验
- `heat_treatment` - 热处理

**状态值**:
- `0` - 草稿/禁用/停用/已归档
- `1` - 已发布/启用/正常/在用

---

*文档结束*
