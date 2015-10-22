<?php
global $_GPC, $_W;
$p = pdo_fetch("select * from".tablename('daijia_price')."where status = 1 and type = 0");
if (checksubmit('daijia')) {
	$city = $_GPC['city'];
	$add1 = $_GPC['ad1'];
	$add2 = $_GPC['ad2'];
	$province = $_GPC['province'];
	$city = $_GPC['city'];
	$district = $_GPC['district'];
	$street = $_GPC['street'];
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
	header("location: " . $this -> createMobileUrl('orderconfirm', array('add1' => $add1, 'add2' => $add2, 'km' => ($data['m'] / 1000), 'second' => $data['seconds'], 'mobile' => $mobile, 'sub' => 'daijia','url_origins_ip'=>$origins_ip,'url_destination_ip'=>$destination_ip)));

}
load() -> func('communication');
$u = "http://restapi.amap.com/v3/staticmap?location=116.481485,39.990464&zoom=10&size=750*300&markers=mid,,A:116.481485,39.990464&key=7d95f0c307d9bcc6c5c1817e1c087d71";
$r = ihttp_request($u);

include $this -> template('daijia');
?>