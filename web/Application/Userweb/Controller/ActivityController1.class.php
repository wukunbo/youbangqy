<?php
namespace Userweb\Controller;
use Think\Controller;

class ActivityController extends BaseController{

	public function __construct(){
		parent::__construct();

		$this->Logic= new \Activity\Logic\ActivityLogic();
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
	}

	public function lists(){
		$categoryLogic= new \Activity\Logic\CategoryLogic();
		$this->category_list=$categoryLogic->get_category();
		

		$search=$_REQUEST[post];
		if($this->user_level == 1){
			$data=$this->Logic->lists(-1,$search);
			$this->partment_list=$this->get_partment();
		}else{
			$data=$this->Logic->lists($this->user_child_id,$search);
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		
		$this->data=$data;
		$this->display();
	}	   

	public function add(){
		$categoryLogic= new \Activity\Logic\CategoryLogic();
		$this->category_list=$categoryLogic->get_category();
		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}

		$id=$_REQUEST["id"];
		$this->data=$this->Logic->detail($id=$id,$this->user_id);
		if(empty($this->data[detail]['baidu_lng']) || empty($id)){
			$this->mylng=114.038158;
			$this->mylat=22.541943;
		}else{
			$this->mylng=$this->data[detail][baidu_lng];
			$this->mylat=$this->data[detail][baidu_lat];
		}
		
		$this->display();
	}

	public function del(){
 
		$id=$_REQUEST[id];
		$status=M("activity")->where("id={$id}")->getField("status");
		if($this->user_level==2 && $status==1){
			$this->showmsg("活动已发布，您无权删除哦");
		}
		$res=$this->del_action("activity",$id,$search);
		$this->showmsg("操作成功!");
 
	}

	#删除活动类型
	public function del_category(){
 
		$id=$_REQUEST[id];
		$res=M("activity_category")->where("cate_id='".$id."'")->delete();
		$this->showmsg("操作成功!");
 
	}

	#删除部门
	public function del_partment(){
 
		$id=$_REQUEST[id];
		$res=M("partment")->where("id='".$id."'")->delete();
		$this->showmsg("操作成功!");
 
	}

	public function add_action(){
		$post=$_REQUEST[post];

		$check_post=$_REQUEST[post];
		unset($check_post['id']);
		// if(in_array('',$check_post)){
		// 	// echo "<script>window.history.go(-1)</script>";
		// 	$this->showmsg("请填写完整信息",-1);

		// }

		$post[intro]= str_replace(PHP_EOL, '', $post[intro]);//去除回车换行

		$res=$this->Logic->add($this->user_child_id,$post,$this->user_wx_id);

		if(!$post[id]){
			$url = 'http://'.$_SERVER['SERVER_NAME'].'/youbang/web/activity.php?c=activity&a=sign_in&id='.$res[id];
			$detail=add_qcr_url($url);
			M('activity')->where("id='".$res[id]."'")->setField("qccode",$detail['pic']);
		}
		
		$this->showmsg("保存成功","userweb.php?c=activity&a=lists");
	}

	public function change_status(){
		$search[to_status]=$_REQUEST[to_status];
		$search[id]=$_REQUEST[id];
		$res=$this->Logic->change_status($search);
		$this->showmsg("更改成功","1");
	}

	#报名名单
	public function check_apply(){
		$id=$_REQUEST["id"];
		$this->show=$_REQUEST["show"];
		$search=array();
		$data[all]=$this->Logic->apply_log($id=$id,$search);

		$this->open_status=M("activity")->where("id={$id}")->getField("open_status");
		$this->open_sign=M("activity")->where("id={$id}")->getField("open_sign");
		$this->only_my=M("activity")->where("id={$id}")->getField("only_my");

		$this->data=$data;
		$this->display();
	}

	#删除报名人员
	public function del_apply(){
		$id=$_REQUEST[id];
		$is_able=M("activity_apply")->where("id={$id}")->getField("is_able");
		if($is_able==1){
			$this->showmsg("该人员已经报名，不能进行删除哦!");
		}
		$res=$this->del_action("activity_apply",$id,$search);
		$this->showmsg("操作成功!");
	}

