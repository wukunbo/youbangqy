<?php
namespace Vote\Controller;
use Think\Controller;
class PcVoteController extends Controller {

	public function __construct(){
		parent::__construct();
		// $this->user_id=$_SESSION[user][userid]=3;
		$this->logic=new \Vote\Logic\PcVoteLogic;
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

	public function vote_show(){
		if(!$_REQUEST[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->vote=$this->logic->get_pcvote($_REQUEST[id]);

		if($_REQUEST[t]){
			dump($this->vote);exit;
		}
		
		// $this->data=$this->logic->get_votedata($this->vote[id]);

		$data=$this->logic->get_vote_sort($_REQUEST[id]);
		$this->data=$data;

		// dump($data);exit;

		$this->display();
	}

	public function ajax_vote_show(){
		$data=$this->logic->get_vote_sort($_REQUEST[id]);
		$this->data=$data;
		$res=$this->fetch('vote_show_tpl');
		$this->ajaxReturn($res);
	}

	public function ajax_vote(){
		$data=$this->logic->get_votedata($_REQUEST[voteId]);
		echo $data;
	}

	public function ajax_sort(){
		$voteId=$_REQUEST[id];
		$res=M("vote_option")->where("vote_id={$voteId}")->field("user_id,truename,count_vote")->order("count_vote desc")->limit(3)->select();
		// dump($res);exit;
		$data[sort]=$res;

		$all=M("vote_option")->where("vote_id={$voteId}")->count();
		$res1=M("vote")->where("id={$voteId}")->field("count_vote,count_view")->find();
		$res1[all_option]=$all;
		// dump($res1);exit;
		$data[sum]=$res1;

		$this->ajaxReturn($data);
	}

	public function index(){
		if(!$_REQUEST[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->vote=M("vote")->where("id={$_REQUEST[id]}")->find();
		if($this->vote[is_pc] != 1){
			$this->showmsg("活动类型不符合",-1);
		}

		$this->display();
	}

	#开始投票
	public function start_vote(){
		$status=$_REQUEST["status"];
		M("vote")->where("id={$_REQUEST[id]}")->setField("pc_status",$status);
		$this->ajaxReturn("ok");
	}

	#展示排名
	public function vote_sort(){
		$this->data=$this->logic->get_sort($_REQUEST[id]);
		$this->display();
	}

}