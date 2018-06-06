<?php
namespace Userweb\Logic;
use Think\Model;

class LoginLogic extends PublicLogic {

	public function __construct(){	
		$this->db= D("Public","Model");
	}
//登录操作
	public function login_action($search){
		$username=$search['username'];
		$password=$search['password'];
		$password=sha1($password);
		//var_dump($username);
		if(strpos($username, ':')){
			//echo 1;
			return $this->signLogin_action_admin_child($username,$password,$search['uid']);
		}else{
			//echo 2;
			return $this->login_action_admin($username,$password);
		}
	}

	
	public function login_action_admin($username,$password){
		$array['where']="username ='$username'";
		$array['table']='user';
		 
		$detail=$this->db->Find($array);
		 
		if ($detail==NULL){
			$data['status']="10002";
			$data['message']="没有这个账号";
			 
		 
		}else {
			if ($password==$detail['password']){
				$data['status']="10001";
				$data['message']='登录成功';
				$data['detail']=$detail;
				 
				$_SESSION['userweb']['userid']=$detail['id'];
				$_SESSION['userweb']['username']=$detail['username'];
				 
			}else {
				$data['status']="10003";
				$data['message']='密码错误';
			}
		}	
		return $data;	
	
	}
	
	public function login_action_admin_child($username,$password){
		$username=str_replace("admin","",$username);			
		$array['where']="tl_adm_username ='$username'";
		$array['table']='user_child';
		$detail=$this->db->Find($array);
		//echo M()->getLastsql();
		if ($detail==NULL){
			$data['status']="10002";
			$data['message']="没有这个账号";
		}else {
			if ($password==$detail['tl_adm_password']){
				$data['status']="10001";
				$data['message']='登录成功';
				$data['detail']=$detail;
				$_SESSION['userweb']['userid']=$detail['id'];
				$_SESSION['userweb']['username']=$detail['tl_adm_username'];
			}else {
				$data['status']="10003";
				$data['message']='密码错误';
				
			}
		}	
		return $data;	
	
	}


	public function signLogin_action_admin_child($username,$password,$uid){
		$username=str_replace("admin:","",$username);			
		$array['where']="wx_userid ='$username'";
		$array['table']='user_child';
		$detail=$this->db->Find($array);
		//echo M()->getLastsql();
		if ($detail==NULL){
			$data['status']="10003";
			$data['message']="密码错误";
		}else {
			if ($password==$detail['tl_adm_password'] && $detail[pass_status]==1){
				$data['status']="10001";
				$data['message']='登录成功';
				$data['detail']=$detail;

				$_SESSION['userweb']['user_wx_id']=$detail['wx_userid'];
				$_SESSION['userweb']['userid']=$detail['id'];
				$_SESSION['userweb']['username']=$detail['tl_adm_username'];
				$_SESSION['userweb']['level']=$detail['level'];
				$_SESSION['userweb']['partment_id']=$detail["partment_id"];
				unlink("Uploads/txt/{$uid}.txt");
			}else {
				$data['status']="10003";
				$data['message']='密码错误';
				
			}
		}	
		return $data;	
	
	}

	//登录操作
	/*
	public function login_action(){
		$username=$_REQUEST['username'];
		$password=$_REQUEST['password'];
		$password=sha1($password);

		$array['where']="username = '$username'";
		$array['table']='user';
		 
		$detail=$this->db->Find($array);
		
		if ($detail == NULL){
			$data['status']="10002";
			$data['message']="没有这个账号"; 
		}else{
			if ($password==$detail['password']){
				$data['status']="10001";
				$data['message']='登录成功';
				$data['detail']=$detail;
				 
				$_SESSION['userweb']['userid']=$detail['id'];
				$_SESSION['userweb']['username']=$detail['username'];
				 
			}else {
				$data['status']="10003";
				$data['message']='密码错误';
			}
		}

		return $data;	
	}
	*/
	

	
}