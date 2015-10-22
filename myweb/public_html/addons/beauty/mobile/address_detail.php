<?php
	$addressid = $_GPC['addressid'];
	$address=pdo_fetch("select * from".tablename('beauty_address')."where id={$addressid}");
	$mrss = pdo_fetchall("select * from".tablename('beauty_beauticians')."where addressid ={$addressid}");
	$allgoods = array();
	$type_body = array();
	$type_yangshen = array();
	$type_zuhe = array();
	foreach($mrss as $key=> $value){
		$type_face[$key] = pdo_fetchall("select * from".tablename('beauty_goods')."where service_beautician_id = {$value['id']} and  gtype=1");
		$type_body[$key] = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE service_beautician_id = {$value['id']} and gtype=2");
		$type_yangshen[$key] = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE service_beautician_id = {$value['id']} and gtype=3");
		$type_zuhe[$key] = pdo_fetchall("SELECT * FROM " . tablename('beauty_goods') . " WHERE service_beautician_id = {$value['id']} and gtype=4");
	}
	include $this -> template('address_detail');
?>
