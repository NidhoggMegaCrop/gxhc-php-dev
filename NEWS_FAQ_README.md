# 最新动态 & 常见问题管理功能说明

## 功能概述

这个功能允许你在后台管理系统中动态管理小程序的"最新动态"和"常见问题"内容，实现内容的实时更新，无需修改代码。

## 功能特性

### 最新动态管理
- ✅ 支持两种类型：动态（news）和精华（featured）
- ✅ 自动计算相对时间（刚才、2小时前、1天前等）
- ✅ 支持封面图片、作者、排序
- ✅ 浏览次数统计
- ✅ 发布时间控制
- ✅ 状态启用/禁用

### 常见问题管理
- ✅ 问题分类管理
- ✅ 问答内容编辑
- ✅ 查看次数统计
- ✅ 排序功能
- ✅ 状态启用/禁用

## 安装步骤

### 1. 创建数据库表

在MySQL数据库中执行以下SQL文件：

```bash
# 使用MySQL命令行
mysql -u用户名 -p密码 数据库名 < public/install/news_and_faq.sql
```

或者直接执行SQL：

```sql
-- 最新动态表
CREATE TABLE `eb_g_news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `author` varchar(100) NOT NULL DEFAULT '' COMMENT '作者/来源',
  `type` varchar(50) NOT NULL DEFAULT 'news' COMMENT '类型：news-动态，featured-精华',
  `cover_image` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图片',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序（越小越靠前）',
  `view_count` int(11) NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `publish_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `publish_time` (`publish_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='最新动态表';

-- 常见问题表
CREATE TABLE `eb_g_faq` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `question` varchar(500) NOT NULL DEFAULT '' COMMENT '问题',
  `answer` text COMMENT '答案',
  `category` varchar(100) NOT NULL DEFAULT '' COMMENT '分类',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序（越小越靠前）',
  `view_count` int(11) NOT NULL DEFAULT '0' COMMENT '查看次数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='常见问题表';
```

### 2. 验证安装

```sql
-- 查看最新动态
SELECT * FROM eb_g_news;

-- 查看常见问题
SELECT * FROM eb_g_faq;
```

## 后台管理接口

所有后台管理接口都需要管理员权限认证。

### 最新动态管理

#### 1. 获取最新动态列表

```
GET /adminapi/news/list
```

请求参数：
- `page`: 页码（默认1）
- `limit`: 每页数量（默认10）
- `status`: 状态筛选（可选）
- `type`: 类型筛选：news-动态，featured-精华（可选）
- `keyword`: 标题关键词（可选）
- `author`: 作者关键词（可选）

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "title": "导师点评:车水的的的...",
        "content": "ju DSEJF\n\n导师点评详细内容...",
        "author": "导师点评",
        "type": "featured",
        "cover_image": "",
        "sort": 1,
        "view_count": 0,
        "status": 1,
        "publish_time": "2024-01-22 00:00:00",
        "time_ago": "刚才"
      }
    ],
    "count": 4
  }
}
```

#### 2. 获取所有启用的动态

```
GET /adminapi/news/all?type=news&limit=20
```

#### 3. 创建最新动态

```
POST /adminapi/news/create
```

请求参数：
```json
{
  "title": "导师点评:最新创业项目分析",
  "content": "详细的点评内容...",
  "author": "导师点评",
  "type": "news",
  "cover_image": "https://example.com/image.jpg",
  "sort": 1,
  "status": 1,
  "publish_time": "2024-01-22 10:00:00"
}
```

#### 4. 更新最新动态

```
PUT /adminapi/news/update/:id
```

#### 5. 删除最新动态

```
DELETE /adminapi/news/delete/:id
```

#### 6. 修改状态

```
PUT /adminapi/news/set_status/:id
```

请求参数：
```json
{
  "status": 1
}
```

### 常见问题管理

#### 1. 获取常见问题列表

```
GET /adminapi/faq/list
```

请求参数：
- `page`: 页码（默认1）
- `limit`: 每页数量（默认10）
- `status`: 状态筛选（可选）
- `category`: 分类筛选（可选）
- `keyword`: 问题关键词（可选）

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "question": "如何提升 BP 诊断的准确度？",
        "answer": "尽量上传完整 PDF格式的BBP...",
        "category": "BP诊断",
        "sort": 1,
        "view_count": 0,
        "status": 1
      }
    ],
    "count": 3
  }
}
```

#### 2. 获取所有分类

```
GET /adminapi/faq/categories
```

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": ["BP诊断", "账户问题", "支付问题"]
}
```

