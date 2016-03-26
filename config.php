<?php
/**
 * 配置文件
 * 
 */
$config = array(
	//队列使用的redis
	'redis'=>array(
			'host' => '127.0.0.1', 
			'port'=>'6379', 
			'db'=>'0'
	),
	'queue_key'=>'celery_startup_zset',//redis 队列key
	'logs'=>'logs/',
	'process'=>5,//进程数
);
