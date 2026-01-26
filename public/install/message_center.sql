-- 消息中心主表
-- 存储所有消息：定向消息(uid>0)和广播消息(uid=0, is_broadcast=1)
CREATE TABLE IF NOT EXISTS `eb_message_center` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '消息ID',
  `uid` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '接收用户ID，0表示广播消息',
  `category` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '消息分类：1=资产变动,2=服务进度,3=系统公告,4=内容/活动推送',
  `title` varchar(256) NOT NULL DEFAULT '' COMMENT '消息标题',
  `content` text COMMENT '消息内容（系统公告支持富文本）',
  `icon_type` varchar(50) NOT NULL DEFAULT 'default' COMMENT '左侧图标类型：lightning=闪电,document=文档,megaphone=喇叭,activity=活动,default=默认',
  `jump_url` varchar(512) NOT NULL DEFAULT '' COMMENT '跳转链接（内部页面路径或外部URL）',
  `jump_type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '跳转类型：0=无跳转,1=内部页面,2=外部链接',
  `extra_data` text COMMENT '附加数据JSON（如order_id、report_id等业务关联数据）',
  `is_broadcast` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否广播消息：0=定向,1=广播全部用户',
  `look` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '已读状态：0=未读,1=已读（仅定向消息使用）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '状态：0=禁用,1=启用',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '是否删除：0=正常,1=已删除',
  `admin_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建管理员ID，0表示系统自动生成',
  `add_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间戳',
  PRIMARY KEY (`id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_category` (`category`),
  KEY `idx_is_broadcast` (`is_broadcast`),
  KEY `idx_add_time` (`add_time`),
  KEY `idx_uid_category_del` (`uid`, `category`, `is_del`),
  KEY `idx_broadcast_status` (`is_broadcast`, `status`, `is_del`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息中心主表';

-- 广播消息已读记录表
-- 记录用户对广播消息的已读状态
CREATE TABLE IF NOT EXISTS `eb_message_center_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `message_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '消息ID',
  `uid` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `add_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '阅读时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_message_uid` (`message_id`, `uid`),
  KEY `idx_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广播消息已读记录表';
