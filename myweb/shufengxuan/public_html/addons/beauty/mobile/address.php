<?php
	$address = pdo_fetchall("select * from".tablename("beauty_address")."where uniacid={$_W['uniacid']}");
	include $this -> template('address');
?>