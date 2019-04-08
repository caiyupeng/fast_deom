CREATE TABLE `__PREFIX__wechatapp_config` (
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
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态:0=未启用,1=启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='微信小程序配置表';

CREATE TABLE `__PREFIX__wechatapp_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT '用户openid',
  `nickname` varchar(50) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '昵称',
  `sex` tinyint(1) unsigned DEFAULT '0' COMMENT '性别:0=未知,1=男,2=女',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `country` varchar(50) DEFAULT NULL COMMENT '国家',
  `headimgurl` varchar(200) DEFAULT NULL COMMENT '头像',
  `language` varchar(50) DEFAULT NULL COMMENT '语言',
  `createtime` int(10) unsigned DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  `realname` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `phone` varchar(20) DEFAULT NULL COMMENT '电话',
  `address` varchar(255) DEFAULT NULL COMMENT '地址',
  `score` int(11) DEFAULT '0' COMMENT '积分',
  `isscore` tinyint(1) DEFAULT '0' COMMENT '是否第一次赋值',
  `company` varchar(255) DEFAULT NULL COMMENT '所在公司',
  `job` varchar(255) DEFAULT NULL COMMENT '职位',
  `birthday` date DEFAULT NULL COMMENT '生日',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COMMENT='微信小程序人员表';

