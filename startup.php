<?php
/**
 * 此文件用于cli模式运行
 * 设置crontab 或者使用 supervisor作为进程管理
 * crontab: * * * * * /usr/bin/php startup.php
 */
@ini_set('date.timezone','Asia/Shanghai');
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'config.php');
include_once(__URL__.'parsley/parsley.php');

global $conf;
$conf = $config;

//run
$process = $conf['process'];
$on_out = system('ps -ef | grep "startup.php" | grep -v "grep" | wc -l',$out);
if (intval(trim($on_out)) > ($process+1)){
	exit('error on start!');
}

$st = new parsley();
$st->digestion_queue_data();