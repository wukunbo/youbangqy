<?php
namespace Userweb\Controller;
use Think\Controller;

class LoginController extends BaseController{

	public function __construct(){
		parent::__construct();
	 	$this->assign('config',$config);
		$this->logic= D("Login","Logic");
	}

	public function login(){
		$this->display();
	}

	public function check(){
		$search[username]=$_REQUEST['username'];
		$search[password]=$_REQUEST['password'];
		$search[uid]=$_REQUEST['uid'];
		$res=$this->logic->login_action($search);
		echo json_encode($res);
	}

	public function logout(){
		session_destroy();
		header("Location: userweb.php");
	}

	#扫码登录
	public function sign_login(){
		$this->display();
	}

	#读取txt是否扫描
	public function read_txt(){
		$uid=$_REQUEST[uid];
		$myfile = fopen("Uploads/txt/{$uid}.txt", "r"); 
        $wx_id = fread($myfile,filesize("Uploads/txt/{$uid}.txt"));//读取
       	$this->ajaxReturn($wx_id);
	}

}