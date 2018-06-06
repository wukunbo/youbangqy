<?php
use Think\Page;
use Think\Model;

function pages($tablName,$pageSize,$rollPage,$wam){
	$M = M("$tablName");
	//$count      = $Goods->where($map)->count();// 查询满足要求的总记录数
	$count      = $M->count();
	$Page  = new Page($count,$pageSize);// 实例化分页类 传入总记录数
	
	$Page->rollPage = $rollPage;
	//$Page->setConfig('header','个商品');
	// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
	$nowPage = isset($_GET['p'])?$_GET['p']:1;
	
	//$data = $Goods->where($map)->order('add_time')->page($nowPage.','.$Page->listRows)->select();
	$data = $M->order($wam)->page($nowPage.','.$Page->listRows)->select();
	$show = $Page->show();// 分页显示输出
	
	//转化为数组
	$show=explode(separator,$show); 
	return array_merge($data,$show);
	
// 	$this->assign('page',$show);// 赋值分页输出
// 	$this->assign('dataList',$data);// 赋值数据集
// 	$this->display("$tablName"); // 输出模板
}

/*
 * 两个表查询
 * $menbers=$model->table('mb_menber m,mb_company c')->where('m.id=c.id')->field('m.*,c.work')->order('m.id')->select();
 */
function findAll($tables,$where,$fields,$order){
	$model = new Model();
	$menbers = $model->table($tables)
					->where($where)
					->field($fields)
					->order($order)
					->select();
	return $menbers;
	
}
function get_ip(){
 	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
 		$cip = $_SERVER["HTTP_CLIENT_IP"];
 	}
 	elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
 		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
 	}
 	elseif(!empty($_SERVER["REMOTE_ADDR"])){
 		$cip = $_SERVER["REMOTE_ADDR"];
 	}
 	else{
 		$cip = "";
 	}
	return $cip;
}