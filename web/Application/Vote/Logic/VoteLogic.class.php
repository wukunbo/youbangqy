<?php
namespace Vote\Logic;
use Think\Model;

class VoteLogic {

	public function __construct(){	
		$this->db= new \Vote\Model\PublicModel();
	}

	public function add_action($post,$user_id,$post_id=-1){
		 // dump($post);exit;
		 $db=M("vote");
		 $post[user_id]=$user_id;
		 
		 $post[time_start]=strtotime($post[time_start]);
		 $post[time_end]=strtotime($post[time_end]);

		 $post[start_time]=strtotime($post[start_time]);
		 $post[end_time]=strtotime($post[end_time]);
		 
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

	#现场pc投票
	public function addpc_action($post,$user_id,$post_id=-1){	
		 $db=M("vote_pc");
		 $post[user_id]=$user_id;
		 
		 $post[start_time]=strtotime($post[start_time]);
		 $post[end_time]=strtotime($post[end_time]);
		 
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

	public function detail($search){
		$where=" 1 ";
		if($search['id']){
			$where.=" AND id={$search[id]}";
		}else{
			$where.=" AND id=-1";
		}

		$search["table"]="vote";
		$search["where"]=$where;
		$res[detail]=$this->db->Find($search);
		$res[status]=10001;
		return $res;
	}

	public function pcdetail($search){
		$where=" 1 ";
		if($search['id']){
			$where.=" AND id={$search[id]}";
		}else{
			$where.=" AND id=-1";
		}

		$search["table"]="vote_pc";
		$search["where"]=$where;
		$res[detail]=$this->db->Find($search);
		$res[status]=10001;
		return $res;
	}

	public function lists($search){
		$where=" 1 ";
		// if($search['user_id']){
		// 	$where.=" AND user_id=".$search[user_id]."";
		// }
		if($search[partment]){
			$where.=" AND tl_vote.partment=".$search[partment]."";
		}
		#投票类型
		if($search[type]){
			$where.=" AND tl_vote.type=".$search[type]."";
		}
		#投票状态
		if($search[status]){
			$where.=" AND tl_vote.status=".$search[status]."";
		}

		#时间筛选
		if($search[time_start] > ""){
			$time_start=strtotime($search[time_start]);
			$where.=" AND tl_vote.time_start > ".$time_start."";
		}
		if($search[time_end] > ""){
			$time_end=strtotime($search[time_end]);
			$where.=" AND tl_vote.time_end < ".$time_end."";
		}
		
		$search["table"]="vote";
		if($search[order]){
			$search["order"]="tl_vote.{$search[order]} desc ";
		}else{
			$search["order"]="tl_vote.id desc";
		}	
		$search["order"]="tl_vote.id desc";
		$search["join"]=" LEFT JOIN tl_partment ON tl_partment.id=tl_vote.partment ";
		$search["field"]=" tl_vote.*,tl_partment.title as partment_title";
		$search["where"]=$where;
		$data=$this->db->Page($search);
		// echo M("vote")->getLastsql();exit;
		return $data;
	}

	public function option_lists($search){
		$where=" 1 ";
		if($search['vote_id']){
			$where.=" AND vote_id={$search[vote_id]}";
		}
		$search['table']="vote_option";
      
        if($search['lists_orders']){
        	$search['order']=$search['lists_orders'];
        }

        if($search['status']){
        	$where.=" AND status={$search['status']}";
        }

        if($search[keyword]){
        	$where.=" AND (truename LIKE '%{$search[keyword]}%') ";
        }

        if($search[order]){
        	$search['order']=$search[order];
        	// echo 1231231;exit;
        }else{
        	$search['order']="addtime desc";
        }

        if($search[num]){
        	$array[num]=$search[num];
        }

        $search['where'] = $where;
    	$data=$this->db->Page($search);
    	$tmp=$data[content];
    	for($i=0;$i<count($tmp);$i++){
    		$where=" count_vote > {$tmp[$i][count_vote]}";
    		if($search[vote_id]){
    			$where.=" AND vote_id={$search[vote_id]} ";
    		}
    		$tmp[$i][paiming]=M('vote_option')->where($where)->count()+1;
    		$tmp[$i][paiming]=$tmp[$i][paiming] == 0 ? 1 : $tmp[$i][paiming];
    	}
    	$data[content]=$tmp;
    	return $data;
	}

	public function option_status($search){
		$db=M("vote_option");
		$where=" id= '".$search[id]."' ";
		if($search[to_status]==1){
			$db->where($where)->setField("status",$search[to_status]);
			$res[status]=10001;
		}
		if($search[to_status]==2){
			$db->where($where)->setField("status",$search[to_status]);
			$res[status]=10001;
		}
		$res[sql]=$db->getLastsql();
		return $res; 
	}

	public function option_add_action($post,$user_id){
		$where=" vote_id=".$post[vote_id]." AND user_id=".$user_id." ";
		$is=M("vote_option")->where($where)->getField("id");

		if($is){
			M("vote_option")->where($where)->save($post);
			$res[status]=12001;
			return $res; 
		}

		$post[addtime]=time();
		$post[user_id]=$user_id;
		$count=M("vote_option")->where("vote_id='".$post[vote_id]."'")->count();
		$post[num]=$count+1;
		M("vote_option")->add($post);
		$res[sql]=M("vote_option")->getLastsql();
		$res[vote_id]=$post[vote_id];
		$res[status]=10001;
		return $res; 
	}

	public function htoption_add_action($post,$option_id){
		$post[addtime]=time();
		if(empty($option_id)){
			$count=M("vote_option")->where("vote_id='".$post[vote_id]."'")->count();
			$post[num]=$count+1;
			M("vote_option")->add($post);
		}else{
			unset($post[user_id]);
			M("vote_option")->where("id={$option_id}")->save($post);
		}
		$res[status]=10001;
		$res[sql]=M("vote_option")->getLastsql();
		return $res; 
	}

	#投票记录统计
	public function vote_log($voteId,$option_id="",$search=""){
		$pre=C("DB_PREFIX");
		$where=" 1 ";
		$where.=" AND ".$pre."vote_votelog.vote_id='".$voteId."'";
		if(!empty($option_id)){
			$where.=" AND ".$pre."vote_votelog.option_id='".$option_id."'";
		}
		$array['table']="vote_votelog";  
		// $array['join']=" LEFT JOIN tl_user ON tl_user.id=tl_vote_votelog.user_id ";
		$array['join'].=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_vote_votelog.user_id ";
		$array['join'].=" LEFT JOIN tl_vote_option ON tl_vote_option.id=tl_vote_votelog.option_id ";
		$array['where']=$where;
		$array['field']="tl_contacts.*,tl_vote_votelog.day,tl_vote_votelog.user_id as log_user,tl_vote_option.truename";

		if($search[num]){
			$array[num]=$search[num];
		}

		$data=$this->db->Page($array);
		return $data;
	}


	#选择活动名单
	public function select_contact($voteId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment_id,partment,code,work_group,wx_userid")->find();
			$dataList=array('vote_id'=>$voteId,'user_id'=>$contact['wx_userid'],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			$array[table]="vote_apply";
			$array[data]=$dataList;
			$vote_apply=M("vote_apply")->where("vote_id={$voteId} AND user_id='{$contact[wx_userid]}' ")->find();
			// echo M("vote_apply")->getLastsql();exit;
			if(!$vote_apply){
				$this->db->Add($array);
				// echo M("vote_apply")->getLastsql();exit;
			}	
		}
		$res[sql]=M("contacts")->getLastsql();
		$res[status]=10001;
		// dump($res);exit;
		// exit;
		return $res; 	
	}

	#导入名单
	public function to_apply($persons,$voteId){
		$db=M('contacts');
		$addtime=time();
		$vote_apply=M("vote_apply");
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"vote_id"=>$voteId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"addtime"=>$addtime);
				$vote=$vote_apply->where("user_id='{$res[wx_userid]}' AND vote_id={$voteId}")->find();
				if($vote){
					$vote_apply->where("user_id='{$res[wx_userid]}' AND vote_id={$voteId}")->save($dataList);
				}else{
					$vote_apply->add($dataList);
				}

			}
		}
	}


}