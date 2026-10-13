# 08-API接口设计

## 目录

- [1. 概述](#1-概述)
- [2. 接口设计规范](#2-接口设计规范)
  - [2.1 RESTful风格定义](#21-restful风格定义)
  - [2.2 URL规范](#22-url规范)
  - [2.3 请求响应格式](#23-请求响应格式)
  - [2.4 状态码定义](#24-状态码定义)
- [3. 认证与授权](#3-认证与授权)
  - [3.1 Token机制](#31-token机制)
  - [3.2 JWT设计](#32-jwt设计)
  - [3.3 接口权限控制](#33-接口权限控制)
- [4. 公共接口](#4-公共接口)
  - [4.1 登录](#41-登录)
  - [4.2 登出](#42-登出)
  - [4.3 Token刷新](#43-token刷新)
  - [4.4 文件上传](#44-文件上传)
  - [4.5 文件下载](#45-文件下载)
- [5. 用户管理接口](#5-用户管理接口)
  - [5.1 用户CRUD](#51-用户crud)
  - [5.2 角色权限](#52-角色权限)
  - [5.3 部门管理](#53-部门管理)
- [6. 产品/零件接口](#6-产品零件接口)
  - [6.1 产品CRUD](#61-产品crud)
  - [6.2 BOM管理](#62-bom管理)
  - [6.3 零件查询](#63-零件查询)
- [7. 工艺管理接口](#7-工艺管理接口)
  - [7.1 工艺路线CRUD](#71-工艺路线crud)
  - [7.2 工序管理](#72-工序管理)
  - [7.3 工艺卡片](#73-工艺卡片)
- [8. 企业微信接口](#8-企业微信接口)
  - [8.1 扫码登录](#81-扫码登录)
  - [8.2 用户同步](#82-用户同步)
  - [8.3 消息推送](#83-消息推送)
- [9. AI与MaxKB接口](#9-ai与maxkb接口)
  - [9.1 对话接口](#91-对话接口)
  - [9.2 决策接口](#92-决策接口)
  - [9.3 知识库查询](#93-知识库查询)
  - [9.4 MaxKB文档管理](#94-maxkb文档管理)
  - [9.5 MaxKB智能体调用](#95-maxkb智能体调用)
- [10. 外部系统对接接口](#10-外部系统对接接口)
  - [10.1 数据同步触发](#101-数据同步触发)
  - [10.2 同步状态查询](#102-同步状态查询)
- [11. 接口版本管理策略](#11-接口版本管理策略)
- [12. 接口限流与防重放设计](#12-接口限流与防重放设计)

---

## 1. 概述

本文档定义了CAPP（计算机辅助工艺规划）系统的全部API接口设计规范，涵盖认证授权、用户管理、产品零件管理、工艺管理、企业微信集成、AI与MaxKB对接、外部系统数据同步等核心模块。所有接口基于PHP原生实现，采用RESTful架构风格，通过JSON格式进行数据交换，使用JWT实现无状态认证，并具备完善的限流、防重放、版本控制等安全机制。

---

## 2. 接口设计规范

### 2.1 RESTful风格定义

系统严格遵循RESTful架构原则，使用HTTP动词表达操作语义：

| HTTP方法 | 语义 | 使用场景 |
|---------|------|---------|
| GET | 读取资源 | 查询、获取详情、列表检索 |
| POST | 创建资源 | 新增数据、提交表单、触发异步任务 |
| PUT | 全量更新 | 完整替换资源全部字段 |
| PATCH | 局部更新 | 仅更新资源部分字段 |
| DELETE | 删除资源 | 物理删除或逻辑删除资源 |

资源命名采用名词复数形式，避免使用动词。对于复杂业务操作无法通过标准HTTP动词表达时，使用POST方法配合动作路径，如 `/api/v1/process-routes/{id}/publish`。

### 2.2 URL规范

**基础格式：**

```
https://{domain}/api/{version}/{module}/{resource}[/{id}][/{action}]
```

**URL组成规则：**

| 组成部分 | 说明 | 示例 |
|---------|------|------|
| domain | 部署域名或IP | `capp.company.com` |
| api | 固定前缀，标识接口入口 | `api` |
| version | 接口版本号 | `v1` |
| module | 业务模块标识 | `auth`, `user`, `product`, `process`, `wechat`, `ai`, `external` |
| resource | 资源名称（复数名词） | `users`, `products`, `process-routes` |
| id | 资源唯一标识 | `10001` |
| action | 自定义动作（可选） | `publish`, `revoke`, `sync` |

**URL命名细则：**
- 全部使用小写字母
- 单词间使用连字符 `-` 分隔（kebab-case）
- 禁止使用下划线 `_`
- 禁止使用驼峰命名
- 资源ID使用数字或UUID字符串
- 查询参数使用蛇形命名（snake_case）

**示例URL：**

```
GET    /api/v1/users                    # 用户列表
GET    /api/v1/users/1001             # 用户详情
POST   /api/v1/users                  # 创建用户
PUT    /api/v1/users/1001             # 全量更新用户
PATCH  /api/v1/users/1001/status      # 局部更新用户状态
DELETE /api/v1/users/1001             # 删除用户
POST   /api/v1/users/1001/reset-password  # 自定义动作：重置密码
```

### 2.3 请求响应格式

**请求头规范：**

所有API请求必须包含以下HTTP Header：

| Header | 必填 | 说明 |
|--------|------|------|
| Content-Type | 是 | `application/json; charset=utf-8` |
| Authorization | 是（除登录等公开接口） | `Bearer {access_token}` |
| X-Request-Id | 是 | 请求唯一标识，UUID格式，用于链路追踪 |
| X-Timestamp | 是 | 请求时间戳，Unix时间戳（秒），用于防重放校验 |
| X-Nonce | 是 | 随机字符串，长度16-32位，用于防重放校验 |
| X-Client-Version | 否 | 客户端版本号，如 `web/1.2.0` |

**请求体格式：**

POST/PUT/PATCH请求的请求体必须为JSON格式，字段命名采用蛇形命名（snake_case）。日期时间统一使用ISO 8601格式字符串：`YYYY-MM-DDTHH:mm:ssZ`。

**统一响应格式：**

```json
{
  "code": 0,
  "message": "success",
  "data": {},
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

响应字段说明：

| 字段 | 类型 | 说明 |
|------|------|------|
| code | integer | 业务状态码，0表示成功，非0表示具体错误 |
| message | string | 状态描述信息 |
| data | mixed | 响应数据，成功时返回具体业务数据，失败时可能包含错误详情 |
| request_id | string | 请求唯一标识，与请求头中的X-Request-Id一致 |
| timestamp | integer | 服务器响应时间戳 |

**分页响应格式：**

列表查询接口统一返回分页结构：

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 156,
      "total_pages": 8,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

分页请求参数：

| 参数 | 类型 | 必填 | 默认值 | 说明 |
|------|------|------|--------|------|
| page | integer | 否 | 1 | 当前页码，从1开始 |
| page_size | integer | 否 | 20 | 每页记录数，最大100 |
| sort_field | string | 否 | id | 排序字段 |
| sort_order | string | 否 | desc | 排序方向：`asc` 或 `desc` |

### 2.4 状态码定义

**HTTP状态码：**

| HTTP状态码 | 使用场景 |
|-----------|---------|
| 200 | 请求成功（GET/PUT/PATCH/DELETE） |
| 201 | 资源创建成功（POST） |
| 204 | 请求成功但无返回内容 |
| 400 | 请求参数错误、格式非法 |
| 401 | 未提供认证信息或Token无效 |
| 403 | 认证通过但无权限访问 |
| 404 | 资源不存在 |
| 409 | 资源冲突（如重复创建） |
| 422 | 业务校验失败（参数格式正确但业务逻辑不通过） |
| 429 | 请求频率超限 |
| 500 | 服务器内部错误 |
| 502 | 网关错误（外部服务不可用） |
| 503 | 服务暂时不可用 |

**业务状态码（code字段）：**

| 状态码 | 含义 | 说明 |
|--------|------|------|
| 0 | 成功 | 请求处理成功 |
| 100001 | 参数错误 | 请求参数缺失或格式非法 |
| 100002 | 签名错误 | 请求签名验证失败 |
| 100003 | 请求过期 | 时间戳超出有效范围 |
| 100004 | 重复请求 | 防重放校验失败 |
| 100005 | 请求频率超限 | 触发限流规则 |
| 200001 | Token缺失 | 未提供Authorization头 |
| 200002 | Token无效 | Token格式错误或已损坏 |
| 200003 | Token过期 | Access Token已过期 |
| 200004 | Refresh Token过期 | Refresh Token已过期，需重新登录 |
| 200005 | 权限不足 | 当前用户无接口访问权限 |
| 300001 | 用户不存在 | 目标用户ID无效 |
| 300002 | 用户已禁用 | 用户账号被冻结 |
| 300003 | 密码错误 | 登录密码不匹配 |
| 400001 | 产品不存在 | 产品ID无效 |
| 400002 | BOM结构错误 | BOM父子关系存在循环依赖 |
| 500001 | 工艺路线不存在 | 工艺路线ID无效 |
| 500002 | 工艺状态冲突 | 当前状态不允许执行目标操作 |
| 600001 | 企业微信API错误 | 调用企微接口失败 |
| 700001 | MaxKB服务异常 | AI服务不可用或返回错误 |
| 800001 | 外部系统同步失败 | ERP/PLM数据同步异常 |
| 900001 | 服务器内部错误 | 未预期的系统异常 |

---

## 3. 认证与授权

### 3.1 Token机制

系统采用双Token机制（Access Token + Refresh Token），实现无状态认证与安全的Token刷新：

- **Access Token**：短期有效，用于接口访问认证，有效期2小时，存储在客户端内存中
- **Refresh Token**：长期有效，用于获取新的Access Token，有效期7天，存储在HttpOnly Cookie中
- **Token类型标识**：通过JWT的 `token_type` 声明区分 `access` 和 `refresh`

**Token颁发流程：**

1. 用户通过用户名/密码或企业微信扫码完成登录认证
2. 服务端验证通过后，生成Access Token和Refresh Token
3. Access Token通过响应体返回给客户端
4. Refresh Token通过 `Set-Cookie` 头写入浏览器HttpOnly Cookie
5. 客户端后续请求在Header中携带 `Authorization: Bearer {access_token}`

**Token刷新流程：**

1. 客户端检测到Access Token即将过期或已过期（收到200003状态码）
2. 客户端调用 `/api/v1/auth/refresh` 接口，自动携带Refresh Token Cookie
3. 服务端验证Refresh Token有效性及是否被吊销
4. 验证通过后，颁发新的Access Token和Refresh Token对
5. 旧的Refresh Token加入黑名单，防止重复使用

**Token吊销机制：**

- 用户主动登出时，当前Access Token和Refresh Token立即加入Redis黑名单
- 用户密码修改、权限变更、账号禁用等安全事件触发时，该用户所有Token批量吊销
- 黑名单使用Redis存储，TTL设置为Token剩余有效期，过期自动清理

### 3.2 JWT设计

**JWT算法与密钥：**

- 签名算法：HS256（HMAC-SHA256）
- 密钥管理：Access Token和Refresh Token使用不同的密钥，存储在服务端环境变量中，定期轮换
- Token格式：标准JWT（Header.Payload.Signature）

**JWT Payload声明：**

| 声明 | 类型 | 说明 |
|------|------|------|
| iss | string | 签发者，固定为 `capp-system` |
| sub | string | 主题，用户唯一标识（user_id） |
| aud | string | 受众，固定为 `capp-api` |
| exp | integer | 过期时间，Unix时间戳 |
| iat | integer | 签发时间，Unix时间戳 |
| jti | string | Token唯一标识，用于黑名单吊销 |
| token_type | string | `access` 或 `refresh` |
| tenant_id | integer | 租户ID（预留多租户支持） |
| roles | array | 用户角色编码列表 |
| dept_id | integer | 所属部门ID |

**JWT Payload示例：**

```json
{
  "iss": "capp-system",
  "sub": "10001",
  "aud": "capp-api",
  "exp": 1717740000,
  "iat": 1717732800,
  "jti": "jwt_7a8b9c0d1e2f",
  "token_type": "access",
  "tenant_id": 1,
  "roles": ["process_engineer", "quality_inspector"],
  "dept_id": 5
}
```

**PHP原生JWT实现要点：**

```php
// 核心实现逻辑（伪代码）
class JwtService {
    private $access_secret;
    private $refresh_secret;
    private $blacklist_redis;
    
    public function generateTokenPair($user_id, $roles, $dept_id) {
        $jti = bin2hex(random_bytes(16));
        $now = time();
        
        $access_payload = [
            'iss' => 'capp-system',
            'sub' => (string)$user_id,
            'aud' => 'capp-api',
            'exp' => $now + 7200,      // 2小时
            'iat' => $now,
            'jti' => 'jwt_' . $jti . '_access',
            'token_type' => 'access',
            'roles' => $roles,
            'dept_id' => $dept_id
        ];
        
        $refresh_payload = [
            'iss' => 'capp-system',
            'sub' => (string)$user_id,
            'aud' => 'capp-api',
            'exp' => $now + 604800,    // 7天
            'iat' => $now,
            'jti' => 'jwt_' . $jti . '_refresh',
            'token_type' => 'refresh'
        ];
        
        return [
            'access_token' => $this->encode($access_payload, $this->access_secret),
            'refresh_token' => $this->encode($refresh_payload, $this->refresh_secret),
            'expires_in' => 7200
        ];
    }
    
    public function verifyAccessToken($token) {
        $payload = $this->decode($token, $this->access_secret);
        if ($payload['token_type'] !== 'access') {
            throw new TokenInvalidException();
        }
        if ($this->blacklist_redis->exists('token_blacklist:' . $payload['jti'])) {
            throw new TokenRevokedException();
        }
        return $payload;
    }
}
```

### 3.3 接口权限控制

系统采用基于RBAC（Role-Based Access Control）的权限模型，结合接口级细粒度控制：

**权限层级：**

| 层级 | 说明 | 控制方式 |
|------|------|---------|
| 菜单权限 | 前端导航菜单可见性 | 角色配置 |
| 按钮权限 | 页面操作按钮可用性 | 角色配置 |
| 接口权限 | API接口访问控制 | 中间件校验 |
| 数据权限 | 数据行级隔离 | 查询条件过滤 |

**接口权限实现：**

每个受保护接口在PHP路由配置中绑定权限编码，请求经过 `AuthMiddleware` 时完成校验：

1. 从JWT中提取用户ID和角色列表
2. 查询Redis缓存获取用户完整权限编码集合
3. 校验当前请求接口所需的权限编码是否在用户权限集合中
4. 权限校验通过则继续执行，否则返回403状态码及200005业务码

**数据权限规则：**

| 规则编码 | 说明 | 数据过滤逻辑 |
|---------|------|------------|
| ALL | 全部数据 | 无过滤条件 |
| DEPT_ONLY | 本部门 | `dept_id = 当前用户dept_id` |
| DEPT_AND_CHILD | 本部门及子部门 | `dept_id IN (本部门及所有子部门ID)` |
| SELF_ONLY | 仅本人 | `created_by = 当前用户ID` |

数据权限在Repository层自动注入，通过全局查询构造器附加过滤条件，确保业务代码无感知。

---

## 4. 公共接口

### 4.1 登录

**接口信息：**

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/login` |
| Method | POST |
| 权限 | 公开接口，无需认证 |
| 限流 | 同一IP 5分钟内最多10次 |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| username | string | 是 | 用户名/工号/邮箱 |
| password | string | 是 | 密码（前端RSA加密传输） |
| captcha_id | string | 是 | 验证码ID |
| captcha_code | string | 是 | 验证码内容 |

**请求示例：**

```json
{
  "username": "zhangsan",
  "password": "encrypted_password_base64",
  "captcha_id": "cap_a1b2c3d4",
  "captcha_code": "7x9k"
}
```

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| access_token | string | Access Token |
| refresh_token | string | Refresh Token（同时写入Cookie） |
| expires_in | integer | Access Token有效期（秒） |
| token_type | string | 固定为 `Bearer` |
| user | object | 当前用户基本信息 |

**响应示例：**

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
      "dept_name": "工艺技术部",
      "roles": ["process_engineer"]
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

### 4.2 登出

**接口信息：**

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/logout` |
| Method | POST |
| 权限 | 需认证 |

**请求参数：** 无请求体

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": null,
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

**处理逻辑：**
- 将当前Access Token和Refresh Token加入Redis黑名单
- 清除HttpOnly Cookie中的Refresh Token
- 记录登出审计日志

### 4.3 Token刷新

**接口信息：**

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/auth/refresh` |
| Method | POST |
| 权限 | 需携带有效Refresh Token（Cookie自动携带） |

**请求参数：** 无请求体，Refresh Token从Cookie中读取

**响应参数：** 同登录接口

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 7200,
    "token_type": "Bearer"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

### 4.4 文件上传

**接口信息：**

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/files/upload` |
| Method | POST |
| Content-Type | `multipart/form-data` |
| 权限 | 需认证 |
| 单文件大小限制 | 50MB |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file | File | 是 | 文件二进制数据 |
| file_type | string | 是 | 文件分类：`avatar`, `drawing`, `process_card`, `attachment` |
| module | string | 是 | 关联模块：`user`, `product`, `process_route`, `process_card` |
| related_id | integer | 否 | 关联业务ID |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| file_id | string | 文件唯一标识（UUID） |
| file_name | string | 原始文件名 |
| file_size | integer | 文件大小（字节） |
| file_url | string | 文件访问URL |
| mime_type | string | MIME类型 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "file_id": "f8a3b2c1-d4e5-6789-0123-456789abcdef",
    "file_name": "工业压缩机总装图.dwg",
    "file_size": 2048000,
    "file_url": "https://capp.company.com/api/v1/files/f8a3b2c1-d4e5-6789-0123-456789abcdef",
    "mime_type": "application/acad"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

**文件存储策略：**
- 文件按类型分目录存储：`/uploads/{file_type}/{YYYY}/{MM}/{file_id}_{original_name}`
- 图片文件自动生成缩略图（最大边长1024px）
- 敏感文件（如图纸）存储路径不直接暴露，通过接口鉴权后流式下载
- 文件元数据（文件名、大小、上传人、关联模块）存入MySQL `sys_file` 表

### 4.5 文件下载

**接口信息：**

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/files/{file_id}` |
| Method | GET |
| 权限 | 需认证，根据文件关联模块校验数据权限 |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| download | integer | 否 | 为1时强制下载（Content-Disposition: attachment） |

**响应：** 文件二进制流，Content-Type根据文件MIME类型设置

---

## 5. 用户管理接口

### 5.1 用户CRUD

#### 5.1.1 获取用户列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users` |
| Method | GET |
| 权限 | `user:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 否 | 关键词（用户名/姓名/工号模糊搜索） |
| dept_id | integer | 否 | 部门ID筛选 |
| role_id | integer | 否 | 角色ID筛选 |
| status | integer | 否 | 状态：0禁用，1启用 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

**响应示例：**

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
        "employee_no": "EMP2024001",
        "phone": "13800138000",
        "email": "zhangsan@company.com",
        "avatar": "https://capp.company.com/static/avatar/10001.jpg",
        "dept_id": 5,
        "dept_name": "工艺技术部",
        "status": 1,
        "last_login_time": "2024-06-07T08:00:00Z",
        "created_at": "2024-01-15T09:30:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 156,
      "total_pages": 8,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 5.1.2 获取用户详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{user_id}` |
| Method | GET |
| 权限 | `user:view` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "user_id": 10001,
    "username": "zhangsan",
    "real_name": "张三",
    "employee_no": "EMP2024001",
    "phone": "13800138000",
    "email": "zhangsan@company.com",
    "avatar": "https://capp.company.com/static/avatar/10001.jpg",
    "dept_id": 5,
    "dept_name": "工艺技术部",
    "roles": [
      {
        "role_id": 3,
        "role_name": "工艺工程师",
        "role_code": "process_engineer"
      }
    ],
    "status": 1,
    "last_login_time": "2024-06-07T08:00:00Z",
    "created_at": "2024-01-15T09:30:00Z",
    "updated_at": "2024-06-01T10:00:00Z"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 5.1.3 创建用户

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users` |
| Method | POST |
| 权限 | `user:create` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| username | string | 是 | 用户名，3-20位字母数字下划线 |
| real_name | string | 是 | 真实姓名 |
| employee_no | string | 是 | 工号，全局唯一 |
| password | string | 是 | 初始密码（服务端强制要求首次登录修改） |
| phone | string | 否 | 手机号 |
| email | string | 否 | 邮箱 |
| dept_id | integer | 是 | 所属部门ID |
| role_ids | array | 是 | 角色ID数组 |
| status | integer | 否 | 状态，默认1 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "user_id": 10002,
    "username": "lisi",
    "real_name": "李四",
    "created_at": "2024-06-07T08:20:00Z"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 5.1.4 更新用户

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{user_id}` |
| Method | PUT |
| 权限 | `user:update` |

**请求参数：** 同创建用户（password字段除外）

#### 5.1.5 删除用户

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{user_id}` |
| Method | DELETE |
| 权限 | `user:delete` |

**处理逻辑：** 逻辑删除，设置 `is_deleted=1`，同时吊销该用户所有Token

#### 5.1.6 重置密码

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/users/{user_id}/reset-password` |
| Method | POST |
| 权限 | `user:reset_password` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| new_password | string | 是 | 新密码（需符合密码强度策略） |
| force_change | integer | 否 | 是否强制下次登录修改，默认1 |

### 5.2 角色权限

#### 5.2.1 获取角色列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles` |
| Method | GET |
| 权限 | `role:view` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "role_id": 1,
        "role_name": "系统管理员",
        "role_code": "admin",
        "description": "系统超级管理员，拥有所有权限",
        "user_count": 2,
        "created_at": "2024-01-01T00:00:00Z"
      },
      {
        "role_id": 3,
        "role_name": "工艺工程师",
        "role_code": "process_engineer",
        "description": "负责工艺路线设计与工艺卡片编制",
        "user_count": 12,
        "created_at": "2024-01-01T00:00:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 8,
      "total_pages": 1,
      "has_next": false,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 5.2.2 获取角色权限树

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles/{role_id}/permissions` |
| Method | GET |
| 权限 | `role:view` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "role_id": 3,
    "role_name": "工艺工程师",
    "permissions": [
      {
        "perm_id": 101,
        "perm_name": "工艺路线管理",
        "perm_code": "process_route:manage",
        "parent_id": 0,
        "perm_type": "menu",
        "children": [
          {
            "perm_id": 10101,
            "perm_name": "查看工艺路线",
            "perm_code": "process_route:view",
            "parent_id": 101,
            "perm_type": "button"
          },
          {
            "perm_id": 10102,
            "perm_name": "创建工艺路线",
            "perm_code": "process_route:create",
            "parent_id": 101,
            "perm_type": "button"
          }
        ]
      }
    ]
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 5.2.3 分配角色权限

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/roles/{role_id}/permissions` |
| Method | PUT |
| 权限 | `role:assign_permission` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| perm_ids | array | 是 | 权限ID数组 |

**处理逻辑：** 权限变更后，自动清除该角色下所有用户的Redis权限缓存，下次请求时重新加载

### 5.3 部门管理

#### 5.3.1 获取部门树

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments` |
| Method | GET |
| 权限 | `dept:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| with_users | integer | 否 | 是否包含用户数量统计，默认0 |

**响应示例：**

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
      "user_count": 45,
      "children": [
        {
          "dept_id": 5,
          "dept_name": "工艺技术部",
          "dept_code": "RD-PE",
          "parent_id": 1,
          "sort_order": 1,
          "user_count": 12,
          "children": []
        },
        {
          "dept_id": 6,
          "dept_name": "结构设计部",
          "dept_code": "RD-SE",
          "parent_id": 1,
          "sort_order": 2,
          "user_count": 18,
          "children": []
        }
      ]
    }
  ],
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 5.3.2 创建部门

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments` |
| Method | POST |
| 权限 | `dept:create` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| dept_name | string | 是 | 部门名称 |
| dept_code | string | 是 | 部门编码，全局唯一 |
| parent_id | integer | 否 | 上级部门ID，默认0（根部门） |
| sort_order | integer | 否 | 排序号，默认0 |
| leader_id | integer | 否 | 部门负责人用户ID |

#### 5.3.3 更新部门

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments/{dept_id}` |
| Method | PUT |
| 权限 | `dept:update` |

#### 5.3.4 删除部门

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/departments/{dept_id}` |
| Method | DELETE |
| 权限 | `dept:delete` |

**校验规则：** 若部门下存在用户或子部门，禁止删除，返回422状态码

---

## 6. 产品/零件接口

### 6.1 产品CRUD

#### 6.1.1 获取产品列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products` |
| Method | GET |
| 权限 | `product:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 否 | 产品名称/型号/图号模糊搜索 |
| product_type | string | 否 | 产品类型：`diaphragm_compressor`, `pressure_vessel`, `component` |
| status | integer | 否 | 状态：0草稿，1已发布，2已归档 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "product_id": 20001,
        "product_name": "GD-100/20型工业压缩机",
        "product_model": "GD-100/20",
        "drawing_no": "DW-GD100-001",
        "product_type": "diaphragm_compressor",
        "description": "排气量100Nm³/h，排气压力20MPa",
        "status": 1,
        "version": "V2.1",
        "created_by": 10001,
        "created_by_name": "张三",
        "created_at": "2024-03-15T10:00:00Z",
        "updated_at": "2024-05-20T14:30:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 86,
      "total_pages": 5,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 6.1.2 获取产品详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}` |
| Method | GET |
| 权限 | `product:view` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "product_id": 20001,
    "product_name": "GD-100/20型工业压缩机",
    "product_model": "GD-100/20",
    "drawing_no": "DW-GD100-001",
    "product_type": "diaphragm_compressor",
    "description": "排气量100Nm³/h，排气压力20MPa",
    "technical_params": {
      "flow_rate": "100Nm³/h",
      "discharge_pressure": "20MPa",
      "suction_pressure": "0.1MPa",
      "motor_power": "75kW",
      "diaphragm_material": "316L不锈钢"
    },
    "status": 1,
    "version": "V2.1",
    "bom_version": "BV3.0",
    "attachments": [
      {
        "file_id": "f8a3b2c1-d4e5-6789-0123-456789abcdef",
        "file_name": "总装配图.dwg",
        "file_url": "https://capp.company.com/api/v1/files/f8a3b2c1-d4e5-6789-0123-456789abcdef"
      }
    ],
    "created_by": 10001,
    "created_by_name": "张三",
    "created_at": "2024-03-15T10:00:00Z",
    "updated_at": "2024-05-20T14:30:00Z"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 6.1.3 创建产品

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products` |
| Method | POST |
| 权限 | `product:create` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| product_name | string | 是 | 产品名称 |
| product_model | string | 是 | 产品型号 |
| drawing_no | string | 是 | 图号，全局唯一 |
| product_type | string | 是 | 产品类型 |
| description | string | 否 | 产品描述 |
| technical_params | object | 否 | 技术参数JSON对象 |
| attachment_ids | array | 否 | 关联附件file_id数组 |

#### 6.1.4 更新产品

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}` |
| Method | PUT |
| 权限 | `product:update` |

#### 6.1.5 删除产品

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}` |
| Method | DELETE |
| 权限 | `product:delete` |

**校验规则：** 若产品已关联BOM或工艺路线，禁止删除，返回422状态码及详细冲突信息

#### 6.1.6 产品版本发布

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/publish` |
| Method | POST |
| 权限 | `product:publish` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| version | string | 是 | 版本号，如 `V2.2` |
| change_log | string | 否 | 变更说明 |

**处理逻辑：** 发布时自动创建版本快照，历史版本可回溯查看

### 6.2 BOM管理

#### 6.2.1 获取产品BOM

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/bom` |
| Method | GET |
| 权限 | `bom:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| version | string | 否 | BOM版本号，默认返回最新版本 |
| level | integer | 否 | 展开层级，默认全部展开（0表示全部） |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "bom_id": 30001,
    "product_id": 20001,
    "version": "BV3.0",
    "status": 1,
    "items": [
      {
        "item_id": 3000101,
        "part_id": 40001,
        "part_no": "P-001",
        "part_name": "曲轴箱",
        "part_type": "assembly",
        "quantity": 1,
        "unit": "件",
        "level": 1,
        "parent_item_id": 0,
        "children": [
          {
            "item_id": 300010101,
            "part_id": 40002,
            "part_no": "P-001-01",
            "part_name": "曲轴箱体",
            "part_type": "part",
            "quantity": 1,
            "unit": "件",
            "level": 2,
            "parent_item_id": 3000101,
            "children": []
          }
        ]
      }
    ],
    "created_by": 10001,
    "created_at": "2024-03-20T09:00:00Z"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 6.2.2 保存BOM

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/bom` |
| Method | PUT |
| 权限 | `bom:update` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| version | string | 是 | BOM版本号 |
| items | array | 是 | BOM明细项数组 |

**items子项结构：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| part_id | integer | 是 | 零件ID |
| quantity | number | 是 | 数量 |
| unit | string | 是 | 单位 |
| parent_item_id | integer | 否 | 父项ID，顶层为0 |
| sort_order | integer | 否 | 排序号 |

**校验规则：**
- 禁止循环依赖（子项不能直接或间接引用自身父项）
- 零件ID必须存在于零件库中
- 顶层item的parent_item_id必须为0

#### 6.2.3 获取BOM版本历史

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/products/{product_id}/bom/versions` |
| Method | GET |
| 权限 | `bom:view` |

### 6.3 零件查询

#### 6.3.1 获取零件列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts` |
| Method | GET |
| 权限 | `part:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 否 | 零件名称/编号/图号模糊搜索 |
| part_type | string | 否 | 零件类型：`part`, `assembly`, `standard_part`, `raw_material` |
| material | string | 否 | 材质筛选 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "part_id": 40001,
        "part_no": "P-001",
        "part_name": "曲轴箱",
        "part_type": "assembly",
        "drawing_no": "DW-P001-00",
        "material": "HT250",
        "specification": "按图加工",
        "weight": 125.5,
        "unit": "kg",
        "stock_quantity": 3,
        "created_at": "2024-01-10T08:00:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 520,
      "total_pages": 26,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 6.3.2 获取零件详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts/{part_id}` |
| Method | GET |
| 权限 | `part:view` |

#### 6.3.3 创建零件

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts` |
| Method | POST |
| 权限 | `part:create` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| part_no | string | 是 | 零件编号，全局唯一 |
| part_name | string | 是 | 零件名称 |
| part_type | string | 是 | 零件类型 |
| drawing_no | string | 否 | 图号 |
| material | string | 否 | 材质 |
| specification | string | 否 | 规格 |
| weight | number | 否 | 重量（kg） |
| unit | string | 否 | 计量单位 |
| technical_requirements | string | 否 | 技术要求 |
| attachment_ids | array | 否 | 关联附件 |

#### 6.3.4 更新零件

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts/{part_id}` |
| Method | PUT |
| 权限 | `part:update` |

#### 6.3.5 删除零件

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/parts/{part_id}` |
| Method | DELETE |
| 权限 | `part:delete` |

**校验规则：** 若零件已被BOM引用，禁止删除

---

## 7. 工艺管理接口

### 7.1 工艺路线CRUD

#### 7.1.1 获取工艺路线列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes` |
| Method | GET |
| 权限 | `process_route:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 否 | 工艺路线名称/编号模糊搜索 |
| product_id | integer | 否 | 关联产品ID筛选 |
| part_id | integer | 否 | 关联零件ID筛选 |
| status | integer | 否 | 状态：0草稿，1待审核，2已发布，3已归档 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "route_id": 50001,
        "route_no": "PR-2024-001",
        "route_name": "GD-100/20曲轴箱加工工艺路线",
        "product_id": 20001,
        "product_name": "GD-100/20型工业压缩机",
        "part_id": 40001,
        "part_name": "曲轴箱",
        "version": "V1.2",
        "status": 2,
        "status_name": "已发布",
        "operation_count": 8,
        "created_by": 10001,
        "created_by_name": "张三",
        "created_at": "2024-04-01T09:00:00Z",
        "updated_at": "2024-05-10T14:00:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 64,
      "total_pages": 4,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 7.1.2 获取工艺路线详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}` |
| Method | GET |
| 权限 | `process_route:view` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "route_id": 50001,
    "route_no": "PR-2024-001",
    "route_name": "GD-100/20曲轴箱加工工艺路线",
    "product_id": 20001,
    "product_name": "GD-100/20型工业压缩机",
    "part_id": 40001,
    "part_name": "曲轴箱",
    "version": "V1.2",
    "status": 2,
    "description": "适用于批量生产",
    "operations": [
      {
        "op_id": 5000101,
        "op_no": "10",
        "op_name": "下料",
        "work_center_id": 8,
        "work_center_name": "锯床组",
        "setup_time": 30,
        "run_time": 45,
        "time_unit": "min",
        "tools": ["带锯床G4028", "游标卡尺0-300mm"],
        "inspection_items": [
          {
            "item_name": "毛坯尺寸",
            "standard_value": "350×280×200",
            "tolerance": "±2",
            "inspection_method": "游标卡尺"
          }
        ],
        "material_consumption": [
          {
            "material_name": "HT250铸件",
            "specification": "350×280×200",
            "quantity": 1,
            "unit": "件"
          }
        ]
      },
      {
        "op_id": 5000102,
        "op_no": "20",
        "op_name": "粗铣基准面",
        "work_center_id": 9,
        "work_center_name": "铣床组",
        "setup_time": 20,
        "run_time": 60,
        "time_unit": "min",
        "tools": ["立式铣床X5032", "面铣刀φ100"],
        "inspection_items": [],
        "material_consumption": []
      }
    ],
    "created_by": 10001,
    "created_by_name": "张三",
    "created_at": "2024-04-01T09:00:00Z",
    "updated_at": "2024-05-10T14:00:00Z"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 7.1.3 创建工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes` |
| Method | POST |
| 权限 | `process_route:create` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| route_name | string | 是 | 工艺路线名称 |
| product_id | integer | 是 | 关联产品ID |
| part_id | integer | 是 | 关联零件ID |
| description | string | 否 | 工艺路线说明 |
| operations | array | 是 | 工序数组 |

**operations子项结构：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| op_no | string | 是 | 工序号，如 `10`, `20` |
| op_name | string | 是 | 工序名称 |
| work_center_id | integer | 是 | 工作中心ID |
| setup_time | integer | 否 | 准备时间（分钟） |
| run_time | integer | 否 | 运行时间（分钟） |
| time_unit | string | 否 | 时间单位，默认 `min` |
| tools | array | 否 | 使用设备/工装列表 |
| inspection_items | array | 否 | 检验项目数组 |
| material_consumption | array | 否 | 材料消耗数组 |

#### 7.1.4 更新工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}` |
| Method | PUT |
| 权限 | `process_route:update` |

**校验规则：** 仅 `status=0`（草稿）或 `status=3`（已归档）状态允许编辑；已发布工艺路线需先撤销发布

#### 7.1.5 删除工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}` |
| Method | DELETE |
| 权限 | `process_route:delete` |

**校验规则：** 仅草稿状态允许删除

#### 7.1.6 提交审核

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/submit` |
| Method | POST |
| 权限 | `process_route:submit` |

**处理逻辑：** 状态从草稿变为待审核，触发企业微信消息通知审核人

#### 7.1.7 审核工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/audit` |
| Method | POST |
| 权限 | `process_route:audit` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| action | string | 是 | `approve` 通过 或 `reject` 驳回 |
| comment | string | 否 | 审核意见 |

#### 7.1.8 发布工艺路线

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/publish` |
| Method | POST |
| 权限 | `process_route:publish` |

**处理逻辑：** 审核通过后，执行发布操作，状态变为已发布，生成版本快照，旧版本自动归档

#### 7.1.9 撤销发布

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-routes/{route_id}/revoke` |
| Method | POST |
| 权限 | `process_route:revoke` |

**校验规则：** 若工艺路线已关联生产工单，禁止撤销，返回422状态码

### 7.2 工序管理

#### 7.2.1 获取工序模板库

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/operation-templates` |
| Method | GET |
| 权限 | `operation_template:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 否 | 工序名称模糊搜索 |
| work_center_id | integer | 否 | 工作中心筛选 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "template_id": 60001,
        "op_name": "车削外圆",
        "work_center_id": 10,
        "work_center_name": "车床组",
        "default_setup_time": 15,
        "default_run_time": 30,
        "default_tools": ["数控车床CK6140", "外圆车刀"],
        "description": "适用于轴类零件外圆加工",
        "usage_count": 45
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 120,
      "total_pages": 6,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 7.2.2 创建工序模板

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/operation-templates` |
| Method | POST |
| 权限 | `operation_template:create` |

#### 7.2.3 获取工作中心列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/work-centers` |
| Method | GET |
| 权限 | `work_center:view` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "work_center_id": 8,
        "work_center_code": "WC-SAW",
        "work_center_name": "锯床组",
        "work_center_type": "cutting",
        "department": "机加工车间",
        "capacity_per_day": 8,
        "time_unit": "h",
        "status": 1
      },
      {
        "work_center_id": 9,
        "work_center_code": "WC-MILL",
        "work_center_name": "铣床组",
        "work_center_type": "machining",
        "department": "机加工车间",
        "capacity_per_day": 16,
        "time_unit": "h",
        "status": 1
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 15,
      "total_pages": 1,
      "has_next": false,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

### 7.3 工艺卡片

#### 7.3.1 获取工艺卡片列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-cards` |
| Method | GET |
| 权限 | `process_card:view` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| keyword | string | 否 | 工艺卡片名称/编号模糊搜索 |
| route_id | integer | 否 | 关联工艺路线ID |
| card_type | string | 否 | 卡片类型：`process_card`, `inspection_card`, `tooling_card` |
| status | integer | 否 | 状态 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "list": [
      {
        "card_id": 70001,
        "card_no": "PC-2024-001-10",
        "card_name": "曲轴箱下料工序卡",
        "card_type": "process_card",
        "route_id": 50001,
        "route_name": "GD-100/20曲轴箱加工工艺路线",
        "op_id": 5000101,
        "op_name": "下料",
        "status": 1,
        "file_url": "https://capp.company.com/api/v1/files/f8a3b2c1-d4e5-6789-0123-456789abcdef",
        "created_by": 10001,
        "created_by_name": "张三",
        "created_at": "2024-04-05T10:00:00Z"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 32,
      "total_pages": 2,
      "has_next": true,
      "has_prev": false
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 7.3.2 生成工艺卡片

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-cards/generate` |
| Method | POST |
| 权限 | `process_card:generate` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| route_id | integer | 是 | 工艺路线ID |
| card_type | string | 是 | 卡片类型 |
| template_id | integer | 否 | 卡片模板ID，默认使用系统默认模板 |

**处理逻辑：** 服务端根据工艺路线数据和模板自动生成PDF格式工艺卡片，存储到文件系统，返回下载URL

#### 7.3.3 下载工艺卡片

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/process-cards/{card_id}/download` |
| Method | GET |
| 权限 | `process_card:view` |

**响应：** PDF文件二进制流，Content-Type: `application/pdf`

---

## 8. 企业微信接口

### 8.1 扫码登录

#### 8.1.1 获取登录二维码

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat/login-qrcode` |
| Method | GET |
| 权限 | 公开接口 |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| redirect_url | string | 否 | 登录成功后前端跳转地址 |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| qrcode_url | string | 二维码图片URL（企业微信生成） |
| scene_str | string | 场景值，用于轮询登录状态 |
| expires_in | integer | 二维码有效期（秒），默认300 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "qrcode_url": "https://work.weixin.qq.com/cgi-bin/get_qrcode?key=abc123",
    "scene_str": "login_8f3a9b2c1d4e",
    "expires_in": 300
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 8.1.2 轮询登录状态

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat/login-poll` |
| Method | GET |
| 权限 | 公开接口 |
| 限流 | 同一scene_str每秒最多1次 |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| scene_str | string | 是 | 二维码场景值 |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| status | string | 状态：`pending` 待扫码，`scanned` 已扫码待确认，`confirmed` 已确认，`expired` 已过期 |
| user_info | object | status为confirmed时返回，包含用户基本信息和Token |

**响应示例（登录成功）：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "status": "confirmed",
    "user_info": {
      "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "expires_in": 7200,
      "token_type": "Bearer",
      "user": {
        "user_id": 10001,
        "username": "zhangsan",
        "real_name": "张三",
        "avatar": "https://capp.company.com/static/avatar/10001.jpg"
      }
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

**处理流程：**
1. 前端调用获取二维码接口，展示二维码
2. 前端以1-2秒间隔轮询登录状态接口
3. 用户扫码后，企业微信推送事件到服务端回调接口
4. 服务端将登录状态与scene_str关联，写入Redis（TTL=5分钟）
5. 用户在企业微信确认登录后，服务端完成用户绑定/注册，生成Token，更新Redis状态
6. 前端轮询到confirmed状态，获取Token完成登录

### 8.2 用户同步

#### 8.2.1 触发用户同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat/sync-users` |
| Method | POST |
| 权限 | `wechat:sync`（仅管理员） |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| sync_type | string | 否 | `full` 全量同步（默认），`incremental` 增量同步 |
| dept_id | integer | 否 | 指定同步部门ID，默认全部 |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| sync_task_id | string | 同步任务ID |
| status | string | 任务状态：`queued` 已入队 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "sync_task_id": "sync_20240607082000_abc123",
    "status": "queued"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

**处理逻辑：** 同步任务进入Redis队列，由后台Worker异步执行，避免阻塞HTTP请求

#### 8.2.2 查询同步任务状态

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat/sync-tasks/{sync_task_id}` |
| Method | GET |
| 权限 | `wechat:sync` |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| sync_task_id | string | 任务ID |
| status | string | `pending`, `running`, `completed`, `failed` |
| progress | object | 进度信息：total, processed, succeeded, failed |
| result | object | 完成时返回详细结果 |
| error | string | 失败时返回错误信息 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "sync_task_id": "sync_20240607082000_abc123",
    "status": "completed",
    "progress": {
      "total": 156,
      "processed": 156,
      "succeeded": 154,
      "failed": 2
    },
    "result": {
      "created": 3,
      "updated": 151,
      "disabled": 2,
      "failed_details": [
        {
          "wechat_user_id": "zhangsan",
          "reason": "邮箱格式非法"
        }
      ]
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

### 8.3 消息推送

#### 8.3.1 发送应用消息

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat/send-message` |
| Method | POST |
| 权限 | `wechat:message` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| msg_type | string | 是 | 消息类型：`text`, `markdown`, `news`, `template_card` |
| to_user | array | 否 | 接收人企业微信UserID数组，与to_party二选一 |
| to_party | array | 否 | 接收部门ID数组 |
| content | object | 是 | 消息内容，根据msg_type变化 |

**text类型content结构：**

```json
{
  "content": "您有一条待审核的工艺路线：GD-100/20曲轴箱加工工艺路线，请尽快处理。"
}
```

**markdown类型content结构：**

```json
{
  "content": "**工艺审核通知**\n> 申请人：张三\n> 工艺路线：GD-100/20曲轴箱加工工艺路线\n> 提交时间：2024-06-07 08:20"
}
```

**template_card类型（用于复杂通知）：**

```json
{
  "card_type": "text_notice",
  "source": {
    "desc": "CAPP系统"
  },
  "main_title": {
    "title": "工艺审核通知",
    "desc": "您有一条待审核的工艺路线"
  },
  "jump_list": [
    {
      "type": 1,
      "url": "https://capp.company.com/process-routes/50001",
      "title": "查看详情"
    }
  ]
}
```

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "message_id": "msg_20240607082000_xyz789",
    "invalid_user": [],
    "invalid_party": []
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 8.3.2 发送审批通知（封装接口）

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/wechat/send-approval-notify` |
| Method | POST |
| 权限 | 内部服务调用（由工艺审核流程自动触发） |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| approval_type | string | 是 | `process_route`, `process_card`, `bom_change` |
| business_id | integer | 是 | 业务单据ID |
| business_no | string | 是 | 业务单据编号 |
| business_name | string | 是 | 业务单据名称 |
| applicant_id | integer | 是 | 申请人用户ID |
| applicant_name | string | 是 | 申请人姓名 |
| approver_ids | array | 是 | 审批人企业微信UserID数组 |
| action_url | string | 是 | 审批页面URL |

**处理逻辑：** 服务端自动组装模板卡片消息，调用企业微信应用消息接口发送

---

## 9. AI与MaxKB接口

### 9.1 对话接口

#### 9.1.1 发起AI对话

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/chat` |
| Method | POST |
| 权限 | `ai:chat` |
| 限流 | 每用户每分钟最多20次 |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| session_id | string | 否 | 会话ID，首次对话可不传，服务端返回新session_id |
| message | string | 是 | 用户输入消息 |
| context_type | string | 否 | 上下文类型：`general`, `process_design`, `quality_analysis`, `troubleshooting` |
| product_id | integer | 否 | 关联产品ID，用于限定回答范围 |
| stream | integer | 否 | 是否流式返回，默认0（非流式） |

**请求示例：**

```json
{
  "session_id": "sess_8f3a9b2c1d4e",
  "message": "工业压缩机膜片破裂的常见原因有哪些？",
  "context_type": "troubleshooting",
  "product_id": 20001,
  "stream": 0
}
```

**响应参数（非流式）：**

| 参数 | 类型 | 说明 |
|------|------|------|
| session_id | string | 会话ID |
| message_id | string | 消息ID |
| reply | string | AI回复内容 |
| references | array | 引用知识库文档列表 |
| confidence | number | 置信度 0-1 |
| tokens_used | object | token消耗统计 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "session_id": "sess_8f3a9b2c1d4e",
    "message_id": "msg_20240607082000_ai001",
    "reply": "工业压缩机膜片破裂的常见原因包括：\n1. **疲劳损伤**：长期交变应力导致膜片材料疲劳，常见于超过设计寿命的膜片；\n2. **腐蚀**：介质中含有腐蚀性成分，或膜片材质选择不当；\n3. **超压运行**：排气压力超过设计值，导致膜片应力超限；\n4. **异物冲击**：液压油或气腔中存在固体颗粒；\n5. **安装不当**：膜片预紧力不均匀或安装角度偏差。\n\n建议定期检查膜片表面状态，建立更换周期档案。",
    "references": [
      {
        "doc_id": "kb_001",
        "doc_title": "工业压缩机故障诊断手册",
        "doc_url": "https://capp.company.com/knowledge/fault-diagnosis",
        "relevance": 0.95
      },
      {
        "doc_id": "kb_015",
        "doc_title": "GD-100/20维护保养规程",
        "doc_url": "https://capp.company.com/knowledge/maintenance-gd100",
        "relevance": 0.88
      }
    ],
    "confidence": 0.92,
    "tokens_used": {
      "prompt_tokens": 256,
      "completion_tokens": 312,
      "total_tokens": 568
    }
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

**流式响应（SSE）：**

当 `stream=1` 时，服务端返回 `text/event-stream` 格式：

```
HTTP/1.1 200 OK
Content-Type: text/event-stream
Cache-Control: no-cache
Connection: keep-alive

event: start
data: {"session_id":"sess_8f3a9b2c1d4e","message_id":"msg_20240607082000_ai001"}

event: delta
data: {"content":"工业压缩机膜片破裂的常见原因包括："}

event: delta
data: {"content":"\n1. **疲劳损伤**：长期交变应力导致膜片材料疲劳"}

...

event: finish
data: {"references":[{"doc_id":"kb_001","doc_title":"工业压缩机故障诊断手册","relevance":0.95}],"confidence":0.92,"tokens_used":{"total_tokens":568}}

event: end
data: {}
```

#### 9.1.2 获取对话历史

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/sessions/{session_id}/messages` |
| Method | GET |
| 权限 | `ai:chat` |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "session_id": "sess_8f3a9b2c1d4e",
    "title": "工业压缩机膜片故障咨询",
    "messages": [
      {
        "message_id": "msg_20240607082000_user001",
        "role": "user",
        "content": "工业压缩机膜片破裂的常见原因有哪些？",
        "created_at": "2024-06-07T08:20:00Z"
      },
      {
        "message_id": "msg_20240607082000_ai001",
        "role": "assistant",
        "content": "工业压缩机膜片破裂的常见原因包括：...",
        "references": [...],
        "created_at": "2024-06-07T08:20:05Z"
      }
    ]
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

### 9.2 决策接口

#### 9.2.1 工艺参数推荐

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/recommend-process-params` |
| Method | POST |
| 权限 | `ai:decision` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| part_id | integer | 是 | 零件ID |
| operation_name | string | 是 | 工序名称，如 `车削外圆`, `铣削平面` |
| material | string | 是 | 材料牌号 |
| requirements | object | 否 | 加工要求：尺寸精度、表面粗糙度等 |

**请求示例：**

```json
{
  "part_id": 40002,
  "operation_name": "车削外圆",
  "material": "45#钢",
  "requirements": {
    "diameter": "φ80mm",
    "tolerance": "h7",
    "surface_roughness": "Ra1.6",
    "length": "200mm"
  }
}
```

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| recommendations | array | 推荐方案数组 |
| confidence | number | 整体置信度 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "recommendations": [
      {
        "rank": 1,
        "machine": "数控车床CK6140",
        "tool": "硬质合金外圆车刀",
        "cutting_speed": "120m/min",
        "feed_rate": "0.15mm/r",
        "cutting_depth": "2mm",
        "coolant": "乳化液",
        "estimated_time": "12min",
        "confidence": 0.91,
        "reason": "基于历史同类零件加工数据，该参数组合在45#钢外圆精车中表现稳定，尺寸合格率99.2%"
      },
      {
        "rank": 2,
        "machine": "数控车床CK6150",
        "tool": "涂层硬质合金车刀",
        "cutting_speed": "150m/min",
        "feed_rate": "0.12mm/r",
        "cutting_depth": "1.5mm",
        "coolant": "乳化液",
        "estimated_time": "10min",
        "confidence": 0.85,
        "reason": "更高切削速度可提升效率，但对机床刚性要求更高"
      }
    ],
    "confidence": 0.88
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 9.2.2 工艺路线智能生成

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/generate-process-route` |
| Method | POST |
| 权限 | `ai:decision` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| part_id | integer | 是 | 零件ID |
| production_type | string | 否 | 生产类型：`single` 单件, `batch` 批量, `mass` 大量，默认 `batch` |
| constraints | array | 否 | 约束条件，如 `["禁用热处理", "优先数控设备"]` |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| draft_route | object | 生成的工艺路线草案 |
| suggestions | array | 优化建议 |
| confidence | number | 置信度 |

**处理逻辑：** AI基于零件信息（材质、结构特征、技术要求）和历史工艺数据，生成推荐工艺路线，由工艺工程师确认后保存

### 9.3 知识库查询

#### 9.3.1 搜索知识库

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/knowledge/search` |
| Method | POST |
| 权限 | `ai:knowledge` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| query | string | 是 | 搜索关键词或自然语言问题 |
| category | string | 否 | 知识分类：`process_standard`, `fault_diagnosis`, `material_data`, `equipment_manual` |
| top_k | integer | 否 | 返回结果数量，默认5，最大20 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "results": [
      {
        "doc_id": "kb_001",
        "doc_title": "工业压缩机故障诊断手册",
        "doc_category": "fault_diagnosis",
        "doc_url": "https://capp.company.com/knowledge/fault-diagnosis",
        "snippet": "膜片破裂原因分析：1. 疲劳损伤...",
        "relevance": 0.95,
        "updated_at": "2024-05-15T10:00:00Z"
      }
    ],
    "total": 15
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

### 9.4 MaxKB文档管理

#### 9.4.1 上传文档到MaxKB

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/maxkb/documents` |
| Method | POST |
| 权限 | `ai:maxkb_manage` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| file_id | string | 是 | 系统文件ID（需先调用文件上传接口） |
| kb_id | string | 是 | MaxKB知识库ID |
| doc_name | string | 否 | 文档名称，默认使用原文件名 |
| category | string | 否 | 文档分类标签 |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| maxkb_doc_id | string | MaxKB文档ID |
| parse_status | string | 解析状态：`pending`, `parsing`, `completed`, `failed` |
| chunk_count | integer | 文档分块数量（解析完成后） |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "maxkb_doc_id": "mdoc_abc123",
    "parse_status": "pending",
    "chunk_count": 0
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

**处理逻辑：**
1. 服务端从本地文件系统读取文件
2. 调用MaxKB API上传文档到指定知识库
3. 记录映射关系到本地 `ai_maxkb_doc` 表
4. 异步轮询解析状态，完成后更新chunk_count

#### 9.4.2 获取MaxKB文档列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/maxkb/documents` |
| Method | GET |
| 权限 | `ai:maxkb_manage` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| kb_id | string | 否 | 知识库ID筛选 |
| status | string | 否 | 解析状态筛选 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

#### 9.4.3 删除MaxKB文档

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/maxkb/documents/{maxkb_doc_id}` |
| Method | DELETE |
| 权限 | `ai:maxkb_manage` |

**处理逻辑：** 先调用MaxKB API删除文档，成功后删除本地映射记录

### 9.5 MaxKB智能体调用

#### 9.5.1 调用MaxKB智能体

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/ai/maxkb/agents/{agent_id}/invoke` |
| Method | POST |
| 权限 | `ai:agent` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| input | string | 是 | 用户输入 |
| session_id | string | 否 | 会话ID |
| variables | object | 否 | 智能体变量赋值 |

**请求示例：**

```json
{
  "input": "分析GD-100/20型工业压缩机曲轴箱的加工工艺",
  "session_id": "sess_8f3a9b2c1d4e",
  "variables": {
    "product_model": "GD-100/20",
    "part_name": "曲轴箱",
    "material": "HT250"
  }
}
```

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| session_id | string | 会话ID |
| output | string | 智能体输出 |
| actions | array | 触发的动作列表（如需要调用外部工具） |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "session_id": "sess_8f3a9b2c1d4e",
    "output": "基于GD-100/20型工业压缩机曲轴箱（材质HT250）的结构特征和历史工艺数据，推荐加工工艺路线如下：\n\n1. 下料：采用带锯床切割铸件毛坯...\n2. 粗铣基准面：使用立式铣床X5032...\n\n该方案已参考同类型零件加工记录32条，平均加工周期18小时，尺寸合格率98.5%。",
    "actions": [
      {
        "action_type": "generate_process_route",
        "params": {
          "part_id": 40002,
          "suggested_operations": [...]
        }
      }
    ]
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

---

## 10. 外部系统对接接口

### 10.1 数据同步触发

#### 10.1.1 触发ERP数据同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/external/sync/erp` |
| Method | POST |
| 权限 | `external:sync` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| sync_type | string | 是 | 同步类型：`material_master`, `inventory`, `production_order` |
| sync_mode | string | 否 | 同步模式：`full` 全量（默认）, `incremental` 增量, `delta` 按时间差量 |
| start_time | string | 否 | 增量同步起始时间，ISO 8601格式 |
| end_time | string | 否 | 增量同步结束时间 |
| callback_url | string | 否 | 同步完成后回调URL |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| sync_task_id | string | 同步任务ID |
| status | string | 任务状态：`queued` |
| estimated_time | integer | 预估执行时间（秒） |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "sync_task_id": "sync_erp_20240607082000_abc123",
    "status": "queued",
    "estimated_time": 120
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 10.1.2 触发PLM数据同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/external/sync/plm` |
| Method | POST |
| 权限 | `external:sync` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| sync_type | string | 是 | 同步类型：`product`, `part`, `bom`, `drawing` |
| sync_mode | string | 否 | 同步模式 |
| product_id | integer | 否 | 指定产品ID同步 |
| drawing_no | string | 否 | 指定图号同步 |
| callback_url | string | 否 | 回调URL |

#### 10.1.3 触发MES数据同步

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/external/sync/mes` |
| Method | POST |
| 权限 | `external:sync` |

**请求参数：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| sync_type | string | 是 | 同步类型：`work_center`, `process_feedback`, `quality_record` |
| sync_mode | string | 否 | 同步模式 |
| work_center_id | integer | 否 | 指定工作中心同步 |
| date_range | object | 否 | 日期范围 `{start_date, end_date}` |
| callback_url | string | 否 | 回调URL |

### 10.2 同步状态查询

#### 10.2.1 查询同步任务详情

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/external/sync-tasks/{sync_task_id}` |
| Method | GET |
| 权限 | `external:sync` |

**响应参数：**

| 参数 | 类型 | 说明 |
|------|------|------|
| sync_task_id | string | 任务ID |
| system | string | 目标系统：`erp`, `plm`, `mes` |
| sync_type | string | 同步类型 |
| sync_mode | string | 同步模式 |
| status | string | `pending`, `running`, `completed`, `failed`, `cancelled` |
| progress | object | 进度详情 |
| result | object | 结果统计 |
| error_log | string | 错误日志（失败时） |
| started_at | string | 开始时间 |
| completed_at | string | 完成时间 |

**响应示例：**

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "sync_task_id": "sync_erp_20240607082000_abc123",
    "system": "erp",
    "sync_type": "material_master",
    "sync_mode": "incremental",
    "status": "completed",
    "progress": {
      "total": 520,
      "processed": 520,
      "succeeded": 518,
      "failed": 2,
      "percentage": 100
    },
    "result": {
      "created": 12,
      "updated": 506,
      "unchanged": 0,
      "failed_details": [
        {
          "source_id": "M-2024-0891",
          "source_name": "特种合金钢棒",
          "error": "材质编码在CAPP系统中不存在映射关系"
        }
      ]
    },
    "error_log": null,
    "started_at": "2024-06-07T08:20:00Z",
    "completed_at": "2024-06-07T08:22:15Z"
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

#### 10.2.2 查询同步任务列表

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/external/sync-tasks` |
| Method | GET |
| 权限 | `external:sync` |

**请求参数（Query）：**

| 参数 | 类型 | 必填 | 说明 |
|------|------|------|------|
| system | string | 否 | 目标系统筛选 |
| sync_type | string | 否 | 同步类型筛选 |
| status | string | 否 | 状态筛选 |
| start_date | string | 否 | 起始日期 |
| end_date | string | 否 | 结束日期 |
| page | integer | 否 | 页码 |
| page_size | integer | 否 | 每页数量 |

#### 10.2.3 取消同步任务

| 项目 | 内容 |
|------|------|
| URL | `/api/v1/external/sync-tasks/{sync_task_id}/cancel` |
| Method | POST |
| 权限 | `external:sync` |

**校验规则：** 仅 `pending` 或 `running` 状态的任务允许取消

---

## 11. 接口版本管理策略

### 11.1 版本命名规则

接口版本采用语义化版本控制（SemVer）的简化形式，仅保留主版本号：

- URL中版本标识：`/api/v1/`, `/api/v2/`
- 主版本号变更（v1 → v2）：表示存在不兼容的破坏性变更
- 次版本和修订版本不在URL中体现，通过文档和变更日志管理

### 11.2 版本演进策略

| 策略 | 说明 |
|------|------|
| 并行运行 | 新版本上线后，旧版本保留至少6个月过渡期 |
| 废弃通知 | 旧版本废弃前90天，在响应Header中增加 `Deprecation` 和 `Sunset` 标识 |
| 强制升级 | 过渡期结束后，旧版本返回410 Gone状态码，引导客户端升级 |

**废弃响应Header示例：**

```
Deprecation: true
Sunset: Sat, 31 Dec 2024 23:59:59 GMT
Link: </api/v2/users>; rel="successor-version"
```

### 11.3 版本兼容性原则

**v1版本期间允许的变更（向后兼容）：**
- 新增接口
- 新增可选请求参数
- 新增响应字段
- 放宽参数校验规则
- 修复Bug导致的响应变化

**必须升级主版本的变更（不兼容）：**
- 删除或重命名接口
- 删除或重命名请求/响应字段
- 新增必填参数
- 收紧参数校验规则
- 修改接口行为语义
- 修改认证授权机制

### 11.4 PHP原生版本路由实现

```php
// 路由配置示例（伪代码）
$router = new Router();

// v1 路由组
$router->group('/api/v1', function($router) {
    // 认证模块
    $router->post('/auth/login', 'AuthController@login');
    $router->post('/auth/logout', 'AuthController@logout');
    $router->post('/auth/refresh', 'AuthController@refresh');
    
    // 用户模块
    $router->get('/users', 'UserController@index');
    $router->get('/users/{id}', 'UserController@show');
    $router->post('/users', 'UserController@create');
    $router->put('/users/{id}', 'UserController@update');
    $router->delete('/users/{id}', 'UserController@delete');
    
    // ... 其他模块
});

// v2 路由组（未来扩展）
$router->group('/api/v2', function($router) {
    // v2 接口定义
});

// 全局中间件
$router->middleware('AuthMiddleware');
$router->middleware('RateLimitMiddleware');
$router->middleware('ReplayProtectionMiddleware');
```

---

## 12. 接口限流与防重放设计

### 12.1 接口限流设计

#### 12.1.1 限流策略

系统采用多层限流机制，基于Redis实现分布式计数器：

| 限流层级 | 维度 | 策略 | 默认值 |
|---------|------|------|--------|
| 全局限流 | 服务端整体 | 令牌桶算法 | 每秒1000请求 |
| IP限流 | 客户端IP | 滑动窗口计数器 | 每分钟600请求 |
| 用户限流 | 用户ID | 滑动窗口计数器 | 每分钟300请求 |
| 接口限流 | 特定接口+用户 | 滑动窗口计数器 | 视接口而定 |

**特殊接口限流配置：**

| 接口 | 限流规则 |
|------|---------|
| 登录 | 同IP 5分钟10次 |
| 文件上传 | 同用户 1分钟5次，单文件50MB |
| AI对话 | 同用户 1分钟20次 |
| 企业微信消息推送 | 同用户 1分钟30次 |
| 数据同步触发 | 同用户 1小时10次 |
| Token刷新 | 同用户 1分钟10次 |

#### 12.1.2 限流响应

触发限流时，返回429 HTTP状态码及标准响应体：

```json
{
  "code": 100005,
  "message": "请求频率超限，请稍后再试",
  "data": {
    "limit": 600,
    "window": 60,
    "retry_after": 45
  },
  "request_id": "req_8f3a9b2c1d4e",
  "timestamp": 1717732800
}
```

响应Header包含：

```
X-RateLimit-Limit: 600
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1717732845
Retry-After: 45
```

#### 12.1.3 PHP原生限流实现

```php
// 滑动窗口限流器（伪代码）
class RateLimiter {
    private $redis;
    
    public function allow($key, $limit, $window) {
        $now = microtime(true);
        $windowStart = $now - $window;
        
        // 移除窗口外的旧记录
        $this->redis->zRemRangeByScore($key, 0, $windowStart);
        
        // 获取当前窗口内请求数
        $current = $this->redis->zCard($key);
        
        if ($current >= $limit) {
            // 获取最早请求的过期时间
            $oldest = $this->redis->zRange($key, 0, 0, true);
            $retryAfter = ceil(reset($oldest) + $window - $now);
            return ['allowed' => false, 'retry_after' => max(1, $retryAfter)];
        }
        
        // 记录当前请求
        $this->redis->zAdd($key, $now, uniqid());
        $this->redis->expire($key, $window + 1);
        
        return ['allowed' => true, 'remaining' => $limit - $current - 1];
    }
}
```

### 12.2 防重放设计

#### 12.2.1 防重放机制

系统通过 `X-Timestamp` + `X-Nonce` + `Redis` 实现请求防重放：

1. **时间戳校验**：请求头 `X-Timestamp` 与服务器当前时间差不得超过 ±300秒（5分钟），超出范围视为过期请求
2. **随机数校验**：请求头 `X-Nonce` 为16-32位随机字符串，同一Nonce在5分钟内禁止重复使用
3. **请求签名**（可选增强）：敏感接口（数据同步、资金相关）额外要求请求签名

**防重放校验流程：**

```
1. 提取 X-Timestamp 和 X-Nonce
2. 校验 |server_time - timestamp| <= 300，否则返回 100003
3. 以 nonce 为Key查询Redis，若存在则返回 100004
4. 将 nonce 写入Redis，TTL=300秒
5. 继续后续业务处理
```

#### 12.2.2 请求签名（增强安全）

对于数据同步、批量操作等敏感接口，要求客户端生成请求签名：

**签名算法：**

1. 拼接签名原文：`{method}\n{url_path}\n{timestamp}\n{nonce}\n{sorted_request_body_json}`
2. 使用HMAC-SHA256算法，密钥为用户的API Secret（与登录密码独立）
3. 将签名值放入请求头 `X-Signature`

**PHP服务端验签示例：**

```php
class SignatureVerifier {
    public function verify($request) {
        $timestamp = $request->header('X-Timestamp');
        $nonce = $request->header('X-Nonce');
        $signature = $request->header('X-Signature');
        $apiSecret = $this->getUserApiSecret($request->user_id);
        
        $payload = implode("\n", [
            $request->method(),
            $request->path(),
            $timestamp,
            $nonce,
            $this->canonicalizeBody($request->body())
        ]);
        
        $expected = hash_hmac('sha256', $payload, $apiSecret);
        
        if (!hash_equals($expected, $signature)) {
            throw new SignatureException();
        }
    }
    
    private function canonicalizeBody($body) {
        // 对请求体JSON进行规范化：按键排序、去除空白
        $data = json_decode($body, true);
        ksort($data);
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
```

#### 12.2.3 幂等性设计

对于可能因网络重试导致重复执行的写操作接口，要求客户端提供幂等键：

| 接口 | 幂等键参数 | 说明 |
|------|-----------|------|
| 创建用户 | `idempotency_key` | 同一key 24小时内重复请求返回首次创建结果 |
| 创建工艺路线 | `idempotency_key` | 同上 |
| 触发数据同步 | `idempotency_key` | 同上 |
| 发送企微消息 | `idempotency_key` | 同上 |

**幂等性实现：**

服务端以 `idempotency_key` 为Key查询Redis/MySQL幂等记录表：
- 若存在且状态为completed，直接返回缓存的响应结果
- 若存在且状态为processing，返回409 Conflict，提示请求处理中
- 若不存在，创建幂等记录（status=processing），执行业务逻辑，完成后更新记录（status=completed，缓存响应结果）

幂等记录TTL设置为24小时，过期自动清理。

---

## 附录A：接口汇总表

| 模块 | 接口URL | Method | 权限编码 | 说明 |
|------|---------|--------|---------|------|
| 认证 | /api/v1/auth/login | POST | 公开 | 用户登录 |
| 认证 | /api/v1/auth/logout | POST | 需认证 | 用户登出 |
| 认证 | /api/v1/auth/refresh | POST | 需Refresh Token | Token刷新 |
| 文件 | /api/v1/files/upload | POST | 需认证 | 文件上传 |
| 文件 | /api/v1/files/{file_id} | GET | 需认证 | 文件下载 |
| 用户 | /api/v1/users | GET | user:view | 用户列表 |
| 用户 | /api/v1/users | POST | user:create | 创建用户 |
| 用户 | /api/v1/users/{id} | GET | user:view | 用户详情 |
| 用户 | /api/v1/users/{id} | PUT | user:update | 更新用户 |
| 用户 | /api/v1/users/{id} | DELETE | user:delete | 删除用户 |
| 用户 | /api/v1/users/{id}/reset-password | POST | user:reset_password | 重置密码 |
| 角色 | /api/v1/roles | GET | role:view | 角色列表 |
| 角色 | /api/v1/roles/{id}/permissions | GET | role:view | 角色权限树 |
| 角色 | /api/v1/roles/{id}/permissions | PUT | role:assign_permission | 分配权限 |
| 部门 | /api/v1/departments | GET | dept:view | 部门树 |
| 部门 | /api/v1/departments | POST | dept:create | 创建部门 |
| 部门 | /api/v1/departments/{id} | PUT | dept:update | 更新部门 |
| 部门 | /api/v1/departments/{id} | DELETE | dept:delete | 删除部门 |
| 产品 | /api/v1/products | GET | product:view | 产品列表 |
| 产品 | /api/v1/products | POST | product:create | 创建产品 |
| 产品 | /api/v1/products/{id} | GET | product:view | 产品详情 |
| 产品 | /api/v1/products/{id} | PUT | product:update | 更新产品 |
| 产品 | /api/v1/products/{id} | DELETE | product:delete | 删除产品 |
| 产品 | /api/v1/products/{id}/publish | POST | product:publish | 发布产品 |
| BOM | /api/v1/products/{id}/bom | GET | bom:view | 获取BOM |
| BOM | /api/v1/products/{id}/bom | PUT | bom:update | 保存BOM |
| BOM | /api/v1/products/{id}/bom/versions | GET | bom:view | BOM版本历史 |
| 零件 | /api/v1/parts | GET | part:view | 零件列表 |
| 零件 | /api/v1/parts | POST | part:create | 创建零件 |
| 零件 | /api/v1/parts/{id} | GET | part:view | 零件详情 |
| 零件 | /api/v1/parts/{id} | PUT | part:update | 更新零件 |
| 零件 | /api/v1/parts/{id} | DELETE | part:delete | 删除零件 |
| 工艺路线 | /api/v1/process-routes | GET | process_route:view | 工艺路线列表 |
| 工艺路线 | /api/v1/process-routes | POST | process_route:create | 创建工艺路线 |
| 工艺路线 | /api/v1/process-routes/{id} | GET | process_route:view | 工艺路线详情 |
| 工艺路线 | /api/v1/process-routes/{id} | PUT | process_route:update | 更新工艺路线 |
| 工艺路线 | /api/v1/process-routes/{id} | DELETE | process_route:delete | 删除工艺路线 |
| 工艺路线 | /api/v1/process-routes/{id}/submit | POST | process_route:submit | 提交审核 |
| 工艺路线 | /api/v1/process-routes/{id}/audit | POST | process_route:audit | 审核工艺路线 |
| 工艺路线 | /api/v1/process-routes/{id}/publish | POST | process_route:publish | 发布工艺路线 |
| 工艺路线 | /api/v1/process-routes/{id}/revoke | POST | process_route:revoke | 撤销发布 |
| 工序模板 | /api/v1/operation-templates | GET | operation_template:view | 工序模板库 |
| 工序模板 | /api/v1/operation-templates | POST | operation_template:create | 创建工序模板 |
| 工作中心 | /api/v1/work-centers | GET | work_center:view | 工作中心列表 |
| 工艺卡片 | /api/v1/process-cards | GET | process_card:view | 工艺卡片列表 |
| 工艺卡片 | /api/v1/process-cards/generate | POST | process_card:generate | 生成工艺卡片 |
| 工艺卡片 | /api/v1/process-cards/{id}/download | GET | process_card:view | 下载工艺卡片 |
| 企微 | /api/v1/wechat/login-qrcode | GET | 公开 | 获取登录二维码 |
| 企微 | /api/v1/wechat/login-poll | GET | 公开 | 轮询登录状态 |
| 企微 | /api/v1/wechat/sync-users | POST | wechat:sync | 触发用户同步 |
| 企微 | /api/v1/wechat/sync-tasks/{id} | GET | wechat:sync | 查询同步任务 |
| 企微 | /api/v1/wechat/send-message | POST | wechat:message | 发送应用消息 |
| 企微 | /api/v1/wechat/send-approval-notify | POST | 内部服务 | 发送审批通知 |
| AI | /api/v1/ai/chat | POST | ai:chat | AI对话 |
| AI | /api/v1/ai/sessions/{id}/messages | GET | ai:chat | 对话历史 |
| AI | /api/v1/ai/recommend-process-params | POST | ai:decision | 工艺参数推荐 |
| AI | /api/v1/ai/generate-process-route | POST | ai:decision | 智能生成工艺路线 |
| AI | /api/v1/ai/knowledge/search | POST | ai:knowledge | 知识库搜索 |
| MaxKB | /api/v1/ai/maxkb/documents | GET | ai:maxkb_manage | MaxKB文档列表 |
| MaxKB | /api/v1/ai/maxkb/documents | POST | ai:maxkb_manage | 上传MaxKB文档 |
| MaxKB | /api/v1/ai/maxkb/documents/{id} | DELETE | ai:maxkb_manage | 删除MaxKB文档 |
| MaxKB | /api/v1/ai/maxkb/agents/{id}/invoke | POST | ai:agent | 调用智能体 |
| 外部系统 | /api/v1/external/sync/erp | POST | external:sync | 触发ERP同步 |
| 外部系统 | /api/v1/external/sync/plm | POST | external:sync | 触发PLM同步 |
| 外部系统 | /api/v1/external/sync/mes | POST | external:sync | 触发MES同步 |
| 外部系统 | /api/v1/external/sync-tasks | GET | external:sync | 同步任务列表 |
| 外部系统 | /api/v1/external/sync-tasks/{id} | GET | external:sync | 同步任务详情 |
| 外部系统 | /api/v1/external/sync-tasks/{id}/cancel | POST | external:sync | 取消同步任务 |

---

*文档版本：v1.0*  
*编写日期：2024-06-07*  
*编写人：CAPP系统架构组*  
*审核状态：待评审*
