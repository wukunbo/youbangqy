<?php
namespace Userweb\Controller;
use Think\Controller;

class VoteController extends BaseController {

	public function __construct(){
		parent::__construct();
		$this->logic=new \Vote\Logic\VoteLogic;
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
		$this->user_id=$_SESSION[userweb][userid];
	}

	 public function add(){
		$search[id]=$_REQUEST[id];
		$data=$this->logic->detail($search);

		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}

		$this->data=$data;
        $this->display();
    }

    public function add_action(){

 		// dump($_REQUEST);exit;
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		unset($check_post['vote_count']);
		unset($check_post['start_time']);
		unset($check_post['end_time']);
		// if(in_array('',$check_post) || (empty($post['vote_count']) && $post['vote_type']==2) ||
		// ((empty($post['start_time']) || empty($post['end_time'])) && $post['type']==1) ){
		// 	$this->showmsg("请填写完整信息",1);
		// }
		// dump($post);exit;

		$res=$this->logic->add_action($post,$this->user_id,$post['id']);
        $this->showmsg("操作成功","userweb.php?c=vote&a=lists");
    }

    public function lists(){
    	$search[user_id]="";

    	$search=$_REQUEST[post];
		
		if($this->user_level == 1){
			$this->data=$this->logic->lists($search);
			$this->partment_list=$this->get_partment();
		}else{
			$search[partment]=$this->user_partment_id;
			$this->data=$this->logic->lists($search);
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		$this->display();
    }

    public function del(){
		$id=$_REQUEST[id];
		$res=$this->del_action("vote",$id,$search);
		$this->showmsg("操作成功!");
	}

	public function option_lists(){
		$search[vote_id]=$_REQUEST[vote_id];
		$this->data=$this->logic->option_lists($search);
		$this->type=M("vote")->where("id={$search[vote_id]}")->getField('type');
        $this->display();
	}

	#添加投票人
	public function option_add(){
		$this->partment_list=$this->get_partment();
		$this->voteId=$_REQUEST[vote_id];
		$data[detail]=M("vote_option")->where("id={$_REQUEST[option_id]}")->find();
		// echo M("vote_option")->getLastsql();
		// dump($data);exit;
		$this->data=$data;
		$this->display();
	}

	public function add_option(){
		$post=$_REQUEST[post];
		// if(in_array("",$post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }
		$option_id=$_REQUEST[option_id];

		$post[status]=1;
		$post[user_id]=$this->user_id;
		// dump($post);exit;
		$res=$this->logic->htoption_add_action($post,$option_id);
		if(isset($_REQUEST[add]) && $_REQUEST[add] == 1 ){
			$this->showmsg("添加成功","userweb.php?c=vote&a=option_add&vote_id={$post[vote_id]}");
		}
       $this->showmsg("操作成功","userweb.php?c=vote&a=option_lists&vote_id={$post[vote_id]}");
	}

	public function del_option(){
		$id=$_REQUEST[id];
		$res=$this->del_action("vote_option",$id,$search);
		$this->showmsg("操作成功!");
	}

	#投票人员统计
	public function vote_count(){
		$search[user_id]="";
		$search=$_REQUEST[post];
		
		$this->partment_list=$this->get_partment();

		if($this->user_level != 1){
			$search[partment]=$this->user_partment_id;
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		
		// echo $this->user_partment_id;exit;
		$this->data=$this->logic->lists($search);
		$this->display();
	}

	public function option_status(){
		$search[id]=$_REQUEST[id];
		$search[to_status]=$_REQUEST[to_status];
		$res=$this->logic->option_status($search);
		if($res){
			$this->showmsg("修改成功",1);
		}
	}

	//选择投票名单
	public function select_list(){
		if(IS_POST){
			$contactIds=$_POST['contactIds'];
			$voteId=$_POST['voteId'];
			$this->logic->select_contact($voteId,$contactIds); 
			#选择名单状态
			M("vote")->where("id={$voteId}")->setField("status",2);
			$this->ajaxReturn("ok");
		}else{
			$this->partment_list=$this->get_contacts_partment();
			$this->voteId=$_REQUEST['id'];
			if($_REQUEST[add] && !isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("vote_apply","vote_id={$_REQUEST['id']}");
			}else if(!empty($_REQUEST[partment]) && isset($_REQUEST[partment]) && !isset($_REQUEST[add])){
				$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
			}else if(isset($_REQUEST[add]) && isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("vote_apply","vote_id={$_REQUEST['id']}",$_REQUEST[partment]);
			}else{
				$this->data=$this->select_contacts();				
			}
			$this->display();
		}
	}

	#已选通讯录名单
	public function check_apply(){
		$id=$_REQUEST["id"];
		$this->show=$_REQUEST["show"];
		$search=array();
		$data[all][lists]=M("vote_apply")->where("vote_id={$id}")->select();

		$this->data=$data;
		$this->open_status=M("vote")->where("id={$id}")->getField("open_status");
		$this->display();
	}

	#对外开放状态
	public function open_status(){
		$id=$_REQUEST[id];
		$open_status=$_REQUEST[open_status];
		M("vote")->where("id={$id}")->setField("open_status",$open_status);
		$this->ajaxReturn("ok");
	}

	#删除已选通讯录名单
	public function del_apply(){
		$id=$_REQUEST[id];
		$res=$this->del_action("vote_apply",$id,$search);
		$this->showmsg("操作成功!");
	}

	#批量删除已选通讯录名单
	public function del_allapply(){
		$ids=$_REQUEST[ids];
		$res=M("vote_apply")->where("id IN ($ids)")->delete();
		$this->ajaxReturn("ok");
	}


	#发布活动
	public function publish_vote(){
		$search[to_status]=1;
		$search[id]=$_REQUEST[id];
		

		#推送消息
		$userIds=M("vote_apply")->where("vote_id={$_REQUEST[id]}")->getField("user_id",true);

		$vote=M("vote")->where("id={$_REQUEST[id]}")->field("title,image_thumb,type,is_pc")->find();
		
		$url='http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/vote.php?c=index&a=show&id=".$_REQUEST[id];
		
		$articles[]=array(
				"title"=>$vote[title],
				"description"=>"投票活动发布了",
				"url"=>$url,
				"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$vote[image_thumb]
			);
		$agentid=M("partment")->join(" INNER JOIN tl_vote ON tl_vote.partment=tl_partment.id")->where("tl_vote.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->send_news($userIds,$articles);
		// dump($res);exit;
		if($res["errmsg"] != "ok"){
			if($res[errcode]==40032){
				$this->showmsg("发布推送人数超出1000人限制,请筛选人数");
			}
			$this->showmsg("发布失败，所选名单不在应用范围内");
		}

		M("vote")->where("id={$_REQUEST[id]}")->setField("status",1);

		$this->showmsg("发布成功!");
	}


	#再次推送
	public function again_publish(){
		$agentid=M("partment")->join(" INNER JOIN tl_vote ON tl_vote.partment=tl_partment.id")->where("tl_vote.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_vote($_REQUEST[id]);
		if($res["errmsg"] != "ok"){
			if($res[errcode]==40032){
				$this->showmsg("发布推送人数超出1000人限制,请筛选人数");
			}
			$this->showmsg("推送失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送成功!");
		}
	}

	#预览功能
	public function preview(){
		$agentid=M("partment")->join(" INNER JOIN tl_vote ON tl_vote.partment=tl_partment.id")->where("tl_vote.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_vote($_REQUEST[id],array($this->user_wx_id));
		if($res["errmsg"] != "ok"){
			$this->showmsg("推送失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送成功!");
		}
	}


	#下架活动
	public function out_vote(){
		$search[to_status]=-1;
		$search[id]=$_REQUEST[id];
		M("vote")->where("id={$_REQUEST[id]}")->setField("status",-1);


		$this->showmsg("操作成功!");
	}


	//投票记录统计
	public function vote_log(){
		$voteId=$_REQUEST[id];
		$this->data=$this->logic->vote_log($voteId);
		$this->display();
	}


	#导出投票记录
	public function excel_votelog(){
		$title = array(
			"编号",
		    "姓名",
		    "投票时间",
		    "手机号码",
		    "区域",
		    "部门",
		    "职位",
		    "投票人"
		);

		$voteId=$_REQUEST[id];
		$search[num]=999999;
		$res=$this->logic->vote_log($voteId,$option_id,$search);
		// dump($res);exit;
		$data=array();
		foreach ($res[content] as $key => $value) {
			$data[] = array($key+1,$value['name'],$value['day'],$value['phone'],$value['area'],$value['partment'],$value['job'],$value['truename']);
		}
		$vote=M("vote")->where("id={$voteId}")->getField("title");
		$filename = $vote."--投票记录".time();
		
		excel_export($filename,$title,$data);
	}

	#现场PC端投票
	public function pc_add(){
		$search[id]=$_REQUEST[id];

		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}

		$this->data=$this->logic->detail($search);
		$this->display();
	}

	public function addpc_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		unset($check_post['vote_count']);
		// if(in_array('',$check_post) || (empty($post['vote_count']) && $post['vote_type']==2) ){
		// 	$this->showmsg("请填写完整信息",1);
		// }
		$post[is_pc]=1;
		$res=$this->logic->add_action($post,$this->user_id,$post['id']);
        $this->showmsg("操作成功","userweb.php?c=vote&a=lists");
    }

    #导出投票统计
	public function excel_count(){
		$title = array(
			"编号",
		    "标题",
		    "投票时间",
		    "访问量",
		    "已投数量",
		);

		$res=$this->data=$this->logic->lists($search);
		$data=array();
		foreach ($res[content] as $key => $value) {
			$data[] = array($key+1,$value['title'],date("Y-m-d",$value['time_start']),$value['count_view'],$value['count_vote']);
		}
		$filename = '投票统计表'.time();
		
		excel_export($filename,$title,$data);
	}

	#导出详细记录
	public function excel_option(){

		$title = array(
			"编号",
			"参数人姓名",
		    "浏览次数",
		    "票数",
		    "投票人姓名",
		    "投票时间",
		);
		$search[vote_id]=$_REQUEST[id];
		$search[num]=9999;
		$res=$this->logic->option_lists($search);
		// dump($data);exit;
		$data=array();
		$search[num]=999999;
		foreach ($res[content] as $key => $value) {
			$res1=$this->logic->vote_log($_REQUEST[id],$value[id],$search);
			$names="";
			foreach ($res1[content] as $key1 => $value1) {
				$names=$names.$value1[name].",";
			}
			// dump($res1);exit;
			$data[]=array($key,$value[truename],$value[count_view],$value[count_vote],$names,$res1[content][0][day]);
		}

		$filename = '详细记录表'.time();
		
		excel_export($filename,$title,$data);

	}

	#导出结果记录
	public function excel_result(){

		$title = array(
			"编号",
			"参数人姓名",
		    "票数(总数)",
		    "排名",
		);

		$search[vote_id]=$_REQUEST[id];
		$search[num]=999999;
		$search[order]="tl_vote_option.count_vote desc ";
		$res=$this->logic->option_lists($search);
		// dump($res);exit;
		
		$data=array();
		foreach ($res[content] as $key => $value) {
			$data[]=array($key,$value[truename],$value[count_vote],$value[paiming]);
		}

		$filename = '结果记录表'.time();
		
		excel_export($filename,$title,$data);

	}


	#导出投票人员名单
	public function excel_vote(){
		// echo $_REQUEST[id];
	}

	#选择通讯录名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$voteId=$_REQUEST['id'];
		$this->logic->select_contact($voteId,$contactIds); 
		#选择名单状态
		$status=M("vote")->where("id={$voteId}")->getField("status");
		if($status!=1){
			M("vote")->where("id={$voteId}")->setField("status",2);
		}
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


	#导入投票人
	public function to_option(){
		$filePath=$_FILES["xlsfile"]["tmp_name"];


		// $filePath="Uploads/excel/to_option.xlsx";
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

		$vote_id=$_REQUEST[vote_id];

		$option = array();  //声明数组

		/**从第二行开始输出，因为excel表中第一行为列名*/ 
  		for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
  			/**ord()将字符转为十进制数*/
			$truename = $currentSheet->getCellByColumnAndRow(ord("A") - 65,$currentRow)->getValue();
			$code_name = $currentSheet->getCellByColumnAndRow(ord("B") - 65,$currentRow)->getValue();
			$code = $currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$content = $currentSheet->getCellByColumnAndRow(ord("D") - 65,$currentRow)->getValue();
			
			$option[truename] = $truename;
			$option[code_name] = $code_name;
			$option[code] = $code;
			$option[content] = $content;
			$option[vote_id] = $vote_id;
			$option[user_id] = $this->user_id;

			// dump($option);exit;
			
    		$res=$this->logic->htoption_add_action($option,$option_id);
    		// dump($res);exit;
  		}
		$this->ajaxReturn("ok");
	}

	public function detail_status(){
		$id=$_REQUEST[id];
		M("vote")->where("id={$id}")->setField("detail_status",$_REQUEST[status]);
		$this->showmsg("操作成功");
	}


	public function vote_qrcode(){
		$id=$_REQUEST[id];
		$this->url='http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/vote.php?c=index&a=show&id=".$id;
		$this->display();
	}




}