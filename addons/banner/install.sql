CREATE TABLE `__PREFIX__banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL COMMENT '轮播图名称',
  `image` varchar(255) NOT NULL COMMENT '图片',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1=启用,2=无效',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `remark` varchar(255) CHARACTER SET utf8mb4 NOT NULL COMMENT '备注说明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='轮播图管理';
