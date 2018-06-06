<?php
namespace Activity\Controller;
use Base\Controller\RootController;

/**
 * 
 * @author grace
 *	前端基础类
 */
class BaseController extends RootController {
 	
	public function __construct(){
 		  
		parent::__construct();

		$wxqy_config[appid]="wxf64ba63493455027";
		$wxqy_config[secret]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";
		$this->wxdata=$wxqy_config;
		$this->set_jssdk($wxqy_config);
 
		$config["static"]="Application/Activity/View/static/";
		$config["tool"]="Application/Activity/View/tool/";
 		 
		$this->assign('setting',$setting);	
	 	$this->assign('config',$config);

	 	$this->wx_user_id=$_SESSION["wx_userid"];
		
	 	$activityId=$_REQUEST[id];
		$activity=M("activity")->where("id={$activityId}")->field("status,end_time,open_status,partment")->find();

		$this->open_status=$activity[open_status];

		#不对外开发
		if($activity[open_status] == 1){
			$this->wxuser_info=M("activity_apply")->where("activity_id={$activityId} AND user_id={$this->wx_user_id}")->find();
			if(!$this->wxuser_info || empty($this->wx_user_id)){
				// $this->showmsg("不在活动邀请范围内，请联系主办方",-1);
			}
		}
		if($activity[status] == -1){
			$this->showmsg("活动已下架",-1);
		}
		// if($activity[end_time] < time()){
		// 	$this->showmsg("活动已结束",-1);
		// }

		$user_child=M("user_child")->where("wx_userid='{$_SESSION[wx_userid]}' AND pass_status=1")->find();

		$this->show_report=0;
		if($user_child){
			$role_auth_ids=M("user_child_role")->where("role_id={$user_child[role_id]}")->getField("role_auth_ids");
			if($user_child[level] == 1 || $user_child[show_all] == 2 || strpos($role_auth_ids,'120') !== false){
				
				$this->show_report=1;
				$this->user_level=1;
			}
			// if($user_child[partment_id] == $activity[partment]){
				$this->show_report=1;
				$this->user_partment_id=$user_child[partment_id];
			// }
		}

		 
	}

	
	public function display_tpl($file='',$theme=''){
	
		// $search[shop_id]=$this->shop_id;
		// $this->Config=new \Shop\Logic\ConfigLogic;
		// $res=$this->Config->detail($search);
		// $config=$res[detail];
		
		// //模板选择优先顺序：用户模板-管理员模板(默认模板)
		
		// $theme=M("shop_templet")->where("  id='".$this->shop_config[templet_id]."'")->getField("theme");
		// if(!$theme){
		// 	//获取默认模板
		// 	$theme=M("shop_templet")->where("is_selected=1")->getField("theme");
		// 	$theme="Jiayuan";
		// }
		// $tpl=$theme.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		// if($file){
		// 	$tpl=$theme.'/'.CONTROLLER_NAME.'/'.$file;
		// }
		
		// $html=$tpl.".html";
 
 	// 	$this->set_jssdk();
		// $this->display($tpl);
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

	public function set_jssdk1(){
	
		$BasicLogic=new \Basic\Logic\BasicLogic;
		$wx_config=$BasicLogic->wx_config($this->wx_id);
		//var_dump($wx_config);
		$_Get=new \Weixin\Sever\GetSever();
		$tmp["appid"]=$wx_config[appid];
		$tmp['id']='3';
		$tmp["secret"]=$wx_config[appsecret];

		$wxconfig[noncestr]="Wm30WZYTPz0wzccndW3";
		$wxconfig[timestamp]=time();
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    	$wxconfig[url]= "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		//var_dump($wxconfig);
		$data=$_Get->getJssdkSignature($wxconfig,$tmp);
		$wxconfig[signature]=$data[signature];
		$wxconfig[appid]=$data[appid]; 
		$user = new \User\Logic\UserLogic();
    	$userinfo=$user->user($_SESSION[user][userid]);   	
		$url=urlencode($_SERVER['HTTP_REFERER']);
		 
		$this->assign('wxconfig',$wxconfig);
		$this->assign("url",$url);
		$this->assign('user_id',$_SESSION[user][userid]);
    	$this->assign('userinfo',$userinfo);
	}
	
	//跨域jsonp
	public function get_json($data){
    	
    	$data=json_encode($data);    	
    	echo $data=$_GET["callback"]."(". $data.");";
  
    }

  //   public function set_jssdk(){
  //   	$Function=new \Base\Model\FunctionServiceModel();
		// $GLOBALS['pf']=$Function;
		// $Get=new \Base\Model\GetServiceModel();
		// $Get->config($wxdata);
  //   }
	/*
	$type 1 全部，2 re 3 post
	*/
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
			$str="&c=".CONTROLLER_NAME."&a=".ACTION_NAME;
			 
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
		return $str;
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
 
	
}