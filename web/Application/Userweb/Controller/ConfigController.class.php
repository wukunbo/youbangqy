<?php
namespace Userweb\Controller;
use Think\Controller;
class ConfigController extends BaseController{

	public function __construct(){
		parent::__construct();
		$this->Logic= new \Basic\Logic\MaterialLogic();
 
	}	    
	//素材列表
	public function material_lists(){
 
		$search[user_id]=$this->user_id;	
		$search[num]=12;
		if($_REQUEST[cate_id]<0 || !$_REQUEST[cate_id]){
			$search[all]=1;//获取公共部分的
		}
		$search[cate_id]=$_REQUEST[cate_id];	
		$data[lists]=$this->Logic->material_lists($search);
		
		$search[user_id]=$this->user_id;
		$data[cate_lists]=$this->Logic->material_cate_lists($search);

		$this->data=$data;
		$this->search=$search;
		 
		if($_REQUEST[ajax]==1){
			$return_fn=isset($_REQUEST[return_fn])?$_REQUEST[return_fn]:"material_select";
 
			$data[lists][page]=str_replace('href="','href="javascript:show_content(\'',$data[lists][page]);
			$data[lists][page]=str_replace('">','\')">',$data[lists][page]);
			$data[lists][page]=str_replace('current\')','current',$data[lists][page]);
			$data[page]=$data[lists][page];

			$this->data=$data;
			$this->return_fn=$return_fn;
			
			$this->display("material_lists_ajax");
		}else{
			$this->display();
		}
		 
	}
	public function get_material_lists(){
		$search[user_id]=$this->user_id;	
		$data[lists]=$this->Logic->material_lists($search);
		$this->data=$data;
		$html=$this->fetch("material_lists_tpl");
		if($_REQUEST[json]==1){
			$res[status]=10001;
			$res[html]=$html;
			echo json_encode($res);
			exit;
		}else{
		 
			$this->showmsg("操作成功!","userweb.php?c=Config&a=material_lists");
		}
	}
	//素材分类列表
	public function material_add_action(){
 
		$post_id=$_REQUEST[post_id];
		$post=$_REQUEST[post];
		$post[user_id]=$this->user_id;
 
		$data=$this->Logic->material_add($post,$post_id);
		
		if($_REQUEST[json]==1){
			$res[status]=10001;
			echo json_encode($res);
			exit;
		}else{
		 
			$this->showmsg("操作成功!","userweb.php?c=Config&a=material_lists");
		}
 
	}
	//素材删除
	public function material_del(){
 
		$id=$_REQUEST[id];
		$res=$this->del_action("tlwx_material",$id,$search);
	// 	var_dump($res);
		$this->showmsg("操作成功!");
 
	}
	//素材分类列表
	public function material_cate_lists(){
 		 
		$search[user_id]=$this->user_id;
		$data=$this->Logic->material_cate_lists($search);
	 
		$this->data=$data;
		$this->display();
	}
	//素材分类列表
	public function material_cate_add(){
 
		$search[id]=$_REQUEST[id];
		$data=$this->Logic->material_cate_detail($search);
		$this->data=$data;
		$this->display();
	}
	//素材分类列表
	public function material_cate_add_action(){
 
		$post_id=$_REQUEST[post_id];
		$post=$_REQUEST[post];
		$post[user_id]=$this->user_id;
		$data=$this->Logic->material_cate_add($post,$post_id);
		$this->showmsg("操作成功!");
 
	}
	//素材分类删除
	public function material_cate_del(){
 
		$id=$_REQUEST[id];
		$res=$this->del_action("tlwx_material_cate",$id,$search);
	// 	var_dump($res);
		$this->showmsg("操作成功!");
 
	}
	
	 
}

