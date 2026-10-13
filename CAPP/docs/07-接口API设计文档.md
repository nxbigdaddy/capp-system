# CAPPç³»ç»æ¥å£APIè®¾è®¡ææ¡£

> **ææ¡£çæ¬**: v1.0.0
> **åå»ºæ¥æ**: 2026-06-07
> **ç³»ç»åç§°**: CAPPï¼è®¡ç®æºè¾å©å·¥èºè®¾è®¡ç³»ç»ï¼
> **å¬å¸èæ¯**: éèåç¼©æºãååå®¹å¨ç åãè®¾è®¡ãçäº§ä¼ä¸
> **ææ¯æ **: PHPåçå¼å + MySQLæ°æ®åº

---

## ç®å½

- [1. APIè®¾è®¡è§è](#1-apiè®¾è®¡è§è)
- [2. ç¨æ·è®¤è¯API](#2-ç¨æ·è®¤è¯api)
- [3. ç¨æ·ç®¡çAPI](#3-ç¨æ·ç®¡çapi)
- [4. è§è²æéAPI](#4-è§è²æéapi)
- [5. é¨é¨ç®¡çAPI](#5-é¨é¨ç®¡çapi)
- [6. äº§åç®¡çAPI](#6-äº§åç®¡çapi)
- [7. ç©æç®¡çAPI](#7-ç©æç®¡çapi)
- [8. å·¥èºè®¾è®¡API](#8-å·¥èºè®¾è®¡api)
- [9. èµæºç®¡çAPI](#9-èµæºç®¡çapi)
- [10. å·¥æ¶å®é¢API](#10-å·¥æ¶å®é¢api)
- [11. æ¨¡æ¿ç®¡çAPI](#11-æ¨¡æ¿ç®¡çapi)
- [12. å®¡æ¹æµç¨API](#12-å®¡æ¹æµç¨api)
- [13. ææ¡£ç®¡çAPI](#13-ææ¡£ç®¡çapi)
- [14. AIåè½API](#14-aiåè½api)
- [15. MaxKBä¸ä¸çAPI](#15-maxkbä¸ä¸çapi)
- [16. ä¼ä¸å¾®ä¿¡API](#16-ä¼ä¸å¾®ä¿¡api)
- [17. å¤é¨ç³»ç»éæAPI](#17-å¤é¨ç³»ç»éæapi)
- [18. ç»è®¡åæAPI](#18-ç»è®¡åæapi)
- [19. ç³»ç»è®¾ç½®API](#19-ç³»ç»è®¾ç½®api)
- [20. Webhookåè°API](#20-webhookåè°api)
- [21. APIå®å¨è®¾è®¡](#21-apiå®å¨è®¾è®¡)
- [22. APIçæ¬ç®¡ç](#22-apiçæ¬ç®¡ç)
- [23. PHPå®ç°ç¤ºä¾](#23-phpå®ç°ç¤ºä¾)

---

## 1. APIè®¾è®¡è§è

### 1.1 æ¥å£åè®®

#### åºç¡URL
```
çäº§ç¯å¢: https://capp.example.com/api/v1
å¼åç¯å¢: http://localhost:8080/api/v1
```

#### RESTfulé£æ ¼
- èµæºä½¿ç¨åè¯å¤æ°å½¢å¼ï¼`/api/users`ã`/api/products`
- åµå¥èµæºä½¿ç¨å±çº§ç»æï¼`/api/products/{id}/bom`
- å¨ä½ä½¿ç¨HTTPæ¹æ³è¡¨è¾¾ï¼é¿åå¨URLä¸­ä½¿ç¨å¨è¯

#### HTTPæ¹æ³ä½¿ç¨è§è

| HTTPæ¹æ³ | ç¨é | ç¤ºä¾ |
|---------|------|------|
| GET | è·åèµæºï¼åæ¡æåè¡¨ï¼ | `GET /api/users` |
| POST | åå»ºèµæºææ§è¡æä½ | `POST /api/users` |
| PUT | å®æ´æ´æ°èµæº | `PUT /api/users/{id}` |
| PATCH | é¨åæ´æ°èµæº | `PATCH /api/users/{id}` |
| DELETE | å é¤èµæº | `DELETE /api/users/{id}` |

#### è¯·æ±/ååºæ ¼å¼
- Content-Type: `application/json; charset=utf-8`
- è¯·æ±ä½ä½¿ç¨JSONæ ¼å¼
- ååºä½ä½¿ç¨JSONæ ¼å¼
- å­ç¬¦ç¼ç ç»ä¸ä½¿ç¨UTF-8

#### è¯·æ±å¤´è§è

| è¯·æ±å¤´ | å¿å¡« | è¯´æ |
|-------|------|------|
| Authorization | æ¯ï¼å¬å¼æ¥å£é¤å¤ï¼ | Bearer {token} |
| Content-Type | æ¯ï¼POST/PUTï¼ | application/json |
| X-Request-ID | å¦ | è¯·æ±è¿½è¸ªIDï¼UUIDï¼ |
| X-Client-Version | å¦ | å®¢æ·ç«¯çæ¬å· |
| Accept-Language | å¦ | zh-CN / en-US |

### 1.2 ç»ä¸ååºæ ¼å¼

#### æåååº
```json
{
  "code": 200,
  "message": "success",
  "data": {},
  "timestamp": 1717718400
}
```

#### åé¡µåè¡¨ååº
```json
{
  "code": 200,
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
  "timestamp": 1717718400
}
```

#### éè¯¯ååº
```json
{
  "code": 40001,
  "message": "åæ°éªè¯å¤±è´¥",
  "data": {
    "errors": {
      "username": ["ç¨æ·åä¸è½ä¸ºç©º"],
      "password": ["å¯ç é¿åº¦ä¸è½å°äº6ä½"]
    }
  },
  "timestamp": 1717718400
}
```

#### ååºå­æ®µè¯´æ

| å­æ®µ | ç±»å | è¯´æ |
|------|------|------|
| code | integer | ä¸å¡ç¶æç ï¼200è¡¨ç¤ºæå |
| message | string | æç¤ºä¿¡æ¯ |
| data | object/array/null | ååºæ°æ® |
| timestamp | integer | Unixæ¶é´æ³ï¼ç§ï¼ |

### 1.3 éè¯¯ç è§è

#### éè¯¯ç åç±»

| éè¯¯ç èå´ | åç±» | è¯´æ |
|-----------|------|------|
| 200 | æå | è¯·æ±æå |
| 40000-40999 | å®¢æ·ç«¯éè¯¯ | è¯·æ±åæ°éè¯¯ãéªè¯å¤±è´¥ç­ |
| 40100-40199 | è®¤è¯éè¯¯ | Tokenå¤±æãç»å½å¤±è´¥ç­ |
| 40300-40399 | æééè¯¯ | æ æéè®¿é®ãæéä¸è¶³ç­ |
| 40400-40499 | èµæºéè¯¯ | èµæºä¸å­å¨ç­ |
| 40900-40999 | å²çªéè¯¯ | æ°æ®å²çªãéå¤æäº¤ç­ |
| 42900-42999 | éæµéè¯¯ | è¯·æ±é¢çè¶é |
| 50000-50999 | æå¡ç«¯éè¯¯ | ç³»ç»åé¨éè¯¯ |
| 50200-50299 | ç¬¬ä¸æ¹æå¡éè¯¯ | å¤é¨ç³»ç»è°ç¨å¤±è´¥ |

#### è¯¦ç»éè¯¯ç å®ä¹

| éè¯¯ç  | è¯´æ | HTTPç¶æç  |
|-------|------|-----------|
| 200 | æå | 200 |
| 40001 | åæ°éªè¯å¤±è´¥ | 400 |
| 40002 | åæ°æ ¼å¼éè¯¯ | 400 |
| 40003 | ç¼ºå°å¿å¡«åæ° | 400 |
| 40101 | æªç»å½æTokenæ æ | 401 |
| 40102 | Tokenå·²è¿æ | 401 |
| 40103 | è´¦å·æå¯ç éè¯¯ | 401 |
| 40104 | è´¦å·å·²è¢«ç¦ç¨ | 401 |
| 40105 | ä¼ä¸å¾®ä¿¡ç»å½å¤±è´¥ | 401 |
| 40301 | æ æéè®¿é® | 403 |
| 40302 | æéä¸è¶³ | 403 |
| 40401 | èµæºä¸å­å¨ | 404 |
| 40402 | æ¥å£ä¸å­å¨ | 404 |
| 40901 | æ°æ®å·²å­å¨ | 409 |
| 40902 | æ°æ®è¢«å¼ç¨æ æ³å é¤ | 409 |
| 42901 | è¯·æ±è¿äºé¢ç¹ | 429 |
| 50001 | æå¡å¨åé¨éè¯¯ | 500 |
| 50002 | æ°æ®åºæä½å¤±è´¥ | 500 |
| 50201 | ç¬¬ä¸æ¹æå¡è°ç¨å¤±è´¥ | 502 |
| 50202 | ç¬¬ä¸æ¹æå¡ååºè¶æ¶ | 504 |

### 1.4 è®¤è¯ä¸ææ

#### JWT Tokenè®¤è¯

**Tokenç»æ**
```json
{
  "header": {
    "alg": "HS256",
    "typ": "JWT"
  },
  "payload": {
    "sub": "user_id",
    "username": "zhangsan",
    "role_ids": [1, 2],
    "dept_id": 10,
    "iat": 1717718400,
    "exp": 1717722000,
    "jti": "unique_token_id"
  }
}
```

**Tokenéç½®**
| éç½®é¡¹ | å¼ | è¯´æ |
|-------|-----|------|
| Access Tokenæææ | 2å°æ¶ | ç­æä»¤ç |
| Refresh Tokenæææ | 7å¤© | é¿æå·æ°ä»¤ç |
| ç­¾åç®æ³ | HS256 | HMAC-SHA256 |
| Tokenå­å¨ | Redis + MySQL | æ¯æä¸»å¨å¤±æ |

**è¯·æ±ç¤ºä¾**
```http
GET /api/v1/users
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### Tokenå·æ°æºå¶

```
1. å®¢æ·ç«¯æºå¸¦Access Tokenè¯·æ±æ¥å£
2. Access Tokenè¿æè¿å40102
3. å®¢æ·ç«¯ä½¿ç¨Refresh Tokenè°ç¨å·æ°æ¥å£
4. è·åæ°çAccess TokenåRefresh Token
5. ä½¿ç¨æ°Tokenç»§ç»­è¯·æ±
```

#### æéæ ¡éª

**RBACæ¨¡å**
```
ç¨æ· -> è§è² -> æé -> æ¥å£/èµæº
```

**æéæ è¯æ ¼å¼**
```
æ¨¡å:æä½:èµæº
ç¤ºä¾:
  user:list           # ç¨æ·åè¡¨
  user:create         # åå»ºç¨æ·
  process:edit        # ç¼è¾å·¥èº
  card:approve        # å®¡æ¹å·¥èºå¡ç
```

**æéæ ¡éªæµç¨**
```
1. è§£æJWT Tokenè·åç¨æ·ä¿¡æ¯
2. æ¥è¯¢ç¨æ·è§è²åæéæ è¯
3. å¹éå½åæ¥å£æéæé
4. æ æéè¿å40301
```

### 1.5 åé¡µè§è

**è¯·æ±åæ°**

| åæ° | ç±»å | å¿å¡« | é»è®¤å¼ | è¯´æ |
|------|------|------|--------|------|
| page | integer | å¦ | 1 | é¡µç ï¼ä»1å¼å§ |
| page_size | integer | å¦ | 20 | æ¯é¡µæ°éï¼æå¤§100 |

**è¯·æ±ç¤ºä¾**
```http
GET /api/v1/users?page=2&page_size=50
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {"id": 51, "username": "user51", "real_name": "ç¨æ·51"}
    ],
    "pagination": {
      "page": 2,
      "page_size": 50,
      "total": 150,
      "total_pages": 3
    }
  },
  "timestamp": 1717718400
}
```

### 1.6 æåºè§è

**è¯·æ±åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| sort_by | string | å¦ | æåºå­æ®µ |
| sort_order | string | å¦ | asc/descï¼é»è®¤desc |

**è¯·æ±ç¤ºä¾**
```http
GET /api/v1/products?sort_by=created_at&sort_order=desc
GET /api/v1/products?sort_by=name&sort_order=asc
```

**å¤å­æ®µæåº**
```http
GET /api/v1/products?sort_by=status:asc,created_at:desc
```

### 1.7 æ¥ææ¶é´æ ¼å¼

| åºæ¯ | æ ¼å¼ | ç¤ºä¾ |
|------|------|------|
| æ¥æ | YYYY-MM-DD | 2026-06-07 |
| æ¥ææ¶é´ | YYYY-MM-DD HH:mm:ss | 2026-06-07 14:30:00 |
| ISO8601 | YYYY-MM-DDTHH:mm:ssZ | 2026-06-07T14:30:00+08:00 |
| æ¶é´æ³ï¼ååºï¼ | Unix Timestampï¼ç§ï¼ | 1717718400 |
| æ¶é´æ³ï¼è¯·æ±ï¼ | Unix Timestampï¼ç§ï¼ | 1717718400 |

**æ¶åºè¯´æ**
- ç³»ç»é»è®¤æ¶åºï¼Asia/Shanghai (UTC+8)
- æ°æ®åºå­å¨ï¼UTCæ¶é´
- APIè¾åºï¼è½¬æ¢ä¸ºåäº¬æ¶é´

---

## 2. ç¨æ·è®¤è¯API

### 2.1 è´¦å·å¯ç ç»å½

**æ¥å£è¯´æ**: ç¨æ·éè¿è´¦å·å¯ç ç»å½ç³»ç»

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/auth/login`

**æéè¦æ±**: å¬å¼æ¥å£

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| username | string | æ¯ | ç¨æ·å/å·¥å·/ææºå· |
| password | string | æ¯ | å¯ç ï¼RSAå å¯åä¼ è¾ï¼ |
| captcha_id | string | å¦ | éªè¯ç IDï¼è¿ç»­å¤±è´¥3æ¬¡åå¿å¡«ï¼ |
| captcha_code | string | å¦ | éªè¯ç  |

**è¯·æ±ç¤ºä¾**
```json
{
  "username": "zhangsan",
  "password": "encrypted_password_string",
  "captcha_id": "capt_abc123",
  "captcha_code": "A3f7"
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "ç»å½æå",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200,
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "username": "zhangsan",
      "real_name": "å¼ ä¸",
      "avatar": "https://example.com/avatar.jpg",
      "dept_name": "å·¥èºé¨",
      "roles": ["å·¥èºå·¥ç¨å¸"]
    }
  },
  "timestamp": 1717718400
}
```

### 2.2 ä¼ä¸å¾®ä¿¡æ«ç ç»å½

**æ¥å£è¯´æ**: éè¿ä¼ä¸å¾®ä¿¡ææç ç»å½

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/auth/qrcode-login`

**æéè¦æ±**: å¬å¼æ¥å£

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| auth_code | string | æ¯ | ä¼ä¸å¾®ä¿¡ææç  |
| state | string | æ¯ | é²CSRFç¶æç  |

**è¯·æ±ç¤ºä¾**
```json
{
  "auth_code": "wecom_auth_code_xxx",
  "state": "random_state_string"
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "ç»å½æå",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIs...",
    "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 7200,
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "username": "zhangsan",
      "real_name": "å¼ ä¸",
      "wecom_userid": "ZhangSan",
      "avatar": "https://example.com/avatar.jpg"
    }
  },
  "timestamp": 1717718400
}
```

### 2.3 å·æ°Token

**æ¥å£è¯´æ**: ä½¿ç¨Refresh Tokenè·åæ°çAccess Token

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/auth/refresh-token`

**æéè¦æ±**: å¬å¼æ¥å£

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| refresh_token | string | æ¯ | å·æ°ä»¤ç |

**ååºç¤ºä¾**
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

### 2.4 éåºç»å½

**æ¥å£è¯´æ**: æ³¨éå½åç»å½ç¶æï¼ä½¿Tokenå¤±æ

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/auth/logout`

**æéè¦æ±**: å·²ç»å½ç¨æ·

**è¯·æ±åæ°**: æ 

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "éåºæå",
  "data": null,
  "timestamp": 1717718400
}
```

### 2.5 è·åå½åç¨æ·ä¿¡æ¯

**æ¥å£è¯´æ**: è·åå½åç»å½ç¨æ·çè¯¦ç»ä¿¡æ¯

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/auth/user-info`

**æéè¦æ±**: å·²ç»å½ç¨æ·

**è¯·æ±åæ°**: æ 

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "username": "zhangsan",
    "real_name": "å¼ ä¸",
    "email": "zhangsan@example.com",
    "phone": "13800138000",
    "avatar": "https://example.com/avatar.jpg",
    "gender": 1,
    "dept_id": 10,
    "dept_name": "å·¥èºé¨",
    "position": "å·¥èºå·¥ç¨å¸",
    "roles": [
      {"id": 1, "name": "å·¥èºå·¥ç¨å¸", "code": "process_engineer"}
    ],
    "permissions": [
      "process:list", "process:create", "process:edit",
      "card:list", "card:create", "card:edit"
    ],
    "created_at": "2025-01-01 09:00:00",
    "last_login_at": "2026-06-07 08:30:00"
  },
  "timestamp": 1717718400
}
```

---

## 3. ç¨æ·ç®¡çAPI

### 3.1 ç¨æ·åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/users`

**æéè¦æ±**: `user:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å¦ | é¡µç ï¼é»è®¤1 |
| page_size | integer | å¦ | æ¯é¡µæ°éï¼é»è®¤20 |
| keyword | string | å¦ | æç´¢å³é®è¯ï¼ç¨æ·å/å§å/ææºå·ï¼ |
| dept_id | integer | å¦ | é¨é¨ID |
| status | integer | å¦ | ç¶æï¼1å¯ç¨ 0ç¦ç¨ |
| sort_by | string | å¦ | æåºå­æ®µ |
| sort_order | string | å¦ | asc/desc |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "username": "zhangsan",
        "real_name": "å¼ ä¸",
        "email": "zhangsan@example.com",
        "phone": "13800138000",
        "dept_id": 10,
        "dept_name": "å·¥èºé¨",
        "position": "å·¥èºå·¥ç¨å¸",
        "status": 1,
        "created_at": "2025-01-01 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 50,
      "total_pages": 3
    }
  },
  "timestamp": 1717718400
}
```

### 3.2 åå»ºç¨æ·

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/users`

**æéè¦æ±**: `user:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| username | string | æ¯ | ç¨æ·åï¼å·¥å·ï¼ï¼4-32å­ç¬¦ |
| password | string | æ¯ | å¯ç ï¼6-32å­ç¬¦ |
| real_name | string | æ¯ | çå®å§å |
| email | string | å¦ | é®ç®± |
| phone | string | å¦ | ææºå· |
| gender | integer | å¦ | æ§å«ï¼1ç· 2å¥³ 0æªç¥ |
| dept_id | integer | æ¯ | é¨é¨ID |
| position | string | å¦ | èä½ |
| role_ids | array | å¦ | è§è²IDæ°ç» |
| status | integer | å¦ | ç¶æï¼1å¯ç¨ 0ç¦ç¨ï¼é»è®¤1 |

**è¯·æ±ç¤ºä¾**
```json
{
  "username": "lisi",
  "password": "123456",
  "real_name": "æå",
  "email": "lisi@example.com",
  "phone": "13900139000",
  "gender": 1,
  "dept_id": 10,
  "position": "å·¥èºå·¥ç¨å¸",
  "role_ids": [1, 2],
  "status": 1
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 2,
    "username": "lisi",
    "real_name": "æå",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 3.3 ç¨æ·è¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/users/{id}`

**æéè¦æ±**: `user:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç¨æ·ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "username": "zhangsan",
    "real_name": "å¼ ä¸",
    "email": "zhangsan@example.com",
    "phone": "13800138000",
    "gender": 1,
    "avatar": "https://example.com/avatar.jpg",
    "dept_id": 10,
    "dept_name": "å·¥èºé¨",
    "position": "å·¥èºå·¥ç¨å¸",
    "status": 1,
    "roles": [
      {"id": 1, "name": "å·¥èºå·¥ç¨å¸", "code": "process_engineer"}
    ],
    "created_at": "2025-01-01 09:00:00",
    "updated_at": "2026-06-01 14:00:00"
  },
  "timestamp": 1717718400
}
```

### 3.4 æ´æ°ç¨æ·

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/users/{id}`

**æéè¦æ±**: `user:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç¨æ·ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| real_name | string | å¦ | çå®å§å |
| email | string | å¦ | é®ç®± |
| phone | string | å¦ | ææºå· |
| gender | integer | å¦ | æ§å« |
| dept_id | integer | å¦ | é¨é¨ID |
| position | string | å¦ | èä½ |
| role_ids | array | å¦ | è§è²IDæ°ç» |
| avatar | string | å¦ | å¤´åURL |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "username": "zhangsan",
    "real_name": "å¼ ä¸ï¼æ´æ°ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 3.5 å é¤ç¨æ·

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/users/{id}`

**æéè¦æ±**: `user:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç¨æ·ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

### 3.6 å¯ç¨/ç¦ç¨ç¨æ·

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/users/{id}/status`

**æéè¦æ±**: `user:status`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç¨æ·ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| status | integer | æ¯ | ç¶æï¼1å¯ç¨ 0ç¦ç¨ |
| reason | string | å¦ | æä½åå  |

**è¯·æ±ç¤ºä¾**
```json
{
  "status": 0,
  "reason": "åå·¥ç¦»è"
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "ç¶ææ´æ°æå",
  "data": {
    "id": 1,
    "status": 0
  },
  "timestamp": 1717718400
}
```

---

## 4. è§è²æéAPI

### 4.1 è§è²åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/roles`

**æéè¦æ±**: `role:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å¦ | é¡µç  |
| page_size | integer | å¦ | æ¯é¡µæ°é |
| keyword | string | å¦ | æç´¢å³é®è¯ |
| status | integer | å¦ | ç¶æç­é |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "name": "å·¥èºå·¥ç¨å¸",
        "code": "process_engineer",
        "description": "è´è´£å·¥èºè·¯çº¿è®¾è®¡åå·¥èºå¡çç¼å¶",
        "status": 1,
        "user_count": 15,
        "created_at": "2025-01-01 09:00:00"
      },
      {
        "id": 2,
        "name": "å·¥èºå®¡æ ¸å",
        "code": "process_reviewer",
        "description": "è´è´£å·¥èºæä»¶å®¡æ ¸",
        "status": 1,
        "user_count": 5,
        "created_at": "2025-01-01 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 8,
      "total_pages": 1
    }
  },
  "timestamp": 1717718400
}
```

### 4.2 åå»ºè§è²

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/roles`

**æéè¦æ±**: `role:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | æ¯ | è§è²åç§° |
| code | string | æ¯ | è§è²ç¼ç ï¼å¯ä¸ï¼ |
| description | string | å¦ | è§è²æè¿° |
| status | integer | å¦ | ç¶æï¼1å¯ç¨ 0ç¦ç¨ |
| permission_ids | array | å¦ | æéIDæ°ç» |

**è¯·æ±ç¤ºä¾**
```json
{
  "name": "å·¥èºä¸»ç®¡",
  "code": "process_manager",
  "description": "å·¥èºé¨é¨è´è´£äººï¼æ¥æå¨é¨å·¥èºç®¡çæé",
  "status": 1,
  "permission_ids": [1, 2, 3, 4, 5]
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 3,
    "name": "å·¥èºä¸»ç®¡",
    "code": "process_manager",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 4.3 æ´æ°è§è²

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/roles/{id}`

**æéè¦æ±**: `role:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | è§è²ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | å¦ | è§è²åç§° |
| code | string | å¦ | è§è²ç¼ç  |
| description | string | å¦ | è§è²æè¿° |
| status | integer | å¦ | ç¶æ |
| permission_ids | array | å¦ | æéIDæ°ç» |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "å·¥èºå·¥ç¨å¸ï¼é«çº§ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 4.4 å é¤è§è²

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/roles/{id}`

**æéè¦æ±**: `role:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | è§è²ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

### 4.5 æéåè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/permissions`

**æéè¦æ±**: `permission:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| module | string | å¦ | æ¨¡åç­é |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "name": "æ¥çç¨æ·åè¡¨",
      "code": "user:list",
      "module": "ç¨æ·ç®¡ç",
      "type": "menu",
      "parent_id": 0,
      "sort": 1
    },
    {
      "id": 2,
      "name": "åå»ºç¨æ·",
      "code": "user:create",
      "module": "ç¨æ·ç®¡ç",
      "type": "button",
      "parent_id": 1,
      "sort": 2
    }
  ],
  "timestamp": 1717718400
}
```

### 4.6 åéæé

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/roles/{id}/permissions`

**æéè¦æ±**: `role:assign`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | è§è²ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| permission_ids | array | æ¯ | æéIDæ°ç» |

**è¯·æ±ç¤ºä¾**
```json
{
  "permission_ids": [1, 2, 3, 4, 5, 6, 7]
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æéåéæå",
  "data": {
    "role_id": 1,
    "permission_count": 7
  },
  "timestamp": 1717718400
}
```

---

## 5. é¨é¨ç®¡çAPI

### 5.1 é¨é¨æ 

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/departments`

**æéè¦æ±**: `department:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| status | integer | å¦ | ç¶æç­é |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "name": "å¬å¸æ»é¨",
      "code": "HQ",
      "parent_id": 0,
      "sort": 1,
      "status": 1,
      "manager_id": 1,
      "manager_name": "æ»ç»ç",
      "children": [
        {
          "id": 10,
          "name": "å·¥èºé¨",
          "code": "PROCESS",
          "parent_id": 1,
          "sort": 1,
          "status": 1,
          "manager_id": 2,
          "manager_name": "å·¥èºä¸»ç®¡",
          "children": [
            {
              "id": 101,
              "name": "éèåç¼©æºå·¥èºç»",
              "code": "DIAPHRAGM",
              "parent_id": 10,
              "sort": 1,
              "status": 1
            },
            {
              "id": 102,
              "name": "ååå®¹å¨å·¥èºç»",
              "code": "PRESSURE_VESSEL",
              "parent_id": 10,
              "sort": 2,
              "status": 1
            }
          ]
        },
        {
          "id": 20,
          "name": "è®¾è®¡é¨",
          "code": "DESIGN",
          "parent_id": 1,
          "sort": 2,
          "status": 1
        },
        {
          "id": 30,
          "name": "çäº§é¨",
          "code": "PRODUCTION",
          "parent_id": 1,
          "sort": 3,
          "status": 1
        }
      ]
    }
  ],
  "timestamp": 1717718400
}
```

### 5.2 åå»ºé¨é¨

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/departments`

**æéè¦æ±**: `department:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | æ¯ | é¨é¨åç§° |
| code | string | æ¯ | é¨é¨ç¼ç ï¼å¯ä¸ï¼ |
| parent_id | integer | å¦ | ä¸çº§é¨é¨IDï¼é»è®¤0 |
| sort | integer | å¦ | æåºï¼é»è®¤0 |
| manager_id | integer | å¦ | é¨é¨è´è´£äººID |
| phone | string | å¦ | é¨é¨çµè¯ |
| status | integer | å¦ | ç¶æï¼1å¯ç¨ 0ç¦ç¨ |

**è¯·æ±ç¤ºä¾**
```json
{
  "name": "è´¨éç®¡çé¨",
  "code": "QUALITY",
  "parent_id": 1,
  "sort": 4,
  "manager_id": 5,
  "phone": "010-12345678",
  "status": 1
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 40,
    "name": "è´¨éç®¡çé¨",
    "code": "QUALITY",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 5.3 æ´æ°é¨é¨

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/departments/{id}`

**æéè¦æ±**: `department:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | é¨é¨ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | å¦ | é¨é¨åç§° |
| code | string | å¦ | é¨é¨ç¼ç  |
| parent_id | integer | å¦ | ä¸çº§é¨é¨ID |
| sort | integer | å¦ | æåº |
| manager_id | integer | å¦ | é¨é¨è´è´£äººID |
| phone | string | å¦ | é¨é¨çµè¯ |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 40,
    "name": "è´¨éç®¡çé¨ï¼æ´æ°ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 5.4 å é¤é¨é¨

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/departments/{id}`

**æéè¦æ±**: `department:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | é¨é¨ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

---

## 6. äº§åç®¡çAPI

### 6.1 äº§ååè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/products`

**æéè¦æ±**: `product:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å¦ | é¡µç  |
| page_size | integer | å¦ | æ¯é¡µæ°é |
| keyword | string | å¦ | æç´¢å³é®è¯ |
| category | string | å¦ | äº§ååç±»ï¼diaphragm_compressor/pressure_vessel |
| status | integer | å¦ | ç¶æ |
| sort_by | string | å¦ | æåºå­æ®µ |
| sort_order | string | å¦ | æåºæ¹å¼ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "code": "DZ-100",
        "name": "DZåéèåç¼©æº",
        "category": "diaphragm_compressor",
        "category_name": "éèåç¼©æº",
        "model": "DZ-100A",
        "status": 1,
        "version_count": 3,
        "current_version": "V3.0",
        "created_at": "2025-06-01 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 25,
      "total_pages": 2
    }
  },
  "timestamp": 1717718400
}
```

### 6.2 åå»ºäº§å

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/products`

**æéè¦æ±**: `product:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| code | string | æ¯ | äº§åç¼å· |
| name | string | æ¯ | äº§ååç§° |
| category | string | æ¯ | äº§ååç±» |
| model | string | å¦ | è§æ ¼åå· |
| description | string | å¦ | äº§åæè¿° |
| drawings | array | å¦ | å¾çº¸æä»¶URLæ°ç» |
| status | integer | å¦ | ç¶æï¼1è®¾è®¡ä¸­ 2å·²åå¸ 3å·²åäº§ |

**è¯·æ±ç¤ºä¾**
```json
{
  "code": "PV-200",
  "name": "é«åå¨æ°ç½",
  "category": "pressure_vessel",
  "model": "PV-200A",
  "description": "å®¹ç§¯200Lï¼è®¾è®¡åå10MPa",
  "status": 1
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 2,
    "code": "PV-200",
    "name": "é«åå¨æ°ç½",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 6.3 äº§åè¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}`

**æéè¦æ±**: `product:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "code": "DZ-100",
    "name": "DZåéèåç¼©æº",
    "category": "diaphragm_compressor",
    "category_name": "éèåç¼©æº",
    "model": "DZ-100A",
    "description": "ææ°é100mÂ³/hï¼ææ°åå32MPa",
    "drawings": [
      {"name": "æ»è£å¾.dwg", "url": "/files/drawings/dz100-assembly.dwg"}
    ],
    "status": 2,
    "status_name": "å·²åå¸",
    "version_count": 3,
    "current_version": "V3.0",
    "created_by": "zhangsan",
    "created_at": "2025-06-01 09:00:00",
    "updated_at": "2026-05-01 14:00:00"
  },
  "timestamp": 1717718400
}
```

### 6.4 æ´æ°äº§å

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}`

**æéè¦æ±**: `product:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | å¦ | äº§ååç§° |
| model | string | å¦ | è§æ ¼åå· |
| description | string | å¦ | äº§åæè¿° |
| drawings | array | å¦ | å¾çº¸æä»¶URLæ°ç» |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "DZåéèåç¼©æºï¼æ¹è¿åï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 6.5 å é¤äº§å

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}`

**æéè¦æ±**: `product:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

### 6.6 è·åäº§åBOM

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}/bom`

**æéè¦æ±**: `product:bom:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| version | string | å¦ | BOMçæ¬å· |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "product_id": 1,
    "product_name": "DZåéèåç¼©æº",
    "version": "V3.0",
    "bom_tree": [
      {
        "id": 1,
        "level": 1,
        "material_code": "DZ100-01",
        "material_name": "æºèº«ç»ä»¶",
        "quantity": 1,
        "unit": "å°",
        "material_type": "assembly",
        "children": [
          {
            "id": 2,
            "level": 2,
            "material_code": "DZ100-01-01",
            "material_name": "æºèº«é¸ä»¶",
            "quantity": 1,
            "unit": "ä»¶",
            "material_type": "part",
            "material_spec": "HT250",
            "children": []
          }
        ]
      }
    ]
  },
  "timestamp": 1717718400
}
```

### 6.7 æ·»å BOMèç¹

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}/bom`

**æéè¦æ±**: `product:bom:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| parent_id | integer | å¦ | ç¶èç¹IDï¼0è¡¨ç¤ºæ ¹èç¹ |
| material_id | integer | æ¯ | ç©æID |
| quantity | decimal | æ¯ | æ°é |
| unit | string | æ¯ | åä½ |
| remark | string | å¦ | å¤æ³¨ |

**è¯·æ±ç¤ºä¾**
```json
{
  "parent_id": 1,
  "material_id": 100,
  "quantity": 2,
  "unit": "ä»¶",
  "remark": "å·¦å³åä¸"
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ·»å æå",
  "data": {
    "id": 3,
    "parent_id": 1,
    "material_name": "ä¾§çæ¿",
    "quantity": 2,
    "unit": "ä»¶"
  },
  "timestamp": 1717718400
}
```

### 6.8 æ´æ°BOMèç¹

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}/bom/{bomId}`

**æéè¦æ±**: `product:bom:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |
| bomId | integer | æ¯ | BOMèç¹ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| quantity | decimal | å¦ | æ°é |
| unit | string | å¦ | åä½ |
| remark | string | å¦ | å¤æ³¨ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 3,
    "quantity": 4,
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 6.9 å é¤BOMèç¹

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}/bom/{bomId}`

**æéè¦æ±**: `product:bom:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |
| bomId | integer | æ¯ | BOMèç¹ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

### 6.10 äº§åçæ¬åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/products/{id}/versions`

**æéè¦æ±**: `product:version:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | äº§åID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "version": "V1.0",
      "status": "archived",
      "status_name": "å·²å½æ¡£",
      "change_description": "åå§çæ¬",
      "created_by": "zhangsan",
      "created_at": "2025-06-01 09:00:00"
    },
    {
      "id": 2,
      "version": "V2.0",
      "status": "archived",
      "status_name": "å·²å½æ¡£",
      "change_description": "ä¼åæºèº«ç»æ",
      "created_by": "lisi",
      "created_at": "2025-12-01 09:00:00"
    },
    {
      "id": 3,
      "version": "V3.0",
      "status": "current",
      "status_name": "å½åçæ¬",
      "change_description": "æ¹è¿å¯å°ç»æ",
      "created_by": "zhangsan",
      "created_at": "2026-05-01 09:00:00"
    }
  ],
  "timestamp": 1717718400
}
```

---

## 7. ç©æç®¡çAPI

### 7.1 ç©æåè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/materials`

**æéè¦æ±**: `material:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å¦ | é¡µç  |
| page_size | integer | å¦ | æ¯é¡µæ°é |
| keyword | string | å¦ | æç´¢å³é®è¯ |
| category | string | å¦ | ç©æåç±» |
| material_type | string | å¦ | ç©æç±»åï¼raw/part/assembly/standard |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "code": "MAT-001",
        "name": "45#é¢åæ£",
        "category": "åææ",
        "material_type": "raw",
        "material_type_name": "åææ",
        "specification": "Î¦80Ã200mm",
        "unit": "æ ¹",
        "material_grade": "45#",
        "status": 1,
        "created_at": "2025-01-01 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 500,
      "total_pages": 25
    }
  },
  "timestamp": 1717718400
}
```

### 7.2 åå»ºç©æ

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/materials`

**æéè¦æ±**: `material:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| code | string | æ¯ | ç©æç¼ç  |
| name | string | æ¯ | ç©æåç§° |
| category | string | æ¯ | ç©æåç±» |
| material_type | string | æ¯ | ç©æç±»å |
| specification | string | å¦ | è§æ ¼åå· |
| unit | string | æ¯ | è®¡éåä½ |
| material_grade | string | å¦ | æè´¨çå· |
| standard | string | å¦ | æ§è¡æ å |
| drawing_no | string | å¦ | å¾å· |
| remark | string | å¦ | å¤æ³¨ |
| status | integer | å¦ | ç¶æï¼1å¯ç¨ 0ç¦ç¨ |

**è¯·æ±ç¤ºä¾**
```json
{
  "code": "MAT-002",
  "name": "éèç",
  "category": "å³é®é¶ä»¶",
  "material_type": "part",
  "specification": "Î¦200Ã0.5mm",
  "unit": "ç",
  "material_grade": "0Cr18Ni9",
  "standard": "GB/T 3280",
  "drawing_no": "DZ100-02-01",
  "status": 1
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 2,
    "code": "MAT-002",
    "name": "éèç",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 7.3 ç©æè¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/materials/{id}`

**æéè¦æ±**: `material:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç©æID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "code": "MAT-001",
    "name": "45#é¢åæ£",
    "category": "åææ",
    "material_type": "raw",
    "material_type_name": "åææ",
    "specification": "Î¦80Ã200mm",
    "unit": "æ ¹",
    "material_grade": "45#",
    "standard": "GB/T 699",
    "drawing_no": null,
    "remark": "è°è´¨å¤çHB220-250",
    "status": 1,
    "created_at": "2025-01-01 09:00:00",
    "updated_at": "2026-01-01 09:00:00"
  },
  "timestamp": 1717718400
}
```

### 7.4 æ´æ°ç©æ

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/materials/{id}`

**æéè¦æ±**: `material:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç©æID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | å¦ | ç©æåç§° |
| category | string | å¦ | ç©æåç±» |
| specification | string | å¦ | è§æ ¼åå· |
| unit | string | å¦ | è®¡éåä½ |
| material_grade | string | å¦ | æè´¨çå· |
| standard | string | å¦ | æ§è¡æ å |
| drawing_no | string | å¦ | å¾å· |
| remark | string | å¦ | å¤æ³¨ |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "45#é¢åæ£ï¼æ¹è¿ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 7.5 å é¤ç©æ

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/materials/{id}`

**æéè¦æ±**: `material:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | ç©æID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

### 7.6 æç´¢ç©æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/materials/search`

**æéè¦æ±**: `material:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| keyword | string | æ¯ | æç´¢å³é®è¯ |
| limit | integer | å¦ | è¿åæ°éï¼é»è®¤10 |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "code": "MAT-001",
      "name": "45#é¢åæ£",
      "specification": "Î¦80Ã200mm",
      "unit": "æ ¹",
      "material_type_name": "åææ"
    }
  ],
  "timestamp": 1717718400
}
```

---

## 8. å·¥èºè®¾è®¡API

### 8.1 å·¥èºè·¯çº¿

#### 8.1.1 å·¥èºè·¯çº¿åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes`

**æéè¦æ±**: `process:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å¦ | é¡µç  |
| page_size | integer | å¦ | æ¯é¡µæ°é |
| keyword | string | å¦ | æç´¢å³é®è¯ |
| product_id | integer | å¦ | äº§åID |
| material_id | integer | å¦ | ç©æID |
| status | integer | å¦ | ç¶æï¼0èç¨¿ 1å·²åå¸ 2å·²å½æ¡£ |
| type | string | å¦ | ç±»åï¼machining/assembly/heat_treatment/surface |
| sort_by | string | å¦ | æåºå­æ®µ |
| sort_order | string | å¦ | æåºæ¹å¼ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "code": "PR-DZ100-001",
        "name": "DZ100æºèº«å å·¥è·¯çº¿",
        "product_id": 1,
        "product_name": "DZåéèåç¼©æº",
        "material_id": 10,
        "material_name": "æºèº«é¸ä»¶",
        "type": "machining",
        "type_name": "æºå å·¥èº",
        "status": 1,
        "status_name": "å·²åå¸",
        "version": "V2.0",
        "operation_count": 8,
        "created_by": "zhangsan",
        "created_at": "2025-06-01 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 45,
      "total_pages": 3
    }
  },
  "timestamp": 1717718400
}
```

#### 8.1.2 åå»ºå·¥èºè·¯çº¿

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes`

**æéè¦æ±**: `process:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| code | string | æ¯ | å·¥èºè·¯çº¿ç¼å· |
| name | string | æ¯ | å·¥èºè·¯çº¿åç§° |
| product_id | integer | å¦ | äº§åID |
| material_id | integer | æ¯ | ç©æID |
| type | string | æ¯ | å·¥èºç±»å |
| description | string | å¦ | å·¥èºæè¿° |
| raw_material_spec | string | å¦ | åææè§æ ¼ |
| blank_type | string | å¦ | æ¯å¯ç±»åï¼casting/forging/bar/plate |
| blank_allowance | decimal | å¦ | æ¯å¯ä½é(mm) |
| status | integer | å¦ | ç¶æï¼0èç¨¿ 1å·²åå¸ |

**è¯·æ±ç¤ºä¾**
```json
{
  "code": "PR-DZ100-002",
  "name": "DZ100æ´»å¡æå å·¥å·¥èºè·¯çº¿",
  "material_id": 20,
  "material_name": "æ´»å¡æ",
  "type": "machining",
  "description": "45#é¢è°è´¨å¤çï¼è¡¨é¢éé¬",
  "raw_material_spec": "Î¦60Ã500mm",
  "blank_type": "bar",
  "blank_allowance": 3.0,
  "status": 0
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 2,
    "code": "PR-DZ100-002",
    "name": "DZ100æ´»å¡æå å·¥å·¥èºè·¯çº¿",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.1.3 å·¥èºè·¯çº¿è¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}`

**æéè¦æ±**: `process:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "code": "PR-DZ100-001",
    "name": "DZ100æºèº«å å·¥è·¯çº¿",
    "product_id": 1,
    "product_name": "DZåéèåç¼©æº",
    "material_id": 10,
    "material_name": "æºèº«é¸ä»¶",
    "type": "machining",
    "type_name": "æºå å·¥èº",
    "description": "æºèº«ä¸»è¦å å·¥å·¥åº",
    "raw_material_spec": "HT250é¸ä»¶",
    "blank_type": "casting",
    "blank_allowance": 5.0,
    "status": 1,
    "status_name": "å·²åå¸",
    "version": "V2.0",
    "operations": [
      {
        "id": 1,
        "seq": 10,
        "name": "åçº¿",
        "operation_code": "OP10",
        "work_center": "åçº¿å¹³å°",
        "description": "æå¾çº¸åå å·¥çº¿",
        "steps_count": 3
      },
      {
        "id": 2,
        "seq": 20,
        "name": "ç²é£åºé¢",
        "operation_code": "OP20",
        "work_center": "é¾é¨é£åº",
        "description": "ç²é£åºé¢ï¼çä½é2mm",
        "steps_count": 4
      }
    ],
    "created_by": "zhangsan",
    "created_at": "2025-06-01 09:00:00",
    "updated_at": "2026-01-01 09:00:00",
    "published_at": "2026-01-15 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.1.4 æ´æ°å·¥èºè·¯çº¿

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}`

**æéè¦æ±**: `process:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | å¦ | å·¥èºè·¯çº¿åç§° |
| description | string | å¦ | å·¥èºæè¿° |
| raw_material_spec | string | å¦ | åææè§æ ¼ |
| blank_type | string | å¦ | æ¯å¯ç±»å |
| blank_allowance | decimal | å¦ | æ¯å¯ä½é |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "DZ100æºèº«å å·¥è·¯çº¿ï¼ä¿®è®¢ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.1.5 å é¤å·¥èºè·¯çº¿

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}`

**æéè¦æ±**: `process:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

#### 8.1.6 å¤å¶å·¥èºè·¯çº¿

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}/copy`

**æéè¦æ±**: `process:create`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | æºå·¥èºè·¯çº¿ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| new_code | string | å¦ | æ°å·¥èºè·¯çº¿ç¼å· |
| new_name | string | å¦ | æ°å·¥èºè·¯çº¿åç§° |

**è¯·æ±ç¤ºä¾**
```json
{
  "new_code": "PR-DZ100-001-COPY",
  "new_name": "DZ100æºèº«å å·¥è·¯çº¿ï¼å¤å¶ï¼"
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å¤å¶æå",
  "data": {
    "id": 3,
    "code": "PR-DZ100-001-COPY",
    "name": "DZ100æºèº«å å·¥è·¯çº¿ï¼å¤å¶ï¼",
    "source_id": 1,
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 8.2 å·¥åº

#### 8.2.1 å·¥åºåè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{routeId}/operations`

**æéè¦æ±**: `process:operation:list`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| routeId | integer | æ¯ | å·¥èºè·¯çº¿ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "route_id": 1,
      "seq": 10,
      "name": "åçº¿",
      "operation_code": "OP10",
      "work_center_id": 1,
      "work_center": "åçº¿å¹³å°",
      "equipment_id": null,
      "description": "æå¾çº¸åå å·¥çº¿",
      "setup_time": 15,
      "process_time": 30,
      "cooling_time": 0,
      "total_time": 45,
      "steps_count": 3,
      "status": 1
    },
    {
      "id": 2,
      "route_id": 1,
      "seq": 20,
      "name": "ç²é£åºé¢",
      "operation_code": "OP20",
      "work_center_id": 2,
      "work_center": "é¾é¨é£åº",
      "equipment_id": 5,
      "description": "ç²é£åºé¢ï¼çä½é2mm",
      "setup_time": 20,
      "process_time": 45,
      "cooling_time": 10,
      "total_time": 75,
      "steps_count": 4,
      "status": 1
    }
  ],
  "timestamp": 1717718400
}
```

#### 8.2.2 åå»ºå·¥åº

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{routeId}/operations`

**æéè¦æ±**: `process:operation:create`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| routeId | integer | æ¯ | å·¥èºè·¯çº¿ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| seq | integer | æ¯ | å·¥åºåºå·ï¼10çåæ°ï¼ |
| name | string | æ¯ | å·¥åºåç§° |
| operation_code | string | å¦ | å·¥åºä»£å· |
| work_center_id | integer | å¦ | å·¥ä½ä¸­å¿ID |
| equipment_id | integer | å¦ | è®¾å¤ID |
| fixture_ids | array | å¦ | å·¥è£å¤¹å·IDæ°ç» |
| tool_ids | array | å¦ | åå·IDæ°ç» |
| description | string | å¦ | å·¥åºè¯´æ |
| setup_time | decimal | å¦ | åå¤æ¶é´(åé) |
| process_time | decimal | å¦ | å å·¥æ¶é´(åé) |
| cooling_time | decimal | å¦ | å·å´æ¶é´(åé) |
| inspection_required | boolean | å¦ | æ¯å¦éè¦æ£éª |
| inspection_items | array | å¦ | æ£éªé¡¹ç® |
| status | integer | å¦ | ç¶æï¼1æ­£å¸¸ |

**è¯·æ±ç¤ºä¾**
```json
{
  "seq": 30,
  "name": "ç²¾é£åºé¢",
  "operation_code": "OP30",
  "work_center_id": 2,
  "equipment_id": 5,
  "fixture_ids": [1],
  "tool_ids": [10, 11],
  "description": "ç²¾é£åºé¢è³å°ºå¯¸ï¼Ra3.2",
  "setup_time": 15,
  "process_time": 60,
  "cooling_time": 10,
  "inspection_required": true,
  "inspection_items": [
    {"name": "å¹³é¢åº¦", "tolerance": "0.02mm"},
    {"name": "è¡¨é¢ç²ç³åº¦", "tolerance": "Ra3.2"}
  ],
  "status": 1
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 3,
    "seq": 30,
    "name": "ç²¾é£åºé¢",
    "operation_code": "OP30",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.2.3 å·¥åºè¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/operations/{id}`

**æéè¦æ±**: `process:operation:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥åºID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "route_id": 1,
    "route_name": "DZ100æºèº«å å·¥è·¯çº¿",
    "seq": 10,
    "name": "åçº¿",
    "operation_code": "OP10",
    "work_center_id": 1,
    "work_center": "åçº¿å¹³å°",
    "equipment_id": null,
    "equipment": null,
    "fixtures": [],
    "tools": [
      {"id": 1, "name": "åé", "code": "T-001", "specification": "æ å"}
    ],
    "description": "æå¾çº¸åå å·¥çº¿",
    "setup_time": 15,
    "process_time": 30,
    "cooling_time": 0,
    "total_time": 45,
    "inspection_required": false,
    "inspection_items": [],
    "steps": [
      {
        "id": 1,
        "seq": 1,
        "name": "æ¸æ´å·¥ä»¶",
        "description": "æ¸é¤é¸ä»¶è¡¨é¢åç ",
        "parameters": {}
      }
    ],
    "status": 1,
    "created_at": "2025-06-01 09:00:00",
    "updated_at": "2026-01-01 09:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.2.4 æ´æ°å·¥åº

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/operations/{id}`

**æéè¦æ±**: `process:operation:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥åºID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| seq | integer | å¦ | å·¥åºåºå· |
| name | string | å¦ | å·¥åºåç§° |
| operation_code | string | å¦ | å·¥åºä»£å· |
| work_center_id | integer | å¦ | å·¥ä½ä¸­å¿ID |
| equipment_id | integer | å¦ | è®¾å¤ID |
| fixture_ids | array | å¦ | å·¥è£å¤¹å·IDæ°ç» |
| tool_ids | array | å¦ | åå·IDæ°ç» |
| description | string | å¦ | å·¥åºè¯´æ |
| setup_time | decimal | å¦ | åå¤æ¶é´ |
| process_time | decimal | å¦ | å å·¥æ¶é´ |
| cooling_time | decimal | å¦ | å·å´æ¶é´ |
| inspection_required | boolean | å¦ | æ¯å¦éè¦æ£éª |
| inspection_items | array | å¦ | æ£éªé¡¹ç® |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "åçº¿ï¼ä¿®è®¢ï¼",
    "process_time": 35,
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.2.5 å é¤å·¥åº

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/operations/{id}`

**æéè¦æ±**: `process:operation:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥åºID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

#### 8.2.6 å·¥åºæåº

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/operations/{id}/sort`

**æéè¦æ±**: `process:operation:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥åºID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| new_seq | integer | æ¯ | æ°åºå· |

**è¯·æ±ç¤ºä¾**
```json
{
  "new_seq": 25
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æåºæ´æ°æå",
  "data": {
    "id": 1,
    "seq": 25,
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

### 8.3 å·¥æ­¥

#### 8.3.1 å·¥æ­¥åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/operations/{operationId}/steps`

**æéè¦æ±**: `process:step:list`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| operationId | integer | æ¯ | å·¥åºID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "operation_id": 1,
      "seq": 1,
      "name": "æ¸æ´å·¥ä»¶",
      "description": "æ¸é¤é¸ä»¶è¡¨é¢åç åæ¯åº",
      "parameters": {
        "cleaning_method": "é¢ä¸å·"
      },
      "equipment_requirements": "",
      "safety_notes": "ä½©æ´é²æ¤æå¥",
      "status": 1
    },
    {
      "id": 2,
      "operation_id": 1,
      "seq": 2,
      "name": "æ¾æ­£åºå",
      "description": "ä»¥åºé¢ä¸ºåºåæ¾æ­£",
      "parameters": {
        "datum": "åºé¢A",
        "tolerance": "0.05mm"
      },
      "equipment_requirements": "å¹³æ¿ãåæ¤é¡¶",
      "safety_notes": "",
      "status": 1
    }
  ],
  "timestamp": 1717718400
}
```

#### 8.3.2 åå»ºå·¥æ­¥

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/operations/{operationId}/steps`

**æéè¦æ±**: `process:step:create`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| operationId | integer | æ¯ | å·¥åºID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| seq | integer | æ¯ | å·¥æ­¥åºå· |
| name | string | æ¯ | å·¥æ­¥åç§° |
| description | string | æ¯ | å·¥æ­¥è¯´æ |
| parameters | object | å¦ | å·¥èºåæ°ï¼é®å¼å¯¹ï¼ |
| cutting_params | object | å¦ | åååæ° |
| equipment_requirements | string | å¦ | è®¾å¤è¦æ± |
| safety_notes | string | å¦ | å®å¨æ³¨æäºé¡¹ |
| status | integer | å¦ | ç¶æ |

**cutting_paramså­æ®µè¯´æ**

| å­æ®µ | ç±»å | è¯´æ |
|------|------|------|
| spindle_speed | decimal | ä¸»è½´è½¬é(rpm) |
| feed_rate | decimal | è¿ç»éåº¦(mm/min) |
| cutting_depth | decimal | ååæ·±åº¦(mm) |
| cutting_width | decimal | ååå®½åº¦(mm) |

**è¯·æ±ç¤ºä¾**
```json
{
  "seq": 3,
  "name": "åå å·¥çº¿",
  "description": "æå¾çº¸å°ºå¯¸ååé¢å å·¥çº¿",
  "parameters": {
    "datum": "åºé¢Aãä¾§é¢B"
  },
  "equipment_requirements": "åçº¿å¹³å°ãé«åº¦å°º",
  "safety_notes": "æ³¨æåéä½¿ç¨å®å¨",
  "status": 1
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 3,
    "seq": 3,
    "name": "åå å·¥çº¿",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.3.3 æ´æ°å·¥æ­¥

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/steps/{id}`

**æéè¦æ±**: `process:step:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥æ­¥ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| seq | integer | å¦ | å·¥æ­¥åºå· |
| name | string | å¦ | å·¥æ­¥åç§° |
| description | string | å¦ | å·¥æ­¥è¯´æ |
| parameters | object | å¦ | å·¥èºåæ° |
| cutting_params | object | å¦ | åååæ° |
| equipment_requirements | string | å¦ | è®¾å¤è¦æ± |
| safety_notes | string | å¦ | å®å¨æ³¨æäºé¡¹ |
| status | integer | å¦ | ç¶æ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "æ¸æ´å·¥ä»¶ï¼ä¿®è®¢ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.3.4 å é¤å·¥æ­¥

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/steps/{id}`

**æéè¦æ±**: `process:step:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥æ­¥ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

### 8.4 å·¥èºå¡ç

#### 8.4.1 å·¥èºå¡çåè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/cards`

**æéè¦æ±**: `card:list`

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å¦ | é¡µç  |
| page_size | integer | å¦ | æ¯é¡µæ°é |
| keyword | string | å¦ | æç´¢å³é®è¯ |
| route_id | integer | å¦ | å·¥èºè·¯çº¿ID |
| product_id | integer | å¦ | äº§åID |
| type | string | å¦ | å¡çç±»å |
| status | integer | å¦ | ç¶æï¼0èç¨¿ 1å®¡æ ¸ä¸­ 2å·²æ¹å 3å·²é©³å |
| sort_by | string | å¦ | æåºå­æ®µ |
| sort_order | string | å¦ | æåºæ¹å¼ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "code": "CARD-20260607-001",
        "name": "DZ100æºèº«å å·¥å·¥èºå¡",
        "route_id": 1,
        "route_name": "DZ100æºèº«å å·¥è·¯çº¿",
        "product_id": 1,
        "product_name": "DZåéèåç¼©æº",
        "type": "process_card",
        "type_name": "å·¥èºè¿ç¨å¡",
        "status": 2,
        "status_name": "å·²æ¹å",
        "version": "V1.0",
        "created_by": "zhangsan",
        "reviewer": "lisi",
        "approved_at": "2026-06-05 14:00:00",
        "created_at": "2026-06-01 09:00:00"
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 120,
      "total_pages": 6
    }
  },
  "timestamp": 1717718400
}
```

#### 8.4.2 åå»ºå·¥èºå¡ç

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/cards`

**æéè¦æ±**: `card:create`

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | æ¯ | å¡çåç§° |
| route_id | integer | æ¯ | å·¥èºè·¯çº¿ID |
| type | string | æ¯ | å¡çç±»åï¼process_card/operation_card/inspection_card |
| template_id | integer | å¦ | æ¨¡æ¿ID |
| remark | string | å¦ | å¤æ³¨ |

**è¯·æ±ç¤ºä¾**
```json
{
  "name": "DZ100æºèº«å å·¥å·¥èºå¡",
  "route_id": 1,
  "type": "process_card",
  "template_id": 1,
  "remark": "ç¨äºDZ100æºèº«æ¹éçäº§"
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 1,
    "code": "CARD-20260607-001",
    "name": "DZ100æºèº«å å·¥å·¥èºå¡",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.4.3 å·¥èºå¡çè¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/cards/{id}`

**æéè¦æ±**: `card:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºå¡çID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "code": "CARD-20260607-001",
    "name": "DZ100æºèº«å å·¥å·¥èºå¡",
    "route_id": 1,
    "route_name": "DZ100æºèº«å å·¥è·¯çº¿",
    "product_id": 1,
    "product_name": "DZåéèåç¼©æº",
    "material_id": 10,
    "material_name": "æºèº«é¸ä»¶",
    "material_spec": "HT250",
    "type": "process_card",
    "type_name": "å·¥èºè¿ç¨å¡",
    "status": 2,
    "status_name": "å·²æ¹å",
    "version": "V1.0",
    "operations": [
      {
        "seq": 10,
        "name": "åçº¿",
        "work_center": "åçº¿å¹³å°",
        "description": "æå¾çº¸åå å·¥çº¿",
        "equipment": "",
        "tools": "åéãé«åº¦å°º",
        "setup_time": 15,
        "process_time": 30,
        "worker_grade": "3çº§",
        "inspection": ""
      }
    ],
    "total_setup_time": 120,
    "total_process_time": 360,
    "total_time": 480,
    "remark": "",
    "created_by": "zhangsan",
    "reviewer": "lisi",
    "approved_by": "wangwu",
    "approved_at": "2026-06-05 14:00:00",
    "created_at": "2026-06-01 09:00:00",
    "updated_at": "2026-06-05 14:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.4.4 æ´æ°å·¥èºå¡ç

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/cards/{id}`

**æéè¦æ±**: `card:edit`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºå¡çID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| name | string | å¦ | å¡çåç§° |
| remark | string | å¦ | å¤æ³¨ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "æ´æ°æå",
  "data": {
    "id": 1,
    "name": "DZ100æºèº«å å·¥å·¥èºå¡ï¼ä¿®è®¢ï¼",
    "updated_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.4.5 å é¤å·¥èºå¡ç

**è¯·æ±æ¹æ³**: `DELETE`

**è¯·æ±è·¯å¾**: `/api/v1/cards/{id}`

**æéè¦æ±**: `card:delete`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºå¡çID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "å é¤æå",
  "data": null,
  "timestamp": 1717718400
}
```

#### 8.4.6 æå°å·¥èºå¡ç

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/cards/{id}/print`

**æéè¦æ±**: `card:print`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºå¡çID |

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| format | string | å¦ | è¾åºæ ¼å¼ï¼html/pdfï¼é»è®¤html |

**ååºè¯´æ**
- format=html: è¿åHTMLæ ¼å¼çæå°é¡µé¢
- format=pdf: è¿åPDFæä»¶æµï¼Content-Type: application/pdf

#### 8.4.7 å¯¼åºå·¥èºå¡ç

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/cards/{id}/export`

**æéè¦æ±**: `card:export`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºå¡çID |

**è¯·æ±åæ°ï¼Queryï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| format | string | æ¯ | å¯¼åºæ ¼å¼ï¼pdf/excel/word |

**ååºè¯´æ**
- è¿åå¯¹åºæ ¼å¼çæä»¶æµ
- Content-Typeæ ¹æ®æ ¼å¼è®¾ç½®
- Content-Disposition: attachment; filename="å·¥èºå¡ç.xlsx"

### 8.5 å·¥èºçæ¬

#### 8.5.1 çæ¬åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}/versions`

**æéè¦æ±**: `process:version:view`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "version": "V1.0",
      "status": "archived",
      "status_name": "å·²å½æ¡£",
      "change_description": "åå§çæ¬",
      "change_reason": "æ°å»ºå·¥èºè·¯çº¿",
      "operation_count": 6,
      "created_by": "zhangsan",
      "created_at": "2025-06-01 09:00:00",
      "published_at": "2025-06-15 10:00:00"
    },
    {
      "id": 2,
      "version": "V2.0",
      "status": "current",
      "status_name": "å½åçæ¬",
      "change_description": "å¢å æ£éªå·¥åº",
      "change_reason": "è´¨éæ¹è¿",
      "operation_count": 8,
      "created_by": "zhangsan",
      "created_at": "2026-01-01 09:00:00",
      "published_at": "2026-01-15 10:00:00"
    }
  ],
  "timestamp": 1717718400
}
```

#### 8.5.2 åå»ºæ°çæ¬

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}/versions`

**æéè¦æ±**: `process:version:create`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| change_description | string | æ¯ | åæ´è¯´æ |
| change_reason | string | å¦ | åæ´åå  |
| copy_from_version | integer | å¦ | åºäºåªä¸ªçæ¬åå»ºï¼é»è®¤å½åçæ¬ |

**è¯·æ±ç¤ºä¾**
```json
{
  "change_description": "ä¼ååååæ°ï¼æé«æç",
  "change_reason": "çäº§æçæå",
  "copy_from_version": 2
}
```

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå»ºæå",
  "data": {
    "id": 3,
    "version": "V3.0",
    "status": "draft",
    "status_name": "èç¨¿",
    "created_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.5.3 åå¸çæ¬

**è¯·æ±æ¹æ³**: `PUT`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}/versions/{versionId}/publish`

**æéè¦æ±**: `process:version:publish`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |
| versionId | integer | æ¯ | çæ¬ID |

**è¯·æ±åæ°ï¼Bodyï¼**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| approver_ids | array | å¦ | å®¡æ¹äººIDæ°ç» |
| remark | string | å¦ | åå¸å¤æ³¨ |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "åå¸æå",
  "data": {
    "id": 3,
    "version": "V3.0",
    "status": "current",
    "status_name": "å½åçæ¬",
    "published_at": "2026-06-07 10:00:00"
  },
  "timestamp": 1717718400
}
```

#### 8.5.4 çæ¬å¯¹æ¯

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/process-routes/{id}/versions/{versionA}/compare/{versionB}`

**æéè¦æ±**: `process:version:compare`

**è·¯å¾åæ°**

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| id | integer | æ¯ | å·¥èºè·¯çº¿ID |
| versionA | integer | æ¯ | çæ¬AçID |
| versionB | integer | æ¯ | çæ¬BçID |

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "version_a": {
      "id": 1,
      "version": "V1.0",
      "operation_count": 6
    },
    "version_b": {
      "id": 2,
      "version": "V2.0",
      "operation_count": 8
    },
    "changes": {
      "added": [
        {
          "type": "operation",
          "seq": 70,
          "name": "ç²¾ç£¨",
          "description": "æ°å¢ç²¾ç£¨å·¥åº"
        }
      ],
      "modified": [
        {
          "type": "operation",
          "seq": 20,
          "name": "ç²é£åºé¢",
          "field": "process_time",
          "old_value": 45,
          "new_value": 40
        }
      ],
      "deleted": []
    }
  },
  "timestamp": 1717718400
}
```

---

## 9. èµæºç®¡çAPI

### 9.1 è®¾å¤

#### 9.1.1 è®¾å¤åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/equipment`

**æéè¦æ±**: `equipment:list`

**è¯·æ±åæ°ï¼Queryï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å?| é¡µç  |
| page_size | integer | å?| æ¯é¡µæ°é |
| keyword | string | å?| æç´¢å³é®è¯?|
| type | string | å?| è®¾å¤ç±»å |
| status | integer | å?| ç¶æï¼1æ­£å¸¸ 2ç»´ä¿® 3åç¨ |
| workshop_id | integer | å?| è½¦é´ID |

#### 9.1.2-9.1.5 è®¾å¤CRUD

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `equipment:create`ã`equipment:view`ã`equipment:edit`ã`equipment:delete`ã?
### 9.2 å·¥è£å¤¹å·

#### 9.2.1 å·¥è£å¤¹å·åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/fixtures`

**æéè¦æ±**: `fixture:list`

#### 9.2.2-9.2.5 å·¥è£å¤¹å·CRUD

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `fixture:create`ã`fixture:view`ã`fixture:edit`ã`fixture:delete`ã?
### 9.3 åå?
#### 9.3.1 åå·åè¡?
**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/tools`

**æéè¦æ±**: `tool:list`

#### 9.3.2-9.3.5 åå·CRUD

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `tool:create`ã`tool:view`ã`tool:edit`ã`tool:delete`ã?
### 9.4 éå·

#### 9.4.1 éå·åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/measuring-tools`

**æéè¦æ±**: `measuring_tool:list`

**ååºç¤ºä¾**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "code": "MT-001",
        "name": "æ¸¸æ å¡å°º",
        "type": "caliper",
        "type_name": "å¡å°º",
        "specification": "0-300mm",
        "precision": "0.02mm",
        "status": 1,
        "calibration_date": "2026-01-01",
        "next_calibration_date": "2026-07-01"
      }
    ],
    "pagination": {"page": 1, "page_size": 20, "total": 45, "total_pages": 3}
  },
  "timestamp": 1717718400
}
```

#### 9.4.2-9.4.5 éå·CRUD

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `measuring_tool:create`ã`measuring_tool:view`ã`measuring_tool:edit`ã`measuring_tool:delete`ã?
### 9.5 ææ

> **æ³¨æ**: ææAPIä¸ç©æç®¡çAPIï¼ç¬¬7ç« ï¼å±ç¨åä¸å¥æ¥å£ï¼éè¿ `material_type=raw` ç­éã?
---

## 10. å·¥æ¶å®é¢API

### 10.1 å·¥æ¶å®é¢åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/time-quotas`

