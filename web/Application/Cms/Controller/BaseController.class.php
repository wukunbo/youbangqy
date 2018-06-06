<?php
namespace Cms\Controller;
use Base\Controller\RootController;

/**
 * 
 * @author grace
 *	前端基础类
 *
 */
class BaseController extends RootController {
	
	public function __construct(){

 		parent::__construct();
		$wxqy_config[appid]="wxf64ba63493455027";
		$wxqy_config[secret]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";
		$this->wxdata=$wxqy_config;
		$this->set_jssdk($wxqy_config);

		$config["view"]="Application/Cms/View/";
	 	$this->assign('config',$config);
		
	}
	/*
	$type 1 全部，2 re 3 post
	*/
	public function page_params($type=1){
		//接收参数值
		if($type==1){
			foreach($_REQUEST as $_k=>$_v){
				 if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
					exit('Request var not allow!');
				 }else{
					$str="&".$_k."=".$_v;
				 }
			}
		}
		//接收参数值
		if($type==2){
			$request='';
			foreach($_GET as $_k=>$_v){
				 if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
					exit('Request var not allow!');
				 }else{
					$str="&".$_k."=".$_v;
				 }
			}
		}
		//接收参数值
		if($type==3){
			foreach($_POST as $_k=>$_v){
				 if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
					exit('Request var not allow!');
				 }else{
					//$request[$_k]=$_v;
					$str="&".$_k."=".$_v;
				 }
			}
		}
		return $request;
    }
	function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {    // 如果是SSL加密则加上“s”
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	} 

	public function showmsg($msg,$url=1){
		//$url=-1 则不跳转
		if($url==1){
			$url= $_SERVER['HTTP_REFERER'];
		}
		$this->msg=$msg;
		$this->url=$url;
		$this->display("Public/showmsg");
		exit;
	}

	public function set_jssdk($wxdata){
		$Function=new \Base\Model\FunctionServiceModel();
		$GLOBALS['pf']=$Function;
		$Get=new \Base\Model\GetServiceModel();
		$Get->config($wxdata);
		$wxconfig=$Get->getJssdkSignature();
		$this->assign('wxconfig',$wxconfig);
	}
}