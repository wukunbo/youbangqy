<?php
namespace Survey\Logic;
use Think\Model;
class ResearchLogic {

	public function __construct(){	
		$this->db= new \Survey\Model\PublicModel();
	}

	//题目列表
	public function question_lists($array){
		$array[table]='survey_question';
		$array[join]="LEFT JOIN tl_survey_question_sort ON tl_survey_question_sort.question_id=tl_survey_question.id ";
		$array[field]="tl_survey_question.*";
		if(empty($array[order])){
			$array[order]="tl_survey_question_sort.sort asc ";
		}
		
		$data=$this->db->Page($array);

		// dump($data);exit;

		//获取选项和分值
		$option=M('survey_option');
		$banks=M("question_banks");
		for($i=0;$i<count($data[content]);$i++){
			$question_id=$data[content][$i][id];
			$detail=$option->where("question_id='$question_id'")->order('sort asc')->select();
			for($j=0;$j<count($detail);$j++){
				$data[content][$i][option][$j][label]=$detail[$j][label];
				$data[content][$i][option][$j][option_title]=$detail[$j][title];
				$data[content][$i][option][$j][option_point]=$detail[$j][point];
			}
			#所属题库
			$category=$banks->where("question_id={$question_id}")->join("INNER JOIN tl_question_category ON tl_question_category.id=tl_question_banks.category_id ")->getField("tl_question_category.title");
			// echo $banks->getLastsql();exit;
			$data[content][$i][tiku]=$category;

			$data[content][$i][xulie]=$i+1;
		}
		return $data;
	}

	public function add_question($id){
		$survey_question=M('survey_question');
		$survey_option=M('survey_option');
		$data[content]=$survey_question->where("id='$id'")->find();
		$detail=$survey_option->where("question_id='$id'")->order('sort asc')->select();
		for($j=0;$j<count($detail);$j++){
			$data[content][option][$j][label]=$detail[$j][label];
			$data[content][option][$j][title]=$detail[$j][title];
			$data[content][option][$j][point]=$detail[$j][point];
			$data[content][option][$j][sort]=$detail[$j][sort];
		}
		return $data;
	}

	#添加题库
	public function add_category_action($post){
		$array[table]='question_category';
		$array[data]=$post;
		if(empty($post[id])){
			$array[data][addtime]=time();
			$res=$this->db->Add($array);
		}else{
			$array[where]="id='$post[id]'";
			$res=$this->db->Save($array);
		}
		return $res;
	}

	public function category_detail($id){
		$array[table]="question_category";
		$array[where]="id={$id}";
		$data=$this->db->Find($array);
		return $data;
	}

	#题库列表
	public function question_category(){
		$search["table"]="question_category";
		$search["order"]="addtime desc";

		$data=$this->db->Page($search);
		return $data;
	}

	//添加题目或添加到题库
	public function add_question_action($id,$post,$categoryid=""){
		$array[table]='survey_question';
		$survey_option=M('survey_option');
		$array[data]=$post[question];

		if(!$id){
			$array[data][addtime]=time();
			$res=$this->db->Add($array);
			if(!empty($categoryid)){
				$dataList=array("category_id"=>$categoryid,"question_id"=>$res,"addtime"=>time());
				$arr[table]="question_banks";
				$arr[data]=$dataList;
				$this->db->Add($arr);
			}
		}else{
			$array[where]="id='$id'";
			$res=$this->db->Save($array);
			$survey_option->where("question_id='$id'")->delete();//删除option表里面的数据再重写
		}
		$list=$post[option];
		// dump($list);exit;
		$title=array_filter($list[option_title]);
		// dump($title);
		// echo count($title);
		// exit;

		for($i=0;$i<count($title);$i++){
			$option[label]=$list[option_label][$i];
			$option[title]=$list[option_title][$i];
			$option[point]=$list[option_point][$i];
			$option[sort]=$list[sort][$i];
			
			//$option[sort]=$i;
			if(!$id){
				$option[question_id]=$res;
				$res2=$survey_option->add($option);
				// echo $survey_option->getLastsql();
			}else{
				$option[question_id]=$id;
				$res2=$survey_option->add($option);
			}

			
		}
		// exit;
		return $res;
	}

