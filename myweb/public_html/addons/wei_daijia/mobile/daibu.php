<?php
global $_GPC, $_W;
$p = pdo_fetch("select * from".tablename('daijia_price')."where status = 1 and type = 1");
if (checksubmit('daibu')) {
	$submit = 'daibu';
	$add1 = $_GPC['ad1'];
	$address = '乌鲁木齐市';//指定地方
	$province = $_GPC['province'];
	$city = $_GPC['city'];
	$district= $_GPC['district'];
	$street= $_GPC['street'];
	$add2 = $_GPC['ad2'];
	$mobile = $_GPC['mobile'];
	load() -> func('communication');
	$url_origins_ip = "http://restapi.amap.com/v3/geocode/geo?address=".$add1."&output=json&key=7d95f0c307d9bcc6c5c1817e1c087d71";
	$re_origins_ip = ihttp_request($url_origins_ip);
	$content_origins_ip = json_decode($re_origins_ip['content'], true);
	$origins_ip = $content_origins_ip['geocodes'][0]['location'];
	
	$url_destination_ip = "http://restapi.amap.com/v3/geocode/geo?address=".$add2."&output=json&key=7d95f0c307d9bcc6c5c1817e1c087d71";
	$re_destination_ip = ihttp_request($url_destination_ip);
	$content_destination_ip = json_decode($re_destination_ip['content'], true);
	$destination_ip = $content_destination_ip['geocodes'][0]['location'];
	
//	echo "<pre>";print_r($content_origins_ip['geocodes'][0]['location']);exit;
	$url = "http://restapi.amap.com/v3/distance?origins=".$origins_ip."&destination=".$destination_ip."&output=json&key=7d95f0c307d9bcc6c5c1817e1c087d71";
	$re = ihttp_request($url);
	
	if ($re['status'] == 'OK') {
		$content = json_decode($re['content'], true);
		$data = array('m' => $content['results'][0]['distance'], //多少米
		'seconds' => 0, //驾车多少秒
		);
	}
	if($data['m'] == 0){
		message('找不到目的地，请重新输入');
		include $this -> template('index');
	}
	header("location: " . $this -> createMobileUrl('orderconfirm', array('add1' => $add1, 'add2' => $add2, 'km' => ($data['m'] / 1000), 'hours' => ($data['seconds']/3600), 'mobile' => $mobile, 'sub' => 'daibu')));

}
include $this -> template('daibu');
?>