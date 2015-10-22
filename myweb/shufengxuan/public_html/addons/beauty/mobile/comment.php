<?php
	global $_W,$_GPC;
	$orderid=$_GPC['orderid'];
	$mrsid = $_GPC['mrsid'];
	$goodsid = $_GPC['id'];
			$order = pdo_fetch("select * from".tablename('beauty_orders')."where uniacid = {$_W['uniacid']} and id={$orderid}");
			$goods = pdo_fetch("select * from".tablename('beauty_goods')."where uniacid = {$_W['uniacid']} and id={$goodsid}");
			$mrs = pdo_fetch("select * from".tablename('beauty_beauticians')."where uniacid = {$_W['uniacid']} and id={$mrsid}");
			$order['mrsname'] = $mrs['name'];
			$order['mrsthumb'] = $mrs['thumb'];
			$order['mrsid'] = $mrs['id'];
			$order['goodsid'] = $goods['id'];
			$order['goodsthumb'] = $goods['thumb'];
			$order['goodsname'] = $goods['gname'];
			$order['goodsprice'] = $order['price'];
			$order['goodsdetail'] = $goods['gdesc'];
			$order['order_time'] = $order['order_time'];
			$comment = $goods['comment'];
			$comment_num = $mrs['comment_num'];
			$now = time();
			if($order['order_time']-$now<=0){
				$order['orderstatus'] = "失效订单";
			}else{
				$order['orderstatus'] = "预约中";
			}
	if (checksubmit('submit')) {
	$remark = $_GPC['remark'];
	$radio = $_GPC['radio'];
	if($radio == 1){
		$comment = $comment+1;
		$comment_num = $comment_num+1;
	}
	pdo_update('beauty_orders',array('comment'=>1,'status'=>3),array('id'=>$orderid));
	pdo_update('beauty_goods',array('comment'=>$comment),array('id'=>$goods['id']));
	pdo_update('beauty_beauticians',array('comment_num'=>$comment_num),array('id'=>$mrsid));
	$data=array(
		'gid'=>$goods['id'],
		'bid'=>$mrs['id'],
		'level'=>$radio,
		'openid'=>$_W['openid'],
		'content'=>$remark,
		'createtime'=>TIMESTAMP,
		'uniacid'=>$_W['uniacid']
	);
	pdo_insert('beauty_comment',$data);
	echo "<script>alert(' 评论成功!');location.href='".$this->createMobileUrl('person_detail', array('status' => 100))."';</script>";
		exit;
	}
 	include $this->template('comment');
?>