<?php
	$mrsid = $_GPC['mrsid'];
	$date =date('m-d', time());
	$date2=date('m-d',strtotime('+1 day'));
	$date3=date('m-d',strtotime('+2 day'));
	$date4=date('m-d',strtotime('+3 day'));
	$ga = date("w"); 
	$goodsid = $_GPC['goodsid'];
	$addressid = $_GPC['addressid'];
	$datefive = array();
	$week = array();
	$weekday = array();
	$datefive = array(
		'0'=>$date,
		'1'=>$date2,
		'2'=>$date3,
		'3'=>$date4
	);
	$five = array(
		'0'=>'x',
		'1'=>'x',
		'2'=>'x',
		'3'=>'x'
	);
	$week[0] = "星期";
	$week[1] = "星期一";
	$week[2] = "星期二";
	$week[3] = "星期三";
	$week[4] = "星期四";
	$week[5] = "星期五";
	$week[6] = "星期六";
	$week[7] = "星期日";
	foreach($week as $k=>$v){
		if($k == $ga){
			$aa = $ga;
			foreach($five as $key=>$value){
				$weekday[$key] = $week[$aa];
				$aa = $aa+1;
				if($aa>7){
					$aa = 1;
				}
			}
		}
	}
//	echo "<pre>";
//	print_r($weekday);exit;
	if (checksubmit('submit')) {
		
		$year=date("Y"); 
		$time = $_GPC['time'];
		$riqi = $_GPC['riqi'];
		$hh = $_GPC['hh'];
		if(!$time || !$hh){
			message("请选择预约时间");exit;
		}
		$riqi2 = substr($riqi,0,5);
		$riqi3 = $year."-".$riqi2." ".$hh;
		$riqi4 = strtotime($riqi3);
//		message($riqi4);exit;
		header("location: " .  $this->createMobileUrl('orderconfirm', array('addressid' => $addressid,'date'=>$riqi4,'goodsid'=>$goodsid,'mrsid'=>$mrsid)));
		exit;
	}
	include $this -> template('pic_date');
?>
