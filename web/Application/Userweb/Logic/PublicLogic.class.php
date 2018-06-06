<?php
namespace Userweb\Logic;
use Think\Model;

class PublicLogic {

	//检测是否登录
	public function check_login(){
		if ($_SESSION['userweb']['userid']!=NULL){
			return true;
		}else {
			return false;
		}
	}


	#统计报告
	public function report($partmentId=1,$id,$table="activity"){

		$db=M("contacts_partment");

		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		if($data){
			foreach ($data as $key => $value) {

				$partmentdata=$this->get_all_chile_ids($value[id]);
				$ids=implode(",",$partmentdata);
				$GLOBALS["partmentId"]=array();
				$partmentdata=array();

				$all=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
				$apply=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND is_able=1")->count();
				
				$sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
				// echo M("{$table}_apply")->getLastsql();exit;
				
				$value["all"]=$all;
				$value["apply"]=$apply;
				$value["sign"]=$sign;
				$value["list"]=$this->report($value["id"],$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}


	#统计报告
	public function report_child($partmentId=1,$is_ids,$id,$table="activity"){

		$db=M("contacts_partment");
		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		$is_ids=",".$is_ids.",";

		if($data){
			foreach ($data as $key => $value) {

				$value["all"]=0;
				$value["apply"]=0;
				$value["sign"]=0;
				if(stristr($is_ids,",".$value["id"].",")){

					$partmentdata=$this->get_all_chile_ids($value[id]);
					$ids=implode(",",$partmentdata);
					$GLOBALS["partmentId"]=array();
					$partmentdata=array();

					$all=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
					
					$apply=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND is_able=1")->count();
					
					$sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
					
					
					$value["all"]=$all;
					$value["apply"]=$apply;
					$value["sign"]=$sign;
				}
				$value["list"]=$this->report_child($value["id"],$is_ids,$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}

	#获取某部门下的全部ids
	public function get_all_chile_ids($partmentId){
		$GLOBALS["partmentId"][]=$partmentId;
		 
		$arr=M("contacts_partment")->where("parentid={$partmentId}")->field("id")->select();
		if($arr){
			foreach ($arr as $key => $value) {
				$this->get_all_chile_ids($value[id]);
			}
		}
		// $ids=implode(",",$GLOBALS["partmentId"]);
		return $GLOBALS["partmentId"];
		// return $ids;
	}


	#抽奖报表统计
	public function report_lottery($partmentId=1,$id,$table="lottery"){

		$db=M("contacts_partment");

		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		if($data){
			foreach ($data as $key => $value) {

				$partmentdata=$this->get_all_chile_ids($value[id]);
				$ids=implode(",",$partmentdata);
				$GLOBALS["partmentId"]=array();
				$partmentdata=array();

				$all=M("{$table}_data")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
				$lottery_count=M("{$table}_data")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->sum("lottery_count");
				
				// $sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
				// echo M("{$table}_apply")->getLastsql();exit;
				
				$value["all"]=$all;
				$value["lottery_count"]=$lottery_count;
				
				$value["list"]=$this->report_lottery($value["id"],$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}

	#子管理员查看数据
	
	public function report_lottery_child($partmentId=1,$is_ids,$id,$table="lottery"){

		$db=M("contacts_partment");
		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		$is_ids=",".$is_ids.",";

		if($data){
			foreach ($data as $key => $value) {

				$value["all"]=0;
				$value["lottery_count"]=0;
				
				if(stristr($is_ids,",".$value["id"].",")){

					$partmentdata=$this->get_all_chile_ids($value[id]);
					$ids=implode(",",$partmentdata);
					$GLOBALS["partmentId"]=array();
					$partmentdata=array();

					$all=M("{$table}_data")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
					$lottery_count=M("{$table}_data")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->sum("lottery_count");
					
					// $sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
					// echo M("{$table}_apply")->getLastsql();exit;
					
					$value["all"]=$all;
					$value["lottery_count"]=$lottery_count;
				}
				
				$value["list"]=$this->report_lottery_child($value["id"],$is_ids,$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}
	

	#调查报表统计
	public function report_survey($partmentId=1,$id,$table="survey"){
		$db=M("contacts_partment");

		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		if($data){
			foreach ($data as $key => $value) {

				$partmentdata=$this->get_all_chile_ids($value[id]);
				$ids=implode(",",$partmentdata);
				$GLOBALS["partmentId"]=array();
				$partmentdata=array();

				$all=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
				$apply=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND is_able=1")->count();
				
				// $sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
				// echo M("{$table}_apply")->getLastsql();exit;
				
				$value["all"]=$all;
				$value["apply"]=$apply;
				// $value["sql"]=M("{$table}_apply")->getLastsql();
				$value["list"]=$this->report_survey($value["id"],$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}


	#子管理员调查报表统计
	public function report_survey_child($partmentId=1,$is_ids,$id,$table="survey"){
		$db=M("contacts_partment");
		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		$is_ids=",".$is_ids.",";

		if($data){
			foreach ($data as $key => $value) {

				$value["all"]=0;
				$value["apply"]=0;
				
				if(stristr($is_ids,",".$value["id"].",")){

					$partmentdata=$this->get_all_chile_ids($value[id]);
					$ids=implode(",",$partmentdata);
					$GLOBALS["partmentId"]=array();
					$partmentdata=array();

					$all=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
					$apply=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND is_able=1")->count();
					
					// $sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
					// echo M("{$table}_apply")->getLastsql();exit;
					
					$value["all"]=$all;
					$value["apply"]=$apply;
				}
				// $value["sql"]=M("{$table}_apply")->getLastsql();
				$value["list"]=$this->report_survey_child($value["id"],$is_ids,$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}


	#收集报表统计
	public function report_collect($partmentId=1,$id,$table="collect"){
		$db=M("contacts_partment");

		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		if($data){
			foreach ($data as $key => $value) {

				$partmentdata=$this->get_all_chile_ids($value[id]);
				$ids=implode(",",$partmentdata);
				$GLOBALS["partmentId"]=array();
				$partmentdata=array();

				$all=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
				$apply=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND is_able=1")->count();
				
				// $sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
				// echo M("{$table}_apply")->getLastsql();exit;
				
				$value["all"]=$all;
				$value["apply"]=$apply;
				// $value["sql"]=M("{$table}_apply")->getLastsql();
				$value["list"]=$this->report_collect($value["id"],$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}

	#收集报表统计
	public function report_collect_child($partmentId=1,$is_ids,$id,$table="collect"){

		$db=M("contacts_partment");
		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		$is_ids=",".$is_ids.",";

		if($data){
			foreach ($data as $key => $value) {

				$value["all"]=0;
				$value["apply"]=0;
				
				if(stristr($is_ids,",".$value["id"].",")){

					$partmentdata=$this->get_all_chile_ids($value[id]);
					$ids=implode(",",$partmentdata);
					$GLOBALS["partmentId"]=array();
					$partmentdata=array();

					$all=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id}")->count();
					$apply=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND is_able=1")->count();
					
					// $sign=M("{$table}_apply")->where( "partment_id IN({$ids}) AND {$table}_id={$id} AND sign_status=1")->count();
					// echo M("{$table}_apply")->getLastsql();exit;
					
					$value["all"]=$all;
					$value["apply"]=$apply;
				}
				// $value["sql"]=M("{$table}_apply")->getLastsql();
				$value["list"]=$this->report_collect_child($value["id"],$is_ids,$id);
				$arr[]=$value;
					
			}
			return $arr;
		}
	}


}