	#查看题库题目列表
	public function show_category($categoryid){
		$db=M("question_banks");
		$subsql=$db->where("category_id={$categoryid}")->field("question_id")->order("sort asc")->select(false);
		$array[where]="tl_survey_question.id IN ({$subsql})";
		$array[order]="tl_survey_question.id asc ";
		$data=$this->question_lists($array);
		foreach ($data[content] as $key => &$value) {
			$value[sort]=$db->where("category_id={$categoryid} AND question_id={$value[id]}")->getField("sort");
		}
		return $data;
	}

	//添加测试调查
	public function add_survey_action($post){
		$array[table]='survey';
		$post[start_time]=strtotime($post[start_time]);
		$post[end_time]=strtotime($post[end_time]);
		$array[data]=$post;
		if(empty($post[id])){
			$array[data][addtime]=time();
			$res=$this->db->Add($array);
		}else{
			$array[where]="id='$post[id]'";
			$res=$this->db->Save($array);
		}
		return $res;
	}

	public function survey_detail($id){
		$array[table]="survey";
		$array[where]="id={$id}";
		$data=$this->db->Find($array);

		//显示已选题库
		$question_category=M("question_category")->where("id IN({$data[question_cateid]})")->getField("title",true);
		// dump($question_category);exit;
		$data[question_category]=implode(",",$question_category);

		return $data;
	}

	//测试调查列表
	public function survey_lists($search){

		$where=" 1 ";

		if($search[partment]){
			$where.=" AND tl_survey.partment=".$search[partment]."";
		}

		if($search[type]){
			$where.=" AND tl_survey.type={$search[type]} ";
		}

		if($search[status]){
			$where.=" AND tl_survey.status={$search[status]} ";
		}

		if($search[start_time]){
			$start_time=strtotime($search[start_time]);
			$where.=" AND tl_survey.start_time > {$start_time} ";
		}

		if($search[end_time]){
			$end_time=strtotime($search[end_time]);
			$where.=" AND tl_survey.end_time < {$end_time} ";
		}

		$array["table"]="survey";

		if($search[order]){
			$array["order"]="tl_survey.{$search[order]} desc ";
		}else{
			$array["order"]="tl_survey.id desc";
		}
		
		$array["field"]="tl_survey.*,tl_partment.title as partment_title ";
		$array["join"]="LEFT JOIN tl_partment ON tl_partment.id=tl_survey.partment ";
		$array["where"]=$where;

		$data=$this->db->Page($array);
		return $data;
	}

	//测试和题目详情
	public function detail($id,$user_id=""){
		$where=" 1 AND id='".$id."'";
		$detail=M("survey")->where($where)->find();
		$data[survey]=$detail;

		$array[table]='survey_question';

		$array[join]="LEFT JOIN tl_survey_question_sort ON tl_survey_question_sort.question_id=tl_survey_question.id ";
		$array[field]="tl_survey_question.*";
		$array[order]="tl_survey_question_sort.sort asc ";
		$array[where]="tl_survey_question.id IN ({$detail['question_ids']})";

		$question=$this->db->Select($array);
		$data[question]=$question;

		$option=M('survey_option');
		for($i=0;$i<count($data[question]);$i++){
			$question_id=$data[question][$i][id];
			$detail=$option->where("question_id='$question_id'")->order('sort asc')->select();
			for($j=0;$j<count($detail);$j++){
				$data[question][$i][option][$j][id]=$detail[$j][id];
				$data[question][$i][option][$j][label]=$detail[$j][label];
				$data[question][$i][option][$j][title]=$detail[$j][title];
				$data[question][$i][option][$j][point]=$detail[$j][point];
			}
			$data[question][$i][xulie]=$i+1;
		}

		if($user_id){
			$array1['where']="survey_id={$id} AND user_id={$user_id}";
			$array1['table']="survey_apply";
			$user=$this->db->Find($array1);
			$data[user]=$user;
		}
		return $data;
	}

