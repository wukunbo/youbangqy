<?php
namespace Userweb\Controller;
use Think\Controller;
class CmsController extends BaseController{
	public function __construct(){
		parent::__construct();
		$this->Logic= new \Cms\Logic\CmsLogic();
 		$this->weixin_news=new \Userweb\Model\WeixinNewsModel($this->wxdata);
	}	    
	
	//文章列表
	public function lists(){
 		$cate_id=$_REQUEST[cate_id];
		$data[key_words]=$_REQUEST[key_words];
		if($this->user_id!=1){
			$data[user_id]=$this->user_id;
		}

		if($this->user_level == 1){
			$this->partment_list=$this->get_partment();
		}else{
			$data[partment]=$this->user_partment_id;
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		 
		 
		$cms_lists=$this->Logic->cms_lists($cate_id,$data);
		$this->cate_id=$cate_id;
		$data=$cms_lists;
		// dump($data);exit;
		$data[lists][page]=$cms_lists[page];
		$this->data=$data;

		if($_REQUEST[ajax]==1){
			$res[status]=1;
			$tpl="Cms/lists_tpl";
			if(!$cms_lists[total]){
				$res[status]=0;
			}
			$res[data]=$this->fetch($tpl);
			echo json_encode($res);
			exit;
		}	

		$this->display();
	}
	//添加文章页面
	public function add(){
	 
		$id=$_REQUEST[id];
 
		if ($id!=null){
			$data[detail]=$this->Logic->detail($id);
			 
		}

		$this->partment_list=$this->get_partment();
		if($this->user_level==2){
			$this->partment_list=$this->get_partment($this->user_partment_id);
		}
		
		
 		$this->data=$data;
		$this->display();
	}
	
	//添加文章
	public function add_action(){
		$post=$_REQUEST[post];
		$id=$_REQUEST[post_id];
		$cate_id=$_REQUEST[cate_id];
		if($id==""){
			$post[user_id]=$this->user_id;
		}
		if($this->shop_id!=1){
			$post[status]=0;
		}	
		
		$data=$this->Logic->add($id,$post,$gallery_thumb,$gallery_orogin);
		 
 		//var_dump($data);
	//exit;
		if ($data[status]==10001){
			
			$this->showmsg('操作成功，页面跳转中...',"userweb.php?c=Cms&a=lists&menu_app=Artcle");
		}else {
			$this->showmsg('操作失败',1);
		}
	}


	//已读人员
	public function read_msg(){
		$search[msg_id]=$_REQUEST[id];
		$this->data=$this->Logic->read_msg($search);
		$this->display();
	}
	
	

	
	
	public function change_status(){
		$status=$_REQUEST[status];
		$id=$_REQUEST[id];
		$db=M('tlwx_msg_content');
		$db->where("id='".$id."'")->setField("status",$status);
		$this->success('修改成功');
	}
	
 
	
	public function delete(){
		$id=$_REQUEST[id];
		$res=$this->del_action("tlwx_msg_content",$id,$where=" id='".$user_id."' AND user_id='".$this->user_id."'");
		if ($data[status]){
			$this->showmsg('删除成功，页面跳转中...', "1");
		}else {
			$this->showmsg('删除成功',1);
		}
	}
	//分类列表
	public function category_add(){
 
		$search[id]=$_REQUEST[id];
		$data[detail]=$this->Logic->category_detail($search);
		//var_dump($data[detail]);
		
		$lists=$this->Logic->get_category_tree($this->user_id,$parent_id=0);	
		//var_dump($lists);
		$data[op_lists]=$this->Logic->get_select($lists,$data[parent_id]);		
		//var_dump($data[op_lists]);
	//	exit;
		
		$this->data=$data;
		$this->display();
	}
	//分类列表
	public function category_add_action(){
 
		$post_id=$_REQUEST[post_id];
		$post=$_REQUEST[post];
		if($post_id==""){
			$post[user_id]=$this->user_id;
		}
		$res=$this->Logic->category_add($post,$post_id);
		//var_dump($res);exit;
		if($data[status]=10001){
			$this->showmsg("操作成功!");
		}else{
			$this->showmsg("操作失败!");
		}
 
	}
	//素材分类列表
	public function category_lists(){
 		 
		$search[user_id]=$this->user_id;
		$data=$this->Logic->category_lists($search);
	 	//var_dump($data);
		//exit;
		$this->data=$data;
		$this->display();
	}
 

	//素材分类删除
	public function category_del(){
 
		$id=$_REQUEST[id];
		$res=$this->del_action("tlwx_msg_category",$id,$search);
	// 	var_dump($res);
		$this->showmsg("操作成功!");
 
	}
	public function send(){
	
		$CmsLogic= new \Cms\Logic\CmsLogic();
		$cate_lists=$CmsLogic->get_category_tree(1,$parent_id=0);	
 
		$this->cate_lists=$cate_lists;
 
		$this->display();
	}
	//群发
	public function send_action(){
		$post=$_REQUEST[post];
		$post_news=$_REQUEST[post_news];
		$post_id=$_REQUEST[post_id];
	//	var_dump($post);
		if($post[is_send]){
			$tmp=implode(",",$post_news[news_id_str]);
			$lists=M("tlwx_msg_content")->where("id in (".$tmp.")")->select();
			
			for($i=0;$i<count($post_news[news_id_str]);$i++){
				$articles[$i][id]=$post_news[news_id_str][$i];
				$articles[$i][listsorder]=$post_news[listsorder][$i];
				for($j=0;$j<count($lists);$j++){
					if($articles[$i][id]==$lists[$j][id]){
						//客服消息
						//echo "-----------".$lists[$j][url]."-----------------";
						if(!$lists[$j][url]){
							$lists[$j][url]="http://www.homeland.net.cn/web/cms.php?c=cms&a=detail&id=".$lists[$j][id]."";
							//echo "-----------1-----------------";
						}
						if(!stripos($lists[$j][url],"http")){
							$lists[$j][url]="http://www.homeland.net.cn/web/".$lists[$j][url];
							//echo "-----------1-----------------";
						}
						 
						$lists[$j][url]=$lists[$j][url]."&key=".md5($lists[$j][id]+"1123").""; //来自群发
						$articles[$i][title]=$lists[$j][title];
						$articles[$i][author]=$lists[$j][author];
						$articles[$i][digest]=$lists[$j][summary];
						$articles[$i][thumb_media_id]=$lists[$j][mediaid];
						$articles[$i][content]=$lists[$j][context];
						$articles[$i][show_cover_pic]=1;	
						$articles[$i][content_source_url]=$lists[$j][url];
					
						 
						
						$articles[$i][url]=$lists[$j][url];
						$articles[$i][description]=$lists[$j][summary];
						$articles[$i][picurl]="http://www.homeland.net.cn/web/".$lists[$j][image_thumb];	
					}
				}
			}
			 //var_dump($articles);
			foreach ($articles as $key=>$value){
				$id[$key] = $value['id'];
				$listsorder[$key] = $value['listsorder'];
			} 
			array_multisort($listsorder,SORT_NUMERIC,SORT_DESC,$id,SORT_STRING,SORT_ASC,$articles);
			
			$WeixinLogic = new \Weixin\Logic\WeixinLogic();
			if($post[is_kf]==0){
				//新增素材
				$BasicLogic=new \Basic\Logic\BasicLogic;
				$wx_config=$BasicLogic->wx_config($wx_id=1);
			
				$GetSever=new \Weixin\Sever\GetSever;
				$add_news=$GetSever->add_news($wx_config,$articles);	
				//$add_news[media_id]="BCa-NfsqPA_sRr2_JU0ejNDAm5byOOx5K9_VvCflnsE";
				//$add_news[media_id]="";
				if($add_news[media_id]){
					//地区
					$search[province]=$post[province];
					$search[city]=$post[city];
					$search[area]=$post[area];
					//群发
					$res=$WeixinLogic->quan_send($this->wx_id,$media_id=$add_news[media_id],$search=$search,$is_kf=0);
					if($res=="20001"){
						$this->showmsg("发送失败!至少两个OPENID",1);
					}
				}else{
					$this->showmsg("发送失败!",1);
				}
			}
			if($post[is_kf]==1){	
				$res=$WeixinLogic->kf_quan_send($this->wx_id,$articles,$search=$search,$is_kf=1);
			}
			
		}
		//地区
		$RegionLogic = new \Plus\Logic\RegionLogic();
		$content=$RegionLogic->get_name($search);
		//var_dump($content);
		$post[media_id]=$add_news[media_id];
		$post[content]=$content;
		$post[user_id]=$this->user_id;
		$post[id_str]=",".implode(",",$post_news[news_id_str]).",";
		
		$data=$this->Logic->add_send_log($post,$post_id);
	//	var_dump($articles);
	// exit;
		if ($data[status]==10001){
			$this->showmsg('修改成功，页面跳转中...', "1");
		}else {
			$this->showmsg('修改失败',1);
		}
	}

	#发布文章
	public function publish(){
		$id=$_REQUEST[id];

		$userIds=M("msg_content_apply")->where("article_id={$id}")->getField("user_id",true);
		// dump($userIds);exit;
		if(empty($userIds)){
			$this->showmsg('请选择发布名单');
		}

		// exit;

		#推送消息
		$agentid=M("partment")->join(" INNER JOIN tl_tlwx_msg_content ON tl_tlwx_msg_content.partment=tl_partment.id")->where("tl_tlwx_msg_content.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);

		$res=$this->weixin_news->publish_article($_REQUEST[id],$userIds);

		if($res["errmsg"] != "ok"){
			$this->showmsg("发布失败，所选名单不在应用范围内");
		}
		M("tlwx_msg_content")->where("id={$id}")->setField("status",1);
		$this->showmsg('发布成功，页面跳转中...', "userweb.php?m=Userweb&c=Cms&a=lists");
	}

	//预览文章
	public function preview(){
		$id=$_REQUEST[id];
		$user_id=M("tlwx_msg_content")->join("INNER JOIN tl_user_child ON tl_user_child.id=tl_tlwx_msg_content.user_id")->where("tl_tlwx_msg_content.id={$id}")->getField("wx_userid");
		$userIds=array($user_id);

		$agentid=M("partment")->join(" INNER JOIN tl_tlwx_msg_content ON tl_tlwx_msg_content.partment=tl_partment.id")->where("tl_tlwx_msg_content.id={$_REQUEST[id]}")->getField("app_id");
		$this->weixin_news->set_agentid($agentid);
		$res=$this->weixin_news->publish_article($_REQUEST[id],$userIds);

		if($res["errmsg"] != "ok"){
			$this->showmsg("预览失败，所选名单不在应用范围内");
		}
		M("tlwx_msg_content")->where("id={$id}")->setField("status",1);
		$this->showmsg('预览发布成功，页面跳转中...', "userweb.php?m=Userweb&c=Cms&a=lists");

	}

	#选择保存名单
	public function save_contact(){
		$contactIds=$_REQUEST['contactIds'];
		$artcleId=$_REQUEST['id'];
		$this->Logic->select_contact($artcleId,$contactIds); 
		#选择名单状态
		$artcle_status=M("tlwx_msg_content")->where("id={$_REQUEST[id]}")->getField("status");
		if(empty($artcle_status)){
			M("tlwx_msg_content")->where("id={$_REQUEST[id]}")->setField("status",2);
		}
		
		$this->ajaxReturn("ok");
	}

	#已选通讯录名单
	public function check_apply(){
		$id=$_REQUEST["id"];
		
		$search=array();
		$data[all][lists]=M("msg_content_apply")->where("article_id={$id}")->select();

		$this->data=$data;
		
		$this->display();
	}

	public function del_apply(){
		$id=$_REQUEST[id];
		M("msg_content_apply")->where("id={$id}")->delete();
		$this->showmsg('删除成功');
	}

	public function del_allapply(){
		$ids=$_REQUEST[ids];
		M("msg_content_apply")->where("id IN ({$ids})")->delete();
		$this->ajaxReturn("ok");
	}	
	 
}

