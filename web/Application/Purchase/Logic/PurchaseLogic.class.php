<?php
namespace Purchase\Logic;
use Think\Model;
class PurchaseLogic {

	public function __construct(){
		$this->user_id=$_SESSION[user][userid];
		$this->db= new \Purchase\Model\PublicModel();
	}

	public function add_action($post,$post_id=-1){	
		$db=M("purchase_good");

		if($post_id==-1 || $post_id==""){
			$post[addtime]=time();
			$db->add($post);
		}else{
			$db->where("id=".$post_id."")->save($post);
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		return $res; 
	}

	#添加采购活动
	public function purchase_action($post,$post_id=-1){	
		$db=M("purchase");

		$post[time_start]=strtotime($post[time_start]);
		$post[time_end]=strtotime($post[time_end]);

		if($post_id==-1 || $post_id==""){
			$post[addtime]=time();
			$db->add($post);
		}else{
			$db->where("id=".$post_id."")->save($post);
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		return $res; 
	}


	public function detail($search,$user_id=""){
		$where=" 1 ";
		if($search['id']){
			$where.=" AND id={$search[id]}";
		}else{
			$where.=" AND id=-1";
		}

		$search["table"]="purchase_good";
		$search["where"]=$where;
		$res[detail]=$this->db->Find($search);

		$purchase_id=$res[detail][purchase_id];
		$search["table"]="purchase";
		$search["where"]="id={$purchase_id}";
		$res[purchase]=$this->db->Find($search);

		$res[status]=10001;
		if($user_id){
			$array['field']="id,user_id,truename,partment,content,is_able";
			$array['where']="purchase_id={$purchase_id} AND user_id={$user_id}";
			$array['table']="purchase_apply";
			$user=$this->db->Find($array);
			$res[user]=$user;
		}
		return $res;
	}

	#采购活动详情
	public function purchase_detail($search,$user_id=""){
		$where=" 1 ";
		if($search['id']){
			$where.=" AND id={$search[id]}";
		}else{
			$where.=" AND id=-1";
		}

		$search["table"]="purchase";
		$search["where"]=$where;
		$res[detail]=$this->db->Find($search);
		$res[status]=10001;
		if($user_id){
			$array['field']="id,user_id,truename,partment,content,is_able";
			$array['where']="purchase_id={$search[id]} AND user_id={$user_id}";
			$array['table']="purchase_apply";
			$user=$this->db->Find($array);
			$res[user]=$user;
		}
		return $res;
	}
	
	

	public function lists($search){
		$where=" 1 ";

		if($search[partment]){
			$where.=" AND tl_purchase.partment=".$search[partment]."";
		}

		if($search[status]){
			$where.=" AND tl_purchase.status={$search[status]} ";
		}

		if($search[time_start]){
			$time_start=strtotime($search[time_start]);
			$where.=" AND tl_purchase.time_start > {$time_start} ";
		}

		if($search[time_end]){
			$time_end=strtotime($search[time_end]);
			$where.=" AND tl_purchase.time_end < {$time_end} ";
		}

		$array["join"]="LEFT JOIN tl_partment ON tl_partment.id=tl_purchase.partment ";
		$array["field"]="tl_purchase.*,tl_partment.title as partment_title ";

		$array["table"]="purchase";

		if($search[order]){
			$array["order"]="tl_purchase.{$search[order]} desc ";
		}else{
			$array["order"]="tl_purchase.id desc";
		}
		
		$array["where"]=$where;
		$data=$this->db->Page($array);
		return $data;
	}

	#商品列表
	public function good_lists($search){
		$where=" purchase_id={$search[purchase_id]} ";
		if($search['user_id']){
			
		}
		$search["table"]="purchase_good";
		$search["order"]="addtime desc";
		$search["where"]=$where;
		$data=$this->db->Page($search);
		return $data;
	}
	

	public function purchase_lists($id,$user_id=""){

		$search["table"]="purchase";
		$search["where"]=" id={$id} AND status=1";
		$data[purchase]=$this->db->Find($search);

		$array[table]="purchase_good";
		$array[order]="addtime desc";
		$array[where]="purchase_id={$id}";
		$data[goods]=$this->db->Select($array);

		if($user_id){
			$array['field']="id,user_id,truename,partment,content,is_able";
			$array['where']="purchase_id={$id} AND user_id={$user_id}";
			$array['table']="purchase_apply";
			$user=$this->db->Find($array);
			$data[user]=$user;
		}
		
		return $data;
	}

	public function purchase_sure($post,$user_id=""){

		$db=M("purchase_good");
		$addtime=time();
		foreach ($post as $key => $value) {
			$arr=explode("-",$value);
			$detail=$db->where("id={$arr[0]}")->field("id,purchase_id,num,title,price,image_thumb")->find();
			$detail[total]=$arr[1];
			$data[good][]=$detail;
			$dataList=array("user_id"=>$user_id,"good_id"=>$arr[0],"num"=>$arr[1],"addtime"=>$addtime);
			M("purchase_log")->add($dataList);
		}

		$purchase_id=$data[good][0][purchase_id];
		$data[purchase]=M("purchase")->where("id={$purchase_id}")->field("id,time_start,time_end")->find();

		if($user_id){
			$array['field']="id,user_id,truename,partment,content,is_able";
			$array['where']="purchase_id={$purchase_id} AND user_id={$user_id}";
			$array['table']="purchase_apply";
			$user=$this->db->Find($array);
			$data[user]=$user;
		}
		
		return $data;
	}

	#确认申购物品
	public function goods_sure($post,$user_id=""){
		// echo $user_id;exit;
		$db=M("purchase_good");
		foreach ($post as $key => $value) {
			$arr=explode("-",$value);
			$detail=$db->where("id={$arr[0]}")->field("id,purchase_id,num,title,price,image_thumb")->find();
			$detail[total]=$arr[1];
			$data[good][]=$detail;
		}

		$purchase_id=$data[good][0][purchase_id];
		$data[purchase]=M("purchase")->where("id={$purchase_id}")->field("id,time_start,time_end")->find();	

		if($user_id){
			$array['field']="id,user_id,truename,partment,content,is_able";
			$array['where']="purchase_id={$purchase_id} AND user_id={$user_id}";
			$array['table']="purchase_apply";
			$user=$this->db->Find($array);
			// echo M("purchase_apply")->getLastsql();exit;
			$data[user]=$user;
		}
		
		return $data;
	}

	public function purchase_status($search){
		$db=M("purchase");
		$where=" id= '".$search[id]."' ";
		if($search[to_status]==1){
			$db->where($where)->setField("status",$search[to_status]);
			$res[status]=10001;
		}
		if($search[to_status]==-1){
			$db->where($where)->setField("status",$search[to_status]);
			$res[status]=10001;
		}
		$res[sql]=$db->getLastsql();
		return $res; 
	}

	#选择活动名单
	public function select_contact($purchaseId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment_id,partment,code,work_group,wx_userid")->find();
			$dataList=array('purchase_id'=>$purchaseId,'user_id'=>$contact['wx_userid'],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			$array[table]="purchase_apply";
			$array[data]=$dataList;
			$purchase_apply=M("purchase_apply")->where("purchase_id={$purchaseId} AND user_id='{$contact[wx_userid]}' ")->find();
			if(!$purchase_apply){
				$this->db->Add($array);
			}
		}
		$res[sql]=M("contacts")->getLastsql();
		$res[status]=10001;
		// dump($res);exit;
		return $res;
	}

	#申购查看统计
	public function purchase_count($purchaseId){
		$db=M("purchase_log");
		$join=" LEFT JOIN tl_purchase_good ON tl_purchase_good.id=tl_purchase_log.good_id ";
		$where=" tl_purchase_good.purchase_id={$purchaseId} ";
		$field="tl_purchase_good.title,tl_purchase_log.good_id,tl_purchase_good.image_thumb,count(tl_purchase_log.good_id) as alluser,sum(tl_purchase_log.num) as allnum ";
		$group=" tl_purchase_log.good_id ";
		$array[table]="purchase_log";
		$array[where]=$where;
		$array[order]="tl_purchase_log.addtime desc";
		$array[join]=$join;
		$array[field]=$field;
		$array[group]=$group;
		$data=$this->db->Page($array);
		return $data;
	}

	#申购人员统计
	public function purchase_log($goodId){
		$array[table]="purchase_log";
		// $array[join]=" LEFT JOIN tl_user ON tl_user.id=tl_purchase_log.user_id ";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_purchase_log.user_id ";
		$array[where]="tl_purchase_log.good_id={$goodId} ";
		$array[order]="tl_purchase_log.addtime desc";
		$array[field]=" tl_purchase_log.num,tl_purchase_log.addtime as logtime,tl_contacts.* ";
		$data=$this->db->Page($array);
		return $data;
	}

	#导入名单
	public function to_apply($persons,$purchaseId){
		$db=M('contacts');
		$addtime=time();
		$purchase_apply=M("purchase_apply");
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"purchase_id"=>$purchaseId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"addtime"=>$addtime);
				$purchase=$purchase_apply->where("user_id='{$res[wx_userid]}' AND purchase_id={$purchaseId}")->find();
				if($purchase){
					$purchase_apply->where("user_id='{$res[wx_userid]}' AND purchase_id={$purchaseId}")->save($dataList);
				}else{
					$purchase_apply->add($dataList);
				}

			}
		}
	}

}