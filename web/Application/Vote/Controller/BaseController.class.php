<?php
namespace Vote\Controller;
use Base\Controller\RootController;

class BaseController extends RootController {
	
	public function __construct(){
		parent::__construct();
		$config["static"]="Application/Vote/View/static/";
		$this->assign('config',$config);

		$voteId=$_REQUEST[id];
		$vote=M("vote")->where("id={$voteId}")->field("status,time_end,open_status,partment,open_status")->find();

		$this->open_status=$vote[open_status];

		#不对外开发
		if($vote[open_status] == 1){
			$this->wxuser_info=M("vote_apply")->where("vote_id={$voteId} AND user_id='{$_SESSION[wx_userid]}'")->find();
			// echo M("vote_apply")->getLastsql();exit;
			if(!$this->wxuser_info || empty($_SESSION[wx_userid])){
				// dump($this->wxuser_info);exit;
-				$this->showmsg("不在活动邀请范围内",-1);
			}
		}

		if($vote[status] == -1){
			$this->showmsg("活动已下架",-1);
		}
		if($vote[time_end] < time()){
			$this->showmsg("活动已结束",-1);
		}
	}


	public function showmsg($msg,$url="-1"){
		// 2 重新刷新
		if($url==1){
			$url= $_SERVER['HTTP_REFERER'];
		}
		$this->msg=$msg;
		$this->url=$url;
		$this->display("Public/showmsg");
		exit;
	}

	public function request(){
		//接收参数值
		 
		foreach($_REQUEST as $_k=>$_v){
			if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
				exit('Request var not allow!');
			}else{
				$request[$_k]=$_v;
			}
		}
		return $request;
    }
}