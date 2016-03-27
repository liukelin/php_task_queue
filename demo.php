<?php
/**
 * test
 */

defined('__URL__') or define('__URL__',dirname(__FILE__).DIRECTORY_SEPARATOR);
include_once(__URL__.'parsley/parsley.php');


$c = new parsley();
for ($i=0;$i<=10000;$i++){
	$c->apply_async('test',array($i,$i));
}

for ($i=0;$i<=10000;$i++){
	$c->apply_async('test.test',array('test'.$i,$i));
}