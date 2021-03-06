<?php
namespace Userweb\Controller;
use Think\Controller;
class AdminController extends BaseController{
	public function __construct(){
		parent::__construct();
		$this->logic= D("Admin","Logic");
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
	}


	public function index(){
		$this->today=time();
		$this->display();
	}

    public function add_child(){
        $action=$_REQUEST['action'];
        $this->assign("action",$action);
        $data=$this->logic->add_adm($this->wx_id);
        $data[partment]=M("partment")->field("id,title")->select();
        if($data){
        	$data[admin_name]=M("contacts")->where("wx_userid='{$data[wx_userid]}'")->getField("name");
        }
        $this->data=$data;
        $this->display();
     }
	
	public function child_lists(){
		$wx_id=$this->wx_id;
		$data=$this->logic->adm_lists($wx_id);
		$tmp=explode(":",$data[tl_adm_username]);
		$data[tl_adm_username]=$tmp[1];
		$this->data=$data;
		$this->display();
	}
	
	public function add(){
 
		 
		$wx_id=$this->wx_id;

		#是否存在该管理员
		if($_REQUEST[id]){
			$user=M("user_child")->where("wx_userid={$_REQUEST[wx_userid]} AND id != {$_REQUEST[id]}")->find();
		}else{
			$user=M("user_child")->where("wx_userid={$_REQUEST[wx_userid]}")->find();
		}
		
		if($user){
			$this->showmsg('该管理员已存在,请更换');
		}
		$data=$this->logic->add($wx_id);
		$userIds=array($_REQUEST[wx_userid]);
		$content='你已被设置为管理员-请点击链接设置登录密码：<a href="http://'.$_SERVER['HTTP_HOST'].'/youbangqy/web/personal.php?c=index&a=admin_pass">点此设置</a>';
		$this->weixin_news->set_agentid(0);
		$res=$this->weixin_news->send_text($userIds,$content);

		// if($data[status]=10002){
		// 	$this->showmsg('用户名重复');
		// }
		$this->showmsg('操作成功',"userweb.php?c=Admin&a=child_lists&menu_app=Admin");
	}
	
	public function set_pass(){
		$userIds=M("user_child")->where("id={$_REQUEST[id]}")->field("wx_userid")->find();
		$content='你已为管理员-请点击链接重置登录密码：<a href="http://aia.tuolve.com/youbangqy/web/personal.php?c=index&a=admin_pass">点此设置</a>';
		$this->weixin_news->set_agentid(0);
		$res=$this->weixin_news->send_text($userIds,$content);
		M("user_child")->where("id={$_REQUEST[id]}")->setField("pass_status",2);
		$this->showmsg('重置密码推送成功');
	}

	public function adm_lock(){
		$data=$this->logic->adm_lock();
		if($data==true){
		$this->redirect('操作成功！',U('Admin/child_lists'));
		}
	}
	public function edit_pwd(){
		$demo=M('admin');
		$user_id=$_SESSION['admin']['id'];
		$field='tl_adm_password';
		
		$demo=M('user');
		$user_id=$this->user_id;
		$field='password';
		
		
		$data=$this->logic->edit_pwd($demo,$user_id,$field);
		if($data[status]==10001){
			//管理员日志
			$this->success('密码修改成功','userweb.php?m=Admin&c=Admin&a=edit_pwd');
		}elseif($data[status]==10002){
			$this->error('没有数据被修改','userweb.php?m=Admin&c=Admin&a=edit_pwd');
		}elseif($data[status]==10003){
			$this->error('原密码不正确');
		}elseif($data[status]==10004){
			$this->error('两次输入密码不一致');
		}
		
		//$this->display();
	}
	

}