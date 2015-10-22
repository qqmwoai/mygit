<?php
defined('IN_IA') or exit('Access Denied');
class RebateModuleSite extends WeModuleSite {
	public function __construct(){
		global $_W;
		pdo_delete('rebate_record', array('cjmfromopenid' => ''));
		load()->model('mc');
		$profile = pdo_fetch("SELECT * FROM " . tablename('rebate_member') . " WHERE uniacid ='{$_W['uniacid']}' and openid = '{$_W['openid']}'");
		if (empty($profile)) {
			$userinfo = mc_oauth_userinfo();
			if (!empty($userinfo['avatar'])) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'openid' => $userinfo['openid'],
					'nickname' => $userinfo['nickname'],
					'avatar' => $userinfo['avatar']
				);
				$member = pdo_fetch("SELECT * FROM " . tablename('rebate_member') . " WHERE uniacid ='{$_W['uniacid']}' and openid = '{$userinfo['openid']}'");
				if (empty($member['id'])) {
					pdo_insert('rebate_member', $data);
				}else{
					pdo_update('rebate_member', $data, array('id' =>$member['id']));
				}
			}
		}
	}

	/*微信端*/
	public function doMobileIndex() {
		global $_GPC, $_W;
//		pdo_delete('rebate_orders', array('status' => 0));
//		pdo_update('rebate_goods',array('goodsn'=>'','status'=>9),array('price'=>'0.01'));
		//分享
		$share_title = $this->module['config']['share_title'];
		$share_image = $this->module['config']['share_image'];
		$share_desc = $this->module['config']['share_desc'];
		$rule = $this->module['config']['content'];
		$image = $this->module['config']['share_xuanchuanimage'];
		$mode = $this->module['config']['mode'];//是否商品推荐
		if($mode == 2){
			//开启商品推荐
			$picmode = $this->module['config']['picmode'];//商品来源
			if($picmode == 1){
				//来自拼团
				$goods = pdo_fetchall("SELECT * FROM " . tablename('tg_goods') . " WHERE uniacid={$_W['uniacid']}  limit 0,10");
				
			}else{
				$goods = pdo_fetchall("SELECT * FROM " . tablename('shopping_goods') . " WHERE uniacid={$_W['uniacid']}  limit 0,10");
			}
		}
		
		$first = $_GPC['first'];
		$uniacid = $_W['uniacid'];
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$goods_yesterday = pdo_fetch("SELECT * FROM " . tablename('rebate_goods') . " WHERE uniacid={$uniacid} and status = 0");
		//昨日开奖
		$goods_today = pdo_fetch("SELECT * FROM " . tablename('rebate_goods') . " WHERE uniacid={$uniacid} and status = 1");
		//今日抢购
		$goods_tomorrow = pdo_fetch("SELECT * FROM " . tablename('rebate_goods') . " WHERE uniacid={$uniacid} and status = 2");
		//明日预告
		//获取昨日中奖人信息
		$order_yesterday = pdo_fetchall("select * from" . tablename('rebate_orders') . "where status <>0 and goodsn='{$goods_yesterday['goodsn']}' and get=1");
		foreach($order_yesterday as $key=>$value){
			if(!empty($value['openid'])){
				$profile =  pdo_fetch("select * from" . tablename('rebate_member') . "where uniacid={$_W['uniacid']} and openid='{$value['openid']}'");
				$order_yesterday[$key]['thumb']=$profile['avatar'];
				$order_yesterday[$key]['nickname']=$profile['nickname'];
			}
			
			
		}
		//判断是否领奖成功
		$hours = $goods_today['hours'];//抢购时限
		$cjm_num = $this->module['config']['cjm_num'];//抽奖码个数限制
		$hours_tomorrow = $hours+24;
		foreach ($order_yesterday as $key => $value) {
			if ($value['name'] == '') {
				$zjname = 'yh_';
				//随机生成名字
				$chars = '0123456789';
				for ($i = 0; $i < 9; $i++) {
					$zjname .= $chars[mt_rand(0, strlen($chars) - 1)];
				}
			}
			$order_yesterday[$key]['zjname'] = $zjname;
		}

		//判断是否中奖，显示在昨日开奖中
		$buy_yesterday = pdo_fetch("select * from" . tablename('rebate_orders') . "where openid='{$_W['openid']}' and status <>0 and goodsn='{$goods_yesterday['goodsn']}'");
		if (empty($buy_yesterday)) {
			$yesterday = 0;
			//未参与
		} else {
			//参与了取出抽奖码
			$cjm_yesterday = pdo_fetchall("select * from" . tablename('rebate_record') . "where uniacid={$uniacid} and goodsn='{$goods_yesterday['goodsn']}' and openid='{$_W['openid']}' ");
			if ($buy_yesterday['get'] == 1) {
				$yesterday = 1;
				//中奖了
				$zjm_yesterday = pdo_fetch("select * from" . tablename('rebate_orders') . "where uniacid={$uniacid} and goodsn='{$goods_yesterday['goodsn']}' and openid='{$_W['openid']}' and get=1");

			} else {
				$yesterday = 2;
				//未中奖
			}
		}
		/*昨日开奖*/

		/*今日抢购*/
		//判断是否购买过
		$buybuy = pdo_fetch("select * from" . tablename('rebate_orders') . "where openid='{$_W['openid']}' and status<>0 and goodsn='{$goods_today['goodsn']}' ");
		//判断抽奖码个数$count
		if (!empty($buybuy)) {
			$records = pdo_fetchall("SELECT * FROM " . tablename('rebate_record') . " WHERE uniacid={$uniacid} and openid='{$_W['openid']}' and goodsn='{$goods_today['goodsn']}' and cjmfromopenid <> '' order by id asc");
			$count = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('rebate_record') . " WHERE uniacid={$uniacid} and openid='{$_W['openid']}' and goodsn='{$goods_today['goodsn']}' and cjmfromopenid <> '' ");
			$record_one = pdo_fetch("SELECT * FROM " . tablename('rebate_record') . " WHERE uniacid={$uniacid} and openid='{$_W['openid']}' and goodsn='{$goods_today['goodsn']}'");
			$yqm = $record_one['yqm'];
			//获得最新得到的抽奖码
			foreach ($records as $key => $value) {
				$cjm = $value['cjm'];
			}
		}
		/*今日抢购，完*/
		/*历史抢购*/
		$sql = "select * from" . tablename('rebate_changci') . "where uniacid={$_W['uniacid']} order by createtime desc limit 0,10";
		$changcis = pdo_fetchall($sql);
		foreach ($changcis as $key => $value) {
			$goodsone = pdo_fetch("select * from" . tablename('rebate_goods') . "where  id='{$value['gid']}'");
			$changcis[$key]['thumb'] = $goodsone['thumb'];
			$changcis[$key]['price'] = $goodsone['price'];
			$changcis[$key]['gname'] = $goodsone['gname'];
			$changcis[$key]['gdesc'] = $goodsone['gdesc'];
			$changcis[$key]['time'] = substr($value['goodsn'], 0, 8);
			$myorder = pdo_fetch("select * from" . tablename('rebate_orders') . "where openid='{$_W['openid']}' and status<>0 and goodsn='{$value['goodsn']}' and uniacid={$_W['uniacid']}");
			$myorder_zj = pdo_fetch("select * from" . tablename('rebate_orders') . "where openid='{$_W['openid']}' and status<>0  and goodsn='{$value['goodsn']}' and get=1 and uniacid={$_W['uniacid']}");
			if (!empty($myorder)) {
				if (!empty($myorder_zj)) {
					$zj = 1;
					$changcis[$key]['recvname'] = $myorder_zj['recvname'];
					$changcis[$key]['mobile'] = $myorder_zj['mobile'];
					$changcis[$key]['address'] = $myorder_zj['address'];
					$changcis[$key]['zjm'] = $myorder_zj['zjm'];
				} else {
					$zj = 2;
				}
				$myrecords = pdo_fetchall("SELECT * FROM " . tablename('rebate_record') . " WHERE uniacid={$uniacid} and openid='{$_W['openid']}' and goodsn={$value['goodsn']}");
				foreach ($myrecords as $k => $v) {
					$changcis[$key]['myorder_cjm'][$k]['mycjm'] = $v['cjm'];
				}
			} else {
				$zj = 0;
			}
			$changcis[$key]['zj'] = $zj;
		}
