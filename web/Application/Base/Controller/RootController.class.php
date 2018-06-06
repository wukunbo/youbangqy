<?php
namespace Base\Controller;
use Think\Controller;


class RootController extends \Think\Controller {
    //put your code here
    public function __construct() {
        parent::__construct();
		// $config[page_title]="綁定";
		// $config["static"]="Application/User/View/static/";
		 	  
		// $this->assign('config',$config);
		if($_SESSION["wx_userid"] || $_SESSION["openId"]){
			$db = D('contacts');
			$detail=D('contacts')->where("wx_userid='".$_SESSION["wx_userid"]."'")->find();
			$_SESSION[user][pernr]=$detail[pernr];
			//var_dump($detail);
			//if($detail){
				//header("Location:index.php?c=User&a=persondata");
			//}			
		}
		
 		//echo 1; exit;
		$auto_login=1;
		if ($_REQUEST[var_login]) {
                $_SESSION[user][pernr] = $_REQUEST[var_login];
				$_SESSION[wx_userid]= $_REQUEST[var_login];
				// $_SESSION[openId]=$_REQUEST[var_login];
				$auto_login=0;
        }

        if($_REQUEST[var_openid]){

        	$_SESSION[user][pernr] = $_REQUEST[var_openid];
			$_SESSION[openId]=$_REQUEST[var_openid];
			$auto_login=0;
        }

		if ($_REQUEST[no_login]) {
               
				$auto_login=0;
        }
		 
	// 	$_SESSION["wx_userid"]="";
		if($auto_login){
		 	$Function=new \Base\Model\FunctionServiceModel();
			$GLOBALS['pf']=$Function;
			$Get=new \Base\Model\GetServiceModel();
			//var_dump($Get);
			$url_self = $_SERVER['PHP_SELF']; 
			$url_self='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
			//$url_self='http://203.195.229.99'.$_SERVER["REQUEST_URI"];			
			//var_dump($url_self);
			//exit;
			//https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=id&corpsecret=secrect
			if((!$_SESSION["wx_userid"] && !$_SESSION["openId"]) && $_REQUEST["state"]!="188"){
				//跳转获取CODE;
				$data["appid"]="wxf64ba63493455027";
				$data["secret"]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";				
				$data["scope"]="snsapi_userinfo";
				$data["state"]="188";
				$data["redirect_uri"]=$url_self;
				$Get->config($data);
				$Get->get_code();
				exit;
			}
		}	
		if($_REQUEST["state"]=="188" && (!$_SESSION["wx_userid"] && !$_SESSION["openId"])){
			$code = $_REQUEST["code"];
			$Function=new \Base\Model\FunctionServiceModel();
		 	$GLOBALS['pf']=$Function;
			$Get=new \Base\Model\GetServiceModel();
 
			$data["appid"]="wxf64ba63493455027";
			$data["secret"]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";			
			$data["scope"]="snsapi_base";
			$data["state"]="188";
			// $data["agentid"]="4";
			$Get->config($data);
			
			$data=$Get->get_wx_userid($code);
			// var_dump($data);
			// dump($data);exit;
			$_SESSION["wx_userid"]=$data[UserId];
			$_SESSION["openId"]=$data[OpenId];
			$_SESSION["wx_deviceid"]=$data[DeviceId];
			
	 		//exit;
			
			if($_SESSION["wx_userid"] || $_SESSION["openId"]){
			
				 //$detail=D('user')->where("wx_userid='".$_SESSION["wx_userid"]."'")->find();
				 // echo D('user')->getLastsql();
				 // var_dump($detail);
				  //exit;
				 //var_dump($_SESSION[url_back]);
				 $_SESSION[user][pernr]=$_SESSION["wx_userid"];
				 if ($_SESSION[user][pernr]){
				 //if ($detail){
					//echo 1;
					$_SESSION[user][pernr]=$detail[pernr];
					if(!$_SESSION[url_back]){
						//echo 2;
						// $_SESSION[url_back]="index.php?c=User&a=persondata";
						$_SESSION[url_back]=$url_self;
					}
					header("Location:".$_SESSION[url_back]."");
					//$post[wx_userid]=$_SESSION["wx_userid"];
				//	$post[addtime_wx]=time();
 
					//D('user')->where("wx_userid='".$_SESSION["wx_userid"]."'")->save($post);
					
					//$_SESSION[user][pernr]=$datail[pernr];
				}else{
				//	header("Location:user.php");
				}
				
				 
			}else{
				 
				// echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><title>".$GLOBALS['_CFG']['shop_name']."</title></head><body><p align='left'>对不起,{$_CFG['shop_name']}登录失败</p></body></html>";
			}
		}			
    }

}
