<?php
namespace Weixin\Logic;
use Think\Model; 
class WeixinMsgLogic {

	public $agentid=0;


	public function __construct($agentid=0){	
		$Function=new \Base\Model\FunctionServiceModel();
		$GLOBALS['pf']=$Function;
		$this->Get=new \Base\Model\GetServiceModel();

		$wxdata[appid]="wxf64ba63493455027";
		$wxdata[secret]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";
		$wxdata[agentid]=$agentid;

		$this->Get->config($wxdata);
		if(empty($wxdata[agentid])){
			$this->agentid=$agentid;
		}else{
			$this->agentid=$wxdata[agentid];
		}
		
	}

	public function set_agentid($agentid){
		$this->agentid=$agentid;
	}

	/**
	 * 发送普通消息
	 * $userid array
	 */
	public function send_text($useIds,$content){
		$touser=implode("|",$useIds);
		$data=array(
			"touser"=>$touser,
			"toparty"=>"@all",
   			"totag"=>"@all",
			"msgtype"=>"text",
			"agentid"=>$this->agentid,
			"text"=>array("content"=>$content),
			"safe"=>"0"
			);

		$res=$this->Get->send_message(json_encode($data,JSON_UNESCAPED_UNICODE));
		return $res;
	}

}