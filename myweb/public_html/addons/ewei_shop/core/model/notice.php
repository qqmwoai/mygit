<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
class Ewei_DShop_Notice {
	public function sendOrderMessage($orderid = '0', $delRefund = false) {
		global $_W;
		if (empty($orderid)) {
			return;
		} 
		$order = pdo_fetch('select * from ' . tablename('ewei_shop_order') . ' where id=:id limit 1', array(':id' => $orderid));
		if (empty($order)) {
			return;
		} 
		$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=order&p=detail&id=' . $orderid;
		if (strexists($detailurl, '/addons/ewei_shop/')) {
			$detailurl = str_replace("/addons/ewei_shop/", '/', $detailurl);
		} 
		if (strexists($detailurl, '/core/mobile/order/')) {
			$detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
		} 
		$openid = $order['openid'];
		$order_goods = pdo_fetchall('select g.id,g.title,og.total,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype from ' . tablename('ewei_shop_order_goods') . ' og ' . ' left join ' . tablename('ewei_shop_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));
		$goods = '';
		foreach ($order_goods as $og) {
			$goods .= "" . $og['title'] . '( ';
			if (!empty($og['optiontitle'])) {
				$goods .= " 规格: " . $og['optiontitle'];
			} 
			$goods .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
		} 
		$member = m('member') -> getMember($openid);
		$usernotice = unserialize($member['noticeset']);
		if (!is_array($usernotice)) {
			$usernotice = array();
		} 
		$set = m('common') -> getSysset();
		$shop = $set['shop'];
		$tm = $set['notice'];
		if ($delRefund) {
			if (!empty($order['refundid'])) {
				$refund = pdo_fetch('select * from ' . tablename('ewei_shop_order_refund') . ' where id=:id limit 1', array(':id' => $order['refundid']));
				if (empty($refund)) {
					return;
				} 
				if (empty($refund['status'])) {
					$msg = array('first' => array('value' => "您的退款申请已经提交！", "color" => "#4a5077"), 'orderProductPrice' => array('title' => '退款金额', 'value' => '¥' . $refund['price'] . '元', "color" => "#4a5077"), 'orderProductName' => array('title' => '商品详情', 'value' => $goods, "color" => "#4a5077"), 'orderName' => array('title' => '订单编号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n等待商家确认退款信息！", "color" => "#4a5077"),);
					if (!empty($tm['refund']) && empty($usernotice['refund'])) {
						m('message') -> sendTplNotice($openid, $tm['refund'], $msg, $detailurl);
					} else if (empty($usernotice['refund'])) {
						m('message') -> sendCustomNotice($openid, $msg, $detailurl);
					} 
				} else if ($refund['status'] == 1) {
					$refundtype = empty($refund['refundtype'])? '余额账户' : '微信钱包';
					$msg = array('first' => array('value' => "您的订单已经完成退款，¥" . $refund['price'] . "已经退回您的{$refundtype}，请留意查收！", "color" => "#4a5077"), 'orderProductPrice' => array('title' => '退款金额', 'value' => '¥' . $refund['price'] . '元', "color" => "#4a5077"), 'orderProductName' => array('title' => '商品详情', 'value' => $goods, "color" => "#4a5077"), 'orderName' => array('title' => '订单编号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n【" . $shop['name'] . "】期待您再次购物！", "color" => "#4a5077"));
					if (!empty($tm['refund1']) && empty($usernotice['refund1'])) {
						m('message') -> sendTplNotice($openid, $tm['refund'], $msg, $detailurl);
					} else if (empty($usernotice['refund1'])) {
						m('message') -> sendCustomNotice($openid, $msg, $detailurl);
					} 
				} elseif ($refund['status'] == -1) {
					$remark = "\n驳回原因: " . $refund['reply'];
					if (!empty($shop['phone'])) {
						$remark .= "\n客服电话:  " . $shop['phone'];
					} 
					$msg = array('first' => array('value' => "您的退款申请被商家驳回，可与商家协商沟通！", "color" => "#4a5077"), 'orderProductPrice' => array('title' => '退款金额', 'value' => '¥' . $refund['price'] . '元', "color" => "#4a5077"), 'orderProductName' => array('title' => '商品详情', 'value' => $goods, "color" => "#4a5077"), 'orderName' => array('title' => '订单编号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => $remark, "color" => "#4a5077"));
					if (!empty($tm['refund2']) && empty($usernotice['refund2'])) {
						m('message') -> sendTplNotice($openid, $tm['refund2'], $msg, $detailurl);
					} else if (empty($usernotice['refund2'])) {
						m('message') -> sendCustomNotice($openid, $msg, $detailurl);
					} 
				} 
				return;
			} 
		} 
		if ($order['status'] == -1) {
			if (empty($order['dispatchtype'])) {
				$address = pdo_fetch('select * from ' . tablename('ewei_shop_member_address') . ' where id=:id and uniacid=:uniacid limit 1 ', array(":uniacid" => $_W['uniacid'], ":id" => $order['addressid']));
				$orderAddress = array('title' => '收货信息', 'value' => '收货地址: ' . $address['province'] . ' ' . $address['city'] . ' ' . $address['area'] . ' ' . $address['address'] . ' 收件人: ' . $address['realname'] . ' 联系电话: ' . $address['mobile'], "color" => "#4a5077");
			} else {
				$carrier = iunserializer($order['carrier']);
				$orderAddress = array('title' => '收货信息', 'value' => '自提地点: ' . $carrier['address'] . ' 联系人: ' . $carrier['realname'] . ' 联系电话: ' . $carrier['mobile'], "color" => "#4a5077");
			} 
			$msg = array('first' => array('value' => "您的订单已取消!", "color" => "#4a5077"), 'orderProductPrice' => array('title' => '订单金额', 'value' => '¥' . $order['price'] . '元(含运费' . $order['dispatchprice'] . '元)', "color" => "#4a5077"), 'orderProductName' => array('title' => '商品详情', 'value' => $goods, "color" => "#4a5077"), 'orderAddress' => $orderAddress, 'orderName' => array('title' => '订单编号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n【" . $shop['name'] . "】欢迎您的再次购物！", "color" => "#4a5077"));
			if (!empty($tm['cancel']) && empty($usernotice['cancel'])) {
				m('message') -> sendTplNotice($openid, $tm['cancel'], $msg, $detailurl);
			} else if (empty($usernotice['cancel'])) {
				m('message') -> sendCustomNotice($openid, $msg, $detailurl);
			} 
		} else if ($order['status'] == 0) {
			if (empty($tm['newtype'])) {
				$msg = array('first' => array('value' => "订单生成通知!", "color" => "#4a5077"), 'keyword1' => array('title' => '时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), "color" => "#4a5077"), 'keyword2' => array('title' => '商品名称', 'value' => $goods, "color" => "#4a5077"), 'keyword3' => array('title' => '订单号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n订单生成成功,请到后台查看!", "color" => "#4a5077"));
				$account = m('common') -> getAccount();
				if (!empty($tm['openid'])) {
					$openids = explode(',', $tm['openid']);
					foreach ($openids as $tmopenid) {
						if (empty($tmopenid)) {
							continue;
						} 
						if (!empty($tm['new'])) {
							m('message') -> sendTplNotice($tmopenid, $tm['new'], $msg, '', $account);
						} else {
							m('message') -> sendCustomNotice($tmopenid, $msg, '', $account);
						} 
					} 
				} 
			} 
			foreach ($order_goods as $og) {
				if (!empty($og['noticeopenid']) && empty($og['noticetype'])) {
					$goodstr = $og['title'] . '( ';
					if (!empty($og['optiontitle'])) {
						$goodstr .= " 规格: " . $og['optiontitle'];
					} 
					$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
					$msg = array('first' => array('value' => "商品下单通知!", "color" => "#4a5077"), 'keyword1' => array('title' => '时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), "color" => "#4a5077"), 'keyword2' => array('title' => '商品名称', 'value' => $goodstr, "color" => "#4a5077"), 'keyword3' => array('title' => '订单号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n商品已经下单，请及时备货，谢谢!", "color" => "#4a5077"));
					if (!empty($tm['new'])) {
						m('message') -> sendTplNotice($og['noticeopenid'], $tm['new'], $msg, '', $account);
					} else {
						m('message') -> sendCustomNotice($og['noticeopenid'], $msg, '', $account);
					} 
				} 
			} 
			$msg = array('first' => array('value' => "您的订单已提交成功！", "color" => "#4a5077"), 'keyword1' => array('title' => '店铺', 'value' => $shop['name'], "color" => "#4a5077"), 'keyword2' => array('title' => '下单时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), "color" => "#4a5077"), 'keyword3' => array('title' => '商品', 'value' => $goods, "color" => "#4a5077"), 'keyword4' => array('title' => '金额', 'value' => '¥' . $order['price'] . '元(含运费' . $order['dispatchprice'] . '元)', "color" => "#4a5077"), 'remark' => array('value' => "\r\n您的订单我们已经收到，支付后我们将尽快配送~~", "color" => "#4a5077"));
			if (!empty($tm['submit']) && empty($usernotice['submit'])) {
				m('message') -> sendTplNotice($openid, $tm['submit'], $msg, $detailurl);
			} else if (empty($usernotice['submit'])) {
				m('message') -> sendCustomNotice($openid, $msg, $detailurl);
			} 
		} else if ($order['status'] == 1) {
			if (intval($tm['newtype']) == 1) {
				$msg = array('first' => array('value' => "订单生成通知!", "color" => "#4a5077"), 'keyword1' => array('title' => '时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), "color" => "#4a5077"), 'keyword2' => array('title' => '商品名称', 'value' => $goods, "color" => "#4a5077"), 'keyword3' => array('title' => '订单号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n订单生成成功,请到后台查看!", "color" => "#4a5077"));
				$account = m('common') -> getAccount();
				if (!empty($tm['openid'])) {
					$openids = explode(',', $tm['openid']);
					foreach ($openids as $tmopenid) {
						if (empty($tmopenid)) {
							continue;
						} 
						if (!empty($tm['new'])) {
							m('message') -> sendTplNotice($tmopenid, $tm['new'], $msg, '', $account);
						} else {
							m('message') -> sendCustomNotice($tmopenid, $msg, '', $account);
						} 
					} 
				} 
			} 
			foreach ($order_goods as $og) {
				if (!empty($og['noticeopenid']) && !empty($og['noticetype'])) {
					$goodstr = $og['title'] . '( ';
					if (!empty($og['optiontitle'])) {
						$goodstr .= " 规格: " . $og['optiontitle'];
					} 
					$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
					$msg = array('first' => array('value' => "商品下单通知!", "color" => "#4a5077"), 'keyword1' => array('title' => '时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), "color" => "#4a5077"), 'keyword2' => array('title' => '商品名称', 'value' => $goodstr, "color" => "#4a5077"), 'keyword3' => array('title' => '订单号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n商品已经下单，请及时备货，谢谢!", "color" => "#4a5077"));
					if (!empty($tm['new'])) {
						m('message') -> sendTplNotice($og['noticeopenid'], $tm['new'], $msg, '', $account);
					} else {
						m('message') -> sendCustomNotice($og['noticeopenid'], $msg, '', $account);
					} 
				} 
			} 
			$msg = array('first' => array('value' => "您已支付成功订单，我们将尽快配送！", "color" => "#4a5077"), 'keyword1' => array('title' => '订单', 'value' => $order['ordersn'], "color" => "#4a5077"), 'keyword2' => array('title' => '支付状态', 'value' => '支付成功', "color" => "#4a5077"), 'keyword3' => array('title' => '支付日期', 'value' => date('Y-m-d H:i:s', $order['paytime']), "color" => "#4a5077"), 'keyword4' => array('title' => '商户', 'value' => $shop['name'], "color" => "#4a5077"), 'keyword5' => array('title' => '金额', 'value' => '¥' . $order['price'] . '元(含运费' . $order['dispatchprice'] . '元)', "color" => "#4a5077"), 'remark' => array('value' => "\r\n【" . $shop['name'] . "】欢迎您的再次购物！", "color" => "#4a5077"));
			$pay_detailurl = $detailurl;
			if (strexists($pay_detailurl, '/addons/ewei_shop/')) {
				$pay_detailurl = str_replace("/addons/ewei_shop/", '/', $pay_detailurl);
			} 
			if (strexists($pay_detailurl, '/core/mobile/order/')) {
				$pay_detailurl = str_replace("/core/mobile/order/", '/', $pay_detailurl);
			} 
			if (!empty($tm['pay']) && empty($usernotice['pay'])) {
				m('message') -> sendTplNotice($openid, $tm['pay'], $msg, $pay_detailurl);
			} else if (empty($usernotice['pay'])) {
				m('message') -> sendCustomNotice($openid, $msg, $pay_detailurl);
			} 
			if ($order['dispatchtype'] == 1) {
				$carrier = iunserializer($order['carrier']);
				if (!is_array($carrier)) {
					return;
				} 
				$msg = array('first' => array('value' => "自提订单提交成功!", "color" => "#4a5077"), 'keyword1' => array('title' => '自提码', 'value' => $order['ordersn'], "color" => "#4a5077"), 'keyword2' => array('title' => '商品详情', 'value' => $goods, "color" => "#4a5077"), 'keyword3' => array('title' => '提货地址', 'value' => $carrier['address'], "color" => "#4a5077"), 'keyword4' => array('title' => '提货时间', 'value' => $carrier['content'], "color" => "#4a5077"), 'remark' => array('value' => "\r\n请您到选择的自提点进行取货, 自提联系人: " . $carrier['realname'] . ' 联系电话: ' . $carrier['mobile'], "color" => "#4a5077"));
				if (!empty($tm['carrier']) && empty($usernotice['carrier'])) {
					m('message') -> sendTplNotice($openid, $tm['carrier'], $msg, $detailurl);
				} else if (empty($usernotice['carrier'])) {
					m('message') -> sendCustomNotice($openid, $msg, $detailurl);
				} 
			} 
		} else if ($order['status'] == 2) {
			if (empty($order['dispatchtype'])) {
				$address = pdo_fetch('select * from ' . tablename('ewei_shop_member_address') . ' where id=:id and uniacid=:uniacid limit 1 ', array(":uniacid" => $_W['uniacid'], ":id" => $order['addressid']));
				if (empty($address)) {
					return;
				} 
				$msg = array('first' => array('value' => "您的宝贝已经发货！", "color" => "#4a5077"), 'keyword1' => array('title' => '订单内容', 'value' => "【" . $order['ordersn'] . "】" . $goods, "color" => "#4a5077"), 'keyword2' => array('title' => '物流服务', 'value' => $order['expresscom'], "color" => "#4a5077"), 'keyword3' => array('title' => '快递单号', 'value' => $order['expresssn'], "color" => "#4a5077"), 'keyword4' => array('title' => '收货信息', 'value' => "地址: " . $address['province'] . ' ' . $address['city'] . ' ' . $address['area'] . ' ' . $address['address'] . "收件人: " . $address['realname'] . ' (' . $address['mobile'] . ') ', "color" => "#4a5077"), 'remark' => array('value' => "\r\n我们正加速送到您的手上，请您耐心等候。", "color" => "#4a5077"));
				if (!empty($tm['send']) && empty($usernotice['send'])) {
					m('message') -> sendTplNotice($openid, $tm['send'], $msg, $detailurl);
				} else if (empty($usernotice['send'])) {
					m('message') -> sendCustomNotice($openid, $msg, $detailurl);
				} 
			} 
		} else if ($order['status'] == 3) {
			$sendtime = empty($order['dispatchtype'])? $order['sendtime'] : $order['finishtime'];
			$msg = array('first' => array('value' => "亲, 您在我们商城买的宝贝已经确认收货!", "color" => "#4a5077"), 'keyword1' => array('title' => '订单号', 'value' => $order['ordersn'], "color" => "#4a5077"), 'keyword2' => array('title' => '商品名称', 'value' => $goods, "color" => "#4a5077"), 'keyword3' => array('title' => '下单时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), "color" => "#4a5077"), 'keyword4' => array('title' => '发货时间', 'value' => date('Y-m-d H:i:s', $sendtime), "color" => "#4a5077"), 'keyword5' => array('title' => '确认收货时间', 'value' => date('Y-m-d H:i:s', $order['finishtime']), "color" => "#4a5077"), 'remark' => array('title' => '', 'value' => "\r\n【" . $shop['name'] . '】感谢您的支持与厚爱，欢迎您的再次购物！', "color" => "#4a5077"));
			if (!empty($tm['finish']) && empty($usernotice['finish'])) {
				m('message') -> sendTplNotice($openid, $tm['finish'], $msg, $detailurl);
			} else if (empty($usernotice['finish'])) {
				m('message') -> sendCustomNotice($openid, $msg, $detailurl);
			} 
		} 
	} 
	public function sendMemberUpgradeMessage($openid = '', $oldlevel = null, $level = null) {
		global $_W, $_GPC;
		$member = m('member') -> getMember($openid);
		$usernotice = unserialize($member['noticeset']);
		if (!is_array($usernotice)) {
			$usernotice = array();
		} 
		$shop = m('common') -> getSysset('shop');
		$tm = m('common') -> getSysset('notice');
		$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=member';
		if (strexists($detailurl, '/addons/ewei_shop/')) {
			$detailurl = str_replace("/addons/ewei_shop/", '/', $detailurl);
		} 
		if (strexists($detailurl, '/core/mobile/order/')) {
			$detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
		} 
		if (!$level) {
			$level = m('member') -> getLevel($openid);
		} 
		$defaultlevelname = empty($shop['levelname'])? '普通会员' : $shop['levelname'];
		$msg = array('first' => array('value' => "亲爱的" . $member['nickname'] . ', 恭喜您成功升级！', "color" => "#4a5077"), 'keyword1' => array('title' => '任务名称', 'value' => '会员升级', "color" => "#4a5077"), 'keyword2' => array('title' => '通知类型', 'value' => '您会员等级从 ' . $defaultlevelname . ' 升级为 ' . $level['levelname'] . ', 特此通知!', "color" => "#4a5077"), 'remark' => array('value' => "\r\n您即可享有" . $level['levelname'] . '的专属优惠及服务！', "color" => "#4a5077"));
		if (!empty($tm['upgrade']) && empty($usernotice['upgrade'])) {
			m('message') -> sendTplNotice($openid, $tm['finish'], $msg, $detailurl);
		} else if (empty($usernotice['upgrade'])) {
			m('message') -> sendCustomNotice($openid, $msg, $detailurl);
		} 
	} 
	public function sendMemberLogMessage($log_id = '') {
		global $_W, $_GPC;
		$log_info = pdo_fetch('select * from ' . tablename('ewei_shop_member_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $log_id, ':uniacid' => $_W['uniacid']));
		$member = m('member') -> getMember($log_info['openid']);
		$shop = m('common') -> getSysset('shop');
		$usernotice = unserialize($member['noticeset']);
		if (!is_array($usernotice)) {
			$usernotice = array();
		} 
		$account = m('common') -> getAccount();
		if (!$account) {
			return;
		} 
		$tm = m('common') -> getSysset('notice');
		if ($log_info['type'] == 0) {
			if ($log_info['status'] == 1) {
				$product = "后台充值";
				if ($log_info['rechargetype'] == 'wechat') {
					$product = "微信支付";
				} else if ($log_info == 'alipay') {
					$product['rechargetype'] = "支付宝";
				} 
				$msg = array('first' => array('value' => "恭喜您充值成功!", "color" => "#4a5077"), 'money' => array('title' => '充值金额', 'value' => '¥' . $log_info['money'] . '元', "color" => "#4a5077"), 'product' => array('title' => '充值方式', 'value' => $product, "color" => "#4a5077"), 'remark' => array('value' => "\r\n谢谢您对我们的支持！", "color" => "#4a5077"));
				$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=member';
				if (strexists($detailurl, '/addons/ewei_shop/')) {
					$detailurl = str_replace("/addons/ewei_shop/", '/', $detailurl);
				} 
				if (strexists($detailurl, '/core/mobile/order/')) {
					$detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
				} 
				if (!empty($tm['recharge_ok']) && empty($usernotice['recharge_ok'])) {
					m('message') -> sendTplNotice($log_info['openid'], $tm['recharge_ok'], $msg, $detailurl);
				} else if (empty($usernotice['recharge_ok'])) {
					m('message') -> sendCustomNotice($log_info['openid'], $msg, $detailurl);
				} 
			} else if ($log_info['status'] == 3) {
				$msg = array('first' => array('value' => "充值退款成功!", "color" => "#4a5077"), 'reason' => array('title' => '退款原因', 'value' => '【' . $shop['name'] . '】充值退款' , "color" => "#4a5077"), 'refund' => array('title' => '退款金额', 'value' => '¥' . $log_info['money'] . '元', "color" => "#4a5077"), 'remark' => array('value' => "\r\n退款成功，请注意查收! 谢谢您对我们的支持！", "color" => "#4a5077"));
				$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=member';
				if (strexists($detailurl, '/addons/ewei_shop/')) {
					$detailurl = str_replace("/addons/ewei_shop/", '/', $detailurl);
				} 
				if (strexists($detailurl, '/core/mobile/order/')) {
					$detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
				} 
				if (!empty($tm['recharge_fund']) && empty($usernotice['recharge_fund'])) {
					m('message') -> sendTplNotice($log_info['openid'], $tm['recharge_fund'], $msg, $detailurl);
				} else if (empty($usernotice['recharge_fund'])) {
					m('message') -> sendCustomNotice($log_info['openid'], $msg, $detailurl);
				} 
			} 
		} else if ($log_info['type'] == 1 && $log_info['status'] == 0) {
			$msg = array('first' => array('value' => "提现申请已经成功提交!", "color" => "#4a5077"), 'money' => array('title' => '提现金额', 'value' => '¥' . $log_info['money'] . '元', "color" => "#4a5077"), 'timet' => array('title' => '提现时间', 'value' => date('Y-m-d H:i:s', $log_info['createtime']), "color" => "#4a5077"), 'remark' => array('value' => "\r\n请等待我们的审核并打款！", "color" => "#4a5077"));
			$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=member&p=log&type=1';
			if (strexists($detailurl, '/addons/ewei_shop/')) {
				$detailurl = str_replace("/addons/ewei_shop/", '/', $detailurl);
			} 
			if (!empty($tm['withdraw']) && empty($usernotice['withdraw'])) {
				m('message') -> sendTplNotice($log_info['openid'], $tm['withdraw'], $msg, $detailurl);
			} else if (empty($usernotice['withdraw'])) {
				m('message') -> sendCustomNotice($log_info['openid'], $msg, $detailurl);
			} 
		} else if ($log_info['type'] == 1 && $log_info['status'] == 1) {
			$msg = array('first' => array('value' => "恭喜您成功提现!", "color" => "#4a5077"), 'money' => array('title' => '提现金额', 'value' => '¥' . $log_info['money'] . '元', "color" => "#4a5077"), 'timet' => array('title' => '提现时间', 'value' => date('Y-m-d H:i:s', $log_info['createtime']), "color" => "#4a5077"), 'remark' => array('value' => "\r\n感谢您的支持！", "color" => "#4a5077"));
			$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=member&p=log&type=1';
			if (!empty($tm['withdraw_ok']) && empty($usernotice['withdraw_ok'])) {
				m('message') -> sendTplNotice($log_info['openid'], $tm['withdraw_ok'], $msg, $detailurl);
			} else if (empty($usernotice['withdraw_ok'])) {
				m('message') -> sendCustomNotice($log_info['openid'], $msg, $detailurl);
			} 
		} else if ($log_info['type'] == 1 && $log_info['status'] == -1) {
			$msg = array('first' => array('value' => "抱歉，提现申请审核失败!", "color" => "#4a5077"), 'money' => array('title' => '提现金额', 'value' => '¥' . $log_info['money'] . '元', "color" => "#4a5077"), 'timet' => array('title' => '提现时间', 'value' => date('Y-m-d H:i:s', $log_info['createtime']), "color" => "#4a5077"), 'remark' => array('value' => "\r\n有疑问请联系客服，谢谢您的支持！", "color" => "#4a5077"));
			$detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=ewei_shop&do=member&p=log&type=1';
			if (strexists($detailurl, '/addons/ewei_shop/')) {
				$detailurl = str_replace("/addons/ewei_shop/", '/', $detailurl);
			} 
			if (strexists($detailurl, '/core/mobile/order/')) {
				$detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
			} 
			if (!empty($tm['withdraw_fail']) && empty($usernotice['withdraw_fail'])) {
				m('message') -> sendTplNotice($log_info['openid'], $tm['withdraw_fail'], $msg, $detailurl);
			} else if (empty($usernotice['withdraw_fail'])) {
				m('message') -> sendCustomNotice($log_info['openid'], $msg, $detailurl);
			} 
		} 
	} 
} 
