<?php
namespace Lottery\Controller;
use Think\Controller;

class PcController extends Controller {

	public function __construct(){
		parent::__construct();
		$config["static"]="Application/Lottery/View/static/";
		$this->assign('config',$config);
		$this->uid=uniqid();
		$this->logic=new \Lottery\Logic\PcLogic();
	}

	public function show_prize(){
		$lotteryId=$_REQUEST[id];
		$award=M("lottery_award")->where("lottery_id={$lotteryId} AND type={$_REQUEST[prize]}")->find();
		// dump($data);exit;
		
		$data=$this->logic->win_log($lotteryId,$_REQUEST[prize]);
		$data[award]=$award;

		$res=$this->logic->lottery_log($lotteryId,$_REQUEST[prize]);
		$data[users]=$res[users];

		// dump($data);exit;
		$this->image_back=M("lottery")->where("id={$lotteryId}")->getField("image_back");
		$this->data=$data;
		$this->display();
	}

	public function show_process(){
		$lotteryId=$_REQUEST[id];
		$data=M("lottery_award")->where("lottery_id={$lotteryId} AND pc_start=1")->find();
		// dump($data);exit;
		$this->image_back=M("lottery")->where("id={$lotteryId}")->getField("image_back");
		$this->data=$data;
		$this->display();
	}

	public function ready_lottery(){
		$this->display();
	}

	public function set_prize(){
		$lotteryId=$_REQUEST[id];
		$type=$_REQUEST[lottery_prize];
		$pc_start=M("lottery_award")->where("lottery_id={$lotteryId} AND type={$type}")->getField("pc_start");
		if($pc_start==-1){
			$this->ajaxReturn("no");
		}else{
			M("lottery_award")->where("lottery_id={$lotteryId} AND type={$type}")->setField("pc_start",1);
			$this->ajaxReturn("ok");
		}
		
	}

	public function get_data(){
		$lotteryId=$_REQUEST[id];
		$type=$_REQUEST[type];
		$all=M("lottery_data")->where("lottery_id={$lotteryId}")->count();
		$apply=M("lottery_pclog")->where("lottery_id={$lotteryId} AND pc_prize={$type}")->count();
		$rate=($apply/$all*100)."%";
		$this->ajaxReturn($rate);
	}

	public function pc_start(){
		$lotteryId=$_REQUEST[id];
		$pc_start=M("lottery_award")->where("lottery_id={$lotteryId} AND pc_start=1")->find();
		$this->ajaxReturn($pc_start);

	}

	//抽奖记录
	public function lottery_log(){
		$prize=$_REQUEST[prize];
		$lotteryId=$_REQUEST[id];
		$data=$this->logic->lottery_log($lotteryId,$prize);
		$this->data=$data;
		// dump($data);exit;
		$res[users]=$this->fetch("user_tpl");
		$res[rate]=$data[rate];
		$res[status]=$data[status];
		$res[percent]=$data[percent];

		$this->ajaxReturn($res);
	}

	public function login(){
		if($_REQUEST[var_login]){
			$_SESSION[lottery][user_id]=$_REQUEST[var_login];
		}

		if($_SESSION[lottery][user_id]){
			$this->redirect("daojishi",array("id"=>$_REQUEST[id]));
			exit;
		}
		// echo $_SESSION[lottery][user_id];exit;
		$this->display();
	}

	public function login_out(){
		$_SESSION[lottery][user_id]="";
	}

	#读取txt是否扫描
	public function read_txt(){
		$uid=$_REQUEST[uid];
		$myfile = fopen("Uploads/txt/{$uid}.txt", "r"); 
        $wx_id = fread($myfile,filesize("Uploads/txt/{$uid}.txt"));//读取
        if($wx_id){
        	$_SESSION[lottery][user_id]=$wx_id;
        	unlink("Uploads/txt/{$uid}.txt");
        }
       	$this->ajaxReturn($wx_id);
	}

	public function daojishi(){
		if($_REQUEST[var_login]){
			$_SESSION[lottery][user_id]=$_REQUEST[var_login];
		}

		if(!$_SESSION[lottery][user_id]){
			$this->redirect("login");
			exit;
		}
		$lotteryId=$_REQUEST[id];
		$lotteryLogic=new \Lottery\Logic\IndexLogic();
		$data=$lotteryLogic->detail($lotteryId);
		// echo time();
		// dump($data);exit;
		if($data[detail][end_time] < time() ){
			$this->showmsg("活动时间已结束!!");
		}
		$is=M("lottery")->where("lottery_id={$lotteryId} AND pc_start=1")->find();
		if($data[detail][start_time]>time() && $is){
			$this->redirect("show_prize");
			exit;
		}
		$this->data=$data;
		$this->display();
	}

	public function showmsg($msg,$url="-1"){
		// 2 重新刷新
		if($url==1){
			$url= $_SERVER['HTTP_REFERER'];
		}
		$this->msg=$msg;
		$this->url=$url;
		$this->display("Public/showmsg");
		exit;
	}

}