	//提交测试答案
	public function submit_answer($survey_id,$post,$user_id=""){
		$where=" 1 AND id='".$survey_id."'";
		$detail=M("survey")->where($where)->find();
		$data[survey]=$detail;

		$addtime=time();
		$sum_point=0;
		$survey_option=M('survey_option');
		$survey_question=M("survey_question");
		foreach ($post as $key => $option) {
			$arr=explode("-",$option);
			$question_id=$arr[0];

			#是否为多选题
			$is_mul=$survey_question->where("id={$question_id}")->getField("is_mul");
			if($is_mul == 'on'){
				$option_id=$arr[1];
				$arr_opt=explode(",",$option_id);
				$mul_point=0;
				$mul_is_0=1;
				foreach ($arr_opt as $key => $value) {
					$point=$survey_option->where("id={$value}")->getField('point');
					if(empty($point)){
						$mul_is_0=0;
					}
					$mul_point+=$point;
					$dataList=array('user_id'=>$user_id,'question_id'=>$question_id,'option_id'=>$option_id,'addtime'=>$addtime,'survey_id'=>$survey_id,"point"=>$point,"mul_option"=>$arr[1]);

				}
				if($mul_is_0){
					$sum_point+=$mul_point;
				}
				
			}else{
				$option_id=$arr[1];
				$point=$survey_option->where("id={$option_id}")->getField('point');
				$sum_point+=$point;
				$dataList=array('user_id'=>$user_id,'question_id'=>$question_id,'option_id'=>$option_id,'addtime'=>$addtime,'survey_id'=>$survey_id,"point"=>$point);

			}

			
			
			$array[table]='survey_answer';
			$is=M("survey_answer")->where("user_id={$user_id} AND question_id={$question_id} AND survey_id={$survey_id} ")->find();
			if($is){
				$dataList=array('option_id'=>$option_id,'addtime'=>$addtime,'point'=>$point);
				if($is_mul=="on"){
					$dataList=array('option_id'=>$option_id,'addtime'=>$addtime,'point'=>$point,"mul_option"=>$arr[1]);
				}
				M("survey_answer")->where("user_id={$user_id} AND question_id={$question_id} AND survey_id={$survey_id} ")->save($dataList);
			}else{
				$array[data]=$dataList;

				$this->db->Add($array);
			}
			
		}
		$data[sum_point]=$sum_point;

		$dataList=array("user_id"=>$user_id,"survey_id"=>$survey_id,"sum_point"=>$sum_point,"addtime"=>$addtime);
		$is=M("survey_log")->where("user_id={$user_id} AND survey_id={$survey_id} ")->find();
		if($is){
			$dataList=array("sum_point"=>$sum_point,"addtime"=>$addtime);
			M("survey_log")->where("user_id={$user_id} AND survey_id={$survey_id} ")->save($dataList);
		}else{
			M("survey_log")->add($dataList);
			M("survey_apply")->where("user_id={$user_id} AND survey_id={$survey_id}")->setField("is_able",1);
		}
		
		

		if($user_id){
			$array['where']="survey_id={$survey_id} AND user_id={$user_id}";
			$array['table']="survey_apply";
			$user=$this->db->Find($array);
			$data[user]=$user;
		}
		return $data;
	}


