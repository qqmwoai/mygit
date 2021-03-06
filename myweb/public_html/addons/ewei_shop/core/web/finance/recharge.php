<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php');
$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$id = intval($_GPC['id']);
$profile = m('member') -> getInfo($id);
if ($op == 'credit1') {
	ca('finance.recharge.credit1');
	if ($_W['ispost']) {
		m('member') -> setCredit($profile['openid'], 'credit1', $_GPC['num'], array());
		plog('finance.recharge.credit1', "积分充值 充值积分: {$_GPC['num']} <br/>会员信息: ID: {$profile['id']} /  {$profile['openid']}/{$profile['nickname']}/{$profile['realname']}/{$profile['mobile']}");
		message('充值成功!', referer(), 'success');
	} 
	$profile['credit1'] = m('member') -> getCredit($profile['openid'], 'credit1');
} elseif ($op == 'credit2') {
	ca('finance.recharge.credit2');
	if ($_W['ispost']) {
		m('member') -> setCredit($profile['openid'], $credittype = 'credit2', $_GPC['num'], $log = array());
		$set = m('common') -> getSysset('shop');
		$logno = m('common') -> createNO('member_log', 'logno', 'RC');
		$data = array('openid' => $profile['openid'], 'logno' => $logno, 'uniacid' => $_W['uniacid'], 'type' => '0', 'createtime' => TIMESTAMP, 'status' => '1', 'title' => $set['name'] . "会员充值", 'money' => $_GPC['num'], 'rechargetype' => 'system',);
		pdo_insert('ewei_shop_member_log', $data);
		$logid = pdo_insertid();
		m('member') -> setRechargeCredit($openid, $log['money']);
		m('notice') -> sendMemberLogMessage($logid);
		plog('finance.recharge.credit2', "余额充值 充值金额: {$_GPC['num']} <br/>会员信息:  ID: {$profile['id']} / {$profile['openid']}/{$profile['nickname']}/{$profile['realname']}/{$profile['mobile']}");
		message('充值成功!', referer(), 'success');
	} 
	$set = m('common') -> getSysset();
	$profile['credit2'] = m('member') -> getCredit($profile['openid'], 'credit2');
} 
load() -> func('tpl');
include $this -> template('web/finance/recharge');
