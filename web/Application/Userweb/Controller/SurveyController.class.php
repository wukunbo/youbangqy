<?php
namespace Userweb\Controller;
use Think\Controller;
class SurveyController extends SurveyBaseController{

	public function __construct(){
		parent::__construct();
		$this->logic= new \Survey\Logic\ResearchLogic;
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
	}

	public function add_question(){
		$id=$_REQUEST['id'];
		$this->categoryid=$_REQUEST['categoryid'];
		$this->data=$this->logic->add_question($id);
		$this->display();
	}

	public function add_question_action(){
		$id=$_REQUEST['id'];
		$post[question]=$_REQUEST[post];
		$post[question][type_id]=1;
		$post[question][survey_id]=$this->survey_id;
		$post[option][option_label]=$_REQUEST['option_label'];
		$post[option][sort]=$_REQUEST['sort'];
		$post[option][option_title]=$_REQUEST['option_title'];
		$post[option][option_point]=$_REQUEST['option_point'];
		// if(in_array('',$_REQUEST['post']) || in_array('',$_REQUEST['option_label']) || in_array('',$_REQUEST['option_title'])){
		// 	$this->showmsg("请填写完整信息",1);
		// }
		#是否直接加入题库
		$categoryid=$_REQUEST[categoryid];
		$this->data=$this->logic->add_question_action($id,$post,$categoryid);
		if(empty($categoryid) && empty($_REQUEST[form])){
			$this->showmsg('操作成功',"userweb.php?c=survey&a=question_lists");
		}else if(isset($_REQUEST[form]) && !empty($_REQUEST[form])){
			$this->showmsg('操作成功',"userweb.php?m=Userweb&c=Survey&a=show_category&id=".$_REQUEST[form]);
		}else{
			$this->showmsg('添加成功',"userweb.php?c=survey&a=question_category");
		}
		
	}

	//题目列表
	public function question_lists(){
		$sql=M("question_banks")->field("question_id")->select(false);

		$array[where]="tl_survey_question.id NOT IN({$sql}) ";

		if($_REQUEST[question_name]){
			$array[where]="title like '%{$_REQUEST[question_name]}%' ";
		}
		$array[num]=20;

		$data=$this->logic->question_lists($array);
		$this->data=$data;
		$this->display();
	}

	public function check_question(){
		$ids=M("survey")->where("id={$_REQUEST[id]}")->getField("question_ids");
		$array[where]="tl_survey_question.id IN({$ids}) ";
		if(isset($_REQUEST[question_category])){
			$sql=M("question_banks")->where("category_id={$_REQUEST[question_category]}")->field("question_id")->select(false);
			$array[where].=" AND tl_survey_question.id IN({$sql}) ";
		}

		$array[num]=100;

		$data=$this->logic->question_lists($array);
		// dump($data);exit;
		//题目排序
		$db=M("survey_question_sort");
		foreach ($data[content] as $key => &$value) {
			$value[sort]=$db->where("survey_id={$_REQUEST[id]} AND question_id={$value[id]}")->getField("sort");
		}

		#已选题库
		$question_cateid=M("survey")->where("id={$_REQUEST[id]}")->getField("question_cateid");
		$cate_name=M("question_category")->where("id IN ({$question_cateid})")->field("id,title")->select();
		// dump($cate_name);exit;
		$this->cate_name=$cate_name;
		$this->data=$data;
		$this->display();
	}

	public function question_sort(){
		$post=$_REQUEST[post];
		$surveyId=$_REQUEST[id];
		$db=M("survey_question_sort");
		M("survey_question_sort")->where("survey_id={$surveyId}")->delete();
		$question_ids=M("survey")->where("id={$surveyId}")->getField("question_ids");
		$question_ids=explode(",",$question_ids);

		foreach ($question_ids as $key1 => $value) {
			$dataList=array("survey_id"=>$surveyId,"question_id"=>$value);
			$db->add($dataList);
		}
		// dump($post);exit;
		foreach ($post as $key => $value) {
			$db->where("survey_id={$surveyId} AND question_id={$key}")->setField("sort",$value);
		}

		// $question_ids=$db->where("survey_id={$surveyId}")->order("sort asc")->getField("question_id",true);
		// dump($question_ids);exit;
		// $question_ids=implode(",",$question_ids);
		// M("survey")->where("id={$surveyId}")->setField("question_ids",$question_ids);
		$this->showmsg("排序成功");
		
	}

