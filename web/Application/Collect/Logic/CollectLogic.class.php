<?php
namespace Collect\Logic;
use Think\Model;
class CollectLogic {

	public function __construct(){
		$this->user_id=$_SESSION[user][userid]=3;
		$this->db= new \Collect\Model\PublicModel();
	}

	public function add_action($post,$post_id=-1){	
		$db=M("collect");

		$post[time_start]=strtotime($post[time_start]);
		$post[time_end]=strtotime($post[time_end]);

		if($post_id==-1 || $post_id==""){
			$post[addtime]=time();
			$id=$db->add($post);
		}else{
			$id=$db->where("id=".$post_id."")->save($post);
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		$res[id]=$id;
		return $res; 
	}

	public function detail($search,$user_id=""){
		$where=" 1 ";
		if($search['id']){
			$where.=" AND id={$search[id]}";
		}else{
			$where.=" AND id=-1";
		}

		$search["table"]="collect";
		$search["where"]=$where;
		$res[detail]=$this->db->Find($search);
		$res[status]=10001;
		if($user_id){
			$array['field']="id,user_id,truename,partment,content,is_able";
			$array['where']="collect_id={$search[id]} AND user_id={$user_id}";
			$array['table']="collect_apply";
			$user=$this->db->Find($array);
			$res[user]=$user;
		}
		return $res;
	}

	public function lists($search){
		$where=" 1 ";

		if($search[partment]){
			$where.=" AND tl_collect.partment=".$search[partment]."";
		}

		if($search[status]){
			$where.=" AND tl_collect.status={$search[status]} ";
		}

		if($search[time_start]){
			$time_start=strtotime($search[time_start]);
			$where.=" AND tl_collect.time_start > {$time_start} ";
		}

		if($search[time_end]){
			$time_end=strtotime($search[time_end]);
			$where.=" AND tl_collect.time_end < {$time_end} ";
		}

		$array["join"]="LEFT JOIN tl_partment ON tl_partment.id=tl_collect.partment ";
		$array["field"]="tl_collect.*,tl_partment.title as partment_title ";

		$array["table"]="collect";

		if($search[order]){
			$array["order"]="tl_collect.{$search[order]} desc ";
		}else{
			$array["order"]="tl_collect.id desc";
		}
		
		$array["where"]=$where;
		$data=$this->db->Page($array);
		return $data;
	}


	#选择活动名单
	public function select_contact($collectId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment_id,partment,code,work_group,wx_userid")->find();
			$dataList=array('collect_id'=>$collectId,'user_id'=>$contact['wx_userid'],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			$array[table]="collect_apply";
			$array[data]=$dataList;
			$collect_apply=M("collect_apply")->where("survey_id={$collectId} AND user_id='{$contact[wx_userid]}' ")->find();
			if(!$collect_apply){
				$this->db->Add($array);
			}
		}
		$res[sql]=M("contacts")->getLastsql();
		$res[status]=10001;
		return $res;
	}

	#收集信息添加
	public function add_person($post,$user_id){
		$db=M("collect_person");
		$post[user_id]=$user_id;
		$post[addtime]=time();
		$id=$db->add($post);
		return $id;
	}

	public function add_person_extend($posts,$user_id,$collect_id){
		$db=M("collect_extend_info");
		// dump($posts);exit;
		foreach ($posts as $key => &$post) {
			$dataList=array("user_id"=>$user_id,"collect_id"=>$collect_id,"addtime"=>time());
			$person_id=M("collect_person")->add($dataList);
            foreach ($post as $key1 => &$data) {
               $data[person_id]=$person_id;
               $db->add($data);
            }
            
        }
	}

	#展示收集信息
	public function show_add($ids){
		$array[table]="collect_person";
		$array[where]="id IN ({$ids})";
		$data[person]=$this->db->Select($array);
		$array[table]="collect";
		$array[where]="id={$data[person][0][collect_id]}";
		$data[collect]=$this->db->Find($array);
		return $data;
	}

	public function show_person($collect_id,$user_id){
		$db=M("collect_person");
		$res=$db->where("collect_id={$collect_id} AND user_id='{$user_id}' ")->field("id,user_id,collect_id,addtime")->select();
		foreach ($res as $key => &$value) {
			$value[extend]=M("collect_extend_info")->where("person_id={$value[id]}")->order("extend_id ASC")->select();
		}
		$data[person]=$res;
		$data[collect]=M("collect")->where("id={$collect_id}")->find();

		return $data;
	}

	public function person_list1($collectId,$area){
		$array[table]="collect_person";
		$array[where]="collect_id={$collectId} AND tl_contacts.area='{$area}' ";
		$array[join]=" LEFT JOIN tl_user ON tl_user.id=tl_collect_person.user_id ";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$array[field]="tl_contacts.area,tl_contacts.work_group,tl_contacts.code,tl_contacts.name as contact_name,tl_collect_person.* ";
		$array[order]="tl_collect_person.addtime desc ";
		$data=$this->db->Page($array);
		return $data;
	}

	#查看收集人员
	public function person_list($collectId,$area){
		$subsql=M("contacts_partment")->where("parentid={$area}")->field("id")->select(false);
		$array[table]="collect_person";
		$array[where]="collect_id={$collectId} AND tl_contacts.partment_id IN ({$subsql}) ";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_collect_person.user_id ";
		$array[field]="tl_contacts.name as contact_name,tl_collect_person.* ";
		$array[order]="tl_collect_person.addtime desc ";
		$data=$this->db->Page($array);
		return $data;
	}

	#人员详细
	public function detail_person($collectId,$ids){
		$array[table]="collect_apply";
		$array[where]="tl_collect_apply.partment_id IN ({$ids}) AND tl_collect_apply.collect_id={$collectId} ";
		$array[join]="INNER JOIN tl_collect_person ON tl_collect_person.user_id=tl_collect_apply.user_id AND tl_collect_person.collect_id={$collectId}";
		$array[field]="tl_collect_apply.truename,tl_collect_person.* ";
		$array[order]="tl_collect_person.addtime desc ";
		$data=$this->db->Page($array);
		// dump($data);exit;
		return $data;
	}
	

	#收集信息区域统计
	public function collect_count1($collectId){
		$array[table]="collect_apply";
		$array[where]="tl_collect_apply.collect_id={$collectId}";
		$array[join]=" LEFT JOIN tl_user ON tl_user.id=tl_collect_apply.user_id ";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$array[group]=" tl_contacts.area ";
		$array[field]="tl_contacts.area,count(tl_contacts.id) as allcount ";
		$array[order]="tl_collect_apply.addtime desc ";
		$data=$this->db->Page($array);
		$db=M('collect_person');
		foreach ($data[content] as $key => &$value) {
			$where="tl_contacts.area='{$value[area]}' AND collect_id={$collectId} ";
			$join=" LEFT JOIN tl_user ON tl_user.id=tl_collect_person.user_id ";
			$join.=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
			$field=" count(tl_collect_person.user_id) as applycount ";
			$value[applycount]=$db->where($where)->join($join)->field($field)->select()[0][applycount];
		}
		return $data;
	}

	#收集信息区域统计
	public function collect_count($collectId){
		$db=M("contacts_partment");
		$data=$db->where("parentid=14")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$sub=$db->where("parentid IN ({$subsql})")->field("id")->select(false);
			$allcount=M("collect_apply")->where("partment_id IN ({$sub}) AND collect_id={$collectId} ")->count();
			$applycount=M("collect_person")->join("LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_collect_person.user_id ")->where("partment_id IN ({$sub}) AND collect_id={$collectId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["applycount"]=$applycount;
		}
		return $data;
	}

	#收集信息区域代码统计
	public function code_count($collectId,$area){
		$db=M("contacts_partment");
		$data=$db->where("parentid={$area}")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$allcount=M("collect_apply")->where("partment_id IN ({$subsql}) AND collect_id={$collectId} ")->count();
			$applycount=M("collect_person")->join("LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_collect_person.user_id ")->where("partment_id IN ({$subsql}) AND collect_id={$collectId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["applycount"]=$applycount;
		}
		return $data;
	}


	#导入名单
	public function to_apply($persons,$collectId){
		$db=M('contacts');
		$addtime=time();
		$collect_apply=M("collect_apply");
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"collect_id"=>$collectId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"addtime"=>$addtime);
				$collect=$collect_apply->where("user_id='{$res[wx_userid]}' AND collect_id={$collectId}")->find();
				if($collect){
					$collect_apply->where("user_id='{$res[wx_userid]}' AND collect_id={$collectId}")->save($dataList);
				}else{
					$collect_apply->add($dataList);
				}

			}
		}
	}


	//添加收集扩展字段
	public function add_collect_extend($collect_id,$posts){
		$db=M("collect_extend_field");
		// dump($posts);exit;
		foreach ($posts as $key => $post) {
			if(empty($post[id])){
				$post[collect_id]=$collect_id;
				$db->add($post);
			}else{
				$db->where("id={$post[id]}")->save($post);
			}
		}
	} 

	

}