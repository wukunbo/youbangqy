<?php
namespace Userweb\Controller;
use Think\Controller;

class PurchaseController extends BaseController{

	public function __construct(){
		parent::__construct();
		$this->logic= new \Purchase\Logic\PurchaseLogic();
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
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

	public function good_lists(){
		$search[purchase_id]=$_REQUEST[id];
		$this->data=$this->logic->good_lists($search);
		$this->display();
	}

	public function add(){
		$this->purchaseId=$_REQUEST[purchaseId];

		$search[id]=$_REQUEST['id'];
		$this->data=$this->logic->detail($search);
		$this->display();
	}

	public function add_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		// if(in_array('',$check_post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }
			 
		$post[user_id]=$this->user_child_id;
		$res=$this->logic->add_action($post,$post['id']);
		if(empty($post['id'])){
			$this->showmsg("发布成功","userweb.php?c=purchase&a=lists");
		}else{
			$this->showmsg("发布成功","userweb.php?c=purchase&a=good_lists&id=".$post['purchase_id']);
		}
        
	}

	#添加采购活动
	public function add_purchase(){
		$search[id]=$_REQUEST[id];
		$this->partment_list=$this->get_partment();

		$this->data=$this->logic->purchase_detail($search);
		$this->display();
	}

	public function purchase_action(){
		$post=$_REQUEST[post];
		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		if(in_array('',$check_post)){
			$this->showmsg("请填写完整信息",1);
		}
		$res=$this->logic->purchase_action($post,$post['id']);
        $this->showmsg("发布成功","userweb.php?c=purchase&a=lists");
	}

	public function del_purchase(){
		$id=$_REQUEST[id];
		$res=$this->del_action("purchase",$id,$search);
		$this->showmsg("操作成功!");
	}

	public function del_good(){
		$id=$_REQUEST[id];
		$res=$this->del_action("purchase_good",$id,$search);
		$this->showmsg("操作成功!");
	}

	public function purchase_status(){
		$search[id]=$_REQUEST[id];
		$search[to_status]=$_REQUEST[to_status];
		$res=$this->logic->purchase_status($search);
		if($res){
			$this->showmsg("修改成功",1);
		}
	}

	#选择名单
	public function select_list(){
		if(IS_POST){
			$contactIds=$_POST['contactIds'];
			$purchaseId=$_POST['purchaseId'];
			$this->logic->select_contact($purchaseId,$contactIds); 
			#选择通讯状态
			M("purchase")->where("id={$purchaseId}")->setField("status",2);
		}else{
			$this->partment_list=$this->get_contacts_partment();
			$this->purchaseId=$_REQUEST['id'];
			if($_REQUEST[add] && !isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("purchase_apply","purchase_id={$this->purchaseId}");
			}else if(!empty($_REQUEST[partment]) && isset($_REQUEST[partment]) && !isset($_REQUEST[add])){
				$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
			}else if(isset($_REQUEST[add]) && isset($_REQUEST[partment])){
				$this->data=$this->select_contacts("purchase_apply","purchase_id={$this->purchaseId}",$_REQUEST[partment]);
			}else{
				$this->data=$this->select_contacts();				
			}
			$this->display();
		}
		
	}

	#已导入数据查看
	public function check_apply(){
		$id=$_REQUEST[id];
		$this->data=M("purchase_apply")->where("purchase_id={$id}")->select();
		$this->display();
	}

	#删除已选通讯录名单
	public function del_apply(){
		$id=$_REQUEST[id];
		$res=$this->del_action("purchase_apply",$id,$search);
		$this->showmsg("操作成功!");
	}

	#发布活动
	public function publish_purchase(){
		M("purchase")->where("id={$_REQUEST[id]}")->setField("status",1);

		#推送消息
		$userIds=M("purchase_apply")->where("purchase_id={$_REQUEST[id]}")->getField("user_id",true);

		$purchase=M("purchase")->where("id={$_REQUEST[id]}")->field("title,image_thumb")->find();
		
		$articles[]=array(
				"title"=>$purchase[title],
				"description"=>"在线申购发布了",
				"url"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/purchase.php?id=".$_REQUEST[id],
				"picurl"=>'http://'.$_SERVER['SERVER_NAME']."/youbangqy/web/".$purchase[image_thumb]
			);

		$agentid=M("partment")->join(" INNER JOIN tl_purchase ON tl_purchase.partment=tl_partment.id")->where("tl_purchase.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);

		$this->weixin_news->send_news($userIds,$articles);


		$this->showmsg("发布成功!");


	}


	#查看统计
	public function purchase_count(){
		$purchaseId=$_REQUEST[id];
		$this->data=$this->logic->purchase_count($purchaseId);
		$this->display();
	}

	#查看采购人员统计
	public function purchase_log(){
		$goodId=$_REQUEST[id];
		$this->data=$this->logic->purchase_log($goodId);
		$this->display();
	}


	#选择保存名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$purchaseId=$_REQUEST['id'];
		$this->logic->select_contact($purchaseId,$contactIds); 
		#选择名单状态
		$purchase_status=M("purchase")->where("id={$purchaseId}")->getField("status");
		if($purchase_status==0){
			M("purchase")->where("id={$purchaseId}")->setField("status",2);
		}
		$this->ajaxReturn("ok");
	}

	#批量删除报名人员
	public function del_allapply(){
		$ids=$_REQUEST[ids];
		$res=M("purchase_apply")->where("id IN ($ids)")->delete();
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

  		$purchase_status=M("purchase")->where("id={$_REQUEST[id]}")->getField("status");
		if($purchase_status==0){
			M("purchase")->where("id={$_REQUEST[id]}")->setField("status",2);
		}

		$this->ajaxReturn("ok");
	}


	//申购协议
	public function protocol(){
		$data=M("protocol")->where("id=1")->find();
		$this->data=$data;
		$this->display();
	}

	public function add_protocol(){
		$post=$_REQUEST[post];
		M("protocol")->where("id=1")->save($post);
		$this->showmsg("操作成功");
	}

}