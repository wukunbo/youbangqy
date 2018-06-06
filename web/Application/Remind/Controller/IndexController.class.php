<?php
namespace Remind\Controller;
use Think\Controller;
class IndexController extends Controller {

	public function __construct(){		   
		parent::__construct();
		$wx_config[appid]="wxf64ba63493455027";
		$wx_config[secret]="EwT0TBZiEzLI1WA-UWprachvlsIKYt4kfn-oUjFipu3sp6LpxzPQNVk2PI2AdS1f";
		$this->wxdata=$wx_config;
		$GLOBALS['pf']="";

	}
   	

   	#活动提前时间推送
	public function activity_remind(){
		// $GLOBALS['pf']
		$activityLogic= new \Activity\Logic\ActivityLogic(); 
        // $activityLogic->activity_remind();
        $activityLogic->activity_remind();
    }

    #凌晨3点自动更新通讯录
    public function contact_updata(){
    	$now=date("H:i:s");
    	// echo $now;exit;
    	if($now=="03:00:00"){
	    	$Function=new \Base\Model\FunctionServiceModel();
		 	$GLOBALS['pf']=$Function;
			$Get=new \Base\Model\GetServiceModel();
			$Get->config($this->wxdata);

			//获取部门
			$data=$Get->get_wx_partment();
			$contactLogic=new \Userweb\Logic\ContactsLogic();
			$res=$contactLogic->weixin_partment($data);

			#获取部门成员
			$db=M("contacts_partment");
			$partment=$db->field("id,name")->select();
			foreach ($partment as $key => $value) {
				$data=$Get->get_wx_contacts($value[id]);
				$res=$this->logic->weixin_contacts($data);
			}
		}
    }

    public function activity_remind1(){
		$activityLogic= new \Activity\Logic\ActivityLogic(); 
        $activityLogic->activity_remind1();
    }
}