#### 3. 创建常见问题

```
POST /adminapi/faq/create
```

请求参数：
```json
{
  "question": "如何提升 BP 诊断的准确度？",
  "answer": "尽量上传完整 PDF格式的BBP，确保文件清晰完整。",
  "category": "BP诊断",
  "sort": 1,
  "status": 1
}
```

#### 4. 更新常见问题

```
PUT /adminapi/faq/update/:id
```

#### 5. 删除常见问题

```
DELETE /adminapi/faq/delete/:id
```

#### 6. 修改状态

```
PUT /adminapi/faq/set_status/:id
```

## 前端API接口

前端调用需要用户登录认证。

### 最新动态API

#### 1. 获取最新动态列表

```
GET /api/news/list?type=news&limit=20
```

参数：
- `type`: 类型筛选：news-动态，featured-精华（可选）
- `limit`: 限制数量（默认20）

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": [
    {
      "id": 1,
      "title": "导师点评:车水的的的...",
      "content": "详细内容...",
      "author": "导师点评",
      "type": "news",
      "cover_image": "",
      "sort": 1,
      "publish_time": "2024-01-22 10:00:00",
      "time_ago": "2小时前"
    }
  ]
}
```

#### 2. 获取精华动态（直播实战精华）

```
GET /api/news/featured?limit=10
```

#### 3. 获取动态详情

```
GET /api/news/detail/:id
```

### 常见问题API

#### 1. 获取常见问题列表

```
GET /api/faq/list?category=BP诊断&limit=20
```

参数：
- `category`: 分类筛选（可选）
- `limit`: 限制数量（默认20）

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": [
    {
      "id": 1,
      "question": "如何提升 BP 诊断的准确度？",
      "answer": "尽量上传完整 PDF格式的BBP...",
      "category": "BP诊断",
      "sort": 1
    }
  ]
}
```

#### 2. 获取所有分类

```
GET /api/faq/categories
```

#### 3. 获取常见问题详情

```
GET /api/faq/detail/:id
```

## 前端集成示例

### 小程序 - 最新动态

```javascript
Page({
  data: {
    newsList: [],
    featuredNews: []
  },

  onLoad() {
    this.getNews();
    this.getFeaturedNews();
  },

  // 获取最新动态列表
  getNews() {
    wx.request({
      url: 'https://你的域名/api/news/list',
      method: 'GET',
      data: {
        type: 'news',
        limit: 20
      },
      header: {
        'token': wx.getStorageSync('token')
      },
      success: (res) => {
        if (res.data.code === 200) {
          this.setData({
            newsList: res.data.data
          });
        }
      }
    });
  },

  // 获取精华动态
  getFeaturedNews() {
    wx.request({
      url: 'https://你的域名/api/news/featured',
      method: 'GET',
      data: {
        limit: 10
      },
      header: {
        'token': wx.getStorageSync('token')
      },
      success: (res) => {
        if (res.data.code === 200) {
          this.setData({
            featuredNews: res.data.data
          });
        }
      }
    });
  }
});
```

### WXML模板 - 最新动态

```xml
<!-- 直播实战精华 -->
<view class="featured-section">
  <view class="section-title">直播实战精华</view>

  <block wx:for="{{featuredNews}}" wx:key="id">
    <view class="featured-card" bindtap="viewDetail" data-id="{{item.id}}">
      <view class="author">{{item.author}}</view>
      <view class="title">{{item.title}}</view>
      <view class="content">{{item.content}}</view>
    </view>
  </block>

  <view class="more-btn">更多实战点评</view>
</view>

<!-- 最新动态 -->
<view class="news-section">
  <view class="section-title">最新动态</view>

  <block wx:for="{{newsList}}" wx:key="id">
    <view class="news-item" bindtap="viewDetail" data-id="{{item.id}}">
      <view class="news-title">{{item.title}}</view>
      <view class="news-time">{{item.time_ago}}</view>
    </view>
  </block>

  <view class="complete-btn">完整动态</view>
</view>
```

### 小程序 - 常见问题

```javascript
Page({
  data: {
    faqList: [],
    categories: []
  },

  onLoad() {
    this.getFaqList();
    this.getCategories();
  },

  // 获取常见问题列表
  getFaqList() {
    wx.request({
      url: 'https://你的域名/api/faq/list',
      method: 'GET',
      data: {
        limit: 20
      },
      header: {
        'token': wx.getStorageSync('token')
      },
      success: (res) => {
        if (res.data.code === 200) {
          this.setData({
            faqList: res.data.data
          });
        }
      }
    });
  },

  // 获取分类
  getCategories() {
    wx.request({
      url: 'https://你的域名/api/faq/categories',
      method: 'GET',
      header: {
        'token': wx.getStorageSync('token')
      },
      success: (res) => {
        if (res.data.code === 200) {
          this.setData({
            categories: res.data.data
          });
        }
      }
    });
  }
});
```

