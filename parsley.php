<?php 
/**
 * 基础方法文件
 * 
 * 基于redis的任务队列，类似于python celery，可用rabbitMQ代替redis消费更可靠。
 * redis有list结构，它也有zset有序集合应为source的存在，使zset有了无限可能
 * liukelin 
 **/
defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR); 
include_once(__URL__.'config.php');
include_once(__URL__.'redisQueue.php');
include_once(__URL__.'func.php');

global $conf;
$conf = $config;

class parsley{
	
	public function redis_conn(){
		global $conf;
		global $redis;
		$r = $conf['redis'];
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
		global $conf;
		
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
		$r = $conf['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
		$redis->zadd($conf['queue_key'] , $key, $data);
		return true;
	}
	
	/**
	 * 消费队列数据
	 */
	public function digestion_queue_data(){
		global $conf;
		
		$redis = new redisQueue();
		
		while (1){
			/**
			try {
				$redis->ping();
			}catch(Exception $e){		
				$r = $conf['redis'];
				$redis = new Redis();
				$redis->connect($r['host'],$r['port'],$r['db']);
			}**/
			try {
				$json = $redis->zlPop($conf['queue_key']);
			}catch (Exception $e){
				$redis = new redisQueue();
				$json = $redis->zlPop($conf['queue_key']);
			}
			
			if(!isset($json)){
				break;
			}
			
			$data = json_decode($json,true);
			if (empty($data['fun'])) {
				break;
			}
			
			//执行
			$ret = $this->call_func($data['fun'], $data['args']);
			if ($ret==false) {
				#消费失败 数据回归队列（头部、尾部）
				$redis->zadd($conf['queue_key'] , $data['key']+100000, $json);
			}
			
			$this->setLog(__URL__.$conf['logs'].'log.log',date('Y-m-d H:i:s').",执行:{$json},{$ret}");
			$json = null;
			$data = null;
		}
	}
	
	/**
	 * 执行方法
	 * @param unknown $queue 方法名
	 * @param unknown $args  方法参数
	 * @return mixed|boolean 
	 */
	public function call_func($queue, $args=array()){
		$ex = array();
		try {
			$ex = explode('.',$queue);
			if (count($ex)>1) {
				return call_user_func_array(array($ex[0],$ex[1]), $args);
			}
			return call_user_func_array($queue, $args);
		}catch(Exception $e){
			return false;
		}
	}
	
	public function setLog($file,$msg){
		$myfile = @fopen($file, "a+");
		@fwrite($myfile, $msg."\r\n");
		@fclose($myfile);
	}
}





