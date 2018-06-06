<?php
namespace Activity\Controller;
use Think\Controller;

class ActivityController extends BaseController{
	
	public function __construct(){		   
		parent::__construct();
		$this->Logic= new \Activity\Logic\ActivityLogic(); 
		$this->user_id=$_SESSION[wx_userid];
		$this->user_openid=$_SESSION["openId"];
	}
    
    
    public function index(){
    	$id = $_REQUEST['id'];
    	$this->data=$this->Logic->show_activity($id,$this->user_id);


    	
		#对外开发
    	if($this->user_openid){
    		$open=M("activity_open")->where("openId={$this->user_openid} AND activity_id={$id}")->find();
    		if($open){
    			if($open[status] == 1)
    				$this->redirect("activity/success",array('id' =>$id,'success'=>1));
    			if($open[status] == -1)
    				$this->redirect("activity/jujie",array('id' =>$id,'jujie'=>1));
    		}else{
    			$this->display("open_index");
    		}
    	}

    	#内部
    	if($this->user_id && !$this->user_openid){
    		$apply=M("activity_apply")->where("user_id={$this->user_id} AND activity_id={$id}")->find();
    		if($apply){
    			if($apply[is_able] == 1)
    				$this->redirect("activity/success",array('id' =>$id,'success'=>1));
    			if($apply[is_able] == -1)
    				$this->redirect("activity/jujie",array('id' =>$id,'jujie'=>1));
    			if($apply[is_able] == 0){
    				$this->display();
    			}
    		}else{
    			$this->display();
    		}
    	}
		
    }

    #对外开放页面
    public function open_index(){
        // $id = $_REQUEST['id'];
        // $this->data=$this->Logic->show_activity($id,$this->user_id);

        $this->display();
    }

    public function baoming(){
    	$id = $_REQUEST['id'];

    	$this->data=$this->Logic->show_activity($id,$this->user_id);
    	$this->display();
    }

    public function chexiao(){
    	$id = $_REQUEST['id'];

    	$this->data=$this->Logic->show_activity($id,$this->user_id);

        // if($this->data[time_apply] < time()){
        //     $this->show("报名已结束",-1);
        // }
        
    	$this->display();
    }

    public function jujie(){
    	$id = $_REQUEST['id'];

    	$this->data=$this->Logic->show_activity($id,$this->user_id);

    	#对外开放  		
    	if($this->user_openid){
    		$open=M("activity_open")->where("openId='{$this->user_openid}' AND activity_id={$id}")->find();
    		if($open){
    			if($open[status] == -1)
    				$this->display();
    			if($open[status] == 1)
    				$this->redirect("activity/success",array('id' =>$id));
    		}else{
    			$this->redirect("activity/index",array('id' =>$id));
    		}
    	}

    	#内部员工
    	if($this->user_id){
    		$apply=M("activity_apply")->where("user_id={$this->user_id} AND activity_id={$id}")->find();
    		if($apply){
    			if($apply[is_able] == -1)
    				$this->display();
    			if($apply[is_able] == 1)
    				$this->redirect("activity/success",array('id' =>$id));
    			if($apply[is_able] == 0)
    				$this->redirect("activity/index",array('id' =>$id));
    		}else{
    			$this->redirect("activity/index",array('id' =>$id));
    		}
    	}
    	


    	
    }

     public function success(){
    	$id = $_REQUEST['id'];

    	$this->data=$this->Logic->show_activity($id,$this->user_id);

        // if($this->data[time_apply] < time()){
        //     $this->showmsg("报名已结束",-1);
        // }

		#对外开放  		
    	if($this->user_openid){
    		$open=M("activity_open")->where("openId='{$this->user_openid}' AND activity_id={$id}")->find();
    		if($open){
    			if($open[status] == 1)
    				$this->display();
    			if($open[status] == -1)
    				$this->redirect("activity/jujie",array('id' =>$id));
    		}else{
    			$this->redirect("activity/index",array('id' =>$id));
    		}
    	}

    	#内部员工
    	if($this->user_id){
    		$apply=M("activity_apply")->where("user_id={$this->user_id} AND activity_id={$id}")->find();
    		if($apply){
    			if($apply[is_able] == 1)
    				$this->display();
    			if($apply[is_able] == -1)
    				$this->redirect("activity/jujie",array('id' =>$id));
    			if($apply[is_able] == 0)
    				$this->redirect("activity/index",array('id' =>$id));
    		}else{
    			$this->redirect("activity/index",array('id' =>$id));
    		}
    	}	
    }

