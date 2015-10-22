<?php
defined('IN_IA') or exit('Access Denied');
class Choose_brideModuleSite extends WeModuleSite {
	public function doMobileIndex() {
		global $_GPC, $_W;
		$pro = pdo_fetch("select * from".tablename('choose_pro')."where uniacid = '{$_W['uniacid']}' and status =1");
		$persons = pdo_fetchall("select * from".tablename('choose_bride')."where pro_id={$pro['id']} order by num desc");
        include $this -> template('index');
	}
	
	public function doMobileConfirm() {
		global $_GPC, $_W;
		$type=$_GPC['type'];
		$pro = pdo_fetch("select * from".tablename('choose_pro')."where uniacid = '{$_W['uniacid']}' and status =1");
		if (checksubmit('submit')) {
			$data=array(
				'ordersn'=>date('md') . random(4, 1),
				'mobile'=>$_GPC['mobile'],
				'user_name'=>$_GPC['name'],
				'pro_id'=>$_GPC['pro_id'],
				'price'=>$pro['yuyue_price'],
				'openid'=>$_W['openid'],
				'uniacid'=>$_W['uniacid'],
				'status'=>0,
				'createtime' => TIMESTAMP
			);
			
			pdo_insert('choose_order', $data);
			$orderid = pdo_insertid();
			if($type == 'yufu'){
				pdo_update('choose_order', array('type'=>1), array('id' => $orderid));
				header("location: " .  $this->createMobileUrl('pay', array('orderid' => $orderid)));
				exit;
			}else{
				pdo_update('choose_order', array('type'=>0), array('id' => $code['id']));
				message('预约成功.', $this->createMobileUrl('index'));
			}
		}
		include $this -> template('confirm');
	}
	
	public function doMobileTry() {
		global $_GPC, $_W;
		$pro = pdo_fetch("select * from".tablename('choose_pro')."where uniacid = '{$_W['uniacid']}' and status=1");
		include $this -> template('try');
	}

	public function doMobilecoupon() {
		global $_GPC, $_W;
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($op == 'display') {
			$id = $_GPC['id'];
			if (empty($id)) {
			    message('参数错误');
			}
			$code = pdo_fetch("SELECT * FROM ".tablename('choose_order')." WHERE uniacid = '{$_W['uniacid']}' AND openid = '{$_W['openid']}' ");
			$codemess = pdo_fetch("SELECT * FROM ".tablename('choose_pro')." WHERE uniacid = '{$_W['uniacid']}' AND id = '{$id}' ");
			include $this -> template('code');
		}
		if ($op == 'post') {
			$id = $_GPC['id'];
			$code = pdo_fetch("SELECT * FROM ".tablename('choose_order')." WHERE uniacid = '{$_W['uniacid']}' AND openid = '{$_W['openid']}' AND mobile = '{$_GPC['mobile']}' ");
			if (!empty($code['code'])) {
				$status = false;
				$msg = '您已经领取过优惠券，请勿重复领取!';
			}elseif (!empty($code)) {
				$carttotal = random(4, 1).random(4, 1).random(4, 1);
				$data=array(
				    'uniacid'=>$_W['uniacid'],
				    'ordersn'=>date('md') . random(4, 1),
				    'openid'=>$_W['openid'],
				    'mobile'=>$_GPC['mobile'],
				    'code'=>$carttotal,
				    'pro_id'=>$id,
				    'createtime'=>TIMESTAMP
				);
				pdo_update('choose_order', $data, array('id' => $code['id']));
				pdo_query("update ".tablename('choose_pro')." set youhui_num=youhui_num+1 where id = '{$_GPC['huodong_id']}' ");
			}else{
				$carttotal = random(4, 1).random(4, 1).random(4, 1);
				$data=array(
				    'uniacid'=>$_W['uniacid'],
				    'ordersn'=>date('md') . random(4, 1),
				    'openid'=>$_W['openid'],
				    'mobile'=>$_GPC['mobile'],
				    'code'=>$carttotal,
				    'pro_id'=>$id,
				    'createtime'=>TIMESTAMP
				);
				pdo_insert('choose_order', $data);
				pdo_query("update ".tablename('choose_pro')." set youhui_num=youhui_num+1 where id = '{$_GPC['huodong_id']}' ");
			}
			$result = array(
				'status' => $status,
				'msg' => $msg,
				'coupon_bn' => $carttotal
			);
			die(json_encode($result));
		}
	}

	public function doMobileAdv() {
		global $_GPC, $_W;
		$id = $_GPC['id'];
		$adv = pdo_fetch("select * from".tablename('choose_adv')."where uniacid = '{$_W['uniacid']}' and id = {$id}");
		$thumbs = pdo_fetchall("select * from".tablename('choose_atlas')."where pro_id = {$adv['pro_id']} and type=1");
		include $this -> template('adv');
	}
	