	#对外名单提交答案
	public function submit_answer_op($survey_id,$post,$openid=""){
		$where=" 1 AND id='".$survey_id."'";
		$detail=M("survey")->where($where)->find();
		$data[survey]=$detail;

		$addtime=time();
		$sum_point=0;
		$survey_option=M('survey_option');
		$survey_question=M("survey_question");
		foreach ($post as $key => $option) {
			$arr=explode("-",$option);
			$question_id=$arr[0];

			#是否为多选题
			$is_mul=$survey_question->where("id={$question_id}")->getField("is_mul");
			if($is_mul == 'on'){
				$option_id=$arr[1];
				$arr_opt=explode(",",$option_id);
				foreach ($arr_opt as $key => $value) {
					$point=$survey_option->where("id={$value}")->getField('point');
					$sum_point+=$point;
					$dataList=array('openid'=>$openid,'question_id'=>$question_id,'option_id'=>$option_id,'addtime'=>$addtime,'survey_id'=>$survey_id,"point"=>$point,"mul_option"=>$arr[1],"open"=>2);

				}
			}else{
				$option_id=$arr[1];
				$point=$survey_option->where("id={$option_id}")->getField('point');
				$sum_point+=$point;
				$dataList=array('openid'=>$openid,'question_id'=>$question_id,'option_id'=>$option_id,'addtime'=>$addtime,'survey_id'=>$survey_id,"point"=>$point,"open"=>2);

			}

			
			
			$array[table]='survey_answer';
			$is=M("survey_answer")->where("openid='{$openid}' AND question_id={$question_id} AND survey_id={$survey_id} ")->find();
			if($is){
				$dataList=array('option_id'=>$option_id,'addtime'=>$addtime,'point'=>$point);
				if($is_mul=="on"){
					$dataList=array('option_id'=>$option_id,'addtime'=>$addtime,'point'=>$point,"mul_option"=>$arr[1]);
				}
				M("survey_answer")->where("openid='{$openid}' AND question_id={$question_id} AND survey_id={$survey_id} ")->save($dataList);
			}else{
				$array[data]=$dataList;

				$this->db->Add($array);
			}
			
		}
		$data[sum_point]=$sum_point;

		$dataList=array("openid"=>$openid,"survey_id"=>$survey_id,"sum_point"=>$sum_point,"addtime"=>$addtime);
		$is=M("survey_log")->where("openid='{$openid}' AND survey_id={$survey_id} ")->find();
		if($is){
			$dataList=array("sum_point"=>$sum_point,"addtime"=>$addtime);
			M("survey_log")->where("openid='{$openid}' AND survey_id={$survey_id} ")->save($dataList);
		}else{
			M("survey_log")->add($dataList);
			M("survey_apply")->where("openid='{$openid}' AND survey_id={$survey_id}")->setField("is_able",1);
		}
		
		
		return $data;
	}


	#选错答案记录
	public function wrong_log($surveyId,$user){
		$db=M("survey_answer");
		$where=" survey_id={$surveyId} AND (user_id='{$user}' OR openid='{$user}') AND (point=0 OR point='' ) AND mul_option IS NULL";
		$res=$db->where($where)->getField("question_id",true);

		// echo $db->getLastsql();exit;

		$option=M("survey_option");
		$question=M("survey_question");
		$data=array();
		foreach ($res as $key => $value) {
			$max=$option->where("question_id={$value}")->max("point");
			$res=$option->where("question_id={$value} AND point>={$max} ")->find();
			$title=$question->where("id={$value}")->getField("title");
			if($res[label]){
				$data[$key][title]=$title;
				$data[$key][right]=$res[label].":".$res[title];
			}
			
		}

		#多选题
		$mul=array();
		$where1=" survey_id={$surveyId} AND (user_id='{$user}' OR openid='{$user}') AND mul_option!='' ";
		$res1=$db->where($where1)->field("question_id,mul_option")->select();
		foreach ($res1 as $key => $value) {
			$options_res=$option->where("question_id={$value[question_id]} AND point!=0  ")->order("sort asc")->select();
			
			foreach ($options_res as $key1 => $option_res) {
				$pos = stripos($value[mul_option], $option_res[id]);
				$is_0=$option->where("id IN({$value[mul_option]}) AND point=0 ")->find();

				if($pos === false || $is_0) {
					$title=$question->where("id={$value[question_id]}")->getField("title");
					$mul[$key][title]=$title;
					$mul[$key][right]="";
					foreach ($options_res as $key2 => $option_res2){
						$mul[$key][right].=$option_res2[label].":".$option_res2[title]." ";
					}
					break;
				}
			}
		}

		$data=array_merge($data,$mul);

		return $data;
	}

	#选择活动名单
	public function select_contact($surveyId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment_id,partment,code,work_group,wx_userid")->find();
			$dataList=array('survey_id'=>$surveyId,'user_id'=>$contact['wx_userid'],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			$array[table]="survey_apply";
			$array[data]=$dataList;
			$survey_apply=M("survey_apply")->where("survey_id={$surveyId} AND user_id='{$contact[wx_userid]}' ")->find();
			if(!$survey_apply){
				$this->db->Add($array);
			}
		}
		$res[sql]=M("contacts")->getLastsql();
		$res[status]=10001;
		return $res; 	
	}