**æéè¦æ±**: `time_quota:list`

**è¯·æ±åæ°ï¼Queryï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å?| é¡µç  |
| page_size | integer | å?| æ¯é¡µæ°é |
| route_id | integer | å?| å·¥èºè·¯çº¿ID |
| operation_id | integer | å?| å·¥åºID |
| keyword | string | å?| æç´¢å³é®è¯?|

### 10.2 åå»ºå·¥æ¶å®é¢

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/time-quotas`

**æéè¦æ±**: `time_quota:create`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| route_id | integer | æ?| å·¥èºè·¯çº¿ID |
| operation_id | integer | æ?| å·¥åºID |
| setup_time | decimal | æ?| åå¤æ¶é´(åé) |
| process_time | decimal | æ?| å å·¥æ¶é´(åé) |
| cooling_time | decimal | å?| å·å´æ¶é´(åé) |
| batch_size | integer | å?| æ¹éå¤§å° |
| worker_grade | string | å?| å·¥äººç­çº§ |

### 10.3-10.5 å·¥æ¶å®é¢è¯¦æ/æ´æ°/å é¤

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `time_quota:view`ã`time_quota:edit`ã`time_quota:delete`ã?
### 10.6 è®¡ç®å·¥æ¶

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/time-quota/calculate`

**æéè¦æ±**: `time_quota:calculate`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| operation_type | string | æ?| å·¥åºç±»å |
| material_grade | string | æ?| æè´¨çå· |
| cutting_params | object | å?| åååæ° |
| batch_size | integer | å?| æ¹éå¤§å° |
| use_ai | boolean | å?| æ¯å¦ä½¿ç¨AIè®¡ç® |

