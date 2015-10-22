<?php
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	//地址展示
	if($op == 'display'){
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$condition = " uniacid = '{$_W['uniacid']}'";
//		if (!empty($_GPC['pay_type'])) {
//			$condition .= " AND fk_typeid = '{$_GPC['pay_type']}'";
//		} 
	$address = pdo_fetchall("SELECT * FROM " . tablename('beauty_address') . " WHERE uniacid = '{$_W['uniacid']}' and status =1");
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND name LIKE '%{$_GPC['keyword']}%'";
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('beauty_address') . "WHERE $condition "); //记录总数
		$pager = pagination($total, $pindex, $psize);
		include $this->template('address');
	}
	//地址编辑
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('beauty_address').' WHERE id=:id ';
			$paramse = array(':id'=>$id);
			$address = pdo_fetch($sql, $paramse);
			//获取当前图集
			if(empty($address)){
				message('未找到指定的地址.', $this->createWebUrl('address'));
			}
		}
		if (checksubmit()) {
			$data = $_GPC['address']; // 获取打包值
			empty($data['name']) && message('请填写地址名称');
			empty($data['detail']) && message('请填写详细地址');
            if(empty($address)){
				$data['uniacid'] = $weid;
				$data['createtime'] = TIMESTAMP;
				$ret = pdo_insert('beauty_address', $data);
			} else {
				$ret = pdo_update('beauty_address', $data, array('id'=>$id));
			}
			message('地址信息保存成功', $this->createWebUrl('address'), 'success');
		}
		include $this->template('address_add');
	}
	//删除地址
	if($op == 'delete') {
		$id = intval($_GPC['id']); //要删除的地址的id
		if(empty($id)){
			message('未找到指定地址分类');
		}
		$result = pdo_delete('beauty_address', array('id'=>$id, 'uniacid'=>$weid));
		if(intval($result) == 1){
			message('删除地址成功.', $this->createWebUrl('address'), 'success');
		} else {
			message('删除地址失败.');
		}
	}
?>