<?php
	$beauticians = pdo_fetchall("select * from".tablename("beauty_beauticians")."where uniacid={$_W['uniacid']}");
	include $this -> template('beautician');
?>