    #报名
    public function ajax_activity(){
    	$id=$_REQUEST[id];
    	$remark=$_REQUEST[beizhu];
    	$status=$_REQUEST[status];

        if($this->open_status==1){
            if(!$this->wxuser_info || empty($this->wx_user_id)){
                $this->ajaxReturn("noapply");
                exit;
            }
        }

        $time_apply=M("activity")->where("id={$id}")->getField("time_apply");
        $now=time();
        if($time_apply < $now){
            $this->ajaxReturn("no");
            exit;
        }

        $person_count=M("activity")->where("id={$id}")->getField("person_count");
        $all_apply=M("activity_apply")->where("activity_id={$id} AND is_able=1")->count();
        if($person_count<$all_apply){
            $this->ajaxReturn("noman");
            exit;
        }

    	#对外开放  		
    	if($this->user_openid){
            $name=$_REQUEST[name];
            $user_code=$_REQUEST[user_code];
    		$this->Logic->add_activity_open($id,$this->user_openid,$remark,$status,$name,$user_code);
    	}

    	#内部员工
    	if($this->user_id){	
    		$this->Logic->add_activity_apply($id,$this->user_id,$remark,$status);
    	}

    	$this->ajaxReturn("ok");
    }

    public function atv_detail(){
    	$id = $_REQUEST['id'];

    	// $field="show_content,content,img_thumb,intro";
    	$this->data=$this->Logic->show_activity($id,$this->user_id);
    	// if(empty($this->data['user'])){
    	// 	$this->showmsg("该活动无权参加",1);
    	// }

		$this->display();
    }


    #上部公共部门
    public function activity_public(){
    	$this->display();
    }


	public function detail(){
		//
		$id=$_REQUEST["id"];
		//var_dump($this->user_id);
		$data=$this->Logic->detail($id=$id,$this->user_id); 
		
		$data[apply_log]=$this->Logic->apply_log($id=$id,$search);
		//var_dump($data);
		$this->data=$data;
		$this->display_tpl();	
 
	 
	}

	public function check_apply(){
		$id=$_REQUEST["id"];
		//var_dump($this->user_id);
		$data=$this->Logic->detail($id=$id,$this->user_id); 
		
 
		$data[apply_log]=$this->Logic->apply_log($id=$id,$search);
		 
		
		$search[status]=0;
		$data[apply_log_0]=$this->Logic->apply_log($id=$id,$search);
		//var_dump($data[apply_log_0][sql]);
		
		$search[status]=1;
		$data[apply_log_1]=$this->Logic->apply_log($id=$id,$search);
		//var_dump($data[apply_log_1][sql]);
		//var_dump($data);
		$search=array();
		$search[is_able]=1;
		$data[apply_log_2]=$this->Logic->apply_log($id=$id,$search);
		
		$search=array();
		$search[is_able]=3;
		$data[apply_log_3]=$this->Logic->apply_log($id=$id,$search);
		//var_dump($data[apply_log_2][sql]);
		//var_dump($data);
		$this->data=$data;
		$this->display_tpl();
	}

	public function check_apply_action(){
		 
		$more=$_REQUEST[more];
		
		if($more==1){
			$post=$_REQUEST[post];
			//var_dump($post);
			$activity_id=$post[activity_id];
			$apply_id=implode(",",$post[apply_id]);
		 
			 
		}else{
			$activity_id=$_REQUEST[activity_id];
			$apply_id=$_REQUEST[apply_id];
		}
		//var_dump($apply_id);
		$to_status=$_REQUEST[to_status];
		$to_able=$_REQUEST[to_able];
		$data=$this->Logic->check_apply_status($activity_id,$apply_id,$to_status,$to_able);//10001
		
		echo json_encode($data);
		//var_dump($post);
	
	}

