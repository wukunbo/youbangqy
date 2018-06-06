<?php
namespace Lottery\Logic;
use Think\Model;
class IndexLogic {

	public function __construct(){
		// $this->user_id=$_SESSION[userweb][userid];
		$this->db= new \Lottery\Model\PublicModel();
	}

	public function lists($search){
		$where=" 1 ";
		if($search['user_id']){
			$where.=" AND user_id=".$search[user_id]."";
		}
		if($search[partment]){
			$where.=" AND tl_lottery.partment=".$search[partment]."";
		}

		if($search[type]){
			$where.=" AND tl_lottery.type={$search[type]} ";
		}

		if($search[status]){
			$where.=" AND tl_lottery.status={$search[status]} ";
		}

		if($search[start_time]){
			$start_time=strtotime($search[start_time]);
			$where.=" AND tl_lottery.start_time > {$start_time} ";
		}

		if($search[end_time]){
			$end_time=strtotime($search[end_time]);
			$where.=" AND tl_lottery.end_time < {$end_time} ";
		}

		$array["table"]="lottery";
		if($search[order]){
			$array["order"]="tl_lottery.{$search[order]} desc ";
		}else{
			$array["order"]="tl_lottery.id desc";
		}
		
		$array["join"]="LEFT JOIN tl_partment ON tl_partment.id=tl_lottery.partment ";
		$array["field"]="tl_lottery.*,tl_partment.title as partment_title ";
		$array["where"]=$where;
		$data=$this->db->Page($array);
		return $data;
	}

	public function detail($id,$user_id=""){
		$where=" 1 AND id='".$id."'";
		$detail=M("lottery")->where($where)->find();
		$data[detail]=$detail;
		if($user_id){
			$array['field']="id,user_id,lottery_count,truename,partment_id";
			$array['where']="lottery_id={$id} AND user_id={$user_id}";
			$array['table']="lottery_data";
			$user=$this->db->Find($array);
			// echo M("lottery_data")->getLastsql();exit;
			$data[user]=$user;
		}
		return $data;    	

	}

	//添加抽奖活动
	public function add_action($post){
		$db=M("lottery");
		$post[start_time]=strtotime($post[start_time]);
		$post[end_time]=strtotime($post[end_time]);
		$post[addtime]=time();
		$post[type]=1;
		
		if(!$post[id]){
			unset($post[id]);
			$post[user_id]=$this->user_id;
			$id=$db->add($post);
		}else{
		 	$db->where("id=".$post[id]."")->save($post);
		 }
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		$res[id]=$id;
		return $res; 
	}

	#添加pc抽奖
	public function pc_action1($post){
		$db=M("lottery_pc");
		$post[addtime]=time();
		// $post[password]=md5($post[password]);
		$post[start_time]=strtotime($post[start_time]);
		$post[end_time]=strtotime($post[end_time]);
		$area=$post[area];
		unset($post[area]);
		$id=$db->add($post);
		foreach ($area as $key => $value) {
			$dataList=array("lottery_pc_id"=>$id,"area"=>$value,"user_id"=>"");
			M("lottery_pc_area")->add($dataList);
		}
		return $id;
	}

