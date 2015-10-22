<?php
global $_GPC, $_W;
$url_origins_ip = $_GPC['url_origins_ip'];
$url_destination_ip = $_GPC['url_destination_ip'];
		$submit=$_GPC['sub'];
		$pricetype = $_GPC['pricetype'];
		if($pricetype!=''){
			if($pricetype == 0){
				$str = '重10公斤以内，20元每次';
				$price = 20;
			}
			if($pricetype == 1){
				$str = '重10-50公斤，60元每次';
				$price = 60;
			}
			if($pricetype == 2){
				$str = '重51-200公斤，200元每次';
				$price = 200;
			}
			if($pricetype == 3){
				$str = '重10公斤以内，100元每次';
				$price = 100;
			}
			if($pricetype == 4){
				$str = '重10-50公斤，200元每次';
				$price = 200;
			}
			if($pricetype == 5){
				$str = '重51-200公斤，300元每次';
				$price = 300;
			}
			if($pricetype == 6){
				$str = '重10公斤以内，300元每次';
				$price = 300;
			}
			if($pricetype == 7){
				$str = '重10-50公斤，500元每次';
				$price = 500;
			}
			if($pricetype == 8){
				$str = '重51-200公斤，800元每次';
				$price = 800;
			}
		}
		$data = array(
			'add1'=>$_GPC['add1'],
			'add2'=>$_GPC['add2'],
			'km'  =>$_GPC['km'],
			'seconds'=>$_GPC['seconds'],
			'mobile'=>$_GPC['mobile'],
			'hours'=>$_GPC['hours']
		);
		
		
		if($submit=='daijia'){
			$type = 0;
		}
		if($submit=='daibu'){
			$type = 1;
		}
		if($submit=='dailao'){
			$type = 2;
		}
		if($submit !=''){
			if($data['add1']!=''){
			$p = pdo_fetch("select * from".tablename('daijia_price')."where status = 1 and type = {$type}");
			if($data['km']>$p['start_km']){
				$price = $p['start_price']+($data['km']-$p['start_km'])*$p['km_price'];
				$price = number_format($price,2);
			}else{
				$price =$p['start_price'];
				$price = number_format($price,2);
			}
			
		}
		}
		
		if(checksubmit('submit')){
			$ttt = $_GPC['type'];
			$ppp = $_GPC['price'];
		if($ttt=='daijia'){
			$type = 0;
				$typename = 'daijia';
		}
		if($ttt=='daibu'){
			$type = 1;
			$typename = 'daibu';
		}
		if($ttt=='dailao'){
			$type = 2;
			$typename = 'dailao';
		}
			if(empty($data['mobile'])){
				$data['mobile'] = $_GPC['mobile'];
			}
			$data2 = array(
			'uniacid' => $_W['uniacid'],
			'openid' => $_W['openid'],
            'pay_time' =>'',//支付成功时间
            'pay_type'=>'',
			'ordersn' => date('md') . random(4, 1),
			'km_price'=>1,
			'price' => $ppp,
			'status' => 0,//订单状态，-1取消状态，0普通状态，1为已付款，2为已发货，3为成功
			'start_address'=>$data['add1'],
			'end_address'=>$data['add2'],
			'mobile'=>$data['mobile'],
			'remark'=>$_GPC['remark'],
			'km'=>$data['km'],
			'hours'=>$data['hours'],
			'type'=>$typename,
			'createtime' => TIMESTAMP
		);
//		echo "<pre>";
//			print_r($data2);exit;
			pdo_insert('daijia_orders', $data2);
			$orderid = pdo_insertid();
			
			header("location: " .  $this->createMobileUrl('pay', array('orderid' => $orderid)));
		}
		include $this->template('orderconfirm');
?>