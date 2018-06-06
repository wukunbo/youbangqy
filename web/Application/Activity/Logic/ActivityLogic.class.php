<?php
namespace Activity\Logic;
use Think\Model;
class ActivityLogic {
	public function  __construct(){
	
		$this->db=new \Activity\Model\PublicModel();
 
	}
	
 
	public function lists($user_id=-1,$search){
		$pre=C("DB_PREFIX");
		$where=" 1 ";
		$array['field']=" ".$pre."activity.*,";

 		if($user_id!=-1){ //-1全部
 			$partment_id=M("user_child")->where("id={$user_id}")->getField("partment_id");
 		// 	$user_ids=M("user_child")->where("partment_id={$partment_id}")->getField("id",true);
 		// 	if(is_array($user_ids)){
 		// 		$user_ids=implode(",",$user_ids);
 		// 	}
 		// 				// $where.=" AND ".$pre."activity.user_id='".$user_id."'";
			// $where.=" AND tl_activity.user_id IN({$user_ids}) ";
			$where.=" AND tl_activity.partment={$partment_id} ";
		}
		if($search[status] > ""){
			$where.=" AND ".$pre."activity.status=".$search[status]."";
		}
		#部门筛选
		if($search[partment] > ""){
			$where.=" AND ".$pre."activity.partment='".$search[partment]."'";
		}
		#活动类型筛选
		if($search[category] > ""){
			$where.=" AND ".$pre."activity.category=".$search[category]."";
		}

		#时间筛选
		if($search[time_start] > ""){
			$time_start=strtotime($search[time_start]);
			$where.=" AND ".$pre."activity.time_start > ".$time_start."";
		}
		if($search[time_apply] > ""){
			$time_apply=strtotime($search[time_apply]);
			$where.=" AND ".$pre."activity.time_apply < ".$time_apply."";
		}

		if($search[my_apply]){ //参加
			 
			$array['table']="activity";  
			$array['join']=" LEFT JOIN ".$pre."activity_apply ON ".$pre."activity_apply.activity_id=".$pre."activity.id";	
			$where="".$pre."activity_apply.user_id='".$user_id."'"; 
			$array['field']="".$pre."activity.*,".$pre."activity_apply.status as apply_status,".$pre."activity_apply.is_able as apply_is_able,".$pre."activity_apply.id as apply_id,";
			
		}
		// $array['join'].=" LEFT JOIN ".$pre."user ON ".$pre."activity.user_id=".$pre."user.id";	
		// $array['field'].=" ".$pre."user.username,".$pre."user.headimgurl,";
		
		$array['join'].=" LEFT JOIN ".$pre."activity_category ON ".$pre."activity_category.cate_id=".$pre."activity.category";	
		$array['field'].=" ".$pre."activity_category.cat_name,";

		$array['join'].=" LEFT JOIN ".$pre."partment ON ".$pre."partment.id=".$pre."activity.partment";	
		$array['field'].=" ".$pre."partment.title as partment_title,";

		$array['join'].=" LEFT JOIN ".$pre."contacts ON ".$pre."contacts.wx_userid=".$pre."activity.wx_userid";	
		$array['field'].=" ".$pre."contacts.name as admin_name";
		
		$array['table']="activity";    	
		$array['where']=$where;	
		$array['field']=$array['field'];
		if($search[order]){
			$array['order']=" ".$pre."activity.{$search[order]} desc ";
		}else{
			$array['order']=" ".$pre."activity.id desc ";
		}	
					
		$array['num']=$search[num];

		$data=$this->db->Page($array);
		// echo M("activity")->getLastsql();exit;
		return $data;    	
	}

	#活动类型列表
	public function category_lists($user_id=-1,$search){
		$array[table]="activity_category";
		$array[where]=" 1 ";
		$array[order]="addtime desc";
		$array[field]="cate_id,cat_name,addtime";

		$data=$this->db->Page($array);	
		return $data;    	
	}

