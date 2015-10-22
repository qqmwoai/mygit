<?php
/**
 * 美容模块处理程序
 *
 * @author 漆乾明
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class BeautyModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
	}
}