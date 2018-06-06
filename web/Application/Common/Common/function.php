<?php
use \Think\Page;

/**
 * 格式化输出
 * @param array $msg
 */
function p($msg) {
    echo '<pre>';
    var_dump($msg);
    echo '</pre>';
}

function SQL($table){

    echo M($table)->getLastsql();
}

function SqlInsert($table, $data) {//sql添加数据处理
    $User = M($table);
    $info = $User->add($data);
    return $info;
}

function SqlUpdate($table, $where, $data) {//sql更新数据处理
    $User = M($table);
    //$id = $data['id'];
    $info =  $User->where($where)->setField($data);
// echo $User->getLastSQL();p($info);exit;
     return $info;
}


/**
 * 单表查询,无分页
 * @param type $table
 * @param type $where
 * @return type
 */
function SqlSelect($table, $where) { //sql的搜索处理
    $User = M($table); // 实例化User对象
    //$result = $User->where($where)->find();
    $result = $User->where($where)->select();
//echo $User->getLastSQL();
    return $result;
}

/**
 * 单表查询,无条件
 * @param type $table
 */
function myselect($table){
    $User = M($table); // 实例化User对象
    //$result = $User->where($where)->find();
    $result = $User->select();
//echo $User->getLastSQL();
    return $result;
}
/**
 * 单表查询,带条件
 * @param type $table
 * @param type $where
 * @return type
 */
function myFind($table, $where) { //sql的搜索处理
    $User = M($table); // 实例化User对象
    $result = $User->where($where)->find();
    //$result = $User->where($where)->select();
//echo $User->getLastSQL();
    return $result;
}

/**
 * 根据条件删除记录
 * @param type $table 名称,不带前缀
 * @param type $where  string
 */
function SqlDelete($table, $where) {//sql删除数据
    $User = M($table);
    return $User->where($where)->delete();
}

function getchose($name, $length) {

    $item = 1;
    for ($i = 0; $i < $length; $i++) {
        $itemname = "$name" . "$i";

        if ($_POST["$itemname"] != NULL) {
            $username[$item] = $_POST["$itemname"];
            $item++;
        }
    }
    return $username;
}


#递归获取全部子部门id
function child_partment($id){
    $GLOBALS["id"][]=$id;
    $arr=M("contacts_partment")->where("parentid={$id}")->field("id")->select();
    if($arr){
        foreach ($arr as $key => $value) {
            child_partment($value[id]);
        }
    }
    return $GLOBALS["id"];
}

function getmessagelist($result) {//将用户打招呼等存入到result
    $array = $result[0]['result'];

    for ($i = 0; $i < count($array); $i++) {
        $Username = M('userinfo');
        $fromuserid = $array[$i]['fromuserid']; //发送用户ID和名字
        $Fromusernameresult = $Username->where("userid='$fromuserid'")->find();

        $array[$i]['fromusername'] = $Fromusernameresult['username'];
        $array[$i]['touch'] = $Fromusernameresult['touch'];
        $array[$i]['vip'] = $Fromusernameresult['vip'];
        $array[$i]['sex'] = $Fromusernameresult['sex'];
        $array[$i]['point'] = $Fromusernameresult['point'];
        $array[$i]['alipay'] = $Fromusernameresult['alipay'];

        $touserid = $array[$i]['userid']; //接受用户ID和名字
        $Usernameresult = $Username->where("userid='$touserid'")->find();

        $array[$i]['tousername'] = $Usernameresult['username'];
    }
    return $result[0]['result'] = $array;
}

function checknull($arr) {
    for ($i = 0; $i < $arr; $i++) {
        if ($arr[$i] == NULL) {
            return 0;
        } else {
            return 1;
        }
    }
}

/**
 * by grace 
 * 2014.10.30
 * 
 * 多表,带分页,搜索,查询
 * @param type $table
 * @param type $pagesize
 * @param type $rollpage
 * @param type $where
 * @param type $order
 * 
 * $list = $user->table('user_status stats, user_profile profile')->
 *                 where('stats.id = profile.typeid')->
 *                 field('stats.id as id, stats.display as display, profile.title as title,profile.content as content')->
 *                 order('stats.id desc' )->
 *                 select();
 */
