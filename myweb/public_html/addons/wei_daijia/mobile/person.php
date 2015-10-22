<?php
    $profile = pdo_fetch("SELECT * FROM " . tablename('daijia_member') . " WHERE uniacid ='{$_W['uniacid']}' and from_user = '{$_W['openid']}'");
	$p = pdo_fetch("select * from".tablename('daijia_price')."where status = 1");
	include $this->template('person');
?>