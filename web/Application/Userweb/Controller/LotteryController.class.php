<?php
namespace Userweb\Controller;
use Think\Controller;

/**
 * 抽奖控制器
 */
class LotteryController extends BaseController{
	public function __construct(){
		parent::__construct();
		$this->logic= new \Lottery\Logic\IndexLogic();
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
		$this->user_id=$_SESSION[userweb][userid];
	}


	public function lists(){

		$search=$_REQUEST[post];

		if($this->user_level == 1){
			$this->partment_list=$this->get_partment();
		}else{
			$search[partment]=$this->user_partment_id;
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		$this->data=$this->logic->lists($search); 
		$this->display();
	}

	//抽奖活动基本设置
	public function add(){
		$id=$_REQUEST["id"];

		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}

		$this->data=$this->logic->detail($id);
		$this->display();
	}

	#pc端抽奖活动
	public function pcadd(){
		// $this->lottery=M("lottery")->field("id,title")->select();
		$id=$_REQUEST["id"];

		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		
		$this->data=$this->logic->detail($id);
		$join="INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_pc_area.pc_user_id ";
		$this->area=M("lottery_pc_area")->join($join)->where("lottery_pc_id={$id}")->field("tl_lottery_pc_area.*,tl_contacts.name")->select();
		$this->display();
	}

	//抽奖活动发布
	public function change_status(){
		
		$agentid=M("partment")->join(" INNER JOIN tl_lottery ON tl_lottery.partment=tl_partment.id")->where("tl_lottery.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_lottery($_REQUEST[id]);


		#推送消息
		if($res["errmsg"] != "ok"){
			$this->showmsg("发布失败，所选名单不在应用范围内");
		}else{
			$search[to_status]=1;
			$search[id]=$_REQUEST[id];
			$res=$this->logic->change_status($search);
			$this->showmsg("发布成功!");
		}
	}

	public function again_publish(){
		$agentid=M("partment")->join(" INNER JOIN tl_lottery ON tl_lottery.partment=tl_partment.id")->where("tl_lottery.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_lottery($_REQUEST[id]);
		if($res["errmsg"] != "ok"){
			$this->showmsg("推送失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送成功!");
		}
	}

	public function del(){
 
		$id=$_REQUEST[id];
		$res=$this->del_action("lottery",$id,$search);
		$this->showmsg("操作成功!");
 
	}

	public function add_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		// if(in_array('',$check_post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }
		
		$post[user_id]=$this->user_child_id;
		$res=$this->logic->add_action($post);
		if(!$post[id]){
			$this->showmsg("操作成功","userweb.php?c=lottery&a=award&id=".$res[id]);
		}else{
			$this->showmsg("修改成功","userweb.php?c=lottery&a=lists");
		}
	}

	#添加pc抽奖
	public function pc_action(){
		$post=$_REQUEST[post];
		// if(in_array('',$post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }
		// dump($post);exit;
		$post[user_id]=$this->user_child_id;
		$res=$this->logic->pc_action($post);
		if(!$post[id]){
			$this->showmsg("操作成功","userweb.php?c=lottery&a=pcaward&id=".$res[id]);
		}else{
			$this->showmsg("修改成功","userweb.php?c=lottery&a=lists");
		}
	}

	//奖品设置
	public function award(){
		$id=$_REQUEST['id'];
		$this->lottery=M("lottery")->field('title,id')->where('id='.$id)->find();

		if(!$id || !$this->lottery){
			$this->showmsg("请先设置抽奖活动的基本信息","userweb.php?c=lottery&a=add");
		}
		$this->data=$this->logic->award_detail($id);
		$this->total=count($this->data[detail]);
		$this->display();
	}

	public function pcaward(){
		$id=$_REQUEST['id'];
		$this->lottery=M("lottery")->field('title,id')->where('id='.$id)->find();

		if(!$id || !$this->lottery){
			$this->showmsg("请先设置抽奖活动的基本信息","userweb.php?c=lottery&a=pcadd");
		}
		$this->data=$this->logic->award_detail($id);
		$this->total=count($this->data[detail]);
		$this->display();
	}



	public function add_award(){
		$post=$_REQUEST[post];

		$check_post[1]=$post[1];
		unset($check_post[1]['id']);
		unset($check_post[1]['image']);

		$check_post[2]=$post[2];
		unset($check_post[2]['id']);
		unset($check_post[2]['image']);

		$check_post[3]=$post[3];
		unset($check_post[3]['id']);
		unset($check_post[3]['image']);
		
		if(in_array('',$check_post[1]) || in_array('',$check_post[2]) || in_array('',$check_post[3])){
			$this->showmsg("请填写完整信息,最少设置三个奖项",1);
		}

		$res=$this->logic->add_award($post);
		$this->showmsg("奖品设置成功","userweb.php?c=lottery&a=lists");
	}

	//选择抽奖名单
	public function select_list(){
		if(IS_POST){
			$contactIds=$_POST['contactIds'];
			$lotteryId=$_POST['lotteryId'];
			$this->logic->select_contact($lotteryId,$contactIds); 
			#发布活动
			M("lottery")->where("id={$lotteryId}")->setField("status",1);
			
		}else{
			$this->lotteryId=$_REQUEST['id'];
			$this->data=$this->select_contacts();
			$this->display();
		}
	}

	#导入抽奖数据
	public function to_lottery(){
		$filePath=$_FILES["xlsfile"]["tmp_name"];
		// $filePath="Uploads/contacts.xlsx";
		// if(file_exists($filePath))
		// 	echo "当前目录中，文件".$filePath."存在";
		// else
		// 	echo "不存在";

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
			$baodan = $currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$lottery_count = $currentSheet->getCellByColumnAndRow(ord("D") - 65,$currentRow)->getValue();
			$person[$currentRow][] = $name;
			$person[$currentRow][] = $code;
			$person[$currentRow][] = $baodan;
			$person[$currentRow][] = $lottery_count;

    		$persons[$currentRow]=$person[$currentRow];
		}
		$ids=$this->logic->lottery_contact($persons,$_REQUEST[id]);
		$this->ajaxReturn(implode(",",$ids));
	}


