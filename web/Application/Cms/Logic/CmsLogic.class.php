<?php
namespace Cms\Logic;
use Think\Model;
class CmsLogic {
	public function  __construct(){	
		$this->Cms_db=new \Cms\Model\CmsModel();	
		$this->table="tlwx_msg_content"	;
		$this->db= new \Vote\Model\PublicModel();

	}
	//列表
	public function cms_lists($cate_id,$data,$num=''){		
		$PRE=C("DB_PREFIX");
		$array[table]=$this->table;
		$array[where].=" 1 ";
		if ($back==1){
			//$array[where]="cate_id='$cate_id' and status = '0'";
		}else {
			//$array[where]="cate_id='$cate_id'";
		}
		if ($cate_id){
			$array[where].=" AND cate_id='$cate_id'";
		}
		if ($data[key_words]){
			$array[where].=" AND title LIKE '%".$data[key_words]."%'";
		}
		if (!empty($data[status]) || $data[status]===0){
			// echo $data[status];exit;
			$array[where].=" AND status ='".$data[status]."'";
		}
		if ($data[user_id]){
			$array[where].=" AND user_id = '".$data[user_id]."'";
		}

		if($data[partment]){
			$array[where].=" AND partment = {$data[partment]} ";
		}
		$array[num]=$num;
		//$array["join"]=" JOIN ".$PRE."shop_config.title ON ";
		$array[order]=''.$PRE.'tlwx_msg_content.sort DESC,'.$PRE.'tlwx_msg_content.id desc';
		
		$data=$this->Cms_db->cms_lists($array);	
		// echo M("tlwx_msg_content")->getLastsql();exit;
		
	 	//dump($data);	
		return $data;		
	}
	//
	public function detail($id){
		$where="id='$id'"; 
		$cms=M($this->table);
		$detail=$cms->where($where)->find();		
		if ($detail==null){
			$data[status]=10002;
		}else {			
			$data[detail]=$detail;
			$data[detail][gallery]=$this->find_cms_gallery($detail[id]);
			$data[status]=10001;
		}
		return $data[detail];		
	}


	//已读人员
	public function read_msg($search){
		$array[table]="msg_read";
		$where=" 1 ";
		if($search[msg_id]){
			$where.=" AND msg_id={$search[msg_id]} ";
		}
		$array[where]=$where;
		$data=$this->db->Page($array);
		foreach ($data[content] as $key => &$value) {
			$value[truename]=M("contacts")->where("wx_userid='{$value[user_id]}'")->getField("name");
		}
		return $data;

	}
	
	public function add($id,$post,$gallery_thumb, $gallery_orogin){
		$cms=M($this->table);
		$where="id='$id'"; 
		if ($id==NULL){
			$post[addtime_1]=time();
			$id=$check=$cms->add($post);
		 
			if($post[status]==1){
				// $url="http://www.homeland.net.cn/web/cms.php?c=cms&a=detail&id=".$id."";
				// if($post[url]){
				// 	$url=$post[url];
				// }
				// $text="您收一条新的信息:\r\n";
				// $text.="【资讯】".$post[title]."\r\n";
				// $text.="<a href='".$url."'>点击查看</a>";
				// $WeixinLogic = new \Weixin\Logic\WeixinLogic();
				 
				// $arr[url]=$url;
				// $arr[description]=$post[title];
				// $arr[picurl]="http://homeland.net.cn/web/".$post[image_thumb];
				// $arr[cate_id]=$post[cate_id];
				// $res=$WeixinLogic->send_all_message($this->wx_id,$text,$arr=$arr);
			}
			//var_dump($res);
			//exit;
		}else {
			if($post[status]==1){
				// $url="http://www.homeland.net.cn/web/cms.php?c=cms&a=detail&id=".$id."";
				// if($post[url]){
				// 	$url=$post[url];
				// }
				// $text="您收一条新的信息:\r\n";
				// $text.="【资讯】".$post[title]."\r\n";
				// $text.="<a href='".$url."'>点击查看</a>";
				// $WeixinLogic = new \Weixin\Logic\WeixinLogic();
				 
				// $arr[url]=$url;
				// $arr[description]=$post[title];
				// $arr[picurl]="http://homeland.net.cn/web/".$post[image_thumb];
				// $arr[cate_id]=$post[cate_id];
				// $res=$WeixinLogic->send_all_message($this->wx_id,$text,$arr=$arr);
			}
			$check=$cms->where($where)->save($post);
		}
		$this->add_cms_gallery($cms_id, $gallery_thumb, $gallery_orogin);
		
		if ($id!=false||$check!=false){			
			$data[status]=10001;
		}else {
			$data[status]=10002;
		}
		$data[sql]=$cms->getLastsql();
		return $data;
	}

	
	public function add_cms_gallery($cms_id,$gallery_thumb,$gallery_orogin){
		$cms_gallery=M('cms_gallery');
		$cms_gallery->where("cms_id='$cms_id'")->delete();
		$options[cms_id]=$cms_id;
	
		for ($i=0;$i<count($gallery_orogin);$i++){
			
			$options[img_thumb]=$gallery_thumb[$i];
			$options[img_orogin]=$gallery_orogin[$i];
		
			$check=$cms_gallery->add($options);
			
		}
		return $check;
	}
	
