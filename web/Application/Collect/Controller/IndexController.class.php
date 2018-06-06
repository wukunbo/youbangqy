<?php
namespace Collect\Controller;
use Think\Controller;
class IndexController extends BaseController {

	 public function __construct(){
		parent::__construct();
	 	$this->logic=new \Collect\Logic\CollectLogic();
	 	$this->user_id=$_SESSION[wx_userid];	 
	}

    public function index(){
    	$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
        $data=$this->logic->detail($request);

        // dump($data);exit;
        $today=time();
        if($data[detail][time_start]>$today){
            $this->showmsg("活动还未开始",-1);
            exit;
        }

        if($data[detail][time_end]<$today){
            $this->showmsg("活动已结束",-1);
            exit;
        }

        // dump($data);exit;

        if($data[detail][apply_count]){
            $has_apply=M("collect_person")->where("collect_id={$_REQUEST[id]}")->count();
            // echo $has_apply;exit;
            if($has_apply>=$data[detail][apply_count]){
                $this->showmsg("参与人数已满",-1);exit;
            }
        }


		$this->data=$data;
		$this->display();
    }

    public function add(){
    	$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}

        $this->protocol=M("protocol")->where("link_id={$request[id]} AND type=2")->getField("title");
        $data=$this->logic->detail($request);

        // dump($data);exit;
        $today=time();
        if($data[detail][time_start]>$today){
            $this->showmsg("活动还未开始",-1);
            exit;
        }

        if($data[detail][time_end]<$today){
            $this->showmsg("活动已结束",-1);
            exit;
        }

        

        // dump($data);exit;
        if($data[detail][apply_count]){
            $has_apply=M("collect_person")->where("collect_id={$_REQUEST[id]}")->count();
            // echo $has_apply;exit;
            if($has_apply>=$data[detail][apply_count]){
                $this->showmsg("参与人数已满",-1);exit;
            }
        }

		$this->data=$data;
        //收集扩展字段
        $this->collect_extend=M("collect_extend_field")->where("collect_id={$_REQUEST[id]}")->order("id ASC")->select();
        
		$this->display();
    }

    //收集多人信息ajax
    public function add_more(){
        $this->ii=$_REQUEST[i];
        $this->collect_extend=M("collect_extend_field")->where("collect_id={$_REQUEST[id]}")->order("id ASC")->select();
        $res=$this->fetch('add_tpl');
        $this->ajaxReturn($res);
    }

    #添加收集信息
    public function add_action(){
    	$posts=$_REQUEST['post'];
        $collectId=$_REQUEST[collect_id];

        // dump($posts);exit;
        //限制人数参与
        $apply_count=M("collect")->where("id={$collectId}")->getField("apply_count");
        if($apply_count){
            $has_apply=M("collect_person")->where("collect_id={$collectId}")->count();
            if($has_apply>=$apply_count){
                $this->showmsg("参与人数已满",-1);
            }
        }

        
        

        

        #检测信息填写
        foreach ($posts as $key => $post) {
            foreach ($post as $key1 => $value) {
               if(empty($value[value])){
                    $this->showmsg("请填写完整信息",1);
                }
            }
            
        }

        #插入数据
        $this->logic->add_person_extend($posts,$this->user_id,$collectId);
    
        // $ids="";
        // foreach ($posts as $key => $post) {
        //     $post[collect_id]=$collectId;
        //     $id=$this->logic->add_person($post,$this->user_id);
        //     $ids.=$id.",";
        // }
        M("collect_apply")->where("user_id={$this->user_id} AND collect_id={$collectId}")->setField("is_able",1);
        // $ids=substr($ids,0,strlen($ids)-1); 
		$this->showmsg("收集成功","collect.php?c=index&a=show_add&id=".$collectId);
    }

    public function show_add(){
    	$id=$_REQUEST[id];
    	$this->data=$this->logic->show_person($id,$this->user_id);
    	$this->display();
    }


    public function protocol(){
        $data=M("protocol")->where("type=2 AND link_id={$_REQUEST[id]}")->find();
        $this->data=$data;
        $this->display();
    }


}