<?php
	global $_W,$_GPC;
	$goodsid = $_GPC['id'];
	$mrsid = $_GPC['mrsid'];
	if($goodsid){
		//服务项
		$goods = pdo_fetch("SELECT * FROM " . tablename('beauty_goods') . " WHERE uniacid = {$_W['uniacid']} and id={$goodsid}");
		//评论
		$comments = pdo_fetchall("SELECT * FROM " . tablename('beauty_comment') . " WHERE uniacid = {$_W['uniacid']} and gid={$goodsid} limit 0,10");
		foreach($comments as$key =>$value){
			$userone = pdo_fetch("SELECT * FROM " . tablename('beauty_member') . "  WHERE uniacid = {$_W['uniacid']} and from_user ='{$value['openid']}'");
			$comments[$key]['thumb'] = $userone['avatar'];
  			$comments[$key]['nickname'] = $userone['nickname'];
		}
//		echo "<pre>";
//		print_r($comments);exit;
		if(empty($mrsid)){
			//默认美容师
		$beautician = pdo_fetch("SELECT * FROM " . tablename('beauty_beauticians') . " WHERE uniacid = '{$_W['uniacid']}' and id={$goods['service_beautician_id']}");
		$mrsid = $beautician['id'];
		}else{
		$beautician = pdo_fetch("SELECT * FROM " . tablename('beauty_beauticians') . " WHERE uniacid = '{$_W['uniacid']}' and id={$mrsid}");
		}
		
	}
	include $this -> template('service_detail');
?>
