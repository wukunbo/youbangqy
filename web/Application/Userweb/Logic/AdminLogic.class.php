<?php
namespace Userweb\Logic;
use Think\Model;
class AdminLogic {
	
	public function __construct(){
		
		$this->db= D("Public","Model");
		$this->user_id=$_SESSION[userweb][userid];
	}
	
	//页面显示
    public function add_adm($wx_id){
      $array2[table]='user_child_role';
      $array2[order]='role_id desc';
      $array2[where]="wx_id='$wx_id' AND is_show=1";
      $data[role]=$this->db->Select($array2);
      $id=$_REQUEST['id'];
      if($id !=NULL){
        $array['table']="user_child";
        $array['where']="id='$id'";
        $data=$this->db->Find($array);
        $data[role]=$this->db->Select($array2);
      }
      return $data;
    }

    //添加管理员
    public function add($wx_id){
      $post=I('post.');
      $array['table']="user_child";
      $post['tl_adm_username']=M("contacts")->where("wx_userid=$post[wx_userid]")->getField("name");
      $array['data']['tl_adm_username']=$post['tl_adm_username'];
      $array['data']['tl_adm_truename']=$post['tl_adm_truename'];
      // $array['data']['tl_adm_password']=sha1($post['tl_adm_password']);
      $array['data']['role_id']=$post['role_id'];
      $array['data']['wx_userid']=$post['wx_userid'];
      $array['data']['partment_id']=$post['partment_id'];
      $array['data']['show_all']=$post['show_all'];
      //修改管理员
      $id=$_REQUEST['id'];
      if($id !=NULL){
        $array['where']="id='$id'";
        $id=$this->db->Save($array);
		//管理员日志
		//userweb_log("$post[tl_adm_username]",'edit', '管理员信息');
       // echo M()->getLastSql();
      }else{
        $array['data']['add_time']=time();
        $array['data']['wx_id']=$wx_id;
        $id=$this->db->Add($array);
		//管理员日志
		//userweb_log("$post[tl_adm_username]",'add','管理员');
        }
        if($id){
        	$data[status]=10001;
        }else{
        	$data[status]=10002;
        }
      return $data;
    }
	

	
	
	public function adm_lists($wx_id){
		$array[table]='user_child';
		$array[join]="LEFT JOIN tl_partment ON tl_partment.id=tl_user_child.partment_id ";
		$array[join].=" LEFT JOIN tl_user_child_role ON tl_user_child_role.role_id=tl_user_child.role_id ";
		$array[field]="tl_partment.title as partment,tl_user_child_role.role_name,tl_user_child.* ";
		$id=$_REQUEST['id'];
		if(!empty($id) && $_REQUEST['action']=='del'){
			M('user_child')->where("id='$id'")->delete();
			//管理员日志
		    //admin_log("ID.$id",'del', '管理员');
		}
		// $array[where]="wx_id='$wx_id'";
		$array[where]="level=2";
		$data=$this->db->Page($array);
		// echo M("user_child")->getLastSql();
		return $data;
	}
	
	public function adm_lock(){
		$id=$_GET['id'];
		$action=$_GET['action'];
		$db=M('user_child');
		if($action=='lock'){
			$data=$db->where("id=$id")->setField('status','2');
			//管理员日志
		   // admin_log("ID.$id",'', '冻结管理员');
		}elseif($action=='unlock'){
			$data=$db->where("id=$id")->setField('status','1');
			//管理员日志
		   // admin_log("ID.$id",'', '解冻管理员');
		}
	  return $data;
	}

	

}