### WXML模板 - 常见问题

```xml
<view class="faq-section">
  <view class="section-title">常见问题</view>

  <block wx:for="{{faqList}}" wx:key="id">
    <view class="faq-item">
      <view class="question">
        <image class="icon" src="/images/question-icon.png"></image>
        <text>{{item.question}}</text>
      </view>
      <view class="answer">{{item.answer}}</view>
    </view>
  </block>
</view>
```

## 数据库字段说明

### 最新动态表（eb_g_news）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键ID |
| title | varchar(255) | 标题 |
| content | text | 内容 |
| author | varchar(100) | 作者/来源 |
| type | varchar(50) | 类型：news-动态，featured-精华 |
| cover_image | varchar(500) | 封面图片URL |
| sort | int | 排序，数值越小越靠前 |
| view_count | int | 浏览次数 |
| status | tinyint | 状态：0=禁用，1=启用 |
| publish_time | int | 发布时间（Unix时间戳） |
| add_time | int | 创建时间（Unix时间戳） |
| update_time | int | 更新时间（Unix时间戳） |

### 常见问题表（eb_g_faq）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键ID |
| question | varchar(500) | 问题 |
| answer | text | 答案 |
| category | varchar(100) | 分类 |
| sort | int | 排序，数值越小越靠前 |
| view_count | int | 查看次数 |
| status | tinyint | 状态：0=禁用，1=启用 |
| add_time | int | 创建时间（Unix时间戳） |
| update_time | int | 更新时间（Unix时间戳） |

## 使用场景

### 最新动态

1. **直播实战精华** (`type=featured`)
   - 展示在首页左侧卡片
   - 内容更丰富，包含封面图
   - 突出显示重要的导师点评

2. **最新动态** (`type=news`)
   - 展示在首页右侧列表
   - 简洁的标题展示
   - 显示相对时间（刚才、2H前、1D前）

### 常见问题

1. **分类管理**
   - 按类别组织问题（如"BP诊断"、"账户问题"等）
   - 方便用户快速找到相关问题

2. **问答展示**
   - 清晰的问答格式
   - 支持图标展示
   - 可折叠/展开详情

## 常见问题

### Q: 如何修改最新动态的显示数量？

A: 在API调用时传入`limit`参数，例如：`GET /api/news/list?limit=10`

### Q: 如何添加新的动态类型？

A: 在后台创建动态时，`type`字段可以自定义，比如`type=announcement`表示公告类型。

### Q: 时间格式如何计算？

A: 系统会自动计算相对时间：
- 小于1分钟：刚才
- 小于1小时：X分钟前
- 小于1天：X小时前
- 小于30天：X天前
- 超过30天：显示日期（2024-01-22）

### Q: 如何按分类筛选常见问题？

A: 在API调用时传入`category`参数，例如：`GET /api/faq/list?category=BP诊断`

## 文件清单

创建的文件包括：

**数据库**：
- `/public/install/news_and_faq.sql` - 数据库表和初始数据

**最新动态模块**：
- `/app/model/gxhc/News.php` - 数据模型
- `/app/dao/gxhc/NewsDao.php` - 数据访问对象
- `/app/services/gxhc/NewsServices.php` - 业务逻辑服务
- `/app/adminapi/controller/v1/gxhc/News.php` - 后台管理控制器
- `/app/api/controller/v1/gxhc/NewsController.php` - 前端API控制器

**常见问题模块**：
- `/app/model/gxhc/Faq.php` - 数据模型
- `/app/dao/gxhc/FaqDao.php` - 数据访问对象
- `/app/services/gxhc/FaqServices.php` - 业务逻辑服务
- `/app/adminapi/controller/v1/gxhc/Faq.php` - 后台管理控制器
- `/app/api/controller/v1/gxhc/FaqController.php` - 前端API控制器

**路由配置**：
- `/app/adminapi/route/gxhc.php` - 后台路由（已更新）
- `/app/api/route/v1.php` - 前端路由（已更新）

**文档**：
- `/NEWS_FAQ_README.md` - 本说明文档

## 技术支持

如有问题，请联系技术团队或查看CRMEB官方文档。
