<?php
	$addressid = $_GPC['addressid'];
	$mrsid = $_GPC['mrsid'];
	$mrs = pdo_fetch("select * from".tablename('beauty_beauticians')."where id = {$mrsid}");
	$address = pdo_fetch("select * from".tablename('beauty_address')."where id = {$addressid}");
	$add = $address['name'].$address['detail'];
	$date = $_GPC['date'];
	$goodsid = $_GPC['goodsid'];
	$goods = pdo_fetch("select * from".tablename('beauty_goods')."where id = {$goodsid}");
	if (checksubmit('submit')) {
		$name = $_GPC['name'];
		$mobile = $_GPC['mobile'];
		$data = array(
			'uniacid' => $_W['uniacid'],
			'openid' => $_W['openid'],
			'ordersn' => date('md') . random(4, 1),
			'price' => $goods['price'],
			'status' => 0,//订单状态，-1取消状态，0普通状态，1为已付款，2为已发货，3为成功
			'address' => $add,
			'gid' => $goodsid,
			'name'=>$name,
			'mobile'=>$mobile,
			'order_time'=>$date,
			'mrsid'=>$mrsid,
			'createtime' => TIMESTAMP
		);
		pdo_insert('beauty_orders', $data);
		$service_num = $mrs['service_num'];
		$service_num = $service_num+1;
		pdo_update('beauty_beauticians',array('service_num'=>$service_num),array('id'=>$mrsid));
		$orderid = pdo_insertid();
		header("location: " .  $this->createMobileUrl('pay', array('orderid' => $orderid)));
		
	}
	include $this -> template('orderconfirm');
?>
