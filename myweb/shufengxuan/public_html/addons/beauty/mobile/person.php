<?php
	$profile = pdo_fetch("SELECT * FROM " . tablename('beauty_member') . " WHERE uniacid ='{$_W['uniacid']}' and from_user = '{$_W['openid']}'");
	include $this->template('person');
?>
