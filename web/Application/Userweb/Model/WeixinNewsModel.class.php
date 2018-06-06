<?php
namespace Userweb\Model;
use Think\Model;


class WeixinNewsModel extends Model
{
	public $agentid;

	public function __construct($wxdata,$agentid=0){	
		$Function=new \Base\Model\FunctionServiceModel();
		$GLOBALS['pf']=$Function;
		$this->Get=new \Base\Model\GetServiceModel();
		if(empty($wxdata)){
			$wxdata[appid]="wxf64ba63493455027";
			$wxdata[secret]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";
		}
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

	/**
	 * 发送图文消息
	 * $userid array
	 */
	public function send_news($useIds,$articles){
		$touser=implode("|",$useIds);
		$data=array(
			"touser"=>$touser,
			"toparty"=>"@all",
   			"totag"=>"@all",
			"msgtype"=>"news",
			"agentid"=>$this->agentid,
			"news"=>array("articles"=>$articles),
			);
		$res=$this->Get->send_message(json_encode($data,JSON_UNESCAPED_UNICODE));
		return $res;
	}


	#推送活动信息
	public function publish_activity($id,$userIds=""){
		
		$activity=M("activity")->where("id={$id}")->field("title,intro,img_thumb,open_sign,only_my,wx_userid")->find();
		
		if(empty($userIds)){
			if($activity[open_sign]==2 && $activity[only_my]==2){
				$userIds=array($activity[wx_userid]);
			}else{
				$join=" INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_activity_apply.user_id ";
				$userIds=M("activity_apply")->where("tl_activity_apply.activity_id={$id}")->join($join)->getField("user_id",true);
				// echo M("activity_apply")->getLastsql();exit;
			}
			
		}

		// dump($userIds);exit;
		
		$articles[]=array(
			"title"=>$activity[title],
			"description"=>trim($activity[intro]),
			"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/activity.php?id=".$id,
			"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$activity[img_thumb]
		);
		// dump($articles);exit;
		$res=$this->send_news($userIds,$articles);
		return $res;
	}


	#推送测试活动
	public function publish_survey($id,$userIds="",$all=''){
		
		if(empty($userIds)){
			$join=" INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_apply.user_id ";
			$userIds=M("survey_apply")->where("survey_id={$_REQUEST[id]}")->join($join)->getField("user_id",true);
		}
		

		$survey=M("survey")->where("id={$_REQUEST[id]}")->field("title,type,intro,image,user_id")->find();

		//互动答题型仅推送给创建人
		if($survey[type]==3 && empty($all)){
			$wx_userid=M("user_child")->where("id={$survey[user_id]}")->getField("wx_userid");
			// echo M("user_child")->getLastsql();exit;
			$userIds=array($wx_userid);
			// dump($userIds);exit;
		}

		$url='http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/survey.php?c=index&a=show&id=".$id;
		if($survey[type] == 3){
			$url='http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/survey.php?c=index&a=pc_survey&sort=1&id=".$id;
		}

		$articles[]=array(
			"title"=>$survey[title],
			"description"=>trim($survey[intro]),
			"url"=>$url,
			"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$survey[image]
		);
		$res=$this->send_news($userIds,$articles);
		return $res;
	}

	#推送投票信息
	public function publish_vote($id,$userIds=""){
		if(empty($userIds)){
			$join=" INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_vote_apply.user_id ";
			$userIds=M("vote_apply")->where("vote_id={$id}")->join($join)->getField("user_id",true);
		}

		$vote=M("vote")->where("id={$id}")->field("title,image_thumb,type,is_pc")->find();

		$articles[]=array(
			"title"=>$vote[title],
			"description"=>$vote[title]."活动发布了",
			"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/vote.php?c=index&a=show&id=".$id,
			"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$vote[image_thumb]
		);

		$res=$this->send_news($userIds,$articles);
		return $res;
	}

	#推送抽奖信息
	public function publish_lottery($id,$userIds=""){
		if(empty($userIds)){
			$join=" INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_apply.user_id ";
			$userIds=M("lottery_apply")->where("lottery_id={$id}")->join($join)->getField("user_id",true);
		}

		$lottery=M("lottery")->where("id={$id}")->field("title,back_img")->find();

		$articles[]=array(
			"title"=>$lottery[title],
			"description"=>"抽奖活动发布啦",
			"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/lottery.php?c=lottery&a=index&id=".$id,
			"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$lottery[back_img]
		);

		$res=$this->send_news($userIds,$articles);
		return $res;
	}

	#推送文章
	public function publish_article($id,$userIds=""){

		// $userIds=array("13570577328");

		$article=M("tlwx_msg_content")->where("id={$id}")->field("title,summary,image_thumb")->find();

		$articles[]=array(
			"title"=>$article[title],
			"description"=>$article[summary],
			"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/cms.php?c=cms&a=detail&id=".$id,
			"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$article[image_thumb]
		);

		$res=$this->send_news($userIds,$articles);
		return $res;
	}

}

?>