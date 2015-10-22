<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
$openid = m('user') -> getOpenid();
$set = m('common') -> getSysset(array('shop', 'trade'));
if ($_W['isajax']) {
	$member = m('member') -> getInfo($openid);
	$member['credit1'] = number_format($member['credit1'], 0);
	$member['credit2'] = number_format($member['credit2'], 2);
	$level = array('levelname' => empty($set['shop']['levelname'])?'普通会员':$set['shop']['levelname']);
	if (!empty($member['level'])) {
		$level = m('member') -> getLevel($openid);
	} 
	$orderparams = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
	$order = array('status0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and status=0  and uniacid=:uniacid limit 1', $orderparams), 'status1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and status=1 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and status=2 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and refundid<>0 and uniacid=:uniacid limit 1', $orderparams),);
	if (mb_strlen($member['nickname'], 'utf-8') > 6) {
		$member['nickname'] = mb_substr($member['nickname'], 0, 6, 'utf-8');
	} 
	show_json(1, array('member' => $member, 'order' => $order, 'level' => $level));
} 
include $this -> template('member/center');
