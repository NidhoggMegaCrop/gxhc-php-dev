-- 实战战报统计表
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
