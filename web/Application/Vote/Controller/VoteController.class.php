<?php
namespace Vote\Controller;
use Think\Controller;
class VoteController extends BaseController {

	public function __construct(){
		parent::__construct();
		$this->user_id=$_SESSION[wx_userid];
		$this->user_openid=$_SESSION["openId"];
	}


	public function vote_log(){
		$vote_id=$_REQUEST[id];
		$id=$_REQUEST[vote_id];

		if(!$vote_id || !$id ){
			$return[status]="非法输入11".$this->user_id;
			echo json_encode($return);
			exit;
		}

		// $user=M("vote_apply")->where("vote_id={$vote_id} AND user_id='{$this->user_id}'")->find();
		// if(!$user){
		// 	$return[status]=10000;
		// 	echo json_encode($return);
		// 	exit;
		// }

		#pc端控制开始
		if(isset($_REQUEST[pc]) && $_REQUEST[pc]==1){
			$pc_status=M("vote")->where("id={$vote_id}")->getField("pc_status");
			if(empty($pc_status)){
				$return[status]=10002;#未按开始按钮
				echo json_encode($return);
				exit;
			}
			if($pc_status==2){
				$return[status]=10003;#未按开始按钮
				echo json_encode($return);
				exit;
			}
		}


		if($this->open_status==2){
			if(empty($this->user_id)){
				$this->user_id=$this->user_openid;
			}
		}


		//是否投票
		$is_voted=$this->check_log($this->user_id,$id,$vote_id);
		if($is_voted!=0){
			$return[status]=20001;
			echo json_encode($return);
			exit;
		}

		$is_repeat=M("vote")->where("id={$vote_id}")->getField("is_repeat");
		if(!$is_repeat){

			$today=date("Y-m-d");
			$is_vote_log=M("vote_votelog")->where("user_id='{$this->user_id}' AND vote_id={$vote_id} AND option_id={$id} AND day='{$today}' ")->find();
			if($is_vote_log){
				$return[status]=40001;
				echo json_encode($return);
				exit;
			}

		}
		

		// echo $is_voted;exit;

		$data[user_id]=$this->user_id;
		$data[option_id]=$id;
		$data[vote_id]=$vote_id;
		$data[day]=date("Y-m-d");
		$data[ip]=get_ip();
		$data[addtime]=time();
		M("vote_votelog")->add($data);
		M("vote_option")->where("id=".$id."")->setInc(count_vote);

		//总投票+1
		M("vote")->where("id=".$vote_id."")->setInc(count_vote);

		$vote_count=M("vote_option")->where("id=".$id."")->getField("count_vote");
		$res[status]=10001;
		$res[vote_count]=$vote_count;

		echo json_encode($res);
		exit;
	}

	public function check_log($user_id,$id_str,$vote_id){
		$vote_type=M("vote")->where("id=".$vote_id."")->getField("vote_type");
		$vote_count=M("vote")->where("id=".$vote_id."")->getField("vote_count");
		//获取某用户总次数
		$where=" user_id='".$user_id."' AND vote_id=".$vote_id."  ";
		$count_total=M("vote_votelog")->where($where)->count();

		//获取当天总次数
		$where=" user_id='".$user_id."' AND vote_id=".$vote_id." AND day='".date("Y-m-d")."'";
		$count_day=M("vote_votelog")->where($where)->count();

		// echo M("vote_votelog")->getLastsql();exit;

		$res=0;
 		if($vote_type==1 && $count_total>0){
			$res=1;
		} 
		if($vote_type==2 && $count_day>=$vote_count){
			$res=1;
		}
		if($vote_type==0 && $count_day>0 && $vote_count==0){
			$res=1;
		}
		return $res;
	}

}