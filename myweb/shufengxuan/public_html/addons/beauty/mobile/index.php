<?php

	$type_face = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE uniacid = '{$_W['uniacid']}' and gtype=1");
	$type_body = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE uniacid = '{$_W['uniacid']}' and gtype=2");
	$type_yangshen = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE uniacid = '{$_W['uniacid']}' and gtype=3");
	$type_zuhe = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE uniacid = '{$_W['uniacid']}' and gtype=4");
	$advs = pdo_fetchall("SELECT * FROM " . tablename('beauty_adv') . " WHERE weid = '{$_W['uniacid']}' and enabled=1");
	include $this -> template('index');
?>
