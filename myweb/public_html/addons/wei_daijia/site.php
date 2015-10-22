<?php

defined('IN_IA') or exit('Access Denied');

class Wei_daijiaModuleSite extends WeModuleSite {
	//会员信息提取
	public function __construct(){
		global $_W;
		load()->model('mc');
		$profile = pdo_fetch("SELECT * FROM " . tablename('daijia_member') . " WHERE uniacid ='{$_W['uniacid']}' and from_user = '{$_W['openid']}'");
		if (empty($profile)) {
			$userinfo = mc_oauth_userinfo();
			if (!empty($userinfo['avatar'])) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'from_user' => $userinfo['openid'],
					'nickname' => $userinfo['nickname'],
					'avatar' => $userinfo['avatar']
				);
				pdo_insert('daijia_member', $data);
			}
		}
	}

	//微信端首页
	public function doMobileIndex() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileDaijia() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileDailao() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileDaibu() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobileActivity() {
		$this -> __mobile(__FUNCTION__);
	}
	public function doMobilePrice() {
		$this -> __mobile(__FUNCTION__);
	}
	//微信端填订单信息确认页面
	public function doMobileOrderConfirm() {
		$this -> __mobile(__FUNCTION__);
	}
	//微信端订单详情页面
	public function doMobileOrderDetails() {
		$this -> __mobile(__FUNCTION__);
	}

	//微信端订单页面
	public function doMobilemyOrder() {
		$this -> __mobile(__FUNCTION__);
	}
	//微信端个人中心页面
	public function doMobilePerson() {
		$this -> __mobile(__FUNCTION__);
	}

	public function doMobilePay() {
		$this -> __mobile(__FUNCTION__);
	}
	
	public function doMobileGrant() {
		$this->Grant(array('wei_daijia'));
	}
	/*＝＝＝＝＝＝＝＝＝＝＝＝＝＝以下为后台页面管理＝＝＝＝＝＝＝＝＝＝＝＝＝＝*/

	//后台订单管理页面
	public function doWebOrder() {
		$this -> __web(__FUNCTION__);
	}
	
    public function doWebPrice() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebPrint() {
		$this -> __web(__FUNCTION__);
	}
	public function doWebActivity() {
		$this -> __web(__FUNCTION__);
	}
	public function __web($f_name){
		global $_W,$_GPC;
		checklogin();
		$weid = $_W['uniacid'];
		load()->func('tpl');
		include_once  'web/'.strtolower(substr($f_name,5)).'.php';
	}
	
	public function __mobile($f_name){
		global $_W,$_GPC;
		$weid = $_W['uniacid'];
		$share_data = $this->module['config'];
		$to_url = "http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
	
		include_once  'mobile/'.strtolower(substr($f_name,8)).'.php';
	}
public function payResult($params) {
		global $_W;
		$fee = intval($params['fee']);	
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => 1, 'wechat' => 2, 'alipay' => 2, 'delivery' => 3);
		$data['pay_type'] = $paytype[$params['type']];
		if($params['result'] == 'success'){
			$data['pay_time'] = TIMESTAMP;
		}	
		pdo_update('daijia_orders', $data, array('ordersn' => $params['tid']));
		if ($params['from'] == 'return') {
			$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
			$credit = $setting['creditbehaviors']['currency'];
			//支付成功，打印订单
			$order = pdo_fetch("SELECT * FROM " . tablename('daijia_orders') . "where ordersn={$params['tid']}");
			//获取所有打印机
			$prints = pdo_fetchall('SELECT * FROM ' . tablename('daijia_print') . ' WHERE uniacid = :aid AND status = 1', array(':aid' => $_W['uniacid']));
			if(empty($prints)) {
				// exit('没有有效的打印机');
			}
			$paytype = $order['paytype'] == '3' ? '货到付款' : '已付款';
			$ordertype = $order['type'] == 'daijia' ? '代驾' : '代劳、代步';
			//邮件提醒
			$orderinfo = '';
			$orderinfo .= '订单编号　　起始地址   目的地址　价格   备注<BR>';
			$orderinfo .= '--------------------------------<BR>';
			$orderInfo .= "{$order['ordersn']}{$order['start_address']}{$order['end_address']}{$order['price']}{$order['remark']}<br />";			
			$orderInfo .= '--------------------------------<BR>';
			$orderinfo .= "合计：{$order['price']}元<BR>";
			$orderinfo .= "联系电话：{$order['mobile']}<BR>";
			$orderinfo .= "支付方式：{$paytype}<BR>";
			
			//微信提醒
			$sendinfo = '您的订单支付成功，请等待处理：\n';
			$sendinfo .= '--------------------\n';
			$sendinfo .= "{$ordertype}详情：\n";
			$sendinfo .= '--------------------\n';
			$sendinfo .= "订单编号：{$order['ordersn']}\n";
			$sendinfo .= "起始地址: {$order['start_address']}\n";
			$sendinfo .= "目的地址: {$order['end_address']}\n";
			$sendinfo .= "备注: {$order['remark']}\n";
			$sendinfo .= '--------------------\n';
			$sendinfo .= "合计：{$order['price']}元\n";
			$sendinfo .= "联系电话：{$order['mobile']}\n";
			$sendinfo .= "支付方式：{$paytype}\n";
			//发送微信提醒
			$send['msgtype'] = 'text';
			$send['text'] = array('content' => urlencode($sendinfo));
			$acc = WeAccount::create($_W['account']['acid']);
			$send['touser'] = trim($_W['openid']);
			$s_mess = $acc->sendCustomNotice($send);
			include 'wprint.class.php';
			//遍历所有打印机
			foreach($prints as $li) {
				if(!empty($li['qrcode_link'])) {
					$orderinfo .= "<QR>{$li['qrcode_link']}</QR>";
				}
				if(!empty($li['print_no']) && !empty($li['key'])) {
					$wprint = new wprint();
					$status = $wprint->StrPrint($li['print_no'], $li['key'], $orderinfo, 1);
					if(!is_error($status)) {
						$i++;
						$data2 = array(
								'uniacid' => $_W['uniacid'],
								'sid' => $sid,
								'pid' => $li['id'],
								'oid' => $id, //订单id
								'status' => 1,
								'aid' => $status,
								'addtime' => TIMESTAMP
							);
						pdo_insert('daijia_order_print', $data2);
					}
				}
			}
			// if($i > 0) {
			// 	 pdo_query('UPDATE ' . tablename('str_order') . " SET print_nums = print_nums + {$i} WHERE uniacid = {$_W['uniacid']} AND id = {$id}");
			// } else {
			// 	exit('发送打印指令失败。没有有效的机器号');
			// }
			// exit('success');
			//打印结束
			if ($params['type'] == $credit) {				
					message('支付成功！', $this->createMobileUrl('daijia',array('op' => 'yes')), 'success');
			} else {	
				message('支付成功！', '../../app/' . $this->createMobileUrl('daijia',array('op' => 'yes')), 'success');
			}
		}

	}
   

}
