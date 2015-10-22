<?php

	$op = intval($_GPC['op']); //op=0对应获取全部订单,op=1对应获取待付款订单,op=2对应获取待收货订单

	$openid = $_W['openid'];//用户的openid
			$sql = 'SELECT * FROM '.tablename('daijia_orders').' WHERE openid = :openid  and uniacid = :uniacid'; //从订单信息表里面取得数据的sql语句
			$params = array(':openid'=>$openid , ':uniacid'=>$weid);
			$orders = pdo_fetchall($sql, $params); 
	include $this->template('myorder');

	

?>