	#报名统计
	public function apply_count($user_id=-1,$search){
		$pre=C("DB_PREFIX");
		$array['table']="activity";
		$array['where']=" 1 ";
		$array['field']="tl_activity.id,tl_activity.title,open_status,person_count,time_start,time_apply,start_time,end_time,tl_activity_category.cat_name,tl_partment.title as partment_title";
		$array['join'].=" LEFT JOIN ".$pre."activity_category ON ".$pre."activity_category.cate_id=".$pre."activity.category";
		$array['join'].=" LEFT JOIN ".$pre."partment ON ".$pre."partment.id=".$pre."activity.partment ";
		
		if($search[order]){
			$array['order']=" ".$pre."activity.{$search[order]} desc ";
		}else{
			$array['order']=" ".$pre."activity.id desc ";
		}

		if(!empty($search[partment])){

			$array[where].=" AND tl_activity.partment={$search[partment]} ";
		}

		#活动类型筛选
		if($search[category] > ""){
			$array[where].=" AND ".$pre."activity.category=".$search[category]."";
		}

		if($search[status] > ""){
			$array[where].=" AND ".$pre."activity.status=".$search[status]."";
		}

		#时间筛选
		if($search[time_start] > ""){
			$time_start=strtotime($search[time_start]);
			$where.=" AND ".$pre."activity.time_start > ".$time_start."";
		}
		if($search[time_apply] > ""){
			$time_apply=strtotime($search[time_apply]);
			$where.=" AND ".$pre."activity.time_apply < ".$time_apply."";
		}

		$data=$this->db->Page($array);
		$tmp=$data[content];
		$activity_apply=M("activity_apply");
		for($i=0;$i<count($tmp);$i++){

			#参与人数
			$where1="activity_id={$tmp[$i][id]} ";
			$tmp[$i]['apply_num']=$activity_apply->where($where1)->count();

			$where="activity_id={$tmp[$i][id]} AND is_able=1";
			$tmp[$i]['apply_count']=$activity_apply->where($where)->count();
			$tmp[$i]['apply_rate']=round($tmp[$i][apply_count]/$tmp[$i][apply_num],2)*100;

			#签到人数
			$where2="activity_id={$tmp[$i][id]} AND sign_status=1";
			$tmp[$i]['sign_num']=$activity_apply->where($where2)->count();
			$tmp[$i]['sign_rate']=round($tmp[$i][sign_num]/$tmp[$i][apply_num],2)*100;
			
		}

		$data[content]=$tmp;
		return $data;
	}

	#区域统计
	public function area_count1($activityId){
		$db=M("activity_apply");
		$where="activity_id={$activityId}";
		$join=" LEFT JOIN tl_contacts ON tl_contacts.id=contact_id ";
		$group="tl_contacts.area";
		$field="area,count(contact_id) as allcontact";
		$data[join]=$db->where($where)->join($join)->field($field)->group($group)->select();
		foreach ($data[join] as $key => $area) {
			$where="activity_id={$activityId} AND is_able=1 AND area='{$area[area]}' ";
			$data[apply][$key][area]=$area[area];
			$field="count(contact_id) as allcontact";
			$res=$db->where($where)->join($join)->field($field)->group($group)->select();
			$data[apply][$key][allcontact]=$res[0][allcontact];
		}
	
		
		return $data;
	}


	#全部统计
	public function all_count($activityId){
		$db=M("contacts_partment");
		$activity=M("activity_apply");
		$data=$db->where("parentid=1")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$idArr=child_partment($value[id]);
			$ids=implode(",",$idArr);
			$GLOBALS["id"]=array();
			$allcount=$activity->where("partment_id IN ({$ids}) AND activity_id={$activityId} ")->count();
			$applycount=$activity->where("partment_id IN ({$ids}) AND is_able=1 AND activity_id={$activityId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["applycount"]=$applycount;
		}
		return $data;
	}

