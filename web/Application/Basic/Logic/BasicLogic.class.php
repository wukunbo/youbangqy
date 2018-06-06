<?php
namespace Basic\Logic;
use Think\Model;
class BasicLogic {
	public function  __construct(){	
 		$this->db=new \Basic\Model\PublicModel();
	}
	// 
	//$BasicLogic=new \Basic\Logic\BasicLogic;
	//$wx_config=$BasicLogic->wx_config($wx_id);
	public function wx_config($wx_id,$user_id='',$appid=''){
		$where="id='-1'";
		if($wx_id){
			$where="id='".$wx_id."'";
		}
		if($user_id){
			$where="user_id='".$user_id."'";
		}
		if($appid){
			$where="appid='".$appid."'";
		}
		$db=M('tlwx_config');
		$detail=$db->where($where)->Find();
		//echo $db->getLastsql();
	//	p($detail) ;
		return $detail;
	}
	//$BasicLogic=new \Basic\Logic\BasicLogic;
	//$wx_config=$BasicLogic->wx_config($key);
	public function config($k=''){
		if($k){
			$where="k='".$k."'";
		} 
			 
		$db=M('config');
		$value=$db->where($where)->getField("value");
		//echo $db->getLastsql();
		return $value;
	}
	
	 
	
	//获取所有应用
	public function get_app($search){
		//$search[wx_id]=1;
		if($search[shop_id]){
			$wx_id=M("shop_config")->where("id='".$search[shop_id]."'")->getField("wx_id");
		}
		if($search[service_id]){
			$wx_id=M("service_config")->where("id='".$search[service_id]."'")->getField("wx_id");
		}
		if($search[wx_id]){
			$wx_id=$search[wx_id];
		}
		if($wx_id){
			//获取
			$data[shop_id]=M("shop_config")->where("wx_id='".$wx_id."'")->getField("id");
			$data[service_id]=M("service_config")->where("wx_id='".$wx_id."'")->getField("id");
			$data[food_id]=M("food_config")->where("wx_id='".$wx_id."'")->getField("id");
			$data[wx_id]=$wx_id;
			$data[user_id]=M("tlwx_config")->where("id='".$wx_id."'")->getField("user_id");
		}
		  
		// var_dump($data);
		return $data;
	}
	
	 
}
