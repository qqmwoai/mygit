<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
global $_W, $_GPC;
$operation = !empty($_GPC['op'])? $_GPC['op'] : 'index';
$openid = m('user') -> getOpenid();
$uniacid = $_W['uniacid'];
$set = m('common') -> getSysset('shop');
$commission = p('commission');
if ($commission) {
	$shopid = intval($_GPC['shopid']);
	if (!empty($shopid)) {
		$myshop = set_medias($commission -> getShop($shopid), array('img', 'logo'));
	} 
} 
if ($_W['isajax']) {
	$args = array('pagesize' => 10, 'page' => $_GPC['page'], 'isnew' => $_GPC['isnew'], 'ishot' => $_GPC['ishot'], 'isrecommand' => $_GPC['isrecommand'], 'isdiscount' => $_GPC['isdiscount'], 'istime' => $_GPC['istime'], 'keywords' => $_GPC['keywords'], 'pcate' => $_GPC['pcate'], 'ccate' => $_GPC['ccate'], 'tcate' => $_GPC['tcate'], 'order' => $_GPC['order'], 'by' => $_GPC['by']);
	if (!empty($myshop['selectgoods']) && !empty($myshop['goodsids'])) {
		$args['ids'] = $myshop['goodsids'];
	} 
	$goods = m('goods') -> getList($args);
	show_json(1, array('goods' => $goods, 'pagesize' => $args['pagesize']));
} 
include $this -> template('shop/list');
