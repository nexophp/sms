CREATE TABLE IF NOT EXISTS `sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drive_type` varchar(50) NOT NULL DEFAULT '' COMMENT '短信驱动',
  `code` varchar(50) NOT NULL  COMMENT '模板代码',
  `name` varchar(100) NOT NULL COMMENT '模板名称',
  `subject` varchar(255)  NULL COMMENT '短信模板',
  `content` text  NULL COMMENT '短信内容',
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信模板';