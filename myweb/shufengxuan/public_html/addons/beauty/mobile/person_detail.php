<?php
	global $_W,$_GPC;
	$status = $_GPC['status'];
	if($status == 100){
		$orders = pdo_fetchall("select * from".tablename('beauty_orders')."where uniacid = {$_W['uniacid']}");
		foreach($orders as $key=>$value){
			$goods = pdo_fetch("select * from".tablename('beauty_goods')."where uniacid = {$_W['uniacid']} and id={$value['gid']}");
			$mrs = pdo_fetch("select * from".tablename('beauty_beauticians')."where uniacid = {$_W['uniacid']} and id={$value['mrsid']}");
			$orders[$key]['mrsname'] = $mrs['name'];
			$orders[$key]['mrsthumb'] = $mrs['thumb'];
			$orders[$key]['mrsid'] = $mrs['id'];
			$orders[$key]['goodsid'] = $goods['id'];
			$orders[$key]['goodsthumb'] = $goods['thumb'];
			$orders[$key]['goodsname'] = $goods['gname'];
			$orders[$key]['goodsprice'] = $value['price'];
			$orders[$key]['goodsdetail'] = $goods['gdesc'];
			$orders[$key]['order_time'] = $value['order_time'];
			$now = time();
			if($value['order_time']-$now<=0){
				$orders[$key]['orderstatus'] = "失效订单";
			}elseif($value['status']==1){
				$orders[$key]['orderstatus'] = "预约中";
			}
		}
	}else{
		$orders = pdo_fetchall("select * from".tablename('beauty_orders')."where status = {$status} and uniacid = {$_W['uniacid']} ");
		foreach($orders as $key=>$value){
			$goods = pdo_fetch("select * from".tablename('beauty_goods')."where uniacid = {$_W['uniacid']} and id={$value['gid']}");
			$mrs = pdo_fetch("select * from".tablename('beauty_beauticians')."where uniacid = {$_W['uniacid']} and id={$value['mrsid']}");
			$orders[$key]['mrsname'] = $mrs['name'];
			$orders[$key]['mrsthumb'] = $mrs['thumb'];
			$orders[$key]['mrsid'] = $mrs['id'];
			$orders[$key]['goodsid'] = $goods['id'];
			$orders[$key]['goodsthumb'] = $goods['thumb'];
			$orders[$key]['goodsname'] = $goods['gname'];
			$orders[$key]['goodsprice'] = $value['price'];
			$orders[$key]['goodsdetail'] = $goods['gdesc'];
			$orders[$key]['order_time'] = $value['order_time'];
			$now = time();
			if($value['order_time']-$now<=0){
				$orders[$key]['orderstatus'] = "失效订单";
			}elseif($value['status']==1){
				$orders[$key]['orderstatus'] = "预约中";
			}
		}
	}
	
	include $this->template('person_detail');
?>
