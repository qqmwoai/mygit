<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
$operation = !empty($_GPC['op'])? $_GPC['op'] : 'display';
$openid = m('user') -> getOpenid();
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['id']);
if ($_W['isajax']) {
	$order = pdo_fetch('select * from ' . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
	if (empty($order)) {
		show_json(0);
	} 
	$goods = pdo_fetchall("select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids  from " . tablename('ewei_shop_order_goods') . " og " . " left join " . tablename('ewei_shop_goods') . " g on g.id=og.goodsid " . " where og.orderid=:orderid and og.uniacid=:uniacid ", array(':uniacid' => $uniacid, ':orderid' => $orderid));
	$goods = set_medias($goods, 'thumb');
	$order['goodstotal'] = count($goods);
	$order['finishtime'] = date('Y-m-d H:i:s', $order['finishtime']);
	$address = false;
	$carrier = false;
	$stores = array();
	if ($order['isverify'] == 1) {
		$storeids = array();
		foreach($goods as $g) {
			if (!empty($g['storeids'])) {
				$storeids = array_merge(explode(',', $g['storeids']), $storeids);
			} 
		} 
		if (empty($storeids)) {
			$stores = pdo_fetchall('select * from ' . tablename('ewei_shop_store') . ' where  uniacid=:uniacid and status=1', array(':uniacid' => $_W['uniacid']));
		} else {
			$stores = pdo_fetchall('select * from ' . tablename('ewei_shop_store') . ' where id in (' . implode(',', $storeids) . ') and uniacid=:uniacid and status=1', array(':uniacid' => $_W['uniacid']));
		} 
	} else {
		if ($order['dispatchtype'] == 0) {
			$address = pdo_fetch('select realname,mobile,address from ' . tablename('ewei_shop_member_address') . ' where id=:id limit 1', array(':id' => $order['addressid']));
		} 
	} 
	if ($order['dispatchtype'] == 1 || $order['isverify'] == 1) {
		$carrier = unserialize($order['carrier']);
	} 
	$set = set_medias(m('common') -> getSysset('shop'), 'logo');
	show_json(1, array('order' => $order, 'goods' => $goods, 'address' => $address, 'carrier' => $carrier, 'stores' => $stores, 'isverify' => $isverify, 'set' => $set));
} 
include $this -> template('order/detail');
