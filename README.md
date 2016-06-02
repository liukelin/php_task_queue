# 一个用PHP开发的，使用redis的任务队列

注： 队列的生产、
	
	消费（拉取方式）、
	
	消费确认、
	
	消费失败回队（回队策略）、
	
	多端生产、

	多段消费（避免资源竞争）

	自定义队列（多队列）

	多进程消费（auto队列数量？）


	end...


	1.配置config.php

	2.将/parsley/parsley.php 引入到你的项目

	3.将你需要执行的方法写在func.php (保证可在文件中直接运行)

	4.将startup.php 加入到crontab. ( * * * * * /usr/bin/php /你的项目路径/startup.php )

	5.
	//apply_async(方法名,方法参数array);
	include_once('parsley/parsley.php');
	$c = new parsley();
	$c->apply_async('test',array($i,$i));


	end...