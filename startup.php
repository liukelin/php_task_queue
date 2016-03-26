<?php
/**
 * 此文件用于cli模式运行
 * 设置crontab 或者使用 supervisor作为进程管理
 */

defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(__URL__.'config.php');
include_once(__URL__.'parsley.php');

global $conf;
$conf = $config;

//run
//开启进程数量
$process = $conf['process'];

$on_out = system('ps -ef | grep "startup.php" | grep -v "grep" | wc -l',$out);
if (intval(trim($on_out)) > ($process+1)){
	exit('error on start!');
}

$st = new parsley();
$st->digestion_queue_data();