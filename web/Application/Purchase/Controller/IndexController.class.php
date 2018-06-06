<?php
namespace Purchase\Controller;
use Think\Controller;
class IndexController extends BaseController {

	public function __construct(){
		parent::__construct();
	 	$this->logic=new \Purchase\Logic\PurchaseLogic();
	 	$this->user_id=$_SESSION[wx_userid];
        // echo $this->user_id;exit;
	}

    public function index(){
        $request=$this->request();
        if(!$request[id]){
            $this->showmsg("非法输入",-1);
        }

    	$this->data=$this->logic->purchase_lists($request[id],$this->user_id);

        if(empty($this->data[purchase][status])){
            $this->showmsg("活动未发布或下架",-1);
        }
        $now=time();

        if($this->data[purchase][time_start] > $now){

            $this->showmsg("该抽奖还未开始",-1);
        }
        if($this->data[purchase][time_end] < $now){

            $this->showmsg("该活动已结束",-1);
        }

        if(!$this->data[user]){
            $this->showmsg("你无权参加该活动",-1);
        }
    	$this->display();
    }

    public function detail(){
    	$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->data=$this->logic->detail($request,$this->user_id);

		if(!$this->data[user]){
			$this->showmsg("你无权参加该活动",-1);
		}
		$this->display();
    }

    public function sumbit_purchase(){
        $res=$_REQUEST[goods];
        $post=array();
        foreach ($res as $key => $value) {
           $post[]=$key."-".$value;
        }
    	$this->data=$this->logic->purchase_sure($post,$this->user_id);
        echo "<script>alert('成功申购物品')</script>";
    	$this->display();
    }

    public function sure_purchase(){
        $request=$this->request();
        $post=array();
        foreach ($request[num] as $key => $value) {
            if(!empty($value)){
                $post[]=$key."-".$value;
            }
        }
       
        if(empty($post)){
            $this->showmsg("请选择申购数量",1);
        }

        $this->protocol=M("protocol")->where("id=1")->getField("title");
        $this->post=json_encode($post);   
        $this->data=$this->logic->goods_sure($post,$this->user_id);
        $this->display();
    }


    public function protocol(){
        $data=M("protocol")->where("id=1")->find();
        $this->data=$data;
        $this->display();
    }
}