---

## 11. æ¨¡æ¿ç®¡çAPI

### 11.1 æ¨¡æ¿åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/templates`

**æéè¦æ±**: `template:list`

### 11.2-11.5 æ¨¡æ¿CRUD

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `template:create`ã`template:view`ã`template:edit`ã`template:delete`ã?
### 11.6 åºç¨æ¨¡æ¿

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/templates/{id}/apply`

**æéè¦æ±**: `template:apply`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| target_type | string | æ?| ç®æ ç±»åï¼card/route |
| target_id | integer | æ?| ç®æ ID |

---

## 12. å®¡æ¹æµç¨API

### 12.1 æµç¨å®ä¹åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/workflows`

**æéè¦æ±**: `workflow:list`

### 12.2-12.5 æµç¨å®ä¹CRUD

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `workflow:create`ã`workflow:view`ã`workflow:edit`ã`workflow:delete`ã?
### 12.6 å®¡æ¹åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/approvals`

**æéè¦æ±**: `approval:list`

**è¯·æ±åæ°ï¼Queryï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| page | integer | å?| é¡µç  |
| page_size | integer | å?| æ¯é¡µæ°é |
| status | integer | å?| ç¶æï¼0å¾å®¡æ?1å·²éè¿ 2å·²é©³å?3å·²æ¤é |
| my_pending | boolean | å?| æ¯å¦åªæ¥è¯¢å¾æå®¡æ¹ç |

