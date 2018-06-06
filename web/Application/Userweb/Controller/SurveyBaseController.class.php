<?php
namespace Userweb\Controller;
use Think\Controller;
class SurveyBaseController extends BaseController {
	public function __construct(){		
		parent::__construct();		
	 	$this->user_id = $_SESSION[userweb][userid];
	 	$BasicLogic=new \Basic\Logic\BasicLogic;
	 }
}