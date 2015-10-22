<?php
			global $_GPC, $_W;
			$orderno = $_GPC['orderno'];
			$data = $_GPC['data'];
			$res = pdo_update('tg_order', $data, array('orderno' => $orderno));
			echo "<script>location.href='".$_W['siteroot'].'app/'.$this->createMobileUrl('index',array('result'=>'success'))."';</script>";
			exit;
?>