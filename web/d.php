<?php
error_reporting(E_ALL ^ E_NOTICE);
	 session_start();
	 header("Content-type: text/html; charset=utf-8");
	// session_start();
	 error_reporting(E_ALL ^ E_NOTICE);
var_dump($_SESSION);
$s=session_destroy();
echo "---------";
var_dump($s);
var_dump($_SESSION);
var_dump($s);