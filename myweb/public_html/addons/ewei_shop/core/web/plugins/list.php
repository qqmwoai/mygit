<?php
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php');
$operation = !empty($_GPC['op'])? $_GPC['op'] : 'display';
if ($operation == 'display') {
	$plugins = m('plugin') -> getAll();
	include $this -> template('web/plugins/list');
	exit;
} 