	public function find_cms_gallery($cms_id){
		$cms_gallery=M('cms_gallery');
		$gallery=$cms_gallery->where("cms_id='$cms_id'")->select();
		return $gallery;
	}
	 public function comment_lists($cms_id){
	 	$array['table'] = "comment" ;
        $where[business_id] = array("eq",$cms_id);
        $where[business_type] = array("eq",'cms') ;
        $array[order] = "addtime desc" ;
        $array[where] = $where;
    	$lists=$this->Cms_db->Page($array);
		//dump($lists);
    	for($i=0;$i<count($lists[content]);$i++){
    		$user_id = $lists[content][$i][user_id] ;
    		$lists[content][$i][userinfo] = M("userinfo")->where("id='$user_id'")->find();
    	}
    	return $lists;
    }
	//文章分类
	public function category_detail(){
		$where="id='".$search[id]."'";
		$db=M('tlwx_msg_category');
		$detail=$db->where($where)->Find();
		//return false;
		return $detail;
	}
	//类新增
	public function category_add($post,$post_id){
		 
		$db=M('tlwx_msg_category');
		if($post_id){
			$db->where("id='".$post_id."' AND user_id='".$post[user_id]."'")->save($post);
		}else{
			$post_id=$db->add($post);
		}
		$res[status]=10001;
		$res[id]=$post_id;
		return $res;

	}
	
	//素材分类列表
	public function category_lists($search){
		 	
		$array['where']="user_id='".$search[user_id]."'";			
		$array['num']=10000;		
		$array['table']="tlwx_msg_category";	
		 
		$result=$this->Cms_db->Page($array);	
		return $result;
	}
	//分类列表下拉框
	public function get_select($lists,$select_id=""){
 		//echo "==1==";
		//var_dump($lists);
		for ($i=0;$i<count($lists);$i++){
			
			$null="";		
			for ($j=0;$j<$lists[$i][level];$j++){				
				$null.='-&nbsp;&nbsp;';					
			}
			if ($select_id==$lists[$i][id]){	
				$str.="<option selected='selected' value=".$lists[$i][id].">".$null.$lists[$i][title]."</option>";	
			}else {					
				$str.="<option value=".$lists[$i][id].">".$null.$lists[$i][title]."</option>";					
			}	
			//if ($data[$i][show_in_nav]==1){											
			$str.=$this->get_select($lists[$i][child],$select_id);				
			//}			        		
		}	
 
		//exit;	
		return $str;
	}
	//递归
	public function get_category_tree($user_id='',$parent_id,$level=0){

		$where="parent_id='".$parent_id."' ";
		if(!empty($user_id)){
			$where.=" AND user_id='".$user_id."'";
		}
		$db=M('tlwx_msg_category');
		$lists=$db->where($where)->select();
		// echo $db->getLastsql();exit;
		$level=$level+1;
		 
		for ($i=0;$i<count($lists);$i++){			
			$lists[$i][child]=$this->get_category_tree($user_id,$lists[$i][id],$level);	
			$lists[$i][level]=$level;	
		}
		// var_dump($lists);
		return $lists;				
	}

	#选择活动名单
	public function select_contact($artcleId,$contactIds){
		$contactId=explode(",",$contactIds);
		$addtime=time();
		$db=M("contacts");
		foreach ($contactId as $key => $id) {
			$contact=$db->where("tl_contacts.wx_userid={$id}")->field("name,partment_id,partment,code,work_group,wx_userid")->find();
			$dataList=array('article_id'=>$artcleId,'user_id'=>$contact['wx_userid'],'truename'=>$contact['name'],'addtime'=>$addtime,'partment_id'=>$contact['partment_id'],'partment'=>$contact['partment'],'content'=>$contact['code']);
			// $array[table]="msg_content_apply";
			// $array[data]=$dataList;
			$artcle_apply=M("msg_content_apply")->where("article_id={$artcleId} AND user_id='{$contact[wx_userid]}' ")->find();
			if(!$artcle_apply){
				M("msg_content_apply")->add($dataList);
			}
		}
		$res[sql]=M("contacts")->getLastsql();
		$res[status]=10001;
		return $res; 	
	}
}