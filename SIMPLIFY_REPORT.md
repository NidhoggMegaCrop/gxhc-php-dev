# 战报统计、最新动态、常见问题功能简化报告

## 一、修改概述

### 1.1 修改背景
原有的三个功能（战报统计、最新动态、常见问题）采用了完整的 MVC 分层架构，包括独立的数据库表、Model、DAO、Services 层。这种设计过于复杂，维护成本较高。

### 1.2 简化方案
改用系统配置表 `system_config` 存储 JSON 数据，与系统"设置"功能保持一致的设计模式。

### 1.3 修改效果
| 指标 | 修改前 | 修改后 |
|------|--------|--------|
| 代码行数 | ~1618 行 | ~825 行 |
| 文件数量 | 17 个 | 6 个 |
| 数据库表 | 3 个独立表 | 复用 system_config |
| 架构层级 | Controller → Services → DAO → Model | Controller 直接操作 |

---

## 二、删除的文件清单（11个）

### 2.1 Model 层（3个）
| 文件路径 | 说明 |
|----------|------|
| `app/model/gxhc/BattleStats.php` | 战报统计数据模型 |
| `app/model/gxhc/News.php` | 最新动态数据模型 |
| `app/model/gxhc/Faq.php` | 常见问题数据模型 |

### 2.2 DAO 层（3个）
| 文件路径 | 说明 |
|----------|------|
| `app/dao/gxhc/BattleStatsDao.php` | 战报统计数据访问层 |
| `app/dao/gxhc/NewsDao.php` | 最新动态数据访问层 |
| `app/dao/gxhc/FaqDao.php` | 常见问题数据访问层 |

### 2.3 Services 层（3个）
| 文件路径 | 说明 |
|----------|------|
| `app/services/gxhc/BattleStatsServices.php` | 战报统计业务逻辑层 |
| `app/services/gxhc/NewsServices.php` | 最新动态业务逻辑层 |
| `app/services/gxhc/FaqServices.php` | 常见问题业务逻辑层 |

### 2.4 SQL 迁移文件（2个）
| 文件路径 | 说明 |
|----------|------|
| `public/install/battle_stats.sql` | 战报统计表结构 |
| `public/install/news_and_faq.sql` | 最新动态和常见问题表结构 |

---

## 三、修改的文件清单（6个）

### 3.1 后台管理控制器（3个）

#### `app/adminapi/controller/v1/gxhc/BattleStats.php`
**修改内容：**
- 移除对 `BattleStatsServices` 的依赖
- 改用 `SystemConfigServices` 存储数据
- 数据存储在配置项 `gxhc_battle_stats` 中（JSON 数组格式）
- 保留原有 API 接口不变

**主要方法：**
| 方法 | 路由 | 功能 |
|------|------|------|
| `index()` | GET /battle_stats/list | 获取列表（支持分页、筛选） |
| `getAllStats()` | GET /battle_stats/all | 获取所有启用项 |
| `read($id)` | GET /battle_stats/detail/:id | 获取详情 |
| `save()` | POST /battle_stats/create | 创建统计项 |
| `update($id)` | PUT /battle_stats/update/:id | 更新统计项 |
| `delete($id)` | DELETE /battle_stats/delete/:id | 删除统计项 |
| `updateValue($id)` | PUT /battle_stats/update_value/:id | 快捷更新值 |
| `setStatus($id)` | PUT /battle_stats/set_status/:id | 修改状态 |

---

#### `app/adminapi/controller/v1/gxhc/News.php`
**修改内容：**
- 移除对 `NewsServices` 的依赖
- 改用 `SystemConfigServices` 存储数据
- 数据存储在配置项 `gxhc_news` 中（JSON 数组格式）
- 内置 `formatTimeAgo()` 方法实现相对时间显示

**主要方法：**
| 方法 | 路由 | 功能 |
|------|------|------|
| `index()` | GET /news/list | 获取列表（支持分页、筛选） |
| `getAllNews()` | GET /news/all | 获取所有启用动态 |
| `read($id)` | GET /news/detail/:id | 获取详情 |
| `save()` | POST /news/create | 创建动态 |
| `update($id)` | PUT /news/update/:id | 更新动态 |
| `delete($id)` | DELETE /news/delete/:id | 删除动态 |
| `setStatus($id)` | PUT /news/set_status/:id | 修改状态 |

---

#### `app/adminapi/controller/v1/gxhc/Faq.php`
**修改内容：**
- 移除对 `FaqServices` 的依赖
- 改用 `SystemConfigServices` 存储数据
- 数据存储在配置项 `gxhc_faq` 中（JSON 数组格式）

