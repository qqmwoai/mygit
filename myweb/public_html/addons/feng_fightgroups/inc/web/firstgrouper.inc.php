<?php
		global $_W,$_GPC;
		load()->func('tpl');
		checklogin();
		
		$this -> backlists();
		$this->checkmode();
		$refundpercent = $this->module['config']['refundpercent'];
		/*退款团长列表*/
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		/*所有团*/
		$status = $_GPC['status'];
		$percent = $_GPC['percent'];
		if(empty($percent)){
				if($refundpercent){
					$percent = $refundpercent;
				}else{
					$percent=50;
				}
		}
		$yes=0;//已退款的订单数
		$no=0;//未退款的订单数
		$all_groups = pdo_fetchall("select * from".tablename('tg_group')."where uniacid='{$_W['uniacid']}' and groupstatus=2 ORDER BY starttime ASC ". "LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		foreach($all_groups as$key=> $value){
			if(empty($status)){
				
				$first_grouper = pdo_fetch("select * from".tablename('tg_order')."where tuan_id='{$value['groupnumber']}' and uniacid='{$_W['uniacid']}' and tuan_first=1 and status in(1,2,3,4)");
				if(!empty($first_grouper)){
					$no+=1;
				}
			}else{
				
				$first_grouper = pdo_fetch("select * from".tablename('tg_order')."where tuan_id='{$value['groupnumber']}' and uniacid='{$_W['uniacid']}' and tuan_first=1 and status=7 ");		
				if(!empty($first_grouper)){
					$yes+=1;
				}
			}
			
			/*退款金额*/
			if(empty($percent)){
				if($refundpercent){
					$percent = $refundpercent;
					$refund = $first_grouper['price']*$refundpercent;
				}else{
					$percent=50;
					$refund = $first_grouper['price']*0.5;
				}
			}else{
				$refund = $first_grouper['price']*$percent*0.01;
			}
			$all_groups[$key]['groupnumber'] = $value['groupnumber'];
			$all_groups[$key]['orderid'] = $first_grouper['id'];
			$all_groups[$key]['addname'] = $first_grouper['addname'];
			$all_groups[$key]['price'] = $first_grouper['price'];
			$all_groups[$key]['refund'] = $refund;
		}

		/*退款团长列表*/
		/*处理退款*/
		$refund_grouper = $_GPC['refund_grouper'];
		if(!empty($refund_grouper)){
				$checkboxs = $_GPC['checkbox'];
				$percent = $_GPC['percent'];
				$success_num =0;
				$fail_num =0;
				foreach($checkboxs as$k=>$value){
					$refund_ids = pdo_fetch("select * from".tablename('tg_order')."where id='{$value}'");
					$fee = $refund_ids['price']*100*$percent*0.01;//退款金额
					if($fee<1){
						$fee=1;
					}
					$res = $this->refund($refund_ids['orderno'],$fee,4);
					if($res == 'success'){
						$success_num+=1;
					}else{
						$fail_num+=1;
					}
				}
				message('团长部分退款操作成功，成功！'.$success_num.'人,失败'.$fail_num.'人', referer(), 'success');
		}
		/*处理退款*/
		if(empty($status)){
			$total=$no;
		}else{
			$total=$yes;
		}
		$pager = pagination($total, $pindex, $psize);
		include $this->template('web/firstgrouper');
?>