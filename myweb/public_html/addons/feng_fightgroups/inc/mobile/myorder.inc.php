<?php
global $_W, $_GPC;
$this->getuserinfo();
load()->model('mc');
$this->cancleorder();
$share_data = $this -> module['config'];
session_start();
$_SESSION['goodsid'] = '';
$_SESSION['tuan_id'] = '';
$_SESSION['groupnum'] = '';
$op = intval($_GPC['op']);
//op=0对应获取全部订单,op=1对应获取待付款订单,op=2对应获取待收货订单
$openid = $_W['openid'];
//用户的openid
//获取当前用户全部订单信息
//取消订单
$type = $_GPC['type'];
$orderno = $_GPC['orderno'];
if ($type == 'cancel' && !empty($orderno)) {
	$ordermy = pdo_fetch("select * from" . tablename('tg_order') . "where orderno='{$orderno}' and uniacid={$_W['uniacid']}");
	$goodsmy = pdo_fetch("select * from" . tablename('tg_goods') . "where id='{$ordermy['g_id']}' and uniacid={$_W['uniacid']}");
	$ret = pdo_update('tg_order', array('status' => 5), array('orderno' => $orderno));
	/*取消订单模板消息*/
	require_once IA_ROOT . '/addons/feng_fightgroups/source/Message.class.php';
	load() -> model('account');
	$access_token = WeAccount::token();
	$url1 = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $access_token . "";
	$url2 = '';
	$sendmessage = new Message();
	$res = $sendmessage -> cancelorder($openid, $ordermy['price'], $goodsmy['gname'], $orderno, $this, $url1, $url2);
	/*取消订单模板消息*/
}
if (empty($op)) {
	$op = 0;
}
if ($op == 0) {
	$sql = 'SELECT * FROM ' . tablename('tg_order') . ' WHERE openid = :openid  and uniacid = :uniacid ORDER BY id desc';
	//从订单信息表里面取得数据的sql语句
	$params = array(':openid' => $openid, ':uniacid' => $_W['uniacid']);
	$orders = pdo_fetchall($sql, $params);
	foreach ($orders as $key => $value) {
		$goods = pdo_fetch("select * from" . tablename('tg_goods') . "where id='{$value['g_id']}' and uniacid={$_W['uniacid']}");
		$orders[$key]['gid'] = $goods['id'];
		$orders[$key]['gimg'] = $goods['gimg'];
		$orders[$key]['gname'] = $goods['gname'];
	}
} elseif ($op == 1) {//获取当前用户待付款订单信息
	$sql = 'SELECT * FROM ' . tablename('tg_order') . ' WHERE openid = :openid  and uniacid = :uniacid  and status = :status ORDER BY id desc';
	//从订单信息表里面取得数据的sql语句
	$params = array(':openid' => $openid, ':uniacid' => $_W['uniacid'], ':status' => 0);
	$orders = pdo_fetchall($sql, $params);
	foreach ($orders as $key => $value) {
		$goods = pdo_fetch("select * from" . tablename('tg_goods') . "where id='{$value['g_id']}' and uniacid={$_W['uniacid']}");
		$orders[$key]['id'] = $goods['id'];
		$orders[$key]['gimg'] = $goods['gimg'];
		$orders[$key]['gname'] = $goods['gname'];
	}
} elseif ($op == 2) {//获取当前用户待收货订单信息 在数据表里status = 2代表待收货
	$sql = 'SELECT * FROM ' . tablename('tg_order') . ' WHERE openid = :openid  and uniacid = :uniacid  and status = :status ORDER BY id desc';
	//从订单信息表里面取得数据的sql语句
	$params = array(':openid' => $openid, ':uniacid' => $_W['uniacid'], ':status' => 2);
	$orders = pdo_fetchall($sql, $params);
	foreach ($orders as $key => $value) {
		$goods = pdo_fetch("select * from" . tablename('tg_goods') . "where id='{$value['g_id']}' and uniacid={$_W['uniacid']}");
		$orders[$key]['gid'] = $goods['id'];
		$orders[$key]['gimg'] = $goods['gimg'];
		$orders[$key]['gname'] = $goods['gname'];
		$orders[$key]['freight'] = $goods['freight'];

	}
}
//获取每一个订单中的商品的信息
$sql_order = 'SELECT g_id FROM ' . tablename('tg_order') . ' WHERE openid = :openid  and uniacid = :uniacid';
$sql_order = 'SELECT gname, gimg FROM ' . tablename('tg_goods') . ' WHERE uniacid = :uniacid  and id in (' . $sql_order . ')';
//获取全部订单的订单数量
$sql_0 = 'SELECT count(*) as "num" FROM ' . tablename('tg_order') . ' a,' . tablename('tg_goods') . ' b' . ' WHERE a.openid = :openid  and a.uniacid = :uniacid and b.id = a.g_id';
//从订单信息表里面取得数据的sql语句
$params_0 = array(':openid' => $openid, ':uniacid' => $_W['uniacid']);
$orders_num_0 = pdo_fetch($sql_0, $params_0);
if (empty($orders_num_0)) {
	$orders_num_0 = 0;
}
//获取待付款订单的订单数量
$sql_1 = 'SELECT count(*) as "num" FROM ' . tablename('tg_order') . ' a,' . tablename('tg_goods') . ' b' . ' WHERE a.openid = :openid  and a.uniacid = :uniacid and b.id = a.g_id and status = 0';
//从订单信息表里面取得数据的sql语句
$params_1 = array(':openid' => $openid, ':uniacid' => $_W['uniacid']);
$orders_num_1 = pdo_fetch($sql_1, $params_1);
if (empty($orders_num_1)) {
	$orders_num_1 = 0;
}
//获取待收货订单的订单数量
$sql_2 = 'SELECT count(*) as "num" FROM ' . tablename('tg_order') . ' a,' . tablename('tg_goods') . ' b' . ' WHERE a.openid = :openid  and a.uniacid = :uniacid and b.id = a.g_id and status = 2';
//从订单信息表里面取得数据的sql语句
$params_2 = array(':openid' => $openid, ':uniacid' => $_W['uniacid']);
$orders_num_2 = pdo_fetch($sql_2, $params_2);
if (empty($orders_num_2)) {
	$orders_num_2 = 0;
}
include $this -> template('myorder');
?>