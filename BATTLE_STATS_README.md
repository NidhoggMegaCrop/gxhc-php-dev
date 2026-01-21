# 实战战报统计管理功能说明

## 功能概述

这个功能允许你在后台管理系统中动态管理实战战报的统计数据（如320个创业者、45位导师、580份报告等），而不再需要在代码中硬编码这些数字。

## 功能特性

- ✅ 后台管理界面：增删改查统计项
- ✅ 动态更新数值：随时修改统计数字
- ✅ 前端API接口：小程序/H5可调用获取最新数据
- ✅ 排序功能：自定义展示顺序
- ✅ 状态控制：可启用/禁用统计项

## 安装步骤

### 1. 创建数据库表

在你的MySQL数据库中执行以下SQL文件：

```bash
# 方法一：使用MySQL命令行
mysql -u用户名 -p密码 数据库名 < public/install/battle_stats.sql

# 方法二：使用phpMyAdmin等图形化工具，导入该SQL文件
```

或者直接执行以下SQL：

```sql
-- 创建表
CREATE TABLE `eb_g_battle_stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `key` varchar(50) NOT NULL DEFAULT '' COMMENT '统计项标识key',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '统计项名称',
  `value` int(11) NOT NULL DEFAULT '0' COMMENT '统计数值',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '单位（个、位、份、家等）',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述说明',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序（越小越靠前）',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='实战战报统计表';