### 12.7 åèµ·å®¡æ¹

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/approvals`

**æéè¦æ±**: `approval:create`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| workflow_id | integer | æ?| æµç¨å®ä¹ID |
| title | string | æ?| å®¡æ¹æ é¢ |
| target_type | string | æ?| ç®æ ç±»å |
| target_id | integer | æ?| ç®æ ID |

### 12.8 å®¡æ¹è¯¦æ

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/approvals/{id}`

**æéè¦æ±**: `approval:view`

### 12.9 å®¡æ¹éè¿

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/approvals/{id}/approve`

**æéè¦æ±**: `approval:approve`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| comment | string | å?| å®¡æ¹æè§ |

### 12.10 å®¡æ¹é©³å

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/approvals/{id}/reject`

**æéè¦æ±**: `approval:reject`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| comment | string | æ?| é©³åæè§ |

### 12.11 æ¤éå®¡æ¹

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/approvals/{id}/cancel`

**æéè¦æ±**: `approval:cancel`

**è¯·æ±åæ°ï¼Bodyï¼?*

| åæ° | ç±»å | å¿å¡« | è¯´æ |
|------|------|------|------|
| reason | string | å?| æ¤éåå  |

---

## 13. ææ¡£ç®¡çAPI

### 13.1 ææ¡£åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/documents`

