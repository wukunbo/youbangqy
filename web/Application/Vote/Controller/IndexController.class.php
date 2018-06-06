<?php
namespace Vote\Controller;
use Think\Controller;
class IndexController extends BaseController {

	public function __construct(){
		parent::__construct();
		$this->user_id=$_SESSION[wx_userid];
		$this->user_openid=$_SESSION["openId"];
		$this->logic=new \Vote\Logic\VoteLogic;
	}

	public function show(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		$this->data=$this->logic->detail($request);

		$now=time();

    	if($this->data[detail][time_start] > $now){
    		$this->showmsg("该投票活动还未开始",-1);
    	}
    	if($this->data[detail][time_end] < $now){
			$this->showmsg("该投票活动已结束",-1);
    	}

    	if(empty($this->data[detail][status])){
			$this->showmsg("未发布",-1);
    	}

		$this->display();
	}

    public function prize(){
        $request=$this->request();
        $this->data=$this->logic->detail($request);
        // dump($this->data);exit;
        $this->display();
    }

    public function index(){
    	$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		//浏览增加+1
		M("vote")->where("id=".$request[id]."")->setInc(count_view);

		//获取选项
		$search[vote_id]=$request[id];
		$search[status]="1";
		$search[lists_orders]="id desc";
		$search[num]=9999;
        if($_REQUEST[keyword]){
            $search[keyword]=$_REQUEST[keyword];
        }

		$data[option_lists]=$this->logic->option_lists($search);

		// dump($data);exit;

		//获取投票详情
		$search[id]=$request[id];
		$data[vote]=$this->logic->detail($search);

		$now=time();

    	if($data[vote][detail][time_start] > $now){
    		$this->showmsg("该投票活动还未开始",-1);
    	}
    	if($data[vote][detail][time_end] < $now){
			$this->showmsg("该投票活动已结束",-1);
    	}

    	if(empty($data[vote][detail][status])){
			$this->showmsg("未发布",-1);
    	}

		$this->data=$data;
    	$this->display();
    }

    public function option_detail(){
    	$request=$this->request();

		$data[detail]=M("vote_option")->where("id='$request[vote_id]'")->find();

		//浏览增加+1
		M("vote_option")->where("id=".$request[vote_id]."")->setInc(count_view);
		//获取投票详情
		$search[id]=$data[detail][vote_id];
		$data[vote]=$this->logic->detail($search);
        if($_REQUEST[t]){
            dump($data);exit;
        }
		$this->data=$data;
		$this->display();
    }

    //排名
    public function sort(){
    	$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		
		//获取选项
		$search[vote_id]=$request[id];
		$search[status]="1";
		$search[lists_orders]="count_vote desc";
		$data[option_lists]=$this->logic->option_lists($search);

		//获取投票详情
		$search[id]=$request[id];
		$data[vote]=$this->logic->detail($search);

		$now=time();
		
		if($data[vote][detail][time_start] > $now){
    		$this->showmsg("该投票活动还未开始",-1);
    	}
    	if($data[vote][detail][time_end] < $now){
			$this->showmsg("该投票活动已结束",-1);
    	}

    	if(empty($data[vote][detail][status])){
			$this->showmsg("未发布",-1);
    	}

		$this->data=$data;
    	$this->display();
    }

    //报名
    public function add(){
    	$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}

		$type=M("vote")->where("id={$request[id]}")->getField("type");

		// if(in_array('',$post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }

		if($type==2){
			$this->showmsg("你无权报名参与",1);
		}

		$is_option=M("vote_option")->where("vote_id={$request[id]} AND user_id={$this->user_id}")->find();
		if($is_option){
            // dump($is_option);exit;
            $this->option_detail=$is_option;
			// $this->showmsg("您已经报名！！",-1);
		}

		//获取投票详情
		$search[id]=$request[id];
		$data[vote]=$this->logic->detail($search);

		$this->data=$data;

		$now=time();

