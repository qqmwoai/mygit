<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
class Ewei_DShop_Finance {
	public function pay($openid = '', $paytype = 0, $money = 0, $trade_no = '', $desc = '') {
		global $_W, $_GPC;
		if (empty($openid)) {
			return error(-1, 'openid不能为空');
		} 
		$member = m('member') -> getInfo($openid);
		if (empty($member)) {
			return error(-1, '未找到用户');
		} 
		if (empty($paytype)) {
			m('member') -> setCredit($openid, 'credit2', $money, array(0, $desc));
			return true;
		} else {
			$setting = uni_setting($_W['uniacid'], array('payment'));
			if (!is_array($setting['payment'])) {
				return error(1, '没有设定支付参数');
			} 
			$pay = m('common') -> getSysset('pay');
			if (empty($pay['weixin_cert']) || empty($pay['weixin_key']) || empty($pay['weixin_root'])) {
				return error(-1, '请到商城后台上传完整的商户证书!');
			} 
			$wechat = $setting['payment']['wechat'];
			$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
			$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
			$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
			$pars = array();
			$pars['mch_appid'] = $row['key'];
			$pars['mchid'] = $wechat['mchid'];
			$pars['nonce_str'] = random(32);
			$pars['partner_trade_no'] = empty($trade_no)?time() . random(4, true):$trade_no;
			$pars['openid'] = $openid;
			$pars['check_name'] = 'NO_CHECK';
			$pars['amount'] = $money;
			$pars['desc'] = empty($desc)?'佣金提现':$desc;
			$pars['spbill_create_ip'] = gethostbyname($_SERVER["HTTP_HOST"]);
			ksort($pars, SORT_STRING);
			$string1 = '';
			foreach ($pars as $k => $v) {
				$string1 .= "{$k}={$v}&";
			} 
			$string1 .= "key=" . $wechat['apikey'];
			$pars['sign'] = strtoupper(md5($string1));
			$xml = array2xml($pars);
			$extras = array();
			$extras['CURLOPT_SSLCERT'] = IA_ROOT . str_replace("../", "/", $pay['weixin_cert']);
			$extras['CURLOPT_SSLKEY'] = IA_ROOT . str_replace("../", "/", $pay['weixin_key']);
			$extras['CURLOPT_CAINFO'] = IA_ROOT . str_replace("../", "/", $pay['weixin_root']);
			load() -> func('communication');
			$resp = ihttp_request($url, $xml, $extras);
			if (empty($resp['content'])) {
				return error(-2, '网络错误');
			} else {
				$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
				$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
				$dom = new \DOMDocument();
				if ($dom -> loadXML($xml)) {
					$xpath = new \DOMXPath($dom);
					$code = $xpath -> evaluate('string(//xml/return_code)');
					$ret = $xpath -> evaluate('string(//xml/result_code)');
					if (strtolower($code) == 'success' && strtolower($ret) == 'success') {
						return true;
					} else {
						$error = $xpath -> evaluate('string(//xml/err_code_des)');
						return error(-2, $error);
					} 
				} else {
					return error(-1, '未知错误');
				} 
			} 
		} 
	} 
} 