	//删除题目
	public function question_del(){
		$id=$_REQUEST['id'];
		M('survey_question')->where("id='$id'")->delete();
		M('survey_option')->where("question_id='$id'")->delete();
		$this->showmsg('更新成功');
	}

	//删除题目
	public function survey_question_del(){
		$id=$_REQUEST['id'];
		$survey_id=$_REQUEST['survey_id'];
		$question_ids=M("survey")->where("id={$survey_id}")->getField("question_ids");
		$question=explode(",",$question_ids);
		unset($question[array_search($id,$question)]);
		$question_ids=implode(",",$question);
		M("survey")->where("id={$survey_id}")->setField("question_ids",$question_ids);
		$this->showmsg('操作成功');
	}


	//清空答题数据
	public function delete_survey_answer(){
		$survey_id=$_REQUEST[id];
		M("survey_answer")->where("survey_id={$survey_id}")->delete();
		$this->showmsg('操作成功');
	}

	#批量删除题目
	public function all_question_del(){
		$questionids=$_REQUEST['questionids'];
		$survey_id=$_REQUEST['survey_id'];
		$question_ids=M("survey")->where("id={$survey_id}")->getField("question_ids");
		$question_ids.=",";

		$question=explode(",",$questionids);
		foreach ($question as $key => $value) {
			$value.=",";
			$question_ids=str_replace($value,'',$question_ids);
		}
		#去除最后一个逗号
		$question_ids=rtrim($question_ids, ",");

		M("survey")->where("id={$survey_id}")->setField("question_ids",$question_ids);
		$this->ajaxReturn("ok");
	}


	#批量删除题目列表的题目
	public function question_list_del(){
		$questionids=$_REQUEST['questionids'];
		M("survey_question")->where("id IN({$questionids})")->delete();
		M("survey_option")->where("question_id IN({$questionids})")->delete();
		$this->ajaxReturn("ok");
	}

	#删除题库
	public function category_alldel(){
		$id=$_REQUEST[id];
		M("question_category")->where("id={$id}")->delete();
		M("question_banks")->where("category_id={$id}")->delete();
		$this->showmsg("操作成功");
	}

	//创建测试活动
	public function add(){
		$id=$_REQUEST['id'];

		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}

