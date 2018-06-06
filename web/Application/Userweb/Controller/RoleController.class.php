<?php
namespace Userweb\Controller;
use Think\Controller;
class RoleController extends BaseController {

	public function __construct() {
		parent::__construct();
		$this->logic=D('Role','Logic');
		$this->user_id=$_SESSION[userweb][userid];
	}
	
	public function role_lists(){
		$wx_id=$this->wx_id;
		$data=$this->logic->role_lists($wx_id);
		$this->data=$data;
		$this->display();
	}
	
		public function add_role222() {
		//显示列表
		$detail=$this->logic->add_role();//dump($detail);exit;
		$this->detail=$detail;
		if($_REQUEST[action]){
			if (!empty($_POST)) {
				$role_id = I('request.role_id');
				$post=$_REQUEST[post];
				$role_name=$post['role_name'];
				if ($role_id) {
					//利用RoleModel模型里边的一个专门方法实现权限分配
					$role = new \Userweb\Model\Admin_roleModel();
					//saveAuth接收到一维数组信息
					$z = $role->saveAuth($_POST['authname'], $role_id);
					$this->showmsg('操作成功！');
				} else {
					$role = new \Userweb\Model\Admin_roleModel();
					$post=$_REQUEST[post];
					$post[seller_id]=$this->user_id;
					$role_id = $role->add($post);
					//管理员日志
					//admin_log($post['role_name'],'add','角色名称');
					$z = $role->saveAuth($_POST['authname'], $role_id);
					$this->showmsg('操作成功！');
				}
			} else {
				//修改页面显示
				$id = I('get.id');
				$detail2 = M('user_child_role')->where("role_id='$id'")->find();
				$this->assign("detail2", $detail2);			
			}
			}
			$this->display();
		}

	public function add_role() {
		//显示列表
		$detail=$this->logic->add_role($this->user_child_id);
		// $detail=$this->logic->add_role(1);//dump($detail);exit;
		$this->detail=$detail;
		$id = I('get.id');
		$this->id=$id;
		$detail2 = M('user_child_role')->where("role_id='$id'")->find();
		$this->assign("detail2", $detail2);	
		$this->display();
		}
		
	public function add_role_action() {
		$role_id = I('request.role_id');
		$post=$_REQUEST[post];
		$role_name=$post['role_name'];
		if ($role_id) {
			//利用RoleModel模型里边的一个专门方法实现权限分配
			$role = new \Userweb\Model\Admin_roleModel();
			M('user_child_role')->where("role_id='$role_id'")->setField('role_name',$role_name);
			//saveAuth接收到一维数组信息
			$z = $role->saveAuth($_POST['authname'], $role_id);
			$this->showmsg('操作成功！');
		} else {
			$role = new \Userweb\Model\Admin_roleModel();
			$post=$_REQUEST[post];
			$post[wx_id]=$this->wx_id;
			$role_id = $role->add($post);
			$z = $role->saveAuth($_POST['authname'], $role_id);
			$this->showmsg('操作成功！');
		}
	}


	
		
}	


