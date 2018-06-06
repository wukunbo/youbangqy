<?php
namespace Weixin\Controller;
use Think\Controller;
class WeixinController extends Controller{ 
	public function index(){
		if($_REQUEST[test]==1){
		 
			$post=$_REQUEST[post];
			if($post[weixin_type]=="text"){
				$MsgType1="text";
				$Content1=$post[keywords];
			}
			if($post[weixin_type]=="subscribe"){
				$MsgType1="event";
				$Event1="subscribe";
				 
			}
			if($post[weixin_type]=="CLICK"){
				$MsgType1="event";
				$Event1="CLICK";
				 
			}
			if($post[weixin_type]=="qc"){
				$MsgType1="event";
				$Event1="subscribe";
			}
			 
			$GLOBALS["HTTP_RAW_POST_DATA"]="
			<xml>
				<ToUserName><![CDATA[toUser]]></ToUserName>
				<FromUserName><![CDATA[FromUser]]></FromUserName>
				<CreateTime>".time()."</CreateTime>
				<MsgType><![CDATA[".$MsgType1."]]></MsgType>
				<Event><![CDATA[".$Event1."]]></Event>
				<EventKey><![CDATA[qrscene_12312_11]]></EventKey>
				<Content><![CDATA[".$Content1."]]></Content>
				
			</xml>";
			//var_dump($GLOBALS["HTTP_RAW_POST_DATA"]);
		}
	 
		if($_GET["echostr"]){
			$echoStr = $_GET["echostr"];
			echo $echoStr;
			exit;
		}
		
		$wx_id=$_REQUEST[wx_id];
		$BasicLogic=new \Basic\Logic\BasicLogic;
		$config=$BasicLogic->wx_config($wx_id);
	 	$config[secret]=$config[appsecret];
 
		
		$GLOBALS['pf']=new \Weixin\Sever\FunctionSever;
		$Get=new \Weixin\Sever\GetSever;		
		$weixin =D('Weixin','Sever');	
		define("TOKEN", 'ozaki_token');	
		$Ticket=$weixin->Ticket();		
		$MsgType=$weixin->MsgType();		
		$EventKey=$weixin->EventKey();		
		$openid=$weixin->FromUsername();		 
		$Content=$weixin->Content();
		$Event=$weixin->Event();
		$imgurl=$weixin->imgurl();
		$MediaId =$weixin->MediaId();
 		
		//$weixin->responseText("".$EventKey);
 
				
				
		if(!$config[appid] && !$config[appsecret] ){
			$weixin->responseText("appid没有配置");
			exit;
		}
 		
 		//var_dump($GLOBALS["HTTP_RAW_POST_DATA"]);
		//扫描
 
		if ($Ticket!=NULL){
			//$text=="扫描";
			//$weixin->responseText($text);
			//exit;	
		}
		 
		//接收文字类型
		if($MsgType=="text"){
			 
			//转移处理
			$curPageDomain=$this->curPageDomain();
			$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
			$GLOBALS['pf']=new \Weixin\Sever\FunctionSever;
			$res=$GLOBALS['pf']->post_xmldata($curPageDomain."wxadmin/mobile/tlwx_api.php?wx_id=".$wx_id."",$postStr);
			echo $res;
			exit;
			
			//转移多客服
			file_put_contents("kf.txt",time().$Content."\n");
			$result=$Get->get_user_con_status($config,$openid);
			if ($result['kf_account']!=NULL){
				$text="您已经接入".$result[kf_account]."客服";
				$kf_account=$result[kf_account]="";
				$weixin->transfer_customer_service($kf_account);
				//$weixin->responseText($text);
				exit;	
			}else {
				$online_kf=$Get->get_online_kf($config);
				//$str=json_encode($result);
				//$weixin->responseText($str);
				//exit;
				$online_kf_lists=$online_kf['kf_online_list'];
				
				for ($i=0;$i<count($online_kf_lists);$i++) { 
					//if ($online_kf_lists[$i][status]==3||$online_kf_lists[$i][status]==1) {
						$tmp[]=$online_kf_lists[$i][kf_account];
					//}
				}
				
				$len=count($tmp);
				if ($len==0){
					$str=" 亲爱的朋友，现在没有客服在线。";
					$weixin->responseText($str);
					exit();
				}else {
					$len--;
					$tmp_num=rand(0,$len);
					$account=$tmp[$tmp_num];
					$kf['openid']=$openid;
					$kf['kf_account']=$account;
					$kf['text']=$Content;
					$data=$Get->new_con_kf($config,$kf);
					$str=json_encode($result);
					$str="OZAKI".$account."客服将为你服务";
					$weixin->responseText($str);
					exit;	
				}
			}
			//默认处理
			$str="请点击下面的菜单栏了解更多关于我们公众平台";
			$weixin->responseText($str);
			exit;
		}
		//特殊事件处理
 
		if($MsgType=="event"){
			//关注 
			if($Event=="subscribe"){
				 
				$user=M('user');
				$db_weixin=M('weixin');
				//$weixin->responseText("2".$EventKey.$MsgType.$Event);  
				$result=$db_weixin->where("openid='$openid'")->find();
				if($_REQUEST[test]!=1){
					if ($result==NULL){
						$data=$Get->get_base_user_info($openid,$config);
						if(!$data[nickname]){
							$weixin->responseText("关注失败，无法获取用户信息。".json_encode($data));
						}
						//新增数据 weixin{}
						$data[addtime]=time();
						$data[wx_id]=$wx_id;
						$db_weixin->add($data);
						//新增数据 user
						$tmp=array();
						$tmp[addtime]=time();
						$tmp[openid]=$data[openid];
						$tmp[wx_id]=$wx_id;
						$tmp[username]=$data[nickname];
						$tmp[headimgurl]=$data[headimgurl];
						
						$userid=$user->add($tmp);
						
						$sql=$user->getLastsql();
						
						//$weixin->responseText("1-".$sql."-".json_encode($tmp)."-".json_encode($data));
						
						$s=$user->getLastsql();
						$s=json_encode($data);
						$s="";
					}else {
						$userid=$result[id];
						$db_weixin->where("openid='$openid'")->setField("subscribe",1);
						//$weixin->responseText("asdfasd".$openid."-".json_encode($tmp));
					}
					 
				}
				//$EventKey="sasdf_52";
				if($EventKey && $userid){
					$arr=explode("_",$EventKey);
				 	$shop_id=M("shop_config")->where("wx_id='".$wx_id."'")->getField("id");
					$ShareLogic=new \Share\Logic\ShareLogic();	
					$search[user_id]=$userid;
					//$search[user_id]=9892;
					$search[parent_id]=$arr[1];
					$search[business_id]=$shop_id;
					$search[app]="shop";
					$res=$ShareLogic->add_user($search);
					
					//$weixin->responseText("2".$EventKey."-".$userid."-".$openid."-".json_encode($search));
					//测试 
					//exit;
				}
				 
				//$weixin->responseText("2".$EventKey.$MsgType.$Event);  
				//$weixin->responseText("appid2".$EventKey."-".json_encode($search));
				//转移处理
				 
				$curPageDomain=$this->curPageDomain();
				$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
				 
				$GLOBALS['pf']=new \Weixin\Sever\FunctionSever;
				$res=$GLOBALS['pf']->post_xmldata($curPageDomain."wxadmin/mobile/tlwx_api.php?wx_id=".$wx_id."",$postStr);
				echo $res;
				exit;
			}
			if($Event=="unsubscribe"){
				$weixin=M('weixin');
				$weixin->where("openid='$openid'")->setField("subscribe",0);
			}
			 
			if($Event=="LOCATION"){
				 
			} 
			
			//文本回复和点击回复
			if($Event=="CLICK"){	
				//转移处理
				$curPageDomain=$this->curPageDomain();
				$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
				$GLOBALS['pf']=new \Weixin\Sever\FunctionSever;
				$res=$GLOBALS['pf']->post_data_normal($curPageDomain."wxadmin/mobile/tlwx_api.php?wx_id=".$wx_id."",$postStr);
				echo $res;
				exit;
			}
		}
	}
	function curPageDomain() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {    // 如果是SSL加密则加上“s”
				$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["PHP_SELF"];
		}
 
		 
		$url_mobile=str_replace(basename($_SERVER['SCRIPT_NAME']),"",$pageURL);
		return $url_mobile;
	} 
}