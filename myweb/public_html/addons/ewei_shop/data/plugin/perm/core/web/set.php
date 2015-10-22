<?php
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php', $this -> pluginname);
ca('perm.set');
$dir = IA_ROOT . "/addons/ewei_shop/data/perm";
$file = $dir . "/set";
$set = array('type' => intval(@file_get_contents($file)));
if (checksubmit('submit')) {
	if (!is_dir(($dir))) {
		load() -> func('file');
		@mkdirs($dir, "0777");
	} 
	$file = $dir . "/set";
	file_put_contents($file, intval($_GPC['data']['type']));
	message('设置成功!', referer(), 'success');
} 
load() -> func('tpl');
include $this -> template('index');
