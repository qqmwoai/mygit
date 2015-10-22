<?php
/*
Author: yyfzx <yyfzxfyd@gmail.com>
PHP实现Socket Server 
SOCKET 3种模型
1.阻塞/同步 模型 效率低下 不考虑使用
2.非阻塞/同步 模型 类似apache的select/poll 模型 无法承受高并发使用
3.非/阻塞/异步 多路复用模型 linux/unix下的 epoll/kqueue 类似nginx 可以高并发使用 理论上带个1.5W打印机不是问题
本类使用第三种模式。阻塞 和 非阻塞 自己设定。建议使用 非阻塞
需要用到第三方组件  libevent 请自行编译安装。

使用方法  php.exe php_socketserver_epoll.php
在WINDOWS下输出中文请用 GBK编码
*/
set_time_limit(0);
class EpollSocketServer
{
	private static $socket;
	private static $connections;
	private static $buffers;
	private static $imei;
	
	function EpollSocketServer ($port)
	{
		global $errno, $errstr;
		
		if (!extension_loaded('libevent')) {
			die("Please install libevent extension firstly/n");
		}
		
		if ($port < 1024) {
			die("Port must be a number which bigger than 1024/n");
		}
		
		$socket_server = stream_socket_server("tcp://0.0.0.0:{$port}", $errno, $errstr);
		if (!$socket_server) die("$errstr ($errno)");
		
		stream_set_blocking($socket_server, 0); // 非阻塞
		
		$base = event_base_new();
		$event = event_new();
		event_set($event, $socket_server, EV_READ | EV_PERSIST, array(__CLASS__, 'ev_accept'), $base);
		event_base_set($event, $base);
		event_add($event);
		event_base_loop($base);
		
		self::$connections = array();
		self::$buffers = array();
		self::$imei = array();
	}
	
	function ev_accept($socket, $flag, $base) 
	{
		static $id = 0;
	
		$connection = stream_socket_accept($socket);
		stream_set_blocking($connection, 0);
	
		$id++; // 连接队列
	
		$buffer = event_buffer_new($connection, array(__CLASS__, 'ev_read'), array(__CLASS__, 'ev_write'), array(__CLASS__, 'ev_error'), $id);
		event_buffer_base_set($buffer, $base);
		event_buffer_timeout_set($buffer, 30, 30);
		event_buffer_watermark_set($buffer, EV_READ, 0, 0xffffff);
		event_buffer_priority_set($buffer, 10);
		event_buffer_enable($buffer, EV_READ | EV_PERSIST);
	
		//维护所有连接队列
		self::$connections[$id] = $connection;
		self::$buffers[$id] = $buffer;
	}
	
	function ev_error($buffer, $error, $id) 
	{
		event_buffer_disable(self::$buffers[$id], EV_READ | EV_WRITE);
		event_buffer_free(self::$buffers[$id]);
		fclose(self::$connections[$id]);
		unset(self::$buffers[$id], self::$connections[$id],self::$imei[$id]);
	}
	
	function ev_read($buffer, $id) 
	{
		static $ct = 0;
		$ct_last = $ct;
		$ct_data = '';
		while ($read = event_buffer_read($buffer, 1024)) {
			$ct += strlen($read);
			$ct_data .= $read;
		}
		$datamsg = '收到数据来自ID:'.$id.' 内容:'.$ct_data;
		$datamsg = iconv('utf-8','gbk',$datamsg);
		echo $datamsg."\n";
		if ($ct_data == '00') {//收到来自打印机的心跳数据 给打印机下发数据
		
			$clientimei = self::$imei[$id];
			$data811 = '%30测试数据';
			$data811 .= '%%%00你的IMEI如下:';
			$data811 .= '%%'.$clientimei;
			$data811 .= '1234:###'.iconv('utf-8', 'gbk', $data811);
			$fblen = $this->jsdata($data811);
			$write = '1123456789'.$fblen.'81101   1'.$data811.'#';
			event_buffer_write($buffer, $write);
		}else{
			$type = substr($ct_data, 20,3);//各类方法
			if ($type == '810'){//拿810 取IMEI
				self::$imei[$id] = substr($ct_data, 23,15);
			}
		}
	}
	
	function ev_write($buffer, $id) 
	{
		echo "[$id] " . __METHOD__ . "/n";
	}
	
	public function jsdata(&$data){
		$len = strlen($data);
		$lenlen = strlen($len);
		if ($lenlen < 10) {
			$bx = 10-$lenlen;
			$bb = '';
			for ($i=0;$i<$bx;$i++){
				$bb .= ' ';
			}
		}
		$restr = $len.$bb;
		return $restr;
	}
}
new EpollSocketServer(8000);