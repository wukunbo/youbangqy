<?php
namespace Userweb\Controller;
use Think\Controller;

class PartmentController extends BaseController{

	public function __construct(){
		parent::__construct();
		$this->logic= new \Userweb\Logic\PartmentLogic();
		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
	}


	#部门列表
	public function partment_lists(){
		$this->data=$this->logic->get_partment();
		$this->display();
	}


	#添加部门
	public function add_partment(){
		$id=$_REQUEST["id"];
		$this->agent_list=M("wx_agent")->field("agentid,name")->select();
		$this->data=M("partment")->where("id={$id}")->find();
		
		$this->partment=M("contacts_partment")->where("id in (".$this->data[partment].")")->getField("name",true);
		$this->partment=implode(",",$this->partment);

		$this->display();
	}

	public function partment_action(){
		$post=$_REQUEST[post];
		$this->logic->partment_action($post);
		$this->showmsg("操作成功","userweb.php?c=Partment&a=partment_lists&menu_app=Partment");
	}

	#获取企业号应用
	public function get_app(){
		$Get=D('GetService');
		$Get->config($this->wxdata);
		$res=$Get->get_agent();
		// dump($res);exit;
		$this->logic->get_agent($res);
		$this->ajaxReturn("ok");
	}

}