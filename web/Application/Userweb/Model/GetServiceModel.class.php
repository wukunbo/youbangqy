<?php

namespace Userweb\Model;

use Think\Model;

/**
 * 微信自动登录类
 * ============================================================================
 * * 版权所有深圳市托略计算机有限公司
  网址地址 http://mp.weixin.qq.com/wiki/index.php?title=%E7%BD%91%E9%A1%B5%E6%8E%88%E6%9D%83%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF
 * ============================================================================

 */
class GetServiceModel extends Model {
    /* 用户同意授权，获取code的链接

      完整如下：	https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
      #wechat_redirect 无论直接打开还是做页面302重定向时候，必须带此参数
      如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。若用户禁止授权，则重定向后不会带上code参数，仅会带上state参数redirect_uri?state=STATE
      code说明 ：
      code作为换取access_token的票据，每次用户授权带上的code将不一样，code只能使用一次，5分钟未被使用自动过期。
     */

    public $_url_get_code = "https://open.weixin.qq.com/connect/oauth2/authorize?";
    //授权使用的获取ACCESS_TOKEN及openid的网址
    
    public $_url_get_partment = "https://qyapi.weixin.qq.com/cgi-bin/department/list?";
    //拉取部门列表
    
    public $_url_get_contacts = "https://qyapi.weixin.qq.com/cgi-bin/user/list?";
    //拉取部门下的成员信息 
    
    public $_url_get_agent = "https://qyapi.weixin.qq.com/cgi-bin/agent/list?";
    //获取企业号应用列表
    
    public $_url_send_message = "https://qyapi.weixin.qq.com/cgi-bin/message/send?";
    

    public $_url_get_access_token = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?";
    //基础接入的ACCESS_TOKEN
    public $_url_get_access_token2 = "https://api.weixin.qq.com/cgi-bin/token?";
    //刷新 access_token

    public $_url_refresh_token = "https://api.weixin.qq.com/sns/oauth2/refresh_token?";
    //授权拉取用户资料

    public $_url_get_user_info = "https://api.weixin.qq.com/sns/userinfo?";
    //非授权拉去用户资料
    public $_url_get_user_info2 = "https://api.weixin.qq.com/cgi-bin/user/info?";
    //APPID;	
    public $_appid = "";
    //secret;	
    public $_secret = "";
    //默认值
    public $_grant_type = "authorization_code ";
    //回调网址，需要urlencode编码
    public $_redirect_uri = "";
    /*
      应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     */
    //public $_scope			=	"snsapi_base";	
    public $_scope = "snsapi_userinfo";
    /*
      重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     */
    public $_state = "STATE";

    //初始化相关值 数组形式
    public function config($data) {
        $this->_appid = trim($data["appid"]);
        $this->_scope = trim($data["scope"]);
        $this->_state = trim($data["state"]);
        $this->_secret = trim($data["secret"]);
		$this->_agentid = trim($data["agentid"]);
        $this->_redirect_uri = urlencode($data["redirect_uri"]);
    }

    //拼接获取CODE的网址
    public function get_code_url() {
        $str = ''
                . $this->_url_get_code
                . 'appid=' . $this->_appid
                . '&redirect_uri=' . $this->_redirect_uri
                . '&response_type=code&scope=' . $this->_scope . '&state=' . $this->_state . '#wechat_redirect';
        return $str;
    }

    // 第一步，获取CODE，返回  $_REQUEST["code"]
    public function get_code() {
        $url = $this->get_code_url();
        //p($url);exit;
        header("Location: " . $url . "");
        exit;
    }

    //接受CODE后，去获取 openid，及access_token
    /*
      {
      "access_token":"ACCESS_TOKEN",
      "expires_in":7200,
      "refresh_token":"REFRESH_TOKEN",
      "openid":"OPENID",
      "scope":"SCOPE"
      }
      错误信息
      {"errcode":40029,"errmsg":"invalid code"}
     */
	public function get_wx_userid($code){
		//https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=ACCESS_TOKEN&code=CODE&agentid=AGENTID
		$url='https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?'
			.'access_token='.$this->get_access_token()
			.'&agentid='.$this->_agentid
			.'&code='.$code;
		$data=$GLOBALS['pf']->get_data($url);
		//echo $url;
		//$openid=$data;
		return $data;
	}

  //获取部门列表
  public function get_wx_partment(){
    $url=''
      .$this->_url_get_partment
      .'access_token='.$this->get_access_token();
    $data=$GLOBALS['pf']->get_data($url);
    return $data[department];
  }

  //获取部门下的成员
  public function get_wx_contacts($partment_id){
    $url=''
      .$this->_url_get_contacts
      .'access_token='.$this->get_access_token()
      .'&department_id='.$partment_id
      .'&fetch_child=1&status=0';
    $data=$GLOBALS['pf']->get_data($url);
    return $data[userlist];
  }

  //获取企业应用
  public function get_agent(){
    $url=''
      .$this->_url_get_agent
      .'access_token='.$this->get_access_token();
    $data=$GLOBALS['pf']->get_data($url);
    return $data[agentlist];
  }

  //发送消息
  public function send_message($data){
     $url=''
      .$this->_url_send_message
      .'access_token='.$this->get_access_token();

     $data=$GLOBALS['pf']->post_data($url,$data);
     return $data;
  }

    public function get_access_token() {
        //获取缓存文件中的get_access_token

        if ($token) {
            
        } else {
            $url = ''
                    . $this->_url_get_access_token
                    . 'corpid=' . $this->_appid
                    . '&corpsecret=' . $this->_secret;
            $data = $GLOBALS['pf']->get_data($url);
			//var_dump($data);
            $access_token = $data['access_token'];
            //$access_token = $data;
            return $access_token;
        }
    }

    /* 刷新refresh_token  https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
      {
      "access_token":"ACCESS_TOKEN",
      "expires_in":7200,
      "refresh_token":"REFRESH_TOKEN",
      "openid":"OPENID",
      "scope":"SCOPE"
      }
      {"errcode":40029,"errmsg":"invalid code"}
     */

    public function refresh_token($refresh_token) {
        //获取缓存文件中的get_access_token
        //require(dirname(__FILE__) . '/caches_token.php');
        if ($token) {
            
        } else {
            $url = ''
                    . $this->_url_refresh_token
                    . 'appid=' . $this->_appid
                    . '&grant_type=refresh_token'
                    . '&refresh_token=' . $refresh_token;
            $data = $GLOBALS['pf']->get_data($url);
            $access_token = $data['access_token'];
            return $access_token;
        }
    }

    /*
      获取用户信息
      https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
      {
      "openid":" OPENID",
      " nickname": NICKNAME,
      "sex":"1",
      "province":"PROVINCE"
      "city":"CITY",
      "country":"COUNTRY",
      "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
      "privilege":[
      "PRIVILEGE1"
      "PRIVILEGE2"
      ]
      }
      {"errcode":40003,"errmsg":" invalid openid "}
     */

    public function get_user_info($openid, $access_token) {


        $url = ''
                . $this->_url_get_user_info
                . 'access_token=' . $access_token
                . '&openid=' . openid
                . '&lang=zh_CN';
        $data = $GLOBALS['pf']->get_data($url);

        return $data;
    }

}
?>