**æéè¦æ±**: `document:list`

### 13.2 ä¸ä¼ ææ¡£

**è¯·æ±æ¹æ³**: `POST`

**è¯·æ±è·¯å¾**: `/api/v1/documents/upload`

**æéè¦æ±**: `document:upload`

**Content-Type**: `multipart/form-data`

### 13.3-13.5 ææ¡£è¯¦æ/æ´æ°/å é¤

æ¥å£ç»ææ åï¼æéæ è¯åå«ä¸º `document:view`ã`document:edit`ã`document:delete`ã?
### 13.6 ä¸è½½ææ¡£

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/documents/{id}/download`

**æéè¦æ±**: `document:download`

### 13.7 ææ¡£çæ¬åè¡¨

**è¯·æ±æ¹æ³**: `GET`

**è¯·æ±è·¯å¾**: `/api/v1/documents/{id}/versions`

**æéè¦æ±**: `document:version:view`

---

## 14. AIåè½API

### 14.1 AIå©æ

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| AIå¯¹è¯ | POST | `/api/v1/ai/chat` | `ai:chat` |
| ä¼è¯åè¡¨ | GET | `/api/v1/ai/sessions` | `ai:chat` |
| åå»ºä¼è¯ | POST | `/api/v1/ai/sessions` | `ai:chat` |
| å é¤ä¼è¯ | DELETE | `/api/v1/ai/sessions/{id}` | `ai:chat` |
| ä¼è¯æ¶æ¯ | GET | `/api/v1/ai/sessions/{id}/messages` | `ai:chat` |

### 14.2 AIå³ç­

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| å·¥èºè·¯çº¿æ¨è | POST | `/api/v1/ai/decision/process-route` | `ai:decision` |
| åæ°ä¼åå»ºè®® | POST | `/api/v1/ai/decision/parameters` | `ai:decision` |
| å·¥æ¶æºè½è®¡ç® | POST | `/api/v1/ai/decision/time-quota` | `ai:decision` |
| ææéæ©å»ºè®® | POST | `/api/v1/ai/decision/material` | `ai:decision` |
| è®¾å¤å¹éæ¨è | POST | `/api/v1/ai/decision/equipment` | `ai:decision` |

### 14.3 ç¥è¯åº?
| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| ç¥è¯åºåè¡?| GET | `/api/v1/ai/knowledge` | `ai:knowledge:list` |
| æ·»å ç¥è¯ | POST | `/api/v1/ai/knowledge` | `ai:knowledge:create` |
| æ´æ°ç¥è¯ | PUT | `/api/v1/ai/knowledge/{id}` | `ai:knowledge:edit` |
| å é¤ç¥è¯ | DELETE | `/api/v1/ai/knowledge/{id}` | `ai:knowledge:delete` |

---

## 15. MaxKBä¸ä¸çAPI

### 15.1 ç¥è¯åºAPI

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| ç¥è¯åºåè¡?| GET | `/api/v1/maxkb/knowledge-bases` | `maxkb:kb:list` |
| åå»ºç¥è¯åº?| POST | `/api/v1/maxkb/knowledge-bases` | `maxkb:kb:create` |
| ä¸ä¼ ææ¡£ | POST | `/api/v1/maxkb/documents/upload` | `maxkb:doc:upload` |
| æ£ç´¢ç¥è¯åº | POST | `/api/v1/maxkb/documents/search` | `maxkb:doc:search` |
| å é¤ææ¡£ | DELETE | `/api/v1/maxkb/documents/{id}` | `maxkb:doc:delete` |

### 15.2 æºè½ä½API

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| æºè½ä½åè¡?| GET | `/api/v1/maxkb/agents` | `maxkb:agent:list` |
| æºè½ä½å¯¹è¯?| POST | `/api/v1/maxkb/agents/{id}/chat` | `maxkb:agent:chat` |
| æµå¼å¯¹è¯ | GET | `/api/v1/maxkb/agents/{id}/chat/stream` | `maxkb:agent:chat` |
| ä¼è¯åè¡¨ | GET | `/api/v1/maxkb/agents/{id}/sessions` | `maxkb:agent:list` |
| å é¤ä¼è¯ | DELETE | `/api/v1/maxkb/agents/{id}/sessions/{sessionId}` | `maxkb:agent:delete` |

### 15.3 åæ­¥API

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| åæ­¥å°MaxKB | POST | `/api/v1/maxkb/sync/to-maxkb` | `maxkb:sync` |
| ä»MaxKBåæ­¥ | POST | `/api/v1/maxkb/sync/from-maxkb` | `maxkb:sync` |

---

## 16. ä¼ä¸å¾®ä¿¡API

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| åè°éªè¯ | GET | `/api/v1/wecom/callback` | å¬å¼æ¥å£ |
| æ¥æ¶äºä»¶ | POST | `/api/v1/wecom/callback` | å¬å¼æ¥å£ |
| åéæ¶æ?| POST | `/api/v1/wecom/message/send` | `wecom:message` |
| åæ­¥ç¨æ· | GET | `/api/v1/wecom/users` | `wecom:sync` |
| åæ­¥é¨é¨ | GET | `/api/v1/wecom/departments` | `wecom:sync` |

---

## 17. å¤é¨ç³»ç»éæAPI

### 17.1 CAXA PLM

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| åæ­¥äº§å | POST | `/api/v1/integration/plm/sync-product` | `integration:plm` |
| åæ­¥BOM | POST | `/api/v1/integration/plm/sync-bom` | `integration:plm` |
| PLMåè° | GET | `/api/v1/integration/plm/callback` | å¬å¼æ¥å£ |

### 17.2 U8 ERP

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| åæ­¥ç©æ | POST | `/api/v1/integration/u8/sync-material` | `integration:u8` |
| æ¨éå·¥è?| POST | `/api/v1/integration/u8/push-process` | `integration:u8` |
| æ¥è¯¢åºå­ | GET | `/api/v1/integration/u8/inventory` | `integration:u8` |

### 17.3 MES

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| ä¸åå·¥èº | POST | `/api/v1/integration/mes/push-process` | `integration:mes` |
| MESåè° | POST | `/api/v1/integration/mes/callback` | å¬å¼æ¥å£ |
| çäº§æ°æ® | GET | `/api/v1/integration/mes/production-data` | `integration:mes` |

---

## 18. ç»è®¡åæAPI

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| å·¥èºç»è®¡ | GET | `/api/v1/statistics/process` | `statistics:process` |
| å·¥æ¶åæ | GET | `/api/v1/statistics/time` | `statistics:time` |
| è®¾å¤å©ç¨ç?| GET | `/api/v1/statistics/equipment` | `statistics:equipment` |
| å¯¼åºæ¥è¡¨ | GET | `/api/v1/statistics/export` | `statistics:export` |

---

## 19. ç³»ç»è®¾ç½®API

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| ç³»ç»åæ°åè¡¨ | GET | `/api/v1/settings` | `setting:list` |
| æ´æ°ç³»ç»åæ° | PUT | `/api/v1/settings` | `setting:edit` |
| æ°æ®å­å¸åè¡¨ | GET | `/api/v1/dictionaries` | `dictionary:list` |
| è·åå­å¸æ°æ® | GET | `/api/v1/dictionaries/{type}` | `dictionary:list` |
| æä½æ¥å¿ | GET | `/api/v1/logs` | `log:list` |
| ç»å½æ¥å¿ | GET | `/api/v1/logs/login` | `log:login` |

---

## 20. Webhookåè°API

| æ¥å£ | æ¹æ³ | è·¯å¾ | æé |
|------|------|------|------|
| PLMåè° | POST | `/api/v1/webhook/plm` | å¬å¼æ¥å£ï¼ééªç­¾ï¼?|
| U8åè° | POST | `/api/v1/webhook/u8` | å¬å¼æ¥å£ï¼ééªç­¾ï¼?|
| MESåè° | POST | `/api/v1/webhook/mes` | å¬å¼æ¥å£ï¼ééªç­¾ï¼?|
| ä¼ä¸å¾®ä¿¡åè° | POST | `/api/v1/webhook/wecom` | å¬å¼æ¥å£ï¼ééªç­¾ï¼?|
| MaxKBåè° | POST | `/api/v1/webhook/maxkb` | å¬å¼æ¥å£ï¼ééªç­¾ï¼?|

---

## 21. APIå®å¨è®¾è®¡

### 21.1 è¯·æ±ç­¾å

**ç­¾åç®æ³**: HMAC-SHA256

**è¯·æ±å¤?*

| è¯·æ±å¤?| è¯´æ |
|-------|------|
| X-App-Key | åºç¨æ è¯ |
| X-Timestamp | è¯·æ±æ¶é´æ³ï¼ç§ï¼ |
| X-Nonce | éæºå­ç¬¦ä¸?|
| X-Signature | è¯·æ±ç­¾å |

### 21.2 é²éæ¾æ»å?
- è¯·æ±æ¶é´æ³ä¸æå¡å¨æ¶é´å·®ä¸è½è¶è¿5åé
- Nonceå?åéåä¸è½éå¤ä½¿ç¨ï¼Rediså­å¨ï¼?
### 21.3 æ¥å£éæµ

**éæµç­ç¥**: æ»å¨çªå£è®¡æ°å¨ç®æ³?
| è§å | éå¶ | è¯´æ |
|------|------|------|
| å¨å±éæµ | 1000æ¬?åé | åIPå¨å±è¯·æ±éå¶ |
| ç»å½éæµ | 5æ¬?åé | ç»å½æ¥å£éå¶ |
| æææä½éæµ | 30æ¬?åé | å é¤ãå¯¼åºç­æä½ |
| AIæ¥å£éæµ | 20æ¬?åé | AIå¯¹è¯ãå³ç­æ¥å?|

### 21.4 IPç½åå?
**éç¨èå´**: å¤é¨ç³»ç»éæAPIãWebhookåè°API

### 21.5 æ°æ®å å¯

| æ°æ®ç±»å | å å¯æ¹å¼ |
|---------|---------|
| å¯ç  | bcrypt |
| Token | JWT (HS256) |
| ææéç½® | AES-256-CBC |
| æ¥å£ä¼ è¾ | RSAï¼å¬é¥å å¯ï¼ç§é¥è§£å¯ï¼?|

---

## 22. APIçæ¬ç®¡ç

### 22.1 çæ¬ç­ç¥

**çæ¬æ è¯**: URLè·¯å¾çæ¬å?`/api/v1/`ã`/api/v2/`

**çæ¬è§å**:
- ä¸»çæ¬å·ï¼ä¸å¼å®¹çAPIåæ´
- ååå¼å®¹çåæ´ä¸éè¦åçº§çæ¬å·
- æ§çæ¬ä¿çè³å°?ä¸ªæ

### 22.2 çæ¬è¿ç§»

**è¿ç§»éç¥æºå¶**:
- ååºå¤´ï¼`X-API-Version: v1`
- åºå¼çæ¬ï¼`X-API-Deprecated: true`
- åºå¼çæ¬ï¼`X-API-Sunset: 2026-12-31`

---

## 23. PHPå®ç°ç¤ºä¾

### 23.1 è·¯ç±è®¾è®¡

**è·¯ç±éç½®æä»¶** `routes/api.php`
```php
<?php
return [
    'POST /api/v1/auth/login' => 'AuthController@login',
    'POST /api/v1/auth/qrcode-login' => 'AuthController@qrcodeLogin',
    'GET /api/v1/auth/user-info' => ['AuthController@userInfo', ['auth']],
    'POST /api/v1/auth/logout' => ['AuthController@logout', ['auth']],
    'GET /api/v1/users' => ['UserController@index', ['auth', 'permission:user:list']],
    'POST /api/v1/users' => ['UserController@store', ['auth', 'permission:user:create']],
    'GET /api/v1/users/{id}' => ['UserController@show', ['auth', 'permission:user:view']],
    'PUT /api/v1/users/{id}' => ['UserController@update', ['auth', 'permission:user:edit']],
    'DELETE /api/v1/users/{id}' => ['UserController@destroy', ['auth', 'permission:user:delete']],
    'GET /api/v1/process-routes' => ['ProcessRouteController@index', ['auth', 'permission:process:list']],
    'POST /api/v1/process-routes' => ['ProcessRouteController@store', ['auth', 'permission:process:create']],
    'GET /api/v1/process-routes/{id}' => ['ProcessRouteController@show', ['auth', 'permission:process:view']],
    'PUT /api/v1/process-routes/{id}' => ['ProcessRouteController@update', ['auth', 'permission:process:edit']],
    'DELETE /api/v1/process-routes/{id}' => ['ProcessRouteController@destroy', ['auth', 'permission:process:delete']],
];
```

### 23.2 æ§å¶å¨åºç±?
**åºç±»æä»¶** `core/BaseController.php`
```php
<?php
namespace App\Controllers;