	#批量删除报名人员
	public function del_allapply(){
		$ids=$_REQUEST[ids];
		// $res=M("activity_apply")->where("id IN ($ids)")->delete();
		$ids=explode(",",$ids);
		$db=M("activity_apply");
		foreach ($ids as $key => $id) {
			$db->where("id={$id} AND is_able!=1")->delete();
		}
		$this->ajaxReturn("ok");
	}

	#报名统计
	public function apply_count(){

		$categoryLogic= new \Activity\Logic\CategoryLogic();
		$this->category_list=$categoryLogic->get_category();
		
		$search=$_REQUEST[post];

		if($this->user_level == 1){
			$data=$this->Logic->apply_count($user_id=-1,$search); 
			$this->partment_list=$this->get_partment();
		}else{
			$search[partment]=$this->user_partment_id;
			$data=$this->Logic->apply_count($user_id=-1,$search); 
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		// $data=$this->Logic->apply_count($user_id=-1,$search); 
		$this->data=$data;
		$this->display();
	}

	#部门统计
	public function all_count(){
		$this->activityId=$_REQUEST[id];
		$this->data=$this->Logic->all_count($this->activityId);
		$this->display();
	}

	#区域统计
	public function area_count(){
		$this->activityId=$_REQUEST[id];
		$partmentId=$_REQUEST[partment];
		$this->data=$this->Logic->area_count($this->activityId,$partmentId);
		$this->display();
	}

	#区域统计导出
	public function excel_areaCount(){
		$this->activityId=$_REQUEST[id];
		$title = array(
			"编号",
		    "区域",
		    "参与人数",
		    "报名人数",
		    "报名率",
		    "出席人数",
		    "出席率",
		);

		$res=$this->Logic->area_count($this->activityId);
		$data=array();
		foreach ($res[join] as $key => $value) {
			$applycount= empty($res[apply][$key][allcontact]) ? 0 : $res[apply][$key][allcontact];
			$applyrate=round($applycount/$value['allcontact']*100,2);
			$data[] = array($key+1,$value['area'],$value['allcontact'],$applycount,$applyrate."%",0,"0%");
		}
		$activity=M("activity")->where("id={$this->activityId}")->getField("title");
		$filename = $activity."-报名统计表".time();
		
		excel_export($filename,$title,$data);
	}

	#详细统计
	public function detail_count(){
		$activityId=$_REQUEST[id];
		$code=$_REQUEST[code];
		$this->data=$this->Logic->detail_count($activityId,$code);
		$this->display();
	}

	#代码区域统计
	public function code_count(){
		$this->activityId=$_REQUEST[id];
		$code=$_REQUEST[code];
		$this->data=$this->Logic->code_count($this->activityId,$code);
		$this->display();
	}
	

	#选择名单
	public function select_list(){
		if(IS_POST){
			$contactIds=$_POST['contactIds'];
			$activityId=$_POST['activityId'];
			$this->Logic->select_contact($activityId,$contactIds); 
			#选择名单状态
			$search[to_status]=2;
			$search[id]=$activityId;
			$this->Logic->change_status($search);
			$this->ajaxReturn("ok");
		}else{
			$this->partment_list=$this->get_contacts_partment();
			$this->activityId=$_REQUEST['id'];
			if($_REQUEST[add] && !isset($_REQUEST[partment])){
				// $this->data=$this->select_contacts("activity_apply","activity_id={$this->activityId}");
				$this->data=$this->select_contacts();

			}else if(!empty($_REQUEST[partment]) && isset($_REQUEST[partment]) && !isset($_REQUEST[add])){
				$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
			}else if(isset($_REQUEST[add]) && isset($_REQUEST[partment])){
				// $join.=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_activity_apply.user_id";
				// $subsql=M('activity_apply')->join($join)->field("tl_contacts.id")->where("activity_id={$this->activityId}")->select(false);
				// $this->data=M("contacts")->where("id NOT IN ({$subsql}) AND tl_contacts.partment_id={$_REQUEST[partment]} ")->select();
				$this->data=M("contacts")->where("partment_id={$_REQUEST[partment]}")->select();
			}else{
				$this->data=$this->select_contacts();				
			}

			$this->display();
		}
		
	}

	#选择保存名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$activityId=$_REQUEST['id'];
		$this->Logic->select_contact($activityId,$contactIds); 
		#选择名单状态
		$activity_status=M("activity")->where("id={$_REQUEST[id]}")->getField("status");
		if(empty($activity_status)){
			$search[to_status]=2;
			$search[id]=$activityId;
			$this->Logic->change_status($search);
		}
		
		$this->ajaxReturn("ok");
	}

