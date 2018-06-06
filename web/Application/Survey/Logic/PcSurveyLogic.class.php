<?php
namespace Survey\Logic;
use Think\Model;

class PcSurveyLogic {

	public function __construct(){	
		$this->db= new \Survey\Model\PublicModel();
	}

	public function set_question($surveyId){
		$db=M("survey_pcquestion");
		$questions=M("survey")->where("id={$surveyId}")->getField("question_ids");
		foreach (explode(",",$questions) as $key => $value) {
			$dataList=array("survey_id"=>$surveyId,"question_id"=>$value,"sort"=>$key+1);
			$db->add($dataList);
		}
	}

	public function get_question($surveyId,$sort){
		$questionId=M("survey_pcquestion")->where("survey_id={$surveyId} AND sort={$sort}")->getField("question_id");
		$data=M("survey_question")->where("id={$questionId}")->field("id,title")->find();
		$option=M("survey_option")->where("question_id={$questionId}")->order("sort asc")->select();
		$data[option]=$option;
		return $data;
	}

	#获取答题的答案条形图
	public function get_answerCount($option,$surveyId,$questionId=""){
		$db=M("survey_answer");
		$data=array();
		$all=$db->where("survey_id={$surveyId} AND question_id={$questionId}")->count();
		foreach ($option as $key => $value) {
			$count=$db->where("survey_id={$surveyId} AND option_id={$value[id]}")->count();
			$rate=$count/$all*100;
			$rate=round($rate,2);
			$answer="答案".$value[label];
			$data[]=array("text"=>$answer,"value"=>$count,"rate"=>$rate);
		}
		return json_encode($data,JSON_NUMERIC_CHECK);
	}

	public function get_data($surveyId,$questionId){
		$db=M("survey_answer");
		$option=M("survey_option")->where("question_id={$questionId}")->order("sort asc")->select();

		$all=$db->where("survey_id={$surveyId} AND question_id={$questionId}")->count();

		$data=array();
		foreach ($option as $key => $value) {
			$count=$db->where("survey_id={$surveyId} AND option_id={$value[id]}")->count();
			$rate=$count/$all*100;
			$rate=round($rate,2);
			$answer="答案".$value[label];
			$data[]=array("text"=>$answer,"value"=>$count,"rate"=>$rate);
		}
		return json_encode($data,JSON_NUMERIC_CHECK);
	}
}