<?php
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php', $this -> pluginname);
ca('qiniu.admin');
$set = $this -> getSet();
if (checksubmit('submit')) {
	$set['user'] = is_array($_GPC['user'])? $_GPC['user'] : array();
	if (!empty($set['user']['upload'])) {
		$ret = $this -> check($set['user']);
		if (empty($ret)) {
			message('配置有误，请仔细检查参数设置!', '', 'error');
		} 
	} 
	$this -> updateSet($set);
	message('设置保存成功!', referer(), 'success');
} 
if (checksubmit('submit_admin')) {
	$set['admin'] = is_array($_GPC['admin'])? $_GPC['admin'] : array();
	if (!empty($set['admin']['upload'])) {
		$ret = $this -> check($set['admin']);
		if (empty($ret)) {
			message('配置有误，请仔细检查参数设置!', '', 'error');
		} 
	} 
	$allsets = pdo_fetchall('select id, uniacid, plugins from ' . tablename('ewei_shop_sysset'));
	foreach($allsets as $s) {
		$plugins = iunserializer($s['plugins']);
		$plugins['qiniu']['admin'] = $set['admin'];
		pdo_update('ewei_shop_sysset', array('plugins' => iserializer($plugins)), array('id' => $s['id']));
		$setdata = pdo_fetch("select * from " . tablename('ewei_shop_sysset') . ' where id=:id limit 1', array(':id' => $s['id']));
		$path = IA_ROOT . "/addons/ewei_shop/data/sysset";
		$cachefile = $path . "/sysset_" . $s['uniacid'];
		if (!is_dir($path)) {
			load() -> func('file');
			@mkdirs($path);
		} 
		file_put_contents($cachefile, iserializer($setdata));
	} 
	plog('qiniu.admin', '设置七牛');
	message('设置保存成功!', referer(), 'success');
} 
include $this -> template('set');
