<?php
namespace Lottery\Controller;
use Base\Controller\RootController;


class BaseController extends RootController {

	public function __construct(){
		parent::__construct();
		$config["static"]="Application/Lottery/View/static/";
		$this->assign('config',$config);

		$user_child=M("user_child")->where("wx_userid='{$_SESSION[wx_userid]}'")->find();

		$this->show_report=0;
		if($user_child){
			if($user_child[level] == 1){
				$this->show_report=1;
			}
			if($user_child[partment_id] == $activity[partment]){
				$this->show_report=1;
			}
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