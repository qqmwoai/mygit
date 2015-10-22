<?php
	$orderid = $_GPC['orderid'];
	if (empty($orderid)) {
        message('抱歉，参数错误！', '', 'error');
    }else{
    		$order = pdo_fetch("SELECT * FROM " . tablename('beauty_orders') . " WHERE id ={$orderid}");
    		$params['tid'] = $order['ordersn'];
    		$params['user'] = $_W['fans']['from_user'];
    		$params['fee'] = $order['price'];
    		$params['title'] = $_W['account']['name'];
    		$params['ordersn'] = $order['ordersn'];
    }
	if($this->module['config']['status'] == 1) {
		include $this->template('pay');
	}else{
		$params['module'] = "beauty";
		include $this->template('ptpay');
	}
?>