	public function doMobileDetail() {
		global $_GPC, $_W;
		$ops = array('display', 'post'); // 只支持此 3 种操作.
		$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
		if($op == 'display') {
			$id = $_GPC['id'];//新人id
			$pro = pdo_fetch("select * from".tablename('choose_pro')."where uniacid = '{$_W['uniacid']}' and id = {$_GPC['pro_id']}");
			$person = pdo_fetch("select * from".tablename('choose_bride')."where uniacid = '{$_W['uniacid']}' and id = {$id}");
			$thumbs = pdo_fetchall("select * from".tablename('choose_atlas')."where bride_id = {$id}");
			include $this -> template('detail');
			exit;
		}
		
		if($op == 'post') {
			$thumb_id=$_GPC['thumb_id'];//图片id
			$openid = $_W['openid'];
			if(!empty($thumb_id)){
				$person = pdo_fetch("select * from".tablename('choose_dian')."where openid='{$openid}' and thumb_id = {$thumb_id}");
				if(empty($person)){
			    	$status = true;
			    	pdo_insert('choose_dian',array('thumb_id'=>$thumb_id,'openid'=>$openid));
					$thumb=pdo_fetch("select * from".tablename('choose_atlas')."where id = {$thumb_id}");
					$bride = pdo_fetch("select * from".tablename('choose_bride')."where id = {$thumb['bride_id']}");
					pdo_update('choose_bride',array('num'=>($bride['num']+1)),array('id'=>$bride['id']));
					pdo_update('choose_atlas',array('num'=>($thumb['num']+1)),array('id'=>$thumb['id']));
					echo 1;
					exit;
				}else{
					$status = false;
					echo 0;
					exit;
				}
			}
		}
	}
	
	public function doMobilePay() {
		global $_GPC, $_W;
		$orderid = $_GPC['orderid'];
		    		$order = pdo_fetch("SELECT * FROM " . tablename('choose_order') . " WHERE id ={$orderid}");
		    		$params['tid'] = $order['ordersn'];
		    		$params['user'] = $_W['fans']['from_user'];
		    		$params['fee'] = $order['price'];
		    		$params['title'] = $_W['account']['name'];
		    		$params['ordersn'] = $order['ordersn'];
					$params['module'] = "choose_bride";
	    include $this->template('pay');
		    
	}
	
	public function doWebAdv() {
	global $_GPC, $_W;
	load()->func('tpl');
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	if($op == 'display'){
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$advs = pdo_fetchall("SELECT * FROM ".tablename('choose_adv')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('choose_adv') . "WHERE uniacid = '{$_W['uniacid']}'"); //记录总数
		$pager = pagination($total, $pindex, $psize);
		include $this->template('adv');
	}
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('choose_adv').' WHERE id=:id ';
			$params = array(':id'=>$id);
			$adv = pdo_fetch($sql, $params);
			$listt = pdo_fetchall("SELECT * FROM" . tablename('choose_atlas') .  "WHERE pro_id = {$adv['pro_id']} and type=1");
			$piclist = array();
				if(is_array($listt)){
					foreach($listt as $p){
						$piclist[] = $p['thumb'];
					}
				}
		}
			
