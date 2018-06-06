<?php
namespace Userweb\Controller;
use Think\Controller;

class CollectController extends BaseController{

	public function __construct(){
		parent::__construct();
		$this->logic= new \Collect\Logic\CollectLogic();
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
	}

	public function add(){
		$search[id]=$_REQUEST[id];
		$this->partment_list=$this->get_partment();
		$this->data=$this->logic->detail($search);

		if($_REQUEST[id]){
			$this->collect_extend=M("collect_extend_field")->where("collect_id={$_REQUEST[id]}")->select();
		}

		$this->display();
	}

	public function add_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		// if(in_array('',$check_post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }

		// dump($post);exit;

		$post[user_id]=$this->user_child_id;
		$res=$this->logic->add_action($post,$post['id']);

		//收集扩展字段
		$posts=$_REQUEST[post1][extend];
		if($post[id]){
			$collect_id=$post[id];
		}else{
			$collect_id=$res[id];
		}
		$this->logic->add_collect_extend($collect_id,$posts);

		// dump($res);exit;
        $this->showmsg("发布成功","userweb.php?c=collect&a=lists");
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

	public function del_purchase(){
		$id=$_REQUEST[id];
		$res=$this->del_action("collect",$id,$search);
		$this->showmsg("操作成功!");
	}

	#选择名单
	public function select_list(){
		if(IS_POST){
			$contactIds=$_POST['contactIds'];
			$collectId=$_POST['collectId'];
			$this->logic->select_contact($collectId,$contactIds); 
			#选择通讯状态
			M("collect")->where("id={$collectId}")->setField("status",2);
			$this->ajaxReturn("ok");
		}else{
			$this->partment_list=$this->get_contacts_partment();
			$this->collectId=$_REQUEST['id'];
			if($_REQUEST[add] && !isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("collect_apply","collect_id={$this->collectId}");
			}else if(!empty($_REQUEST[partment]) && isset($_REQUEST[partment]) && !isset($_REQUEST[add])){
				$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
			}else if(isset($_REQUEST[add]) && isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("collect_apply","collect_id={$this->collectId}",$_REQUEST[partment]);
			}else{
				$this->data=$this->select_contacts();				
			}
			$this->display();
		}
		
	}

	#已导入数据查看
	public function check_apply(){
		$id=$_REQUEST[id];
		$this->data=M("collect_apply")->where("collect_id={$id}")->select();
		$this->display();
	}

	#删除已选通讯录名单
	public function del_apply(){
		$id=$_REQUEST[id];
		$res=$this->del_action("collect_apply",$id,$search);
		$this->showmsg("操作成功!");
	}

	#发布活动
	public function publish_collect(){
		M("collect")->where("id={$_REQUEST[id]}")->setField("status",1);

		#推送消息
		$userIds=M("collect_apply")->where("collect_id={$_REQUEST[id]}")->getField("user_id",true);

		$collect=M("collect")->where("id={$_REQUEST[id]}")->field("title,image_thumb")->find();
		
		$articles[]=array(
				"title"=>$collect[title],
				"description"=>"收集信息活动发布了",
				"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/collect.php?id=".$_REQUEST[id],
				"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$collect[image_thumb]
			);

		$agentid=M("partment")->join(" INNER JOIN tl_collect ON tl_collect.partment=tl_partment.id")->where("tl_collect.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);

		$this->weixin_news->send_news($userIds,$articles);


		$this->showmsg("发布成功!");


	}

	#再次推上发布活动
	public function again_publish_collect(){
		// M("collect")->where("id={$_REQUEST[id]}")->setField("status",1);

		#推送消息
		$userIds=M("collect_apply")->where("collect_id={$_REQUEST[id]}")->getField("user_id",true);

		$collect=M("collect")->where("id={$_REQUEST[id]}")->field("title,image_thumb")->find();
		
		$articles[]=array(
				"title"=>$collect[title],
				"description"=>"收集信息活动发布了",
				"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/collect.php?id=".$_REQUEST[id],
				"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$collect[image_thumb]
			);

		$agentid=M("partment")->join(" INNER JOIN tl_collect ON tl_collect.partment=tl_partment.id")->where("tl_collect.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);

		$this->weixin_news->send_news($userIds,$articles);


		$this->showmsg("再次推送成功!");


	}


	#已收集人员信息
	public function person_list(){
		$collectId=$_REQUEST[id];
		$area=$_REQUEST[area];
		$this->data=$this->logic->person_list($collectId,$area);
		$this->display();
	}

	#收集人员区域统计
	public function collect_count(){
		$this->collectId=$_REQUEST[id];
		$this->data=$this->logic->collect_count($this->collectId);
		$this->display();
	}

	#收集人员区域代码统计
	public function code_count(){
		$this->collectId=$_REQUEST[id];
		$code=$_REQUEST[area];
		$this->data=$this->logic->code_count($this->collectId,$code);
		$this->display();
	}

	#选择保存名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$collectId=$_REQUEST['id'];
		$this->logic->select_contact($collectId,$contactIds); 
		#选择名单状态
		$collect_status=M("collect")->where("id={$collectId}")->getField("status");
		if($collect_status==0){
			M("collect")->where("id={$collectId}")->setField("status",2);
		}
		$this->ajaxReturn("ok");
	}

	#批量删除报名人员
	public function del_allapply(){
		$ids=$_REQUEST[ids];
		$res=M("collect_apply")->where("id IN ($ids)")->delete();
		// echo M("parchase_apply")->getLastsql();exit;
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
			// $hint = $currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$person[$currentRow][] = $name;
			$person[$currentRow][] = $code;
			// $person[$currentRow][] = $hint;

    		$persons[$currentRow]=$person[$currentRow];
  		}
  		$this->logic->to_apply($persons,$_REQUEST[id]);

  		$collect_status=M("collect")->where("id={$_REQUEST[id]}")->getField("status");
		if($collect_status==0){
			M("collect")->where("id={$_REQUEST[id]}")->setField("status",2);
		}

		$this->ajaxReturn("ok");
	}


