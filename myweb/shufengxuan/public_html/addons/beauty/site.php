<?php
defined('IN_IA') or exit('Access Denied');

class BeautyModuleSite extends WeModuleSite {
	//会员信息提取
	public function __construct(){
		global $_W;
		load()->model('mc');
		$profile = pdo_fetch("SELECT * FROM " . tablename('beauty_member') . " WHERE uniacid ='{$_W['uniacid']}' and from_user = '{$_W['openid']}'");
		if (empty($profile)) {
			$userinfo = mc_oauth_userinfo();
			
			if (!empty($userinfo['avatar'])) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'from_user' => $userinfo['openid'],
					'nickname' => $userinfo['nickname'],
					'avatar' => $userinfo['avatar']
				);
				$member = pdo_fetch("SELECT * FROM " . tablename('beauty_member') . " WHERE uniacid ='{$_W['uniacid']}' and from_user = '{$userinfo['openid']}'");
				if (empty($member['id'])) {
					pdo_insert('beauty_member', $data);
				}else{
					pdo_update('beauty_member', $data, array('id' =>$member['id']));
				}
			}
		}
	}		
	/*--------------Mbile------------------------*/
	public function doMobileIndex() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileService_detail() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobilePic_address() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobilePic_date() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileOrderconfirm() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobilePay() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileBeautician() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileAddress() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobilePerson() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileMrs_detail() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileAddress_detail() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobilePerson_detail() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileComment() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileChangemobile() {
		$this -> __mobile(__FUNCTION__);
	}
	/*--------------WEB------------------------*/
	public function doWebGoods() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebCategory() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebBeauticians() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebAddress() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebOrder() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebParam() {
		$tag = random(32);
		global $_GPC,$_W;
		$beauticians = pdo_fetchall("SELECT * FROM ".tablename('beauty_beauticians')." WHERE uniacid={$_W['uniacid']} and status=1 ORDER BY id DESC");
		
		include $this->template('param');
	}
	public function __web($f_name){
		global $_W,$_GPC;
		$weid = $_W['uniacid'];
		load()->func('tpl');
		include_once  'web/'.strtolower(substr($f_name,5)).'.php';
	}
	
	public function __mobile($f_name){
		global $_W,$_GPC;
		$weid = $_W['uniacid'];
		include_once  'mobile/'.strtolower(substr($f_name,8)).'.php';
	}
	public function payResult($params) {
		global $_W;
		$fee = intval($params['fee']);	
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => 1, 'wechat' => 2, 'alipay' => 2, 'delivery' => 3);
		$data['pay_type'] = $paytype[$params['type']];
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		// //货到付款
		if ($params['type'] == 'delivery') {
			$data['status'] = 1;
			$data['pay_time'] = TIMESTAMP;
		}
		if($params['result'] == 'success'){
			$data['pay_time'] = TIMESTAMP;
		}	
		pdo_update('beauty_orders', $data, array('ordersn' => $params['tid']));
		if ($params['from'] == 'return') {
			$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
			$credit = $setting['creditbehaviors']['currency'];
			if ($params['type'] == $credit) {
					message('支付成功！', $this->createMobileUrl('index'), 'success');
			} else {
			   	message('支付成功！', '../../app/' . $this->createMobileUrl('index'), 'success');
			}
		}
	}
//幻灯片管理
	public function doWebAdv() {
		global $_W, $_GPC;
		load()->func('tpl');
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$list = pdo_fetchall("SELECT * FROM " . tablename('beauty_adv') . " WHERE weid = '{$_W['uniacid']}' ORDER BY displayorder DESC");
		} elseif ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (checksubmit('submit')) {
				$data = array(
					'weid' => $_W['uniacid'],
					'advname' => $_GPC['advname'],
					'link' => $_GPC['link'],
					'enabled' => intval($_GPC['enabled']),
					'displayorder' => intval($_GPC['displayorder']),
					'thumb'=>$_GPC['thumb']
				);
				if (!empty($id)) {
					pdo_update('beauty_adv', $data, array('id' => $id));
				} else {
					pdo_insert('beauty_adv', $data);
					$id = pdo_insertid();
				}
				message('更新幻灯片成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
			}
			$adv = pdo_fetch("select * from " . tablename('beauty_adv') . " where id=:id and weid=:weid limit 1", array(":id" => $id, ":weid" => $_W['uniacid']));
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$adv = pdo_fetch("SELECT id FROM " . tablename('beauty_adv') . " WHERE id = '$id' AND weid=" . $_W['uniacid'] . "");
			if (empty($adv)) {
				message('抱歉，幻灯片不存在或是已经被删除！', $this->createWebUrl('adv', array('op' => 'display')), 'error');
			}
			pdo_delete('beauty_adv', array('id' => $id));
			message('幻灯片删除成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
		} else {
			message('请求方式不存在');
		}
		include $this->template('adv', TEMPLATE_INCLUDEPATH, true);
	}
}