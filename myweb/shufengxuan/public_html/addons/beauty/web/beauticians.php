<?php
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	$address = pdo_fetchall("SELECT * FROM " . tablename('beauty_address') . " WHERE uniacid = '{$_W['uniacid']}' and status=1");
	//美容师展示
	if($op == 'display'){
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$condition = " uniacid = '{$_W['uniacid']}'";
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND name LIKE '%{$_GPC['keyword']}%'";
		}
		$beauticians = pdo_fetchall("SELECT * FROM ".tablename('beauty_beauticians')." WHERE $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		foreach($beauticians as $key=>$value){
			$address2 = pdo_fetch("SELECT * FROM " . tablename('beauty_address') . " WHERE uniacid = '{$_W['uniacid']}' and status=1 and id={$value['addressid']}");
			$beauticians[$key]['addressname']=$address2['name'];
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('beauty_beauticians') . "WHERE $condition "); //记录总数
		$pager = pagination($total, $pindex, $psize);
		include $this->template('beauticians');
	}
	//美容师编辑
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		$address = pdo_fetchall("SELECT * FROM " . tablename('beauty_address') . " WHERE uniacid = '{$_W['uniacid']}' and status=1");
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('beauty_beauticians').' WHERE id=:id ';
			$paramse = array(':id'=>$id);
			$beautician = pdo_fetch($sql, $paramse);
			if(empty($beautician)){
				message('未找到指定的美容师.', $this->createWebUrl('beauticians'));
			}
		}
		if (checksubmit()) {
			$data = $_GPC['beautician']; // 获取打包值
			empty($data['name']) && message('请填写美容师名称');
			empty($data['thumb']) && message('请上传头像');
			empty($data['desc']) && message('请填写美容师简介');
			$data['desc'] = htmlspecialchars_decode($data['desc']);
            if(empty($beautician)){
				$data['uniacid'] = $weid;
				$data['createtime'] = TIMESTAMP;
				$ret = pdo_insert('beauty_beauticians', $data);
				if (!empty($ret)) {
					$id = pdo_insertid();
				}
			} else {
				$ret = pdo_update('beauty_beauticians', $data, array('id'=>$id));
			}
			message('美容师信息保存成功', $this->createWebUrl('beauticians'), 'success');
		}
		include $this->template('beauticians_add');
	}
	//删除美容师
	if($op == 'delete') {
		$id = intval($_GPC['id']); //要删除的美容师的id
		if(empty($id)){
			message('未找到指定美容师');
		}
		$result = pdo_delete('beauty_beauticians', array('id'=>$id, 'uniacid'=>$weid));
		if(intval($result) == 1){
			message('删除美容师成功.', $this->createWebUrl('beauticians'), 'success');
		} else {
			message('删除美容师失败.');
		}
	}
?>