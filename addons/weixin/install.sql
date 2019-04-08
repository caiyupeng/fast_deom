CREATE TABLE `__PREFIX__weixin_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公众号ID',
  `name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '公众号名称',
  `appid` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'appId',
  `appsecret` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'appSecret',
  `token` varchar(50) DEFAULT NULL COMMENT 'token',
  `accesstoken` varchar(255) DEFAULT NULL COMMENT 'accesstoken',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) unsigned DEFAULT NULL COMMENT '删除时间',
  `encodingaeskey` varchar(100) DEFAULT NULL COMMENT '秘钥',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态:1=启用,2=无效',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='微信公众号配置表';

CREATE TABLE `__PREFIX__weixin_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `openid` varchar(80) CHARACTER SET utf8mb4 NOT NULL COMMENT 'openid',
  `name` varchar(50) CHARACTER SET utf8mb4 NOT NULL COMMENT '姓名',
  `nickname` varchar(50) CHARACTER SET utf8mb4 NOT NULL COMMENT '昵称',
  `miniavatar` varchar(500) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '头像',
  `photoavatars` varchar(1000) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '个人照片',
  `sex` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '性别:1=男,2=女',
  `phone` varchar(20) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '电话号码',
  `createtime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=438 DEFAULT CHARSET=utf8 COMMENT='用户管理';

