<?php
	global $_W, $_GPC;
	$this->getuserinfo();
	$orderid = $_GPC['orderid'];
	if (empty($orderid)) {
        message('抱歉，参数错误！', '', 'error');
    }else{
    		$order = pdo_fetch("SELECT * FROM " . tablename('tg_order') . " WHERE id ='{$orderid}'");
			$goods = pdo_fetch("SELECT * FROM " . tablename('tg_goods') . " WHERE id ='{$order['g_id']}'");
    		$params['tid'] = $order['orderno'];
    		$params['user'] = $_W['fans']['from_user'];
    		$params['fee'] = $order['price'];
    		$params['title'] = $goods['gname'];
    		$params['ordersn'] = $order['orderno'];
    }
	$params['module'] = "feng_fightgroups";
	include $this->template('pay');
?>