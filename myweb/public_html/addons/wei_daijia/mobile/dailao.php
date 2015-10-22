<?php
global $_GPC, $_W;
$price = pdo_fetch("select *from".tablename("daijia_price")."where type=2 and uniacid = {$_W['uniacid']}");
if (checksubmit('submit')) {
	$radio = $_GPC['radio'];
	header("location: " . $this -> createMobileUrl('orderconfirm', array('pricetype'=>$radio, 'sub' => 'dailao')));

}
include $this -> template('dailao');
?>