<?php
namespace Vote\Logic;
use Think\Model;

class PcVoteLogic {

	public function __construct(){	
		$this->db= new \Vote\Model\PublicModel();
	}

	public function get_pcvote($pcVoteId){
		$vote=M("vote")->where("id={$pcVoteId}")->find();
		return $vote;
	}

	public function get_votedata($voteId){
		$vote=M("vote_option")->where("vote_id={$voteId}")->field("user_id,truename,count_vote")->select();
		$data=array();
		foreach ($vote as $key => $value) {
			$data[]=array("text"=>$value[truename],"value"=>$value[count_vote]);
		}
		return json_encode($data,JSON_NUMERIC_CHECK);
	}

	public function get_sort($voteId){
		$vote=M("vote_option")->where("vote_id={$voteId}")->field("user_id,truename,count_vote")->order("count_vote DESC")->select();
		$data=array();
		foreach ($vote as $key => $value) {
			$data[]=array("text"=>$value[truename],"value"=>$value[count_vote]);
		}
		return json_encode($data,JSON_NUMERIC_CHECK);
	}

	public function get_vote_sort($voteId){
		// $colors=array("#2d7e38","#e7e921","#a021e9","#ec6409","#009249","#b8d295","#545650","#387ef5","#009249","#00cccc","#ccc");
		// 
		$colors=array("#d31145","#ff7c80","#ff9966","#ffb850","#b0d04b","#99ff66","#99ff99","#66ffff","#bdd7ee","#d6dce4","#ccc");

		$vote=M("vote_option")->where("vote_id={$voteId}")->field("user_id,truename,count_vote")->order("count_vote DESC")->limit(10)->select();
		$sum=M("vote")->where("id={$voteId}")->getField("count_vote");
		foreach ($vote as $key => &$value) {
			$value[rate]=round($value[count_vote]/$sum*100);
			// if($value[rate]<=25){
			// 	$value[color]="#2d7e38";
			// }else if($value[rate]>25 && $value[rate]<=50){
			// 	$value[color]="#e7e921";
			// }else if($value[rate]>50 && $value[rate]<=75){
			// 	$value[color]="#a021e9";
			// }else{
			// 	$value[color]="#ec6409";
			// }
			$value[color]=$colors[$key];
		}

		return $vote;
	}


}