	public function apply(){
		//
	 	//获取用户信息
		$activity_id=$_REQUEST[activity_id];
		$UserLogic= new \User\Logic\UserLogic(); 
		$data=$UserLogic->userinfo($this->user_id);
		
		$is=M("activity_apply")->where("user_id='".$this->user_id."' AND activity_id='".$activity_id."'")->getField("id");
		 
		
		//var_dump($data);
		$this->activity_id=$activity_id;
		$this->data=$data;
		$this->is=$is;
		$this->display_tpl("apply_tpl");	
 
	 
	}

	public function apply_action(){
		//
	 	//获取用户信息
		$post=$_REQUEST[post];
		$data=$this->Logic->apply($this->user_id,$post); // 40002 40001
 	 
	 	echo json_encode($data);
 
	 
	}

	public function add(){
		$id=$_REQUEST["id"];
		$data=$this->Logic->detail($id=$id,$this->user_id); 
		$this->data=$data;
		$this->display_tpl();	
	 
	}

	public function add_action(){
		$request=$this->request();
		$post=$request[post];
		//var_dump($post);
		if(!$post[title] || !$post[address]  || !$post[province] || !$post[city] || !$post[area]   || !$post[truename] || !$post[phone] || !$post[content]  ){
			$this->showmsg("请填写完整信息",1);
		}
		$post[status]=1;
		 
		$res=$this->Logic->add($this->user_id,$post);
		
 		//dump($res);exit;
		//$this->upadte_session($res[shop_id]);
  
	   $this->showmsg("发布成功","activity.php?c=user&a=activity_lists");
    }

    #查看报表
    public function report1(){
    	$this->activityId=$_REQUEST[id];
		$data=$this->Logic->area_count($this->activityId);
		foreach ($data[join] as $key => &$area) {
			$area[work_group]=$this->Logic->work_group($this->activityId,$area[area]);
			$data[apply][$key][work_group]=$this->Logic->work_group_apply($this->activityId,$area[area],$area[work_group]);
		}
		$this->data=$data;
    	$this->display();
    }

    public function report(){
    	$this->activityId=$_REQUEST[id];
		$data=$this->Logic->area_count($this->activityId);
		foreach ($data as $key => &$area) {
			$area[code]=$this->Logic->code_count($this->activityId,$area[id]);
			foreach ($area[code] as $key1 => &$code) {
				$code[persons]=$this->Logic->detail_count($this->activityId,$code[id])[content];
			}
		}
		$this->data=$data;
    	$this->display();
    }

    public function report3(){
        if(!$this->show_report){
            $this->showmsg("无权打开该页面",-1);
        }
        $this->activityId=$_REQUEST[id];
        $PublicLogic=new \Userweb\Logic\PublicLogic();

        if($this->user_level == 1){
            $res=$PublicLogic->report(1,$this->activityId);
        }else{
            $PartmentLogic=new \Userweb\Logic\PartmentLogic();
            $ids=$PartmentLogic->get_partment_ids($this->user_partment_id);
            // echo $ids;exit;
            $res=$PublicLogic->report_child(1,$ids,$this->activityId);
            // dump($res);exit;
        }

        if($this->open_status==2){
            $all=M("activity_open")->where("activity_id={$_REQUEST[id]}")->count();
            $sign=M("activity_open")->where("activity_id={$_REQUEST[id]} AND sign_status=1")->count();
            $open_data[all]=$all;
            $open_data[sign]=$sign;
            $this->open_data=$open_data;
        }
        
        $this->data=$res;
        $this->display();
    }

    #活动签到
    public function sign_show(){
        $activity=M("activity")->where("id={$_REQUEST[id]}")->find();
        $now=time();

        if($now > $activity[signend_time]){
            $this->showmsg("活动签到时间已过",-1);
        }

        if($now < $activity[signstart_time]){
            $this->showmsg("活动签到时间还未开始",-1);
        }

        if($now > $activity[end_time]){
            $this->showmsg("活动已结束",-1);
        }

        if($this->user_id){
            $apply=M("activity_apply")->where("activity_id={$_REQUEST[id]} AND user_id={$this->user_id}")->find();
            if($apply[is_able]!=1 && $activity[open_sign]==1){
                $this->showmsg("该活动您未报名",-1);
            }
            #允许直接签到，添加人员
            if($activity[open_sign]==1 && !$apply){
                $contact=M("contacts")->where("wx_userid='{$this->user_id}'")->find();
                $dataList=array("activity_id"=>$_REQUEST[id],"user_id"=>$this->user_id,"truename"=>$contact[name],"partment"=>$contact[partment],"partment_id"=>$contact[partment_id]);
                M("activity_apply")->add($dataList);
            }

        }else{
            
            $activity_open=M("activity_open")->where("activity_id={$_REQUEST[id]} AND openId='{$this->user_openid}' ")->find();


            if($activity[open_sign]==1 && !$activity_open){
                $dataList=array("activity_id"=>$_REQUEST[id],"openId"=>$this->user_openid,"addtime"=>time());
                M("activity_open")->add($dataList);
            }

            if(!$activity_open && $activity[open_sign]!=1){
                $this->showmsg("该活动您未报名",-1);
            }
        }

        

        $this->activity=$activity;
        $this->apply=$apply;

        $this->display();
    }

