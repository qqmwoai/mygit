<?php
	$orderid = $_GPC['orderid'];
	if (empty($orderid)) {
        message('抱歉，参数错误！', '', 'error');
    }else{
    	 		
		    		$order = pdo_fetch("SELECT * FROM " . tablename('daijia_orders') . " WHERE id ={$orderid}");
					
		    		$params['tid'] = $order['ordersn'];
		    		$params['user'] = $_W['fans']['from_user'];
		    		$params['fee'] = $order['price'];
		    		$params['title'] = $_W['account']['name'];
		    		$params['ordersn'] = $order['ordersn'];
		    	

    }
	include $this->template('pay');
?>