	public function area_count($activityId,$partmentId){
		$db=M("contacts_partment");
		$activity=M("activity_apply");
		$data=$db->where("parentid={$partmentId}")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$idArr=child_partment($value[id]);
			$ids=implode(",",$idArr);
			$GLOBALS["id"]=array();
			$allcount=$activity->where("partment_id IN ({$ids}) AND activity_id={$activityId} ")->count();
			$applycount=$activity->where("partment_id IN ({$ids}) AND is_able=1 AND activity_id={$activityId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["applycount"]=$applycount;
		}
		return $data;
	}

	#外勤统计
	public function area_count2($activityId,$partmentId){
		$db=M("contacts_partment");
		$data=$db->where("parentid=14")->field("id,name")->select();
		foreach ($data as $key => $value) {
			$subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$sub=$db->where("parentid IN ({$subsql})")->field("id")->select(false);
			$allcount=M("activity_apply")->where("partment_id IN ({$sub}) AND activity_id={$activityId} ")->count();
			$applycount=M("activity_apply")->where("partment_id IN ({$sub}) AND is_able=1 AND activity_id={$activityId} ")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["applycount"]=$applycount;
		}
		return $data;
	}

	#组别统计
	public function work_group($activityId,$area){
		$db=M("activity_apply");
		$where="activity_id={$activityId} AND tl_contacts.area='{$area}' ";
		$join=" LEFT JOIN tl_contacts ON tl_contacts.id=contact_id ";
		$group="tl_contacts.work_group";
		$field="work_group,count(contact_id) as allcontact";
		$data=$db->where($where)->join($join)->field($field)->group($group)->select();
		return $data;
	}

	#组别报名统计
	public function work_group_apply($activityId,$area,$work_group){
		$db=M("activity_apply");
		$join=" LEFT JOIN tl_contacts ON tl_contacts.id=contact_id ";
		$group="tl_contacts.work_group";
		$field="count(contact_id) as allcontact";
		foreach ($work_group as $key => $value) {
			$data[$key][work_group]=$value[work_group];
			$where=" is_able=1 AND activity_id={$activityId} AND area='{$area}' AND tl_contacts.work_group='{$value[work_group]}' ";
			$res=$db->where($where)->join($join)->field($field)->group($group)->select();
			$data[$key][allcontact]=$res[0][allcontact];
			$field1="is_able,tl_contacts.name ";
			$where1=" activity_id={$activityId} AND area='{$area}' AND tl_contacts.work_group='{$value[work_group]}' ";
			$data[$key][persons]=$db->where($where1)->join($join)->field($field1)->select();
		}
		return $data;
	}


	#详细统计
	public function detail_count1($activityId,$area){
		$array[table]="activity_apply";
		$array[join]=" LEFT JOIN tl_contacts ON tl_contacts.id=contact_id ";
		$array[field]="tl_contacts.*,tl_activity_apply.is_able ";
		$array[where]="activity_id={$activityId} AND tl_contacts.area='{$area}' ";
		$data=$this->db->Page($array);
		return $data;
	}

	#详细统计
	public function detail_count($activityId,$code){
		$db=M("contacts_partment");
		$subsql=$db->where("parentid={$code}")->field("id")->select(false);
		$array[table]="activity_apply";
		$array[where]="partment_id IN ({$subsql}) AND activity_id={$activityId} ";
		$data=$this->db->Page($array);
		return $data;
	}

	#人员详细
	public function detail_person($activityId,$ids){
		$array[table]="activity_apply";
		$array[where]="partment_id IN ({$ids}) AND activity_id={$activityId} ";
		$data=$this->db->Page($array);
		return $data;
	}

	#子管理员详细
	public function child_detail_person($activityId,$ids,$partmentId){
		$array[table]="activity_apply";
		$array[where]="partment_id IN ({$ids}) AND partment_id IN ({$partmentId}) AND activity_id={$activityId} ";
		$data=$this->db->Page($array);
		return $data;
	}

	#外部人员统计
	public function open_report($activityId){
		$array[table]="activity_open";
		$array[where]="activity_id={$activityId} ";
		$data=$this->db->Page($array);
		$data[all]=M("activity_open")->where("activity_id={$activityId}")->count();
		$data[sign_all]=M("activity_open")->where("activity_id={$activityId} AND sign_status=1")->count();
		return $data;
	}