	public function to_lotteryData(){
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
			$baodan = $currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$lottery_count = $currentSheet->getCellByColumnAndRow(ord("D") - 65,$currentRow)->getValue();
			$remark = $currentSheet->getCellByColumnAndRow(ord("E") - 65,$currentRow)->getValue();
			$person[$currentRow][] = $name;
			$person[$currentRow][] = $code;
			$person[$currentRow][] = $baodan;
			$person[$currentRow][] = $lottery_count;
			$person[$currentRow][] = $remark;

    		$persons[$currentRow]=$person[$currentRow];
		}
		$ids=$this->logic->lottery_data($persons,$_REQUEST[id]);
		M("lottery")->where("id={$_REQUEST[id]}")->setField("data_status",1);
		
		// echo M("lottery")->getLastsql();
		$this->ajaxReturn("ok");

	}

	#确认抽奖数据
	public function sure_contact(){
		$this->ids=$_REQUEST[ids];
		$this->data=$this->logic->sure_contact($this->ids);
		$this->display();
	}

	#撤销抽奖数据
	public function delect_contact(){
		$ids=$_REQUEST[ids];
		$db=M("lottery_apply");
		$where="id IN ({$ids}) ";
		$db->where($where)->delete();
		$this->ajaxReturn("ok");
	}

	#已导入名单查看
	public function check_apply(){
		$id=$_REQUEST[id];
		$this->data=M("lottery_apply")->where("lottery_id={$id}")->select();
		$this->display();
	}

	#已导入数据查看
	public function apply_data(){
		$id=$_REQUEST[id];
		$this->data=M("lottery_data")->where("lottery_id={$id}")->select();
		$this->display();
	}

	public function del_data(){
		$id=$_REQUEST[id];
		$res=$this->del_action("lottery_data",$id,$search);
		$this->showmsg("操作成功!");
	}

	#删除已选通讯录名单
	public function del_apply(){
		$id=$_REQUEST[id];
		$res=$this->del_action("lottery_apply",$id,$search);
		$this->showmsg("操作成功!");
	}

	#查看区域统计
	public function lottery_count(){
		$this->lotteryId=$_REQUEST[id];
		$this->data=$this->logic->lottery_count($this->lotteryId);
		$this->display();
	}

	#查看区域下代码统计
	public function code_count(){
		$this->lotteryId=$_REQUEST[id];
		$area=$_REQUEST[area];
		$this->data=$this->logic->code_count($this->lotteryId,$area);
		$this->display();
	}

	#查看中奖
	public function win_count(){
		$lotteryId=$_REQUEST[id];
		$area=$_REQUEST[area];
		$this->data=$this->logic->win_count($lotteryId,$area);
		$this->display();
	}

	#查看中奖人信息
	public function win_person(){
		$lotteryId=$_REQUEST[id];
		$data=$this->logic->win_person($lotteryId);
		$this->data=$data;
		$this->display();
	}

	#导出中奖信息
	public function excel_win(){
		$lotteryId=$_REQUEST[id];
		$search[num]=9999;
		$res=$this->logic->win_person($lotteryId,$search);

		$title = array(
			"编号",
		    "姓名",
		    "代码",
		    "手机号",
		    "所属职场",
		    "中奖等次",
		    "中奖时间"
		);
		// dump($res);exit;
		$data=array();
		$prize=array("1"=>"一等奖","2"=>"二等奖","3"=>"三等奖","4"=>"四等奖","5"=>"五等奖","6"=>"六等奖");
		foreach ($res[content] as $key => $value) {
			$win_prize=$prize[$value[win]];
			$win_time=date("Y-m-d H:i:s",$value[addtime]);
			$data[] = array($key+1,$value['name'],$value['user_id'],$value['phone'],$value[partment],$win_prize,$win_time);
		}
		// dump($data);exit;
		// $lottery=M("lottery")->where("id={$_REQUEST[id]}")->getField("title");
		$filename = "中奖信息表";
		
		excel_export($filename,$title,$data);
	}

	#导出区域统计
	public function excel_count(){

		$title = array(
			"编号",
		    "区域",
		    "代码",
		    "抽奖次数",
		);

		$res=$this->logic->lottery_count($_REQUEST[id]);
		$data=array();
		foreach ($res as $key => $value) {
			$data[] = array($key+1,$value['area'],$value['allcontact'],$value['allcount']);
		}
		$lottery=M("lottery")->where("id={$_REQUEST[id]}")->getField("title");
		$filename = $lottery.time();
		
		excel_export($filename,$title,$data);
	}

	#选择保存名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$lotteryId=$_REQUEST['id'];
		$this->logic->select_contact($lotteryId,$contactIds); 
		#选择名单状态
		$search[to_status]=2;
		$search[id]=$lotteryId;
		$this->logic->change_status($search);
		$this->ajaxReturn("ok");
	}

	#批量删除已选通讯录名单
	public function del_allapply(){
		$ids=$_REQUEST[ids];
		$res=M("lottery_apply")->where("id IN ($ids)")->delete();
		$this->ajaxReturn("ok");
	}

	public function del_alldata(){
		$ids=$_REQUEST[ids];
		$res=M("lottery_data")->where("id IN ($ids)")->delete();
		$this->ajaxReturn("ok");
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
			
			$person[$currentRow][] = $name;
			$person[$currentRow][] = $code;
			
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
			$data=$PublicLogic->report_lottery(1,$id);
		}else{
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($this->user_partment_id);

			$data=$PublicLogic->report_lottery_child(1,$ids,$id);
		}
		
		$this->lotteryId=$id;
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
		// dump($this->data);exit;
		$this->display();

	}




}
