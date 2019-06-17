CREATE TABLE `__PREFIX__article_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `name` varchar(30) NOT NULL COMMENT '分类名称',
  `weigh` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  `path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='文章类别';


CREATE TABLE `__PREFIX__article_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `article_category_id` int(4) unsigned DEFAULT NULL COMMENT '类型',
  `article_category_path` varchar(255) DEFAULT NULL,
  `images` varchar(1000) DEFAULT NULL COMMENT '图片',
  `avatar` varchar(512) DEFAULT NULL COMMENT '头图',
  `author` varchar(50) DEFAULT '' COMMENT '作者',
  `resource` varchar(250) DEFAULT '' COMMENT '来源',
  `desc` varchar(800) DEFAULT NULL COMMENT '简介',
  `content` text NOT NULL COMMENT '文章详情',
  `virtualreadnum` int(10) unsigned DEFAULT '0' COMMENT '初始阅读数',
  `readnum` int(10) unsigned DEFAULT '0' COMMENT '阅读数',
  `status` enum('1','2') NOT NULL DEFAULT '2' COMMENT '是否上线:1=是,2=否',
  `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  KEY `category_id` (`article_category_id`) USING BTREE COMMENT '类型'
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='文章内容';