	#区域下的代码区域统计
	public function code_count($activityId,$code){
		$db=M("contacts_partment");
		$data=$db->where("parentid={$code}")->field("id,name")->select();
		foreach ($data as $key => $value) {
			// $subsql=$db->where("parentid={$value[id]}")->field("id")->select(false);
			$allcount=M("activity_apply")->where("partment_id IN ({$value[id]}) AND activity_id={$activityId} ")->count();
			$applycount=M("activity_apply")->where("partment_id IN ({$value[id]}) AND is_able=1 AND activity_id={$activityId}")->count();
			$data[$key]["allcount"]=$allcount;
			$data[$key]["applycount"]=$applycount;
		}

		return $data;
	}

	public function detail($id,$user_id=""){
 		$where=" 1 AND id='".$id."'";
		$detail=M("activity")->where($where)->find();
		//var_dump($detail);
		//获取地址
		$RegionLogic=new \Plus\Logic\RegionLogic();
		$search[province]=$detail[province];
		$search[city]=$detail[city];
		$search[area]=$detail[area];
		$search[district]=$detail[district];
		$search[estate]=$detail[estate];
		$detail[region_name]=$RegionLogic->get_name($search);
		$detail[is_user]=0;
		if($user_id==$detail[user_id]){
			$detail[is_user]=1;
		}
		$detail[time_apply_text]=date("Y-m-d H:i:s",$detail[time_apply]);
		$detail[time_start_text]=date("Y-m-d H:i:s",$detail[time_start]);
		$detail[status_text]="未审核";
		if($detail[status]==2){
			$detail[status_text]="审核未过";
		}
		if($detail[status]==1){
			$detail[status_text]="审核已过";
		}
		$detail[is_apply]=1;
		$detail[time_apply]=((int)$detail[time_apply]);

 
		if($detail[time_apply]<time()){
			$detail[is_apply]=0;
 
		}
		$detail[is_start]=1;
		$detail[time_start]=((int)$detail[time_start]);
		if($detail[time_start]<time()){
			$detail[is_start]=0;
		}
		$data[detail]=$detail;	
 
		return $data;    	
	}
	
 	public function apply($user_id,$post){
		//var_dump($post);
		$is=M("activity_apply")->where("user_id='".$user_id."' AND activity_id='".$post[activity_id]."'")->getField("id");
		$has_apply=M("activity_apply")->where("user_id='".$user_id."' AND activity_id='".$post[activity_id]."' AND status=1")->count();
		
		 
		$detail=M("activity")->where(" id='".$post[activity_id]."'")->field("price,person_count")->find();
		$is_apply=1;
		if($is){
			$res[status]=40002;
			$is_apply=0;
		}
		//var_dump($detail);
		//var_dump($has_apply);
		if($detail[person_count]<$has_apply){
			$res[status]=40003;
			$res[txt]="报名人数已满";
			$is_apply=0;
		}
		$is_apply=1;
		if($is_apply==1){
			$post[addtime]=time();
			$post[user_id]=$user_id;
			M("activity_apply")->add($post);
			$res[sql]=M("activity_apply")->getLastsql();
			//判断是否需要支付
			if($detail[price]>0){
				$OrderLogic=new \Shop\Logic\OrderLogic();	
				$tmp["is_real"]="2";
				$tmp["total_fee"]=$detail[price];
				$tmp[app]="activity";
				$tmp[user_id]=$user_id;
				$tmp[business_id]=$post[activity_id];
				$data=$OrderLogic->add_order($this->user_id,$tmp);	
				//var_dump($data);
				$res[log_id]=$data[log_id];		
			}
			 
			$res[status]=40001;
		}
		//var_dump($res);
		return $res;
	}
	public function apply_log($activity_id,$search){
		$pre=C("DB_PREFIX");
		 
		if(isset($search[status])){ 
			
			$where=" AND ".$pre."activity_apply.status='".$search[status]."'";
		}
		if(!empty($search[is_able])){
			$where=" AND ".$pre."activity_apply.is_able='".$search[is_able]."'";
		}
		$lists=M("activity_apply")
			->join("".$pre."contacts ON ".$pre."contacts.wx_userid=".$pre."activity_apply.user_id")
			->field("".$pre."contacts.name,".$pre."contacts.avatar_url,".$pre."activity_apply.*")
			->where(" ".$pre."activity_apply.activity_id='".$activity_id."' ".$where."")
			->select();
		$data[lists]=$lists;
		$data[sql]=M("activity_apply")->getLastsql();
		$data[total]=count($lists);
		return $data;
		
	}
	public function check_apply_status($activity_id,$apply_id,$to_status,$to_able){
		if($to_status==1){
			M("activity_apply")->where("activity_id=".$activity_id." AND id in (".$apply_id.") AND status=0 ")->setField("status",1);
		}
		if($to_status=="0"){
			M("activity_apply")->where("activity_id=".$activity_id." AND id in (".$apply_id.") AND status=1  AND is_able=0")->setField("status",0);
		}
		if($to_able==1){
			M("activity_apply")->where("activity_id=".$activity_id." AND id in (".$apply_id.") AND status=1  AND is_able=0")->setField("is_able",1);
		}
		if($to_able=="0"){
			M("activity_apply")->where("activity_id=".$activity_id." AND id in (".$apply_id.") AND status=1  AND is_able=1")->setField("is_able",0);
		}
		//echo 1;
		$data[sql]=M("activity_apply")->getLastsql();
		$data[status]=10001;
		return $data;
	
	}
	
