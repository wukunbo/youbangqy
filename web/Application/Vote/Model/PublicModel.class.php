<?php
namespace Vote\Model;
use Think\Model;

class PublicModel extends Model{

	public function Page($array){
		$table=$array['table'];
		$num=$array['num'];
		$where=$array['where'];
		$order=$array['order'];
		$field=$array['field'];
		$join=$array["join"] ;
		$search=$array['search'];
		if($field == NULL){
			$field='*';
		}
		if($num == ""){
			$num=10;
		}
		if($num == -1){
			$num="";
		}
		if ($order == ""){
			$order="id desc";
		}
		$Sql=M($table);
		$count= $Sql->where($where)->count();
		$Page= new \Think\Page($count,$num);
		if ($search != ""){
			foreach($search as $key=>$val) {
				$Page->parameter["$key"]=$val;
			}
		}

		$show = $Page->show();// 分页显示输出
		$lists = $Sql->where($where)->join($join)->field($field)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
		$result['page']=$show;
		$result['content']=$lists;
		$result[total]=$count;
		$result[sql]=$Sql->getLastSql();
		return $result;		
	}

	public function Find($array){
		$table=$array['table'];
		$where=$array['where'];
		$order=$array['order'];
		$field=$array['field'];
		
		if ($field == NULL){
			$field='*';
		}
		$Sql=M($table);
		$data=$Sql->field($field)->where($where)->order($order)->find();
		return $data;
	}

	public function Delete($array){
		 $table=$array[table];
		 $where=$array[where];
		 $Sql=M($table);
		 $data=$Sql->where($where)->delete();
		 return $data;
	}

	public function Select($array){
		$table=$array['table'];
		$where=$array['where'];
		$order=$array['order'];
		$field=$array['field'];
		if($order == NULL){
			$order="id desc";
		}
		if ($field == NULL){
			$field='*';
		}
		$Sql=M($table);
		$data=$Sql->field($field)->where($where)->order($order)->select();
	
		return $data;
	}

	public function Save($array){
		$table=$array['table'];
		$where=$array['where'];
		$data=$array['data'];
		
		$Sql=M($table);
		$detail=$Sql->where($where)->save($data);
		return $detail;

	}

	public function Add($array){
		$table=$array['table'];
		$data=$array['data'];

		$Sql=M($table);
		$data=$Sql->add($data);
		return $data;
	}

	public function Find_idmax($array){
		$table=$array['table'];
		$where=$array['where'];

		$Sql=M($table);
		$id=$Sql->where($where)->max('id');
		$where="id='$id'";
		$data=$Sql->where($where)->find();
		return $data;
		
	}
	
	public function count($array){
		$table=$array['table'];
		$where=$array['where'];
		$count=$array['count'];
		
		$Sql=M($table);
		$data=$Sql->where($where)->sum($count);
		return $data;
	}


}