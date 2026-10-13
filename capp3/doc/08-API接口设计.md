# CAPP系统API接口设计说明书

## 目录

- [1. 概述](#1-概述)
  - [1.1 文档目的](#11-文档目的)
  - [1.2 适用范围](#12-适用范围)
  - [1.3 术语定义](#13-术语定义)
- [2. 接口设计规范](#2-接口设计规范)
  - [2.1 RESTful风格定义](#21-restful风格定义)
  - [2.2 URL规范](#22-url规范)
  - [2.3 请求格式规范](#23-请求格式规范)
  - [2.4 响应格式规范](#24-响应格式规范)
  - [2.5 状态码定义](#25-状态码定义)
  - [2.6 通用请求头](#26-通用请求头)
- [3. 认证与授权](#3-认证与授权)
  - [3.1 Token机制概述](#31-token机制概述)
  - [3.2 JWT设计](#32-jwt设计)
  - [3.3 接口权限控制](#33-接口权限控制)
  - [3.4 Token刷新机制](#34-token刷新机制)
- [4. 公共接口](#4-公共接口)
  - [4.1 用户登录](#41-用户登录)
  - [4.2 用户登出](#42-用户登出)
  - [4.3 Token刷新](#43-token刷新)
  - [4.4 文件上传](#44-文件上传)
  - [4.5 文件下载](#45-文件下载)
  - [4.6 获取当前用户信息](#46-获取当前用户信息)
- [5. 用户管理接口](#5-用户管理接口)
  - [5.1 用户CRUD](#51-用户crud)
  - [5.2 角色管理](#52-角色管理)
  - [5.3 权限管理](#53-权限管理)
  - [5.4 部门管理](#54-部门管理)
- [6. 产品/零件接口](#6-产品零件接口)
  - [6.1 产品CRUD](#61-产品crud)
  - [6.2 BOM管理](#62-bom管理)
  - [6.3 零件查询](#63-零件查询)
- [7. 工艺管理接口](#7-工艺管理接口)
  - [7.1 工艺路线CRUD](#71-工艺路线crud)
  - [7.2 工序管理](#72-工序管理)
  - [7.3 工艺卡片](#73-工艺卡片)
  - [7.4 工艺审批](#74-工艺审批)
  - [7.5 工艺版本](#75-工艺版本)
- [8. 企业微信接口](#8-企业微信接口)
  - [8.1 扫码登录](#81-扫码登录)
  - [8.2 用户同步](#82-用户同步)
  - [8.3 消息推送](#83-消息推送)
- [9. AI接口](#9-ai接口)
  - [9.1 对话接口](#91-对话接口)
  - [9.2 决策接口](#92-决策接口)
  - [9.3 知识库查询](#93-知识库查询)
- [10. 外部系统对接接口](#10-外部系统对接接口)
  - [10.1 数据同步触发](#101-数据同步触发)
  - [10.2 同步状态查询](#102-同步状态查询)
  - [10.3 数据推送接收](#103-数据推送接收)
- [11. 接口版本管理策略](#11-接口版本管理策略)
- [12. 接口限流与防重放设计](#12-接口限流与防重放设计)
  - [12.1 限流策略](#121-限流策略)
  - [12.2 防重放设计](#122-防重放设计)
  - [12.3 签名验证机制](#123-签名验证机制)

---

## 1. 概述

### 1.1 文档目的

本文档为CAPP（计算机辅助工艺规划）系统的API接口设计说明书，旨在为前后端开发、第三方系统集成、测试及运维人员提供统一、完整、可执行的接口规范。本文档覆盖系统全部业务模块的接口定义，包括认证授权、用户管理、产品零件管理、工艺管理、企业微信集成、AI服务及外部系统对接等核心领域。

### 1.2 适用范围

- **前端开发团队**：基于本文档进行前后端数据交互开发
- **后端开发团队**：基于本文档进行PHP原生接口实现
- **第三方系统集成方**：基于本文档进行ERP/MES/PDM等系统对接
- **测试团队**：基于本文档编写接口测试用例
- **运维团队**：基于本文档配置API网关、限流及监控策略

### 1.3 术语定义

| 术语 | 英文全称 | 说明 |
|------|----------|------|
| CAPP | Computer Aided Process Planning | 计算机辅助工艺规划 |
| JWT | JSON Web Token | 基于JSON的开放标准令牌 |
| BOM | Bill of Materials | 物料清单 |
| PDM | Product Data Management | 产品数据管理 |
| ERP | Enterprise Resource Planning | 企业资源计划 |
| MES | Manufacturing Execution System | 制造执行系统 |
| REST | Representational State Transfer | 表述性状态传递架构风格 |
| RBAC | Role-Based Access Control | 基于角色的访问控制 |

---

## 2. 接口设计规范

### 2.1 RESTful风格定义

系统所有业务接口严格遵循RESTful架构风格，通过HTTP方法表达操作语义，通过URL定位资源。针对PHP原生实现，采用自研路由分发器解析URL并映射到对应的控制器方法。

| HTTP方法 | 语义 | 使用场景 | 幂等性 |
|----------|------|----------|--------|
| GET | 获取资源 | 查询、列表、详情 | 是 |
| POST | 创建资源 | 新增、提交、执行操作 | 否 |
| PUT | 全量更新 | 完整替换资源 | 是 |
| PATCH | 局部更新 | 修改部分字段 | 否 |
| DELETE | 删除资源 | 物理/逻辑删除 | 是 |

对于不符合标准CRUD语义的业务操作（如审批、发布、复制等），采用`POST`方法配合动作标识实现，例如：`POST /api/v1/process-route/{id}/approve`。

### 2.2 URL规范

**基础格式**：

```
https://{domain}/api/{version}/{module}/{resource}[/{id}][/{action}]
```

**URL组成说明**：

| 组成部分 | 说明 | 示例 |
|----------|------|------|
| `https://{domain}` | 服务器域名或IP | `https://capp.company.com` |
| `/api` | API根路径 | 固定前缀 |
| `/{version}` | 接口版本号 | `v1`、`v2` |
| `/{module}` | 业务模块标识 | `auth`、`user`、`product`、`process` |
| `/{resource}` | 资源名称（复数形式） | `users`、`products`、`routes` |
| `/{id}` | 资源唯一标识 | 数字ID或UUID |
| `/{action}` | 自定义动作 | `approve`、`publish`、`copy` |

**命名规范**：
- 全部使用小写字母
- 单词间使用连字符`-`分隔（kebab-case）
- 资源名称使用复数形式
- 禁止在URL中使用动词（HTTP方法已表达动作语义）

**URL示例**：

```
GET    /api/v1/products              # 获取产品列表
GET    /api/v1/products/1001          # 获取产品详情
POST   /api/v1/products              # 创建产品
PUT    /api/v1/products/1001        # 全量更新产品
PATCH  /api/v1/products/1001        # 局部更新产品
DELETE /api/v1/products/1001        # 删除产品
POST   /api/v1/products/1001/copy   # 复制产品
```

### 2.3 请求格式规范

**Content-Type**：
- 标准请求：`application/json`
- 文件上传：`multipart/form-data`
- 表单提交：`application/x-www-form-urlencoded`（兼容场景）

**请求参数位置**：

| 参数类型 | 传递位置 | 适用方法 | 说明 |
|----------|----------|----------|------|
| 路径参数 | URL路径 | GET/PUT/PATCH/DELETE | 资源标识，如 `/users/{id}` |
| 查询参数 | URL查询字符串 | GET | 筛选、分页、排序，如 `?page=1&size=20` |
| 请求体参数 | Request Body | POST/PUT/PATCH | JSON格式对象 |
| 请求头参数 | HTTP Header | 全部 | 认证、版本、签名等 |

**分页参数规范**：

所有列表查询接口统一采用以下分页参数：

| 参数名 | 类型 | 必填 | 默认值 | 说明 |
|--------|------|------|--------|------|
| page | int | 否 | 1 | 当前页码，从1开始 |
| size | int | 否 | 20 | 每页记录数，最大100 |
| sort | string | 否 | - | 排序字段，格式 `field:direction`，如 `created_at:desc` |

### 2.4 响应格式规范

所有接口返回统一的JSON响应结构：

```json
{
  "code": 0,
  "message": "success",
  "data": {},
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

**字段说明**：

| 字段名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| code | int | 是 | 业务状态码，`0`表示成功，非`0`表示失败 |
| message | string | 是 | 提示信息，成功时为`success`，失败时为错误描述 |
| data | any | 否 | 响应数据，类型根据接口不同而变化 |
| timestamp | int | 是 | 服务器响应时间戳（Unix秒） |
| request_id | string | 是 | 请求唯一标识，用于日志追踪和问题排查 |

**列表响应数据结构**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [],
    "pagination": {
      "page": 1,
      "size": 20,
      "total": 156,
      "total_pages": 8,
      "has_next": true,
      "has_prev": false
    }
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 2.5 状态码定义

**HTTP状态码**：

| 状态码 | 含义 | 使用场景 |
|--------|------|----------|
| 200 | OK | 请求成功 |
| 201 | Created | 资源创建成功 |
| 204 | No Content | 删除成功，无返回体 |
| 400 | Bad Request | 请求参数错误、格式错误 |
| 401 | Unauthorized | 未认证或Token失效 |
| 403 | Forbidden | 无权限访问 |
| 404 | Not Found | 资源不存在 |
| 409 | Conflict | 资源冲突（如重复创建） |
| 422 | Unprocessable Entity | 业务校验失败 |
| 429 | Too Many Requests | 请求过于频繁，触发限流 |
| 500 | Internal Server Error | 服务器内部错误 |
| 503 | Service Unavailable | 服务暂时不可用 |

**业务状态码（code字段）**：

| 状态码 | 含义 | 说明 |
|--------|------|------|
| 0 | 成功 | 请求处理成功 |
| 10001 | 参数错误 | 请求参数缺失或格式不正确 |
| 10002 | 认证失败 | Token无效、过期或缺失 |
| 10003 | 权限不足 | 当前用户无操作权限 |
| 10004 | 资源不存在 | 请求的资源未找到 |
| 10005 | 资源冲突 | 数据重复或状态冲突 |
| 10006 | 业务校验失败 | 违反业务规则 |
| 10007 | 请求过于频繁 | 触发限流策略 |
| 10008 | 签名错误 | 请求签名验证失败 |
| 10009 | 重放攻击 | 请求时间戳过期或nonce重复 |
| 20001 | 数据库错误 | 数据库操作异常 |
| 20002 | 外部系统错误 | 调用第三方系统失败 |
| 20003 | 文件操作错误 | 上传/下载/读写文件失败 |
| 20004 | AI服务错误 | AI模块调用异常 |
| 99999 | 系统内部错误 | 未知异常 |

### 2.6 通用请求头

| 请求头 | 必填 | 说明 | 示例 |
|--------|------|------|------|
| Authorization | 是（除登录外） | 认证令牌 | `Bearer eyJhbGciOiJIUzI1NiIs...` |
| Content-Type | 是（POST/PUT/PATCH） | 内容类型 | `application/json` |
| X-Request-ID | 否 | 客户端请求标识 | `req_8f3a2b1c4d5e6f7a` |
| X-API-Version | 否 | 接口版本 | `v1` |
| X-Timestamp | 是（开放接口） | 请求时间戳（Unix秒） | `1717651200` |
| X-Nonce | 是（开放接口） | 随机字符串，防重放 | `a1b2c3d4e5f6` |
| X-Signature | 是（开放接口） | 请求签名 | `sha256=abc123...` |

---

## 3. 认证与授权

### 3.1 Token机制概述

系统采用双Token机制（Access Token + Refresh Token）实现无状态认证：

- **Access Token**：短期有效（默认2小时），用于接口访问认证，存储在HTTP Header中
- **Refresh Token**：长期有效（默认7天），用于Access Token过期后刷新，存储在HTTP Only Cookie中

Token由PHP原生代码基于`firebase/php-jwt`库生成和校验（若环境允许），或基于自研JWT实现（纯原生环境）。Token载荷中携带用户ID、角色、部门及权限摘要，避免每次请求查询数据库。

### 3.2 JWT设计

**Access Token结构**：

```json
{
  "header": {
    "alg": "HS256",
    "typ": "JWT"
  },
  "payload": {
    "iss": "capp-system",
    "sub": "user_10001",
    "aud": "capp-api",
    "exp": 1717658400,
    "nbf": 1717651200,
    "iat": 1717651200,
    "jti": "jwt_a1b2c3d4e5f6",
    "data": {
      "user_id": 10001,
      "username": "zhangsan",
      "real_name": "张三",
      "dept_id": 5,
      "role_ids": [1, 3],
      "permissions": ["process:create", "process:edit", "product:view"]
    }
  }
}
```

**JWT字段说明**：

| 字段 | 全称 | 说明 |
|------|------|------|
| iss | Issuer | 签发者，固定为`capp-system` |
| sub | Subject | 主题，用户唯一标识 |
| aud | Audience | 受众，固定为`capp-api` |
| exp | Expiration Time | 过期时间（Unix时间戳） |
| nbf | Not Before | 生效时间（Unix时间戳） |
| iat | Issued At | 签发时间（Unix时间戳） |
| jti | JWT ID | 令牌唯一标识，用于黑名单机制 |
| data | Custom Data | 自定义业务数据 |

**密钥管理**：
- Access Token签名密钥：存储于服务器环境变量`JWT_ACCESS_SECRET`，长度不少于256位
- Refresh Token签名密钥：存储于服务器环境变量`JWT_REFRESH_SECRET`，与Access Token密钥隔离
- 密钥定期轮换机制：支持双密钥并行验证期，平滑过渡

### 3.3 接口权限控制

系统采用RBAC（Role-Based Access Control）模型，结合数据权限实现细粒度控制。

**权限标识格式**：

```
{module}:{action}
```

例如：`user:create`、`process:approve`、`product:delete`。

**权限控制层级**：

1. **接口级权限**：通过路由中间件校验用户是否拥有接口对应的权限标识
2. **数据级权限**：通过部门隔离、数据范围（全部/本部门/本人）控制数据可见性
3. **字段级权限**：通过响应数据过滤器控制敏感字段返回（如成本字段仅财务可见）

**权限校验流程**：

```
请求到达 → 解析JWT → 验证Token有效性 → 提取权限列表 → 匹配接口所需权限 → 校验数据范围 → 执行业务逻辑
```

### 3.4 Token刷新机制

当Access Token过期时，客户端使用Refresh Token申请新的Access Token，无需重新登录。

**刷新规则**：
- Refresh Token在有效期内可无限次刷新Access Token
- 每次刷新生成新的Access Token和Refresh Token（Refresh Token轮转）
- Refresh Token过期前3天，接口响应头中携带`X-Token-Refresh-Required: true`提示客户端
- 用户主动修改密码、登出或管理员强制下线时，Refresh Token加入Redis黑名单

---

## 4. 公共接口

### 4.1 用户登录

**接口说明**：账号密码登录，成功后返回Access Token，Refresh Token写入Cookie。

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/login` |
| Method | POST |
| 权限 | 公开 |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名/工号 |
| password | string | 是 | 密码（前端RSA加密传输） |
| captcha_key | string | 条件 | 验证码标识（失败3次后必填） |
| captcha_code | string | 条件 | 验证码内容（失败3次后必填） |

**请求示例**：

```json
{
  "username": "zhangsan",
  "password": "encrypted_password_base64",
  "captcha_key": "cap_a1b2c3",
  "captcha_code": "7x9k"
}
```

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| access_token | string | Access Token |
| expires_in | int | Access Token有效期（秒） |
| token_type | string | 固定为`Bearer` |
| user | object | 用户基本信息 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 7200,
    "token_type": "Bearer",
    "user": {
      "user_id": 10001,
      "username": "zhangsan",
      "real_name": "张三",
      "avatar": "https://capp.company.com/static/avatar/10001.jpg",
      "dept_name": "工艺部",
      "role_names": ["工艺员", "审核员"]
    }
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 4.2 用户登出

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/logout` |
| Method | POST |
| 权限 | 需认证 |

**请求参数**：无

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": null,
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

**处理逻辑**：将当前Access Token和Refresh Token加入Redis黑名单，有效期与Token剩余有效期一致。

### 4.3 Token刷新

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/refresh` |
| Method | POST |
| 权限 | 需Refresh Token（Cookie携带） |

**请求参数**：无（Refresh Token从Cookie自动获取）

**响应参数**：同登录接口

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 7200,
    "token_type": "Bearer"
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 4.4 文件上传

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/files/upload` |
| Method | POST |
| 权限 | 需认证 |
| Content-Type | `multipart/form-data` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| file | file | 是 | 文件二进制数据 |
| module | string | 是 | 业务模块标识，决定存储路径：`product`/`process`/`drawing` |
| file_type | string | 否 | 文件类型限制：`image`/`document`/`drawing`/`all` |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| file_id | string | 文件唯一标识 |
| file_name | string | 原始文件名 |
| file_url | string | 访问URL |
| file_size | int | 文件大小（字节） |
| mime_type | string | MIME类型 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "file_id": "file_20240606120001_a1b2",
    "file_name": "膜片加工工艺卡.pdf",
    "file_url": "https://capp.company.com/api/v1/files/file_20240606120001_a1b2",
    "file_size": 245760,
    "mime_type": "application/pdf"
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

**文件限制**：
- 单文件最大：50MB
- 图片类型：jpg、jpeg、png、gif、tiff
- 文档类型：pdf、doc、docx、xls、xlsx
- 图纸类型：pdf、dwg、dxf

### 4.5 文件下载

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/files/{file_id}` |
| Method | GET |
| 权限 | 需认证（根据文件所属模块校验数据权限） |

**路径参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| file_id | string | 是 | 文件唯一标识 |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| download | int | 否 | 是否强制下载（`1`下载，`0`预览），默认`0` |

**响应**：文件二进制流或预览响应

### 4.6 获取当前用户信息

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/me` |
| Method | GET |
| 权限 | 需认证 |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| user_id | int | 用户ID |
| username | string | 用户名 |
| real_name | string | 真实姓名 |
| dept_id | int | 部门ID |
| dept_name | string | 部门名称 |
| roles | array | 角色列表 |
| permissions | array | 权限标识列表 |
| menus | array | 前端菜单树 |

---

## 5. 用户管理接口

### 5.1 用户CRUD

#### 5.1.1 获取用户列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users` |
| Method | GET |
| 权限 | `user:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| size | int | 否 | 每页条数，默认20 |
| keyword | string | 否 | 关键词（用户名/姓名/工号模糊搜索） |
| dept_id | int | 否 | 部门ID筛选 |
| role_id | int | 否 | 角色ID筛选 |
| status | int | 否 | 状态：0禁用，1启用 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "user_id": 10001,
        "username": "zhangsan",
        "real_name": "张三",
        "employee_no": "GY-2020-001",
        "dept_id": 5,
        "dept_name": "工艺部",
        "roles": [{"role_id": 1, "role_name": "工艺员"}],
        "phone": "138****1234",
        "email": "zhangsan@company.com",
        "status": 1,
        "last_login_time": "2024-06-05 14:30:00",
        "created_at": "2020-03-15 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "size": 20,
      "total": 156,
      "total_pages": 8,
      "has_next": true,
      "has_prev": false
    }
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 5.1.2 创建用户

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users` |
| Method | POST |
| 权限 | `user:create` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名，4-20位字母数字下划线 |
| real_name | string | 是 | 真实姓名 |
| employee_no | string | 是 | 工号，全局唯一 |
| password | string | 是 | 初始密码，6-20位 |
| dept_id | int | 是 | 所属部门ID |
| role_ids | array | 是 | 角色ID数组 |
| phone | string | 否 | 手机号 |
| email | string | 否 | 邮箱 |
| status | int | 否 | 状态，默认1 |

#### 5.1.3 获取用户详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{id}` |
| Method | GET |
| 权限 | `user:view` |

#### 5.1.4 更新用户

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{id}` |
| Method | PUT |
| 权限 | `user:edit` |

#### 5.1.5 删除用户

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{id}` |
| Method | DELETE |
| 权限 | `user:delete` |

**说明**：物理删除前校验用户是否关联工艺数据、审批记录等业务数据，若存在则禁止删除并提示。

#### 5.1.6 重置密码

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{id}/reset-password` |
| Method | POST |
| 权限 | `user:edit` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| new_password | string | 是 | 新密码 |
| force_change | int | 否 | 是否强制首次登录修改密码，默认1 |

### 5.2 角色管理

#### 5.2.1 获取角色列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles` |
| Method | GET |
| 权限 | `role:view` |

#### 5.2.2 创建角色

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles` |
| Method | POST |
| 权限 | `role:create` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| role_name | string | 是 | 角色名称 |
| role_code | string | 是 | 角色编码，全局唯一 |
| description | string | 否 | 角色描述 |
| permission_ids | array | 是 | 权限ID数组 |
| data_scope | string | 否 | 数据范围：`all`/`dept`/`self`，默认`self` |

#### 5.2.3 更新角色

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles/{id}` |
| Method | PUT |
| 权限 | `role:edit` |

#### 5.2.4 删除角色

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles/{id}` |
| Method | DELETE |
| 权限 | `role:delete` |

**说明**：系统内置角色（超级管理员、工艺员、审核员）禁止删除。

### 5.3 权限管理

#### 5.3.1 获取权限树

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/permissions` |
| Method | GET |
| 权限 | `permission:view` |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "permission_id": 1,
      "permission_name": "系统管理",
      "permission_code": "system",
      "type": "module",
      "children": [
        {
          "permission_id": 11,
          "permission_name": "用户管理",
          "permission_code": "user",
          "type": "menu",
          "children": [
            {"permission_id": 111, "permission_name": "查看用户", "permission_code": "user:view", "type": "action"},
            {"permission_id": 112, "permission_name": "创建用户", "permission_code": "user:create", "type": "action"},
            {"permission_id": 113, "permission_name": "编辑用户", "permission_code": "user:edit", "type": "action"},
            {"permission_id": 114, "permission_name": "删除用户", "permission_code": "user:delete", "type": "action"}
          ]
        }
      ]
    }
  ],
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 5.4 部门管理

#### 5.4.1 获取部门树

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments` |
| Method | GET |
| 权限 | `dept:view` |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "dept_id": 1,
      "dept_name": "研发中心",
      "dept_code": "RD",
      "parent_id": 0,
      "sort_order": 1,
      "children": [
        {
          "dept_id": 5,
          "dept_name": "工艺部",
          "dept_code": "RD-PE",
          "parent_id": 1,
          "sort_order": 1,
          "children": []
        },
        {
          "dept_id": 6,
          "dept_name": "设计部",
          "dept_code": "RD-DE",
          "parent_id": 1,
          "sort_order": 2,
          "children": []
        }
      ]
    }
  ],
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 5.4.2 创建部门

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments` |
| Method | POST |
| 权限 | `dept:create` |

#### 5.4.3 更新部门

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments/{id}` |
| Method | PUT |
| 权限 | `dept:edit` |

#### 5.4.4 删除部门

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments/{id}` |
| Method | DELETE |
| 权限 | `dept:delete` |

**说明**：存在子部门或关联用户时禁止删除。

---

## 6. 产品/零件接口

### 6.1 产品CRUD

#### 6.1.1 获取产品列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products` |
| Method | GET |
| 权限 | `product:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |
| keyword | string | 否 | 产品型号/名称关键词 |
| category | string | 否 | 产品类别：`diaphragm_compressor`/`pressure_vessel` |
| status | int | 否 | 状态：0草稿，1已发布 |

#### 6.1.2 创建产品

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products` |
| Method | POST |
| 权限 | `product:create` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| product_code | string | 是 | 产品型号，全局唯一 |
| product_name | string | 是 | 产品名称 |
| category | string | 是 | 类别：`diaphragm_compressor`/`pressure_vessel` |
| specification | string | 否 | 规格参数（JSON格式） |
| description | string | 否 | 产品描述 |
| design_drawing_ids | array | 否 | 设计图纸文件ID数组 |
| pdm_id | string | 否 | PDM系统关联ID |

**请求示例**：

```json
{
  "product_code": "GD-100/20",
  "product_name": "工业压缩机GD-100/20",
  "category": "diaphragm_compressor",
  "specification": {
    "flow_rate": "100 Nm³/h",
    "inlet_pressure": "0.1 MPa",
    "outlet_pressure": "20 MPa",
    "medium": "氢气",
    "power": "45 kW"
  },
  "description": "适用于氢气充装站的大流量工业压缩机",
  "design_drawing_ids": ["file_001", "file_002"],
  "pdm_id": "PDM-2024-00156"
}
```

#### 6.1.3 获取产品详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{id}` |
| Method | GET |
| 权限 | `product:view` |

**响应参数**：包含产品基本信息、BOM概要、关联工艺路线列表、版本历史。

#### 6.1.4 更新产品

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{id}` |
| Method | PUT |
| 权限 | `product:edit` |

#### 6.1.5 删除产品

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{id}` |
| Method | DELETE |
| 权限 | `product:delete` |

**说明**：已发布且关联工艺路线的产品禁止删除，仅支持逻辑删除。

### 6.2 BOM管理

#### 6.2.1 获取产品BOM

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/bom` |
| Method | GET |
| 权限 | `product:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| version | string | 否 | BOM版本号，默认最新版本 |
| level | int | 否 | 展开层级，默认全部层级 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "bom_id": 5001,
    "version": "V1.0",
    "product_id": 1001,
    "items": [
      {
        "item_id": 1,
        "part_id": 2001,
        "part_code": "MP-001",
        "part_name": "膜片组件",
        "quantity": 1,
        "unit": "套",
        "level": 1,
        "children": [
          {
            "item_id": 2,
            "part_id": 2002,
            "part_code": "MP-001-01",
            "part_name": "膜片",
            "quantity": 6,
            "unit": "片",
            "level": 2,
            "children": []
          }
        ]
      }
    ]
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 6.2.2 创建/更新BOM

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/bom` |
| Method | POST |
| 权限 | `product:edit` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| version | string | 是 | BOM版本号 |
| items | array | 是 | BOM明细数组 |
| items[].part_id | int | 是 | 零件ID |
| items[].quantity | float | 是 | 数量 |
| items[].unit | string | 是 | 单位 |
| items[].parent_item_id | int | 否 | 父项ID，顶层为0 |
| items[].sort_order | int | 否 | 排序号 |

#### 6.2.3 导入BOM

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/bom/import` |
| Method | POST |
| 权限 | `product:edit` |
| Content-Type | `multipart/form-data` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| file | file | 是 | Excel文件（.xlsx） |
| import_mode | string | 否 | 导入模式：`overwrite`/`merge`，默认`overwrite` |

### 6.3 零件查询

#### 6.3.1 获取零件列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts` |
| Method | GET |
| 权限 | `product:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |
| keyword | string | 否 | 零件编码/名称关键词 |
| material | string | 否 | 材质筛选 |
| category | string | 否 | 零件类别 |

#### 6.3.2 获取零件详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts/{id}` |
| Method | GET |
| 权限 | `product:view` |

**响应参数**：包含零件基本信息、关联工艺路线、使用产品列表、库存信息（ERP同步）。

---

## 7. 工艺管理接口

### 7.1 工艺路线CRUD

#### 7.1.1 获取工艺路线列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes` |
| Method | GET |
| 权限 | `process:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |
| keyword | string | 否 | 工艺路线编号/产品型号关键词 |
| product_id | int | 否 | 关联产品ID |
| status | string | 否 | 状态：`draft`/`reviewing`/`approved`/`published`/`obsolete` |
| created_by | int | 否 | 编制人ID |

#### 7.1.2 创建工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes` |
| Method | POST |
| 权限 | `process:create` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| route_code | string | 是 | 工艺路线编号，全局唯一 |
| route_name | string | 是 | 工艺路线名称 |
| product_id | int | 是 | 关联产品ID |
| version | string | 是 | 版本号，如`V1.0` |
| source_type | string | 否 | 来源：`new`/`copy`/`template`，默认`new` |
| source_id | int | 条件 | 来源ID（复制或引用时必填） |
| remark | string | 否 | 备注 |

#### 7.1.3 获取工艺路线详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}` |
| Method | GET |
| 权限 | `process:view` |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "route_id": 8001,
    "route_code": "GY-GD100-001",
    "route_name": "GD-100/20工业压缩机总装工艺路线",
    "product_id": 1001,
    "product_code": "GD-100/20",
    "version": "V1.0",
    "status": "published",
    "status_text": "已发布",
    "operations": [
      {
        "op_id": 1,
        "op_seq": 10,
        "op_code": "OP-010",
        "op_name": "膜片成型",
        "work_center_id": 101,
        "work_center_name": "冲压车间",
        "setup_time": 30,
        "run_time": 120,
        "time_unit": "min",
        "is_key_op": true,
        "is_parallel": false,
        "quality_requirement": "膜片厚度公差±0.02mm",
        "inspection_standard": "IS-MP-001",
        "tools": ["模具MP-001", "压边圈"],
        "next_op_id": 2
      },
      {
        "op_id": 2,
        "op_seq": 20,
        "op_code": "OP-020",
        "op_name": "膜片热处理",
        "work_center_id": 102,
        "work_center_name": "热处理车间",
        "setup_time": 60,
        "run_time": 240,
        "time_unit": "min",
        "is_key_op": true,
        "is_parallel": false,
        "quality_requirement": "硬度HRC 38-42",
        "inspection_standard": "IS-MP-002",
        "tools": ["热处理炉HT-003"],
        "next_op_id": 3
      }
    ],
    "creator": {"user_id": 10001, "real_name": "张三"},
    "reviewer": {"user_id": 10002, "real_name": "李四"},
    "approver": {"user_id": 10003, "real_name": "王五"},
    "created_at": "2024-05-10 09:00:00",
    "published_at": "2024-05-15 16:00:00"
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 7.1.4 更新工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}` |
| Method | PUT |
| 权限 | `process:edit` |

**说明**：仅`draft`（草稿）状态可编辑，其他状态需先撤回或创建新版本。

#### 7.1.5 删除工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}` |
| Method | DELETE |
| 权限 | `process:delete` |

**说明**：仅`draft`状态可删除；`published`状态需先作废。

#### 7.1.6 复制工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}/copy` |
| Method | POST |
| 权限 | `process:create` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| target_product_id | int | 否 | 目标产品ID，默认原产品 |
| new_version | string | 否 | 新版本号，默认自动递增 |

### 7.2 工序管理

#### 7.2.1 添加工序

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/operations` |
| Method | POST |
| 权限 | `process:edit` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| op_seq | int | 是 | 工序序号 |
| op_code | string | 是 | 工序代码 |
| op_name | string | 是 | 工序名称 |
| work_center_id | int | 是 | 工作中心ID |
| setup_time | int | 否 | 准备时间 |
| run_time | int | 否 | 运行时间 |
| time_unit | string | 否 | 时间单位：`min`/`h`，默认`min` |
| is_key_op | int | 否 | 是否关键工序，默认0 |
| is_parallel | int | 否 | 是否并行工序，默认0 |
| quality_requirement | string | 否 | 质量要求 |
| inspection_standard | string | 否 | 检验标准编号 |
| tools | array | 否 | 工装工具列表 |
| prev_op_id | int | 否 | 前序工序ID |

#### 7.2.2 更新工序

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/operations/{op_id}` |
| Method | PUT |
| 权限 | `process:edit` |

#### 7.2.3 删除工序

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/operations/{op_id}` |
| Method | DELETE |
| 权限 | `process:edit` |

#### 7.2.4 调整工序顺序

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/operations/reorder` |
| Method | POST |
| 权限 | `process:edit` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| operation_ids | array | 是 | 按新顺序排列的工序ID数组 |

### 7.3 工艺卡片

#### 7.3.1 获取工艺卡片列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-cards` |
| Method | GET |
| 权限 | `process:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |
| keyword | string | 否 | 卡片编号/名称关键词 |
| card_type | string | 否 | 卡片类型：`process`/`inspection`/`assembly`/`welding` |
| route_id | int | 否 | 关联工艺路线ID |

#### 7.3.2 创建工艺卡片

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-cards` |
| Method | POST |
| 权限 | `process:create` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| card_code | string | 是 | 卡片编号 |
| card_name | string | 是 | 卡片名称 |
| card_type | string | 是 | 卡片类型 |
| route_id | int | 是 | 关联工艺路线ID |
| op_id | int | 条件 | 关联工序ID（工序卡片必填） |
| content | object | 是 | 卡片内容（JSON格式，根据类型变化） |
| file_ids | array | 否 | 附件文件ID数组 |

**工艺卡片内容示例（机加工工艺卡片）**：

```json
{
  "content": {
    "equipment": "数控车床CK6150",
    "fixture": "三爪卡盘",
    "tooling": ["外圆车刀", "切断刀", "中心钻"],
    "material": "316L不锈钢",
    "blank_size": "Φ65×120",
    "steps": [
      {
        "step_no": 1,
        "operation": "装夹",
        "content": "三爪卡盘夹持毛坯，伸出长度80mm",
        "speed": "-",
        "feed": "-",
        "depth": "-",
        "tool": "-"
      },
      {
        "step_no": 2,
        "operation": "车端面",
        "content": "车平端面，保证总长118mm",
        "speed": "800",
        "feed": "0.15",
        "depth": "0.5",
        "tool": "外圆车刀"
      }
    ],
    "inspection_items": [
      {"item": "外径", "standard": "Φ60h7", "method": "千分尺"},
      {"item": "长度", "standard": "118±0.1", "method": "游标卡尺"}
    ]
  }
}
```

#### 7.3.3 生成工艺卡片PDF

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-cards/{id}/pdf` |
| Method | GET |
| 权限 | `process:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| watermark | int | 否 | 是否添加水印，默认1 |

**响应**：PDF文件流，Content-Type为`application/pdf`。

### 7.4 工艺审批

#### 7.4.1 提交审批

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}/submit` |
| Method | POST |
| 权限 | `process:edit` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| remark | string | 否 | 提交备注 |

#### 7.4.2 审核工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}/review` |
| Method | POST |
| 权限 | `process:review` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| action | string | 是 | 操作：`pass`/`reject` |
| comment | string | 条件 | 审核意见，`reject`时必填 |

#### 7.4.3 批准工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}/approve` |
| Method | POST |
| 权限 | `process:approve` |

**请求参数**：同审核接口

### 7.5 工艺版本

#### 7.5.1 获取版本历史

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}/versions` |
| Method | GET |
| 权限 | `process:view` |

#### 7.5.2 版本对比

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{id}/compare` |
| Method | GET |
| 权限 | `process:view` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| version_a | string | 是 | 版本A |
| version_b | string | 是 | 版本B |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "differences": [
      {
        "type": "operation_added",
        "op_seq": 25,
        "op_name": "膜片预压",
        "description": "V1.1新增工序"
      },
      {
        "type": "operation_modified",
        "op_seq": 20,
        "field": "run_time",
        "old_value": 240,
        "new_value": 300,
        "description": "热处理时间调整"
      }
    ]
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

---

## 8. 企业微信接口

### 8.1 扫码登录

#### 8.1.1 获取扫码登录URL

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/auth-url` |
| Method | GET |
| 权限 | 公开 |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| redirect_type | string | 否 | 重定向类型：`web`/`mobile`，默认`web` |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| auth_url | string | 企业微信授权URL |
| state | string | 防CSRF状态码，需缓存用于后续回调校验 |
| expires_in | int | 授权URL有效期（秒） |

#### 8.1.2 扫码登录回调

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/callback` |
| Method | GET |
| 权限 | 公开 |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| code | string | 是 | 企业微信授权码 |
| state | string | 是 | 状态码 |

**响应**：重定向至前端页面并携带临时Token，或返回JSON（AJAX场景）。

#### 8.1.3 企业微信账号绑定

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/bind` |
| Method | POST |
| 权限 | 需认证 |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| wx_auth_code | string | 是 | 企业微信授权码 |

### 8.2 用户同步

#### 8.2.1 触发用户同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/sync-users` |
| Method | POST |
| 权限 | `system:admin` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| sync_mode | string | 否 | 同步模式：`full`/`incremental`，默认`incremental` |
| dept_ids | array | 否 | 指定同步的部门ID数组，默认全部 |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| sync_task_id | string | 同步任务ID |
| status | string | 任务状态：`queued`/`running`/`completed`/`failed` |

#### 8.2.2 获取同步任务状态

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/sync-tasks/{task_id}` |
| Method | GET |
| 权限 | `system:admin` |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "sync_task_id": "sync_20240606_001",
    "status": "completed",
    "total_count": 156,
    "success_count": 154,
    "fail_count": 2,
    "failed_items": [
      {"wx_user_id": "zhangsan", "reason": "手机号格式错误"}
    ],
    "started_at": "2024-06-06 10:00:00",
    "completed_at": "2024-06-06 10:00:15"
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 8.3 消息推送

#### 8.3.1 发送审批通知

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/messages/approval` |
| Method | POST |
| 权限 | `system:admin` 或系统内部调用 |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| to_user_ids | array | 是 | 接收人用户ID数组 |
| title | string | 是 | 消息标题 |
| description | string | 是 | 消息描述 |
| url | string | 否 | 点击跳转URL |
| route_id | int | 否 | 关联工艺路线ID |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "message_id": "msg_20240606_001",
    "sent_count": 3,
    "failed_count": 0
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 8.3.2 发送系统公告

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat-work/messages/announcement` |
| Method | POST |
| 权限 | `system:admin` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| to_party_ids | array | 否 | 接收部门ID数组 |
| to_tag_ids | array | 否 | 接收标签ID数组 |
| to_user_ids | array | 否 | 接收人用户ID数组（三选一必填） |
| content | string | 是 | 公告内容 |
| priority | string | 否 | 优先级：`normal`/`high`，默认`normal` |

---

## 9. AI接口

### 9.1 对话接口

#### 9.1.1 发送对话消息

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/chat` |
| Method | POST |
| 权限 | `ai:chat` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| session_id | string | 否 | 会话ID，首次对话可不传，系统自动创建 |
| message | string | 是 | 用户消息内容 |
| context_type | string | 否 | 上下文类型：`general`/`process`/`knowledge`，默认`general` |
| product_id | int | 条件 | 关联产品ID（工艺咨询场景） |

**请求示例**：

```json
{
  "session_id": "sess_a1b2c3d4",
  "message": "工业压缩机膜片热处理工序的最佳工艺参数是什么？",
  "context_type": "process",
  "product_id": 1001
}
```

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| session_id | string | 会话ID |
| message_id | string | 消息ID |
| reply | string | AI回复内容 |
| references | array | 引用来源（知识库文档/工艺标准） |
| suggested_questions | array | 推荐追问 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "session_id": "sess_a1b2c3d4",
    "message_id": "msg_20240606_001",
    "reply": "根据企业工艺知识库及行业标准，工业压缩机膜片（316L不锈钢材质）热处理推荐参数如下：\n\n1. **固溶处理**：温度1050±10℃，保温时间按1.5min/mm计算，水冷；\n2. **时效处理**：温度480±5℃，保温4h，空冷；\n3. **硬度控制目标**：HRC 38-42。\n\n注意事项：\n- 升温速率≤150℃/h，防止变形；\n- 热处理前需进行表面清洗，去除油污；\n- 建议采用真空热处理炉，防止表面氧化。",
    "references": [
      {"doc_id": "kb_001", "doc_title": "膜片热处理工艺规范", "page": 12},
      {"doc_id": "std_003", "doc_title": "GB/T 1220-2016 不锈钢棒", "section": "5.3"}
    ],
    "suggested_questions": [
      "膜片热处理后的检验标准是什么？",
      "膜片固溶处理后变形如何控制？",
      "不同材质膜片的热处理参数差异"
    ]
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 9.1.2 获取对话历史

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/chat/{session_id}/history` |
| Method | GET |
| 权限 | `ai:chat` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |

### 9.2 决策接口

#### 9.2.1 工艺参数推荐

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/recommend/process-params` |
| Method | POST |
| 权限 | `ai:recommend` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| product_id | int | 是 | 产品ID |
| operation_name | string | 是 | 工序名称 |
| material | string | 是 | 材料牌号 |
| constraints | object | 否 | 约束条件（尺寸、精度、设备等） |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| recommendations | array | 推荐参数列表（含置信度） |
| confidence | float | 整体置信度 |
| basis | string | 推荐依据说明 |

#### 9.2.2 工艺路线优化建议

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/recommend/route-optimization` |
| Method | POST |
| 权限 | `ai:recommend` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| route_id | int | 是 | 现有工艺路线ID |
| optimization_goal | string | 否 | 优化目标：`time`/`cost`/`quality`，默认`time` |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "original_metrics": {
      "total_time": 1800,
      "total_cost": 12500,
      "quality_risk": "medium"
    },
    "optimized_metrics": {
      "total_time": 1620,
      "total_cost": 11800,
      "quality_risk": "low"
    },
    "improvements": [
      {
        "type": "merge_operations",
        "description": "将工序'车端面'与'打中心孔'合并为一道工序，减少装夹次数",
        "saving_time": 30,
        "saving_cost": 200,
        "confidence": 0.92
      },
      {
        "type": "adjust_sequence",
        "description": "将'无损检测'调整至'热处理'之后，避免热影响区缺陷漏检",
        "saving_time": 0,
        "saving_cost": 0,
        "quality_improvement": "high",
        "confidence": 0.88
      }
    ],
    "warnings": [
      "合并工序需确认设备复合加工能力"
    ]
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 9.3 知识库查询

#### 9.3.1 语义搜索

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/knowledge/search` |
| Method | POST |
| 权限 | `ai:knowledge` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| query | string | 是 | 搜索查询语句 |
| filters | object | 否 | 筛选条件 |
| filters.doc_type | array | 否 | 文档类型：`standard`/`regulation`/`case`/`template` |
| filters.category | array | 否 | 工艺类别 |
| top_k | int | 否 | 返回结果数量，默认10 |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| results | array | 搜索结果列表 |
| results[].doc_id | string | 文档ID |
| results[].title | string | 文档标题 |
| results[].snippet | string | 匹配片段 |
| results[].relevance_score | float | 相关度得分 |
| results[].metadata | object | 文档元数据 |

---

## 10. 外部系统对接接口

### 10.1 数据同步触发

#### 10.1.1 触发PDM数据同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/sync/pdm/trigger` |
| Method | POST |
| 权限 | `sync:manage` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| sync_type | string | 是 | 同步类型：`product`/`bom`/`drawing`/`all` |
| sync_mode | string | 否 | 同步模式：`full`/`incremental`，默认`incremental` |
| product_ids | array | 条件 | 指定产品ID数组（`sync_type=product`时可用） |
| start_time | string | 条件 | 增量同步起始时间（`sync_mode=incremental`时可用） |

**响应参数**：

| 参数名 | 类型 | 说明 |
|--------|------|------|
| sync_task_id | string | 同步任务ID |
| status | string | 任务状态 |
| estimated_duration | int | 预估耗时（秒） |

#### 10.1.2 触发ERP数据同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/sync/erp/trigger` |
| Method | POST |
| 权限 | `sync:manage` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| sync_type | string | 是 | 同步类型：`material`/`inventory`/`work_center`/`all` |
| sync_mode | string | 否 | 同步模式：`full`/`incremental`，默认`incremental` |

#### 10.1.3 触发MES数据推送

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/sync/mes/push` |
| Method | POST |
| 权限 | `sync:manage` |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| route_id | int | 是 | 工艺路线ID |
| push_type | string | 是 | 推送类型：`route`/`card`/`all` |
| work_order_no | string | 否 | 关联工单号 |

### 10.2 同步状态查询

#### 10.2.1 获取同步任务列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/sync/tasks` |
| Method | GET |
| 权限 | `sync:manage` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |
| system | string | 否 | 目标系统：`pdm`/`erp`/`mes` |
| status | string | 否 | 状态：`pending`/`running`/`completed`/`failed` |

#### 10.2.2 获取同步任务详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/sync/tasks/{task_id}` |
| Method | GET |
| 权限 | `sync:manage` |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "sync_task_id": "sync_pdm_20240606_001",
    "system": "pdm",
    "sync_type": "product",
    "sync_mode": "incremental",
    "status": "completed",
    "total_items": 45,
    "processed_items": 45,
    "success_items": 43,
    "failed_items": 2,
    "failed_details": [
      {
        "item_id": "PDM-2024-00123",
        "item_name": "GD-200/30膜头",
        "error_code": "DATA_FORMAT_ERROR",
        "error_message": "规格参数JSON格式非法"
      }
    ],
    "started_at": "2024-06-06 02:00:00",
    "completed_at": "2024-06-06 02:03:25",
    "duration": 205,
    "triggered_by": {"user_id": 1, "real_name": "系统管理员"}
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 10.2.3 获取同步日志

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/sync/tasks/{task_id}/logs` |
| Method | GET |
| 权限 | `sync:manage` |

**查询参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| level | string | 否 | 日志级别：`info`/`warning`/`error`，默认全部 |
| page | int | 否 | 页码 |
| size | int | 否 | 每页条数 |

### 10.3 数据推送接收

#### 10.3.1 PDM变更推送接收（Webhook）

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/webhooks/pdm` |
| Method | POST |
| 权限 | 签名验证（开放接口） |

**请求头**：

| 请求头 | 必填 | 说明 |
|--------|------|------|
| X-Webhook-Signature | 是 | 请求签名 |
| X-Webhook-Timestamp | 是 | 请求时间戳 |
| X-Webhook-Event | 是 | 事件类型 |

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| event_type | string | 是 | 事件类型：`product.created`/`product.updated`/`bom.changed`/`drawing.released` |
| payload | object | 是 | 事件载荷数据 |
| payload.product_id | string | 条件 | PDM产品ID |
| payload.version | string | 否 | 版本号 |
| payload.changes | array | 否 | 变更字段列表 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "received": true,
    "event_id": "evt_pdm_20240606_001",
    "processing_status": "queued"
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

#### 10.3.2 ERP库存变更推送接收（Webhook）

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/webhooks/erp` |
| Method | POST |
| 权限 | 签名验证（开放接口） |

**事件类型**：

| 事件类型 | 说明 |
|----------|------|
| `material.created` | 物料新增 |
| `material.updated` | 物料更新 |
| `inventory.changed` | 库存变更 |
| `work_center.changed` | 工作中心变更 |

---

## 11. 接口版本管理策略

### 11.1 版本命名规则

接口版本采用语义化版本控制（SemVer）的简化形式，格式为`v{主版本号}`，如`v1`、`v2`。

- **主版本号（Major）**：当接口发生不兼容的变更时递增，如URL结构调整、请求/响应格式变更、字段删除等
- **次版本号（Minor）**：新功能以向后兼容方式添加时，不在URL中体现，通过文档和变更日志记录
- **修订号（Patch）**：Bug修复和内部优化，不在URL中体现

### 11.2 版本控制策略

**URL路径版本控制**：

版本号嵌入URL路径中，作为必选标识：

```
/api/v1/users
/api/v2/users
```

**多版本并行支持**：

- 系统同时支持最多两个主版本并行运行（当前版本 + 上一版本）
- 新版本发布后，旧版本进入维护期（6个月），期间仅修复严重Bug和安全漏洞
- 维护期结束后，旧版本标记为弃用（Deprecated），再经过3个月过渡期后下线
- 版本下线前30天，通过响应头`X-API-Deprecation`和邮件通知所有对接方

**版本兼容性原则**：

| 变更类型 | 兼容性 | 处理方式 |
|----------|--------|----------|
| 新增接口 | 兼容 | 当前版本直接添加 |
| 新增可选参数 | 兼容 | 当前版本直接添加 |
| 新增响应字段 | 兼容 | 当前版本直接添加 |
| 修改必填参数为可选 | 兼容 | 当前版本直接修改 |
| 删除接口 | 不兼容 | 新版本移除，旧版本保留 |
| 删除参数 | 不兼容 | 新版本移除，旧版本保留 |
| 删除响应字段 | 不兼容 | 新版本移除，旧版本保留 |
| 修改参数含义 | 不兼容 | 新版本修改，旧版本保留 |
| 修改响应数据结构 | 不兼容 | 新版本修改，旧版本保留 |

**版本协商机制**：

客户端可通过以下方式指定版本：
1. **URL路径**（优先）：`/api/v1/users`
2. **请求头**：`X-API-Version: v1`
3. **查询参数**：`?api_version=v1`

若多种方式同时存在，优先级为：URL路径 > 请求头 > 查询参数。

### 11.3 版本变更管理

**版本发布流程**：

1. 需求评审阶段评估接口变更影响，判定是否需要升级主版本
2. 新版本开发完成后，编写版本迁移指南（Migration Guide）
3. 在测试环境部署双版本，进行兼容性回归测试
4. 生产环境灰度发布，先对内部应用开放，再逐步扩大范围
5. 正式发布后，更新API文档、SDK及示例代码

**版本信息查询接口**：

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/system/api-versions` |
| Method | GET |
| 权限 | 公开 |

**响应示例**：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "current_version": "v1",
    "supported_versions": ["v1"],
    "deprecated_versions": [],
    "sunset_versions": [],
    "version_details": [
      {
        "version": "v1",
        "status": "stable",
        "release_date": "2024-01-15",
        "documentation_url": "https://capp.company.com/docs/api/v1"
      }
    ]
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

---

## 12. 接口限流与防重放设计

### 12.1 限流策略

### 12.1.1 限流目标

防止恶意攻击、资源滥用及突发流量导致系统过载，保障核心业务的可用性和响应速度。

### 12.1.2 限流维度

系统采用多层限流策略，从不同维度进行流量控制：

| 限流维度 | 标识方式 | 默认阈值 | 说明 |
|----------|----------|----------|------|
| 全局IP限流 | 客户端IP | 1000次/分钟 | 防止单IP恶意刷接口 |
| 用户级限流 | 用户ID | 600次/分钟 | 防止账号级滥用 |
| 接口级限流 | URL路径 | 120次/分钟 | 防止热点接口过载 |
| 接口+用户限流 | 用户ID+URL | 60次/分钟 | 精细化控制 |

### 12.1.3 限流算法

采用**滑动窗口计数器**算法实现限流，基于Redis存储计数数据，确保分布式环境下的一致性。

**算法逻辑**：

```php
// 伪代码示意
function isAllowed($key, $limit, $window) {
    $now = time();
    $windowStart = $now - ($now % $window);
    $redisKey = "rate_limit:{$key}:{$windowStart}";
    
    $current = redis->incr($redisKey);
    if ($current === 1) {
        redis->expire($redisKey, $window * 2);
    }
    
    return $current <= $limit;
}
```

### 12.1.4 限流响应

触发限流时，接口返回HTTP 429状态码，响应体包含限流详情：

```json
{
  "code": 10007,
  "message": "请求过于频繁，请稍后再试",
  "data": {
    "retry_after": 45,
    "limit": 60,
    "remaining": 0,
    "reset_time": 1717651245
  },
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

**响应头**：

| 响应头 | 说明 | 示例 |
|--------|------|------|
| X-RateLimit-Limit | 当前限流阈值 | `60` |
| X-RateLimit-Remaining | 剩余可用次数 | `0` |
| X-RateLimit-Reset | 限流重置时间（Unix时间戳） | `1717651245` |
| Retry-After | 建议等待秒数 | `45` |

### 12.1.5 特殊接口限流配置

| 接口 | 限流阈值 | 说明 |
|------|----------|------|
| 登录接口 | 5次/分钟/IP | 防止暴力破解 |
| 文件上传 | 10次/分钟/用户 | 防止存储滥用 |
| AI对话接口 | 30次/分钟/用户 | AI服务成本较高 |
| 企业微信消息推送 | 20次/分钟/用户 | 防止骚扰推送 |
| 数据同步触发 | 10次/小时/用户 | 防止频繁同步 |
| Webhook接收 | 1000次/分钟/来源IP | 保护内部接口 |

### 12.2 防重放设计

### 12.2.1 重放攻击风险

攻击者截获合法请求后，重复发送以造成数据重复、资源消耗或状态异常。针对开放接口（Webhook、第三方系统对接）必须实施防重放保护。

### 12.2.2 防重放机制

**时间戳校验**：

- 请求头`X-Timestamp`与服务器当前时间差不得超过300秒（5分钟）
- 超时请求直接拒绝，返回错误码`10009`

**Nonce随机数机制**：

- 请求头`X-Nonce`为唯一随机字符串（建议UUID或16位以上随机字符）
- 服务器将Nonce存入Redis，有效期5分钟
- 收到请求时校验Nonce是否已存在，存在则判定为重放攻击

**请求签名机制**：

开放接口（Webhook、第三方对接）必须携带有效签名，签名生成规则见12.3节。

### 12.2.3 防重放响应

```json
{
  "code": 10009,
  "message": "请求已过期或存在重放风险，请重新发起请求",
  "data": null,
  "timestamp": 1717651200,
  "request_id": "req_8f3a2b1c4d5e6f7a"
}
```

### 12.3 签名验证机制

### 12.3.1 适用范围

以下场景必须进行请求签名验证：
- 外部系统Webhook回调（PDM、ERP、MES推送）
- 第三方系统调用开放API
- 移动端APP关键接口（可选）

### 12.3.2 签名生成流程

**步骤1：收集参数**

提取以下参数参与签名：
- HTTP方法（大写）：如`GET`、`POST`
- URL路径（不含域名和查询参数）：如`/api/v1/webhooks/pdm`
- 时间戳：`X-Timestamp`值
- Nonce：`X-Nonce`值
- 请求体（POST/PUT/PATCH）：JSON字符串的SHA256哈希值（前16位）

**步骤2：构建签名字符串**

```
{METHOD}\n{URL_PATH}\n{TIMESTAMP}\n{NONCE}\n{BODY_HASH}
```

**步骤3：计算签名**

使用HMAC-SHA256算法，以API密钥（`api_secret`）为密钥，对签名字符串进行签名：

```php
$signature = hash_hmac('sha256', $stringToSign, $apiSecret);
```

**步骤4：传递签名**

将签名值放入请求头`X-Signature`：

```
X-Signature: sha256=abc123def456...
```

### 12.3.3 签名验证示例（PHP原生）

```php
function verifySignature($method, $urlPath, $timestamp, $nonce, $body, $apiSecret, $receivedSignature) {
    // 1. 校验时间戳
    if (abs(time() - $timestamp) > 300) {
        return false; // 请求超时
    }
    
    // 2. 校验Nonce（防重放）
    $nonceKey = "nonce:{$nonce}";
    if (redis->exists($nonceKey)) {
        return false; // Nonce已使用
    }
    redis->setex($nonceKey, 300, "1");
    
    // 3. 计算请求体哈希
    $bodyHash = substr(hash('sha256', $body), 0, 16);
    
    // 4. 构建签名字符串
    $stringToSign = implode("\n", [
        strtoupper($method),
        $urlPath,
        $timestamp,
        $nonce,
        $bodyHash
    ]);
    
    // 5. 计算期望签名
    $expectedSignature = hash_hmac('sha256', $stringToSign, $apiSecret);
    
    // 6. 比对签名（常量时间比较，防时序攻击）
    return hash_equals($expectedSignature, $receivedSignature);
}
```

### 12.3.4 API密钥管理

| 项目 | 说明 |
|------|------|
| 密钥生成 | 系统为每个对接方自动生成32位随机字符串作为`api_key`和`api_secret` |
| 密钥存储 | `api_secret`仅显示一次，服务端存储其SHA256哈希值 |
| 密钥轮换 | 支持双密钥并行期，对接方可平滑切换 |
| 密钥失效 | 发现泄露或对接方下线时，立即禁用并清理Redis中的Nonce记录 |

### 12.3.5 开放接口认证流程

```
第三方系统          CAPP系统
    │                  │
    │── 1.注册应用 ───>│
    │<─ 2.返回api_key ─│
    │                  │
    │── 3.携带签名请求 >│
    │   X-Timestamp    │
    │   X-Nonce        │
    │   X-Signature    │
    │                  │
    │                  │── 4.校验时间戳
    │                  │── 5.校验Nonce
    │                  │── 6.校验签名
    │                  │── 7.执行业务逻辑
    │<─ 8.返回结果 ────│
```

---

## 附录A：接口汇总表

| 模块 | 接口数量 | 主要接口路径 |
|------|----------|--------------|
| 认证授权 | 4 | `/api/v1/auth/*` |
| 用户管理 | 12 | `/api/v1/users/*`、`/api/v1/roles/*`、`/api/v1/permissions/*`、`/api/v1/departments/*` |
| 产品零件 | 10 | `/api/v1/products/*`、`/api/v1/parts/*` |
| 工艺管理 | 16 | `/api/v1/process-routes/*`、`/api/v1/process-cards/*` |
| 企业微信 | 6 | `/api/v1/wechat-work/*` |
| AI服务 | 5 | `/api/v1/ai/*` |
| 外部系统对接 | 6 | `/api/v1/sync/*`、`/api/v1/webhooks/*` |
| 公共接口 | 2 | `/api/v1/files/*`、`/api/v1/system/*` |
| **合计** | **61** | - |

## 附录B：HTTP状态码与业务码对照速查

| 场景 | HTTP状态码 | 业务码 |
|------|------------|--------|
| 成功 | 200/201/204 | 0 |
| 参数错误 | 400 | 10001 |
| 认证失败 | 401 | 10002 |
| 权限不足 | 403 | 10003 |
| 资源不存在 | 404 | 10004 |
| 资源冲突 | 409 | 10005 |
| 业务校验失败 | 422 | 10006 |
| 请求过于频繁 | 429 | 10007 |
| 签名错误 | 401 | 10008 |
| 重放攻击 | 401 | 10009 |
| 数据库错误 | 500 | 20001 |
| 外部系统错误 | 502/503 | 20002 |
| 文件操作错误 | 500 | 20003 |
| AI服务错误 | 503 | 20004 |
| 系统内部错误 | 500 | 99999 |

---

*文档版本：V1.0*  
*编写日期：2024年6月*  
*编写部门：CAPP系统项目组*  
*审核状态：待评审*
