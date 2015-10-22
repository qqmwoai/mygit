<?php
global $_GPC, $_W;
$type = $_GPC['type'];
$profile = pdo_fetch("SELECT * FROM " . tablename('daijia_member') . " WHERE uniacid ='{$_W['uniacid']}'  and from_user = '{$_W['openid']}'");
if($type!=''){
	$price = pdo_fetch("select * from".tablename('daijia_price')."where status = 1 and type={$type}");
	include $this->template('price');
}else{
	$daijia = pdo_fetch("select * from".tablename('daijia_price')."where status = 1 and type=0");
	$daibu = pdo_fetch("select * from".tablename('daijia_price')."where status = 1 and type=1");
	include $this->template('price2');
}


?>