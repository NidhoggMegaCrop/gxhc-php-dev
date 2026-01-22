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

-- 插入最新动态示例数据
INSERT INTO `eb_g_news` (`title`, `content`, `author`, `type`, `sort`, `status`, `publish_time`, `add_time`, `update_time`) VALUES
('导师点评:车水的的的...', 'ju DSEJF\n\n导师点评详细内容...', '导师点评', 'featured', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('导师点评:车水的的的...', '这是一条最新的导师点评动态内容', '导师点评', 'news', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('导师点评:车水的的的...', '这是2小时前的导师点评动态', '导师点评', 'news', 2, 1, UNIX_TIMESTAMP() - 7200, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('导师点评:车水的的的...', '这是1天前的导师点评动态', '导师点评', 'news', 3, 1, UNIX_TIMESTAMP() - 86400, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入常见问题示例数据
INSERT INTO `eb_g_faq` (`question`, `answer`, `category`, `sort`, `status`, `add_time`, `update_time`) VALUES
('如何提升 BP 诊断的准确度？', '尽量上传完整 PDF格式的BBP，确保文件清晰完整，包含所有必要信息。建议文件大小在10MB以内，格式为PDF最佳。', 'BP诊断', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('如何提升 BP 诊断的准确度？', '尽量上传完整 PDF格式的BBP，同时确保商业计划书内容完整，包括市场分析、竞争优势、财务预测等关键部分。', 'BP诊断', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('如何提升 BP 诊断的准确度？', '尽量上传完整 PDF格式的BBP，建议在上传前检查文件是否可以正常打开，文字是否清晰可读。', 'BP诊断', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
