<?php
namespace Lottery\Controller;
use Think\Controller;
class IndexController extends BaseController {

    public function __construct(){
		parent::__construct();
	 	$this->logic=new \Lottery\Logic\IndexLogic();
	 	$this->user_id=$_SESSION[wx_userid];	 
	}

	public function index(){
		$this->id=$_REQUEST[id];
		$data=$this->logic->detail($this->id,$this->user_id);

		if(empty($data[detail][status])){
			$this->showmsg("活动未发布",-1);
		}

		$now=time();

		if($data[detail][start_time] > $now){

    		$this->showmsg("该抽奖还未开始",1);
    	}
    	if($data[detail][end_time] < $now){

			$this->showmsg("该抽奖活动已结束",1);
    	}
		
		if(!$data[user]){
			$this->showmsg("你无权参加该抽奖",-1);
		}

		$res['status']="10001";
		$res[detail][back_img]=$data[detail][back_img];
		$res[user][lottery_count]=$data[user][lottery_count];
		$this->data=$res;

		$this->prize=M("lottery_award")->where("lottery_id={$this->id}")->count();#有几等奖

		$this->display();
	}

	public function win(){
		$data=$this->logic->win($_REQUEST[id],$this->user_id);
		echo json_encode($data);
	}

	#填写中奖资料
	public function win_ziliao(){
		$post[phone]=$_REQUEST[phone];
		$post[lottery_id]=$_REQUEST[lottery_id];
		$post[win]=$_REQUEST[win];
		$post[partment]=$_REQUEST[partment];
		$this->logic->win_ziliao($post,$this->user_id);
		$data[status]=10001;
		$this->ajaxReturn($data);
	}
}