<?php

/**
* 
*/

namespace Userweb\Model;
use Think\Model;


class FunctionServiceModel extends Model
{
	
	//提交资料 post_data
    function post_data($url, $data) { // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'); // 模拟用户使用的浏览器
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包x
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl); //捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        $data = json_decode($result, true);
        return $data;
    }

     function get_data($url) { // 模拟提交数据函数
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
        return $data;
    }

    /**
      write_static_cache('shop_config', $arr);
      $data = read_static_cache('shop_config');
     * 读结果缓存文件
     *
     * @params  string  $cache_name
     *
     * @return  array   $data
     */
    function read_static_cache($cache_name) {
        if ((DEBUG_MODE & 2) == 2) {
            return false;
        }
        static $result = array();
        if (!empty($result[$cache_name])) {
            return $result[$cache_name];
        }
        $cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
        if (file_exists($cache_file_path)) {
            include_once($cache_file_path);
            $result[$cache_name] = $data;
            return $result[$cache_name];
        } else {
            return false;
        }
    }

    /**
     * 写结果缓存文件
     *
     * @params  string  $cache_name
     * @params  string  $caches
     *
     * @return
     */
    function write_static_cache($cache_name, $caches) {
        if ((DEBUG_MODE & 2) == 2) {
            return false;
        }
        $cache_file_path = ROOT_PATH . '/temp/static_caches/' . $cache_name . '.php';
        $content = "<?php\r\n";
        $content .= "\$data = " . var_export($caches, true) . ";\r\n";
        $content .= "?>";
        file_put_contents($cache_file_path, $content, LOCK_EX);
    }

    function C($name) { // 模拟提交数据函数
        // $fileName
        //file_put_contents($fileName, var_export($array, true));
    }
}
?>