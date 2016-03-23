<?php 
/**
 * 此文件用于cli模式运行
 * 
 * 基于redis的任务队列，类似于python celery，可用rabbitMQ代替redis消费更可靠。
 * redis有list结构，它也有zset有序集合应为source的存在，使zset有了无限可能
 * liukelin 
 **/
defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR); 
include_once(__URL__.'config.php');
global $config;
$config = $conf;

class celery{
	
	private $queue_key = 'celery_startup_zset';
	
	public function redis_conn(){
		global $config;
		global $redis;
		$r = $config['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
	}
	
	/**
	 * 将执行任务写入队列
	 * @param unknown $queue 方法名
	 * @param unknown $args	 参数
	 * @return boolean
	 */	
	public function apply_async($queue, $args=array()){
		global $config;
		
		if(empty($queue)){
			return false;
		}
		$key = microtime(true)*10000;
		$arr = array(
			'fun'=>$queue,
			'args'=>$args,
			'key'=>$key,
		);
		$data = json_encode($arr);

		# push redis zset
		$r = $config['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
		$redis->zadd($this->queue_key , $key, $data);
		return true;
	}
	
	/**
	 * 消费队列数据
	 */
	public function digestion_queue_data(){
		global $config;
		
		$r = $config['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
		
// 		while (1){
			try {
				$redis->ping();
			}catch(Exception $e){		
				$r = $config['redis'];
				$redis = new Redis();
				$redis->connect($r['host'],$r['port'],$r['db']);
			}
			
			$redis->multi();
// 			$redis->watch($this->queue_key);
// 			$json = $redis->zRange($this->queue_key, 0, 0);
// 			$redis->zrem($this->queue_key, $json[0]);
// 			$redis->exec();
			$json = $redis->lPop($this->queue_key);
			
			var_dump($json);
			
// 			exit;
			
			$data = json_decode($json[0],true);
			$json = null;
// 			print_r($data);
			
			//执行
			$ret = $this->call_func($data['fun'], $data['args']);
			if (!$ret) {
				#消费失败 数据回归队列（头部、尾部）
				$redis->zadd($this->queue_key , $data['key'], $json);
			}
			$data = null;
// 		}
	}
	
	/**
	 * 执行方法
	 * @param unknown $queue 方法名
	 * @param unknown $args  方法参数
	 * @return mixed|boolean 
	 */
	public function call_func($queue, $args=array()){		
		try {
			return call_user_func_array($queue, $args);
		}catch(Exception $e){
			return false;
		}
	}
}

function test($a,$b){
	$myfile = fopen("testfile.txt", "a+");
	fwrite($myfile, $a.'=='.$b);
	fclose($myfile);
}

//run 
//开启进程数量
$processNo = 5;

$on_out = system('ps -ef | grep "startup.php" | grep -v "grep" | wc -l',$out);
if (intval(trim($on_out)) > ($processNo+1)){
	exit('error on start!');
}

// phpinfo();

$st = new celery();
$st->digestion_queue_data();

