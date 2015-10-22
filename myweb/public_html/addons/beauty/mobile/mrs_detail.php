<?php
	$mrs_id = $_GPC['mrs_id'];
	$mrs=pdo_fetch("select * from".tablename('beauty_beauticians')."where id={$mrs_id}");
	$morengoods = pdo_fetchall("select * from".tablename('beauty_goods')."where service_beautician_id ={$mrs['id']}");
	
	$goodsid = pdo_fetchall("select * from".tablename('beauty_goodsparams')."where mrsid ={$mrs['id']}");
	$type_face = array();
	$type_body = array();
	$type_yangshen = array();
	$type_zuhe = array();
//	foreach($morengoods as $k=>$v){
//		
//	}
	foreach($goodsid as $key=>$value){
		$goods = pdo_fetch("select * from".tablename('beauty_goods')."where id ={$value['goodsid']}");
		if($goods['gtype']==1){
			$type_face[$key] = $goods;
		}
		if($goods['gtype']==2){
			$type_body[$key] = $goods;
		}
		if($goods['gtype']==3){
			$type_yangshen[$key] = $goods;
		}
		if($goods['gtype']==4){
			$type_zuhe[$key] = $goods;
		}
	}
	include $this -> template('mrs_detail');
?>
