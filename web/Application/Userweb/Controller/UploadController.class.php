<?php
namespace Userweb\Controller;
use Think\Controller;

class UploadController extends BaseController{

	public function __construct(){
		parent::__construct();
		$this->user_id=$_SESSION[userweb][userid];
	}

	public function upload_img(){
		$file=$_FILES[myfile];	
		$type=$_REQUEST[type];
		$this->image_logic=new \Plus\Logic\ImageLogic();
		$data=$this->image_logic->Upload_img($file,$type,$this->user_id);		
		echo json_encode($data);
	}
}