    public function sign(){

        $lat1=$_REQUEST[latitude];
        $lng1=$_REQUEST[longitude];

        $id=$_REQUEST[id];
        $activity=M("activity")->where("id={$id}")->find();
        $lat2=$activity[lat];
        $lng2=$activity[lng];

        $distance=$this->Logic->getDistance($lat1, $lng1, $lat2, $lng2);
        $res[hint]="";

        if($distance < 1000){
            $res[status]=10001;
            $res[msg]="签到成功";
            $res[time]=date("Y-m-d H:i:s");
            $res[hint]=M("activity_apply")->where("activity_id={$id} AND user_id={$this->user_id}")->getField("hint");

            if($this->user_id){
                M("activity_apply")->where("activity_id={$id} AND user_id={$this->user_id}")->setField("sign_status",1);
                M("activity_apply")->where("activity_id={$id} AND user_id={$this->user_id}")->setField("sign_time",time());

                $agentid=M("partment")->where("id={$activity[partment]}")->getField("app_id");

                $weixinMsgLogic = new \Weixin\Logic\WeixinMsgLogic($agentid);
                $msg="您已".$res[msg]."欢迎参与本次活动！！！".$res[hint];
                $weixinMsgLogic->send_text(array($this->user_id),$msg);
            }else{
                M("activity_open")->where("activity_id={$_REQUEST[id]} AND openId='{$this->user_openid}' ")->setField("sign_status",1);
                M("activity_open")->where("activity_id={$_REQUEST[id]} AND openId='{$this->user_openid}' ")->setField("sign_time",time());
            }

        }else{
            $res[status]=10000;
            $res[msg]="签到失败";
            $res[time]=date("Y-m-d H:i:s");
            
            M("activity_apply")->where("activity_id={$id} AND user_id={$this->user_id}")->setField("sign_status",-1);
            M("activity_open")->where("activity_id={$_REQUEST[id]} AND openId='{$this->user_openid}' ")->setField("sign_status",-1);


        }

        $this->ajaxReturn($res);   
    }

    public function sign_out(){
        $id=$_REQUEST[id];
        M("activity_apply")->where("activity_id={$id} AND user_id={$this->user_id}")->setField("out_status",-1);
        echo "已签退";
    }

    #查看报表人员明细
    public function apply_person(){
        if(!$this->show_report){
            $this->showmsg("无权打开该页面",-1);
        }
        
        $id=$_REQUEST["id"];
        $partment_id=$_REQUEST[partment];
        $PublicLogic=new \Userweb\Logic\PublicLogic();
        $ids=$PublicLogic->get_all_chile_ids($partment_id);
        $ids=implode(",",$ids);
        $GLOBALS["partmentId"]=array();

        if($this->user_level == 1){
            $this->data=$this->Logic->detail_person($id,$ids);
        }else{
            $PartmentLogic=new \Userweb\Logic\PartmentLogic();
            $partmentId=$PartmentLogic->get_child_partment_ids($this->user_partment_id);
            $this->data=$this->Logic->child_detail_person($id,$ids,$partmentId);
        }

        $this->display();
    }

    public function open_person(){
        $id=$_REQUEST["id"];
        $this->data=M("activity_open")->where("activity_id={$id}")->select();
        $this->display();
    }

    #活动开始前2小时推送
    public function activity_remind(){
        echo 12313;exit;
        $this->Logic->activity_remind();
    }

   
	
}