function mypages($table, $pagesize, $rollpage, $where, $field, $order) {

    $demo = M();
    $count = $demo->
            where($where)->
            table($table)->
            count();
   // echo $demo->getLastSql();
   // echo '<br />';
//    $count = $Grade->where("tl_grade.site_id = $id")->
//            field("tl_user.username username,tl_checkstation.sitename sitename,tl_grade.*")->
//            join("tl_user on tl_user.id = tl_grade.user_id")->
//            join("tl_checkstation on tl_checkstation.id = tl_grade.site_id")
//            ->count("*");
    //echo $Grade->getLastSql();
    //exit;
    $Page = new Page($count, $pagesize); // 实例化分页类 传入总记录数
    $Page->rollPage = $rollpage;


    $nowPage = isset($_GET['p']) ? $_GET['p'] : 1;

    $datalist = $demo->
            where($where)->
            table($table)->
            field($field)->
            order($order)->
            page($nowPage . ',' . $Page->listRows)->
            select();
    //echo $demo->getLastSql();
    //p($datalist);
    $show = $Page->show(); // 分页显示输出
    //转化为数组
    $show = explode(separator, $show);
    return array_merge($datalist, $show);
}

/* by grace */




    /**
     * 下载文件
     * @param string $file
     *               被下载文件的路径
     * @param string $name
     *               用户看到的文件名
     */
    function mydownload($file,$name=''){
        $fileName = $name ? $name : pathinfo($file,PATHINFO_FILENAME);
        $filePath = realpath($file);
        
        $fp = fopen($filePath,'rb');
        
        if(!$filePath || !$fp){
            header('HTTP/1.1 404 Not Found');
            echo "Error: 404 Not Found.(server file path error)<!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding --><!-- Padding -->";
            exit;
        }
        
        $fileName = $fileName .'.'. pathinfo($filePath,PATHINFO_EXTENSION);
        //$encoded_filename = urlencode($fileName);
        //$encoded_filename = str_replace("+", "%20", $encoded_filename);
        $encoded_filename=rawurlencode($fileName);
        
        header('HTTP/1.1 200 OK');
        header( "Pragma: public" );
        header( "Expires: 0" );
        header("Content-type: application/octet-stream");
        header("Content-Length: ".filesize($filePath));
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($filePath));
        
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
        }
        
         ob_end_clean(); //<--有些情况可能需要调用此函数
        // 输出文件内容
        fpassthru($fp);
        exit;
    }

    
    function array_sort($arr, $keys, $type = 'desc') {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
    
function _location($info,$url){
	if(!empty($info)){
	echo "<script language='javascript'>alert('$info');location.href='$url'</script>";
	}else{
		header('location:'.$url);
	}  
}

function timeop($time,$type="talk") {
	$ntime=time()-$time;
	if ($ntime<60) {
		return("刚才");
	} elseif ($ntime<3600) {
		return(intval($ntime/60)."分钟前");
	} elseif ($ntime<3600*24) {
		return(intval($ntime/3600)."小时前");
	} elseif (date('Y',time())==date('Y',$time) && $ntime<3600*24*365) {
		if ($type=="talk") {
			return(gmdate('m'."月".'d'."日".' H:i',$time+8*3600));
		} else {
			return(gmdate('m-d H:i',$time+8*3600));
		}
	} else {
		if ($type=="talk") {
			return(gmdate('Y'."年".'m'."月".'d'."日".' H:i',$time+8*3600));
		} else {
			return(gmdate('Y-m-d H:i',$time+8*3600));
		}
	}
}

function textartToHtml($post){
	$post=str_replace(chr(13),'<br>',$post);
	$post=str_replace(chr(32),'&nbsp;',$post);
	return $post;
}


//生成二维码
function add_qcr($goods_sn){
	$fliedir="Uploads/fx_pic/qrc";
	mkdir($fliedir);
	$current_time=time();
	$file_name="Uploads/fx_pic/qrc/".$current_time.".jpg";
	//include('http://localhost/card/Public/phpqrcode/phpqrcode.class.php') ;
    include("p.class.php");
	//import("Common.phpqrcode.phpqrcode");
	$url = 'http://yun.tuolve.com/card/index.php?c=Card&a=card_detail&card_id='.$goods_sn;
	$level = 'L';
	$QRcode = new \QRcode();
	// 点的大小：1到10,用于手机端4就可以了
	$size =10;
	// 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
	// 生成的文件名
	if(!file_exists($file_name)){
		$QRcode->png($url, $file_name, $level, $size);
	}
	$data[pic]=$file_name;
	return $data;
	// 	$data['url'] = substr($file_name, 3);
	// 	$data['rs'] = true;
	// 	$data['status'] = 10001;
	// 	myJson($data);
}

//生成二维码url
function add_qcr_url($url){
    $fliedir="Uploads/fx_pic/qrc";
    mkdir($fliedir);
    $current_time=time();
    $file_name="Uploads/fx_pic/qrc/".$current_time.".jpg";
    //include('http://localhost/card/Public/phpqrcode/phpqrcode.class.php') ;
    include("p.class.php");
    //import("Common.phpqrcode.phpqrcode");
    $level = 'L';
    $QRcode = new \QRcode();
    // 点的大小：1到10,用于手机端4就可以了
    $size =10;
    // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
    // 生成的文件名
    if(!file_exists($file_name)){
        $QRcode->png($url, $file_name, $level, $size);
    }
    $data[pic]=$file_name;
    return $data;
    //  $data['url'] = substr($file_name, 3);
    //  $data['rs'] = true;
    //  $data['status'] = 10001;
    //  myJson($data);
}


  function diffDate($date1,$date2){ 
        if(strtotime($date1)>strtotime($date2)){ 
            $tmp=$date2; 
            $date2=$date1; 
            $date1=$tmp; 
        } 
        list($Y1,$m1,$d1)=explode('-',$date1); 
        list($Y2,$m2,$d2)=explode('-',$date2); 
        $y=$Y2-$Y1; 
        $m=$m2-$m1; 
        $d=$d2-$d1; 
        if($d<0){ 
            $d+=(int)date('t',strtotime("-1 month $date2")); 
            $m--; 
        } 
       //  echo $y;echo $m;echo $d;
	  	
		if($m==0){
			if($d<0){
				 $d+=(int)date('t',strtotime("-1 month $date2")); 
            	 $m--;  
			}
		}
		if($m<0){ 
            $m+=12; 
            $y--; 
        } 
		//echo $y;echo $m;echo $d;
        return array('year'=>$y,'month'=>$m,'day'=>$d); 
    }

    /**
     * 记录管理员的操作内容
     *
     * @access  public
     * @param   string      $sn         数据的唯一值
     * @param   string      $action     操作的类型
     * @param   string      $content    操作的内容
     * @return  void
     */
    function admin_log($sn = '',$action, $content){
        $admin_auth=M('shop_admin_auth');
        switch($action){
            case 'add':$auth_name='添加';break;
            case 'edit':$auth_name='编辑';break;
            case 'del':$auth_name='删除';break;
         }
        $log_info = $auth_name . $content .': '. $sn;
        $admin_log=M('admin_log');
        $list[admin_id]=$_SESSION['admin']['id'];
        $list[addtime]=time();
        $list[log_info]=$log_info;
        $list[ip]=$_SERVER["REMOTE_ADDR"];
        $admin_log->add($list);
    } 	
    //获取星期几
    function   get_week($date){
    	//强制转换日期格式
    	$date_str=date('Y-m-d',strtotime($date));
    	 
    	//封装成数组
    	$arr=explode("-", $date_str);
    
    	//参数赋值
    	//年
    	$year=$arr[0];
    
    	//月，输出2位整型，不够2位右对齐
    	$month=sprintf('%02d',$arr[1]);
    
    	//日，输出2位整型，不够2位右对齐
    	$day=sprintf('%02d',$arr[2]);
    
    	//时分秒默认赋值为0；
    	$hour = $minute = $second = 0;
    
    	//转换成时间戳
    	$strap = mktime($hour,$minute,$second,$month,$day,$year);
    
    	//获取数字型星期几
    	$number_wk=date("w",$strap);
    
    	//自定义星期数组
    	$weekArr=array("周日","周一","周二","周三","周四","周五","周六");
    
    	//获取数字对应的星期
    	return $weekArr[$number_wk];
    }
    
    //测试
    //$date="2015-12-10";
   // echo get_week($date);
    //星期四
 