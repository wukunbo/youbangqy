<?php
namespace Admin\Controller;
use Think\Controller;
class OrderController extends BaseController{
	public function __construct(){
		
		parent::__construct();
		
		$this->order_logic= new \Shop\Logic\OrderLogic();
		
	}
	public function order_lists(){
	
		$keyword=$_REQUEST[keyword];
		$status=$_REQUEST[status];
		
		switch ($status){
			case 1:
				$pay_stauts=0;	
			break;	
			case 2:
				$pay_stauts=1;		
			break;	
			case 3:
				$shipping_status=0;				
			break;	
			case 4:
				$shipping_status=1;				
			break;	
			case 5:
				$shipping_status=2;				
			break;	
			case 6:
				$shipping_status=3;				
			break;	
			case 7:
				$shipping_status=4;				
			break;	
		}	
		$order_log=M(order_log);
		
		$user = new \User\Logic\UserLogic();
		$data=$this->order_logic->order_lists('',$pay_stauts,$shipping_status,$keyword,$user_id);
		for ($i=0;$i<count($data[content]);$i++){
			$order_id=$data[content][$i][order_id];
			$user_id=$data[content][$i][user_id];
			$order_detail=$this->order_logic->order_detail($order_id);
			$userinfo=$user->user($user_id);   
			$data[content][$i][name]=$userinfo[userinfo][nickname];
			$data[content][$i][order_detail]=$order_detail[goods_lists];
			$checks=$order_log->where("order_id='$order_id' and status = '1'")->find();
		
			if ($checks!=null){
				$data[content][$i]['addtime_text']="成交时间";
				$data[content][$i][addtime]=$checks[addtime];
			}else {
				$data[content][$i]['addtime_text']="下单时间";
			}
			$order_log_result=$order_log->where("order_id ='$order_id' and status = '0'")->order('addtime desc')->find();
			$data[content][$i]['weixin_order_sn']=md5($order_log_result[order_log_id]);
		}
	//	dump($data);
		$this->status=$status;
		$this->keyword=$keyword;
		$this->data=$data;
		
		$this->display();
	}

	public function set_city(){
		$city=$this->logic->set_city();
	
		echo $city;
		$this->city=$city;
	}
	
	public function kuaidi_form(){		
		$order_id=$_REQUEST[order_id];
		$order_goods_id=$_REQUEST[order_goods_id];
		$order_detail=$this->order_logic->order_detail($order_id);
	
		if($order_goods_id==null){
			$where="order_id='$order_id'";
		}else {
			$where="order_id='$order_id' and order_goods_id ='$order_goods_id'";
		}
		
		$kuaidi=M('kuaidi');
		$kuaidi_lists=$kuaidi->where("1")->select();		
		$order_shipping=M('order_shipping');
		$result=$order_shipping->where($where)->find();
		$this->order_goods_id=$order_goods_id;
		$this->order_detail=$order_detail;
		$this->order_id=$order_id;
		$this->result=$result;
		$this->kuaidi_lists=$kuaidi_lists;
		$this->display();
	}
	
	public function add_order_shipping(){		
		$order_shipping=M('order_shipping');			
		$post=$_REQUEST[post];		
		$post[addtime]=time();		
		$order_id=$post[order_id];		
		$order=M('order');
		$order->where("order_id='$order_id'")->setField("shipping_status",'1');	
		
		$order_log=M('order_log');
		$result=$order_log->where("order_id='$order_id'")->find();
		$this->order_logic->add_order_log($result[order_id],$result[user_id],2,$result[point_fee],$result[money_fee],1,$_SESSION['admin']['id']);
		
		
		if ($post[order_goods_id]!=null){
			$order_goods_id=$post[order_goods_id];
			$where="order_id='$order_id' and order_goods_id ='$order_goods_id'";
			$result=$order_shipping->where($where)->find();
		
			$post[order_goods_id]=$order_goods_id;
			if ($result!=NULL){			
				$order_shipping->where($where)->save($post);			
			}else {			
				$order_shipping->add($post);
			}
		}else {
			$order_goods=M('order_goods');
			$order_goods_id_lists=$order_goods->where("order_id='$order_id'")->getField('id',true);
			for ($i=0;$i<count($order_goods_id_lists);$i++){
				$order_goods_id=$order_goods_id_lists[$i];
				$where="order_id='$order_id' and order_goods_id ='$order_goods_id'";
				$result=$order_shipping->where($where)->find();
				$post[order_goods_id]=$order_goods_id;
				if ($result!=NULL){			
					$order_shipping->where($where)->save($post);			
				}else {			
					$order_shipping->add($post);
				}
			}
		}
		$this->success('success');
	}
	
	public function show_out(){
		$order_id=$_REQUEST[order_id];
		$data=$this->order_logic->order_detail($order_id);
		//dump($data);
		$this->attr_logic= new \Shop\Logic\AttrLogic();
		for ($i=0;$i<count($data[goods_lists]);$i++){
			$tmp_attr=$data[goods_lists][$i][attr];
			$tmp_attr_lists=explode(',', $tmp_attr);
			for ($j=0;$j<count($tmp_attr_lists);$j++){
				$tmp_op_id=$tmp_attr_lists[$j];
				$data[goods_lists][$i][op_lists][]=$this->attr_logic->get_op_detail($tmp_op_id);
			}
			
		}
		$this->order_detail=$data;
		$this->display();
	}
	
	
	public function order_form(){
		$order_id=$_REQUEST['order_id'];
		$data=$this->order_logic->order_detail($order_id);
		//dump($data);
		$this->data=$data;
		$this->display();
	}
	
	
	public function sreach(){
		
		$data=$this->logic->sreach();
		$this->data=$data;
		$this->display();
	}
	public function imgdetele(){
		$data=$this->logic->imgdetele();
		$goods_id=$_REQUEST[goods_id];
		$this->redirect('imglist',array('id' => $goods_id));
		$this->display();
	}
	
