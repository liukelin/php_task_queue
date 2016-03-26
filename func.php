<?php
/**
 * 业务方法类，此类用来放置需要执行的方法函数
 * 方法return false，表示通知消息消费失败，重新回到队列执行
 */
defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(__URL__.'config.php');
global $conf;
$conf = $config;


function test($a,$b){
	$myfile = fopen("testfile.txt", "a+");
	fwrite($myfile, date('Y-m-d H:i:s').",{$a}=={$b}\r\n");
	fclose($myfile);
	return true;
}