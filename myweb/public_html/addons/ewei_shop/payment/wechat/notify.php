<?php
error_reporting(0);
define('IN_MOBILE', true);
$input = file_get_contents('php://input');
if (!empty($input) && empty($_GET['out_trade_no'])) {
	$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
	$data = json_decode(json_encode($obj), true);
	if (empty($data)) {
		exit('fail');
	} 
	if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
		exit('fail');
	} 
	$get = $data;
} else {
	$get = $_GET;
} 
require '../../../../framework/bootstrap.inc.php';
$strs = explode(':', $get['attach']);
$_W['uniacid'] = $_W['weid'] = $strs[0];
$type = $strs[1];
$setting = uni_setting($_W['uniacid'], array('payment'));
if (is_array($setting['payment'])) {
	$wechat = $setting['payment']['wechat'];
	if (!empty($wechat)) {
		ksort($get);
		$string1 = '';
		foreach ($get as $k => $v) {
			if ($v != '' && $k != 'sign') {
				$string1 .= "{$k}={$v}&";
			} 
		} 
		$wechat['signkey'] = ($wechat['version'] == 1)? $wechat['key'] : $wechat['signkey'];
		$sign = strtoupper(md5($string1 . "key={$wechat['signkey']}"));
		if ($sign == $get['sign']) {
			if ($type == '0') {
				$tid = $get['out_trade_no'];
				$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';
				$params = array();
				$params[':tid'] = $tid;
				$params[':module'] = 'ewei_shop';
				$log = pdo_fetch($sql, $params);
				if (!empty($log) && $log['status'] == '0') {
					$log['tag'] = iunserializer($log['tag']);
					$log['tag']['transaction_id'] = $get['transaction_id'];
					$record = array();
					$record['status'] = '1';
					$record['tag'] = iserializer($log['tag']);
					pdo_update('core_paylog', $record, array('plid' => $log['plid']));
					$site = WeUtility :: createModuleSite($log['module']);
					if (!is_error($site)) {
						$method = 'payResult';
						if (method_exists($site, $method)) {
							$ret = array();
							$ret['weid'] = $log['weid'];
							$ret['uniacid'] = $log['uniacid'];
							$ret['result'] = 'success';
							$ret['type'] = $log['type'];
							$ret['from'] = 'return';
							$ret['tid'] = $log['tid'];
							$ret['user'] = $log['openid'];
							$ret['fee'] = $log['fee'];
							$ret['tag'] = $log['tag'];
							$site -> $method($ret);
							exit('success');
						} 
					} 
				} 
			} else {
				require '../../../../addons/ewei_shop/defines.php';
				require '../../../../addons/ewei_shop/core/inc/functions.php';
				$logno = trim($get['out_trade_no']);
				if (empty($logno)) {
					exit;
				} 
				$log = pdo_fetch('SELECT * FROM ' . tablename('ewei_shop_member_log') . ' WHERE `uniacid`=:uniacid and `logno`=:logno limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $logno));
				if (!empty($log) && empty($log['status'])) {
					pdo_update('ewei_shop_member_log', array('status' => 1, 'rechargetype' => 'wechat'), array('id' => $log['id']));
					m('member') -> setCredit($log['openid'], 'credit2', $log['money'], array(0, '人人商城会员充值:credit2:' . $log['money']));
					m('member') -> setRechargeCredit($log['openid'], $log['money']);
					m('notice') -> sendMemberLogMessage($log['id']);
				} 
			} 
		} 
	} 
} 
exit('fail');
