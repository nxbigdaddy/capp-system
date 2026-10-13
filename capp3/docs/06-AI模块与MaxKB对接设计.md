# 06-AI模块与MaxKB对接设计

## 目录

1. [概述](#1-概述)
2. [AI助手功能设计](#2-ai助手功能设计)
3. [AI决策支持](#3-ai决策支持)
4. [MaxKB专业版知识库对接设计](#4-maxkb专业版知识库对接设计)
5. [MaxKB专业版智能体对接设计](#5-maxkb专业版智能体对接设计)
6. [AI知识库](#6-ai知识库)
7. [AI模型接入](#7-ai模型接入)
8. [AI对话管理](#8-ai对话管理)
9. [AI与业务系统集成](#9-ai与业务系统集成)
10. [AI权限与安全](#10-ai权限与安全)
11. [AI训练数据管理](#11-ai训练数据管理)
12. [数据表设计](#12-数据表设计)
13. [接口清单](#13-接口清单)
14. [附录](#14-附录)

---

## 1. 概述

### 1.1 设计目标

本文档为CAPP（计算机辅助工艺规划）系统的AI模块与MaxKB专业版对接提供完整的技术设计方案。通过引入大语言模型（LLM）能力与知识库管理技术，实现以下核心目标：

- **智能工艺问答**：基于自然语言交互，快速检索工艺规范、技术标准和历史工艺数据，降低工艺人员信息检索成本。
- **AI决策支持**：利用AI分析能力为工艺优化、生产排产、质量预警提供数据驱动的决策建议。
- **知识库统一管理**：通过MaxKB专业版构建企业工艺知识库，实现工艺文档的智能化管理、索引与检索。
- **智能体工作流集成**：将MaxKB智能体嵌入CAPP业务流程（如工艺审批），实现AI辅助决策节点。
- **数据安全与隔离**：确保工艺数据在AI处理过程中的安全性，实现严格的权限控制与数据脱敏。

### 1.2 适用范围

本设计适用于工业压缩机、压力容器研发设计生产企业的CAPP系统AI能力建设，覆盖工艺设计辅助、工艺知识管理、智能问答、决策分析等业务场景。

### 1.3 技术约束

- **后端技术**：PHP原生开发，不依赖任何框架。
- **数据库**：MySQL 5.7+。
- **AI知识库平台**：MaxKB专业版（本地部署或私有化部署）。
- **大语言模型**：支持OpenAI API、国产大模型API（如文心一言、通义千问、智谱AI）及本地私有化部署模型（如ChatGLM、Llama2/3等）。
- **部署环境**：支持内网私有化部署，确保工艺数据不出域。

### 1.4 术语定义

| 术语 | 说明 |
|------|------|
| LLM | Large Language Model，大语言模型 |
| RAG | Retrieval-Augmented Generation，检索增强生成 |
| MaxKB | 基于LLM的知识库问答系统，支持知识库管理、文档检索、智能对话 |
| Embedding | 文本向量化表示，用于语义检索 |
| Vector DB | 向量数据库，存储文本Embedding向量 |
| Agent | 智能体，具备自主决策和工具调用能力的AI实体 |
| Prompt | 提示词，引导大语言模型生成特定输出的输入文本 |
| Token | 大语言模型处理文本的最小单位 |
| Rerank | 重排序，对初步检索结果进行精排优化 |

---

## 2. AI助手功能设计

### 2.1 功能架构

AI助手作为CAPP系统的统一智能交互入口，提供以下核心能力：

```
┌─────────────────────────────────────────────┐
│              CAPP AI 助手入口                │
│  (Web端悬浮窗 / 移动端独立页 / 工艺编辑页嵌入) │
└─────────────────────────────────────────────┘
                      │
        ┌─────────────┼─────────────┐
        ▼             ▼             ▼
   ┌─────────┐  ┌──────────┐  ┌──────────┐
   │工艺查询  │  │数据查询  │  │智能问答  │
   │助手     │  │助手      │  │助手      │
   └─────────┘  └──────────┘  └──────────┘
        │             │             │
        └─────────────┼─────────────┘
                      ▼
           ┌─────────────────────┐
           │   意图识别与路由     │
           │  (Intent Router)    │
           └─────────────────────┘
                      │
        ┌─────────────┼─────────────┐
        ▼             ▼             ▼
   ┌─────────┐  ┌──────────┐  ┌──────────┐
   │MaxKB    │  │CAPP业务  │  │LLM通用   │
   │知识库   │  │数据库    │  │对话能力  │
   └─────────┘  └──────────┘  └──────────┘
```

### 2.2 自然语言交互设计

#### 2.2.1 交互入口

AI助手在CAPP系统中提供多入口访问：

1. **Web端全局悬浮窗**：系统右下角固定悬浮AI助手图标，点击后展开对话窗口，支持拖拽调整位置。
2. **移动端独立页面**：企业微信CAPP应用内底部导航栏"AI助手"Tab页，全屏对话界面。
3. **工艺编辑页嵌入**：在工艺文件编辑页面右侧提供"AI辅助"侧边栏，支持选中内容后右键唤起AI助手进行解释、优化建议。
4. **搜索框智能提示**：系统顶部全局搜索框支持自然语言输入，当检测到问句时自动路由至AI助手处理。

#### 2.2.2 多轮对话设计

AI助手支持上下文感知的多轮对话：

- **会话上下文维护**：每次对话携带最近5轮（10条）历史消息作为上下文，超出部分由AI自动总结为摘要后携带。
- **会话隔离**：不同用户的会话相互隔离，同一用户可同时开启多个独立会话主题（如"工业装配工艺咨询"、"排产优化分析"）。
- **上下文清除**：用户可主动点击"清除上下文"按钮重置当前会话，或系统检测到话题切换时自动建议开启新会话。
- **会话持久化**：所有对话记录保存至数据库，用户可随时查看历史会话列表并继续对话。

#### 2.2.3 意图识别与路由

AI助手接收到用户输入后，首先进行意图识别，将请求路由至最合适的处理模块：

| 意图类型 | 识别关键词/模式 | 路由目标 | 示例 |
|----------|----------------|----------|------|
| 工艺知识查询 | "工艺规范"、"怎么做"、"标准是什么" | MaxKB知识库 | "工业压缩机膜片装配的工艺规范是什么？" |
| 业务数据查询 | "查询"、"统计"、"有多少"、"列表" | CAPP业务数据库 | "查询我本月提交的工艺文件数量" |
| 工艺优化建议 | "优化"、"建议"、"改进"、"提升" | LLM + 业务数据 | "如何优化工业压缩机的装配工序？" |
| 系统操作辅助 | "怎么操作"、"如何使用"、"功能在哪" | 系统帮助文档 | "如何在系统中发起工艺变更审批？" |
| 通用对话 | 其他未识别输入 | LLM通用对话 | "你好"、"谢谢" |

**意图识别实现方式**：

1. **规则匹配层**：基于关键词和正则表达式的快速匹配，处理高置信度场景。
2. **LLM分类层**：对于规则未覆盖的输入，调用轻量级LLM（或本地小模型）进行意图分类，Prompt示例：

```
请判断以下用户输入的意图类型，仅返回JSON格式：
输入：{user_input}
可选类型：process_query（工艺知识查询）、data_query（业务数据查询）、optimization（优化建议）、help（系统帮助）、chat（通用对话）
输出格式：{"intent": "类型", "confidence": 0-1之间的置信度}
```

### 2.3 工艺查询助手

#### 2.3.1 功能描述

工艺查询助手面向工艺设计人员，提供基于自然语言的工艺知识检索服务。用户可以用日常语言描述需求，AI助手从MaxKB知识库和CAPP历史工艺数据中检索最相关的信息并生成回答。

#### 2.3.2 查询场景示例

| 场景 | 用户输入 | AI处理流程 | 输出示例 |
|------|----------|------------|----------|
| 工艺规范查询 | "工业压缩机膜片安装扭矩要求" | 检索MaxKB知识库中的装配工艺规范文档 | 返回扭矩值、工具要求、检验标准 |
| 相似工艺推荐 | "查找与GY-2023-0156相似的工业压缩机工艺" | 检索CAPP历史工艺库，基于工艺内容相似度匹配 | 返回Top5相似工艺文件编号、名称、相似度得分 |
| 材料替代查询 | "304不锈钢能否替代316L用于工业压缩机阀体" | 检索材料规范知识库 + 历史变更记录 | 返回材料性能对比、适用性分析、历史替代案例 |
| 工序说明 | "解释工业压缩机三级压缩的装配顺序" | 检索工艺规程文档，提取相关工序 | 返回分步骤说明、关键控制点、注意事项 |

#### 2.3.3 检索增强生成（RAG）流程

```
用户输入 → 意图识别(工艺查询) → 查询改写/扩展 → 向量检索(MaxKB) + 关键词检索(CAPP)
                ↓
         结果融合与重排序(Rerank)
                ↓
         构建Prompt(系统提示+检索结果+用户问题)
                ↓
         调用LLM生成回答
                ↓
         后处理(格式美化、引用标注、敏感词过滤)
                ↓
         返回给用户
```

**查询改写示例**：

用户原始输入："膜片怎么装"
改写后查询："工业压缩机膜片装配工艺 安装步骤 扭矩要求 检验标准"

### 2.4 数据查询助手

#### 2.4.1 功能描述

数据查询助手允许用户通过自然语言查询CAPP系统中的业务数据，无需掌握SQL或熟悉系统报表功能。

#### 2.4.2 技术实现方案

采用"Text-to-SQL"技术路线：

1. **Schema预处理**：将CAPP系统核心数据表的表结构、字段含义、关联关系整理为LLM可理解的Schema描述文档。
2. **Prompt工程**：构建包含Schema描述、示例SQL、安全约束的Prompt模板。
3. **SQL生成**：调用LLM根据用户自然语言输入生成SQL语句。
4. **SQL校验**：执行前进行安全校验（禁止DROP/DELETE/TRUNCATE等危险操作，限制查询范围）。
5. **结果格式化**：将SQL查询结果转换为自然语言回答或表格/图表形式展示。

**Prompt模板示例**：

```
你是一名精通MySQL的数据库专家。以下是CAPP系统的数据库Schema：

[表结构描述]
表名：process_file（工艺文件表）
字段：id(主键), file_no(工艺文件编号), file_name(工艺名称), category(工艺类别), creator_id(创建人ID), create_time(创建时间), status(状态:1草稿2审批中3已发布4已作废)

表名：process_task（工艺任务表）
字段：id, task_no(任务编号), file_id(关联工艺文件ID), assignee_id(执行人ID), deadline(截止日期), status(状态:1待执行2执行中3已完成4已逾期)

[示例]
用户：查询本月完成的工艺任务数量
SQL：SELECT COUNT(*) FROM process_task WHERE status=3 AND DATE_FORMAT(finish_time,'%Y-%m')=DATE_FORMAT(NOW(),'%Y-%m')

[安全约束]
- 仅允许SELECT查询
- 不允许查询密码、Token等敏感字段
- 涉及多表关联时必须使用JOIN而非子查询（性能考虑）

请根据以下用户输入生成SQL，仅返回SQL语句，不要解释：
用户输入：{user_input}
```

#### 2.4.3 查询结果展示

- **单值结果**：直接以自然语言回答，如"本月共完成工艺任务 15 项"。
- **列表结果**：以表格形式展示，支持前端分页和排序。
- **统计结果**：自动生成柱状图/饼图/折线图（集成前端图表库）。
- **异常提示**：当查询结果为空或SQL执行错误时，AI主动提示可能原因并建议修正查询条件。

---

## 3. AI决策支持

### 3.1 工艺优化建议

#### 3.1.1 功能描述

基于历史工艺数据、质量反馈数据和行业最佳实践，AI为工艺设计人员提供工艺路线、参数设置、工序安排的优化建议。

#### 3.1.2 数据来源

| 数据类型 | 来源表/系统 | 用途 |
|----------|-------------|------|
| 历史工艺文件 | CAPP process_file / process_detail | 分析相似产品的工艺路线 |
| 质量检验记录 | QMS系统 / quality_record | 识别高频质量问题关联的工序 |
| 工时数据 | ERP系统 / work_hour_record | 分析工序耗时，识别瓶颈 |
| 设备参数 | MES系统 / equipment_param | 评估设备能力与工艺参数匹配度 |
| 行业知识 | MaxKB知识库 / 外部标准文档 | 提供行业最佳实践参考 |

#### 3.1.3 优化建议生成流程

1. **工艺特征提取**：解析当前工艺文件，提取产品类型、材料、关键工序、工艺参数等特征。
2. **相似工艺检索**：在CAPP历史库中检索相似产品的工艺文件，对比工艺路线差异。
3. **质量问题关联**：查询质量系统中与该类产品相关的历史质量问题，定位高风险工序。
4. **知识库检索**：从MaxKB检索该类产品的工艺优化案例和行业标准。
5. **综合生成建议**：将上述信息组织为结构化Prompt，调用LLM生成优化建议报告。

**输出格式示例**：

```markdown
## 工艺优化建议报告
**工艺文件**：GY-2024-0089（工业压缩机三级压缩装配工艺）

### 1. 工艺路线优化建议
- 建议将"膜片预压检测"工序前置至"膜片安装"之前，历史数据显示该调整可降低返工率12%。
- 参考工艺GY-2023-0156，建议增加"气密性预检"工序。

### 2. 参数优化建议
- 当前膜片安装扭矩设定为45N·m，根据同系列产品统计，建议调整为42-48N·m区间，并增加扭矩校验记录。

### 3. 风险预警
- 工序12"阀体研磨"在历史质量记录中关联3次表面粗糙度不合格，建议增加研磨后100%检验。

### 4. 工时优化
- 当前总工时预估为32小时，参考相似工艺平均工时28小时，建议重点优化"管路焊接"工序（当前6小时，行业平均4小时）。
```

### 3.2 排产建议

#### 3.2.1 功能描述

结合订单需求、设备产能、物料齐套情况、工艺路线复杂度，AI为生产计划人员提供智能排产建议。

#### 3.2.2 输入数据

- 订单信息：产品型号、数量、交付日期、优先级
- 工艺路线：工序顺序、标准工时、所需设备/工装
- 资源状态：设备可用时间、人员技能矩阵、物料库存
- 约束条件：设备独占性、工序前后置关系、最小批量

#### 3.2.3 排产建议输出

AI不直接生成最终排产计划（避免替代ERP/MES核心功能），而是提供建议性分析：

- **瓶颈识别**：指出当前排产方案中的资源瓶颈工序及预计等待时间。
- **优先级建议**：基于交付紧急度和工艺复杂度，建议订单加工优先级调整。
- **风险预警**：识别可能延期交付的订单及原因（如物料不齐套、关键设备占用）。
- **优化方案**：提供2-3种备选排产策略及各自的预期交付时间、资源利用率对比。

### 3.3 质量预警

#### 3.3.1 功能描述

基于工艺参数、历史质量数据和实时检测数据，AI识别潜在质量风险并提前预警。

#### 3.3.2 预警场景

| 预警类型 | 触发条件 | 预警内容 | 响应动作 |
|----------|----------|----------|----------|
| 参数偏离预警 | 工艺执行参数超出历史合格区间 | 具体参数名称、当前值、建议范围 | 推送消息至工艺员和质量工程师 |
| 工序风险预警 | 当前工序在历史记录中不合格率>5% | 工序名称、历史不合格率、常见缺陷模式 | 建议增加检验频次或调整工艺参数 |
| 供应商材料预警 | 某批次材料历史关联质量问题 | 材料批次、供应商、历史问题描述 | 建议加强来料检验或启动供应商评审 |
| 环境条件预警 | 温湿度等环境参数超出工艺要求 | 环境参数、影响工序、建议措施 | 推送至车间管理人员 |

---

## 4. MaxKB专业版知识库对接设计

### 4.1 MaxKB系统简介与架构

#### 4.1.1 系统定位

MaxKB（Max Knowledge Base）是一款基于大语言模型的开源知识库问答系统，支持用户上传文档、自动构建知识库、进行智能问答。MaxKB专业版在开源版基础上增加了更多企业级特性，包括：

- 多知识库管理
- 高级文档解析（支持PDF、Word、Excel、PPT、TXT、Markdown等）
- 分段与索引策略配置
- 多模型接入与管理
- API开放接口
- 用户权限与数据隔离
- 对话记录与分析

#### 4.1.2 系统架构

```
┌─────────────────────────────────────────────┐
│              MaxKB 专业版                     │
├─────────────────────────────────────────────┤
│  Web UI层  │  知识库管理  │  文档管理  │  对话界面  │
├─────────────────────────────────────────────┤
│  API网关层  │  RESTful API  │  认证鉴权  │  限流控制  │
├─────────────────────────────────────────────┤
│  核心服务层  │  文档解析  │  文本分段  │  Embedding  │  检索引擎  │
├─────────────────────────────────────────────┤
│  模型接入层  │  OpenAI API  │  国产模型API  │  本地模型  │
├─────────────────────────────────────────────┤
│  数据存储层  │  MySQL(元数据)  │  Vector DB(向量)  │  文件存储  │
└─────────────────────────────────────────────┘
```

### 4.2 知识库同步机制

#### 4.2.1 同步架构设计

CAPP系统与MaxKB之间的知识库同步采用"CAPP主动推送 + MaxKB被动接收"的模式：

```
CAPP系统                          MaxKB专业版
├─ 工艺文件库                       ├─ 工艺知识库
├─ 技术规范文档                     ├─ 技术规范知识库
├─ 质量标准文件      ──API推送──>    ├─ 质量标准知识库
├─ 设备操作手册                     ├─ 设备知识库
├─ 培训教材                         ├─ 培训知识库
└─ 问题案例库                       └─ 案例知识库
```

#### 4.2.2 文档上传与解析

**同步流程**：

1. **文档筛选**：CAPP系统根据文档类型和状态，筛选需要同步至MaxKB的文档（如已发布的工艺文件、现行有效的技术规范）。
2. **文档转换**：将CAPP系统中的工艺文件（可能存储为结构化数据库记录或特定格式文件）转换为MaxKB支持的格式（PDF、Word、Markdown等）。
3. **元数据封装**：为每个文档附加元数据，包括文档编号、版本、生效日期、所属分类、密级等。
4. **API上传**：调用MaxKB文档上传API，将文档及元数据推送至对应知识库。
5. **解析与分段**：MaxKB接收文档后，自动进行文档解析、文本提取、智能分段。
6. **索引构建**：MaxKB将分段后的文本生成Embedding向量，存入向量数据库，构建可检索索引。
7. **同步状态回写**：MaxKB返回文档处理状态（解析中、索引中、已完成、失败），CAPP系统更新本地同步记录。

**PHP同步任务实现**：

```php
class MaxKbSyncService {
    private $apiBaseUrl;
    private $apiKey;
    
    public function __construct() {
        $this->apiBaseUrl = MAXKB_API_URL; // 如 http://maxkb.company.com/api
        $this->apiKey = YOUR_SECRET;
    }
    
    /**
     * 同步单个文档至MaxKB
     */
    public function syncDocument($docId, $knowledgeBaseId) {
        $db = getDbInstance();
        
        // 1. 获取CAPP文档信息
        $doc = $db->query("SELECT * FROM process_file WHERE id = ? AND status = 3", [$docId]);
        if (empty($doc)) {
            throw new Exception('文档不存在或未发布');
        }
        $doc = $doc[0];
        
        // 2. 生成Markdown格式文档内容
        $content = $this->convertProcessToMarkdown($doc);
        $fileName = $doc['file_no'] . '_' . $doc['file_name'] . '.md';
        $tempFile = sys_get_temp_dir() . '/' . $fileName;
        file_put_contents($tempFile, $content);
        
        // 3. 调用MaxKB上传API
        $url = "{$this->apiBaseUrl}/dataset/{$knowledgeBaseId}/document";
        $postData = [
            'file' => new CURLFile($tempFile, 'text/markdown', $fileName),
            'meta' => json_encode([
                'source_system' => 'CAPP',
                'doc_id' => $docId,
                'file_no' => $doc['file_no'],
                'version' => $doc['version'],
                'category' => $doc['category'],
                'security_level' => $doc['security_level'],
                'effective_date' => $doc['effective_date']
            ])
        ];
        
        $response = $this->httpPost($url, $postData, true);
        $result = json_decode($response, true);
        
        // 4. 清理临时文件
        unlink($tempFile);
        
        // 5. 记录同步状态
        if ($result['code'] == 200) {
            $db->execute("INSERT INTO maxkb_sync_record 
                (capp_doc_id, maxkb_doc_id, knowledge_base_id, sync_type, status, sync_time, response) 
                VALUES (?, ?, ?, 'upload', 'success', NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                maxkb_doc_id=?, status='success', sync_time=NOW(), response=?",
                [$docId, $result['data']['id'], $knowledgeBaseId, $response, 
                 $result['data']['id'], $response]);
            return $result['data']['id'];
        } else {
            $db->execute("INSERT INTO maxkb_sync_record 
                (capp_doc_id, knowledge_base_id, sync_type, status, sync_time, error_msg) 
                VALUES (?, ?, 'upload', 'failed', NOW(), ?)
                ON DUPLICATE KEY UPDATE status='failed', sync_time=NOW(), error_msg=?",
                [$docId, $knowledgeBaseId, $result['message'] ?? '未知错误', $result['message'] ?? '未知错误']);
            throw new Exception('MaxKB文档上传失败: ' . ($result['message'] ?? '未知错误'));
        }
    }
    
    /**
     * 将工艺文件转换为Markdown
     */
    private function convertProcessToMarkdown($doc) {
        $db = getDbInstance();
        $details = $db->query("SELECT * FROM process_detail WHERE file_id = ? ORDER BY seq_no", [$doc['id']]);
        
        $md = "# {$doc['file_name']}\n\n";
        $md .= "**文件编号**：{$doc['file_no']}  \n";
        $md .= "**版本**：{$doc['version']}  \n";
        $md .= "**产品型号**：{$doc['product_model']}  \n";
        $md .= "**编制日期**：{$doc['create_time']}  \n\n";
        $md .= "---\n\n";
        
        foreach ($details as $step) {
            $md .= "## 工序{$step['seq_no']}：{$step['step_name']}\n\n";
            $md .= "**工序内容**：\n{$step['content']}\n\n";
            $md .= "**设备/工装**：{$step['equipment']}\n\n";
            $md .= "**工艺参数**：{$step['parameters']}\n\n";
            $md .= "**质量要求**：{$step['quality_requirement']}\n\n";
            $md .= "**检验方法**：{$step['inspect_method']}\n\n";
            $md .= "---\n\n";
        }
        
        return $md;
    }
}
```

#### 4.2.3 分段策略配置

针对工艺文档的特点，在MaxKB中配置专门的分段策略：

| 配置项 | 建议值 | 说明 |
|--------|--------|------|
| 分段方式 | 语义分段 + 固定长度重叠 | 保证工序内容的完整性 |
| 分段长度 | 800-1200字符 | 覆盖一个完整工序的描述 |
| 重叠长度 | 200字符 | 保证跨段上下文连贯 |
| 分段标识 | 二级标题（##）优先 | 以工序标题作为分段边界 |
| 元数据保留 | 开启 | 保留文件编号、版本等元数据用于过滤 |

#### 4.2.4 增量更新策略

1. **版本变更检测**：CAPP系统中工艺文件版本升级时（如从V1.0升至V2.0），自动触发MaxKB知识库更新。
2. **差异同步**：对于已同步的文档，CAPP系统记录文档内容哈希值。当哈希值变化时，调用MaxKB更新API替换旧版本文档，并重新索引。
3. **失效文档处理**：CAPP系统中工艺文件作废时，同步调用MaxKB删除API将该文档从知识库移除，或标记为"已失效"（根据业务需求）。
4. **定时全量校验**：每周日凌晨执行全量校验任务，比对CAPP系统与MaxKB知识库的文档清单，发现遗漏或差异时自动修复。

### 4.3 MaxKB API对接

#### 4.3.1 API认证方式

MaxKB专业版API采用API Key认证，CAPP系统在请求Header中携带：

```
Authorization: Bearer {MAXKB_API_KEY}
Content-Type: application/json
```

#### 4.3.2 核心API清单

| API功能 | 请求方法 | 路径 | 说明 |
|---------|----------|------|------|
| 知识库列表 | GET | /dataset | 获取MaxKB中所有知识库 |
| 创建知识库 | POST | /dataset | 在MaxKB中创建新知识库 |
| 上传文档 | POST | /dataset/{id}/document | 向指定知识库上传文档 |
| 更新文档 | PUT | /dataset/{id}/document/{docId} | 更新已有文档 |
| 删除文档 | DELETE | /dataset/{id}/document/{docId} | 删除文档 |
| 文档列表 | GET | /dataset/{id}/document | 获取知识库文档列表 |
| 知识检索 | POST | /dataset/{id}/hit_test | 测试知识库检索效果 |
| 发起对话 | POST | /chat/{chatId}/chat | 向指定应用发起对话 |
| 获取对话历史 | GET | /chat/{chatId}/log | 获取对话记录 |
| 应用列表 | GET | /application | 获取所有AI应用 |

#### 4.3.3 知识检索API对接

CAPP系统可直接调用MaxKB的检索API，获取与用户问题相关的知识片段，再自行组织Prompt调用LLM生成回答。这种方式适合需要与CAPP业务数据融合的场景。

**检索请求示例**：

```php
function searchKnowledgeBase($knowledgeBaseId, $query, $topK = 5) {
    $url = MAXKB_API_URL . "/dataset/{$knowledgeBaseId}/hit_test";
    
    $data = [
        'query_text' => $query,
        'top_number' => $topK,
        'similarity' => 0.7, // 相似度阈值
        'search_mode' => 'embedding', // embedding/keywords/blend
        'rerank' => true // 启用重排序
    ];
    
    $response = $this->httpPost($url, json_encode($data));
    $result = json_decode($response, true);
    
    if ($result['code'] == 200) {
        return $result['data']; // 返回检索到的段落列表
    }
    
    return [];
}
```

**检索结果处理**：

```php
function buildRagPrompt($userQuery, $searchResults) {
    $context = "";
    $references = [];
    
    foreach ($searchResults as $index => $item) {
        $context .= "[参考文档" . ($index + 1) . "]\n";
        $context .= "内容：" . $item['content'] . "\n";
        $context .= "来源：" . ($item['meta']['file_no'] ?? '未知') . "\n\n";
        $references[] = [
            'file_no' => $item['meta']['file_no'] ?? '',
            'title' => $item['meta']['title'] ?? '',
            'similarity' => $item['similarity'] ?? 0
        ];
    }
    
    $prompt = "你是一名工业压缩机工艺专家。请基于以下参考文档回答用户问题。如果参考文档不足以回答问题，请明确说明。\n\n";
    $prompt .= $context;
    $prompt .= "\n用户问题：{$userQuery}\n\n";
    $prompt .= "请用中文回答，并在回答末尾列出引用的文档编号。";
    
    return ['prompt' => $prompt, 'references' => $references];
}
```

#### 4.3.4 对话API对接

对于简单的问答场景，CAPP系统可直接调用MaxKB的对话API，由MaxKB内部完成检索+生成的完整流程。

**对话请求示例**：

```php
function chatWithMaxKb($chatId, $message, $history = []) {
    $url = MAXKB_API_URL . "/chat/{$chatId}/chat";
    
    $data = [
        'message' => $message,
        're_chat' => false, // 是否重新回答
        'stream' => false   // 是否流式返回（PHP环境建议先使用非流式）
    ];
    
    if (!empty($history)) {
        $data['history'] = $history; // 历史对话记录
    }
    
    $response = $this->httpPost($url, json_encode($data));
    $result = json_decode($response, true);
    
    if ($result['code'] == 200) {
        return [
            'answer' => $result['data']['content'] ?? '',
            'chat_id' => $result['data']['chat_id'] ?? '',
            'execution_details' => $result['data']['execution_details'] ?? []
        ];
    }
    
    throw new Exception('MaxKB对话失败: ' . ($result['message'] ?? '未知错误'));
}
```

### 4.4 数据映射与转换

#### 4.4.1 CAPP工艺数据 → MaxKB知识库格式

| CAPP数据实体 | MaxKB概念 | 映射说明 |
|--------------|-----------|----------|
| 工艺文件（process_file） | 文档（Document） | 一个工艺文件对应MaxKB中的一个文档 |
| 工艺类别（category） | 知识库（Dataset） | 每类工艺对应一个知识库，如"装配工艺库"、"焊接工艺库" |
| 工序内容（process_detail） | 文档段落（Paragraph） | MaxKB自动分段，CAPP提供结构化内容辅助分段 |
| 文件版本（version） | 文档版本 | MaxKB文档更新时覆盖旧版本，CAPP保留历史版本记录 |
| 密级（security_level） | 元数据/访问控制 | 通过MaxKB应用权限或CAPP前置过滤控制访问 |
| 产品型号（product_model） | 元数据 | 用于检索时过滤特定产品相关的工艺 |

#### 4.4.2 元数据设计

每个同步至MaxKB的文档携带以下元数据，用于精细化检索和权限控制：

```json
{
  "source_system": "CAPP",
  "doc_id": 12345,
  "file_no": "GY-2024-0089",
  "file_name": "工业压缩机三级压缩装配工艺",
  "version": "V2.1",
  "category": "装配工艺",
  "sub_category": "工业压缩机",
  "product_model": "GD-100/20",
  "security_level": "内部公开",
  "effective_date": "2024-01-15",
  "expire_date": "2026-01-14",
  "creator_name": "张三",
  "department": "工艺技术部",
  "status": "已发布"
}
```

### 4.5 同步策略与调度

#### 4.5.1 同步触发机制

| 触发方式 | 触发条件 | 处理逻辑 |
|----------|----------|----------|
| 实时同步 | 工艺文件发布/更新/作废 | 事务提交后异步触发同步任务 |
| 定时同步 | 每日凌晨1:00 | 扫描当日有变更的文档，批量同步 |
| 手动同步 | 管理员在后台点击"同步知识库" | 立即执行全量或指定范围同步 |
| 全量重建 | 每月第一个周日 | 重建整个知识库索引，确保一致性 |

#### 4.5.2 同步任务队列

使用Redis列表实现轻量级任务队列，避免同步任务阻塞主业务流程：

```php
// 发布工艺文件后，推送同步任务
function pushSyncTask($docId, $action) {
    $redis = getRedisInstance();
    $task = json_encode([
        'doc_id' => $docId,
        'action' => $action, // upload/update/delete
        'knowledge_base_id' => getKnowledgeBaseIdByDoc($docId),
        'create_time' => date('Y-m-d H:i:s')
    ]);
    $redis->lPush('maxkb:sync:queue', $task);
}

// 定时任务消费队列
function consumeSyncQueue() {
    $redis = getRedisInstance();
    $service = new MaxKbSyncService();
    
    while ($task = $redis->rPop('maxkb:sync:queue')) {
        $task = json_decode($task, true);
        try {
            switch ($task['action']) {
                case 'upload':
                    $service->syncDocument($task['doc_id'], $task['knowledge_base_id']);
                    break;
                case 'update':
                    $service->updateDocument($task['doc_id'], $task['knowledge_base_id']);
                    break;
                case 'delete':
                    $service->deleteDocument($task['doc_id'], $task['knowledge_base_id']);
                    break;
            }
        } catch (Exception $e) {
            // 记录失败日志，进入死信队列
            $redis->lPush('maxkb:sync:dead_queue', json_encode($task));
            logError('MaxKB同步失败: ' . $e->getMessage());
        }
    }
}
```

---

## 5. MaxKB专业版智能体对接设计

### 5.1 智能体配置与调用

#### 5.1.1 智能体概念

MaxKB专业版支持"智能体（Agent）"功能，智能体是在大语言模型基础上，配置特定Prompt、知识库关联、工具调用能力的AI实体。CAPP系统可配置多个专用智能体，分别服务于不同业务场景。

#### 5.1.2 CAPP专用智能体规划

| 智能体名称 | 关联知识库 | 功能定位 | 适用场景 |
|------------|------------|----------|----------|
| 工艺规范助手 | 工艺规范库、标准文件库 | 解答工艺规范、标准条款相关问题 | 工艺设计、工艺审核 |
| 装配工艺专家 | 装配工艺库、设备手册库 | 提供装配工艺指导、故障排查 | 现场装配、工艺培训 |
| 焊接工艺专家 | 焊接工艺库、材料库 | 焊接参数推荐、缺陷分析 | 焊接工序、质量分析 |
| 质量检验助手 | 质量标准库、检验规范库 | 检验方法指导、不合格品分析 | 质量检验、质量问题处理 |
| 排产优化助手 | 工艺路线库、工时库 | 排产建议、产能分析 | 生产计划、调度优化 |

#### 5.1.3 智能体调用方式

CAPP系统通过MaxKB应用API调用特定智能体：

```php
function callMaxKbAgent($agentId, $userMessage, $sessionId = null) {
    $url = MAXKB_API_URL . "/application/{$agentId}/chat";
    
    $data = [
        'question' => $userMessage,
        'chat_id' => $sessionId, // 保持会话连续性
        'stream' => false,
        're_chat' => false
    ];
    
    $response = $this->httpPost($url, json_encode($data));
    $result = json_decode($response, true);
    
    if ($result['code'] == 200) {
        return [
            'answer' => $result['data']['content'] ?? '',
            'session_id' => $result['data']['chat_id'] ?? '',
            'references' => $this->extractReferences($result['data']['paragraph_list'] ?? [])
        ];
    }
    
    throw new Exception('智能体调用失败: ' . ($result['message'] ?? '未知错误'));
}
```

### 5.2 智能体工作流集成

#### 5.2.1 工艺审批流中的AI决策节点

在CAPP系统的工艺审批流程中，嵌入AI辅助决策节点：

```
[提交工艺] → [AI预审节点] → [工艺主管审批] → [技术总工审批] → [发布]
                ↓
         AI自动检查以下内容：
         1. 工艺完整性（必填项是否齐全）
         2. 参数合规性（是否在标准范围内）
         3. 与历史相似工艺的差异对比
         4. 潜在风险识别
         5. 生成预审报告
```

**AI预审节点实现**：

1. **触发时机**：工艺文件提交审批时，系统自动触发AI预审。
2. **数据准备**：提取工艺文件结构化数据（工序、参数、材料、设备），检索MaxKB中的相关规范和历史工艺。
3. **AI分析**：调用"工艺规范助手"智能体，传入工艺数据和检索到的规范，要求AI生成预审意见。
4. **结果处理**：
   - 若AI发现严重问题（如关键参数缺失、明显违反规范），标记为"AI预审不通过"，退回申请人并附AI意见。
   - 若AI发现轻微问题或建议，标记为"AI预审通过（有建议）"，流转至人工审批节点，审批人可见AI建议。
   - 若AI未发现明显问题，标记为"AI预审通过"，正常流转。
5. **人工覆盖**：审批人可查看AI预审报告，但不受AI结论约束，可独立做出审批决定。

**预审Prompt设计**：

```
你是一名经验丰富的工艺审核专家。请对以下工艺文件进行预审，从完整性、合规性、风险性三个维度进行评估。

[工艺文件数据]
文件编号：{file_no}
产品型号：{product_model}
工艺类别：{category}
工序数量：{step_count}
关键参数：{parameters}

[检索到的相关规范]
{retrieved_standards}

[历史相似工艺]
{similar_processes}

请按以下格式输出预审报告：
1. 完整性检查：列出缺失或不完备的项目
2. 合规性检查：列出不符合规范要求的项目
3. 风险识别：指出潜在的工艺风险和质量隐患
4. 改进建议：提供具体的优化建议
5. 总体结论：通过/建议修改/不通过
```

#### 5.2.2 质量问题处理中的AI辅助

当生产现场出现质量问题时，质量人员可通过CAPP系统发起"AI质量分析"：

1. **问题描述录入**：质量人员描述问题现象、发生工序、产品批次。
2. **AI关联分析**：系统自动检索MaxKB中的质量问题案例库和工艺规范，调用"质量检验助手"智能体分析可能原因。
3. **根因推荐**：AI返回可能的原因列表（按概率排序），并关联推荐处置措施。
4. **专家确认**：质量工程师确认或修正AI分析结果，形成最终质量分析报告。
5. **知识沉淀**：确认后的质量分析报告自动同步至MaxKB质量问题案例库，形成知识闭环。

### 5.3 智能体与CAPP业务系统联动

#### 5.3.1 工具调用设计（Function Calling）

MaxKB智能体支持配置外部工具调用，CAPP系统开放以下API供智能体调用：

| 工具名称 | 功能 | 输入参数 | 返回结果 |
|----------|------|----------|----------|
| query_process_by_no | 按编号查询工艺文件 | file_no | 工艺文件基本信息 |
| query_process_detail | 查询工艺工序详情 | file_id | 工序列表及参数 |
| query_quality_record | 查询质量记录 | product_model, date_range | 质量检验记录列表 |
| query_work_hour | 查询工时数据 | file_id, step_id | 标准工时、实际工时 |
| query_material_stock | 查询物料库存 | material_code | 库存数量、可用批次 |
| query_equipment_status | 查询设备状态 | equipment_code | 设备运行状态、维护计划 |

**工具API实现示例**：

```php
// CAPP系统提供给MaxKB智能体调用的内部API
class AiToolApi {
    
    /**
     * 按编号查询工艺文件
     */
    public function queryProcessByNo($params) {
        $fileNo = $params['file_no'] ?? '';
        if (empty($fileNo)) {
            return ['error' => '缺少file_no参数'];
        }
        
        $db = getDbInstance();
        $result = $db->query("SELECT id, file_no, file_name, product_model, category, version, status, create_time 
                              FROM process_file WHERE file_no = ? AND status = 3", [$fileNo]);
        
        if (empty($result)) {
            return ['error' => '未找到工艺文件'];
        }
        
        return ['data' => $result[0]];
    }
    
    /**
     * 查询质量记录
     */
    public function queryQualityRecord($params) {
        $productModel = $params['product_model'] ?? '';
        $dateRange = $params['date_range'] ?? '';
        
        $db = getDbInstance();
        $sql = "SELECT * FROM quality_record WHERE 1=1";
        $bindParams = [];
        
        if ($productModel) {
            $sql .= " AND product_model = ?";
            $bindParams[] = $productModel;
        }
        if ($dateRange) {
            $dates = explode(',', $dateRange);
            if (count($dates) == 2) {
                $sql .= " AND inspect_date BETWEEN ? AND ?";
                $bindParams[] = $dates[0];
                $bindParams[] = $dates[1];
            }
        }
        
        $sql .= " ORDER BY inspect_date DESC LIMIT 50";
        $result = $db->query($sql, $bindParams);
        
        return ['data' => $result, 'count' => count($result)];
    }
}
```

#### 5.3.2 联动流程示例

**场景**：用户询问"GD-100/20型号工业压缩机最近三个月有哪些质量问题？"

```
用户提问
    ↓
MaxKB智能体接收问题
    ↓
意图识别：需要查询CAPP业务数据
    ↓
智能体生成工具调用：query_quality_record
参数：{"product_model": "GD-100/20", "date_range": "2024-01-01,2024-03-31"}
    ↓
调用CAPP系统工具API
    ↓
CAPP返回质量记录数据
    ↓
智能体结合数据生成自然语言回答
    ↓
返回给用户
```

### 5.4 智能体权限与数据隔离

#### 5.4.1 用户级权限控制

不同角色的CAPP用户访问智能体时，应看到不同的知识范围和拥有不同的工具权限：

| 用户角色 | 可访问知识库 | 可调用工具 | 特殊限制 |
|----------|--------------|------------|----------|
| 工艺员 | 本部门工艺库、通用规范库 | query_process_by_no, query_process_detail | 只能查询本部门工艺 |
| 工艺主管 | 全部工艺库、标准库 | 全部查询工具 | 可查看全部工艺 |
| 质量工程师 | 质量标准库、问题案例库 | query_quality_record, query_process_by_no | 可查看质量问题相关工艺 |
| 生产计划员 | 工艺路线库、工时库 | query_work_hour, query_equipment_status, query_material_stock | 不可查看详细工艺参数 |
| 系统管理员 | 全部知识库 | 全部工具 | 无限制 |

#### 5.4.2 实现方案

1. **CAPP侧权限过滤**：在调用MaxKB API前，CAPP系统根据当前用户角色，在检索请求中附加过滤条件（如`department_id`、`security_level`等元数据过滤），确保检索结果已按权限过滤。
2. **工具调用鉴权**：CAPP系统的工具API在接收到MaxKB智能体调用时，校验调用Token对应的用户权限，拒绝越权查询。
3. **敏感数据脱敏**：智能体返回的结果中如涉及敏感工艺参数（如特殊材料配比、核心加工参数），根据用户权限决定是否脱敏展示。

---

## 6. AI知识库

### 6.1 知识库分类体系

CAPP系统AI知识库按以下体系组织：

```
工艺知识库
├── 工业压缩机工艺库
│   ├── 装配工艺
│   ├── 焊接工艺
│   ├── 加工工艺
│   └── 检验工艺
├── 压力容器工艺库
│   ├── 成型工艺
│   ├── 焊接工艺
│   ├── 热处理工艺
│   └── 无损检测工艺
├── 通用工艺规范库
│   ├── 机械加工通用规范
│   ├── 焊接通用规范
│   ├── 装配通用规范
│   └── 检验通用规范
├── 设备操作手册库
│   ├── CNC加工中心
│   ├── 焊接机器人
│   ├── 三坐标测量机
│   └── 压力试验设备
├── 质量标准库
│   ├── 国家标准（GB）
│   ├── 行业标准（JB/T, NB/T）
│   ├── 企业内控标准
│   └── 客户特殊要求
├── 质量问题案例库
│   ├── 焊接缺陷案例
│   ├── 装配问题案例
│   ├── 材料问题案例
│   └── 尺寸超差案例
└── 培训教材库
    ├── 新员工入职培训
    ├── 工艺技能培训
    ├── 质量意识培训
    └── 安全操作培训
```

### 6.2 知识库内容来源

| 来源类型 | 具体内容 | 同步方式 | 更新频率 |
|----------|----------|----------|----------|
| CAPP系统 | 已发布的工艺文件、工艺卡片 | API自动同步 | 实时/定时 |
| 标准文件系统 | 国家标准、行业标准PDF | 手动上传/批量导入 | 标准更新时 |
| 设备管理系统 | 设备操作手册、维护规程 | 手动上传 | 设备更新时 |
| 质量管理系统 | 质量报告、不合格品处理单 | API自动同步 | 每日 |
| 培训管理系统 | 培训课件、考核题库 | 手动上传 | 培训更新时 |
| 外部采购 | 行业技术论文、专利文献 | 手动上传 | 按需 |

### 6.3 知识检索优化

#### 6.3.1 混合检索策略

采用"向量检索 + 关键词检索 + 元数据过滤"的混合策略：

1. **向量检索（语义匹配）**：将用户问题和知识库文本转换为Embedding向量，通过余弦相似度匹配语义相近的内容，即使关键词不完全一致也能召回相关结果。
2. **关键词检索（精确匹配）**：对于工艺文件编号、标准号、材料牌号等精确标识，使用关键词检索确保精确召回。
3. **元数据过滤**：在检索时附加过滤条件，如`product_model = 'GD-100/20'`、`security_level <= '内部公开'`、`status = '已发布'`，缩小检索范围并保证权限合规。
4. **重排序（Rerank）**：对初步召回的结果使用更精确的交叉编码器模型进行重排序，提升TopK结果的准确性。

#### 6.3.2 检索效果优化措施

- **查询扩展**：对用户输入进行同义词扩展（如"膜片"扩展为"密封件、膜片组件"），提升召回率。
- **HyDE（假设文档嵌入）**：让LLM先根据用户问题生成一个假设的理想回答，再用该回答的Embedding去检索，改善短查询的检索效果。
- **结果去重与合并**：对检索到的相似段落进行去重和语义合并，避免向LLM传入重复内容浪费Token。
- **引用溯源**：在AI回答中标注引用来源（如"根据工艺文件GY-2024-0089"），增强回答可信度，方便用户核实。

---

## 7. AI模型接入

### 7.1 大语言模型API对接

#### 7.1.1 多模型支持架构

CAPP系统AI模块支持接入多种大语言模型，通过统一的模型调用层屏蔽底层差异：

```
┌─────────────────────────────────────────────┐
│           CAPP AI 模型调用层                 │
│         (统一接口、负载均衡、降级)            │
└─────────────────────────────────────────────┘
                      │
        ┌─────────────┼─────────────┐
        ▼             ▼             ▼
   ┌─────────┐  ┌──────────┐  ┌──────────┐
   │OpenAI   │  │国产模型API │  │本地模型  │
   │GPT-4    │  │文心/通义/ │  │ChatGLM  │
   │GPT-3.5  │  │智谱/讯飞  │  │Llama3   │
   └─────────┘  └──────────┘  └──────────┘
```

#### 7.1.2 统一模型调用接口

```php
interface LlmProviderInterface {
    /**
     * 发送对话请求
     * @param array $messages 消息列表，格式：[{"role": "system/user/assistant", "content": "..."}]
     * @param array $options 额外参数，如temperature、max_tokens等
     * @return array 返回结果，包含content、usage、model等字段
     */
    public function chat(array $messages, array $options = []): array;
    
    /**
     * 获取Embedding向量
     * @param string|array $text 文本或文本数组
     * @return array 向量数组
     */
    public function embed($text): array;
    
    /**
     * 检查服务可用性
     * @return bool
     */
    public function healthCheck(): bool;
}
```

#### 7.1.3 OpenAI API对接示例

```php
class OpenAiProvider implements LlmProviderInterface {
    private $apiKey;
    private $baseUrl;
    private $model;
    
    public function __construct($apiKey, $baseUrl = 'https://api.openai.com/v1', $model = 'gpt-4') {
        $this->apiKey = YOUR_SECRET;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->model = $model;
    }
    
    public function chat(array $messages, array $options = []): array {
        $url = "{$this->baseUrl}/chat/completions";
        
        $data = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 4096,
            'stream' => false
        ], $options);
        
        $response = $this->httpPost($url, json_encode($data), [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        $result = json_decode($response, true);
        
        if (isset($result['choices'][0]['message']['content'])) {
            return [
                'content' => $result['choices'][0]['message']['content'],
                'usage' => $result['usage'] ?? [],
                'model' => $result['model'] ?? $this->model,
                'finish_reason' => $result['choices'][0]['finish_reason'] ?? ''
            ];
        }
        
        throw new Exception('OpenAI API调用失败: ' . ($result['error']['message'] ?? '未知错误'));
    }
    
    public function embed($text): array {
        $url = "{$this->baseUrl}/embeddings";
        $input = is_array($text) ? $text : [$text];
        
        $data = [
            'model' => 'text-embedding-ada-002',
            'input' => $input
        ];
        
        $response = $this->httpPost($url, json_encode($data), [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        $result = json_decode($response, true);
        
        if (isset($result['data'])) {
            return array_column($result['data'], 'embedding');
        }
        
        throw new Exception('Embedding获取失败');
    }
    
    public function healthCheck(): bool {
        try {
            $this->chat([['role' => 'user', 'content' => 'Hi']]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function httpPost($url, $data, $headers = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 内网环境可能需要关闭
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
}
```

#### 7.1.4 国产模型适配

针对文心一言、通义千问、智谱AI等国产大模型，分别实现`LlmProviderInterface`接口，主要差异在于：

- **API端点与认证方式**：不同厂商的API地址和认证Header格式不同。
- **请求/响应格式**：部分国产模型在message格式、参数命名上有细微差异。
- **Embedding模型**：国产模型通常提供独立的Embedding接口，需单独适配。

### 7.2 本地模型部署方案

#### 7.2.1 部署场景

对于涉及核心工艺参数、敏感设计数据的场景，优先使用本地私有化部署模型，确保数据不出域。

#### 7.2.2 推荐本地模型

| 模型 | 参数规模 | 适用场景 | 部署方式 |
|------|----------|----------|----------|
| ChatGLM3-6B | 6B | 通用问答、意图识别 | 单卡RTX 3090/4090 |
| Qwen-7B/14B | 7B/14B | 中文工艺领域问答 | 单卡/双卡A10 |
| Llama3-8B-Chinese | 8B | 英文技术文档理解 | 单卡RTX 4090 |
| BGE-Large | 335M | 文本Embedding | CPU即可，GPU加速 |

#### 7.2.3 部署架构

```
CAPP服务器                    AI推理服务器（内网隔离区）
    │                                    │
    │  HTTP API调用（内网）              │
    │  ──────────────────────────────>   │
    │                                    │
    │                            ┌───────┴───────┐
    │                            │  vLLM /      │
    │                            │  Text        │
    │                            │  Generation  │
    │                            │  Inference   │
    │                            │  Server      │
    │                            └───────┬───────┘
    │                                    │
    │                            ┌───────┴───────┐
    │                            │  ChatGLM3 /    │
    │                            │  Qwen /        │
    │                            │  Llama3        │
    │                            └───────────────┘
```

**部署要点**：

- 使用vLLM或Text Generation Inference（TGI）框架部署模型服务，提供OpenAI兼容的API格式。
- CAPP系统通过内网HTTP调用本地模型服务，与调用云端模型使用相同的`LlmProviderInterface`接口。
- 模型服务部署在独立的内网服务器或容器中，与CAPP应用服务器分离，便于独立扩缩容。
- 本地模型服务配置API Key鉴权，防止未授权访问。

---

## 8. AI对话管理

### 8.1 会话上下文管理

#### 8.1.1 会话模型设计

每个AI对话会话在CAPP系统中对应一条`ai_conversation`记录，会话中的每条消息对应`ai_message`记录。

**会话状态流转**：

```
[创建] → [进行中] → [已结束]
   ↓        ↓
[挂起]   [用户主动结束/超时自动结束]
```

#### 8.1.2 上下文压缩策略

随着对话轮数增加，上下文Token消耗增大，采用以下压缩策略：

1. **滑动窗口**：保留最近N轮（如5轮）完整对话作为上下文，超出部分丢弃。
2. **摘要压缩**：当对话轮数超过阈值（如10轮），调用LLM对前5轮对话生成摘要，后续对话携带"摘要 + 最近5轮"作为上下文。
3. **关键信息提取**：在对话过程中，自动提取用户明确提及的关键约束条件（如产品型号、工艺编号），单独存储并在后续对话中强制携带，避免被滑动窗口丢弃。

### 8.2 历史记录管理

#### 8.2.1 记录内容

每条AI消息记录包含：

- 消息ID、会话ID、用户ID
- 角色（system/user/assistant/tool）
- 消息内容（原始文本）
- 消息类型（text/image/file）
- 关联的知识引用（检索到的文档片段ID列表）
- 使用的模型名称
- Token消耗（prompt_tokens, completion_tokens）
- 响应耗时
- 创建时间

#### 8.2.2 历史记录查询

用户可在AI助手界面查看历史会话列表，支持：

- 按时间范围筛选
- 按会话主题搜索（基于首条用户消息生成主题摘要）
- 收藏重要会话
- 导出会话记录为PDF/Markdown
- 从历史会话中继续对话

### 8.3 多轮对话优化

#### 8.3.1 指代消解

在多轮对话中，用户常使用指代（如"这个工艺"、"上面的参数"），系统需维护对话状态：

- **实体追踪**：记录对话中提及的关键实体（工艺编号、产品型号、参数名称）。
- **指代替换**：在将用户输入发送至LLM前，对明显的指代进行替换（如将"这个工艺"替换为具体的工艺编号）。

#### 8.3.2 话题切换检测

当用户输入与当前话题明显无关时，系统提示用户是否开启新会话：

```
用户当前在讨论"工业压缩机膜片装配工艺"，突然输入："查询我上周提交的工艺文件"

系统检测：话题从"工艺知识咨询"切换至"个人业务数据查询"
系统提示："检测到您的话题已切换，是否开启新的对话会话？"
用户选择："是" → 创建新会话并处理查询
         "否" → 在当前会话中继续处理
```

---

## 9. AI与业务系统集成

### 9.1 工艺推荐

#### 9.1.1 相似工艺推荐

在工艺设计过程中，当工艺员创建新工艺文件时，系统自动推荐相似历史工艺：

1. **特征提取**：提取当前工艺的产品类型、材料、关键工序等特征。
2. **相似度计算**：基于工艺内容Embedding向量，计算与历史工艺的余弦相似度。
3. **结果展示**：在工艺编辑页面右侧展示Top5相似工艺，显示相似度得分和关键差异点。
4. **一键引用**：工艺员可点击"引用工艺"，将相似工艺的工序内容复制到当前工艺中作为参考模板。

#### 9.1.2 工艺参数推荐

基于历史数据和知识库，为工艺参数设置提供智能推荐：

- 当工艺员选择"膜片材料"为"316L不锈钢"时，自动推荐该材料在历史工艺中的常用加工参数范围。
- 当工艺员设置"焊接方法"为"TIG焊"时，推荐匹配的电流、电压、气体流量参数区间。

### 9.2 异常检测

#### 9.2.1 工艺数据异常检测

在工艺文件提交或审批时，AI自动检测以下异常：

| 异常类型 | 检测逻辑 | 处理方式 |
|----------|----------|----------|
| 参数超限 | 工艺参数超出知识库中该类产品的历史范围 | 黄色警告，建议核实 |
| 工序缺失 | 与相似工艺相比缺少关键工序 | 红色警告，要求确认 |
| 逻辑矛盾 | 前后工序存在明显逻辑冲突 | 红色警告，阻断提交 |
| 版本回退 | 新版本工艺参数明显劣于旧版本 | 黄色警告，提示对比 |

#### 9.2.2 质量异常预警

基于实时质量检测数据，AI识别异常趋势：

- 某工序连续3批次出现相同类型的轻微偏差，AI预警该工序可能存在系统性问题。
- 某设备加工的产品尺寸分布出现异常偏移，AI建议检查设备刀具磨损情况。

### 9.3 预测分析

#### 9.3.1 工艺工时预测

基于工艺文件内容（工序数量、复杂度、材料类型），AI预测该工艺的标准工时和实际可能工时范围，为排产和成本核算提供参考。

#### 9.3.2 质量风险预测

在工艺设计阶段，基于工艺特征和历史质量数据，预测该工艺在执行过程中可能出现的质量风险点及概率，指导工艺员提前制定预防措施。

---

## 10. AI权限与安全

### 10.1 数据脱敏

#### 10.1.1 脱敏场景

在向LLM或MaxKB发送数据前，对敏感信息进行脱敏处理：

| 敏感信息类型 | 脱敏方式 | 示例 |
|--------------|----------|------|
| 客户名称 | 替换为代号 | "某能源集团" → "客户A" |
| 核心材料配比 | 部分隐藏 | "Cr:18%, Ni:12%, Mo:2.5%" → "Cr:xx%, Ni:xx%, Mo:2.5%" |
| 特殊工艺参数 | 范围化 | "转速3500rpm" → "转速3000-4000rpm" |
| 供应商信息 | 替换为代号 | "XX特钢有限公司" → "供应商B" |
| 价格信息 | 完全隐藏 | 不传入LLM |

#### 10.1.2 脱敏实现

```php
class DataDesensitizer {
    
    private $rules = [];
    
    public function __construct() {
        // 从配置加载脱敏规则
        $this->rules = [
            'customer_name' => ['type' => 'mask', 'replacement' => '客户A'],
            'material_ratio' => ['type' => 'partial_mask', 'pattern' => '/(\w+):(\d+)%/', 'replacement' => '$1:xx%'],
            'price' => ['type' => 'remove'],
        ];
    }
    
    /**
     * 对文本进行脱敏
     */
    public function desensitize($text, $context = 'llm') {
        // 根据上下文选择脱敏级别
        $level = $this->getDesensitizeLevel($context);
        
        foreach ($this->rules as $field => $rule) {
            switch ($rule['type']) {
                case 'mask':
                    $text = $this->applyMask($text, $field, $rule['replacement']);
                    break;
                case 'partial_mask':
                    $text = preg_replace($rule['pattern'], $rule['replacement'], $text);
                    break;
                case 'remove':
                    $text = $this->removeField($text, $field);
                    break;
            }
        }
        
        return $text;
    }
    
    private function getDesensitizeLevel($context) {
        // llm: 传给大模型的数据，严格脱敏
        // internal: 内部检索，轻度脱敏
        // public: 公开场景，完全脱敏
        $levels = ['llm' => 'strict', 'internal' => 'moderate', 'public' => 'full'];
        return $levels[$context] ?? 'strict';
    }
}
```

### 10.2 访问控制

#### 10.2.1 AI功能权限矩阵

| 功能模块 | 工艺员 | 工艺主管 | 质量工程师 | 生产计划员 | 管理员 |
|----------|--------|----------|------------|------------|--------|
| AI助手对话 | ✓ | ✓ | ✓ | ✓ | ✓ |
| 工艺知识查询 | ✓(本部门) | ✓(全部) | ✓(相关) | ✓(通用) | ✓ |
| 业务数据查询 | ✓(个人) | ✓(部门) | ✓(质量) | ✓(生产) | ✓ |
| AI预审工艺 | - | ✓ | - | - | ✓ |
| 排产建议查看 | - | - | - | ✓ | ✓ |
| 质量预警查看 | - | - | ✓ | - | ✓ |
| 知识库管理 | - | - | - | - | ✓ |
| 模型配置 | - | - | - | - | ✓ |
| 对话记录审计 | - | - | - | - | ✓ |

#### 10.2.2 API鉴权

所有AI相关API均通过CAPP系统统一认证体系鉴权：

1. 用户请求携带CAPP登录Token。
2. 后端校验Token有效性及用户权限。
3. 根据用户角色过滤可访问的知识库范围和工具权限。
4. 记录操作日志用于审计。

### 10.3 审计日志

#### 10.3.1 审计内容

AI模块的所有关键操作均记录审计日志：

- 用户发起的每次AI对话（用户ID、问题摘要、时间、IP）
- AI回答中引用的知识库文档（用于追溯回答依据）
- AI对业务数据的查询操作（查询条件、返回数据量）
- AI预审工艺的结论（工艺ID、预审结果、AI意见摘要）
- 模型调用记录（模型名称、Token消耗、响应时间）
- 知识库同步操作（同步文档、操作人、结果）

#### 10.3.2 日志存储

审计日志存储于`ai_audit_log`表，保留期限不少于2年。敏感操作（如查询核心工艺参数）的日志永久保留。

---

## 11. AI训练数据管理

### 11.1 数据收集

#### 11.1.1 数据来源

| 数据类型 | 收集方式 | 用途 |
|----------|----------|------|
| 对话记录 | 自动记录所有AI对话 | 分析用户常见问题，优化知识库 |
| 用户反馈 | 对话后"点赞/点踩"按钮 | 评估回答质量，筛选优质数据 |
| 专家修正 | 工艺专家对AI回答的人工修正 | 构建高质量训练样本 |
| 业务数据 | CAPP系统脱敏后的工艺数据 | 领域模型微调 |
| 标准文档 | 国家标准、行业标准 | 知识库构建 |

#### 11.1.2 数据质量筛选

建立数据质量评分机制，从以下维度评估：

- **用户满意度**：基于点赞/点踩比例评分。
- **专家验证**：经工艺专家确认正确的回答标记为"已验证"。
- **完整性**：回答是否包含引用来源、是否覆盖用户问题的全部要点。
- **准确性**：回答中的工艺参数、标准条款是否与权威来源一致。

### 11.2 数据标注

#### 11.2.1 标注流程

1. **自动预标注**：利用规则引擎和现有模型对收集的数据进行自动预标注。
2. **人工审核**：工艺专家对预标注结果进行审核和修正。
3. **交叉验证**：重要数据由多名专家独立标注，不一致时由资深专家仲裁。
4. **标注内容**：包括意图标签、实体标注、回答质量评分、修正后标准回答。

#### 11.2.2 标注工具

在CAPP系统后台提供数据标注界面：

- 展示原始对话记录
- 支持对AI回答进行编辑修正
- 支持标注意图类型、涉及工艺领域、引用文档
- 支持批量操作和导出

### 11.3 模型微调

#### 11.3.1 微调策略

采用"通用基座模型 + 领域LoRA微调"的策略：

1. **基座模型选择**：选择通用能力较强的开源模型（如Qwen-14B、ChatGLM3-6B）作为基座。
2. **领域数据准备**：将标注后的高质量工艺问答数据、工艺规范文本整理为微调数据集。
3. **LoRA微调**：在基座模型上使用LoRA（Low-Rank Adaptation）技术进行轻量化微调，训练领域适配器。
4. **模型评估**：使用工艺专家准备的测试集评估微调后模型的领域回答准确率和幻觉率。
5. **模型部署**：将微调后的LoRA权重与基座模型合并部署，或动态加载LoRA适配器。

#### 11.3.2 微调数据格式

```json
{
  "conversations": [
    {
      "role": "system",
      "content": "你是一名工业压缩机工艺专家，基于以下参考文档回答问题。"
    },
    {
      "role": "user",
      "content": "膜片安装时的扭矩要求是多少？"
    },
    {
      "role": "assistant",
      "content": "根据工艺规范GY-2023-0056，工业压缩机膜片安装时的扭矩要求如下：\n1. 一级膜片：42±2 N·m\n2. 二级膜片：45±2 N·m\n3. 三级膜片：48±2 N·m\n安装时需使用校准过的扭矩扳手，并在扭矩达到要求后进行复紧检查。"
    }
  ],
  "metadata": {
    "domain": "装配工艺",
    "product": "工业压缩机",
    "source": "专家标注",
    "quality_score": 0.95
  }
}
```

#### 11.3.3 微调调度

- **定期微调**：每季度使用新积累的高质量数据对LoRA适配器进行增量微调。
- **紧急微调**：当发现模型在特定领域出现系统性错误时，紧急收集该领域数据并进行针对性微调。
- **A/B测试**：新模型上线前，与旧模型并行运行一段时间，对比回答质量后再全量切换。

---

## 12. 数据表设计

### 12.1 AI对话记录表（ai_conversation）

存储AI对话会话信息。

```sql
CREATE TABLE `ai_conversation` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `conversation_no` VARCHAR(32) NOT NULL COMMENT '会话编号，如AI-20240115-001',
  `uid` INT UNSIGNED NOT NULL COMMENT 'CAPP用户ID',
  `title` VARCHAR(200) DEFAULT '' COMMENT '会话主题摘要',
  `agent_id` VARCHAR(64) DEFAULT '' COMMENT '使用的MaxKB智能体ID',
  `model_name` VARCHAR(50) DEFAULT '' COMMENT '使用的大模型名称',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态：1进行中，2已结束，3已挂起',
  `context_summary` TEXT COMMENT '上下文摘要（用于长会话压缩）',
  `total_tokens` INT UNSIGNED DEFAULT 0 COMMENT '累计Token消耗',
  `total_messages` INT UNSIGNED DEFAULT 0 COMMENT '累计消息数',
  `is_favorite` TINYINT NOT NULL DEFAULT 0 COMMENT '是否收藏：1是，0否',
  `start_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '开始时间',
  `end_time` DATETIME DEFAULT NULL COMMENT '结束时间',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conversation_no` (`conversation_no`),
  KEY `idx_uid` (`uid`),
  KEY `idx_status` (`status`),
  KEY `idx_start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI对话记录表';
```

### 12.2 AI消息记录表（ai_message）

存储对话中的每条消息。

```sql
CREATE TABLE `ai_message` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `conversation_id` INT UNSIGNED NOT NULL COMMENT '关联会话ID',
  `message_no` INT UNSIGNED NOT NULL COMMENT '消息序号，会话内递增',
  `role` VARCHAR(20) NOT NULL COMMENT '角色：system/user/assistant/tool',
  `content` LONGTEXT COMMENT '消息内容',
  `content_type` VARCHAR(20) DEFAULT 'text' COMMENT '内容类型：text/image/file',
  `message_type` VARCHAR(30) DEFAULT 'chat' COMMENT '消息类型：chat/data_query/approval/pre_check',
  `intent_type` VARCHAR(30) DEFAULT '' COMMENT '意图类型',
  `references` JSON COMMENT '引用的知识库文档片段ID列表',
  `tool_calls` JSON COMMENT '工具调用记录',
  `model_name` VARCHAR(50) DEFAULT '' COMMENT '生成该回答使用的模型',
  `prompt_tokens` INT UNSIGNED DEFAULT 0 COMMENT 'Prompt Token数',
  `completion_tokens` INT UNSIGNED DEFAULT 0 COMMENT 'Completion Token数',
  `total_tokens` INT UNSIGNED DEFAULT 0 COMMENT '总Token数',
  `response_time_ms` INT UNSIGNED DEFAULT 0 COMMENT '响应耗时（毫秒）',
  `feedback` TINYINT DEFAULT 0 COMMENT '用户反馈：1点赞，-1点踩，0未评价',
  `feedback_comment` VARCHAR(500) DEFAULT '' COMMENT '反馈备注',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_conv_msg` (`conversation_id`, `message_no`),
  KEY `idx_role` (`role`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI消息记录表';
```

### 12.3 AI决策记录表（ai_decision）

存储AI在业务系统中的决策/建议记录。

```sql
CREATE TABLE `ai_decision` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `decision_no` VARCHAR(32) NOT NULL COMMENT '决策编号',
  `decision_type` VARCHAR(30) NOT NULL COMMENT '决策类型：pre_check(预审)/optimization(优化建议)/schedule(排产建议)/quality_alert(质量预警)',
  `business_type` VARCHAR(30) NOT NULL COMMENT '业务类型：process/approval/task/quality',
  `business_id` INT UNSIGNED NOT NULL COMMENT '业务关联ID',
  `business_no` VARCHAR(50) DEFAULT '' COMMENT '业务编号',
  `input_data` JSON COMMENT '输入给AI的数据摘要',
  `ai_conclusion` VARCHAR(20) DEFAULT '' COMMENT 'AI结论：pass/warning/reject/suggest',
  `ai_suggestion` LONGTEXT COMMENT 'AI建议内容',
  `ai_confidence` DECIMAL(3,2) DEFAULT 0.00 COMMENT 'AI置信度',
  `human_decision` VARCHAR(20) DEFAULT '' COMMENT '人工决策：agree/overrule/modify',
  `human_comment` VARCHAR(500) DEFAULT '' COMMENT '人工意见',
  `handler_id` INT UNSIGNED DEFAULT 0 COMMENT '处理人ID',
  `handler_time` DATETIME DEFAULT NULL COMMENT '处理时间',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态：1待处理，2已采纳，3已驳回，4已修改',
  `model_name` VARCHAR(50) DEFAULT '' COMMENT '使用的模型',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_decision_no` (`decision_no`),
  KEY `idx_business` (`business_type`, `business_id`),
  KEY `idx_type` (`decision_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI决策记录表';
```

### 12.4 MaxKB对接配置表（maxkb_config）

存储MaxKB系统的对接配置。

```sql
CREATE TABLE `maxkb_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `config_key` VARCHAR(50) NOT NULL COMMENT '配置键',
  `config_value` TEXT COMMENT '配置值',
  `config_desc` VARCHAR(255) DEFAULT '' COMMENT '配置说明',
  `is_encrypt` TINYINT NOT NULL DEFAULT 0 COMMENT '是否加密存储：1是，0否',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='MaxKB对接配置表';

-- 初始化数据
INSERT INTO `maxkb_config` (`config_key`, `config_value`, `config_desc`, `is_encrypt`) VALUES
('api_url', 'http://maxkb.company.com/api', 'MaxKB API基础地址', 0),
('api_key', 'encrypted_value', 'MaxKB API密钥', 1),
('default_chat_id', 'app-default-001', '默认对话应用ID', 0),
('knowledge_base_process', 'kb-process-001', '工艺知识库ID', 0),
('knowledge_base_standard', 'kb-standard-001', '标准规范知识库ID', 0),
('knowledge_base_quality', 'kb-quality-001', '质量知识库ID', 0),
('sync_enabled', '1', '是否启用自动同步', 0),
('sync_interval', '3600', '同步间隔（秒）', 0);
```

### 12.5 MaxKB同步记录表（maxkb_sync_record）

记录CAPP系统与MaxKB之间的文档同步历史。

```sql
CREATE TABLE `maxkb_sync_record` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `capp_doc_id` INT UNSIGNED NOT NULL COMMENT 'CAPP文档ID',
  `capp_doc_no` VARCHAR(50) DEFAULT '' COMMENT 'CAPP文档编号',
  `maxkb_doc_id` VARCHAR(64) DEFAULT '' COMMENT 'MaxKB文档ID',
  `knowledge_base_id` VARCHAR(64) NOT NULL COMMENT 'MaxKB知识库ID',
  `sync_type` VARCHAR(20) NOT NULL COMMENT '同步类型：upload/update/delete',
  `status` VARCHAR(20) NOT NULL COMMENT '状态：success/failed/pending/processing',
  `doc_hash` VARCHAR(64) DEFAULT '' COMMENT '文档内容哈希值',
  `sync_time` DATETIME DEFAULT NULL COMMENT '同步时间',
  `error_msg` VARCHAR(500) DEFAULT '' COMMENT '错误信息',
  `response` TEXT COMMENT 'MaxKB返回的原始响应',
  `retry_count` TINYINT NOT NULL DEFAULT 0 COMMENT '重试次数',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_doc_kb` (`capp_doc_id`, `knowledge_base_id`),
  KEY `idx_status` (`status`),
  KEY `idx_sync_time` (`sync_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='MaxKB同步记录表';
```

### 12.6 AI知识库文档表（ai_knowledge_doc）

存储CAPP系统内AI知识库文档的元数据（与MaxKB知识库对应）。

```sql
CREATE TABLE `ai_knowledge_doc` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `doc_no` VARCHAR(50) NOT NULL COMMENT '文档编号',
  `doc_name` VARCHAR(200) NOT NULL COMMENT '文档名称',
  `knowledge_base_id` VARCHAR(64) NOT NULL COMMENT '所属知识库ID',
  `category` VARCHAR(50) DEFAULT '' COMMENT '文档分类',
  `sub_category` VARCHAR(50) DEFAULT '' COMMENT '文档子分类',
  `file_path` VARCHAR(500) DEFAULT '' COMMENT '文件存储路径',
  `file_size` INT UNSIGNED DEFAULT 0 COMMENT '文件大小（字节）',
  `file_type` VARCHAR(20) DEFAULT '' COMMENT '文件类型：pdf/word/markdown/txt',
  `security_level` VARCHAR(20) DEFAULT '内部公开' COMMENT '密级：公开/内部公开/秘密/机密',
  `effective_date` DATE DEFAULT NULL COMMENT '生效日期',
  `expire_date` DATE DEFAULT NULL COMMENT '失效日期',
  `source_type` VARCHAR(30) DEFAULT '' COMMENT '来源类型：capp/standard/manual/training',
  `source_id` INT UNSIGNED DEFAULT 0 COMMENT '来源ID',
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '状态：1有效，0失效',
  `maxkb_sync_status` TINYINT NOT NULL DEFAULT 0 COMMENT 'MaxKB同步状态：0未同步，1已同步，2同步失败',
  `maxkb_doc_id` VARCHAR(64) DEFAULT '' COMMENT 'MaxKB文档ID',
  `create_by` INT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_doc_no` (`doc_no`),
  KEY `idx_knowledge_base` (`knowledge_base_id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  KEY `idx_sync_status` (`maxkb_sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI知识库文档表';
```

### 12.7 AI审计日志表（ai_audit_log）

记录AI模块的关键操作审计日志。

```sql
CREATE TABLE `ai_audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `audit_type` VARCHAR(30) NOT NULL COMMENT '审计类型：chat/query/decision/sync/config',
  `uid` INT UNSIGNED DEFAULT 0 COMMENT '操作用户ID',
  `username` VARCHAR(50) DEFAULT '' COMMENT '操作用户名',
  `action` VARCHAR(50) NOT NULL COMMENT '操作动作',
  `target_type` VARCHAR(30) DEFAULT '' COMMENT '操作对象类型',
  `target_id` VARCHAR(64) DEFAULT '' COMMENT '操作对象ID',
  `request_summary` VARCHAR(500) DEFAULT '' COMMENT '请求摘要',
  `response_summary` VARCHAR(500) DEFAULT '' COMMENT '响应摘要',
  `ip_address` VARCHAR(50) DEFAULT '' COMMENT '操作IP',
  `user_agent` VARCHAR(255) DEFAULT '' COMMENT 'User-Agent',
  `risk_level` TINYINT DEFAULT 0 COMMENT '风险等级：0正常，1低风险，2中风险，3高风险',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_audit_type` (`audit_type`),
  KEY `idx_uid` (`uid`),
  KEY `idx_action` (`action`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI审计日志表';
```

### 12.8 AI模型配置表（ai_model_config）

存储接入的大语言模型配置信息。

```sql
CREATE TABLE `ai_model_config` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `model_code` VARCHAR(50) NOT NULL COMMENT '模型编码',
  `model_name` VARCHAR(100) NOT NULL COMMENT '模型显示名称',
  `provider_type` VARCHAR(30) NOT NULL COMMENT '提供商类型：openai/azure/wenxin/tongyi/zhipu/local',
  `api_url` VARCHAR(255) NOT NULL COMMENT 'API地址',
  `api_key` VARCHAR(255) DEFAULT '' COMMENT 'API密钥（加密存储）',
  `model_id` VARCHAR(50) NOT NULL COMMENT '模型ID，如gpt-4、qwen-14b',
  `embedding_model` VARCHAR(50) DEFAULT '' COMMENT '关联的Embedding模型ID',
  `max_tokens` INT UNSIGNED DEFAULT 4096 COMMENT '最大Token数',
  `temperature` DECIMAL(3,2) DEFAULT 0.70 COMMENT '温度参数',
  `timeout` INT UNSIGNED DEFAULT 60 COMMENT '超时时间（秒）',
  `is_default` TINYINT NOT NULL DEFAULT 0 COMMENT '是否默认模型：1是，0否',
  `is_enabled` TINYINT NOT NULL DEFAULT 1 COMMENT '是否启用：1是，0否',
  `priority` INT UNSIGNED DEFAULT 0 COMMENT '优先级，数字越小优先级越高',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `create_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_model_code` (`model_code`),
  KEY `idx_provider` (`provider_type`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI模型配置表';
```

---

## 13. 接口清单

### 13.1 CAPP系统内部AI接口

| 接口路径 | 方法 | 功能说明 | 调用方 |
|----------|------|----------|--------|
| /api/ai/chat | POST | AI对话接口 | AI助手前端 |
| /api/ai/chat/stream | POST | AI对话流式接口（SSE） | AI助手前端 |
| /api/ai/conversation/list | GET | 获取用户会话列表 | AI助手前端 |
| /api/ai/conversation/detail | GET | 获取会话详情 | AI助手前端 |
| /api/ai/conversation/create | POST | 创建新会话 | AI助手前端 |
| /api/ai/conversation/end | POST | 结束会话 | AI助手前端 |
| /api/ai/feedback | POST | 提交消息反馈 | AI助手前端 |
| /api/ai/search | POST | 知识库检索 | 业务模块 |
| /api/ai/pre_check | POST | AI工艺预审 | 审批模块 |
| /api/ai/optimize | POST | AI工艺优化建议 | 工艺编辑页 |
| /api/ai/schedule/suggest | POST | AI排产建议 | 排产模块 |
| /api/ai/quality/alert | GET | AI质量预警 | 质量模块 |
| /api/ai/history/export | GET | 导出对话记录 | 用户后台 |

### 13.2 MaxKB对接接口（CAPP调用MaxKB）

| 接口路径 | 方法 | 功能 |
|----------|------|------|
| /api/dataset | GET/POST | 知识库管理 |
| /api/dataset/{id}/document | GET/POST/PUT/DELETE | 文档管理 |
| /api/dataset/{id}/hit_test | POST | 知识检索测试 |
| /api/application | GET | 应用（智能体）列表 |
| /api/application/{id}/chat | POST | 智能体对话 |
| /api/chat/{id}/log | GET | 对话历史 |

### 13.3 CAPP工具API（MaxKB智能体调用）

| 接口路径 | 方法 | 功能 |
|----------|------|------|
| /api/ai/tool/process/get | POST | 查询工艺文件 |
| /api/ai/tool/process/detail | POST | 查询工艺详情 |
| /api/ai/tool/quality/record | POST | 查询质量记录 |
| /api/ai/tool/workhour/query | POST | 查询工时数据 |
| /api/ai/tool/material/stock | POST | 查询物料库存 |
| /api/ai/tool/equipment/status | POST | 查询设备状态 |

### 13.4 定时任务

| 任务名称 | 执行频率 | 功能 |
|----------|----------|------|
| maxkb_sync_push | 每5分钟 | 消费同步队列，推送文档至MaxKB |
| maxkb_sync_verify | 每日01:30 | 校验CAPP与MaxKB文档一致性 |
| ai_conversation_clean | 每日03:00 | 清理30天前已结束的会话上下文缓存 |
| ai_audit_log_archive | 每月1日 | 归档3个月前的审计日志 |
| ai_model_health_check | 每10分钟 | 检测各模型服务健康状态 |

---

## 14. 附录

### 14.1 MaxKB API响应格式参考

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": "doc-12345",
    "name": "工业压缩机装配工艺.md",
    "status": "completed",
    "paragraph_count": 25,
    "token_count": 3500
  }
}
```

### 14.2 相关文档索引

- 《01-项目概述与需求规格说明书.md》
- 《02-系统架构设计.md》
- 《04-工艺管理模块详细设计.md》
- 《08-API接口设计.md》
- 《09-前端UI设计.md》

---

*文档版本：V1.0*  
*编写日期：2024年*  
*技术栈：PHP原生 + MySQL + MaxKB专业版*  
*适用范围：工业压缩机、压力容器研发设计生产企业CAPP系统*
