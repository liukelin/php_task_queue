<?php
/**
 * redis 队列数据操作类
 * 模拟实现zset的 lpop rpop
 * redis有list结构，它也有zset有序集合应为source的存在，使zset有了无限可能（插队）, 虽然对于此场景list结构的pop功能很好用，但还是使用灵活性更高的zset
 * 
 */
defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(__URL__.'config.php');
global $conf;
$conf = $config;

class redisQueue{
	const POSITION_FIRST = 0;
	const POSITION_LAST = -1;
	
	public function zadd($key,$source,$value){
		global $conf;
		$r = $conf['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
		$redis->zadd($key,$source,$value);
	}

	/**
	 * 获取队列头部元素，并删除
	 * @param unknown $zset
	 */
	public function zPop($zset){
		return $this->zsetPop($zset, self::POSITION_FIRST);
	}
	
	/**
	 * 获取队列尾部元素，并删除
	 * @param unknown $zset
	 */
	public function zRevPop($zset){
		return $this->zsetPop($zset, self::POSITION_LAST);
	}

	/**
	 * 方法1：使用watch监控key，获取元素 (轮询大大增加了时间消耗)
	 * @param unknown $zset
	 * @param unknown $position
	 * @return boolean|unknown
	 */
	private function zsetPop($zset, $position){
		global $conf;
		$r = $conf['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
		
		//乐观锁监控key是否变化
		$redis->watch($zset);

		$element = $redis->zRange($zset, $position, $position);

		if (!isset($element[0])) {
			return false;
		}

		//若无变化返回数据
		$redis->multi();
		$redis->zRem($zset, $element[0]);
		if($redis->exec()){
			return $element[0];
		}
		//key发生变化，重新获取(轮询大大增加了时间消耗)
		return $this->zsetPop($zset, $position);
	}
	
	/**
	 * 获取队列头部元素，并删除
	 * @param unknown $zset
	 */
	public function zlPop($zset){
		return $this->zsetPopCheck($zset, self::POSITION_FIRST);
	}
	
	/**
	 * 方法2：使用写入标记key，获取可用元素 (轮询大大增加了时间消耗)
	 * @param unknown $zset
	 * @param unknown $position
	 * @return boolean|unknown
	 */
	private function zsetPopCheck($zset, $position){
		global $conf;
		$r = $conf['redis'];
		$redis = new Redis();
		$redis->connect($r['host'],$r['port'],$r['db']);
		
		$element = $redis->zRange($zset, $position, $position);
		if (!isset($element[0])) {
			return false;
		}
		
		$myCheckKey = microtime(true)*10000;
		$k = $element[0].'_check';
		$checkKey = $redis->get($k);
		
		print_r($checkKey.'#'.$myCheckKey);
		
		if (empty($checkKey) || $myCheckKey == $checkKey) {
			
			$redis->setex($k, 10, $myCheckKey);
			$redis->watch($k);//监控锁
			$redis->multi();
			$redis->zRem($zset, $element[0]);
			if($redis->exec()){
				return $element[0];//返回数据
			}
			//return false;
		}
		//重新获取
		return $this->zsetPopCheck($zset,$position);//$position = 2
	}
}
