<?php
namespace Plus\Logic;
use Think\Model;
class ImageLogic{
	public function getMillisecond() {
	//	list($t1, $t2) = explode(' ', microtime());
		return time();
		//return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
	}
	public function Upload_img($file,$type,$user_id){	
		if(!$type){
			$type="public";
		}
		if(!$user_id){
			$user_id="0";
		}
		if ($file==null){			
			$data['status']="10009";				
			return $data;				
			exit;  			
		}		
		$tmp_type=$file[type];				
		$uptypes=array('image/jpg','image/jpeg','image/png','image/pjpeg','image/gif','image/bmp','image/x-png');  
		if(!in_array($tmp_type,$uptypes)){  				
			$data['status']="10006";  			
		}else {		
			//var_dump($user_id);
			$fliedir="Uploads/";			
			mkdir($fliedir);			
			$fliedir=$fliedir."$type/";			
			mkdir($fliedir);					
			$fliedir=$fliedir."$user_id/";			
			mkdir($fliedir);			
			$cur_time=uniqid()."-".time();
			$file_name=$fliedir.$user_id.'-orogin-'.'-'.$type.'-'.$cur_time.".jpg";			
			move_uploaded_file($file[tmp_name],$file_name);			
			$none=file_exists($file_name);							
			if ($none!=null){
				$image = new \Think\Image();
				$data[status]=10001;	
				$data[img_orogin]=$file_name;	
    			$image->open($file_name);    	
    			$img_thumb=$fliedir.$user_id.'-thumb'.'-'.$type.'-'.$cur_time.".jpg";
    			if ($type=="goods"){
    				// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
	    			$image->thumb(300, 300,\Think\Image::IMAGE_THUMB_FIXED)->save($img_thumb); 
				//	$image->thumb(800, 800,\Think\Image::IMAGE_THUMB_FIXED)->save($file_name); 
    			}else {
    				$image->save($img_thumb); 
    			}
   				
				$data[img_thumb]=$img_thumb;				
			}else {				
				$data[status]=10012;			
			}				
		}	

	//	dump($data);
		return $data;		
	}
	
	public function change_image_size($file_url,$width,$height,$change_img_url){		
		$image = new \Think\Image();			
		$image->open($file_url);		
    	$image->thumb($width,$height)->save($change_img_url);		
		$none=file_exists($change_img_url);				
		if ($none!=null){				
			$data[status] = 10001;			
			$data[img_url] = $change_img_url;				
		}else {				
			$data[status] = 10007;			
			$data[img_url] = $change_img_url;				
		}    	
	}
		
}