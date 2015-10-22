<?php
global $_GPC, $_W;
$operation = $_GPC['op'];
if (checksubmit('daijia')) {
	$add1 = $_GPC['ad1'];
	$add2 = $_GPC['ad2'];
	$province = $_GPC['province'];
	$city = $_GPC['city'];
	$district = $_GPC['district'];
	$street = $_GPC['street'];
	$mobile = $_GPC['mobile'];
	load() -> func('communication');
	$url = "http://api.map.baidu.com/direction/v1/routematrix?output=json&origins=" . $add1 . "&destinations=" . $add2 . "&ak=steGNL2uu8ChNvz83MA3Gita";
	$re = ihttp_request($url);
	echo "<pre>";print_r($re);exit;
	if ($re['status'] == 'OK') {
		$content = json_decode($re['content'], true);
		$data = array('m' => $content['result']['elements'][0]['distance']['value'], //多少米
		'seconds' => $content['result']['elements'][0]['duration']['value'], //驾车多少秒
		);
	}
	if($data['m'] == 0){
		message('找不到目的地，请重新输入');
		include $this -> template('index');
	}
	header("location: " . $this -> createMobileUrl('orderconfirm', array('add1' => $add1, 'add2' => $add2, 'km' => ($data['m'] / 1000), 'second' => $data['seconds'], 'mobile' => $mobile, 'sub' => 'daijia')));

}
include $this -> template('daijia');
?>