class BaseController
{
    protected function success($data = null, string $message = 'success', int $code = 200): void
    {
        Response::success($data, $message, $code);
    }
    
    protected function error(int $code, string $message, $data = null): void
    {
        Response::error($code, $message, $data);
    }
    
    protected function paginate(array $data, int $total, int $page, int $pageSize): array
    {
        return [
            'list' => $data,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total' => $total,
                'total_pages' => (int)ceil($total / $pageSize),
            ],
        ];
    }
    
    protected function input(string $key, $default = null)
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        return $input[$key] ?? $default;
    }
    
    protected function validate(array $rules, array $data): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $ruleParts = explode('|', $rule);
            foreach ($ruleParts as $r) {
                if ($r === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
                    $errors[$field][] = "{$field}ä¸è½ä¸ºç©º";
                }
            }
        }
        return $errors;
    }
    
    protected function currentUser(): array
    {
        return Request::user();
    }
}
```

### 23.3 ååºå°è£

**ååºç±?* `core/Response.php`
```php
<?php
class Response
{
    public static function success($data = null, string $message = 'success', int $code = 200): void
    {
        self::json($code, $message, $data);
    }
    
    public static function error(int $code, string $message, $data = null, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        self::json($code, $message, $data);
    }
    
    public static function file(string $filePath, string $filename, string $mimeType = 'application/octet-stream'): void
    {
        if (!file_exists($filePath)) {
            self::error(40401, 'æä»¶ä¸å­å?);
            return;
        }
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
    
    private static function json(int $code, string $message, $data = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('X-API-Version: v1');
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => time(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
```

### 23.4 ä¸­é´ä»¶å®ç?
#### 23.4.1 è®¤è¯ä¸­é´ä»?
**æä»¶** `middleware/AuthMiddleware.php`
```php
<?php
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): void
    {
        $token = $this->getToken();
        if (!$token) {
            Response::error(40101, 'æªç»å½æTokenæ æ', null, 401);
        }
        try {
            $payload = JWT::decode($token, Config::get('jwt.secret'));
            if (Redis::exists("token:blacklist:{$payload['jti']}")) {
                Response::error(40101, 'Tokenå·²å¤±æ?, null, 401);
            }
            $user = User::find($payload['sub']);
            if (!$user || $user['status'] == 0) {
                Response::error(40104, 'è´¦å·å·²è¢«ç¦ç¨', null, 401);
            }
            Request::setUser($user);
        } catch (TokenExpiredException $e) {
            Response::error(40102, 'Tokenå·²è¿æ?, null, 401);
        } catch (Exception $e) {
            Response::error(40101, 'Tokenæ æ', null, 401);
        }
    }
    
    private function getToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
```

#### 23.4.2 æéä¸­é´ä»?
**æä»¶** `middleware/PermissionMiddleware.php`
```php
<?php
class PermissionMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): void
    {
        if (empty($params)) return;
        $user = Request::user();
        $requiredPermission = $params[0];
        if (in_array('super_admin', $user['role_codes'])) return;
        if (!in_array($requiredPermission, $user['permissions'])) {
            Response::error(40301, 'æ æéè®¿é?, null, 403);
        }
    }
}
```

#### 23.4.3 éæµä¸­é´ä»?
**æä»¶** `middleware/RateLimitMiddleware.php`
```php
<?php
class RateLimitMiddleware implements MiddlewareInterface
{
    public function handle(array $params = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "ratelimit:{$ip}";
        $limit = 1000;
        $window = 60;
        
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (strpos($uri, '/auth/login') !== false) {
            $key = "ratelimit:login:{$ip}";
            $limit = 5;
        } elseif (strpos($uri, '/ai/') !== false) {
            $key = "ratelimit:ai:{$ip}";
            $limit = 20;
        }
        
        $current = Redis::incr($key);
        if ($current === 1) Redis::expire($key, $window);
        
        if ($current > $limit) {
            Response::error(42901, 'è¯·æ±è¿äºé¢ç¹', ['retry_after' => Redis::ttl($key)], 429);
        }
        
        header("X-RateLimit-Limit: {$limit}");
        header("X-RateLimit-Remaining: " . max(0, $limit - $current));
        header("X-RateLimit-Reset: " . (time() + Redis::ttl($key)));
    }
}
```

### 23.5 éè¯¯å¤ç

**å¨å±éè¯¯å¤çå?* `core/ErrorHandler.php`
```php
<?php
class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleException(Throwable $e): void
    {
        Logger::error('Uncaught Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        if (Config::get('app.debug')) {
            Response::error(50001, $e->getMessage(), null, 500);
        } else {
            Response::error(50001, 'æå¡å¨åé¨éè¯?, null, 500);
        }
    }
    
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        Logger::error('PHP Error', compact('errno', 'errstr', 'errfile', 'errline'));
        Response::error(50001, 'æå¡å¨åé¨éè¯?, null, 500);
        return true;
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR])) {
            Logger::error('Fatal Error', $error);
            Response::error(50001, 'æå¡å¨åé¨éè¯?, null, 500);
        }
    }
}

ErrorHandler::register();
```

### 23.6 å¥å£æä»¶

**å¥å£æä»¶** `public/index.php`
```php
<?php
define('ROOT_PATH', dirname(__DIR__) . '/');
require ROOT_PATH . 'config/app.php';
require ROOT_PATH . 'config/database.php';

spl_autoload_register(function ($class) {
    $file = ROOT_PATH . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) require $file;
});

date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ErrorHandler::register();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

$router = new Router();
$router->dispatch();
```

---

## éå½

### A. æéæ è¯å®æ´åè¡¨

| æ¨¡å | æéæ è¯ | è¯´æ |
|------|---------|------|
| ç¨æ·ç®¡ç | user:list, user:create, user:view, user:edit, user:delete, user:status | ç¨æ·ç®¡çæé |
| è§è²ç®¡ç | role:list, role:create, role:edit, role:delete, role:assign | è§è²ç®¡çæé |
| é¨é¨ç®¡ç | department:list, department:create, department:edit, department:delete | é¨é¨ç®¡çæé |
| äº§åç®¡ç | product:list, product:create, product:view, product:edit, product:delete, product:bom:view, product:bom:edit, product:bom:delete, product:version:view | äº§åç®¡çæé |
| ç©æç®¡ç | material:list, material:create, material:view, material:edit, material:delete | ç©æç®¡çæé |
| å·¥èºç®¡ç | process:list, process:create, process:view, process:edit, process:delete, process:operation:list, process:operation:create, process:operation:view, process:operation:edit, process:operation:delete, process:step:list, process:step:create, process:step:edit, process:step:delete, process:version:view, process:version:create, process:version:publish, process:version:compare | å·¥èºç®¡çæé |
| å·¥èºå¡ç | card:list, card:create, card:view, card:edit, card:delete, card:print, card:export, card:approve | å·¥èºå¡çæé |
| èµæºç®¡ç | equipment:list, equipment:create, equipment:view, equipment:edit, equipment:delete, fixture:list, fixture:create, fixture:view, fixture:edit, fixture:delete, tool:list, tool:create, tool:view, tool:edit, tool:delete, measuring_tool:list, measuring_tool:create, measuring_tool:view, measuring_tool:edit, measuring_tool:delete | èµæºç®¡çæé |
| å·¥æ¶å®é¢ | time_quota:list, time_quota:create, time_quota:view, time_quota:edit, time_quota:delete, time_quota:calculate | å·¥æ¶å®é¢æé |
| æ¨¡æ¿ç®¡ç | template:list, template:create, template:view, template:edit, template:delete, template:apply | æ¨¡æ¿ç®¡çæé |
| å®¡æ¹ç®¡ç | workflow:list, workflow:create, workflow:view, workflow:edit, workflow:delete, approval:list, approval:create, approval:view, approval:approve, approval:reject, approval:cancel | å®¡æ¹ç®¡çæé |
| ææ¡£ç®¡ç | document:list, document:upload, document:view, document:edit, document:delete, document:download, document:version:view | ææ¡£ç®¡çæé |
| AIåè½ | ai:chat, ai:decision, ai:knowledge:list, ai:knowledge:create, ai:knowledge:edit, ai:knowledge:delete | AIåè½æé |
| MaxKB | maxkb:kb:list, maxkb:kb:create, maxkb:doc:upload, maxkb:doc:search, maxkb:doc:delete, maxkb:agent:list, maxkb:agent:chat, maxkb:agent:delete, maxkb:sync | MaxKBæé |
| ä¼ä¸å¾®ä¿¡ | wecom:message, wecom:sync | ä¼ä¸å¾®ä¿¡æé |
| ç³»ç»éæ | integration:plm, integration:u8, integration:mes | ç³»ç»éææé |
| ç»è®¡åæ | statistics:process, statistics:time, statistics:equipment, statistics:export | ç»è®¡åææé |
| ç³»ç»è®¾ç½® | setting:list, setting:edit, dictionary:list, log:list, log:login | ç³»ç»è®¾ç½®æé |

### B. æ°æ®åºè¡¨ç»ææ¦è§

| è¡¨å | è¯´æ | ä¸»è¦å­æ®µ |
|------|------|---------|
| users | ç¨æ·è¡?| id, username, password, real_name, email, phone, dept_id, status |
| roles | è§è²è¡?| id, name, code, description, status |
| permissions | æéè¡?| id, name, code, module, type, parent_id, sort |
| user_roles | ç¨æ·è§è²å³èè¡?| user_id, role_id |
| role_permissions | è§è²æéå³èè¡?| role_id, permission_id |
| departments | é¨é¨è¡?| id, name, code, parent_id, sort, manager_id, status |
| products | äº§åè¡?| id, code, name, category, model, description, status |
| product_versions | äº§åçæ¬è¡?| id, product_id, version, status, change_description |
| materials | ç©æè¡?| id, code, name, category, material_type, specification, unit, material_grade |
| product_bom | äº§åBOMè¡?| id, product_id, parent_id, material_id, quantity, unit, level |
| process_routes | å·¥èºè·¯çº¿è¡?| id, code, name, product_id, material_id, type, status, version |
| operations | å·¥åºè¡?| id, route_id, seq, name, operation_code, work_center_id, equipment_id |
| steps | å·¥æ­¥è¡?| id, operation_id, seq, name, description, parameters |
| cards | å·¥èºå¡çè¡?| id, code, name, route_id, type, status, version |
| equipment | è®¾å¤è¡?| id, code, name, type, model, manufacturer, workshop_id, status |
| fixtures | å·¥è£å¤¹å·è¡?| id, code, name, type, drawing_no, status |
| tools | åå·è¡¨ | id, code, name, type, specification, material, brand, status |
| measuring_tools | éå·è¡?| id, code, name, type, specification, precision, calibration_date, status |
| time_quotas | å·¥æ¶å®é¢è¡?| id, route_id, operation_id, setup_time, process_time, total_time |
| templates | æ¨¡æ¿è¡?| id, name, code, type, category, content, status |
| workflows | æµç¨å®ä¹è¡?| id, name, code, type, description, status |
| workflow_nodes | æµç¨èç¹è¡?| id, workflow_id, seq, name, type, role, timeout_hours |
| approvals | å®¡æ¹è¡?| id, workflow_id, title, target_type, target_id, status, initiator_id |
| approval_records | å®¡æ¹è®°å½è¡?| id, approval_id, node_seq, operator_id, action, comment |
| documents | ææ¡£è¡?| id, name, code, type, category, file_size, file_type, version, storage_path |
| document_versions | ææ¡£çæ¬è¡?| id, document_id, version, file_size, change_description |
| ai_sessions | AIä¼è¯è¡?| id, user_id, title, message_count |
| ai_messages | AIæ¶æ¯è¡?| id, session_id, role, content, created_at |
| ai_knowledge | AIç¥è¯åºè¡¨ | id, title, category, type, content, tags, status |
| settings | ç³»ç»åæ°è¡?| id, key, value, group, type, description |
| dictionaries | æ°æ®å­å¸è¡?| id, type, value, label, sort |
| logs | æä½æ¥å¿è¡?| id, user_id, module, action, target_id, target_name, ip, created_at |
| login_logs | ç»å½æ¥å¿è¡?| id, user_id, username, ip, user_agent, status, created_at |

---

> **ææ¡£çæ¬**: v1.0.0  
> **æåæ´æ?*: 2026-06-07  
> **ç»´æ¤äººå**: ç³»ç»æ¶æç»?
