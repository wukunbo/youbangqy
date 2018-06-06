<?php
namespace Personal\Controller;
use Think\Controller;
class IndexController extends BaseController {

    public function __construct(){
		parent::__construct();
		$this->user_id=$_SESSION[wx_userid];
		$this->logic= new \Personal\Logic\IndexLogic;
	}

	public function index(){
		$this->data=$this->logic->get_userinfo($this->user_id);
		// dump($this->data);exit;
		$this->display();
	}

	public function activity(){
		$this->data=$this->logic->get_activity($this->user_id);
		// dump($this->data);exit;
		$this->display();
	}

	public function lottery(){
		$this->data=$this->logic->get_lottery($this->user_id);
		// dump($this->data);exit;
		$this->display();
	}

	#在线调查
	public function survey(){
		$this->data=$this->logic->get_survey($this->user_id);
		// exit;
		$this->display();
	}

	public function collect(){
		$this->data=$this->logic->get_collect($this->user_id);
		// dump($this->data);exit;
		$this->display();
	}


	#管理员扫码设计密码
	public function admin_pass(){
		$admin=M("user_child")->where("wx_userid='{$this->user_id}'")->find();
		if(!$admin){
			$this->showmsg("无权设置",-1);
		}
		$this->display();
	}

	#提交管理员密码
	public function action_pass(){
		$pass=$_REQUEST[pass];
		M("user_child")->where("wx_userid='{$this->user_id}'")->setField("tl_adm_password",sha1($pass));
		M("user_child")->where("wx_userid='{$this->user_id}'")->setField("pass_status",1);
		$this->showmsg("设置成功",-1);
	}

	#扫描登录
	public function sign_login(){
		$uid=$_REQUEST[uid];

		$admin=M("user_child")->where("wx_userid='{$this->user_id}'")->find();
		if(!$admin){
			$this->showmsg("无权登录",-1);
		}

		$this->display();

        // //写入文件
        // $myfile = fopen("Uploads/txt/{$uid}.txt",'w');
        // $content="admin:".$this->user_id;
        // fwrite($myfile,$content);//写入   转化为json
        // fclose($myfile);//关闭
        // $this->showmsg("登录成功,请输入密码登录",-1);
	}

	public function login_action(){
		$uid=$_REQUEST[uid];
		//写入文件
        $myfile = fopen("Uploads/txt/{$uid}.txt",'w');
        $content="admin:".$this->user_id;
        fwrite($myfile,$content);//写入   转化为json
        fclose($myfile);//关闭
        $this->ajaxReturn("ok");
	}
}