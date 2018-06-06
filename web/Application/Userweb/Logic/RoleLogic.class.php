<?php
namespace Userweb\Logic;
use Think\Model;
class RoleLogic {
	
	public function __construct(){		
		$this->db= D("Public","Model");
		$this->user_id=$_SESSION[userweb][userid];
	}
	
	public function add_role($user_child_id){
		$array[table]=$list[table]='auth';
		$map[auth_pid]=array('eq',0);
		// 获取管理员的权限，在管理员权限下分配子账号
		if($user_child_id){
			$role_id=M('user_child')->where("id='$this->user_id'")->getField('role_id');
			$tmp=$this->left_child($role_id,$user_child_id);
		}else{
			$role_id=M('user')->where("id='$this->user_id'")->getField('role_id');
			$tmp=$this->left($role_id);
		}
		
		
		return $tmp[lists];
	}
	
	public function role_lists($wx_id){
		if($_GET['id']){
			$id = I('get.id');
			if($_REQUEST['action']=='del'){
				$role_name=M('user_child_role')->where("role_id='$id'")->getField("role_name");
				//admin_log($role_name,'del', '角色名称');
				M('user_child_role')->where("role_id='$id'")->delete();
				
			}
		}
		$array[table]='user_child_role';
		$array[order]='role_id desc';
		$array[where]="wx_id='$wx_id' AND is_show=1";
		$array2[table]='auth';
		$detail=$this->db->Page($array);
		for($i=0;$i<count($detail[content]);$i++){
			$role_auth_ids=$detail[content][$i]["role_auth_ids"];
 			$role_auth_id[$i]=explode(",",$role_auth_ids);
 			$auth_name='';
 			for($j=0;$j<count($role_auth_id[$i]);$j++){
 			    $role_auth_id2=$role_auth_id[$i][$j];
 				$array2[where]="auth_id='$role_auth_id2'";
 				$array2[order]='auth_id asc';
 				$detail2=$this->db->Find($array2);
				if($j==count($role_auth_id[$i])-1){
					$auth_name.=$detail2['auth_name'];
				}else{
					$auth_name.=$detail2['auth_name'].',';
				}
 				
		   }
		     $detail[content][$i][auth_name]=$auth_name;
		}	
		return $detail;
	}
	
	public function left($role_id,$user_child_id="") {
		//获取当期路径参数
		$params=$this->page_params(1);

		//根据角色信息获得权限ids的信息
		if($user_child_id){
			$detail= M('user_child_role')->where("role_id='".$role_id."'")->find();
		}else{
			$detail= M('user_role')->where("id='".$role_id."'")->find();
		}
		

		 
		
		$auth_arr=explode(",",$detail[app]);
		$text_arr=explode(",",$detail[text]);
 		 
		//序列化$auth_arr
		$search=""; 
		for($i=0;$i<count($auth_arr);$i++){
			$search.="'".$auth_arr[$i]."',";
			$app_lists[$i][title]=$text_arr[$i];
			$app_lists[$i][val]=$auth_arr[$i];
			 
		}
		//默认基础权限
		$search=$search."'basic'";
 		//获取所有权限  
		$data= M('auth')->where(" app in (".$search.") AND auth_pid=0 ")->order("auth_id asc,sort DESC")->select();
		
		for($i=0;$i<count($data);$i++){
			
			$data[$i][sub]= M('auth')->where(" app ='".$data[$i][app]."' AND auth_pid=".$data[$i][sourse_id]."")->order("sort DESC,auth_id ASC")->select();
			for($j=0;$j<count($data[$i][sub]);$j++){
				//重组
				$tmp="c=".$data[$i][sub][$j][auth_c]."&a=".$data[$i][sub][$j][auth_a].$data[$i][sub][$j][parameters];
 				 
				if(strpos($params,$tmp)){
					$data[$i][cur]=1;
					$data[$i][sub][$j][cur]=1;
					$res[cur]["CONTROLLER_NAME"]=$data[$i][auth_name];
					$res[cur]["ACTION_NAME"]=$data[$i][sub][$j][auth_name];
					$res[cur]["app"]=$data[$i][sub][$j][app];
				}
			} 
		}
		 
	 	if(!$res[cur]["app"]){
			$res[cur]["app"]="basic";
		}

	 	$res[app_lists]=$app_lists;
		$res[lists]=$data;
		return $res;
	}

	public function left_child($role_id){
		$params=$this->page_params(1);
		$detail= M('user_child_role')->where("role_id='".$role_id."'")->find();
		$data= M('auth')->where(" auth_id IN ({$detail[role_auth_ids]}) AND auth_pid=0 ")->order("auth_id asc,sort DESC")->select();

		for($i=0;$i<count($data);$i++){
			
			$data[$i][sub]= M('auth')->where(" app ='".$data[$i][app]."' AND auth_pid=".$data[$i][sourse_id]."")->order("sort DESC,auth_id ASC")->select();
			for($j=0;$j<count($data[$i][sub]);$j++){
				//重组
				$tmp="c=".$data[$i][sub][$j][auth_c]."&a=".$data[$i][sub][$j][auth_a].$data[$i][sub][$j][parameters];
 				 
				if(strpos($params,$tmp)){
					$data[$i][cur]=1;
					$data[$i][sub][$j][cur]=1;
					$res[cur]["CONTROLLER_NAME"]=$data[$i][auth_name];
					$res[cur]["ACTION_NAME"]=$data[$i][sub][$j][auth_name];
					$res[cur]["app"]=$data[$i][sub][$j][app];
				}
			} 
		}

		if(!$res[cur]["app"]){
			$res[cur]["app"]="basic";
		}

		$res[lists]=$data;
		return $res;
	}
	
		/*
	$type 1 全部，2 re 3 post
	*/
	public function page_params($type=1){
		//接收参数值
		if($type==1){
			  
			foreach($_REQUEST as $_k=>$_v){
				 if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
					exit('Request var not allow!');
				 }else{
					$str="&".$_k."=".$_v;
				 }
			}
			$str="&c=".CONTROLLER_NAME."&a=".ACTION_NAME;
			 
		}
		//接收参数值
		if($type==2){
			$request='';
			foreach($_GET as $_k=>$_v){
				 if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
					exit('Request var not allow!');
				 }else{
					$str="&".$_k."=".$_v;
				 }
			}
		}
		//接收参数值
		if($type==3){
			foreach($_POST as $_k=>$_v){
				 if( strlen($_k)>0 && eregi('^(cfg_|GLOBALS)',$_k) && !isset($_COOKIE[$_k]) ){
					exit('Request var not allow!');
				 }else{
					//$request[$_k]=$_v;
					$str="&".$_k."=".$_v;
				 }
			}
		}
		return $str;
    }

}