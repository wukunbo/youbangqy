<?php
namespace Survey\Controller;
use Think\Controller;
class IndexController extends BaseController {

	public function __construct(){
		parent::__construct();
		$this->user_id=$_SESSION[wx_userid];
		$this->user_openid=$_SESSION["openId"];

		$this->logic= new \Survey\Logic\ResearchLogic;
	}


	public function show(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->data=$this->logic->survey_detail($request[id]);
		$this->display();
	}

	public function index(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}

		#是否对外开放
		$open_status=M("survey")->where("id={$request[id]}")->getField("open_status");

    	if($open_status != 2){

    		$this->data=$this->logic->detail($request[id],$this->user_id);

    		if(!$this->data[user]){
    			$this->showmsg("很抱歉，您不在本次活动名单以内，请与主办方联系，谢谢！",-1);
    		}
    	}else{
    		if($this->user_id){
    			$is=M("survey_apply")->where("survey_id={$request[id]} AND user_id='{$this->user_id}'")->find();
    			// echo M("survey_apply")->getLastsql();exit;
    			if(!$is){
    				$contact=M("contacts")->where("wx_userid={$this->user_id}")->find();
    				$dataList=array("survey_id"=>$request[id],"user_id"=>$this->user_id,"truename"=>$contact[name],"partment"=>$contact[partment],"partment_id"=>$contact[partment_id],"addtime"=>time());
    				M("survey_apply")->add($dataList);
    				$user=array("partment"=>$contact[partment],"user_id"=>$this->user_id,"truename"=>$contact[name]);
    				
    				// dump($user);exit;
    			}
    		}
    		if($this->user_openid){
    			$is=M("survey_apply")->where("survey_id={$request[id]} AND openid='{$this->user_openid}'")->find();
    			if(!$is){
    				$dataList=array("survey_id"=>$request[id],"openid"=>$this->user_openid,"open"=>2,"addtime"=>time());
    				M("survey_apply")->add($dataList);
    			}
    		}

    		$this->data=$this->logic->detail($request[id],$this->user_id);
    	}


		
		$this->total=count($this->data[question]);

		$now=time();

		if($this->data[survey][start_time] > $now){

    		$this->showmsg("该测试还未开始",-1);
    	}
    	if($this->data[survey][end_time] < $now){
			$this->showmsg("该测试活动已结束",-1);
    	}
    	if(empty($this->data[survey][status])){

			$this->showmsg("该测试活动还未发布",-1);
    	}
		
		// if(!$this->data[user]){
		// 	$this->showmsg("你无权参加该活动",-1);
		// }
		// 
    	

		if($this->data[survey][repeat] == 1){
			if($this->user_id){
				$is=M("survey_log")->where("user_id={$this->user_id} AND survey_id={$request[id]}")->find();
				if($is){
					$this->showmsg("你已经参加了这次测试活动了","survey.php?c=index&a=can_survey&id=".$request[id]);
				}
			}
			if($this->user_openid){
				$is=M("survey_log")->where("openid='{$this->user_openid}' AND survey_id={$request[id]}")->find();
				if($is){
					$this->showmsg("你已经参加了这次测试活动了","survey.php?c=index&a=can_survey&id=".$request[id]);
				}
			}
			
		}

		if($this->user_openid){
			$this->type_value=2;
		}else{
			$this->type_value=1;
		}

		// dump($this->data[user]);exit;

