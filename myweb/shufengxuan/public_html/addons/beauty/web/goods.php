<?php
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	//商品展示
	if($op == 'display'){
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$condition = " uniacid = '{$_W['uniacid']}'";
//		if (!empty($_GPC['pay_type'])) {
//			$condition .= " AND fk_typeid = '{$_GPC['pay_type']}'";
//		} 
	$category = pdo_fetchall("SELECT * FROM " . tablename('beauty_category') . " WHERE weid = '{$_W['uniacid']}' and enabled=1 ORDER BY displayorder DESC");
	$beauticians = pdo_fetchall("SELECT * FROM " . tablename('beauty_beauticians') . " WHERE uniacid = '{$_W['uniacid']}' and status=1");
		
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND gname LIKE '%{$_GPC['keyword']}%'";
		}
		$goodses = pdo_fetchall("SELECT * FROM ".tablename('beauty_goods')." WHERE $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		foreach($goodses as $key=>$value){
			$cate = pdo_fetch("SELECT * FROM " . tablename('beauty_category') . " WHERE weid = '{$_W['uniacid']}' and enabled=1 and id = {$value['gtype']}");
			$beautician = pdo_fetch("SELECT * FROM " . tablename('beauty_beauticians') . " WHERE uniacid = '{$_W['uniacid']}' and status=1 and id={$value['service_beautician_id']}");
			$goodses[$key]['beauticiansname']=$beautician['name'];
			$goodses[$key]['typename']=$cate['name'];
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('beauty_goods') . "WHERE $condition "); //记录总数
		$pager = pagination($total, $pindex, $psize);
		include $this->template('goods');
	}
	//商品编辑
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		$opp = $_GPC['opp'];
		if($opp=='params'){
			$paramsid = $_GPC['paramsid'];
			pdo_delete('beauty_goodsparams',array('id'=>$paramsid));
		}
		$category = pdo_fetchall("SELECT * FROM " . tablename('beauty_category') . " WHERE weid = '{$_W['uniacid']}' and enabled=1 ORDER BY displayorder DESC");
		$beauticians = pdo_fetchall("SELECT * FROM " . tablename('beauty_beauticians') . " WHERE uniacid = '{$_W['uniacid']}' and status=1");
		
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('beauty_goods').' WHERE id=:id ';
			$paramse = array(':id'=>$id);
			$goods = pdo_fetch($sql, $paramse);
			$params = pdo_fetchall("SELECT * FROM" . tablename('beauty_goodsparams') .  "WHERE goodsid = '{$id}' ");
			foreach($params as $key=>$value){
				$mrs = pdo_fetch("SELECT * FROM" . tablename('beauty_beauticians') .  "WHERE id = '{$value['mrsid']}' ");
				$params[$key]['name'] = $mrs['name'];
				$params[$key]['mrsid'] = $mrs['id'];
			}
			if(empty($goods)){
				message('未找到指定的商品.', $this->createWebUrl('goods'));
			}
		}
		if (checksubmit()) {
			$data = $_GPC['goods']; // 获取打包值
			empty($data['gname']) && message('请填写商品名称');
			empty($data['price']) && message('请填写商品团购价');
			empty($data['gthumb']) && message('请上传图片');
			empty($data['thumb']) && message('请上传图片');
			empty($data['gdesc']) && message('请填写商品简介');
			$data['gdesc'] = htmlspecialchars_decode($data['gdesc']);
            if(empty($goods)){
				$data['uniacid'] = $weid;
				$data['createtime'] = TIMESTAMP;
				$ret = pdo_insert('beauty_goods', $data);
				if (!empty($ret)) {
					$id = pdo_insertid();
				}
				$params = $_POST['param'];
				foreach($params as $key=>$value){
				$data2 = array(
					'goodsid'=>$id,
					'mrsid'=>$value
				);
				$ret = pdo_insert('beauty_goodsparams', $data2);
				}
				$ret = pdo_insert('beauty_goodsparams', array('goodsid'=>$id,'mrsid'=>$data['service_beautician_id']));
			}else{
				pdo_delete('beauty_goodsparams',array('goodsid'=>$id));
				$ret = pdo_update('beauty_goods', $data, array('id'=>$id));
				$params = $_GPC['param'];
				foreach($params as $key=>$value){
					$data2 = array(
						'goodsid'=>$id,
						'mrsid'=>$value
					);
				$ret = pdo_insert('beauty_goodsparams', $data2);
				}
				$ret = pdo_insert('beauty_goodsparams', array('goodsid'=>$id,'mrsid'=>$data['service_beautician_id']));
			message('商品信息保存成功', $this->createWebUrl('goods'), 'success');
			}
		}
		include $this->template('goodsadd');
		
	}
	//删除商品
	if($op == 'delete') {
		$id = intval($_GPC['id']); //要删除的商品的id
		if(empty($id)){
			message('未找到指定商品分类');
		}
		$result = pdo_delete('beauty_goods', array('id'=>$id, 'uniacid'=>$weid));
		if(intval($result) == 1){
			message('删除商品成功.', $this->createWebUrl('goods'), 'success');
		} else {
			message('删除商品失败.');
		}
	}
?>