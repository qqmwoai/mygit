<?php
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php', $this -> pluginname);
ca('sale.enough.view');
$set = $this -> getSet();
if (checksubmit('submit')) {
	ca('sale.enough.save');
	$data = is_array($_GPC['data'])? $_GPC['data'] : array();
	$set['enoughfree'] = intval($data['enoughfree']);
	$set['enoughorder'] = round(floatval($data['enoughorder']), 2);
	$set['enoughareas'] = $data['enoughareas'];
	$set['enoughmoney'] = round(floatval($data['enoughmoney']), 2);
	$set['enoughdeduct'] = round(floatval($data['enoughdeduct']), 2);
	$this -> updateSet($set);
	plog('sale.enough.save', '修改满额优惠');
	message('满额优惠设置成功!', referer(), 'success');
} 
$areafile = IA_ROOT . "/addons/ewei_shop/data/areas";
$areas = json_decode(@file_get_contents($areafile), true);
if (!is_array($areas)) {
	require_once EWEI_SHOP_INC . 'json/xml2json.php';
	$file = IA_ROOT . "/addons/ewei_shop/static/js/dist/area/Area.xml";
	$content = file_get_contents($file);
	$json = xml2json :: transformXmlStringToJson($content);
	$areas = json_decode($json, true);
	file_put_contents($areafile, $json);
} 
load() -> func('tpl');
include $this -> template('enough');
