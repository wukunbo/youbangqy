<?php
/**
 * Description of ExportCsvComponent
 *
 */
class ExportCsvComponent {
    public $fileName;
    public $title;
    public $data;
    public $sep = "\t";
    
    function __construct($fileName) {
        $this->fileName = $fileName;
    }
    
    function setData($data){
        $this->data = $data;
    }
    
    function setTitle($title){
        $this->title = $title;
    }
    function export(){
        $file_type = "vnd.ms-excel";
        $file_ending = "xls";
        header("Content-Type: application/$file_type;");
        header("Content-Disposition: attachment; filename=$this->fileName.$file_ending");
        header("Pragma: no-cache");
        header("Expires: 0");
        $schema_insert = '';
        $schema_insert = implode($this->sep,$this->title);
        $schema_insert = mb_convert_encoding($schema_insert,"GBK", "UTF-8");
        print(trim($schema_insert));
        print "\n";
        foreach ((array)$this->data as $row) {
            $schema_insert = '';
            $schema_insert = implode($this->sep,$row);
            $schema_insert = mb_convert_encoding($schema_insert,"GBK", "UTF-8");
            print(trim($schema_insert));
            print "\n";
        }
    }
}
