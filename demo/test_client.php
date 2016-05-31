<?php
/**
 * 创建任务
 * @author: liukelin 314566990@qq.com
 */

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'../parsley/parsley.php');


function test($i){
	print_r('hello:'.$i);
}

//创建任务
$c = new parsley();
for ($i=0; $i<=110000; $i++){
	$c->apply_async('test',array($i));
}
