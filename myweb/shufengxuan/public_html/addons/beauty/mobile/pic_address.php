<?php
	$goodsid = $_GPC['goodsid'];
	$mrsid = $_GPC['mrsid'];
	$address = pdo_fetchall("SELECT * FROM " . tablename('beauty_address') . " WHERE uniacid = '{$_W['uniacid']}' and status=1 order by id desc");
	if (checksubmit('submit')) {
		$addressid = $_GPC['radio'];//地址ID
		header("location: " .  $this->createMobileUrl('pic_date', array('addressid' => $addressid,'goodsid'=>$goodsid,'mrsid'=>$mrsid)));
		exit;
	}
	
	include $this -> template('pic_address');
?>