	#添加pc抽奖
	public function pc_action($post){
		$db=M("lottery");
		$post[type]=2;#pc抽奖
		$post[addtime]=time();
		// $post[password]=md5($post[password]);
		$post[start_time]=strtotime($post[start_time]);
		$post[end_time]=strtotime($post[end_time]);
		$post[back_img]=$post[image_start];
		$area=$post[lotteryarea];
		unset($post[lotteryarea]);
		if(!$post[id]){
			$id=$db->add($post);
			foreach ($area as $key => $value) {
				$dataList=array("lottery_pc_id"=>$id,"area"=>$value[area],"pc_user_id"=>$value[pc_user_id]);
				M("lottery_pc_area")->add($dataList);
			}
		}else{
			unset($post[user_id]);
		 	$db->where("id=".$post[id]."")->save($post);
		 	foreach ($area as $key => $value) {
		 		M("lottery_pc_area")->where("id={$value[id]}")->save($value);
		 	}

		 	$id=$post[id];
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		$res[id]=$id;
		return $res; 
	}

	#活动奖品详情
	public function award_detail($lottery_id){
		$where=" 1 AND lottery_id='".$lottery_id."'";
		$detail=M("lottery_award")->where($where)->order("type asc")->select();
		$data[detail]=$detail;
		$data[award]=array("一等奖","二等奖","三等奖","四等奖","五等奖","六等奖");	
		return $data;
	}

	//设置奖品
	public function add_award($post){
		$db=M("lottery_award");
		$total=count($post);
		for($i=1;$i<=$total;$i++){
			$post[$i][addtime]=time();
			$post[$i][type]=$i;
			if(!$post[$i][id]){
				$db->add($post[$i]);
			}else{
				$db->where("id=".$post[$i][id]."")->save($post[$i]);
			}
			
		}
		$res[sql]=$db->getLastsql();
		$res[status]=10001;
		return $res; 
	}

	public function change_status($data){
		$res=M("lottery")->where("id='".$data[id]."'")->setField("status",$data[to_status]);
		return $res;
	}

	#人员详细
	public function detail_person($lotteryId,$ids){
		$array[table]="lottery_data";
		$array[where]="partment_id IN ({$ids}) AND lottery_id={$lotteryId} ";
		$data=$this->db->Page($array);
		return $data;
	}


	#选择活动名单
	public function select_contact($lotteryId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment,partment_id,code,work_group,wx_userid")->find();
			$dataList=array('lottery_id'=>$lotteryId,'user_id'=>$contact[wx_userid],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			$array[table]="lottery_apply";
			$array[data]=$dataList;
			$lottery_apply=M("lottery_apply")->where("lottery_id={$lotteryId} AND user_id='{$contact[wx_userid]}' ")->find();
			if(!$lottery_apply){
				$this->db->Add($array);
			}
		}
		$res[sql]=M("contacts")->getLastsql();
		$res[status]=10001;
		return $res; 	
	}

	#导入抽奖数据匹配通讯录
	public function lottery_contact($persons,$lotteryId){
		$db=M('contacts');
		$baodan=M('lottery_baodan');
		$ids=array();
		$addtime=time();
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"lottery_id"=>$lotteryId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"lottery_count"=>$person[3],"addtime"=>$addtime);
				$array[table]="lottery_apply";
				$array[data]=$dataList;
				$lottery=M("lottery_apply")->where("user_id='{$res[wx_userid]}' AND lottery_id={$lotteryId}")->find();
				if(!$lottery){
					$id=$this->db->Add($array);
					$ids[]=$id;
					#导入保单
					foreach (explode(",",$person[2]) as $value) {
						$baodandata=array("user_id"=>$res[wx_userid],"lottery_id"=>$lotteryId,"baodan"=>$value);
						$arr[table]="lottery_baodan";
						$arr[data]=$baodandata;
						$this->db->Add($arr);
					}
					
				}
			}
		}
		return $ids;
	}

	#导入抽奖数据
	public function lottery_data($persons,$lotteryId){
		$db=M('contacts');
		$addtime=time();
		M("lottery_data")->where("lottery_id={$lotteryId}")->delete();
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			$dataList=array("user_id"=>$res[wx_userid],"lottery_id"=>$lotteryId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"baodan"=>$person[2],"lottery_count"=>$person[3],"remark"=>$person[4],"addtime"=>$addtime);
			$array[table]="lottery_data";
			$array[data]=$dataList;
			$this->db->Add($array);
		}
	}

	public function sure_contact($ids){
		$db=M("lottery_apply");
		$where=" tl_lottery_apply.id IN ({$ids}) ";
		$join.=" LEFT JOIN tl_contacts ON  tl_contacts.wx_userid=tl_lottery_apply.user_id ";
		$field=" tl_lottery_apply.lottery_count,tl_contacts.* ";
		$data=$db->where($where)->join($join)->field($field)->select();
		return $data;
	}

	#已导入抽奖数据
	public function check_apply(){
		
	}

	#中奖
	public function win1($lotteryId,$user_id){

		$lottery_count=M("lottery_apply")->where("lottery_id={$lotteryId} AND user_id={$user_id}")->getField("lottery_count");
		if ($lottery_count <= 0){
			$data[lottery_count]=0;
			$data['status']="10021";
			$data['text']="抽奖次数已用完";
			
			return $data;
		}
		$db=M("lottery_award");
		$data=$db->where("lottery_id={$lotteryId}")->order("type asc")->select();
		$prizes=array();#封装中奖数组
		foreach ($data as $key => $value) {
			$i=$key+1;
			$prizes[$i][id]=$value[id];
			$prizes[$i][title]=$value[title];
			$prizes[$i][chance]=$value[chance];
			$prizes[$i][amount]=$value[amount];
		}
		$prize_num=rand(1,10000);
		$prize=1;
		$prize=$this->win_prize($prizes, $prize_num, $prize);
		#prize在中奖范围内
		if ($prize<=count($prizes)){
			$prize_lists['level']=$prize;//几等奖/1.2.3.4.5.6
			$prize_lists['prize']=$prizes[$prize][title];//奖品
			$prize_lists['prize_name']="恭喜你获得了".$prize."等奖";//一等奖.二等奖.文字
			$prize_lists['zhongjiang']=1;//是否中奖 中奖为1
			$db->where("id={$prizes[$prize][id]}")->setDec("amount");

			#插入中奖表
			$win[table]="lottery_win";
			$win[data]=array("lottery_id"=>$lotteryId,"user_id"=>$user_id,"win"=>$prize,"addtime"=>time());	  	
		 	$this->db->Add($win);
		}else {
			$prize_lists['zhongjiang']=2;
			$prize_lists['prize_name']="谢谢参加";
			$prize_lists['prize']="22";
		}
		M("lottery_apply")->where("lottery_id={$lotteryId} AND user_id={$user_id}")->setDec("lottery_count");
		$prize_lists[lottery_count]=$lottery_count-1;
		return $prize_lists;
		
	}

	#中奖
	public function win($lotteryId,$user_id){

		$lottery_count=M("lottery_data")->where("lottery_id={$lotteryId} AND user_id={$user_id}")->getField("lottery_count");
		if ($lottery_count <= 0){
			$data[lottery_count]=0;
			$data['status']="10021";
			$data['text']="抽奖次数已用完";
			
			return $data;
		}
		$db=M("lottery_award");
		$data=$db->where("lottery_id={$lotteryId}")->order("type asc")->select();
		$prizes=array();#封装中奖数组
		foreach ($data as $key => $value) {
			$i=$key+1;
			$prizes[$i][id]=$value[id];
			$prizes[$i][title]=$value[title];
			$prizes[$i][chance]=$value[chance];
			$prizes[$i][amount]=$value[amount];
		}

		// echo count($prizes);exit;

		$prize_num=rand(1,10000);
		$prize=1;
		$prize=$this->win_prize($prizes, $prize_num, $prize);
		#prize在中奖范围内
		if ($prize<=count($prizes)){
			$prize_lists['level']=$prize;//几等奖/1.2.3.4.5.6
			$prize_lists['prize']=$prizes[$prize][title];//奖品
			$prize_lists['prize_name']="恭喜你获得了".$prize."等奖";//一等奖.二等奖.文字
			$prize_lists['zhongjiang']=1;//是否中奖 中奖为1
			$db->where("id={$prizes[$prize][id]}")->setDec("amount");

			#插入中奖表
			$win[table]="lottery_win";
			$win[data]=array("lottery_id"=>$lotteryId,"user_id"=>$user_id,"win"=>$prize,"addtime"=>time());	  	
		 	$this->db->Add($win);
		}else {
			$prize_lists['zhongjiang']=2;
			$prize_lists['prize_name']="谢谢参加";
			$prize_lists['prize']="22";
		}
		M("lottery_data")->where("lottery_id={$lotteryId} AND user_id={$user_id}")->setDec("lottery_count");
		$prize_lists[lottery_count]=$lottery_count-1;
		return $prize_lists;
		
	}


	function win_prize($data,$prize_num,$prize){
		#当中奖等奖大于总奖数
		if ($prize>count($data)){
			// echo 312312;exit;
			return $prize;
		}

		#该奖总数小于0则抽下一等奖
		if ($data[$prize][amount]<=0){
			$prize++;
			$prize=$this->win_prize($data, $prize_num,$prize);
		}
		#prize_num该随机数在当等奖机率内则中该奖
		if ($prize_num<=$data[$prize][chance]){
			return $prize;
		}else{
			$prize++;
			$prize=$this->win_prize($data, $prize_num,$prize);
			return $prize;
		}
	}

	#活动中奖人信息
	public function win_person($lotteryId,$search=""){
		$array[table]="lottery_win";
		$array[where]="lottery_id={$lotteryId}";
		$array[join]="LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_win.user_id ";
		$array[field]="tl_contacts.name,tl_lottery_win.* ";
		if($search[num]){
			$array[num]=$search[num];
		}
		$data=$this->db->Page($array);
		return $data;
	}

	#区域统计人数
	public function lottery_count1($lotteryId){
		$db=M("lottery_apply");
		$where="lottery_id={$lotteryId}";
		$join=" LEFT JOIN tl_user ON tl_user.id=user_id ";
		$join.=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$group="tl_contacts.area";
		$field="area,count(contact_id) as allcontact,sum(lottery_count) as allcount ";
		$data=$db->where($where)->join($join)->field($field)->group($group)->select();
		return $data;
	}

	#区域统计
	public function lottery_count($lotteryId){
		$db=M("contacts_partment");
		$data=$db->where("parentid=14")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$sub=$db->where("parentid IN ({$subsql})")->field("id")->select(false);
			$field="count(id) as allcontact,sum(lottery_count) as allcount ";
			$count=M("lottery_apply")->where("partment_id IN ({$sub}) AND lottery_id={$lotteryId}")->field($field)->select();
			$data[$key]["allcontact"]=$count[0][allcontact];
			$data[$key]["allcount"]=$count[0][allcount];
		}
		return $data;
	}

	#区域下的代码区域统计
	public function code_count($lotteryId,$code){
		$db=M("contacts_partment");
		$data=$db->where("parentid={$code}")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$field="count(id) as allcontact,sum(lottery_count) as allcount ";
			$count=M("lottery_apply")->where("partment_id IN ({$subsql}) AND lottery_id={$lotteryId}")->field($field)->select();
			$data[$key]["allcontact"]=$count[0][allcontact];
			$data[$key]["allcount"]=$count[0][allcount];
		}

		return $data;
	}

	#详细统计
	public function detail_count($lotteryId,$code){
		$db=M("contacts_partment");
		$subsql=$db->where("parentid={$code}")->field("id")->select(false);
		$array[table]="lottery_apply";
		$array[where]="partment_id IN ({$subsql}) AND lottery_id={$lotteryId} ";
		$data=$this->db->Page($array);
		return $data;
	}



	#前台报表
	public function lottery_report($lotteryId){
		$db=M("lottery_apply");
		$where="lottery_id={$lotteryId}";
		$join=" LEFT JOIN tl_user ON tl_user.id=user_id ";
		$join.=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$group="tl_contacts.area";
		$field="area,count(contact_id) as allcontact,sum(lottery_count) as allcount ";
		$data=$db->where($where)->join($join)->field($field)->group($group)->select();

		foreach ($data as $key => &$value) {
			$where="lottery_id={$lotteryId} AND tl_contacts.area='{$value[area]}' ";
			$field="tl_contacts.name,tl_lottery_apply.lottery_count ";
			$value[persons]=$db->where($where)->join($join)->field($field)->select();
		}
		return $data;

	}

	#查看区域中奖人
	public function win_count1($lotteryId,$area){
		$array[table]="lottery_win";
		$array[where]="tl_lottery_win.lottery_id={$lotteryId} AND tl_contacts.area='{$area}' ";
		$array[join]=" LEFT JOIN tl_user ON tl_user.id=tl_lottery_win.user_id ";
		$array[join].=" LEFT JOIN tl_contacts ON tl_contacts.id=tl_user.contact_id ";
		$array[field]="tl_lottery_win.win,tl_lottery_win.addtime as wintime,tl_contacts.* ";
		$data=$this->db->Page($array);
		return $data;
	}



	#查看区域中奖人
	public function win_count($lotteryId,$area){
		$db=M("contacts_partment");
		$subsql=$db->where("parentid={$area}")->field("id")->select(false);

		$array[table]="lottery_win";
		$array[where]="tl_lottery_win.lottery_id={$lotteryId} AND tl_contacts.partment_id IN({$subsql}) ";
		$array[join]=" LEFT JOIN tl_contacts ON tl_contacts.wx_userid=tl_lottery_win.user_id ";
		$array[field]="tl_lottery_win.win,tl_lottery_win.addtime as wintime,tl_contacts.* ";
		$data=$this->db->Page($array);
		return $data;
	}

	#查看保单数据
	public function shuju1($lotteryId,$user_id){
		$db=M("lottery_baodan");
		$data=$db->where("lottery_id={$lotteryId} AND user_id='{$user_id}' ")->select();
		return $data;
	}

	#查看保单数据
	public function shuju($lotteryId,$user_id){
		$db=M("lottery_data");
		$baodan=$db->where("lottery_id={$lotteryId} AND user_id='{$user_id}' ")->getField("baodan");
		$baodan=explode(",",trim($baodan));
		$data[baodan]=$baodan;
		$remark=$db->where("lottery_id={$lotteryId} AND user_id='{$user_id}' ")->getField("remark");
		$data[remark]=$remark;
		return $data;
	}


	public function win_ziliao($post,$user_id){
		$db=M("lottery_win");
		$data[partment]=$post[partment];
		$data[phone]=$post[phone];
		$id=$db->where("lottery_id={$post[lottery_id]} AND user_id={$user_id} AND win={$post[win]}")->setField($data);
		// $db->where("lottery_id={$post[lottery_id]} AND user_id={$user_id} AND win={$post[win]}")->setField("partment",$post[partment]);
		return $id;
	}


	#导入名单
	public function to_apply($persons,$lotteryId){
		// dump($persons);exit;
		$db=M('contacts');
		$addtime=time();
		$lottery_apply=M("lottery_apply");
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"lottery_id"=>$lotteryId,"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"addtime"=>$addtime);
				$lottery=$lottery_apply->where("user_id='{$res[wx_userid]}' AND lottery_id={$lotteryId}")->find();
				if($lottery){
					$lottery_apply->where("user_id='{$res[wx_userid]}' AND lottery_id={$lotteryId}")->save($dataList);
				}else{
					$lottery_apply->add($dataList);
				}

				// echo $lottery_apply->getLastsql();exit;

			}
		}
	}

	#pc端检查是否可以参与抽奖
	public function check_lottery($lotteryId,$user_id){
		$db=M("lottery_data");
		$lottery_count=$db->where("lottery_id={$lotteryId} AND user_id={$user_id}")->getField("lottery_count");
		$prize_status=M("lottery_award")->where("lottery_id={$lotteryId} AND pc_start=1")->find();
		
		if(empty($lottery_count)){
			$res[code]="10000";
			$res[msg]="抽奖次数已用完";
		}
		if(!$prize_status){
			$res[code]="10001";
			$res[sql]=M("lottery_award")->getLastsql();
			$res[msg]="抽奖未开始或已结束";
		}

		if($lottery_count && $prize_status){

			$is_lottery=M("lottery_pclog")->where("lottery_id={$lotteryId} AND user_id={$user_id} AND pc_prize={$prize_status[type]} ")->find();
			
			if(!$is_lottery){
				$db->where("lottery_id={$lotteryId} AND user_id={$user_id}")->setDec("lottery_count");
				$dataList=array("lottery_id"=>$lotteryId,"user_id"=>$user_id,"pc_prize"=>$prize_status[type],"addtime"=>time());
				M("lottery_pclog")->add($dataList);

				$res[code]="20000";
				$res[lottery_count]=$lottery_count-1;
				$res[msg]="抽奖成功";
			}else{
				$res[code]="30000";
				$res[lottery_count]=$lottery_count;
				$res[msg]="您已成功抽取本轮奖项，请等待一下轮";
			}
			
		}

		return $res;

	}



}