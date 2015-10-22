<?php
	global $_W, $_GPC;
	$this->getuserinfo();
	session_start();
	$_SESSION['goodsid']='';
	$_SESSION['tuan_id']='';
	$_SESSION['groupnum']='';
	$share_data = $this -> module['config'];
	$result = $this->getfansinfo($_W['openid']);
	load()->model('account');
	$modules = uni_modules();
	include $this->template('person');
?>
