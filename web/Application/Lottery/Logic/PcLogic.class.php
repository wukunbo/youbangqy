<?php
namespace Lottery\Logic;
use Think\Model;
class PcLogic {

	public function __construct(){
		// $this->user_id=$_SESSION[userweb][userid];
		$this->db= new \Lottery\Model\PublicModel();
	}


	//pc端开奖
	public function pc_win($lotteryId,$win){
		$count=M("lottery_award")->where("lottery_id={$lotteryId} AND type={$win} ")->getField("amount");
		$win_count=M("lottery_win")->where("lottery_id={$lotteryId} AND win={$win} ")->count();
		$db=M("lottery_pclog");
		$lottery_win=M("lottery_win");

		//面向部门
		$win_type=M("lottery")->where("id={$lotteryId} AND type=2")->getField("win_type");

		if($count && $win_count<=$count){
			#随机抽奖
			for($i=$win_count;$i<$count;$i++){
				$lottery_pclog=$db->where("lottery_id={$lotteryId}")->field(" DISTINCT(user_id) ")->order("rand()")->find();
				$datalist=array("lottery_id"=>$lotteryId,"user_id"=>$lottery_pclog[user_id],"win"=>$win,"type"=>"pc","addtime"=>time());
				// $res=$lottery_win->where("user_id={$lottery_pclog[user_id]} AND win={$win}")->find();
				$res=$lottery_win->where("user_id={$lottery_pclog[user_id]} AND lottery_id={$lotteryId} ")->find();
				// dump($res);exit;
				
				//只能中一次奖
				if($win_type==1){

					if(!$res){
					// dump($datalist);exit;
						$lottery_win->add($datalist);
					}
				}

				//可中多等奖
				if($win_type==2){
					$res1=$lottery_win->where("user_id={$lottery_pclog[user_id]} AND lottery_id={$lotteryId} AND win={$win}")->find();
					if(!$res1){
					// dump($datalist);exit;
						$lottery_win->add($datalist);
					}
				}
				
			}
			// $data=M("lottery_pclog")->where("lottery_id={$lotteryId}")->field(" DISTINCT(user_id) ")->order("rand()")->limit("{$count}")->select();
			// foreach ($data as $key => $value) {
			// 	$datalist=array("lottery_id"=>$lotteryId,"user_id"=>$value[user_id],"win"=>$win,"type"=>"pc","addtime"=>time());
			// 	M("lottery_win")->add($datalist);
			// }
		}
		M("lottery_award")->where("lottery_id={$lotteryId} AND type={$win} ")->setField("pc_start",-1);
	}


	public function lottery_login($lotteryId,$user_id){
		$db=M("lottery_pc_area");
		$is=$db->where("lottery_pc_id={$lotteryId} AND pc_user_id={$user_id}")->find();
		if($is){
			return true;
		}else{
			$wx_userid=M("lottery")->where("tl_lottery.id={$lotteryId}")->join("INNER JOIN tl_user_child ON tl_user_child.id=tl_lottery.user_id")->getField("tl_user_child.wx_userid");
			if($wx_userid==$user_id){
				return true;
			}else{
				return false;
			}
		}
	}

	//抽奖记录
	public function lottery_log($lotteryId,$pc_prize){
		$db=M("lottery_pclog");
		$where="tl_lottery_pclog.lottery_id={$lotteryId} AND pc_prize={$pc_prize} ";

		$time=time()-10;
		$where.=" AND tl_lottery_pclog.addtime>=$time ";

		$join="INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_pclog.user_id ";
		$field="tl_lottery_pclog.*,tl_contacts.name ";
		$order="tl_lottery_pclog.addtime desc ";
		$data[users]=$db->where($where)->join($join)->field($field)->order($order)->select();

		// echo $db->getLastsql();exit;

		$all=M("lottery_data")->where("lottery_id={$lotteryId}")->count();
		$apply=$db->where("lottery_id={$lotteryId} AND pc_prize={$pc_prize}")->count();
		$rate=($apply/$all*10);
		if($rate===0){
			$rate=1;
		}
		$data[percent]=($apply/$all*100);
		$rate=ceil($rate);
		$data[rate]=$rate;

		$pc_start=M("lottery_award")->where("lottery_id={$lotteryId} AND type={$pc_prize}")->getField("pc_start");
		// echo $pc_start;
		if($pc_start==1){
			$data[status]=10000;
		}
		if($pc_start==-1){
			$data[status]=10001;
		}
		return $data;
	}

	//中奖记录
	public function win_log($lotteryId,$win){
		$db=M("lottery_win");
		$where="tl_lottery_win.lottery_id={$lotteryId} AND tl_lottery_win.win={$win} ";
		$join="INNER JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_win.user_id ";
		$field="tl_lottery_win.*,tl_contacts.avatar_url,tl_contacts.name ";
		$order="tl_lottery_win.addtime asc ";
		$data[win]=$db->where($where)->join($join)->field($field)->order()->select();
		// echo $db->getLastsql();exit;
		$data[win_count]=$db->where($where)->count();
		return $data;
	}



}