	#发布活动
	public function publish_activity(){
		
		$search[to_status]=1;
		$search[id]=$_REQUEST[id];
		$time_apply=M("activity")->where("id={$search[id]}")->getField("time_apply");
		if($time_apply < time()){
			$this->showmsg("报名时间已过!",1);
		}
		
		$url = 'http://'.$_SERVER['SERVER_NAME'].'/youbang/web/activity.php?c=activity&a=sign_out&id='.$_REQUEST[id];
		$detail=add_qcr_url($url);
		M('activity')->where("id='".$_REQUEST[id]."'")->setField("signout",$detail['pic']);

		#推送消息
		$agentid=M("partment")->join(" INNER JOIN tl_activity ON tl_activity.partment=tl_partment.id")->where("tl_activity.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_activity($_REQUEST[id]);

		// dump($res);exit;
		
		if($res["errmsg"] != "ok"){
			// dump($res);exit;
			$this->showmsg("发布失败，所选名单不在应用范围内");
		}

		$this->Logic->change_status($search);
		M("activity")->where("id={$search[id]}")->setField("publishtime",time());

		$this->showmsg("发布成功!");
	}

	#再次推送
	public function again_publish(){
		$agentid=M("partment")->join(" INNER JOIN tl_activity ON tl_activity.partment=tl_partment.id")->where("tl_activity.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_activity($_REQUEST[id]);
		// dump($res);exit;
		if($res["errmsg"] != "ok"){
			$this->showmsg("推送失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送成功!");
		}
	}

	#预览功能
	public function preview(){
		$agentid=M("partment")->join(" INNER JOIN tl_activity ON tl_activity.partment=tl_partment.id")->where("tl_activity.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		
		// $this->user_wx_id="8827802";
		// echo $this->user_wx_id;
		$res=$this->weixin_news->publish_activity($_REQUEST[id],array($this->user_wx_id));
		// dump($res);exit;
		if($res["errmsg"] != "ok"){
			$this->showmsg("推送失败，所选名单不在应用范围内");
		}else{
			$this->showmsg("推送成功!");
		}
	}

	#下架活动
	public function out_activity(){
		$search[to_status]=-1;
		$search[id]=$_REQUEST[id];
		$this->Logic->change_status($search);
		$this->showmsg("下架成功!");
	}

	#活动类型列表
	public function category_lists(){
		$this->data=$this->Logic->category_lists($user_id=-1,$search); 
		$this->display();
	}

	#添加活动类型
	public function add_category(){
		if(IS_POST){
			$cat_name=trim($_REQUEST[post][cat_name]);
			if(empty($cat_name)){
				$this->showmsg("请填写完整信息",1);
			}
			$db=M("activity_category");
			$category=$db->where("cat_name='{$cat_name}'")->find();
			if(!empty($category)){
				$this->showmsg("已存在该活动类型",1);
			}
			if(empty($_REQUEST[post][cate_id])){
				$_REQUEST[post][addtime]=time();
				$db->add($_REQUEST[post]);
			}else{
				$db->save($_REQUEST[post]);
			}
			$this->showmsg("编辑成功","userweb.php?c=activity&a=category_lists");
		}else{
			$id=$_REQUEST["id"];
			$this->data=M("activity_category")->where("cate_id={$id}")->field("cate_id,cat_name")->find();
			$this->display();
		}
	
	}

	#部门列表
	public function partment_lists(){
		$this->data=$this->get_partment();
		$this->display();
	}


	#添加部门
	public function add_partment(){
		if(IS_POST){
			$title=trim($_REQUEST[post][title]);
			if(empty($title)){
				$this->showmsg("请填写完整信息",1);
			}
			$db=M("partment");
			$partment=$db->where("title='{$title}'")->find();
			if(!empty($partment)){
				$this->showmsg("已存在该部门",1);
			}
			if(empty($_REQUEST[post][id])){
				$_REQUEST[post][addtime]=time();
				$db->add($_REQUEST[post]);
			}else{
				$db->save($_REQUEST[post]);
			}
			$this->showmsg("编辑成功","userweb.php?c=activity&a=partment_lists");
		}else{
			$id=$_REQUEST["id"];
			$this->data=M("partment")->where("id={$id}")->field("id,title")->find();
			$this->display();
		}
	
	}

	#导出报名统计
	public function excel_count(){
		$title = array(
			"编号",
		    "活动时间",
		    "活动标题",
		    "活动部门",
		    "活动类型",
		    "参与人数",
		    "报名人数",
		    "报名率",
		    "签到人数",
		    "签到率",
		);

		$res=$this->Logic->apply_count($user_id=-1,$search);
		// dump($res);exit;
		$data=array();
		foreach ($res[content] as $key => $value) {
			$data[] = array($key+1,date("Y-m-d",$value['time_start']),$value['title'],$value['partment_title'],$value['cat_name'],$value['apply_num'],$value['apply_count'],$value['apply_rate']."%",$value['sign_num'],$value['sign_rate']."%");
		}
		$filename = '活动统计表'.time();
		
		excel_export($filename,$title,$data);
	}

	#导出外部人员
	public function open_excel_count(){
		$title = array(
			"编号",
		    "邀请人代码",
		    "姓名",
		    "报名",
		    "报名时间",
		    "签到",
		    "签到时间",
		    "备注",
		);

		$res=$this->Logic->open_report($_REQUEST[id]);
		// dump($res);exit;
		$data=array();
		foreach ($res[content] as $key => $value) {
			$sign=$value['sign_status'] == 1? "已签到" : "";
			$sign_time=empty($value[sign_time])? "" : date("Y-m-d H:i:s",$value[sign_time]);
			$data[] = array($key+1,$value[user_code],$value[name],"已报名",date("Y-m-d H:i:s",$value['addtime']),$sign,$sign_time,$value['remark']);
		}
		$filename = '外部人员统计表'.time();
		
		excel_export($filename,$title,$data);
	}

	#导出区域统计
	public function excel_area(){
		$title = array(
			
			"区域",
		    "参与人数",
		    "报名人数",
		    "报名率",
		    "出席人数",
		    "出席率",
		);

		$id=$_REQUEST["id"];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		$res=$PublicLogic->report(1,$id);

		$data=array();
		foreach ($res as $key => $vo) {
			if(!empty($vo['all'])){
				$rate=round($vo['apply']/$vo['all']*100,2)."%";
				$data[] = array($vo['name'],$vo['all'],$vo['apply'],$rate,0,"0%");

				#二级
				if($vo['list'] != null){
					foreach($vo['list'] as $k2=>$v1){
						if(!empty($v1['all'])){
							$rate=round($v1['apply']/$v1['all']*100,2)."%";
							$data[] = array(">>".$v1['name'],$v1['all'],$v1['apply'],$rate,0,"0%");

							#三级
							if($v1['list'] != null){
								foreach($v1['list'] as $k3=>$v2){
									if(!empty($v2['all'])){
										$rate=round($v2['apply']/$v2['all']*100,2)."%";
										$data[] = array(">>>".$v2['name'],$v2['all'],$v2['apply'],$rate,0,"0%");

										#四级
										if($v2['list'] != null){
											foreach($v2['list'] as $k4=>$v3){
												if(!empty($v3['all'])){
													$rate=round($v3['apply']/$v3['all']*100,2)."%";
													$data[] = array(">>>>".$v3['name'],$v3['all'],$v3['apply'],$rate,0,"0%");
												}
											}
										}
									}
								}
							}
						}
					}
				
				}
			}
		}
		$filename = '活动统计表'.time();
		
		excel_export($filename,$title,$data);
	}

	#导出详细统计
	public function excel_person(){
		ini_set("memory_limit",-1);
		$title = array(
			"区域",
		    "代码",
		    "姓名",
		    "报名时间",
		    "报名状态",
		    "签到",
		    "签到时间",
		    "提示信息",
		    "备注",
		);
		$id=$_REQUEST["id"];
		$res=M("activity_apply")->where("activity_id={$id}")->select();
		// dump($res);exit;
		$data=array();
		foreach ($res as $key => $vo) {
			$applytime=empty($vo[applytime]) ? "" : date("Y-m-d H:i:s",$vo['applytime']);
			$signtime=empty($vo[sign_time]) ? "" : date("Y-m-d H:i:s",$vo['sign_time']);
			$is_able=empty($vo[is_able]) ? "" : ($vo[is_able]==1 ? "已报名" : "拒绝");
			$sign=empty($vo[sign_status]) ? "" : ($vo[sign_status]==1 ? "已签到" : "位置不正确，签到失败"); 
			$data[] = array($vo[partment],$vo[user_id],$vo[truename],$applytime,$is_able,$sign,$signtime,$vo[hint],$vo['remark']);
		}
		$filename = '活动详细统计表'.time();
		
		excel_export($filename,$title,$data);
	}
	

	#扫码签到
	public function sign_in(){
		echo "签到";
	}

	#扫码离场
	// public function sign_out(){
	// 	echo "离场";
	// }

	#下架活动复制
	public function copy_activity(){
		$id=$_REQUEST[id];
		$post=M("activity")->where("id={$id}")->find();
		$post[user_id]=$this->user_id;
		$post[wx_userid]=$this->user_wx_id;
		$post[addtime]=time();
		$post[publishtime]=null;
		$post[status]=0;
		unset($post[id]);
		M("activity")->add($post);
		$this->showmsg("活动复制成功",1);
	}


	#报表统计
	public function report(){
		$id=$_REQUEST["id"];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		if($this->user_level == 1){
			$data=$PublicLogic->report(1,$id);
		}else{
			// $PartmentLogic=new \Userweb\Logic\PartmentLogic();
			// $partmentId=$PartmentLogic->get_partment_ids($this->user_partment_id);
			// dump($partmentId);exit;
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$ids=$PartmentLogic->get_partment_ids($this->user_partment_id);
			// $partmentId=M("partment")
			$data=$PublicLogic->report_child(1,$ids,$id);
			// dump($data);exit;
		}
		
		$this->activityId=$id;
		$this->data=$data;
		$this->display();
	}

	#外部人员统计
	public function open_report(){
		$id=$_REQUEST["id"];
		$this->data=$this->Logic->open_report($id);
		$this->display();
	}

	#对外开放状态
	public function open_status(){
		$id=$_REQUEST[id];
		$open_status=$_REQUEST[open_status];
		M("activity")->where("id={$id}")->setField("open_status",$open_status);
		$this->ajaxReturn("ok");
	}

	#允许直接签到
	public function open_sign(){
		$id=$_REQUEST[id];
		$open_sign=$_REQUEST[open_sign];
		M("activity")->where("id={$id}")->setField("open_sign",$open_sign);
		$this->ajaxReturn("ok");
	}

	#仅推送给自己
	public function only_my(){
		$id=$_REQUEST[id];
		$only_my=$_REQUEST[only_my];
		M("activity")->where("id={$id}")->setField("only_my",$only_my);
		$this->ajaxReturn("ok");
	}

	#详细统计
	public function apply_person(){
		$id=$_REQUEST["id"];
		$partment_id=$_REQUEST[partment];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		$ids=$PublicLogic->get_all_chile_ids($partment_id);
		$ids=implode(",",$ids);
		$GLOBALS["partmentId"]=array();

		if($this->user_level == 1){
			$this->data=$this->Logic->detail_person($id,$ids);
		}else{
			$PartmentLogic=new \Userweb\Logic\PartmentLogic();
			$partmentId=$PartmentLogic->get_child_partment_ids($this->user_partment_id);
			$this->data=$this->Logic->child_detail_person($id,$ids,$partmentId);
		}
			
		
		$this->display();

	}

	#签到二维码
	public function sign_code(){
		$this->display();
	}

	#签退二维码
	public function sign_out(){
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
			$hint = $currentSheet->getCellByColumnAndRow(ord("C") - 65,$currentRow)->getValue();
			$person[$currentRow][] = $name;
			$person[$currentRow][] = $code;
			$person[$currentRow][] = $hint;

    		$persons[$currentRow]=$person[$currentRow];
  		}
  		$this->Logic->to_apply($persons,$_REQUEST[id]);

		$this->ajaxReturn("ok");
	}

	#修改提示信息
	public function post_hint(){
		M("activity_apply")->where("id={$_REQUEST[id]}")->setField("hint",$_REQUEST[hint]);
		$this->ajaxReturn("ok");
	}

}