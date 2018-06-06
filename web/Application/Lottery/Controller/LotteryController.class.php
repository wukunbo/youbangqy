<?php
namespace Lottery\Controller;
use Think\Controller;

class LotteryController extends BaseController {

	public function __construct(){
		parent::__construct();
	 	$this->logic=new \Lottery\Logic\IndexLogic();
	 	$this->user_id=$_SESSION[wx_userid];	 
	}

	public function index(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}

		$this->data=$this->logic->detail($request[id],$this->user_id);
		// dump($this->data);exit;
		if(empty($this->data[detail][status])){
			$this->showmsg("活动未发布",-1);
		}

		$this->pc_is=0;
		$this->pc_admin=0;
		#pc端控制抽奖
		if($this->data[detail][type]==2){
			$lottery_pc_area=M("lottery_pc_area")->where("lottery_pc_id={$_REQUEST[id]} AND pc_user_id={$this->user_id}")->find();
			if($lottery_pc_area){
				$this->pc_is=1;
			}
			#总控人员
			$user_adminId=M("user_child")->where("id={$this->data[detail][user_id]}")->getField("wx_userid");
			if($user_adminId==$this->user_id){
				$this->pc_admin=1;
			}
		}

		
		// if(!$this->data[user]){
		// 	$this->showmsg("你无权参加该活动",-1);
		// }
		$this->display();
	}

	//抽奖活动详情
	public function detail(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}

		$this->data=$this->logic->detail($request[id]);
		$this->display();
	}

	//抽奖奖品
	public function award(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->data=$this->logic->award_detail($request[id]);
		// dump($this->data);exit;
		$this->display();
	}

	//查看报表
	public function report(){
		$lotteryId=$_REQUEST[id];
		$data=$this->logic->lottery_count($lotteryId);
		foreach ($data as $key => &$area) {
			$area[code]=$this->logic->code_count($lotteryId,$area[id]);
			foreach ($area[code] as $key1 => &$code) {
				$code[persons]=$this->logic->detail_count($lotteryId,$code[id])[content];
			}
		}
		$this->data=$data;
		$this->display();
	}

	#报表统计
	public function report2(){
		if(!$this->show_report){
            $this->showmsg("无权打开该页面",-1);
        }

		$id=$_REQUEST["id"];
		$PublicLogic=new \Userweb\Logic\PublicLogic();
		$data=$PublicLogic->report_lottery(1,$id);
		$this->lotteryId=$id;
		$this->data=$data;
		$this->display();
	}


	#查看保单数据
	public function shuju(){
		$lotteryId=$_REQUEST[id];
		$this->data=$this->logic->shuju($lotteryId,$this->user_id);
		$this->back_image=M("lottery")->where("id={$lotteryId}")->getField("back_img");
		$this->display();
	}

	#详细统计
	public function apply_person(){
		if(!$this->show_report){
            $this->showmsg("无权打开该页面",-1);
        }

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


	#pc职员投票准备页面
	public function pc_admin(){
		
		if(IS_POST){
			M("lottery_pc_area")->where("lottery_pc_id={$_POST[lotteryId]} AND pc_user_id='{$this->user_id}' ")->setField('status',1);
			$this->ajaxReturn("ok");
		}
		$this->lotteryId=$_REQUEST[id];
		if(!$this->lotteryId){
			$this->showmsg("非法输入",-1);
		}
		$this->area=M("lottery_pc_area")->where("lottery_pc_id={$this->lotteryId}")->select();
		$this->status1=M("lottery_pc_area")->where("lottery_pc_id={$this->lotteryId} AND pc_user_id='{$this->user_id}' ")->getField("status");
		$this->display();
	}

	#pc轮流抽奖
	public function pc_lottery(){
		$this->lotteryId=$_REQUEST[id];
		if(!$this->lotteryId){
			$this->showmsg("非法输入",-1);
		}
		$this->area=M("lottery_pc_area")->where("lottery_pc_id={$this->lotteryId}")->select();
		$this->lottery_prize=M("lottery_award")->where("lottery_id={$this->lotteryId}")->order("type asc")->select();
		$this->display();
	}

	#pc开始点击抽奖
	public function pc_lottery_start(){
		$lotteryId=$_REQUEST[id];
		$res=$this->logic->check_lottery($lotteryId,$this->user_id);
		$this->ajaxReturn($res);
	}

	public function pc_show(){
		$this->display();
	}

	#pc抽奖结果
	public function pc_prize(){
		$prize=$_REQUEST[prize];
		$lotteryId=M("lottery_pc")->where("id={$_REQUEST[id]}")->getField("lottery_id");
		$usreIds=M("lottery_apply")->where("lottery_id={$lotteryId}")->getField("user_id",true);
		$keys=array_rand($usreIds,$prize);
		foreach ($keys as $key => $value) {
			$datalist=array("lottery_id"=>$lotteryId,"user_id"=>$usreIds[$value],"win"=>$prize,"type"=>"pc","addtime"=>time());
			M("lottery_win")->add($datalist);
		}
		$this->redirect('Lottery/show_prize', array('id'=>$lotteryId,"prize"=>$prize));
	}

	#展示抽奖结果
	public function show_prize(){
		$lotteryId=$_REQUEST[id];
		$win=$_REQUEST[prize];
		$join=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_win.user_id ";
		$field=" tl_contacts.* ";
		$this->data=M("lottery_win")->join($join)->where("lottery_id={$lotteryId} AND win={$win} AND type='pc' ")->field($field)->select();
		$this->display();
	}

	#pc端抽奖开奖结果
	public function pc_win(){
		$lotteryId=$_REQUEST[id];
		$win=$_REQUEST[prize];
		$pcLogic=new \Lottery\Logic\PcLogic();
		$pcLogic->pc_win($lotteryId,$win);
		$this->redirect('Lottery/show_prize', array('id'=>$lotteryId,"prize"=>$win));
	}


	#pc端登录
	public function lottery_login(){
		$lotteryId=$_REQUEST[id];
		$pcLogic=new \Lottery\Logic\PcLogic();
		$flag=$pcLogic->lottery_login($lotteryId,$this->user_id);
		if($flag){
			// $_SESSION[lottery][user_id]=$this->user_id;
			// echo $_SESSION[lottery][user_id];exit;
			$uid=$_REQUEST[uid];
			//写入文件
	        $myfile = fopen("Uploads/txt/{$uid}.txt",'w');
	        $content=$this->user_id;
	        fwrite($myfile,$content);//写入   转化为json
	        fclose($myfile);//关闭
			$this->showmsg("扫描登录成功");
			// $this->showmsg($_SESSION[lottery][user_id]);
		}else{
			$this->showmsg("无权登录，请联系管理员");
		}
	}

}