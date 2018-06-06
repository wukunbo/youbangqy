<?php
namespace Weixin\Controller;
use Think\Controller;
class BaseController extends Controller{
	 
	public function __construct($wx_id,$url_self=''){  
 		parent::__construct();
		if($_REQUEST[var_login]){
			$auto_login=0;
			$_SESSION[user][user_id]=$_REQUEST[var_login];
			$_SESSION[wx][$wx_id]["openid"]=1;

		}else if($_REQUEST[var_openid]) {
			$auto_login=0;
			$_SESSION[user][user_id]="";
			$_SESSION[wx][$wx_id]["openid"]=$_REQUEST[var_openid];
		}else{
			$auto_login=1;
		}
		if($_REQUEST[var_login]==-1){
			$_SESSION="";
		}
		
		//获取基本配置信息
	//	var_dump($wx_id);
		if($_REQUEST[tt]==1){
			$_SESSION[wx][$wx_id]["openid"]="";
		}
		if($_REQUEST[t]==1){
			//var_dump($_SESSION);
			//exit;
		}
		$BasicLogic=new \Basic\Logic\BasicLogic;
		$wx_config=$BasicLogic->wx_config($wx_id);
		 
		//var_dump($wx_config);
		//$_SESSION[wx][$wx_id]["openid"]="";
		if($auto_login && $wx_config){
			 
			$Get=new \Weixin\Sever\GetSever;
			$GLOBALS['pf']=new \Weixin\Sever\FunctionSever;
			if(!$url_self){
				$url_self='http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
			}
			if(!$_SESSION[wx][$wx_id]["openid"] && $_REQUEST["state"]!="188"){
				 
				//$config["scope"]="snsapi_user";
			//	$wx_config["scope"]="snsapi_base";//基本
				$wx_config["scope"]="snsapi_userinfo";//授权 获取用户资料snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
				$wx_config["appid"]=$wx_config["appid"];
				$wx_config["secret"]=$wx_config["appsecret"];
				$wx_config["state"]="188";
				$wx_config["redirect_uri"]=$url_self;
				
				$Get->config($wx_config);
				$Get->get_code();
			}
		}
 
	//	$_SESSION["openid"]="";
	   if($_REQUEST["state"]=="188" && ( !$_SESSION[wx][$wx_id]["openid"])){

			$code = $_REQUEST["code"];
			$Get=new \Weixin\Sever\GetSever;
			//$config["scope"]="snsapi_user";
			$wx_config["appid"]=$wx_config["appid"];
			$wx_config["secret"]=$wx_config["appsecret"];
			$wx_config["scope"]="snsapi_base";
			$wx_config["state"]="188";
			$Get->config($wx_config);
			
			$data=$Get->get_openid($code);
	 
		
			$_SESSION[wx][$wx_id]["openid"]=$data[openid];
			$_SESSION[wx][$wx_id]["code"]=$code;
			$openid=$_SESSION[wx][$wx_id]["openid"];
			
			 
			$access_token=$data[access_token];
			$db_weixin=M('weixin');
			$db_user=M('user');
			
			if($openid!=NULL){
				 
				//是否存在数据库  对于无法获取用户名的
				 
				$Get=new \Weixin\Sever\GetSever();	
				//$weixin_data=$Get->get_base_user_info_for_unionid($openid,$wx_config);//open.weixin.qq.com
				//var_dump($weixin_data);
				//$weixin_data=$Get->get_base_user_info($openid,$wx_config);
				$weixin_data=$Get->get_user_info($openid,$access_token);//授权
				 
					//var_dump($weixin_data);
				$weixin_data[privilege]=json_decode($weixin_data[privilege]);
				if ($weixin_data[privilege]==NULL){
					$weixin_data[privilege]="0";
				}
				$weixin_data[wx_id]=$wx_id;
				$weixin_data[openid]=$openid;;
				$weixin_data[addtime]=time();
				if($_REQUEST[t]==1){
				
					echo "-----weixin_data-----";
					var_dump($weixin_data);
					//exit;
				}
				
				$id=$db_weixin->where("openid='".$openid."'")->getField('id');
				//创建 weixin
				if(!$id){
					$db_weixin->add($weixin_data);
				}
				//补全 weixin
				$nickname=$db_weixin->where("openid='".$openid."'")->getField('nickname');
				if($id && ! $nickname){
					$db_weixin->where("openid='".$openid."'")->save($weixin_data);
				}
				if($_REQUEST[t]){
					echo "+++++++++++++++++++++";
					echo $db_weixin->getLastsql();
				}
				
				
				//是否存在数据库		
				//$user_id=$db_user->where("unionid='".$weixin_data[unionid]."'")->getField('id'); //open.weixin.qq.com
				$user_id=$db_user->where("openid='".$openid."'")->getField('id');
				//创建 user 
				if($_REQUEST[t]){
					echo "++++++++db_userdb_userdb_userdb_userdb_user+++++++++++++";
					echo $db_user->getLastsql();
				}
				if(!$user_id){		
					$tmp=array();
					$tmp[wx_id]=$wx_id;
					$tmp[openid]=$openid;
					$tmp[username]=$weixin_data[nickname];
					$tmp[unionid]=$weixin_data[unionid];
					$tmp[headimgurl]=$weixin_data[headimgurl];

					$tmp[addtime]=time();
					$user_id=$db_user->add($tmp);
					
					$db_user->getlastSql();
 				
					 
				}
				//补全 user
				$nickname=$db_weixin->where("openid='".$openid."'")->getField('username');
				if($user_id && !$nickname){
					$tmp=array();
					$tmp[wx_id]=$wx_id;
					$tmp[openid]=$openid;
					$tmp[username]=$weixin_data[nickname];
					$tmp[unionid]=$weixin_data[unionid];
					$tmp[headimgurl]=$weixin_data[headimgurl];
					//$tmp[addtime]=time();
					$db_user->where("openid='".$openid."'")->save($tmp);
				}
				 
				$_SESSION[user][user_id]=$user_id;
				if($_REQUEST[t]){
						echo "+++++++++++++++++++++";
						echo $db_user->getLastsql();
						echo "+++++++++++++++user_id++++";
						var_dump($_SESSION[user][user_id]);
						echo "+++++++++++++++++++++";
					}
			 
			}else{
				 
				//echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><title>".$GLOBALS['_CFG']['shop_name']."</title></head><body><p align='left'>对不起,{$_CFG['shop_name']}登录失败</p></body></html>";
			}
		}		
    }
 
}