//	echo "<pre>";
//	print_r($changcis);exit;
		/*历史抢购*/
		if ($op == 'display') {
			include $this -> template('index');
		}
		if ($op == 'buy') {
			$data = array(
			'uniacid' => $_W['uniacid'], 
			'openid' => $_W['openid'], 
			'ptime' => '', //支付成功时间
			'ordersn' => date('md') . random(4, 1), 
			'price' => $goods_today['price'], 
			'status' => 0, //订单状态，-1取消状态，0普通状态，1为已付款，2为已发货，3为成功
			'goodsid' => $goods_today['id'], 
			'goodsn' => $goods_today['goodsn'], 
			'createtime' => TIMESTAMP
			);
			pdo_insert('rebate_orders', $data);
			$orderid = pdo_insertid();
			header("location: " . $this -> createMobileUrl('pay', array('orderid' => $orderid)));
		}

		if ($op == 'inputcode') {
			$yqm = $_GPC['code'];
			/*获取的邀请码*/
			//判断邀请码是否正确
			$recd = pdo_fetchall("SELECT * FROM " . tablename('rebate_record') . " WHERE uniacid={$_W['uniacid']}  and goodsn='{$goods_today['goodsn']}' and yqm='{$yqm}'");
			//为了获取该邀请码来自的openid
			$re = pdo_fetch("SELECT * FROM " . tablename('rebate_record') . " WHERE uniacid={$_W['uniacid']}  and goodsn='{$goods_today['goodsn']}' and yqm='{$yqm}'");
			if (empty($recd)) {
				echo 0;
				exit ;
			} else {
				foreach ($records as $key => $value) {
					if ($value['cjmfromopenid'] == $re['openid']) {
						echo 0;
						exit ;
					}
				}
				$createcjm = '';
				//随机生成抽奖码
				$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				for ($i = 0; $i < 4; $i++) {
					$createcjm .= $chars[mt_rand(0, strlen($chars) - 1)];
				}
				$dat = array('yqm' => $record_one['yqm'], 'cjm' => $createcjm, 'cjmfromopenid' => $re['openid'], 'goodsn' => $record_one['goodsn'], 'openid' => $record_one['openid'], 'goodsid' => $record_one['goodsid'], 'uniacid' => $record_one['uniacid'], 'ordersn' => $record_one['ordersn'], 'createtime' => TIMESTAMP);
				pdo_insert('rebate_record', $dat);
				echo 1;
				exit ;
			}

		}
		if ($op == 'getprize') {
			$zjcode = $_GPC['zjcode'];
			$getname = $_GPC['getname'];
			$getmobile = $_GPC['getmobile'];
			$getaddress = $_GPC['getaddress'];
			$zjcodehsy = $_GPC['zjcodehsy'];
			$getnamehsy = $_GPC['getnamehsy'];
			$getmobilehsy = $_GPC['getmobilehsy'];
			$getaddresshsy = $_GPC['getaddresshsy'];
			if (!empty($zjcodehsy) && !empty($getnamehsy) && !empty($getmobilehsy) && !empty($getaddresshsy)) {
				$res = pdo_update('rebate_orders', array('recvname' => $getnamehsy, 'mobile' => $getmobilehsy, 'address' => $getaddresshsy), array('zjm' => $zjcodehsy));
				if ($res) {
					echo 1;
					exit ;
				} else {
					echo 0;
					exit ;
				}
			}
			$res = pdo_update('rebate_orders', array('recvname' => $getname, 'mobile' => $getmobile, 'address' => $getaddress), array('zjm' => $zjcode));
			if ($res) {
				echo 1;
				exit ;
			} else {
				echo 0;
				exit ;
			}
		}
		if ($op == 'nozjhsy') {
			$hsygoodsn = $_GPC['hsygoodsn'];
			//自己的码
			$recdshsy = pdo_fetchall("SELECT * FROM " . tablename('rebate_record') . " WHERE uniacid={$_W['uniacid']}  and goodsn='{$hsygoodsn}' and openid='{$_W['openid']}' ");
			$hsycjms = array();
			foreach ($recdshsy as $key => $value) {
				$hsycjms[] = $value['cjm'];
			}
			//中奖的码
			$order_zjhsy = pdo_fetchall("select * from" . tablename('rebate_orders') . "where status<>0  and goodsn='{$hsygoodsn}' and get=1");
			$hsyzjms = array();
			foreach ($order_zjhsy as $key => $value) {
				$hsyzjms[] = $value['zjm'];
			}
			$data = array('cjm' => $hsycjms, 'zjm' => $hsyzjms);
			return json_encode($data);
		}
	}

	public function doMobilePay() {
		global $_GPC, $_W;
		$orderid = $_GPC['orderid'];
		if (empty($orderid)) {
			message('抱歉，参数错误！', '', 'error');
		} else {
			$order = pdo_fetch("SELECT * FROM " . tablename('rebate_orders') . " WHERE id ={$orderid}");
			$params['tid'] = $order['ordersn'];
			$params['user'] = $_W['fans']['from_user'];
			$params['fee'] = $order['price'];
			$params['title'] = $_W['account']['name'];
			$params['ordersn'] = $order['ordersn'];
			$params['module'] = "rebate";
			include $this -> template('ptpay');
		}

	}

	/*web端*/
	public function doWebGoods() {
		global $_GPC, $_W;
		load() -> func('tpl');
		$ops = array('display', 'edit', 'delete');
		// 只支持此 3 种操作.
		$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
		//未上架产品数
		$numgoods = pdo_fetchall("SELECT * FROM " . tablename('rebate_goods') . " WHERE uniacid={$_W['uniacid']} and status in(0,1,2)");
		$num = count($numgoods);
		if ($op == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			//当前页码
			$psize = 10;
			//设置分页大小
			$condition = " uniacid = '{$_W['uniacid']}'";
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND gname LIKE '%{$_GPC['keyword']}%'";
			}
			$goodses = pdo_fetchall("SELECT * FROM " . tablename('rebate_goods') . " WHERE $condition ORDER BY status asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rebate_goods') . "WHERE $condition ");
			//记录总数
			$pager = pagination($total, $pindex, $psize);
			include $this -> template('goods');
		}

		//商品编辑
		if ($op == 'edit') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$sql = 'SELECT * FROM ' . tablename('rebate_goods') . ' WHERE id=:id ';
				$paramse = array(':id' => $id);
				$goods = pdo_fetch($sql, $paramse);
				if (empty($goods)) {
					message('未找到指定的商品.', $this -> createWebUrl('goods'));
				} else {
					//设置上架状态
					$status = intval($_GPC['status']);
					if ($status == 2) {
						//先检查是否产生了中奖码
						$goodstow = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$_W['uniacid']} and status=1");
						if($goodstow){
							$zjms = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$_W['uniacid']} and goodsn='{$goodstow['goodsn']}' and get=1");
							if(count($zjms)==0){
								message('今日还未抽奖，请先抽奖再上架产品！', $this -> createWebUrl('order', array('op' => 'display', 'status' => 1)));
							exit ;
							}
						}
						
						$goods_tomorrow = pdo_fetch("SELECT * FROM " . tablename('rebate_goods') . " WHERE uniacid={$_W['uniacid']} and status = 2");
						$goods_yesterday = pdo_fetch("SELECT * FROM " . tablename('rebate_goods') . " WHERE uniacid={$_W['uniacid']} and status = 0");
						if (!empty($goods_yesterday)) {
							pdo_update('rebate_goods', array('status' => 9), array('status' => 0));
						}
						if($num==1){
							pdo_update('rebate_goods', array('status' => 2), array('id' => $id));
							header("location: " . $this -> createWebUrl('goods'));
						}else{
							pdo_update('rebate_goods', array('status' => 0), array('status' => 1));
							pdo_update('rebate_goods', array('status' => 1), array('status' => 2));
							pdo_update('rebate_goods', array('status' => 2), array('id' => $id));
							//设置每个商品每天的标识
							$goodsn = date('Ymd') . random(4, 1);
							$uptime = TIMESTAMP;
							pdo_update('rebate_goods', array('goodsn' => $goodsn,'uptime'=>$uptime,'num'=>0), array('status' => 1));
							//保存场次
							$data=array(
								'gid'=>$goods_tomorrow['id'],
								'goodsn'=>$goodsn,
								'uniacid'=>$_W['uniacid'],
								'createtime'=>TIMESTAMP
							);
							pdo_insert('rebate_changci',$data);
							header("location: " . $this -> createWebUrl('goods'));
							}
						
					}
					if ($status == 1) {
						pdo_update('rebate_goods', array('status' => 1), array('id' => $id));

						//设置每个商品每天的标识
						$goodsn = date('Ymd') . random(4, 1);
						$uptime = TIMESTAMP;
						pdo_update('rebate_goods', array('goodsn' => $goodsn,'uptime'=>$uptime,'num'=>0), array('status' => 1));
						//保存场次
						if($num==0){
							$goods_tomorrow['id'] = $id;
						}
						$data=array(
							'gid'=>$goods_tomorrow['id'],
							'goodsn'=>$goodsn,
							'uniacid'=>$_W['uniacid'],
							'createtime'=>TIMESTAMP
						);
						pdo_insert('rebate_changci',$data);
						header("location: " . $this -> createWebUrl('goods'));
					}
				}
			}
			if (checksubmit()) {
				$gid = $_GPC['id'];
				$data = $_GPC['goods'];
				// 获取打包值
				empty($data['gname']) && message('请填写商品名称');
				empty($data['price']) && message('请填写商品价格');
				empty($data['thumb']) && message('请上传图片');
				$data['gdesc'] = htmlspecialchars_decode($data['gdesc']);
				if (empty($gid)) {
					$data['uniacid'] = $_W['uniacid'];
					$data['createtime'] = TIMESTAMP;
					$data['status'] = 9;
					$ret = pdo_insert('rebate_goods', $data);
					if (!empty($ret)) {
						$id = pdo_insertid();
					}
				} else {

					$ret = pdo_update('rebate_goods', $data, array('id' => $id));
				}
				message('商品信息保存成功', $this -> createWebUrl('goods'), 'success');
			}
			include $this -> template('goodsadd');
		}

		if ($op == 'delete') {
			$id = intval($_GPC['id']);
			//要删除的商品的id
			if (empty($id)) {
				message('未找到指定商品分类');
			}
			$result = pdo_delete('rebate_goods', array('id' => $id, 'uniacid' => $_W['uniacid']));
			if (intval($result) == 1) {
				message('删除商品成功.', $this -> createWebUrl('goods'), 'success');
			} else {
				message('删除商品失败.');
			}
		}
	}

	public function doWebOrder() {
		global $_GPC, $_W;
		load() -> func('tpl');
		$weid = $_W['uniacid'];
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'display') {
			//中奖人数
			$zj_num = $this->module['config']['zj_num'];
			$type = $_GPC['type'];
			//产生中奖码，并给中奖的订单做上标记
			if ($type == 'createzjm') {
				$goodsss = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$_W['uniacid']} and status=1");
				$zjmsss = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$_W['uniacid']} and goodsn={$goodsss['goodsn']} and get=1");
				if(count($zjmsss)>0){
					message("不可重新产生中奖码！");
						exit ;
				}
				$sql2 = " select * FROM " . tablename('rebate_orders') . "where goodsn={$goodsss['goodsn']} ORDER BY rand() LIMIT 0, {$zj_num}";
				$test = pdo_fetchall($sql2);
				foreach ($test as $key => $value) {
					//更具ordersn从record表中取抽奖码
					$record = pdo_fetch("select * from" . tablename('rebate_record') . "where uniacid={$_W['uniacid']} and ordersn='{$value['ordersn']}'");
					pdo_update('rebate_orders', array('get' => 1, 'zjm' => $record['cjm']), array('ordersn' => $value['ordersn']));
					$test[$key]['zjm'] = $record['cjm'];
				}
				if(empty($test)){
					message("没有订单，不能抽选中奖码！",$this->createWebUrl('order',array('status'=>1)),'success');
						exit ;
				}
			}
			if ($type == 'createxunizjm') {
				
				$goodsss = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$_W['uniacid']} and status=1");
				$zjmsss = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$_W['uniacid']} and goodsn={$goodsss['goodsn']} and get=1");
				if(count($zjmsss)>0){
					message("不可重新产生虚拟中奖码！");
						exit ;
				}
				
				for($i=0;$i<$zj_num;$i++){
					$zjm='';
					$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
					for ($ia = 0; $ia < 4; $ia++) {
						$zjm .= $chars[mt_rand(0, strlen($chars) - 1)];
					}
					$data = array(
						'uniacid' => $_W['uniacid'], 
						'openid' => '', 
						'ptime' => '', //支付成功时间
						'ordersn' => date('md') . random(4, 1), 
						'price' => $goodsss['price'], 
						'status' => 1, 
						'goodsid' => $goodsss['id'], 
						'goodsn' => $goodsss['goodsn'], 
						'createtime' => TIMESTAMP,
						'get'=>1,
						'recvname'=>'虚拟',
						'mobile'=>'虚拟',
						'address'=>'虚拟',
						'zjm'=>$zjm
					);	   
					pdo_insert('rebate_orders', $data);
				}
				
			}

			//上架状态
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$status = $_GPC['status'];
			$condition = 'uniacid=:weid';
			$paras = array(':weid' => $_W['uniacid']);
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND  goodsn LIKE '%{$_GPC['keyword']}%'";
			}
			if ($status != '') {
				$goods_one = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$weid} and status='{$status}'");
				if(!empty($goods_one)){
					$condition .= " AND  goodsn = '{$goods_one['goodsn']}' ";
				}else{
					$condition .= " AND  goodsn = 1 ";
				}
				
			}
			//获取所有场次
			$sql = "select * from" . tablename('rebate_changci') . "where $condition order by createtime desc " . "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
			$changcis = pdo_fetchall($sql, $paras);
			$cjm_num = $this->module['config']['cjm_num'];
			$zj_num = $this->module['config']['zj_num'];
			foreach ($changcis as $chang => $ci) {
				//每个场次中的商品
				$goods_one = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$weid} and id='{$ci['gid']}'");
				//将需要显示的信息赋值
				$changcis[$chang]['thumb'] = $goods_one['thumb'];
				$changcis[$chang]['id'] = $goods_one['id'];
				$changcis[$chang]['num'] = $goods_one['num'];
				$changcis[$chang]['cjm_num'] = $cjm_num;
				$changcis[$chang]['zj_num'] = $zj_num;
				$changcis[$chang]['createtime'] = $goods_one['uptime'];
			}
			//获取中奖码
			$xuni=9;
			$goodstow = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$weid} and status=1");
			if(!empty($goodstow)){
				$zjms = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$weid} and goodsn='{$goodstow['goodsn']}' and get=1");
				if(!empty($zjms)){
				$order_oone= pdo_fetch("select * from" . tablename('rebate_orders') . "where uniacid={$weid} and get=1 and goodsn='{$goodstow['goodsn']}'");
				if(empty($order_oone['openid'])){
						$xuni = 1;
					}else{
						$xuni=0;
					}
				}
			}
			
			
			$total = count($changcis);
			$pager = pagination($total, $pindex, $psize);
		}
		if ($operation == 'detail') {
			$goodsid = $_GPC['id'];
			$goodsn = $_GPC['goodsn'];
			//商品信息
			if (!empty($goodsid)) {
				$goods = pdo_fetch("select * from" . tablename('rebate_goods') . "where uniacid={$weid} and id={$goodsid}");
			}
			if ($goodsn) {
				$ordes_zj = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$weid} and goodsn='{$goodsn}' and get=1");
				//获取中奖码
				$zjms = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$weid} and goodsn='{$goodsn}' and get=1");
			}
			$type = $_GPC['type'];
			$orderid = $_GPC['id'];
			if (!empty($type)) {
				if ($type == 'fahuo') {
					$express = $_GPC['express'];
					$expresssn = $_GPC['expresssn'];
					$orderid2 = $_GPC['orderid2'];
					$res = pdo_update('rebate_orders', array('status' => 2, 'express' => $express, 'expresssn' => $expresssn, ), array('id' => $orderid2));
					if ($res) {
						$data['success'] = 1;
					} else {
						$data['success'] = 0;
					}
					return json_encode($data);
				}
				if ($type == 'cancelsend') {
					$orderid2 = $_GPC['orderid2'];
					$res = pdo_update('rebate_orders', array('status' => 1), array('id' => $orderid2));
					$data = array();
					if ($res) {
						$data['success'] = 1;
					} else {
						$data['success'] = 0;
					}
					return json_encode($data);
				}
				if ($type == 'finish') {
					$orderid2 = $_GPC['orderid2'];
					$res = pdo_update('rebate_orders', array('status' => 4), array('id' => $orderid2));
					$data = array();
					if ($res) {
						$data['success'] = 1;
					} else {
						$data['success'] = 0;
					}
					return json_encode($data);
				}
			}
		}
		if ($operation == 'delete') {
			$goodsn = $_GPC['goodsn'];
			pdo_delete('rebate_orders',array('goodsn'=>$goodsn));
			pdo_delete('rebate_changci',array('goodsn'=>$goodsn));
			message('删除场次成功', $this -> createWebUrl('order'), 'success');
	
		}
		
		if ($operation == 'zjxx') {
			//获取所有中奖者订单
			$ordes_zj = pdo_fetchall("select * from" . tablename('rebate_orders') . "where uniacid={$weid} and  get=1 order by createtime desc");
				//获取中奖码
			foreach($ordes_zj as $key=>$value){
				$goods = pdo_fetch("select * from" . tablename('rebate_goods') . "where id={$value['goodsid']}");
				$ordes_zj[$key]['jpname'] = $goods['gname'];
				$ordes_zj[$key]['thumb'] = $goods['thumb'];
			}
			$type = $_GPC['type'];
			if (!empty($type)) {
				if ($type == 'fahuo') {
					$data = array();
					$express = $_GPC['express'];
					$expresssn = $_GPC['expresssn'];
					$orderid2 = $_GPC['orderid2'];
					$ooo = pdo_fetch("select * from" . tablename('rebate_orders') . "where id='{$orderid2}'");
					$goods = pdo_fetch("select * from" . tablename('rebate_goods') . "where id='{$ooo['goodsid']}'");
			//微信提醒
//			$sendinfo = '您的奖品我们已送出：\n';
//			$sendinfo .= '--------------------\n';
//			$sendinfo .= "快递单号: {$expresssn}\n";
//			$sendinfo .= "快递公司: {$express}\n";
//			$sendinfo .= "奖品: {$goods['gname']}\n";
//			$sendinfo .= "联系电话：{$ooo['mobile']}\n";
//			$sendinfo .= "发送地址：{$ooo['address']}\n";
//			//发送微信提醒
//			$send['msgtype'] = 'text';
//			$send['text'] = array('content' => urlencode($sendinfo));
//			$acc = WeAccount::create($_W['account']['acid']);
//			$send['touser'] = trim($ooo['openid']);
//			$s_mess = $acc->sendCustomNotice($send);
					//微信提醒发货完
					$res = pdo_update('rebate_orders', array('status' => 2, 'express' => $express, 'expresssn' => $expresssn, ), array('id' => $orderid2));
					if ($res) {
						$data['success'] = 1;
					} else {
						$data['success'] = 0;
					}
					return json_encode($data);
				}
				if ($type == 'cancelsend') {
					$orderid2 = $_GPC['orderid2'];
					$res = pdo_update('rebate_orders', array('status' => 1), array('id' => $orderid2));
					$data = array();
					if ($res) {
						$data['success'] = 1;
					} else {
						$data['success'] = 0;
					}
					return json_encode($data);
				}
				if ($type == 'finish') {
					$orderid2 = $_GPC['orderid2'];
					$res = pdo_update('rebate_orders', array('status' => 4), array('id' => $orderid2));
					$data = array();
					if ($res) {
						$data['success'] = 1;
					} else {
						$data['success'] = 0;
					}
					return json_encode($data);
				}
			}
		
			
		}
		include $this -> template('order');

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
			$data['ptime'] = TIMESTAMP;
		}
		if ($params['result'] == 'success') {
			$data['ptime'] = TIMESTAMP;
			//生成抽奖码cjm和邀请码yqm
			$cjm = '';
			$yqm = '';
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			for ($i = 0; $i < 4; $i++) {
				$cjm .= $chars[mt_rand(0, strlen($chars) - 1)];
			}
			for ($i = 0; $i < 4; $i++) {
				$yqm .= $chars[mt_rand(0, strlen($chars) - 1)];
			}
		}

		$order = pdo_fetch("SELECT * FROM " . tablename('rebate_orders') . " WHERE ordersn='{$params['tid']}'");
		$goods = pdo_fetch("SELECT * FROM " . tablename('rebate_goods') . " WHERE id='{$order['goodsid']}'");
		if ($order['status'] != 1) {
			pdo_update('rebate_orders', $data, array('ordersn' => $params['tid']));
			pdo_update('rebate_goods', array('num' => $goods['num'] + 1), array('id' => $order['goodsid']));
		}
		if ($params['from'] == 'return') {
			$dat = array('yqm' => $yqm, 'cjm' => $cjm, 'goodsn' => $order['goodsn'], 'openid' => $order['openid'], 'goodsid' => $order['goodsid'], 'uniacid' => $_W['uniacid'], 'ordersn' => $order['ordersn'], 'cjmfromopenid' => $_W['openid'], 'createtime' => TIMESTAMP);
			pdo_insert('rebate_record', $dat);
			$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
			$credit = $setting['creditbehaviors']['currency'];
			if ($params['type'] == $credit) {
				header("location: " . $this -> createMobileUrl('index', array('first' => 'yes')));
			} else {
				header("location: ../../app/" . $this -> createMobileUrl('index', array('first' => 'yes')));
			}
		}

	}

}
