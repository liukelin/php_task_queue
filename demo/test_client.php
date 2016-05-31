<?php
/**
 * 创建任务  （可运行多个进程）
 * @author: liukelin 314566990@qq.com
 */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'../parsley/parsley.php');

//创建任务
$c = new parsley();
for ($i=0; $i<=1000000; $i++){
	$c->apply_async('helloWorld',array($i));
}
