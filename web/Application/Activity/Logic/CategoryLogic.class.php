<?php
namespace Activity\Logic;
use Think\Model;
class CategoryLogic {
	public function  __construct(){
	
		//$this->Category_db=new \Shop\Model\CategoryModel();
		
		$this->table='activity_category';
		
	}
	//第一层分类
	public function get_cate_lists($shop_id,$num,$order){
				
		//$array[num]=$num;
		$array[order]=$order;
		//$array[table]=$this->table;
		$array[where]="";
		//$lists[content]=$this->Category_db->Select($array);
		
		$lists[content]=M("activity_category")->where($array[where])->order($array[order])->select();
		$lists[sql]=M("activity_category")->getLastsql();
		if ($lists[content]==NULL){
			$data[status]=10002;			
		}else {	
			$data[status]=10001;			
		}		
		$data[lists]=$lists;
		return $data;
		
	}
	
	//获取推荐商品分类
	public function get_push_lists($num,$order,$where){
		$array[num]=$num;
		$array[order]=$order;
		$array[table]=$this->table;
		$array[where]=$where;
	//	dump($array);
		$lists=$this->Category_db->Page($array);
//	dump($lists);
		if ($lists[content]==NULL){
			$data[status]=10002;			
		}else {	
			$data[status]=10001;			
		}		
		$data[lists]=$lists;
		return $data;
	}
	
	
	//获取子节点
	public function get_cate_children($cate_id,$field){	
		$category_lists[]['cate_id']=$cate_id;
	 
		$cate_lists=$this->Category_db->get_cate_children($category_lists,$field);
		 
		if ($cate_lists[0][child]==null){		
			$data[status]=10002;			
		}else {			
			$data[status]=10001;			
			$data[lists][content]=$cate_lists[0][child];			
		}				
		return $data;		
	}	
		
	//通过cate_id获取子分类id
	public function get_cate_children_id($cate_id){		
		$category_lists[]=$cate_id;
		$return_lists[]=$cate_id;		
		$cate_lists=$this->Category_db->get_cate_children_id($category_lists,$return_lists);		
		return $cate_lists;
		
	}
	
	//获取分类详情
	public function get_cate_detail($cate_id,$shop_id){
		
		$cate=M('shop_category');		
		$result=$cate->where("  cate_id='$cate_id'")->find();		
		return $result;
		
	}
	
	//构造分类树
	public function get_cate_tree($shop_id='',$field=''){
		
		$cate_lists=$this->get_cate_lists($shop_id);			
		$content=$cate_lists[lists][content];
		
		for ($i=0;$i<count($content);$i++){
	
			$cate_id=$content[$i][cate_id];		
			$lists=$this->get_cate_children($cate_id,$field);	
			 
			if ($lists[status]==10001){		
				$content[$i][child]=$lists[lists][content];				
			}	
			$content[$i][child_num]=count($content[$i][child]);			
		}		
		$cate_lists[lists][content]=$content;					
		return $cate_lists;				
	}

//分类列表下拉框
	public function get_select($data,$parent_id){

		for ($i=0;$i<count($data);$i++){			
			for ($j=0;$j<$data[$i][grade];$j++){				
				$null.='-&nbsp;&nbsp;';					
			}
			
			if ($parent_id==$data[$i][cate_id]){	
				$str.="<option selected='selected' value=".$data[$i][cate_id].">".$null.$data[$i][cat_name]."</option>";	
			}else {					
				$str.="<option value=".$data[$i][cate_id].">".$null.$data[$i][cat_name]."</option>";					
			}
					
			if ($data[$i][show_in_nav]==1){											
				$str.=$this->get_select($data[$i][child],$parent_id);				
			}			        
			$null='';			
		}		
		return $str;	
	}
	
	//分类列表模板
	public function get_lists_show($data){
		//p($data);exit;
		for ($i=0;$i<count($data);$i++){				
			for ($j=0;$j<$data[$i][grade];$j++){					
				$null.='&nbsp;->';				
			}
			
			if ($data[$i][is_show]==1){				
				$is_show="显示";				
			}else{				
				$is_show="不显示";				
			}
					
			$str.="<tr>
				<td><input id=".$data[$i][cate_id]." type='checkbox' value=".$data[$i][cate_id]."  name='cate_id_delete[]'  ></td>
				<td >".$data[$i][cate_id]."</td>					 
				<td >".$null.$data[$i][cat_name]."</td>					
				<td ><img src=".$data[$i][img_thumb]." width='50px'></td>				        			      
				<td>".$data[$i][grade]."</td>				        
				<td>".$is_show."</td>				 
				<td>				        
				<a href='userweb.php?m=Userweb&c=ShopCate&a=cate_form&cate_id=".$data[$i][cate_id]."' ><i class='fa fa-pencil'></i> </a>			            
				<a href='userweb.php?m=Userweb&c=ShopCate&a=cate_delete&cate_id=".$data[$i][cate_id]."&type=1' onClick='return del_sure()' ><i class='fa fa-trash-o'></i></a>		
				</td>			    
				</tr> ";	
							
			if ($data[$i][show_in_nav]==1){			
				$str.=$this->get_lists_show($data[$i][child]);			
			}	
					  
			$null='';			
		}			
		return $str;
	}
	
	
	public function cate_delete($cate_id,$cate_id_delete,$type){
		
		$category=M('shop_category');	
		if ($type==1){
			$cate_id_lists=$this->get_cate_children_id($cate_id);			
			$map[cate_id]=array('in',$cate_id_lists);			
			$check=$category->where($map)->delete();
			 
			 	
		}else if ($type==2){					
			for ($i=0;$i<count($cate_id_delete);$i++){				
				$cate_id_lists=$this->get_cate_children_id($cate_id_delete[$i]);			
				$map[cate_id]=array('in',$cate_id_lists);			
				$check=$category->where($map)->delete();
				 			
			}		
		}
	
		if ($check!=null){			
			$data[status]=10001;			
			return $data;
			
		}else {			
			$data[status]=10002;			
			return $data;
			
		}
		
	}
	
	public function add_cate($cate_id,$post,$parent_id){
		
		$sql=M("shop_category");
		
		if ($parent_id!=0){
			$result=$sql->where("shop_id in ('".$post['shop_id']."') and  cate_id='$parent_id'")->find();
			if ($result[show_in_nav]!=1){				
				$sql->where("shop_id in ('".$post['shop_id']."') and  cate_id='$parent_id'")->setField('show_in_nav','1');				
			}
			$post[grade]=$result[grade]+1;
		}
		
		if ($cate_id!=NULL){

			$id=$sql->where("shop_id in ('".$post['shop_id']."') and  cate_id='$cate_id'")->save($post);
			//管理员日志
			if($id){
				//admin_log($sn = $post[cat_name],'edit', '');
			}
		}else {
			$id=$sql->add($post);
			//管理员日志
			if($id){
				//admin_log($sn = $post[cat_name],'add', '');
			}
		}
	
		if ($id!=false){
			
			$data[status]=10001;
			
		}else {
				
			$data[status]=10002;
			
		}
		
		return $data;
		
	}

	public function get_category(){
		$data=M("activity_category")->field('cate_id,cat_name')->order('cate_id desc')->select();
		return $data;
	}
	

		
		
}