	public function order_delete(){
		 $p=$_REQUEST[p];
		 $order_id=$_REQUEST[order_id];
		$order=M('order');
		$resutl=$order->where("order_id='$order_id'")->find();
		if ($resutl[pay_status]!=0){
			$this->error('不能删除已支付的订单');
		}else {
			$order->where("order_id='$order_id'")->delete();
		}
			$this->redirect('order_lists',array('p' => $p));
	}
	
	public function back_order(){
		$data=$this->order_logic->back_order_lists();
	
		$this->data=$data;
		$this->display();
	}
	
	public function back_order_form(){
		$order_back_id=$_REQUEST[order_back_id];
		$order_back=M('order_back');
		$result=$order_back->where("order_back_id ='$order_back_id '")->find();
		$order_id=$result[order_id];
		$order=$this->order_logic->order_detail($order_id);
		$orderback_gallery=M('orderback_gallery');
		$back_gallery=$orderback_gallery->where("order_back_id='$order_back_id'")->select();
		$result[back_gallery]=$back_gallery;
		$result[order]=$order;
		$this->data=$result;
		$this->display();
	}
	
	public function order_back_delete(){
		$order_back_id=$_REQUEST[back_id];
		$order_back=M('order_back');
		$order_back->where("order_back_id ='$order_back_id '")->delete();
		$orderback_gallery=M('orderback_gallery');
		$orderback_gallery->where("order_back_id='$order_back_id'")->delete();
		$this->redirect('back_order');
	}
	
	public function order_add(){
		$order_id=$_REQUEST[order_id];
		$post=$_REQUEST[post];
		$order=M('order');
		$check1=$order->where("order_id='$order_id'")->setField('pay_status',$post[pay_status]);
		$check2=$order->where("order_id='$order_id'")->setField('shipping_status',$post[shipping_status]);
		$check3=$order->where("order_id='$order_id'")->save($post);
		
		if ($check3==false){
			$this->error('修改失败');
		}else {
			
			$order_log=M('order_log');
			$result=$order_log->where("order_id='$order_id'")->find();
			if($check1!=false){
				if ($post[pay_status]==1){
					$status=1;
				}elseif ($post[pay_status]==0) {
					$status=0;
				}elseif ($post[pay_status]==3) {
					$status=6;
				}
				$this->order_logic->add_order_log($result[order_id],$result[user_id],$status,$result[point_fee],$result[money_fee],1,$_SESSION['admin']['id']);
		
			}
			
			if($check2!=false){
				if ($post[shipping_status]==0){
					$status=8;
				}elseif ($post[shipping_status]==1){
					$status=2;
				}elseif ($post[shipping_status]==2){
					$status=3;
				}elseif ($post[shipping_status]==3){
					$status=4;
				}elseif ($post[shipping_status]==4){
					$status=5;
				}
				
				$this->order_logic->add_order_log($result[order_id],$result[user_id],$status,$result[point_fee],$result[money_fee],1,$_SESSION['admin']['id']);
		
			}
			
			$this->success('修改成功');
		}
	}
	
	public function order_back_add(){
		$post=$_REQUEST[post];
		$back_order_id=$_REQUEST[back_order_id];
		$order_back=M('order_back');
		$check=$order_back->where("order_back_id='$back_order_id'")->save($post);
		if ($post[status]==3){
			$order_id=$order_back->where("order_back_id='$back_order_id'")->getField('order_id');
			$this->userlogic=new \User\Logic\UserLogic;
			$this->userlogic->deduct($user_id,$order_id,14);
		}
		if ($check==false){
			$this->error('修改失败');
		}else {
			$this->success('修改成功');
		}
	}
	
	
	public function order_log(){
		$keyword=$_REQUEST[keyword];
		$array[where]="form = 1";
		if ($keyword!=null){
			$order=M('order');
			$order_id=$order->where("order_sn like '%$keyword%' ")->getField('order_id');
			$array[where].=" and order_id='$order_id'";
		}
		$array[table]="order_log";
		$data=$this->order_logic->order_log($array);
		$this->data=$data;
		//dump($data);
		$this->display();
	}
	
	public function change_shipping_status(){
		$chose=$_REQUEST[chose];
		$pay_status=$_REQUEST[pay_status];
		$shipping_status=$_REQUEST[shipping_status];
		$map[order_id]=array('in',$chose);
	
		$order=M('order');
		if ($shipping_status!=null){
			$order->where($map)->setField("shipping_status",$shipping_status);
		}
		if ($pay_status!=null){
			$order->where($map)->setField("pay_status",$pay_status);
		}
		
		$this->redirect('order_lists',array('p' => $p,'back' =>10001));
	}
	
	
}