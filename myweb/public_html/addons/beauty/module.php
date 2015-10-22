<?php
/**
 * 美容模块定义
 *
 * @author 漆乾明
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class BeautyModule extends WeModule {
	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		load()->func('tpl');
		load()->model('account');
		$modules = uni_modules();
		if(checksubmit()) {
			$dat = array(
                'share_title' => $_GPC['share_title'],
                'share_image' => $_GPC['share_image'],
                'share_desc' => $_GPC['share_desc'],
                'url'=>$_GPC['url'],
                'mobile'=>$_GPC['mobile'],
                'copyright'=>$_GPC['copyright']
            );
			if ($this->saveSettings($dat)) {
                message('保存成功', 'refresh');
            }
		}
		//这里来展示设置项表单
		include $this->template('setting');
	}

}