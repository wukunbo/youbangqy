<?php
namespace Weixin\Sever;
use Think\Model;
/**
 * 微信自动登录类
 * ============================================================================
 * * 版权所有深圳市托略计算机有限公司
网址地址 http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
 * ============================================================================

 */
class GetSever {
	/*用户同意授权，获取code的链接
				 
		完整如下：	https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
		#wechat_redirect 无论直接打开还是做页面302重定向时候，必须带此参数 
		如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数redirect_uri?state=STATE 
		code说明 ：
code作为换取access_token的票据，每次用户授权带上的code将不一样，code只能使用一次，5分钟未被使用自动过期。
	*/

	public $_url_get_code			=	"https://open.weixin.qq.com/connect/oauth2/authorize?";
	//授权使用的获取ACCESS_TOKEN及openid的网址
	public $_url_web_access_token			= "https://api.weixin.qq.com/sns/oauth2/access_token?";
	
	public $_url_open_access_token			= "https://api.weixin.qq.com/sns/oauth2/access_token?";
	
	//基础接入的ACCESS_TOKEN
	public $_url_base_access_token     =     "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&";
	//刷新 网页access_token
	public $_url_refresh_token			= "https://api.weixin.qq.com/sns/oauth2/refresh_token?";
	//授权拉取用户资料
	public $_url_web_get_user_info			= "https://api.weixin.qq.com/sns/userinfo?";

	//基础拉取用户资料
	public $_url_base_get_user_info      =     "https://api.weixin.qq.com/cgi-bin/user/info?";
	//
	public $_url_base_get_user_info_for_unionid      =     "https://api.weixin.qq.com/sns/userinfo?";
	 
	//默认值
	public $_grant_type   =  "authorization_code ";
	//回调网址，需要urlencode编码
	public $_redirect_uri			=	"";	
	//永久二维码生成
	public $_get_ticket="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=";
	//通过ticket换取二维码
	public $_get_ticket_url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=";
	//发送文字信息
	public $_send_new="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=";
	//不知用处
	public $_state	=	"STATE";
	//在线客服列表
	public $_get_online_kf_lists="https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist?access_token=";
	//创建客服会话
	public  $_create_con_kf ="https://api.weixin.qq.com/customservice/kfsession/create?access_token=";
	//关闭客服会话
	public  $_close_con_kf ="https://api.weixin.qq.com/customservice/kfsession/close?access_token=";
	//获取客户会话状态
	public $_get_user_con_status="https://api.weixin.qq.com/customservice/kfsession/getsession?";
	//获取聊天记录
	public $_get_con_text="https://api.weixin.qq.com/cgi-bin/customservice/getrecord?access_token=";
	//回复客服信息
	public $_send_kf_text="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=";
	//初始化相关值 数组形式
	public function config($data){
		$GLOBALS['pf']=new \Weixin\Sever\FunctionSever;
		if(!$data[appid] || !$data[secret] ){

			var_dump($data);
		}
		$this->_appid=trim($data["appid"]);
		$this->_scope=trim($data["scope"]);
		$this->_state=trim($data["state"]);
		$this->_secret=trim($data["secret"]);
		$this->_redirect_uri=urlencode($data["redirect_uri"]);
	}


	//拼接获取CODE的网址
	public function get_code_url(){
		$str = ''
			.$this->_url_get_code
			.'appid='.$this->_appid
			.'&redirect_uri='.$this->_redirect_uri
			.'&response_type=code&scope='.$this->_scope.'&state='.$this->_state.'#wechat_redirect';
		return $str;
	}


	// 网页获取CODE，返回  $_REQUEST["code"]
	public function get_code(){

		$url=$this->get_code_url();
		header("Location: ".$url."");
		exit;
	}

	//网页获取access_token和openid
	public function get_openid($code){

		$url=''
			.$this->_url_web_access_token
			.'appid='.$this->_appid
			.'&secret='.$this->_secret
			.'&code='.$code
			.'&grant_type='.$this->_grant_type;
		$data=$GLOBALS['pf']->get_data($url);

		return $data;
	}

	//获取基本的access
	public function get_base_access_token($data=array()){
		if($data){
			$this->config($data);
		}
		$cache_name='access_token_'.$data['appid'];
		$cache=$GLOBALS['pf']->read_static_cache($cache_name);

		$time=$cache["time"];

		$nowtime=time();

		$passtime=$nowtime-$time;
		if($_REQUEST[t]){
				
				echo "-----------time:";
				echo $nowtime;
				echo "-----------time:";
				echo $time;
				echo "-----------time:";
				echo $passtime;
				echo "-----------time:";
		}

		if ($passtime>60||$cache['access_token']==NULL){

			$url=''.$this->_url_base_access_token
				.'appid='.$this->_appid
				.'&secret='.$this->_secret;
			$cache=$GLOBALS['pf']->get_data($url);

			$cache[access_token]=$cache['access_token'];
			$cache["time"]=time();
			$GLOBALS['pf']->write_static_cache($cache_name, $cache);
			if(!$cache[access_token]){
				
				echo $url;
				var_dump($cache);
				return $cache;
			}
			return $cache;
		}
		// $this->access_token2=$data[access_token];
		return $cache;


	}

