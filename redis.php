<?php
/**
 * 模拟实现zset的 lpop rpop
 * 
 */
defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(__URL__.'config.php');
global $config;
$config = $conf;

class RedisClient{
	const POSITION_FIRST = 0;
	const POSITION_LAST = -1;

	//获取队列头部元素，并删除
	public function zPop($zset){
		return $this->zsetPop($zset, self::POSITION_FIRST);
	}
	//获取队列尾部元素，并删除
	public function zRevPop($zset){
		return $this->zsetPop($zset, self::POSITION_LAST);
	}

	//使用watch监控key，获取元素
	private function zsetPop($zset, $position){
		global $config;
		$r = $config['redis'];
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
	
	
	
}
