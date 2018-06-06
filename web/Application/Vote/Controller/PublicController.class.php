<?php
namespace Vote\Controller;
use Think\Controller;
class PublicController extends Controller {

	public function __construct(){
		parent::__construct();
	}

	 //上传图片
    public function upload_img(){
		$file=$_FILES[myfile];	
		$type=$_REQUEST[type];
		$this->image_logic=new \Plus\Logic\ImageLogic();
		$data=$this->image_logic->Upload_img($file,$type,$this->user_id);		
		echo json_encode($data);
	}

}