		$this->display();
	}

	#已参加用户的显示
	public function can_survey(){
		$data=$this->logic->detail($_REQUEST[id],$this->user_id);
		if($this->user_id){
			$data[sum_point]=M("survey_log")->where("user_id={$this->user_id} AND survey_id={$_REQUEST[id]}")->getField("sum_point");
		}
		if($this->user_openid){
			$data[sum_point]=M("survey_log")->where("openid='{$this->user_openid}' AND survey_id={$_REQUEST[id]}")->getField("sum_point");

		}
		$this->data=$data;
		$this->is_repeat=M("survey")->where("id={$_REQUEST[id]}")->getField("repeat");
		$this->display("submit_answer");
	}

	public function submit_answer(){
		$survey_id=$_REQUEST[survey_id];
		$post=$_REQUEST[option];
		if(!$survey_id){
			$this->showmsg("非法输入",-1);
		}

		$this->is_repeat=M("survey")->where("id={$survey_id}")->getField("repeat");
		$this->survey_type=M("survey")->where("id={$survey_id}")->getField("type");
		$this->open_status=M("survey")->where("id={$survey_id}")->getField("open_status");

		// dump($_REQUEST);exit;
		if($this->user_id){
			$this->data=$this->logic->submit_answer($survey_id,$post,$this->user_id);
			if($this->survey_type){
				$this->wrong=$this->logic->wrong_log($survey_id,$this->user_id);
			}
			
		}else{
			// dump($_REQUEST);exit;
			$dataList=array("value1"=>$_REQUEST[post1][value1],"value2"=>$_REQUEST[post1][value2],"value3"=>$_REQUEST[post1][value3]);
			M("survey_apply")->where("survey_id={$survey_id} AND openid='{$this->user_openid}'")->save($dataList);

			$this->data=$this->logic->submit_answer_op($survey_id,$post,$this->user_openid);
			if($this->survey_type){
				$this->wrong=$this->logic->wrong_log($survey_id,$this->user_openid);
			}

			$this->open_value=M("survey_apply")->where("survey_id={$survey_id} AND openid='{$this->user_openid}'")->field("value1,value2,value3")->find();
		}

		// dump($this->wrong);exit;
		

		#添加建议
		if(!empty($_REQUEST[advice]) && isset($_REQUEST[advice])){
			$this->logic->save_advice($this->user_id,$survey_id,$_REQUEST[advice]);
		}

		if($this->open_status==1){
			if(!$this->data[user]){
				$this->showmsg("很抱歉，您不在本次活动名单以内，请与主办方联系，谢谢！",-1);
			}
		}
		
		$this->display();
	}

	public function pc_survey(){
		$surveyId=$_REQUEST[id];
		$sort=$_REQUEST[sort];
		$survey=M("survey_pcquestion")->where("survey_id={$surveyId}")->find();
		if(!$survey){
			$this->showmsg("该测试还未开始",1);
		}
		$question=M("survey_pcquestion")->where("survey_id={$surveyId} AND sort={$sort}")->find();
		if(!$question){
			$this->showmsg("已无题目",-1);
		}
		if($question[status] != 1){
			$this->showmsg("下一题还未开始","survey.php?c=index&a=pc_survey&id=".$surveyId."&sort=".$sort);
		}
		$this->data=$this->logic->pc_survey($surveyId,$question[question_id],$this->user_id);
		$this->display();
	}

	public function submit_pcanswer(){
		$surveyId=$_REQUEST[id];
		$sort=$_REQUEST[sort]+1;#下一题
		$post=$_REQUEST[option];
		$arr=explode("-",$post);
		$question_id=$arr[0];
		$option_id=$arr[1];
		if($this->user_id){
			$is=M("survey_answer")->where("user_id={$this->user_id} AND survey_id={$surveyId} AND question_id={$question_id}")->find();
			$dataList=array('user_id'=>$this->user_id,'question_id'=>$question_id,'option_id'=>$option_id,'addtime'=>time(),'survey_id'=>$surveyId);
			if(!$is){
				M("survey_answer")->add($dataList);
			}else{
				M("survey_answer")->where("user_id={$this->user_id} AND survey_id={$surveyId} AND question_id={$question_id}")->save($dataList);
			}
		}

		if($this->user_openid){
			// echo $this->user_openid;exit;
			$is=M("survey_answer")->where("openid='{$this->user_openid}' AND survey_id={$surveyId} AND question_id={$question_id}")->find();
			$dataList=array('openid'=>$this->user_openid,'question_id'=>$question_id,'option_id'=>$option_id,'addtime'=>time(),'survey_id'=>$surveyId);
			if(!$is){
				M("survey_answer")->add($dataList);
			}else{
				M("survey_answer")->where("openid='{$this->user_openid}' AND survey_id={$surveyId} AND question_id={$question_id}")->save($dataList);
			}
		}
	
		// $this->showmsg("答案已提交，下一题中","survey.php?c=index&a=pc_survey&id=".$surveyId."&sort=".$sort);
		$this->showmsg("答案已提交",-1);
	}
    
}