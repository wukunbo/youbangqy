<?php

#导出excel
function excel_export($filename,$title,$data){
	include("ExportCsvComponent.class.php");
	$execl = new ExportCsvComponent($filename);
	$execl->setTitle($title);
	$execl->setData($data);
	$execl->export();
}


?>