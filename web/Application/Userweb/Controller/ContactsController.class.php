<?php
namespace Userweb\Controller;
use Think\Controller;

class ContactsController extends BaseController{

	public function __construct(){
		parent::__construct();
		$this->logic=new \Userweb\Logic\ContactsLogic();
	}

	public function add(){
		$id=$_REQUEST['id'];
		$search[id]=$id;
		$this->data=$this->logic->add($search);
		$this->display();
	}

	public function add_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		if(in_array('',$check_post)){
			$this->showmsg("请填写完整信息",1);
		}

		$res=$this->logic->add_action($post);
		$this->showmsg("发布成功","userweb.php?c=contacts&a=lists");
	}

	public function lists(){
		$partment=$_REQUEST[partment];
		$this->partment_list=$this->get_contacts_partment();

		
		$ids=implode(",",$this->child($partment));
		// echo $ids;exit;
		$GLOBALS["id"]=array();
		$search[partment]=$ids;

		if (!empty($partment)) {
			$this->partmentUrl=$this->logic->get_partmentName($partment);
		}

		if(!empty($_REQUEST[user_code])){
			$search[user_code]=$_REQUEST[user_code];
			// dump($_REQUEST);exit;
		}
		
		$this->data=$this->logic->lists($search);
		$this->display();
	}

	public function search_partment(){
		$post=$_REQUEST[post];
		$user_code=$_REQUEST[user_code];
		$this->redirect("Contacts/lists",array("partment"=>$post[partment],"user_code"=>$user_code));

	}

	public function get_partmentName($partmentId){
		$db=M("contacts_partment");
		$title1=$db->where("id={$partmentId}")->find();
	}

	public function out_excel(){
		$title = array(
			"编号",
		    "姓名",
		    "电话",
		    "身份证",
		    "代码",
		    "区域",
		    "组别",
		    "部门",
		    "职位",
		);
		$data=array();
		$res=M("contacts")->select();
		foreach ($res as $key => $value) {
			$data[] = array($key+1,$value['name'],$value['phone'],$value['idcard'],$value['code'],$value['area'],$value['work_group'],$value['partment'],$value['job']);
		}
		$filename = '通讯录名单';
		
		excel_export($filename,$title,$data);
	}

	#读取excel文件导入通讯录
	public function to_contacts(){

		$filePath=$_FILES["xlsfile"]["tmp_name"];
		// $filePath="Uploads/contacts.xlsx";
		if(file_exists($filePath))
			echo "当前目录中，文件".$filePath."存在";
		else
			echo "不存在";

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
		    echo 'no Excel'; 
		    return; 
		  } 
		} 

		$PHPExcel = $PHPReader->load($filePath); 
		$currentSheet = $PHPExcel->getSheet(0);  //读取excel文件中的第一个工作表
		$allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
		$allRow = $currentSheet->getHighestRow(); //取得一共有多少行
		echo $allColumn."--".$allRow;
		$person = array();  //声明数组
  
  		/**从第二行开始输出，因为excel表中第一行为列名*/ 
  		for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
  			/**从第A列开始输出*/ 
    		for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){
    			/**ord()将字符转为十进制数*/
    			$val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();
    			$person[$currentRow][] = $val; 
    		}
    		$persons[$currentRow]=$person[$currentRow];
		}
		$db=M("contacts");
		$addtime=time();
		foreach ($persons as $key => $person) {
			$datalist=array("name"=>$person[0],"phone"=>$person[1],"idcard"=>$person[2],"code"=>$person[3],"area"=>$person[4],"work_group"=>$person[5],"partment"=>$person[6],"job"=>$person[7],"addtime"=>$addtime);
			$db->add($datalist);
		}

	}

	#导入微信通讯录
	public function weixin_contacts(){
		set_time_limit(0);
		$Function=D('FunctionService');
		$GLOBALS['pf']=$Function;
		$Get=D('GetService');
		$Get->config($this->wxdata);

		//获取部门
		$data=$Get->get_wx_partment();
		$res=$this->logic->weixin_partment($data);

		#获取部门成员
		$db=M("contacts_partment");
		$partment=$db->field("id,name")->select();

		// dump($partment);exit;
		// 
		$data=$Get->get_wx_contacts(1);
		$res=$this->logic->weixin_contacts($data);
		
		// foreach ($partment as $key => $value) {
		// 	$data=$Get->get_wx_contacts($value[id]);
		// 	// dump($data);exit;
		// 	$res=$this->logic->weixin_contacts($data);
		// 	// dump($res);exit;
		// }

		$req[status]=10001;
		echo json_encode($req);
	}


	#手动通讯录
	public function test_contacts(){
		$stime=microtime(true);
		set_time_limit(0);
		$Function=D('FunctionService');
		$GLOBALS['pf']=$Function;
		$Get=D('GetService');
		$Get->config($this->wxdata);

		// echo M("contacts")->count();exit;

		$data=$Get->get_wx_contacts(1);

		// dump($data);exit;
		$res=$this->logic->weixin_contacts($data);

		$etime=microtime(true);

		$total=$etime-$stime;  //计算差值
		echo $total.'秒<br>';

		echo "更新完毕";

		// dump($res);exit;

		//获取部门
		// $data=$Get->get_wx_partment();
		// dump($data);exit;
		// $res=$this->logic->weixin_partment($data);


	}

	#选择通讯录部门
	public function get_select_partment(){
		$partmentid=$_REQUEST[partmentid];
		$partment_list=$this->get_contacts_partment($partmentid);
		if(!$partment_list){
			exit;
		}
		$html='<select class="form-control  sel" name="post[partment]" id="partment'.$partmentid.'" style="width: 100px" onchange="select_partment('.$partmentid.')">';
		$html.='<option value="'.$partmentid.'">请选择</option>';
		foreach ($partment_list as $key => $partment) {
			$html.='<option value="'.$partment[id].'">'.$partment[name].'</option>';
		}
		echo $html;	
	}

	// public function test(){
	// 	$this->display();
	// }

	public function select_list(){

		$this->partment_list=$this->get_contacts_partment();
		$this->id=$_REQUEST['id'];
		$table=$_REQUEST[t];
		if($_REQUEST[add] && !isset($_REQUEST[partment])){
			$this->data=$this->select_contacts("{$table}_apply","{$table}_id={$this->id}");
		}else if(!empty($_REQUEST[partment]) && isset($_REQUEST[partment]) && !isset($_REQUEST[add])){
			$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
		}else if(isset($_REQUEST[add]) && isset($_REQUEST[partment])){
			$this->data=$this->select_contacts("{$table}_apply","{$table}_id={$this->id}",$_REQUEST[partment]);
		}else{
			$this->data=$this->select_contacts();				
		}
		$this->display();	
	}

	#选择通讯录
	public function select_contact(){

		$ids=-1;//全部

		if($this->user_child_id && $this->user_level==2){
			$partment_id=M("user_child")->where("id='".$this->user_child_id."'")->getField("partment_id");
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($partment_id);
		}

		$data=$this->logic->select_left(1,$ids);
		$this->data=$data;
		$this->display();
	}

	#选择抽奖区域管理员
	public function select_area(){

		$ids=-1;//全部

		if($this->user_child_id && $this->user_level==2){
			$partment_id=M("user_child")->where("id='".$this->user_child_id."'")->getField("partment_id");
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($partment_id);
		}

		$data=$this->logic->select_left(1,$ids);
		$this->data=$data;
		$this->display();
	}


	#ajax获取通讯录
	public function ajax_contact(){
		$partmentId=$_REQUEST[partmentid];
		$ids=implode(",",$this->child($partmentId));
		$GLOBALS["id"]=array();
		$search[partment]=$ids;

		$PartmentLogic=new \Userweb\Logic\PartmentLogic();
		$user_partment=$PartmentLogic->get_child_partment_ids($this->user_partment_id);
		$search[user_partment]=$user_partment;

		$type=isset($_REQUEST['type']) ? $_REQUEST[type] : 'checkbox';
		$html=$this->logic->ajax_lists($search,$type);
		$this->ajaxReturn($html);
	}

	public function ajax_name(){
		$name=$_REQUEST["name"];
		$type=isset($_REQUEST['type']) ? $_REQUEST[type] : 'checkbox';
		$html=$this->logic->ajax_name($name,$type);
		$this->ajaxReturn($html);
	}

	public function ajax_contact_name(){
		$wx_userid=$_REQUEST['wx_id'];
		$name=M("contacts")->where("wx_userid={$wx_userid}")->getField("name");
		$this->ajaxReturn($name);
	}

	public function test1(){

	}

	public function select_contacts_partment(){
		$this->display();
	}

	#选择名单
	public function choose_contact(){
		$ids=-1;//全部

		if($this->user_child_id && $this->user_level==2){
			$partment_id=M("user_child")->where("id='".$this->user_child_id."'")->getField("partment_id");
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($partment_id);//
		}
		$data=$this->logic->select_left(1,$ids);
		$this->data=$data;
		$this->display();
	}

	#选择可见范围
	public function select_partment(){
		$data=$this->logic->select_left(1);
		$this->data=$data;
		//dump($data);exit;
		$this->display();
	}

	#删除通讯录
	public function delete_contact(){
		$id=$_REQUEST[id];
		M("contacts")->where("id={$id}")->delete();
		$this->showmsg("操作成功");
	}

	public function add_contacts(){
		$this->partment_list=$this->get_contacts_partment();
		$this->display();
	}

	public function contacts_action(){
		$post=$_REQUEST[post];
		// dump($post);exit;
		$post[partment_id]=$post[partment];
		unset($post[partment]);
		$post[addtime]=time();
		M("contacts")->add($post);
		$this->showmsg("操作成功","userweb.php?c=Contacts&a=lists&menu_app=Contacts");
	}

}