	//新增
	public function add($user_id,$post,$wx_userid=""){
		 // dump($post);exit;
		 $db=M("activity");
		 $post[time_apply]=strtotime($post[time_apply]);
		 $post[time_start]=strtotime($post[time_start]);

		 $post[start_time]=strtotime($post[start_time]);
		 $post[end_time]=strtotime($post[end_time]);
		 // (strtotime("+1 week 3 days 7 hours 5 seconds"));

		 // $timearr=explode(":",$post[signend_time]);

		 // $timestr="+".$timearr[0]." hours ".$timearr[1]." min ".$timearr[2]." seconds";
		 // $post[signend_time]=strtotime($timestr,$post[start_time]);
		 
		 $post[signend_time]=strtotime($post[signend_time]);
		 $post[signstart_time]=strtotime($post[signstart_time]);

		 if(!$post[id]){
		 	$post[addtime]=time();
			$post[status]=0;
			$post[user_id]=$user_id;
			$post[wx_userid]=$wx_userid;
		 	$id=$db->add($post);
			// if($post[status]==1){
			// 	$url="http://www.homeland.net.cn/web/activity.php?c=activity&a=detail&id=".$id."";

			// 	$text="您收一条新的信息:\r\n";
			// 	$text.="【活动】".$post[title]."\r\n";
			// 	$text.="<a href='".$url."'>点击查看</a>";
			// 	$WeixinLogic = new \Weixin\Logic\WeixinLogic();
				 
			// 	$arr[url]=$url;
			// 	$arr[description]=$post[title];
			// 	$arr[picurl]="http://homeland.net.cn/web/".$post[image_thumb];
			// 	$arr[cate_id]=$post[category];
			// 	$res=$WeixinLogic->send_all_message($this->wx_id,$text,$arr=$arr);
			// 	//var_dump($res);
				
			// }
			//exit;
		 }else{
		 	$post[addtime]=time();
		 	$db->where("id=".$post[id]."")->save($post);
		 }
		 $res[sql]=$db->getLastsql();
		 $res[id]=$id;
		 $res[status]=10001;
		 return $res; 	
	}
	public function change_status($data){
		$res=M("activity")->where("id='".$data[id]."'")->setField("status",$data[to_status]);
		// if($data[to_status]==1){
 
		// 	$user_id=M("activity")->where("id='".$data[id]."'")->getField("user_id");
		// 	//var_dump($user_id);
		// 	$points=1000;
		// 	$data[content]="活动发布成功,获取".$points;
		// 	$data[business_id]=$data[id];
		// 	$data[app]="activity";
		// 	$data[wx_id]="1";
		// 	$points=$points;
		// 	$PointsLogic= new \Points\Logic\PointsLogic();
		// 	$PointsLogic->do_point_arr($points,$user_id,$data);
			 
		// }
		 return $res;
	}