	#导入名单
	public function to_apply($persons,$surveyId){
		$db=M('contacts');
		$addtime=time();
		$survey_apply=M("survey_apply");
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"survey_id"=>$surveyId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"addtime"=>$addtime);
				$survey=$survey_apply->where("user_id='{$res[wx_userid]}' AND survey_id={$surveyId}")->find();
				if($survey){
					$survey_apply->where("user_id='{$res[wx_userid]}' AND survey_id={$surveyId}")->save($dataList);
				}else{
					$survey_apply->add($dataList);
				}

			}
		}
	}


	#区域统计人数
	public function survey_count1($surveyId){
		$db=M("survey_apply");
		$where="survey_id={$surveyId}";
		$join=" LEFT JOIN tl_user ON tl_user.id=user_id ";
		$join.=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$group="tl_contacts.area";
		$field="area,count(contact_id) as allcontact ";
		$data=$db->where($where)->join($join)->field($field)->group($group)->select();
		$logdb=M("survey_log");
		foreach ($data as $key => &$value) {
			$where="survey_id={$surveyId} AND tl_contacts.area='{$value[area]}' ";
			$field="count(tl_survey_log.user_id) as logcount ";
			$value[logcount]=$logdb->where($where)->join($join)->field($field)->select()[0][logcount];
		}
		return $data;
	}

	#区域统计人数
	public function survey_count($surveyId){
		$db=M("contacts_partment");
		$data=$db->where("parentid=14")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$sub=$db->where("parentid IN ({$subsql})")->field("id")->select(false);
			$allcount=M("survey_apply")->where("partment_id IN ({$sub}) AND survey_id={$surveyId} ")->count();
			$logcount=M("survey_log")->join("LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_log.user_id ")->where("partment_id IN ({$sub}) AND survey_id={$surveyId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["logcount"]=$logcount;
		}
		return $data;
	}

	#区域下的代码区域统计
	public function code_count($surveyId,$code){
		$db=M("contacts_partment");
		$data=$db->where("parentid={$code}")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$allcount=M("survey_apply")->where("partment_id IN ({$subsql}) AND survey_id={$surveyId} ")->count();
			$logcount=M("survey_log")->join("LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_log.user_id ")->where("partment_id IN ({$subsql}) AND survey_id={$surveyId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["logcount"]=$logcount;
		}
		return $data;
	}

	#区域分数统计
	public function survey_log1($surveyId,$area){
		$array[table]="survey_log";
		$array[join]=" LEFT JOIN tl_user ON tl_user.id=tl_survey_log.user_id ";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$array[field]="tl_survey_log.sum_point,tl_survey_log.addtime as logtime,tl_contacts.* ";
		$array[where]="tl_survey_log.survey_id={$surveyId} AND tl_contacts.area='{$area}' ";
		$array[order]="tl_survey_log.addtime desc ";
		$data=$this->db->Page($array);
		return $data;
	}

	#查看答题区域分数统计
	public function survey_log($surveyId,$area){
		$db=M("contacts_partment");
		$subsql=$db->where("parentid={$area}")->field("id")->select(false);
		$array[table]="survey_log";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_log.user_id ";
		$array[field]="tl_survey_log.sum_point,tl_survey_log.addtime as logtime,tl_contacts.* ";
		$array[where]="partment_id IN ({$subsql}) AND tl_survey_log.survey_id={$surveyId} ";
		$array[order]="tl_survey_log.addtime desc ";
		$data=$this->db->Page($array);
		return $data;
	}

	#人员详细
	public function detail_person($surveyId,$ids){
		$array[table]="survey_apply";
		$array[where]="tl_survey_apply.partment_id IN ({$ids}) AND tl_survey_apply.survey_id={$surveyId} ";
		$array[join]="LEFT JOIN tl_survey_log ON tl_survey_log.user_id=tl_survey_apply.user_id AND tl_survey_log.survey_id={$surveyId}";
		$array[field]="tl_survey_log.sum_point,tl_survey_log.addtime as logtime,tl_survey_apply.truename,tl_survey_apply.user_id ";
		$array[order]="tl_survey_log.addtime desc ";

		$data=$this->db->Page($array);
		// dump($data);exit;
		return $data;
	}

	#下载报表人员详细
	public function excel_person($surveyId){
		$array[table]="survey_log";
		$array[join]="LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_log.user_id ";
		$array[where]="tl_survey_log.survey_id={$surveyId} ";
		$array[field]="tl_survey_log.sum_point,tl_survey_log.addtime as logtime,tl_contacts.name,tl_contacts.partment,tl_survey_log.user_id ";
		$array[order]="tl_survey_log.addtime desc ";
		$array[num]=9999;

		$data=$this->db->Page($array);
		// dump($data);exit;
		return $data;

	}

	#测试意见收集
	public function save_advice($userid,$surveyId,$content){
		$array[table]="survey_advice";
		$array[data]=array("user_id"=>$userid,"survey_id"=>$surveyId,"content"=>$content,"addtime"=>time());
		$this->db->add($array);
	}

	#意见列表
	public function advice_lists($surveyId){
		$array[table]="survey_advice";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_advice.user_id ";
		$array[field]="tl_contacts.*,tl_survey_advice.content as advice_content,tl_survey_advice.addtime as advicetime ";
		$array[where]=" tl_survey_advice.survey_id={$surveyId} ";
		$array[order]="tl_survey_advice.addtime desc ";
		$data=$this->db->Page($array);
		return $data;
	}

	#pc互动测试
	public function pc_survey($surveyId,$questionId,$user_id){
		$data=M("survey_question")->where("id={$questionId}")->field("id,title")->find();
		$data[survey][title]=M("survey")->where("id={$surveyId}")->getField("title");
		$option=M("survey_option")->where("question_id={$questionId}")->order("sort asc")->select();
		$data[option]=$option;
		if($user_id){
			$array['where']="survey_id={$surveyId} AND user_id={$user_id}";
			$array['table']="survey_apply";
			$user=$this->db->Find($array);
			$data[user]=$user;
		}
		return $data;
	}


	#答题详情
	public function answer_log($surveyId,$user_id){
		$array[table]="survey_answer";
		$array[num]=20;
		$array[where]="tl_survey_answer.survey_id={$surveyId} AND tl_survey_answer.user_id={$user_id} ";
		$array[order]="tl_survey_answer.addtime desc ";
		$array[join]=" INNER JOIN tl_survey_question ON tl_survey_question.id=tl_survey_answer.question_id ";
		$array[join].=" INNER JOIN tl_survey_option ON tl_survey_option.id=tl_survey_answer.option_id ";
		$array[field]="tl_survey_question.id,tl_survey_answer.addtime as answertime,tl_survey_answer.mul_option,tl_survey_answer.user_id,tl_survey_answer.point,tl_survey_question.title,tl_survey_option.label,tl_survey_option.title as option_title ";

		$data=$this->db->Page($array);

		//多选题的选择答案
		$survey_question=M("survey_question");
		$survey_option=M("survey_option");
		foreach ($data[content] as $key => &$value) {
			$is_mul=$survey_question->where("id={$value[id]}")->getField("is_mul");
			if($is_mul=="on"){
				$mul_option=$survey_option->where("id IN({$value[mul_option]})")->select();
				foreach ($mul_option as $k => $val) {
					$value[sel_opt].=$val[label].":".$val[title]." ;";
					$value[opt_point]+=$val[point];
				}
			}else{
				$value[sel_opt]=$value[label].":".$value[option_title];
				$value[opt_point]=$value[point];

			}
		}
		$data[answer_name]=M("contacts")->where("wx_userid={$user_id}")->getField("name");
		return $data;
	}

	#导出答题详情
	public function excel_answer($surveyId){
		$survey_log=M("survey_log");
		$survey_option=M("survey_option");
		$survey_answer=M("survey_answer");
		$survey_advice=M("survey_advice");

		$users=$survey_log->where("survey_id={$surveyId}")->join("INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_survey_log.user_id")->field("user_id,name,sum_point,tl_survey_log.addtime")->select();
		// dump($users);exit;
		$question_ids=M("survey")->where("id={$surveyId}")->getField("question_ids");

		$question=M("survey_question")->where("id IN({$question_ids})")->order("id asc")->select();
		// dump($question);exit;
		$title=array();
		$title[]="代码";
		$title[]="姓名";
		foreach ($question as $key => $value) {
			$title[]=($key+1).".".$value[title];
			$title[]="得分";
		}
		$title[]="总分数";
		$type=M("survey")->where("id={$surveyId}")->getField("type");
		if($type==2){
			$title[]="意见反馈";
		}
		$title[]="测试时间";

		$join="INNER JOIN tl_survey_option ON tl_survey_option.id=tl_survey_answer.option_id ";
		$field="tl_survey_answer.*,tl_survey_option.label,tl_survey_option.title as option_title";

		$data=array();
		foreach ($users as $key => $user) {
			$data[$key]=array();
			$data[$key][]=$user[user_id];
			$data[$key][]=$user[name];
			foreach ($question as $k2 => $value) {
				$is_mul=$value[is_mul];
				$option=$survey_answer->join($join)->where("user_id={$user[user_id]} AND tl_survey_answer.question_id={$value[id]} AND survey_id={$surveyId} ")->field($field)->find();
				// echo $survey_answer->getLastsql();exit;
				// dump($option);exit;

				if($is_mul=="on"){#多选
					$mul_option=$survey_option->where("id IN({$option[mul_option]})")->select();
					$sel[sel_opt]="";
					$sel[opt_point]="";
					foreach ($mul_option as $k => $val) {
						$sel[sel_opt].=$val[label].":".$val[title]." ;";
						$sel[opt_point]+=$val[point];
					}
				}else{
					$sel[sel_opt]=$option[label].":".$option[option_title];
					$sel[opt_point]=$option[point];
				}
				// dump($sel);exit;
				$data[$key][]=$sel[sel_opt];
				if(empty($sel[opt_point])){
					$sel[opt_point]=0;
				}
				$data[$key][]=$sel[opt_point];
			}
			$data[$key][]=$user[sum_point];
			if($type==2){
				$data[$key][]=$survey_advice->where("user_id={$user[user_id]} AND survey_id={$surveyId}")->getField("content");
			}
			$data[$key][]=date("Y-m-d H:m:s",$user[addtime]);
		}
		
		$res[title]=$title;
		$res[data]=$data;
		return $res;
	}

	public function excel_point($surveyId){
		$title=array("题目","总得分","平均分","选项:人数","总计:");

		$all_log=M("survey_log")->where("survey_id={$surveyId}")->count();
		$title[]=$all_log."份问卷";

		$question_ids=M("survey")->where("id={$surveyId}")->getField("question_ids");

		$question=M("survey_question")->where("id IN({$question_ids})")->order("id asc")->select();

		$data=array();
		$survey_answer=M("survey_answer");
		foreach ($question as $key => $value) {
			$data[$key]=array();
			$data[$key][]=($key+1).".".$value[title];
			$sum=M("survey_answer")->where("survey_id={$surveyId} AND question_id={$value[id]}")->sum("point");
			$data[$key][]=$sum;
			$avg=M("survey_answer")->where("survey_id={$surveyId} AND question_id={$value[id]}")->avg("point");
			$data[$key][]=$avg;
				
			$option=M("survey_option")->where("question_id={$value[id]}")->field("id,question_id,label")->order("sort asc")->select();
			// dump($option);exit;
			// $str="";
			foreach ($option as $key1 => $val) {
				$num=M("survey_answer")->where("survey_id={$surveyId} AND question_id={$val[question_id]} AND option_id={$val[id]}")->count();
				$str.=$val[label].":".$num." ";
				$data[$key][]=$val[label];
				$data[$key][]=$num;
			}

			// $data[$key][]=$str;
			// $data[$key][]="";
			// $data[$key][]="";
		}

		$res[title]=$title;
		$res[data]=$data;
		return $res;

	}

}