	public function getJssdkSignature($data,$config) {

		$GLOBALS['pf'] = new \Weixin\Sever\FunctionSever;
		$cache_name = "jssdk_ticket_".$config[appid];
		$tempdata = $GLOBALS['pf']->read_static_cache($cache_name);

		$addtime = $tempdata[addtime];
		$this->_appid=$tempdata[appid];

		$data[ticket] = $tempdata['ticket'];

		$nowtime = time();
		$passtime = $nowtime - $addtime;
		$cache = $this->get_base_access_token($config);

		$ACCESS_TOKEN=$cache['access_token'];
		$url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $ACCESS_TOKEN . "&type=jsapi";

      	 if ($passtime>6500||$data[ticket]==NULL){
			$tmp= $GLOBALS['pf']->get_data($url);
      	  	  $r_data[addtime] = time();
			$tmp_arr =$tmp;
		}else {
			$tmp_arr =$tempdata;
      		  $r_data[addtime] = $tempdata['addtime'];
		}

		$data[ticket] = $tmp_arr['ticket'];

		$r_data[ticket] = $tmp_arr['ticket'];
		$r_data[addtime] = time();
		$r_data[appid] = $this->_appid;
		$GLOBALS['pf']->write_static_cache($cache_name, $r_data);

		$data[jsapi_ticket] = $data[ticket];
		$data[appid] = $this->_appid;

		$string1 = "jsapi_ticket=" . $data[jsapi_ticket] . "&noncestr=" . $data[noncestr] . "&timestamp=" . $data[timestamp] . "&url=" . $data[url];

		$signature = sha1($string1);

		$r[appid] = $this->_appid;
		$r[signature] = $signature;
		return $r;
	}

	//群发消息
	public function send_new($data){
		$access_token=get_base_access_token($data);

		$url=''.$this->_send_new.''.$access_token;

		$GLOBALS['pf']->post_data($url,$data);

	}

	//创建二维码
	public function get_ticket_url($config,$data){
		//创建二维码

		$cache=$this->get_base_access_token($config);

		$url=''.$this->_get_ticket.$cache[access_token];

		$result=$GLOBALS['pf']->post_data($url,$data);
		//var_dump($result);
		if(!$result[ticket]){
			var_dump($result);
		}


		$ticket=$result[ticket];
		$ticket=UrlEncode($ticket);
		$url=''.$this->_get_ticket_url.$ticket;

		return $url;
	}
	//基本没用的更新token
	public function refresh_token($refresh_token){
		//获取缓存文件中的get_access_token
		//require(dirname(__FILE__) . '/caches_token.php');
		if($token){
		}else{
			$url=''
				.$this->_url_refresh_token
				.'appid='.$this->_appid
				.'&grant_type=refresh_token'
				.'&refresh_token='.$refresh_token;
			$data=$GLOBALS['pf']->get_data($url);
			$access_token=$data['access_token'];
			return $access_token;
		}
	}
 
	//推送模板消息
	public function post_msg($json){
		//获取token 
		$data=$this->get_base_access_token();
		$url=$this->_post_msg
			.'access_token='.$data[access_token];
		$data=$GLOBALS['pf']->post_data($url,$json);
		//echo $url;
		//var_dump($json);
		// $data[status]=10001;失败
		return $data;
		 
	}
//基础支持的获取用户信息
	public function get_base_user_info($openid,$config){
		
		$cache=$this->get_base_access_token($config);
		 
		$access_token=$cache['access_token'];
		if(!$access_token){
			return $cache;
		}
		$url=''
			.$this->_url_base_get_user_info
			.'access_token='.$access_token
			.'&openid='.$openid
			.'&lang=zh_CN';
		$data=$GLOBALS['pf']->get_data($url);
	
		
		return $data;
		 
	}
	//链接网页的获取用户信息
	public function get_user_info($openid,$access_token){
		 
		$url=''
			.$this->_url_web_get_user_info
			.'access_token='.$access_token
			.'&openid='.$openid
			.'&lang=zh_CN';
		$data=$GLOBALS['pf']->get_data($url);

		return $data;

	}
	