	#显示活动
	public function show_activity($activity_id,$user_id,$field=null){
		// $array['field']="id,title,time_start,time_apply,province,city,area,lng,lat,address,image_thumb,intro";
		if(!empty($field)){
			$array['field'].=",".$field;
		}
		$array['where']="id={$activity_id}";
		$array['table']="activity";
		$detail=$this->db->Find($array);

		//获取地址
		$RegionLogic=new \Plus\Logic\RegionLogic();
		$search[province]=$detail[province];
		$search[city]=$detail[city];
		$search[area]=$detail[area];
		$region_name=$RegionLogic->get_name($search);

		$detail[region_name]=$region_name.$detail['address'];
		$where="cate_id={$detail[category]}";
		$category_name=M('activity_category')->field('cat_name')->where($where)->find();
		$detail['category_name']=$category_name['cat_name'];
		$data[detail]=$detail;

		// $array['field']="id,activity_id,user_id,truename,partment,content,is_able";
		// $array['where']="activity_id={$activity_id} AND user_id={$user_id}";
		// $array['table']="activity_apply";
		$array['field']="wx_userid,name";
		$array['where']="wx_userid={$user_id}";
		$array['table']="contacts";
		$user=$this->db->Find($array);
		$data[user]=$user;
		return $data;
	}

	#参与活动状态
	public function activity_status($activity_id,$user_id,$to_status,$remark=""){
		if($to_status==-1){
			$array[is_able]=-1;
			$array[remark]=$remark;
			M("activity_apply")->where("activity_id=".$activity_id." AND user_id = ".$user_id)->setField($array);
		}
		if($to_status==1){
			$array[is_able]=1;
			$array[remark]="";
			M("activity_apply")->where("activity_id=".$activity_id." AND user_id = ".$user_id)->setField($array);
		}
		$res[sql]=M("activity_apply")->getLastsql();
		$res[status]=10001;
		return $res; 	
	}

	#添加对外放外部名单
	public function add_activity_open($activityId,$openId,$remark="",$status=1,$name="",$user_code=""){
		$db=M("activity_open");
		$dataList=array("activity_id"=>$activityId,"openId"=>$openId,"name"=>$name,"user_code"=>$user_code,"remark"=>$remark,"status"=>$status,"addtime"=>time());
		$open=M("activity_open")->where("activity_id={$activityId} AND openId='{$openId}'")->find();
		if(!$open){
			$db->add($dataList);
		}else{
			$dataList=array("remark"=>$remark,"status"=>$status);
			$db->where("activity_id={$activityId} AND openId='{$openId}' ")->save($dataList);
		}
		
	}

	#对外开放内部名单
	public function add_activity_apply($activityId,$user_id,$remark,$status=0){
		$user=M("contacts")->where("wx_userid={$user_id}")->find();
		$dataList=array("activity_id"=>$activityId,"user_id"=>$user_id,"contact_id"=>$user[id],"truename"=>$user[name],"partment"=>$user[partment],"partment_id"=>$user[partment_id],"is_able"=>$status,"remark"=>$remark,"addtime"=>time(),"applytime"=>time());
		$apply=M("activity_apply")->where("activity_id={$activityId} AND user_id={$user_id}")->find();
		if(!$apply){
			M("activity_apply")->add($dataList);
		}else{
			$dataList=array("is_able"=>$status,"remark"=>$remark,"applytime"=>time());
			M("activity_apply")->where("activity_id={$activityId} AND user_id={$user_id}")->save($dataList);
		}
	}


