<?php
	global $_W,$_GPC;
	$profile = pdo_fetch("SELECT * FROM " . tablename('beauty_member') . " WHERE uniacid ='{$_W['uniacid']}' and from_user = '{$_W['openid']}'");
	if (checksubmit('submit')) {
		$nickname = $_GPC['nickname'];
		$mobile = $_GPC['mobile'];
		pdo_update('beauty_member',array('nickname'=>$nickname,'mobile'=>$mobile),array('from_user'=>$_W['openid']));
		echo "<script>alert(' 修改成功!');location.href='".$this->createMobileUrl('person')."';</script>";
		exit;
	}
	include $this->template('changemobile');
?>