<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
$operation = !empty($_GPC['op'])? $_GPC['op'] : 'display';
$openid = m('user') -> getOpenid();
if (empty($openid)) {
	$openid = $_GPC['openid'];
} 
$member = m('member') -> getMember($openid);
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
if ($operation == 'display' && $_W['isajax']) {
	if (empty($orderid)) {
		show_json(0, '参数错误!');
	} 
	$order = pdo_fetch("select * from " . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1' , array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
	if (empty($order)) {
		show_json(0, '订单未找到!');
	} 
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'ewei_shop', ':tid' => $order['ordersn']));
	if (!empty($log) && $log['status'] != '0') {
		show_json(-1, '订单已支付, 无需重复支付!');
	} 
	if (!empty($log) && $log['status'] == '0') {
		pdo_delete('core_paylog', array('plid' => $log['plid']));
		$log = null;
	} 
	$plid = $log['plid'];
	if (empty($log)) {
		$log = array('uniacid' => $uniacid, 'openid' => $member['uid'], 'module' => "ewei_shop", 'tid' => $order['ordersn'], 'fee' => $order['price'], 'status' => 0,);
		pdo_insert('core_paylog', $log);
		$plid = pdo_insertid();
	} 
	$set = m('common') -> getSysset(array('shop', 'pay'));
	$credit = array('success' => false);
	if (is_weixin()) {
		$currentcredit = 0;
		if (isset($set['pay']) && $set['pay']['credit'] == 1) {
			if ($order['deductcredit2'] <= 0) {
				$credit = array('success' => true, 'current' => m('member') -> getCredit($openid, 'credit2'));
			} 
		} 
	} 
	load() -> model('payment');
	$setting = uni_setting($_W['uniacid'], array('payment'));
	$wechat = array('success' => false);
	if (is_weixin()) {
		if (isset($set['pay']) && $set['pay']['weixin'] == 1) {
			if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
				$wechat['success'] = true;
			} 
		} 
	} 
	$alipay = array('success' => false);
	if (isset($set['pay']) && $set['pay']['alipay'] == 1) {
		if (is_array($setting['payment']['alipay']) && $setting['payment']['alipay']['switch']) {
			$alipay['success'] = true;
		} 
	} 
	$unionpay = array('success' => false);
	if (isset($set['pay']) && $set['pay']['unionpay'] == 1) {
		if (is_array($setting['payment']['unionpay']) && $setting['payment']['unionpay']['switch']) {
			$unionpay['success'] = true;
		} 
	} 
	$cash = array('success' => $order['cash'] == 1 && isset($set['pay']) && $set['pay']['cash'] == 1);
	$returnurl = urlencode($this -> createMobileUrl('order/pay', array('orderid' => $orderid)));
	show_json(1, array('order' => $order, 'set' => $set, 'credit' => $credit, 'wechat' => $wechat, 'alipay' => $alipay, 'unionpay' => $unionpay, 'cash' => $cash, 'isweixin' => is_weixin(), 'currentcredit' => $currentcredit, 'returnurl' => $returnurl));
} else if ($operation == 'pay' && $_W['ispost']) {
	$set = m('common') -> getSysset(array('shop', 'pay'));
	$order = pdo_fetch("select * from " . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1' , array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
	if (empty($order)) {
		show_json(0, '订单未找到!');
	} 
	$type = $_GPC['type'];
	if (!in_array($type, array('weixin', 'alipay', 'unionpay'))) {
		show_json(0, '未找到支付方式');
	} 
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'ewei_shop', ':tid' => $order['ordersn']));
	if (empty($log)) {
		show_json(0, '支付出错,请重试!');
	} 
	$plid = $log['plid'];
	$param_title = $set['shop']['name'] . "订单: " . $order['ordersn'];
	if ($type == 'weixin') {
		pdo_update('ewei_shop_order', array('paytype' => 21), array('id' => $order['id']));
		if (!is_weixin()) {
			show_json(0, '非微信环境!');
		} 
		if (empty($set['pay']['weixin'])) {
			show_json(0, '未开启微信支付!');
		} 
		$wechat = array('success' => false);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['user'] = $openid;
		$params['fee'] = $order['price'];
		$params['title'] = $param_title;
		load() -> model('payment');
		$setting = uni_setting($_W['uniacid'], array('payment'));
		if (is_array($setting['payment'])) {
			$options = $setting['payment']['wechat'];
			$options['appid'] = $_W['account']['key'];
			$options['secret'] = $_W['account']['secret'];
			$wechat = m('common') -> wechat_build($params, $options, 0);
			$wechat['success'] = false;
			if (!is_error($wechat)) {
				$wechat['success'] = true;
			} else {
				show_json(0, $wechat['message']);
			} 
		} 
		if (!$wechat['success']) {
			show_json(0, '微信支付参数错误!');
		} 
		show_json(1, array('wechat' => $wechat));
	} else if ($type == 'unionpay') {
		if (empty($set['pay']['unionpay'])) {
			show_json(0, '未开启银联支付!');
		} 
		pdo_update('ewei_shop_order', array('paytype' => 23), array('id' => $order['id']));
		$params = array();
		$params['tid'] = $plid;
		$params['user'] = $openid;
		$params['fee'] = $order['price'];
		$params['title'] = $param_title;
		$sl = base64_encode(json_encode($params));
		$auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
		show_json(1, array('url' => $_W['siteroot'] . "payment/unionpay/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}"));
	} else if ($type == 'alipay') {
		pdo_update('ewei_shop_order', array('paytype' => 22), array('id' => $order['id']));
		$alipay = array('success' => false);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['user'] = $openid;
		$params['fee'] = $order['price'];
		$params['title'] = $param_title;
		load() -> func('communication');
		load() -> model('payment');
		$setting = uni_setting($_W['uniacid'], array('payment'));
		if (is_array($setting['payment'])) {
			$options = $setting['payment']['alipay'];
			$alipay = m('common') -> alipay_build($params, $options, 0, $openid);
			if (!empty($alipay['url'])) {
				$alipay['success'] = true;
			} 
		} 
		show_json(1, array('alipay' => $alipay));
	} 
} else if ($operation == 'complete' && $_W['ispost']) {
	$order = pdo_fetch("select * from " . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1' , array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
	if (empty($order)) {
		show_json(0, '订单未找到!');
	} 
	$type = $_GPC['type'];
	if (!in_array($type, array('weixin', 'alipay', 'credit', 'cash'))) {
		show_json(0, '未找到支付方式');
	} 
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'ewei_shop', ':tid' => $order['ordersn']));
	if (empty($log)) {
		show_json(0, '支付出错,请重试!');
	} 
	$plid = $log['plid'];
	if ($type == 'cash') {
		pdo_update('ewei_shop_order', array('paytype' => 3), array('id' => $order['id']));
		$ret = array();
		$ret['result'] = 'success';
		$ret['type'] = 'cash';
		$ret['from'] = 'return';
		$ret['tid'] = $log['tid'];
		$ret['user'] = $order['openid'];
		$ret['fee'] = $order['price'];
		$ret['weid'] = $_W['uniacid'];
		$ret['uniacid'] = $_W['uniacid'];
		$this -> payResult($ret);
		exit;
	} 
	$ps = array();
	$ps['tid'] = $log['tid'];
	$ps['user'] = $openid;
	$ps['fee'] = $log['fee'];
	$ps['title'] = $log['title'];
	if ($type == 'credit') {
		$credits = m('member') -> getCredit($openid, 'credit2');
		if ($credits < $ps['fee']) {
			show_json(0, "余额不足,请充值");
		} 
		$fee = floatval($ps['fee']);
		$result = m('member') -> setCredit($openid, 'credit2', - $fee, array($_W['member']['uid'], '消费' . $setting['creditbehaviors']['currency'] . ':' . $fee));
		if (is_error($result)) {
			show_json(0, $result['message']);
		} 
		$record = array();
		$record['status'] = '1';
		$record['type'] = 'cash';
		pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		pdo_update('ewei_shop_order', array('paytype' => 1), array('id' => $order['id']));
		$ret = array();
		$ret['result'] = 'success';
		$ret['type'] = $log['type'];
		$ret['from'] = 'return';
		$ret['tid'] = $log['tid'];
		$ret['user'] = $log['openid'];
		$ret['fee'] = $log['fee'];
		$ret['weid'] = $log['weid'];
		$ret['uniacid'] = $log['uniacid'];
		$this -> payResult($ret);
	} else if ($type == 'weixin') {
		$record = array();
		$record['status'] = '1';
		$record['type'] = 'wechat';
		pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		$ret = array();
		$ret['result'] = 'success';
		$ret['type'] = 'wechat';
		$ret['from'] = 'return';
		$ret['tid'] = $log['tid'];
		$ret['user'] = $log['openid'];
		$ret['fee'] = $log['fee'];
		$ret['weid'] = $log['weid'];
		$ret['uniacid'] = $log['uniacid'];
		$ret['deduct'] = intval($_GPC['deduct']) == 1;
		$this -> payResult($ret);
	} else if ($type == 'alipay') {
		$record = array();
		$record['status'] = '1';
		$record['type'] = 'alipay';
		pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		$ret = array();
		$ret['result'] = 'success';
		$ret['type'] = 'alipay';
		$ret['from'] = 'return';
		$ret['tid'] = $log['tid'];
		$ret['user'] = $log['openid'];
		$ret['fee'] = $log['fee'];
		$ret['weid'] = $log['weid'];
		$ret['uniacid'] = $log['uniacid'];
		$ret['deduct'] = intval($_GPC['deduct']) == 1;
		$this -> payResult($ret);
	} 
} else if ($operation == 'return') {
	$tid = $_GPC['out_trade_no'];
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'ewei_shop', ':tid' => $tid));
	if (empty($log)) {
		die('支付出现错误，请重试!');
	} 
	if ($log['status'] != 1) {
		$record = array();
		$record['status'] = '1';
		$record['type'] = 'alipay';
		pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		$ret = array();
		$ret['result'] = 'success';
		$ret['type'] = 'alipay';
		$ret['from'] = 'return';
		$ret['tid'] = $log['tid'];
		$ret['user'] = $log['openid'];
		$ret['fee'] = $log['fee'];
		$ret['weid'] = $log['weid'];
		$ret['uniacid'] = $log['uniacid'];
		$this -> payResult($ret);
	} 
	die('<div style="width:94%;padding:0 3%px;font-size:24px;">支付成功, 请关闭到浏览器, 返回到微信点击返回!</div>');
} 
if ($operation == 'display') {
	include $this -> template('order/pay');
} 