		$this->data=$this->logic->survey_detail($id);
		$this->display();
	}

	public function add_survey_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		// if(in_array('',$check_post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }

		$post[user_id]=$this->user_child_id;
		$id=$this->logic->add_survey_action($post);
		// if(!$post[id]){
		// 	$url = 'http://192.168.1.115/youbang/web/survey.php?c=index&a=index&id='.$id;
		// 	$detail=add_qcr_url($url);
		// 	M('survey')->where("id='".$id."'")->setField("qccode",$detail['pic']);
		// }
		$this->showmsg('更新成功',"userweb.php?c=survey&a=lists");

	}

	//删除调查
	public function del(){
		$id=$_REQUEST['id'];
		M('survey')->where("id='$id'")->delete();
		$this->showmsg('删除成功');
	}

	//测试调查列表
	public function lists(){

		$search=$_REQUEST[post];

		if($this->user_level == 1){
			$this->partment_list=$this->get_partment();
		}else{
			$search[partment]=$this->user_partment_id;
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}

		$data=$this->logic->survey_lists($search);
		// dump($data);exit;
		$this->data=$data;
		$this->display();
	}

	#下架活动
	public function out_survey(){
		$id=$_REQUEST[id];
		M("survey")->where("id={$id}")->setField("status",-1);
		$this->showmsg("操作成功");
	}

	#下架活动复制
	public function copy_survey(){
		$id=$_REQUEST[id];
		$post=M("survey")->where("id={$id}")->find();
		$post[user_id]=$this->user_id;
		// $post[wx_userid]=$this->user_wx_id;
		$post[addtime]=time();
		// $post[publishtime]=null;
		$post[status]=0;
		unset($post[id]);
		M("survey")->add($post);
		$this->showmsg("活动复制成功",1);
	}

	//选择题目
	public function select_question(){

		$this->category=M("question_category")->getField("id,title");

		if(!empty($_REQUEST[categoryid]) && isset($_REQUEST[categoryid])){
			$subsql=M("question_banks")->where("category_id={$_REQUEST[categoryid]}")->field("question_id")->select(false);
			$array[where]="tl_survey_question.id IN ({$subsql})";
			$this->tiku_name=M("question_category")->where("id={$_REQUEST[categoryid]}")->getField("title");
		}

		if(!empty($_REQUEST[keyword]) && isset($_REQUEST[keyword])){
			$array[where]="tl_survey_question.title LIKE '%{$_REQUEST[keyword]}%' ";
		}

		#已选题库
		if(!empty($_REQUEST[ids]) && isset($_REQUEST[ids])){
			$array[where]="tl_survey_question.id IN ({$_REQUEST[ids]})";
		}

		if(isset($_REQUEST[sel])){

			$sql=M("question_banks")->field("question_id")->select(false);

			// $array[where]="id NOT IN({$sql}) ";
			$array[where]="tl_survey_question.id=-1";
		}

		$array[num]=10;
		$data_page=$this->logic->question_lists($array);
		$data_page[per_num]=10;
		$array[num]=9999;//不分页
		$data=$this->logic->question_lists($array);
		
		$this->data_page=$data_page;
		$this->data=$data;
		$this->display();
	}

	//选择测试名单
	public function select_list(){
		if(IS_POST){
			$contactIds=$_POST['contactIds'];
			$surveyId=$_POST['surveyId'];
			$this->logic->select_contact($surveyId,$contactIds); 
			#选择通讯录活动
			M("survey")->where("id={$surveyId}")->setField("status",2);
			$this->ajaxReturn("ok");
		}else{
			$this->partment_list=$this->get_contacts_partment();
			$this->surveyId=$_REQUEST['id'];
			if($_REQUEST[add] && !isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("survey_apply","survey_id={$_REQUEST['id']}");
			}else if(!empty($_REQUEST[partment]) && isset($_REQUEST[partment]) && !isset($_REQUEST[add])){
				$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
			}else if(isset($_REQUEST[add]) && isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("survey_apply","survey_id={$_REQUEST['id']}",$_REQUEST[partment]);
			}else{
				$this->data=$this->select_contacts();				
			}
			$this->display();
		}
	}

	#选择保存名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$surveyId=$_REQUEST['id'];
		$this->logic->select_contact($surveyId,$contactIds); 
		#选择名单状态
		$survey_status=M("survey")->where("id={$surveyId}")->getField("status");
		if($survey_status==0){
			M("survey")->where("id={$surveyId}")->setField("status",2);
		}
		$this->ajaxReturn("ok");
	}


	#已导入数据查看
	public function check_apply(){
		$id=$_REQUEST[id];
		$this->data=M("survey_apply")->where("survey_id={$id}")->select();
		#是否允许重复答题
		$this->repeat=M("survey")->where("id={$id}")->getField("repeat");
		
		$this->open_status=M("survey")->where("id={$id}")->getField("open_status");

		$this->survey_value1=M("survey")->where("id={$id}")->getField("value1");
		$this->survey_value2=M("survey")->where("id={$id}")->getField("value2");
		$this->survey_value3=M("survey")->where("id={$id}")->getField("value3");


		$this->display();
	}

	#删除已选通讯录名单
	public function del_apply(){
		$id=$_REQUEST[id];
		$res=$this->del_action("survey_apply",$id,$search);
		$this->showmsg("操作成功!");
	}

	#批量删除报名人员
	public function del_allapply(){
		$ids=$_REQUEST[ids];
		$res=M("survey_apply")->where("id IN ($ids)")->delete();
		$this->ajaxReturn("ok");
	}

	#发布活动
	public function publish_survey(){
		M("survey")->where("id={$_REQUEST[id]}")->setField("status",1);

		#推送消息
		$agentid=M("partment")->join(" INNER JOIN tl_survey ON tl_survey.partment=tl_partment.id")->where("tl_survey.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_survey($_REQUEST[id]);
		if($res["errmsg"] != "ok"){
			$this->showmsg("发布失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("发布成功!");
		}

	}

	#再次推送
	public function again_publish(){
		$agentid=M("partment")->join(" INNER JOIN tl_survey ON tl_survey.partment=tl_partment.id")->where("tl_survey.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_survey($_REQUEST[id]);
		if($res["errmsg"] != "ok"){
			$this->showmsg("推送失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送成功!");
		}
	}

	#预览
	public function preview(){
		$agentid=M("partment")->join(" INNER JOIN tl_survey ON tl_survey.partment=tl_partment.id")->where("tl_survey.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$user_id=M("survey")->where("tl_survey.id={$_REQUEST[id]}")->join("INNER JOIN tl_user_child ON tl_user_child.id=tl_survey.user_id ")->getField("tl_user_child.wx_userid");
		$res=$this->weixin_news->publish_survey($_REQUEST[id],array($user_id));
		if($res["errmsg"] != "ok"){
			$this->showmsg("推送预览失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送预览成功!");
		}
	}


	#批量导入题目
	public function to_question(){
		$filePath=$_FILES["xlsfile"]["tmp_name"];

		define("ROOT_PATH","ThinkPHP/Library/Vendor/") ;
    	require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel.class.php");
   		require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel/IOFactory.class.php");
    	require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel/Writer/Excel5.class.php");
    	require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel/Writer/Excel2007.php");
    	
  		$PHPExcel = new \PHPExcel(); 
  		/**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/ 
		$PHPReader = new \PHPExcel_Reader_Excel2007(); 
		if(!$PHPReader->canRead($filePath)){ 
		  $PHPReader = new \PHPExcel_Reader_Excel5(); 
		  if(!$PHPReader->canRead($filePath)){ 
		    // echo 'no Excel'; 
		    return; 
		  } 
		}

		$PHPExcel = $PHPReader->load($filePath); 
		$currentSheet = $PHPExcel->getSheet(0);  //读取excel文件中的第一个工作表
		$allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
		$allRow = $currentSheet->getHighestRow(); //取得一共有多少行

		$person = array();  //声明数组

		/**从第二行开始输出，因为excel表中第一行为列名*/ 
  		for($currentRow = 2;$currentRow <= $allRow;$currentRow++){

  			$question = $currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();
  			$post[question][title]=$question;
  			$post[question][type_id]=1;
			$post[question][addtime]=time();

			$option_label=array("A","B","C","D","E","F","G");

			$option_sort=array("1","2","3","4","5","6","7");

			$option_title=array();
			$option_point=array();

			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("B") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("D") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("E") - 65,$currentRow)->getValue();
			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("F") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("G") - 65,$currentRow)->getValue();
			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("H") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("I") - 65,$currentRow)->getValue();
			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("J") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("K") - 65,$currentRow)->getValue();
			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("L") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("M") - 65,$currentRow)->getValue();
			$option_title[]=$currentSheet->getCellByColumnAndRow(ord("N") - 65,$currentRow)->getValue();
			$option_point[]=$currentSheet->getCellByColumnAndRow(ord("O") - 65,$currentRow)->getValue();

			$post[option][option_label]=$option_label;
			$post[option][option_title]=$option_title;
			$post[option][option_point]=$option_point;
			$post[option][sort]=$option_sort;

			$this->logic->add_question_action($id,$post,$_REQUEST[categoryid]);
  		}

	}


	#测试统计
	public function survey_count(){
		$this->surveyId=$_REQUEST[id];
		$this->data=$this->logic->survey_count($this->surveyId);
		$this->display();

	}

	#区域分数
	public function area_count(){
		$surveyId=$_REQUEST[id];
		$area=$_REQUEST[area];
		$this->data=$this->logic->survey_log($surveyId,$area);
		$this->display();
	}

	public function code_count(){
		$surveyId=$_REQUEST[id];
		$area=$_REQUEST[area];
		$this->data=$this->logic->code_count($surveyId,$area);
		$this->display();
	}

	#题库列表
	public function question_category(){
		$this->data=$this->logic->question_category();
		$this->display();
	}

	public function add_question_category(){
		$id=$_REQUEST[id];
		$this->data=$this->logic->category_detail($id);
		$this->display();
	}

	#添加题库
	public function add_category_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		if(in_array('',$check_post)){
			$this->showmsg("请填写完整信息",1);
		}

		$res=$this->logic->add_category_action($post);
		$this->showmsg('操作成功',"userweb.php?c=survey&a=question_category");
	}

	#选择题目到题库
	public function select_category(){
		$array[num]=20;

		$categoryid=$_REQUEST[id];

		// $subsql=M("question_banks")->where("category_id={$categoryid}")->field("question_id")->select(false);
		$subsql=M("question_banks")->field("question_id")->select(false);
		$array[where]="tl_survey_question.id NOT IN ({$subsql})";


		if(isset($_REQUEST[question_name])){
			$array[where]="title like '%{$_REQUEST[question_name]}%' ";
		}

		$data=$this->logic->question_lists($array);
		$this->data=$data;
		$this->display();
	}

	#题库题目查看
	public function show_category(){
		$categoryid=$_REQUEST[id];
		$this->data=$this->logic->show_category($categoryid);
		// dump($this->data);exit;
		$this->display();
	}

	#删除题库中题目
	public function category_del(){
		$categoryid=$_REQUEST[categoryid];
		$questionid=$_REQUEST[questionid];
		M("question_banks")->where("category_id={$categoryid} AND question_id={$questionid}")->delete();
		$this->showmsg('操作成功');
	}

	#添加到题库
	public function save_category(){
		$addtime=time();
		$questionids=explode(",",$_REQUEST[questionids]);
		foreach ($questionids as $key => $value) {
			$data=array("category_id"=>$_REQUEST[categoryid],"question_id"=>$value,"addtime"=>$addtime);
			M("question_banks")->add($data);
		}

		$this->ajaxReturn("ok");
	}

	#导出区域统计
	public function excel_count(){

		$title = array(
			"编号",
		    "区域",
		    "参与人数",
		    "答题人数",
		);

		$res=$this->logic->survey_count($_REQUEST[id]);
		$data=array();
		foreach ($res as $key => $value) {
			$data[] = array($key+1,$value['area'],$value['allcontact'],$value['logcount']);
		}
		$survey=M("survey")->where("id={$_REQUEST[id]}")->getField("title");
		$filename = $survey.time();
		
		excel_export($filename,$title,$data);
	}

	#反馈意见列表
	public function survey_advice(){
		$surveyId=$_REQUEST[id];
		$this->data=$this->logic->advice_lists($surveyId);
		// dump($this->data);exit;
		$this->display();
	}

	#导入名单
	public function to_apply(){
		$filePath=$_FILES["xlsfile"]["tmp_name"];

		define("ROOT_PATH","ThinkPHP/Library/Vendor/") ;
    	require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel.class.php");
   		require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel/IOFactory.class.php");
    	require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel/Writer/Excel5.class.php");
    	require_once(ROOT_PATH."PHPExcel/Classes/PHPExcel/Writer/Excel2007.php");

    	$PHPExcel = new \PHPExcel(); 
  		/**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/ 
		$PHPReader = new \PHPExcel_Reader_Excel2007(); 
		if(!$PHPReader->canRead($filePath)){ 
		  $PHPReader = new \PHPExcel_Reader_Excel5(); 
		  if(!$PHPReader->canRead($filePath)){ 
		    // echo 'no Excel'; 
		    return; 
		  } 
		} 

		$PHPExcel = $PHPReader->load($filePath); 
		$currentSheet = $PHPExcel->getSheet(0);  //读取excel文件中的第一个工作表
		$allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
		$allRow = $currentSheet->getHighestRow(); //取得一共有多少行

		$person = array();  //声明数组

		/**从第二行开始输出，因为excel表中第一行为列名*/ 
  		for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
  			/**ord()将字符转为十进制数*/
			$name = $currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();
			$code = $currentSheet->getCellByColumnAndRow(ord("B") - 65,$currentRow)->getValue();
			// $hint = $currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$person[$currentRow][] = $name;
			$person[$currentRow][] = $code;
			// $person[$currentRow][] = $hint;

    		$persons[$currentRow]=$person[$currentRow];
  		}
  		$this->logic->to_apply($persons,$_REQUEST[id]);

		$this->ajaxReturn("ok");
	}

	#报表统计
	public function report(){
		$id=$_REQUEST["id"];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		if($this->user_level == 1){
			$data=$PublicLogic->report_survey(1,$id);
		}else{
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($this->user_partment_id);

			$data=$PublicLogic->report_survey_child(1,$ids,$id);
		}
		
		$this->surveyId=$id;
		// dump($data);exit;
		$this->data=$data;
		$this->display();
	}

	#详细统计
	public function apply_person(){
		$id=$_REQUEST["id"];
		$partment_id=$_REQUEST[partment];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		$ids=$PublicLogic->get_all_chile_ids($partment_id);
		$ids=implode(",",$ids);
		$GLOBALS["partmentId"]=array();
		$this->data=$this->logic->detail_person($id,$ids);
		$this->display();

	}

	#批量删除题库题目
	public function del_category(){
		$categoryid=$_REQUEST[categoryid];
		$questionids=$_REQUEST[questionids];
		M("question_banks")->where("category_id={$categoryid} AND question_id IN ({$questionids}) ")->delete();
		// echo M("question_banks")->getLastsql();
		$this->ajaxReturn("ok");
	}

	#删除题库中的题目
	public function delsuy_category(){
		$cateid=$_REQUEST['cateid'];
		$survey_id=$_REQUEST['survey_id'];

		$question_ids=M("survey")->where("id={$survey_id}")->getField("question_ids");
		$question_ids.=",";

		$question=M("question_banks")->where("category_id={$cateid}")->getField("question_id",true);
		// dump($question);exit;
		foreach ($question as $key => $value) {
			$value.=",";
			// echo $value;exit;
			$question_ids=str_replace($value,'',$question_ids);
		}
		#去除最后一个逗号
		$question_ids=rtrim($question_ids, ",");

		// echo $question_ids;exit;

		$question_cateid=M("survey")->where("id={$survey_id}")->getField("question_cateid");
		$question_cateid.=",";

		$question_cateid=str_replace($cateid.",",'',$question_cateid);
		$question_cateid=rtrim($question_cateid, ",");
		
		M("survey")->where("id={$survey_id}")->setField("question_cateid",$question_cateid);
		M("survey")->where("id={$survey_id}")->setField("question_ids",$question_ids);

		$this->ajaxReturn("ok");
	}


	#导出详细统计
	public function excel_person(){
		$title = array(
			"区域",
		    "代码",
		    "姓名",
		    "分数",
		    "测试时间"
		);
		$id=$_REQUEST["id"];
		$survey_name=M("survey")->where("id={$id}")->getField("title");

		$res=$this->logic->excel_person($id);
		// dump($res);exit;

		$data=array();
		foreach ($res[content] as $key => $vo) {
			$logtime=empty($vo[logtime]) ? "" : date("Y-m-d H:i:s",$vo['logtime']);
			
			$data[] = array($vo[partment],$vo[user_id],$vo[name],$vo[sum_point],$logtime);
		}
		$filename = $survey_name.'细统计表';
		
		excel_export($filename,$title,$data);
	}

	//个人答题详情
	public function answer_log(){
		$user_id=$_REQUEST["uid"];
		$surveyId=$_REQUEST["id"];

		$data=$this->logic->answer_log($surveyId,$user_id);
		$this->data=$data;
		$this->display();
	}

	//导出答题详情
	public function excel_answer(){
		$surveyId=$_REQUEST["id"];
		$res=$this->logic->excel_answer($surveyId);

		$survey_name=M("survey")->where("id={$surveyId}")->getField("title");
		$filename = $survey_name.'详细统计表';
		excel_export($filename,$res[title],$res[data]);
	}

	//导出总分统计表
	public function excel_point(){
		$surveyId=$_REQUEST["id"];
		$res=$this->logic->excel_point($surveyId);
		// dump($res);exit;
		
		$survey_name=M("survey")->where("id={$surveyId}")->getField("title");
		$filename = $survey_name.'分数统计表';
		excel_export($filename,$res[title],$res[data]);
	}

	//是否允许重复答题
	public function is_repeat(){
		$id=$_REQUEST[id];
		$repeat=$_REQUEST[repeat];
		M("survey")->where("id={$id}")->setField("repeat",$repeat);
		$this->ajaxReturn("ok");
	}

	#对外开放状态
	public function open_status(){
		$id=$_REQUEST[id];
		$open_status=$_REQUEST[open_status];
		M("survey")->where("id={$id}")->setField("open_status",$open_status);
		$this->ajaxReturn("ok");
	}

	public function change_value(){
		$id=$_REQUEST[id];
		M("survey")->where("id={$id}")->setField($_REQUEST[value],$_REQUEST[content]);
		$this->ajaxReturn("ok");
	}

	


}