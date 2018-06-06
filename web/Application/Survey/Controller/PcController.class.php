<?php
namespace Survey\Controller;
use Think\Controller;
class PcController extends Controller {

	public function __construct(){
		parent::__construct();
		// $this->user_id=$_SESSION[user][userid]=3;
		$this->logic=new \Survey\Logic\PcSurveyLogic;
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
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

	public function index(){
		if(!$_REQUEST[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->survey=M("survey")->where("id={$_REQUEST[id]}")->find();
		if($this->survey[type] != 3){
			$this->showmsg("活动类型不符合",-1);
		}
		$this->display();
	}

	#开始pc测试
	public function start_survey(){

		// $agentid=M("partment")->join(" INNER JOIN tl_survey ON tl_survey.partment=tl_partment.id")->where("tl_survey.id={$_REQUEST[id]}")->getField("app_id");
		// $this->weixin_news->set_agentid($agentid);
		// $res=$this->weixin_news->publish_survey($_REQUEST[id],$userIds,1);

		$status=$_REQUEST["status"];
		$pc_status=M("survey")->where("id={$_REQUEST[id]}")->getField("pc_status");
		if(empty($pc_status)){
			$this->logic->set_question($_REQUEST[id]);
		}
		M("survey")->where("id={$_REQUEST[id]}")->setField("pc_status",$status);
		if($status==1){
			M("survey_pcquestion")->where("survey_id={$_REQUEST[id]} AND sort=1")->setField("status",$status);
		}
		$this->showmsg("测试开始","survey.php?c=pc&a=show_survey&id=".$_REQUEST[id]."&next=1");
	}

	#展示测试题目
	public function show_survey(){
		$surveyId=$_REQUEST[id];
		$sort=$_REQUEST[next];
		$question=M("survey_pcquestion")->where("survey_id={$surveyId} AND sort={$sort}")->find();
		if(!$question){
			$this->showmsg("已没有题目了",1);
		}
		M("survey_pcquestion")->where("survey_id={$surveyId} AND sort={$sort}")->setField("status",1);

		$this->data=$this->logic->get_question($surveyId,$sort);
		// dump($this->data);exit;

		// $survey=M("survey")->where("id={$surveyId}")->find();
		// $agentid=M("partment")->where("id={$survey[partment]}")->getField("app_id");
  //       $weixinMsgLogic = new \Weixin\Logic\WeixinMsgLogic($agentid);
  //       $msg="您参加的【".$survey[title]."】测试活动，题目【".$this->data[title]."】现在已开始,".'<a href="http://'.$_SERVER['HTTP_HOST'].'/youbangqy/web/survey.php?c=index&a=pc_survey&id='.$surveyId.'&sort='.$sort.'">点此进行答题</a>';
  //       $userIds=M("survey_apply")->where("survey_id={$surveyId}")->getField("user_id",true);
        // $res=$weixinMsgLogic->send_text($userIds,$msg);
        // dump($res);exit;

		$this->answer=$this->logic->get_answerCount($this->data[option],$surveyId,$question[question_id]);
		$this->display();
	}

	public function ajax_data(){
		$data=$this->logic->get_data($_REQUEST[id],$_REQUEST[questionId]);
		echo $data;
	}
}