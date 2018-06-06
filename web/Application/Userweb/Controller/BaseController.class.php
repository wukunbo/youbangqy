<?php
namespace Userweb\Controller;
use Think\Controller;

class BaseController extends Controller {

	public function __construct(){
		parent::__construct();
		//配置config全局使用
		
		$config[page_title]="友邦多功能系统";
		$config['admin_tpl_static']="Application/Userweb/View/static/proton/";
		$config['static']="Application/Userweb/View/static/";
		$config['Public']="Application/Userweb/View/Public/";
		$config_userweb['static']="Application/Userweb/View/Public/";
		$config['report']="Application/Userweb/View/Public/report/";

		$this->assign('config_userweb',$config_userweb);
		$this->assign('config',$config);

		// $wx_config[appid]="wxf64ba63493455027";
		// $wx_config[secret]="AsJi2YDHW9dBtxz3ZQzETQmD2QDulChrMji6XKX8u3CYzLEXXvQFT9itOWCQiU8E";

		$wx_config[appid]="wxf64ba63493455027";
		$wx_config[secret]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";

		if($_REQUEST[var_login]){
			$_SESSION['userweb']['userid']=1;
		}
		

		$this->user_wx_id=$_SESSION[userweb][user_wx_id];
		$this->user_id=$_SESSION['userweb']['userid'];



		// $_SESSION['userweb']['username']="admin:activity";
//var_dump($_SESSION['userweb']['username']);
		if(stristr($_SESSION['userweb']['username'], ':')){
		//	echo 1;
		//	exit;
			
        	$left=$this->left_child($this->userweb_id);
			$this->user_child_id=$this->user_id;
			$this->user_level=$_SESSION['userweb']['level'];
			$this->user_partment_id=$_SESSION['userweb']['partment_id'];
        	 // dump($left);exit;
        }else{
        	// $left=$this->left(1);
        	$left=$this->left_child($this->user_id);
			$this->user_child_id=$this->user_id;
			$this->user_level=$_SESSION['userweb']['level'];
			$this->user_partment_id=$_SESSION['userweb']['partment_id'];
        }

        #企业应用id
        $this->wx_agentid=M("partment")->where("id={$this->user_partment_id}")->getField("app_id");
        $wx_config[agentid]=$this->wx_agentid;

		$config["cur"]=$left[cur];

		$this->wxdata=$wx_config;

		$this->assign('auth', $left[lists]);
		$this->assign('auth_all', $left);

		$this->assign('left',$left);
		$this->assign('config',$config);

		//登录
		if(CONTROLLER_NAME!=Login){
			$this->public_logic=D("Public","Logic");
			$check=$this->public_logic->check_login();

			if (!$check){
				$this->uid=uniqid();
				$this->display("Login/sign_login");
				// $this->display("Login/login");
				exit();
			}
		}
	}

	public function showmsg($msg,$url=1){
		if($url==1){
			$url= $_SERVER['HTTP_REFERER'];
		}

		$this->msg=$msg;
		$this->url=$url;
		$this->display("Public/showmsg");
		exit;
	}

	public function del_action($tb,$id,$search){
		$db = M($tb);
		$res[status] = $db->where("id='".$id."'")->delete();
		$res[sql] =$db->getLastsql();
		return $res;
	}

	public function select_contacts($table="",$where="",$partment=""){
		if(empty($table)){
			$db=M("contacts");
			// $sql=$this->get_all_person();
			$data=$db->select();
		}else{
			if(empty($partment)){
				// $join=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_{$table}.user_id ";
				// $subsql=M($table)->join($join)->field("tl_contacts.id")->where($where)->select(false);
				// $data=M("contacts")->where("id NOT IN ({$subsql})")->select();
				$data=M("contacts")->select();
			}else{
				// $join=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_{$table}.user_id ";
				// $subsql=M($table)->join($join)->field("tl_contacts.id")->where($where)->select(false);
				// $data=M("contacts")->where("id NOT IN ({$subsql}) AND tl_contacts.partment_id={$partment}")->select();
				$data=M("contacts")->where("partment_id={$partment}")->select();
			}
			
		}
		
		return $data;
	}

	public function get_partment($partmentid=""){
		if(empty($partmentid)){
			$data=M("partment")->where("")->field('id,title,addtime')->order('id desc')->select();
		}else{
			$data=M("partment")->where("id={$partmentid}")->field('id,title,addtime')->order('id desc')->select();
		}
		
		return $data;
	}

	public function get_contacts_partment($partmentid=""){
		if(empty($partmentid)){
			$where="parentid=1";
		}else{
			$where="parentid={$partmentid}";
		}
		$data=M("contacts_partment")->where($where)->field('id,name,addtime')->order('addtime desc')->select();
		return $data;
	}

	#获取外勤部门下的全部id
	public function get_all_person(){
		$db=M("contacts_partment");
		$subsql1=$db->where("parentid=14")->field("id")->select(false);
		$subsql2=$db->where("parentid IN($subsql1) ")->field("id")->select(false);
		$res=$db->where("parentid IN($subsql2)")->field("id")->select(false);
		return $res;
	}

	#递归获取全部子部门id
	public function child($id){
		$GLOBALS["id"][]=$id;
		$arr=M("contacts_partment")->where("parentid={$id}")->field("id")->select();
		if($arr){
			foreach ($arr as $key => $value) {
				$this->child($value[id]);
			}
		}
		return $GLOBALS["id"];
	}