	//基础支持的获取用户信息 多个应用
	public function get_base_user_info_for_unionid($openid,$config){
		
		$cache=$this->get_base_access_token($config);
		 
		$access_token=$cache['access_token'];
		if($_REQUEST[t]){
			var_dump($access_token);
		}
 
		$url=''
			.$this->_url_base_get_user_info_for_unionid
			.'access_token='.$access_token
			.'&openid='.$openid
			.'&lang=zh_CN';
		$data=$GLOBALS['pf']->get_data($url);
	
		
		return $data;

	}
//以下是客服
//获取在线客服接待信息
	public function get_online_kf($data){
		$cache=$this->get_base_access_token($data);
		$access_token=$cache['access_token'];
		$url=''.$this->_get_online_kf_lists.$access_token;

		$data=$GLOBALS['pf']->get_data($url);
		return $data;
	}
	
//创建会话
	public function new_con_kf($data,$kf){

		$cache=$this->get_base_access_token($data);
		$access_token=$cache['access_token'];
		$url=$this->_create_con_kf.$access_token;
		$openid=$kf['openid'];
		$kf_account=$kf['kf_account'];

		$text=$kf['text'];
		$test=' {
      			"openid":"'.$openid.'",
      			"kf_account" : "'.$kf_account.'",
     			 "text" : "'.$text.'"
				 }';

		$data=$GLOBALS['pf']->post_data($url,$test);

		return $data;
	}
	//关闭会话
	public function close_con_kf($config,$openid,$kf_account,$text){
		$cache=$this->get_base_access_token($config);
		$access_token=$cache['access_token'];
		$data=
			'{
		"openid":"'.$openid.'",
		"kf_account" : "'.$kf_account.'",
		"text" : "'.$text.'"
		}';

		$url=$this->_close_con_kf.$access_token;
		$result=$GLOBALS['pf']->post_data($url,$data);

		return $result;
	}
	//获取客户会话状态
	public function get_user_con_status($data,$oepnid){
		$cache=$this->get_base_access_token($data);
		$access_token=$cache['access_token'];
		$url=$this->_get_user_con_status."access_token=".$access_token."&openid=".$oepnid;
		$result=$GLOBALS['pf']->get_data($url);
	
		return $result;
	}
	//获取聊天记录
	public function get_con_text($config,$openid){

		$cache=$this->get_base_access_token($config);
		$access_token=$cache['access_token'];
		$url=$this->_get_con_text.$access_token;
		$endtime=time();
		$starttime=$endtime-3;
		$data='{
			    "endtime" : '.$endtime.',
			    "openid" : "'.$openid.'",
			    "pageindex" : 1,
			    "pagesize" : 100,
			    "starttime" : '.$starttime.'
			}';
		$result=$GLOBALS['pf']->post_data($url,$data);
		return $result;
	}

	//客服回复文本信息
	public function send_message($config,$text,$openid){
		$cache=$this->get_base_access_token($config);
		$access_token=$cache['access_token'];
		$url=$this->_send_kf_text.$access_token;
		$data='{
    			"touser": "'.$openid.'", 
			    "msgtype": "text", 
			    "text": {
			        "content": "'.$text.'"
			    	}
		}';
		$result=$GLOBALS['pf']->post_data($url,$data);
		$res[result]=$result;
		$res[data]=$data;
		return $res;

	}
	//下载图片
	function downloadWeixinFile($serverId,$config)
	{
		$cache=$this->get_base_access_token($config);
		$access_token=$cache['access_token'];
		$url="http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$access_token."&media_id=".$serverId."";
		// echo $url;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);    
		curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		$imageAll = array_merge(array('header' => $httpinfo), array('body' => $package)); 
		
		//$media = array_merge(array('mediaBody' => $package), $httpinfo);
       // var_dump($media["httpinfo"]);
        //求出文件格式
       // preg_match('/\w\/(\w+)/i', $media["content_type"], $extmatches);
		$fileExt = $extmatches[1];
		$fileExt ="jpg";
		//$tmp=json_decode($package,true);
		//var_dump($imageAll);
		 
		 //var_dump($imageAll);
		$fliedir="Uploads/";			
		mkdir($fliedir);			
		$fliedir=$fliedir."weixin/";			
		mkdir($fliedir);		
		if(!$_SESSION[user_id]){
			$_SESSION[user_id]=0;
		}			
		$fliedir=$fliedir."".$_SESSION[user_id]."/";			
		mkdir($fliedir);	
		$cur_time=uniqid()."-".time();
		$imgname=$_SESSION[user_id]."-".$cur_time."-".rand(1000,9999).".".$fileExt;
		//$filename=$fliedir."".$_SESSION[user_id]."-".time()."-".md5($serverId,16).".jpg";
		$filename=$fliedir."".$imgname;
		 
		//echo $filename;
	 
		$data[filename]=$filename;
		$data[imgname]=$imgname;
		$data[fileExt]=$fileExt;
		 
		$local_file = fopen($filename, 'w');
		if (false !== $local_file){
			if (false !== fwrite($local_file, $imageAll[body])) {
				fclose($local_file);
			}
		}
		 
	 	return $data;
	}
}


?>