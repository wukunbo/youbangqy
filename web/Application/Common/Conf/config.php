<?php
return array(
	//'配置项'=>'配置值'
	//
	'DB_TYPE' => 'mysql', // 数据库类型
	// 'DB_HOST' => 'localhost', // 服务器地址
    'DB_HOST' => '120.76.158.170', // 服务器地址
    'DB_NAME' => 'youbang', // 数据库名

    // 'DB_USER' => 'root', // 用户名
    // 'DB_PWD' => 'c4dcce705a', // 密码
    'DB_USER' => 'yuancheng', // 用户名
    'DB_PWD' => 'yuancheng123452asdfwe;asdf6', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'tl_', // 数据库表前缀 
    'DB_CHARSET' => 'utf8', // 字符集
    'URL_MODEL' => '0',
    'DB_FIELDS_CACHE'=>false  ,
    'SHOW_PAGE_TRACE' => false, // 显示页面Trace信息

    #缓存
    'TMPL_CACHE_ON' => false,
    'TMPL_CACHE_ON' => false,
 

	
	'DB_FIELDS_CACHE'=>false   ,
	'LOG_RECORD' => true, // 开启日志记录
	'LOG_LEVEL'  =>'EMERG,ALERT,ERR,SQL,WARN', // 只记录EMERG ALERT CRIT ERR 错误
);