	#选择活动名单
	public function select_contact($activityId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment,partment_id,code,work_group,wx_userid")->find();
			$dataList=array('activity_id'=>$activityId,'contact_id'=>$contact[id],"user_id"=>$contact[wx_userid],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			$array[table]="activity_apply";
			$array[data]=$dataList;
			$activity_apply=M("activity_apply")->where("activity_id={$activityId} AND user_id='{$contact[wx_userid]}' ")->find();
			if(!$activity_apply){
				$this->db->Add($array);
			}
		}
		$res[sql]=M("activity_apply")->getLastsql();
		$res[status]=10001;
		return $res; 	
	}

	/**
	*  @desc 根据两点间的经纬度计算距离
	*  @param float $lat 纬度值
	*  @param float $lng 经度值
	*/
	public function getDistance($lat1, $lng1, $lat2, $lng2)
	{
		$earthRadius = 6367000; 

		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;

		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;

		return round($calculatedDistance);
	}

	#导入名单
	public function to_apply($persons,$activityId){
		$db=M('contacts');
		$addtime=time();
		$activity_apply=M("activity_apply");
		foreach ($persons as $key => $person) {
			$where="wx_userid='{$person[1]}' ";
			$res=$db->where($where)->find();
			if($res){
				$dataList=array("user_id"=>$res[wx_userid],"activity_id"=>$activityId,"contact_id"=>$res[id],"truename"=>$res[name],"partment_id"=>$res[partment_id],"partment"=>$res[partment],"content"=>$res[code],"hint"=>$person[2],"addtime"=>$addtime);
				$activity=$activity_apply->where("user_id='{$res[wx_userid]}' AND activity_id={$activityId}")->find();
				if($activity){
					$activity_apply->where("user_id='{$res[wx_userid]}' AND activity_id={$activityId}")->save($dataList);
				}else{
					$activity_apply->add($dataList);
				}

			}else{
				$dataList=array("user_id"=>$person[1],"activity_id"=>$activityId,"truename"=>$person[0],"content"=>$person[1],"hint"=>$person[2],"addtime"=>$addtime);
				$activity_apply->add($dataList);
			}
		}
	}

	#活动开始前2小时推送
	public function activity_remind(){
		$current_time=time();
		$activity_apply=M('activity_apply');
		$join="INNER JOIN tl_activity ON tl_activity.id=tl_activity_apply.activity_id ";
		$join.="INNER JOIN tl_partment ON tl_partment.id=tl_activity.partment ";
		$field="tl_activity_apply.user_id,tl_activity_apply.is_able,tl_activity.id,tl_activity.start_publish,tl_activity.end_publish,tl_activity.sign_publish,tl_activity.time_apply,tl_activity.title,tl_activity.start_time,tl_activity.signstart_time,tl_partment.app_id,tl_activity.apply_remind,tl_activity.sign_remind,start_remind ";
		// $data=$activity_apply->join($join)->where("tl_activity_apply.is_able=1")->field($field)->select();
		$data=$activity_apply->join($join)->field($field)->select();
		// dump($data);exit;
		// 
		$weixinMsgLogic = new \Weixin\Logic\WeixinMsgLogic();
		foreach ($data as $key1 => $activity){

			$time=$activity['start_time'];
			// $current_time=time();
			if($activity[start_remind] == 'on'){

				if($time-$current_time == $activity[start_publish]*60){

					$start_hour=$activity[start_publish]/60;
					$start_publish= $activity[start_publish] > 60 ? $start_hour."小时" : ($activity[start_publish]."分钟"); 
					$weixinMsgLogic->set_agentid($activity[app_id]);
            		$url="http://".$_SERVER['HTTP_HOST']."/youbangqy/web/activity.php?id=".$activity[id];
            		$msg="您好:\n<a href='".$url."'>【".$activity[title]."】</a>将在".$start_publish."以后开始，请注意时间安排，预祝您有一个丰盛的收获";
            		$weixinMsgLogic->send_text(array($activity[user_id]),$msg);
				}
            	
			}

			if($activity[sign_remind] == 'on'){

				if($activity[signstart_time]-$current_time == $activity[sign_publish]*60){

					$sign_hour=$activity[sign_publish]/60;
					$sign_publish= $activity[sign_publish] > 60 ? $sign_hour."小时" : $activity[sign_publish]."分钟"; 
					$weixinMsgLogic->set_agentid($activity[app_id]);
            		$url="http://".$_SERVER['HTTP_HOST']."/youbangqy/web/activity.php?id=".$activity[id];
            		$msg="您好:\n<a href='".$url."'>【".$activity[title]."】</a>签到将在".$sign_publish."以后开始，敬请留意。";
            		$weixinMsgLogic->send_text(array($activity[user_id]),$msg);
				}
			}

			if($activity[apply_remind] == 'on' && $activity[is_able] == 0){

				if($activity['time_apply']-$current_time == $activity[end_publish]*60){

					$end_hour=$activity[end_publish]/60;
					$end_publish= $activity[end_publish] > 60 ? $end_hour."小时" : $activity[end_publish]."分钟"; 

					$weixinMsgLogic->set_agentid($activity[app_id]);
					$url="http://".$_SERVER['HTTP_HOST']."/youbangqy/web/activity.php?id=".$activity[id];
					$msg="您好:\n<a href='".$url."'>【".$activity[title]."】</a>报名将在".$end_publish."结束，请勿错过报名时间哦。";
	            	// $msg.="<a href='".$url."'>点击查看</a>";
	            	$weixinMsgLogic->send_text(array($activity[user_id]),$msg);
            	}
            	
			}

			// sleep(1);
			// dump($activity);exit;
			
			// $weixinMsgLogic->set_agentid($activity[app_id]);
   //      	$msg="【".$activity[title]."】活动，报名时间快截止咯，快点来报名参加吧！";
   //      	$weixinMsgLogic->send_text(array($activity[user_id]),$msg);

			
		}  
	}

