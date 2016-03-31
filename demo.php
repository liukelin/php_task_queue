<?php
/**
 * test
 * @author: liukelin 314566990@qq.com
 */

defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(__URL__.'parsley/parsley.php');

// $r = new Redis();
// $r->connect('127.0.0.1',6379,0);
// $r->set('kk',1);

// $r = new redisQueue();
// $r->zadd('k1', 1, 12);


//创建任务
$c = new parsley();
for ($i=0;$i<=110000;$i++){
	$c->apply_async('test',array($i,$i));
}

for ($i=0;$i<=100000;$i++){
	$c->apply_async('test.test',array('test'.$i,$i));
}