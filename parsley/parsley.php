<?php 
/**
 * 基础方法文件
 * 
 * 
 * 基于redis的任务队列，类似于python celery，可用rabbitMQ代替redis消费更可靠。
 * redis有list结构，它也有zset有序集合应为source的存在，使zset有了无限可能
 * liukelin 
 **/
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'../config.php');
include_once(__URL__.'parsley/redisQueue.php');
include_once(__URL__.'func.php');

global $conf;
$conf = $config;

class parsley{
	
	/**
	 * 将执行任务写入队列
	 * @param unknown $queue func.php方法名
	 * @param unknown $args	 参数(顺序array)
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
		$redis = new redisQueue();
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
			try {
				$element = $redis->zlPop($conf['queue_key']);
			}catch (Exception $e){
				$redis = new redisQueue();
				$element = $redis->zlPop($conf['queue_key']);
			}
			
			if(empty($element) && !$conf['keep']){
				break;
			}
			
			$data = json_decode($element,true);
			if (empty($data['fun'])) {
				continue;
			}
			
			//执行
			$ret = $this->call_func($data['fun'], $data['args']);
			if ($ret==false) {
				//消费失败计数
				$incr = $redis->incr($element.'_error_no');
				if($conf['again']>=0 && $conf['again']>=$incr){
					/**
					 *消费失败 数据回归队列top100位置（头部/尾部/top2）
					 *避免放回头部,如果重复消费失败 阻塞任务
					 */
					$newSource = 0;
					$posElement = $redis->zRange($conf['queue_key'],100,100,true);
					if(!empty($posElement)){
						$posElement = $redis->zRange($conf['queue_key'],-1,-1,true);//插入最后
					}
					$newSource = (!empty($posElement[0]))?reset($posElement):$data['key'];
					$redis->zadd($conf['queue_key'] , $newSource, $element);
				}
			}
			$redis->del($element.'_error_no');
			
			$this->setLog(__URL__.$conf['logs'], date('Y-m-d H:i:s').",执行:{$element},return:{$ret}");
			$element = null;
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
		global $conf;
		$ex = array();
		try {
			$ex = explode('.',$queue);
			if (count($ex)>1) {
				$c = new $ex[0];
				return call_user_func_array(array($c,$ex[1]), $args);
			}
			return call_user_func_array($queue, $args);
		}catch(Exception $e){
			$this->setLog(__URL__.$conf['error_logs'],date('Y-m-d H:i:s').",执行:{$queue}".json_encode(array($args)).",return:{$e}");
			return false;
		}
	}
	
	public function setLog($file,$msg){
		$file = @str_replace('{date}', date('Ymd'), $file);
		$myfile = @fopen($file, "a+");
		@fwrite($myfile, $msg."\r\n");
		@fclose($myfile);
	}
}