-- 插入初始数据
INSERT INTO `eb_g_battle_stats` (`key`, `name`, `value`, `unit`, `description`, `sort`, `status`, `add_time`, `update_time`) VALUES
('entrepreneurs', '创业者提交诊断', 320, '个', '已完成创业诊断的创业者数量', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('mentors', '导师深度伴用', 45, '位', '深度参与指导的导师数量', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('reports', 'AI诊断报告生成', 580, '份', '已生成的AI诊断报告数量', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('partners', '生态合作伙伴', 8, '家', '生态系统合作伙伴数量', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
```

### 2. 验证安装

安装完成后，可以通过以下方式验证：

```sql
SELECT * FROM eb_g_battle_stats;
```

应该能看到4条初始数据。

## 使用说明

### 后台管理接口

所有后台管理接口都需要管理员权限认证。

#### 1. 获取战报统计列表

```
GET /adminapi/battle_stats/list
```

请求参数：
- `page`: 页码（默认1）
- `limit`: 每页数量（默认10）
- `status`: 状态筛选（可选）
- `keyword`: 名称关键词（可选）

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "key": "entrepreneurs",
        "name": "创业者提交诊断",
        "value": 320,
        "unit": "个",
        "description": "已完成创业诊断的创业者数量",
        "sort": 1,
        "status": 1,
        "add_time": "2024-01-01 00:00:00",
        "update_time": "2024-01-01 00:00:00"
      }
    ],
    "count": 4
  }
}
```

#### 2. 获取所有启用的统计项

```
GET /adminapi/battle_stats/all
```

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": [
    {
      "id": 1,
      "key": "entrepreneurs",
      "name": "创业者提交诊断",
      "value": 320,
      "unit": "个",
      ...
    }
  ]
}
```

#### 3. 获取统计项详情

```
GET /adminapi/battle_stats/detail/:id
```

#### 4. 创建统计项

```
POST /adminapi/battle_stats/create
```

请求参数：
```json
{
  "key": "new_stat",
  "name": "新统计项",
  "value": 100,
  "unit": "个",
  "description": "描述说明",
  "sort": 5,
  "icon": "",
  "status": 1
}
```

#### 5. 更新统计项

```
PUT /adminapi/battle_stats/update/:id
```

请求参数：同创建接口

#### 6. 删除统计项

```
DELETE /adminapi/battle_stats/delete/:id
```

#### 7. 更新统计值（快捷修改数值）

```
PUT /adminapi/battle_stats/update_value/:id
```

请求参数：
```json
{
  "value": 350
}
```

#### 8. 修改状态

```
PUT /adminapi/battle_stats/set_status/:id
```

请求参数：
```json
{
  "status": 1
}
```

### 前端API接口

前端调用无需管理员权限，但需要用户登录认证。

#### 1. 获取战报统计数据

```
GET /api/battle_stats/list
```

响应示例：
```json
{
  "code": 200,
  "msg": "success",
  "data": [
    {
      "id": 1,
      "key": "entrepreneurs",
      "name": "创业者提交诊断",
      "value": 320,
      "unit": "个",
      "description": "已完成创业诊断的创业者数量",
      "icon": "",
      "sort": 1
    },
    {
      "id": 2,
      "key": "mentors",
      "name": "导师深度伴用",
      "value": 45,
      "unit": "位",
      "description": "深度参与指导的导师数量",
      "icon": "",
      "sort": 2
    }
  ]
}
```

#### 2. 根据key获取单个统计项

```
GET /api/battle_stats/get/:key
```

示例：
```
GET /api/battle_stats/get/entrepreneurs
```

## 前端集成示例

### 小程序示例

```javascript
// 在小程序页面中调用
Page({
  data: {
    battleStats: []
  },

  onLoad() {
    this.getBattleStats();
  },

  getBattleStats() {
    wx.request({
      url: 'https://你的域名/api/battle_stats/list',
      method: 'GET',
      header: {
        'token': wx.getStorageSync('token') // 用户登录token
      },
      success: (res) => {
        if (res.data.code === 200) {
          this.setData({
            battleStats: res.data.data
          });
        }
      }
    });
  }
});
```

### WXML模板示例

```xml
<view class="battle-stats">
  <view class="stats-title">Uni 1.0 实时战报</view>

  <view class="stats-grid">
    <block wx:for="{{battleStats}}" wx:key="id">
      <view class="stat-item">
        <view class="stat-value">{{item.value}}</view>
        <view class="stat-unit">{{item.unit}}</view>
        <view class="stat-name">{{item.name}}</view>
      </view>
    </block>
  </view>
</view>
```

### Vue/React示例

```javascript
// Vue示例
export default {
  data() {
    return {
      battleStats: []
    }
  },

  mounted() {
    this.fetchBattleStats();
  },

  methods: {
    async fetchBattleStats() {
      try {
        const response = await this.$http.get('/api/battle_stats/list');
        if (response.data.code === 200) {
          this.battleStats = response.data.data;
        }
      } catch (error) {
        console.error('获取战报数据失败', error);
      }
    }
  }
}
```

## 数据库字段说明

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 主键ID |
| key | varchar(50) | 统计项唯一标识，如：entrepreneurs |
| name | varchar(100) | 统计项名称，如：创业者提交诊断 |
| value | int | 统计数值，如：320 |
| unit | varchar(20) | 单位，如：个、位、份、家 |
| description | varchar(255) | 描述说明 |
| sort | int | 排序，数值越小越靠前 |
| icon | varchar(255) | 图标路径（可选） |
| status | tinyint | 状态：0=禁用，1=启用 |
| add_time | int | 创建时间（Unix时间戳） |
| update_time | int | 更新时间（Unix时间戳） |

## 常见问题

### Q: 如何修改统计数字？

A: 有两种方式：
1. 使用快捷接口：`PUT /adminapi/battle_stats/update_value/:id`，只传value参数
2. 使用完整更新接口：`PUT /adminapi/battle_stats/update/:id`，可以修改所有字段

### Q: 如何添加新的统计项？

A: 调用创建接口 `POST /adminapi/battle_stats/create`，提供所有必要字段即可。

### Q: 前端如何实时获取最新数据？

A: 每次页面加载时调用 `GET /api/battle_stats/list` 接口即可获取最新数据。

### Q: 可以禁用某个统计项吗？

A: 可以，调用 `PUT /adminapi/battle_stats/set_status/:id`，设置status为0即可禁用，前端接口将不会返回禁用的统计项。

## 文件清单

创建的文件包括：

1. **数据库**：
   - `/public/install/battle_stats.sql` - 数据库表和初始数据

2. **模型层**：
   - `/app/model/gxhc/BattleStats.php` - 数据模型

3. **数据访问层**：
   - `/app/dao/gxhc/BattleStatsDao.php` - 数据访问对象

4. **服务层**：
   - `/app/services/gxhc/BattleStatsServices.php` - 业务逻辑服务

5. **控制器**：
   - `/app/adminapi/controller/v1/gxhc/BattleStats.php` - 后台管理控制器
   - `/app/api/controller/v1/gxhc/BattleStatsController.php` - 前端API控制器

6. **路由**：
   - `/app/adminapi/route/gxhc.php` - 后台路由（已更新）
   - `/app/api/route/v1.php` - 前端路由（已更新）

7. **文档**：
   - `/BATTLE_STATS_README.md` - 本说明文档

## 技术支持

如有问题，请联系技术团队或查看CRMEB官方文档。
