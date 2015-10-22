<?php

$sql = "
CREATE TABLE IF NOT EXISTS `ims_ewei_shop_designer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL DEFAULT '0' COMMENT '公众号',
  `pagename` varchar(255) NOT NULL DEFAULT '' COMMENT '页面名称',
  `pagetype` tinyint(3) NOT NULL DEFAULT '0' COMMENT '页面类型',
  `pageinfo` text NOT NULL,
  `createtime` varchar(255) NOT NULL DEFAULT '' COMMENT '页面创建时间',
  `keyword` varchar(255) DEFAULT '',
  `savetime` varchar(255) NOT NULL DEFAULT '' COMMENT '页面最后保存时间',
  `setdefault` tinyint(3) NOT NULL DEFAULT '0' COMMENT '默认页面',
  `datas` text NOT NULL COMMENT '数据',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_pagetype` (`pagetype`),
  FULLTEXT KEY `idx_keyword` (`keyword`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ims_ewei_shop_perm_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `uniacid` int(11) DEFAULT '0',
  `name` varchar(255) DEFAULT '',
  `type` varchar(255) DEFAULT '',
  `op` text,
  `createtime` int(11) DEFAULT '0',
  `ip` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_uniacid` (`uniacid`),
  FULLTEXT KEY `idx_type` (`type`),
  FULLTEXT KEY `idx_op` (`op`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ims_ewei_shop_perm_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT '0',
  `plugins` text,
  PRIMARY KEY (`id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_acid` (`acid`),
  KEY `idx_type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ims_ewei_shop_perm_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `rolename` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `perms` text,
  `deleted` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ims_ewei_shop_perm_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `uid` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT '',
  `password` varchar(255) DEFAULT '',
  `roleid` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `perms` text,
  `deleted` tinyint(3) DEFAULT '0',
  `realname` varchar(255) DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_uid` (`uid`),
  KEY `idx_roleid` (`roleid`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `ims_ewei_shop_plugin` (`id`, `displayorder`, `identity`, `name`, `version`, `author`, `status`) VALUES
(1, 1, 'qiniu', '七牛存储', '1.0', '官方', 1),
(2, 2, 'taobao', '淘宝助手', '1.0', '官方', 1),
(3, 3, 'commission', '人人分销', '1.0', '官方', 1),
(4, 4, 'poster', '超级海报', '1.2', '官方', 1),
(5, 5, 'verify', 'O2O核销', '1.0', '官方', 1),
(6, 6, 'tmessage', '会员群发', '1.0', '官方', 1),
(7, 7, 'perm', '分权系统', '1.0', '官方', 0),
(8, 8, 'sale', '营销宝', '1.0', '官方', 0),
(9, 9, 'designer', '店铺装修', '1.0', '官方', 0);
  
  

";
pdo_query($sql);

 if(!pdo_fieldexists('ewei_shop_commission_shop', 'selectgoods')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_commission_shop')." ADD   `selectgoods` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_commission_shop', 'selectcategory')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_commission_shop')." ADD   `selectcategory` tinyint(3) DEFAULT '0';");
}
if(!pdo_fieldexists('ewei_shop_commission_shop', 'goodsids')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_commission_shop')." ADD   `goodsids` text;");
}



 if(!pdo_fieldexists('ewei_shop_goods', 'noticetype')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." ADD  `noticetype` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_goods', 'needfollow')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." ADD  `needfollow` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_goods', 'followtip')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." ADD  `followtip` varchar(255) DEFAULT '0';");
}
if(!pdo_fieldexists('ewei_shop_goods', 'followurl')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." ADD  `followurl` varchar(255) DEFAULT '0';");
}
if(!pdo_fieldexists('ewei_shop_goods', 'deduct')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_goods')." ADD  `deduct` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('ewei_shop_member', 'agentselectgoods')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_member')." ADD    `agentselectgoods` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_order', 'verifytime')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD   `verifytime` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_order', 'verifystoreid')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD   `verifystoreid` int(11) DEFAULT '0';");
}

 if(!pdo_fieldexists('ewei_shop_order', 'deductprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `deductprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('ewei_shop_order', 'deductcredit')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `deductcredit` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_order', 'deductcredit2')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `deductcredit2` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('ewei_shop_order', 'deductenough')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `deductenough` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('ewei_shop_plugin', 'status')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_plugin')." ADD    `status` tinyint(3) DEFAULT '0';");
}