		if (checksubmit()) {
			$data = $_GPC['adv']; // 获取打包值
			empty($data['thumb']) && message('请上传图片');
			empty($data['name']) && message('请填广告名');
			empty($data['detail']) && message('请填广告详情');
			empty($data['price']) && message('请填广告价格');
			empty($data['taocan']) && message('请填广告套餐详情');
			$data['detail'] = htmlspecialchars_decode($data['detail']);
			$data['taocan'] = htmlspecialchars_decode($data['taocan']);
            if(empty($adv)){
            $images = $_GPC['img'];//获取图集
			if($images){
	                foreach ($images as $key => $value){
	                    $data1 = array(
	                        'thumb' => $images[$key], 
	                        'bride_id' => '',
	                        'pro_id' =>$data['pro_id'],
	                        'type'	=> 1
	                        );
	                    pdo_insert('choose_atlas',$data1);
	                }
	            }
				$data['uniacid'] = $_W['uniacid'];
				$data['createtime'] = TIMESTAMP;
				$ret = pdo_insert('choose_adv', $data);
				if (!empty($ret)) {
					$id = pdo_insertid();
				}
			} else {
			    pdo_delete('choose_atlas',  array('pro_id' => $adv['pro_id'],'type'=>1));
			     $images = $_GPC['img'];//获取图集
				if($images){
	                foreach ($images as $key => $value){
	                    $data1 = array(
	                        'thumb' => $images[$key], 
	                        'bride_id' => '',
	                        'pro_id' =>$data['pro_id'],
	                        'type'	=> 1
	                        );
	                    pdo_insert('choose_atlas',$data1);
	                }
	            }
				$ret = pdo_update('choose_adv', $data, array('id'=>$id));
			}
			message('信息保存成功', $this->createWebUrl('adv'), 'success');
		}
		include $this->template('advadd');
	}
	
	if($op == 'delete') {
		$id = intval($_GPC['id']); 
		if(empty($id)){
			message('未找到');
		}
		$result = pdo_delete('choose_adv', array('id'=>$id, 'uniacid'=>$_W['uniacid']));
		if(intval($result) == 1){
			message('删除成功.', $this->createWebUrl('adv'), 'success');
		} else {
			message('删除失败.');
		}
		include $this->template('adv');
	}
}
	public function doWebBride() {
	global $_GPC, $_W;
	load()->func('tpl');
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	if (!empty($_GPC['pro_id']) && $_SESSION['pro_id']!=$_GPC['pro_id']) {
		$_SESSION['pro_id'] = $_GPC['pro_id'];
	}
	if($op == 'display'){
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$brides = pdo_fetchall("SELECT * FROM ".tablename('choose_bride')." WHERE uniacid = '{$_W['uniacid']}' and pro_id ='{$_SESSION['pro_id']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('choose_bride') . "WHERE uniacid = '{$_W['uniacid']}' and pro_id ='{$_SESSION['pro_id']}'"); //记录总数
		$pager = pagination($total, $pindex, $psize);
		include $this->template('bride');
	}
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('choose_bride').' WHERE id=:id ';
			$params = array(':id'=>$id);
			$bride = pdo_fetch($sql, $params);
			$listt = pdo_fetchall("SELECT * FROM" . tablename('choose_atlas') .  "WHERE bride_id = '{$id}' and type = 0");
			$piclist = array();
				if(is_array($listt)){
					foreach($listt as $p){
						$piclist[] = $p['thumb'];
					}
				}
		}
			
		if (checksubmit()) {
			$data = $_GPC['bride']; // 获取打包值
			empty($data['MR_name']) && message('请填写新郎名字');
			empty($data['MS_name']) && message('请填写新娘名字');
			empty($data['thumb']) && message('请上传图片');
			empty($data['detail']) && message('请填写简介');
            if(empty($bride)){
            
				$data['uniacid'] = $_W['uniacid'];
				$data['createtime'] = TIMESTAMP;
				$ret = pdo_insert('choose_bride', $data);
				if (!empty($ret)) {
					$id = pdo_insertid();
				}
			$images = $_GPC['img'];//获取图集
			if($images){
	                foreach ($images as $key => $value){
	                    $data1 = array(
	                        'thumb' => $images[$key], 
	                        'bride_id' => $id,
	                        'pro_id' =>$data['pro_id'],
	                        'type'	=> 0,
	                        'name'=>''
	                        );
	                    pdo_insert('choose_atlas',$data1);
	                }
	            }
			} else {
			    $images = $_GPC['img'];//获取图集
				if($images){
					pdo_delete('choose_atlas',  array('bride_id' => $id));
	                foreach ($images as $key => $value){
	                    $data1 = array(
	                        'thumb' => $images[$key], 
	                        'bride_id' => $bride['id'],
	                        'pro_id' =>$data['pro_id'],
	                        'type'	=> 0
	                        );
	                    pdo_insert('choose_atlas',$data1);
	                }
	            }
				$ret = pdo_update('choose_bride', $data, array('id'=>$id));
			}
			message('信息保存成功', $this->createWebUrl('bride'), 'success');
		}
		
		include $this->template('brideadd');
	}
	
	if($op == 'delete') {
		$id = intval($_GPC['id']); 
		if(empty($id)){
			message('未找到');
		}
		$result = pdo_delete('choose_bride', array('id'=>$id, 'uniacid'=>$_W['uniacid']));
		if(intval($result) == 1){
			message('删除成功.', $this->createWebUrl('bride'), 'success');
		} else {
			message('删除失败.');
		}
		include $this->template('bride');
	}
	
	}
	public function doWebPro() {
	global $_GPC, $_W;
	load()->func('tpl');
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	
	if($op == 'display'){
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$pros = pdo_fetchall("SELECT * FROM ".tablename('choose_pro')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('choose_pro') . "WHERE uniacid = '{$_W['uniacid']}'"); //记录总数
		$pager = pagination($total, $pindex, $psize);
		include $this->template('pro');
	}
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('choose_pro').' WHERE id=:id ';
			$params = array(':id'=>$id);
			$pro = pdo_fetch($sql, $params);
			$listt = pdo_fetchall("SELECT * FROM" . tablename('choose_atlas') .  "WHERE pro_id = '{$id}' and type = 2");
			$piclist = array();
				if(is_array($listt)){
					foreach($listt as $p){
						$piclist[] = $p['thumb'];
					}
				}
		}
		if (checksubmit()) {
			$data = $_GPC['pro']; // 获取打包值
			$data['starttime']=strtotime( $_GPC['datelimit']['start']);
            $data['endtime']=strtotime( $_GPC['datelimit']['end']);
			empty($data['name']) && message('请填写项目名称');
			empty($data['copyright']) && message('请填写版权信息');
			empty($data['thumb']) && message('请上传图片');
			empty($data['detail']) && message('请填写项目简介');
			empty($data['starttime']) && message('请填写项目上架时间');
			empty($data['yuyue_name']) && message('请填写预约主题');
			empty($data['yuyue_price']) && message('请填写预约价格');
			empty($data['yuyue_detail']) && message('请填写预约详情');
			empty($data['youhui_name']) && message('请填写优惠名');
			empty($data['youhui_price']) && message('请填写优惠价格');
			empty($data['youhui_detail']) && message('请填写优惠详情');
			$data['yuyue_detail'] = htmlspecialchars_decode($data['yuyue_detail']);
            if(empty($pro)){
				$data['uniacid'] = $_W['uniacid'];
				$data['createtime'] = TIMESTAMP;
				$ret = pdo_insert('choose_pro', $data);
				if (!empty($ret)) {
					$id = pdo_insertid();
				}
			} else {
				$ret = pdo_update('choose_pro', $data, array('id'=>$id));
			}
			message('项目信息保存成功', $this->createWebUrl('pro'), 'success');
		}
		
		include $this->template('proadd');
	}
	
	if($op == 'delete') {
		$id = intval($_GPC['id']); //要删除的商品的id
		if(empty($id)){
			message('未找到项目');
		}
		$result = pdo_delete('choose_pro', array('id'=>$id, 'uniacid'=>$_W['uniacid']));
		if(intval($result) == 1){
			message('删除项目成功.', $this->createWebUrl('pro'), 'success');
		} else {
			message('删除项目失败.');
		}
		include $this->template('pro');
	}
      
	}
    public function payResult($params) {
		global $_W;
		$fee = intval($params['fee']);	
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => 1, 'wechat' => 2, 'alipay' => 2, 'delivery' => 3);
		$data['pay_type'] = $paytype[$params['type']];
		// //货到付款
		if ($params['type'] == 'delivery') {
			$data['status'] = 1;
			$data['ptime'] = TIMESTAMP;
		}
		if($params['result'] == 'success'){
			$data['ptime'] = TIMESTAMP;
		}	
		pdo_update('choose_order', $data, array('ordersn' => $params['tid']));
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
	public function doWebYuyue(){
		global $_GPC, $_W;
	    load()->func('tpl');
		$type = $_GPC['type'];
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
	    if ($operation == 'display') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$status = $_GPC['status'];
		$condition = " uniacid = '{$_W['uniacid']}' and pro_id = '{$_SESSION['pro_id']}'";
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		if(!empty($type)){
			if($type=='yuyue'){
				$condition.= " AND type=0";
			}else{
				$condition.= " AND type=1";
			}
		}
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND ordersn LIKE '%{$_GPC['keyword']}%'";
		}
		if (!empty($_GPC['member'])) {
			$condition .= " AND (user_name LIKE '%{$_GPC['member']}%' or mobile LIKE '%{$_GPC['member']}%')";
		}
		if (!empty($_GPC['time'])) {
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']) + 86399;
			$condition .= " AND createtime >= '{$starttime}' AND createtime <= '{$endtime}' ";
		}
		$list = pdo_fetchall("select * from".tablename('choose_order')."where $condition");
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('choose_order') ." WHERE $condition", $paras);
		$pager = pagination($total, $pindex, $psize);
		
	} elseif ($operation == 'delete') {
		/*订单删除*/
		$orderid = intval($_GPC['id']);
		if (pdo_delete('choose_order', array('id' => $orderid))) {
			message('订单删除成功', $this->createWebUrl('yuyue', array('op' => 'display')), 'success');
		} else {
			message('订单不存在或已被删除', $this->createWebUrl('yuyue', array('op' => 'display')), 'error');
		}
	}
		include $this->template('yuyue');
}
}
?>