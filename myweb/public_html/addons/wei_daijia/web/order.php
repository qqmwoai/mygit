<?php
		global $_W, $_GPC;
		load()->func('tpl');
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			$type = $_GPC['type'];
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$status = $_GPC['status'];
			$condition = " uniacid = {$_W['uniacid']}";
			if(!empty($type)){
				if($type == 'daijia'){
					$condition .= " AND type = 'daijia'";
				}
				if($type == 'daibu'){
					$condition .= " AND type = 'daibu'";
				}
				if($type == 'dailao'){
					$condition .= " AND type = 'dailao'";
				}
				
			}
			if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
				$endtime = time();
			}
			if (!empty($_GPC['time'])) {
				$starttime = strtotime($_GPC['time']['start']);
				$endtime = strtotime($_GPC['time']['end']) + 86399;
				$paras[':starttime'] = $starttime;
				$paras[':endtime'] = $endtime;
			}
			if (!empty($_GPC['pay_type'])) {
				$condition .= " AND pay_type = '{$_GPC['pay_type']}'";
			} elseif ($_GPC['pay_type'] === '0') {
				$condition .= " AND pay_type = '{$_GPC['pay_type']}'";
			}
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND ordersn LIKE '%{$_GPC['keyword']}%'";
			}
			
			if ($status != '') {
				$condition .= " AND status = '" . intval($status) . "'";
			}
			
			$sql = "select *  from ".tablename('daijia_orders')." where $condition ORDER BY status DESC, createtime DESC "
					. "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$list = pdo_fetchall($sql,$paras);
			$paytype = array (
					'1' => array('css' => 'default', 'name' => '未支付'),
					'2' => array('css' => 'info', 'name' => '在线支付'),
					'3' => array('css' => 'warning', 'name' => '货到付款')
			);
			$orderstatus = array (
					'9' => array('css' => 'default', 'name' => '已取消'),
					'0' => array('css' => 'danger', 'name' => '待付款'),
					'1' => array('css' => 'info', 'name' => '已付款'),
					'2' => array('css' => 'warning', 'name' => '待完成'),
					'3' => array('css' => 'success', 'name' => '已完成')
			);
			foreach ($list as &$value) {
				$s = $value['status'];
				$value['statuscss'] = $orderstatus[$value['status']]['css'];
				$value['status'] = $orderstatus[$value['status']]['name'];
				if ($s < 1) {
					$value['css'] = $paytype[$s]['css'];
					$value['pay_type'] = $paytype[$s]['name'];
					continue;
				}
				$value['css'] = $paytype[$value['pay_type']]['css'];
				if ($value['pay_type'] == 2) {
					if (empty($value['transid'])) {
						$value['pay_type'] = '支付宝支付';
					} else {
						$value['pay_type'] = '微信支付';
					}
				} else {
					$value['pay_type'] = $paytype[$value['pay_type']]['name'];
				}
			}

			$total = pdo_fetchcolumn(
						'SELECT COUNT(*) FROM ' . tablename('daijia_orders') ." WHERE $condition", $paras);
			$pager = pagination($total, $pindex, $psize);
		} elseif ($operation == 'detail') {
			
			$id = intval($_GPC['id']);
			
			$item = pdo_fetch("SELECT * FROM " . tablename('daijia_orders') . " WHERE id = :id", array(':id' => $id));
			
			if (empty($item)) {
				message("抱歉，订单不存在!", referer(), "error");
			}
		    if (checksubmit('send')) {
				pdo_update('daijia_orders', array('status' => 2), array('id' => $id));
				message('订单操作成功！', referer(), 'success');
			}
			if (checksubmit('finish')) {
				pdo_update('daijia_orders', array('status' => 3), array('id' => $id));
				message('订单操作成功！', referer(), 'success');
			}			
			if (checksubmit('refund')) {	
				message('退款成功！', referer(), 'success');
			}			
			if (checksubmit('confrimpay')) {
				pdo_update('shopping_order', array('status' => 1, 'pay_type' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
				//设置库存
				$this->setOrderStock($id);
				message('确认订单付款操作成功！', referer(), 'success');
			}
		} elseif ($operation == 'delete') {
			$orderid = intval($_GPC['id']);		
			if (pdo_delete('daijia_orders', array('id' => $orderid))) {
				message('订单删除成功', $this->createWebUrl('order', array('op' => 'display')), 'success');
			} else {
				message('订单不存在或已被删除', $this->createWebUrl('order', array('op' => 'display')), 'error');
			}
		}
		include $this->template('order');
