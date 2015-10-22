<?php
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	$type = $_GPC['type'];
	if($op == 'display'){
		if(empty($type)){
			$type = 0;
		}
		$pindex = max(1, intval($_GPC['page'])); //当前页码
		$psize = 10;	//设置分页大小 
		$prices = pdo_fetchall("SELECT * FROM ".tablename('daijia_price')." WHERE uniacid = '{$_W['uniacid']}' and type = {$type} ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('daijia_price') . "WHERE uniacid = '{$_W['uniacid']}' and type={$type}"); //记录总数
		$pager = pagination($total, $pindex, $psize);
		if($type==2){
			$price = pdo_fetch("SELECT * FROM ".tablename('daijia_price')." WHERE uniacid = '{$_W['uniacid']}' and type = {$type} and status = 1");
			include $this->template('dailaoprice');
		}else{
			include $this->template('price');
		}
		
	}
	//商品编辑
	$dailao = $_GPC['dailao'];
	if ($op == 'edit') {
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('daijia_price').' WHERE id=:id ';
			$params = array(':id'=>$id);
			$price = pdo_fetch($sql, $params);
		
		}
		if (checksubmit()) {
			$data = array();
			$data['type'] = $_GPC['type'];
			$data['start_price'] = $_GPC['start_price'];
			$data['start_km'] = $_GPC['start_km'];
			$data['km_level'] = $_GPC['km_level'];
			$data['km_price'] = $_GPC['km_price'];	
			$data['status'] = $_GPC['status'];
			$data['mobile'] = $_GPC['mobile'];
			$data['pricetype1'] = $_GPC['pricetype1'];
			$data['pricetype2'] = $_GPC['pricetype2'];
			$data['pricetype3'] = $_GPC['pricetype3'];
			$data['pricetype4'] = $_GPC['pricetype4'];
			$data['pricetype5'] = $_GPC['pricetype5'];
			$data['pricetype6'] = $_GPC['pricetype6'];
			$data['pricetype7'] = $_GPC['pricetype7'];
			$data['pricetype8'] = $_GPC['pricetype8']; 
			$data['pricetype9'] = $_GPC['pricetype9'];
			
            if(empty($id)){
            	if($data['status']==1){
            		if($dailao == 'dailao'){
            			pdo_delete('daijia_price',array('type'=>2,'status'=>1));
            		}
            	}
				$data['uniacid'] = $_W['uniacid'];
				$data['createtime'] = TIMESTAMP;
				pdo_insert('daijia_price', $data);
				message('价格添加成功', $this->createWebUrl('price'), 'success');
			}else{
				pdo_update('daijia_price', $data,array('id'=>$id));
				message('价格信息保存成功', $this->createWebUrl('price'), 'success');
			}
			
		}
		if($dailao == 'dailao'){
			include $this->template('dailao_priceadd');
		}else{
			include $this->template('priceadd');
		}
		
	}
	
	if($op == 'delete') {
		$id = intval($_GPC['id']); //要删除的商品的id
		if(empty($id)){
			message('未找到指定商品分类');
		}
		$result = pdo_delete('daijia_price', array('id'=>$id, 'uniacid'=>$_W['uniacid']));
		if(intval($result) == 1){
			message('删除价格成功.', $this->createWebUrl('price'), 'success');
		} else {
			message('删除价格失败.');
		}
	}
?>