	public function left($user_id) {
		//获取当期路径参数
		$params=$this->page_params(1);

		//根据session用户id信息查询角色id信息
		$role_id = M('user')->where("id='$user_id'")->getField('role_id');
 
		//p($role_id);exit;
		//根据角色信息获得权限ids的信息
		$detail= M('user_role')->where("id='".$role_id."'")->find();
 	 	// dump($detail);exit;

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
				$tmp="c=".$data[$i][sub][$j][auth_c]."&a=".$data[$i][sub][$j][auth_a];
 				$cur="c=".CONTROLLER_NAME."&a=".ACTION_NAME;
				$is_cur=0;
				if($tmp==$cur){
					$is_cur=1;
				}else{
					$tmp="c=".$data[$i][sub][$j][auth_c]."&a=".$data[$i][sub][$j][auth_a].$data[$i][sub][$j][parameters];
					if(strpos($params,$tmp)){
						//$is_cur=1;
					}
				}
				 
 
				if($is_cur==1){
					$data[$i][cur]=1;
					$data[$i][sub][$j][cur]=1;
					$res[cur]["CONTROLLER_NAME"]=$data[$i][auth_name];
					$res[cur]["ACTION_NAME"]=$data[$i][sub][$j][auth_name];
					
					$res[cur]["app"]=$data[$i][sub][$j][app];
				//	var_dump($data[$i][sub][$j][app]);
				//	var_dump($data[$i][sub]);
				}
			} 
		}
		 
	 	if(!$res[cur]["app"]){
			$res[cur]["app"]="basic";
		}
	//	var_dump($res[cur]);
		
	//	exit;
	 	$res[app_lists]=$app_lists;
		$res[lists]=$data;
		//$data[s_info]= M('auth')->where(" app in (".$search.") AND auth_pid！=0")->select();
		 //var_dump($res);
 
		return $res;
	}
	
	public function left_child($user_id) {
		//根据session用户id信息查询角色id信息
		$role_id = M('user_child')->where("id='$user_id'")->getField('role_id');
		//p($role_id);exit;
		//根据角色信息获得权限ids的信息
		$sql = "select * from tl_user_child_role where role_id=" . $role_id;

		$rinfo = D()->query($sql);

		$auth_ids = $rinfo[0]['role_auth_ids'];
		$from_table = $rinfo[0]['from_table'];
		$_SESSION['from_table']=$from_table;
		//p($auth_ids);exit;
		//根据$auth_ids查询全部拥有的权限信息
		
		// $sql_app="select distinct app from tl_auth where auth_pid=0 and auth_id in ($auth_ids) order by sort asc";
		$sql_app="select distinct app from tl_auth where auth_id in ($auth_ids) AND  auth_pid=0";
		$detail=D()->query($sql_app);

		for($i=0;$i<count($detail);$i++){
			$app=$detail[$i]['app'];
			$res[app_lists][$i][val]=$app;
			if($app!='basic'){
			
				$res[app_lists][$i][title]=M('app')->where("value='$app'")->getField('title');
			}
		}
		//① 获得顶级权限
		// $sql = "select * from tl_auth where auth_pid=0 and auth_id in ($auth_ids) order by sort asc";
		$sql = "select * from tl_auth where auth_id in ($auth_ids)   AND  auth_pid=0  order by sort desc";
		$res[lists] = D()->query($sql);
		for($j=0;$j<count($res[lists]);$j++){
			$auth_path=$res[lists][$j]['auth_path'];
			$sourse_id=$res[lists][$j]['sourse_id'];
			$app=$res[lists][$j]['app'];
			//② 获得次顶级权限
			//var_dump($res[lists]);
			$sql = "select * from tl_auth where auth_pid='$sourse_id' and app='$app'   order by sort desc";
			//echo $sql;
			//exit;
			$res[lists][$j][sub] = D()->query($sql);

			for($jj=0;$jj<count($res[lists][$j][sub]);$jj++){
				//重组
				$tmp="c=".$res[lists][$j][sub][$jj][auth_c]."&a=".$res[lists][$j][sub][$jj][auth_a];
 				$cur="c=".CONTROLLER_NAME."&a=".ACTION_NAME;
				$is_cur=0;
				if($tmp==$cur){
					$is_cur=1;
				}else{
					$tmp="c=".$res[lists][$j][sub][$jj][auth_c]."&a=".$res[lists][$j][sub][$jj][auth_a].$res[lists][$j][sub][$jj][parameters];
					if(strpos($params,$tmp)){
						//$is_cur=1;
					}
				}
				 
 
				if($is_cur==1){
					$res[lists][$j][cur]=1;
					$res[lists][$j][sub][$jj][cur]=1;
					$res[cur]["CONTROLLER_NAME"]=$res[lists][$j][auth_name];
					$res[cur]["ACTION_NAME"]=$res[lists][$j][sub][$jj][auth_name];
					
					$res[cur]["app"]=$res[lists][$j][sub][$jj][app];
				//	var_dump($data[$i][sub][$j][app]);
				//	var_dump($data[$i][sub]);
				}
			}
		}
		

		//p($p_info);exit;
		//p($p_info);
		//② 获得次顶级权限
		
		//如果是admin管理员要现实全部权限

			//② 获得次顶级权限
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
	
	//获取当前链接
	function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {    // 如果是SSL加密则加上“s”
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	} 

}