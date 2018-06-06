<?php
namespace Userweb\Logic;
use Think\Model;

class ContactsLogic extends PublicLogic {

	public function __construct(){	
		$this->db= D("Public","Model");
	}

	#通讯录列表
	public function lists($search){
		$search[table]="contacts";
		$search[where]="1 ";
		if(!empty($search[policy_type])){
			$search[where].=" AND partment='{$search[partment]}' ";
		}
		if($search[member] > ""){
			$search[where].=" AND member={$search[member]} ";
		}
		if(!empty($search[partment])){
			$search[where].=" AND partment_id IN({$search[partment]}) ";
		}

		if($search[user_code]){
			$search[where].=" AND wx_userid='{$search[user_code]}' ";
		}

		$search[order]="addtime desc";
		$data=$this->db->Page($search);
		return $data;
	}

	#ajax返回通讯录
	public function ajax_lists($search,$type="checkbox"){
		$search[table]="contacts";
		$search[where]="1 ";
		if(!empty($search[partment])){
			$search[where].=" AND partment_id IN({$search[partment]}) ";
		}
		if(!empty($search[user_partment])){
			$search[where].=" AND partment_id IN({$search[user_partment]}) ";
		}
		$search[filed]="wx_userid,name,phone,email,partment";
		$data=$this->db->Select($search);
		$html="";
		foreach ($data as $key => $value) {
			$html.="<tr>";
			$html.='<td><input name="namelist" type="'.$type.'" value="'.$value[wx_userid].'" /></td>';
			$html.='<td>'.$value[name].'</td>';
			$html.='<td>'.$value[wx_userid].'</td>';
			$html.='<td>'.$value[partment].'</td>';
			$html.='<td>'.$value[phone].'</td>';
			$html.='<td>'.$value[email].'</td>';
		}
		return $html;
	}

	public function ajax_name($name,$type="checkbox"){
		$db=M("contacts");
		$where="name LIKE '%".$name."%' ";
		$filed="wx_userid,name,phone,email,partment";
		$data=$db->where($where)->field($filed)->select();
		$html="";
		foreach ($data as $key => $value) {
			$html.="<tr>";
			$html.='<td><input name="namelist" type="'.$type.'" value="'.$value[wx_userid].'" /></td>';
			$html.='<td>'.$value[name].'</td>';
			$html.='<td>'.$value[wx_userid].'</td>';
			$html.='<td>'.$value[partment].'</td>';
			$html.='<td>'.$value[phone].'</td>';
			$html.='<td>'.$value[email].'</td>';
		}
		return $html;
	}

	public function get_partmentName($partmentId){
		$db=M("contacts_partment");
		
		$arr=array();
		$partment1 = $partmentId;
		while ($partment1 != 1) {
			$partment=$db->where("id={$partment1}")->find();
			$partment1=$partment[parentid];
			$arr[] = $partment[name];
		}
		krsort($arr);
		return implode("-",$arr);
	}

	public function add($search){
		$where=" 1 ";
		if($search['id']){
			$where.=" AND id={$search[id]}";
		}else{
			$where.=" AND id=-1";
		}

		$search["table"]="contacts";
		$search["where"]=$where;
		$res[detail]=$this->db->Find($search);
		$res[status]=10001;
		return $res;
	}

	public function add_action($post){	
		 $db=M("contacts");
		 $post_id=$post[id];
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

	#获取微信部门列表
	public function weixin_partment($data){
		$db=M("contacts_partment");
		$db->where('1')->delete(); 
		$addtime=time();
		foreach ($data as $key => $res) {
			// $partment=$db->where("id={$res[id]}")->find();
			$datalist=array("id"=>$res[id],"name"=>$res[name],"parentid"=>$res[parentid],"order"=>$res[order],"addtime"=>$addtime);
			// if(!$partment){
				$db->add($datalist);
			// }else{
			// 	$db->where("id={$res[id]}")->save($datalist);
			// }
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		return $res; 
	}

	#获取微信部门列表下的用户
	public function weixin_contacts($data,$partment_name="",$partment_id=""){
		set_time_limit(0);
		$db=M("contacts");
		$contacts_partment=M("contacts_partment");
		$addtime=time();

		$wx_userid_str="";

		foreach ($data as $key => $res) {
			$contact=$db->where("wx_userid='{$res[userid]}'")->find();

			$partment=$contacts_partment->where("id={$res[department][0]}")->getField("name");
			$datalist=array("wx_userid"=>$res[userid],"name"=>$res[name],"avatar_url"=>$res[avatar],"partment"=>$partment,"partment_id"=>$res[department][0],"gender"=>$res[gender],"weixinid"=>$res[weixinid],"phone"=>$res[mobile],"email"=>$res[email],"job"=>$res[position],"addtime"=>$addtime);

			if(!$contact){
				$db->add($datalist);
			}else{
				$db->where("wx_userid='{$res[userid]}'")->save($datalist);
			}

			// $wx_userid_arr[$key]=$res[userid];
			if($key==0){
				$wx_userid_str.="'{$res[userid]}'";
			}else{
				$wx_userid_str.=",'{$res[userid]}'";
			}
			
		}

		// $wx_userid_str=implode(",",$wx_userid_arr);
		$db->where("wx_userid NOT IN({$wx_userid_str})")->delete();

		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		return $res; 
	}

	public function select_left($partmentId,$is_ids){
		$db=M("contacts_partment");
		$data=$db->where("parentid in ({$partmentId})")->field("id,name,parentid")->select();
		$arr=array(); 
		if($is_ids == -1){
			if($data){
				foreach ($data as $key => $value) {
					$value["is_show"]=1;
					$value["list"]=$this->select_left($value["id"],$is_ids);
					$arr[]=$value;
				}
				return $arr;
			}
		}else{
			$is_ids=",".$is_ids.",";
			 // var_dump($is_ids);
			if($data){
				foreach ($data as $key => $value) {
					 
					$value["is_show"]=0;
					if(stristr($is_ids,",".$value["id"].",")){
						$value["is_show"]=1;
					}
					$value["list"]=$this->select_left($value["id"],$is_ids);
					$arr[]=$value;
				}
				return $arr;
			}
		}
	}


}
