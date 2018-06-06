<?php
namespace Userweb\Logic;
use Think\Model;
class PartmentLogic {
	
	public function __construct(){
		
		$this->db= D("Public","Model");
		$this->user_id=$_SESSION[userweb][userid];
	}


	#添加部门
	public function get_partment(){
		$partment=M("partment")->order("id desc")->select();
		$db=M("contacts_partment");
		foreach ($partment as $key => &$value) {
			$tmp="";
			if($value[partment]){
				$tmp=$db->where("id in (".$value[partment].")")->getField("name",true);
			}
			$value[partment]=implode(",",$tmp);
		 	//var_dump($tmp);
			//var_dump($value[partment]);
			
		}
		//var_dump($partment);
		return $partment;

	}

	public function partment_action($post){
		$db=M("partment");
		$post_id=$post[id];
		
		//重组命令 剔除下属的
		$tmp=explode(",",$post[partment]);
	 
		$tmp_str=$post[partment].",";
		
		
		for($i=0;$i<count($tmp);$i++){
			//获取子ID
			if($tmp[$i]){
				$GLOBALS["id"]="";
				$child_arr=$this->child_arr($tmp[$i],0);
				for($j=0;$j<count($child_arr);$j++){
					if(stristr($tmp_str,",".$child_arr[$j].",")){///子ID存在 提交的数据里
						$tmp_str=str_replace(",".$child_arr[$j]."","",$tmp_str);
		 
						$tmp_str=$tmp[$i].",".$tmp_str;
						 

					}
				} 
			}
		}
		//重组
		$arr=explode(",",$tmp_str);
 
		$arr=array_filter(array_unique($arr));
 
		$post[partment]=implode(",",$arr);
		// var_dump($post[partment]);
		// exit;
		if(empty($post_id)){
			$post[addtime]=time();
			$db->add($post);
		}else{
			$db->where("id=".$post_id."")->save($post);
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		return $res; 
	}
	#递归获取全部子部门id
	public function child_arr($id){// 1加入 0 不加入
		 
		$GLOBALS["id"][]=$id;
		 
		$arr=M("contacts_partment")->where("parentid={$id}")->field("id")->select();
		if($arr){
			foreach ($arr as $key => $value) {
				$this->child_arr($value[id]);
			}
		}
		return $GLOBALS["id"];
	}
	#递归获取全部子部门id
	public function child_str($id){
		if($GLOBALS["id_str"]){
			$GLOBALS["id_str"]=$GLOBALS["id_str"].",".$id;
		}else{
			$GLOBALS["id_str"]=$id;
		}
		 
		$arr=M("contacts_partment")->where("parentid={$id}")->field("id")->select();
		if($arr){
			foreach ($arr as $key => $value) {
				$this->child_str($value[id]);
			}
		}
		return $GLOBALS["id_str"];
	}
	#递归获取全部子部门id
	public function parent_arr($id,$is_top=0){// 1加入 0 不加入
		if($is_top){
			unset($GLOBALS["tmp"]);
		}
		$GLOBALS["tmp"][]=$id;
		$parentid=M("contacts_partment")->where("id={$id}")->getField("parentid");
		if($parentid){
			 $this->parent_arr($parentid);
		}
		return $GLOBALS["tmp"];
	}
	#递归获取全部子部门id
	public function parent_str($id){
		$tmp=$this->parent_arr($id,$is_top=1);
		$tmp=implode(",",$tmp);
		return $tmp;
	}
	public function get_partment_ids($id){
		$str=M("partment")->where("id={$id}")->getField("partment");
 
		$tmp=explode(",",$str);
		$ids="";
	 	
		for($i=0;$i<count($tmp);$i++){
			$ids=$this->parent_str($tmp[$i]).",".$ids."";
			$ids=$this->child_str($tmp[$i]).",".$ids."";
		}
		$ids=substr($ids,0,-1);
		return $ids;
 
	}

	#获取某部门下全部子id
	public function get_child_partment_ids($id){
		$str=M("partment")->where("id={$id}")->getField("partment");
 
		$tmp=explode(",",$str);
		$ids="";
		for($i=0;$i<count($tmp);$i++){
			$ids=$this->child_str($tmp[$i]).",".$ids."";
		}
		$ids=substr($ids,0,-1);
		$arr_ids=array_unique(explode(",",$ids));
		$ids=implode(",",$arr_ids);
		return $ids;
	}

	//获取企业号应用列表
	public function get_agent($data){
		$db=M("wx_agent");
		for ($i=1; $i<count($data); $i++) { 
			$dataList=array("agentid"=>$data[$i][agentid],"name"=>$data[$i][name]);
			$is_data=$db->where("agentid={$data[$i][agentid]}")->find();
			if($is_data){
				$db->where("agentid={$data[$i][agentid]}")->save($dataList);
			}else{
				$db->add($dataList);
			}
			
		}
	}
	 
}