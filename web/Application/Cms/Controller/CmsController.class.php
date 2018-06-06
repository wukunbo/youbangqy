<?php
namespace Cms\Controller;
use Think\Controller;

class CmsController extends BaseController{
	
	public function __construct(){
		parent::__construct();
		$this->user_id=$_SESSION[wx_userid];
		$this->user_openid=$_SESSION["openId"];		
	}
		
	public function cms_lists(){		
 		$num=10;   
  	
    	$this->Cms_logic= new \Cms\Logic\CmsLogic(); 
    	$Cms_lists=$this->Cms_logic->cms_lists($cate_id,1,$num); 
    	$this->Cms_lists=$Cms_lists;	
    	$this->cate_id=$cate_id;
    	$this->title=$title;
		$tpl='Cms/lists';
 
		 
		  //var_dump($Cms_lists);
		if($_REQUEST[ajax]==1){
			$res[status]=1;
			$tpl=$tpl."_tpl";
			if(!$Cms_lists[total]){
				$res[status]=0;
			}
 
			$res[data]=$this->fetch($tpl);
		 
			echo json_encode($res);
			exit;
			
		}	
 
		$this->display($tpl);
 
 
    	 
	}
	
	public function detail(){
		 
		$id=$_REQUEST[id];
		$cate_id=$_REQUEST[cate_id];
		M("tlwx_msg_content")->where("id='$id'")->setInc("click_count");

		$is=M("msg_read")->where("user_id='{$this->user_id}' OR openid='{$this->user_openid}' ")->find();
		if($is){
			M("msg_read")->where("user_id='{$this->user_id}' OR openid='{$this->user_openid}' ")->setInc("read_count");
		}else{
			$dataList=array("user_id"=>$this->user_id,"openid"=>$this->user_openid,"msg_id"=>$id,"read_count"=>1,"addtime"=>time());
			M("msg_read")->add($dataList);
		}

		$this->Cms_logic= new \Cms\Logic\CmsLogic(); 
    	$data[detail]=$this->Cms_logic->detail($id,$cate_id);
		
		$data[comment_lists]=$this->Cms_logic->comment_lists($id);
        //dump($data);
		 
		
		//判断任务ID
		$where=" type=23 and subjoin='".$cms_id."'";
		$data[renwu_id]=M("renwu")->where($where)->getField(id);
		//echo M("renwu")->getLastsql();
		$this->data=$data;
		$share=$_REQUEST[share];
		$this->share = $share;
		$this->display('Cms/detail');
	}
	
 
	
	public function get_cms_gallery(){
		$cms_id=$_REQUEST[cms_id];
		$cms_gallery=M('cms_gallery');
		$img_orogin=$cms_gallery->where("cms_id='$cms_id'")->getField('img_orogin',true);
		for ($i=0;$i<count($img_orogin);$i++){
			$str.='<div class="swiper-slide ">			
				<img src="'.$img_orogin[$i].'" width="100%" >		
			</div>';
		}
		echo $str;
		/*<div class="swiper-slide ">			
				<img src="Uploads/cms/1/1-thumb-cms-1442999392.jpg" width="100%" >		
			</div>	*/
		
	}
 
	
	
}