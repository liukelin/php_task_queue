<?php
/**
 * 配置文件
 * 
 */
@ini_set('date.timezone','Asia/Shanghai');
@ini_set('default_socket_timeout', -1);
defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);

$config = array(
	//队列使用的redis
	'redis'=>array(
			'host' => '127.0.0.1', 
			'port'=>'6379', 
			'db'=>'0'
		),
	//redis 队列key
	'queue_key'=>'celery_startup_zset',
	//进程数
	'process'=>5,
	//保持脚本持续（为false时，处理完数据 脚本结束）
	'keep'=>false,
	//消费失败重新执行次数，0不重复执行,-1一直执行
	'again'=>-1,
	//执行log
	'logs'=>'logs/log_{date}.log',
	//错误log
	'error_logs'=>'logs/error_{date}.log',
);
