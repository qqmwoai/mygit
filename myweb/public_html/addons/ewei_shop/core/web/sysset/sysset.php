<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php');
function upload_cert($fileinput) {
	global $_W;
	$path = IA_ROOT . "/addons/ewei_shop/cert";
	load() -> func('file');
	mkdirs($path, '0777');
	$f = $fileinput . '_' . $_W['uniacid'] . '.pem';
	$outfilename = $path . "/" . $f;
	$filename = $_FILES[$fileinput]['name'];
	$tmp_name = $_FILES[$fileinput]['tmp_name'];
	if (!empty($filename) && !empty($tmp_name)) {
		$ext = strtolower(substr($filename, strrpos($filename, '.')));
		if ($ext != '.pem') {
			message('证书文件格式错误: ' . $fileinput . "!", '', 'error');
		} 
		$result = move_uploaded_file($tmp_name, $outfilename);
		if ($result) {
			return "../addons/ewei_shop/cert/" . $f;
		} 
	} 
	return "";
} 
$op = empty($_GPC['op'])? 'shop' : trim($_GPC['op']);
$setdata = pdo_fetch("select * from " . tablename('ewei_shop_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
$set = unserialize($setdata['sets']);
$oldset = unserialize($setdata['sets']);
if ($op == 'template') {
	$styles = array();
	$dir = IA_ROOT . "/addons/ewei_shop/template/mobile/";
	if ($handle = opendir($dir)) {
		while (($file = readdir($handle)) !== false) {
			if ($file != ".." && $file != ".") {
				if (is_dir($dir . "/" . $file)) {
					$styles[] = $file;
				} 
			} 
		} 
		closedir($handle);
	} 
} else if ($op == 'notice') {
	$salers = array();
	if (isset($set['notice']['openid'])) {
		if (!empty($set['notice']['openid'])) {
			$openids = array();
			$strsopenids = explode(",", $set['notice']['openid']);
			foreach ($strsopenids as $openid) {
				$openids[] = "'" . $openid . "'";
			} 
			$salers = pdo_fetchall("select id,nickname,avatar,openid from " . tablename('ewei_shop_member') . ' where openid in (' . implode(",", $openids) . ") and uniacid={$_W['uniacid']}");
		} 
	} 
} 
if (checksubmit()) {
	if ($op == 'shop') {
		$shop = is_array($_GPC['shop'])? $_GPC['shop'] : array();
		$set['shop']['name'] = trim($shop['name']);
		$set['shop']['img'] = save_media($shop['img']);
		$set['shop']['logo'] = save_media($shop['logo']);
		$set['shop']['signimg'] = save_media($shop['signimg']);
		plog('sysset.save.shop', '修改系统设置-商城设置');
	} elseif ($op == 'follow') {
		$set['share'] = is_array($_GPC['share'])? $_GPC['share'] : array();
		$set['share']['icon'] = save_media($set['share']['icon']);
		plog('sysset.save.follow', '修改系统设置-分享及关注设置');
	} else if ($op == 'notice') {
		$set['notice'] = is_array($_GPC['notice'])? $_GPC['notice'] : array();
		if (is_array($_GPC['openids'])) {
			$set['notice']['openid'] = implode(",", $_GPC['openids']);
		} 
		plog('sysset.save.notice', '修改系统设置-模板消息通知设置');
	} elseif ($op == 'trade') {
		$set['trade'] = is_array($_GPC['trade'])? $_GPC['trade'] : array();
		if (!$_W['isfounder']) {
			unset($set['trade']['receivetime']);
		} else {
			file_put_contents(IA_ROOT . '/addons/ewei_shop/data/receive_time', $set['trade']['receivetime']);
		} 
		plog('sysset.save.trade', '修改系统设置-交易设置');
	} elseif ($op == 'pay') {
		$set['pay'] = is_array($_GPC['pay'])? $_GPC['pay'] : array();
		$weixin_cert = upload_cert('weixin_cert_file');
		if (!empty($weixin_cert)) {
			$set['pay']['weixin_cert'] = $weixin_cert;
		} 
		$weixin_key = upload_cert('weixin_key_file');
		if (!empty($weixin_key)) {
			$set['pay']['weixin_key'] = $weixin_key;
		} 
		$weixin_root = upload_cert('weixin_root_file');
		if (!empty($weixin_root)) {
			$set['pay']['weixin_root'] = $weixin_root;
		} 
		plog('sysset.save.pay', '修改系统设置-支付设置');
	} elseif ($op == 'template') {
		$shop = is_array($_GPC['shop'])? $_GPC['shop'] : array();
		$set['shop']['style'] = save_media($shop['style']);
		$datapath = IA_ROOT . "/addons/ewei_shop/data/template";
		if (!is_dir($datapath)) {
			load() -> func('file');
			@mkdirs($datapath, "777");
		} 
		file_put_contents($datapath . "/shop_" . $_W['uniacid'], $set['shop']['style']);
		plog('sysset.save.pay', '修改系统设置-模板设置');
	} elseif ($op == 'member') {
		$shop = is_array($_GPC['shop'])? $_GPC['shop'] : array();
		$set['shop']['levelname'] = trim($shop['levelname']);
		$set['shop']['levelurl'] = trim($shop['levelurl']);
		plog('sysset.save.pay', '修改系统设置-会员设置');
	} elseif ($op == 'category') {
		$shop = is_array($_GPC['shop'])? $_GPC['shop'] : array();
		$set['shop']['catlevel'] = trim($shop['catlevel']);
		$set['shop']['catshow'] = intval($shop['catshow']);
		$set['shop']['catadvimg'] = save_media($shop['catadvimg']);
		$set['shop']['catadvurl'] = trim($shop['catadvurl']);
		plog('sysset.save.pay', '修改系统设置-分类层级设置');
	} elseif ($op == 'contact') {
		$shop = is_array($_GPC['shop'])? $_GPC['shop'] : array();
		$set['shop']['qq'] = trim($shop['qq']);
		$set['shop']['address'] = trim($shop['address']);
		$set['shop']['phone'] = trim($shop['phone']);
		$set['shop']['description'] = trim($shop['description']);
		plog('sysset.save.pay', '修改系统设置-联系方式设置');
	} 
	$data = array('uniacid' => $_W['uniacid'], 'sets' => iserializer($set));
	if (empty($setdata)) {
		pdo_insert('ewei_shop_sysset', $data);
	} else {
		pdo_update('ewei_shop_sysset', $data, array('uniacid' => $_W['uniacid']));
	} 
	$setdata = pdo_fetch("select * from " . tablename('ewei_shop_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
	$path = IA_ROOT . "/addons/ewei_shop/data/sysset";
	$cachefile = $path . "/sysset_" . $_W['uniacid'];
	if (!is_dir($path)) {
		load() -> func('file');
		@mkdirs($path);
	} 
	file_put_contents($cachefile, iserializer($setdata));
	message('设置保存成功!', $this -> createWebUrl('sysset', array('op' => $op)), 'success');
} 
load() -> func('tpl');
include $this -> template('web/sysset/' . $op);
exit;