	#活动开始前2小时推送
	public function activity_remind1(){
		$current_time=1467706098;
		$activity_apply=M('activity_apply');
		$join="INNER JOIN tl_activity ON tl_activity.id=tl_activity_apply.activity_id ";
		$join.="INNER JOIN tl_partment ON tl_partment.id=tl_activity.partment ";
		$field="tl_activity_apply.user_id,tl_activity_apply.is_able,tl_activity.id,tl_activity.start_publish,tl_activity.sign_publish,tl_activity.time_apply,tl_activity.title,tl_activity.start_time,tl_activity.signstart_time,tl_partment.app_id,tl_activity.apply_remind,tl_activity.sign_remind,start_remind ";
		// $data=$activity_apply->join($join)->where("tl_activity_apply.is_able=1")->field($field)->select();
		$data=$activity_apply->join($join)->field($field)->select();
		// dump($data);
		// 
		$weixinMsgLogic = new \Weixin\Logic\WeixinMsgLogic();
		
		foreach ($data as $key => $activity){

			// echo $activity[signstart_time]+30*60;exit;
			
			$time=$activity['start_time'];
			// $current_time=time();
			// echo $time-$current_time;
			// dump($activity);exit;
			if($activity[sign_remind] == 'on'){
				// echo $activity[signstart_time]-$activity[sign_publish]*60;
				// dump($activity);exit;
				if($activity[signstart_time]-$current_time == $activity[sign_publish]*60){
					// dump($activity);exit;
					$sign_hour=$activity[sign_publish]/60;
					$sign_publish= $activity[sign_publish] > 60 ? $sign_hour."小时" : $activity[sign_publish]."分钟"; 
					$weixinMsgLogic->set_agentid($activity[app_id]);
            		$url="http://".$_SERVER['HTTP_HOST']."/youbangqy/web/activity.php?id=".$activity[id];
            		$msg="您好，<a href='".$url."'>【".$activity[title]."】</a>将在".$sign_publish."以后开始，敬请留意。";
            		$res=$weixinMsgLogic->send_text(array($activity[user_id]),$msg);
            		dump($res);exit;
				}
			}

			

			
		}  
	}


	 
}