	#报表统计
	public function report(){
		$id=$_REQUEST["id"];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		if($this->user_level == 1){
			$data=$PublicLogic->report_collect(1,$id);
		}else{
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($this->user_partment_id);

			$data=$PublicLogic->report_collect_child(1,$ids,$id);
		}
		
		$this->collectId=$id;
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
		$data=$this->logic->detail_person($id,$ids);

		foreach ($data[content] as $key => &$value) {
			$value[extend]=M("collect_extend_info")->where("person_id={$value[id]}")->order("extend_id ASC")->select();
		}
		// dump($data);exit;
		$this->data=$data;
		$this->collect_extend=M("collect_extend_field")->where("collect_id={$id}")->order("id ASC")->select();

		$this->display();

	}


	#导出详细统计
	public function excel_person(){
		$id=$_REQUEST["id"];

		$title = array(
			"编号",
			"提供代码",
		    "提供者姓名",
		);

		$collect_extend=M("collect_extend_field")->where("collect_id={$id}")->order("id ASC")->select();
		foreach ($collect_extend as $key => $value) {
			$title[]=$value[name];
		}

		$title[]="收集时间";

		
		// $join="INNER JOIN tl_collect_apply ON tl_collect_apply.user_id=tl_collect_person.user_id ";
		$res=M("collect_person")->where("tl_collect_person.collect_id={$id}")->field("tl_collect_person.*")->select();
		// dump($res);exit;
		// echo M("collect_person")->getLastsql();exit;
		$data=array();
		foreach ($res as $key => $vo) {
			$extend=M("collect_extend_info")->where("person_id={$vo[id]}")->order("extend_id ASC")->select();
			// dump($extend);

			
			$data[$key][] = $key+1;
			$data[$key][] = $vo[user_id];
			$data[$key][] = M("collect_apply")->where("user_id='{$vo[user_id]}' ")->getField('truename');

			foreach ($extend as $key1 => $value1) {
				// dump($value1);exit;
				$data[$key][]=$value1[value];
			}

			$addtime=empty($vo[addtime]) ? "" : date("Y-m-d",$vo['addtime']);
			$data[$key][]=$addtime;

		}

		// dump($data);exit;
		$filename = '收集详细统计表'.time();
		
		excel_export($filename,$title,$data);
	}

	//删除
	public function del_collect(){
		M("collect")->where("id={$_REQUEST[id]}")->delete();
		$this->showmsg("操作成功");
	}


	//收集协议
	public function protocol(){
		$id=$_REQUEST[id];
		$data=M("protocol")->where("type=2 AND link_id={$id}")->find();
		$this->data=$data;
		$this->display();
	}

	public function add_protocol(){
		$post=$_REQUEST[post];
		// M("protocol")->where("id=2")->save($post);
		if($post[id]){
			M("protocol")->where("id={$post[id]}")->save($post);
		}else{
			$post[type]=2;
			M("protocol")->add($post);
		}
		$this->showmsg("操作成功","userweb.php?c=Collect&a=lists&menu_app=Purchase");
	}

}