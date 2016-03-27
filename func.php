<?php
/**
 * 业务方法类，此类用来放置需要执行的方法函数
 * 方法return false，表示通知消息消费失败，重新回到队列执行
 */

//普通函数形式
function test($a,$b){
	$myfile = fopen("testfile.txt", "a+");
	fwrite($myfile, date('Y-m-d H:i:s').",test,{$a}=={$b}\r\n");
	fclose($myfile);
	return 1;
}

//可以为类形式
class test{
	public function test($a, $b){
		$myfile = fopen("testfile.txt", "a+");
		fwrite($myfile, date('Y-m-d H:i:s').",test.test,{$a}=={$b}\r\n");
		fclose($myfile);
		return 1;
	}
}