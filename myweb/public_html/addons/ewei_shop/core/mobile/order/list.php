<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
$operation = !empty($_GPC['op'])? $_GPC['op'] : 'display';
$openid = m('user') -> getOpenid();
$uniacid = $_W['uniacid'];
if ($_W['isajax']) {
	if ($operation == 'display') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 5;
		$status = $_GPC['status'];
		$condition = " and openid=:openid  and userdeleted=0 and deleted=0 and uniacid=:uniacid ";
		$params = array(':uniacid' => $uniacid, ':openid' => $openid);
		if ($status != '') {
			if ($status != 4) {
				$condition .= ' and status=' . intval($status);
			} else {
				$condition .= ' and refundid<>0';
			} 
		} else {
			$condition .= ' and status<>-1';
		} 
		$list = pdo_fetchall("select id,ordersn,price,status,iscomment,isverify,verified,iscomment,refundid,expresscom,express,expresssn from " . tablename('ewei_shop_order') . " where 1 {$condition} order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
		$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . " where 1 {$condition}", $params);
		foreach($list as &$row) {
			$sql = 'SELECT og.goodsid,og.total,g.title,g.thumb,og.price,og.optionname as optiontitle,og.optionid FROM ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join ' . tablename('ewei_shop_goods') . ' g on og.goodsid = g.id ' . ' where og.orderid=:orderid order by og.id asc';
			$row['goods'] = set_medias(pdo_fetchall($sql, array(':orderid' => $row['id'])), 'thumb');
			$row['goodscount'] = count($row['goods']);
			switch ($row['status']) {
				case "-1": $status = "已取消";
					break;
				case "0": $status = "待付款";
					break;
				case "1": $status = "待发货";
					break;
				case "2": $status = "待收货";
					break;
				case "3": if (empty($row['iscomment'])) {
						$status = "待评价";
					} else {
						$status = "交易完成";
					} 
					break;
			} 
			$row['statusstr'] = $status;
			if (!empty($row['refundid'])) {
				$row['statusstr'] = '待退款';
			} 
		} 
		unset($row);
		show_json(1, array('total' => $total, 'list' => $list, 'pagesize' => $psize));
	} 
} 
include $this -> template('order/list');
