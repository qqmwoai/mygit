<?php
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php', $this -> pluginname);
$agentlevels = $this -> model -> getLevels();
$operation = empty($_GPC['op'])? 'display' : $_GPC['op'];
if ($operation == 'display') {
	ca('commission.agent.view');
	$level = $this -> set['level'];
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$params = array();
	$condition = '';
	if (!empty($_GPC['mid'])) {
		$condition .= ' and dm.id=:mid';
		$params[':mid'] = intval($_GPC['mid']);
	} 
	if (!empty($_GPC['realname'])) {
		$_GPC['realname'] = trim($_GPC['realname']);
		$condition .= ' and ( dm.realname like :realname or dm.nickname like :realname or dm.mobile like :realname)';
		$params[':realname'] = "%{$_GPC['realname']}%";
	} 
	if ($_GPC['parentid'] == '0') {
		$condition .= ' and dm.agentid=0';
	} else if (!empty($_GPC['parentname'])) {
		$_GPC['parentname'] = trim($_GPC['parentname']);
		$condition .= ' and ( p.mobile like :parentname or p.nickname like :parentname or p.realname like :parentname)';
		$params[':parentname'] = "%{$_GPC['parentname']}%";
	} 
	if ($_GPC['followed'] != '') {
		if ($_GPC['followed'] == 2) {
			$condition .= ' and f.follow=0 and dm.uid<>0';
		} else {
			$condition .= ' and f.follow=' . intval($_GPC['followed']);
		} 
	} 
	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	} 
	if (!empty($_GPC['time'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']);
		if ($_GPC['searchtime'] == '1') {
			$condition .= " AND dm.agenttime >= :starttime AND dm.agenttime <= :endtime ";
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		} 
	} 
	if (!empty($_GPC['agentlevel'])) {
		$condition .= ' and dm.agentlevel=' . intval($_GPC['agentlevel']);
	} 
	$list = pdo_fetchall("select dm.*,dm.nickname,dm.avatar,l.levelname,p.nickname as parentname,p.avatar as parentavatar from " . tablename('ewei_shop_member') . " dm " . " left join " . tablename('ewei_shop_member') . " p on p.id = dm.agentid " . " left join " . tablename('ewei_shop_commission_level') . " l on l.id = dm.agentlevel" . " left join " . tablename('mc_mapping_fans') . "f on f.openid=dm.openid and f.uniacid={$_W['uniacid']}" . " where dm.uniacid = " . $_W['uniacid'] . " and dm.isagent =1  {$condition} ORDER BY dm.agenttime desc limit " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn("select count(dm.id) from" . tablename('ewei_shop_member') . " dm  " . " left join " . tablename('ewei_shop_member') . " p on p.id = dm.agentid " . " left join " . tablename('mc_mapping_fans') . "f on f.openid=dm.openid" . " where dm.uniacid =" . $_W['uniacid'] . " and dm.isagent =1 {$condition}", $params);
	foreach ($list as &$row) {
		$info = $this -> model -> getInfo($row['openid'], array('total', 'pay'));
		$row['levelcount'] = $info['agentcount'];
		if ($level >= 1) {
			$row['level1'] = $info['level1'];
		} 
		if ($level >= 2) {
			$row['level2'] = $info['level2'];
		} 
		if ($level >= 3) {
			$row['level3'] = $info['level3'];
		} 
		$row['credit1'] = m('member') -> getCredit($row['openid'], 'credit1');
		$row['credit2'] = m('member') -> getCredit($row['openid'], 'credit2');
		$row['commission_total'] = $info['commission_total'];
		$row['commission_pay'] = $info['commission_pay'];
		$row['followed'] = m('user') -> followed($row['openid']);
	} 
	unset($row);
	$pager = pagination($total, $pindex, $psize);
} else if ($operation == 'detail') {
	ca('commission.agent.view');
	$id = intval($_GPC['id']);
	$member = $this -> model -> getInfo($id, array('total', 'pay'));
	if (checksubmit('submit')) {
		ca('commission.agent.edit|commission.agent.check');
		$data = is_array($_GPC['data'])? $_GPC['data'] : array();
		if (empty($_GPC['oldstatus']) && $data['status'] == 1) {
			$time = time();
			$data['agenttime'] = time();
			$this -> model -> sendMessage($member['openid'], array('nickname' => $member['nickname'], 'agenttime' => $time), TM_COMMISSION_BECOME);
			plog('commission.agent.check', "审核分销商 <br/>分销商信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
		} 
		plog('commission.agent.edit', "修改分销商 <br/>分销商信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
		pdo_update('ewei_shop_member', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
		message('保存成功!', $this -> createPluginWebUrl('commission/agent'), 'success');
	} 
} else if ($operation == 'delete') {
	ca('commission.agent.delete');
	$id = intval($_GPC['id']);
	$member = pdo_fetch("select * from " . tablename('ewei_shop_member') . " where uniacid=:uniacid and id=:id limit 1 ", array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if (empty($member)) {
		message('会员不存在，无法删除!', $this -> createPluginWebUrl('commission/agent'), 'error');
	} 
	$agentcount = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member') . ' where  uniacid=:uniacid and agentid=:agentid limit 1 ', array(':uniacid' => $_W['uniacid'], ':agentid' => $id));
	if ($agentcount > 0) {
		message('此会员有下线存在，无法删除!', '', 'error');
	} 
	pdo_delete('ewei_shop_member', array('id' => $_GPC['id']));
	plog('commission.agent.delete', "删除分销商 <br/>分销商信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
	message('删除成功！', $this -> createPluginWebUrl('commission/agent'), 'success');
} else if ($operation == 'user') {
	ca('commission.agent.user');
	$level = intval($_GPC['level']);
	$agentid = intval($_GPC['id']);
	$member = $this -> model -> getInfo($agentid);
	$total = $member['agentcount'];
	$level1 = $member['level1'];
	$level2 = $member['level2'];
	$level3 = $member['level3'];
	$condition = '';
	$params = array();
	if (empty($level)) {
		$condition = " and ( dm.agentid={$member['id']}";
		if ($level1 > 0) {
			$condition .= " or  dm.agentid in( " . implode(',', array_keys($member['level1_agentids'])) . ")";
		} 
		if ($level2 > 0) {
			$condition .= " or  dm.agentid in( " . implode(',', array_keys($member['level2_agentids'])) . ")";
		} 
		$condition .= ' )';
		$hasagent = true;
	} else if ($level == 1) {
		if ($level1 > 0) {
			$condition = " and dm.agentid={$member['id']}";
			$hasagent = true;
		} 
	} else if ($level == 2) {
		if ($level2 > 0) {
			$condition = " and dm.agentid in( " . implode(',', array_keys($member['level1_agentids'])) . ")";
			$hasagent = true;
		} 
	} else if ($level == 3) {
		if ($level3 > 0) {
			$condition = " and dm.agentid in( " . implode(',', array_keys($member['level2_agentids'])) . ")";
			$hasagent = true;
		} 
	} 
	if (!empty($_GPC['mid'])) {
		$condition .= ' and dm.id=:mid';
		$params[':mid'] = intval($_GPC['mid']);
	} 
	if (!empty($_GPC['realname'])) {
		$_GPC['realname'] = trim($_GPC['realname']);
		$condition .= ' and ( dm.realname like :realname or dm.nickname like :realname or dm.mobile like :realname)';
		$params[':realname'] = "%{$_GPC['realname']}%";
	} 
	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	} 
	if (!empty($_GPC['agentlevel'])) {
		$condition .= ' and dm.agentlevel=' . intval($_GPC['agentlevel']);
	} 
	if ($_GPC['parentid'] == '0') {
		$condition .= ' and dm.agentid=0';
	} else if (!empty($_GPC['parentname'])) {
		$_GPC['parentname'] = trim($_GPC['parentname']);
		$condition .= ' and ( p.mobile like :parentname or p.nickname like :parentname or p.realname like :parentname)';
		$params[':parentname'] = "%{$_GPC['parentname']}%";
	} 
	if ($_GPC['followed'] != '') {
		if ($_GPC['followed'] == 2) {
			$condition .= ' and f.follow=0 and dm.uid<>0';
		} else {
			$condition .= ' and f.follow=' . intval($_GPC['followed']);
		} 
	} 
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = array();
	if ($hasagent) {
		$total = pdo_fetchcolumn("select count(dm.id) from" . tablename('ewei_shop_member') . " dm " . " left join " . tablename('ewei_shop_member') . " p on p.id = dm.agentid " . " left join " . tablename('mc_mapping_fans') . "f on f.openid=dm.openid" . " where dm.uniacid =" . $_W['uniacid'] . " and dm.isagent =1 and dm.status=1 {$condition}", $params);
		$list = pdo_fetchall("select dm.*,p.nickname as parentname,p.avatar as parentavatar  from " . tablename('ewei_shop_member') . " dm " . " left join " . tablename('ewei_shop_member') . " p on p.id = dm.agentid " . " left join " . tablename('mc_mapping_fans') . "f on f.openid=dm.openid  and f.uniacid={$_W['uniacid']}" . " where dm.uniacid = " . $_W['uniacid'] . " and dm.isagent =1 and dm.status=1 {$condition}   ORDER BY dm.agenttime desc limit " . ($pindex - 1) * $psize . ',' . $psize, $params);
		$pager = pagination($total, $pindex, $psize);
		foreach ($list as &$row) {
			$info = $this -> model -> getInfo($row['openid'], array('total', 'pay'));
			$row['levelcount'] = $info['agentcount'];
			if ($this -> set['level'] >= 1) {
				$row['level1'] = $info['level1'];
			} 
			if ($this -> set['level'] >= 2) {
				$row['level2'] = $info['level2'];
			} 
			if ($this -> set['level'] >= 3) {
				$row['level3'] = $info['level3'];
			} 
			$row['credit1'] = m('member') -> getCredit($row['openid'], 'credit1');
			$row['credit2'] = m('member') -> getCredit($row['openid'], 'credit2');
			$row['commission_total'] = $info['commission_total'];
			$row['commission_pay'] = $info['commission_pay'];
			$row['followed'] = m('user') -> followed($row['openid']);
			if ($row['agentid'] == $member['id']) {
				$row['level'] = 1;
			} else if (in_array($row['agentid'], array_keys($member['level1_agentids']))) {
				$row['level'] = 2;
			} else if (in_array($row['agentid'], array_keys($member['level2_agentids']))) {
				$row['level'] = 3;
			} 
		} 
	} 
	unset($row);
	load() -> func('tpl');
	include $this -> template('agent_user');
	exit;
} 
load() -> func('tpl');
include $this -> template('agent');