**主要方法：**
| 方法 | 路由 | 功能 |
|------|------|------|
| `index()` | GET /faq/list | 获取列表（支持分页、筛选） |
| `getAllFaq()` | GET /faq/all | 获取所有启用问题 |
| `getCategories()` | GET /faq/categories | 获取所有分类 |
| `read($id)` | GET /faq/detail/:id | 获取详情 |
| `save()` | POST /faq/create | 创建问题 |
| `update($id)` | PUT /faq/update/:id | 更新问题 |
| `delete($id)` | DELETE /faq/delete/:id | 删除问题 |
| `setStatus($id)` | PUT /faq/set_status/:id | 修改状态 |

---

### 3.2 前端 API 控制器（3个）

#### `app/api/controller/v1/gxhc/BattleStatsController.php`
**修改内容：**
- 移除构造函数中的 Services 注入
- 直接使用 `sys_config()` 读取配置数据

**主要方法：**
| 方法 | 功能 |
|------|------|
| `getStats()` | 获取所有启用的战报统计 |
| `getStatByKey()` | 根据 key 获取单个统计项 |

---

#### `app/api/controller/v1/gxhc/NewsController.php`
**修改内容：**
- 移除构造函数中的 Services 注入
- 直接使用 `sys_config()` 读取配置数据
- 内置 `formatTimeAgo()` 方法

**主要方法：**
| 方法 | 功能 |
|------|------|
| `getNewsList()` | 获取最新动态列表 |
| `getFeaturedNews()` | 获取精华动态 |
| `getNewsDetail()` | 获取动态详情 |

---

#### `app/api/controller/v1/gxhc/FaqController.php`
**修改内容：**
- 移除构造函数中的 Services 注入
- 直接使用 `sys_config()` 读取配置数据

**主要方法：**
| 方法 | 功能 |
|------|------|
| `getFaqList()` | 获取常见问题列表 |
| `getCategories()` | 获取所有分类 |
| `getFaqDetail()` | 获取问题详情 |

---

## 四、数据存储格式

### 4.1 战报统计 (`gxhc_battle_stats`)
```json
[
  {
    "id": 1,
    "key": "total_users",
    "name": "累计用户数",
    "value": 10000,
    "unit": "人",
    "description": "平台累计注册用户数",
    "icon": "icon-users",
    "sort": 100,
    "status": 1,
    "add_time": 1705987200,
    "update_time": 1705987200
  }
]
```

### 4.2 最新动态 (`gxhc_news`)
```json
[
  {
    "id": 1,
    "title": "新功能上线公告",
    "content": "详细内容...",
    "author": "官方",
    "type": "news",
    "cover_image": "/uploads/news/cover.jpg",
    "sort": 0,
    "status": 1,
    "view_count": 0,
    "publish_time": 1705987200,
    "add_time": 1705987200,
    "update_time": 1705987200
  }
]
```

### 4.3 常见问题 (`gxhc_faq`)
```json
[
  {
    "id": 1,
    "question": "如何注册账号？",
    "answer": "点击右上角注册按钮...",
    "category": "账号相关",
    "sort": 100,
    "status": 1,
    "view_count": 0,
    "add_time": 1705987200,
    "update_time": 1705987200
  }
]
```

---

## 五、数据库配置

### 5.1 添加配置项
在 `eb_system_config` 表中执行以下 SQL：

```sql
INSERT INTO `eb_system_config` (`menu_name`, `type`, `config_tab_id`, `value`, `status`) VALUES
('gxhc_battle_stats', 'text', 0, '[]', 1),
('gxhc_news', 'text', 0, '[]', 1),
('gxhc_faq', 'text', 0, '[]', 1);
```

### 5.2 验证配置
```sql
SELECT * FROM `eb_system_config`
WHERE `menu_name` IN ('gxhc_battle_stats', 'gxhc_news', 'gxhc_faq');
```

### 5.3 旧表清理（可选）
如果之前已创建独立表，可执行以下 SQL 删除：
```sql
DROP TABLE IF EXISTS `eb_g_battle_stats`;
DROP TABLE IF EXISTS `eb_g_news`;
DROP TABLE IF EXISTS `eb_g_faq`;
```

---

## 六、无需修改的文件

以下文件保持不变，无需修改：

| 文件路径 | 说明 |
|----------|------|
| `app/adminapi/route/gxhc.php` | 路由配置文件 |
| `app/api/route/v1.php` | 前端 API 路由 |

---

## 七、Git 分支信息

| 分支名称 | 用途 |
|----------|------|
| `claude/simplify-management-features-xQ1IK` | 完整的代码变更（可直接 merge） |
| `claude/simplified-files-only-xQ1IK` | 仅修改后的文件（方便复制粘贴） |

---

## 八、注意事项

1. **API 接口完全兼容**：前端调用方式无需任何修改
2. **缓存清理**：修改配置后会自动清理缓存（`CacheService::clear()`）
3. **数据迁移**：如已有旧数据，需手动迁移到新的 JSON 格式
4. **表前缀**：SQL 中的 `eb_` 前缀请根据实际安装配置调整

---

*报告生成时间：2026-01-23*