    	if($this->data[vote][detail][start_time] > $now){
    		$this->showmsg("该报名还未开始",-1);
    	}
    	if($this->data[vote][detail][end_time] < $now){
			$this->showmsg("该报名已结束",-1);
    	}


    	$this->display();
    }

    //添加报名
    public function option_add_action(){
    	$request=$this->request();
		$post=$request[post];
		$type=M("vote")->where("id={$post[vote_id]}")->getField("type");

		// if(in_array('',$post)){
		// 	$this->showmsg("请填写完整信息",1);
		// }

		if($type==2){
			$this->showmsg("你无权报名参与",-1);
		}

		$res=$this->logic->option_add_action($post,$this->user_id);
		if($res[status] == 12001){
			$this->showmsg("修改保存成功！！",-1);
		}else{
			$this->showmsg("成功报名！！","vote.php?c=index&a=index&id=".$res[vote_id]);
		}

    }

    //上传图片
    public function upload_img(){
		$file=$_FILES[myfile];	
		$type=$_REQUEST[type];
		$this->image_logic=new \Plus\Logic\ImageLogic();
		$data=$this->image_logic->Upload_img($file,$type,$this->user_id);		
		echo json_encode($data);
	}


	#不可报名投票
	public function novote(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		//浏览增加+1
		M("vote")->where("id=".$request[id]."")->setInc(count_view);

		//获取选项
		$search[vote_id]=$request[id];
		$search[status]="1";
		$search[lists_orders]="id desc";
        $search[num]=9999;

		$data[option_lists]=$this->logic->option_lists($search);

		//获取投票详情
		$search[id]=$request[id];
		$data[vote]=$this->logic->detail($search);

		$now=time();

    	if($data[vote][detail][time_start] > $now){
    		$this->showmsg("该投票活动还未开始",-1);
    	}
    	if($data[vote][detail][time_end] < $now){
			$this->showmsg("该投票活动已结束",-1);
    	}

    	if(empty($data[vote][detail][status])){
			$this->showmsg("未发布",1);
    	}
    	if($data[vote][detail][type] != 2){
    		$this->showmsg("活动类型不符合",-1);
    	}

		$this->data=$data;

		$this->display();
	}

	#不可报名投票排名
	public function novote_sort(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		
		//获取选项
		$search[vote_id]=$request[id];
		$search[status]="1";
		$search[lists_orders]="count_vote desc";
        $search[num]=9999;

		$data[option_lists]=$this->logic->option_lists($search);

		//获取投票详情
		$search[id]=$request[id];
		$data[vote]=$this->logic->detail($search);

		$now=time();
		
		if($data[vote][detail][time_start] > $now){
    		$this->showmsg("该投票活动还未开始",-1);
    	}
    	if($data[vote][detail][time_end] < $now){
			$this->showmsg("该投票活动已结束",-1);
    	}

    	if(empty($data[vote][detail][status])){
			$this->showmsg("未发布",1);
    	}

		$this->data=$data;
    	$this->display();
	}

	public function pcvote(){
		$request=$this->request();
		if(!$request[id]){
			$this->showmsg("非法输入",-1);
		}
		//浏览增加+1
		M("vote")->where("id=".$request[id]."")->setInc(count_view);

		//获取选项
		$search[vote_id]=$request[id];
		$search[status]="1";
		$search[lists_orders]="id asc";
		$search[num]=9999;
		
		$data[option_lists]=$this->logic->option_lists($search);

		//获取投票详情
		$search[id]=$request[id];
		$data[vote]=$this->logic->detail($search);

		$now=time();

    	if($data[vote][detail][time_start] > $now){
    		$this->showmsg("该投票活动还未开始",-1);
    	}
    	if($data[vote][detail][time_end] < $now){
			$this->showmsg("该投票活动已结束",-1);
    	}

    	if(empty($data[vote][detail][status])){
			$this->showmsg("未发布",-1);
    	}

    	if($data[vote][detail][is_pc] != 1){
    		$this->showmsg("活动类型不符合",-1);
    	}

		$this->data=$data;

		$this->display();
	}

}