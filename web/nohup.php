<?php

$host="http://".$_SERVER['HTTP_HOST']."/youbangqy/web/";
define("DOMAIN",$host);
ignore_user_abort(true);  
set_time_limit(0);

$run=1;
while($run==1){
	ob_flush();
	flush();
	$h=date("H");
	$s="\n".date("Y-m-d H:i:s");
	file_put_contents("php_nohup_log.txt",$s);

	#活动开始推送提醒
	$activity_url=DOMAIN.'remind.php?c=index&a=activity_remind&var_login=1';
	file_get_contents($activity_url);
	// echo $content;exit;

	#凌晨3点自动更新通讯录
	$contact_url=DOMAIN.'remind.php?c=index&a=contact_updata&var_login=1';
	file_get_contents($contact_url);
	$s=file_get_contents("php_nohup_run.txt");

	if($s==-1){
		$run=0;
	}
	sleep(1);
}


?>